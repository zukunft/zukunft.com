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

namespace cfg;

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

include_once MODEL_HELPER_PATH . 'library.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

class phrase_type extends type_object
{

    // list of the phrase types that have a coded functionality
    // TODO add the missing functionality and unit tests
    const NORMAL = "default";
    const MATH_CONST = "constant"; // TODO add usage sample
    const TIME = "time";
    const TIME_JUMP = "time_jump";
    const LATEST = "latest"; // TODO add usage sample
    const PERCENT = "percent";
    const MEASURE = "measure";
    const MEASURE_DIVISOR = "measure_divisor";
    const SCALING = "scaling";
    const SCALING_HIDDEN = "scaling_hidden";
    const SCALING_PCT = "scaling_percent"; // TODO used to define the scaling formula word to scale percentage values ?
    const SCALED_MEASURE = "scaled_measure"; // TODO add usage sample
    const FORMULA_LINK = "formula_link"; // special phrase type for functional words that are used to link values to formulas
    const CALC = "calc"; // TODO add usage sample
    const LAYER = "view"; // TODO add usage sample
    const OTHER = "type_other";
    const KEY = "key";
    const INFO = "information";
    const TRIPLE_HIDDEN = "hidden_triple";
    const SYSTEM_HIDDEN = "hidden_system";
    const GROUP = "group";
    const THIS = "this";
    const NEXT = "next";
    const PRIOR = "previous";

    const DEFAULT = self::NORMAL;


    /*
     * database link
     */

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
        $this->id = $id;
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
     * @param string $class the phrase type class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        $lib = new library();
        log_debug($id);
        $dp_type = $lib->class_to_name($class);
        // TODO rename table phrase_type to phrase_type
        if ($dp_type == 'phrase_type') {
            $dp_type = 'phrase_type';
        }
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $dp_type);
        return $this->load_typ_obj($qp, $dp_type);
    }

}
