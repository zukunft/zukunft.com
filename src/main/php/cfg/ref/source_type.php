<?php

/*

    model/ref/source_type.php - the base object for external source type such as pubmed
    -------------------------

    the source type is used for all external sources that have some coded functionality
    but does not allow a full bidirectional synchronisation like a reference type


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

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class source_type extends type_object
{

    // the url that can be used to receive data if the external key is added
    // public ?string $url = null;

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to link predefined behaviour to a source';
    const string FLD_GROUP_COM = 'the message id of the translatable name of the group e.g. structured data formats used to group the source types in a selector';
    const string FLD_GROUP = 'group_msg_code_id';
    const string FLD_WIKIPEDIA_COM = 'the url of the english wikipedia page that explains the format';
    const string FLD_WIKIPEDIA = 'wikipedia';

    // list of fields that are additional to the standard type fields used for the source type
    const array FLD_LST_EXTRA = array(
        [self::FLD_GROUP, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_GROUP_COM],
        [self::FLD_WIKIPEDIA, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_WIKIPEDIA_COM],
    );


    /*
     * object vars
     */

    // the message id of the group name e.g. source_type_group_structured
    public ?string $group_msg_code_id = null;
    // the url of the english wikipedia page that explains the format
    public ?string $wikipedia = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this source type object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->group_msg_code_id = null;
        $this->wikipedia = null;
    }

    /**
     * fill the source type object vars based on an array of fields from the database or the code link csv
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, user_message $msg, string $class): bool
    {
        $result = parent::row_mapper_typ_obj($db_row, $msg, $class);
        if ($result) {
            if (array_key_exists(self::FLD_GROUP, $db_row)) {
                $this->group_msg_code_id = $db_row[self::FLD_GROUP];
            }
            if (array_key_exists(self::FLD_WIKIPEDIA, $db_row)) {
                // the code link csv writes a missing wikipedia page as NULL
                $wikipedia = $db_row[self::FLD_WIKIPEDIA];
                $this->wikipedia = $wikipedia === sql::NULL_VALUE ? null : $wikipedia;
            }
        }
        return $msg->is_ok();
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
                self::FLD_GROUP,
                self::FLD_WIKIPEDIA
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param source_type|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        source_type|db_object_seq_id $obj,
        user_message                 $msg,
        sql_type_list                $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->group_msg_code_id !== $this->group_msg_code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_GROUP,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_GROUP),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_GROUP,
                $this->group_msg_code_id,
                sql_field_type::NAME,
                $obj->group_msg_code_id
            );
        }
        if ($obj->wikipedia !== $this->wikipedia) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_WIKIPEDIA,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_WIKIPEDIA),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_WIKIPEDIA,
                $this->wikipedia,
                sql_field_type::TEXT,
                $obj->wikipedia
            );
        }
        return $lst;
    }

}
