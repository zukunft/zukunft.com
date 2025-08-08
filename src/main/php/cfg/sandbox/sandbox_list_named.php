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

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_COMPONENT . 'component_list.php';
//include_once paths::MODEL_HELPER . 'data_object.php';
//include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
//include_once paths::MODEL_IMPORT . 'import.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
//include_once paths::MODEL_PHRASE . 'phrase_list.php';
//include_once paths::MODEL_PHRASE . 'term.php';
//include_once paths::MODEL_REF . 'source_list.php';
//include_once paths::MODEL_WORD . 'triple_list.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'view_list.php';
//include_once paths::MODEL_WORD . 'triple.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED . 'library.php';

use cfg\component\component;
use cfg\component\component_list;
use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\db\sql_par_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\data_object;
use cfg\helper\db_object_seq_id;
use cfg\import\import;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\ref\source_list;
use cfg\view\view;
use cfg\view\view_list;
use cfg\word\triple_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\word\triple;
use cfg\word\word_list;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\enum\value_types;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\library;

class sandbox_list_named extends sandbox_list
{

    // memory vs speed optimize vars for faster finding the list position by the object name
    private array $name_pos_lst;
    private bool $lst_name_dirty;
    private array $name_pos_lst_all;
    private bool $lst_name_dirty_all;

    /*
     * construct and map
     */

    /**
     * @param array $lst object array that could be set with the construction
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     */
    function __construct(user $usr, array $lst = [])
    {
        $this->name_pos_lst = [];
        $this->name_pos_lst_all = [];
        $this->set_lst_dirty();

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
        $this->lst_name_dirty_all = true;
    }

    /**
     * @return true if the name hash table is updated
     */
    protected function is_name_list_dirty(): bool
    {
        return $this->lst_name_dirty;
    }

    /**
     * @return true if the name hash table including the excluded is updated
     */
    protected function is_all_name_list_dirty(): bool
    {
        return $this->lst_name_dirty_all;
    }

    /**
     * @returns array with all unique names of this list with the keys within this list
     */
    function name_pos_lst(): array
    {
        $result = array();
        if ($this->is_name_list_dirty()) {
            foreach ($this->lst() as $key => $obj) {
                $result[$obj->name()] = $key;
            }
            $this->name_pos_lst = $result;
            $this->lst_name_dirty = false;
        } else {
            $result = $this->name_pos_lst;
        }
        return $result;
    }

