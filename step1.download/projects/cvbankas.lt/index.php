<?php

// Makes life easier
chdir(__DIR__);
$project =  basename(__DIR__);

// Include core files needed
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'ContentManager.php';
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPosting.php';
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPostingStep1Download.php';
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'Job.php';
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PageList.php';

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
