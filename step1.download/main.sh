#!/usr/bin/env bash

# Run entrance.sh scripts of ./projects/{project_name} subdirectories

echo -e "\n STEP1.DOWNLOAD: Started: $(date +%Y-%m-%d:%H:%M:%S)"

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Get entrance.sh scripts of available projects
ENTRANCE_SCRIPTS=$(php -r "
    \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . 'settings.json'), true);
    \$projectsOn = \$settings['projects-on'];
    \$entranceScripts = [];
     foreach(\$projectsOn as \$projectsOnData) {
         \$file = '$DIR' . \$projectsOnData['entrance.sh'];
         if(!is_file(\$file)) {
            //continue;
         }
         \$entranceScripts[] = \$file;
     }
     echo implode(\"\\\n\", \$entranceScripts);
    ");

echo -e "\n******************************";

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

echo -e "\n STEP1.DOWNLOAD: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
