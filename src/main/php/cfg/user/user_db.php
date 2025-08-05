<?php

/*

    model/user/user.php - a person who uses zukunft.com
    -------------------

    TODO make sure that no right gain is possible
    TODO move the non functional user parameters to hidden words to be able to reuse the standard view functionality
    TODO log the access attempts to objects with a restricted access
    TODO build a process so that a user can request access to an object with restricted access

    if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
    if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
    if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
    if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

    if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
    if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
    if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

    the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

    The main sections of this object are
    - db const:          const for the database link
    - preserved:         const user names used by the system
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - save:              manage to update the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\user;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
//include_once paths::MODEL_REF . 'source.php';
//include_once paths::MODEL_WORD . 'triple.php';
//include_once paths::MODEL_WORD . 'triple_db.php';
//include_once paths::MODEL_VIEW . 'view.php';

use cfg\db\sql_db;
use cfg\helper\db_object_seq_id;
use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\ref\source;
use cfg\word\triple;
use cfg\view\view;
use cfg\word\triple_db;

class user_db extends db_object_seq_id
{

    /*
     * db const
     */

    // TODO move to user_db class like word_db
    // database fields and comments only used for user
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'user_id'; // also the field name for foreign keys
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    // fields for the main logon
    const FLD_NAME_COM = 'the user name unique for this pod';
    const FLD_NAME = 'user_name';
    const FLD_IP_ADDR_COM = 'all users a first identified with the ip address';
    const FLD_IP_ADDR = 'ip_address';
    const FLD_PASSWORD_COM = 'the hash value of the password';
    const FLD_PASSWORD = 'password';
    // description and type
    const FLD_DESCRIPTION_COM = 'for system users the description to explain the profile to human users';
    const FLD_CODE_ID_COM = 'to select e.g. the system batch user';
    const FLD_CODE_ID = 'code_id';
    const FLD_PROFILE_COM = 'to define the user roles and read and write rights';
    const FLD_PROFILE = 'user_profile_id';
    const FLD_TYPE_ID_COM = 'to set the confirmation level of a user';
    const FLD_TYPE_ID = 'user_type_id';
    const FLD_EXCLUDED_COM = 'true if the user is deactivated but cannot be deleted due to log entries';
    const FLD_LEVEL_COM = 'the access right level to prevent not permitted right gaining';
    const FLD_LEVEL = 'right_level';
    // online verification
    const FLD_EMAIL_COM = 'the primary email for verification';
    const FLD_EMAIL = 'email';
    const FLD_EMAIL_STATUS_COM = 'if the email has been verified or if a password reset has been send';
    const FLD_EMAIL_STATUS = 'email_status';
    const FLD_EMAIL_ALT_COM = 'an alternative email for account recovery';
    const FLD_EMAIL_ALT = 'email_alternative';
    const FLD_TWO_FACTOR_ID = 'mobile_number';
    const FLD_TWO_FACTOR_STATUS = 'mobile_status';
    const FLD_ACTIVATION_KEY = 'activation_key';
    const FLD_ACTIVATION_TIMEOUT = 'activation_timeout';
    // offline verification
    const FLD_FIRST_NAME = 'first_name';
    const FLD_LAST_NAME = 'last_name';
    const FLD_NAME_TRIPLE_COM = 'triple that contains e.g. the given name, family name, selected name or title of the person';
    const FLD_NAME_TRIPLE_ID = 'name_triple_id';
    const FLD_GEO_TRIPLE_COM = 'the post address with street, city or any other form of geo location for physical transport';
    const FLD_GEO_TRIPLE_ID = 'geo_triple_id';
    const FLD_GEO_STATUS = 'geo_status_id';
    const FLD_OFFICIAL_ID_COM = 'e.g. the number of the passport';
    const FLD_OFFICIAL_ID = 'official_id';
    const FLD_OFFICIAL_TYPE_ID = 'official_id_type';
    const FLD_OFFICIAL_ID_STATUS = 'official_id_status';
    // settings
    const FLD_TERM_COM = 'the last term that the user had used';
    const FLD_TERM = 'term_id';
    const FLD_VIEW_COM = 'the last mask that the user has used';
    const FLD_VIEW = 'view_id';
    const FLD_SOURCE_COM = 'the last source used by this user to have a default for the next value';
    const FLD_SOURCE = 'source_id';
    const FLD_STATUS_COM = 'e.g. to exclude inactive users';
    const FLD_STATUS = 'user_status_id';
    const FLD_CREATED = 'created';
    const FLD_LAST_LOGIN = 'last_login';
    const FLD_LAST_LOGOUT = 'last_logoff';


    // database fields used for the user logon process
    const FLD_DB_NOW = 'NOW() AS db_now';

    // all database field names excluding the id
    // TODO review and sync with FLD_LST_ALL and move non critical fields to a value_list
    const FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_IP_ADDR,
        self::FLD_PASSWORD,
        sql_db::FLD_DESCRIPTION,
        sql_db::FLD_CODE_ID,
        self::FLD_PROFILE,
        // TODO to be added
        //self::FLD_TYPE_ID,
        //self::FLD_LEVEL,
        self::FLD_EMAIL,
        //self::FLD_EMAIL_STATUS,
        self::FLD_FIRST_NAME,
        self::FLD_LAST_NAME,
        self::FLD_TERM,
        self::FLD_SOURCE,
        self::FLD_ACTIVATION_KEY,
        self::FLD_ACTIVATION_TIMEOUT,
        //self::FLD_DB_NOW
    );
    // the database field names excluding the id and the fields for logon
    const FLD_NAMES_LIST = array(
        sql_db::FLD_CODE_ID,
        self::FLD_IP_ADDR,
        self::FLD_EMAIL,
        self::FLD_FIRST_NAME,
        self::FLD_LAST_NAME,
        self::FLD_TERM,
        self::FLD_SOURCE,
        self::FLD_PROFILE
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        // main logon
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_IP_ADDR, sql_field_type::CODE_ID, sql_field_default::NULL, sql::INDEX, '', self::FLD_IP_ADDR_COM],
        [self::FLD_PASSWORD, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_PASSWORD_COM],
        // description and type
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, sql::INDEX, '', self::FLD_CODE_ID_COM],
        [self::FLD_PROFILE, sql_field_type::INT_SMALL, sql_field_default::NULL, sql::INDEX, user_profile::class, self::FLD_PROFILE_COM],
        [self::FLD_TYPE_ID, sql_field_type::INT_SMALL, sql_field_default::NULL, sql::INDEX, user_type::class, self::FLD_TYPE_ID_COM],
        [sql_db::FLD_EXCLUDED, sql_db::FLD_EXCLUDED_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_EXCLUDED_COM],
        [self::FLD_LEVEL, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_LEVEL_COM],
        // online verification
        [self::FLD_EMAIL, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_EMAIL_COM],
        [self::FLD_EMAIL_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_EMAIL_STATUS_COM],
        [self::FLD_EMAIL_ALT, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_EMAIL_ALT_COM],
        [self::FLD_TWO_FACTOR_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
        [self::FLD_TWO_FACTOR_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_ACTIVATION_KEY, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_ACTIVATION_TIMEOUT, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
        // offline verification
        [self::FLD_FIRST_NAME, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_LAST_NAME, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_NAME_TRIPLE_ID, sql_field_type::INT, sql_field_default::NULL, '', triple::class, self::FLD_NAME_TRIPLE_COM, triple_db::FLD_ID],
        [self::FLD_GEO_TRIPLE_ID, sql_field_type::INT, sql_field_default::NULL, '', triple::class, self::FLD_GEO_TRIPLE_COM, triple_db::FLD_ID],
        [self::FLD_GEO_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_OFFICIAL_ID, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_OFFICIAL_ID_COM],
        [self::FLD_OFFICIAL_TYPE_ID, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_OFFICIAL_ID_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        // settings
        [self::FLD_TERM, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_TERM_COM],
        [self::FLD_VIEW, sql_field_type::INT, sql_field_default::NULL, '', view::class, self::FLD_VIEW_COM],
        [self::FLD_SOURCE, sql_field_type::INT, sql_field_default::NULL, '', source::class, self::FLD_SOURCE_COM],
        [self::FLD_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_STATUS_COM],
        [self::FLD_CREATED, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, '', '', ''],
        [self::FLD_LAST_LOGIN, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
        [self::FLD_LAST_LOGOUT, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
    );

}
