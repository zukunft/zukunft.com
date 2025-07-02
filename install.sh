#!/bin/bash

# ------------------------------------------
# zukunft.com pod install script
# for direct installation on a debian system
# ------------------------------------------

# Color variables
RED="\033[0;31m"
GREEN="\033[0;32m"
NC="\033[0m"
PGSQL_USERNAME=zukunft_db_root
PGSQL_PASSWORD=mysecretpassword
PGSQL_DATABASE=zukunft
WWW_ROOT=/var/www/html
LOCAL_USER=${SUDO_USER:-$USER}

# ------------------------------------------
##  START Main
# ------------------------------------------
main() {
    #rootCheck

    # Set current directory
    CURRENT_DIR=$(pwd)

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
        downloadAndInstallZukunft
        downloadAndInstallExternalLibraries
        runZukunftApp
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
    clear > "$(tty)"

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
    sudo apt-get update -y && sudo apt-get upgrade -y

    # make sure that git is installed
    sudo apt-get install -y git
}

installAndConfigurePostgresql() {
    echo -e "\n${GREEN}Installing postgres ...${NC}"

    # Install postgres
    # TODO check if postgres is already installed and if yes request the user and password once to create a zukunft user and a db
    sudo apt-get update && sudo apt-get install -y postgresql postgresql-contrib
    sudo systemctl start postgresql
    sudo systemctl enable postgresql

    # Create user if not exists
    sudo -u postgres psql -c "DO \$\$
    BEGIN
       IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '$PGSQL_USERNAME') THEN
           CREATE ROLE $PGSQL_USERNAME LOGIN PASSWORD '$PGSQL_PASSWORD';
       END IF;
    END
    \$\$;"

    # Initialize database
    # TODO if no password is given just create on and write it to the .env secrets
    # TODO use the generated or give db password in the php code
    # TODO add postgres admin username and password if postgres is ready running and the standard user name is changed
    # TODO secure the standard postgres user name after install
    
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
    sudo apt-get install -y apache2

    sudo systemctl enable apache2
    sudo systemctl start apache2
}

installAndConfigurePhp() {
    echo -e "\n${GREEN}Installing PHP ...${NC}"

    # Install PHP
    sudo apt-get install -y php
    sudo apt-get install -y php-pgsql
    sudo apt-get install -y php-yaml
    sudo apt-get install -y php-curl
    sudo apt-get install -y php-xml
    sudo apt-get install -y php-json

    PHP_VERSION=$(php -r 'echo PHP_VERSION;' | cut -d. -f1,2)
    #if [[ "$PHP_VERSION" != "8.2" ]]; then
    #    echo -e "${RED}PHP 8.2 is required, found $PHP_VERSION${NC}"
    #    exit 1
    #fi
    echo -e "Installed PHP: \n$(php --version)"
    sleep 3
}

downloadAndInstallExternalLibraries() {
    echo -e "\n${GREEN}Installing external libraries ...${NC}"

    cd $WWW_ROOT

    echo -e "\n${GREEN}Installing bootstrap ...${NC}"
    git clone --branch v4.1.3 --depth 1 https://github.com/twbs/bootstrap.git
    mkdir -p "$WWW_ROOT/lib_external/bootstrap/4.1.3/"
    rsync -av --delete bootstrap/ "$WWW_ROOT/lib_external/bootstrap/4.1.3/"

    echo -e "\n${GREEN}Installing fontawesome ...${NC}"
    git clone https://github.com/gabrielelana/awesome-terminal-fonts
    mkdir -p "$WWW_ROOT/lib_external/fontawesome/"
    rsync -av --delete awesome-terminal-fonts/ "$WWW_ROOT/lib_external/fontawesome/"
    sleep 3
}

downloadAndInstallZukunft() {
    echo -e "\n${GREEN}Installing zukunft.com ...${NC}"
    sudo mkdir -p $WWW_ROOT
    
    sudo chown -R "$LOCAL_USER":"$LOCAL_USER" "$WWW_ROOT/"

    # switch later to something like git://git.zukunft.com/zukunft.git
    git clone -b "$BRANCH" https://github.com/zukunft/zukunft.com "$WWW_ROOT/"
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

runZukunftApp() {
    # force to reread to www root ?
    sudo systemctl restart apache2

    # create the zukunft.com database tables
    php "$WWW_ROOT/test/reset_db.php"

    # TODO check result and create warning if it does not end with
    #      0 test errors
    #      0 internal errors

    # test the zukunft.com
    php "$WWW_ROOT/test/test.php"

    # TODO check result and create warning if it does not end with
    #      0 test errors
    #      0 internal errors

    # TODO if ENV is prod, remove the test script to avoid database reset by mistake
    # TODO if ENV is release add und use a script to clone the database from prod

    cd "$CURRENT_DIR" || exit
    sleep 3
}

# ------------------------------------------
# END Utilities
# ------------------------------------------

main "$@"; exit