    /**
     * like name_pos_lst but include also the excluded names e.g. for import
     * @returns array with all unique names of this list with the keys within this list
     */
    function name_pos_lst_all(): array
    {
        $result = array();
        if ($this->is_all_name_list_dirty()) {
            foreach ($this->lst() as $key => $obj) {
                $result[$obj->name(true)] = $key;
            }
            $this->name_pos_lst_all = $result;
            $this->lst_name_dirty_all = false;
        } else {
            $result = $this->name_pos_lst_all;
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * load a list by the names
     * @param array $names a named object used for selection e.g. a word type
     * @param bool $load_all force to include also the excluded triples e.g. for admins
     * @return bool true if at least one found
     */
    function load_by_names(array $names = [], bool $load_all = false): bool
    {
        global $db_con;
        if (count($names) > 0) {
            $sc = $db_con->sql_creator();
            $qp = $this->load_sql_by_names($sc, $names);
            return $this->load($qp, $load_all);
        } else {
            return false;
        }
    }

    /**
     * set the SQL query parameters to load a list by the names
     * @param sql_creator $sc with the target db_type set
     * @param array $names a list of strings with the names
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array       $names,
        string      $fld = ''
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'names');
        if (count($names) > 0) {
            $sc->add_where($fld, $names, sql_par_type::TEXT_LIST);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve a list from the database
     * expected to be overwritten by the child objects to set the specific vars
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= $query_name;

        $sc->set_name($qp->name);

        return $qp;
    }


    /*
     * im- and export
     */

    /**
     * import a list of views from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        return new user_message();
    }


    /*
     * info
     */

    /**
     * reports the difference to the given word list as a human-readable messages
     * @param sandbox_list_named $sbx_lst the list of the object to compare with
     * @param msg_id $msg_missing the message id for a missing sandbox object
     * @param msg_id $msg_id_missing the message id for a missing sandbox object id
     * @param msg_id $msg_additional the message id for an additional sandbox object
     * @param msg_id $msg_id_additional the message id for an additional sandbox object id
     * @return user_message
     */
    function diff_msg(
        sandbox_list_named $sbx_lst,
        msg_id             $msg_missing = msg_id::WORD_MISSING,
        msg_id             $msg_id_missing = msg_id::WORD_ID_MISSING,
        msg_id             $msg_additional = msg_id::WORD_ADDITIONAL,
        msg_id             $msg_id_additional = msg_id::WORD_ID_ADDITIONAL
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($this->lst() as $sbx) {
            $sbx_to_chk = $sbx_lst->get($sbx->id());
            if ($sbx_to_chk == null) {
                $sbx_to_chk = $sbx_lst->get_by_name($sbx->name());
                if ($sbx_to_chk == null) {
                    $vars = [msg_id::VAR_NAME => $sbx->dsp_id()];
                    $usr_msg->add_id_with_vars($msg_missing, $vars);
                } else {
                    $vars = [msg_id::VAR_ID => $sbx->dsp_id()];
                    $usr_msg->add_id_with_vars($msg_id_missing, $vars);
                }
            }
            if ($sbx_to_chk != null) {
                $usr_msg->add($sbx->diff_msg($sbx_to_chk));
            }
        }
        foreach ($sbx_lst->lst() as $sbx) {
            $sbx_to_chk = $this->get($sbx->id());
            if ($sbx_to_chk == null) {
                $sbx_to_chk = $sbx_lst->get_by_name($sbx->name());
                if ($sbx_to_chk == null) {
                    $vars = [msg_id::VAR_NAME => $sbx->dsp_id()];
                    $usr_msg->add_id_with_vars($msg_additional, $vars);
                } else {
                    $vars = [msg_id::VAR_ID => $sbx->$sbx->dsp_id()];
                    $usr_msg->add_id_with_vars($msg_id_additional, $vars);
                }
            }
        }
        return $usr_msg;
    }

    /**
     * get the words, formulas, components that needs to be saved to the database
     * TODO review overwrites and e.g. check word list usage
     * @return sandbox_list_named with all named sandbox object that does not yet have a database id
     */
    function missing_ids(): sandbox_list_named
    {
        $lst = clone $this;
        $lst->reset();
        foreach ($this->lst() as $sbx) {
            if ($sbx->id() == 0) {
                $lst->add_by_name_direct($sbx);
            }
        }
        return $lst;
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

        // second line of defence
        // TODO Prio 2 review
        if ($this::class == triple_list::class and $to_add::class != triple::class) {
            log_err('trying to add a none triple to a triple list');
        }
        if ($this::class == view_list::class and $to_add::class != view::class) {
            log_err('trying to add a none view to a view list');
        }
        if ($this::class == component_list::class and $to_add::class != component::class) {
            log_err('trying to add a none component to a component list');
        }

        if ($to_add != null) {
            if ($this->is_empty()) {
                $usr_msg = $this->add_obj($to_add);
                $result = $usr_msg->is_ok();
            } else {
                if (!array_key_exists($to_add->id(), $this->id_pos_lst())) {
                    if ($to_add->id() != 0) {
                        $usr_msg = $this->add_obj($to_add);
                        $result = $usr_msg->is_ok();
                    }
                } else {
                    log_debug($to_add->dsp_id() . ' not added, because it is already in the list');
                }
            }
        }
        return $result;
    }

    /**
     * add a named object to the list that does not yet have an id but has a name
     * @param sandbox_named|triple|phrase|term|null $obj_to_add the named user sandbox object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    function add_by_name(sandbox_named|triple|phrase|term|null $obj_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if ($obj_to_add != null) {
            // if a sandbox object has a name, but not (yet) an id, add it nevertheless to the list
            $name = $obj_to_add->name();
            if ($name != '') {
                if (!in_array($name, array_keys($this->name_pos_lst())) or $allow_duplicates) {
                    // add only objects that have all mandatory values
                    $result = $obj_to_add->can_be_ready()->is_ok();

                    if ($result) {
                        $this->add_direct($obj_to_add);
                        $this->set_lst_dirty();
                    }
                } else {
                    $result = parent::add_obj($obj_to_add, $allow_duplicates)->is_ok();
                }
            }
        }
        return $result;
    }

    /**
     * add a named object to the list that does not yet have an id but has a name
     * without checking if the object is db ready
     * used e.g. for the triple import to update triple fields
     * without repeating the links in the import json message
     * @param sandbox_named|triple|phrase|term|null $obj_to_add the named user sandbox object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    function add_by_name_direct(sandbox_named|triple|phrase|term|null $obj_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if ($obj_to_add != null) {
            // if a sandbox object has a name, but not (yet) an id, add it nevertheless to the list
            $name = $obj_to_add->name(true);
            if ($name != '') {
                if (!in_array($name, array_keys($this->name_pos_lst())) or $allow_duplicates) {
                    $this->add_direct($obj_to_add);
                    $this->set_lst_dirty();
                } else {
                    $result = parent::add_obj($obj_to_add, $allow_duplicates)->is_ok();
                }
            }
        }
        return $result;
    }

    /**
     * add the object to the list without duplicate check
     * and add the id to the id hash
     *
     * @param IdObject|TextIdObject|CombineObject|value_types|sandbox_named|triple|phrase|term|null $obj_to_add
     * @return void
     */
    protected function add_direct(IdObject|TextIdObject|CombineObject|value_types|sandbox_named|triple|phrase|term|null $obj_to_add): void
    {
        if (!$this->is_name_list_dirty()) {
            $this->name_pos_lst[$obj_to_add->name()] = count($this->lst());
        }
        if (!$this->is_all_name_list_dirty()) {
            // TODO add handling of excluded named objects
            $this->name_pos_lst_all[$obj_to_add->name(true)] = count($this->lst());
        }
        parent::add_direct($obj_to_add);
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
        global $usr;
        $usr_msg = new user_message();
        foreach ($lst_new->lst() as $sbx_new) {
            if ($sbx_new->id() != 0 and $sbx_new->name() != '') {
                $sbx_old = $this->get_by_id($sbx_new->id());
                if ($sbx_old != null) {
                    $sbx_old->fill($sbx_new, $usr);
                } else {
                    $this->add($sbx_new);
                }
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::FILL_OBJECT_ID_MISSING, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($sbx_new::class),
                    msg_id::VAR_NAME => $sbx_new->dsp_id()
                ]);
            }
        }
        return $usr_msg;
    }

