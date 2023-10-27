<?php

/*

    model/formula/formula_element.php - either a word, triple, verb or formula with a link to a formula
    ---------------------------------

    formula elements are terms or expression operators such as add or brackets
    The term formula elements are saved in the database for fast detection of dependencies
    formula elements are terms with a link to a formula

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

namespace cfg;

include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';

use cfg\db\sql;
use html\word\word as word_dsp;
use html\formula\formula as formula_dsp;

class formula_element extends db_object_seq_id_user
{

    // the allowed objects types for a formula element
    const TYPE_WORD = word::class;        // a word is used for an AND selection of values
    const TYPE_TRIPLE = triple::class;    // a triple is used for an AND selection of values
    const TYPE_VERB = verb::class;        // a verb is used for dynamic usage of linked words for an AND selection
    const TYPE_FORMULA = formula::class;  // a formula is used to include formula results of another formula

    // database fields only used for formula elements
    const FLD_ID = 'formula_element_id';
    const FLD_ORDER = 'order_nbr';
    const FLD_TYPE = 'formula_element_type_id';
    const FLD_REF_ID = 'ref_id';
    // TODO: is resolved text needed?

    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        formula::FLD_ID,
        user::FLD_ID,
        self::FLD_ORDER,
        self::FLD_TYPE,
        self::FLD_REF_ID
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
    }

    /**
     * map the formula element database fields for later load of the object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $this->id = 0;
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
     * @return int|null the database id of the related object
     */
    function id(): int
    {
        return $this->obj?->id;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to get the formula element from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * get the related object (term?) from the database
     * @param int $id the id of the formula element
     * @param string $class the name of the class which is 'formula_element'
     * @return int the id of the formula_element found and zero if nothing is found
     */
    function load_obj_by_id(int $id, string $class = self::class): int
    {
        if ($id != 0 and $this->user()->is_set()) {
            if ($this->type == self::TYPE_WORD) {
                $wrd = new word($this->user());
                $wrd->load_by_id($id, word::class);
                $this->symbol = expression::WORD_START . $wrd->id() . expression::WORD_END;
                $this->obj = $wrd;
            } elseif ($this->type == self::TYPE_TRIPLE) {
                $trp = new triple($this->user());
                $trp->load_by_id($id);
                $this->symbol = expression::TRIPLE_START . $trp->id() . expression::TRIPLE_END;
                $this->obj = $trp;
            } elseif ($this->type == self::TYPE_VERB) {
                $vrb = new verb;
                $vrb->set_user($this->user());
                $vrb->load_by_id($id);
                $this->symbol = expression::TRIPLE_START . $vrb->id . expression::TRIPLE_END;
                $this->obj = $vrb;
            }
            if ($this->type == self::TYPE_FORMULA) {
                $frm = new formula($this->user());
                $frm->load_by_id($id, formula::class);
                $this->symbol = expression::FORMULA_START . $frm->id() . expression::FORMULA_END;
                $this->obj = $frm;
                /*
                // in case of a formula load also the corresponding word
                $wrd = new word($this->user());
                $wrd->load_by_name($frm->name, word::class);
                $this->wrd_obj = $wrd;
                */
                //
                if ($frm->is_special()) {
                    $this->frm_type = $frm->type_cl;
                }
            }
            log_debug("formula_element->load got " . $this->dsp_id() . " (" . $this->symbol . ").");
        }
        return $id;
    }

    /**
     * create an SQL statement to retrieve a formula element by id from the database
     * just set the class formula element for the parent function
     *
     * @param sql $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int $id): sql_par
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
        if ($this->id > 0) {
            $result .= '(' . $this->id . ')';
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

}