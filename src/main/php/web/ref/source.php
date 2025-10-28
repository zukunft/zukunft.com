<?php

/*

    web/ref/source.php - the extension of the source API objects to create source base html code
    ------------------

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class source extends sandbox_code_id
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::SOURCE_ADD;
    const string VIEW_EDIT = views::SOURCE_EDIT;
    const string VIEW_DEL = views::SOURCE_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::SOURCE_ADD;
    const msg_id MSG_EDIT = msg_id::SOURCE_EDIT;
    const msg_id MSG_DEL = msg_id::SOURCE_DEL;


    /*
     * object vars
     */

    private ?string $url = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this source frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::URL, $url_array)) {
                $this->url = $url_array[url_var::URL];
            } else {
                $this->url = null;
            }
        }
        return $usr_msg;
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


    /*
     * api
     */

    /**
     * set the vars of this source frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->url = $json_array[json_fields::URL];
        } else {
            $this->url = null;
        }
        return $usr_msg;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::URL] = $this->url;
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
        return $this->name();
    }

    /**
     * display the source name with a link to the main page for the source
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::SOURCE_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * select
     */

    /**
     * @param string $form
     * @param string $pattern
     * @return string
     */
    function source_selector(string $form, string $pattern): string
    {
        $src_lst = new source_list();
        $src_lst->load_like($pattern);
        return $src_lst->selector($form, $this->id(), url_var::SOURCE_LONG,  msg_id::LABEL_STYLE);
    }

    /**
     * called from \web\component\execute\system_form to select the source type
     * @param string $form name of the html form where the type selector should be added
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type within a form
     */
    function source_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_source_type_id = $this->type_id();
        if ($used_source_type_id == null) {
            $used_source_type_id = $typ_lst->html_source_types->default_id();
        }
        return $typ_lst->html_source_types->selector($form, $used_source_type_id);
    }

}
