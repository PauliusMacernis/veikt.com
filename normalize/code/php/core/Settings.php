<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 09:07
 */

namespace NormalizeCore;
use \DownloadCore\Settings as DownloadSettings;


class Settings extends DownloadSettings
{

    /*
    protected $database;

    public function getDatabase() {
        return $this->database;
    }

    protected function setDatabase() {

        $projectDir = $this->getEntranceDirOfProject();

        $fileSettings = $projectDir . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . 'settings.database.json';

        // Require and decode all settings
        if (!is_file($fileSettings)) {
            die('Settings file is not found.');
        }

        $settings = file_get_contents($fileSettings);
        $settings = json_decode($settings, true);
        if (!isset($settings) || empty($settings)) {
            // @todo: Shouldn't be just die (applies everywhere where die is being used)... Another solution needed in here.
            die('No settings found.');
        }

        $this->all = $settings;

    }
    */

}