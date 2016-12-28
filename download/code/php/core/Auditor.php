<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 23:51
 */

namespace DownloadCore;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class Auditor
{

    protected $loggerSuccessDownload;
    protected $loggerFailureDownload;
    protected $loggerSuccessSave;
    protected $loggerFailureSave;
    protected $loggerDataIntegrity;
    protected $settings;                // All settings (global + related to the project)
    protected $projectSettings;         // Settings related to the project
    protected $requiredProperties;      // Required files to create as the output (=properties of Job class)
    protected $indexDir;                // Project's root directory
    protected $datetime;                // Datetime value (UTC) of object initiation
    protected $downloadsDirectoryPathJobs; // The place where job adds are being downloaded to

    public function __construct($indexDir, array $settings, array $projectSettings, $downloadsDirectoryPathJobs)
    {
        // Datetime
        $this->datetime = new \DateTime('now', new \DateTimeZone('UTC'));

        // SETTINGS
        $this->settings = $settings;
        $this->projectSettings = $projectSettings;

        // Project's root directory
        $this->indexDir = $indexDir;

        // REQUIRED PROPERTIES/FILES
        $this->requiredProperties = $this->getRequiredProperties();

        // LOGGERS
        $this->setLogger('SuccessDownload');
        $this->setLogger('FailureDownload');
        $this->setLogger('SuccessSave');
        $this->setLogger('FailureSave');

        $this->downloadsDirectoryPathJobs = $downloadsDirectoryPathJobs;
        $this->setLoggerDataIntegrity();

    }

    protected function getRequiredProperties()
    {

        if (!isset($this->requiredProperties)) {
            $requiredProperties = array_filter($this->settings['files-to-output'], function ($value) {
                return $value['required'];
            });
            $this->requiredProperties = $requiredProperties;
        }

        return $this->requiredProperties;

    }

    protected function setLogger($name = 'Success')
    {
        // LOGGER.SUCCESS
        // create a log channel
        $this->{"logger" . $name} = new Logger($name);

        // Get path to log file
        $pathToLog = $this->getPathToLogFile($name);

        $this->{"logger" . $name}->pushHandler(new StreamHandler($pathToLog, Logger::INFO));
    }

    protected function getPathToLogFile($name = 'Success', $new = false)
    {
        $pathToLog =
            $this->indexDir
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . $this->projectSettings['dir_downloaded_logs']
            . DIRECTORY_SEPARATOR . $this->datetime->format($this->projectSettings['file_downloaded_logs'])
            . '-' . $name . '.log';

        return $pathToLog;
    }

    private function setLoggerDataIntegrity($name = 'DataIntegrity')
    {
        // LOGGER.SUCCESS
        // create a log channel
        $this->{'logger' . $name} = new Logger($name);

        // Get path to log file
        $pathToLog = $this->getPathToDataIntegrityLogFile($name);

        $streamHandler = new StreamHandler($pathToLog, Logger::INFO);
        $streamHandler->setFormatter(new LineFormatter('%message%' . "\n"));

        $this->{"logger" . $name}->pushHandler($streamHandler);
    }

    protected function getPathToDataIntegrityLogFile($name = 'DataIntegrity')
    {
        $pathToLog =
            $this->downloadsDirectoryPathJobs
            . DIRECTORY_SEPARATOR . $name . '.log';

        return $pathToLog;
    }

    public function registerListAndJobs(array $List, array $saveResults)
    {
        $ListAudited = $this->auditList($List, $saveResults);
        $this->logListAudited($ListAudited, $saveResults);
        $this->logSuccessOrFail($ListAudited);

    }

    protected function auditList(array $List, array $JobsSaved)
    {

        $result = array('success' => [], 'failure' => []);

        foreach ($List as $url) {
            // Job is not saved -> failure
            if (isset($JobsSaved['failure'][$url]) && !empty($JobsSaved['failure'][$url])) {
                $result['failure'][$url] = $JobsSaved['failure'][$url];
                continue;
            }
            // Job is missing in successfully saved list -> failure
            if (!isset($JobsSaved['success'][$url]) || empty($JobsSaved['success'][$url])) {
                $result['failure'][$url] = new JobSaved($url, null, null);
                continue;
            }
            // Job was saved successfully, but saved files do not pass validation -> failure
            if (!$this->isSuccess($JobsSaved['success'][$url]->getJob())) {
                $result['failure'][$url] = $JobsSaved['success'][$url];
                continue;
            }
            $result['success'][$url] = $JobsSaved['success'][$url];
        }

        return $result;

    }

