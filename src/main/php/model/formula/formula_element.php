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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class formula_element
{

    const TYPE_WORD = 'word';        //
    const TYPE_VERB = 'verb';        //
    const TYPE_FORMULA = 'formula';  //

    public ?int $id = null;          // the database id of the word, verb or formula
    public ?user $usr = null;        // the person who has requested the formula element
    public string $type = '';        // either "word", "verb" or "formula" to direct the links
    public ?string $name = null;     // the user name of the formula element
    public ?string $dsp_name = null; // the user name of the formula element with the HTML link
    public ?string $back = null;     // link to what should be display after this action is finished
    public ?string $symbol = null;   // the database reference symbol for formula expressions
    public ?object $obj = null;      // the word, verb or formula object
    public ?int $wrd_id = null;      // in case of a formula the corresponding word id (maybe a duplicate of the wrd_obj of the formula)
    public ?word $wrd_obj = null;    // in case of a formula the corresponding word object
    public ?string $frm_type = null; // in case of a special formula the predefined formula type

    // get the name and other parameters from the database
    function load()
    {
        if ($this->id > 0 and isset($this->usr)) {
            if ($this->type == self::TYPE_WORD) {
                $wrd = new word_dsp;
                $wrd->id = $this->id;
                $wrd->usr = $this->usr;
                $wrd->load();
                $this->name = $wrd->name;
                $this->dsp_name = $wrd->display($this->back);
                $this->symbol = ZUP_CHAR_WORD_START . $wrd->id . ZUP_CHAR_WORD_END;
                $this->obj = $wrd;
            }
            if ($this->type == self::TYPE_VERB) {
                $lnk = new verb;
                $lnk->id = $this->id;
                $lnk->usr = $this->usr;
                $lnk->load();
                $this->name = $lnk->name;
                $this->dsp_name = $lnk->display($this->back);
                $this->symbol = ZUP_CHAR_LINK_START . $lnk->id . ZUP_CHAR_LINK_END;
                $this->obj = $lnk;
            }
            if ($this->type == self::TYPE_FORMULA) {
                $frm = new formula;
                $frm->id = $this->id;
                $frm->usr = $this->usr;
                $frm->load();
                $this->name = $frm->name;
                $this->dsp_name = $frm->name_linked($this->back);
                $this->symbol = ZUP_CHAR_FORMULA_START . $frm->id . ZUP_CHAR_FORMULA_END;
                $this->obj = $frm;
                // in case of a formula load also the corresponding word
                $wrd = new word_dsp;
                $wrd->name = $frm->name;
                $wrd->usr = $this->usr;
                $wrd->load();
                $this->wrd_id = $wrd->id;
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

    display functions

    */

    // return best possible id for this element mainly used for debugging
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

    // to show the element name to the user in the most simple form (without any ids)
    function name()
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
                    $result = $this->obj->name();
                } else {
                    $result = $this->name;
                }
            }
        }

        return $result;
    }

    // return the HTML code for the element name including a link to inspect the element
    function name_linked($back)
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
                    $result = $this->obj->name_linked($back);
                } else {
                    $result = $this->name;
                }
            }
        }

        return $result;
    }

}