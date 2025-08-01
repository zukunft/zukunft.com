<?php

/*

    model/log/change_value.php - log object for changes of values with a big group id
    ------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\log;

use cfg\const\paths;

include_once paths::MODEL_LOG . 'change_value.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class change_values_big extends change_value
{

    /*
     * database link
     */

    // user log database and JSON object field names for named user sandbox objects
    const TBL_COMMENT = 'to log all changes done by any user on values with a big group id';

    // field list to identify the value with a big group id that has been changed
    const FLD_LST_ROW_ID = array(
        [self::FLD_GROUP_ID, sql_field_type::TEXT, sql_field_default::NOT_NULL, '', '', ''],
    );

}