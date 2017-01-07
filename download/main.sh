#!/usr/bin/env bash


# Php
PHP_FILE_PRODUCTION_ENV="/opt/php/php-5.5.13/bin/php"
# Local php config file
PHP_CONFIG_FILE_PRODUCTION_ENV="/home/antdelno/php_extensions/php.ini"

# Run entrance.sh scripts of projects-on
echo -e "\n DOWNLOAD: Started: $(date +%Y-%m-%d:%H:%M:%S)"

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
ENTRANCE_SCRIPT_CODE="
    \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
    \$projectsOn = \$settings['projects-on'];
    \$entranceScripts = [];
     foreach(\$projectsOn as \$projectsOnData) {
         if(!isset(\$projectsOnData['entrance_sh_download'])) {
            // Skip the project that does not have the entrance.sh script defined
            continue;
         }
         \$file = '$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . \$projectsOnData['entrance_sh_download'];
         if(!is_file(\$file)) {
            // Skip the project that does not have the entrance.sh script in filesystem
            continue;
         }
         \$entranceScripts[] = \$file;
     }
     echo implode(\"\\\n\", \$entranceScripts);
"


# Get entrance.sh scripts of available projects
if [ -f $PHP_CONFIG_FILE_PRODUCTION_ENV ];
then
   ENTRANCE_SCRIPTS=$("$PHP_FILE_PRODUCTION_ENV" -c "$PHP_CONFIG_FILE_PRODUCTION_ENV" -r "$ENTRANCE_SCRIPT_CODE")
else
   ENTRANCE_SCRIPTS=$(php -r "$ENTRANCE_SCRIPT_CODE");
fi


# Run every entrance.sh script found
echo -e $ENTRANCE_SCRIPTS |
while IFS= read -r opFile; do
  if [ -f "${opFile}" ]; then
    echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Started with '${opFile}'";
    chmod 774 $opFile
    bash $opFile
    echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Finished with '${opFile}'";
    echo -e "\n******************************";
  fi
done

echo -e "\n DOWNLOAD: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
