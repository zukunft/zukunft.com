<?php

/*

    model/system/pod.php - the technical details of the mash network pods
    ------------------

    $pod is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_db.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;

class pod extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the technical details of the mash network pods';
    const string FLD_ID_COM = 'the unique id of the pods within this pod database';
    const string FLD_ID = 'pod_id';
    const sql_field_type FLD_ID_SQL_TYP = sql_field_type::INT; // overwrite the type object setting because the number of pods may be bigger
    const string FLD_URL = 'pod_url';
    const string FLD_PARAM = 'param_triple_id';

    // field lists for the table creation
    const array FLD_LST_EXTRA = array(
        [pod_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, pod_type::class, ''],
        [self::FLD_URL, sql_field_type::NAME, sql_field_default::NOT_NULL, '', '', ''],
        [pod_status::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, pod_status::class, ''],
        [self::FLD_PARAM, sql_field_type::INT, sql_field_default::NULL, '', triple::class, '', triple_db::FLD_ID],
    );

}
