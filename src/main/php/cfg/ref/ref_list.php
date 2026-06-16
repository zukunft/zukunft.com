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

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql_db.php';
//include_once paths::MODEL_HELPER . 'type_list.php';
//include_once paths::MODEL_HELPER . 'type_object.php';
//include_once paths::MODEL_IMPORT . 'import.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::SHARED_CONST . 'refs.php';
//include_once paths::SHARED_CONST . 'triples.php';
//include_once paths::SHARED_CONST . 'words.php';
//include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

// TODO Prio 2 check if not better based on the sandbox_link_list
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
        parent::__construct(true);
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
    function get_user(): ?user
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
     * api
     */

    function api_json_array(api_type_list|array $typ_lst = [], user|null $usr = null): array
    {
        $vars = [];
        foreach ($this->lst() as $ref) {
            $ref_vars = $ref->api_json_array($typ_lst, $usr);
            $vars[] = $ref_vars;
        }
        return $vars;
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
        global $sys;
        $usr = $sys?->usr_req;
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

    /**
     * load all references of one phrase (e.g. the wikidata and wikipedia link of a word)
     * uses the ref query with the correct ref columns instead of the generic type_list load,
     * which selects a non-existing code_id column for the refs table
     *
     * @param int $phr_id the database id of the phrase whose references should be loaded
     * @return bool true if at least one reference has been loaded
     */
    function load_by_phr_id(int $phr_id): bool
    {
        global $db_con;
        $this->reset();
        $ref = new ref($this->get_user());
        $sc = $db_con->sql_creator();
        $qp = $ref->load_sql($sc, 'by_phr');
        $sc->add_where(ref::FLD_FROM, $phr_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $ref_obj = new ref($this->get_user());
                $ref_obj->row_mapper_sandbox($db_row);
                $this->add($ref_obj);
            }
        }
        return !$this->is_empty();
    }

    function load_sql_by_names(): sql_db
    {
        $qp = new sql_db();
        return $qp;
    }

    // TODO Prio 1 activate
    function load_sql_by_source(): sql_db
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
        global $sys;
        $usr = $sys?->usr_req;
        $type = new ref($usr);
        $type->id = 1;
        $type->set_name(refs::WIKIDATA_TYPE);
        $type->set_code_id_db(refs::WIKIDATA_TYPE);
        $this->add($type);
    }


    /*
     * extract
     */

    /**
     * @param ?int $limit the max number of ids to show
     * @retur array the list of the ref ids
     */
    function ids(?int $limit = null): array
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
        // TODO Prio 1 add $usr_msg to the parameters
        $usr_msg = new user_message();
        $result = false;
        if ($to_add != null) {
            if (!in_array($to_add->get_key(), array_keys($this->key_list()))) {
                // add only objects that have all mandatory values
                if ($to_add->can_be_ready($usr_msg)) {
                    $this->add_direct($to_add);
                }
            }
        } else {
            $this->add_direct($to_add);
            $result = true;
        }
        return $result;
    }

    function add_direct(ref|type_object|IdObject|TextIdObject|CombineObject|value_types|null $obj_to_add): void
    {
        parent::add_direct($obj_to_add);
        $this->key_lst[] = $obj_to_add->get_key();
    }

    function del(user_message $usr_msg): void
    {
    }


    /*
     * save
     */

    /**
     * store all references from this list in the database using grouped calls of predefined sql functions
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param import $imp the import object with the estimate of the total save time
     * @param float $est_per_sec the expected number of sources that can be updated in the database per second
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg, import $imp, float $est_per_sec = 0.0): bool
    {
        global $cfg;

        $load_per_sec = $cfg->get_by([words::REFERENCES, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], def::FALLBACK_IMPORT_PER_SEC);
        $save_per_sec = $cfg->get_by([words::REFERENCES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], def::FALLBACK_IMPORT_PER_SEC);

        // TODO replace this slow solution
        foreach ($this->lst() as $ref) {
            // TODO Prio 1 avoid this workaround
            if ($ref->get_user()->id <= 0) {
                $ref->set_user($this->get_user());
            }
            // for each item of a list an empty user_message statement should be used
            // so that an issue in one item does not prevent other item from being saved
            $ref_usr_msg = $usr_msg->clone_reset();
            // actual save the reference to the database
            $ref->save($ref_usr_msg);
            // collect the user message for a consolidated list for the user
            $usr_msg->merge($ref_usr_msg);
        }
        /*
        if ($this->is_empty()) {
            $usr_msg->add_info('no references to save');
        } else {
            // load the references that are already in the database
            $step_time = $this->count() / $load_per_sec;
            $imp->step_start(msg_id::LOAD, ref::class, $this->count(), $step_time);
            $db_lst = new ref_list($this->get_user());
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

        return $usr_msg->is_ok();
    }

}