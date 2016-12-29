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
     * @var array All settings from settings.json
     */
    protected $all;

    /**
     * @var array All settings related to project from settings.json
     */
    protected $project;

    /**
     * @var array All settings related to database from settings.database.private.json
     */
    protected $database;

    /**
     * @var string Directory where entrance.sh resides
     */
    protected $entranceDirOfProject;

    /**
     * Settings constructor.
     * @param string $entranceDirOfProject Directory where entrance.sh resides
     */
    public function __construct($entranceDirOfProject)
    {
        $this->setEntranceDirOfProject($entranceDirOfProject);
        $this->setAll();
        $this->setDatabase();
        $this->setProject();
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
                substr_compare(
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

    /**
     * @param array $contentFiles
     * @return bool
     */
    public function isContentValid(array $contentFiles)
    {
        $settings = $this->getAll();

        if (!isset($settings['files-to-output'])) {
            return true;
        }

        foreach ($settings['files-to-output'] as $filename => $fileInfo) {
            if (!$fileInfo['required']) {
                continue;
            }

            if (!isset($contentFiles[$filename])) {
                return false;
            }

        }

        return true;

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

        $fileSettings = $projectDir . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . 'settings'
            . ($case ? ('.' . $case . '.private') : '')
            . '.json';

        // Require and decode all settings
        if (!is_file($fileSettings)) {
            throw new ErrorHandler('Settings file is not found.');
        }

        $settings = file_get_contents($fileSettings);
        $settings = json_decode($settings, true);
        if (!isset($settings) || empty($settings)) {
            throw new ErrorHandler('No settings found.');
        }
        return $settings;
    }

}