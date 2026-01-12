<?php

/*

    model/element/element.php - either a word, triple, verb or formula with a link to a formula
    -------------------------

    formula elements are terms or expression operators such as add or brackets
    The term formula elements are saved in the database for fast detection of dependencies
    formula elements are terms with a link to a formula

    The main sections of this object are
    - db const:          const for the database link
    - construct and map: including the mapping of the db row to this sandbox object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - forward:           forward functions of the object parts for better code reading only


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\element;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id_user.php';
include_once paths::MODEL_ELEMENT . 'element_db.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_FORMULA . 'expression.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CALC . 'parameter_type.php';
include_once paths::SHARED_CONST . 'chars.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id_user;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\calc\parameter_type;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class element extends db_object_seq_id_user
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'cache for fast update of formula resolved text';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = element_db::FLD_ID;
    const array FLD_NAMES = element_db::FLD_NAMES;
    const array FLD_LST_ALL = element_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // TODO should be actually just the linked formula id that extends the term

    public formula $frm;                              // the repeated formula object for direct access when saving to the database
    public word|verb|triple|formula|null $obj = null; // the word, verb, triple or formula object
    public ?string $symbol = null;                    // the database reference symbol for formula expressions


    /*
     * construct and map
     */

    /**
     * always set the user because a formula element is always user-specific
     * @param user $usr the user who requested to use this formula element
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        db_object_seq_id_user::__construct($usr);
    }

    /**
     * reset the vars of this element
     * @param bool $keep_user set to true to keep the original user for sandbox objects
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        if ($keep_user) {
            $this->frm = new formula($this->get_user());
        } else {
            $this->frm = new formula(new user());
        }
        $this->obj = null;
    }

    /**
     * clone this object and all linked objects
     * @return $this a complete clone including a clone of all child objects
     */
    function clone_all(): element
    {
        $elm = parent::clone_all();
        $elm->frm = $elm->frm->clone_all();
        if ($elm->obj != null) {
            $elm->obj = $elm->obj->clone_all();
        }
        return $elm;
    }

    /**
     * map the formula element database fields for a later load of the object
     *
     * @param array|null $db_row with the data directly from the database
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(?array $db_row): bool
    {
        $this->id = 0;
        $result = parent::row_mapper($db_row, element_db::FLD_ID);
        if ($result) {
            $this->load_obj_by_id($db_row[element_db::FLD_REF_ID]);
        }
        return $result;
    }

    /**
     * map an element api json to this model element object
     * @param array $api_json the api array with the element values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete, the human-readable message what happened and how to solve it
     *                              including the user who has requested the mapping e.g. to check permissions to set code id or profiles
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        if (!array_key_exists(json_fields::ID, $api_json)) {
            log_warning('Missing id in api_json');
        } elseif (!array_key_exists(json_fields::OBJECT_CLASS, $api_json)) {
            log_warning('Missing class in api_json');
        } else {
            if ($api_json[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
                $wrd = new word($this->get_user());
                if ($wrd->api_mapper($api_json, $usr_msg)) {
                    $this->obj = $wrd;
                }
            } elseif ($api_json[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
                $trp = new triple($this->get_user());
                if ($trp->api_mapper($api_json, $usr_msg)) {
                    $this->obj = $trp;
                }
            } elseif ($api_json[json_fields::OBJECT_CLASS] == json_fields::CLASS_VERB) {
                $vrb = new verb();
                if ($usr_msg->is_ok()) {
                    $this->obj = $vrb;
                }
            } elseif ($api_json[json_fields::OBJECT_CLASS] == json_fields::CLASS_FORMULA) {
                $frm = new formula($this->get_user());
                if ($frm->api_mapper($api_json, $usr_msg)) {
                    $this->obj = $frm;
                }
            } else {
                $this->obj = null;
            }
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
        $vars = [];
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {
            if ($this->obj != null) {
                $vars = $this->obj->api_json_array($typ_lst, $usr);
                if ($this->is_word()) {
                    $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
                } elseif ($this->is_triple()) {
                    $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
                } elseif ($this->is_verb()) {
                    $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VERB;
                } elseif ($this->is_formula()) {
                    $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_FORMULA;
                } else {
                    $vars[json_fields::OBJECT_CLASS] = '';
                }
            } else {
                $vars[json_fields::OBJECT_CLASS] = '';
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * @return string the element name to the user in the most simple form (without any ids)
     */
    function name(): string
    {
        if ($this->obj != null) {
            return $this->obj->name();
        } else {
            return '';
        }
    }

    /**
     * @return int|null the database id of the related object
     */
    function trm_id(): int|null
    {
        return $this->obj?->id();
    }

    function term(): term
    {
        return $this->obj?->term();
    }

    function type(): string
    {
        if ($this->obj != null) {
            return $this->obj::class;
        } else {
            return '';
        }
    }

    // TODO Prio 1 review and get from $sys and add the class to the code_id db col
    function type_id(): int
    {
        global $sys;

        $id = 0;
        if ($this->obj != null) {
            if ($this->type() == word::class) {
                $id = 1;
            } elseif ($this->type() == verb::class) {
                $id = 2;
            } elseif ($this->type() == triple::class) {
                $id = 3;
            } elseif ($this->type() == formula::class) {
                $id = 4;
            } else {
                log_err('id of type ' . $this->type() . ' is not expected');
            }

        }
        return $id;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to get the formula element from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_fields(element_db::FLD_NAMES);

        return $qp;
    }

    /**
     * get the related object (term?) from the database
     * @param int $id the id of the formula element
     * @return int the id of the element found and zero if nothing is found
     */
    function load_obj_by_id(int $id): int
    {
        if ($id != 0 and $this->get_user()->is_set()) {
            if ($this->type() == word::class) {
                $wrd = new word($this->get_user());
                $wrd->load_by_id($id);
                $this->symbol = chars::WORD_START . $wrd->id() . chars::WORD_END;
                $this->obj = $wrd;
            } elseif ($this->type() == triple::class) {
                $trp = new triple($this->get_user());
                $trp->load_by_id($id);
                $this->symbol = chars::TRIPLE_START . $trp->id() . chars::TRIPLE_END;
                $this->obj = $trp;
            } elseif ($this->type() == verb::class) {
                $vrb = new verb;
                $vrb->set_user($this->get_user());
                $vrb->load_by_id($id);
                $this->symbol = chars::TRIPLE_START . $vrb->id() . chars::TRIPLE_END;
                $this->obj = $vrb;
            } elseif ($this->type() == formula::class) {
                $frm = new formula($this->get_user());
                $frm->load_by_id($id);
                $this->symbol = chars::FORMULA_START . $frm->id() . chars::FORMULA_END;
                $this->obj = $frm;
                /*
                // in case of a formula load also the corresponding word
                $wrd = new word($this->get_user());
                $wrd->load_by_name($frm->name);
                $this->wrd_obj = $wrd;
                */
                //
            } else {
                log_err('id of type ' . $this->type() . ' is not expected');
            }
            log_debug("element->load got " . $this->dsp_id());
        }
        return $id;
    }

    /**
     * create an SQL statement to retrieve a formula element by id from the database
     * set the class formula element for the parent function
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
    }


    /*
     * forward
     */

    function include(): void
    {
        $this->obj->include();
    }

    function exclude(): void
    {
        $this->obj->exclude();
    }

    function is_excluded(): bool
    {
        if ($this->is_verb()) {
            return false;
        } else {
            if ($this->obj != null) {
                return $this->obj->is_excluded();
            } else {
                return false;
            }
        }
    }

    function is_word(): bool
    {
        if ($this->obj != null) {
            if ($this->obj::class == word::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function is_triple(): bool
    {
        if ($this->obj != null) {
            if ($this->obj::class == triple::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function is_verb(): bool
    {
        if ($this->obj != null) {
            if ($this->obj::class == verb::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function is_formula(): bool
    {
        if ($this->obj != null) {
            if ($this->obj::class == formula::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * checks if the element object can be added to the database
     *
     * @param user_message $usr_msg the explanation for the user why the element cannot yet be added to the database
     * @return true if all mandatory vars of the element are set and the element can be stored in the database
     */
    function db_ready(user_message $usr_msg): bool
    {
        if ($this->obj != null) {
            return $this->obj->db_ready($usr_msg);
        } else {
            return false;
        }
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add an element to the database
     * all fields are always included in the query to be able to remove by overwriting with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // create an empty sandbox object but of the same type and with the same user to detect the fields that should be written
        $db_row = $this->clone_reset();

        // get the field names, values and parameter types that have been changed
        $fvt_lst = $this->db_fields_changed($db_row, $usr_msg);

        // prepare the sql statement
        $qp = $this->sql_prepare($sc, $fvt_lst, $usr_msg, sql_type::INSERT, $sc_par_lst);

        $qp->sql = $sc->create_sql_insert($fvt_lst);
        $qp->par = $fvt_lst->db_values();

        return $qp;
    }

    /**
     * create the sql statement to update a word in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param element|db_object_seq_id $db_row the word with the database values before the update
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_update(
        sql_creator              $sc,
        element|db_object_seq_id $db_row,
        user_message             $usr_msg,
        sql_type_list            $sc_par_lst = new sql_type_list()): sql_par
    {
        // get the field names, values and parameter types that have been changed
        // and that needs to be updated in the database
        // the db_* child function call the corresponding parent function
        // including the sql parameters for logging
        $fvt_lst = $this->db_fields_changed($db_row, $usr_msg);

        // prepare the sql statement
        $qp = $this->sql_prepare($sc, $fvt_lst, $usr_msg, sql_type::UPDATE, $sc_par_lst);

        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
        $qp->par = $fvt_lst->db_values();

        // unlike the db_* function the sql_update_* parent function is called directly

        return $qp;
    }

    function sql_prepare(
        sql_creator        $sc,
        sql_par_field_list $fvt_lst,
        user_message       $usr_msg,
        sql_type           $sql_type,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;

        // set the sql query type
        $sc_par_lst_used->add($sql_type);

        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();

        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // create the sql and get the sql parameters used
        $qp = new sql_par($this::class, $sc_par_lst_used, $ext);
        $sc->set_class($this::class, $sc_par_lst_used);
        $sc->set_name($qp->name);

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * to get a list of all database fields that might be changed,
     * a field list must be corresponding to the db_fields_changed fields
     *
     * @return array list of all database field names that might have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                formula_db::FLD_ID,
                element_type::FLD_ID,
                user_db::FLD_ID,
                element_db::FLD_REF_ID,
            ],
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param element|db_object_seq_id $obj the compare value to detect the changed fields
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        element|db_object_seq_id $obj,
        user_message             $usr_msg,
        sql_type_list            $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($obj, $usr_msg, $sc_par_lst);
        if ($obj->frm->id() !== $this->frm->id()) {
            $lst->add_field(
                formula_db::FLD_ID,
                $this->frm->id(),
                sql_field_type::INT,
                $obj->frm->id()
            );
        }
        if ($obj->type_id() !== $this->type_id()) {
            $lst->add_field(
                element_db::FLD_TYPE,
                $this->type_id(),
                sql_field_type::INT,
                $obj->type_id()
            );
        }
        if ($obj->get_user_id() !== $this->get_user_id()) {
            $lst->add_field(
                user_db::FLD_ID,
                $this->get_user_id(),
                sql_field_type::INT,
                $obj->get_user_id()
            );
        }
        if ($obj->trm_id() !== $this->trm_id()) {
            $lst->add_field(
                element_db::FLD_REF_ID,
                $this->trm_id(),
                sql_field_type::INT,
                $obj->trm_id()
            );
        }
        return $lst;
    }


    /*
     * debug
     */

    /**
     * @return string best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $lib = new library();
        $result = '';
        if ($this->type() <> '') {
            $class_name = $lib->class_to_name($this->type());
            $result .= $class_name . ' ';
        }
        $name = $this->name();
        if ($name <> '') {
            $result .= '"' . $name . '" ';
        }
        if ($this->id() > 0) {
            $result .= '(' . $this->id() . ')';
        } else {
            if ($this->obj != null) {
                $result .= '(' . $this->obj->id() . ')';
            }
        }
        $result .= $this->dsp_id_user();

        return $result;
    }

}