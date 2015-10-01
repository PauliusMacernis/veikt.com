#!/usr/bin/env bash

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Execute current script with PHP
php -f "$DIR/index.php"