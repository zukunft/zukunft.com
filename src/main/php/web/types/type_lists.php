<?php

/*

    web/types/type_list.php - parent object for all preloaded types used in the html frontend
    -----------------------

    TODO move the global vars to vars within the cache
    TODO add a garbage collection
    TODO add a momory usage check


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

include_once TYPES_PATH . 'type_object.php';
include_once TYPES_PATH . 'type_list.php';
include_once TYPES_PATH . 'change_action_list.php';
include_once TYPES_PATH . 'change_table_list.php';
include_once TYPES_PATH . 'change_field_list.php';
include_once TYPES_PATH . 'sys_log_status_list.php';
include_once TYPES_PATH . 'user_profiles.php';
include_once TYPES_PATH . 'job_type_list.php';
include_once TYPES_PATH . 'languages.php';
include_once TYPES_PATH . 'language_forms.php';
include_once TYPES_PATH . 'share.php';
include_once TYPES_PATH . 'protection.php';
include_once TYPES_PATH . 'verbs.php';
include_once TYPES_PATH . 'phrase_types.php';
include_once TYPES_PATH . 'formula_type_list.php';
include_once TYPES_PATH . 'formula_link_type_list.php';
include_once TYPES_PATH . 'source_type_list.php';
include_once TYPES_PATH . 'ref_type_list.php';
include_once TYPES_PATH . 'view_type_list.php';
include_once TYPES_PATH . 'view_link_type_list.php';
include_once TYPES_PATH . 'component_type_list.php';
include_once TYPES_PATH . 'component_link_type_list.php';
include_once TYPES_PATH . 'position_type_list.php';
include_once VIEW_PATH . 'view_list.php';

// get the api const that are shared between the backend and the html frontend
include_once SHARED_PATH . 'api.php';

use html\user\user_message;
use html\view\view_list as view_list_dsp;
use html\word\word as word_dsp;
use shared\api;

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
        $ctrl = new api();
        $json_array = json_decode($json_api_msg, true);
        $type_lists_json = $ctrl->check_api_msg($json_array, api::JSON_TYPE_LISTS);
        $this->set_from_json_array($type_lists_json);
    }

    /**
     * set the vars of this log html object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(api::JSON_LIST_USER_PROFILES, $json_array)) {
            $this->set_user_profiles($json_array[api::JSON_LIST_USER_PROFILES]);
        } else {
            $usr_msg->add_err('Mandatory user profiles missing in API JSON ' . json_encode($json_array));
            $this->set_user_profiles();
        }
        if (array_key_exists(api::JSON_LIST_PHRASE_TYPES, $json_array)) {
            $this->set_phrase_types($json_array[api::JSON_LIST_PHRASE_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory phrase_types missing in API JSON ' . json_encode($json_array));
            $this->set_phrase_types();
        }
        if (array_key_exists(api::JSON_LIST_FORMULA_TYPES, $json_array)) {
            $this->set_formula_types($json_array[api::JSON_LIST_FORMULA_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory formula_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_types();
        }
        if (array_key_exists(api::JSON_LIST_FORMULA_LINK_TYPES, $json_array)) {
            $this->set_formula_link_types($json_array[api::JSON_LIST_FORMULA_LINK_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory formula_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_link_types();
        }
        if (array_key_exists(api::JSON_LIST_VIEW_TYPES, $json_array)) {
            $this->set_view_types($json_array[api::JSON_LIST_VIEW_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory view_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_types();
        }
        if (array_key_exists(api::JSON_LIST_VIEW_LINK_TYPES, $json_array)) {
            $this->set_view_link_types($json_array[api::JSON_LIST_VIEW_LINK_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory view_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_link_types();
        }
        if (array_key_exists(api::JSON_LIST_COMPONENT_TYPES, $json_array)) {
            $this->set_component_types($json_array[api::JSON_LIST_COMPONENT_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory component_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_types();
        }
        /*
        if (array_key_exists(api::JSON_LIST_VIEW_COMPONENT_LINK_TYPES, $json_array)) {
            $this->set_component_link_types($json_array[api::JSON_LIST_VIEW_COMPONENT_LINK_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory component_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_link_types();
        }
        */
        if (array_key_exists(api::JSON_LIST_COMPONENT_POSITION_TYPES, $json_array)) {
            $this->set_position_types($json_array[api::JSON_LIST_COMPONENT_POSITION_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory position_types missing in API JSON ' . json_encode($json_array));
            $this->set_position_types();
        }
        if (array_key_exists(api::JSON_LIST_REF_TYPES, $json_array)) {
            $this->set_ref_types($json_array[api::JSON_LIST_REF_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory ref_types missing in API JSON ' . json_encode($json_array));
            $this->set_ref_types();
        }
        if (array_key_exists(api::JSON_LIST_SOURCE_TYPES, $json_array)) {
            $this->set_source_types($json_array[api::JSON_LIST_SOURCE_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory source_types missing in API JSON ' . json_encode($json_array));
            $this->set_source_types();
        }
        if (array_key_exists(api::JSON_LIST_SHARE_TYPES, $json_array)) {
            $this->set_share_types($json_array[api::JSON_LIST_SHARE_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory share_types missing in API JSON ' . json_encode($json_array));
            $this->set_share_types();
        }
        if (array_key_exists(api::JSON_LIST_PROTECTION_TYPES, $json_array)) {
            $this->set_protection_types($json_array[api::JSON_LIST_PROTECTION_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory protection_types missing in API JSON ' . json_encode($json_array));
            $this->set_protection_types();
        }
        if (array_key_exists(api::JSON_LIST_LANGUAGES, $json_array)) {
            $this->set_languages($json_array[api::JSON_LIST_LANGUAGES]);
        } else {
            $usr_msg->add_err('Mandatory languages missing in API JSON ' . json_encode($json_array));
            $this->set_languages();
        }
        if (array_key_exists(api::JSON_LIST_LANGUAGE_FORMS, $json_array)) {
            $this->set_language_forms($json_array[api::JSON_LIST_LANGUAGE_FORMS]);
        } else {
            $usr_msg->add_err('Mandatory language_forms missing in API JSON ' . json_encode($json_array));
            $this->set_language_forms();
        }
        if (array_key_exists(api::JSON_LIST_VERBS, $json_array)) {
            $this->set_verbs($json_array[api::JSON_LIST_VERBS]);
        } else {
            $usr_msg->add_err('Mandatory verbs missing in API JSON ' . json_encode($json_array));
            $this->set_verbs();
        }
        if (array_key_exists(api::JSON_LIST_SYSTEM_VIEWS, $json_array)) {
            $this->set_system_views($json_array[api::JSON_LIST_SYSTEM_VIEWS]);
        } else {
            //$usr_msg->add_err('Mandatory system_views missing in API JSON ' . json_encode($json_array));
            $this->set_system_views();
        }
        if (array_key_exists(api::JSON_LIST_SYS_LOG_STATI, $json_array)) {
            $this->set_sys_log_stati($json_array[api::JSON_LIST_SYS_LOG_STATI]);
        } else {
            $usr_msg->add_err('Mandatory sys_log_stati missing in API JSON ' . json_encode($json_array));
            $this->set_sys_log_stati();
        }
        if (array_key_exists(api::JSON_LIST_JOB_TYPES, $json_array)) {
            $this->set_job_types($json_array[api::JSON_LIST_JOB_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory job_types missing in API JSON ' . json_encode($json_array));
            $this->set_job_types();
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_ACTIONS, $json_array)) {
            $this->set_change_action_list($json_array[api::JSON_LIST_CHANGE_LOG_ACTIONS]);
        } else {
            $usr_msg->add_err('Mandatory change_action_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_action_list();
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_TABLES, $json_array)) {
            $this->set_change_table_list($json_array[api::JSON_LIST_CHANGE_LOG_TABLES]);
        } else {
            $usr_msg->add_err('Mandatory change_table_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_table_list();
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_FIELDS, $json_array)) {
            $this->set_change_field_list($json_array[api::JSON_LIST_CHANGE_LOG_FIELDS]);
        } else {
            $usr_msg->add_err('Mandatory change_field_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_field_list();
        }
        return $usr_msg;
    }

    function set_user_profiles(array $json_array = null): void
    {
        global $html_user_profiles;
        $html_user_profiles = new user_profiles();
        $html_user_profiles->set_from_json_array($json_array);
    }

    function set_phrase_types(array $json_array = null): void
    {
        global $html_phrase_types;
        $html_phrase_types = new phrase_types();
        $html_phrase_types->set_from_json_array($json_array);
    }

    function set_formula_types(array $json_array = null): void
    {
        global $html_formula_types;
        $html_formula_types = new formula_type_list();
        $html_formula_types->set_from_json_array($json_array);
    }

    function set_formula_link_types(array $json_array = null): void
    {
        global $html_formula_link_types;
        $html_formula_link_types = new formula_link_type_list();
        $html_formula_link_types->set_from_json_array($json_array);
    }

    function set_view_types(array $json_array = null): void
    {
        global $html_view_types;
        $html_view_types = new view_type_list();
        $html_view_types->set_from_json_array($json_array);
    }

    function set_view_link_types(array $json_array = null): void
    {
        global $html_view_link_types;
        $html_view_link_types = new view_link_type_list();
        $html_view_link_types->set_from_json_array($json_array);
    }

    function set_component_types(array $json_array = null): void
    {
        global $html_component_types;
        $html_component_types = new component_type_list();
        $html_component_types->set_from_json_array($json_array);
    }

    function set_component_link_types(array $json_array = null): void
    {
        global $html_component_link_types;
        $html_component_link_types = new component_link_type_list();
        $html_component_link_types->set_from_json_array($json_array);
    }

    function set_position_types(array $json_array = null): void
    {
        global $html_position_types;
        $html_position_types = new position_type_list();
        $html_position_types->set_from_json_array($json_array);
    }

    function set_ref_types(array $json_array = null): void
    {
        global $html_ref_types;
        $html_ref_types = new ref_type_list();
        $html_ref_types->set_from_json_array($json_array);
    }

    function set_source_types(array $json_array = null): void
    {
        global $html_source_types;
        $html_source_types = new source_type_list();
        $html_source_types->set_from_json_array($json_array);
    }

    function set_share_types(array $json_array = null): void
    {
        global $html_share_types;
        $html_share_types = new share();
        $html_share_types->set_from_json_array($json_array);
    }

    function set_protection_types(array $json_array = null): void
    {
        global $html_protection_types;
        $html_protection_types = new protection();
        $html_protection_types->set_from_json_array($json_array);
    }

    function set_languages(array $json_array = null): void
    {
        global $html_languages;
        $html_languages = new languages();
        $html_languages->set_from_json_array($json_array);
    }

    function set_language_forms(array $json_array = null): void
    {
        global $html_language_forms;
        $html_language_forms = new language_forms();
        $html_language_forms->set_from_json_array($json_array);
    }

    function set_verbs(array $json_array = null): void
    {
        global $html_verbs;
        $html_verbs = new verbs();
        $html_verbs->set_from_json_array($json_array);
    }

    function set_sys_log_stati(array $json_array = null): void
    {
        global $html_sys_log_stati;
        $html_sys_log_stati = new sys_log_status_list();
        $html_sys_log_stati->set_from_json_array($json_array);
    }

    function set_job_types(array $json_array = null): void
    {
        global $html_job_types;
        $html_job_types = new job_type_list();
        $html_job_types->set_from_json_array($json_array);
    }

    function set_change_action_list(array $json_array = null): void
    {
        global $html_change_action_list;
        $html_change_action_list = new change_action_list();
        $html_change_action_list->set_from_json_array($json_array);
    }

    function set_change_table_list(array $json_array = null): void
    {
        global $html_change_table_list;
        $html_change_table_list = new change_table_list();
        $html_change_table_list->set_from_json_array($json_array);
    }

    function set_change_field_list(array $json_array = null): void
    {
        global $html_change_field_list;
        $html_change_field_list = new change_field_list();
        $html_change_field_list->set_from_json_array($json_array);
    }

    function set_system_views(array $json_array = null): void
    {
        global $html_system_views;
        $html_system_views = new view_list_dsp();
        $html_system_views->set_from_json_array($json_array);
    }

    // TODO add similar functions for all cache types
    function get_view_by_id(int $id): string
    {
        global $html_system_views;
        $msk = $html_system_views->get_by_id($id);
        $wrd = new word_dsp();
        return $msk->show($wrd);
    }

    function get_view(string $code_id): string
    {
        global $html_system_views;
        $msk = $html_system_views->get_by_id($code_id);
        $wrd = new word_dsp();
        return $msk->show($wrd);
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }


}