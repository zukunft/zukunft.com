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

include_once DB_PATH . 'sql_par_type.php';
include_once API_SANDBOX_PATH . 'type_object.php';

use api\type_object_api;
use cfg\db\sql_par_type;
use model\db_cl;

class type_object
{

    /*
     * database link
     */

    // database and JSON object field names
    const FLD_NAME = 'type_name';

    // type name exceptions
    const FLD_ACTION = 'change_action_name';
    const FLD_TABLE = 'change_table_name';
    const FLD_FIELD = 'change_table_field_name';


    /*
     * object vars
     */

    // the standard fields of a type
    public int $id;                // the database id is also used as the array pointer
    public string $name;           // simply the type name as shown to the user
    public string $code_id;        // this id text is unique for all code links and is used for system im- and export
    public ?string $comment = '';  // to explain the type to the user as a tooltip


    /*
     * construct and map
     */

    function __construct(?string $code_id, string $name = '', string $comment = '', int $id = 0)
    {
        $this->set_id($id);
        $this->set_name($name);
        $this->set_code_id($code_id);
        if ($comment != '') {
            $this->set_comment($comment);
        }
    }

    function row_mapper(array $db_row, string $db_type): bool
    {
        $result = false;
        $this->id = $db_row[$this->id_field($db_type)];
        if ($this->id > 0) {
            $this->code_id = strval($db_row[sql_db::FLD_CODE_ID]);
            $type_name = '';
            if ($db_type == db_cl::LOG_ACTION) {
                $type_name = strval($db_row[self::FLD_ACTION]);
            } elseif ($db_type == db_cl::LOG_TABLE) {
                $type_name = strval($db_row[self::FLD_TABLE]);
            } elseif ($db_type == sql_db::VT_TABLE_FIELD) {
                $type_name = strval($db_row[self::FLD_FIELD]);
            } elseif ($db_type == sql_db::TBL_LANGUAGE) {
                $type_name = strval($db_row[language::FLD_NAME]);
            } elseif ($db_type == sql_db::TBL_LANGUAGE_FORM) {
                $type_name = strval($db_row[language_form::FLD_NAME]);
            } else {
                $type_name = strval($db_row[sql_db::FLD_TYPE_NAME]);
            }
            $this->name = $type_name;
            $this->comment = strval($db_row[sql_db::FLD_DESCRIPTION]);
            $result = true;
        }
        return $result;
    }


    /*
     * set and get
     */

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function set_comment(string $comment): void
    {
        $this->comment = $comment;
    }

    function id(): int
    {
        return $this->id;
    }

    function name(): string
    {
        return $this->name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    function comment(): string
    {
        return $this->comment;
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
        $api_obj->id = $this->id;
        $api_obj->name = $this->name;
        $api_obj->code_id = $this->code_id;
        return $api_obj;
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
     * load (used if the user can request a new type via the GUI)
     */

    /**
     * create an SQL statement to retrieve a type object by id from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the type object
     * @param string $db_type the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_db $db_con, int $id, string $db_type = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($db_con, $db_type, sql_db::FLD_ID);
        $db_con->add_par_int($id);
        $qp->sql = $db_con->select_by_field($this->id_field($db_type));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a type object by name from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $name the name of the source
     * @param string $db_type the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_db $db_con, string $name, string $db_type = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($db_con, $db_type, sql_db::FLD_NAME);
        $db_con->add_par(sql_par_type::TEXT, $name);
        $qp->sql = $db_con->select_by_field($this->name_field($db_type));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a type object by code id from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $code_id the code id of the source
     * @param string $db_type the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_db $db_con, string $code_id, string $db_type = ''): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($db_con, $db_type, 'code_id');
        $db_con->add_par(sql_par_type::TEXT, $code_id);
        $qp->sql = $db_con->select_by_code_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load a type object e.g. phrase type, language or language form from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp, string $db_type): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row, $db_type);
        return $this->id();
    }

    private function id_field(string $db_type): string
    {
        global $db_con;
        return $db_con->get_id_field_name($db_type);
    }

    private function name_field(string $db_type): string
    {
        global $db_con;
        return $db_con->get_name_field($db_type);
    }

}
