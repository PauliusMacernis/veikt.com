<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-09
 * Time: 22:56
 */

namespace DownloadProject\Cvbankas\Lt\Classes;

use DownloadCore\Job as CoreJob;

class Job extends CoreJob
{

    /**
     * Gets content_static value. This value is saved to file later.
     * This is the place for job content that DOES NOT change.
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     */
    protected function content_static(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        $value = trim($Content->filter('#jobad_cont')->html());

        $this->$fileAndPropertyName = $value;

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
     */
    protected function content_dynamic(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    )
    {

        $value = trim($Content->filter('#job_ad_statistics')->html());

        $this->$fileAndPropertyName = $value;

    }

    /**
     * Unique identifier identifying job posting in the source system
     *
     * @param $fileAndPropertyName
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param $url
     * @param array $projectSettings
     */
    /* // Comment this for a while as the parent method does the job quite well.
    protected function id(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings,
        $uniqueBrowserId
    ) {
        $valueArray = explode('/', (string)$url);
        $this->$fileAndPropertyName = end($valueArray);
    }
    */

}