<?php

namespace DownloadCore\Logger\Fabric;

class Integrity
{
    public static function create($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix)
    {
        return new \DownloadCore\Logger\Integrity($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix);
    }
}