#!/usr/bin/env bash


echo -e "\n Started: $(date +%Y-%m-%d:%H:%M:%S)"

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# The php file this script should run
MAIN_DOWNLOAD="download/main.sh"
MAIN_NORMALIZE="normalize/main.sh"
MAIN_PUBLICIZE="publicize/main.sh"

# Run all 
chmod 774 "$DIR/$MAIN_DOWNLOAD"
"$DIR/$MAIN_DOWNLOAD"
chmod 774 "$DIR/$MAIN_NORMALIZE"
"$DIR/$MAIN_NORMALIZE"

# Publicize is moved to inside of MAIN_NORMALIZE
# chmod 774 "$DIR/$MAIN_PUBLICIZE"
# "$DIR/$MAIN_PUBLICIZE"


echo -e "\n Finished: $(date +%Y-%m-%d:%H:%M:%S)"
