<?php
/**
 * Created by PhpStorm.
 * User: Shannon
 * Date: 11/30/2016
 * Time: 9:04 AM
 */

namespace CurationPipeline;


use Directory;
use Exception;

class PopulateCurationDirectory {
    private $sourcePath = '';
    private $curationPath = '';

    private $filesToHave = 6;

    private $ignoredTokens = ['&', 'and', 'the'];

    public function __construct($sourceDirectoryPath, $curationDirectoryPath) {
        $this->sourcePath = $sourceDirectoryPath;
        $this->curationPath = $curationDirectoryPath;
    }

    public function getFilesToHave() {
        return $this->filesToHave;
    }

    public function setFilesToHave($x) {
        $this->filesToHave = $x;
    }

    /**
     * @throws Exception
     */
    public function run() {
        $curationDir = dir($this->curationPath);
        $sourceDir = dir($this->sourcePath);

        if (!$curationDir) {
            throw new Exception("Invalid Curation Directory: {$this->curationPath}");
        }

        $artistDirectories = $this->collectDirectories($curationDir);

        foreach ($artistDirectories as $artistDirectory) {
            if (!$this->fileExistsForDirectory($artistDirectory, $curationDir)) {
                if ($this->fileCount($curationDir) < $this->filesToHave) {
                    $moved = $this->getArtistFile($artistDirectory, $sourceDir, $curationDir);

                    if (!$moved) {
                        $fullArtistDirPath = $curationDir->path . '/' . $artistDirectory;

                        $artistCompleteTrigger = new ArtistCompleteTrigger(
                            $fullArtistDirPath, $sourceDir->path
                        );
                        $artistCompleteTrigger->run();
                    }
                }
            }
        }

        while ($this->fileCount($curationDir) < $this->filesToHave) {
            $this->getRandomFile($sourceDir, $curationDir);
        }
    }

    /**
     * @param Directory $curationDir
     *
     * @return array
     */
    private function collectDirectories($curationDir) {
        $dirs = [];

        while (false !== ($entry = $curationDir->read())) {
            if ($entry == '.' || $entry == '..') {
                // skip
            } else {
                $fullPath = $curationDir->path . '/' . $entry;

                if (is_dir($fullPath)) {
                    $dirs[] = $entry;
                }
            }
        }

        return $dirs;
    }

    /**
     * @param string    $directoryName
     * @param Directory $curationDir
     *
     * @return bool
     */
    private function fileExistsForDirectory($directoryName, $curationDir) {

        $curationDir->rewind();

        $tokens = [$directoryName];
        if (strpos($directoryName, ' ') !== false) {
            $tokens = explode(' ', $directoryName);
            foreach ($tokens as $key => $token) {
                if (in_array(strtolower($token), $this->ignoredTokens)) {
                    unset($tokens[$key]);
                }
            }
        }

        while (false !== ($entry = $curationDir->read())) {
            $isMatch = true;

            foreach ($tokens as $token) {
                $isMatch = $isMatch && (stripos($entry, $token) !== false);
            }

            if ($isMatch) {
                $fullPath = $curationDir->path . '/' . $entry;
                if (!is_dir($fullPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Directory $directory
     *
     * @return int
     */
    private function fileCount($directory) {
        $directory->rewind();

        $count = 0;
        while (false !== ($entry = $directory->read())) {
            $fullPath = $directory->path . '/' . $entry;
            if (!is_dir($fullPath)) {
                $count++;
            }
        }

        //        echo $count . "\n";
        return $count;
    }

    /**
     * @param string    $artist
     * @param Directory $sourceDir
     * @param Directory $destinationDir
     *
     * @return bool
     * @throws Exception
     */
    public function getArtistFile($artist, $sourceDir, $destinationDir) {
        // $sourceDir->rewind();

        $matches = $this->collectMatches($artist, $sourceDir);

        if (count($matches)) {
            $fileToMove = $matches[rand(0, count($matches) - 1)];

            echo $fileToMove . "\n";

            $sourceDirName = $sourceDir->path;
            $destinationDirName = $destinationDir->path;

            $rs = rename($sourceDirName . '/' . $fileToMove, $destinationDirName . '/' . $fileToMove);

            if ($rs) {
                $trigger = new EnqueueToVLCTrigger($destinationDirName . '/' . $fileToMove);
                $trigger->run();
            }

            return $rs;
        }

        return false;
    }

    /**
     * @param string    $artist
     * @param Directory $sourceDir
     *
     * @return array
     */
    public function collectMatches($artist, $sourceDir) {
        $sourceDir->rewind();

        $tokens = [$artist];
        if (strpos($artist, ' ') !== false) {
            $tokens = explode(' ', $artist);

            foreach ($tokens as $key => $token) {
                if (in_array(strtolower($token), $this->ignoredTokens)) {
                    unset($tokens[$key]);
                }
            }
        }

        $matches = [];
        while (false !== ($entry = $sourceDir->read())) {
            $isMatch = true;

            foreach ($tokens as $token) {
                $isMatch = $isMatch && (stripos($entry, $token) !== false);
            }

            if ($isMatch) {
                $matches[] = $entry;
            }
        }

        return $matches;
    }

    /**
     * @param Directory $sourceDir
     * @param Directory $destinationDir
     *
     * @return bool
     */
    private function getRandomFile($sourceDir, $destinationDir) {
        $sourceDir->rewind();

        $fileCount = 0;
        while (false !== ($entry = $sourceDir->read())) {
            if (!is_dir($sourceDir->path . '/' . $entry)) {
                $fileCount++;
            }
        }

        if ($fileCount) {
            $sourceDir->rewind();
            $fileCount = rand(0, $fileCount - 1);

            while (false !== ($entry = $sourceDir->read())) {
                if (is_file($sourceDir->path . '/' . $entry)) {
                    $fileCount--;

                    if ($fileCount == 1) {
                        echo $entry . "\n";

                        $sourceDirName = $sourceDir->path;
                        $destinationDirName = $destinationDir->path;

                        $rs = rename($sourceDirName . '/' . $entry, $destinationDirName . '/' . $entry);

                        if ($rs) {
                            $trigger = new EnqueueToVLCTrigger($destinationDirName . '/' . $entry);
                            $trigger->run();
                        }

                    }
                }
            }
        }

        return false;
    }
}