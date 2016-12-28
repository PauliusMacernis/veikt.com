<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-12-26
 * Time: 20:08
 */

namespace DownloadCore\Pattern;

// Use auto-loading classes...
use DownloadCore\Settings;
use DownloadProject\Cvbankas\Lt\Classes\Auditor;
use DownloadProject\Cvbankas\Lt\Classes\Browser;


class Core
{

    protected $settingsAll;
    protected $settingsProject;
    protected $Browser;
    protected $Auditor;


    public function __construct($indexDir)
    {
        // Get settings: All & Project specific
        $settings = new Settings($indexDir);
        $this->settingsAll = $settings->getAll();
        $this->settingsProject = $settings->getProject();

        // Initiate main objects for dealing with the content
        $this->Browser = new Browser(
            $indexDir,
            $this->settingsAll,
            $this->settingsProject
        );
        $this->Auditor = new Auditor(
            $indexDir,
            $this->settingsAll,
            $this->settingsProject,
            $this->Browser->getDownloadsDirectoryPathJobs()
        );

    }
}