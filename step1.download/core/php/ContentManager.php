<?php

class ContentManager
{

    public function __construct()
    {

    }

    const DELAY_MIN = 3;
    const DELAY_MAX = 20;
    const APPLY_GET_URL_CONTENT_DELAY = false;

    private $black_list_chars_page_numbers = array('-', '/', '|', ' ', "\n", "\r", '.');

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'delay':
                return rand(self::DELAY_MIN, self::DELAY_MAX);
            default:
                return $this->$name;
        }
    }

    public function getUrlContent($url)
    {

        if ($this::APPLY_GET_URL_CONTENT_DELAY) {
            sleep($this->delay);
        }

        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = FALSE;

        // Create a stream
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n",
                'user_agent' => "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0",
                'follow_location' => 0
            )
        );
        $context = stream_context_create($options);

        // @todo: is there something better than the following line?
        // The reason of the following line is:
        //     "Warning: DOMDocument::loadHTML(): Unexpected end tag : iframe in Entity, line: ... in ... on line ..."
        libxml_use_internal_errors(true);

        $doc->loadHTML(mb_convert_encoding(file_get_contents(trim($url), false, $context), 'HTML-ENTITIES', 'UTF-8'));

        //@$doc->loadHTMLFile($url);
        return $doc;//->saveHTML();
    }

    public function getInnerHtml($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    public function saveToFile($id = null, array $getObjectVars)
    {
        if (!$id) {
            // @todo: log this case as it is important!
            return false;
        }

        if (empty($getObjectVars)) {
            return true;
        }

        foreach ($getObjectVars as $var => $value) {
            if (is_null($value) || ($value === '')) {
                continue;
            }

            $dirToOutput = '.' . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . $id;
            if (!is_dir($dirToOutput)) {
                mkdir($dirToOutput, 0777, true);
            }

            $fp = fopen($dirToOutput . DIRECTORY_SEPARATOR . $var, 'w');
            fwrite($fp, $value);
            fclose($fp);
        }

        return true;

    }


}