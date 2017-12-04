<?php

namespace DownloadCore\Repeatable;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Methods to validate the content downloaded.
 * Most of the time these methods will be used to check if the downloaded content is ok (not blank), if fully loaded.
 * Returns true (the content is valid) or false (the content is invalid)
 *
 * Trait ContentValidator
 * @package DownloadCore\Repeatable
 */
trait ContentValidator
{
    protected function testResultOfGetContentOfUrl(Crawler $result)
    {
        if (!($result instanceof Crawler)) {
            return false;
        }

        if (!$result->count()) {
            // No nodes...
            return false;
        }

//        $list = $result->filter('#job_ad_list');
//        if($list->count())

        return true;

    }

//    protected function testResultOfGetContentOfUrl(Crawler $result) {
//        return $this->testResultOfGetContentOfUrl($result);
//    }
}