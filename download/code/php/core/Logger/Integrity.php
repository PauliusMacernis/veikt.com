<?php

namespace DownloadCore\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Integrity extends Core
{
    protected $pathToLogFile;

    public function setPathToLogFile($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix = '')
    {
        // This logger gets the whole path of the log file
        // @todo: Construct the whole path here instead of somewhere outside?
        $this->pathToLogFile = $filenamePrefix;
    }

    protected function getCustomStreamHandler()
    {
        $streamHandler = new StreamHandler($this->pathToLogFile, Logger::INFO);
        $streamHandler->setFormatter(new LineFormatter('%message%' . "\n"));
        return $streamHandler;
    }
}