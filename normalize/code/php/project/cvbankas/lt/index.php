<?php

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//set_error_handler(array(new \DownloadCore\ErrorHandler, 'defaultErrorHandler'));
//register_shutdown_function(array(new \NormalizeCore\ErrorHandler, 'defaultRegisterShutdown'));


use NormalizeCore\JobAsFile;
use NormalizeCore\JobContentToDbWriter;
use NormalizeProject\Cvbankas\Lt\Classes\JobContentNormalizer;
use NormalizeProject\Cvbankas\Lt\Classes\JobContentTransformer;


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


// Print the dot to imitate the "progress bar"
echo ".";
exit;

function failIfNotValid(array $argv, $argumentsCountExpected = 3)
{
    if (count($argv) < $argumentsCountExpected) {
        throw new \NormalizeCore\ErrorHandler(
            "Arguments not received or received as empty. Arguments received: "
            . print_r($argv, true)
        );
    }

    for ($varCount = 0; $varCount < $argumentsCountExpected; $varCount++) {
        $var = isset($argv[$varCount]) ? trim($argv[$varCount]) : null;
        if (empty($var)) {
            throw new \NormalizeCore\ErrorHandler(
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