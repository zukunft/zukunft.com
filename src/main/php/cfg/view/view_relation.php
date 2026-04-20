<?php

/*

    model/view/view_relation.php - to define the relation between two views e.g. to have a parent view where the child view have additional components
    ----------------------------

    the view relation defines how two views are connected whereas
    the view link defines how a term is connected to a view
    both are n:m relations

    The main sections of this object are
    - db const:          const for the database link


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

namespace Zukunft\ZukunftCom\main\php\cfg\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view_relation_db.php';
include_once paths::MODEL_VIEW . 'view_relation_type.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'view_relation_types.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;

class view_relation extends sandbox_link
{

    /*
     * db const
     */

    // the database and JSON object field names used only for the view relation
    // *_COM is the description of the field used for the SQL database
    const string TBL_COMMENT = 'to define the relation between two views to another view e.g. to extend the change word view with the word usage and log components shared with the exclude word view';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = view_relation_db::FLD_ID;
    const array FLD_LST_LINK = view_relation_db::FLD_LST_LINK;
    const array FLD_LST_MUST_BUT_STD_ONLY = view_relation_db::FLD_LST_MUST_BUT_STD_ONLY;
    const array FLD_LST_USER_CAN_CHANGE = view_relation_db::FLD_LST_USER_CAN_CHANGE;
    const array FLD_NAMES = view_relation_db::FLD_NAMES;
    const array FLD_NAMES_USR = view_relation_db::FLD_NAMES_USR;
    const array ALL_SANDBOX_FLD_NAMES = view_relation_db::ALL_SANDBOX_FLD_NAMES;

    // overwrite the parent link const
    const string FLD_FROM = view_relation_db::FLD_PARENT;
    const string FLD_PREDICATE = view_relation_type::FLD_ID;
    const string FLD_TO = view_relation_db::FLD_CHILD;



    /*
     * object vars
     */

    public ?int $start_pos = null;
    public ?string $description = null;


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to modify a view
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset(true);
        $this->set_predicate(view_relation_type::DEFAULT);
    }

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->set_predicate_id(null);
        $this->start_pos = null;
        $this->description = null;
    }

    /**
     * map the database fields to the object fields
     * TODO get the related view objects from the cache if possible
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view relation is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = view_relation_db::FLD_ID): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, view_relation_db::FLD_ID);
        if ($result) {
            $prt = new view($this->get_user());
            $prt->id = $db_row[view_relation_db::FLD_PARENT];
            $this->set_parent($prt);
            $cld = new view($this->get_user());
            $cld->id = $db_row[view_relation_db::FLD_CHILD];
            $this->set_child($cld);
            $this->set_predicate_id($db_row[view_relation_type::FLD_ID]);
            $this->start_pos = $db_row[view_relation_db::FLD_START_POS];
            $this->description = $db_row[sql_db::FLD_DESCRIPTION];
        }
        return $result;
    }

    /**
     * set the vars of this named link object based on the given json without writing to the database
     * import the name and description of a sandbox link object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        parent::import_mapper($in_ex_json, $msg, $dto);;

        // reset of object not needed, because the calling function has just created the object
        // name is not mandatory because might be generated based on the link
        if (key_exists(json_fields::PARENT, $in_ex_json)) {
            if (is_string($in_ex_json[json_fields::PARENT])) {
                $this->set_parent_by_name($in_ex_json[json_fields::PARENT]);
            } else {
                $msk = new view($this->get_user());
                $msk->import_mapper($in_ex_json[json_fields::PARENT], $msg, $dto);
                $this->set_parent($msk);
            }
        }
        if (key_exists(json_fields::CHILD, $in_ex_json)) {
            if (is_string($in_ex_json[json_fields::CHILD])) {
                $this->set_child_by_name($in_ex_json[json_fields::CHILD]);
            } else {
                $msk = new view($this->get_user());
                $msk->import_mapper($in_ex_json[json_fields::CHILD], $msg, $dto);
                $this->set_child($msk);
            }
        }
        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->set_relation_type($in_ex_json[json_fields::TYPE_NAME]);
        }
        if (key_exists(json_fields::POSITION, $in_ex_json)) {
            $this->start_pos = $in_ex_json[json_fields::POSITION];
        }
        if (key_exists(json_fields::DESCRIPTION, $in_ex_json)) {
            $this->description = $in_ex_json[json_fields::DESCRIPTION];
        }

        return $msg->is_ok();
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
        $vars = [];
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {
            $vars = parent::api_json_array($typ_lst, $usr);
            if ($this->parent() != null) {
                if ($typ_lst->include_views()) {
                    $vars[json_fields::PARENT] = $this->parent()->api_json_array($typ_lst, $usr);
                } else {
                    $vars[json_fields::PARENT_ID] = $this->parent()->id();
                }
            }
            if ($this->child() != null) {
                if ($typ_lst->include_views()) {
                    $vars[json_fields::CHILD] = $this->child()->api_json_array($typ_lst, $usr);
                } else {
                    $vars[json_fields::CHILD_ID] = $this->child()->id();
                }
            }
            if ($this->start_pos != null) {
                $vars[json_fields::POSITION] = $this->start_pos;
            }
            if ($this->description != null) {
                $vars[json_fields::DESCRIPTION] = $this->description;
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            if ($this->id() != 0) {
                $vars[json_fields::ID] = $this->id();
            }
            $vars[json_fields::EXCLUDED] = true;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the main vars with one function
     * @param int $id the database id of the relation
     * @param view $prt the parent view that should be modified by the child view
     * @param view $cld the child view that modifies the parent view
     * @return void
     */
    function set(int $id, view $prt, view $cld): void
    {
        $this->id = $id;
        $this->set_parent($prt);
        $this->set_child($cld);
    }

    /**
     * interface function to set the parent view always to the "from" object
     * @param view $prt the view that should be modified
     * @return void
     */
    function set_parent(view $prt): void
    {
        $this->set_fob($prt);
    }

    /**
     * interface function to set the parent view always to the "from" object
     * @param string $name the view that should be modified
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return void
     */
    function set_parent_by_name(string $name, ?data_object $dto = null): void
    {
        $msk = new view($this->get_user());
        $msk->set_name($name);
        $this->set_parent($msk);
    }

    /**
     * interface function to set the child view always to the "to" object
     * @param view $cld the view that defines the modification
     * @return void
     */
    function set_child(view $cld): void
    {
        $this->set_tob($cld);
    }

    /**
     * interface function to set the child view always to the "from" object
     * @param string $name the view that should be modified
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return void
     */
    function set_child_by_name(string $name, ?data_object $dto = null): void
    {
        $msk = new view($this->get_user());
        $msk->set_name($name);
        $this->set_child($msk);
    }

    /**
     * interface function just to rename the predicate
     * @param string $relation_code_id the relation code id
     * @return void
     */
    function set_relation_type(string $relation_code_id): void
    {
        $this->set_predicate($relation_code_id);
    }

    /**
     * interface function to set the relation type from the parent to the child view
     * @param string $type_code_id the code_id that defines how the child view should modify
     *                             the parent view for the used view
     * @return void
     */
    function set_predicate(string $type_code_id): void
    {
        global $sys;
        $msk_rel_lst = $sys->view_relation_types();
        $this->set_predicate_id($msk_rel_lst->id($type_code_id));
    }

    /**
     * interface function to get the parent view
     * @return view|sandbox_named|null but actually the view object
     */
    function parent(): view|sandbox_named|null
    {
        return $this->fob();
    }

    /**
     * interface function to get the child view
     * @return view|sandbox_named|null but actually the view object
     */
    function child(): view|sandbox_named|null
    {
        return $this->tob();
    }

    /**
     * overwrite the link type function
     * @return string|null the code id of the verb
     */
    function get_predicate_code_id(): ?string
    {
        global $sys;
        $id = $this->predicate_id;
        if ($id == null) {
            // if type is not set use the default
            return null;
        } else {
            $typ = $sys->view_relation_types()->get($id);
            if ($typ != null) {
                return $typ->get_code_id();
            } else {
                $msg = 'view relation type with id ' . $id . ' is missing';
                log_err($msg);
                return $msg;
            }
        }
    }


    /*
     * fields
     */

    /**
     * @return string with the field name for the parent view as an overwrite function
     */
    function from_field(): string
    {
        return view_relation_db::FLD_PARENT;
    }

    /**
     * @return string with the field name for the child view as an overwrite function
     */
    function to_field(): string
    {
        return view_relation_db::FLD_CHILD;
    }

    /**
     * @return string with the field name for the relation type as an overwrite function
     */
    function type_field(): string
    {
        return view_relation_type::FLD_ID;
    }

    /**
     * the view_relation does not really have a name, only a description
     * @return string
     */
    function name_field(): string
    {
        return '';
    }

    /**
     * @return array with the all field names that the user can change for this object
     * TODO move to the highest object level
     */
    protected function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields of this component
     * which does not include the internal database id
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);
        // TODO Prio 0 if requested export the object
        if (!$do_load) {
            $vars[json_fields::PARENT] = $this->parent()?->export_json($exp_typ, $do_load);
            $vars[json_fields::CHILD] = $this->child()?->export_json($exp_typ, $do_load);
        } else {
            $vars[json_fields::PARENT] = $this->parent()?->name();
            $vars[json_fields::CHILD] = $this->child()?->name();
        }
        $vars[json_fields::TYPE_NAME] = $this->get_predicate_code_id();

        if ($this->start_pos >= 0) {
            $vars[json_fields::POSITION] = $this->start_pos;
        }
        if ($this->description != '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }

        return $vars;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param view_relation|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param view_relation|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return view_relation|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        view_relation|CombineObject|db_object_seq_id $std_obj,
        view_relation|CombineObject|db_object_seq_id $result
    ): view_relation|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->start_pos !== $this->start_pos) {
            $result->start_pos = $this->start_pos;
        }
        if ($std_obj->description !== $this->description) {
            $result->description = $this->description;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this view relation object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null), the type is removed
     *
     * @param view_relation|sandbox|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(view_relation|sandbox|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($this->start_pos === null and $obj->start_pos != null) {
            $this->start_pos = $obj->start_pos;
        }
        if ($this->description === null and $obj->description != null) {
            $this->description = $obj->description;
        }
        return $usr_msg;
    }


    /*
     * preloaded
     */

    /**
     * @return string|null the name of the relation type e.g. add components
     */
    function predicate_name(): ?string
    {
        global $sys;
        return $sys->view_relation_name($this->relation_type_id());
    }

    /**
     * @return string|null the code id of the relation type e.g. add components
     */
    function relation_type_code_id(): ?string
    {
        global $sys;
        return $sys->view_relation_code_id($this->relation_type_id());
    }

    /**
     * @return int|null the id of the relation type
     */
    function relation_type_id(): ?int
    {
        return $this->predicate_id;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a view relation from the database
     * TODO move to the highest object level
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(view_relation_db::FLD_NAMES);
        $sc->set_usr_fields(view_relation_db::FLD_NAMES_USR);
        $sc->set_usr_num_fields(view_relation_db::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * TODO move inner part to view as "load_remaining"
     * TODO add a bool var "is_loaded" to db_object
     *      to indicate is the object has just been created and might be incomplete
     *      or if loaded from the db and is expected to have all vars in line with the db
     * @param user_message $msg to collect the message due to missing links
     * @return bool true if all the related objects has been loaded
     */
    function reload_objects(user_message $msg): bool
    {
        $result = true;

        $prt = $this->parent();
        if ($prt->id() == 0) {
            if ($prt->name() != '') {
                if (!$prt->load_by_name($prt->name())) {
                    $msg->add(msg_id::LOAD_VIEW_SIDE_BY_ID_FAILED, [
                        msg_id::VAR_SIDE => msg_id::SIDE_PARENT->text(),
                        msg_id::VAR_VIEW => $this->parent()->dsp_id()
                    ]);
                }
            } else {
                $msg->add(msg_id::LOAD_VIEW_SIDE_NAME_MISSING, [
                    msg_id::VAR_SIDE => msg_id::SIDE_PARENT->text(),
                    msg_id::VAR_VIEW => $this->dsp_id()
                ]);
            }
        } else {
            if ($prt->name() == '') {
                if (!$prt->load_by_id($prt->id())) {
                    $msg->add(msg_id::LOAD_VIEW_SIDE_BY_ID_FAILED, [
                        msg_id::VAR_SIDE => msg_id::SIDE_PARENT->text(),
                        msg_id::VAR_VIEW => $this->parent()->dsp_id()
                    ]);
                }
            }
        }

        $cld = $this->child();
        if ($cld->id() == 0) {
            if ($cld->name() != '') {
                if (!$cld->load_by_name($cld->name())) {
                    $msg->add(msg_id::LOAD_VIEW_SIDE_BY_ID_FAILED, [
                        msg_id::VAR_SIDE => msg_id::SIDE_CHILD->text(),
                        msg_id::VAR_VIEW => $this->child()->dsp_id()
                    ]);
                }
            } else {
                $msg->add(msg_id::LOAD_VIEW_SIDE_NAME_MISSING, [
                    msg_id::VAR_SIDE => msg_id::SIDE_CHILD->text(),
                    msg_id::VAR_VIEW => $this->dsp_id()
                ]);
            }
        } else {
            if ($cld->name() == '') {
                if (!$cld->load_by_id($cld->id())) {
                    $msg->add(msg_id::LOAD_VIEW_SIDE_BY_ID_FAILED, [
                        msg_id::VAR_SIDE => msg_id::SIDE_CHILD->text(),
                        msg_id::VAR_VIEW => $this->child()->dsp_id()
                    ]);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * load the object parameters for all users by the link id
     *
     * @param int $from_id the id of the from link object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_link(
        int $from_id,
        int $to_id,
        user_message $msg
    ): bool
    {
        return parent::load_standard_by_link_parent(
            view_relation_db::FLD_PARENT, $from_id,
            view_relation_db::FLD_CHILD, $to_id, $msg
        );
    }


    /*
     * sql write fields
     */

    /**
     * add the type fields to the list of all database fields that might be changed
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                view_relation_type::FLD_ID,
                view_relation_db::FLD_START_POS,
                sql_db::FLD_DESCRIPTION,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * add the type field to the list of changed database fields with name, value and type
     *
     * @param view_relation|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        view_relation|db_object_seq_id $obj,
        user_message                   $msg,
        sql_type_list                  $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);

        if ($obj->predicate_id() !== $this->predicate_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view_relation_type::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . view_relation_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            if ($this->predicate_id() < 0) {
                $msg->add(msg_id::VIEW_LINK_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->predicate_name(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                view_relation_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $obj->predicate_id(),
                $sys->typ_lst->mrl_typ);
        }

        if ($obj->start_pos !== $this->start_pos) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view_relation_db::FLD_START_POS,
                    $sys->typ_lst->cng_fld->id($table_id . view_relation_db::FLD_START_POS),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                view_relation_db::FLD_START_POS,
                $this->start_pos,
                sql_field_type::INT,
                $obj->start_pos
            );
        }

        if ($obj->description !== $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_DESCRIPTION,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_DESCRIPTION,
                $this->description,
                sql_db::FLD_DESCRIPTION_SQL_TYP,
                $obj->description
            );
        }

        return $lst->merge($this->db_changed_sandbox_list($obj, $sc_par_lst));
    }


    /*
     * sql fields
     */

    /**
     * @return array with all fields names of this view_relation object
     */
    protected function all_fields(): array
    {
        return array_merge(
            view_relation_db::FLD_NAMES,
            view_relation_db::FLD_NAMES_USR,
            array(user_db::FLD_ID));
    }


    /*
     * debug
     */

    /**
     * @return string the html code to display the link name
     */
    function name(): string|null
    {
        $result = null;

        if ($this->parent() != null) {
            $result = $this->parent()->name();
        }
        if ($this->child() != null) {
            $result = ' to ' . $this->child()->name();
        }

        return $result;
    }

}
