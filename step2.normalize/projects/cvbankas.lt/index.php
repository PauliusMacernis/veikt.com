<?php

// Makes life easier
chdir(__DIR__);


// Project name is the same as directory in which this file is (convention)
$projectName =  basename(__DIR__);

// step1 directory of all projects
$step1Dir = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'step1.download';

// step1 directory of the project
$step1DirProject = $step1Dir . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $projectName;
$step1DirProjectPosts = $step1DirProject . DIRECTORY_SEPARATOR . 'posts';


// Include abstract JobPosting class
require_once $step1Dir . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR
    . 'JobPosting.php';

// Include JobPostingStep1Download class
require_once $step1Dir . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR
    . 'JobPostingStep1Download.php';

// Include JobPostingStep2Normalize class
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPostingStep2Normalize.php';

// Require Job class (the class extended from JobPosting)
//require_once
//    '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
//    . 'step1.download' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR
//    . 'Job.php';

// Get params required & parse yml to array
require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'spyc' . DIRECTORY_SEPARATOR
    . 'Spyc.php';

$paramsFileYml = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'step3.output' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR
    . 'parameters.yml';

$parsedParams = Spyc::YAMLLoad($paramsFileYml);

// Make DB connection
try {
    $PDO = new PDO('mysql:'
        . 'host=' . $parsedParams['parameters']['database_host'] . ';'
        . 'dbname=' . $parsedParams['parameters']['database_name'] . ';'
        . 'charset=utf8'
        , $parsedParams['parameters']['database_user']
        , $parsedParams['parameters']['database_password']
    );
} catch (PDOException $e) {
    print "PDO Connection Error! ";// . $e->getMessage() . "";
    die();
}

// Read all directories inside the project's step1 posts directory
$dirs = scandir($step1DirProjectPosts);
foreach($dirs as $dir) {
    $skipIt = ['.', '..', '.gitignore'];

    if(in_array($dir, $skipIt)) {
        continue;
    }

    $postDir = $step1DirProjectPosts . DIRECTORY_SEPARATOR . $dir;
    $files = scandir($postDir);
    if(in_array($dir, $skipIt)) {
        continue;
    }

    $JobStep1 = new JobPostingStep1Download();
    $JobStep2 = new JobPostingStep2Normalize();
    $JobStep2->set('project', $projectName);

    // Add property values to Job
    foreach ($files as $fileName) {
        if(!property_exists($JobStep2, $fileName)) {
            continue; // Just skip the file not representing property of /Job
        }

        $fileContent = file_get_contents($postDir . DIRECTORY_SEPARATOR . $fileName);

        if(false === $fileContent) {
            continue; // Just skip the file if we are not able to read the content of it
        }

        $JobStep2->set($fileName, $fileContent);

    }

    // Normalize Job
    $JobStep2->normalize($JobStep1->getPropertyNamesToNormalizeFrom());

    // Save Job to DB
    $JobStep2->saveToDb($PDO);

    // Remove job posting from filesystem
    $it = new RecursiveDirectoryIterator($postDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it,
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) {
        if ($file->isDir()){
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($postDir);
}


