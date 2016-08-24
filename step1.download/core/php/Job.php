<?php

class Job extends JobPostingStep1Download
{

    /**
     * Job constructor.
     * @param string $url
     * @param ContentManager $ContentManager
     * @param string $project
     */
    public function __construct($url, ContentManager $ContentManager, $project)
    {
        $this->set('project', $project);

        $this->set('url', trim($url));
        $idArray = explode('/', (string)$this->get('url'));
        $this->set('id', end($idArray));
        $this->set('downloaded_time', date("Y-m-d H:i:s"));

        // Also sets $this->html and $this->statistics properties
        $this->getUrlContent($ContentManager);

        // Save object ($this) vars to file. Each property as separate file.
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

        $ContentManager->saveToFile($this->get('id'), get_object_vars($this));

    }

}