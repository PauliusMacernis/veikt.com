<?php

/**
 * The class of JobPosting for step 2 - normalization
 */
class JobPostingStep2Normalize extends JobPostingStep1Download {

    /**
     * Get columns required to update. step1_id, step1_url, etc.
     *
     * @param PDO $PDO
     * @param $prefixRequired
     * @return array
     */
    public static function getColumnsRequiredToUpdate(PDO $PDO, $prefixRequired) {

        return self::getColumnsRequiredToUpdateFromDB($PDO, $prefixRequired, 'job');

    }

    public static function getColumnsNeededToBeNormalizedTo(PDO $PDO, $prefixRequired) {

        return self::getColumnsNeededToBeNormalizedToDB($PDO, $prefixRequired, 'job');

    }

    /**
     * Checks if files of the job post and table columns match.
     *
     * @param array $propertiesRequiredToUpdate
     * @param array $files
     * @param string $prefix                        Prefix meaning required files ("step1_")
     * @return bool
     */
    public static function areFilesCorrect(array $propertiesRequiredToUpdate, array $files, $prefix) {

        if(count($propertiesRequiredToUpdate) !== count($files)) {
            return false;
        }

        foreach($files as $file) {
            if(!in_array($prefix . $file, $propertiesRequiredToUpdate)) {
                return false;
            }
        }

        return true;

    }

    /**
     * Normalize values coming from JobPostingStep1Download object
     * (it means - distribute existing values of the object acros other properties of the same object)
     */
    public function normalize(array $propertiesNeededToBeNormalizedTo = array())
    {
        return; // @todo: run normalization process. Every project may have the own process of nornalization?
    }

    /**
     * Save to DB
     *
     * @param PDO $PDO
     * @param $columns
     * @param $prefix
     * @return bool|void
     */
    public function saveToDb(PDO $PDO, $columns, $prefix) {

        if(!$columns) {
            return;
        }

        $valuesForSql = array();

        foreach ($columns as $columnName) {
            // @todo: Maybe replacing main properties in step1 and step2 would be much better than dealing with this kin of conversions?
            // Convert $this columns to DB (step1) columns format
            if (substr($columnName, 0, strlen($prefix)) == $prefix) {
                $columnNameThis = substr($columnName, strlen($prefix));
            }
            // ...
            $valuesForSql[$columnName] = $this->get($columnNameThis);
        }


        $PDO->beginTransaction();

        $q = 'INSERT INTO job (`' .
            implode('`,`', array_keys($valuesForSql))
            . '`) VALUES (:' .
            implode(', :', array_keys($valuesForSql))
            . ')';
        $PDOStatement = $PDO->prepare($q);

        foreach($valuesForSql as $columnName => $columnValue) {
            $PDOStatement->bindValue((':' . $columnName), $columnValue);
        }

        $PDOStatement->execute();
        $success = $PDO->commit();

        return $success;

    }

    public function getPropertiesRestrictedToUpdate() {
        return ['id', 'created'];
    }

    public static function getColumnsRequiredToUpdateFromDB(PDO $PDO, $prefix, $table)
    {

        $q = $PDO->prepare("DESCRIBE " . $table);
        $q->execute();

        $tableColumns = array();

        while($columnName = $q->fetchColumn()) {
            if (0 !== strpos($columnName, $prefix)) {
                continue;
            }
            $tableColumns[$columnName] = $columnName;
        }

        return $tableColumns;

    }

    public static function getColumnsNeededToBeNormalizedToDB(PDO $PDO, $prefix, $table)
    {

        $q = $PDO->prepare("DESCRIBE " . $table);
        $q->execute();

        $tableColumns = array();

        while($columnName = $q->fetchColumn()) {
            if (0 === strpos($columnName, $prefix)) {
                continue;
            }
            $tableColumns[$columnName] = $columnName;
        }

        return $tableColumns;

    }

}