<?php

class Job extends JobPosting
{

    public $id = null;
    public $html = null;
    public $statistics = null;


    public function __construct($url, ContentManager $ContentManager)
    {
        $this->set('url', trim($url));
        $idArray = explode('/', (string)$this->get('url'));
        $this->set('id', end($idArray));

        $this->getUrlContent($ContentManager);
        $this->saveToFile($ContentManager);

    }

    public function set($property, $value)
    {
        $this->$property = $value;
    }

    public function get($property)
    {
        return $this->$property;
    }

    protected function getUrlContent(ContentManager $ContentManager)
    {
        $EntirePage = $ContentManager->getURLContent($this->url);

        $UrlActualContent = $EntirePage->getElementById('jobad_cont');
        $this->html = trim($ContentManager->getInnerHtml($UrlActualContent));

        $UrlStatistics = $EntirePage->getElementById('job_ad_statistics');
        $this->statistics = trim($ContentManager->getInnerHtml($UrlStatistics));

    }

    protected function saveToFile(ContentManager $ContentManager)
    {

        $ContentManager->saveToFile($this->get('id'), get_object_vars($this));

    }

}