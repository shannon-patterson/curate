<?php

include_once('../EnqueueToVLCTrigger.php');


$file = "F:\sorting\music\Pearl Jam - 2005-09-13 Hamilton - 05 - Sad.mp3";

$trigger = new \CurationPipeline\EnqueueToVLCTrigger($file);
$trigger->run();