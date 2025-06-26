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

include_once API_OBJECT_PATH . 'api_message.php';
include_once API_OBJECT_PATH . 'controller.php';
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
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';

use cfg\component\component_link_type_list;
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
use controller\api_message;
use shared\json_fields;
use shared\types\api_type_list;

class type_lists
{


    /*
     * load
     */

    /**
     * reload the cache used for logging the changes
     * @param sql_db $db_con
     * @return bool
     */
    function load_log(sql_db $db_con): bool
    {
        global $cng_act_cac;
        global $cng_tbl_cac;
        global $cng_fld_cac;

        $result = true;

        $cng_act_cac = new change_action_list();
        $cng_act_cac->load($db_con);
        $cng_tbl_cac = new change_table_list();
        $cng_tbl_cac->load($db_con);
        $cng_fld_cac = new change_field_list();
        $cng_fld_cac->load($db_con);

        return $result;
    }

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
        $cmp_lnk_typ_cac = new component_link_type_list();
        $cmp_lnk_typ_cac->load($db_con);
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

    /*
     * api
     */

    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        // null values are not needed in the api message to the frontend
        // but in the api message to the backend null values are relevant
        // e.g. to remove empty string overwrites
        $vars = $this->api_json_array();
        $vars = array_filter($vars, fn($value) => !is_null($value) && $value !== '');

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con,  $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * @return array with all preloaded types
     */
    function api_json_array(): array
    {

        log_debug();
        $vars = [];
        global $usr_pro_cac;
        $vars[json_fields::LIST_USER_PROFILES] = $usr_pro_cac->api_json_array();
        global $phr_typ_cac;
        $vars[json_fields::LIST_PHRASE_TYPES] = $phr_typ_cac->api_json_array();
        global $frm_typ_cac;
        $vars[json_fields::LIST_FORMULA_TYPES] = $frm_typ_cac->api_json_array();
        global $frm_lnk_typ_cac;
        $vars[json_fields::LIST_FORMULA_LINK_TYPES] = $frm_lnk_typ_cac->api_json_array();
        global $elm_typ_cac;
        $vars[json_fields::LIST_ELEMENT_TYPES] = $elm_typ_cac->api_json_array();
        global $msk_typ_cac;
        $vars[json_fields::LIST_VIEW_TYPES] = $msk_typ_cac->api_json_array();
        global $msk_sty_cac;
        $vars[json_fields::LIST_VIEW_STYLES] = $msk_sty_cac->api_json_array();
        global $msk_lnk_typ_cac;
        $vars[json_fields::LIST_VIEW_LINK_TYPES] = $msk_lnk_typ_cac->api_json_array();
        global $cmp_typ_cac;
        $vars[json_fields::LIST_COMPONENT_TYPES] = $cmp_typ_cac->api_json_array();
        global $cmp_lnk_typ_cac;
        $vars[json_fields::LIST_COMPONENT_LINK_TYPES] = $cmp_lnk_typ_cac->api_json_array();
        global $pos_typ_cac;
        $vars[json_fields::LIST_COMPONENT_POSITION_TYPES] = $pos_typ_cac->api_json_array();
        global $ref_typ_cac;
        $vars[json_fields::LIST_REF_TYPES] = $ref_typ_cac->api_json_array();
        global $src_typ_cac;
        $vars[json_fields::LIST_SOURCE_TYPES] = $src_typ_cac->api_json_array();
        global $shr_typ_cac;
        $vars[json_fields::LIST_SHARE_TYPES] = $shr_typ_cac->api_json_array();
        global $ptc_typ_cac;
        $vars[json_fields::LIST_PROTECTION_TYPES] = $ptc_typ_cac->api_json_array();
        global $lan_cac;
        $vars[json_fields::LIST_LANGUAGES] = $lan_cac->api_json_array();
        global $lan_for_cac;
        $vars[json_fields::LIST_LANGUAGE_FORMS] = $lan_for_cac->api_json_array();
        global $sys_log_sta_cac;
        $vars[json_fields::LIST_SYS_LOG_STATI] = $sys_log_sta_cac->api_json_array();
        global $job_typ_cac;
        $vars[json_fields::LIST_JOB_TYPES] = $job_typ_cac->api_json_array();
        global $cng_act_cac;
        $vars[json_fields::LIST_CHANGE_LOG_ACTIONS] = $cng_act_cac->api_json_array();
        global $cng_tbl_cac;
        $vars[json_fields::LIST_CHANGE_LOG_TABLES] = $cng_tbl_cac->api_json_array();
        global $cng_fld_cac;
        $vars[json_fields::LIST_CHANGE_LOG_FIELDS] = $cng_fld_cac->api_json_array();
        global $vrb_cac;
        $vars[json_fields::LIST_VERBS] = $vrb_cac->api_json_array();
        global $sys_msk_cac;
        if ($sys_msk_cac != null) {
            $vars[json_fields::LIST_SYSTEM_VIEWS] = $sys_msk_cac->api_json_array();
        }
        return $vars;
    }

}
