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
        $contentStatic = new Crawler();
        $contentStatic->addHtmlContent($content['content_static'], 'UTF-8');
        $content['content_static'] = $contentStatic;

        $contentDynamic = new Crawler();
        $contentDynamic->addHtmlContent($content['content_dynamic'], 'UTF-8');
        $content['content_dynamic'] = $contentDynamic;

        return $content;

    }
}