    /**
     * add the ids and other variables from the given list and add missing words, triples, ...
     * select the related object by the name
     *
     * @param sandbox_list_named $db_lst a list of sandbox objects that might have more vars set e.g. the db id
     * @param bool $fill_all force to include also the excluded names e.g. for import
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_name(
        sandbox_list_named $db_lst,
        bool               $fill_all = false
    ): user_message
    {
        global $usr;
        $usr_msg = new user_message();

        // loop over the objects of theis list because it is expected to be smaller than tha cache list
        foreach ($this->lst() as $obj_to_fill) {
            if ($obj_to_fill->id() == 0 and $obj_to_fill->name($fill_all) != '') {
                $db_obj = $db_lst->get_by_name($obj_to_fill->name($fill_all), $fill_all);
                if ($db_obj != null) {
                    $obj_to_fill->fill($db_obj, $usr);
                }
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::USED_OBJECT_ID_AND_NAME_MISSING, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($obj_to_fill::class),
                    msg_id::VAR_WORD_NAME => $obj_to_fill->dsp_id(),
                    msg_id::VAR_NAME => $this->name()
                ]);
            }
        }
        return $usr_msg;
    }

    function add_id_by_name(array $id_lst, string $class): user_message
    {
        $usr_msg = new user_message();
        foreach ($id_lst as $name => $id) {
            if ($id != 0 and $name != '') {
                $sbx_old = $this->get_by_name($name);
                if ($sbx_old != null) {
                    $sbx_old->set_id($id);
                } else {
                    $lib = new library();
                    $usr_msg->add_id_with_vars(msg_id::ADDED_OBJECT_NOT_FOUND, [
                        msg_id::VAR_CLASS_NAME => $lib->class_to_name($class),
                        msg_id::VAR_NAME => $name
                    ]);
                }
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::ADDED_OBJECT_ID_MISSING, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($class),
                    msg_id::VAR_NAME => $name
                ]);
            }
        }
        return $usr_msg;
    }

    /**
     * add the named sandbox objects of the given list to this list but avoid duplicates
     * merge as a function, because the array_merge does not create an object
     * @param sandbox_list_named $lst_to_add with the terms to be added
     * @return sandbox_list_named with all terms of this list and the given list
     */
    function merge(sandbox_list_named $lst_to_add): sandbox_list_named
    {
        if (!$lst_to_add->is_empty()) {
            foreach ($lst_to_add->lst() as $obj_to_add) {
                $this->add($obj_to_add);
            }
        }
        return $this;
    }


