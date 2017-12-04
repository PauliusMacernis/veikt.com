<?php

namespace DownloadCore\Logger\Fabric;

class Connection
{
    public static function create($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix)
    {
        return new \DownloadCore\Logger\Connection($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix);
    }
}