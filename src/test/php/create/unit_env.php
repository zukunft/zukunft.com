<?php

/*

    test/create/unit_env.php - create an environment for the unit tests
    ------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_type_list;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_COMPONENT . 'component_link_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::MODEL_USER . 'user_type_list.php';
include_once paths::MODEL_USER . 'user_status_list.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_VIEW . 'view_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_relation_type_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type_list;
use Zukunft\ZukunftCom\main\php\cfg\element\element_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type_list;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form_list;
use Zukunft\ZukunftCom\main\php\cfg\language\language_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_types;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\protection_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\share_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_sys_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type_list;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;

class unit_env
{

    function init_unit_tests(): void
    {
        global $usr;
        global $usr_sys;
        global $sys;

        // prepare the unit tests
        $this->init_sys_log_status();
        $this->init_user_profiles();
        $this->init_job_types();

        // set the profile of the test users
        $usr->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::EMAIL);
        $usr_sys->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::SYSTEM);
        $usr->id = 1;
        $this->init_sys_users($usr_sys);

        // continue with preparing unit tests
        $this->init_phrase_types();
        $this->init_verbs();
        $this->init_formula_types();
        $this->init_formula_html_types();
        $this->init_formula_link_types();
        $this->init_element_types();
        $this->init_views($usr);
        $this->init_view_types();
        $this->init_view_link_types();
        $this->init_view_relation_types();
        $this->init_component_types();
        $this->init_component_link_types();
        $this->init_component_pos_types();
        $this->init_ref_types();
        $this->init_source_types();
        $this->init_share_types();
        $this->init_protection_types();
        $this->init_languages();
        $this->init_language_forms();
        $this->init_job_statuus();
        $this->init_job_types();
        $this->init_log_actions();
        $this->init_log_tables();
        $this->init_log_fields();

    }

    /**
     * create the system log status list for the unit tests without database connection
     */
    private function init_sys_log_status(): void
    {
        global $sys;

        $sys->typ_lst->sys_log_sta = new sys_log_status_list();
        $sys->typ_lst->sys_log_sta->load_dummy();
    }

    /**
     * TODO check usr profile
     * create the system user list for the unit tests without database connection
     * @param user $usr the user who has requested the system user dummy list, which must be a system test admin user
     */
    private function init_sys_users(user $usr): void
    {
        global $sys;
        $sys->usr_sys = new user_list($usr);
        $sys->usr_sys->load_dummy();
    }

    /**
     * create the user profiles for the unit tests without database connection
     */
    private function init_user_profiles(): void
    {
        global $sys;

        $sys->typ_lst->usr_pro = new user_profile_list();
        $sys->typ_lst->usr_pro->load_dummy();

    }

    /**
     * create the user types for the unit tests without database connection
     */
    private function init_user_types(): void
    {
        global $sys;

        $sys->typ_lst->usr_typ = new user_type_list();
        $sys->typ_lst->usr_typ->load_dummy();

    }

    /**
     * create the user statuus for the unit tests without database connection
     */
    private function init_user_statuus(): void
    {
        global $sys;

        $sys->typ_lst->usr_sta = new user_status_list();
        $sys->typ_lst->usr_sta->load_dummy();

    }

    /**
     * create word type array for the unit tests without database connection
     */
    private function init_phrase_types(): void
    {
        global $sys;

        $sys->typ_lst->phr_typ = new phrase_types();
        $sys->typ_lst->phr_typ->load_dummy();

    }

    /**
     * create verb array for the unit tests without database connection
     */
    private function init_verbs(): void
    {
        global $sys;

        $sys->typ_lst->vrb = new verb_list();
        $sys->typ_lst->vrb->load_dummy();

    }

    /**
     * create formula type array for the unit tests without database connection
     */
    private function init_formula_types(): void
    {
        global $sys;
        $sys->typ_lst->frm_typ->load_dummy();

    }

    /**
     * create formula frontend type array for the unit tests without database connection
     */
    private function init_formula_html_types(): void
    {
        global $sys;

        $sys->typ_lst->frm_typ = new formula_type_list();
        $sys->typ_lst->frm_typ->load_dummy();
        //$sys->typ_lst->frm_typ->set_from_json_array($sys->typ_lst->frm_typ->api_json_array());

    }

    /**
     * create formula link type array for the unit tests without database connection
     */
    private function init_formula_link_types(): void
    {
        global $sys;

        $sys->typ_lst->frm_lnk_typ = new formula_link_type_list();
        $sys->typ_lst->frm_lnk_typ->load_dummy();

    }

    /**
     * create formula element type array for the unit tests without database connection
     */
    private function init_element_types(): void
    {
        global $sys;

        $sys->typ_lst->elm_typ = new element_type_list();
        $sys->typ_lst->elm_typ->load_dummy();

    }

    /**
     * create an array of the system views for the unit tests without database connection
     */
    private function init_views(user $usr): void
    {
        global $sys_msk_cac;

        $sys_msk_cac = new view_sys_list($usr);
        $sys_msk_cac->load_dummy();

    }

    /**
     * create view type array for the unit tests without database connection
     */
    private function init_view_types(): void
    {
        global $sys;

        $sys->typ_lst->msk_typ = new view_type_list();
        $sys->typ_lst->msk_typ->load_dummy();

    }

    /**
     * create view link type array for the unit tests without database connection
     */
    private function init_view_link_types(): void
    {
        global $sys;

        $sys->typ_lst->msk_lnk_typ = new view_link_type_list();
        $sys->typ_lst->msk_lnk_typ->load_dummy();

    }

    /**
     * create view link type array for the unit tests without database connection
     */
    private function init_view_relation_types(): void
    {
        global $cac;

        $cac->typ_lst->mrl_lst = new view_relation_type_list();
        $cac->typ_lst->mrl_lst->load_dummy();

    }

    /**
     * create view component type array for the unit tests without database connection
     */
    private function init_component_types(): void
    {
        global $sys;

        $sys->typ_lst->cmp_typ = new component_type_list();
        $sys->typ_lst->cmp_typ->load_dummy();

    }

    /**
     * create view component link type array for the unit tests without database connection
     */
    private function init_component_link_types(): void
    {
        global $sys;

        $sys->typ_lst->cmp_lnk_typ = new component_link_type_list();
        $sys->typ_lst->cmp_lnk_typ->load_dummy();

    }

    /**
     * create view component position type array for the unit tests without database connection
     */
    private function init_component_pos_types(): void
    {
        global $sys;

        $sys->typ_lst->pos_typ = new position_type_list();
        $sys->typ_lst->pos_typ->load_dummy();

    }

    /**
     * create ref type array for the unit tests without database connection
     */
    private function init_ref_types(): void
    {
        global $sys;

        $sys->typ_lst->ref_typ = new ref_type_list();
        $sys->typ_lst->ref_typ->load_dummy();

    }

    /**
     * create source type array for the unit tests without database connection
     */
    private function init_source_types(): void
    {
        global $sys;

        $sys->typ_lst->src_typ = new source_type_list();
        $sys->typ_lst->src_typ->load_dummy();

    }

    /**
     * create share type array for the unit tests without database connection
     */
    private function init_share_types(): void
    {
        global $sys;

        $sys->typ_lst->shr_typ = new share_type_list();
        $sys->typ_lst->shr_typ->load_dummy();

    }

    /**
     * create protection type array for the unit tests without database connection
     */
    private function init_protection_types(): void
    {
        global $sys;

        $sys->typ_lst->ptc_typ = new protection_type_list();
        $sys->typ_lst->ptc_typ->load_dummy();

    }

    /**
     * create languages array for the unit tests without database connection
     */
    private function init_languages(): void
    {
        global $sys;

        $sys->typ_lst->lan = new language_list();
        $sys->typ_lst->lan->load_dummy();

    }

    /**
     * create language forms array for the unit tests without database connection
     */
    private function init_language_forms(): void
    {
        global $sys;

        $sys->typ_lst->lan_for = new language_form_list();
        $sys->typ_lst->lan_for->load_dummy();

    }

    /**
     * create the job types array for the unit tests without database connection
     */
    private function init_job_types(): void
    {
        global $sys;
        $sys->typ_lst->job_typ->load_dummy();

    }

    /**
     * create the job status array for the unit tests without database connection
     */
    private function init_job_statuus(): void
    {
        global $sys;
        $sys->typ_lst->job_sta->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_actions(): void
    {
        global $sys;
        $sys->typ_lst->cng_act->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_tables(): void
    {
        global $sys;
        $sys->typ_lst->cng_tbl->load_dummy();

    }

    /**
     * create log field array for the unit tests without database connection
     */
    private function init_log_fields(): void
    {
        global $sys;
        $sys->typ_lst->cng_fld->load_dummy();

    }

}