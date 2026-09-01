<?php

/*

    web/ref/source.php - the extension of the source API objects to create source base html code
    ------------------

    $src is the suggested var name

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - base:              html code for the single object vars
    - select:            html code to select parameter like the type


    This file is part of the frontend of zukunft.com - calc with words

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\web\ref;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST_FIELDS . 'fields.php';
include_once html_paths::SHARED_CONST_FIELDS . 'source_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_TYPES . 'view_types.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::DB . 'sql_db.php';
include_once html_paths::MODEL_REF . 'source_db.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;

class source extends sandbox_code_id
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::SOURCE_ADD;
    const string VIEW_EDIT = views::SOURCE_EDIT;
    const string VIEW_DEL = views::SOURCE_DEL;
    const int VIEW_ADD_ID = views::SOURCE_ADD_ID;
    const int VIEW_EDIT_ID = views::SOURCE_EDIT_ID;
    const int VIEW_DEL_ID = views::SOURCE_DEL_ID;

    // crud message id
    const msg_id MSG_ADD = msg_id::SOURCE_ADD;
    const msg_id MSG_EDIT = msg_id::SOURCE_EDIT;
    const msg_id MSG_DEL = msg_id::SOURCE_DEL;


    /*
     * object vars
     */

    private ?string $url = null;
    private ?string $doi = null;
    // the values that name this source, filled only if the source has been loaded for its page
    // (see load_by_id_with_related), otherwise null
    public ?value_list $val_lst = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this source frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
        if ($msg->is_ok()) {
            if (array_key_exists(url_var::URL, $url_array)) {
                $this->url = $url_array[url_var::URL];
            } else {
                $this->url = null;
            }
            if (array_key_exists(url_var::DOI, $url_array)) {
                $this->doi = $url_array[url_var::DOI];
            } else {
                $this->doi = null;
            }
        }
        return $msg;
    }


    /*
     * set and get
     */

    /**
     * as a function to overwrite the parent function
     * @return string|null
     */
    function url(): ?string
    {
        return $this->url;
    }

    /**
     * as a function to overwrite the parent function
     * @return string|null the digital object identifier e.g. 10.5281/zenodo.19443909
     */
    function doi(): ?string
    {
        return $this->doi;
    }

    /**
     * @return string|null the doi.org url of the doi or null if this source has no doi
     */
    function doi_url(): ?string
    {
        if ($this->doi == null or $this->doi == '') {
            $result = null;
        } else {
            $result = def::LINK_DOI . $this->doi;
        }
        return $result;
    }

    /**
     * @return array the ordered db field names of a source used for the change preview order
     */
    function sandbox_fld_order(): array
    {
        return source_fields::ALL_NAMES;
    }

    /**
     * @return array the user-editable source db field names mapped to their url var key
     */
    function db_fld_to_url(): array
    {
        return [
            source_fields::FLD_NAME => url_var::NAME,
            fields::FLD_DESCRIPTION => url_var::DESCRIPTION,
            fields::FLD_URL => url_var::URL,
            fields::FLD_DOI => url_var::DOI,
            fields::FLD_CODE_ID => url_var::CODE_ID,
        ];
    }


    /*
     * api
     */

    /**
     * load the source by id AND ask the backend to include the views that can show this source,
     * the change log and the user overwrites, which the tabs of the source page show
     *
     * the api handler sets api_types::INCL_RELATED and source::api_json_array() emits the views,
     * changes and overwrites that the frontend api_mapper picks up into view_lst, chg_log,
     * user_overwrites and other_overwrites
     *
     * @param int|string $id the source id to load
     * @param int $usr_id the id of the session user to load the source for, 0 for the default
     * @return bool true on a successful load (mirrors load_by_id)
     */
    function load_by_id_with_related(int|string $id, user_message $msg, int $usr_id = 0): bool
    {
        return $this->load_by_id($id, $msg, [url_var::INCL_RELATED => url_var::TRUE], $usr_id);
    }

    /**
     * set the vars of this source frontend object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->url = $json_array[json_fields::URL];
        } else {
            $this->url = null;
        }
        if (array_key_exists(json_fields::DOI, $json_array)) {
            $this->doi = $json_array[json_fields::DOI];
        } else {
            $this->doi = null;
        }
        // only the source page asks for the values, so a missing list is not an empty list
        if (is_array($json_array[json_fields::VALUES] ?? null)) {
            $val_lst = new value_list();
            $val_lst->api_mapper($json_array[json_fields::VALUES]);
            $this->val_lst = $val_lst;
        } else {
            $this->val_lst = null;
        }
        return $msg->is_ok();
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);
        $vars[json_fields::URL] = $this->url;
        $vars[json_fields::DOI] = $this->doi;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * base
     */

    /**
     * display the source name with the tooltip
     * @returns string the html code
     */
    function name_tip(): string
    {
        // escape the user-settable name: the name_tip base contract returns html-safe output, so a
        // generic name_tip caller that renders it into a list would otherwise be stored xss
        return htmlspecialchars($this->name(), ENT_QUOTES);
    }

    /**
     * display the source name with a link to the main page for the source
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(
        array  $url_arr = [],
        string $style = '',
        int $msk_id = views::SOURCE_ID,
        string $base_url = ''
    ): string
    {
        return parent::name_link($url_arr, $style, $msk_id, $base_url);
    }


    /*
     * select
     */

    /**
     * @param string $form
     * @param string $pattern
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @return string
     */
    function source_selector(string $form, string $pattern, ?source_list $src_lst): string
    {
        // TODO review and maybe use test_mode parameter
        if ($pattern != '') {
            $src_lst->load_like($pattern);
        }
        return $src_lst->selector($form, $this->id(), url_var::SOURCE, msg_id::FORM_SELECT_SOURCE);
    }

    /**
     * called from \web\component\execute\system_form to select the source type
     * @param string $form name of the html form where the type selector should be added
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type within a form
     */
    function source_type_selector(string $form, ?type_lists $typ_lst, user_message $msg): string
    {
        global $ui_sys;
        // fall back to the frontend request cache if the caller has no type list
        if ($typ_lst == null) {
            log_err('type list cache missing, falling back to the request cache');
            $typ_lst = $ui_sys->typ_lst_cache;
        }
        $used_source_type_id = $this->type_id($msg);
        if ($used_source_type_id == null) {
            $used_source_type_id = $typ_lst->src_typ->default_id();
        }
        return $typ_lst->src_typ->selector($form, $used_source_type_id);
    }

    /**
     * create the HTML code to select a view usable for a source
     * @param string $form the name of the html form
     * @param view_list $msk_lst with all suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string       $form,
        view_list    $msk_lst,
        user_message $msg,
        string       $name = url_var::VIEW,
        msg_id       $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        $msk_lst = $msk_lst->only_type(view_types::SOURCE, $msg);
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

}
