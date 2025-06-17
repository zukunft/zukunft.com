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

    displayIntro
    initEnvironment
    readVar

    checkOs
    checkEnv

    # TODO add docker version
    updateDebian
    installAndConfigurePostgresql
    installAndConfigureApache
    installAndConfigurePhp
    downloadAndInstallExternalLibraries
    downloadAndInstallZukunft
    testInstallation
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
    SUCCESS=0

    if [ "$UID" -ne "$ROOT_UID" ]; then
        echo "Sorry must be in root to run this script"
        exit 65
    fi
}

displayIntro() {
    clear >$(tty)

    # Initial prompt
    echo -e "${GREEN}ZUKUNFT INSTALLER${NC}"
    printf "\n"
    echo "This script will install a debian based LAPP stack and a zukunft.com pod"
    printf "\n\n"
    read -p "Press enter to continue or CTRL+C to exit"
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

checkEnv() {
    # reject unexpected environments
    if [[ "$ENV" == "prod" ]]; then
        echo -e "${GREEN}install a production instance of zukunft.com${NC}"
        if [[ "$ZUKUNFT_BRANCH" != "master" ]]; then
            echo -e "${RED}branch $ZUKUNFT_BRANCH not expected for a production instance${NC}"
        fi
    else
        if [[ "$ENV" == "test" ]]; then
            echo -e "${GREEN}install a zukunft.com for user acceptance testing${NC}"
            if [[ "$ZUKUNFT_BRANCH" != "release" ]]; then
               echo -e "${RED}branch $ZUKUNFT_BRANCH not expected for a production instance${NC}"
            fi
        else
            if [[ "$ENV" == "dev" ]]; then
                echo -e "${GREEN}install a zukunft.com for development${NC}"
                if [[ "$ZUKUNFT_BRANCH" != "develop" ]]; then
                    echo -e "${RED}branch $ZUKUNFT_BRANCH not expected for a production instance${NC}"
                fi
            else
                echo -e "\n${RED}environment $ENV not yet supported by zukunft.com${NC}"
            fi
        fi
    fi
}

# TODO add other linux distributions such as Fedora
updateDebian() {
    clear >$(tty)
    echo -e "\n${GREEN}Updating Debian...${NC}"

    # Update Debian
    apt-get update && apt-get upgrade

    # make sure that git is installed
    apt-get install -y git
}

installAndConfigurePostgresql() {
    clear >$(tty)
    echo -e "\n${GREEN}Installing PostgreSQL ...${NC}"

    # Install PostgreSQL
    # TODO check if postgres is already installed and if yes request the user and password once to create a zukunft user and a db
    apt-get install -y postgresql postgresql-contrib

    systemctl enable postgresql
    systemctl start postgresql

    # Backup pg_hba.conf
    PG_HBA=$(find /etc/postgresql/ -name pg_hba.conf | head -n 1)
    cp "$PG_HBA" "$PG_HBA.bak"
    chown postgres:postgres /var/lib/pgsql/data/pg_hba.conf.bak

    # Initialize database
    # TODO if no password is given just create on and write it to the .env secrets
    # TODO use the generated or give db password in the php code
    runuser -l postgres -c "psql -c \"CREATE USER $PGSQL_ZUKUNFT_USER WITH PASSWORD '$PGSQL_ZUKUNFT_USER_PASSWORD';\""
    runuser -l postgres -c "psql -c \"CREATE DATABASE $PGSQL_ZUKUNFT_DATABASE WITH OWNER $PGSQL_ZUKUNFT_USER ENCODING 'UTF8' LC_COLLATE='en_US.UTF-8' LC_CTYPE='en_US.UTF-8' TEMPLATE=template0;\""

    echo -e "Installed PostgreSQL: \n$(psql --version)"

    systemctl stop postgresql
    cat "$(pwd)/config/pg_hba.conf" > "$PG_HBA"
    systemctl start postgresql

    # rm /var/lib/pgsql/data/pg_hba.conf
    # mv $(pwd)/config/pg_hba.conf /var/lib/pgsql/data/pg_hba.conf
    # chown postgres:postgres /var/lib/pgsql/data/pg_hba.conf
    # chmod 600 /var/lib/pgsql/data/pg_hba.conf
    # systemctl restart postgresql
    sleep 3
}

# TODO add a nginx based installation
installAndConfigureApache() {
    clear >$(tty)
    echo -e "\n${GREEN}Installing Apache...${NC}"

    # Install Apache
    apt-get install -y apache2

    systemctl enable apache2
    systemctl start apache2
}

installAndConfigurePhp() {
    clear >$(tty)
    echo -e "\n${GREEN}Installing PHP ...${NC}"

    # Install PHP
    apt-get install -y php \
    php-pgsql php-yaml php-curl \
    php-spl php-xml php-json
    # check which might be needed
    # php-opcache php-gd php-mysqlnd php-mbstring \
    # php-openssl php-xmlrpc php-soap php-zip php-simplexml \
    # php-pcre php-dom php-intl php-json \
    # php-pdo_pgsql

    PHP_VERSION=$(php -r 'echo PHP_VERSION;' | cut -d. -f1,2)
    if [[ "$PHP_VERSION" != "8.2" ]]; then
        echo -e "${RED}PHP 8.2 is required, found $PHP_VERSION${NC}"
        exit 1
    fi
    echo -e "Installed PHP: \n$(php --version)"
    sleep 3
}

downloadAndInstallExternalLibraries() {
    clear >$(tty)
    echo -e "\n${GREEN}Installing external libraries ...${NC}"

    echo -e "\n${GREEN}Installing bootstrap ...${NC}"
    git clone https://github.com/twbs/bootstrap.git
    rsync -av --delete bootstrap/ "$WWW_ROOT/lib_external/bootstrap/4.1.3/"

    echo -e "\n${GREEN}Installing fontawesome ...${NC}"
    git clone https://github.com/gabrielelana/awesome-terminal-fonts
    rsync -av --delete awesome-terminal-fonts/ "$WWW_ROOT/lib_external/fontawesome/"
    sleep 3
}

downloadAndInstallZukunft() {
    clear >$(tty)
    echo -e "\n${GREEN}Installing zukunft.com ...${NC}"

    # switch later to something like git://git.zukunft.com/zukunft.git
    git clone -b $ZUKUNFT_BRANCH https://github.com/zukunft/zukunft.com
    rsync -avP $(pwd)/zukunft.com/ $WWW_ROOT/

    chown -R apache:apache $WWW_ROOT
    cd $WWW_ROOT/admin/cli

    # TODO check result and create warning if it does not end with
    #      0 test errors
    #      0 internal errors
    #      Process finished with exit code 0
    runuser -u apache $(which php) reset_db.php --

    # TODO if ENV is prod, remove the test script to avoid database reset by mistake
    # TODO if ENV is release add und use a script to clone the database from prod

    chown -R root:root $WWW_ROOT
    chmod -R 755 $WWW_ROOT

    systemctl restart postgresql
    systemctl restart httpd
    cd $CURRENT_DIR
    sleep 3
}

# ------------------------------------------
# END Utilities
# ------------------------------------------

main "$@"; exit
