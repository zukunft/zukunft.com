#!/bin/bash

# ------------------------------------------
# zukunft.com pod install script
# for direct installation on a debian system
# ------------------------------------------

# Color variables
RED="\033[0;31m"
GREEN="\033[0;32m"
NC="\033[0m"


# ------------------------------------------
##  START Main
# ------------------------------------------
main() {
    rootCheck

    # Set current directory
    CURRENT_DIR=$(pwd)

    cd zukunft.com || exit

    displayIntro
    parseArguments "$@"
    initEnvironment
    readVar

    checkEnv
    checkOs
    checkDb

    if [[ "$OS" == "debian" ]]; then
        updateDebian
        installAndConfigurePostgresql
        installAndConfigureApache
        installAndConfigurePhp
        downloadZukunft
        downloadAndInstallExternalLibraries
        installZukunft
        testZukunft
        #testInstallation
    else
        if [[ "$OS" == "docker" ]]; then
            installZukunftInDocker
            #testInstallation
        fi
    fi

}
# ------------------------------------------
# END Main
# ------------------------------------------


# ------------------------------------------
# START Utilities
# ------------------------------------------

# Check if the script is run as root or user with superuser privilege
rootCheck() {
    ROOT_UID=0

    if [ "$UID" -ne "$ROOT_UID" ]; then
        echo "Sorry must be in root to run this script"
        exit 65
    fi
}

displayIntro() {
    clear ">$(tty)"

    # Initial prompt
    echo -e "${GREEN}ZUKUNFT INSTALLER${NC}"
    printf "\n"
    echo "This script will install a debian based LAPP stack and a zukunft.com pod"
    printf "\n"
    echo "and it will recreated the zukunft database if it exists"
    printf "\n\n"
    read -rp "Press enter to continue or CTRL+C to exit and keep the database named zukunft"
}

# Parse arguments
parseArguments() {
    while [[ "$#" -gt 0 ]]; do
        case $1 in
            --os=*) OS="${1#*=}" ;;
            --os) OS="$2"; shift ;;
            *) echo "Updating with default environment";;
        esac
        shift
    done
}

initEnvironment() {
    # TODO modify to environment base on the given parameters e.g. -docker
    echo -e "\n${GREEN}Create environment...${NC}"
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
            echo -e "\n${GREEN}Environment created ...${NC}"
        else
            echo -e "\n${RED}Sample environment .env.example file does not exist.${NC}"
        fi
    else
        echo -e "\n${GREEN}Environment already exists.${NC}"
    fi
    sleep 3
}

readVar() {
    set -o allexport; source .env; set +o allexport
}

checkEnv() {
    # reject unexpected environments
    if [[ "$ENV" == "prod" ]]; then
        echo -e "${GREEN}install a production instance of zukunft.com${NC}"
        if [[ "$BRANCH" != "master" ]]; then
            echo -e "${RED}branch $BRANCH not expected for a production instance${NC}"
        fi
    else
        if [[ "$ENV" == "test" ]]; then
            echo -e "${GREEN}install a zukunft.com for user acceptance testing${NC}"
            if [[ "$BRANCH" != "release" ]]; then
               echo -e "${RED}branch $BRANCH not expected for a production instance${NC}"
            fi
        else
            if [[ "$ENV" == "dev" ]]; then
                echo -e "${GREEN}install a zukunft.com for development${NC}"
                if [[ "$BRANCH" != "develop" ]]; then
                    echo -e "${RED}branch $BRANCH not expected for a production instance${NC}"
                fi
            else
                echo -e "\n${RED}environment $ENV not yet supported by zukunft.com${NC}"
            fi
        fi
    fi
}

checkOs() {
    # reject unexpected operating systems
    if [[ "$OS" == "debian" ]]; then
        echo -e "${GREEN}install on debian${NC}"
    else
        if [[ "$OS" == "docker" ]]; then
            echo -e "${GREEN}install using docker${NC}"
        else
            echo -e "\n${RED}install for $OS not yet possible.${NC}"
        fi
    fi
}

checkDb() {
    # reject unexpected operating systems
    if [[ "$DB" == "postgres" ]]; then
        echo -e "${GREEN}using postgres database${NC}"
    else
        if [[ "$DB" == "mysql" ]]; then
        echo -e "${GREEN}using mysql database${NC}"
        else
            echo -e "\n${RED}database $DB not yet possible.${NC}"
        fi
    fi
}

# TODO add other linux distributions such as Fedora
updateDebian() {
    echo -e "\n${GREEN}Updating debian...${NC}"

    # Update Debian
    apt-get update && apt-get upgrade

    # make sure that git is installed
    apt-get install -y git
}

