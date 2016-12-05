<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 21:01
 */

namespace NormalizeCore;

/**
 * Transforms data before passing to JobContentNormalizer
 *
 * Class JobContentTransformer
 * @package NormalizeCore
 *
 */
class JobContentTransformer
{
    /**
     * The main method
     * @param $content Content found in file after downloading job posting
     */
    public function transform($content)
    {
        return $content;
    }
}