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


try {
    failIfNotValid($argv);

    $projectDirToNormalize = (string)trim($argv[1]);
    $projectDirToNormalizeParent = getParentDir($projectDirToNormalize);
    $uniqueProcessIdAssignedByMain = (string)trim($argv[2]);

    $Job = new JobAsFile(__DIR__, $projectDirToNormalize);
    $Job->validateDownloaded();
    $Job->normalize(JobContentNormalizer::class, JobContentTransformer::class);
    $Job->validateNormalized();
    $Job->writeNormalizedContentToDb(JobContentToDbWriter::class);
    $Job->validateWritten();
    $Job->removeDownloadedFiles();
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

function failIfNotValid(array $argv, $argumentsCountExpected = 3)
{

    if (count($argv) < $argumentsCountExpected) {
        throw new \LogicException(
            "Arguments not received or received as empty. Arguments received: "
            . print_r($argv, true)
        );
    }

    for ($varCount = 0; $varCount < $argumentsCountExpected; $varCount++) {
        $var = isset($argv[$varCount]) ? trim($argv[$varCount]) : null;
        if (empty($var)) {
            throw new \LogicException(
                "Argument #" . $varCount . " is not valid. Arguments received: "
                . print_r($argv, true)
            );
        }
    }
    //END. Check for empty
}

/**
 * Get parent directory path.
 * Using "../" will not work, because the child may not exist.
 *
 * @param $dir      Child dir
 * @return string   Parent dir
 */
function getParentDir($dir)
{

    $ds = DIRECTORY_SEPARATOR;

    $path = explode($ds, $dir);
    array_pop($path);

    return implode($ds, $path);

}