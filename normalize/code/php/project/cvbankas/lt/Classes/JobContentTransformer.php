<?php

namespace NormalizeProject\Cvbankas\Lt\Classes;

use NormalizeCore\JobContentTransformer as CoreTransformer;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Transforms data before passing to JobContentNormalizer
 *
 * Class JobContentTransformer
 * @package NormalizeProject
 *
 */
class JobContentTransformer extends CoreTransformer
{
    /**
     * The main method
     * @param $content Content found in file after downloading job posting
     */
    public function transform($content)
    {

        // Transform main HTML content to easier extract smaller amount of it later
        $content['content_static'] = new Crawler($content['content_static']);
        $content['content_dynamic'] = new Crawler($content['content_dynamic']);

        return $content;

    }
}