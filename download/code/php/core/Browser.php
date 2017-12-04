<?php

namespace DownloadCore;

use DownloadCore\BrowserClient\Fabric\BrowserClient;
use DownloadCore\Logger\Fabric\Connection;
use DownloadCore\Repeatable\ContentValidator;
use DownloadCore\Repeatable\Repeatable;
use DownloadProject\Cvbankas\Lt\Classes\Job;


/**
 * @TODO: Rename to "Worker" or something close to that?
 * The main reason to rename - the "uniqueBrowserId" for every run.
 * ...which is actually "uniqueWorkerId" or "uniqueCrawlingId" or...
 *
 * Class Browser
 * @package DownloadCore
 */
class Browser
{
    use Repeatable;
    use ContentValidator;

    const HOMEPAGE_URL = null;


    // CURLOPT_LOW_SPEED_LIMIT = 1
    // CURLOPT_LOW_SPEED_TIME = 3600
    // CURLOPT_TIMEOUT = 3600
    // etc?

    protected $id;                  // Unique id of the Browser object.
    protected $projectSettings;
    protected $dirRoot;             // httpdocs
    protected $dirProject;          // project code root
    protected $settings;
    protected $jobProperties;
    protected $blackListCharsForPageNumbers = array('-', '/', '|', ' ', "\n", "\r", '.');
    protected $jobsCounter;
    protected $robotsTxtContent;    // https://en.wikipedia.org/wiki/Robots_exclusion_standard
    protected $proxySettingsGlobalAndProject;   // All settings that can be applied

    protected $clientSuccessful;    // a client for the browser that is proven to be successful (proxy IP, port, etc.)

    //protected $proxySettingsApplied;            // All settings that were actually applied

    public function __construct($dirRoot, $dirProject, $settings, $projectSettings, $proxySettingsGlobalAndProject, $careAboutRobotsDotTxt = true)
    {

        // UNIQUE AUDITOR ID
        $reflect = new \ReflectionClass($this);
        $this->id = uniqid($reflect->getShortName(), true);

        // JOB COUNTER set to 0
        // It means: No jobs found by Browser at the time of creating Browser object
        $this->jobsCounter = 0;

        // PROJECT SETTINGS
        $this->projectSettings = $projectSettings;

        // BASE DIRS (httpdocs, project code root)
        $this->dirRoot = $dirRoot;
        $this->dirProject = $dirProject;

        // SETTINGS
        $this->settings = $settings;

        // PROXY SETTINGS (merged global and project-specific according to global and project settings)
        $this->proxySettingsGlobalAndProject = $proxySettingsGlobalAndProject;

        //$this->setClientRandom();

        // REQUIRED PROPERTIES/FILES
        $this->jobProperties = $this->getJobProperties();

        // Should we take care about robots.txt file?
        if ($careAboutRobotsDotTxt) {
            $this->robotsTxtContent = $this->getRobotsTxtContent();
        } else {
            $this->robotsTxtContent = null;
        }

        // Datetime (UTC)
        $this->browserUtcDateTimeBegin = new \DateTime('now', new \DateTimeZone('UTC'));

    }

    /**
     * Get client with settings set for the browser to start working on behalf of
     * @return \DownloadCore\BrowserClient\BrowserClient
     */
//    protected function setClientRandom()
//    {
//        $this->clientRandom = BrowserClient::create($this->proxySettingsGlobalAndProject);
//    }

    protected function getJobProperties()
    {

        if (!isset($this->jobProperties)) {
            $this->jobProperties = (array)$this->settings['files-to-output'];
        }

        return $this->jobProperties;

    }

    protected function getRobotsTxtContent()
    {
        if (isset($this->robotsTxtContent)) {
            return $this->robotsTxtContent;
        }

        $url = $this->getRobotsTxtUrl();
        if (!$url) {
            $this->robotsTxtContent = '';
            return '';
        }

        $Content = $this->doRepeatableAction('getContentOfRemoteRobotsTxt', $url);
        if (!$Content) {
            $this->robotsTxtContent = '';
            return '';
        }

        $this->robotsTxtContent = $Content;
        return $this->robotsTxtContent;

    }

    protected function getRobotsTxtUrl()
    {

        $urlParsed = parse_url($this->projectSettings['url']);
        $url = ''
            . (isset($urlParsed['scheme']) ? ($urlParsed['scheme'] . '://') : '')
            . (
            (isset($urlParsed['user']) && isset($urlParsed['user']))
                ? ($urlParsed['pass'] . ':' . $urlParsed['pass'] . '@')
                : ''
            )
            . (isset($urlParsed['host']) ? $urlParsed['host'] : '')
            . (isset($urlParsed['port']) ? (':' . $urlParsed['port']) : '')
            . '/'
            . 'robots.txt';

        return $url;

    }

