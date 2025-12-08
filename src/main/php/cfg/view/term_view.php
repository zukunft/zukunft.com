<?php

/*

    model/view/term_view.php - to define the view for a word, triple, verb or formula
    ------------------------

    TODO Prio 1 rename to view_link (or all view_link to term_view)

    The main sections of this object are
    - db const:          const for the database link
    - set and get:       to capsule the vars from unexpected changes
    - fields:            the field names of this object as overwrite functions
    - load:              database access object (DAO) functions
    - sql write fields:  field list for writing to the database


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
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
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
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
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
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class term_view extends sandbox_link
{

    /*
     * db const
     */

    // the database and JSON object field names used only for term to view links
    // *_SQL_TYP is the sql data type used for the field
    const string TBL_COMMENT = 'to link view to a word, triple, verb or formula with an n:m relation';
    const string FLD_ID = 'term_view_id';
    const string FLD_TYPE_COM = '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups';

    // all database field names excluding the id
    const array FLD_NAMES = array(
        term::FLD_ID,
        view_link_type::FLD_ID,
        view_db::FLD_ID
    );
    //
    const array FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION
    );
    // all database field names, excluding the id, used to identify if there are some user specific changes
    // TODO check if this is used in all relevant objects
    // TODO Prio 2 maybe add a priority
    const array ALL_SANDBOX_FLD_NAMES = array(
        view_link_type::FLD_ID,
        sql_db::FLD_DESCRIPTION,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const array FLD_LST_LINK = array(
        [term::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [view_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [view_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, view_link_type::class, self::FLD_TYPE_COM],
    );
    // list of MANDATORY fields that CAN be CHANGEd by the user
    const array FLD_LST_MUST_BUT_STD_ONLY = array(
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CAN be CHANGEd by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [view_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_link_type::class, ''],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    public ?string $description = null;


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
        $this->set_predicate(view_link_type::DEFAULT);
    }

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->set_predicate_id(null);
        $this->description = null;
    }

    /**
     * map the database fields to the object fields
     * TODO get the related view and term object from the cache if possible
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view component link is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $msk = new view($this->user());
            $msk->id = $db_row[view_db::FLD_ID];
            $this->set_view($msk);
            $trm = new term($this->user());
            $trm->set_id($db_row[term::FLD_ID]);
            $this->set_term($trm);
            $this->set_predicate_id($db_row[view_link_type::FLD_ID]);
            $this->description = $db_row[sql_db::FLD_DESCRIPTION];
        }
        return $result;
    }

    /**
     * fill the vars with this link type view link object based on the given api json array
     * basically use the json field type instead of predicate and
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {

        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::TYPE, $api_json)) {
            $this->predicate_id = $api_json[json_fields::TYPE];
        }
        if (array_key_exists(json_fields::DESCRIPTION, $api_json)) {
            $this->description = $api_json[json_fields::DESCRIPTION];
        }

        return $usr_msg->is_ok();
    }

    /**
     * set the vars of this view link object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the data object that contains the already imported formulas
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        // reset the all parameters for these formula link object but keep the user
        $this->reset(true);

        parent::import_mapper($in_ex_json, $usr_msg, $dto);

        // import the view
        if (array_key_exists(json_fields::VIEW, $in_ex_json)) {
            $msk_json = $in_ex_json[json_fields::VIEW];
            if (is_array($msk_json)) {
                if (count($msk_json) == 1 and array_key_exists(json_fields::NAME, $msk_json)) {
                    $msk_json = $msk_json[json_fields::NAME];
                }
            }
            if (is_string($msk_json)) {
                $msk = $dto?->get_view_by_name($msk_json);
                if ($msk == null) {
                    $usr_msg->add_id_with_vars(msg_id::VIEW_MISSING_IMPORT, [
                        msg_id::VAR_VIEW => $msk_json,
                        msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                    ]);
                    $msk = new view($usr_msg->usr);
                    $msk->set_name($msk_json);
                }
                $this->set_view($msk);
            } elseif (is_array($msk_json)) {
                $msk = new view($usr_msg->usr);
                $msk->import_mapper($msk_json, $usr_msg, $dto);
                if ($usr_msg->is_ok()) {
                    $this->set_view($msk);
                }
            }
        } else {
            $usr_msg->add_info_with_vars(msg_id::VIEW_CREATED, [
                msg_id::VAR_VIEW_NAME => $in_ex_json[json_fields::NAME]
            ]);
            $msk = new view($usr_msg->usr);
            $msk->import_mapper($in_ex_json, $usr_msg, $dto);
            $this->set_view($msk);
        }

        // import the term
        if (array_key_exists(json_fields::TERM, $in_ex_json)) {
            $trm_json = $in_ex_json[json_fields::TERM];
            if (is_array($trm_json)) {
                if (count($trm_json) == 1 and array_key_exists(json_fields::NAME, $trm_json)) {
                    $trm_json = $trm_json[json_fields::NAME];
                }
            }
            if (is_string($trm_json)) {
                $trm = $dto?->get_term_by_name($trm_json);
                if ($trm == null) {
                    $usr_msg->add_id_with_vars(msg_id::TERM_MISSING_IMPORT, [
                        msg_id::VAR_TERM => $trm_json,
                        msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                    ]);
                    $trm = new term($usr_msg->usr);
                    $trm->set_name($trm_json);
                }
                $this->set_term($trm);
            } elseif (is_array($trm_json)) {
                $trm = new term($usr_msg->usr);
                $trm->import_mapper($trm_json, $usr_msg, $dto);
                if ($usr_msg->is_ok()) {
                    $this->set_term($trm);
                }
            }
        } else {
            $usr_msg->add_info_with_vars(msg_id::TERM_CREATED, [
                msg_id::VAR_TERM_NAME => $in_ex_json[json_fields::NAME]
            ]);
            $trm = new term($usr_msg->usr);
            //$phr->import_mapper($in_ex_json, $usr_msg, $dto);
            $this->set_term($trm);
        }

        if (array_key_exists(json_fields::PREDICATE, $in_ex_json)) {
            global $sys;
            $this->predicate_id = $sys->typ_lst->msk_lnk_typ->id($in_ex_json[json_fields::PREDICATE]);;
        }
        if (array_key_exists(json_fields::DESCRIPTION, $in_ex_json)) {
            $this->description = $in_ex_json[json_fields::DESCRIPTION];;
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

        if ($this->get_view()?->id() != 0) {
            if ($typ_lst->include_views()) {
                $vars[json_fields::VIEW] = $this->get_view()->api_json_array($typ_lst, $usr);
            } else {
                $vars[json_fields::VIEW_ID] = $this->get_view()->id();
            }
        }
        if ($this->term()?->id() != 0) {
            if ($typ_lst->include_phrases()) {
                $vars[json_fields::TERM] = $this->term()->api_json_array($typ_lst, $usr);
            } else {
                $vars[json_fields::TERM_ID] = $this->term()->id();
            }
        }

        if ($this->description != null) {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the main vars with one function
     * @param int $id the database id of the link
     * @param view $msk the view that should be linked
     * @param term $trm the term to which the view should be linked
     * @return void
     */
    function set(int $id, view $msk, term $trm): void
    {
        $this->id = $id;
        $this->set_view($msk);
        $this->set_term($trm);
    }

    /**
     * interface function to set the view always to the from object
     * @param view $msk the view that should be linked
     * @return void
     */
    function set_view(view $msk): void
    {
        $this->set_fob($msk);
    }

    /**
     * interface function to set the term always to the to object
     * @param term $trm the word, triple or formula that should be linked
     * @return void
     */
    function set_term(term $trm): void
    {
        $this->set_tob($trm);
    }

    /**
     * interface function to set the connection type from the term to the view
     * @param string $type_code_id the word, triple or formula that should be linked
     * @return void
     */
    function set_predicate(string $type_code_id): void
    {
        global $sys;
        $this->set_predicate_id($sys->typ_lst->msk_lnk_typ->id($type_code_id));
    }

    /**
     * interface function to get the view
     * @return view|sandbox_named|combine_named|null but actually the view object
     */
    function get_view(): view|sandbox_named|combine_named|null
    {
        return $this->fob();
    }

    /**
     * interface function to get the term
     * @return term|sandbox_named|combine_named|null but actually the term object
     */
    function term(): view|sandbox_named|combine_named|null
    {
        return $this->tob();
    }

    /**
     * overwrite the link type function with the view link
     * @return string|null the code id of the verb
     */
    function get_predicate_code_id(): ?string
    {
        global $sys;
        $id = $this->predicate_id();
        $typ = $sys->typ_lst->msk_lnk_typ->get($this->predicate_id());
        if ($typ != null) {
            return $typ->code_id();
        } else {
            // TODO Prio 0 use msg_id
            $msg = 'term view link type with id ' . $id . ' is missing';
            log_err($msg);
            return $msg;
        }
    }


    /*
     * modify
     */

    /**
     * fill this view link object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null) the type is removed
     *
     * @param view_relation|sandbox|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(view_relation|sandbox|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->description != null) {
            $this->description = $obj->description;
        }
        return $usr_msg;
    }

    /*
     * fields
     */

    /**
     * @return string with the field name for the view as an overwrite function
     */
    function from_field(): string
    {
        return view_db::FLD_ID;
    }

    /**
     * @return string with the field name for the term as an overwrite function
     */
    function to_field(): string
    {
        return term::FLD_ID;
    }

    /**
     * TODO check if the overwrites are correct for all objects
     *      and if a to_id() function is needed
     * @return string with the term name
     */
    function to_value(): string
    {
        if ($this->tob() == null) {
            return '';
        } else {
            return $this->tob()->name();
        }
    }

    /**
     * @return string with the field name for the link type as an overwrite function
     */
    function type_field(): string
    {
        return view_link_type::FLD_ID;
    }

    /**
     * the term_view does not really have a name, only a description
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
     * preloaded
     */

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function predicate_name(): string
    {
        global $sys;
        return $sys->typ_lst->msk_lnk_typ->name($this->predicate_id);
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a view term link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * TODO move inner part to view as "load_remaining"
     * TODO add a bool var "is_loaded" to db_object
     *      to indicate is the object has just been created and might be incomplete
     *      or if loaded from the db and is expected to have all vars in line with the db
     * @return bool true if all the related objects has been loaded
     */
    function reload_objects(): bool
    {
        $result = true;

        $msk = $this->get_view();
        if ($msk->id() == 0) {
            if ($msk->name() != '') {
                $result = $msk->load_by_name($msk->name());
            } else {
                log_warning('Cannot load view because neither id nor name is set');
            }
        } else {
            if ($msk->name() == '') {
                $result = $msk->load_by_id($msk->id());
            }
        }

        $trm = $this->term();
        if ($trm->id() == 0) {
            if ($trm->name() != '') {
                $result = $trm->load_by_name($trm->name());
            } else {
                log_warning('Cannot load term because neither id nor name is set');
            }
        } else {
            if ($trm->name() == '') {
                $result = $trm->load_by_id($trm->id());
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard view term link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        // try to get the search values from the objects
        if ($this->id() <= 0) {
            $this->id = 0;
        }

        $sc->set_class($this::class);
        $qp = new sql_par($this::class);
        if ($this->id() != 0) {
            $qp->name .= 'std_id';
        } else {
            $qp->name .= 'std_link_ids';
        }
        $sc->set_name($qp->name);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID)));
        if ($this->id() > 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->get_view()->id() > 0 and $this->term()->id() != 0) {
            $sc->add_where(view_db::FLD_ID, $this->get_view()->id());
            $sc->add_where(term::FLD_ID, $this->term()->id());
        } else {
            if ($this->get_view()->id() > 0) {
                log_err('Cannot load default view term link because term id for ' . $this->term()->dsp_id() . 'is missing');
            } else {
                log_err('Cannot load default view term link because term id for ' . $this->get_view()->dsp_id() . 'is missing');
            }
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
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
        if ($this->get_view()?->name() != null) {
            $vars[json_fields::VIEW] = $this->get_view()->export_json($exp_typ, $do_load);
        }
        if ($this->term()?->name() != null) {
            $vars[json_fields::TERM] = $this->term()->export_json($exp_typ, $do_load);
        }

        global $sys;
        if ($this->predicate_id == $sys->typ_lst->msk_lnk_typ->id(view_link_type::DEFAULT)) {
            unset($vars[json_fields::PREDICATE]);
        }
        if ($this->description != null) {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }

        return $vars;
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
                sql_db::FLD_DESCRIPTION,
                view_link_type::FLD_ID,
            ]
        );
    }

    /**
     * add the type field to the list of changed database fields with name, value and type
     *
     * @param sandbox|term_view $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|term_view $sbx,
        sql_type_list     $sc_par_lst = new sql_type_list(),
        user_message      $usr_msg = new user_message()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst, $usr_msg);

        if ($sbx->description !== $this->description) {
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
                $sbx->description
            );
        }

        if ($sbx->predicate_id() !== $this->predicate_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view_link_type::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . view_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->predicate_id() < 0) {
                $usr_msg->add_id_with_vars(msg_id::VIEW_LINK_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->predicate_name(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                view_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $sys->typ_lst->phr_typ);
        }
        return $lst;
    }

}
