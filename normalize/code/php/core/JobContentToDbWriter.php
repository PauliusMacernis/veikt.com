<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 11:20
 */

namespace NormalizeCore;


class JobContentToDbWriter
{

    protected $entranceDir;
    protected $contentData;

    /**
     * @var \PDO $dbConnection
     */
    protected $dbConnection;

    public function __construct($entranceDir, array $contentData)
    {
        $this->setEntranceDir($entranceDir);
        $this->contentData = $contentData;
        $this->contentDataWithSettings = $this->getSettingsMergedIntoContentData();
        $this->setDbConnectionOrFail();

    }

    protected function setEntranceDir($entranceDir)
    {
        $this->entranceDir = $entranceDir;
    }

    protected function getSettingsMergedIntoContentData()
    {
        $Settings = $this->createAndReturnSettingsObject();
        $settingsAll = $Settings->getAll();

        if (!isset($this->contentData)) {
            return array('data' => [], 'settings' => []);
        }

        if (!isset($settingsAll['content-to-extract-from-files'])) {
            return array('data' => $this->contentData, 'settings' => []);
        }

        $result = [];
        foreach ($this->contentData as $property => $info) {
            $settings = isset($settingsAll['content-to-extract-from-files'][$property])
                ? $settingsAll['content-to-extract-from-files'][$property]
                : null;
            $result[$property] = array('data' => $info, 'settings' => $settings);
        }

        return $result;

    }

    protected function setDbConnectionOrFail()
    {
        $settings = $this->createAndReturnSettingsObject($this->entranceDir);
        $settingsDatabase = $settings->getDatabase();

        $dsn = $settingsDatabase['connections']['mysql']['driver'] . ':'
            . 'dbname='
            . $settingsDatabase['connections']['mysql']['database'] . ';'
            . 'host='
            . $settingsDatabase['connections']['mysql']['host'] . ';';
        $user = $settingsDatabase['connections']['mysql']['username'];
        $password = $settingsDatabase['connections']['mysql']['password'];

        try {
            $this->dbConnection = new \PDO($dsn, $user, $password);
        } catch (ErrorHandler $e) {
            throw new ErrorHandler('Connection failed: ' . $e->getMessage());
        }

    }

    /**
     * Aka. Saving normalized info to db...
     */
    public function write()
    {

        $contentDataByTable = $this->splitContentDataByTable();

        // Begin transaction
        $this->dbConnection->beginTransaction();
        $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        foreach ($contentDataByTable as $tableName => $info) {
            $existingItemsData = $this->getExistingItemsDataFromDb($tableName, $info);

            // check for anomalies
            $existingItemsCount = count($existingItemsData);
            if ($existingItemsCount > 1) {
                $this->dbConnection->rollBack();
                throw new ErrorHandler($this->getToManyExistingItemsErrorText($existingItemsCount, $tableName, $existingItemsData));
            }

            if ($existingItemsCount == 1) {
                $this->updateData($tableName, $info, $existingItemsData);
            } else {
                $this->insertData($tableName, $info);
            }

        }

        $this->dbConnection->commit();

        //die('OKkkkKkKK...');

    }

//    protected function writeToTable($tableName, $data)
//    {
//        $id = $this->getDataIdIfExistsOrNullIfNot($tableName, $data);
//    }

    //protected function getDataIdIfExistsOrNullIfNot($tableName, $data)
    //{

    //$concatData = $this->getSelectQuery($tableName, array_filter($data));

    //}

    protected function splitContentDataByTable()
    {
        $result = array();

        foreach ($this->contentDataWithSettings as $property => $info) {
            $result[$info['settings']['database-table']][$property] = $info;
        }

        return $result;

    }

    protected function getExistingItemsDataFromDb($tableName, array $dataWithSettings)
    {
        if (empty($tableName)) {
            throw new ErrorHandler('Table name is not set.');
        }

        // Extract column settings
        $anyItem = reset($dataWithSettings);
        $idColumn = $this->getIdColumnNameFromDataWithSettingsItem($anyItem);

        // SELECT
        $q = 'SELECT `' . $idColumn . '`';
        $q .= ' FROM `' . $tableName . '` ';

        // WHERE...
        list($dataForEmbedding, $qWhereString) = $this->getQueryWhereIdentityStringWithDataColumnsAndDataToEmbed($dataWithSettings);

        if (empty($dataForEmbedding)) {
            return array();
        }

        $q .= ' WHERE (' . $qWhereString . ')';

        // query
        $PDOQuery = $this->dbConnection->prepare($q);
        $PDOQuery->execute($dataForEmbedding);
        $similarData = $PDOQuery->fetchAll(\PDO::FETCH_OBJ);

        return $similarData;


    }

