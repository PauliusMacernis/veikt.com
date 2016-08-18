<?php

/**
 * The class of JobPosting for step 2 - normalization
 */
class JobPostingStep2Normalize extends JobPostingStep1Download {

    /**
     * Normalize values coming from JobPostingStep1Download object (it means - split values from
     */
    public function normalize(array $propertiesToNormalize = array())
    {
        return; // @todo: run normalization process. Every project may have the own process of nornalization?
    }

    public function saveToDb(PDO $PDO) {

        $columns = $this->getJobColumns($PDO);
        if(!$columns) {
            return;
        }

        $valuesForSql = array();

        foreach ($columns as $columnName) {
            $valuesForSql[$columnName] = $this->get($columnName);
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

    private function getJobColumns(PDO $PDO, $table = 'job', $forUpdate = true)
    {

        $propertiesRestrictedToUpdate = $this->getPropertiesRestrictedToUpdate();

        $q = $PDO->prepare("DESCRIBE " . $table);
        $q->execute();

        $tableColumns = array();

        while($columnName = $q->fetchColumn()) {
            if($forUpdate &&
                (in_array($columnName, $propertiesRestrictedToUpdate) || !$this->get($columnName))
            ) {
                continue; // skip columns with no properties
            }

            $tableColumns[] = $columnName;

        }

        return $tableColumns;

    }

}