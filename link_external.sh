#!/bin/bash

# must be in current project lib external directory
# e.g. /home/user/PhpProjects/zukunft.com/lib_external/
# prepare once with: chmod 777 link_external.sh
CURRENT_DIR=$(pwd)

if [[ "$CURRENT_DIR" == */zukunft.com ]]; then

    # remove to existing folder to clear all existing files
    rm -rf lib_external

    # recreate the external library folder
    mkdir lib_external

    # remember the lib folder
    cd lib_external || exit
    ZUKUNFT_LIB_DIR=$(pwd)

    # create a git folder for the external libraries
    # on the same level as the project folder to clone the external project
    cd "$CURRENT_DIR" || exit
    cd .. || exit
    # remove to existing folder to clear all existing files
    rm -rf zukunft_external_lib_link
    # recreate a git folder for the external libraries
    mkdir zukunft_external_lib_link
    cd zukunft_external_lib_link || exit
    LIB_LINK_DIR=$(pwd)

    echo -e "\n${GREEN}Linking bootstrap ...${NC}"
    git clone https://github.com/twbs/bootstrap.git "$LIB_LINK_DIR/bootstrap/"

    echo -e "\n${GREEN}Linking fontawesome ...${NC}"
    git clone https://github.com/FortAwesome/Font-Awesome "$LIB_LINK_DIR/fontawesome/"

    # copy the relevant parts of the bootstrap
    rsync -av --delete "$LIB_LINK_DIR/bootstrap/dist/css" "$ZUKUNFT_LIB_DIR/bootstrap/"
    rsync -av --delete "$LIB_LINK_DIR/bootstrap/dist/js" "$ZUKUNFT_LIB_DIR/bootstrap/"

    # copy the relevant parts of the bootstrap
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/css" "$ZUKUNFT_LIB_DIR/fontawesome/"
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/js" "$ZUKUNFT_LIB_DIR/fontawesome/"
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/webfonts" "$ZUKUNFT_LIB_DIR/fontawesome/"

    # include the external libraries into this git repository
    cd "$ZUKUNFT_LIB_DIR" || exit
    git add --all

else
    echo "must be in zukunft.com/ to link / clone the external libraries"
fi

# go back to original dir
cd "$CURRENT_DIR" || exit
