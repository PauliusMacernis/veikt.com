<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 23:51
 */

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Auditor
{

    protected $loggerSuccessDownload;
    protected $loggerFailureDownload;
    protected $loggerSuccessSave;
    protected $loggerFailureSave;
    protected $settings;
    protected $requiredProperties;

    public function __construct(array $projectSettings, $indexDir, array $settings)
    {
        // LOGGERS
        $this->setLogger($projectSettings, $indexDir, 'SuccessDownload');
        $this->setLogger($projectSettings, $indexDir, 'FailureDownload');
        $this->setLogger($projectSettings, $indexDir, 'SuccessSave');
        $this->setLogger($projectSettings, $indexDir, 'FailureSave');

        // SETTINGS
        $this->settings = $settings;

        // REQUIRED PROPERTIES/FILES
        $this->requiredProperties = $this->getRequiredProperties();

    }

    public function registerListAndJobs(array $List, array $Jobs, array $saveResults) {
        $ListAudited = $this->auditList($List, $Jobs);

        $this->logListAudited($ListAudited, $saveResults);

    }

    public function doReport() {
        // @TODO:
        //  - Generate file with list of failed to download urls.
        //  - Make repetitive downloading possible by passing that file with fails to somewhere within the system...
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
            $requiredProperties = array_filter($this->settings['files-required-to-output'], function($value) {
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

    protected function setLogger(array $projectSettings, $indexDir, $name = 'Success') {
        // LOGGER.SUCCESS
        // create a log channel
        $this->{"logger" . $name} = new Logger($name);

        // Get path to log file
        $pathToLog =
            $indexDir . DIRECTORY_SEPARATOR
            . $projectSettings['dir_downloaded_logs'] . DIRECTORY_SEPARATOR
            . date($projectSettings['file_downloaded_logs']) . '-' . $name . '.log';

        $this->{"logger" . $name}->pushHandler(new StreamHandler($pathToLog, Logger::INFO));
    }

}