    public function markQueueBegin()
    {
        $this->createQueueMarkerFile($this->settings['markers']['begin']['file-name']);
    }

    /**
     * @param $markerFileName
     */
    protected function createQueueMarkerFile($markerFileName)
    {
        $jobsPath = $this->getDownloadsDirectoryPathJobs();
        $this->createDirectoryIfNotExist($jobsPath);
        $this->createFileIfNotExists(
            $jobsPath . DIRECTORY_SEPARATOR . $markerFileName,
            $this->getMarkerContent()
        );
    }

    /**
     * @param \DateTime $DateTime
     * @return string
     */
    public function getDownloadsDirectoryPathJobs()
    {
        $dir = $this->dirRoot
            . DIRECTORY_SEPARATOR . $this->projectSettings['dir_downloaded_posts']
            . DIRECTORY_SEPARATOR . $this->browserUtcDateTimeBegin->format('Y-m-d')
            . '--' . $this->getId();

        return $dir;
    }

    protected function getId()
    {
        return $this->id;
    }

    /**
     * @param $dir
     * @param int $cmod
     * @return bool
     */
    protected function createDirectoryIfNotExist($dir, $cmod = 0775)
    {
        if (!is_dir($dir)) {
            return mkdir($dir, $cmod, true);
        }
        return true;
    }

    private function createFileIfNotExists($fileFullPath, $fileContent)
    {
        return file_put_contents($fileFullPath, $fileContent);
    }

    protected function getMarkerContent()
    {

        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return
            $this->getId()
            . "\n"
            . $currentDateTime->format('Y-m-d H:i:s');
    }

    public function markQueueEnd()
    {
        $this->createQueueMarkerFile($this->settings['markers']['end']['file-name']);
    }

