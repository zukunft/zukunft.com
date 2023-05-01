<?php

/*

    formula_list_dsp.php - a list function to create the HTML code to display a formula list
    -----------------

    This file is part of zukunft.com - calc with formulas

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

namespace html\formula;

use html\html_base;
use html\html_selector;
use html\list_dsp;
use html\formula\formula as formula_dsp;

include_once WEB_SANDBOX_PATH . 'list.php';

class formula_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a formula object based on the given json
     * @param array $json_array an api single object json message
     * @return object a formula set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new formula_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a formula to the list
     * @returns bool true if the formula has been added
     */
    function add(formula_dsp $wrd): bool
    {
        return parent::add_obj($wrd);
    }


    /*
     * display
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the formula names with html links
     * ex. names_linked
     */
    function display(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the formula names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst as $wrd) {
            if (!$wrd->is_hidden()) {
                $result[] = $wrd->dsp_obj()->display_linked($back);
            }
        }
        return $result;
    }

    /**
     * show all formulas of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @return string the html code with all formulas of the list
     */
    function tbl(string $back = ''): string
    {
        $html = new html_base();
        $cols = '';
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst as $wrd) {
            $lnk = $wrd->dsp_obj()->display_linked($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), html_base::STYLE_BORDERLESS);
    }

    /**
     * @returns string the html code to select a formula from this list
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

}
