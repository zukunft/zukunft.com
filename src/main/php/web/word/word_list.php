<?php

/*

    word_list_dsp.php - a list function to create the HTML code to display a word list
    -----------------

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

namespace html;

include_once WEB_SANDBOX_PATH . 'list.php';
//include_once CFG_PATH . 'phrase_type.php';

use cfg\phrase_type;
use term_list;
use user;

class word_list_dsp extends list_dsp
{

    /*
     * modify
     */

    /**
     * add a word to the list
     * @returns bool true if the word has been added
     */
    function add(word_dsp $phr): bool
    {
        return parent::add_obj($phr);
    }


    /*
     * display
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the word names with html links
     * ex. names_linked
     */
    function dsp(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the word names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst as $wrd) {
            if (!$wrd->is_hidden()) {
                $result[] = $wrd->dsp_link($back);
            }
        }
        return $result;
    }

    /**
     * show all words of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function tbl(string $back = ''): string
    {
        $html = new html_base();
        $cols = '';
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst as $wrd) {
            $lnk = $wrd->dsp_link($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), html_base::STYLE_BORDERLESS);
    }

    /**
     * @returns string the html code to select a word from this list
     */
    function selector(string $name = '', string $form = '', int $selected = 0): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;
        return $sel->dsp();
    }

    // display a list of words that match to the given pattern
    // TODO REVIEW
    function dsp_like($word_pattern, user $usr): string
    {
        log_debug($word_pattern . ',u' . $usr->id());

        global $db_con;
        global $phrase_types;

        $result = '';

        $back = 1;
        $trm_lst = new term_list($usr);

        // get the link types related to the word
        $sql = " ( SELECT t.word_id AS id, t.word_name AS name, 'word' AS type
                 FROM words t 
                WHERE t.word_name like '" . $word_pattern . "%' 
                  AND t.word_type_id <> " . $phrase_types->id(phrase_type::FORMULA_LINK) . ")
       UNION ( SELECT f.formula_id AS id, f.formula_name AS name, 'formula' AS type
                 FROM formulas f 
                WHERE f.formula_name like '" . $word_pattern . "%' )
             ORDER BY name
                LIMIT 200;";
        //$db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get_old($sql);

        // loop over the words and display it with the link
        foreach ($db_lst as $db_row) {
            //while ($entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
            if ($db_row['type'] == "word") {
                $wrd = new word_dsp($db_row['id'], $db_row['name']);
                $result .= $wrd->tr();
            }
            if ($db_row['type'] == "formula") {
                $frm = new formula_dsp();
                $frm->id = $db_row['id'];
                $frm->set_name($db_row['name']);
                $result .= $frm->name_linked($back);
            }
        }

        return $result;
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
     * @param word_list_dsp $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(word_list_dsp $del_lst): void
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
     * @param word_list_dsp $new_wrd_lst with the words that should be added
     */
    function merge(word_list_dsp $new_wrd_lst)
    {
        foreach ($new_wrd_lst->lst as $new_wrd) {
            $this->add($new_wrd);
        }
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @return word_list_dsp with the all words of the give type
     */
    private function filter(string $type): word_list_dsp
    {
        $result = new word_list_dsp();
        foreach ($this->lst as $wrd) {
            if ($wrd->is_type($type)) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all time words from this list of words
     */
    function time_lst(): word_list_dsp
    {
        return $this->filter(phrase_type::TIME);
    }

    /**
     * get all measure words from this list of words
     */
    function measure_lst(): word_list_dsp
    {
        return $this->filter(phrase_type::MEASURE);
    }

    /**
     * get all scaling words from this list of words
     */
    function scaling_lst(): word_list_dsp
    {
        $result = new word_list_dsp();
        foreach ($this->lst as $wrd) {
            if ($wrd->is_scaling()) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all measure and scaling words from this list of words
     * @returns word_list_dsp words that are usually shown after a number
     */
    function measure_scale_lst(): word_list_dsp
    {
        $scale_lst = $this->scaling_lst();
        $measure_lst = $this->measure_lst();
        $measure_lst->merge($scale_lst);
        return $measure_lst;
    }

    /**
     * get all measure words from this list of words
     */
    function percent_lst(): word_list_dsp
    {
        return $this->filter(phrase_type::PERCENT);
    }

    /**
     * like names_linked, but without measure and time words
     * because measure words are usually shown after the number
     * TODO call this from the display object t o avoid casting again
     * @returns word_list_dsp a word
     */
    function ex_measure_and_time_lst(): word_list_dsp
    {
        $wrd_lst_ex = clone $this;
        $wrd_lst_ex->ex_time();
        $wrd_lst_ex->ex_measure();
        $wrd_lst_ex->ex_scaling();
        $wrd_lst_ex->ex_percent(); // the percent sign is normally added to the value
        return $wrd_lst_ex;
    }

    /**
     * Exclude all time words from this word list
     */
    function ex_time(): void
    {
        $this->diff($this->time_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_measure(): void
    {
        $this->diff($this->measure_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_scaling(): void
    {
        $this->diff($this->scaling_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_percent(): void
    {
        $this->diff($this->percent_lst());
    }

}