    /*
     * search
     */

    /**
     * find an object from the loaded list by name using the hash
     * should be cast by the child function get_by_name
     *
     * @param string $name the unique name of the object that should be returned
     * @param bool $use_all force to include also the excluded names e.g. for import
     * @return phrase|term|CombineObject|IdObject|TextIdObject|null the found user sandbox object or null if no name is found
     */
    function get_by_name(string $name, bool $use_all = false): phrase|term|CombineObject|IdObject|TextIdObject|null
    {
        if ($use_all) {
            $key_lst = $this->name_pos_lst_all();
        } else {
            $key_lst = $this->name_pos_lst();
        }
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
     * add one object to the list of user sandbox objects, but only if it is not yet part of the list
     * @param IdObject|TextIdObject|CombineObject|db_object_seq_id|sandbox $obj_to_add the backend object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns user_message if adding failed or something is strange the messages for the user with the suggested solutions
     */
    function add_obj(
        IdObject|TextIdObject|CombineObject|db_object_seq_id|sandbox $obj_to_add,
        bool                                                         $allow_duplicates = false
    ): user_message
    {
        $usr_msg = new user_message();

        // add only objects that have all mandatory values
        $usr_msg->add($obj_to_add->db_ready());

        // add only object with the same user
        $usr_msg->add($this->same_user($obj_to_add));

        // do not create duplicates if not explicitly allowed
        if ($obj_to_add->id() <> 0 or $obj_to_add->name() != '') {
            if ($allow_duplicates) {
                $usr_msg->add(parent::add_obj($obj_to_add, $allow_duplicates));
            } else {
                if ($obj_to_add->id() <> 0) {
                    if (!array_key_exists($obj_to_add->id(), $this->id_pos_lst())) {
                        $usr_msg->add(parent::add_obj($obj_to_add));
                    } else {
                        $usr_msg->add_id_with_vars(msg_id::LIST_DOUBLE_ENTRY,
                            [
                                msg_id::VAR_NAME => $obj_to_add->dsp_id(),
                                msg_id::VAR_CLASS_NAME => $obj_to_add::class
                            ]);
                    }
                } elseif ($obj_to_add->name() != '') {
                    if (!in_array($obj_to_add->name(), $this->names())) {
                        $usr_msg->add(parent::add_obj($obj_to_add));
                    } else {
                        $usr_msg->add_id_with_vars(msg_id::LIST_DOUBLE_ENTRY,
                            [
                                msg_id::VAR_NAME => $obj_to_add->dsp_id(),
                                msg_id::VAR_CLASS_NAME => $obj_to_add::class
                            ]);
                    }
                }
            }
        }
        return $usr_msg;
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

    function name_id_list(): array
    {
        $result = array();
        foreach ($this->lst() as $obj) {
            $result[$obj->id()] = $obj->name();
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * select the sandbox objects that needs to be updated in the database
     * @param sandbox_list_named $db_lst list of sandbox objects as loaded from the database
     * @return sandbox_list_named with the sandbox objects that needs to be updated
     */
    function update_list(sandbox_list_named $db_lst): sandbox_list_named
    {
        $upd_lst = clone $this;
        $upd_lst->reset();
        foreach ($this->lst() as $sbx) {
            // TODO test if get_by_obj_id is faster
            $db_sbx = $db_lst->get_by_name($sbx->name());
            if ($db_sbx != null) {
                if ($sbx->needs_db_update($db_sbx)) {
                    $upd_lst->add($sbx);
                }
            }
        }
        return $upd_lst;
    }

    /**
     * select the sandbox objects that can be deleted from the database because they are not used
     * @param sandbox_list_named $db_lst list of sandbox objects as loaded from the database
     * @return sandbox_list_named with the sandbox objects that can be deleted
     */
    function delete_list(sandbox_list_named $db_lst): sandbox_list_named
    {
        $del_lst = clone $this;
        $del_lst->reset();
        foreach ($this->lst() as $sbx) {
            // TODO test if get_by_obj_id is faster
            $db_sbx = $db_lst->get_by_name($sbx->name(true), true);
            if ($db_sbx != null) {
                // TODO review not_used so that e.g. words are "not_used" that have an owner but are not used for other object like values
                // if ($sbx->is_excluded() and $sbx->not_used()) {
                if ($sbx->is_excluded()) {
                    $del_lst->add($sbx);
                }
            }
        }
        return $del_lst;
    }


    /*
     * save
     */

    /**
     * store all named sandbox objects from this list in the database using grouped calls of predefined sql functions
     *
     * @param import $imp the import object with the estimate of the total save time
     * @param string $cfg_wrd the word related to the class to select the config values
     * @param string $class the class name of the list entries that should be saved e.g. word or formula
     * @return user_message the problem description what has failed and a suggested solution
     */
    function save_block_wise(
        import             $imp,
        string             $cfg_wrd,
        string             $class,
        sandbox_list_named $db_lst
    ): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        $load_per_sec = $cfg->get_by([$cfg_wrd, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $upd_per_sec = $cfg->get_by([$cfg_wrd, words::UPDATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $del_per_sec = $cfg->get_by([$cfg_wrd, words::DELETE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        if ($this->is_empty()) {
            $usr_msg->add_info_text('no ' . $cfg_wrd . ' to save');
        } else {
            // load the sandbox objects that are already in the database
            $step_time = $this->count() / $load_per_sec;
            $imp->step_start(msg_id::LOAD, $class, $this->count(), $step_time);
            $db_lst->load_by_names($this->names());
            $imp->step_end($db_lst->count(), $load_per_sec);

            // create any missing sql functions and insert the missing sandbox objects
            $usr_msg->add($this->insert($db_lst, true, $imp, $class));

            // create any missing sql update functions and update the sandbox objects
            // TODO create a test that fields not included in the import message are not updated, but e.g. an empty description is updated
            // TODO create blocks of update function calls
            $usr_msg->add($this->update($db_lst, true, $imp, $class, $upd_per_sec));

            // create any missing sql delete functions and delete unused sandbox objects
            $usr_msg->add($this->delete($db_lst, true, $imp, $class, $del_per_sec));
        }

        return $usr_msg;
    }

    /**
     * create any missing sql functions and queries to save the list objects
     * TODO create blocks of insert function calls
     * *
     * @param word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst filled with the words or triples that are already in the db so a kind of cache
     * @param bool $use_func true if sql function should be used to insert the named user sandbox objects
     * @param import|null $imp the import object e.g. with the ETA
     * @param string $class the object class that should be stored in the database
     * @return user_message in case of an issue the problem description what has failed and a suggested solution
     */
    function insert(
        word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst,
        bool                                                             $use_func = true,
        import                                                           $imp = null,
        string                                                           $class = ''
    ): user_message
    {
        global $db_con;
        global $cfg;

        // prepare
        $sc = $db_con->sql_creator();
        $usr_msg = new user_message();
        $lib = new library();

        // get the configuration values
        $cfg_wrd = $lib->class_to_word($class);
        $save_per_sec = $cfg->get_by([$cfg_wrd, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        // get the db id from the loaded objects
        $usr_msg->add($this->fill_by_name($db_lst, true, false));

        // get the objects that need to be added
        $db_names = $db_lst->names();
        $imp->step_start(msg_id::CHECK, $class, count($db_names));
        $add_lst = clone $this;
        $add_lst = $add_lst->filter_by_name($db_names);
        $imp->step_end(count($db_names));

        if (!$add_lst->is_empty()) {

            // get the sql call to add the missing objects
            // TODO use sql_insert ?
            $ins_calls = $add_lst->sql_insert_call_with_par($sc, $use_func);
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
            $imp->step_start(msg_id::ADD, $class, $add_lst->count(), $step_time);
            $add_lst = $add_lst->filter_by_name($func_create_obj_names);
            $ins_calls = $add_lst->sql_insert_call_with_par($sc, $use_func);
            $usr_msg->add($ins_calls->exe($class));

            // TODO create a loop to add depending triples
            // add the just added words or triples id to this list
            $this->add_id_by_name($usr_msg->db_row_id_lst(), $class);

            $imp->step_end($add_lst->count(), $save_per_sec);

        }

        return $usr_msg;
    }

    /**
     * create any missing sql functions and queries to update the list objects
     * TODO create blocks of update function calls
     *
     * @param word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst filled with the objects that are already in the db
     * @param bool $use_func true if sql function should be used to insert the named user sandbox objects
     * @param import|null $imp the import object e.g. with the ETA
     * @param string $class the object class that should be stored in the database
     * @param float $upd_per_sec the expected updates per second used for the progress bar calculation
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function update(
        word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst,
        bool                                                             $use_func = true,
        import                                                           $imp = null,
        string                                                           $class = '',
        float                                                            $upd_per_sec = 0.1
    ): user_message
    {
        global $db_con;

        // prepare
        $sc = $db_con->sql_creator();
        $usr_msg = new user_message();

        // get the objects that need to be added
        $imp->step_start(msg_id::CHECK, $class, $db_lst->count());
        $upd_lst = $this->update_list($db_lst);
        $imp->step_end($db_lst->count());

        if (!$upd_lst->is_empty()) {

            // get the sql call to add the missing objects
            $upd_calls = $upd_lst->sql_update($sc, $db_lst, $use_func);
            $imp->step_start(msg_id::PREPARE, $class, $upd_calls->count());

            // get the functions that are already in the database
            $db_func_lst = $db_con->get_functions();

            // get the sql functions that have not yet been created
            $func_to_create = $upd_calls->sql_functions_missing($db_func_lst);

            // get the first object that have requested the missing function
            $func_create_obj = clone $upd_lst;
            $func_create_obj_names = $func_to_create->object_names();
            $func_create_obj = $func_create_obj->select_by_name($func_create_obj_names);

            // create the missing sql functions and add the first missing object
            $func_to_create = $func_create_obj->sql_update($sc, $db_lst);
            $func_to_create->exe_update($class);
            $imp->step_end($func_to_create->count());

            // add the remaining missing words or triples
            $step_time = $db_lst->count() / $upd_per_sec;
            $imp->step_start(msg_id::SAVE, $class, $db_lst->count(), $step_time);
            $upd_calls = $upd_lst->sql_update_call_with_par($sc, $db_lst, $imp->usr, $use_func);
            $usr_msg->add($upd_calls->exe_update($class));

            $imp->step_end($db_lst->count(), $upd_per_sec);
        }

        return $usr_msg;
    }

    /**
     * create any missing sql functions and queries to exclude or delete the objects of the list
     * TODO create blocks of delete function calls
     *
     * @param word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst filled with the objects that are already in the db
     * @param bool $use_func true if sql function should be used to insert the named user sandbox objects
     * @param import|null $imp the import object e.g. with the ETA
     * @param string $class the object class that should be stored in the database
     * @param float $del_per_sec the expected deletes per second used for the progress bar calculation
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function delete(
        word_list|triple_list|phrase_list|source_list|sandbox_list_named $db_lst,
        bool                                                             $use_func = true,
        import                                                           $imp = null,
        string                                                           $class = '',
        float                                                            $del_per_sec = 0.1
    ): user_message
    {
        global $db_con;

        // prepare
        $sc = $db_con->sql_creator();
        $usr_msg = new user_message();

        // get the objects that need to be added
        $imp->step_start(msg_id::CHECK, $class, $db_lst->count());
        $del_lst = $this->delete_list($db_lst);
        $imp->step_end($db_lst->count());

        if (!$del_lst->is_empty()) {

            // get the sql call to add the missing objects
            $del_calls = $del_lst->sql_delete($sc, $db_lst, $use_func);
            $imp->step_start(msg_id::PREPARE, $class, $del_calls->count());

            // get the functions that are already in the database
            $db_func_lst = $db_con->get_functions();

            // get the sql functions that have not yet been created
            $func_to_create = $del_calls->sql_functions_missing($db_func_lst);

            // get the first object that have requested the missing function
            $func_create_obj = clone $del_lst;
            $func_create_obj_names = $func_to_create->object_names();
            $func_create_obj = $func_create_obj->select_by_name($func_create_obj_names);

            // create the missing sql functions and add the first missing object
            $func_to_create = $func_create_obj->sql_delete($sc, $db_lst);
            $func_to_create->exe_delete($class);
            $imp->step_end($func_to_create->count());

            // delete upfront depending database entries like the formula elements
            $this->delete_depending($usr_msg);

            // add the remaining missing words, triples or ...
            $step_time = $db_lst->count() / $del_per_sec;
            $imp->step_start(msg_id::DEL, $class, $db_lst->count(), $step_time);
            $del_calls = $del_lst->sql_delete_call_with_par($sc, $db_lst, $use_func);
            $usr_msg->add($del_calls->exe_delete($class));

            $imp->step_end($db_lst->count(), $del_per_sec);
        }

        return $usr_msg;
    }

    /**
     * get a list of all sql functions that are needed to add all triples of this list to the database
     * @return sql_par_list with the sql function names
     */
    function sql_insert(sql_creator $sc, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            // check always user sandbox and normal name, because reading from database for check would take longer
            $sc_par_lst = new sql_type_list();
            if ($use_func) {
                $sc_par_lst->add(sql_type::LOG);
            }
            $qp = $sbx->sql_insert($sc, $sc_par_lst);
            $qp->obj_name = $sbx->name();
            $sql_list->add($qp);
        }
        return $sql_list;
    }

    /**
     * get a list of all sql functions that are needed to update all objects of this list to the database
     * @return sql_par_list with the sql function names
     */
    function sql_update(sql_creator $sc, sandbox_list_named $db_lst, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            $db_row = $db_lst->get_by_name($sbx->name());
            // another validation check as a second line of defence
            if ($db_row != null) {
                // check always user sandbox and normal name, because reading from database for check would take longer
                $sc_par_lst = new sql_type_list();
                if ($use_func) {
                    $sc_par_lst->add(sql_type::LOG);
                }
                $qp = $sbx->sql_update($sc, $db_row, $sc_par_lst);
                $qp->obj_name = $sbx->name();
                $sql_list->add_by_name($qp);
            }
        }
        return $sql_list;
    }

    /**
     * get a list of all sql functions that are needed to delete all objects of this list to the database
     * @return sql_par_list with the sql function names
     */
    function sql_delete(sql_creator $sc, sandbox_list_named $db_lst, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            $db_row = $db_lst->get_by_name($sbx->name());
            // another validation check as a second line of defence
            if ($db_row != null) {
                // check always user sandbox and normal name, because reading from database for check would take longer
                $sc_par_lst = new sql_type_list();
                if ($use_func) {
                    $sc_par_lst->add(sql_type::LOG);
                }
                $qp = $sbx->sql_delete($sc, $sc_par_lst);
                $qp->obj_name = $sbx->name();
                $sql_list->add_by_name($qp);
            }
        }
        return $sql_list;
    }

    // TODO Prio 3 use the given $usr_msg instead of $usr_msg->add() to increase speed
    protected function delete_depending(user_message $usr_msg): void
    {
        $usr_msg->add_info_text('no depending defined for ' . $this::class);
    }

    /**
     * get a list of all sql function names that are needed to add all loaded of this list to the database
     * @param bool $use_func true if sql function should be used to write the named user sandbox objects to the database
     * @return sql_par_list with the sql function names
     */
    function sql_insert_call_with_par(sql_creator $sc, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            // another validation check as a second line of defence
            if ($sbx->db_ready()) {
                // check always user sandbox and normal name, because reading from database for check would take longer
                $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
                if ($use_func) {
                    $sc_par_lst->add(sql_type::LOG);
                }
                $qp = $sbx->sql_insert($sc, $sc_par_lst);
                $qp->obj_name = $sbx->name();
                $sql_list->add($qp);
            }
        }
        return $sql_list;
    }

    /**
     * get a list of all sql function names that are needed to update all loaded of this list to the database
     * @param sql_creator $sc
     * @param sandbox_list_named $db_lst
     * @param user $usr_req the user who has requested the database update of the users
     * @param bool $use_func true if sql function should be used to write the named user sandbox objects to the database
     * @return sql_par_list with the sql function names
     */
    function sql_update_call_with_par(
        sql_creator        $sc,
        sandbox_list_named $db_lst,
        user               $usr_req,
        bool               $use_func = true
    ): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            $db_row = $db_lst->get_by_name($sbx->name());
            // another validation check as a second line of defence
            if ($db_row != null) {
                // do not overwrite db values not set by the import
                $sbx->fill($db_row, $usr_req);

                if (!$sbx->db_ready()) {
                    log_err($sbx->dsp_id() . ' is not filled in sql_update_call_with_par');
                } else {
                    if (!$sbx->needs_db_update($db_row)) {
                        log_info($sbx->dsp_id() . ' has no database relevant difference so db update is skipped');
                    } else {
                        // check always user sandbox and normal name, because reading from database for check would take longer
                        $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
                        if ($use_func) {
                            $sc_par_lst->add(sql_type::LOG);
                        }
                        $qp = $sbx->sql_update($sc, $db_row, $sc_par_lst);
                        $qp->obj_name = $sbx->name();
                        $sql_list->add($qp);
                    }
                }
            }
        }
        return $sql_list;
    }

    /**
     * get a list of all sql function names that are needed to delete all loaded of this list to the database
     * @param bool $use_func true if sql function should be used to write the named user sandbox objects to the database
     * @return sql_par_list with the sql function names
     */
    function sql_delete_call_with_par(sql_creator $sc, sandbox_list_named $db_lst, bool $use_func = true): sql_par_list
    {
        $sql_list = new sql_par_list();
        foreach ($this->lst() as $sbx) {
            $db_row = $db_lst->get_by_name($sbx->name(true));
            // another validation check as a second line of defence
            if ($db_row != null) {
                // check always user sandbox and normal name, because reading from database for check would take longer
                $sc_par_lst = new sql_type_list([sql_type::CALL_AND_PAR_ONLY]);
                if ($use_func) {
                    $sc_par_lst->add(sql_type::LOG);
                }
                $qp = $sbx->sql_delete($sc, $sc_par_lst);
                $qp->obj_name = $sbx->name(true);
                $sql_list->add($qp);
            }
        }
        return $sql_list;
    }

    /*
     * overwrite
     */

    function save(import $imp = null): user_message
    {
        $msg = 'sandbox_list_named function save not overwritten';
        log_err($msg);
        $usr_msg = new user_message();
        $usr_msg->add_warning_text($msg);
        return $usr_msg;
    }


    /*
     * debug
     */

    /**
     * TODO PRIO 1 review and move away from debug section because it is used in the import
     * @param bool $ignore_excluded if true also the excluded names are included
     * @param ?int $limit the max number of ids to show
     * @return array with all names of the list
     */
    function names(bool $ignore_excluded = false, int $limit = null): array
    {
        if ($limit == null and !$this->is_name_list_dirty()) {
            $result = array_keys($this->name_pos_lst);
        } else {
            $result = [];
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($pos <= $limit or $limit == null) {
                    $result[] = $sbx_obj->name($ignore_excluded);
                    $pos++;
                }
            }
        }
        return $result;
    }

}
