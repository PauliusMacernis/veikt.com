<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 11:20
 */

namespace NormalizeProject\Cvbankas\Lt\Classes;

use \NormalizeCore\JobContentNormalizer as CoreNormalizer;
use Symfony\Component\DomCrawler\Crawler;


class JobContentNormalizer extends CoreNormalizer
{
    /**
     * @param $transformedContent   Job posting related content - all job content
     *                                  downloaded from web.
     *                              This content is transformed by
     *                                  JobContentTransformer->transform method
     * @return string               String representing the information
     *                                  related to method name.
     *                                  For more info look into settings.json file
     */
//    public function content_static($transformedContent) {
//
//        var_dump($transformedContent['content_static']); die();
//
//        return (string)$transformedContent['datetime'];
//    }

    public function file_id($transformedContent) {

        return $transformedContent['id'];

    }

    public function file_id2($transformedContent) {

        return $this->file_id($transformedContent);

    }


    public function file_content_static($transformedContent) {

        return $transformedContent['content_static']->html();

    }


    public function file_content_dynamic($transformedContent) {

        return $transformedContent['content_dynamic']->html();

    }
}