<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-12-26
 * Time: 20:00
 */

namespace DownloadCore\Pattern;


/**
 * Class ListNextPage
 * @package DownloadCore\Pattern
 */
class ListNextPage extends Core
{

    public function __construct($dirRoot, $dirProject)
    {
        parent::__construct($dirRoot, $dirProject);
    }


    /*
     * Download one job posting.
     * For example:
     * http://download.veikt.dev/code/php/project/cvbankas/lt/index.php?url=http://www.cvbankas.lt/pardavimu-telefonu-vadybininkas-e-vilniuje-lietuvos-rinka-vilniuje/1-4204758
     */
    public function downloadOne($url)
    {

        if (!isset($url) || empty($url)) {
            return;
        }

        // Get job info
        $iJobList = array($url => $url);
        $iJob = $this->Browser->getJobsFromTheList($iJobList);

        // Save info to files
        $iSave = $this->Browser->saveJobsToFiles($iJob);

        // Log the action
        $this->Auditor->registerListAndJobs($iJobList, $iSave);

        // Done.
        $this->Auditor->doReport();

    }


    /**
     * Download many job posting urls.
     * For example:
     * http://download.veikt.dev/code/php/project/cvbankas/lt/index.php
     */
    public function downloadAll()
    {
        // We are starting to get the whole list of jobs...
        $this->Browser->markQueueBegin();

        // Getting the initial list
        $List = $this->Browser->getFirstListOfJobLinks();
        //$List = [reset($List)];

        do {
            // Get and save jobs by one, not by the whole list.
            foreach ($List as $iJobUrl) {
                // Simplify the list & Get new jobs of the simplified list
                $iJobList = array($iJobUrl => $iJobUrl);
                $iJob = $this->Browser->getJobsFromTheList($iJobList);

                // Save data to files
                $iSave = $this->Browser->saveJobsToFiles($iJob);

                // Put fresh data on top of existing
                //$Jobs = $iJob + $Jobs;

                // Logging and similar stuff
                $this->Auditor->registerListAndJobs($iJobList, $iSave);
            }
        } while (
            $List = $this->Browser->getNextListOfJobLinks()
            //$List = null
        );

        $this->Browser->markQueueEnd();
        $this->Auditor->doReport();
    }

}