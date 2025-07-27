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

namespace cfg\language;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;
use cfg\user\user;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class language extends type_object
{

    /*
     * db const
     */

    // database and JSON object field names
    const TBL_COMMENT = 'for table languages';
    const FLD_ID = 'language_id';
    const FLD_NAME = 'language_name';
    const FLD_WIKI_CODE = 'wikimedia_code';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
    );
    const FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
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
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        $vars = array_merge($vars, get_object_vars($this));
        $vars[json_fields::ID] = $this->id();
        return $vars;
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
