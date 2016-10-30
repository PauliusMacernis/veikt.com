<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-13
 * Time: 18:16
 */

namespace Core;


class Helper
{

    public static function getSettingsAll($projectDir)
    {

        $fileSettings = $projectDir . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . 'settings.json';

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

        return $settings;

    }

    public static function getSettingsProject($projectDirFromEntranceScript, array $settingsAll)
    {

        // Find project settings
        if (!isset($settingsAll['projects-on']) || !is_array($settingsAll['projects-on'])) {
            die('No projects enabled in \'settings.json\'.');
        }

        // Find settings of this project
        foreach ($settingsAll['projects-on'] as $projectName => $projectSettingsTemp) {
            if (!isset($projectSettingsTemp['entrance_sh_step1_download'])) {
                continue;
            }
            $projectDirFromSettings = pathinfo($projectSettingsTemp['entrance_sh_step1_download'], PATHINFO_DIRNAME);
            if (
                substr_compare(
                    $projectDirFromEntranceScript,
                    $projectDirFromSettings,
                    strlen($projectDirFromEntranceScript) - strlen($projectDirFromSettings),
                    strlen($projectDirFromSettings)
                ) === 0
            ) {
                // $projectSettings item is found!
                return $projectSettingsTemp;
            }
        }

        return array();

    }

}