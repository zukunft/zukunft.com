<?php

/*

    web/view/view_list.php - a list function to create the HTML code to display a view list
    ----------------------

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

use cfg\const\paths;
use html\const\paths as html_paths;
use html\ref\source;
use html\rest_call;
use html\sandbox\list_dsp;
use html\sandbox\sandbox;
use html\user\user_message;
use html\verb\verb;
use html\view\view as view_dsp;
use html\word\triple;
use html\word\word;
use shared\const\views;
use shared\types\view_styles;
use shared\url_var;

include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

class view_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of the view list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new view_dsp());
    }

    function get_by_code_id(string $code_id): view_dsp
    {
        // TODO use a hash list
        $result = new view_dsp();
        foreach ($this->lst() as $dsp) {
            if ($dsp->code_id() == $code_id) {
                $result = $dsp;
            }
        }
        return $result;
    }


    /*
     * load
     */

    function load_by_pattern(string $pattern = '%'): bool
    {
        $result = false;

        $data = array(url_var::PATTERN => $pattern);
        $rest = new rest_call();
        $json_body = $rest->api_get(view_list::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * get the views that use this component from the backend
     *
     * @param int $id of the component
     * @return bool true if the load has been successful
     */
    function load_by_component_id(int $id): bool
    {
        $result = false;

        $data = array(url_var::CMP_ID => $id);
        $rest = new rest_call();
        $json_body = $rest->api_get(view_base::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * base
     */

    /**
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $views = array();
        foreach ($this->lst() as $msk) {
            $views[] = $msk->name_tip();
        }
        return implode(', ', $views);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function name_link(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the view names with html links
     */
    private function names_linked(string $back = ''): array
    {
        $views = array();
        foreach ($this->lst() as $msk) {
            $views[] = $msk->name_link();
        }
        return $views;
    }


    /*
     * info
     */

    /**
     * get the default view
     * TODO if a phrase can be ranked use the ranking view
     * @param sandbox $sbx the object to which the default view should be found
     * @return int the view id if no view has been selected until now
     */
    function default_id(sandbox $sbx): int
    {
        return match ($sbx::class) {
            word::class => views::WORD_ID,
            verb::class => views::VERB_ID,
            triple::class => views::TRIPLE_ID,
            source::class => views::SOURCE_ID,
            default => views::START_ID
        };
    }


    /*
     * select
     */

    /**
     * HTML code of a view selector
     * @param string $form the name of the html form
     * @param int $selected the id of the preselected item
     * @param string $name the unique name inside the form for this selector
     * @param string $label the label name (TODO remove from the selector?
     * @param string $col_class the formatting code to adjust the formatting
     * @param string $pattern the pattern to filter the views
     * @return string with the HTML code to show the view selector
     */
    function selector(
        string    $form = '',
        int       $selected = 0,
        string    $name = 'view',
        string    $label = 'view: ',
        string    $col_class = view_styles::COL_SM_4,
        string    $pattern = ''
    ): string
    {
        if ($pattern != '') {
            $this->load_like($pattern);
        }
        return parent::selector($form, $selected, $name, $label, $col_class);
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
            if ($view_id == $this->id()) {
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
