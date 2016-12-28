<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-12-26
 * Time: 23:18
 */

namespace DownloadCore;


class JobSaved
{

    protected $url;
    protected $dirToSaveTo;
    protected $Job;


    public function __construct($url, $dirToSaveTo, Job $Job)
    {
        $this->setUrl($url);
        $this->setDirToSaveTo($dirToSaveTo);
        $this->setJob($Job);
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->Job;
    }

    /**
     * @param mixed $Job
     */
    public function setJob(Job $Job)
    {
        $this->Job = $Job;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getDirToSaveTo()
    {
        return $this->dirToSaveTo;
    }

    /**
     * @param mixed $dirToSaveTo
     */
    public function setDirToSaveTo($dirToSaveTo)
    {
        $this->dirToSaveTo = $dirToSaveTo;
    }

}