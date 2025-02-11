<?php

/*

    result_list_min_display.php - the display extension of the api result list object
    ---------------------------

    to creat the HTML code to display a list of formula results


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

namespace html\result;

include_once WEB_SANDBOX_PATH . 'list_value.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_HTML_PATH . 'html_base.php';
//include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_GROUP_PATH . 'group_list.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_SANDBOX_PATH . 'sandbox_list.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'api.php';

use html\rest_ctrl as api_dsp;
use html\html_base;
use html\formula\formula;
use html\group\group_list;
use html\phrase\phrase_list;
use html\sandbox\list_value;
use html\user\user_message;
use shared\api;

class result_list extends list_value
{

    /*
     * set and get
     */

    /**
     * set the vars of a result object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return parent::set_list_from_json($json_array, new result());
    }


    /*
     * load
     */

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param formula $frm to select the result
     * @param group_list $lst the group used for the selection
     * @return bool true if result has been loaded
     */
    function load_by_formula_and_group_list(formula $frm, group_list $lst): bool
    {
        $result = false;

        $api = new api_dsp();
        $data = array();
        $data[api::URL_VAR_FORMULA] = $frm->id();
        $data[api::URL_VAR_GROUP] = $lst->ids();
        $json_body = $api->api_get(self::class, $data);
        $this->set_from_json_array($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }



    /*
     * modify
     */

    /**
     * add a formula result to the list
     * @returns bool true if the formula result has been added
     */
    function add(result $res): bool
    {
        $result = false;
        if (!in_array($res->id(), $this->id_lst())) {
            $this->add_direct($res);
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * load a list of results linked to
     * a formula
     * a phrase group
     *   either of the source or the result
     *   and with or without time selection
     * a word or a triple
     *
     * @param object $obj a named object used for selection e.g. a formula
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if value or phrases are found
     */
    function load_by_obj(object $obj, bool $by_source = false): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_obj_old($db_con, $obj, $by_source);
        return $this->load($qp);
    }


    /*
     * display
     */

    /**
     * @return string with a list of the result names with html links
     * ex. names_linked
     */
    function display(): string
    {
        $results = array();
        foreach ($this->lst() as $res) {
            $results[] = $res->display();
        }
        return implode(', ', $results);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the result names with html links
     * ex. names_linked
     */
    function display_linked(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the result names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst() as $res) {
            $result[] = $res->display_linked();
        }
        return $result;
    }

    /**
     * @return string the html code to show the results as a table to the user
     */
    function table(phrase_list $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->name_link();
        }
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $res) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($res->display_linked($common_phrases));
            $row .= $html->td($res->value_linked($back));
            $rows .= $html->tr($row);
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF);
    }

}
