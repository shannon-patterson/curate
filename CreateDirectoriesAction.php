<?php

namespace CurationPipeline;


use Directory;
use Exception;

class CreateDirectoriesAction
{
    private $sourcePath = '';

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $sourceDir = dir($this->directory);

        $files = $this->collectFiles($sourceDir);

        $sourceDir->rewind();
        $directories = $this->collectDirectories($sourceDir);

        foreach ($files as $file) {
            try {
                $artistName = $this->getArtistName($file);

                if (!in_array(strtolower($artistName), $directories)) {
                    $this->createDirectory($artistName, $sourceDir);
                    $directories[] = $artistName;

                    echo "Created directory $artistName\n";

                    rename(
                        $sourceDir->path . DIRECTORY_SEPARATOR . $file,
                        $sourceDir->path . DIRECTORY_SEPARATOR . $artistName . DIRECTORY_SEPARATOR . $file
                    );

                    echo "Moved $file \n";
                }
            } catch (Exception $exception) {
                echo $exception . "$file" . "\n";
            }
        }
        exit();
    }

    private function createDirectory($artistName, $dir)
    {

        $newDirPath = $dir->path . DIRECTORY_SEPARATOR . ucwords(strtolower($artistName));

        if (!is_dir($newDirPath)) {
            $response = mkdir($newDirPath);

            if ($response === false) {
                throw new Exception("Could not make iteration directory {$newDirPath}");
            }

            return $response;
        }
    }

    private function getArtistName(string $fileName)
    {
        $options = [' - ', '-'];

        foreach ($options as $option) {
            $idx = strpos($fileName, $option);

            if ($idx !== false) {
                return substr($fileName, 0, $idx);
            }
        }

        throw new Exception("Could not find an artist name");
    }

    private function collectFiles(Directory $directory)
    {
        $files = [];

        while (false !== ($entry = $directory->read())) {
            if ($entry == '.' || $entry == '..') {
                // skip
            } else {
                $filePath = $directory->path . "/" . $entry;

                if (!is_dir($filePath)) {
                    $files[] = $entry;
                }
            }
        }

        return $files;
    }

    private function collectDirectories(Directory $curationDir) {
        $dirs = [];

        while (false !== ($entry = $curationDir->read())) {
            if ($entry == '.' || $entry == '..') {
                // skip
            } else {
                $fullPath = $curationDir->path . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($fullPath)) {
                    $dirs[] = strtolower($entry);
                }
            }
        }

        return $dirs;
    }
}