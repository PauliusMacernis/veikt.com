#!/usr/bin/env bash

# Run entrance.sh scripts of ./projects/{project_name} subdirectories


echo -e "\n NORMALIZE: Started: $(date +%Y-%m-%d:%H:%M:%S)"

UNIQUE_ID_ASSIGNED_FOR_A_QUEUE=$(php -r "echo uniqid('main_', true);")
# If UNIQUE_ID_ASSIGNED_FOR_A_QUEUE is empty then throw the error, log and do not continue. It is the serious error..
if [ -z $UNIQUE_ID_ASSIGNED_FOR_A_QUEUE ];
    then
        echo "Error! Unique id is not assigned to the queue. This is serious error. Is PHP working? Cannot continue." 1>&2
        exit 2
fi

# Get list of directories containing any of required files
# - Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Get required files list
# @TODO: ? make content_static and content_dynamic coming from settings.json->files-to-output->file-is-created-by-default == 1
REQUIRED_FILES=$(php -r "
    \$separator = ':';
    \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
    \$filesToOutput = \$settings['files-to-output'];

    \$result = array();
    foreach(\$filesToOutput as \$filename => \$fileToOutput) {
        if(!\$fileToOutput['required']) {
            continue;
        }
        \$result[] = \$filename;
    }
    echo \"{}/\" . implode(\" -a -e {}/\", \$result); // Let's leave this logic for the project's code to deal with...
");
# Simmpler REQUIRED_FILES solution (uncomment for easier debugging)
#REQUIRED_FILES=$(php -r "
#    \$result = array(\"id\");
#    echo \"{}/\" . implode(\" -a -e {}/\", \$result); // Let's leave this logic for the project's code to deal with...
#");
#END. Simmpler solution (for easier debugging)

# Get entrance.sh scripts of available projects
DIRS_FOR_CONTENT=$(php -r "
    \$separator = ':';
    \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
    \$projectsOn = \$settings['projects-on'];
    \$dirsToSearchIn = array();
     foreach(\$projectsOn as \$projectsOnData) {

         // Skip the project that does not have the directory for downloaded posts set
         if(!isset(\$projectsOnData['dir_downloaded_posts'])) {
            continue;
         }

         // Skip the project that does not have the directory for downloaded posts set in filesystem
         \$dir = '$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . \$projectsOnData['dir_downloaded_posts'];
         if(!is_dir(\$dir)) {
            continue;
         }

         // Skip the project that does not have the entrance.sh script defined
         if(!isset(\$projectsOnData['entrance_sh_normalize'])) {
            continue;
         }

         // Skip the project that does not have the entrance.sh script in filesystem
         \$fileEntranceSh = '$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . \$projectsOnData['entrance_sh_normalize'];
         if(!is_file(\$fileEntranceSh)) {
            continue;
         }

         \$dirsToSearchIn[] = \$fileEntranceSh . \$separator . \$dir;

     }
     echo implode(\"\\\n\", \$dirsToSearchIn);
    ");

# Find all sub-dirs within dirs & run index script on every sub-dir
echo -e $DIRS_FOR_CONTENT |
while IFS= read -r ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED; do

    ENTRANCE_SH_FILE=$(echo $ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED | cut -f1 -d ":")
    # If ENTRANCE_SH_FILE is empty then read next ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED
    if [ -z $ENTRANCE_SH_FILE ];
        then
            echo -e "Error! ENTRANCE_SH_FILE is empty. This entry is being skipped.\n More info: POSTS_DIR: \"$POSTS_DIR\", UNIQUE_ID_ASSIGNED_FOR_A_QUEUE: \"$UNIQUE_ID_ASSIGNED_FOR_A_QUEUE\", ENTRANCE_SH_FILE: \"$ENTRANCE_SH_FILE\"" 1>&2
            continue
    fi

    POSTS_DIR=$(echo $ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED | cut -f2 -d ":")
    # If POSTS_DIR is empty then read next ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED
    if [ -z $POSTS_DIR ];
        then
            echo -e "POSTS_DIR is empty. This entry is being skipped.\n More info: POSTS_DIR: \"$POSTS_DIR\", UNIQUE_ID_ASSIGNED_FOR_A_QUEUE: \"$UNIQUE_ID_ASSIGNED_FOR_A_QUEUE\", ENTRANCE_SH_FILE: \"$ENTRANCE_SH_FILE\"" 1>&2
            continue
    fi

    PROJECT_DIRS_TO_NORMALIZE=$(find $POSTS_DIR -type d -exec test -e $REQUIRED_FILES \; -print)
    # If PROJECT_DIRS_TO_NORMALIZE is empty then read next ENTRANCE_SH_FILE__POSTS_DIR__DELIMITED
    if [ -z $PROJECT_DIRS_TO_NORMALIZE ];
        then
            echo -e "\"$POSTS_DIR\" do not have any of required files. No action taken." 1>&2
            continue
    fi

    echo -e "$PROJECT_DIRS_TO_NORMALIZE" | while IFS2= read -r PROJECT_DIR_TO_NORMALIZE; do

        # If PROJECT_DIR_TO_NORMALIZE is empty then read next PROJECT_DIR_TO_NORMALIZE
        if [ -z $PROJECT_DIR_TO_NORMALIZE ];
            then
                echo -e "PROJECT_DIR_TO_NORMALIZE is empty. No action taken.\n More info: POSTS_DIR: \"$POSTS_DIR\", UNIQUE_ID_ASSIGNED_FOR_A_QUEUE: \"$UNIQUE_ID_ASSIGNED_FOR_A_QUEUE\", ENTRANCE_SH_FILE: \"$ENTRANCE_SH_FILE\"" 1>&2
                continue
        fi

        chmod 774 $ENTRANCE_SH_FILE
        bash $ENTRANCE_SH_FILE "$PROJECT_DIR_TO_NORMALIZE" "$UNIQUE_ID_ASSIGNED_FOR_A_QUEUE"

    done
done


echo -e "\n NORMALIZE: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
