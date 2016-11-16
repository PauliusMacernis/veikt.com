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
    public function file_datetime($transformedContent) {
        return (string)$transformedContent['datetime'];
    }
}