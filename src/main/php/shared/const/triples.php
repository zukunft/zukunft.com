<?php

/*

    shared/const/triples.php - predefined triples used in the backend and frontend as code id
    ------------------------

    all preserved words must always be owned by an administrator so that the standard cannot be renamed

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

namespace Zukunft\ZukunftCom\main\php\shared\const;



use Zukunft\ZukunftCom\test\php\const\triple_names;

class triples
{

    // this list includes all preserved triple names
    // *_COM is the tooltip for the triple; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing

    // triple names used in the config.yaml (sorted alphabetically)
    const string API_USER = 'api user';
    const string AUTOMATIC_CREATE = 'automatic create';
    const string AVERAGE_DELAY = 'average delay';
    const string BLOCK_SIZE = 'block size';
    const string BYTES_PER_SECOND = 'bytes second';
    const string BY_IP_ADDRESS = 'by ip-address';
    const string BY_LOCATION = 'by location';
    const string CHECK_PERIOD = 'check period';
    const string DECREASE_DAYS = 'decrease days';
    const string EMAIL_SERVER = 'email server';
    const string EXPECTED_TIME = 'expected time';
    const string FACTOR_WRONG = 'factor wrong';
    const string FILE_READ = 'file read';
    const string FILE_SIZE = 'file size';
    const string FUTURE_PERCENT = 'future percent';
    const string INCREASE_LIST = 'increase list';
    const string IP_USER = 'ip user';
    const string LINK_LIST = 'link list';
    const string MAX_CHANGE = 'max change';
    const string MAX_CHANGES = 'max changes';
    const string MAX_COLUMNS = 'max columns';
    const string MAX_DELAY = 'max delay';
    const string MAX_LEVELS = 'max levels';
    const string MAX_LIFETIME = 'max lifetime';
    const string MAX_LOGIN = 'max login';
    const string MAX_PHRASE = 'max phrase';
    const string MAX_TABLES = 'max tables';
    const string MIN_COLUMNS = 'min columns';
    const string MIN_NAMES = 'min names';
    const string MIN_NUMBERS = 'min numbers';
    const string MIN_VALUES = 'min values';
    const string NAME_LIST = 'name list';
    const string NOT_TRUSTED = 'not trusted';
    const string NUMBER_FORMAT = 'number format';
    const string OBJECTS_PER_SECOND = 'objects second';
    const string OBJECT_CREATION = 'object creation';
    const string OBJECT_STORING = 'object storing';
    const string OUTPUT_FORMAT = 'output format';
    const string PERCENT_DECIMAL = 'percent decimal';
    const string PER_DAY = 'per day';
    const string PER_MONTH = 'per month';
    const string PER_VERB = 'per verb';
    const string PER_WEEK = 'per week';
    const string PER_YEAR = 'per year';
    const string PHRASE_DISTRIBUTION = 'phrase distribution';
    const string RESPONSE_TIME = 'response time';
    const string ROW_LIMIT = 'row limit';
    const string SIDE_WIDTH = 'side width';
    const string SOURCE_TABLE = 'source table';
    const string START_DELAY = 'start delay';
    const string STORAGE_SIZE = 'storage size';
    const string SYSTEM_CONFIG = 'system configuration';
    const int SYSTEM_CONFIG_ID = 97;
    const string SYSTEM_ERRORS = 'system errors';
    const string TABLE_NAME = 'table name';
    const string TIME_PERCENT = 'time percent';
    const string TOP_LEVEL = 'top level';
    const string VALUE_TABLE = 'value table';
    const string WEB_MOBILE = 'web mobile';
    const string WORD_CHANGES = 'word changes';

    const array BASE_TRIPLES = [
        [triple_names::MATH_CONST, triple_names::MATH_CONST_ID],
        [triple_names::CANTON_ZURICH, triple_names::CANTON_ZURICH_ID],
        [triple_names::CHF_SYMBOL, triple_names::CHF_SYMBOL_ID],
        [triple_names::CASH_FLOW, triple_names::CASH_FLOW_ID],
        [triple_names::CASH_FLOW_STATEMENT, triple_names::CASH_FLOW_STATEMENT_ID],
        [triple_names::CITY_BE, triple_names::CITY_BE_ID],
        [triple_names::CITY_GE, triple_names::CITY_GE_ID],
        [triple_names::CITY_ZH, triple_names::CITY_ZH_ID],
        [triple_names::COMPANY_ZURICH, triple_names::COMPANY_ZURICH_ID],
        [triple_names::E, triple_names::E_ID],
        [triple_names::GLOBAL_PROBLEM, triple_names::GLOBAL_PROBLEM_ID],
        [triple_names::GLOBAL_WARMING, triple_names::GLOBAL_WARMING_ID],
        [triple_names::GLOBAL_WARMING_PROBLEM, triple_names::GLOBAL_WARMING_PROBLEM_ID],
        [triple_names::GWP, triple_names::GWP_ID],
        [triple_names::HAPPY_TIME_POINTS, triple_names::HAPPY_TIME_POINTS_ID],
        [triple_names::INCOME_TAX, triple_names::INCOME_TAX_ID],
        [triple_names::PI, triple_names::PI_ID],
        [triple_names::PI_SYMBOL, triple_names::PI_SYMBOL_ID],
        [triple_names::POPULISM_PROBLEM, triple_names::POPULISM_PROBLEM_ID],
        [triple_names::POTENTIAL_EDUCATION_PROBLEM, triple_names::POTENTIAL_EDUCATION_PROBLEM_ID],
        [triple_names::POTENTIAL_HEALTH_PROBLEM, triple_names::POTENTIAL_HEALTH_PROBLEM_ID],
        [triple_names::POVERTY_PROBLEM, triple_names::POVERTY_PROBLEM_ID],
        [triple_names::TIME_POINTS, triple_names::TIME_POINTS_ID],
    ];

    // list of predefined triple used for system testing that are expected to be never renamed
    const array RESERVED_NAMES = array(
        self::SYSTEM_CONFIG,
        triple_names::SYSTEM_TEST_ADD,
        triple_names::SYSTEM_TEST_RENAMED,
        triple_names::SYSTEM_TEST_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        triple_names::MATH_CONST
    );

}
