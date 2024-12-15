<?php

/*

    /model/helper/type_object.php - the superclass for word, formula and view types
    -----------------------------

    a base type object that can be used to link program code to single objects
    e.g. if a value is classified by a phrase of type percent the value by default is formatted in percent

    types are used to assign coded functionality to a word, formula or view
    a user can create a new type to group words, formulas or views and request new functionality for the group
    types can be renamed by a user and the user change the comment
    it should be possible to translate types on the fly
    on each program start the types are loaded once into an array, because they are not supposed to change during execution


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

include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once DB_PATH . 'sql_par_type.php';
include_once API_SANDBOX_PATH . 'type_object.php';

use api\sandbox\type_object as type_object_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\log\change_action;
use cfg\log\change_table;
use cfg\log\change_table_field;
use JsonSerializable;
use shared\json_fields;

class type_object extends db_object_seq_id implements JsonSerializable
{

    /*
     * database link
     */

    // comments used for the database creation
    // *_SQL_TYP is the sql data type used for the field
    const TBL_COMMENT = 'for a type to set the predefined behaviour of an object';

    // database and JSON object field names
    const FLD_ID_COM = 'the database id is also used as the array pointer';
    const FLD_ID_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_NAME_COM = 'the unique type name as shown to the user and used for the selection';
    const FLD_NAME = 'type_name';
    const FLD_CODE_ID_COM = 'this id text is unique for all code links, is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const FLD_DESCRIPTION_COM = 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
    const FLD_DESCRIPTION = 'description';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;

    // type name exceptions
    const FLD_ACTION = 'change_action_name';
    const FLD_TABLE = 'change_table_name';
    const FLD_FIELD = 'change_table_field_name';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const FLD_LST_ALL = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );


    /*
     * object vars
     */

    // the standard fields of a type
    public string $name; // the unique type name as shown to the user
    public ?string $code_id; // this id text is unique for all code links and is used for system im- and export
    public ?string $description = '';  // to explain the type to the user as a tooltip


    /*
     * construct and map
     */

    function __construct(?string $code_id, string $name = '', ?string $description = null, int $id = 0)
    {
        parent::__construct();
        $this->set_id($id);
        $this->set_name($name);
        $this->set_code_id($code_id);
        if ($description != '') {
            $this->set_description($description);
        }
    }

    function reset(): void
    {
        $this->set_id(0);
        $this->code_id = null;
        $this->name = '';
        $this->description = null;
    }

    /**
     * fill the type object vars based on an array of fields from the database
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, string $class): bool
    {
        $result = parent::row_mapper($db_row, $this->id_field_typ($class));
        // set the id upfront to allow row mapping
        if ($class == language::class AND array_key_exists(language::FLD_ID, $db_row)) {
            $this->set_id(($db_row[language::FLD_ID]));
        }
        if ($this->id() > 0) {
            $this->code_id = strval($db_row[sql::FLD_CODE_ID]);
            $type_name = '';
            if ($class == change_action::class) {
                $type_name = strval($db_row[self::FLD_ACTION]);
            } elseif ($class == change_table::class) {
                $type_name = strval($db_row[self::FLD_TABLE]);
            } elseif ($class == change_table_field::class) {
                $type_name = strval($db_row[self::FLD_FIELD]);
            } elseif ($class == language_form::class) {
                $type_name = strval($db_row[language_form::FLD_NAME]);
            } elseif ($class == language::class) {
                $type_name = strval($db_row[language::FLD_NAME]);
            } else {
                $type_name = strval($db_row[sql::FLD_TYPE_NAME]);
            }
            $this->name = $type_name;
            $this->description = strval($db_row[sandbox_named::FLD_DESCRIPTION]);
            $result = true;
        }
        return $result;
    }


    /*
     * set and get
     */

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function set_code_id(?string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    function name(): string
    {
        return $this->name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    function description(): string
    {
        return $this->description;
    }


    /*
     * cast
     */

    /**
     * @return type_object_api the code link frontend api object
     */
    function api_obj(): type_object_api
    {
        $api_obj = new type_object_api();
        $api_obj->id = $this->id();
        $api_obj->name = $this->name;
        $api_obj->code_id = $this->code_id;
        return $api_obj;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = [];

        if ($this->name() <> '') {
            $vars[json_fields::NAME] = $this->name();
        }
        if ($this->code_id <> '') {
            $vars[json_fields::CODE_ID] = $this->code_id;
        }
        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }

        return $vars;
    }


    /*
     * information
     */

    function is_type(string $type_to_check): bool
    {
        if ($this->code_id == $type_to_check) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a type object
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        $sql = $sc->sql_separator();
        // the pod is a type object but the number of pods might be significant higher than the number of types
        if ($this:: class == pod::class) {
            $sql .= $this->sql_table_create($sc);
        } else {
            $sql .= $this->sql_table_create($sc, new sql_type_list([sql_type::KEY_SMALL_INT]));
        }
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a type object
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc);
        return $sql;
    }


    /*
     * load (used if the user can request a new type via the GUI)
     */

    /**
     * create an SQL statement to retrieve a type object by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int $id, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, sql_db::FLD_ID);
        $sc->add_where($this->id_field_typ($class), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * synthetic creation of grandparent:: for verb
     * @param sql $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id_fwd(sql $sc, int $id, string $class = ''): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
    }

    /**
     * create an SQL statement to retrieve a type object by name from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $name the name of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql $sc, string $name, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, sql_db::FLD_NAME);
        $sc->add_where($this->name_field_typ($class), $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a type object by code id from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $code_id the code id of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql $sc, string $code_id, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, 'code_id');
        $sc->add_where(sql::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a type object e.g. phrase type, language or language form from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @param string $class the type class name that should be filled
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load_typ_obj(sql_par $qp, string $class): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_typ_obj($db_row, $class);
        return $this->id();
    }

    private function id_field_typ(string $class): string
    {
        global $db_con;
        return $db_con->get_id_field_name($class);
    }

    private function name_field_typ(string $db_type): string
    {
        global $db_con;
        return $db_con->get_name_field($db_type);
    }


    /*
     * interface
     */

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
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        return $this->name . '/' . $this->code_id() . parent::dsp_id();
    }

}
