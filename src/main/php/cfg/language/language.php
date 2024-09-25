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


use api\api;
use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use JsonSerializable;
use shared\library;

class language extends type_object implements JsonSerializable
{

    /*
     * database link
     */

    // database and JSON object field names
    const TBL_COMMENT = 'for table languages';
    const FLD_ID = 'language_id';
    const FLD_NAME = 'language_name';
    const FLD_WIKI_CODE = 'wikimedia_code';

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [sql::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQLTYP, sql_field_default::NULL, '', '', ''],
        [self::FLD_WIKI_CODE, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
    );


    /*
     * code link
     */

    // list of the languages that have a coded functionality
    const DEFAULT = "english";
    const DEFAULT_ID = 1;
    const TN_READ = "English";


    /*
     * interface
     */

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->get_json();
    }

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the sandbox vars without empty values that are not needed
     * the message from the backend to the frontend does not need to include empty fields
     * the message from the frontend to the backend on the other side must include empty fields
     * to be able to unset fields in the backend
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        $vars = array_merge($vars, get_object_vars($this));
        $vars[api::FLD_ID] = $this->id();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * load
     */

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param int $id the id of the language
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $this::class);
        return $this->load_typ_obj($qp, $this::class);
    }

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param string $name the name of the language
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $lib = new library();
        $dp_type = $lib->class_to_name($this::class);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name, $dp_type);
        return $this->load_typ_obj($qp, $this::class);
    }

}
