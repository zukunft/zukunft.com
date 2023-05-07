<?php

/*

    view_list_dsp.php - a list function to create the HTML code to display a view list
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

namespace html\view;

include_once WEB_SANDBOX_PATH . 'list.php';
include_once MODEL_VIEW_PATH . 'view_list.php';

use html\list_dsp;
use html\view\view as view_dsp;

class view_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a view object based on the given json
     * @param array $json_array an api single object json message
     * @return object a view set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new view_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a view to the list
     * @returns bool true if the view has been added
     */
    function add(view_dsp $dsp): bool
    {
        return parent::add_obj($dsp);
    }


    /*
     * display
     */

    /**
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function display(): string
    {
        $views = array();
        foreach ($this->lst as $fig) {
            $views[] = $fig->display();
        }
        return implode(', ', $views);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function display_linked(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the view names with html links
     */
    function names_linked(string $back = ''): array
    {
        $views = array();
        foreach ($this->lst as $fig) {
            $views[] = $fig->display_linked();
        }
        return $views;
    }


    /**
     * create a selection page where the user can select a view that should be used for a view
     */
    /*
    function selector_page($wrd_id, $back): string
    {

        global $db_con;
        $result = '';

        $sql = "SELECT view_id, view_name
                  FROM views
                 WHERE code_id IS NULL
              ORDER BY view_name;";
        $sql = sql_lst_usr("view", $this->user());
        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        $dsp_lst = $db_con->get_old($sql);
        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp['id'];
            $view_name = $dsp['name'];
            if ($view_id == $this->id) {
                $result .= '<b><a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a></b> ';
            } else {
                $result .= '<a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a> ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        log_debug('done');
        return $result;
    }
    */

}
