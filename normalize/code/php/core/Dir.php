<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-03
 * Time: 18:03
 */

namespace NormalizeCore;


class Dir
{
    protected $entranceDir;
    protected $downloadedPostDir;


    public function __construct($entranceDir, $downloadedPostDir)
    {
        $this->setEntranceDir($entranceDir);
        $this->setDownloadedPostDir($downloadedPostDir);
    }

    private function setEntranceDir($entranceDir)
    {
        $this->entranceDir = $entranceDir;
    }

    protected function setDownloadedPostDir($downloadedPostDir)
    {
        $this->downloadedPostDir = $downloadedPostDir;
    }

}