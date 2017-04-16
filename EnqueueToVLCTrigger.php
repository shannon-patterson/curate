<?php

namespace CurationPipeline;


class EnqueueToVLCTrigger
{

    private $fileName;
    private $VLCPath = 'C:\Program Files (x86)\VideoLAN\VLC\vlc.exe';

    public function __construct($fileName) {

        $this->fileName = $fileName;
    }

    public function run() {
        if (!is_file($this->fileName)) {
            throw new \Exception("{$this->fileName} is not a file.");
        }

        $command = "\"{$this->VLCPath}\" --playlist-enqueue \"{$this->fileName}\"";
        @exec($command);
    }
}