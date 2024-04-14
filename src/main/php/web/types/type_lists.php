<?php

/*

    web/types/type_list.php - parent object for all preloaded types used in the html frontend
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\types;

include_once WEB_TYPES_PATH . 'type_object.php';
include_once WEB_TYPES_PATH . 'type_list.php';
include_once WEB_TYPES_PATH . 'change_action_list.php';
include_once WEB_TYPES_PATH . 'change_table_list.php';
include_once WEB_TYPES_PATH . 'change_field_list.php';
include_once WEB_TYPES_PATH . 'sys_log_status_list.php';
include_once WEB_TYPES_PATH . 'user_profiles.php';
include_once WEB_TYPES_PATH . 'job_type_list.php';
include_once WEB_TYPES_PATH . 'languages.php';
include_once WEB_TYPES_PATH . 'language_forms.php';
include_once WEB_TYPES_PATH . 'share.php';
include_once WEB_TYPES_PATH . 'protection.php';
include_once WEB_TYPES_PATH . 'verbs.php';
include_once WEB_TYPES_PATH . 'phrase_types.php';
include_once WEB_TYPES_PATH . 'formula_type_list.php';
include_once WEB_TYPES_PATH . 'formula_link_type_list.php';
include_once WEB_TYPES_PATH . 'source_type_list.php';
include_once WEB_TYPES_PATH . 'ref_type_list.php';
include_once WEB_TYPES_PATH . 'view_type_list.php';
include_once WEB_TYPES_PATH . 'component_type_list.php';
include_once WEB_TYPES_PATH . 'component_link_type_list.php';
include_once WEB_TYPES_PATH . 'position_type_list.php';
include_once WEB_VIEW_PATH . 'view_list.php';

use controller\controller;
use html\view\view_list as view_list_dsp;

class type_lists
{

    /*
     * construct and map
     */

    /**
     * fill the global html frontend type vars base on the api message
     * @param string|null $api_json the api message to set all types
     */
    function __construct(?string $api_json = null)
    {
        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of this frontend object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $ctrl = new controller();
        $json_array = json_decode($json_api_msg, true);
        $type_lists_json = $ctrl->check_api_msg($json_array, controller::API_TYPE_LISTS);
        $this->set_from_json_array($type_lists_json);
    }

    /**
     * set the vars of this log html object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(controller::API_LIST_USER_PROFILES, $json_array)) {
            $this->set_user_profiles($json_array[controller::API_LIST_USER_PROFILES]);
        } else {
            log_err('Mandatory user profiles missing in API JSON ' . json_encode($json_array));
            $this->set_user_profiles();
        }
        if (array_key_exists(controller::API_LIST_PHRASE_TYPES, $json_array)) {
            $this->set_phrase_types($json_array[controller::API_LIST_PHRASE_TYPES]);
        } else {
            log_err('Mandatory phrase_types missing in API JSON ' . json_encode($json_array));
            $this->set_phrase_types();
        }
        if (array_key_exists(controller::API_LIST_FORMULA_TYPES, $json_array)) {
            $this->set_formula_types($json_array[controller::API_LIST_FORMULA_TYPES]);
        } else {
            log_err('Mandatory formula_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_types();
        }
        if (array_key_exists(controller::API_LIST_FORMULA_LINK_TYPES, $json_array)) {
            $this->set_formula_link_types($json_array[controller::API_LIST_FORMULA_LINK_TYPES]);
        } else {
            log_err('Mandatory formula_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_link_types();
        }
        if (array_key_exists(controller::API_LIST_VIEW_TYPES, $json_array)) {
            $this->set_view_types($json_array[controller::API_LIST_VIEW_TYPES]);
        } else {
            log_err('Mandatory view_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_types();
        }
        if (array_key_exists(controller::API_LIST_COMPONENT_TYPES, $json_array)) {
            $this->set_component_types($json_array[controller::API_LIST_COMPONENT_TYPES]);
        } else {
            log_err('Mandatory component_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_types();
        }
        /*
        if (array_key_exists(controller::API_LIST_VIEW_COMPONENT_LINK_TYPES, $json_array)) {
            $this->set_component_link_types($json_array[controller::API_LIST_VIEW_COMPONENT_LINK_TYPES]);
        } else {
            log_err('Mandatory component_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_link_types();
        }
        */
        if (array_key_exists(controller::API_LIST_COMPONENT_POSITION_TYPES, $json_array)) {
            $this->set_position_types($json_array[controller::API_LIST_COMPONENT_POSITION_TYPES]);
        } else {
            log_err('Mandatory position_types missing in API JSON ' . json_encode($json_array));
            $this->set_position_types();
        }
        if (array_key_exists(controller::API_LIST_REF_TYPES, $json_array)) {
            $this->set_ref_types($json_array[controller::API_LIST_REF_TYPES]);
        } else {
            log_err('Mandatory ref_types missing in API JSON ' . json_encode($json_array));
            $this->set_ref_types();
        }
        if (array_key_exists(controller::API_LIST_SOURCE_TYPES, $json_array)) {
            $this->set_source_types($json_array[controller::API_LIST_SOURCE_TYPES]);
        } else {
            log_err('Mandatory source_types missing in API JSON ' . json_encode($json_array));
            $this->set_source_types();
        }
        if (array_key_exists(controller::API_LIST_SHARE_TYPES, $json_array)) {
            $this->set_share_types($json_array[controller::API_LIST_SHARE_TYPES]);
        } else {
            log_err('Mandatory share_types missing in API JSON ' . json_encode($json_array));
            $this->set_share_types();
        }
        if (array_key_exists(controller::API_LIST_PROTECTION_TYPES, $json_array)) {
            $this->set_protection_types($json_array[controller::API_LIST_PROTECTION_TYPES]);
        } else {
            log_err('Mandatory protection_types missing in API JSON ' . json_encode($json_array));
            $this->set_protection_types();
        }
        if (array_key_exists(controller::API_LIST_LANGUAGES, $json_array)) {
            $this->set_languages($json_array[controller::API_LIST_LANGUAGES]);
        } else {
            log_err('Mandatory languages missing in API JSON ' . json_encode($json_array));
            $this->set_languages();
        }
        if (array_key_exists(controller::API_LIST_LANGUAGE_FORMS, $json_array)) {
            $this->set_language_forms($json_array[controller::API_LIST_LANGUAGE_FORMS]);
        } else {
            log_err('Mandatory language_forms missing in API JSON ' . json_encode($json_array));
            $this->set_language_forms();
        }
        if (array_key_exists(controller::API_LIST_VERBS, $json_array)) {
            $this->set_verbs($json_array[controller::API_LIST_VERBS]);
        } else {
            log_err('Mandatory verbs missing in API JSON ' . json_encode($json_array));
            $this->set_verbs();
        }
        if (array_key_exists(controller::API_LIST_SYSTEM_VIEWS, $json_array)) {
            $this->set_system_views($json_array[controller::API_LIST_SYSTEM_VIEWS]);
        } else {
            //log_err('Mandatory system_views missing in API JSON ' . json_encode($json_array));
            $this->set_system_views();
        }
        if (array_key_exists(controller::API_LIST_SYS_LOG_STATI, $json_array)) {
            $this->set_sys_log_stati($json_array[controller::API_LIST_SYS_LOG_STATI]);
        } else {
            log_err('Mandatory sys_log_stati missing in API JSON ' . json_encode($json_array));
            $this->set_sys_log_stati();
        }
        if (array_key_exists(controller::API_LIST_JOB_TYPES, $json_array)) {
            $this->set_job_types($json_array[controller::API_LIST_JOB_TYPES]);
        } else {
            log_err('Mandatory job_types missing in API JSON ' . json_encode($json_array));
            $this->set_job_types();
        }
        if (array_key_exists(controller::API_LIST_CHANGE_LOG_ACTIONS, $json_array)) {
            $this->set_change_action_list($json_array[controller::API_LIST_CHANGE_LOG_ACTIONS]);
        } else {
            log_err('Mandatory change_action_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_action_list();
        }
        if (array_key_exists(controller::API_LIST_CHANGE_LOG_TABLES, $json_array)) {
            $this->set_change_table_list($json_array[controller::API_LIST_CHANGE_LOG_TABLES]);
        } else {
            log_err('Mandatory change_table_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_table_list();
        }
        if (array_key_exists(controller::API_LIST_CHANGE_LOG_FIELDS, $json_array)) {
            $this->set_change_field_list($json_array[controller::API_LIST_CHANGE_LOG_FIELDS]);
        } else {
            log_err('Mandatory change_field_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_field_list();
        }
    }

    function set_user_profiles(array $json_array = null): void
    {
        global $html_user_profiles;
        $html_user_profiles = new user_profiles();
        $html_user_profiles->set_obj_from_json_array($json_array);
    }

    function set_phrase_types(array $json_array = null): void
    {
        global $html_phrase_types;
        $html_phrase_types = new phrase_types();
        $html_phrase_types->set_obj_from_json_array($json_array);
    }

    function set_formula_types(array $json_array = null): void
    {
        global $html_formula_types;
        $html_formula_types = new formula_type_list();
        $html_formula_types->set_obj_from_json_array($json_array);
    }

    function set_formula_link_types(array $json_array = null): void
    {
        global $html_formula_link_types;
        $html_formula_link_types = new formula_link_type_list();
        $html_formula_link_types->set_obj_from_json_array($json_array);
    }

    function set_view_types(array $json_array = null): void
    {
        global $html_view_types;
        $html_view_types = new view_type_list();
        $html_view_types->set_obj_from_json_array($json_array);
    }

    function set_component_types(array $json_array = null): void
    {
        global $html_component_types;
        $html_component_types = new component_type_list();
        $html_component_types->set_obj_from_json_array($json_array);
    }

    function set_component_link_types(array $json_array = null): void
    {
        global $html_component_link_types;
        $html_component_link_types = new component_link_type_list();
        $html_component_link_types->set_obj_from_json_array($json_array);
    }

    function set_position_types(array $json_array = null): void
    {
        global $html_position_types;
        $html_position_types = new position_type_list();
        $html_position_types->set_obj_from_json_array($json_array);
    }

    function set_ref_types(array $json_array = null): void
    {
        global $html_ref_types;
        $html_ref_types = new ref_type_list();
        $html_ref_types->set_obj_from_json_array($json_array);
    }

    function set_source_types(array $json_array = null): void
    {
        global $html_source_types;
        $html_source_types = new source_type_list();
        $html_source_types->set_obj_from_json_array($json_array);
    }

    function set_share_types(array $json_array = null): void
    {
        global $html_share_types;
        $html_share_types = new share();
        $html_share_types->set_obj_from_json_array($json_array);
    }

    function set_protection_types(array $json_array = null): void
    {
        global $html_protection_types;
        $html_protection_types = new protection();
        $html_protection_types->set_obj_from_json_array($json_array);
    }

    function set_languages(array $json_array = null): void
    {
        global $html_languages;
        $html_languages = new languages();
        $html_languages->set_obj_from_json_array($json_array);
    }

    function set_language_forms(array $json_array = null): void
    {
        global $html_language_forms;
        $html_language_forms = new language_forms();
        $html_language_forms->set_obj_from_json_array($json_array);
    }

    function set_verbs(array $json_array = null): void
    {
        global $html_verbs;
        $html_verbs = new verbs();
        $html_verbs->set_obj_from_json_array($json_array);
    }

    function set_sys_log_stati(array $json_array = null): void
    {
        global $html_sys_log_stati;
        $html_sys_log_stati = new sys_log_status_list();
        $html_sys_log_stati->set_obj_from_json_array($json_array);
    }

    function set_job_types(array $json_array = null): void
    {
        global $html_job_types;
        $html_job_types = new job_type_list();
        $html_job_types->set_obj_from_json_array($json_array);
    }

    function set_change_action_list(array $json_array = null): void
    {
        global $html_change_action_list;
        $html_change_action_list = new change_action_list();
        $html_change_action_list->set_obj_from_json_array($json_array);
    }

    function set_change_table_list(array $json_array = null): void
    {
        global $html_change_table_list;
        $html_change_table_list = new change_table_list();
        $html_change_table_list->set_obj_from_json_array($json_array);
    }

    function set_change_field_list(array $json_array = null): void
    {
        global $html_change_field_list;
        $html_change_field_list = new change_field_list();
        $html_change_field_list->set_obj_from_json_array($json_array);
    }

    function set_system_views(array $json_array = null): void
    {
        global $html_system_views;
        $html_system_views = new view_list_dsp();
        $html_system_views->set_from_json_array($json_array);
    }

}