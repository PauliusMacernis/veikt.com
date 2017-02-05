<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-11-06
 * Time: 11:20
 */

namespace PublicizeCore;


use NormalizeCore\JobContentToDbWriter;


class UnpublishToDbWriter extends JobContentToDbWriter
{
    public function unpublishJobsUpdatedOldiesOrFail()
    {
        $q = $this->getUnpublishJobsUpdatedOldiesQuery();

        $PDOQuery = $this->dbConnection->prepare($q);
        $success = $PDOQuery->execute();

        if (!$success) {
            throw new ErrorHandler(implode(";\n", $PDOQuery->errorInfo()));
        }
    }

    private function getUnpublishJobsUpdatedOldiesQuery($ageTurningOldInHours = 48)
    {
        $q = '
            UPDATE
                  job as j
                SET
                  j.is_published = 0
                WHERE
                  j.is_published = 1
                    AND 
                  j.datetime_imported < SUBDATE(UTC_TIMESTAMP(), INTERVAL ' . (int)$ageTurningOldInHours . ' HOUR);
        ';

        return $q;

    }

    public function unpublishJobsImportedOldiesOrFail()
    {
        $q = $this->getUnpublishJobsImportedOldiesQuery();

        $PDOQuery = $this->dbConnection->prepare($q);
        $success = $PDOQuery->execute();

        if (!$success) {
            throw new ErrorHandler(implode(";\n", $PDOQuery->errorInfo()));
        }
    }

    private function getUnpublishJobsImportedOldiesQuery($ageTurningOldInHours = 48)
    {
        $q = '
            UPDATE
                  job as j
                SET
                  j.is_published = 0
                WHERE
                  j.is_published = 1
                    AND 
                  j.datetime_imported < SUBDATE(UTC_TIMESTAMP(), INTERVAL ' . (int)$ageTurningOldInHours . ' HOUR);
        ';

        return $q;

    }

    public function unpublishJobsFromOldTransactionsOrFail()
    {
        $q = $this->getUnpublishOldJobsQuery();

        $PDOQuery = $this->dbConnection->prepare($q);
        $success = $PDOQuery->execute();

        if (!$success) {
            throw new ErrorHandler(implode(";\n", $PDOQuery->errorInfo()));
        }

    }

    /**
     * @return string   SQL query as a string
     */
    protected function getUnpublishOldJobsQuery()
    {
        $q = 'UPDATE 
                job as j
                  SET j.is_published = 0
                WHERE
                  j.file_browser_id NOT IN (
                        
                        # Selected all latest file_browser_id values per project
                        SELECT
                            dataProjectProcess.browser_process_id
                            FROM (
                                SELECT
                                    j.file_project as project, j.file_browser_id as browser_process_id, MAX(j.datetime_imported) as last_import_datetime
                                FROM 
                                    job as j
                                #WHERE
                                #	j.is_published = 1
                                GROUP BY 
                                    j.file_project, j.file_browser_id
                            ) as dataProjectProcess
                        
                            INNER JOIN (
                                SELECT
                                    j.file_project as project, MAX(j.datetime_imported) as last_import_datetime
                                FROM 
                                    job as j
                                #WHERE
                                #	j.is_published = 1
                                GROUP BY 
                                    j.file_project
                            ) as dataProject ON (dataProjectProcess.project = dataProject.project AND dataProjectProcess.last_import_datetime = dataProject.last_import_datetime)
                        # END. Selected all latest file_browser_id values per project
                
                  )
              ';
        return $q;
    }

    /**
     * @return Settings
     */
    protected function createAndReturnSettingsObject()
    {
        $Settings = new Settings();
        return $Settings;
    }

}