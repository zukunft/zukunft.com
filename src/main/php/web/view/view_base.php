<?php

/*

    web/view/view_base.php - the main frontend view object for the link to the backend
    ----------------------

    to create the HTML code to display a view

    The main sections of this object are
    - object vars:       the variables of this view base object used to use (view_exe.php) or change a view (view.php)
    - construct and map: the mapping of a url to this object or an api json message
    - api:               set the object vars based on the api json message and create a json for the backend
    - set and get:       to capsule the vars from unexpected changes
    - load:              get an api json from the backend and
    - base:              html code for the single object vars
    - select:            html code to select parameter like the type


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

namespace Zukunft\ZukunftCom\main\php\web\view;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class view_base extends sandbox_code_id
{

    /*
     * const
     */

    // curl views
    const string VIEW_ADD = views::VIEW_ADD;
    const string VIEW_EDIT = views::VIEW_EDIT;
    const string VIEW_DEL = views::VIEW_DEL;

    // curl message id
    const msg_id MSG_ADD = msg_id::VIEW_ADD;
    const msg_id MSG_EDIT = msg_id::VIEW_EDIT;
    const msg_id MSG_DEL = msg_id::VIEW_DEL;


    /*
     * object vars
     */

    // code_id is used for system views
    protected component_list $cmp_lst;

    // objects that should be displayed (only one is supposed to be not null)
    // the word, triple or formula object that should be shown to the user
    protected ?db_object $dbo;

    // the style e.g. to define the width
    protected ?int $style_id = null;


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->code_id = null;
        $this->cmp_lst = new component_list();
        $this->dbo = null;
        $this->style_id = null;
        parent::__construct($api_json);
    }

    /**
     * set the vars of this view bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if (array_key_exists(url_var::STYLE, $url_array)) {
            $this->set_style_id($url_array[url_var::STYLE]);
        }
        return $usr_msg;
    }

    /**
     * set the vars this view bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        // the root view object
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::STYLE, $json_array)) {
            $this->set_style_id($json_array[json_fields::STYLE]);
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
                $this->dbo->api_mapper($dbo_json, $msg);
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
                $this->dbo->api_mapper($dbo_json, $msg);
            }
        }
        $this->cmp_lst = $cmp_lst;
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(api_type_list|array $typ_lst = []): array
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        $vars = parent::api_array();
        $vars[json_fields::STYLE] = $this->get_style_id();
        $vars[json_fields::COMPONENTS] = $this->cmp_lst->api_array($typ_lst);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    function get_component_list(): component_list
    {
        return $this->cmp_lst;
    }

    function set_style_id(?int $style_id = null): void
    {
        $this->style_id = $style_id;
    }

    function get_style_id(): ?int
    {
        return $this->style_id;
    }

    function type_code_id(): ?string
    {
        global $ui_cac;
        $msk_typ_lst = $ui_cac->typ_lst_cache->html_view_types;
        $id = $this->type_id();
        if ($id != null) {
            return $msk_typ_lst->get($this->type_id())?->get_code_id();
        } else {
            return '';
        }
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
        $data[url_var::LEVELS] = 1;
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

    function title(db_object|type_object $dbo): string
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

    public function style_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_style_id = $this->get_style_id();
        if ($used_style_id == null) {
            $used_style_id = $typ_lst->html_view_styles->default_id();
        }
        return $typ_lst->html_view_styles->selector($form, $used_style_id);
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

    function dsp_navbar(?data_object $cfg = null, string $back = ''): string
    {
        $msg = 'ERROR: dsp_navbar is expected to be overwritten by the child object ' . $this::class;
        log_err($msg);
        return $msg;
    }

}
