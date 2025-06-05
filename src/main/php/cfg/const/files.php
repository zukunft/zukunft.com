<?php

/*

    model/const/files.php - resource file names used in the backend
    ---------------------


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\const;

class files
{

    /*
     * types and extensions
     */

    CONST JSON = '.json';
    CONST YAML = '.yaml';
    const CODE_LINK_TYPE = '.csv';


    /*
     * path
     */

    const RESOURCE_PATH = MAIN_PATH . 'resources' . DIRECTORY_SEPARATOR;
    const MESSAGE_PATH = self::RESOURCE_PATH . 'messages' . DIRECTORY_SEPARATOR;
    const DB_PATH = self::RESOURCE_PATH . 'db' . DIRECTORY_SEPARATOR;
    const DB_UPGRADE_PATH = self::DB_PATH . 'upgrade' . DIRECTORY_SEPARATOR;
    const DB_UPGRADE_V003_PATH = self::DB_UPGRADE_PATH . 'v0.0.3' . DIRECTORY_SEPARATOR;

    // TODO make the csv file list based on the class name
    const CODE_LINK_PATH = self::RESOURCE_PATH . 'db_code_links' . DIRECTORY_SEPARATOR;


    /*
     * system config
     */

    // the system users as a zukunft.com user import json
    const SYSTEM_USERS = self::RESOURCE_PATH . 'users' . self::JSON;

    // the default system config as a yaml including the pod and the user frontend config
    const SYSTEM_CONFIG = self::RESOURCE_PATH . 'config' . self::YAML;

    // the system views as a zukunft.com user import json
    const SYSTEM_VIEWS_FILE = 'system_views' . self::JSON;
    const SYSTEM_VIEWS = self::MESSAGE_PATH . self::SYSTEM_VIEWS_FILE;
    const TRANSLATION_PATH = self::RESOURCE_PATH . 'translations' . DIRECTORY_SEPARATOR;

    // initial configuration of some views that the user can change
    const BASE_VIEWS_FILE = 'base_views' . self::JSON;


    /*
     * initial pod data
     */

    // the initial verbs as a zukunft.com verb import json
    const VERBS = self::RESOURCE_PATH . 'verbs' . self::JSON;


    // the initial list of blocked ip addresses
    const IP_BLACKLIST_FILE = 'ip_blacklist' . self::JSON;

    // sources used for the initial pod setup and for system testing
    const SOURCES_FILE = 'sources' . self::JSON;

    // some basic units e.g. kilogram
    const UNITS_FILE = 'units' . self::JSON;

    // some basic scaling formulas e.g. to scale millions to one
    const SCALING_FILE = 'scaling' . self::JSON;

    // some basic time definition e.g. years
    const TIME_FILE = 'time_definition' . self::JSON;

    // data for the default start page
    const START_PAGE_DATA_FILE = 'solution_prio' . self::JSON;

    // initial data just to add some sample data and for system testing
    const COUNTRY_FILE = 'country' . self::JSON;
    const COMPANY_FILE = 'company' . self::JSON;


    /*
     * file lists
     */

    // to load the default data for a pod
    const BASE_CONFIG_FILES = [
        self::SYSTEM_VIEWS_FILE,
        self::SOURCES_FILE,
        self::UNITS_FILE,
        self::SCALING_FILE,
        self::IP_BLACKLIST_FILE,
        self::TIME_FILE,
    ];

    // to load the default data for all pods
    const BASE_CONFIG_FILES_DIRECT = [
        self::BASE_VIEWS_FILE,
        self::START_PAGE_DATA_FILE,
        self::COMPANY_FILE,
        self::COUNTRY_FILE,
    ];

    // to load the default data for a standard pod
    const POD_CONFIG_FILES_DIRECT = [
        self::IP_BLACKLIST_FILE,
        self::BASE_VIEWS_FILE,
        self::COUNTRY_FILE,
        self::START_PAGE_DATA_FILE,
        self::COMPANY_FILE
    ];

}
