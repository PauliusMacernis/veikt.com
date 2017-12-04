<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 23:51
 */

namespace DownloadCore;

use DownloadCore\Logger\Fabric\Integrity;
use DownloadCore\Logger\Fabric\Project;
use DownloadCore\Logger\Interfaces\Logger;


class Auditor
{
    /**
     * @var Logger $loggerSuccessDownload
     */
    protected $loggerSuccessDownload;
    /**
     * @var Logger $loggerFailureDownload
     */
    protected $loggerFailureDownload;
    /**
     * @var Logger $loggerSuccessSave
     */
    protected $loggerSuccessSave;
    /**
     * @var Logger $loggerFailureSave
     */
    protected $loggerFailureSave;
    /**
     * @var Logger $loggerFailureProxy
     */
    protected $loggerFailureProxy;
    /**
     * @var Logger $loggerDataIntegrity
     */
    protected $loggerDataIntegrity;
    protected $settings;                // All settings (global + related to the project)
    protected $projectSettings;         // Settings related to the project
    protected $requiredPropertiesFile;      // Required files to create as the output (=properties of Job class)
    protected $requiredPropertiesData;      // Required data to create as the output (=properties of Job class)
    protected $dirRoot;                 // Root directory (httpdocs)
    protected $dirProject;                // Project's root directory
    protected $datetime;                // Datetime value (UTC) of object initiation
    protected $downloadsDirectoryPathJobs; // The place where job adds are being downloaded to

    public function __construct($dirRoot, $dirProject, array $settings, array $projectSettings, $downloadsDirectoryPathJobs)
    {
        // Datetime
        $this->datetime = new \DateTime('now', new \DateTimeZone('UTC'));

        // SETTINGS
        $this->settings = $settings;
        $this->projectSettings = $projectSettings;

        // httpdocs dir & project's code root directory
        $this->dirRoot = $dirRoot;
        $this->dirProject = $dirProject;

        // REQUIRED PROPERTIES/FILES
        $this->requiredPropertiesFile = $this->getRequiredPropertiesFile();
        $this->requiredPropertiesData = $this->getRequiredPropertiesData();

        // LOGGERS
        $this->setLogger('SuccessDownload');
        $this->setLogger('FailureDownload');
        $this->setLogger('SuccessSave');
        $this->setLogger('FailureSave');
        $this->setLogger('FailureProxy');

        $this->downloadsDirectoryPathJobs = $downloadsDirectoryPathJobs;
        $this->setLoggerDataIntegrity();

    }

    protected function getRequiredPropertiesFile()
    {

        if (!isset($this->requiredPropertiesFile)) {
            $this->setRequiredProperties('required-file', 'requiredPropertiesFile');
        }

        return $this->requiredPropertiesFile;

    }

    protected function setRequiredProperties($keyInSettings, $propertyName)
    {

        $requiredProperties = array_filter($this->settings['files-to-output'],
            function ($value) use ($keyInSettings) {
                return $value[$keyInSettings];
            }
        );

        $this->$propertyName = $requiredProperties;

    }

    protected function getRequiredPropertiesData()
    {

        if (!isset($this->requiredPropertiesData)) {
            $this->setRequiredProperties('required-data', 'requiredPropertiesData');
        }

        return $this->requiredPropertiesData;

    }

    protected function setLogger($name = 'Success')
    {
        // create a log channel
        $this->{"logger" . $name} = Project::create(
            $name,
            $this->dirRoot,
            $this->projectSettings['dir_downloaded_logs'],
            $this->datetime->format($this->projectSettings['file_downloaded_logs'])
        );
    }

    private function setLoggerDataIntegrity($name = 'DataIntegrity')
    {
        $this->{"logger" . $name} = Integrity::create(
            $name,
            $this->dirRoot,
            $this->dirProject,
            $this->getPathToDataIntegrityLogFile()
        );
    }

    protected function getPathToDataIntegrityLogFile()
    {
        $pathToLog =
            $this->downloadsDirectoryPathJobs
            . DIRECTORY_SEPARATOR
            . $this->settings['markers']['content']['file-name'];

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

        // Check for required files to set
        $requiredPropertiesFile = $this->getRequiredPropertiesFile();
        foreach ($requiredPropertiesFile as $property => $propertyData) {
            if (!isset($Job->$property)) {
                return false;
            }
        }

        // Check for required non-empty content
        $requiredPropertiesData = $this->getRequiredPropertiesData();
        foreach ($requiredPropertiesData as $property => $propertyData) {
            if (empty($Job->$property)) {
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
            $message = 'The data integrity failure detected. 
            Cannot continue further, because integrity is already lost. 
            URLs failing: ' . print_r($ListAudited['failure'], true);
            throw new ErrorHandler($message);
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
        // Prepare "to"
        $to = $this->getAdminEmails();
        if (empty($to)) {
            return;
        }

        $failureDownload = $this->getFailureDownloadContent();
        $failureSave = $this->getFailureSaveContent();

        $subject = $this->createSubjectForDoReport($failureDownload, $failureSave);
        $body = $this->createBodyForDoReport($failureDownload, $failureSave);

        $Mail = new Mail();
        return $Mail->createAndSendMessage($body, $subject, $to);

    }

    /**
     * @return array
     */
    protected function getAdminEmails()
    {

        if (!isset($this->projectSettings['administrators'])
            || empty($this->projectSettings['administrators'])
            || !is_array($this->projectSettings['administrators'])
        ) {
            return [];
        }

        $to = [];
        foreach ($this->projectSettings['administrators'] as $administrator) {
            if (!isset($administrator['email']) || empty($administrator['email'])) {
                continue;
            }
            $to[$administrator['email']] = $administrator['name'];
        }
        return $to;
    }

    /**
     * @return string
     */
    protected function getFailureDownloadContent()
    {
        $failureDownload = '';
        if (is_file($this->loggerFailureDownload->getPathToLogFile())) {
            $failureDownload = file_get_contents(
                $this->loggerFailureDownload->getPathToLogFile()
            );
        }
        return (string)$failureDownload;
    }

    /**
     * @return string
     */
    protected function getFailureSaveContent()
    {
        $failureSave = '';
        if (is_file($this->loggerFailureSave->getPathToLogFile())) {
            $failureSave = file_get_contents($this->loggerFailureSave->getPathToLogFile());
        }
        return (string)$failureSave;
    }

    /**
     * @param $failureDownload
     * @param $failureSave
     * @return string
     */
    protected function createSubjectForDoReport($failureDownload, $failureSave)
    {
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
        return $subject;
    }

    /**
     * @param $subject
     * @param $failureDownload
     * @param $failureSave
     * @return string
     */
    protected function createBodyForDoReport($failureDownload, $failureSave)
    {
        $body =
            (($failureDownload) ? ("\n\n" . "**** DOWNLOADING FAILED: ****" . "\n\n" . $failureDownload) : '')
            . (($failureSave) ? ("\n\n" . "**** SAVING FAILED: ****" . "\n\n" . $failureSave) : '')
            . "\n\n\n\n"
            . "You are getting this email because your email address has been pushed to the repository of the project.\n"
            . "Remove your email address from the repository if you wish to avoid these emails.";
        return $body;
    }

}