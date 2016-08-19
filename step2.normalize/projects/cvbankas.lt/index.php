<?php

// Makes life easier
chdir(__DIR__);
$completedSuccessfully = array();


// Project name is the same as directory in which this file is (convention)
$projectName =  basename(__DIR__);

// step1: directory of all projects
$step1Dir = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'step1.download';
// step1: directory of the project
$step1DirProject = $step1Dir . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $projectName;
// step1: directory where posts of project are downloaded
$step1DirProjectPosts = $step1DirProject . DIRECTORY_SEPARATOR . 'posts';


// Include abstract JobPosting class
require_once $step1Dir . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPosting.php';
// Include JobPostingStep1Download class
require_once $step1Dir . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'JobPostingStep1Download.php';
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

// Get job columns required to update
$prefixRequired = 'step1_';
//$JobStep2 = new JobPostingStep2Normalize();
$propertiesRequiredToUpdate = JobPostingStep2Normalize::getColumnsRequiredToUpdate($PDO, $prefixRequired);
$propertiesNeededToBeNormalizedTo = JobPostingStep2Normalize::getColumnsNeededToBeNormalizedTo($PDO, $prefixRequired);


// Read all directories inside the project's step1 posts directory
$dirs = scandir($step1DirProjectPosts);
foreach($dirs as $dir) {
    $skipIt = ['.', '..', '.gitignore'];

    // Skip examining dots and files of VCS
    if(in_array($dir, $skipIt)) {
        continue;
    }

    // Get downloaded files of the job ad
    $postDir = $step1DirProjectPosts . DIRECTORY_SEPARATOR . $dir;
    $files = scandir($postDir);
    $files = array_filter($files, function($file) use ($skipIt) {
        if(in_array($file, $skipIt)) {
            return false;
        }
        return true;
    });
    if(empty($files)) {
        continue;
    }


    // Ckeck if the set of existing files is ok
    if(!JobPostingStep2Normalize::areFilesCorrect($propertiesRequiredToUpdate, $files, $prefixRequired)) {
        //@todo: (email? log?) Inform about the problematic case, maybe some extra bugfix or development is needed, maybe the source changed or other...
        continue;
    }

    // Load data to object
    $JobStep2 = new JobPostingStep2Normalize();
    $filesEmpty = 0;
    $filesTotal = count($files);
    foreach ($files as $fileName) {
        $fileContent = file_get_contents($postDir . DIRECTORY_SEPARATOR . $fileName);
        if(false === $fileContent) {
            // @todo: (email? log?) Inform about content we cannot get...
            $filesEmpty++;
            continue 2;
        }
        if(empty($fileContent)) {
            $filesEmpty++;
        }
        $JobStep2->set($fileName, $fileContent);
    }
    if($filesEmpty === $filesTotal) {
        // @todo: (email? log?) Inform about all files being empty...
        continue;
    }


    // Normalize Job
    $JobStep2->normalize($propertiesNeededToBeNormalizedTo);


    // Save Job to DB
    $success = $JobStep2->saveToDb($PDO, $propertiesRequiredToUpdate, $prefixRequired);
    $completedSuccessfully[] = $success;

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

// ok.
echo 'Success (' . count($completedSuccessfully) . '): ' . implode(', ', $completedSuccessfully);


