<?php

/*

    model/language/language.php - to define a language for the user interface
    ---------------------------

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

use library;

class language extends type_object
{

    /*
     * database link
     */

    // database and JSON object field names
    const FLD_NAME = 'language_name';


    /*
     * code link
     */

    // list of the languages that have a coded functionality
    const DEFAULT = "english";
    const TN_READ = "English";

    
    /*
     * load
     */

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param int $id the id of the language
     * @param string $class the language class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $lib = new library();
        $dp_type = $lib->base_class_name($class);
        $qp = $this->load_sql_by_id($db_con, $id, $dp_type);
        return $this->load($qp, $dp_type);
    }

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param string $name the name of the language
     * @param string $class the language class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $lib = new library();
        $dp_type = $lib->base_class_name($class);
        $qp = $this->load_sql_by_name($db_con, $name, $dp_type);
        return $this->load($qp, $dp_type);
    }

}