    public function getJobsFromTheList($List)
    {

        if (!$List) {
            return array();
        }

        $jobs = array();

        foreach ($List as $url) {
            $Content = $this->doRepeatableAction('getContentOfUrl', $url, ['testResultOfGetContentOfUrl']);
            $jobs[$url] = $this->extractJob(
                $Content,
                $url
            );
        }

        return $jobs;

    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param array $filesRequiredToOutput
     * @param $url
     * @return
     */
    public function extractJob(
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url
    )
    {

        if (empty($this->jobProperties)) {
            return null;
        }

        return new Job($Content, $this->jobProperties, $url, $this->projectSettings, $this->getId());

    }

    public function saveJobsToFiles(array $Jobs)
    {

        $result = array('success' => [], 'failure' => []);

        foreach ($Jobs as $url => $Job) {
            // Counter
            $counter = ++$this->jobsCounter;

            $dir = $this->createJobDirectoryIfNotExists($counter);

            $JobSaved = new JobSaved($url, $dir, $Job);

            if (!$dir) {
                $result['failure'][$url] = $JobSaved;
                continue;
            }

            $success = $this->saveJobToFile($Job, $dir);
            if (!$success) {
                $result['failure'][$url] = $JobSaved;
                continue;
            }

            $result['success'][$url] = $JobSaved;

        }

        return $result;

    }

    protected function createJobDirectoryIfNotExists($jobCounter)
    {

        if (empty($this->projectSettings)) {
            return false;
        }

        if (!isset($this->projectSettings['dir_downloaded_posts']) || empty($this->projectSettings['dir_downloaded_posts'])) {
            return false;
        }

        if (!isset($jobCounter) || empty($jobCounter)) {
            return false;
        }

        $dir = $this->getDownloadsDirectoryPathJob($jobCounter);
        $success = $this->createDirectoryIfNotExist($dir);

        if (!$success) {
            return false;
        }

        return $dir;

    }

    /**
     * @param $jobCounter
     * @return string
     */
    protected function getDownloadsDirectoryPathJob($jobCounter)
    {
        $dir = $this->getDownloadsDirectoryPathJobs()
            . DIRECTORY_SEPARATOR . $jobCounter
            . '--' . $this->browserUtcDateTimeBegin->format('Y-m-d--H-i-s')
            . '--' . uniqid();
        return $dir;
    }

    protected function saveJobToFile(\DownloadCore\Job $Job, $dirToSaveTo)
    {

        $success = true;

        foreach ($Job as $property => $value) {

            $file = $dirToSaveTo . DIRECTORY_SEPARATOR . $property;

            if (!$this->saveValueToFile($file, $value)) {
                $success = false;
            }
        }

        return $success;

    }

    protected function saveValueToFile($file, $content)
    {

        $fp = fopen($file, 'w');
        $success = fwrite($fp, $content);
        fclose($fp);

        if ($success === false) {
            return false;
        }

        return true;

    }

    public function getNextListOfJobLinks()
    {

        $nextPageUrl = $this->getNextPageUrlOfListOfJobLinks();
        $this->listContent = $this->doRepeatableAction('getContentOfUrl', $nextPageUrl, ['testResultOfGetContentOfUrl']);
        return (array)$this->extractJobLinks();

    }

    /**
     * To be implemented in child class
     *
     * @return null
     */
    protected function getNextPageUrlOfListOfJobLinks()
    {
        return null;
    }

    /**
     * Returns content of URL
     *
     * @param string $url Any valid URL
     * @param array $callbacksToTestResultOn
     * @param bool $listenRobotsDotTxt
     * @param string $actionType "GET", "POST", any other...
     * @return null|\Symfony\Component\DomCrawler\Crawler
     * @throws ErrorHandler
     */
    protected function getContentOfUrl($url, $callbacksToTestResultOn = [], $listenRobotsDotTxt = true, $actionType = 'GET', $useClientSuccessful = true)
    {
        //$url = 'https://www.whatismyip.net/';
        //var_dump($url);
        if (!$url) {
            return null;
        }

        // Check if url is allowed
        if ($listenRobotsDotTxt && !$this->urlIsAllowedByRobotsDotTxt($url)) {
            //var_dump('URL ' . $url . ' is not allowed by robots.txt');
            return null;
        }

        if ($useClientSuccessful && isset($this->clientSuccessful)) {
            $client = $this->getClientSuccessful();
            //var_dump('Client new success: ' . print_r($client->getClient()->getConfig(), true));
        } else {
            $client = $this->getClientRandom();
            //var_dump('Client new random: ' . print_r($client->getClient()->getConfig(), true));
        }

        $this->setRepeatableBagClientConfig($client->getClient()->getConfig());

        try {
            $result = $client->request($actionType, $url);
            //var_dump('Good connection.');
        } catch (\Exception $e) {
            //var_dump('Bad connection.');
            $this->logBadConnection($e);
            return null;
        }

        $response = $client->getResponse();
        if ($response->getStatus() >= 400) {
            //var_dump('Bad status: ' . $response->getStatus());
            return null;
        }

        if (!$result) {
            //var_dump('No result...');
            return null;
        }

        foreach ($callbacksToTestResultOn as $callbackToTestOn) {
            $resultFromCallback = $this->$callbackToTestOn($result);
            if (false === $resultFromCallback) {
                //var_dump('Failed to validate result..');
                return null; // treat it as no success when downloading
            }
        }

        $this->clientSuccessful = $client;

        return $result;

    }

    /**
     * @param string $url
     * @return bool
     * @throws ErrorHandler
     */
    protected function urlIsAllowedByRobotsDotTxt($url)
    {
        if (!isset($this->robotsTxtContent)) {
            throw new ErrorHandler("robotsTxtContent is not set when expected to be set.");
        }
        $parser = new \RobotsTxtParser($this->robotsTxtContent);
        // $parser->setUserAgent('VeiktDotComBot'); // ???
        if ($parser->isDisallowed($url)) {
            return false;
        }

        return true;

    }

    /**
     * @return \DownloadCore\BrowserClient\BrowserClient
     */
    public function getClientSuccessful()
    {
        return $this->clientSuccessful;
    }

    /**
     * @return mixed
     */
    public function getClientRandom()
    {
        //$this->setClientRandom();
        return BrowserClient::create($this->proxySettingsGlobalAndProject);
    }

    public function logBadConnection(\Exception $e)
    {
        $logger = Connection::create('proxy', $this->dirRoot, $this->dirProject, 'robots');
        $logger->addInfo($e->getMessage());
    }

    /**
     * Gets content of remote rebots.txt file
     * @todo: merge with getContentUrl
     *
     * @param $url              For example: "http://www.example.org/robots.txt
     * @return null|string
     */
    protected function getContentOfRemoteRobotsTxt($url, $useClientSuccessful = true)
    {
        if (!$url) {
            return null;
        }

        if ($useClientSuccessful && isset($this->clientSuccessful)) {
            $client = $this->getClientSuccessful();
        } else {
            $client = $this->getClientRandom();
        }

        try {
            $client->request('GET', $url);
        } catch (\Exception $e) {     // \GuzzleHttp\Exception\ConnectException
            $this->logBadConnection($e);
            return null;
        }

        /**
         * @var $response \Symfony\Component\BrowserKit\Response
         */
        $response = $client->getResponse();
        if ($response->getStatus() >= 400) {
            return null;
        }

        $this->clientSuccessful = $client;

        return $response->getContent();

    }

}