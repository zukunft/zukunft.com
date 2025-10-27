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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_COMPONENT . 'component_link_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_LOG . 'change_action_list.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_VIEW . 'view_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once html_paths::TYPES . 'formula_type_list.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type_list;
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
use Zukunft\ZukunftCom\main\php\cfg\system\job_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_sys_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type_list;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\web\types\formula_type_list as formula_type_list_ui;

class unit_env
{

    function init_unit_tests(): void
    {
        global $usr;
        global $usr_sys;
        global $usr_pro_cac;

        // prepare the unit tests
        $this->init_sys_log_status();
        $this->init_sys_users();
        $this->init_user_profiles();
        $this->init_job_types();

        // set the profile of the test users
        $usr->profile_id = $usr_pro_cac->id(user_profiles::EMAIL);
        $usr_sys->profile_id = $usr_pro_cac->id(user_profiles::SYSTEM);
        $usr->id = 1;

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
        $this->init_component_types();
        $this->init_component_link_types();
        $this->init_component_pos_types();
        $this->init_ref_types();
        $this->init_source_types();
        $this->init_share_types();
        $this->init_protection_types();
        $this->init_languages();
        $this->init_language_forms();
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
        global $sys_log_sta_cac;

        $sys_log_sta_cac = new sys_log_status_list();
        $sys_log_sta_cac->load_dummy();
    }

    /**
     * create the system user list for the unit tests without database connection
     */
    private function init_sys_users(): void
    {
        global $usr_sys;
        global $system_users;

        $system_users = new user_list($usr_sys);
        $system_users->load_dummy();
    }

    /**
     * create the user profiles for the unit tests without database connection
     */
    private function init_user_profiles(): void
    {
        global $usr_pro_cac;

        $usr_pro_cac = new user_profile_list();
        $usr_pro_cac->load_dummy();

    }

    /**
     * create word type array for the unit tests without database connection
     */
    private function init_phrase_types(): void
    {
        global $phr_typ_cac;

        $phr_typ_cac = new phrase_types();
        $phr_typ_cac->load_dummy();

    }

    /**
     * create verb array for the unit tests without database connection
     */
    private function init_verbs(): void
    {
        global $vrb_cac;

        $vrb_cac = new verb_list();
        $vrb_cac->load_dummy();

    }

    /**
     * create formula type array for the unit tests without database connection
     */
    private function init_formula_types(): void
    {
        global $frm_typ_cac;

        $frm_typ_cac = new formula_type_list();
        $frm_typ_cac->load_dummy();

    }

    /**
     * create formula frontend type array for the unit tests without database connection
     */
    private function init_formula_html_types(): void
    {
        global $html_formula_types;
        global $frm_typ_cac;

        $html_formula_types = new formula_type_list_ui();
        $html_formula_types->set_from_json_array($frm_typ_cac->api_json_array());

    }

    /**
     * create formula link type array for the unit tests without database connection
     */
    private function init_formula_link_types(): void
    {
        global $frm_lnk_typ_cac;

        $frm_lnk_typ_cac = new formula_link_type_list();
        $frm_lnk_typ_cac->load_dummy();

    }

    /**
     * create formula element type array for the unit tests without database connection
     */
    private function init_element_types(): void
    {
        global $elm_typ_cac;

        $elm_typ_cac = new element_type_list();
        $elm_typ_cac->load_dummy();

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
        global $msk_typ_cac;

        $msk_typ_cac = new view_type_list();
        $msk_typ_cac->load_dummy();

    }

    /**
     * create view link type array for the unit tests without database connection
     */
    private function init_view_link_types(): void
    {
        global $msk_lnk_typ_cac;

        $msk_lnk_typ_cac = new view_link_type_list();
        $msk_lnk_typ_cac->load_dummy();

    }

    /**
     * create view component type array for the unit tests without database connection
     */
    private function init_component_types(): void
    {
        global $cmp_typ_cac;

        $cmp_typ_cac = new component_type_list();
        $cmp_typ_cac->load_dummy();

    }

    /**
     * create view component link type array for the unit tests without database connection
     */
    private function init_component_link_types(): void
    {
        global $cmp_lnk_typ_cac;

        $cmp_lnk_typ_cac = new component_link_type_list();
        $cmp_lnk_typ_cac->load_dummy();

    }

    /**
     * create view component position type array for the unit tests without database connection
     */
    private function init_component_pos_types(): void
    {
        global $pos_typ_cac;

        $pos_typ_cac = new position_type_list();
        $pos_typ_cac->load_dummy();

    }

    /**
     * create ref type array for the unit tests without database connection
     */
    private function init_ref_types(): void
    {
        global $ref_typ_cac;

        $ref_typ_cac = new ref_type_list();
        $ref_typ_cac->load_dummy();

    }

    /**
     * create source type array for the unit tests without database connection
     */
    private function init_source_types(): void
    {
        global $src_typ_cac;

        $src_typ_cac = new source_type_list();
        $src_typ_cac->load_dummy();

    }

    /**
     * create share type array for the unit tests without database connection
     */
    private function init_share_types(): void
    {
        global $shr_typ_cac;

        $shr_typ_cac = new share_type_list();
        $shr_typ_cac->load_dummy();

    }

    /**
     * create protection type array for the unit tests without database connection
     */
    private function init_protection_types(): void
    {
        global $ptc_typ_cac;

        $ptc_typ_cac = new protection_type_list();
        $ptc_typ_cac->load_dummy();

    }

    /**
     * create languages array for the unit tests without database connection
     */
    private function init_languages(): void
    {
        global $lan_cac;

        $lan_cac = new language_list();
        $lan_cac->load_dummy();

    }

    /**
     * create language forms array for the unit tests without database connection
     */
    private function init_language_forms(): void
    {
        global $lan_for_cac;

        $lan_for_cac = new language_form_list();
        $lan_for_cac->load_dummy();

    }

    /**
     * create the job types array for the unit tests without database connection
     */
    private function init_job_types(): void
    {
        global $job_typ_cac;

        $job_typ_cac = new job_type_list();
        $job_typ_cac->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_actions(): void
    {
        global $cng_act_cac;

        $cng_act_cac = new change_action_list();
        $cng_act_cac->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_tables(): void
    {
        global $cng_tbl_cac;

        $cng_tbl_cac = new change_table_list();
        $cng_tbl_cac->load_dummy();

    }

    /**
     * create log field array for the unit tests without database connection
     */
    private function init_log_fields(): void
    {
        global $cng_fld_cac;

        $cng_fld_cac = new change_field_list();
        $cng_fld_cac->load_dummy();

    }

}