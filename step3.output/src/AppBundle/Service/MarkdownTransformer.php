<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-09-23
 * Time: 23:30
 */

namespace AppBundle\Service;


use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class MarkdownTransformer
{

    private $markdownParser;

    public function __construct(MarkdownParserInterface $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function parse($str) {
        return $this->markdownParser
            ->transformMarkdown($str);
    }

}