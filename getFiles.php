<?php

use CurationPipeline\PopulateCurationDirectory;

require_once("ArtistCompleteTrigger.php");
require_once("PopulateCurationDirectory.php");
require_once("EnqueueToVLCTrigger.php");

$sourceDir = "E:\Shannon External Drive backup\sorting\source";
$curationDir = "E:\Shannon External Drive backup\sorting\music";
$destinationDir = "E:\Shannon External Drive backup\Music";
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

