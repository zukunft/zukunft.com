<?php

/*

    model/element/element_db.php - the database const for the element table
    ----------------------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\element;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;

class element_db
{

    /*
     * db const
     */

    // database fields only used for formula elements
    const string FLD_ID = 'element_id';
    const string FLD_FORMULA_COM = 'each element can only be used for one formula';

    const string FLD_TYPE = 'element_type_id';
    const string FLD_REF_ID_COM = 'either a term, verb or formula id';
    const string FLD_REF_ID = 'ref_id';
    const string FLD_TEXT = 'resolved_text';
    // TODO: is resolved text needed?

    // all database field names excluding the id, standard name and user-specific fields
    const array FLD_NAMES = array(
        formula_db::FLD_ID,
        user_db::FLD_ID,
        self::FLD_TYPE,
        self::FLD_REF_ID
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, self::FLD_FORMULA_COM],
        [element_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, element_type::class, ''],
        [user_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, '', user::class, ''],
        [self::FLD_REF_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_REF_ID_COM],
        [self::FLD_TEXT, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
    );


}
