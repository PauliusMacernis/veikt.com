#!/usr/bin/env bash
id

# Production vs. local: if php.ini file exists then it is production
PHP_CONFIG_FILE_PRODUCTION_ENV="/home/antdelno/php_extensions/php.ini"
if [ -f $PHP_CONFIG_FILE_PRODUCTION_ENV ];
then
   PHP_NAME="php-cli"
else
   PHP_NAME="php"
fi
export PHP_NAME


echo -e "\n NORMALIZE: Started: $(date +%Y-%m-%d:%H:%M:%S)"


# Run entrance.sh scripts of ./projects/{project_name} subdirectories

UNIQUE_ID_ASSIGNED_FOR_A_QUEUE=$("$PHP_NAME" -r "echo uniqid('main_', true);")
# If UNIQUE_ID_ASSIGNED_FOR_A_QUEUE is empty then throw the error, log and do not continue. It is the serious error..
if [ -z $UNIQUE_ID_ASSIGNED_FOR_A_QUEUE ];
    then
        echo "Error! Unique id is not assigned to the queue. This is serious error. Is PHP working? Cannot continue." 1>&2
        exit 2
fi

# - Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# - Get entrance.sh scripts of available projects
DIRS_FOR_CONTENT=$("$PHP_NAME" -r "
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

MARKER_FILENAME_BEGIN=$("$PHP_NAME" -r "
        \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
        echo \$settings['markers']['begin']['file-name'];
")
MARKER_FILENAME_CONTENT=$("$PHP_NAME" -r "
        \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
        echo \$settings['markers']['content']['file-name'];
")
MARKER_FILENAME_END=$("$PHP_NAME" -r "
        \$settings = json_decode(file_get_contents('$DIR' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'settings.json'), true);
        echo \$settings['markers']['end']['file-name'];
")

export MARKER_FILENAME_BEGIN
export MARKER_FILENAME_CONTENT
export MARKER_FILENAME_END


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

    PROJECT_DIRS_TO_NORMALIZE=$(find $POSTS_DIR -maxdepth 1 -type d -exec bash -c '[[ -e $1/$MARKER_FILENAME_BEGIN && -e $1/$MARKER_FILENAME_CONTENT && -e $1/$MARKER_FILENAME_END && $(head -n 1 $1/$MARKER_FILENAME_BEGIN) == $(head -n 1 $1/$MARKER_FILENAME_END) ]]' bash {} \; -printf '%T@ %p\n' | sort -k1 -n -z | awk '{ print $2 "\\n" }')

    # Read DataIntegrity.log found in each directory.
    echo -e $PROJECT_DIRS_TO_NORMALIZE |
    while IFS="\\n" read -r PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE; do

        # Trim the variable
        PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE=${PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE//[[:space:]]/}
        if [[ -z "${PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE// }" ]]
        then
            # Skip any empty value...
            continue
        fi


        if [ -z "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE/DataIntegrity.log" ];
        then
            echo -e "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE/DataIntegrity.log is not found." 1>&2
            continue
        fi

        # Read each result line (directory found)
        while IFS='' read -r PROJECT_DIR_TO_NORMALIZE || [[ -n "$PROJECT_DIR_TO_NORMALIZE" ]]; do
            if [ -z "$PROJECT_DIR_TO_NORMALIZE" ];
            then
                echo -e "$PROJECT_DIR_TO_NORMALIZE is not found." 1>&2
                continue
            fi

            # Apply (PHP) action
            chmod 774 $ENTRANCE_SH_FILE
            bash "$ENTRANCE_SH_FILE" "$PROJECT_DIR_TO_NORMALIZE" "$UNIQUE_ID_ASSIGNED_FOR_A_QUEUE"

        done < "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_CONTENT"

        # Remove $MARKER_FILENAME_BEGIN file
        if [ -f "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_BEGIN" ] && [ $(ls "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE" -1 | wc -l) == 3 ] ; then
            rm "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_BEGIN"
        fi

        # Remove $MARKER_FILENAME_END file
        if [ -f "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_END" ] && [ $(ls "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE" -1 | wc -l) == 2 ] ; then
            rm "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_END"
        fi

        # Remove $MARKER_FILENAME_CONTENT file as we red it already
        if [ -f "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_CONTENT" ] && [ $(ls "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE" -1 | wc -l) == 1 ] ; then
            rm "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"/"$MARKER_FILENAME_CONTENT"
        fi

        # Remove directory containing once downloaded data as we red it already
        if [ -d "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE" ] ; then
            rmdir "$PROJECT_DIRS_TO_NORMALIZE_WITH_LIST_FILE"
        fi

        echo -e ""

    done

done

unset MARKER_FILENAME_BEGIN
unset MARKER_FILENAME_CONTENT
unset MARKER_FILENAME_END

unset PHP_NAME


# Apply (PHP) action - Publicize
PUBLICIZE_MAIN_SH_FILE="$DIR"/../publicize/main.sh
chmod 774 "$PUBLICIZE_MAIN_SH_FILE"
bash "$PUBLICIZE_MAIN_SH_FILE"
# END. Apply (PHP) action - Publicize

echo -e "\n NORMALIZE: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
