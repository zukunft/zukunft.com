<?php

/*

    web\phrase.php - the display extension of the api phrase object
    --------------

    mainly links to the word and triple display functions


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

use api\phrase_api;

class phrase_dsp extends phrase_api
{

    /**
     * @returns string the html code to display with mouse over that shows the description
     */
    function dsp(): string
    {
        if ($this->is_word()) {
            return $this->wrd_dsp()->dsp();
        } else {
            return $this->trp_dsp()->dsp();
        }
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function dsp_link(): string
    {
        return $this->name;
    }

    /**
     * simply to display a single word in a table cell
     */
    function dsp_tbl_cell(int $intent): string
    {
        $result = '';
        if ($this->is_word()) {
            $wrd = $this->wrd_dsp();
            $result .= $wrd->td('', '', $intent);
        }
        return $result;
    }

    /**
     * @returns string the html code that allows the user to unlink this phrase
     */
    function dsp_unlink(int $link_id): string
    {
        $result = '    <td>' . "\n";
        $result .= \html\btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id);
        $result .= '    </td>' . "\n";

        return $result;
    }

    //
    //
    // $type
    /**
     * create a selector that contains the words and triples
     * if one form contains more than one selector, $pos is used for identification
     *
     * @param phrase_api $type is a word to preselect the list to only those phrases matching this type
     * @param string $form_name
     * @param int $pos
     * @param string $class
     * @param string $back
     * @return string
     */
    function dsp_selector(phrase_api $type, string $form_name, int $pos, string $class, string $back = ''): string
    {
        $result = '';

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
        } else {
            $field_name = "phrase";
        }
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_name;
        if ($form_name == "value_add" or $form_name == "value_edit") {
            $sel->label = "";
        } else {
            if ($pos == 1) {
                $sel->label = "From:";
            } elseif ($pos == 2) {
                $sel->label = "To:";
            } else {
                $sel->label = "Word:";
            }
        }
        $sel->bs_class = $class;
        $sel->sql = $this->sql_list($type);
        $sel->selected = $this->id;
        $sel->dummy_text = '... please select';
        $result .= $sel->display();

        return $result;
    }

}
