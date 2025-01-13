<?php

/*

    shared/words.php - predefined words used for in the backend and frontend as code id
    ----------------

    all words must always be owned by an administrator so that the standard cannot be renamed


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared;

class words
{

    /*
     * config
     */

    // words used in the frontend and backend for the system configuration
    // code_id and name of a words used by the system for its own configuration
    // e.g. the number of decimal places related to the user specific words
    // system configuration that are core for the database setup and update check are using the flat cfg methods
    // *_COM is the tooltip for the word; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    // this list is included in all preserved word names

    // keyword to select the system configuration
    const THIS_SYSTEM = 'zukunft.com';
    const SYSTEM = 'system';
    const CONFIGURATION = 'configuration';
    const SYSTEM_CONFIG = 'system configuration';
    const SYSTEM_CONFIG_ID = 73;

    // words used to select parts of the system configuration where the normal name should not be changed
    const TOOLTIP_COMMENT_COM = 'keyword to read the word or triple description from the config.yaml';
    const TOOLTIP_COMMENT = 'tooltip-comment';
    const SYS_CONF_VALUE_COM = 'keyword to read the numeric value from the config.yaml';
    const SYS_CONF_VALUE = 'sys-conf-value';

    // for the system setup and all pods of zukunft.com
    const POD = 'pod';
    const POD_ID = 298;
    const MASTER_POD_NAME = 'zukunft.com';
    const MASTER_POD_NAME_ID = 314;
    const JOB = 'job';
    const USER = 'user';
    const FRONTEND = 'frontend';
    const BACKEND = 'backend';

    // e.g. one instance / pod of zukunft.com
    const URL = 'url';
    const URL_ID = 309;
    // e.g. the launch date of the first beta version of zukunft.com
    const LAUNCH = 'launch';
    const LAUNCH_ID = 309;

    // for the user settings
    const ROW = 'row';
    const LIMIT = 'limit';
    const WORD = 'word';
    const CHANGES = 'changes';
    const PERCENT = 'percent';
    const DECIMAL = 'decimal';
    // e.g. the geolocation of the development of zukunft.com
    const POINT = 'point';
    const POINT_ID = 243;

    // general words used also for the system configuration that have a fixed tooltip
    const TIME = 'time';
    const TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const YEAR = 'year';
    const YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const CALCULATION = 'calculation';
    const CALCULATION_COM = 'A calculation is a deliberate mathematical process that transforms one or more inputs into one or more outputs or results';
    const MIN = 'min';
    const MIN_COM = 'The minimal numeric value.';
    const MAX = 'max';
    const MAX_COM = 'The maximal numeric value.';
    const AVERAGE = 'average';
    const AVERAGE_COM = 'The arithmetic mean – the sum of the numbers divided by how many numbers are in the list.';
    const DEFAULT = 'default';
    const DEFAULT_COM = 'The setting used if nothing else is specified.';
    const DATABASE = 'database';
    const DATABASE_COM = 'An organized collection of data stored and accessed electronically.';

    // general words used also for the system configuration where the initial tooltip is in the config.yaml
    const VALUE = 'value';
    const VERSION = 'version';
    const RETRY = 'retry';
    const START = 'start';
    const DELAY = 'delay';
    const SEC = 'sec';
    const BLOCK = 'block';
    const SIZE = 'size';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const TABLE = 'table';
    const NAME = 'name';
    const PHRASE = 'phrase';
    const MILLISECOND = 'millisecond';
    const SELECT = 'select';
    const INITIAL = 'initial';
    const ENTRY = 'entry';
    const PRESELECT = 'preselect';
    const FUTURE = 'future';
    const COLUMNS = 'columns';
    const AUTOMATIC = 'automatic';
    const CREATE = 'create';
    const VIEW = 'view';
    const FREEZE = 'freeze';
    const CHANGE = 'change';
    const DAILY = 'daily';
    const IP = 'ip';

    // for the configuration of a single job
    // TODO complete the concrete setup
    const IMPORT_TYPE = 'import type';
    const API_WORD = 'API';
    // to group the user data and configuration within the system configuration
    const PASSWORD = 'password';
    const OPEN_API = 'OpenAPI';
    const DEFINITION = 'definition';

}
