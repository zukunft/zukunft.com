<?php

/*

    model/ref/ref_list.php - al list of ref objects
    ----------------------


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

namespace cfg\ref;

use cfg\const\paths;

include_once paths::DB . 'sql_db.php';
//include_once paths::MODEL_HELPER . 'type_list.php';
//include_once paths::MODEL_HELPER . 'type_object.php';
//include_once paths::MODEL_IMPORT . 'import.php';
//include_once paths::MODEL_REF . 'ref.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::SHARED_CONST . 'refs.php';
//include_once paths::SHARED_CONST . 'triples.php';
//include_once paths::SHARED_CONST . 'words.php';
//include_once paths::SHARED_ENUM . 'messages.php';

use cfg\db\sql_db;
use cfg\helper\type_list;
use cfg\helper\type_object;
use cfg\import\import;
use cfg\user\user;
use cfg\user\user_message;
use cfg\view\view;
use shared\const\refs;
use shared\const\triples;
use shared\const\words;

class ref_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the ref list is loaded, so to say the viewer

    // search and load fields
    public ?array $ids = array(); // list of the ref ids to load a list from the database

    private ?array $key_lst = [];
    private bool $key_lst_dirty = false;

    /*
     * construct and map
     */

    /**
     * define the settings for this ref list object
     * @param user|null $usr the user who requested to see the ref list
     */
    function __construct(?user $usr = null)
    {
        parent::__construct();
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the ref list
     *
     * @param user|null $usr the person who wants to access the refs
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the refs
     */
    function user(): ?user
    {
        return $this->usr;
    }

    function key_list(): array
    {
        if ($this->key_lst_dirty) {
            foreach ($this->key_lst as $key) {
                $this->key_lst[] = $key;
            }
            $this->key_lst_dirty = false;
        }
        return $this->key_lst;
    }


    /*
     * load
     */

    /**
     * force to reload the complete list of refs from the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return bool true if at least one ref has been loaded
     */
    function load(sql_db $db_con, string $class = ref::class): bool
    {
        $result = false;
        $this->set_lst($this->load_list($db_con, $class));
        if ($this->count() > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * force to reload the complete list of refs from the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the class of the related object e.g. phrase_type or formula_type
     * @return array the list of types
     */
    protected function load_list(sql_db $db_con, string $class): array
    {
        global $usr;
        $this->reset();
        $qp = $this->load_sql_all($db_con->sql_creator(), $class);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $ref = new ref($usr);
                $ref->row_mapper_sandbox($db_row);
                $this->lst()[$db_row[$db_con->get_id_field_name($class)]] = $ref;
            }
        }
        return $this->lst();
    }

    /**
     * load a list of sources by the names
     * @param array $keys a named object used for selection e.g. a source type
     * @return bool true if at least one source found
     */
    function load_by_keys(array $keys): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_names($db_con->sql_creator(), $keys);
        return $this->load($qp);
    }

    function load_sql_by_names(): sql_db
    {
        $qp = new sql_db();
        return $qp;
    }

    /**
     * adding the refs used for unit tests to the dummy list
     * TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        global $usr;
        $type = new ref($usr);
        $type->set_id(1);
        $type->set_name(refs::WIKIDATA_TYPE);
        $type->set_code_id_db(refs::WIKIDATA_TYPE);
        $this->add($type);
    }


    /*
     * extract
     */

    /**
     * @retur array the list of the ref ids
     */
    function ids(): array
    {
        $result = array();
        if ($this->lst() != null) {
            foreach ($this->lst() as $ref) {
                if ($ref->id() > 0) {
                    $result[] = $ref->id();
                }
            }
        }
        // fallback solution if the load is not yet called e.g. for unit testing
        if (count($result) <= 0) {
            if (count($this->ids) > 0) {
                $result = $this->ids;
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a reference to the list that does not yet have an id but has the phrase name, the type and the external key set
     * @param ref|null $to_add the named user sandbox object that should be added
     * @returns bool true if the object has been added
     */
    function add_by_name_type_and_key(ref|null $to_add): bool
    {
        $result = false;
        if ($to_add != null) {
            if (!in_array($to_add->key(), array_keys($this->key_list()))) {
                // add only objects that have all mandatory values
                $result = $to_add->can_be_ready()->is_ok();

                if ($result) {
                    $this->add_direct($to_add);
                }
            }
        } else {
            $this->add_direct($to_add);
            $result = true;
        }
        return $result;
    }

    function add_direct(ref|type_object|view|null $item): void
    {
        parent::add_direct($item);
        $this->key_lst[] = $item->key();
    }


    /*
     * save
     */

    /**
     * store all references from this list in the database using grouped calls of predefined sql functions
     *
     * @param import $imp the import object with the estimate of the total save time
     * @param float $est_per_sec the expected number of sources that can be updated in the database per second
     * @return user_message
     */
    function save(import $imp, float $est_per_sec = 0.0): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        $load_per_sec = $cfg->get_by([words::REFERENCES, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $save_per_sec = $cfg->get_by([words::REFERENCES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        // TODO replace this slow solution
        foreach ($this->lst() as $ref) {
            $usr_msg->add($ref->save());
        }
        /*
        if ($this->is_empty()) {
            $usr_msg->add_info('no references to save');
        } else {
            // load the references that are already in the database
            $step_time = $this->count() / $load_per_sec;
            $imp->step_start(msg_id::LOAD, ref::class, $this->count(), $step_time);
            $db_lst = new ref_list($this->user());
            $db_lst->load_by_names($this->names());
            $imp->step_end($this->count(), $load_per_sec);

            // create any missing sql functions and insert the missing references
            $step_time = $this->count() / $save_per_sec;
            $imp->step_start(msg_id::SAVE, ref::class, $this->count(), $step_time);
            $usr_msg->add($this->insert($db_lst, true, $imp, ref::class));
            $imp->step_end($this->count(), $save_per_sec);

            // update the existing references
            // TODO create a test that fields not included in the import message are not updated, but e.g. an empty description is updated
            // loop over the references and check if all needed functions exist
            // create the missing functions
            // create blocks of update function calls
        }
        */

        return $usr_msg;
    }

}