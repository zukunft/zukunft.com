<?php

/*

    model/phrase/phrase_type.php - the phrase type object for the frontend API
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\phrase;

use cfg\const\paths;

include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;
use shared\library;

class phrase_type extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the phrase type to set the predefined behaviour of a word or triple';

    // database and JSON object field names additional to the type field only for phrase types
    const FLD_SCALE_COM = 'e.g. for percent the scaling factor is 100';
    const FLD_SCALE = 'scaling_factor';
    const FLD_SYMBOL_COM = 'e.g. for percent the symbol is %';
    const FLD_SYMBOL = 'word_symbol';

    // field lists for the table creation of phrase type
    const FLD_LST_EXTRA = array(
        [self::FLD_SCALE, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_SCALE_COM],
        [self::FLD_SYMBOL, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_SYMBOL_COM],
    );


    /*
     * construct and map
     */

    function __construct(string $code_id, int $id = 0, string $name = '')
    {
        parent::__construct($code_id, $name, $id);
        $this->code_id = $code_id;
        $this->set_id($id);
        $this->name = $name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }


    /*
     * load
     */

    /**
     * load a phrase type object by database id
     * just set the class name for the type object function
     * 
     * @param int $id the id of the phrase type
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        $lib = new library();
        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $this::class);
        return $this->load_typ_obj($qp, $this::class);
    }

}
