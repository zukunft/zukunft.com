<?php

/*

    web/sandbox/sandbox.php - extends the frontend db object superclass for user sandbox functions such as share type
    -----------------------


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

namespace html\sandbox;

include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once API_SANDBOX_PATH . 'sandbox_named.php';
include_once HTML_PATH . 'html_base.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_USER_PATH . 'user.php';
include_once WEB_USER_PATH . 'user_message.php';
//include_once WEB_VIEW_PATH . 'view_list.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';

use html\view\view_list;
use shared\api;
use html\sandbox\db_object as db_object_dsp;
use html\user\user as user_dsp;
use html\user\user_message;
use shared\json_fields;
use shared\types\view_styles;

class sandbox extends db_object_dsp
{

    // for preloaded types just include the id on the sandbox object
    public ?int $share_id = null;      // id for public, personal, group or private
    public ?int $protection_id = null; // id for no, user, admin or full protection

    protected ?user_dsp $owner = null;

    // the id of the default view for this object
    private ?int $view_id = null;


    /*
     * set and get
     */

    /**
     * set the vars of this sandbox object bases on the api json array
     * do not set the default share and protection type to be able to identify forced updates to the default type
     *
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);

        if (array_key_exists(json_fields::SHARE, $json_array)) {
            $this->share_id = $json_array[json_fields::SHARE];
        } else {
            $this->share_id = null;
        }
        if (array_key_exists(json_fields::PROTECTION, $json_array)) {
            $this->protection_id = $json_array[json_fields::PROTECTION];
        } else {
            $this->protection_id = null;
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_url_array(array $url_array): user_message
    {
        $usr_msg = parent::set_from_json_array($url_array);
        if (array_key_exists(api::URL_VAR_SHARE, $url_array)) {
            $this->share_id = $url_array[api::URL_VAR_SHARE];
        } else {
            $this->share_id = null;
        }
        if (array_key_exists(api::URL_VAR_PROTECTION, $url_array)) {
            $this->protection_id = $url_array[api::URL_VAR_PROTECTION];
        } else {
            $this->protection_id = null;
        }
        return $usr_msg;
    }

    function view_id(): ?int
    {
        return $this->view_id;
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

        if ($this->share_id != null) {
            $vars[json_fields::SHARE] = $this->share_id;
        }
        if ($this->protection_id != null) {
            $vars[json_fields::PROTECTION] = $this->protection_id;
        }
        return $vars;
    }


    /*
     * selectors
     */

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @return string the html code to select a view
     */
    public function view_selector(string $form, view_list $msk_lst): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        return $msk_lst->selector($form, $view_id);
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the share type
     */
    protected function share_type_selector(string $form_name): string
    {
        global $usr;
        global $html_share_types;
        $used_share_id = $this->share_id;
        if ($used_share_id == null) {
            $used_share_id = $html_share_types->default_id();
        }
        if ($usr === $this->owner or $this->owner == null) {
            return $html_share_types->selector($form_name, $used_share_id, 'share', view_styles::COL_SM_4, 'share:');
        } else {
            return '';
        }
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the share type
     */
    protected function protection_type_selector(string $form_name): string
    {
        global $usr;
        global $html_protection_types;
        $used_protection_id = $this->protection_id;
        if ($used_protection_id == null) {
            $used_protection_id = $html_protection_types->default_id();
        }
        if ($usr === $this->owner or $this->owner == null) {
            return $html_protection_types->selector($form_name, $used_protection_id, 'protection', view_styles::COL_SM_4, 'protection:');
        } else {
            return '';
        }
    }

}


