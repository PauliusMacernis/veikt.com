#!/usr/bin/env bash

# Run entrance.sh scripts of ./projects/{project_name} subdirectories

echo -e "\n STEP1.DOWNLOAD: Started: $(date +%Y-%m-%d:%H:%M:%S)"

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

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

# @todo: Go further...
echo -e $ENTRANCE_SCRIPTS;
exit;

while read -r $ENTRANCE_SCRIPTS ; do
    echo "Processing $ENTRANCE_SCRIPTS"
    # your code goes here
done

exit;

#$ENTRANCE_SCRIPTS > test.txttt
exit;

#ENTRANCE_FILES=$( find

for D in $(find ./projects/* -maxdepth 0 -mindepth 0 -type d); do
    if [ -d "${D}" ]; then
		#fName=$(basename ${D})
		opFile="${D}/entrance.sh"
		if [ -f "${opFile}" ]; then
		    echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Started with '${opFile}'";
		    chmod 774 $opFile
			bash $opFile
			echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Finished with '${opFile}'";
		fi
    fi
done

echo -e "\n STEP1.DOWNLOAD: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
