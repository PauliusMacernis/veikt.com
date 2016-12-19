<?php

// Makes life easier
chdir(__DIR__);

// Require autoload
require_once '..'
    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Use auto-loading classes...
use DownloadCore\Settings;
use DownloadProject\Cvbankas\Lt\Classes\Auditor;
use DownloadProject\Cvbankas\Lt\Classes\Browser;

// Get settings: All & Project specific
$settings = new Settings(__DIR__);
$settingsAll = $settings->getAll();
$settingsProject = $settings->getProject();

// Initiate main objects to deal with the content
$Browser = new Browser(__DIR__, $settingsAll, $settingsProject);
$Auditor = new Auditor(__DIR__, $settingsAll, $settingsProject, $Browser->getDownloadsDirectoryPathJobs());


// Let's make one url download possible
//  In case we will want to download one specific url
//  For example: http://download.veikt.dev/code/php/project/cvbankas/lt/index.php?url=http://www.cvbankas.lt/pardavimu-telefonu-vadybininkas-e-vilniuje-lietuvos-rinka-vilniuje/1-4204758
/*
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
*/
// We are starting to get the whole list of jobs...
$Browser->markQueueStart();

// Let's make many urls download possible
//  In case we will want to download many urls
//  For example: http://download.veikt.dev/code/php/project/cvbankas/lt/index.php
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

$Browser->markQueueFinish();
$Auditor->doReport();

echo "\nDONE.";
exit;