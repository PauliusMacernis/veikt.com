<?php

namespace DownloadCore\Logger\Interfaces;

interface Logger
{
    public function getPathToLogFile();

    //public function setPathToLogFile($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix);
}