<?php

class PageList
{

    // const firstPageListUrl = 'http://www.cvbankas.lt/darbo-skelbimai?page=1';
    const firstPageListUrl = 'http://www.cvbankas.lt/?page=1';

    private $contentManager = null;
    private $jobs = array();
    private $content = null;
    private $url = null;


    public function __construct($url = null, ContentManager $contentManager)
    {

        if (!$url) {
            return;
        }

        $this->set('url', $url);
        $this->set('contentManager', $contentManager);
        $this->set('content', $this->getUrlContent($url));
        $this->set('jobs', $this->extractJobs($this->get('content')));

    }

    public function set($property, $value)
    {
        $this->$property = $value;
    }

    public function get($property)
    {
        return $this->$property;
    }

    private function getUrlContent($url)
    {
        $content = $this->get('contentManager')->getUrlContent($url);
        return $content;
    }

    private function extractJobs($EntirePage)
    {

        $jobs = array(); // List of Jobs

        $EntirePageDataContainer = $EntirePage->getElementById('job_ad_list');
        $EntirePageArticles = $EntirePageDataContainer->getElementsByTagName('article');

        foreach ($EntirePageArticles as $Article) {
            $Links = $Article->getElementsByTagName('a');

            $Job = new Job(trim($Links->item(0)->getAttribute('href')), $this->get('contentManager'));

            $jobs[$Job->get('url')] = $Job;
        }

        return $jobs;

    }

    public function getNextPageListUrl()
    {

        $finder = new DomXPath($this->get('content'));
        $classname = "pages_ul_inner";

        $PageNumbersContent = $finder->query("//ul[contains(@class, '$classname')]");

        $PageNumbersUl = $PageNumbersContent->item(0);

        $PageNumberLis = $PageNumbersUl->getElementsByTagName('li');


        $nextPageListIsNeeded = false;
        foreach ($PageNumberLis as $li) {

            if (!is_numeric(str_replace($this->get('contentManager')->__get('black_list_chars_page_numbers'), '', $li->nodeValue))) { // drop possible extra symbols off & check if content is a number
                continue;
            }

            if ($nextPageListIsNeeded) { // we are looking at the next one at the moment!
                foreach ($li->childNodes as $childNode) {
                    if (!($childNode instanceof DOMElement) || ($childNode->tagName != 'a')) {
                        continue;
                    }
                    foreach ($childNode->attributes as $attribute) {
                        if ($attribute->name == 'href') {
                            return trim($attribute->value); // the url of the next page is found
                        }
                    }
                }

            }

            foreach ($li->childNodes as $childNode) {
                if (!($childNode instanceof DOMElement) || ($childNode->tagName != 'a')) {
                    continue;
                }
                foreach ($childNode->attributes as $attribute) {
                    if ($attribute->name == 'class' && $attribute->value == 'current') {
                        $nextPageListIsNeeded = true;
                    }
                }
            }

        }

        return null; // the url of the next page is not found

    }

}