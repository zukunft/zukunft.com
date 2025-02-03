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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\formula;

include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;
use cfg\phrase\phrase;
use cfg\phrase\phrase_type;

class formula_link_type extends type_object
{

    /*
     * code links
     */

    // list of the formula link types that have a coded functionality
    const DEFAULT = "default";               // a simple link between a formula and a phrase
    const TIME_PERIOD = "time_period_based"; // for time based links

    // the database and JSON object field names used only for formula links
    const FLD_ID = 'formula_link_type_id';


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to assign predefined behaviour to a formula link';

    // field lists for the table creation of phrase type
    const FLD_LST_EXTRA = array(
        [formula::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, '', formula::class, ''],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NOT_NULL, '', phrase_type::class, ''],
    );

}
