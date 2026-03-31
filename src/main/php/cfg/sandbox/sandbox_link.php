<?php

/*

    model/sandbox/sandbox_link.php - the superclass for handling user-specific link objects including the database saving
    ------------------------------

    This superclass should be used by the class word links, formula links and view link

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - sql create:        to support the initial database setup
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - info:              functions to make code easier to read
    - log:               functions to track the changes
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - message:           add message function that might be overwritten by a child object for a more precise message
    - debug:             internal support functions for debugging

    TODO Prio 2 rename predicate to type
                because predicate makes only sense for triples
                where verb is the batter name for the reference to the object
    TODO add weight with int and 100'000 as 100% because
         humans usually cannot handle more than 100'000 words
         so weight sorted list has a single place for each word


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_COMPONENT . 'component_link.php';
//include_once paths::MODEL_COMPONENT . 'component_link_type.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
//include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_LOG . 'change.php';
//include_once paths::MODEL_REF . 'ref.php';
//include_once paths::MODEL_VIEW . 'term_view.php';
//include_once paths::MODEL_VIEW . 'view_relation.php';
//include_once paths::MODEL_VIEW . 'view_relation_type.php';
//include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'formula_link_types.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED_TYPES . 'view_link_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_type;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\formula_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Exception;

class sandbox_link extends sandbox
{

    /*
     * db const
     */

    // list of fields that select the objects that should be linked
    // dummy array to enable references here and is overwritten by the child object
    const array FLD_LST_LINK = array();
    const array FLD_LST_MUST_BUT_STD_ONLY = array();

    // separator to create a unique key based on the
    const string KEY_SEP = '/';
    // to allow the usage of the name key separator within an object name
    const string KEY_SEP_ESC = '//';

    // the fields names of the link that are supposed to be overwritten by the child objects
    const string FLD_FROM = 'from_id';
    const string FLD_PREDICATE = 'predicate_id';
    const string FLD_TO = 'to_id';


    /*
     * object vars
     */

    public sandbox_named|combine_named|null $fob = null; // the (F)rom (OB)ject which this linked object is creating the connection
    public sandbox_named|combine_named|string|null $tob = null; // the (T)o (OB)ject which this linked object is creating the connection (can be a string for external keys)

    // database id of the type used for named link user sandbox objects with predefined functionality
    // which is formula link and view component link
    // repeating _sandbox_typed, because php 8.1 does not yet allow multi extends
    public ?int $predicate_id = null;

    // database fields only used for objects that link two objects
    // TODO create a more specific object that covers all the objects that could be linked e.g. linkable_object
    public ?string $from_name = null;  // the name of the from object type e.g. view for component_links
    public ?string $to_name = '';      // the name of the  to  object type e.g. view for component_links


    /*
     * construct and map
     */

    /**
     * reset all object vars of this object to the null or default value
     * used e.g. the clean up the object before the import mapping
     * @param bool $keep_user set to true to keep the original user
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->fob = null;
        $this->tob = null;
        $this->predicate_id = null;
    }

    // the row_mapper_sandbox function is not added here
    // because the db field name for the predicate differs for all objects
    // so the setting of the predicate anyway needs to be done in the overwrite function of each link

    /**
     * fill the vars with this link type sandbox object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete, the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {

        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::PREDICATE_ID, $api_json)) {
            $this->predicate_id = $api_json[json_fields::PREDICATE_ID];
        }

        return $usr_msg->is_ok();
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

        // for triples the predicate is the verb and already included in the vars at this point
        if ($this::class != triple::class) {
            if ($this->predicate_id() != 0) {
                // TODO Prio 3 review and check what is the best solution for the overwrites e.g. where is the PREDICATE really used
                if ($this::class == formula_link::class) {
                    global $sys;
                    if ($this->predicate_id() != $sys->typ_lst->frm_lnk_typ->id(formula_link_types::DEFAULT)) {
                        $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
                    }
                } elseif ($this::class == view_relation::class) {
                    global $sys;
                    if ($this->predicate_id() != $sys->typ_lst->mrl_typ->id(view_relation_type::DEFAULT)) {
                        $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
                    }
                } elseif ($this::class == term_view::class) {
                    global $sys;
                    if ($this->predicate_id() != $sys->typ_lst->msk_lnk_typ->id(view_link_types::DEFAULT)) {
                        $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
                    }
                } elseif ($this::class == component_link::class) {
                    global $sys;
                    if ($this->predicate_id() != $sys->typ_lst->cmp_lnk_typ->id(component_link_type::DEFAULT)) {
                        $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
                    }
                } else {
                    $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
                }
            }
        }

        return $vars;
    }


    /*
     * set and get
     */

    function set_fob(sandbox_named|combine_named|null $fob): void
    {
        $this->fob = $fob;
    }

    function fob(): sandbox_named|combine_named|null
    {
        return $this->fob;
    }

    /**
     * @return int the id of the linked object
     */
    function from_id(): int
    {
        if ($this->fob == null) {
            return 0;
        } else {
            return $this->fob->id();
        }
    }

    /**
     * @return string|null the name of the linked object
     */
    function from_name(): ?string
    {
        return $this->fob()?->name();
    }

    /**
     * @return int|string the id of the linked object
     */
    function from_id_or_name(): int|string
    {
        if ($this->fob == null) {
            return 0;
        } else {
            if ($this->fob->id() == 0) {
                return $this->fob->name();
            } else {
                return $this->fob->id();
            }
        }
    }

    /**
     * @return bool true if the from object is not set
     */
    function from_empty(): bool
    {
        if ($this->fob() == null) {
            return true;
        } elseif ($this->from_id() == 0
            and ($this->from_name() == null or $this->from_name() == '')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * set the database id of the type
     *
     * @param int|null $predicate_id the database id of the type
     * @return void
     */
    function set_predicate_id(?int $predicate_id): void
    {
        $this->predicate_id = $predicate_id;
    }

    /**
     * @return int|null the database id of the type
     */
    function predicate_id(): ?int
    {
        return $this->predicate_id;
    }

    /**
     * to be overwritten by the child objects
     * @return string|null the name of connection type
     */
    function predicate_name(): ?string
    {
        return null;
    }

    /**
     * to be overwritten by the child objects
     * @return string|null the name of connection type
     */
    function get_predicate_code_id(): ?string
    {
        return null;
    }

    /**
     * @return bool true if the verb object is not set
     */
    function verb_empty(): bool
    {
        if ($this->predicate_id() == 0
            and ($this->predicate_name() == null or $this->predicate_name() == '')) {
            return true;
        } else {
            return false;
        }
    }

    function set_tob(sandbox_named|combine_named|string|null $tob): void
    {
        $this->tob = $tob;
    }

    function tob(): sandbox_named|combine_named|string|null
    {
        return $this->tob;
    }

    /**
     * @return int|string|null the id of the linked object
     * or in case of an external reference the external key as a string
     */
    function to_id(): int|string|null
    {
        if ($this->tob == null) {
            return 0;
        } else {
            return $this->tob->id();
        }
    }

    /**
     * @return string the name of the linked object
     */
    function to_name(): string
    {
        return $this->tob()?->name();
    }

    /**
     * @return int|string the id of the linked object
     */
    function to_id_or_name(): int|string
    {
        if ($this->tob == null) {
            return 0;
        } else {
            if ($this->tob->id() == 0) {
                return $this->tob->name();
            } else {
                return $this->tob->id();
            }
        }
    }

    /**
     * @return bool true if the from object is not set
     */
    function to_empty(): bool
    {
        if ($this->tob() == null) {
            return true;
        } elseif ($this->to_id() == 0
            and ($this->to_name() == null or $this->to_name() == '')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * copy the link objects from this object to the given link
     * used to unset any changes in the link to detect only the changes fields that the user is allowed to change
     *
     * @param sandbox_link $lnk
     * @return sandbox_link
     */
    function set_link_objects(sandbox_link $lnk): sandbox_link
    {
        $lnk->fob = $this->fob;
        $lnk->tob = $this->tob;
        return $lnk;
    }

    /**
     * create a clone but keep the unique db ids
     *
     * @return $this a clone with the name changed
     */
    function cloned(): sandbox_link
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->id = $this->id();
        $obj_cpy->set_fob($this->fob());
        $obj_cpy->set_tob($this->tob());
        return $obj_cpy;
    }

    /**
     * @return string a unique key of the link based on the names of the objects that are linked
     */
    function get_key(): string
    {
        $from_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->from_name());
        $link_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->predicate_name());
        $to_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->to_name());
        return $from_name . self::KEY_SEP . $link_name . self::KEY_SEP . $to_name;
    }


    /*
     * settings
     */

    /**
     * @return bool true because all child objects use the link type
     */
    function is_link_type_obj(): bool
    {
        return true;
    }


    /*
     * sql create
     */

    /**
     * create an array with the fields and parameters for the sql table creation of the link object
     *
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $use_sandbox = $sc_par_lst->use_sandbox_fields();
        if (!$usr_tbl) {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(false);
            // the link fields are not repeated in the user table because they cannot be changed individually
            $fields = array_merge($fields, $this::FLD_LST_LINK);
            // set the owner of the link
            $fields = array_merge($fields, sandbox::FLD_ALL_OWNER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_STD_ONLY);
            // fields that can be changed the user but are empty if the user has not overwritten the fields
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        } else {
            // the primary id field is always the first
            $fields = $this->sql_id_field_par(true);
            // a user overwrite must always have a user
            $fields = array_merge($fields, sandbox::FLD_ALL_CHANGER);
            // mandatory fields that can be changed the user
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
            // fields that can be changed the user but are empty if the user has not overwritten the fields
            $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        }
        if ($use_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }


    /*
     * load
     */

    /**
     * load a named user sandbox object by name
     * @param int $from the subject object id
     * @param int $predicate_id the predicate object id
     * @param int|string $to the object (grammar) object id or the unique external key
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $predicate_id = 0, int|string $to = 0, string $class = ''): int
    {
        global $db_con;

        if ($class == '') {
            $class = $this::class;
        }

        $lib = new library();
        log_debug($lib->dsp_array(array($from, $predicate_id, $to)));
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $predicate_id, $to, $class);
        return parent::load($qp);
    }

    /**
     * create an SQL statement to retrieve a user sandbox link by the ids of the linked objects from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $from the subject object id
     * @param int $predicate_id the predicate object id
     * @param int|string $to the object (grammar) object id or the unique external key
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_link(sql_creator $sc, int $from, int $predicate_id, int|string $to, string $class): sql_par
    {
        if ($predicate_id > 0) {
            $qp = $this->load_sql($sc, 'link_type_ids');
            $sc->add_where($this->from_field(), $from);
            $sc->add_where($this->type_field(), $predicate_id);
        } else {
            $qp = $this->load_sql($sc, 'link_ids');
            $sc->add_where($this->from_field(), $from);
        }
        $sc->add_where($this->to_field(), $to);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the object parameters for all users by the link ids
     * to be overwritten by the child objects
     *
     * @param int $from_id the id of the from link object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_link(
        int          $from_id,
        int          $to_id,
        user_message $msg
    ): bool
    {
        $msg->add(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'load_standard_by_link',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $msg->is_ok();
    }

    /**
     * load the object parameters for all users by the standard formula link from the database
     * to be overwritten by the child objects
     *
     * @param int $from_id the id of the from link object
     * @param int $typ_id the id of the verb object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_type_link(
        int          $from_id,
        int          $typ_id,
        int          $to_id,
        user_message $msg
    ): bool
    {
        $msg->add(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'load_standard_by_type_link',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $msg->is_ok();
    }

    /**
     * load the object parameters for all users by the link id
     *
     * @param string $from_fld the id field name of the from link object
     * @param int $from_id the id of the from link object
     * @param string $to_fld the id field name of the to link object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_link_parent(
        string       $from_fld,
        int          $from_id,
        string       $to_fld,
        int          $to_id,
        user_message $msg
    ): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_standard_by_link($from_fld, $from_id, $to_fld, $to_id, $sc);

        $db_row = $db_con->get1($qp, $msg);
        if (!$this->row_mapper_sandbox(
            $db_row, true, false)) {
            $lib = new library();
            $msg->add(msg_id::LOAD_STANDARD_MAPPING_FAILED, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $msg->is_ok();
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard_by_link(
        string      $from_fld,
        int         $from_id,
        string      $to_fld,
        int         $to_id,
        sql_creator $sc
    ): sql_par
    {
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= 'link_ids';

        $sc->set_class($this::class);
        $sc->set_name($qp->name);
        $sc->set_fields($this->all_fields());
        $sc->add_where($from_fld, $from_id);
        $sc->add_where($to_fld, $to_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the object parameters for all users by the link id
     *
     * @param string $from_fld the id field name of the from link object
     * @param int $from_id the id of the from link object
     * @param string $to_fld the id field name of the to link object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_type_link_parent(
        string       $from_fld,
        int          $from_id,
        string       $type_fld,
        int          $type_id,
        string       $to_fld,
        int          $to_id,
        user_message $msg
    ): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_standard_by_type_link($from_fld, $from_id, $type_fld, $type_id, $to_fld, $to_id, $sc);

        $db_row = $db_con->get1($qp, $msg);
        if (!$this->row_mapper_sandbox(
            $db_row, true, false)) {
            $lib = new library();
            $msg->add(msg_id::LOAD_STANDARD_MAPPING_FAILED, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $msg->is_ok();
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard formula link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard_by_type_link(
        string      $from_fld,
        int         $from_id,
        string      $type_fld,
        int         $type_id,
        string      $to_fld,
        int|string  $to_id,
        sql_creator $sc
    ): sql_par
    {
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= 'link_type_ids';

        $sc->set_class($this::class);
        $sc->set_name($qp->name);
        $sc->set_fields($this->all_fields());
        $sc->add_where($from_fld, $from_id);
        $sc->add_where($type_fld, $type_id);
        if ($to_fld != '') {
            $sc->add_where($to_fld, $to_id);
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * info
     */

    /**
     * @return bool true if the object value is valid for identifying a unique link
     */
    function is_unique(): bool
    {
        $result = false;
        if ($this->id() > 0) {
            $result = true;
        } else {
            if ($this->fob != null and $this->tob != null) {
                if ($this->fob->id() > 0 and $this->tob->id() > 0) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the link object (e.g. triple) might be added to the database
     * if all related objects have been added to the database
     * @param user_message|Message $msg to add the suggested solutions if something is missing e.g. a linked object
     * @return bool true if the link can be added to the database after the linked objects have been added
     */
    function can_be_ready(user_message|Message $msg): bool
    {
        parent::db_ready($msg);

        if ($this->needs_from()) {
            if ($this->fob == null) {
                $msg->add(msg_id::FROM_MISSING,
                    [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                $this->fob->can_be_ready($msg);
            }
        }
        if ($this->needs_to()) {
            if ($this->tob == null) {
                $msg->add(msg_id::TO_MISSING,
                    [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                // a reference have only an external key but not a target object
                if ($this::class != ref::class) {
                    $this->tob->can_be_ready($msg);
                }
            }
        }
        return $msg->is_ok();
    }

    function needs_from(): bool
    {
        return true;
    }

    function needs_to(): bool
    {
        return true;
    }

    /**
     * returns ok message if this link e.g. triple can be added to the database
     * if e.g. the database id of the from or the to object is missing
     *         first the linked object needs to be added to the database
     * @param user_message|Message $msg is enriched with the explanation why the link cannot yet be added to the database
     * @return bool false if something is missing
     */
    function db_ready(user_message|Message $msg): bool
    {
        parent::db_ready($msg);

        if ($this->needs_from()) {
            if ($this->fob == null) {
                // for some triples it is ok if the from object is not set
                // e.g. per day
                $msg->add(msg_id::FROM_MISSING,
                    [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                // if the from object is set it should be valid
                // e.g. for cubic meter per second
                if (!$this->fob->is_valid()) {
                    $msg->add(msg_id::FROM_ZERO_ID,
                        [msg_id::VAR_NAME => $this->dsp_id()]);
                }
            }
        }
        if ($this->needs_to()) {
            if ($this->tob == null) {
                $msg->add(msg_id::TO_MISSING,
                    [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                if (!$this->tob->is_valid()) {
                    $msg->add(msg_id::TO_ZERO_ID,
                        [msg_id::VAR_NAME => $this->dsp_id()]);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * @return bool true if the triple object probably has already been added to the database
     *              false e.g. if some parameters are missing
     */
    function is_valid(): bool
    {
        if ($this->id != 0 and $this->name() != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_link|CombineObject|IdObject $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(sandbox_link|CombineObject|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->fob->id() != 0) {
            if ($this->fob->id() != $db_obj->fob->id()) {
                $result = true;
            }
        }
        if ($this->tob->id() != 0) {
            if ($this->tob->id() != $db_obj->tob->id()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        if ($this->predicate_id() != 0) {
            $api_obj->set_predicate_id($this->predicate_id());
        }
    }

    /*
    /**
     * set the vars of the minimal api object based on this link object
     * @param object $api_obj frontend API object filled with the database id
     *
     * @return void
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        if ($this->fob != null) {
            $api_obj->fob = $this->fob->api_obj();
        }
        if ($this->tob != null) {
            $api_obj->tob = $this->tob->api_obj();
        }
    }
    */


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param sandbox_link|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param sandbox_link|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return sandbox_link|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        sandbox_link|CombineObject|db_object_seq_id $std_obj,
        sandbox_link|CombineObject|db_object_seq_id $result
    ): sandbox_link|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->predicate_id() !== $this->predicate_id()) {
            $result->set_predicate_id($this->predicate_id());
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this sandbox link object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null) the type is removed
     *
     * @param sandbox|sandbox_link|CombineObject|db_object_seq_id $obj sandbox link object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox|sandbox_link|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        // fill to link objects
        if ($this->from_empty()) {
            if (!$obj->from_empty()) {
                $this->set_fob($obj->fob());
            }
        } else {
            if (!$obj->from_empty()) {
                $this->fob()->fill($obj->fob(), $usr_req);
            }
        }
        if ($this->predicate_id() === null and $obj->predicate_id() != null) {
            $this->set_predicate_id($obj->predicate_id());
        }
        if ($this->to_empty()) {
            if (!$obj->to_empty()) {
                $this->set_tob($obj->tob());
            }
        } else {
            if (!$obj->to_empty()) {
                $this->tob()->fill($obj->tob(), $usr_req);
            }
        }


        return $usr_msg;
    }


    /*
     * im- and export
     */

    /**
     * add the link-specific values to the export array
     * which is actually only the predicate code id
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);
        if ($this->predicate_id != null) {
            $vars[json_fields::PREDICATE] = $this->get_predicate_code_id();
        }
        return $vars;
    }


    /*
     * log
     */

    /**
     * set the log entry parameter for a new link object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     * @returns change_link with the object presets e.g. th object name
     */
    function log_link_add(): change_link
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $usr_msg = new user_message();

        $log = new change_link($this->get_user());
        $log->new_from = $this->fob;
        $log->new_to = $this->tob;

        $log->set_action(change_actions::ADD);
        // TODO add the table exceptions from sql_db
        $tbl_name = $lib->class_to_name($this::class);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->row_id = 0;
        $log->add($usr_msg);

        return $log;
    }

    /**
     * set the log entry parameter to delete an object
     * @returns change_link with the object presets e.g. th object name
     */
    function log_del_link(): change_link
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $usr_msg = new user_message();

        $log = new change_link($this->get_user());
        $log->set_action(change_actions::DELETE);
        $tbl_name = $lib->class_to_name($this::class);
        $log->set_table($tbl_name . sql_db::TABLE_EXTENSION);
        $log->old_from = $this->fob();
        $log->old_to = $this->tob();

        $log->row_id = $this->id();
        $log->add($usr_msg);

        return $log;
    }

    /**
     * TODO for normal fields use the change log, but for link changes use the link log
     * @return change|change_link the object that is used to log the user changes
     */
    function log_object(): change|change_link
    {
        return new change($this->get_user());
    }


    /*
     * save
     */

    /**
     * create a new link object and log the change
     * TODO do a rollback in case of an error
     * @param user_message $msg with status ok
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    function add(user_message $msg): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $msg, new sql_type_list([sql_type::LOG]));
        if ($msg->is_ok()) {
            $msg_txt = 'add and log ' . $this->dsp_id();
            if ($db_con->insert($qp, $msg_txt, $msg)) {
                $this->id = $msg->get_row_id();
            }
        }

        return $msg->is_ok();
    }

    /**
     * check if the id parameters are supposed to be changed
     * TODO add the link type for word links
     * @param sandbox_link $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated_link(sandbox $db_rec): bool
    {
        $result = False;
        log_debug($this->dsp_id());

        if ($db_rec->fob->id() <> $this->fob->id()
            or $db_rec->tob->id() <> $this->tob->id()) {
            $result = True;
            // TODO check if next line is needed
            // $this->reset_objects();
        }

        return $result;
    }

    /**
     * @return string text that tells the user that the change would create a duplicate
     */
    function msg_id_already_used(): string
    {
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        return 'A ' . $class_name . ' from ' . $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id() . ' already exists.';
    }

    /**
     * check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
     * @param object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same link
     */
    function is_same_std(object $obj_to_check): bool
    {
        $result = false;
        if (isset($this->fob)
            and isset($this->tob)
            and isset($obj_to_check->fob)
            and isset($obj_to_check->tob)) {
            if ($this->fob->id() == $obj_to_check->fob->id() and
                $this->tob->id() == $obj_to_check->tob->id()) {
                $result = true;
            }
        } elseif ($obj_to_check::class == triple::class) {
            if (isset($this->fob)
                and $this->has_verb()
                and isset($this->tob)
                and isset($obj_to_check->fob)
                and $obj_to_check->has_verb()
                and isset($obj_to_check->tob)) {
                if ($this->fob->id() == $obj_to_check->fob->id()
                    and $this->predicate_id() == $obj_to_check->predicate_id()
                    and $this->tob->id() == $obj_to_check->tob->id()) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object;
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @param user_message $msg the user who has requested the update and the object to collect the potential reject messages
     * @return sandbox|null a filled object that links the same objects
     *                      or null if nothing similar has been found
     */
    function get_similar(user_message $msg): ?sandbox
    {
        $sim = null;

        // check potential duplicate by name
        // check for linked objects
        if (!isset($this->fob) or !isset($this->tob)) {
            log_err('The linked objects for ' . $this->dsp_id() . ' are missing.', '_sandbox->get_similar');
        } else {
            $db_chk = $this->clone_reset(true);
            $db_chk->set_predicate_id($this->predicate_id());
            if (in_array($this::class, def::LINK_TYPE_CLASSES)) {
                if ($db_chk->load_standard_by_type_link($this->fob()->id(), $this->predicate_id(), $this->tob()->id(), $msg)) {
                    if ($db_chk->id() > 0) {
                        log_debug('the ' . $this->fob->name() . ' "' . $this->fob->name() . '" is already linked to "' . $this->tob->name() . '" of the standard link space');
                        $sim = $db_chk;
                    }
                }
            } else {
                if ($db_chk->load_standard_by_link($this->fob()->id(), $this->tob()->id(), $msg)) {
                    if ($db_chk->id() > 0) {
                        log_debug('the ' . $this->fob->name() . ' "' . $this->fob->name() . '" is already linked to "' . $this->tob->name() . '" of the standard link space');
                        $sim = $db_chk;
                    }
                }
            }
            // check with the user link space
            $db_chk->set_user($this->get_user());
            if ($db_chk->load_by_link_id($this->fob->id(), 0, $this->tob->id(), $this::class)) {
                if ($db_chk->id() > 0) {
                    log_debug('the ' . $this->fob->name() . ' "' . $this->fob->name() . '" is already linked to "' . $this->tob->name() . '" of the user link space');
                    $sim = $db_chk;
                }
            }
        }

        return $sim;
    }

    /**
     * check if target key value already exists
     * overwritten in the word class for formula link words
     * TODO load the user value not the standard value but also check the standard value
     * TODO should not ADDITIONAL the user-specific load be called
     *
     * @return sandbox object with id zero if no object with the same id is found
     */
    function get_obj_with_same_id_fields(user_message $msg): sandbox
    {
        log_debug('check if target with the name already exists ' . $this->dsp_id());
        $db_chk = $this->clone_reset();
        $chk_msg = $msg->clone_reset(); // it is in this case ok if no db row a found so an error should not influence the later process steps
        if (in_array($this::class, def::LINK_TYPE_CLASSES)) {
            $db_chk->load_standard_by_type_link($this->fob()->id(), $this->predicate_id(), $this->tob()->id(), $msg);
        } else {
            $db_chk->load_standard_by_link($this->fob()->id(), $this->tob()->id(), $chk_msg);
        }
        return $db_chk;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new link sandbox object e.g. triple to the database
     * TODO add qp merge
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst_sub->is_usr_tbl();
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        $from_can_be_missing = false;
        if ($this::class == triple::class) {
            if (in_array($this->get_predicate_code_id(), verbs::WITHOUT_FROM)) {
                $from_can_be_missing = true;
            }
        }

        // get the parameters used for the table key
        $fvt_from = $fvt_lst->get($this->from_field(), $usr_msg, $from_can_be_missing);
        $fvt_type = $fvt_lst->get($this->type_field(), $usr_msg);
        $fvt_to = $fvt_lst->get($this->to_field(), $usr_msg);

        // create the list of parameters in order of the function usage
        $fvt_insert_list = new sql_par_field_list();
        $fvt_insert_list->add_id_part($fvt_from);
        $fvt_insert_list->add_id_part($fvt_type);
        if ($fvt_to != null) {
            if ($fvt_to->id == null) {
                // for the external reference key
                $fvt_insert_list->add($fvt_to);
            } else {
                $fvt_insert_list->add_id_part($fvt_to);
            }
        }

        // create the sql to insert the row
        $sql = '';
        $sc_insert = clone $sc;
        $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        if ($sc->db_type == sql_db::MYSQL) {
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
        }
        $qp_insert->sql = $sc_insert->create_sql_insert(
            $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
        $qp_insert->par = [$fvt_from?->value, $fvt_type->value, $fvt_to?->value];

        // add the insert row to the function body
        $sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        /*
        $fvt_split_list = new sql_par_field_list();
        $fvt_split_list->add_with_split($fvt_from);
        $fvt_split_list->add_with_split($fvt_type);
        $fvt_split_list->add_with_split($fvt_to);
        */

        $qp->sql = $sql;
        $qp->par_fld_lst = $fvt_insert_list;

        return $qp;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_text(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_name = $fvt_lst->get_value($this->from_field());
        $type_name = $fvt_lst->get_value($this->type_field());
        $to_name = $fvt_lst->get_value($this->to_field());
        if ($this->is_excluded()) {
            $from_name = null;
            $type_name = null;
            $to_name = null;
        }
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_FROM_TEXT,
            $from_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_LINK_TEXT,
            $type_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_TO_TEXT,
            $to_name,
            sql_field_type::NAME
        );
        return $fvt_lst_out;
    }

    /**
     * @param sql_par_field_list $fvt_lst list of all
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_text_old(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_name = $fvt_lst->get_old($this->from_field());
        $type_name = $fvt_lst->get_old($this->type_field());
        $to_name = $fvt_lst->get_old($this->to_field());
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_FROM_TEXT,
            $from_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_LINK_TEXT,
            $type_name,
            sql_field_type::NAME
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_TO_TEXT,
            $to_name,
            sql_field_type::NAME
        );
        return $fvt_lst_out;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_id(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_id = $fvt_lst->get_id($this->from_field());
        $type_id = $fvt_lst->get_id($this->type_field());
        $to_id = $fvt_lst->get_id($this->to_field());
        if ($this->is_excluded()) {
            $from_id = null;
            $type_id = null;
            $to_id = null;
        }
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_FROM_ID,
            $from_id,
            sql_field_type::INT
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_LINK_ID,
            $type_id,
            sql_field_type::INT_SMALL
        );
        $fvt_lst_out->add_field(
            change_link::FLD_NEW_TO_ID,
            $to_id,
            sql_field_type::INT
        );
        return $fvt_lst_out;
    }

    /**
     * @return sql_par_field_list with the text values of the linked items for the log
     */
    function sql_key_fields_id_old(sql_par_field_list $fvt_lst): sql_par_field_list
    {
        $fvt_lst_out = new sql_par_field_list();
        $from_id = $fvt_lst->get_old_id($this->from_field());
        $type_id = $fvt_lst->get_old_id($this->type_field());
        $to_id = $fvt_lst->get_old_id($this->to_field());
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_FROM_ID,
            $from_id,
            sql_field_type::INT
        );
        $fvt_lst_out->add_field(
            change_link::FLD_OLD_LINK_ID,
            $type_id,
            sql_field_type::INT_SMALL
        );
        if ($to_id != null) {
            $fvt_lst_out->add_field(
                change_link::FLD_OLD_TO_ID,
                $to_id,
                sql_field_type::INT
            );
        }
        return $fvt_lst_out;
    }

    /**
     * add a message for the user that the new object has been merged with a standard object with the same name or unique key
     *
     * @param sandbox_link|sandbox $obj_to_add the object that the user wants to add to the database
     * @param user_message $msg to collect the messages and suggested solutions for the user
     * @return bool true if the merge is fine
     */
    function merged_info_message(sandbox_link|sandbox $obj_to_add, user_message $msg): bool
    {
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        $obj_to_add_name = $lib->class_to_name($obj_to_add::class);
        $msg->add_info_with_vars(msg_id::MERGED_BY_LINK_WITH_STANDARD_OBJECT, [
            msg_id::VAR_CLASS_NAME => $class_name,
            msg_id::VAR_NAME => $this->link_id(),
            msg_id::VAR_VALUE => $obj_to_add_name,
            msg_id::VAR_NAME_CHK => $obj_to_add->link_id()
        ]);
        return $msg->is_ok();
    }

    /**
     * deleting the references of links is usually needed
     * so no action is done and just true is returned
     *
     * @param user_message $usr_msg the message object just to allow overwrites e.g. for triples
     * @return bool true because a link usually does not have references
     */
    function del_links(user_message $usr_msg): bool
    {
        return true;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed of the named link object
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_all_fields_link(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        if ($usr_tbl) {
            return [$this::FLD_ID,
                user_db::FLD_ID
            ];
        } else {
            return [$this::FLD_ID,
                user_db::FLD_ID,
                $this->from_field(),
                $this->to_field()
            ];
        }
    }

    /**
     * get a list of database field names, values and types that have been updated
     * of the object to combine the list with the list of the child object e.g. word
     *
     * @param sandbox_link|db_object_seq_id $obj the same named sandbox as this to compare which fields have been changed
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_link|db_object_seq_id $obj,
        user_message                  $msg,
        sql_type_list                 $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $is_insert = $sc_par_lst->is_insert();
        $is_delete = $sc_par_lst->is_delete();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        // for insert statements of user sandbox rows user id fields always needs to be included
        if ($usr_tbl and $is_insert) {
            $lst->add_id_and_user($this);
        } else {
            $lst->add_user($this, $obj, $do_log, $table_id);
        }
        // the user cannot change the link type, because this would be another link
        if (!$usr_tbl) {
            // to delete a link, the actual link is compared with an empty link, so no message should be created
            if ($this->needs_from() and !$sc_par_lst->is_delete()) {
                if ($this->fob() == null) {
                    $this->message_from_invalid($msg);
                } elseif (!$this->fob()->is_valid()) {
                    $this->message_from_invalid($msg);
                }
            }
            if (($obj->from_id_or_name() !== $this->from_id_or_name()) or $sc_par_lst->is_insert()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                        $sys->typ_lst->cng_fld->id($table_id . $this->from_field()),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                // TODO Prio 2: move "from_" to a const and or function
                $lst->add_link_field(
                    $this->from_field(),
                    'from_' . $this->fob()?->name_field(),
                    $this->fob(),
                    $obj->fob()
                );
            }
            if ($this:: class != ref::class) {
                // to delete a link, the actual link is compared with an empty link, so no message should be created
                if ($this->needs_to() and !$sc_par_lst->is_delete()) {
                    if ($this->tob() == null) {
                        $this->message_to_invalid($msg);
                    } elseif (!$this->tob()->is_valid()) {
                        $this->message_to_invalid($msg);
                    }
                }
            }
            if (($obj->to_id_or_name() !== $this->to_id_or_name()) or $sc_par_lst->is_insert()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                        $sys->typ_lst->cng_fld->id($table_id . $this->to_field()),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                // e.g. for external references
                if ($this->tob == null and $obj->tob == null) {
                    $lst->add_field(
                        $this->to_field(),
                        $this->to_value(),
                        sql_field_type::TEXT,
                        $obj->to_value()
                    );
                } else {
                    // TODO Prio 2: move "to_" to a const and or function
                    $lst->add_link_field(
                        $this->to_field(),
                        'to_' . $this->tob()?->name_field(),
                        $this->tob(),
                        $obj->tob()
                    );
                }
            }
        } else {
            // add the from and to fields even if the objects are the same in case of an insert exclude or delete to identify the rows
            $from_fld = '';
            $to_fld = '';
            if ($is_insert) {
                $from_fld = $this->fob()?->name_field();
                if ($this->tob() == null) {
                    // e.g. for references the external key
                    $to_fld = $this->to_field();
                } else {
                    if (is_string($this->tob())) {
                        // e.g. for references the external key
                        $to_fld = $this->to_field();
                    } else {
                        $to_fld = $this->tob()->name_field();
                    }
                }
            }
            if ($is_delete) {
                $from_fld = $obj->fob()?->name_field();
                if ($obj->tob() == null) {
                    // e.g. for references the external key
                    $to_fld = $obj->to_field();
                } else {
                    if (is_string($obj->tob())) {
                        // e.g. for references the external key
                        $to_fld = $obj->to_field();
                    } else {
                        $to_fld = $obj->tob()->name_field();
                    }
                }
            }
            if ($is_insert or $is_delete) {
                if ($from_fld == $to_fld) {
                    $from_fld = sql::FROM_FLD_PREFIX . $from_fld;
                    $to_fld = sql::TO_FLD_PREFIX . $to_fld;
                }
                // TODO check how to handle if the standard
                if (($this->is_excluded() and !$obj->is_excluded()) or $is_delete) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                            $sys->typ_lst->cng_fld->id($table_id . $this->from_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_link_field(
                        $this->from_field(),
                        $from_fld,
                        null,
                        $obj->fob()
                    );
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                            $sys->typ_lst->cng_fld->id($table_id . $this->to_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    if ($this::class == ref::class) {
                        $lst->add_field(
                            $this->to_field(),
                            null,
                            sandbox_named::FLD_NAME_SQL_TYP,
                            $obj->to_value(),
                            $to_fld,
                            null,
                            null,
                            db_object_seq_id::FLD_ID_SQL_TYP
                        );
                    } else {
                        $lst->add_link_field(
                            $this->to_field(),
                            $to_fld,
                            null,
                            $obj->tob()
                        );
                    }
                } elseif ((!$this->is_excluded() and $obj->is_excluded()) or $is_delete) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->from_field(),
                            $sys->typ_lst->cng_fld->id($table_id . $this->from_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_link_field(
                        $this->from_field(),
                        $from_fld,
                        $this->fob(),
                        null
                    );
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . $this->to_field(),
                            $sys->typ_lst->cng_fld->id($table_id . $this->to_field()),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    if ($this::class == ref::class) {
                        $lst->add_field(
                            $this->to_field(),
                            $this->tob(),
                            sandbox_named::FLD_NAME_SQL_TYP,
                            null,
                            $to_fld,
                            $this->tob(),
                            null,
                            db_object_seq_id::FLD_ID_SQL_TYP
                        );
                    } else {
                        $lst->add_link_field(
                            $this->to_field(),
                            $to_fld,
                            $this->tob(),
                            null
                        );
                    }
                }
            }
        }
        return $lst;
    }


    /*
     * internal
     */

    /**
     * for most links there are no preserved names so the default value true
     * which means that the link can be saved
     * this function is overwritten by the triple object
     * because that some triples are reserved for system testing and should never be used by a user
     *
     * @param user_message $usr_msg the message object why the link is reserved and which alternative names can be used
     *                              of the internal error that an overwrite is missing to interrupt the workflow
     * @return bool true if no preserved link of link name is used and the link can be saved to the database
     */
    protected function check_save(user_message $usr_msg): bool
    {
        return true;
    }


    /**
     * @return bool true if this sandbox object links two objects (final function)
     */
    function is_link_obj(): bool
    {
        return true;
    }

    /**
     * @return bool true if this sandbox object has a name as unique key (final function)
     */
    function is_named_obj(): bool
    {
        return false;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new sandbox link object to the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     * TODO check first the query name and skip the sql building if not needed
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // fields and values that the word has additional to the standard named user sandbox object
        $lnk_empty = $this->clone_reset();
        // if a user has deleted or excluded a link fill the empty object with the link id so that the link id can be used for the log
        if ($this->is_excluded()) {
            if (in_array($this::class, def::LINK_CLASSES)) {
                $lnk_empty->set_fob($this->fob());
                if ($this::class == ref::class) {
                    $lnk_empty->external_key = $this->external_key;
                } else {
                    $lnk_empty->set_tob($this->tob());
                }
                if (in_array($this::class, def::LINK_TYPE_CLASSES)) {
                    $lnk_empty->set_predicate_id($this->predicate_id());
                }
                if ($this::class == component_link::class) {
                    // default values does not need to be inserted
                    // TODO Prio 2 do the same for all default values also of other objects
                    if ($this->pos_type?->get_code_id() == position_types::DEFAULT) {
                        $lnk_empty->pos_type = $this->pos_type;
                    }
                }
            }
        }
        // for a new component link the owner should be set, so remove the user id to force writing the user
        $lnk_empty->set_user($this->get_user()->clone_reset());
        // for linked user db rows, use the link fields of the standard row, because the link itself cannot be changed by the user
        if ($sc_par_lst_used->is_usr_tbl()) {
            $lnk_empty = $this->set_link_objects($lnk_empty);
        }
        // get the list of the changed fields
        $fvt_lst = $this->db_fields_changed($lnk_empty, $usr_msg, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $all_fields = $this->db_fields_all($sc_par_lst_used);
        // create either the prepared sql query or a sql function that includes the logging of the changes
        return parent::sql_insert_switch($sc, $fvt_lst, $all_fields, $usr_msg, $sc_par_lst_used);
    }

    /**
     * create the sql statement to update a sandbox link object in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox|db_object_seq_id $db_row the word with the database values before the update
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par|null the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_update(
        sql_creator              $sc,
        sandbox|db_object_seq_id $db_row,
        user_message             $usr_msg,
        sql_type_list            $sc_par_lst = new sql_type_list()
    ): sql_par|null
    {
        if ($this->can_update($usr_msg)) {
            // clone the sql parameter list to avoid changing the given list
            $sc_par_lst_used = clone $sc_par_lst;
            // set the sql query type
            $sc_par_lst_used->add(sql_type::UPDATE);
            // get the field names, values and parameter types that have been changed
            // and that needs to be updated in the database
            // the db_* child function call the corresponding parent function
            // including the sql parameters for logging
            $fld_lst = $this->db_fields_changed($db_row, $usr_msg, $sc_par_lst_used);
            $all_fields = $this->db_fields_all($sc_par_lst_used);
            // unlike the db_* function the sql_update_* parent function is called directly
            return $this::sql_update_switch($sc, $fld_lst, $all_fields, $usr_msg, $sc_par_lst_used);
        } else {
            return null;
        }
    }


    /*
     * message
     */

    function message_from_invalid(user_message $msg): void
    {
        $msg->add(msg_id::MANDATORY_FROM_OBJECT_INVALID, [
            msg_id::VAR_NAME_FROM => $this->fob()?->dsp_id(),
            msg_id::VAR_NAME => $this->dsp_id(),
        ]);
    }

    function message_to_invalid(user_message $msg): void
    {
        $msg->add(msg_id::MANDATORY_TO_OBJECT_INVALID, [
            msg_id::VAR_NAME_TO => $this->tob()?->dsp_id(),
            msg_id::VAR_NAME => $this->dsp_id(),
        ]);
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->fob() != null) {
            $result .= 'from ' . $this->fob()->dsp_id(false) . ' ';
        }
        if ($this->tob() != null) {
            $result .= 'to ' . $this->tob()->dsp_id(false);
        }

        $result .= ' as' . parent::dsp_id();
        return $result;
    }

    /**
     * @return string with the ids of the link e.g. 1/2/3
     */
    function link_id(): string
    {
        return $this->from_id() . '/' . $this->predicate_id() . '/' . $this->to_id();

    }

}


