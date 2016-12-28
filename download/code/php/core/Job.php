<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-09
 * Time: 22:56
 */

namespace DownloadCore;


class Job
{

    public function __construct(
        \Symfony\Component\DomCrawler\Crawler $Content,
        array $filesRequiredToOutput,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        if (empty($filesRequiredToOutput)) {
            return $this;
        }

        foreach ($filesRequiredToOutput as $fileName => $fileData) {
            //$setMethodName = 'set' . ucfirst(strtolower($fileName));

            if (method_exists($this, $fileName)) {
                $this->$fileName($fileName, $Content, $url, $projectSettings, $uniqueBrowserId);
            } else {
                $this->{$fileName} = null;
            }

        }

        return $this;

    }

    /**
     * Gets property value.
     *  Property name is the same as file name from
     *  settings.js ["files-to-output"]
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            default:
                return $this->$name;
        }
    }

    /**
     * This method is used as default method to set default values
     *  for properties that have no individual set{FileName} method.
     * Simply saying, this is the method we use for:
     *  "The feature of extracting content for $name file does not exist."
     *
     * For example, if the new file requirement is being added globally then such
     *  file will be created immediately by using this method. However,
     *  no content will be passed to the file, because
     *  the content extraction logic is not ready while not added by a developer.
     *
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->$name = isset($value) ? $value : null;
    }

    /*********************************************
     *
     * METHODS TO GET DATA FOR SAVING TO FILES
     *
     * *******************************************
     */

    /**
     * Gets unique id to identify the process of download.
     * All items downloaded at the same try
     *  will have the same unique id associated with.
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function browser_id(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {
        // @TODO: create Browser id...
        $this->$fileAndPropertyName = $uniqueBrowserId;
    }

    /**
     * Gets datetime value. When the job posting was downloaded?
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function datetime(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        $value = new \DateTime('now', new \DateTimeZone('UTC'));
        $value = $value->format('Y-m-d H:i:s');

        $this->$fileAndPropertyName = $value;

    }

    /**
     * Gets project's name
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function project(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        $value = isset($projectSettings['project_name']) ? $projectSettings['project_name'] : null;

        $this->$fileAndPropertyName = $value;

    }

    /**
     * Gets URL or the job add
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function url(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        $this->$fileAndPropertyName = $url;

    }


    /**
     * Unique identifier identifying job posting in the source system
     * Defaults to URL
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function id(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {
        $this->$fileAndPropertyName = $url;
    }


    /**
     * Gets content_static value. This value is saved to file later.
     * This is the place for job content that DOES change.
     * For example, statistics of page views, unique visitors, applicants, etc.
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     * @param string $uniqueBrowserId
     */
    protected function content_dynamic(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        // Empty value by default
        $this->$fileAndPropertyName = '';

    }


}