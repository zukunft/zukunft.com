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

namespace cfg;

include_once API_SYSTEM_PATH . 'type_object.php';
include_once API_SYSTEM_PATH . 'type_lists.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_PHRASE_PATH . 'phrase_types.php';
include_once MODEL_SYSTEM_PATH . 'batch_job_type_list.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_FORMULA_PATH . 'formula_type.php';
include_once MODEL_FORMULA_PATH . 'formula_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_element_type_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_type.php';
include_once MODEL_VIEW_PATH . 'view_type_list.php';
include_once MODEL_VIEW_PATH . 'component_link_types.php';
include_once MODEL_COMPONENT_PATH . 'component_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component_pos_type_list.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once MODEL_REF_PATH . 'source_type_list.php';
include_once MODEL_SANDBOX_PATH . 'share_type_list.php';
include_once MODEL_SANDBOX_PATH . 'protection_type_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_form_list.php';
include_once MODEL_LOG_PATH . 'change_log_action.php';
include_once MODEL_LOG_PATH . 'change_log_table.php';
include_once MODEL_LOG_PATH . 'change_log_field.php';

use api\system\type_lists as type_lists_api;
use cfg\component\component_pos_type_list;
use cfg\component\component_type_list;
use cfg\log\change_log_action;
use cfg\log\change_log_field;
use cfg\log\change_log_table;
use controller\controller;

class type_lists
{

    /*
     * cast
     */

    function api_obj(user $usr): type_lists_api
    {
        global $db_con;
        global $user_profiles;
        global $phrase_types;
        global $formula_types;
        global $formula_link_types;
        global $formula_element_types;
        global $view_types;
        global $component_types;
        global $component_link_types;
        global $component_position_types;
        global $ref_types;
        global $source_types;
        global $share_types;
        global $protection_types;
        global $languages;
        global $language_forms;
        global $verbs;
        global $system_views;
        global $sys_log_stati;
        global $job_types;
        global $change_log_actions;
        global $change_log_tables;
        global $change_log_fields;

        log_debug();
        $lst = new type_lists_api($db_con, $usr);
        $lst->add($user_profiles->api_obj(), controller::API_LIST_USER_PROFILES);
        $lst->add($phrase_types->api_obj(), controller::API_LIST_PHRASE_TYPES);
        $lst->add($formula_types->api_obj(), controller::API_LIST_FORMULA_TYPES);
        $lst->add($formula_link_types->api_obj(), controller::API_LIST_FORMULA_LINK_TYPES);
        $lst->add($formula_element_types->api_obj(), controller::API_LIST_FORMULA_ELEMENT_TYPES);
        $lst->add($view_types->api_obj(), controller::API_LIST_VIEW_TYPES);
        $lst->add($component_types->api_obj(), controller::API_LIST_COMPONENT_TYPES);
        //$lst->add($component_link_types->api_obj(), controller::API_LIST_VIEW_COMPONENT_LINK_TYPES);
        $lst->add($component_position_types->api_obj(), controller::API_LIST_COMPONENT_POSITION_TYPES);
        $lst->add($ref_types->api_obj(), controller::API_LIST_REF_TYPES);
        $lst->add($source_types->api_obj(), controller::API_LIST_SOURCE_TYPES);
        $lst->add($share_types->api_obj(), controller::API_LIST_SHARE_TYPES);
        $lst->add($protection_types->api_obj(), controller::API_LIST_PROTECTION_TYPES);
        $lst->add($languages->api_obj(), controller::API_LIST_LANGUAGES);
        $lst->add($language_forms->api_obj(), controller::API_LIST_LANGUAGE_FORMS);
        $lst->add($sys_log_stati->api_obj(), controller::API_LIST_SYS_LOG_STATI);
        $lst->add($job_types->api_obj(), controller::API_LIST_JOB_TYPES);
        $lst->add($change_log_actions->api_obj(), controller::API_LIST_CHANGE_LOG_ACTIONS);
        $lst->add($change_log_tables->api_obj(), controller::API_LIST_CHANGE_LOG_TABLES);
        $lst->add($change_log_fields->api_obj(), controller::API_LIST_CHANGE_LOG_FIELDS);
        $lst->add($verbs->api_obj(), controller::API_LIST_VERBS);
        if ($system_views != null) {
            $lst->add($system_views->api_obj(), controller::API_LIST_SYSTEM_VIEWS);
        }
        log_debug('done');
        return $lst;
    }

    /*
     * load
     */

    function load(sql_db $db_con, ?user $usr): bool
    {
        global $sys_log_stati;
        global $system_users;
        global $user_profiles;
        global $phrase_types;
        global $formula_types;
        global $formula_link_types;
        global $formula_element_types;
        global $view_types;
        global $component_types;
        global $component_link_types;
        global $component_position_types;
        global $ref_types;
        global $source_types;
        global $share_types;
        global $protection_types;
        global $languages;
        global $language_forms;
        global $sys_log_stati;
        global $job_types;
        global $change_log_actions;
        global $change_log_tables;
        global $change_log_fields;
        global $verbs;
        global $system_views;

        $result = true;

        // load backend only default records
        $sys_log_stati = new sys_log_status();
        $sys_log_stati->load($db_con);
        $system_users = new user_list($usr);
        $system_users->load_system($db_con);

        // load the type database enum
        // these tables are expected to be so small that it is more efficient to load all database records once at start
        $user_profiles = new user_profile_list();
        $user_profiles->load($db_con);
        $phrase_types = new phrase_types();
        $phrase_types->load($db_con);
        $formula_types = new formula_type_list();
        $formula_types->load($db_con);
        $formula_link_types = new formula_link_type_list();
        $formula_link_types->load($db_con);
        $formula_element_types = new formula_element_type_list();
        $formula_element_types->load($db_con);
        $view_types = new view_type_list();
        $view_types->load($db_con);
        $component_types = new component_type_list();
        $component_types->load($db_con);
        // TODO review: not yet needed?
        //$component_link_types = new component_link_type_list();
        //$component_link_types->load($db_con);
        $component_position_types = new component_pos_type_list();
        $component_position_types->load($db_con);
        $ref_types = new ref_type_list();
        $ref_types->load($db_con);
        $source_types = new source_type_list();
        $source_types->load($db_con);
        $share_types = new share_type_list();
        $share_types->load($db_con);
        $protection_types = new protection_type_list();
        $protection_types->load($db_con);
        $languages = new language_list();
        $languages->load($db_con);
        $language_forms = new language_form_list();
        $language_forms->load($db_con);
        $job_types = new batch_job_type_list();
        $job_types->load($db_con);
        $change_log_actions = new change_log_action();
        $change_log_actions->load($db_con);
        $change_log_tables = new change_log_table();
        $change_log_tables->load($db_con);
        $change_log_fields = new change_log_field();
        $change_log_fields->load($db_con);

        // preload the little more complex objects
        $verbs = new verb_list();
        $verbs->load($db_con);
        if ($usr != null) {
            $system_views = new view_sys_list($usr);
            $system_views->load($db_con);
        }

        return $result;
    }
}
