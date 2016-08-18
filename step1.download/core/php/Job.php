<?php

class Job extends JobPostingStep1Download
{

    public function __construct($url, ContentManager $ContentManager)
    {
        $this->set('url', trim($url));
        $idArray = explode('/', (string)$this->get('url'));
        $this->set('idInSourceSystem', end($idArray));

        $this->getUrlContent($ContentManager);
        $this->saveToFile($ContentManager);

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

        $ContentManager->saveToFile($this->get('idInSourceSystem'), get_object_vars($this));

    }

}