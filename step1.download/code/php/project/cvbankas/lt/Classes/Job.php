<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-09
 * Time: 22:56
 */

namespace Project\Cvbankas\Lt\Classes;

use Core\Job as CoreJob;

class Job extends CoreJob
{


    protected function datetime(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {

        $value = new \DateTime('now',  new \DateTimeZone( 'UTC' ));
        $value = $value->format('Y-m-d H:i:s');

        $this->$fileAndPropertyName = $value;

    }

    protected function project(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {

        $value = isset($projectSettings['project_name']) ? $projectSettings['project_name'] : null;

        $this->$fileAndPropertyName = $value;

    }

    protected function url(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {

        $this->$fileAndPropertyName = $url;

    }

    protected function content_static(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {

        $value = trim($Content->filter('#jobad_cont')->html());

        $this->$fileAndPropertyName = $value;

    }

    protected function content_dynamic(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {

        $value = trim($Content->filter('#job_ad_statistics')->html());

        $this->$fileAndPropertyName = $value;

    }

    protected function id(
        $fileAndPropertyName,
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url,
        array $projectSettings
    ) {
        $valueArray = explode('/', (string)$url);
        $this->$fileAndPropertyName = end($valueArray);
    }

}