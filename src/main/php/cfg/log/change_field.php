<?php

/*

    model/log/change_field.php - the field where a user has done a change including deprecated field names
    --------------------------

    TODO Prio 1 add a column with the short name that should be shown to the user
                and for the selection the unique name should be "name (word)" instead of 5word_name
                and use the description of the e.g. word_db object for the code_link csv file


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class change_field extends type_object
{


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to keep the original field name even if a table name has changed';
    const string FLD_ID = 'change_field_id';
    const string FLD_NAME_COM = 'the real name';
    const string FLD_NAME = 'change_field_name';
    const string FLD_TABLE_COM = 'because every field must only be unique within a table';
    const string FLD_TABLE = 'table_id';
    const string FLD_CODE_ID_COM = 'to display the change with some linked information';

    // field lists for the field creation
    const array FLD_LST_NAME = array(
        [self::FLD_TABLE, sql_field_type::INT_SMALL_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, change_table::class, self::FLD_TABLE_COM, change_table::FLD_ID],
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const array FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    public ?int $tbl_id = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this change field object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->tbl_id = null;
    }

    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }


    /*
     * load
     */

    /**
     * load the change log table field from the database selected by the table id and the field name
     * @param string $name the name of the field
     * @param int $tbl_id the database id of the table to which the field name belongs
     * @return int the id of the type object found and zero if nothing is found
     */
    function load_by_name_and_table_id(string $name, int $tbl_id): int
    {
        global $db_con;

        log_debug($name);
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_name_and_table_id($sc, $name, $tbl_id);
        return $this->load($qp);
    }

    /**
     * create an SQL statement to retrieve a type object by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the code id of the source
     * @param int $tbl_id the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name_and_table_id(
        sql_creator $sc,
        string      $name,
        int         $tbl_id
    ): sql_par
    {
        $typ_lst = new type_list();
        $qp = $typ_lst->load_sql($sc, $this::class, 'by_name_and_id');
        $sc->add_where(self::FLD_NAME, $name);
        $sc->add_where(self::FLD_TABLE, $tbl_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                self::FLD_TABLE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param change_field|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        change_field|db_object_seq_id $obj,
        user_message                  $msg,
        sql_type_list                 $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->tbl_id !== $this->tbl_id) {
            $lst->add_field(
                self::FLD_TABLE,
                $this->tbl_id,
                sql_field_type::INT_SMALL,
                $obj->tbl_id
            );
        }
        return $lst;
    }

}