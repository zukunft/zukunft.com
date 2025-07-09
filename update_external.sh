#!/bin/bash


# must be in current project directory
# e.g. /home/user/PhpProjects/zukunft.com
# prepare once with: chmod 777 update_external.sh
CURRENT_DIR=$(pwd)
cd lib_external || exit
ZUKUNFT_LIB_DIR=$(pwd)

if [[ "$ZUKUNFT_LIB_DIR" == */zukunft.com/lib_external ]]; then
    echo -e "\n${GREEN}Updating bootstrap ...${NC}"
    cd bootstrap || exit
    git pull

    # go back to lib_external
    cd "$ZUKUNFT_LIB_DIR" || exit

    echo -e "\n${GREEN}Updating fontawesome ...${NC}"
    cd fontawesome || exit
    git pull

else
    echo "folder zukunft.com/lib_external/ not found in current directory"
fi

# go back to original dir
cd "$CURRENT_DIR" || exit

