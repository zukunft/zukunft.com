<?php

/*

    model/language/language_form.php - to define a language form e.g. plural
    --------------------------------

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

namespace cfg;

include_once DB_PATH . 'sql.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class language_form extends type_object
{

    /*
     * database link
     */

    // database and JSON object field names
    const TBL_COMMENT = 'for language forms like plural';
    const FLD_NAME_COM = 'type of adjustment of a term in a language e.g. plural';
    const FLD_NAME = 'language_form_name';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const FLD_LST_ALL = array(
        [sql::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [language::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, language::class, ''],
    );

    /*
     * code link
     */

    // list of the language forms that have a coded functionality
    const DEFAULT = "standard";
    const PLURAL = "plural";


    /*
     * load
     */

    /**
     * load a language form object by database id
     * mainly set the class name for the type object function
     *
     * @param int $id the id of the language form
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $this::class);
        return $this->load_typ_obj($qp, $this::class);
    }

}
