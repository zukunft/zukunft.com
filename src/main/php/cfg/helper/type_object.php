<?php

/*

    model/helper/type_object.php - the superclass for word, formula and view types
    ----------------------------

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

namespace cfg\helper;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
// TODO avoid include loops
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_LANGUAGE . 'language_form.php';
//include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_table.php';
//include_once paths::MODEL_LOG . 'change_table_field.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
//include_once paths::MODEL_SYSTEM . 'pod.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change_action;
use cfg\log\change_table;
use cfg\log\change_table_field;
use cfg\sandbox\sandbox_named;
use cfg\system\pod;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class type_object extends db_object_seq_id
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

    // type name exceptions
    const FLD_ACTION = 'change_action_name';
    const FLD_TABLE = 'change_table_name';
    const FLD_FIELD = 'change_table_field_name';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );


    /*
     * object vars
     */

    // the standard fields of a type

    // the unique type name as shown to the user
    public string $name;
    // this id text is unique for all code links and is used for system im- and export
    public ?string $code_id;
    // to explain the type to the user as a tooltip
    public ?string $description = null;


    /*
     * construct and map
     */

    function __construct(?string $code_id, string $name = '', ?string $description = null, int $id = 0)
    {
        parent::__construct();
        $this->set_id($id);
        $this->set_name($name);
        $this->set_code_id_db($code_id);
        $this->set_description($description);
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
        if ($class == language::class and array_key_exists(language::FLD_ID, $db_row)) {
            $this->set_id(($db_row[language::FLD_ID]));
        }
        if ($this->id() > 0) {
            $this->code_id = strval($db_row[sql_db::FLD_CODE_ID]);
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
                $type_name = strval($db_row[sql_db::FLD_TYPE_NAME]);
            }
            $this->name = $type_name;
            $this->description = strval($db_row[sql_db::FLD_DESCRIPTION]);
            $result = true;
        }
        return $result;
    }

    /**
     * fill the vars with this sandbox object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = new user_message();

        if (array_key_exists(json_fields::ID, $api_json)) {
            $this->set_id($api_json[json_fields::ID]);
        }
        if (array_key_exists(json_fields::NAME, $api_json)) {
            $this->set_name($api_json[json_fields::NAME]);
        }
        if (array_key_exists(json_fields::DESCRIPTION, $api_json)) {
            if ($api_json[json_fields::DESCRIPTION] <> '') {
                $this->description = $api_json[json_fields::DESCRIPTION];
            }
        }


        return $usr_msg;
    }

    /**
     * general part to import a database object from a JSON array object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
    {
        return new user_message();
    }


    /*
     * set and get
     */

    /**
     * set the vars of this type object based on json string from the frontend object
     * @param string $api_json with the api message created by the frontend
     * @return user_message with problems and suggested solutions for the user
     */
    function set_from_api(string $api_json): user_message
    {
        return $this->api_mapper(json_decode($api_json, true));
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the unique id to select a single verb by the program
     *r
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system or configuration
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_code_id(?string $code_id, user $usr): user_message
    {
        $usr_msg = new user_message();
        if ($usr->can_set_code_id()) {
            $this->code_id = $code_id;
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => sql_db::FLD_CODE_ID,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $usr_msg;
    }

    /**
     * set the code id without check
     * should only be called by the database mapper function
     */
    function set_code_id_db(?string $code_id): void
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

    function description(): ?string
    {
        return $this->description;
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
     * info
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
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
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
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
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
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = ''): sql_par
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
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the type object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id_fwd(sql_creator $sc, int $id, string $class = ''): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
    }

    /**
     * create an SQL statement to retrieve a type object by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name, string $class = ''): sql_par
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
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the source
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id, string $class = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $class, 'code_id');
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
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
        return array_merge($vars, get_object_vars($this));
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
