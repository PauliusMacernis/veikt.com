<?php

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

$List = $Browser->getFirstListOfJobLinks();
$Jobs = array();

do {
    // Get new jobs of the list,
    $NewJobs = $Browser->getJobsFromTheList($List);

    // Save data to files
    $Save = $Browser->saveJobsToFiles($NewJobs);

    // Put fresh data on top of existing
    $Jobs = $NewJobs + $Jobs;

    // Logging and similar stuff
    $Auditor->registerListAndJobs($List, $Jobs, $Save);
} while(
    $List = $Browser->getNextListOfJobLinks()
);

$Auditor->doReport();


/////////////////////////
die('FINISHED. INDEX.');
/*
// Action!
$Browser = new Browser();
//$urls = $ContentManager->collectAllUrlsOfJobPostings();
$resultsSuccess = array();
$resultsFailure = array();

foreach($Browser->getNextPageList() as $pageList) {

    // Get URL content
    $success = $pageList->getJobPostingContent()->saveToFiles();

    // Try once again if failed #1
    if(!$success) {
        sleep(rand(1,5)); // Wait few seconds
        $success = $pageList->getJobPostingContent()->saveToFiles();
    }

    // Try once again if failed #2
    if(!$success) {
        sleep(rand(5,10)); // Wait few seconds
        $success = $pageList->getJobPostingContent()->saveToFiles();
    }

    // Try once again if failed #3
    if(!$success) {
        sleep(rand(10,30)); // Wait few seconds
        $success = $url->getJobPostingContent()->saveToFiles();
    }

    // Success or fail: register
    if($success) {
        $resultsSuccess[$url->getUrl()] = $url->getUrl();
    } else {
        $resultsFailure[$url->getUrl()] = $url->getUrl();
    }

    $urls[$url->getAsString()] = $url->getAsString();

}

$ContentManager->reportSuccess($resultsSuccess);
$ContentManager->reportFailure($resultsFailure);



die('OK.ENDED.');






//$ContentManager = new ContentManager();


*/

/*$Logger = new Logger();


$jobUrls = $ContentManager->getJobUrlsOfCurrentListPage();
foreach($jobUrls as $jobUrl) {
    $success = $jobUrl->getJobContent()->saveJobContentToRequiredFiles($settings);
    $Logger->log($jobUrlsOfCurrentListPage->, $jobUrl->getValueAsString, $success);
}

////////////////////

$ContentManager = new ContentManager();
$PageList = new PageList(PageList::firstPageListUrl, $ContentManager, $project);
$jobs = array();
if (is_array($PageList->get('jobs'))) {
    $jobs = $PageList->get('jobs');
}

while ($nextUrl = $PageList->getNextPageListUrl()) {
    $PageList = new PageList($nextUrl, $ContentManager, $project);
    $jobs = array_merge($jobs, $PageList->get('jobs'));
}

die('DONE.STOP.INDEX');
*/
// Action!
$ContentManager = new ContentManager();
//$urls = $ContentManager->collectAllUrlsOfJobPostings();
$resultsSuccess = array();
$resultsFailure = array();

foreach($ContentManager->getNextPageListUrl() as $url) {

    // Get URL content
    $success = $url->getJobPostingContent()->saveToFiles();

    // Try once again if failed #1
    if(!$success) {
        sleep(rand(1,5)); // Wait few seconds
        $success = $url->getJobPostingContent()->saveToFiles();
    }

    // Try once again if failed #2
    if(!$success) {
        sleep(rand(5,10)); // Wait few seconds
        $success = $url->getJobPostingContent()->saveToFiles();
    }

    // Try once again if failed #3
    if(!$success) {
        sleep(rand(10,30)); // Wait few seconds
        $success = $url->getJobPostingContent()->saveToFiles();
    }

    // Success or fail: register
    if($success) {
        $resultsSuccess[$url->getUrl()] = $url->getUrl();
    } else {
        $resultsFailure[$url->getUrl()] = $url->getUrl();
    }

    $urls[$url->getAsString()] = $url->getAsString();

}

$ContentManager->reportSuccess($resultsSuccess);
$ContentManager->reportFailure($resultsFailure);


// .....................

//$client = new Client();



var_dump($client); die();

//require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'ContentManager.php';
//require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPosting.php';
//require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPostingStep1Download.php';
//require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Job.php';
//require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PageList.php';

// Action!
$ContentManager = new ContentManager();
$PageList = new PageList(PageList::firstPageListUrl, $ContentManager, $project);
$jobs = array();
if (is_array($PageList->get('jobs'))) {
    $jobs = $PageList->get('jobs');
}

while ($nextUrl = $PageList->getNextPageListUrl()) {
    $PageList = new PageList($nextUrl, $ContentManager, $project);
    $jobs = array_merge($jobs, $PageList->get('jobs'));
}
