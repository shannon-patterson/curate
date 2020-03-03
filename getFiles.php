<?php

use CurationPipeline\PopulateCurationDirectory;

require_once("ArtistCompleteTrigger.php");
require_once("PopulateCurationDirectory.php");
require_once("EnqueueToVLCTrigger.php");

$sourceDir = "E:\Shannon\Media\sorting\source";
$curationDir = "E:\Shannon\Media\sorting\music";
$destinationDir = "E:\Shannon\Media\Music";
$filesToHave = 10;

$curation = dir($curationDir);

$artistDirectories = [];
$fileCount = 0;

$populateCuration = new PopulateCurationDirectory($sourceDir, $curationDir);
try {
    $populateCuration->run();
} catch (Exception $e) {
    echo $e->getMessage();

    print_r($e->getTrace());
    die();
}

