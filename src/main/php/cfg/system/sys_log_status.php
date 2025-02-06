<?php

/*

    model/system/sys_log_status.php - to link coded functionality to a system log status
    -------------------------------

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

namespace cfg\system;

include_once MODEL_HELPER_PATH . 'type_object.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;

class sys_log_status extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to define the status of internal errors';
    const FLD_ID = 'sys_log_status_id'; // name of the id field as const for other const
    const FLD_ACTION_COM = 'description of the action to get to this status';
    const FLD_ACTION = 'action';

    // list of fields that are additional to the standard type fields used for the system log status
    const FLD_LST_EXTRA = array(
        [self::FLD_ACTION, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_ACTION_COM],
    );

}
