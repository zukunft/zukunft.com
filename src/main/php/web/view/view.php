<?php

/*

    /web/view/view.php - the display extension of the api view object
    ------------------

    to creat the HTML code to display a view


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

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use controller\controller;
use html\api;
use html\button;
use html\html_base;
use html\system\back_trace;
use html\view\component_list as component_list_dsp;
use html\sandbox_typed_dsp;
use html\word\word;
use html\sandbox\db_object as db_object_dsp;
use model\library;

class view extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    // used for system views
    private string $code_id;
    private component_list_dsp $cmp_lst;


    /*
     * set and get
     */

    /**
     * repeat here the sandbox object function to force to include all view object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new view();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars this view bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_CODE_ID, $json_array)) {
            $this->code_id = $json_array[controller::API_FLD_CODE_ID];
        } else {
            $this->code_id = null;
        }
        $cmp_lst = new component_list_dsp();
        if (array_key_exists(controller::API_FLD_COMPONENTS, $json_array)) {
            $cmp_lst->set_from_json_array($json_array[controller::API_FLD_COMPONENTS]);
        }
        $this->cmp_lst = $cmp_lst;
    }

    function component_list(): component_list_dsp
    {
        return $this->cmp_lst;
    }

    function code_id(): string
    {
        return $this->code_id;
    }


    /*
     * display
     */

    /**
     * TODO review these simplified function
     * @return string
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * TODO review these simplified function
     * @return string
     */
    function display_linked(): string
    {
        return $this->name();
    }

    /**
     * create the html code to view a sandbox object
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param string $back the history of the user actions to allow rollbacks
     * @return string the html code for a view: this is the main function of this lib
     * TODO use backtrace or use a global backtrace var
     */
    function show(db_object_dsp $dbo, string $back): string
    {
        log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());
        $result = '';

        // check and correct the parameters
        if ($back == '') {
            $back = $dbo->id();
        }

        if ($this->id() <= 0) {
            log_err("The view id must be loaded to display it.", "view->display");
        } else {
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($back);
            $result .= $this->dsp_entries($dbo, $back);
            $result .= $this->dsp_type_close();
        }
        log_debug('done');

        return $result;
    }

    /**
     * create the html code for all components of this view
     *
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @return string the html code of all view components
     */
    private function dsp_entries(db_object_dsp $dbo, string $back): string
    {
        log_debug($this->dsp_id());
        $result = '';
        if (!$this->cmp_lst->is_empty()) {
            foreach ($this->cmp_lst->lst() as $cmp) {
                $result .= $cmp->dsp_entries($dbo, $back);
            }
        }

        return $result;
    }

    /**
     * return the beginning html code for the view_type;
     * the view type defines something like the basic setup of a view
     * e.g. the catch view does not have the header, whereas all other views have
     */
    private function dsp_type_open(): string
    {
        $result = '';
        // move to database !!
        // but avoid security leaks
        // maybe use a view component for that
        if ($this->type_id() == 1) {
            $result .= '<h1>';
        }
        return $result;
    }

    /**
     * show the navigation bar, which allow the user to search, to login or change the settings
     * without javascript this is the top right corner
     * with    javascript this is a bar on the top
     */
    private function dsp_navbar(string $back = ''): string
    {
        log_debug();
        $result = '';

        // check the all minimal input parameters are set
        if ($this->id <= 0) {
            log_err("The display ID (" . $this->id . ") must be set to display a view.", "view_dsp->dsp_navbar");
        } else {
            if (UI_USE_BOOTSTRAP) {
                $result = $this->dsp_navbar_bs(TRUE, $back);
            } else {
                $result = $this->dsp_navbar_html($back);
            }
        }

        log_debug('done');
        return $result;
    }

    /*
     * JavaScript functions using bootstrap
     */

    // same as dsp_navbar_html, but using bootstrap
    private function dsp_navbar_bs(bool $show_view, string $back): string
    {
        $lib = new library();
        $html = new html_base();
        $result = '<nav class="navbar bg-light fixed-top">';
        $result .= $html->logo();
        $result .= '  <form action="/http/find.php" class="form-inline my-2 my-lg-0">';
        $result .= '<label for="pattern"></label>';
        $result .= $this->input_search_pattern();
        $result .= '    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Get numbers</button>';
        $result .= '  </form>';
        $result .= '  <div class="col-sm-2">';
        $result .= '    <ul class="nav navbar-nav">';
        $result .= '      <li class="active">';
        $result .= $this->dsp_user($back);
        $result .= '      </li>';
        $result .= '      <li class="active">';
        $result .= $this->dsp_logout();
        $result .= '      </li>';
        if ($show_view) {
            $result .= '      <li class="active">';
            $result .= $this->dsp_view_name($back);
            $class = $lib->class_to_name(view::class);
            //$url_edit = $html->url($class . api::UPDATE, $this->id, $back, '', word::class . '=' . $back);
            $url_edit = $html->url($class . api::UPDATE, $this->id, '', '');
            $result .= (new button('adjust the view ' . $url_edit))->edit();
            //$url_add = $html->url($class . api::CREATE, 0, $back, '', word::class . '=' . $back);
            $url_add = $html->url($class . api::CREATE, 0, '', '');
            $result .= (new button('create a new view', $url_add))->add();
            $result .= '      </li>';
        }
        $result .= '    </ul>';
        $result .= '  </div>';
        /*
        $result .= '  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
        $result .= '    <span class="navbar-toggler-icon"></span>';
        $result .= '  </button>';
        $result .= '  <div class="collapse navbar-collapse" id="navbarSupportedContent">';
        $result .= '    <ul class="navbar-nav mr-auto">';
        // $result .= '      <li><a href="/http/find.php?word='.$back).'"><span class="glyphicon glyphicon-search"></span></a></li>';
        $result .= '      <li class="nav-item dropdown">';
        $result .= '        <a class="nav-link dropdown-toggle" ';
        $result .= '          href="/http/view_select.php?id='.$this->id.'&word='.$back.'&back='.$back.'"';
        $result .= '          id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $result .= '          '.$this->name.'';
        $result .= '        </a>';
        $result .= '        <div class="dropdown-menu" aria-labelledby="navbarDropdown">';
        $result .= '          <a class="dropdown-item" href="/http/view_edit.php?id='.$this->id.'&word='.$back.'&back='.$back.'">Edit</a>';
        $result .= '          <a class="dropdown-item" href="#">New</a>';
        $result .= '        </div>';
        $result .= '      </li>';
        $result .= '    </ul>';
        $result .= '  </div>';
        */
        $result .= '</nav>';
        // to avoid that the first data line is below the navbar
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        return $result;
    }

    private function input_search_pattern(): string
    {
        $html = new html_base();
        return $html->input(
            'pattern', '',
            html_base::INPUT_SEARCH,
            html_base::BS_SM_2,
            'word or formula');
    }

    /**
     * show the standard top right corner, where the user can log in or change the settings
     * @param string $back the id of the word from which the page has been called (TODO to be replace with the back trace object)
     * @returns string the HTML code to display the navigation bar on top of the page
     */
    private function dsp_navbar_html(string $back = ''): string
    {
        $html = new html_base();

        $result = $this->html_navbar_start();
        $result .= '<td class="right_ref">';
        if ($this->is_system() and !$this->user()->is_admin()) {
            $result .= (new button('find a word or formula', $html->url(api::SEARCH)))->find() . ' - ';
            $result .= $this->name . ' ';
        } else {
            $result .= (new button('find a word or formula', '/http/find.php?word=' . $back))->find() . ' - ';
            $result .= $this->dsp_view_name($back);
            $result .= (new button('adjust the view ' . $this->name, '/http/view_edit.php?id=' . $this->id . '&word=' . $back . '&back=' . $back))->edit() . ' ';
            $result .= (new button('create a new view', '/http/view_add.php?word=' . $back . '&back=' . $back))->add();
        }
        $result .= ' - ';
        log_debug($this->dsp_id() . ' (' . $this->id . ')');
        $result .= $this->dsp_user($back);
        $result .= ' ';
        $result .= $this->dsp_logout();
        $result .= '</td>';
        $result .= $this->html_navbar_end();

        return $result;
    }

    private function html_navbar_start(): string
    {
        $html = new html_base();
        $result = $html->dsp_tbl_start();
        $result .= '<tr><td>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= $html->logo();
        $result .= '    </td>';
        return $result;
    }

    /**
     * the zukunft logo that should be show always
     */
    private function html_navbar_end(): string
    {
        $html = new html_base();
        $result = '  </tr>';
        $result .= $html->dsp_tbl_end();
        return $result;
    }

    private function dsp_type_close(): string
    {
        $result = '';
        // move to a view component function
        // for the word array build an object
        if ($this->type_id() == 1) {
            $result = $result . '<br><br>';
            //$result = $result . '<a href="/http/view.php?words='.implode (",", $word_array).'&type=3">Really?</a>';
            $result = $result . '</h1>';
        }
        return $result;
    }

    private function dsp_user(): string
    {
        return '';
    }

    /**
     * TODO fill
     * @return string
     */
    private function dsp_logout(): string
    {
        return '';
    }

    /**
     * TODO fill
     * @return string
     */
    private function dsp_view_name($back): string
    {
        return '';
    }

    /**
     * TODO fill
     * @return string
     */
    private function is_system(): bool
    {
        return false;
    }

    /**
     * TODO fill
     * @return string
     */
    private function user(): string
    {
        return '';
    }

    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[controller::API_FLD_CODE_ID] = $this->code_id;
        $vars[controller::API_FLD_COMPONENTS] = $this->cmp_lst->api_array();
        return array_filter($vars, fn($value) => !is_null($value));
    }

}
