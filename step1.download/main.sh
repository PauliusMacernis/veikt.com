#!/usr/bin/env bash

# Run entrance.sh scripts of ./projects/{project_name} subdirectories

echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Started.";

for D in $(find ./projects/* -maxdepth 0 -mindepth 0 -type d); do
    if [ -d "${D}" ]; then
		#fName=$(basename ${D})
		opFile="${D}/entrance.sh"
		if [ -f "${opFile}" ]; then
		    echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Started with '${opFile}'";
			bash $opFile
			echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Finished with '${opFile}'";
		fi
    fi
done

echo -e "\n $(date +"%Y-%m-%d %I:%M:%S"): Finished.";
