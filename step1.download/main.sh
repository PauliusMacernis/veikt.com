#!/usr/bin/env bash

# Step1: Run scripts of ./step1.download/sources/ directory
for D in $(find ./step1.download/sources/* -type d); do
    if [ -d "${D}" ]; then
		#fName=$(basename ${D})
		opFile="${D}/entrance.sh"
		if [ -f "${opFile}" ]; then
			bash $opFile
		fi
    fi
done
