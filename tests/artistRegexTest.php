<?php
require_once("../ArtistCompleteTrigger.php");
require_once("../PopulateCurationDirectory.php");
require_once("../EnqueueToVLCTrigger.php");

$sourceDir = "F:\sorting\source";
$curationDir = "F:\sorting\music";
$destinationDir = "F:\Music";
$filesToHave = 10;

$curation = dir($curationDir);

$artistDirectories = [];
$fileCount = 0;

$populateCuration = new \CurationPipeline\PopulateCurationDirectory($sourceDir, $curationDir);

$artist = "Simon & Garfunkel";
$sourceDir = dir($sourceDir);

$populateCuration->collectMatches($artist, $sourceDir);