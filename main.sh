#!/usr/bin/env bash
id
date
bash --version
php -v
git --version

echo -e "\n Started: $(date +%Y-%m-%d:%H:%M:%S)"

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# The php file this script should run
MAIN_DOWNLOAD="download/main.sh"
MAIN_NORMALIZE="normalize/main.sh"
MAIN_PUBLICIZE="publicize/main.sh"

# Chmod all important
chmod 774 "$DIR/$MAIN_DOWNLOAD"
chmod 774 "$DIR/$MAIN_NORMALIZE"
# Publicize is moved to inside of MAIN_NORMALIZE
# chmod 774 "$DIR/$MAIN_PUBLICIZE"

# Run all
bash "$DIR/$MAIN_DOWNLOAD"
bash "$DIR/$MAIN_NORMALIZE"
# Publicize is moved to inside of MAIN_NORMALIZE
# "$DIR/$MAIN_PUBLICIZE"


echo -e "\n Finished: $(date +%Y-%m-%d:%H:%M:%S)"
