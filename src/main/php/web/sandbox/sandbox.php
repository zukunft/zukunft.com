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
//include_once html_paths::USER . 'user.php';
//include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\component_list;

class sandbox extends db_object
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
    protected ?user $owner = null;

    // the id of the default view for this object
    public ?int $view_id = null;


    /*
     * set and get
     */

    /**
     * set the vars of this sandbox object bases on the api json array
     * do not set the default share and protection type to be able to identify forced updates to the default type
     *
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);

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
        return $msg->is_ok();
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
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

    /**
     * @return array parent url array extended with the share and protection of this sandbox object
     */
    function to_url_array(): array
    {
        $url_array = parent::to_url_array();
        $url_array[url_var::SHARE] = $this->share_id;
        $url_array[url_var::PROTECTION] = $this->protection_id;
        return $url_array;
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
     * load
     */

    /**
     * add the user to the load of the user sandbox object e.g. word by id via api
     * TODO Prio 1 add user_message as parameter
     * @param int|string $id the database id of the object that should be loaded
     * @param array $data additional data that should be included in the get request
     * @param int $usr_id the id of the session user to load the object for, 0 for the default
     * @return bool
     */
    function load_by_id(int|string $id, array $data = [], int $usr_id = 0): bool
    {
        if ($usr_id > 0) {
            $data[url_var::USER] = $usr_id;
        }
        return parent::load_by_id($id, $data, $usr_id);
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
        msg_id    $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        $msk_lst = $msk_lst->ex_system();
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

    /**
     * TODO Prio 0 make sure that all selectors create a hidden form field with the original values
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function share_type_selector(string $form, ?type_lists $typ_lst): string
    {
        global $ui_sys;
        $used_share_id = $this->share_id;
        if ($used_share_id == null) {
            $used_share_id = $typ_lst->shr_typ->default_id();
        }
        if ($ui_sys->usr === $this->owner or $this->owner == null) {
            // also send the opening share id as the '8'-prefixed pre value so the confirm view can show
            // the existing share and detect whether the user actually changed it (see url_var::PRE)
            $html = new html_base();
            return $typ_lst->shr_typ->selector($form, $used_share_id)
                . $html->form_hidden(url_var::PRE . url_var::SHARE, (string)$used_share_id);
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
        global $ui_sys;
        $used_protection_id = $this->protection_id;
        if ($used_protection_id == null) {
            $used_protection_id = $typ_lst->ptc_typ->default_id();
        }
        if ($ui_sys->usr === $this->owner or $this->owner == null) {
            // also send the opening protection id as the '8'-prefixed pre value so the confirm view can
            // show the existing protection and detect whether the user changed it (see url_var::PRE)
            $html = new html_base();
            return $typ_lst->ptc_typ->selector($form, $used_protection_id)
                . $html->form_hidden(url_var::PRE . url_var::PROTECTION, (string)$used_protection_id);
        } else {
            return '';
        }
    }

    /**
     * @param string $form the name of the html form
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list $cmp_lst with the suggested components
     * @return string the html code to select a component
     */
    public function component_selector(
        string         $form,
        string         $pattern,
        int            $id,
        component_list $cmp_lst
    ): string
    {
        if ($pattern != '') {
            $cmp_lst->load_like($pattern);
        }
        return $cmp_lst->selector($form, $id, url_var::COMPONENT, msg_id::FORM_SELECT_COMPONENT);
    }

    /**
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component link type
     */
    public function component_link_type_selector(string $form, ?type_lists $typ_lst): string
    {
        if ($typ_lst->cmp_lnk_typ != null) {
            return $typ_lst->cmp_lnk_typ->selector($form);
        } else {
            return 'no component types yet defined';
        }
    }

}


