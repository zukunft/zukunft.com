<?php

/*

    web/view/view_navbar.php - add the navigation bar to the view object to finish the frontend view object
    ------------------------

    add the function to create a navigation bar to the html frontend view object

    The main sections of this object are
    - object vars:       the variables of this word object


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

include_once html_paths::VIEW . 'view_exe.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'display_list.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_type.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'library.php';

use html\button;
use html\display_list;
use html\html_base;
use html\log\user_log_display;
use html\styles;
use html\system\back_trace;
use html\types\type_lists;
use html\word\word;
use shared\api;
use shared\const\rest_ctrl;
use shared\const\views;
use shared\library;
use shared\types\view_styles;
use shared\types\view_type;
use shared\enum\messages as msg_id;
use shared\helper\Config as shared_config;

class view extends view_exe
{

    /*
     * const
     */

    // curl views
    const VIEW_ADD = views::VIEW_ADD;
    const VIEW_EDIT = views::VIEW_EDIT;
    const VIEW_DEL = views::VIEW_DEL;

    // curl message id
    const MSG_ADD = msg_id::VIEW_ADD;
    const MSG_EDIT = msg_id::VIEW_EDIT;
    const MSG_DEL = msg_id::VIEW_DEL;


    /**
     * show the navigation bar, which allow the user to search, to login or change the settings
     * without javascript this is the top right corner
     * with    javascript this is a bar on the top
     */
    function dsp_navbar(string $back = ''): string
    {
        $result = '';

        // check the all minimal input parameters are set
        if ($this->id() <= 0) {
            $this->log_err("The display ID (" . $this->id() . ") must be set to display a view.", "view_dsp->dsp_navbar");
        } else {
            if (html_base::UI_USE_BOOTSTRAP) {
                $result = $this->dsp_navbar_bs(TRUE, $back);
            } else {
                $result = $this->dsp_navbar_html($back);
            }
        }

        return $result;
    }

    /**
     * same as dsp_navbar_html, but using bootstrap
     * JavaScript functions using bootstrap
     */
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
        $result .= '  <div class="' . view_styles::COL_SM_2 . '">';
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
            //$url_edit = $html->url($class . api_dsp::UPDATE, $this->id(), $back, '', word::class . '=' . $back);
            $url_edit = $html->url($class . rest_ctrl::UPDATE, $this->id(), '', '');
            // TODO fix for frontend based version
            //echo 'button init';
            $result .= $this->btn_edit();
            //echo 'button_dsp init' . $url_edit;
            //$btn = new button_dsp($url_edit, '');
            // TODO fix for frontend based version
            //$result .= $btn->edit(messages::VIEW_EDIT);
            //$url_add = $html->url($class . api_dsp::CREATE, 0, $back, '', word::class . '=' . $back);
            $url_add = $html->url($class . rest_ctrl::CREATE, 0, '', '');
            // TODO fix for frontend based version
            //$result .= (new button_dsp($url_add))->add(messages::VIEW_ADD);
            $result .= $this->btn_add();
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

    /**
     * same as dsp_navbar, but without the view change used for the view editors
     */
    function dsp_navbar_no_view(string $back = ''): string
    {
        $result = '';

        // check the all minimal input parameters are set
        if ($this->user() == null) {
            $this->log_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
        } else {
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= $this->dsp_navbar_bs(FALSE, $back);
            } else {
                $result .= $this->dsp_navbar_html_no_view($back);
            }
        }
        return $result;
    }

    /**
     * the basic zukunft top elements that should be show always
     */
    function dsp_navbar_simple(): string
    {
        if (html_base::UI_USE_BOOTSTRAP) {
            $result = $this->dsp_navbar_bs(FALSE, 0);
        } else {
            $result = $this->html_navbar_start();
            $result .= $this->html_navbar_end();
        }
        return $result;
    }


    /*
     * internal execute
     */

    private function input_search_pattern(): string
    {
        $html = new html_base();
        return $html->input(
            'pattern', '',
            html_base::INPUT_SEARCH,
            view_styles::BS_SM_2,
            'word or formula');
    }

    /**
     * show the standard top right corner, where the user can log in or change the settings
     * @param string $back the id of the word from which the page has been called (TODO to be replace with the back trace object)
     * @returns string the HTML code to display the navigation bar on top of the page
     */
    private function dsp_navbar_html(string $back = ''): string
    {
        global $usr;
        $html = new html_base();

        $result = $this->html_navbar_start();
        $result .= '<td class="' . styles::STYLE_RIGHT . '">';
        if ($this->is_system() and !$usr->is_admin()) {
            $url = $html->url(rest_ctrl::SEARCH);
            $result .= (new button($url, $back))->find(msg_id::SEARCH_MAIN) . ' - ';
            $result .= $this->name . ' ';
        } else {
            $url = '/http/find.php?word=' . $back;
            $result .= (new button($url, $back))->find(msg_id::SEARCH_MAIN) . ' - ';
            $result .= $this->dsp_view_name($back);
            $url = $html->url(api::DSP_VIEW_EDIT, $this->id());
            $result .= (new button($url, $back))->edit(msg_id::VIEW_EDIT, $this->name) . ' ';
            $url = $html->url(api::DSP_VIEW_ADD);
            $result .= (new button($url, $back))->add(msg_id::VIEW_ADD);
        }
        $result .= ' - ';
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
     * the zukunft logo that should be always shown
     */
    private function html_navbar_end(): string
    {
        $html = new html_base();
        $result = '  </tr>';
        $result .= $html->dsp_tbl_end();
        return $result;
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
     * @return bool
     */
    private function is_system(): bool
    {
        return false;
    }

    /**
     * TODO fill
     * @return string
     */
    protected function user(): string
    {
        return '';
    }


    /*
     * to review / deprecate
     */

    /**
     * same as dsp_navbar, but without the view change used for the view editors
     */
    function dsp_navbar_html_no_view(string $back = ''): string
    {

        $result = $this->html_navbar_start();
        $result .= '<td class="' . styles::STYLE_RIGHT . '">';
        $result .= $this->dsp_user($back);
        $result .= $this->dsp_logout();
        $result .= '</td>';
        $result .= $this->html_navbar_end();

        return $result;
    }


    /*
     * to review
     */

    /**
     * HTML code to edit all word fields
     */
    function dsp_edit($add_cmp, $wrd, $back): string
    {
        global $usr;
        global $msk_typ_cac;

        $result = '';
        $html = new html_base();

        // use the default settings if needed
        if ($this->type_id() <= 0) {
            $this->set_type_id($msk_typ_cac->id(view_type::DEFAULT));
        }

        // the header to add or change a view
        if ($this->id() <= 0) {
            $script = "view_add";
            $result .= $html->dsp_text_h2('Create a new view (for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        } else {
            $script = "view_edit";
            $result .= $html->dsp_text_h2('Edit view "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        }
        $result .= '<div class="row">';

        // when changing a view show the fields only on the left side
        if ($this->id() > 0) {
            $result .= '<div class="' . view_styles::COL_SM_7 . '">';
        }

        // show the edit fields
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_form_id($this->id());
        $result .= $html->dsp_form_hidden("word", $wrd->id);
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        if ($add_cmp < 0 or $add_cmp > 0) {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", view_styles::COL_SM_8, "disabled");
            $result .= $this->dsp_type_selector($script, view_styles::COL_SM_4, "disabled");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:", "", "disabled");
        } else {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", view_styles::COL_SM_8);
            $result .= $this->dsp_type_selector($script, view_styles::COL_SM_4, "");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:");
            $result .= $html->dsp_form_end('', $back, "/http/view_del.php?id=" . $this->id() . "&back=" . $back);
        }

        // in edit mode show the assigned words and the hist on the right
        if ($this->id() > 0) {
            $result .= '</div>';

            $comp_html = $this->linked_components($add_cmp, $wrd, $script, $back);

            // collect the history
            $changes = $this->dsp_hist(0, shared_config::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, shared_config::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $link_html = $changes;
            } else {
                $link_html = 'No component have been added or removed yet.';
            }

            // display the tab box with the links and changes
            $result .= $html->dsp_link_hist_box('Components', $comp_html,
                '', '',
                'Changes', $hist_html,
                'Component changes', $link_html);
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

    /**
     * @param string $script the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code for the view type selector
     */
    private function dsp_type_selector(string $script, string $class, string $attribute, ?type_lists $typ_lst = null): string
    {
        //$sel->bs_class = $class;
        //$sel->attribute = $attribute;
        return $typ_lst->html_view_types->selector($script);
    }

    /**
     * lists of all view components which are used by this view
     */
    private function linked_components($add_cmp, $wrd, string $script, $back): string
    {
        $html = new html_base();

        $result = '';

        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_start_hist();
        }

        // show the view elements and allow the user to change them
        if (!$this->load_components()) {
            $this->log_err('Loading of view components for ' . $this->dsp_id() . ' failed');
        } else {
            $dsp_list = new display_list;
            $dsp_list->lst = $this->cmp_lst->lst();
            $dsp_list->script_name = "view_edit.php";
            $dsp_list->class_edit = view::class;
            $dsp_list->script_parameter = $this->id() . "&back=" . $back . "&word=" . $wrd->id();
            $result .= $dsp_list->display($back);
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // check if the add button has been pressed and ask the user what to add
            if ($add_cmp > 0) {
                $result .= 'View component to add: ';
                $url = $html->url(api::DSP_VIEW_ADD, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=-1&');
                $result .= (new button($url, $back))->add(msg_id::COMPONENT_ADD);
                $id_selected = 0; // no default view component to add defined yet, maybe use the last???
                $result .= $this->component_selector($script, '', $id_selected);

                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } elseif ($add_cmp < 0) {
                $result .= 'Name of the new display element: ';
                $result .= $html->input('entry_name', '', html_base::INPUT_TEXT);
                // TODO ??? should this not be the default entry type
                $result .= $this->component_selector($script, '', $this->type_id());
                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } else {
                $url = $html->url(api::DSP_COMPONENT_LINK, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=1');
                $result .= (new button($url, $back))->add(msg_id::COMPONENT_ADD);
            }
        }
        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= '</td></tr>';
        }

        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_end();
        }

        return $result;
    }

    /**
     * display the history of a view
     */
    function dsp_hist(
        int        $page,
        int        $size,
        string     $call,
        back_trace $back = null
    ): string
    {
        $log_dsp = new user_log_display();
        return $log_dsp->dsp_hist(view::class, $this->id(), $size, $page, '', $back);
    }

    /**
     * display the link history of a view
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        global $usr;
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($usr);
        $log_dsp->id = $this->id();
        $log_dsp->type = view::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        return $result;
    }

    private function load_components(): bool
    {
        return true;
    }

    /**
     * create a selection page where the user can select a view
     * that should be used for a term
     *
     */
    function selector_page($wrd_id, $back): string
    {
        global $usr;
        $result = '';

        $dsp_lst = new view_list();

        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp->id();;
            $view_name = $dsp->name();
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

        return $result;
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }

}
