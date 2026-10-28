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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_lists.php';
//include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
//include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
//include_once html_paths::USER . 'user.php';
//include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::USER . 'user_message.php';
// view.php is not included here, because it includes word.php, which extends this class, so the
// include would read word.php before this class is defined; the type is only checked at a call
//include_once html_paths::VIEW . 'view.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Message.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class sandbox extends db_object
{

    // the number of views that the tooltip of "... more" of the edit menu names as examples
    const int MENU_MORE_NAMES = 3;
    // false for an object without an edit view of its own e.g. a group, which inherits the word
    // edit view of db_object, so that the edit menu and the views tab offer no switch icon that
    // would change the object as a word
    const bool HAS_EDIT_VIEW = true;

    // the share_id is used to define the access rights
    // which can be public, personal, group, private or log
    // for preloaded types just include the id on the sandbox object
    public ?int $share_id = null;

    // the protection_id is used to define the change rights
    //  which can no, user, admin or full protection
    public ?int $protection_id = null;

    // to reactivate an excluded sandbox object also excluded objects are send to the frontend
    public ?bool $excluded = null;

    // the fields that the requesting user has overwritten in the user sandbox (overlay) table
    // e.g. user_words, each with the db field name, the user value and the standard value;
    // rendered by the 'my' tab of the object page (see ui_preview::user_overwrites_table)
    public array $user_overwrites = [];

    // the shared overwrites of other users, each additionally with the name of the overwriting
    // user; rendered by the 'others' tab of the object page (ui_preview::other_overwrites_table)
    public array $other_overwrites = [];

    // the recent changes from the api message, rendered by the 'changes' tab
    public ?change_log_list $chg_log = null;

    // the views that can show this object from the api message, rendered by the 'views' tab
    public ?view_list $view_lst = null;
    // the views assigned to this object by the import e.g. "Mathematical constant" to "Pi",
    // which the edit menu of the navbar offers first
    public ?view_list $view_assigned_lst = null;

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
        if (array_key_exists(json_fields::OWNER, $json_array)) {
            $this->set_owner_name($json_array[json_fields::OWNER]);
        } else {
            $this->owner = null;
        }
        if (array_key_exists(json_fields::USER_OVERWRITES, $json_array)) {
            $this->user_overwrites = $json_array[json_fields::USER_OVERWRITES];
        } else {
            $this->user_overwrites = [];
        }
        if (array_key_exists(json_fields::OTHER_OVERWRITES, $json_array)) {
            $this->other_overwrites = $json_array[json_fields::OTHER_OVERWRITES];
        } else {
            $this->other_overwrites = [];
        }
        if (is_array($json_array[json_fields::CHANGES] ?? null)) {
            $this->chg_log = new change_log_list();
            $this->chg_log->api_mapper($json_array[json_fields::CHANGES]);
        } else {
            $this->chg_log = null;
        }
        if (is_array($json_array[json_fields::VIEWS] ?? null)) {
            $this->view_lst = new view_list();
            $this->view_lst->api_mapper($json_array[json_fields::VIEWS]);
        } else {
            $this->view_lst = null;
        }
        if (is_array($json_array[json_fields::VIEWS_ASSIGNED] ?? null)) {
            $this->view_assigned_lst = new view_list();
            $this->view_assigned_lst->api_mapper($json_array[json_fields::VIEWS_ASSIGNED]);
        } else {
            $this->view_assigned_lst = null;
        }
        return $msg->is_ok();
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
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
        if (array_key_exists(url_var::OWNER, $url_array)) {
            $this->set_owner_name($url_array[url_var::OWNER]);
        } else {
            $this->owner = null;
        }
        return $msg;
    }

    /**
     * @return array parent url array extended with the share and protection of this sandbox object
     */
    function to_url_array(user_message $msg): array
    {
        $url_array = parent::to_url_array($msg);
        $url_array[url_var::SHARE] = $this->share_id;
        $url_array[url_var::PROTECTION] = $this->protection_id;
        if ($this->owner_name() != '') {
            $url_array[url_var::OWNER] = $this->owner_name();
        }
        return $url_array;
    }

    /**
     * set the owner by the user name e.g. as sent by the api message or the page url
     * @param string|null $name the name of the user who owns the object
     * @return void
     */
    function set_owner_name(?string $name): void
    {
        if ($name == null or $name == '') {
            $this->owner = null;
        } else {
            $this->owner = new user();
            $this->owner->name = $name;
        }
    }

    /**
     * @return string the name of the user who owns the object or an empty string if not known
     */
    function owner_name(): string
    {
        return $this->owner?->name() ?? '';
    }

    function view_id(): ?int
    {
        return $this->view_id;
    }


    /*
     * menu
     */

    /**
     * the views that the edit menu of the navbar suggests for this object, in the order of the menu:
     * first the views directly linked to the object (its own default view and the views assigned to
     * it), second the related views e.g. of the parents of a word and third the default views of its
     * type e.g. the default, detail, range and detail with range view of a triple; a view is offered
     * only once in its first group, which is expected and no problem e.g. the own view is also related
     *
     * @param type_lists $typ_lst the frontend cache with the system views
     * @param user_message $msg to report a view that cannot be added
     * @return view_list the suggested views in the order of the menu
     */
    function menu_views_ui(type_lists $typ_lst, user_message $msg): view_list
    {
        $msk_lst = new view_list();
        $this->add_view_once_ui($msk_lst, $this->own_view_ui(), $msg);
        $this->add_views_by_name_ui($msk_lst, $this->view_assigned_lst, $msg);
        $this->add_views_by_name_ui($msk_lst, $this->view_lst, $msg);
        foreach ($this->type_view_ids_ui() as $msk_id) {
            $this->add_view_once_ui($msk_lst, $typ_lst->get_view_by_id($msk_id), $msg);
        }
        return $msk_lst;
    }

    /**
     * the entries of the edit menu of the navbar for the suggested views of this object, each with
     * the view name and behind it the switch icon like in the views tab; the view itself is
     * changed by the "change this view" entry of the menu, so the entries have no edit icon;
     * the view that shows the page is grey and the own view of the object has no switch icon,
     * because the object is already always shown with it; an object without an edit view of its
     * own has no switch icon at all, because the inherited edit view is the one of a word
     *
     * @param view_list $menu_lst the suggested views of this object (see menu_views_ui)
     * @param int $used_id the id of the view that shows the page, 0 if not known
     * @return string the html list items of the suggested views
     */
    function menu_view_items_ui(view_list $menu_lst, int $used_id = 0): string
    {
        $html = new html_base();
        $result = '';
        // e.g. a new word has no name yet
        $obj_name = $this->name() ?? '';
        foreach ($menu_lst->lst() as $msk) {
            if ($msk->id() == $used_id) {
                $links = $msk->used_link($this->id(), $obj_name);
            } else {
                $links = $msk->open_link($this->id(), $obj_name);
            }
            if ($msk->id() != $this->view_id and static::HAS_EDIT_VIEW) {
                $links .= $msk->switch_link($this->id(), static::VIEW_EDIT_ID, $obj_name);
            }
            // in one row, because the menu shows each link of a list item as a block
            $result .= $html->list_item($links, styles::FLEX_ROW) . "\n";
        }
        return $result;
    }

    /**
     * the names of some cached views that the edit menu does not offer for this object, which the
     * tooltip of "... more" names as examples of the views that the more page shows
     *
     * @param type_lists $typ_lst the frontend cache with the system views
     * @param view_list $menu_lst the suggested views of this object (see menu_views_ui)
     * @return array the names of the first views of the cache that are not in the menu
     */
    function menu_more_names_ui(type_lists $typ_lst, view_list $menu_lst): array
    {
        $menu_ids = $menu_lst->id_lst();
        $sys_lst = $typ_lst->msk_sys ?? new view_list();
        $names = [];
        foreach ($sys_lst->lst() as $msk) {
            if (!in_array($msk->id(), $menu_ids) and count($names) < self::MENU_MORE_NAMES) {
                $names[] = $msk->name();
            }
        }
        return $names;
    }

    /**
     * @return array the ids of the default views of the type of this object e.g. of a word its
     *               default, detail, range and detail with range view, empty for a type without them
     */
    private function type_view_ids_ui(): array
    {
        $msk_lst = new view_list();
        $views = new views();
        return $views->default_versions($msk_lst->default_id($this));
    }

    /**
     * add the views of one group of the edit menu sorted by name, so that the menu does not
     * depend on the database order
     * @param view_list $msk_lst the views of the menu that the group is added to
     * @param view_list|null $grp_lst the views of the group e.g. the views of the parents
     * @param user_message $msg to report a view that cannot be added
     */
    private function add_views_by_name_ui(view_list $msk_lst, ?view_list $grp_lst, user_message $msg): void
    {
        if ($grp_lst != null) {
            $sorted = clone $grp_lst;
            $sorted->sort_by_name();
            foreach ($sorted->lst() as $msk) {
                $this->add_view_once_ui($msk_lst, $msk, $msg);
            }
        }
    }

    /**
     * @return view|null the own default view of this object, which is part of the related views from
     *                   the backend, or null if the object has no own default view
     */
    private function own_view_ui(): ?view
    {
        $result = null;
        if ($this->view_id != null and $this->view_lst != null) {
            $result = $this->view_lst->get($this->view_id);
        }
        return $result;
    }

    /**
     * add a view to the edit menu only if it is not yet in it, without reporting the repeat,
     * because in the menu a view of a later group that an earlier group already offers is normal
     * @param view_list $msk_lst the views of the menu
     * @param view|null $msk the view to add or null e.g. if the cache has not got the view
     * @param user_message $msg to report a view that cannot be added
     */
    private function add_view_once_ui(view_list $msk_lst, ?view $msk, user_message $msg): void
    {
        if ($msk != null and !$msk_lst->has_id($msk->id())) {
            $msk_lst->add($msk, $msg);
        }
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);

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
     * @param user_message|Message $msg
     * @param array $data additional data that should be included in the get request
     * @param int $usr_id the id of the session user to load the object for, 0 for the default
     * @return bool
     */
    function load_by_id(int|string $id, user_message|Message $msg, array $data = [], int $usr_id = 0): bool
    {
        if ($usr_id > 0) {
            $data[url_var::USER] = $usr_id;
        }
        return parent::load_by_id($id, $msg, $data, $usr_id);
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
        $msk_lst = $msk_lst->ex_system($msg);
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

    /**
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function share_type_selector(string $form, ?type_lists $typ_lst): string
    {
        global $ui_sys;
        // fall back to the frontend request cache if the caller has no type list
        if ($typ_lst == null) {
            log_err('type list cache missing, falling back to the request cache');
            $typ_lst = $ui_sys->typ_lst_cache;
        }
        if ($ui_sys->usr === $this->owner or $this->owner == null) {
            return $this->type_selector_with_pre($typ_lst->shr_typ, $form, $this->share_id);
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
        // fall back to the frontend request cache if the caller has no type list
        if ($typ_lst == null) {
            log_err('type list cache missing, falling back to the request cache');
            $typ_lst = $ui_sys->typ_lst_cache;
        }
        if ($ui_sys->usr === $this->owner or $this->owner == null) {
            return $this->type_selector_with_pre($typ_lst->ptc_typ, $form, $this->protection_id);
        } else {
            return '';
        }
    }

    /**
     * @param string $form the name of the html form
     * @param user_message $msg with the requesting user for whom the components are selected
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list $cmp_lst with the suggested components
     * @return string the html code to select a component
     */
    public function component_selector(
        string         $form,
        user_message   $msg,
        string         $pattern,
        int            $id,
        component_list $cmp_lst
    ): string
    {
        if ($pattern != '') {
            $cmp_lst->load_like($pattern, $msg);
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
        global $ui_sys;
        // fall back to the frontend request cache if the caller has no type list
        if ($typ_lst == null) {
            log_err('type list cache missing, falling back to the request cache');
            $typ_lst = $ui_sys->typ_lst_cache;
        }
        if ($typ_lst->cmp_lnk_typ != null) {
            return $typ_lst->cmp_lnk_typ->selector($form);
        } else {
            return 'no component types yet defined';
        }
    }

}


