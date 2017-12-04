<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-13
 * Time: 18:16
 */

namespace DownloadCore;


class Settings
{

    /**
     * @var string Path to directory where the settings files reside
     */
    protected $settingsDirPath;

    /**
     * @var array All settings from settings.json
     */
    protected $all;

    /**
     * @var array All settings related to project from settings.json
     */
    protected $project;

    /**
     * @var array All settings related to database from settings.database.private.json
     * or settings.database{.public}.json if the previous does not exist
     */
    protected $database;

    /**
     * @var array All settings related to mail from settings.mail.private.json
     * or settings.mail{.public}.json if the previous does not exist
     */
    protected $mail;

    /**
     * @var array All settings related to a proxy from settings.proxy.private.json
     * or settings.proxy{.public}.json if the previous does not exist
     */
    protected $proxy;

    /**
     * @var array List of proxies: $this->proxy + $this->project->proxy
     */
    protected $proxyGlobalAndProject;
    /**
     * @var string Directory where entrance.sh resides
     */
    protected $entranceDirOfProject;

    /**
     * Settings constructor.
     * @param string $entranceDirOfProject Directory where entrance.sh resides
     */
    public function __construct($entranceDirOfProject = null)
    {
        $this->setEntranceDirOfProject($entranceDirOfProject);
        $this->setAll();
        $this->setDatabase();
        $this->setMail();
        $this->setProxy();
        $this->setProject();

        // Global + project
        $this->setProxyGlobalAndProject();
    }

    /**
     * @return array
     */
    public function getProxyGlobalAndProject()
    {
        return $this->proxyGlobalAndProject;
    }

    public function setProxyGlobalAndProject()
    {
        // Global
        if (
            isset($this->project['proxies_include_global']) && $this->project['proxies_include_global']
            && isset($this->proxy) && is_array($this->proxy)
        ) {
            $globalProxiesList = $this->proxy;
        } else {
            $globalProxiesList = [];
        }

        // Project
        if (isset($this->project)
            && isset($this->project['proxies']) && is_array($this->project['proxies'])) {
            $privateProxiesList = $this->project['proxies'];
        } else {
            $privateProxiesList = [];
        }

        // Set Global + Project
        $this->proxyGlobalAndProject = array_merge(array_values($globalProxiesList), array_values($privateProxiesList));

    }

    public function getProject()
    {
        return $this->project;
    }

    protected function setProject()
    {

        $projectDirFromEntranceScript = $this->getEntranceDirOfProject();
        $settingsAll = $this->getAll();

        // Find project settings
        if (!isset($settingsAll['projects-on']) || !is_array($settingsAll['projects-on'])) {
            $errorText = 'No projects enabled in \'settings.json\'.';
            throw new ErrorHandler($errorText);
        }

        // Find settings of this project
        foreach ($settingsAll['projects-on'] as $projectName => $projectSettingsTemp) {
            if (!isset($projectSettingsTemp['unique_project_path'])) {
                continue;
            }
            $uniqueProjectDirFromSettings = $projectSettingsTemp['unique_project_path'];

            if (
                !empty($projectDirFromEntranceScript)
                && !empty($uniqueProjectDirFromSettings)
                && substr_compare(
                    $projectDirFromEntranceScript,
                    $uniqueProjectDirFromSettings,
                    strlen($projectDirFromEntranceScript) - strlen($uniqueProjectDirFromSettings),
                    strlen($uniqueProjectDirFromSettings)
                ) === 0
            ) {
                // $projectSettings item is found!
                $this->project = $projectSettingsTemp;
                return $this->project;
            }
        }

        $this->project = array();
        return $this->project;

    }

    public function getEntranceDirOfProject()
    {
        return $this->entranceDirOfProject;
    }