    protected function isSuccess(\DownloadCore\Job $Job)
    {

        $requiredProperties = $this->getRequiredProperties();

        foreach ($requiredProperties as $property => $propertyData) {
            if (!isset($Job->$property) || empty($Job->$property)) {
                return false;
            }
        }

        return true;

    }

    protected function logListAudited(array $ListAudited, array $saveResults)
    {

        if (isset($ListAudited['success']) && !empty($ListAudited['success'])) {
            foreach ($ListAudited['success'] as $url => $JobSaved) {
                $this->loggerSuccessDownload->info($this->getStringForLog($JobSaved));
            }
        }

        if (isset($ListAudited['failure']) && !empty($ListAudited['failure'])) {
            foreach ($ListAudited['failure'] as $url => $JobSaved) {
                $this->loggerFailureDownload->info($this->getStringForLog($JobSaved));
            }
        }
//
//        if(isset($saveResults['success']) && !empty($saveResults['success'])) {
//            /**
//             * @var $JobSaved \DownloadCore\JobSaved
//             */
//            foreach($saveResults['success'] as $JobSaved) {
//                $this->loggerSuccessSave->info($this->getStringForLog($JobSaved));
//            }
//        }
//
//        if(isset($saveResults['failure']) && !empty($saveResults['failure'])) {
//            foreach($saveResults['failure'] as $JobSaved) {
//                $this->loggerFailureSave->info($this->getStringForLog($JobSaved));
//            }
//        }
    }

    protected function getStringForLog(JobSaved $JobSaved)
    {

        return
            $JobSaved->getUrl()
            . " TO: "
            . $JobSaved->getDirToSaveTo();

    }

    protected function logSuccessOrFail($ListAudited)
    {
        if (isset($ListAudited['failure']) && !empty($ListAudited['failure'])) {
            $message = 'The data integrity failure detected. Cannot continue further, because integrity is already lost. URLs failing: ' . print_r($ListAudited['failure'], true);
            throw new \Exception($message);
        }

        $this->logSuccess($ListAudited['success']);

    }

    private function logSuccess(array $successfulUrls, $name = 'DataIntegrity')
    {
        foreach ($successfulUrls as $url => $JobSaved) {
            /**
             * @var JobSaved $JobSaved
             */
            $this->{'logger' . $name}->info($JobSaved->getDirToSaveTo());
        }
    }

    public function doReport()
    {

        $failureDownload = false;
        if (is_file($this->getPathToLogFile('FailureDownload'))) {
            $failureDownload = file_get_contents($this->getPathToLogFile('FailureDownload'));
        }

        $failureSave = false;
        if (is_file($this->getPathToLogFile('FailureSave'))) {
            $failureSave = file_get_contents($this->getPathToLogFile('FailureSave'));
        }


        $subject = "The project " . $this->projectSettings['project_name'] . " |";
        if (!$failureDownload && !$failureSave) {
            $subject = "The project " . $this->projectSettings['project_name'] . " downloaded and saved successfully.";
        }
        if ($failureDownload && $failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had issues downloading and saving information.";
        }
        if ($failureDownload && !$failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had issues downloading the information. Saving went well.";
        }
        if (!$failureDownload && $failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had no problems downloading the information. However, saving was not going well...";
        }

        // Prepare "to"
        if (!isset($this->projectSettings['administrators'])
            || empty($this->projectSettings['administrators'])
            || !is_array($this->projectSettings['administrators'])
        ) {
            return;
        }
        $toArray = [];
        foreach ($this->projectSettings['administrators'] as $administrator) {
            if (!isset($administrator->email) || empty($administrator->email)) {
                continue;
            }
            $toArray[] = $administrator->email;
        }
        $to = implode(", ", $toArray);

        $body = $subject
            . (($failureDownload) ? ("\n\n" . "**** DOWNLOADING FAILED: ****" . "\n\n" . $failureDownload) : '')
            . (($failureSave) ? ("\n\n" . "**** SAVING FAILED: ****" . "\n\n" . $failureSave) : '')
            . "\n\n\n\n" . "You are getting this email because your email address has been pushed to the repository of the project. 
            Remove your email address from the repository if you wish to avoid these emails.";


        return mail($to, $subject, $body);

    }

}