<?php

/*

    model/sandbox/sandbox_related.php - the related data of a sandbox object for its page tabs
    ---------------------------------

    the changes and the user sandbox (overlay) overwrites of a sandbox object as api arrays,
    used by the 'changes', 'my' and 'others' tab of an object page

    zukunft.com has two sandbox hierarchies without a common parent: sandbox (one database id
    per row e.g. a word) and sandbox_multi (a group id per row e.g. a value), so the shared
    code of both cannot live in a parent class and lives here instead

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

namespace Zukunft\ZukunftCom\main\php\cfg\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_LOG . 'change_log_list.php';
// both sandbox classes include this file, so including them here would be a cycle; a caller
// always holds an instance of one of them, so both are loaded before any function below is used
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'share_types.php';

use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\share_types as share_type_shared;

/**
 * @var sandbox_related $sbx_rel the suggested var name of this class
 */
class sandbox_related
{

    /*
     * api
     */

    /**
     * the views that can show the object for the views tab of the object page; the list is
     * loaded by the object itself (load_views_related), because which views are related
     * differs per object e.g. the own default view of a word or the view type of a value
     *
     * @param view_list|null $msk_lst the related views of the object or null if none is loaded
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created
     * @return array the view entries of the api json array
     */
    function views_array(?view_list $msk_lst, user_message $msg, ?user $usr): array
    {
        $vars = [];
        if ($msk_lst != null and !$msk_lst->is_empty()) {
            // drop the related views the requester may not read (idor)
            $msk_lst->filter_readable_by($usr);
            $vars[json_fields::VIEWS] = $msk_lst->api_json_array([], $msg, $usr);
        }
        return $vars;
    }

    /**
     * the changes of the given object for the changes tab of the object page
     * @param sandbox|sandbox_multi $sbx the object whose changes should be shown
     * @param api_type_list $typ_lst the test mode keeps the list set by the caller instead of loading
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created
     * @return array the change entries of the api json array
     */
    function changes_array(
        sandbox|sandbox_multi $sbx,
        api_type_list         $typ_lst,
        user_message          $msg,
        ?user                 $usr
    ): array
    {
        $vars = [];
        if ($sbx->changes_related == null and !$typ_lst->test_mode()) {
            $this->load_changes($sbx, $msg);
        }
        if ($sbx->changes_related != null and !$sbx->changes_related->is_empty()) {
            $vars[json_fields::CHANGES] = $sbx->changes_related->api_json_array(
                new api_type_list(), $msg, $usr);
        }
        return $vars;
    }

    /**
     * fill changes_related of the given object for changes_array()
     * @param sandbox|sandbox_multi $sbx the object whose changes should be loaded
     * @param user_message $msg to collect any problem while loading the changes
     * @return void
     */
    function load_changes(sandbox|sandbox_multi $sbx, user_message $msg): void
    {
        $chg_lst = new change_log_list();
        $chg_lst->load_obj_last($sbx, $sbx->get_user(), $msg);
        $sbx->changes_related = $chg_lst;
    }

    /**
     * the user sandbox overwrites of the given object for the 'my' and 'others' tab
     * @param sandbox|sandbox_multi $sbx the object whose overwrites should be shown
     * @param api_type_list $typ_lst the test mode reads no overlay rows
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created
     * @return array the overwrite entries of the api json array
     */
    function overwrites_array(
        sandbox|sandbox_multi $sbx,
        api_type_list         $typ_lst,
        user_message          $msg,
        ?user                 $usr
    ): array
    {
        $vars = [];
        if (!$typ_lst->test_mode()) {
            $ovr_msg = new user_message($usr); // a buffer for the api user, merged back below
            $usr_ovr = $this->user_overwrites($sbx, $ovr_msg);
            if ($usr_ovr != []) {
                $vars[json_fields::USER_OVERWRITES] = $usr_ovr;
            }
            $oth_ovr = $this->other_overwrites($sbx, $ovr_msg);
            if ($oth_ovr != []) {
                $vars[json_fields::OTHER_OVERWRITES] = $oth_ovr;
            }
            $msg->merge($ovr_msg);
        }
        return $vars;
    }

