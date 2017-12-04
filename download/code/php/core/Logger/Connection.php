<?php

namespace DownloadCore\Logger;


class Connection extends Core
{
    protected $pathToLogFile;

    public function setPathToLogFile($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix = '')
    {
        $this->pathToLogFile =
            $dirRootAbsolute
            . DIRECTORY_SEPARATOR . 'download'
            . DIRECTORY_SEPARATOR . 'logs'
            . DIRECTORY_SEPARATOR . 'connection'
            . DIRECTORY_SEPARATOR . $filenamePrefix . '-' . $name . '.log';
    }
}