<?php

/*

    web/view/view.php - the display extension of the api view object
    -----------------

    to create the HTML code to display a view

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - load:              get an api json from the backend and
    - base:              html code for the single object vars
    - buttons:           html code for the buttons e.g. to add, edit, del, link or unlink
    - select:            html code to select parameter like the type
    - execute:           create the html code for an object view


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
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_HTML_PATH . 'display_list.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_COMPONENT_PATH . 'component.php';
include_once WEB_COMPONENT_PATH . 'component_list.php';
include_once WEB_HELPER_PATH . 'config.php';
include_once WEB_HELPER_PATH . 'data_object.php';
include_once WEB_LOG_PATH . 'user_log_display.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VIEW_PATH . 'view_list.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once SHARED_CONST_PATH . 'components.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_TYPES_PATH . 'position_types.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_TYPES_PATH . 'view_type.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';


// TODO remove model classes
use html\button;
use html\component\component;
use html\component\component_list;
use html\display_list;
use html\helper\config;
use html\helper\data_object;
use html\html_base;
use html\log\user_log_display;
use html\rest_ctrl as api_dsp;
use html\sandbox\db_object;
use html\sandbox\sandbox_typed;
use html\system\back_trace;
use html\system\messages;
use html\user\user_message;
use html\word\triple;
use html\word\word;
use shared\api;
use shared\const\views;
use shared\json_fields;
use shared\library;
use shared\const\components;
use shared\types\position_types;
use shared\types\view_styles;
use shared\types\view_type;

class view extends sandbox_typed
{

    /*
     * object vars
     */

    // used for system views
    private ?string $code_id;
    protected component_list $cmp_lst;

    // objects that should be displayed (only one is supposed to be not null)
    // the word, triple or formula object that should be shown to the user
    protected ?db_object $dbo;


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->code_id = null;
        $this->cmp_lst = new component_list();
        $this->dbo = null;
        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    function component_list(): component_list
    {
        return $this->cmp_lst;
    }

    function code_id(): ?string
    {
        return $this->code_id;
    }


    /*
     * api
     */

    /**
     * set the vars this view bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        // the root view object
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        // set the components
        $cmp_lst = new component_list();
        if (array_key_exists(json_fields::COMPONENTS, $json_array)) {
            $cmp_lst->set_from_json_array($json_array[json_fields::COMPONENTS]);
        }
        // set the objects (e.g. word)
        if (array_key_exists(api::API_WORD, $json_array)) {
            $this->dbo = new word();
            $dbo_json = $json_array[api::API_WORD];
            $id = 0;
            if (array_key_exists(json_fields::ID, $json_array)) {
                $id = $dbo_json[json_fields::ID];
            }
            if ($id != 0) {
                $this->dbo->set_from_json_array($dbo_json);
            }
        }
        if (array_key_exists(api::API_TRIPLE, $json_array)) {
            $this->dbo = new triple();
            $dbo_json = $json_array[api::API_TRIPLE];
            $id = 0;
            if (array_key_exists(json_fields::ID, $json_array)) {
                $id = $dbo_json[json_fields::ID];
            }
            if ($id != 0) {
                $this->dbo->set_from_json_array($dbo_json);
            }
        }
        $this->cmp_lst = $cmp_lst;
        return $usr_msg;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::CODE_ID] = $this->code_id;
        $vars[json_fields::COMPONENTS] = $this->cmp_lst->api_array();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * load
     */

    /**
     * load the user sandbox object e.g. word by id via api
     * @param int $id
     * @return bool
     */
    function load_by_id_with(int $id): bool
    {
        $data = [];
        $data[api::URL_VAR_CHILDREN] = 1;
        return parent::load_by_id($id, $data);
    }


    /*
     * base
     */

    /**
     * create the html code to show the component name with the link to change the component parameters
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::VIEW_EDIT_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }

    function title(db_object $dbo): string
    {
        return $this->name() . ' ' . $dbo->name();
    }


    /*
     * buttons
     */

    /**
     * @return string the html code for a bottom
     * to create a new view for the current user
     */
    function btn_add(string $back = ''): string
    {
        return parent::btn_add_sbx(
            views::VIEW_ADD_ID,
            messages::VIEW_ADD,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to change a view e.g. the name or the type
     */
    function btn_edit(string $back = ''): string
    {
        return parent::btn_edit_sbx(
            views::VIEW_EDIT_ID,
            messages::VIEW_EDIT,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to exclude the view for the current user
     * or if no one uses the view delete the complete view
     */
    function btn_del(string $back = ''): string
    {
        return parent::btn_del_sbx(
            views::VERB_DEL_ID,
            messages::VALUE_DEL,
            $back);
    }


    /*
     * select
     */

    /**
     * create the HTML code to select a view type
     * @param string $form the name of the html form
     * @return string the html code to select the phrase type
     */
    function type_selector(string $form): string
    {
        global $html_view_types;
        $used_type_id = $this->type_id();
        if ($used_type_id == null) {
            $used_type_id = $html_view_types->default_id();
        }
        return $html_view_types->selector($form, $used_type_id);
    }

    /**
     * @param string $form_name
     * @param string $pattern
     * @param int $id
     * @return string
     */
    function component_selector(string $form_name, string $pattern, int $id): string
    {
        $cmp_lst = new component_list;
        $cmp_lst->load_like($pattern);
        return $cmp_lst->selector($form_name, $id, 'add_component', 'please define a component', '');
    }


    /*
     * execute
     */

    /**
     * create the html code to view a sandbox object
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $back the history of the user actions to allow rollbacks
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code for a view: this is the main function of this lib
     * TODO use backtrace or use a global backtrace var
     */
    function show(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $back = '',
        bool         $test_mode = false
    ): string
    {
        $result = '';

        $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());

        // check and correct the parameters
        if ($this->code_id() != '') {
            $form_name = $this->code_id();
        } else {
            $form_name = $this->name();
        }
        if ($back == '') {
            $back = $dbo->id();
        }

        if ($this->id() <= 0) {
            $this->log_err("The view id must be loaded to display it.", "view->display");
        } else {
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($back);
            $result .= $this->dsp_entries($dbo, $cfg, $form_name, $back, $test_mode);
            $result .= $this->dsp_type_close();
        }

        return $result;
    }

    /**
     * create the html code for all components of this view
     *
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $form_name the name of the view which is also used for the html form name
     * @param string $back the backtrace for undo actions
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    private function dsp_entries(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $form_name = '',
        string       $back = '',
        bool         $test_mode = false
    ): string
    {
        $this->log_debug($this->dsp_id());
        $result = '';
        if ($this->cmp_lst->is_empty()) {
            $this->log_debug('no components for ' . $this->dsp_id());
        } else {
            $row = '';
            $button_only = true;
            foreach ($this->cmp_lst->lst() as $cmp) {
                // add previous collected components to the final result
                if ($row != '') {
                    if ($cmp->pos_type_code_id() == position_types::BELOW) {
                        if ($button_only) {
                            $result .= $row;
                        } else {
                            // TODO easy move code to HTML class
                            $result .= '<div class="row ';
                            $result .= view_styles::COL_SM_12;
                            $result .= '">' . $row . ' </div>';
                        }
                        $row = '';
                        $button_only = true;
                    }
                }
                if (!$cmp->is_button_or_hidden()) {
                    $button_only = false;
                }
                $row .= $cmp->dsp_entries($dbo, $form_name, $this->id(), $cfg, $back, $test_mode);
            }
            if ($row != '') {
                $result .= $row;
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
            $url_edit = $html->url($class . api_dsp::UPDATE, $this->id(), '', '');
            // TODO fix for frontend based version
            //echo 'button init';
            $result .= $this->btn_edit();
            //echo 'button_dsp init' . $url_edit;
            //$btn = new button_dsp($url_edit, '');
            // TODO fix for frontend based version
            //$result .= $btn->edit(messages::VIEW_EDIT);
            //$url_add = $html->url($class . api_dsp::CREATE, 0, $back, '', word::class . '=' . $back);
            $url_add = $html->url($class . api_dsp::CREATE, 0, '', '');
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
        $result .= '<td class="right_ref">';
        if ($this->is_system() and !$usr->is_admin()) {
            $url = $html->url(api_dsp::SEARCH);
            $result .= (new button($url, $back))->find(messages::SEARCH_MAIN) . ' - ';
            $result .= $this->name . ' ';
        } else {
            $url = '/http/find.php?word=' . $back;
            $result .= (new button($url, $back))->find(messages::SEARCH_MAIN) . ' - ';
            $result .= $this->dsp_view_name($back);
            $url = $html->url(api::DSP_VIEW_EDIT, $this->id());
            $result .= (new button($url, $back))->edit(messages::VIEW_EDIT, $this->name) . ' ';
            $url = $html->url(api::DSP_VIEW_ADD);
            $result .= (new button($url, $back))->add(messages::VIEW_ADD);
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
    protected function user(): string
    {
        return '';
    }


    /*
     * interface
     */


    /*
     * to review / deprecate
     */

    function dsp_system_view(): string
    {
        $result = '';
        switch ($this->code_id) {
            case api::DSP_COMPONENT_ADD:
                $cmp = new component();
                $cmp->set_id(0);
                $result = $cmp->form_edit_new('', '', '', '', '');
                break;
            case api::DSP_COMPONENT_EDIT:
                $cmp = new component();
                $cmp->set_id(components::WORD_ID);
                $cmp->set_name(components::WORD_NAME);
                $result = $cmp->form_edit_new('', '', '', '', '');
                break;
            case api::DSP_COMPONENT_DEL:
                // TODO fill
                $result = 'del';
                break;
        }
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
     * same as dsp_navbar, but without the view change used for the view editors
     */
    function dsp_navbar_html_no_view(string $back = ''): string
    {

        $result = $this->html_navbar_start();
        $result .= '<td class="right_ref">';
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
            $this->log_debug('create a view');
            $script = "view_add";
            $result .= $html->dsp_text_h2('Create a new view (for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        } else {
            $this->log_debug($this->dsp_id() . ' for user ' . $usr->name() . ' (called from ' . $back . ')');
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
            $changes = $this->dsp_hist(0, config::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, config::ROW_LIMIT, '', $back);
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

            $this->log_debug('done');
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

    /**
     * @param string $script the name of the html form
     * @return string the html code for the view type selector
     */
    private function dsp_type_selector(string $script, string $class, string $attribute): string
    {
        global $html_view_types;
        //$sel->bs_class = $class;
        //$sel->attribute = $attribute;
        return $html_view_types->selector($script);
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
        $this->log_debug('load');
        if (!$this->load_components()) {
            $this->log_err('Loading of view components for ' . $this->dsp_id() . ' failed');
        } else {
            $this->log_debug('loaded');
            $dsp_list = new display_list;
            $dsp_list->lst = $this->cmp_lst->lst();
            $dsp_list->script_name = "view_edit.php";
            $dsp_list->class_edit = view::class;
            $dsp_list->script_parameter = $this->id() . "&back=" . $back . "&word=" . $wrd->id();
            $result .= $dsp_list->display($back);
            $this->log_debug('displayed');
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // check if the add button has been pressed and ask the user what to add
            if ($add_cmp > 0) {
                $result .= 'View component to add: ';
                $url = $html->url(api::DSP_VIEW_ADD, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=-1&');
                $result .= (new button($url, $back))->add(messages::COMPONENT_ADD);
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
                $result .= (new button($url, $back))->add(messages::COMPONENT_ADD);
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
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($usr);
        $log_dsp->id = $this->id();
        $log_dsp->type = view::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        $this->log_debug("done");
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
        $this->log_debug($this->id() . ',' . $wrd_id);

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

        $this->log_debug('done');
        return $result;
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }

    function log_debug(string $msg): void
    {
        echo '';
    }

}