    /**
     * the fields that the user of the given object has overwritten in the user sandbox (overlay)
     * table e.g. user_words, each with the user value and the value of the standard object;
     * used by the 'my' tab of the object page (see the web ui_preview::user_overwrites_table)
     *
     * @param sandbox|sandbox_multi $sbx the object whose overwrites should be shown
     * @param user_message $msg to collect the error messages for the calling user
     * @return array one entry per overwritten field with the db field name, the user value
     *               and the standard value
     */
    function user_overwrites(sandbox|sandbox_multi $sbx, user_message $msg): array
    {
        $result = [];
        if ($sbx->has_usr_cfg()) {
            $std = clone $sbx;
            $std->load_standard($sbx->id(), $msg);
            $result = $this->overwrite_rows($sbx, $std, $msg);
        }
        return $result;
    }

    /**
     * the fields that users other than the user of the given object have overwritten in the user
     * sandbox (overlay) table e.g. user_words, each with the name of the overwriting user, the
     * user value and the value of the standard object; overwrites that the other user does not
     * share (the personal and private share types) are never included; used by the 'others'
     * tab of the object page (see the web ui_preview::other_overwrites_table)
     *
     * @param sandbox|sandbox_multi $sbx the object whose overwrites should be shown
     * @param user_message $msg to collect the error messages for the calling user
     * @param sandbox|sandbox_multi|null $std the standard object if the caller has already loaded
     *                                        it, so that it is not read a second time e.g. by the
     *                                        user page, which needs it for the standard value too
     * @param user_list|null $changers the users that have changed the object if the caller has
     *                                 already read them for many objects at once, so that they are
     *                                 not read again per object (see sandbox::changed_by_ids)
     * @return array one entry per overwritten field and user, sorted by user name and field
     */
    function other_overwrites(
        sandbox|sandbox_multi      $sbx,
        user_message               $msg,
        sandbox|sandbox_multi|null $std = null,
        ?user_list                 $changers = null
    ): array
    {
        $result = [];
        $changers = $changers ?? $sbx->changed_by($msg);
        // the user list of changed_by() stays null if no other user has changed the object
        foreach ($changers->lst() ?? [] as $other) {
            if ($other->id() != $sbx->get_user()->id()) {
                $other_obj = clone $sbx;
                $other_obj->set_user($other);
                $other_obj->load_by_id($sbx->id(), $msg);
                // a null share id is the default public share (the default of a nullable field is
                // resolved at the point of use), so only a set share type can restrict the listing
                $shr = share_type_shared::PUBLIC;
                if ($other_obj->share_id() != null) {
                    $shr = $other_obj->share_type_code_id();
                }
                if ($other_obj->has_usr_cfg()
                    and $shr != share_type_shared::PERSONAL and $shr != share_type_shared::PRIVATE) {
                    if ($std == null) {
                        $std = clone $sbx;
                        $std->load_standard($sbx->id(), $msg);
                    }
                    foreach ($this->overwrite_rows($other_obj, $std, $msg) as $row) {
                        $row[json_fields::USER_NAME] = $other->name();
                        $result[] = $row;
                    }
                }
            }
        }
        // sort by user name and field so the html order never depends on the db row order
        usort($result, fn($a, $b) => [$a[json_fields::USER_NAME], $a[json_fields::FIELD]]
            <=> [$b[json_fields::USER_NAME], $b[json_fields::FIELD]]);
        return $result;
    }

    /**
     * the overwrite rows of the given object compared to the given standard object: one entry per
     * field that differs with the db field name, the value of the object and the standard
     * value; the shared row builder of the 'my' and the 'others' overwrite api arrays
     *
     * @param sandbox|sandbox_multi $sbx the user object as loaded for the overwriting user
     * @param sandbox|sandbox_multi $std the standard object of the object as loaded via load_standard
     * @param user_message $msg to collect the error messages for the calling user
     * @return array one entry per overwritten field
     */
    private function overwrite_rows(
        sandbox|sandbox_multi $sbx,
        sandbox|sandbox_multi $std,
        user_message          $msg
    ): array
    {
        $result = [];
        $fvt_lst = $sbx->db_fields_changed($std, $msg);
        foreach ($fvt_lst->names() as $name) {
            // the object id and the changing user are keys, not field overwrites
            if ($name != $sbx::FLD_ID and $name != user_db::FLD_ID) {
                $fld = $fvt_lst->get($name, $msg);
                $result[] = [
                    json_fields::FIELD => $name,
                    json_fields::USR_VALUE => $fld?->value,
                    json_fields::STD_VALUE => $fld?->old,
                ];
            }
        }
        return $result;
    }

}
