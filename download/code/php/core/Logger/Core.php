<?php

namespace DownloadCore\Logger;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Core extends Logger implements \DownloadCore\Logger\Interfaces\Logger
{
    protected $pathToLogFile;

    public function __construct($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix)
    {
        $this->setPathToLogFile($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix);
        parent::__construct($name, [
            $this->getCustomStreamHandler()
        ]);
    }

    /**
     * @return StreamHandler
     */
    protected function getCustomStreamHandler()
    {
        return new StreamHandler($this->pathToLogFile, Logger::INFO);
    }

    public function getPathToLogFile()
    {
        return $this->pathToLogFile;
    }

    public function setPathToLogFile($name, $dirRootAbsolute, $dirProjectRelative, $filenamePrefix = '')
    {
        $this->pathToLogFile =
            $dirRootAbsolute
            . DIRECTORY_SEPARATOR . $dirProjectRelative
            . DIRECTORY_SEPARATOR . $filenamePrefix . '-' . $name . '.log';
    }
}