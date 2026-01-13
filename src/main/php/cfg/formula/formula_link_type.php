<?php

/*

    model/formula/formula_link_type.php - the formula link type object with the ENUM values for hardcoded formulas
    ---------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;

class formula_link_type extends type_object
{

    /*
     * code links
     */

    // the database and JSON object field names used only for formula links
    const string FLD_ID = 'formula_link_type_id';


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to assign predefined behaviour to a formula link';

    // field lists for the table creation of phrase type
    const array FLD_LST_EXTRA = array(
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, '', formula::class, ''],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NOT_NULL, '', phrase_type::class, ''],
    );

}
