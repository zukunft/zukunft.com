<?php

/*

    model/component/view_style.php - db based ENUM of the view and component display styles e.g. the width
    ----------------------------

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

namespace cfg\component;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';

use cfg\helper\type_object;
use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class view_style extends type_object
{

    // the list of code ids of the view or component styles are in the object shared with the frontend

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'the display style for a view or component e.g. number of columns to use';
    const FLD_NAME = 'view_style_name';
    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );

}
