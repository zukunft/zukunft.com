#!/bin/bash


# must be in current project directory
# e.g. /home/user/PhpProjects/zukunft.com
# prepare once with: chmod 777 update_external.sh
CURRENT_DIR=$(pwd)

if [[ "$CURRENT_DIR" == */zukunft.com ]]; then

    # remember the lib folder
    cd external_lib || exit
    ZUKUNFT_LIB_DIR=$(pwd)

    # go to the external library git directory
    cd "$CURRENT_DIR" || exit
    cd .. || exit
    cd zukunft_external_lib_link || exit
    LIB_LINK_DIR=$(pwd)

    echo -e "\n${GREEN}Updating bootstrap ...${NC}"
    cd bootstrap || exit
    git pull

    # go back to external_lib
    cd "$LIB_LINK_DIR" || exit

    echo -e "\n${GREEN}Updating fontawesome ...${NC}"
    cd fontawesome || exit
    git pull

    # copy the relevant parts of the bootstrap
    rsync -av --delete "$LIB_LINK_DIR/bootstrap/dist/css" "$ZUKUNFT_LIB_DIR/bootstrap/"
    rsync -av --delete "$LIB_LINK_DIR/bootstrap/dist/js" "$ZUKUNFT_LIB_DIR/bootstrap/"

    # copy the relevant parts of the bootstrap
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/css" "$ZUKUNFT_LIB_DIR/fontawesome/"
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/js" "$ZUKUNFT_LIB_DIR/fontawesome/"
    rsync -av --delete "$LIB_LINK_DIR/fontawesome/webfonts" "$ZUKUNFT_LIB_DIR/fontawesome/"

else
    echo "folder zukunft.com/external_lib/ not found in current directory"
fi

# go back to original dir
cd "$CURRENT_DIR" || exit

