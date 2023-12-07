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
    private $primarySourcePath = null;
    private $sourcePath = '';
    private $curationPath = '';

    private $columnHeight = 19;

    private $ignoredTokens = ['&', 'and', 'the'];

    public function __construct($sourceDirectoryPath, $curationDirectoryPath, $primarySourceDirectoryPath = null) {
        $this->primarySourcePath = $primarySourceDirectoryPath;
        $this->sourcePath = $sourceDirectoryPath;
        $this->curationPath = $curationDirectoryPath;
    }

    /**
     * @throws Exception
     */
    public function run() {
        $curationDir = dir($this->curationPath);
        $sourceDir = dir($this->sourcePath);
        $primarySourceDir = null;

        if ($this->primarySourcePath) {
            $primarySourceDir = dir($this->primarySourcePath);
        }


        if (!$curationDir) {
            throw new Exception("Invalid Curation Directory: {$this->curationPath}");
        }
        if (!$primarySourceDir && $this->primarySourcePath) {
            throw new Exception(("Invalid primary source directory: {$this->primarySourcePath}"));
        }
        if (!$sourceDir) {
            throw new Exception(("Invalid source directory: {$this->sourcePath}"));
        }

        $artistDirectories = $this->collectDirectories($curationDir);

        foreach ($artistDirectories as $artistDirectory) {
            if ($this->fileCount($curationDir, true) >= $this->columnHeight) {
                echo "==> Complete <=== \n";
                return;
            }

            echo "==> " . $artistDirectory . "\n";

            if (!$this->fileExistsForDirectory($artistDirectory, $curationDir)) {
                if ($this->fileCount($curationDir, true) < $this->columnHeight) {
                    $moved = false;
                    if ($primarySourceDir) {
                        $moved = $this->getArtistFile($artistDirectory, $primarySourceDir, $curationDir);
                    }

                    if (!$moved) {
                        $moved = $this->getArtistFile($artistDirectory, $sourceDir, $curationDir);
                    }

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

        while ($this->fileCount($curationDir, true) < $this->columnHeight) {
            echo "==> Get Random File\n";
            $found = null;

            if ($primarySourceDir) {
                $found = $this->getRandomFile($primarySourceDir, $curationDir);
            }

            if (!$found) {
                $this->getRandomFile($sourceDir, $curationDir);
            }
        }

        if ($this->fileCount($curationDir, true) >= $this->columnHeight) {
            echo "==> Complete <=== \n";
            return;
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
//                    echo "[{$directoryName}] matches {$entry}\n";

                    return true;
                }
            }
        }

//        echo "[{$directoryName}] no matches in current directory\n";

        return false;
    }

    /**
     * @param Directory $directory
     *
     * @return int
     */
    private function fileCount($directory, $includeDirectory = false) {
        $directory->rewind();

        $count = 0;
        while (false !== ($entry = $directory->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $fullPath = $directory->path . '/' . $entry;

            if (!is_dir($fullPath) || $includeDirectory) {
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

//            echo "[$artist] " . $fileToMove . "\n";
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
            $fileCount = rand(1, $fileCount);

            while (false !== ($entry = $sourceDir->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                if (is_file($sourceDir->path . '/' . $entry) && $fileCount == 1) {
                    echo $entry . "\n";

                    $sourceDirName = $sourceDir->path;
                    $destinationDirName = $destinationDir->path;

                    $rs = rename($sourceDirName . '/' . $entry, $destinationDirName . '/' . $entry);

                    if ($rs) {
                        $trigger = new EnqueueToVLCTrigger($destinationDirName . '/' . $entry);
                        $trigger->run();
                    }

                    return true;
                }

                $fileCount--;
            }
        }

        return false;
    }
}