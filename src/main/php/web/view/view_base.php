<?php

/*

    web/view/view_base.php - the main frontend view object for the link to the backend
    ----------------------

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
include_once WEB_HTML_PATH . 'display_list.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_COMPONENT_PATH . 'component.php';
include_once WEB_COMPONENT_PATH . 'component_list.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VIEW_PATH . 'view_list.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once SHARED_CONST_PATH . 'components.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use html\component\component_list;
use html\sandbox\db_object;
use html\sandbox\sandbox_typed;
use html\system\messages;
use html\user\user_message;
use html\word\triple;
use html\word\word;
use shared\api;
use shared\const\views;
use shared\json_fields;

class view_base extends sandbox_typed
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
    function api_mapper(array $json_array): user_message
    {
        // the root view object
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        // set the components
        $cmp_lst = new component_list();
        if (array_key_exists(json_fields::COMPONENTS, $json_array)) {
            $cmp_lst->api_mapper($json_array[json_fields::COMPONENTS]);
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
                $this->dbo->api_mapper($dbo_json);
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
                $this->dbo->api_mapper($dbo_json);
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
     * load the view via api
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
            views::VIEW_ADD,
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
            views::VIEW_EDIT,
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
            views::VERB_DEL,
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

    public function view_type_selector(string $form_name): string
    {
        return $this->type_selector($form_name);
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

    function log_err(string $msg): void
    {
        echo $msg;
    }

    function log_debug(string $msg): void
    {
        echo '';
    }


    /*
     * overwrite
     */

    function dsp_navbar(string $back = ''): string
    {
        return 'Error: dsp_navbar is expected to be overwritten by the child object';
    }

}
