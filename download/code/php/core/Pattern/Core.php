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

    protected $dirRoot;
    protected $dirProject;

    protected $settingsAll;
    protected $settingsProject;
    protected $settingsProxyGlobalAndProject;
    protected $Browser;
    protected $Auditor;


    public function __construct($dirRoot, $dirProject)
    {
        $this->dirRoot = $dirRoot;
        $this->dirProject = $dirProject;

        // Get settings: All & Project specific
        $settings = new Settings($this->dirProject);
        $this->settingsAll = $settings->getAll();
        $this->settingsProject = $settings->getProject();
        $this->settingsProxyGlobalAndProject = $settings->getProxyGlobalAndProject();

        // Initiate main objects for dealing with the content
        $this->Browser = new Browser( // @todo: pass dir & settings as two objects instead of many string-like params?
            $this->dirRoot,
            $this->dirProject,
            $this->settingsAll,
            $this->settingsProject,
            $this->settingsProxyGlobalAndProject
        );
        $this->Auditor = new Auditor(
            $this->dirRoot,
            $this->dirProject,
            $this->settingsAll,
            $this->settingsProject,
            $this->Browser->getDownloadsDirectoryPathJobs()
        );

    }
}