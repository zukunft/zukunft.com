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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_lists.php';
//include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_dsp;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class sandbox extends db_object_dsp
{

    // the share_id is used to define the access rights
    // which can be public, personal, group, private or log
    // for preloaded types just include the id on the sandbox object
    public ?int $share_id = null;

    // the protection_id is used to define the change rights
    //  which can no, user, admin or full protection
    public ?int $protection_id = null;

    // to reactivate an excluded sandbox object also excluded objects are send to the frontend
    public ?bool $excluded = null;

    // the user that has created the standard object
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
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);

        // TODO Prio 0 add dto cache object to api mapper
        //if ($this->has_id()) {
        //    $cac_obj = $dto->get_object_by_id($this);
        //    $this->fill($cac_obj, $this->user());
        //}

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
        if (array_key_exists(json_fields::EXCLUDED, $json_array)) {
            $this->excluded = $json_array[json_fields::EXCLUDED];
        } else {
            $this->excluded = null;
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if (array_key_exists(url_var::SHARE, $url_array)) {
            $this->share_id = $url_array[url_var::SHARE];
        } else {
            $this->share_id = null;
        }
        if (array_key_exists(url_var::PROTECTION, $url_array)) {
            $this->protection_id = $url_array[url_var::PROTECTION];
        } else {
            $this->protection_id = null;
        }
        if (array_key_exists(url_var::EXCLUDED, $url_array)) {
            $this->excluded = $url_array[url_var::EXCLUDED];
        } else {
            $this->excluded = null;
        }
        return $usr_msg;
    }

    function view_id(): ?int
    {
        return $this->view_id;
    }


    /*
     * api
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
        if ($this->excluded != null) {
            $vars[json_fields::EXCLUDED] = $this->excluded;
        }
        return $vars;
    }


    /*
     * set and get
     */

    function share_id(): ?int
    {
        return $this->share_id;
    }

    function protection_id(): ?int
    {
        return $this->protection_id;
    }

    function is_excluded(): bool
    {
        if ($this->excluded != null) {
            return $this->excluded;
        } else {
            return false;
        }
    }


    /*
     * selectors
     */

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string    $form,
        view_list $msk_lst,
        string    $name = url_var::VIEW,
        msg_id    $msg_id = msg_id::FORM_FIELD_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

    /**
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function share_type_selector(string $form, ?type_lists $typ_lst): string
    {
        global $usr;
        $used_share_id = $this->share_id;
        if ($used_share_id == null) {
            $used_share_id = $typ_lst->html_share_types->default_id();
        }
        if ($usr === $this->owner or $this->owner == null) {
            return $typ_lst->html_share_types->selector($form, $used_share_id);
        } else {
            return '';
        }
    }

    /**
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function protection_type_selector(string $form, ?type_lists $typ_lst): string
    {
        global $usr;
        $used_protection_id = $this->protection_id;
        if ($used_protection_id == null) {
            $used_protection_id = $typ_lst->html_protection_types->default_id();
        }
        if ($usr === $this->owner or $this->owner == null) {
            return $typ_lst->html_protection_types->selector($form, $used_protection_id);
        } else {
            return '';
        }
    }

}


