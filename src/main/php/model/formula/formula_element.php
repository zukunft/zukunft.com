<?php

/*

    formula_element.php - either a word, verb or formula link
    -------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class formula_element
{

    // the allowed objects types for a formula element
    const TYPE_WORD = word::class;        // a word is used for an AND selection of values
    const TYPE_VERB = verb::class;        // a verb is used for dynamic usage of linked words for an AND selection
    const TYPE_FORMULA = formula::class;  // a formula is used to include formula results of another formula

    // means: database fields only used for words
    const FLD_ORDER = 'order_nbr';
    const FLD_TYPE = 'formula_element_type_id';
    const FLD_REF_ID = 'ref_id';
    // TODO: is resolved text needed?

    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        formula::FLD_ID,
        user_sandbox::FLD_USER,
        self::FLD_ORDER,
        self::FLD_TYPE,
        self::FLD_REF_ID
    );

    public ?int $id = null;          // the database id of the word, verb or formula
    public ?user $usr = null;        // the person who has requested the formula element
    public string $type = '';        // either "word", "verb" or "formula" to direct the links
    public ?string $name = null;     // the username of the formula element
    public ?string $dsp_name = null; // the username of the formula element with the HTML link
    public ?string $back = null;     // link to what should be display after this action is finished
    public ?string $symbol = null;   // the database reference symbol for formula expressions
    public ?object $obj = null;      // the word, verb or formula object
    public ?word $wrd_obj = null;    // in case of a formula the corresponding word object
    public ?string $frm_type = null; // in case of a special formula the predefined formula type

    // get the name and other parameters from the database
    function load()
    {
        if ($this->id > 0 and isset($this->usr)) {
            if ($this->type == self::TYPE_WORD) {
                $wrd = new word_dsp($this->usr);
                $wrd->id = $this->id;
                $wrd->load();
                $this->name = $wrd->name;
                $this->dsp_name = $wrd->display($this->back);
                $this->symbol = expression::MAKER_WORD_START . $wrd->id . expression::MAKER_WORD_END;
                $this->obj = $wrd;
            }
            if ($this->type == self::TYPE_VERB) {
                $lnk = new verb;
                $lnk->id = $this->id;
                $lnk->usr = $this->usr;
                $lnk->load();
                $this->name = $lnk->name;
                $this->dsp_name = $lnk->display($this->back);
                $this->symbol = expression::MAKER_TRIPLE_START . $lnk->id . expression::MAKER_TRIPLE_END;
                $this->obj = $lnk;
            }
            if ($this->type == self::TYPE_FORMULA) {
                $frm = new formula($this->usr);
                $frm->id = $this->id;
                $frm->load();
                $this->name = $frm->name;
                $this->dsp_name = $frm->dsp_obj()->name_linked($this->back);
                $this->symbol = expression::MAKER_FORMULA_START . $frm->id . expression::MAKER_FORMULA_END;
                $this->obj = $frm;
                // in case of a formula load also the corresponding word
                $wrd = new word_dsp($this->usr);
                $wrd->name = $frm->name;
                $wrd->load();
                $this->wrd_obj = $wrd;
                //
                if ($frm->is_special()) {
                    $this->frm_type = $frm->type_cl;
                }
            }
            log_debug("formula_element->load got " . $this->dsp_id() . " (" . $this->symbol . ").");
        }
    }

    /*
     * display functions
     */

    /**
     * return best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->type <> '') {
            $result .= $this->type . ' ';
        }
        $name = $this->name();
        if ($name <> '') {
            $result .= '"' . $name . '" ';
        }
        $result .= '(' . $this->id . ')';
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    /**
     * to show the element name to the user in the most simple form (without any ids)
     */
    function name(): string
    {
        $result = '';

        if ($this->id > 0 and isset($this->usr)) {
            if ($this->type == 'word') {
                if (isset($this->obj)) {
                    $result = $this->obj->name();
                } else {
                    $result = $this->name;
                }
            } elseif ($this->type == 'verb') {
                $result = $this->name;
            } elseif ($this->type == 'formula') {
                if (isset($this->obj)) {
                    $result = $this->obj->name;
                } else {
                    $result = $this->name;
                }
            }
        }

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

        if ($this->id > 0 and isset($this->usr)) {
            if ($this->type == 'word') {
                if (isset($this->obj)) {
                    $result = $this->obj->display($back);
                } else {
                    $result = $this->name;
                }
            }
            if ($this->type == 'verb') {
                $result = $this->name;
            }
            if ($this->type == 'formula') {
                if (isset($this->obj)) {
                    $result = $this->obj->dsp_obj()->name_linked($back);
                } else {
                    $result = $this->name;
                }
            }
        }

        return $result;
    }

}