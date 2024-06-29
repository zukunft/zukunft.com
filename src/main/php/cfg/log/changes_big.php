<?php

/*

    cfg/log/changes_big.php - log group changes for values with more than 16 phrases
    -----------------------

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

include_once MODEL_LOG_PATH . 'change_value.php';

use cfg\db\sql_field_default;

class changes_big extends change
{

    /*
     * database link
     */

    // user log database and JSON object field names for named user sandbox objects
    const TBL_COMMENT = 'to log all changes done by any user on the group name for values with more than 16 phrases';

    // field list to log the actual change of the named user sandbox object
    const FLD_LST_CHANGE = array(
        [self::FLD_FIELD_ID, self::FLD_FIELD_ID_SQLTYP, sql_field_default::NOT_NULL, '', change_field::class, ''],
        [self::FLD_OLD_VALUE, self::FLD_OLD_VALUE_SQLTYP, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_VALUE, self::FLD_NEW_VALUE_SQLTYP, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_ID, self::FLD_OLD_ID_BIG_SQLTYP, sql_field_default::NULL, '', '', self::FLD_OLD_ID_COM],
        [self::FLD_NEW_ID, self::FLD_OLD_ID_BIG_SQLTYP, sql_field_default::NULL, '', '', self::FLD_NEW_ID_COM],
    );

}