installAndConfigurePostgresql() {
    echo -e "\n${GREEN}Installing postgres ...${NC}"

    # Install postgres
    # TODO check if postgres is already installed and if yes request the user and password once to create a zukunft user and a db
    apt-get install -y postgresql postgresql-contrib

    # Initialize database
    # TODO if no password is given just create on and write it to the .env secrets
    # TODO use the generated or give db password in the php code
    # TODO add postgres admin username and password if postgres is ready running and the standard user name is changed
    # TODO secure the standard postgres user name after install
    # create the user
    sudo -u postgres psql -d postgres -U postgres -c "CREATE USER $PGSQL_USERNAME WITH PASSWORD '$PGSQL_PASSWORD';"
    # drop any old database with the same name
    sudo -u postgres psql -d postgres -U postgres -c "DROP DATABASE $PGSQL_DATABASE"
    # create the database
    sudo -u postgres psql -d postgres -U postgres -c "CREATE DATABASE $PGSQL_DATABASE WITH OWNER $PGSQL_USERNAME ENCODING 'UTF8';"
    # TODO if the database existed change the owner of the tables or drop all tables

    echo -e "Installed postgres: \n$(psql --version)"

    sleep 3
}

# TODO add a nginx based installation
installAndConfigureApache() {
    echo -e "\n${GREEN}Installing Apache...${NC}"

    # Install Apache
    apt-get install -y apache2

    systemctl enable apache2
    systemctl start apache2
}

installAndConfigurePhp() {
    echo -e "\n${GREEN}Installing PHP ...${NC}"

    # Install PHP
    apt-get install -y php
    apt-get install -y php-pgsql
    apt-get install -y php-yaml
    apt-get install -y php-curl
    apt-get install -y php-xml
    apt-get install -y php-json

    PHP_VERSION=$(php -r 'echo PHP_VERSION;' | cut -d. -f1,2)
    if [[ "$PHP_VERSION" != "8.2" ]]; then
        echo -e "${RED}PHP 8.2 is required, found $PHP_VERSION${NC}"
        exit 1
    fi
    echo -e "Installed PHP: \n$(php --version)"
    sleep 3
}

downloadZukunft() {
    echo -e "\n${GREEN}Download selected zukunft.com branch ...${NC}"

    # switch later to something like git://git.zukunft.com/zukunft.git
    git clone -b "$BRANCH" https://github.com/zukunft/zukunft.com "$WWW_ROOT/"
    # copy the .env file to the webserver
    cp "$CURRENT_DIR/zukunft.com/.env" "$WWW_ROOT/"

}

downloadAndInstallExternalLibraries() {
    echo -e "\n${GREEN}Installing external libraries ...${NC}"

    echo -e "\n${GREEN}Installing bootstrap ...${NC}"
    git clone https://github.com/twbs/bootstrap.git "$WWW_ROOT/lib_external/bootstrap/4.1.3/"

    echo -e "\n${GREEN}Installing fontawesome ...${NC}"
    git clone https://github.com/gabrielelana/awesome-terminal-fonts "$WWW_ROOT/lib_external/fontawesome/"
    sleep 3
}

installZukunft() {
    echo -e "\n${GREEN}Installing zukunft.com ...${NC}"

    # force to reread to www root ?
    systemctl restart apache2

    # create the zukunft.com database tables
    php "$WWW_ROOT/test/reset_db.php"

    # TODO check result and create warning if it does not end with
    # TODO fix the errors on the first run that are caused e.g. by the missing db rows
    #      0 test errors
    #      0 internal errors

    cd "$CURRENT_DIR" || exit

    # TODO maybe remove to git clone in the local folder to avoid confusion
    #      this maybe depending on the update and upgrade process
    #      e.g. if this is done via git clone to the webserver folder
    #      and how the .env file can be kept
    rm -rf zukunft.com

    sleep 3
}

testZukunft() {
    echo -e "\n${GREEN}Test zukunft.com ...${NC}"

    # test the zukunft.com
    php "$WWW_ROOT/test/test.php"

    # TODO check result and create warning if it does not end with
    #      0 test errors
    #      0 internal errors

    # TODO if ENV is prod, remove the test script to avoid database reset by mistake
    # TODO if ENV is release add und use a script to clone the database from prod

    sleep 3
}

installZukunftInDocker() {
    echo -e "\n${GREEN}Installing zukunft.com in docker ...${NC}"

    # switch later to something like git://git.zukunft.com/zukunft.git
    git clone -b "$BRANCH" https://github.com/zukunft/zukunft.com "$WWW_ROOT/"

    cd "$WWW_ROOT" || exit

    echo -e "\n${GREEN}Building docker images ...${NC}"

    docker compose up -d --build

    echo -e "\n${GREEN}Resetting database ...${NC}"

    docker compose exec app php test/reset_db.php

}

# ------------------------------------------
# END Utilities
# ------------------------------------------

main "$@"; exit
