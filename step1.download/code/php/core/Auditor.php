<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 23:51
 */

namespace Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class Auditor
{

    protected $loggerSuccessDownload;
    protected $loggerFailureDownload;
    protected $loggerSuccessSave;
    protected $loggerFailureSave;
    protected $settings;                // All settings (global + related to the project)
    protected $projectSettings;         // Settings related to the project
    protected $requiredProperties;      // Required files to create as the output (=properties of Job class)
    protected $indexDir;                // Project's root directory
    protected $datetime;                // Datetime value (UTC) of object initiation

    public function __construct($indexDir, array $settings, array $projectSettings)
    {
        // Datetime
        $this->datetime = new \DateTime('now',  new \DateTimeZone( 'UTC' ));

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

    }

    public function registerListAndJobs(array $List, array $Jobs, array $saveResults) {
        $ListAudited = $this->auditList($List, $Jobs);

        $this->logListAudited($ListAudited, $saveResults);

    }

    public function doReport() {

        $failureDownload = false;
        if(is_file($this->getPathToLogFile('FailureDownload'))) {
            $failureDownload = file_get_contents($this->getPathToLogFile('FailureDownload'));
        }

        $failureSave = false;
        if(is_file($this->getPathToLogFile('FailureSave'))) {
            $failureSave = file_get_contents($this->getPathToLogFile('FailureSave'));
        }


        $subject = "The project " . $this->projectSettings['project_name'] . " |";
        if(!$failureDownload && !$failureSave) {
            $subject = "The project " . $this->projectSettings['project_name'] . " downloaded and saved successfully.";
        }
        if($failureDownload && $failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had issues downloading and saving information.";
        }
        if($failureDownload && !$failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had issues downloading the information. Saving went well.";
        }
        if(!$failureDownload && $failureSave) {
            $subject = "UPS... The project " . $this->projectSettings['project_name'] . " had no problems downloading the information. However, saving was not going well...";
        }

        // Prepare "to"
        if(!isset($this->projectSettings['administrators'])
            || empty($this->projectSettings['administrators'])
            || !is_array($this->projectSettings['administrators'])
        ) {
            return;
        }
        $toArray = [];
        foreach($this->projectSettings['administrators'] as $administrator) {
            if(!isset($administrator->email) || empty($administrator->email)) {
                continue;
            }
            $toArray[] = $administrator->email;
        }
        $to = implode(", ", $toArray);

        $body = $subject
            . (($failureDownload)   ? ("\n\n" . "**** DOWNLOADING FAILED: ****" . "\n\n" . $failureDownload)   : '')
            . (($failureSave)       ? ("\n\n" . "**** SAVING FAILED: ****" . "\n\n" . $failureSave)       : '')
            . "\n\n\n\n" . "You are getting this email because your email address has been pushed to the repository of the project. 
            Remove your email address from the repository if you wish to avoid these emails.";


        return mail($to, $subject, $body);

    }

    protected function logListAudited(array $ListAudited, array $saveResults) {

        if(isset($ListAudited['success']) && !empty($ListAudited['success'])) {
            foreach($ListAudited['success'] as $url) {
                $this->loggerSuccessDownload->info($url);
            }
        }

        if(isset($ListAudited['failure']) && !empty($ListAudited['failure'])) {
            foreach($ListAudited['failure'] as $url) {
                $this->loggerFailureDownload->info($url);
            }
        }

        if(isset($saveResults['success']) && !empty($saveResults['success'])) {
            foreach($saveResults['success'] as $url) {
                $this->loggerSuccessSave->info($url);
            }
        }

        if(isset($saveResults['failure']) && !empty($saveResults['failure'])) {
            foreach($saveResults['failure'] as $url) {
                $this->loggerFailureSave->info($url);
            }
        }
    }

    protected function getRequiredProperties() {

        if(!isset($this->requiredProperties)) {
            $requiredProperties = array_filter($this->settings['files-to-output'], function($value) {
                return $value['required'];
            });
            $this->requiredProperties = $requiredProperties;
        }

        return $this->requiredProperties;

    }

    protected function auditList(array $List, array $Jobs) {

        $result = array('success' => [], 'failure' => []);

        foreach($List as $url) {
            if(!isset($Jobs[$url]) || empty($Jobs[$url])) {
                $result['failure'][$url] = $url;
                continue;
            }
            if(!$this->isSuccess($Jobs[$url])) {
                $result['failure'][$url] = $url;
                continue;
            }
            $result['success'][$url] = $url;
        }

        return $result;

    }

    protected function isSuccess(\Core\Job $Job) {

        $requiredProperties = $this->getRequiredProperties();

        foreach($requiredProperties as $property => $propertyData) {
            if(!isset($Job->$property) || empty($Job->$property)) {
                return false;
            }
        }

        return true;

    }

    protected function setLogger($name = 'Success') {
        // LOGGER.SUCCESS
        // create a log channel
        $this->{"logger" . $name} = new Logger($name);

        // Get path to log file
        $pathToLog = $this->getPathToLogFile($name);

        $this->{"logger" . $name}->pushHandler(new StreamHandler($pathToLog, Logger::INFO));
    }

    protected function getPathToLogFile($name = 'Success', $new = false) {
        $pathToLog =
            $this->indexDir . DIRECTORY_SEPARATOR
            . $this->projectSettings['dir_downloaded_logs'] . DIRECTORY_SEPARATOR
            . $this->datetime->format($this->projectSettings['file_downloaded_logs'])
            . '-' . $name . '.log';

        return $pathToLog;
    }

}