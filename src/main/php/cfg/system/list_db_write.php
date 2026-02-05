<?php

/*

    model/system/list_db_write.php - add the database write functions to the list_db_read object
    ------------------------------

    e.g. used for the ip range list object

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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'list_db_read.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
//include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\library;

class list_db_write extends list_db_read
{

    /*
     * db add
     */

    /**
     * add all list objects to the database using grouped calls of predefined sql functions
     *
     * @param import|null $imp the import object with the estimate of the total save time
     * @param user_message $usr_msg in case of an issue the problem description what has failed and a suggested solution
     * @param string $class the object class that should be stored in the database
     * @return bool true if everything has been fine
     */
    function db_insert(user_message $usr_msg, ?import $imp, string $class): bool
    {
        global $db_con;
        global $cfg;

        if (!$this->is_empty()) {

            // prepare
            $sc = $db_con->sql_creator();
            $lib = new library();

            // get the configuration values
            $cfg_wrd = $lib->class_to_word($class);
            $save_per_sec = $cfg->get_by([$cfg_wrd, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

            // get the sql call to add the missing objects
            // TODO use sql_insert ?
            $ins_calls = $this->sql_insert_call_with_par($sc, $usr_msg);
            $imp->step_start(msg_id::PREPARE, $class, $ins_calls->count());

            // get the functions that are already in the database
            $db_func_lst = $db_con->get_functions();

            // get the sql functions that have not yet been created
            $func_to_create = $ins_calls->sql_functions_missing($db_func_lst);

            // get the first object that have requested the missing function
            $func_create_obj = clone $this;
            $func_create_obj_names = $func_to_create->object_names();
            $func_create_obj = $func_create_obj->select_by_name($func_create_obj_names);

            // create the missing sql functions and add the first missing word
            $func_to_create = $func_create_obj->sql_insert($sc);
            $func_to_create->exe($class);
            $imp->step_end($func_to_create->count());

            // add the remaining missing words or triples
            $step_time = $this->count() / $save_per_sec;
            $imp->step_start(msg_id::ADD, $class, $this->count(), $step_time);
            $add_lst = $this->filter_by_name($func_create_obj_names);
            $ins_calls = $add_lst->sql_insert_call_with_par($sc);
            $usr_msg->merge($ins_calls->exe($class));

            // TODO create a loop to add depending triples
            // add the just added words or triples id to this list
            $this->add_id_by_name($usr_msg->db_row_id_lst(), $class);

            $imp->step_end($add_lst->count(), $save_per_sec);

        }
        return $usr_msg->is_ok();
    }


    /*
     * db del
     */

    /**
     * delete all list objects from the database using grouped calls of predefined sql functions
     *
     * @param import|null $imp the import object with the estimate of the total save time
     * @param user_message $usr_msg in case of an issue the problem description what has failed and a suggested solution
     * @param string $class the object class that should be removed from the database
     * @return bool true if everything has been fine
     */
    function db_delete(user_message $usr_msg, ?import $imp, string $class): bool
    {
        global $db_con;
        global $cfg;

        if (!$this->is_empty()) {

            // prepare
            $sc = $db_con->sql_creator();
            $lib = new library();

            // get the configuration values
            $cfg_wrd = $lib->class_to_word($class);
            $save_per_sec = $cfg->get_by([$cfg_wrd, words::REMOVE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], def::FALLBACK_IMPORT_PER_SEC);

            // get the sql call to delete the objects not needed any more
            // TODO use sql_insert ?
            $del_calls = $this->sql_delete_call_with_par($sc, $usr_msg);
            $imp->step_start(msg_id::PREPARE, $class, $del_calls->count());

            // get the functions that are already in the database
            $db_func_lst = $db_con->get_functions();

            // get the sql functions that have not yet been created
            $func_to_create = $del_calls->sql_functions_missing($db_func_lst);

            // get the first object that have requested the missing function
            $func_create_obj = clone $this;
            $func_create_obj_names = $func_to_create->object_names();
            $func_create_obj = $func_create_obj->select_by_name($func_create_obj_names);

            // create the missing sql functions and add the first missing word
            $func_to_create = $func_create_obj->sql_delete($sc);
            $func_to_create->exe($class);
            $imp->step_end($func_to_create->count());

            // add the remaining missing words or triples
            $step_time = $this->count() / $save_per_sec;
            $imp->step_start(msg_id::ADD, $class, $this->count(), $step_time);
            $del_lst = $this->filter_by_name($func_create_obj_names);
            $del_calls = $del_lst->sql_delete_call_with_par($sc);
            $usr_msg->merge($del_calls->exe($class));

            // TODO create a loop to add depending triples
            // add the just added words or triples id to this list
            $this->add_id_by_name($usr_msg->db_row_id_lst(), $class);

            $imp->step_end($del_lst->count(), $save_per_sec);

        }
        return $usr_msg->is_ok();
    }


    /*
     * db helper
     */

    /**
     * get a list of all sql function names that are needed to add all objects of this list to the database
     *
     * @param user_message $usr_msg in case of an issue the problem description what has failed and a suggested solution
     * @return sql_par_list with the sql function names
     */
    function sql_insert_call_with_par(sql_creator $sc, user_message $usr_msg): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            // another validation check as a second line of defence
            if ($sbx->db_ready($usr_msg)) {
                // check always user sandbox and normal name, because reading from database for check would take longer
                $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
                $sc_par_lst->add(sql_type::LOG);
                $ins_usr_msg = new user_message();
                $qp = $sbx->sql_insert($sc, $ins_usr_msg, $sc_par_lst);
                if ($ins_usr_msg->is_ok()) {
                    $qp->obj_name = $sbx->name();
                    $sql_list->add($qp);
                } else {
                    $usr_msg->merge($ins_usr_msg);
                    log_err('Internal import error: ' . $usr_msg->all_message_text());
                }
            }
        }
        return $sql_list;
    }

    /**
     * get a list of all sql function names that are needed to delete all objects of this list from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg in case of an issue the problem description what has failed and a suggested solution
     * @param list_db_write|null $db_lst the list of delete statements that are already in the database
     * @return sql_par_list with the sql function names
     */
    function sql_delete_call_with_par(
        sql_creator $sc,
        user_message $usr_msg,
        list_db_write|null $db_lst = null
    ): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            // check always user sandbox and normal name, because reading from database for check would take longer
            $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
            $sc_par_lst->add(sql_type::LOG);
            $ins_usr_msg = new user_message();
            $qp = $sbx->sql_delete($sc, $ins_usr_msg, $sc_par_lst);
            if ($ins_usr_msg->is_ok()) {
                $qp->obj_name = $sbx->name();
                $sql_list->add($qp);
            } else {
                $usr_msg->merge($ins_usr_msg);
                log_err('Internal import error: ' . $usr_msg->all_message_text());
            }
        }
        return $sql_list;
    }

    /**
     * select a word list by names
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2016", "2017"
     *
     * @param array $names with the words that should be removed
     * @returns list_db_read with only the remaining words
     */
    function select_by_name(array $names): list_db_write
    {
        $result = clone $this;
        $result->reset();

        // check and adjust the parameters
        if (count($names) <= 0) {
            log_warning('Phrases to delete are missing.', 'word_list->filter');
        }

        foreach ($this->lst() as $wrd) {
            if (in_array($wrd->name(), $names)) {
                $result->add_by_name($wrd);
            }
        }

        return $result;
    }


    /*
     * filter
     */

    /**
     * get all objects that are not in the given list
     *
     * @param list_db_write|ListOfIdObjects $lst the list to compare with
     * @return list_db_write|ListOfIdObjects the list of objects that are only in this list
     */
    function diff(list_db_write|ListOfIdObjects $lst): list_db_write|ListOfIdObjects
    {
        return parent::diff($lst);
    }

}
