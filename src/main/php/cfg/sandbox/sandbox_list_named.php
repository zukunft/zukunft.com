<?php

/*

    model/sandbox/sandbox_list.php - a base object for a list of user sandbox objects
    ------------------------------


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

namespace cfg\sandbox;

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_WORD_PATH . 'triple_list.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_list.php';
include_once SHARED_HELPER_PATH . 'CombineObject.php';
include_once SHARED_HELPER_PATH . 'IdObject.php';
include_once SHARED_HELPER_PATH . 'TextIdObject.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_par_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\import\import;
use cfg\phrase\phrase;
use cfg\phrase\term;
use cfg\word\triple_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\word\triple;
use cfg\word\word_list;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\library;

class sandbox_list_named extends sandbox_list
{

    // memory vs speed optimize vars for faster finding the list position by the object name
    private array $name_pos_lst;
    private bool $lst_name_dirty;

    /*
     * construct and map
     */

    /**
     * @param array $lst object array that could be set with the construction
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     */
    function __construct(user $usr, array $lst = array())
    {
        $this->name_pos_lst = array();
        $this->lst_name_dirty = false;

        parent::__construct($usr, $lst);
    }


    /*
     * set and get
     */

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    protected function set_lst_dirty(): void
    {
        parent::set_lst_dirty();
        $this->lst_name_dirty = true;
    }

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    protected function set_lst_clean(): void
    {
        parent::set_lst_clean();
        $this->lst_name_dirty = false;
    }


    /*
     * modify
     */

    /**
     * add one named object e.g. a word to the list, but only if it is not yet part of the list
     * @param sandbox_named|triple|phrase|term|null $to_add the named object e.g. a word object that should be added
     * @returns bool true the object has been added
     */
    function add(sandbox_named|triple|phrase|term|null $to_add): bool
    {
        $result = false;
        if ($to_add != null) {
            if ($this->is_empty()) {
                $result = $this->add_obj($to_add);
            } else {
                if (!in_array($to_add->id(), $this->ids())) {
                    if ($to_add->id() != 0) {
                        $result = $this->add_obj($to_add);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * add a named object to the list that does not yet have an id but has a name
     * @param sandbox_named|triple|phrase|term|null $to_add the named user sandbox object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    function add_by_name(sandbox_named|triple|phrase|term|null $to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if (!in_array($to_add->name(), array_keys($this->name_pos_lst())) or $allow_duplicates) {
            // if a sandbox object has a name, but not (yet) an id, add it nevertheless to the list
            if ($to_add->id() == null) {
                $this->set_lst_dirty();
            }
            $result = parent::add_obj($to_add, $allow_duplicates);
        }
        return $result;
    }

    /**
     * add the names and other variables from the given list and add missing words, triples, ...
     * select the related object by the id
     *
     * @param sandbox_list_named $lst_new a list of sandbox object e.g. that might have more vars set e.g. the name
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_id(sandbox_list_named $lst_new): user_message
    {
        $usr_msg = new user_message();
        foreach ($lst_new->lst() as $sbx_new) {
            if ($sbx_new->id() != 0 and $sbx_new->name() != '') {
                $sbx_old = $this->get_by_id($sbx_new->id());
                if ($sbx_old != null) {
                    $sbx_old->fill($sbx_new);
                } else {
                    $this->add($sbx_new);
                }
            } else {
                $usr_msg->add_message('id or name of word ' . $sbx_new->dsp_id() . ' missing');
            }
        }
        return $usr_msg;
    }

    /**
     * add the ids and other variables from the given list and add missing words, triples, ...
     * select the related object by the name
     *
     * @param sandbox_list_named $lst_new a list of sandbox object e.g. that might have more vars set e.g. the db id
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_name(sandbox_list_named $lst_new): user_message
    {
        $usr_msg = new user_message();
        foreach ($lst_new->lst() as $sbx_new) {
            if ($sbx_new->id() != 0 and $sbx_new->name() != '') {
                $sbx_old = $this->get_by_name($sbx_new->name());
                if ($sbx_old != null) {
                    $sbx_old->fill($sbx_new);
                } else {
                    $this->add($sbx_new);
                }
            } else {
                $usr_msg->add_message('id or name of word ' . $sbx_new->dsp_id() . ' missing');
            }
        }
        return $usr_msg;
    }


    /*
     * search
     */

    /**
     * find an object from the loaded list by name using the hash
     * should be cast by the child function get_by_name
     *
     * @param string $name the unique name of the object that should be returned
     * @return CombineObject|IdObject|TextIdObject|null the found user sandbox object or null if no name is found
     */
    function get_by_name(string $name): CombineObject|IdObject|TextIdObject|null
    {
        $key_lst = $this->name_pos_lst();
        $pos = null;
        if (key_exists($name, $key_lst)) {
            $pos = $key_lst[$name];
        }
        if ($pos !== null) {
            return $this->get($pos);
        } else {
            return null;
        }
    }

    /**
     * filters a word list by names
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param array $names with the words that should be removed
     * @returns sandbox_list_named with only the remaining words
     */
    function filter_by_name(array $names): sandbox_list_named
    {
        log_debug('->filter_by_name ' . $this->dsp_id());
        $result = clone $this;
        $result->reset();

        // check and adjust the parameters
        if (count($names) <= 0) {
            log_warning('Phrases to delete are missing.', 'word_list->filter');
        }

        foreach ($this->lst() as $wrd) {
            if (!in_array($wrd->name(), $names)) {
                $result->add_by_name($wrd);
            }
        }

        return $result;
    }

    /**
     * select a word list by names
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2016", "2017"
     *
     * @param array $names with the words that should be removed
     * @returns sandbox_list_named with only the remaining words
     */
    function select_by_name(array $names): sandbox_list_named
    {
        log_debug('->filter_by_name ' . $this->dsp_id());
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
     * modify
     */

    /**
     * TODO add a unit test
     * @returns array with all unique names of this list with the keys within this list
     */
    protected function name_pos_lst(): array
    {
        $result = array();
        if ($this->lst_name_dirty) {
            foreach ($this->lst() as $key => $obj) {
                if (!in_array($obj->name(), $result)) {
                    $result[$obj->name()] = $key;
                }
            }
            $this->name_pos_lst = $result;
            $this->lst_name_dirty = false;
        } else {
            $result = $this->name_pos_lst;
        }
        return $result;
    }

    /**
     * sort this list by name
     * @return void
     */
    function sort_by_name(): void
    {
        $result = [];
        $pos_lst = $this->names();
        natcasesort($pos_lst);
        foreach ($pos_lst as $key => $value) {
            $result[] = $this->lst()[$key];
        }
        $this->set_lst($result);
    }


    /*
     * save
     */

    /**
     * create any missing sql functions and queries to save the list objects
     * @param word_list|triple_list $db_lst filled with the words or triples that are already in the db
     * @param bool $use_func true if sql function should be used to insert the named user sandbox objects
     * @param import|null $imp the import object e.g. with the ETA
     * @param string $class the object class that should be stored in the database
     * @return user_message
     */
    function insert(
        word_list|triple_list $db_lst,
        bool                  $use_func = true,
        import                $imp = null,
        string $class = ''
    ): user_message
    {
        global $db_con;

        $lib = new library();
        $name = $lib->class_to_table($class);

        $sc = $db_con->sql_creator();
        $usr_msg = new user_message();

        // get the db id from the loaded objects
        $usr_msg->add($this->fill_by_name($db_lst));

        // get the objects that need to be added
        $db_names = $db_lst->names();
        $imp->display_progress('update ' . $name . ': ' . count($db_names), true);
        $add_lst = clone $this;
        $add_lst = $add_lst->filter_by_name($db_names);
        $imp->display_progress('add ' . $name . ': ' . $add_lst->count(), true);

        // get the sql call to add the missing objects
        $ins_calls = $add_lst->sql_call_with_par($sc, $use_func);
        $imp->display_progress('db statements ' . $ins_calls->count());

        // get the functions that are already in the database
        $db_func_lst = $db_con->get_functions();

        // get the sql functions that have not yet been created
        $func_to_create = $ins_calls->sql_functions_missing($db_func_lst);
        $imp->display_progress('create db statements ' . $func_to_create->count());

        // get the first object that have requested the missing function
        $func_create_obj = clone $this;
        $func_create_obj_names = $func_to_create->object_names();
        $func_create_obj = $func_create_obj->select_by_name($func_create_obj_names);

        // create the missing sql functions and add the first missing word
        $func_to_create = $func_create_obj->sql($sc);
        $func_to_create->exe();
        $imp->display_progress('created db statements ' . $func_to_create->count());

        // add the remaining missing words
        $add_lst = $add_lst->filter_by_name($func_create_obj_names);
        $ins_calls = $add_lst->sql_call_with_par($sc, $use_func);
        $usr_msg->add($ins_calls->exe());
        $imp->display_progress('added ' . $name . ': ' . $add_lst->count(), true);


        return $usr_msg;
    }

    /**
     * get a list of all sql functions that are needed to add all triples of this list to the database
     * @return sql_par_list with the sql function names
     */
    function sql(sql_creator $sc, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $trp) {
            // check always user sandbox and normal name, because reading from database for check would take longer
            $sc_par_lst = new sql_type_list();
            if ($use_func) {
                $sc_par_lst->add(sql_type::LOG);
            }
            $qp = $trp->sql_insert($sc, $sc_par_lst);
            $qp->obj_name = $trp->name();
            $sql_list->add($qp);
        }
        return $sql_list;
    }

    /**
     * get a list of all sql function names that are needed to add all loaded of this list to the database
     * @param bool $use_func true if sql function should be used to write the named user sandbox objects to the database
     * @return sql_par_list with the sql function names
     */
    function sql_call_with_par(sql_creator $sc, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $trp) {
            // check always user sandbox and normal name, because reading from database for check would take longer
            $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
            if ($use_func) {
                $sc_par_lst->add(sql_type::LOG);
            }
            $qp = $trp->sql_insert($sc, $sc_par_lst);
            $qp->obj_name = $trp->name();
            $sql_list->add($qp);
        }
        return $sql_list;
    }

}
