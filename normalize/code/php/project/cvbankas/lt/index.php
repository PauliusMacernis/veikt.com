<?php

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


use \NormalizeCore\JobAsFile;
use \NormalizeCore\JobContentToDbWriter;
use \NormalizeProject\Cvbankas\Lt\Classes\JobContentNormalizer;
use \NormalizeProject\Cvbankas\Lt\Classes\JobContentTransformer;

try {
    $projectDirToNormalize = isset($argv[1]) ? (string)$argv[1] : '';
    $uniqueProcessIdAssignedByMain = isset($argv[2]) ? (string)$argv[2] : '';

    if(!$projectDirToNormalize) {
        die('No dir to normalize...');
    }

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

var_dump(' OK|DONE '); die();

//var_dump($Job); die();


//$Job->terminateIfNotValid();

die('x................x');

//die('W?');

//$Auditor = new Auditor();

/*
$JobAsFilesystem = new DownloadedJobFilesystem();
if(!$JobAsFilesystem->isContentOk($Auditor)) {
    $Auditor->log();
    exit;
}

$JobAsCrawler = new JobAsCrawler();
if(!$JobAsCrawler->normalizeJob()) {
    exit;
}
if(!$JobAsCrawler->saveToDb()) {
    exit;
}

return 0;






// FilesJob: Check if content is ok (compare to settings of settings.json). Terminate if not ok. Log the problem?
class DownloadedJobFilesystem {
    public function isContentOk($Auditor){
        if(!$this->isContentOkFilesystem()) {
            $Auditor->registerContentNotOkFilesystem();
            return false;
        }
        if(!$this->isContentOkSettings()) {
            $Auditor->registerContentNotOkSettings();
            return false;
        }

        return true;

    }
        protected function isContentOkFilesystem() {
            return true;
        }
        protected function isContentOkSettings() {
            return true;
        }

}

class Auditor {
    public function registerContentNotOkFilesystem() {

    }

    public function registerContentNotOkSettings() {

    }
}

class JobAsCrawler {

}
*/



/*
// DownloadedJobAsText ext FilesJob: Get all existing files into one object? Properties of "DownloadedJob" is filenames, values - content of these files as string?
class DownloadedJobAsText extends DownloadedJobAsFiles  {
    public function __set() {
        // Assign values to properties as content (text) of files
    }
}

// "DownloadedJobAsCrawler" must have one the method for converting properties of "DownloadedJob" from string to \Symfony\Component\DomCrawler\Crawler $Content objects
class DownloadedJobAsCrawler extends DownloadedJobAsFiles  {
    public function __set() {
        // Assign values to properties as \Symfony\Component\DomCrawler\Crawler $Content objects
    }
}

class DownloadedJob extends DownloadedJobAsText | DownloadedJobAsCrawler {
    public function __set() {
        parent::__set();
    }
}

class NormalizedJob {

    public __constructor(DownloadedJob)

    public {method name is the same as settings.json wants, for example: "base_salary_min"}
}

class SaverToDb {
    public function save(NormalizedJob);
}
*/

// Pass


die('@TODO: ' . $projectDirToNormalize . "\n");

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Use auto-loading classes...
use Core\Helper;
use Project\Cvbankas\Lt\Classes\Auditor;
use Project\Cvbankas\Lt\Classes\Browser;

// Get settings: All & Project specific
$settingsAll = Helper::getSettingsAll(__DIR__);
$settingsProject = Helper::getSettingsProject(__DIR__, $settingsAll);

// Initiate main objects to deal with the content
$Browser = new Browser(__DIR__, $settingsAll, $settingsProject);
$Auditor = new Auditor(__DIR__, $settingsAll, $settingsProject);

// Let's make one url download possible
//  In case we will want to download one specific url
//  For example: http://step1.veikt.dev/code/php/project/cvbankas/lt/index.php?url=http://www.cvbankas.lt/pardavimu-telefonu-vadybininkas-e-vilniuje-lietuvos-rinka-vilniuje/1-4204758
if (isset($_REQUEST['url'])) {
    $iJobUrl = $_REQUEST['url'];

    // Get job info
    $iJobList = array($iJobUrl => $iJobUrl);
    $iJob = $Browser->getJobsFromTheList($iJobList);

    // Save info to files
    $iSave = $Browser->saveJobsToFiles($iJob);

    // Log the action
    $Auditor->registerListAndJobs($iJobList, $iJob, $iSave);

    // Done.
    $Auditor->doReport();

    echo "\nDONE.";
    exit;
}


// Let's make many urls download possible
//  In case we will want to download many urls
//  For example: http://step1.veikt.dev/code/php/project/cvbankas/lt/index.php
$List = $Browser->getFirstListOfJobLinks();

do {
    // Get and save jobs by one, not by the whole list.
    foreach ($List as $iJobUrl) {
        // Simplify the list & Get new jobs of the simplified list
        $iJobList = array($iJobUrl => $iJobUrl);
        $iJob = $Browser->getJobsFromTheList($iJobList);

        // Save data to files
        $iSave = $Browser->saveJobsToFiles($iJob);

        // Put fresh data on top of existing
        //$Jobs = $iJob + $Jobs;

        // Logging and similar stuff
        $Auditor->registerListAndJobs($iJobList, $iJob, $iSave);
    }
} while (
$List = $Browser->getNextListOfJobLinks()
);

$Auditor->doReport();

echo "\nDONE.";
exit;