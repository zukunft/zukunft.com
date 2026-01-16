<?php

/*

    model/system/job_db.php - the database const for jon tables
    -----------------------

    The main sections of this object are
    - db const:          const for the database link


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/


namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_REF . 'ref_db.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_db;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;

class job_db
{

    /*
     * database link
     */

    // object specific database object field names and comments
    const string FLD_ID_COM = 'the unique internal id of the job';
    const string FLD_ID = 'job_id';
    const string FLD_USER_COM = 'the id of the user who has requested the job by editing the scheduler the last time';
    const string FLD_TIME_REQUEST_COM = 'timestamp of the request for the job execution';
    const string FLD_TIME_REQUEST = 'request_time';
    const string FLD_TIME_START_COM = 'timestamp when the system has started the execution';
    const string FLD_TIME_START = 'start_time';
    const string FLD_TIME_END_COM = 'timestamp when the job has been completed or canceled';
    const string FLD_TIME_END = 'end_time';
    const string FLD_TYPE_COM = 'the id of the job type that should be started';
    const string FLD_TYPE = 'job_type_id';
    const string FLD_PARAMETER_COM = 'id of the phrase with the snapped parameter set for this job start';
    const string FLD_PARAMETER = 'parameter';
    const string FLD_CHANGE_FIELD_COM = 'e.g. for undo jobs the id of the field that should be changed';
    const string FLD_CHANGE_FIELD = 'change_field_id';
    const string FLD_ROW_COM = 'e.g. for undo jobs the id of the row that should be changed';
    const string FLD_ROW = 'row_id';
    const string FLD_SOURCE_COM = 'used for import to link the source';
    const string FLD_REF_COM = 'used for import to link the reference';

    // all database field names excluding the id used to identify if there are some user-specific changes
    const array FLD_NAMES = array(
        self::FLD_ID,
        self::FLD_TIME_REQUEST,
        self::FLD_TIME_START,
        self::FLD_TIME_END,
        self::FLD_TYPE,
        self::FLD_ROW,
        self::FLD_CHANGE_FIELD
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [user_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [job_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, job_type::class, self::FLD_TYPE_COM],
        [self::FLD_TIME_REQUEST, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_REQUEST_COM],
        [self::FLD_TIME_START, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_START_COM],
        [self::FLD_TIME_END, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_END_COM],
        [self::FLD_PARAMETER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_PARAMETER_COM, phrase::FLD_ID],
        [self::FLD_CHANGE_FIELD, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_CHANGE_FIELD_COM],
        [self::FLD_ROW, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_ROW_COM],
        [source_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, self::FLD_SOURCE_COM],
        [ref_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, ref::class, self::FLD_REF_COM],
    );

}