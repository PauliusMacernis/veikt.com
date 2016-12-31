<?php

// Require autoload
require_once
    'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


use PublicizeCore\UnpublishToDbWriter as Db;

$Db = new Db(__DIR__, []);
$Db->unpublishJobsFromOldTransactionsOrFail();
$Db->unpublishJobsImportedOldiesOrFail();
$Db->unpublishJobsUpdatedOldiesOrFail();
