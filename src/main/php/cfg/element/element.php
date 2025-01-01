<?php

/*

    cgf/element/element.php - either a word, triple, verb or formula with a link to a formula
    -----------------------

    formula elements are terms or expression operators such as add or brackets
    The term formula elements are saved in the database for fast detection of dependencies
    formula elements are terms with a link to a formula

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\element;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'expression.php';
include_once MODEL_FORMULA_PATH . 'parameter_type.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_USER_PATH . 'user.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id_user;
use cfg\formula\expression;
use cfg\formula\formula;
use cfg\formula\parameter_type;
use cfg\phrase\term;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\helper\type_object;
use cfg\user\user;
use cfg\word\word;
use html\formula\formula as formula_dsp;
use html\word\word as word_dsp;
use shared\library;

class element extends db_object_seq_id_user
{

    // the allowed objects types for a formula element
    // a word is used for an AND selection of values
    // a triple is used for an AND selection of values
    // a verb is used for dynamic usage of linked words for an AND selection
    // a formula is used to include formula results of another formula
    const ELM_CLASSES = [
        word::class,
        triple::class,
        verb::class,
        formula::class
    ];


    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'cache for fast update of formula resolved text';

    // database fields only used for formula elements
    const FLD_ID = 'element_id';
    const FLD_FORMULA_COM = 'each element can only be used for one formula';
    const FLD_ORDER = 'order_nbr';
    const FLD_ORDER_SQL_TYP = sql_field_type::INT;
    const FLD_TYPE = 'element_type_id';
    const FLD_REF_ID_COM = 'either a term, verb or formula id';
    const FLD_REF_ID = 'ref_id';
    const FLD_TEXT = 'resolved_text';
    // TODO: is resolved text needed?

    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        formula::FLD_ID,
        user::FLD_ID,
        self::FLD_ORDER,
        self::FLD_TYPE,
        self::FLD_REF_ID
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [formula::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, self::FLD_FORMULA_COM],
        [self::FLD_ORDER, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', ''],
        [element_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, element_type::class, ''],
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, '', user::class, ''],
        [self::FLD_REF_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_REF_ID_COM],
        [self::FLD_TEXT, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    // TODO should be actually just the linked formula id that extends the term

    public string $type = '';        // the word, verb or formula class name to direct the links
    public ?string $symbol = null;   // the database reference symbol for formula expressions
    public ?object $obj = null;      // the word, verb or formula object
    public ?word $wrd_obj = null;    // in case of a formula the corresponding word object
    public ?string $frm_type = null; // in case of a special formula the predefined formula type


    /*
     * construct and map
     */

    /**
     * always set the user because a formula element is always user specific
     * @param user $usr the user who requested to use this formula element
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        db_object_seq_id_user::__construct($usr);
    }

    /**
     * map the formula element database fields for later load of the object
     *
     * @param array|null $db_row with the data directly from the database
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(?array $db_row): bool
    {
        $this->set_id(0);
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $par_typ = new parameter_type();
            $this->type = $par_typ->class_name($db_row[self::FLD_TYPE]);
            $this->load_obj_by_id($db_row[self::FLD_REF_ID]);
        }
        return $result;
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
     * @return int the database id of the related object
     */
    function id(): int
    {
        return $this->obj?->id();
    }

    /**
     * @return int the database id of the related object
     */
    function trm_id(): int
    {
        return $this->obj?->id();
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * get the related object (term?) from the database
     * @param int $id the id of the formula element
     * @return int the id of the element found and zero if nothing is found
     */
    function load_obj_by_id(int $id): int
    {
        if ($id != 0 and $this->user()->is_set()) {
            if ($this->type == word::class) {
                $wrd = new word($this->user());
                $wrd->load_by_id($id);
                $this->symbol = expression::WORD_START . $wrd->id() . expression::WORD_END;
                $this->obj = $wrd;
            } elseif ($this->type == triple::class) {
                $trp = new triple($this->user());
                $trp->load_by_id($id);
                $this->symbol = expression::TRIPLE_START . $trp->id() . expression::TRIPLE_END;
                $this->obj = $trp;
            } elseif ($this->type == verb::class) {
                $vrb = new verb;
                $vrb->set_user($this->user());
                $vrb->load_by_id($id);
                $this->symbol = expression::TRIPLE_START . $vrb->id() . expression::TRIPLE_END;
                $this->obj = $vrb;
            } elseif ($this->type == formula::class) {
                $frm = new formula($this->user());
                $frm->load_by_id($id);
                $this->symbol = expression::FORMULA_START . $frm->id() . expression::FORMULA_END;
                $this->obj = $frm;
                /*
                // in case of a formula load also the corresponding word
                $wrd = new word($this->user());
                $wrd->load_by_name($frm->name);
                $this->wrd_obj = $wrd;
                */
                //
                if ($frm->is_special()) {
                    $this->frm_type = $frm->type_cl;
                }
            } else {
                log_err('id of type ' . $this->type . ' is not expected');
            }
            log_debug("element->load got " . $this->dsp_id() . " (" . $this->symbol . ").");
        }
        return $id;
    }

    /**
     * create an SQL statement to retrieve a formula element by id from the database
     * just set the class formula element for the parent function
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
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
        if ($this->type <> '') {
            $class_name = $lib->class_to_name($this->type);
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

    /**
     * return the HTML code for the element name including a link to inspect the element
     *
     * @param string $back
     * @return string
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        if ($this->obj != null) {
            if ($this->obj->id() <> 0) {
                // TODO replace with phrase
                if ($this->type == word::class) {
                    $wrd_dsp = new word_dsp($this->obj->api_json());
                    $result = $wrd_dsp->display_linked($back);
                }
                if ($this->type == verb::class) {
                    $result = $this->name();
                }
                if ($this->type == formula::class) {
                    $frm_dsp = new formula_dsp($this->obj->api_json());
                    $result = $frm_dsp->edit_link($back);
                }
            }
        }

        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add an element to the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // get the fields and values that are filled and should be written to the db
        $elm_empty = new element($this->user()->clone_reset());
        $fvt_lst = $this->db_fields_changed($elm_empty);

        // create the sql and get the sql parameters used
        $qp = new sql_par($this::class, $sc_par_lst_used);
        $qp->sql = $sc->create_sql_insert($fvt_lst);
        $qp->par = $fvt_lst->db_values();

        // update the sql creator settings
        $sc->set_class($this::class, $sc_par_lst_used);
        $sc->set_name($qp->name);

        return $qp;
    }

    /**
     * create the sql statement to update a word in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param element $db_row the word with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql_creator $sc, element $db_row, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        // get the field names, values and parameter types that have been changed
        // and that needs to be updated in the database
        // the db_* child function call the corresponding parent function
        // including the sql parameters for logging
        $fvt_lst = $this->db_fields_changed($db_row);
        $this->db_fields_all();
        // create the sql and get the sql parameters used
        $qp = new sql_par($this::class, $sc_par_lst);
        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
        $qp->par = $fvt_lst->db_values();

        // unlike the db_* function the sql_update_* parent function is called directly
        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * field list must be corresponding to the db_fields_changed fields
     *
     * @return array list of all database field names that might have been updated
     */
    function db_fields_all(): array
    {
        return [
            $this::FLD_ID,
            user::FLD_ID,
            self::FLD_REF_ID
        ];
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param element $sbx the compare value to detect the changed fields
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(element $sbx): sql_par_field_list
    {
        $lst = new sql_par_field_list();
        if ($sbx->trm_id() <> $this->trm_id()) {
            $lst->add_field(
                term::FLD_ID,
                $this->trm_id(),
                term::FLD_ID_SQL_TYP,
                $sbx->trm_id()
            );
        }
        return $lst;
    }

}