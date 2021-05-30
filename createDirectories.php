<?php

use CurationPipeline\CreateDirectoriesAction;

require_once("CreateDirectoriesAction.php");

$sourceDir = "C:\Users\shann\sorting\music";

$createDirectories = new CreateDirectoriesAction($sourceDir);
try {
    $createDirectories->run();
} catch (Exception $e) {
    echo $e->getMessage();

    print_r($e->getTrace());
    die();
}

