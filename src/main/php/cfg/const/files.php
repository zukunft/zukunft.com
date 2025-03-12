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
    const SYSTEM_USERS = self::RESOURCE_PATH . 'users.json';

    // the default system config as a yaml including the pod and the user frontend config
    const SYSTEM_CONFIG = self::RESOURCE_PATH . 'config.yaml';

    // TODO deprecate: the default default system config as a json
    const SYSTEM_CONFIG_OLD = self::RESOURCE_PATH . 'config.json';

    // the system views as a zukunft.com user import json
    const SYSTEM_VIEWS_FILE = 'system_views.json';
    const SYSTEM_VIEWS = self::MESSAGE_PATH . self::SYSTEM_VIEWS_FILE;

    // initial configuration of some views that the user can change
    const BASE_VIEWS_FILE = 'base_views.json';


    /*
     * initial pod data
     */

    // the initial verbs as a zukunft.com verb import json
    const VERBS = self::RESOURCE_PATH . 'verbs.json';


    // the initial list of blocked ip addresses
    const IP_BLACKLIST_FILE = 'ip_blacklist.json';

    // sources used for the initial pod setup and for system testing
    const SOURCES_FILE = 'sources.json';

    // some basic units e.g. kilogram
    const UNITS_FILE = 'units.json';

    // some basic scaling formulas e.g. to scale millions to one
    const SCALING_FILE = 'scaling.json';

    // some basic time definition e.g. years
    const TIME_FILE = 'time_definition.json';

    // data for the default start page
    const START_PAGE_DATA_FILE = 'solution_prio.json';

    // initial data just to add some sample data and for system testing
    const COUNTRY_FILE = 'country.json';
    const COMPANY_FILE = 'company.json';


    /*
     * files types
     */

    const CODE_LINK_TYPE = '.csv';


    /*
     * file lists
     */

    // to load the default data for a pod
    const BASE_CONFIG_FILES = [
        self::SYSTEM_VIEWS_FILE,
        self::SOURCES_FILE,
        self::UNITS_FILE,
        self::SCALING_FILE,
        self::TIME_FILE,
        self::IP_BLACKLIST_FILE,
        self::BASE_VIEWS_FILE,
        self::COUNTRY_FILE,
        self::START_PAGE_DATA_FILE,
        self::COMPANY_FILE
    ];

    // to load the default data for a pod
    const BASE_CONFIG_FILES_DIRECT = [
        self::SYSTEM_VIEWS_FILE,
        self::SOURCES_FILE,
        self::UNITS_FILE,
        self::SCALING_FILE,
        self::TIME_FILE,
        self::IP_BLACKLIST_FILE,
        self::BASE_VIEWS_FILE,
        self::COUNTRY_FILE,
        self::START_PAGE_DATA_FILE,
        self::COMPANY_FILE
    ];

}
