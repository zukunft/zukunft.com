<?php

/*

    /web/value_list.php - the display extension of the api value list object
    ------------------

    to creat the HTML code to display a list of values


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

namespace html\value;

include_once WEB_PHRASE_PATH . 'phrase_group_list.php';

use api\phrase_list_api;
use html\button;
use html\html_base;
use html\list_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use html\phrase\phrase_group_list as phrase_group_list_dsp;
use html\value\value as value_dsp;
use model\library;

class value_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a value object based on the given json
     * @param array $json_array an api single object json message
     * @return object a value set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new value_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(value_dsp $val): bool
    {
        $result = false;
        if (!in_array($val->id(), $this->id_lst())) {
            $this->lst[] = $val;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }


    /*
     * display
     */

    /**
     * @param phrase_list_dsp|null $context_phr_lst list of phrases that are already known to the user by the context of this table and that does not need to be shown to the user again
     * @param string $back
     * @return string the html code to show the values as a table to the user
     */
    function table(phrase_list_dsp $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;

        // get the common phrases of the value list e.g. inhabitants, 2019
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        // TODO make the replacement language and user specific
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->display_linked();
        }

        // TODO add a button to add a new value using
        //$btn_new = $common_phrases->btn_add_value();
        $btn_new = '';

        // display the single values
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $val) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($val->name_linked($common_phrases));
            $row .= $html->td($val->value_linked($back));
            $rows .= $html->tr($row);
            // TODO add button to delete a value or add a similar value
            //$btn_del = $val->btn_del();
            //$btn_add = $val->btn_add();
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF) . $btn_new;
    }


    /*
     * info
     */

    /**
     * @return phrase_list_dsp a list of phrases used for each value
     * similar to the model function with the same name
     */
    function common_phrases(): phrase_list_dsp
    {
        $lib = new library();
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * return a list of phrase groups for all values of this list
     */
    function phrase_groups(): phrase_group_list_dsp
    {
        log_debug();
        $lib = new library();
        $grp_lst = new phrase_group_list_dsp();
        foreach ($this->lst as $val) {
            $grp = $val->grp();
            if ($grp != null) {
                $grp_lst->lst[] = $grp;
            } else {
                log_err("The phrase group for value " . $val->id . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug($lib->dsp_count($grp_lst->lst));
        return $grp_lst;
    }

}
