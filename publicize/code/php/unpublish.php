<?php

// Require autoload
require_once
    'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//set_error_handler(array(new \DownloadCore\ErrorHandler, 'defaultErrorHandler'));
//register_shutdown_function(array(new \NormalizeCore\ErrorHandler, 'defaultRegisterShutdown'));

use PublicizeCore\UnpublishToDbWriter as Db;

$Db = new Db(__DIR__, []);
$Db->unpublishJobsFromOldTransactionsOrFail();
$Db->unpublishJobsImportedOldiesOrFail();
$Db->unpublishJobsUpdatedOldiesOrFail();
