<?php

/*

    model/helper/type_lists.php - helper class to combine all preloaded types in one class for the API
    ---------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\helper;

include_once API_SYSTEM_PATH . 'type_object.php';
include_once API_SYSTEM_PATH . 'type_lists.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status_list.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once MODEL_PHRASE_PATH . 'phrase_types.php';
include_once MODEL_SYSTEM_PATH . 'job_type_list.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status_list.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_ELEMENT_PATH . 'element_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_type.php';
include_once MODEL_FORMULA_PATH . 'formula_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_type.php';
include_once MODEL_VIEW_PATH . 'view_type_list.php';
include_once MODEL_VIEW_PATH . 'view_link_type_list.php';
include_once MODEL_COMPONENT_PATH . 'view_style.php';
include_once MODEL_COMPONENT_PATH . 'view_style_list.php';
include_once MODEL_COMPONENT_PATH . 'component_link_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component_type_list.php';
include_once MODEL_COMPONENT_PATH . 'position_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_link_type.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once MODEL_REF_PATH . 'source_type_list.php';
include_once MODEL_SANDBOX_PATH . 'share_type_list.php';
include_once MODEL_SANDBOX_PATH . 'protection_type_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_form_list.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_action_list.php';
include_once MODEL_LOG_PATH . 'change_table.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'change_field.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_profile_list.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once API_PATH . 'controller.php';

use api\system\type_lists as type_lists_api;
use cfg\component\position_type_list;
use cfg\component\component_type_list;
use cfg\component\view_style_list;
use cfg\db\sql_db;
use cfg\element\element_type_list;
use cfg\formula\formula_link_type_list;
use cfg\formula\formula_type_list;
use cfg\language\language_form_list;
use cfg\language\language_list;
use cfg\log\change_action_list;
use cfg\log\change_field_list;
use cfg\log\change_table_list;
use cfg\phrase\phrase_types;
use cfg\ref\ref_type_list;
use cfg\ref\source_type_list;
use cfg\sandbox\protection_type_list;
use cfg\sandbox\share_type_list;
use cfg\system\job_type_list;
use cfg\system\sys_log_status_list;
use cfg\user\user;
use cfg\user\user_profile_list;
use cfg\user\user_list;
use cfg\verb\verb_list;
use cfg\view\view_link_type_list;
use cfg\view\view_sys_list;
use cfg\view\view_type_list;
use controller\controller;

class type_lists
{

    /*
     * cast
     */

    function api_obj(user $usr): type_lists_api
    {
        global $db_con;
        global $usr_pro_cac;
        global $phr_typ_cac;
        global $frm_typ_cac;
        global $frm_lnk_typ_cac;
        global $elm_typ_cac;
        global $msk_typ_cac;
        global $msk_sty_cac;
        global $msk_lnk_typ_cac;
        global $cmp_typ_cac;
        global $cmp_lnk_typ_cac;
        global $pos_typ_cac;
        global $ref_typ_cac;
        global $src_typ_cac;
        global $shr_typ_cac;
        global $ptc_typ_cac;
        global $lan_cac;
        global $lan_for_cac;
        global $vrb_cac;
        global $sys_msk_cac;
        global $sys_log_sta_cac;
        global $job_typ_cac;
        global $cng_act_cac;
        global $cng_tbl_cac;
        global $cng_fld_cac;

        log_debug();
        $lst = new type_lists_api($db_con, $usr);
        $lst->add($usr_pro_cac->api_obj(), controller::API_LIST_USER_PROFILES);
        $lst->add($phr_typ_cac->api_obj(), controller::API_LIST_PHRASE_TYPES);
        $lst->add($frm_typ_cac->api_obj(), controller::API_LIST_FORMULA_TYPES);
        $lst->add($frm_lnk_typ_cac->api_obj(), controller::API_LIST_FORMULA_LINK_TYPES);
        $lst->add($elm_typ_cac->api_obj(), controller::API_LIST_ELEMENT_TYPES);
        $lst->add($msk_typ_cac->api_obj(), controller::API_LIST_VIEW_TYPES);
        $lst->add($msk_sty_cac->api_obj(), controller::API_LIST_VIEW_STYLES);
        $lst->add($msk_lnk_typ_cac->api_obj(), controller::API_LIST_VIEW_LINK_TYPES);
        $lst->add($cmp_typ_cac->api_obj(), controller::API_LIST_COMPONENT_TYPES);
        //$lst->add($cmp_lnk_typ_cac->api_obj(), controller::API_LIST_VIEW_COMPONENT_LINK_TYPES);
        $lst->add($pos_typ_cac->api_obj(), controller::API_LIST_COMPONENT_POSITION_TYPES);
        $lst->add($ref_typ_cac->api_obj(), controller::API_LIST_REF_TYPES);
        $lst->add($src_typ_cac->api_obj(), controller::API_LIST_SOURCE_TYPES);
        $lst->add($shr_typ_cac->api_obj(), controller::API_LIST_SHARE_TYPES);
        $lst->add($ptc_typ_cac->api_obj(), controller::API_LIST_PROTECTION_TYPES);
        $lst->add($lan_cac->api_obj(), controller::API_LIST_LANGUAGES);
        $lst->add($lan_for_cac->api_obj(), controller::API_LIST_LANGUAGE_FORMS);
        $lst->add($sys_log_sta_cac->api_obj(), controller::API_LIST_SYS_LOG_STATI);
        $lst->add($job_typ_cac->api_obj(), controller::API_LIST_JOB_TYPES);
        $lst->add($cng_act_cac->api_obj(), controller::API_LIST_CHANGE_LOG_ACTIONS);
        $lst->add($cng_tbl_cac->api_obj(), controller::API_LIST_CHANGE_LOG_TABLES);
        $lst->add($cng_fld_cac->api_obj(), controller::API_LIST_CHANGE_LOG_FIELDS);
        $lst->add($vrb_cac->api_obj(), controller::API_LIST_VERBS);
        if ($sys_msk_cac != null) {
            $lst->add($sys_msk_cac->api_obj(), controller::API_LIST_SYSTEM_VIEWS);
        }
        log_debug('done');
        return $lst;
    }

    /*
     * load
     */

    function load(sql_db $db_con, ?user $usr): bool
    {
        global $sys_log_sta_cac;
        global $system_users;
        global $usr_pro_cac;
        global $phr_typ_cac;
        global $frm_typ_cac;
        global $frm_lnk_typ_cac;
        global $elm_typ_cac;
        global $msk_typ_cac;
        global $msk_sty_cac;
        global $msk_lnk_typ_cac;
        global $cmp_typ_cac;
        global $cmp_lnk_typ_cac;
        global $pos_typ_cac;
        global $ref_typ_cac;
        global $src_typ_cac;
        global $shr_typ_cac;
        global $ptc_typ_cac;
        global $lan_cac;
        global $lan_for_cac;
        global $sys_log_sta_cac;
        global $job_typ_cac;
        global $cng_act_cac;
        global $cng_tbl_cac;
        global $cng_fld_cac;
        global $vrb_cac;
        global $sys_msk_cac;

        $result = true;

        // load backend only default records
        $sys_log_sta_cac = new sys_log_status_list();
        $sys_log_sta_cac->load($db_con);
        $system_users = new user_list($usr);
        $system_users->load_system($db_con);

        // load the type database enum
        // these tables are expected to be so small that it is more efficient to load all database records once at start
        $usr_pro_cac = new user_profile_list();
        $usr_pro_cac->load($db_con);
        $phr_typ_cac = new phrase_types();
        $phr_typ_cac->load($db_con);
        $frm_typ_cac = new formula_type_list();
        $frm_typ_cac->load($db_con);
        $frm_lnk_typ_cac = new formula_link_type_list();
        $frm_lnk_typ_cac->load($db_con);
        $elm_typ_cac = new element_type_list();
        $elm_typ_cac->load($db_con);
        $msk_typ_cac = new view_type_list();
        $msk_typ_cac->load($db_con);
        $msk_sty_cac = new view_style_list();
        $msk_sty_cac->load($db_con);
        $msk_lnk_typ_cac = new view_link_type_list();
        $msk_lnk_typ_cac->load($db_con);
        $cmp_typ_cac = new component_type_list();
        $cmp_typ_cac->load($db_con);
        // TODO review: not yet needed?
        //$cmp_lnk_typ_cac = new component_link_type_list();
        //$cmp_lnk_typ_cac->load($db_con);
        $pos_typ_cac = new position_type_list();
        $pos_typ_cac->load($db_con);
        $ref_typ_cac = new ref_type_list();
        $ref_typ_cac->load($db_con);
        $src_typ_cac = new source_type_list();
        $src_typ_cac->load($db_con);
        $shr_typ_cac = new share_type_list();
        $shr_typ_cac->load($db_con);
        $ptc_typ_cac = new protection_type_list();
        $ptc_typ_cac->load($db_con);
        $lan_cac = new language_list();
        $lan_cac->load($db_con);
        $lan_for_cac = new language_form_list();
        $lan_for_cac->load($db_con);
        $job_typ_cac = new job_type_list();
        $job_typ_cac->load($db_con);
        $cng_act_cac = new change_action_list();
        $cng_act_cac->load($db_con);
        $cng_tbl_cac = new change_table_list();
        $cng_tbl_cac->load($db_con);
        $cng_fld_cac = new change_field_list();
        $cng_fld_cac->load($db_con);

        // preload the little more complex objects
        $vrb_cac = new verb_list();
        $vrb_cac->load($db_con);
        if ($usr != null) {
            $sys_msk_cac = new view_sys_list($usr);
            $sys_msk_cac->load($db_con);
        }

        return $result;
    }
}
