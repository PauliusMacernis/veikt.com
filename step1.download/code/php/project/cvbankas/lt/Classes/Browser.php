<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 21:33
 */

namespace Project\Cvbankas\Lt\Classes;

use Core\Browser as CoreBrowser;


class Browser extends CoreBrowser
{

    const HOMEPAGE_URL = 'http://www.cvbankas.lt/?page=1';


    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $listContent;


    public function getFirstListOfJobLinks() {
        $this->listContent = $this->doRepeatableAction('getContentOfUrl', self::HOMEPAGE_URL);
        return (array)$this->extractJobLinks();
    }


    /**
     * Extracts job links and returns as array
     *
     * @return array
     */
    protected function extractJobLinks() {

        $linksToJobs = array();

        $list = $this->listContent->filter('#job_ad_list')->children("article");

        foreach ($list as $item) {

            $aElements  = $item->getElementsByTagName('a');
            if(!$aElements) {
                continue;
            }

            $firstItem  = $aElements->item(0);
            if(!$firstItem) {
                continue;
            }

            $href = $firstItem->getAttribute('href');
            if(!$href) {
                continue;
            }

            $linksToJobs[$href] = $href;

        }

        return $linksToJobs;

    }


    protected function getNextPageUrlOfListOfJobLinks() {

        $classname = "pages_ul_inner";
        $PageNumbersContent = $this->listContent->filterXPath("//ul[contains(@class, '$classname')]");

        $PageNumbersUl = $PageNumbersContent->first();
        $PageNumberLis = $PageNumbersUl->filter('li');

        $nextPageListIsNeeded = false;
        foreach ($PageNumberLis as $li) {

            if (!is_numeric(str_replace($this->blackListCharsForPageNumbers, '', trim($li->nodeValue)))) { // drop possible extra symbols off & check if content is a number
                continue;
            }

            if ($nextPageListIsNeeded) { // we are looking at the next one at the moment!

                foreach ($li->childNodes as $childNode) {

                    if (!($childNode instanceof \DOMElement) || ($childNode->tagName != 'a')) {
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

                if (!($childNode instanceof \DOMElement) || ($childNode->tagName != 'a')) {
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