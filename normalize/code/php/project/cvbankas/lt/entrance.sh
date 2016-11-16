#!/usr/bin/env bash

PROJECT_DIR_TO_NORMALIZE=$1
UNIQUE_PROCESS_ID_ASSIGNED_BY_MAIN=$2

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# The php file this script should run
INDEX_FILE_PHP="index.php"

# Php
PHP_FILE_PRODUCTION_ENV="/opt/php/php-5.5.0/bin/php"
# Local php config file
PHP_CONFIG_FILE_PRODUCTION_ENV="/home/antdelno/php_extensions/php.ini"

# Run php with local php configuration if config file exists
if [ -f $PHP_CONFIG_FILE_PRODUCTION_ENV ];
then
   $PHP_FILE_PRODUCTION_ENV -c PHP_CONFIG_FILE_PRODUCTION_ENV -f "$DIR/$INDEX_FILE_PHP" $PROJECT_DIR_TO_NORMALIZE $UNIQUE_PROCESS_ID_ASSIGNED_BY_MAIN
else
   php -f "$DIR/$INDEX_FILE_PHP" $PROJECT_DIR_TO_NORMALIZE $UNIQUE_PROCESS_ID_ASSIGNED_BY_MAIN
fi
