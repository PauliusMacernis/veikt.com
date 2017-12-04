<?php

namespace DownloadCore\BrowserClient\Fabric;

/**
 * Class BrowserClient
 * @package DownloadCore\BrowserClient\Fabric
 */
class BrowserClient
{
    public static function create($proxiesList = [])
    {
        $browserClient = new \DownloadCore\BrowserClient\BrowserClient();
        $browserClientSettings = new \DownloadCore\BrowserClient\BrowserClientSettings($proxiesList);

        return $browserClient->setClient($browserClientSettings);

    }
}