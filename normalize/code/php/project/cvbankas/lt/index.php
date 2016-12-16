<?php

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


use NormalizeCore\JobAsFile;
use NormalizeCore\JobContentToDbWriter;
use NormalizeProject\Cvbankas\Lt\Classes\JobContentNormalizer;
use NormalizeProject\Cvbankas\Lt\Classes\JobContentTransformer;

echo $argv[1] . "\n";
//echo "\n\n";
die();

try {

    // Check for empty
    // @todo: Better solution for checking if empty?
    $empty = "...";
    if (count($argv) < 3
        || empty($argv[1]) || (string)$argv[1] === (string)$empty
        || empty($argv[2]) || (string)$argv[2] == (string)$empty
    ) {
        $msg = "Arguments not received or received as empty. Arguments received: "
            . print_r($argv, true);
        throw new \LogicException($msg);
    }
    //END. Check for empty

    $projectDirToNormalize = isset($argv[1]) ? (string)$argv[1] : '';
    $uniqueProcessIdAssignedByMain = isset($argv[2]) ? (string)$argv[2] : '';


    $Job = new JobAsFile(__DIR__, $projectDirToNormalize);
    $Job->validateDownloaded();
    $Job->normalize(JobContentNormalizer::class, JobContentTransformer::class);
    $Job->validateNormalized();
    $Job->writeNormalizedContentToDb(JobContentToDbWriter::class);
    $Job->validateWritten();
    $Job->removeDownloadedFiles();
    $Job->removeDownloadedFilesStartFinishMarkers();
    $Job->removeDownloadedFilesDate();
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n"
        . 'File: ' . $e->getFile() . "\n"
        . 'Line: ' . $e->getLine() . "\n"
        . 'Backtrace: ' . $e->getTraceAsString();
    exit;
}

// Print the dot to imitate the "progress bar"
echo ".";
exit;