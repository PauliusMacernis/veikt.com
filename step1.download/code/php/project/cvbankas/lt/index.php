<?php

// @TODO: Job->id is not required, but files are created based on that id. This id must be required or id should be auto-generated from URL.


// Makes life easier
chdir(__DIR__);
$project =  basename(__DIR__);

// Define core files
$fileAutoload = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . '..' . DIRECTORY_SEPARATOR
    . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$fileSettings = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . '..' . DIRECTORY_SEPARATOR
    . 'settings.json';

// Require core files
require_once $fileAutoload;

// Require and decode all settings
$settings = file_get_contents($fileSettings);
$settings = json_decode($settings, true);
if(!isset($settings) || empty($settings)) {
    die('No settings found.');
}

// Find project settings
if(!isset($settings['projects-on']) || !is_array($settings['projects-on'])) {
    die('No projects enabled in \'settings.json\'.');
}

// Find settings of this project
$projectSettings = array();
foreach($settings['projects-on'] as $projectName => $projectSettingsTemp) {
    if(!isset($projectSettingsTemp['entrance.sh'])) {
        continue;
    }
    $projectDir = pathinfo($projectSettingsTemp['entrance.sh'], PATHINFO_DIRNAME);
    if(
        substr_compare(
            __DIR__,
            $projectDir,
            strlen(__DIR__) - strlen($projectDir),
            strlen($projectDir)
        ) === 0
    ) {
        // $projectSettings item is found!
        $projectSettings = $projectSettingsTemp;
        break;
    }
}



// use...
use Project\Cvbankas\Lt\Classes\Auditor;
use Project\Cvbankas\Lt\Classes\Browser;

$Browser = new Browser($projectSettings, __DIR__, $settings);
$Auditor = new Auditor($projectSettings, __DIR__, $settings);

// Let's make one url download possible
//  In case we will want to download several specific urls
if(!isset($_REQUEST['url'])) {
    $List = $Browser->getFirstListOfJobLinks();
} else {
    $List = array($_REQUEST['url'] => $_REQUEST['url']);
}
//$Jobs = array();

do {
    // Get and save jobs by one, not by the whole list.
    foreach($List as $iJobUrl) {
        // Simplify the list & Get new jobs of the simplified list
        $iJobList = array($iJobUrl => $iJobUrl);
        $iJob = $Browser->getJobsFromTheList($iJobList);

        // Save data to files
        $iSave = $Browser->saveJobsToFiles($iJob);

        // Put fresh data on top of existing
        //$Jobs = $iJob + $Jobs;

        // Logging and similar stuff
        $Auditor->registerListAndJobs($iJobList, $iJob, $iSave);

        if(isset($_REQUEST['url'])) {
            break 2; // break the foreach and do..while
                     //  if this is the request for one url only
        }
    }
} while(
    $List = $Browser->getNextListOfJobLinks()
);

$Auditor->doReport(__DIR__);
