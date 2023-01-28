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

use api\word_list_api;
use cfg\phrase_type;
use db_cl;
use formula;
use term_list;
use user;

class word_list_dsp extends word_list_api
{

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
                $result[] = $wrd->dsp_obj()->dsp_link($back);
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
            $lnk = $wrd->dsp_obj()->dsp_link($back);
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
        $result = '';

        $back = 1;
        $trm_lst = new term_list($usr);

        // get the link types related to the word
        $sql = " ( SELECT t.word_id AS id, t.word_name AS name, 'word' AS type
                 FROM words t 
                WHERE t.word_name like '" . $word_pattern . "%' 
                  AND t.word_type_id <> " . cl(db_cl::PHRASE_TYPE, phrase_type::FORMULA_LINK) . ")
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

}