    /**
     * @param $anyItem
     * @return string
     */
    protected function getIdColumnNameFromDataWithSettingsItem($anyItem)
    {
        //@todo: Shouldn't this method be moved to Settings class?

        if (isset($anyItem['settings']['database-column-id']) && !empty($anyItem['settings']['database-column-id'])) {
            $idColumn = $anyItem['settings']['database-column-id'];
            return $idColumn;
        } else {
            $idColumn = 'id';
            return $idColumn;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getQueryWhereIdentityStringWithDataColumnsAndDataToEmbed(array $dataWithSettings)
    {
        $queryArray = array();
        $dataForEmbedding = array();

        foreach ($dataWithSettings as $column => $dataAndSettings) {

            // Checking if identity component
            if (!$this->isIdentityComponentFromDataWithSettings($dataAndSettings)) {
                continue;
            }

            $queryArray[$column] =
                '('
                . $dataAndSettings['settings']['database-column']
                . '=:'
                . $dataAndSettings['settings']['database-column']
                . ')';
            $dataForEmbedding[$column] = $dataAndSettings['data'];
        }
        $queryString = implode(' AND ', $queryArray);
        return array($dataForEmbedding, $queryString);
    }

    /**
     * @param $dataAndSettings
     * @return bool
     */
    protected function isIdentityComponentFromDataWithSettings($dataAndSettings)
    {
        return (
            isset($dataAndSettings['settings']['is-identity-component'])
            && $dataAndSettings['settings']['is-identity-component']
        );
    }

    /**
     * @param $similarDataCount
     * @param $tableName
     * @param $existingData
     * @return string
     */
    protected function getToManyExistingItemsErrorText($similarDataCount, $tableName, $existingData)
    {
        return $similarDataCount . ' similar entries have been found'
            . ' inside "' . $tableName . '" table.'
            . ' One or none expected.'
            . ' Either a bug in the system exists'
            . ' (and so:'
            . ' 1. The system creates multiple values'
            . ' instead of updating an old one;'
            . ' 2. Any existing value has been manually updated'
            . ' and so now matches other value;'
            . ' 3. other'
            . ')'
            . ', either'
            . ' columns sharing "is-identity-component" values'
            . ' needs to be adjusted.'
            . ' Founding: ' . print_r($existingData, true);
    }

    protected function updateData($tableName, $dataAndSettings, $existingItemsData)
    {
        // @todo: (think about it if needed at all...) Check if we need to update at all...?
        if (!$existingItemsData) {
            throw new ErrorHandler("Update action is not available while no data presented for WHERE... part of the update query.");
        }

        // @todo: Update...
        list($columnNames, $columnValuesForEmbedding, $columnNamesForEmbedding)
            = $this->getColumnNamesAndValuesAndNamesForEmbedding($dataAndSettings);

        $anyItem = reset($dataAndSettings);
        $idColumnName = $this->getIdColumnNameFromDataWithSettingsItem($anyItem);

        $idsToUpdate = $this->getIdsToUpdate($tableName, $existingItemsData, $idColumnName);

        $q = 'UPDATE'
            . ' `' . $tableName . '`'
            . ' SET ' . $this->getUpdateQuerySetPart($columnNames, $columnNamesForEmbedding) . ''
            . ' WHERE `' . $idColumnName . '` IN (' . implode(',', $idsToUpdate) . ')';

        $PDOQuery = $this->dbConnection->prepare($q);
        $success = $PDOQuery->execute($columnValuesForEmbedding);

        if (!$success) {
            throw new ErrorHandler(implode(";\n", $PDOQuery->errorInfo()));
        }

    }

    /**
     * @param $dataAndSettings
     * @return array
     */
    protected function getColumnNamesAndValuesAndNamesForEmbedding(array $dataAndSettings)
    {
        $columnNames = $this->getColumnNamesFromDataWithSettings($dataAndSettings);
        $columnValuesForEmbedding = $this->getColumnValuesForEmbeddingFromDataWithSettings($dataAndSettings);
        $columnNamesForEmbedding = $this->getColumnNamesForEmbeddingFromColumnNames($columnNames);

        return array($columnNames, $columnValuesForEmbedding, $columnNamesForEmbedding);
    }

    /**
     * @param $dataAndSettings
     * @return array
     */
    protected function getColumnNamesFromDataWithSettings(array $info)
    {
        return array_keys($info);
    }

    /**
     * @param $info
     * @return array
     */
    protected function getColumnValuesForEmbeddingFromDataWithSettings(array $info)
    {
        return array_map(function ($value) {
            return $value['data'];
        }, $info);
    }

    /**
     * @param $columnNames
     * @return array
     */
    protected function getColumnNamesForEmbeddingFromColumnNames(array $columnNames)
    {
        return array_map(function ($columnName) {
            return ':' . $columnName;
        }, $columnNames);
    }

    /**
     * @param $tableName
     * @param $existingItemsData
     * @param $idColumnName
     * @param $idsToUpdate
     * @return mixed
     */
    protected function getIdsToUpdate($tableName, $existingItemsData, $idColumnName)
    {
        $idsToUpdate = array();

        foreach ($existingItemsData as $item) {
            if (!property_exists($item, $idColumnName)) {
                throw new ErrorHandler('Would like to update entries in "' . $tableName . '" table, but there is no ids of updatable entries.');
            }
            $idsToUpdate[(int)$item->$idColumnName] = (int)$item->$idColumnName;
        }
        return $idsToUpdate;
    }

    protected function getUpdateQuerySetPart($columnNames, $columnNamesForEmbedding)
    {
        $setValues = array();
        $combinedData = array_combine($columnNames, $columnNamesForEmbedding);
        foreach ($combinedData as $columnName => $columnNameForEmbedding) {
            $setValues[$columnName] = $columnName . '=' . $columnNameForEmbedding;
        }
        return implode(',', $setValues);
    }

    protected function insertData($tableName, $dataAndSettings)
    {
        list($columnNames, $columnValuesForEmbedding, $columnNamesForEmbedding)
            = $this->getColumnNamesAndValuesAndNamesForEmbedding($dataAndSettings);

        $q = 'INSERT INTO'
            . ' `' . $tableName . '`'
            . ' (' . implode(',', $columnNames) . ')'
            . ' VALUES (' . implode(',', $columnNamesForEmbedding) . ')';

        $PDOQuery = $this->dbConnection->prepare($q);
        $PDOQuery->execute($columnValuesForEmbedding);
    }

    /**
     * @return Settings
     */
    protected function createAndReturnSettingsObject()
    {
        $Settings = new Settings($this->entranceDir);
        return $Settings;
    }

}