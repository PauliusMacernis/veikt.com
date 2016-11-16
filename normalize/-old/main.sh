#!/usr/bin/env bash

# Run entrance.sh scripts of ./projects/{project_name} subdirectories

echo -e "\n STEP2.NORMALIZE: Started: $(date +%Y-%m-%d:%H:%M:%S)"

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

echo -e "\n STEP2.NORMALIZE: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
