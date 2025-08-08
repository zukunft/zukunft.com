<?php

/*

    web/formula/formula_list.php - a list function to create the HTML code to display a formula list
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

namespace html\formula;

use html\const\paths as html_paths;

include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::USER . 'user_message.php';

use html\html_base;
use html\sandbox\list_dsp;
use html\formula\formula as formula_dsp;
use html\styles;
use html\user\user_message;

class formula_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a formula object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new formula_dsp());
    }


    /*
     * display
     */

    /**
     * @return string with a list of the formula names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $names = array();
        foreach ($this->lst() as $frm) {
            $names[] = $frm->name_tip();
        }
        return implode(', ', $names);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the formula names with html links
     * ex. names_linked
     */
    function name_link(string $back = ''): string
    {
        return implode(', ', $this->names_link($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the formula names with html links
     */
    private function names_link(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst() as $frm) {
            $result[] = $frm->name_link($back);
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
        foreach ($this->lst() as $wrd) {
            $lnk = $wrd->dsp_obj()->display_linked($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), styles::STYLE_BORDERLESS);
    }

    /**
     * lists all formulas with results related to a word
     */
    function display_old($type = 'short'): string
    {
        log_debug('formula_list->display ' . $this->dsp_id());
        $result = '';
        $back = '';

        // list all related formula results
        if ($this->lst() != null) {
            // TODO add usort to base_list
            $lst = $this->lst();
            usort($lst, array(formula::class, "cmp"));
            $this->set_lst($lst);
            if ($this->lst() != null) {
                foreach ($this->lst() as $frm) {
                    // formatting should be moved
                    //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
                    //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($back));
                    $frm_dsp = $frm->dsp_obj_old();
                    $frm_html = new formula_dsp($frm->api_json());
                    $result = '';
                    if ($frm->name_wrd != null) {
                        $result = $frm_dsp->dsp_result($frm->name_wrd->phrase(), $back);
                    }
                    // if the result is empty use the id to be able to select the formula
                    if ($result == '') {
                        $result .= $frm_dsp->id();
                    } else {
                        $result .= ' value ' . $result;
                    }
                    $result .= ' ' . $frm_html->edit_link($back);
                    if ($type == 'short') {
                        $result .= ' ' . $frm_dsp->btn_del($back);
                        $result .= ', ';
                    } else {
                        $result .= ' (' . $frm_dsp->dsp_text($back) . ')';
                        $result .= ' ' . $frm_dsp->btn_del($back);
                        $result .= ' <br> ';
                    }
                }
            }
        }

        log_debug("formula_list->display ... done (" . $result . ")");
        return $result;
    }

}
