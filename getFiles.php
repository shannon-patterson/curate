<?php

use CurationPipeline\PopulateCurationDirectory;

require_once("ArtistCompleteTrigger.php");
require_once("PopulateCurationDirectory.php");
require_once("EnqueueToVLCTrigger.php");

$sourceDir = "C:\Users\shann\sorting\source";
$newSourceDir = 'C:\Users\shann\sorting\2022';
$curationDir = "C:\Users\shann\sorting\music";

$populateCuration = new PopulateCurationDirectory($sourceDir, $curationDir, $newSourceDir);
try {
    $populateCuration->run();
} catch (Exception $e) {
    echo $e->getMessage();

    print_r($e->getTrace());
    die();
}

