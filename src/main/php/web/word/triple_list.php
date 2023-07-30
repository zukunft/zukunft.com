<?php

/*

    web/word/triple_list_dsp.php - a list function to create the HTML code to display a triple list
    ----------------------------

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

namespace html\word;

include_once WEB_SANDBOX_PATH . 'list.php';


use cfg\phrase_type;
use html\formula\formula as formula_dsp;
use html\html_base;
use html\html_selector;
use html\list_dsp;
use html\word\triple as triple_dsp;
use html\word\triple_list as triple_list_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use cfg\term_list;
use cfg\user;
use cfg\verb;
use word_select_direction;

class triple_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a triple object based on the given json
     * @param array $json_array an api single object json message
     * @return object a triple set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new triple_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a triple to the list
     * @returns bool true if the triple has been added
     */
    function add(triple_dsp $phr): bool
    {
        return parent::add_obj($phr);
    }


    /*
     * display
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the triple names with html links
     * ex. names_linked
     */
    function display(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the triple names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst as $wrd) {
            if (!$wrd->is_hidden()) {
                $result[] = $wrd->display_linked($back);
            }
        }
        return $result;
    }

    /**
     * show all triples of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @param bool $add_btn set to true for eas allow of similar triples
     * @return string the html code with all triples of the list
     */
    function tbl(string $back = '', bool $add_btn = false): string
    {
        $html = new html_base();
        $cols = '';
        $last_trp = null;
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst as $trp) {
            $lnk = $trp->display_linked($back);
            $cols .= $html->td($lnk);
            $last_trp = $trp;
        }
        if ($add_btn) {
            $add_trp = $this->suggested();
            $add_url = $add_trp->btn_add($back);
            $cols .= $html->td($add_url);
        }
        return $html->tbl($html->tr($cols), html_base::STYLE_BORDERLESS);
    }

    /**
     * @returns string the html code to select a triple from this list
     */
    function selector(string $name = '', string $form = '', int $selected = 0): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;
        return $sel->display();
    }


    /*
     * select
     */

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param triple_list_dsp $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(triple_list_dsp $del_lst): void
    {
        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst as $wrd) {
                if (!in_array($wrd->id(), $lst_ids)) {
                    $result[] = $wrd;
                }
            }
            $this->lst = $result;
        }
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @param triple_list_dsp $new_wrd_lst with the triples that should be added
     */
    function merge(triple_list_dsp $new_wrd_lst): void
    {
        foreach ($new_wrd_lst->lst as $new_wrd) {
            $this->add($new_wrd);
        }
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @return triple_list_dsp with the all triples of the give type
     */
    private function filter(string $type): triple_list_dsp
    {
        $result = new triple_list_dsp();
        foreach ($this->lst as $wrd) {
            if ($wrd->is_type($type)) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all time triples from this list of triples
     */
    function time_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type::TIME);
    }

    /**
     * get all measure triples from this list of triples
     */
    function measure_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type::MEASURE);
    }

    /**
     * get all scaling triples from this list of triples
     */
    function scaling_lst(): triple_list_dsp
    {
        $result = new triple_list_dsp();
        foreach ($this->lst as $wrd) {
            if ($wrd->is_scaling()) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all measure and scaling triples from this list of triples
     * @returns triple_list_dsp triples that are usually shown after a number
     */
    function measure_scale_lst(): triple_list_dsp
    {
        $scale_lst = $this->scaling_lst();
        $measure_lst = $this->measure_lst();
        $measure_lst->merge($scale_lst);
        return $measure_lst;
    }

    /**
     * get all measure triples from this list of triples
     */
    function percent_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type::PERCENT);
    }

    /**
     * like names_linked, but without measure and time triples
     * because measure triples are usually shown after the number
     * TODO call this from the display object t o avoid casting again
     * @returns triple_list_dsp a triple
     */
    function ex_measure_and_time_lst(): triple_list_dsp
    {
        $wrd_lst_ex = clone $this;
        $wrd_lst_ex->ex_time();
        $wrd_lst_ex->ex_measure();
        $wrd_lst_ex->ex_scaling();
        $wrd_lst_ex->ex_percent(); // the percent sign is normally added to the value
        return $wrd_lst_ex;
    }

    /**
     * Exclude all time triples from this triple list
     */
    function ex_time(): void
    {
        $this->diff($this->time_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_measure(): void
    {
        $this->diff($this->measure_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_scaling(): void
    {
        $this->diff($this->scaling_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_percent(): void
    {
        $this->diff($this->percent_lst());
    }

    /**
     * @return phrase_list_dsp with all from phrases
     */
    function from_phrase_list(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        foreach ($this->lst() as $trp) {
            $lst->add($trp->from);
        }
        return $lst;
    }

    /**
     * @return phrase_list_dsp with all from phrases
     */
    function to_phrase_list(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        foreach ($this->lst() as $trp) {
            $lst->add($trp->to);
        }
        return $lst;
    }

    function suggested(): triple_dsp
    {
        $trp = new triple_dsp();
        $from_lst = $this->from_phrase_list();
        $from_phr = $from_lst->mainly();
        if ($from_phr != null) {
            $trp->set_from($from_phr);
        }
        // TODO preset verb
        $to_lst = $this->to_phrase_list();
        $to_phr = $to_lst->mainly();
        if ($to_phr != null) {
            $trp->set_to($to_phr);
        }
        return $trp;
    }

}