    /**
     * @param string $entranceDirOfProject Directory where entrance.sh resides
     */
    protected function setEntranceDirOfProject($entranceDirOfProject)
    {
        $this->entranceDirOfProject = $entranceDirOfProject;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    protected function setDatabase()
    {
        $this->database = $this->getSettingsFileContent('database');
    }

    public function getMail()
    {
        return $this->mail;
    }

    protected function setMail()
    {
        $this->mail = $this->getSettingsFileContent('mail');
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    protected function setProxy()
    {
        $this->proxy = $this->getSettingsFileContent('proxy');
    }

    /**
     * @param array $contentFiles
     * @return bool
     */
    public function validateContentDownloaded(array $contentFiles)
    {
        $settings = $this->getAll();

        // No requirements? The content is valid then...
        if (!isset($settings['files-to-output'])) {
            return;
        }

        foreach ($settings['files-to-output'] as $filename => $fileInfo) {
            // Ignore non-required files
            if (!$fileInfo['required-file']) {
                continue;
            }

            // Fail if file is not created
            if ($fileInfo['required-file'] && !isset($contentFiles[$filename])) {
                throw new ErrorHandler(
                    'Error. File "' . $filename . '" is not created. '
                    . 'It must be created according to settings.json '
                    . 'Dir: "' . $this->downloadedPostDir . '"'
                );
            }

            // Fail if file is empty
            if ($fileInfo['required-data'] && empty($contentFiles[$filename])) {
                throw new ErrorHandler(
                    'Error. File "' . $filename . '" is empty. '
                    . 'It must be not empty according to settings.json '
                    . 'Dir: "' . $this->downloadedPostDir . '"'
                );
            }
        }

        // OK
        return;

    }

    public function getAll()
    {
        return $this->all;
    }

    protected function setAll()
    {
        $this->all = $this->getSettingsFileContent('');
    }

    /**
     * @param array $normalizedContent
     * @return bool
     */
    public function isNormalizedContentValid(array $normalizedContent)
    {
        $settings = $this->getRequiredToNormalize();

        foreach ($settings as $property => $info) {
            if (!isset($normalizedContent['file_datetime'])) {
                return false;
            }
        }

        return true;

    }

    protected function getRequiredToNormalize()
    {
        $all = $this->getAll();

        if (!isset($all['content-to-extract-from-files'])) {
            return array();
        }

        return array_filter($all['content-to-extract-from-files'], function ($value) {
            if (!isset($value['required'])) {
                return false;
            }
            return (bool)$value['required'];
        });

    }


    /**
     * @param $case
     * @return mixed|string
     */
    protected function getSettingsFileContent($case)
    {
        $projectDir = $this->getEntranceDirOfProject();

        $fileSettings = $this->getSettingsFilePath($case, $projectDir);

        $settings = file_get_contents($fileSettings);
        $settings = json_decode($settings, true);
        if (!isset($settings) || empty($settings)) {
            throw new ErrorHandler('No settings found.');
        }
        return $settings;
    }

    /**
     * @param $case
     * @param $projectDir
     * @return string
     * @throws ErrorHandler
     */
    protected function getSettingsFilePath($case, $projectDir)
    {
        $fileSettings = $this->getSettingsDirPath($projectDir)
            . 'settings'
            . ($case ? ('.' . $case) : '')
            . '{{VISIBILITY}}'
            . '.json';

        // Require and decode all settings
        if (is_file(strtr($fileSettings, ['{{VISIBILITY}}' => '.private']))) {
            return strtr($fileSettings, ['{{VISIBILITY}}' => '.private']);
        } elseif (is_file(strtr($fileSettings, ['{{VISIBILITY}}' => '.public']))) {
            return strtr($fileSettings, ['{{VISIBILITY}}' => '.public']);
        } elseif (is_file(strtr($fileSettings, ['{{VISIBILITY}}' => '']))) {
            return strtr($fileSettings, ['{{VISIBILITY}}' => '']);
        } else {
            throw new ErrorHandler('Settings file is not found.');
        }
    }

    /**
     * @param $projectDir
     * @return string
     */
    protected function getSettingsDirPath($projectDir)
    {
        if (!isset($this->settingsDirPath)) {
            $this->setSettingsDirPath();
        }

        return $this->settingsDirPath;

    }

    protected function setSettingsDirPath()
    {
        $this->settingsDirPath =
            dirname(__FILE__) . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR;
    }

}