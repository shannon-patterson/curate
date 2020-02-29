<?php

namespace CurationPipeline;

use Directory;
use Exception;

class ArtistCompleteTrigger {

    private $iterationLimit = 3;
    private $artistDir = '';
    private $sourcePath = '';

    public function __construct($artistDirectoryPath, $sourceDirectoryPath) {
        $this->artistDir = $artistDirectoryPath;
        $this->sourcePath = $sourceDirectoryPath;
    }

    /**
     * @throws Exception
     */
    public function run() {
        $artistDir = dir($this->artistDir);
        $sourceDir = dir($this->sourcePath);

        if (!$artistDir || !$sourceDir) {
            throw new Exception("Invalid directory");
        }

        $iterationNumber = $this->getIterationCount($artistDir);

        if ($iterationNumber === false) {
            $this->resetIterationDir($artistDir, $sourceDir);
            $this->makeIterationDir($artistDir, 1);

        } elseif ($iterationNumber < $this->iterationLimit) {
            echo "ARTIST has levelled up {$artistDir->path} \n";
            $this->resetIterationDir($artistDir, $sourceDir);
            $this->updateIterationDir($artistDir, $iterationNumber, $iterationNumber + 1);

        } else {

            echo "ARTIST has reached max iteration count. {$artistDir->path} \n";
            //            throw new \Exception("Not yet configured to move files to final destination.  ARTIST = {$artistDir->path}");
        }


    }

    /**
     * @param Directory $dir
     *
     * @return bool
     */
    private function getIterationCount($dir) {

        $dir->rewind();

        while (false !== ($filename = $dir->read())) {
            if ($filename == '.' || $filename == '..') {
                // skip
            } elseif (is_dir($dir->path . '/' . $filename) && (intval($filename) == $filename)) {
                return intval($filename);
            }
        }

        return false;
    }

    /**
     * @param Directory $origin
     * @param Directory $destination
     *
     * @return int
     * @throws Exception
     */
    private function resetIterationDir($origin, $destination) {
        $origin->rewind();

        $count = 0;
        while (false !== ($file = $origin->read())) {
            if (!is_dir($origin->path . '/' . $file)) {
                $old = $origin->path . '/' . $file;
                $new = $destination->path . '/' . $file;

                $response = rename($old, $new);

                if ($response === false) {
                    throw new Exception("Could not move file {$old} to {$new}");
                }
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param Directory $dir
     * @param string    $dirName
     *
     * @return bool
     * @throws Exception
     */
    private function makeIterationDir($dir, $dirName) {

        $newDirPath = $dir->path . '/' . $dirName;

        $response = mkdir($newDirPath);

        if ($response === false) {
            throw new Exception("Could not make iteration directory {$newDirPath}");
        }

        return $response;
    }

    /**
     * @param Directory $dir
     * @param string    $oldName
     * @param string    $newName
     *
     * @return bool
     * @throws Exception
     */
    private function updateIterationDir($dir, $oldName, $newName) {
        $oldName = $dir->path . '/' . $oldName;
        $newName = $dir->path . '/' . $newName;

        $response = rename($oldName, $newName);

        if ($response === false) {
            throw new Exception("Could not rename iteration directory {$oldName}");
        }

        return $response;
    }
}