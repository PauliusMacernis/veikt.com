<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 11:20
 */

namespace NormalizeCore;


class JobContentNormalizer
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
    public function file_browser_id($transformedContent)
    {
        return (string)$transformedContent['browser_id'];
    }


    public function file_datetime($transformedContent)
    {
        return (string)$transformedContent['datetime'];
    }


    public function file_project($transformedContent)
    {
        return (string)$transformedContent['project'];
    }


    public function file_url($transformedContent)
    {
        return (string)$transformedContent['url'];
    }


    public function file_id($transformedContent)
    {
        return (string)$transformedContent['id'];
    }


    public function file_content_static($transformedContent)
    {
        return $transformedContent['content_static']->html();
    }


    public function file_content_dynamic($transformedContent)
    {
        return $transformedContent['content_dynamic']->html();
    }


    public function datetime_imported($transformedContent)
    {
        $Datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        return $Datetime->format('Y-m-d H:i:s');
    }


    public function content_static_without_tags($transformedContent)
    {
        return strip_tags($transformedContent['content_static']->html());
    }


    public function content_dynamic_without_tags($transformedContent)
    {
        return strip_tags($transformedContent['content_dynamic']->html());
    }


}