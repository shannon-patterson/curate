<?php

include_once('../ArtistCompleteTrigger.php');

$artistPath = "F:\\sorting\\music\\41st And Home";
$sourcePath = "F:\\sorting\\source";

$trigger = new \CurationPipeline\ArtistCompleteTrigger($artistPath, $sourcePath);
$trigger->run();