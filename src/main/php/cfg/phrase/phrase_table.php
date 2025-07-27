<?php

/*

    model/phrase/phrase_table.php - remember which phrases are stored in which table and pod
    ---------------------------


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

namespace cfg\phrase;

use cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_PHRASE . 'phrase_table_status.php';
include_once paths::MODEL_SYSTEM . 'pod.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_USER . 'user.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\db_object_seq_id;
use cfg\system\pod;
use cfg\system\sys_log_status;
use cfg\user\user;

class phrase_table extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names
    // and comments used for the database creation
    const TBL_COMMENT = 'remember which phrases are stored in which table and pod';
    const FLD_ID = 'phrase_table_id';
    const FLD_PHRASE_COM = 'the values and results of this phrase are primary stored in dynamic tables on the given pod';
    const FLD_POD_COM = 'the primary pod where the values and results related to this phrase saved';


    // all database field names excluding the id
    // the extra user field is needed because it is common to check the log entries of others users e.g. for admin users
    const FLD_NAMES = array(
        user::FLD_ID,
        sys_log_status::FLD_ID
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_PHRASE_COM],
        [pod::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, pod::class, self::FLD_POD_COM],
        [phrase_table_status::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, phrase_table_status::class, ''],
    );


    /*
     * object vars
     */

    // object vars for the database fields
    public phrase|null $phr_id = null;         // the user id who was logged in when the error happened

}