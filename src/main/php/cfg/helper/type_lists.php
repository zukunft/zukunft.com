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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_profile.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_SYSTEM . 'job_status_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';
include_once paths::MODEL_VIEW . 'view_type.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::MODEL_VIEW . 'view_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_relation_type_list.php';
include_once paths::MODEL_COMPONENT . 'view_style.php';
include_once paths::MODEL_COMPONENT . 'view_style_list.php';
include_once paths::MODEL_COMPONENT . 'component_link_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_link_type.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_action_list.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\view_style_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\element\element_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type_list;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form_list;
use Zukunft\ZukunftCom\main\php\cfg\language\language_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_types;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\protection_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\share_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job_status_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type_list;
use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class type_lists
{


    /*
     *  object vars
     */

    // system users
    public user_profile_list $usr_pro;
    public user_list $system_users;

    // system log
    public sys_log_status_list $sys_log_sta;

    // change log
    public change_action_list $cng_act;
    public change_table_list $cng_tbl;
    public change_field_list $cng_fld;

    // language and system jobs
    public job_status_list $job_sta;
    public job_type_list $job_typ;
    public language_list $lan;
    public language_form_list $lan_for;

    // sandbox
    public share_type_list $shr_typ;
    public protection_type_list $ptc_typ;

    // word, number and formula types
    public verb_list $vrb;
    public phrase_types $phr_typ;
    public ref_type_list $ref_typ;
    public source_type_list $src_typ;
    public formula_type_list $frm_typ;
    public formula_link_type_list $frm_lnk_typ;
    public element_type_list $elm_typ;

    // view
    public view_type_list $msk_typ;
    public view_style_list $msk_sty;
    public view_link_type_list $msk_lnk_typ;
    public component_type_list $cmp_typ;
    public component_link_type_list $cmp_lnk_typ;
    public position_type_list $pos_typ;
    public view_relation_type_list $mrl_typ;


    /*
     * construct and map
     */

    function __construct()
    {
        // system users
        $this->usr_pro = new user_profile_list();
        $this->system_users = new user_list();

        // system log
        $this->sys_log_sta = new sys_log_status_list();

        // change log
        $this->cng_act = new change_action_list();
        $this->cng_tbl = new change_table_list();
        $this->cng_fld = new change_field_list();

        // language and system jobs
        $this->job_sta = new job_status_list();
        $this->job_typ = new job_type_list();
        $this->lan = new language_list();
        $this->lan_for = new language_form_list();

        // sandbox
        $this->shr_typ = new share_type_list();
        $this->ptc_typ = new protection_type_list();

        // word, number and formula types
        $this->vrb = new verb_list();
        $this->phr_typ = new phrase_types();
        $this->ref_typ = new ref_type_list();
        $this->src_typ = new source_type_list();
        $this->frm_typ = new formula_type_list();
        $this->frm_lnk_typ = new formula_link_type_list();
        $this->elm_typ = new element_type_list();

        // view
        $this->msk_typ = new view_type_list();
        $this->msk_sty = new view_style_list();
        $this->msk_lnk_typ = new view_link_type_list();
        $this->cmp_typ = new component_type_list();
        $this->cmp_lnk_typ = new component_link_type_list();
        $this->pos_typ = new position_type_list();
        $this->mrl_typ = new view_relation_type_list();
    }


    /*
     * load
     */

    // TODO Prio 0 use the dto object and cache the type data
    /**
     * load the type objects once from the database because they are expected to change very rarely
     * @param sql_db $db_con an open database connection to be able to redirect the loading
     * @return bool true if the loading is complete
     */
    function load(sql_db $db_con): bool
    {

        // user
        $result = $this->usr_pro->load($db_con);

        // log
        if ($result) {
            $result = $this->load_backend_only($db_con);
        }
        if ($result) {
            $result = $this->load_log($db_con);
        }

        // load the type database enum
        // these tables are expected to be so small that it is more efficient to load all database records once at start

        // language and system jobs
        if ($result) {
            $result = $this->lan->load($db_con);
        }
        if ($result) {
            $result = $this->lan_for->load($db_con);
        }
        if ($result) {
            $result = $this->job_typ->load($db_con);
        }

        // sandbox
        if ($result) {
            $result = $this->shr_typ->load($db_con);
        }
        if ($result) {
            $result = $this->ptc_typ->load($db_con);
        }

        // word, number and formula types
        if ($result) {
            $result = $this->load_core($db_con);
        }
        if ($result) {
            $result = $this->ref_typ->load($db_con);
        }
        if ($result) {
            $result = $this->src_typ->load($db_con);
        }
        if ($result) {
            $result = $this->frm_typ->load($db_con);
        }
        if ($result) {
            $result = $this->frm_lnk_typ->load($db_con);
        }
        if ($result) {
            $result = $this->elm_typ->load($db_con);
        }

        // view
        if ($result) {
            $result = $this->msk_typ->load($db_con);
        }
        if ($result) {
            $result = $this->msk_sty->load($db_con);
        }
        if ($result) {
            $result = $this->msk_lnk_typ->load($db_con);
        }
        if ($result) {
            $result = $this->cmp_typ->load($db_con);
        }
        if ($result) {
            $result = $this->cmp_lnk_typ->load($db_con);
        }
        if ($result) {
            $result = $this->pos_typ->load($db_con);
        }
        if ($result) {
            $result = $this->mrl_typ->load($db_con);
        }

        // preload type lists vars of this object
        if ($this->mrl_typ->is_empty()) {
            $this->mrl_typ->load_dummy();
        }

        // preload the little more complex objects
        $this->vrb->load($db_con);
        // TODO move the a separate loader on the data_object level
        //$sys_msk_cac = new view_sys_list($usr);
        //$sys_msk_cac->load($db_con);

        return $result;
    }

    /**
     * reload the cache used for logging the changes
     * @param sql_db $db_con an open database connection to be able to redirect the loading
     * @return bool false if the load is incomplete
     */
    function load_log(sql_db $db_con): bool
    {
        $result = $this->cng_act->load($db_con);
        if ($result) {
            $result = $this->cng_tbl->load($db_con);
        }
        if ($result) {
            $result = $this->cng_fld->load($db_con);
        }
        if ($result) {
            $result = $this->usr_pro->load($db_con);
        }

        return $result;
    }

    /**
     * load the core type lists needed for the api
     * @param sql_db $db_con an open database connection to be able to redirect the loading
     * @return bool false if the load is incomplete
     */
    function load_core(sql_db $db_con): bool
    {
        return $this->phr_typ->load($db_con);
    }

    /**
     * load the backend only type lists
     * @param sql_db $db_con an open database connection to be able to redirect the loading
     * @return bool false if the load is incomplete
     */
    function load_backend_only(sql_db $db_con): bool
    {
        $result = $this->sys_log_sta->load($db_con);
        /* TODO move the user cache
        if ($result) {
            $this->system_users = new user_list();
            $result = $this->system_users->load_system($db_con);
        }
        */

        return $result;
    }


    /*
     * api
     */

    /**
     * create the api message based on the loaded types
     * @param api_type_list|array $typ_lst to define the message format
     *        e.g. if the header should be included to avoid multi clients by using a message seq number security
     * @param user|null $usr the user for whom the message has been created
     * @return string with the encoded json message
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        global $db_con;
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = $this->api_json_array();
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
    }

    /**
     * @return array with all preloaded types
     */
    function api_json_array(): array
    {

        log_debug();
        $vars = [];

        $vars[json_fields::LIST_USER_PROFILES] = $this->usr_pro->api_json_array();

        $vars[json_fields::LIST_SYS_LOG_STATUUS] = $this->sys_log_sta->api_json_array();

        $vars[json_fields::LIST_CHANGE_LOG_ACTIONS] = $this->cng_act->api_json_array();
        $vars[json_fields::LIST_CHANGE_LOG_TABLES] = $this->cng_tbl->api_json_array();
        $vars[json_fields::LIST_CHANGE_LOG_FIELDS] = $this->cng_fld->api_json_array();

        $vars[json_fields::LIST_JOB_TYPES] = $this->job_typ->api_json_array();
        $vars[json_fields::LIST_LANGUAGES] = $this->lan->api_json_array();
        $vars[json_fields::LIST_LANGUAGE_FORMS] = $this->lan_for->api_json_array();

        $vars[json_fields::LIST_SHARE_TYPES] = $this->shr_typ->api_json_array();
        $vars[json_fields::LIST_PROTECTION_TYPES] = $this->ptc_typ->api_json_array();

        $vars[json_fields::LIST_VERBS] = $this->vrb->api_json_array();
        $vars[json_fields::LIST_PHRASE_TYPES] = $this->phr_typ->api_json_array();
        $vars[json_fields::LIST_REF_TYPES] = $this->ref_typ->api_json_array();
        $vars[json_fields::LIST_SOURCE_TYPES] = $this->src_typ->api_json_array();
        $vars[json_fields::LIST_FORMULA_TYPES] = $this->frm_typ->api_json_array();
        $vars[json_fields::LIST_FORMULA_LINK_TYPES] = $this->frm_lnk_typ->api_json_array();
        $vars[json_fields::LIST_ELEMENT_TYPES] = $this->elm_typ->api_json_array();

        $vars[json_fields::LIST_VIEW_TYPES] = $this->msk_typ->api_json_array();
        $vars[json_fields::LIST_VIEW_STYLES] = $this->msk_sty->api_json_array();
        $vars[json_fields::LIST_VIEW_LINK_TYPES] = $this->msk_lnk_typ->api_json_array();
        $vars[json_fields::LIST_COMPONENT_TYPES] = $this->cmp_typ->api_json_array();
        $vars[json_fields::LIST_COMPONENT_LINK_TYPES] = $this->cmp_lnk_typ->api_json_array();
        $vars[json_fields::LIST_COMPONENT_POSITION_TYPES] = $this->pos_typ->api_json_array();
        $vars[json_fields::LIST_VIEW_RELATION_TYPES] = $this->mrl_typ->api_json_array();

        return $vars;
    }


    /**
     * set all type list to dummy fallback values
     */

    function load_dummy(): void
    {
        // system users
        $this->usr_pro ->load_dummy();
        $this->system_users ->load_dummy();

        // system log
        $this->sys_log_sta ->load_dummy();

        // change log
        $this->cng_act ->load_dummy();
        $this->cng_tbl ->load_dummy();
        $this->cng_fld ->load_dummy();

        // language and system jobs
        $this->job_typ ->load_dummy();
        $this->lan ->load_dummy();
        $this->lan_for ->load_dummy();

        // sandbox
        $this->shr_typ ->load_dummy();
        $this->ptc_typ ->load_dummy();

        // word, number and formula types
        $this->vrb ->load_dummy();
        $this->phr_typ ->load_dummy();
        $this->ref_typ ->load_dummy();
        $this->src_typ ->load_dummy();
        $this->frm_typ ->load_dummy();
        $this->frm_lnk_typ ->load_dummy();
        $this->elm_typ ->load_dummy();

        // view
        $this->msk_typ ->load_dummy();
        $this->msk_sty ->load_dummy();
        $this->msk_lnk_typ ->load_dummy();
        $this->cmp_typ ->load_dummy();
        $this->cmp_lnk_typ ->load_dummy();
        $this->pos_typ ->load_dummy();
        $this->mrl_typ ->load_dummy();
    }

}
