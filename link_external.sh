#!/bin/bash

# must be in current project lib external directory
# e.g. /home/user/PhpProjects/zukunft.com/lib_external/
# prepare once with: chmod 777 link_external.sh
CURRENT_DIR=$(pwd)

if [[ "$CURRENT_DIR" == */zukunft.com ]]; then
  mkdir lib_external
  cd lib_external || exit
  ZUKUNFT_LIB_DIR=$(pwd)

    echo -e "\n${GREEN}Linking bootstrap ...${NC}"
    # init bootstrap repository
    #git clone --filter=blob:none --no-checkout https://github.com/twbs/bootstrap.git bootstrap/
    git clone --filter=blob:none --no-checkout https://github.com/twbs/bootstrap.git "$ZUKUNFT_LIB_DIR/bootstrap/"

    # goto folder
    cd bootstrap || exit

    # activate sparse-checkout
    git sparse-checkout init --cone

    # get only the used dist/css and dist/js folder
    git sparse-checkout set dist/css dist/js

    # run check
    git checkout main

    # go back to lib_external
    cd "$ZUKUNFT_LIB_DIR" || exit

    echo -e "\n${GREEN}Linking fontawesome ...${NC}"
    #git clone https://github.com/FortAwesome/Font-Awesome fontawesome/
    git clone --filter=blob:none --no-checkout https://github.com/FortAwesome/Font-Awesome "$ZUKUNFT_LIB_DIR/fontawesome/"

    # goto folder
    cd fontawesome || exit

    # activate sparse-checkout
    git sparse-checkout init --cone

    # get only the used css and js folder
    git sparse-checkout set css js

    # run check
    git checkout master

else
    echo "must be in zukunft.com/ to link / clone the external libraries"
fi

# go back to original dir
cd "$CURRENT_DIR" || exit
