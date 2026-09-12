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
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
//include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
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
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_list;
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

/**
 * a list of references, e.g. all wikidata and wikipedia links of a word
 *
 * a ref is a user sandbox object like a word or a source, so this list loads and saves like the
 * other sandbox lists; it is not a sandbox_link_list, because a ref links a phrase to a key of
 * another system and not two objects of zukunft.com
 *
 * @author Timon Zielonka <timon@zukunft.com>
 * $abbr $ref_lst
 */
class ref_list extends sandbox_list
{

    // search and load fields
    public ?array $ids = array(); // list of the ref ids to load a list from the database

    private ?array $key_lst = [];
    private bool $key_lst_dirty = false;

    /*
     * construct and map
     */

    /**
     * map the database rows of one load to the refs of this list
     *
     * @param array $db_rows is an array of an array with the database values
     * @param user_message $msg to enrich with problems and suggested solutions
     * @param bool $load_all force to include also the excluded refs e.g. for admins
     * @return bool true if at least one ref has been loaded
     */
    protected function rows_mapper(array $db_rows, user_message $msg, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new ref($this->get_user()), $db_rows, $msg, $load_all);
    }


    /*
     * set and get
     */

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

    function api_json_array(api_type_list|array $typ_lst, user_message $msg, user|null $usr = null): array
    {
        $vars = [];
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        foreach ($this->lst() as $ref) {
            $ref_vars = $ref->api_json_array($typ_lst, $msg, $usr);
            $vars[] = $ref_vars;
        }
        return $vars;
    }


    /*
     * load
     */

    /**
     * load a list of references by their database ids, e.g. to name the changed refs of a change
     * log that lists the changes of more than one object (see change_log_list::name_lists)
     *
     * @param array $ids the database ids of the references that should be loaded
     * @param user_message $msg to collect the load warnings for the user
     * @return bool true if at least one reference has been loaded
     */
    function load_by_ids(array $ids, user_message $msg): bool
    {
        global $db_con;

        $result = false;
        $this->reset();
        // an empty id list is the normal case of an object type without a change, so it is no error
        if ($ids != []) {
            $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
            $result = $this->load($qp, $msg);
        }
        return $result;
    }

    /**
     * load all references of one phrase (e.g. the wikidata and wikipedia link of a word)
     *
     * @param int $phr_id the database id of the phrase whose references should be loaded
     * @param user_message $msg to collect the load warnings for the user
     * @return bool true if at least one reference has been loaded
     */
    function load_by_phr_id(int $phr_id, user_message $msg): bool
    {
        global $db_con;

        $this->reset();
        $qp = $this->load_sql_by_phr_id($db_con->sql_creator(), $phr_id);
        return $this->load($qp, $msg);
    }


    /*
     * load sql
     */

    /**
     * set the common part of the sql parameters to load a list of references
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(ref::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(ref::FLD_NAMES);
        $sc->set_usr_fields(ref::FLD_NAMES_USR);
        $sc->set_usr_num_fields(ref::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of references by their database ids
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids the database ids of the references that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(ref::FLD_ID, $ids, sql_par_type::INT_LIST);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load the references of one phrase
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $phr_id the database id of the phrase whose references should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_phr_id(sql_creator $sc, int $phr_id): sql_par
    {
        $qp = $this->load_sql($sc, 'phr');
        $sc->add_where(ref::FLD_FROM, $phr_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
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
        $usr = $this->get_user();
        $type = new ref($usr);
        $type->id = 1;
        $type->set_name(refs::WIKIDATA_TYPE);
        $type->set_code_id_db(refs::WIKIDATA_TYPE);
        $this->add_obj($type);
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
     * @param user_message $msg to report a reference that cannot be added
     * @returns bool true if the object has been added
     */
    function add_by_name_type_and_key(ref|null $to_add, user_message $msg): bool
    {
        $result = false;
        if ($to_add != null) {
            if (!in_array($to_add->get_key(), array_keys($this->key_list()))) {
                // add only objects that have all mandatory values, judged by a local message,
                // because can_be_ready returns the state of the given message, so a shared
                // message with an earlier error would block a reference that is fine
                $rdy_msg = new user_message($msg->usr); // the verdict of this reference
                if ($to_add->can_be_ready($rdy_msg)) {
                    $this->add_direct($to_add);
                    $result = true;
                } else {
                    // never fail silently: a reference that is dropped here is missing after the
                    // import without any trace, so report which ref is dropped and why
                    log_warning('reference ' . $to_add->dsp_id()
                        . ' not added to the list because ' . $rdy_msg->all_message_text());
                    $msg->merge($rdy_msg);
                }
            }
        } else {
            $this->add_direct($to_add);
            $result = true;
        }
        return $result;
    }

    function add_direct(ref|IdObject|TextIdObject|CombineObject|value_types|null $obj_to_add): void
    {
        parent::add_direct($obj_to_add);
        $this->key_lst[] = $obj_to_add->get_key();
    }

    function del(user_message $msg): void
    {
    }


    /*
     * save
     */

    /**
     * store all references from this list in the database using grouped calls of predefined sql functions
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param import $imp the import object with the estimate of the total save time
     * @param float $est_per_sec the expected number of sources that can be updated in the database per second
     * @return bool true if everything has been fine
     */
    function save(user_message $msg, import $imp, float $est_per_sec = 0.0): bool
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
            $ref_usr_msg = $msg->clone_reset();
            // actual save the reference to the database
            $ref->save($ref_usr_msg);
            // collect the user message for a consolidated list for the user
            $msg->merge($ref_usr_msg);
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

        return $msg->is_ok();
    }

    /**
     * drop the references the requesting user may not read, so a reference list returned by the api
     * never discloses another user's non-public reference (ref extends sandbox, see is_readable_by);
     * the filter is on sandbox_list_named, which a ref list is not, because a ref has no name column
     *
     * @param user|null $usr the user who has requested to read the list
     * @return ref_list this list with only the references readable by the given user
     */
    function filter_readable_by(?user $usr): ref_list
    {
        $result = array();
        foreach ($this->lst() as $ref) {
            if ($ref->is_readable_by($usr)) {
                $result[] = $ref;
            }
        }
        $this->set_lst($result);
        return $this;
    }

}