<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-09-23
 * Time: 23:30
 */

namespace AppBundle\Service;


use Doctrine\Common\Cache\Cache;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class MarkdownTransformer
{

    private $markdownParser;
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(MarkdownParserInterface $markdownParser, Cache $cache) // PHPStorm: Alt+Enter to initialize fields
    {
        $this->markdownParser = $markdownParser;
        $this->cache = $cache;
    }

    public function parse($str) {

        $cache = $this->cache;
        $key = md5($str);
        if($cache->contains($key)) {
            return $cache->fetch($key);
        }

        //sleep(10);
        $str = $this->markdownParser
            ->transformMarkdown($str);
        $cache->save($key, $str);

        return $str;

    }

}