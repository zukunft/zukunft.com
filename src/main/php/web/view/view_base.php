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

use cfg\const\paths;
use html\component\component_list;
use html\const\paths as html_paths;
use html\sandbox\db_object;
use html\sandbox\sandbox_code_id;
use html\types\type_lists;
use html\user\user_message;
use html\word\triple;
use html\word\word;
use shared\api;
use shared\const\views;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\url_var;

include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'display_list.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'triple.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

class view_base extends sandbox_code_id
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


    /*
     * object vars
     */

    // code_id is used for system views
    protected component_list $cmp_lst;

    // objects that should be displayed (only one is supposed to be not null)
    // the word, triple or formula object that should be shown to the user
    protected ?db_object $dbo;


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->set_code_id(null);
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
        $data[url_var::CHILDREN] = 1;
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
     * select
     */

    /**
     * create the HTML code to select a view type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_type_id = $this->type_id();
        if ($used_type_id == null) {
            $used_type_id = $typ_lst->html_view_types->default_id();
        }
        return $typ_lst->html_view_types->selector($form, $used_type_id);
    }

    public function view_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        return $this->type_selector($form_name, $typ_lst);
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
