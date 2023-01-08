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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use api\type_lists_api;

class type_lists
{

    /*
     * cast
     */
    public function api_obj(): type_lists_api
    {
        global $db_con;
        global $user_profiles;
        global $phrase_types;
        global $formula_types;
        global $formula_link_types;
        global $formula_element_types;
        global $view_types;
        global $view_component_types;
        global $view_component_link_types;
        global $view_component_position_types;
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

        $lst = new type_lists_api($db_con);
        $lst->add($user_profiles->api_obj(), 'user_profiles');
        $lst->add($phrase_types->api_obj(), 'phrase_types');
        $lst->add($formula_types->api_obj(), 'formula_types');
        $lst->add($formula_link_types->api_obj(), 'formula_link_types');
        $lst->add($formula_element_types->api_obj(), 'formula_element_types');
        $lst->add($view_types->api_obj(), 'view_types');
        $lst->add($view_component_types->api_obj(), 'view_component_types');
        //$lst->add($view_component_link_types->api_obj(), 'view_component_link_types');
        $lst->add($view_component_position_types->api_obj(), 'view_component_position_types');
        $lst->add($ref_types->api_obj(), 'ref_types');
        $lst->add($source_types->api_obj(), 'source_types');
        $lst->add($share_types->api_obj(), 'share_types');
        $lst->add($protection_types->api_obj(), 'protection_types');
        $lst->add($languages->api_obj(), 'languages');
        $lst->add($language_forms->api_obj(), 'language_forms');
        $lst->add($sys_log_stati->api_obj(), 'sys_log_stati');
        $lst->add($job_types->api_obj(), 'job_types');
        $lst->add($change_log_actions->api_obj(), 'change_log_actions');
        $lst->add($change_log_tables->api_obj(), 'change_log_tables');
        $lst->add($change_log_fields->api_obj(), 'change_log_fields');
        $lst->add($verbs->api_obj(), 'verbs');
        if ($system_views != null) {
            $lst->add($system_views->api_obj(), 'system_views');
        }
        return $lst;
    }

    /*
     * load
     */

    public function load(sql_db $db_con, ?user $usr): bool
    {
        global $sys_log_stati;
        global $system_users;
        global $user_profiles;
        global $phrase_types;
        global $formula_types;
        global $formula_link_types;
        global $formula_element_types;
        global $view_types;
        global $view_component_types;
        global $view_component_link_types;
        global $view_component_position_types;
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
        $system_users = new user_list();
        $system_users->load_system($db_con);

        // load the type database enum
        // these tables are expected to be so small that it is more efficient to load all database records once at start
        $user_profiles = new user_profile_list();
        $user_profiles->load($db_con);
        $phrase_types = new word_type_list();
        $phrase_types->load($db_con);
        $formula_types = new formula_type_list();
        $formula_types->load($db_con);
        $formula_link_types = new formula_link_type_list();
        $formula_link_types->load($db_con);
        $formula_element_types = new formula_element_type_list();
        $formula_element_types->load($db_con);
        $view_types = new view_type_list();
        $view_types->load($db_con);
        $view_component_types = new view_cmp_type_list();
        $view_component_types->load($db_con);
        // not yet needed?
        //$view_component_link_types = new view_component_link_type_list();
        //$view_component_link_types->load($db_con);
        $view_component_position_types = new view_cmp_pos_type_list();
        $view_component_position_types->load($db_con);
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
        $job_types = new job_type_list();
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
