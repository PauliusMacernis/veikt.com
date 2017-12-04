<?php

namespace DownloadCore\Logger\Fabric;

class Project
{
    public static function create($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix)
    {
        return new \DownloadCore\Logger\Project($loggerName, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix);
    }
}