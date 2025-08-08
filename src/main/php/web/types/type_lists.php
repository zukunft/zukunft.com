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

use cfg\const\paths;
use html\const\paths as html_paths;
use html\user\user_message;
use html\verb\verb;
use html\view\view;
use html\view\view_list as view_list_dsp;
use html\word\word as word_dsp;
use shared\api;
use shared\json_fields;

include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'change_action_list.php';
include_once html_paths::TYPES . 'change_table_list.php';
include_once html_paths::TYPES . 'change_field_list.php';
include_once html_paths::TYPES . 'sys_log_status_list.php';
include_once html_paths::TYPES . 'user_profile.php';
include_once html_paths::TYPES . 'job_type_list.php';
include_once html_paths::TYPES . 'languages.php';
include_once html_paths::TYPES . 'language_forms.php';
include_once html_paths::TYPES . 'share.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::TYPES . 'verbs.php';
include_once html_paths::TYPES . 'phrase_types.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'formula_link_type_list.php';
include_once html_paths::TYPES . 'source_type_list.php';
include_once html_paths::TYPES . 'ref_type_list.php';
include_once html_paths::TYPES . 'view_type_list.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::TYPES . 'view_link_type_list.php';
include_once html_paths::TYPES . 'component_type_list.php';
include_once html_paths::TYPES . 'component_link_type_list.php';
include_once html_paths::TYPES . 'position_type_list.php';
//include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

// get the api const that are shared between the backend and the html frontend
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

class type_lists
{

    /*
     * object vars
     */

    public ?user_profile $html_user_profiles = null;
    public ?phrase_types $html_phrase_types = null;
    public ?formula_type_list $html_formula_types = null;
    public ?formula_link_type_list $html_formula_link_types = null;
    public ?view_type_list $html_view_types = null;
    public ?view_style_list $html_view_styles = null;
    public ?view_link_type_list $html_view_link_types = null;
    public ?component_type_list $html_component_types = null;
    public ?component_link_type_list $html_component_link_types = null;
    public ?position_type_list $html_position_types = null;
    public ?source_type_list $html_source_types = null;
    public ?ref_type_list $html_ref_types = null;
    public ?share $html_share_types = null;
    public ?protection $html_protection_types = null;
    public ?languages $html_languages = null;
    public ?language_forms $html_language_forms = null;
    public ?verbs $html_verbs = null;
    public ?sys_log_status_list $html_sys_log_statuus = null;
    public ?job_type_list $html_job_types = null;
    public ?change_action_list $html_change_action_list = null;
    public ?change_table_list $html_change_table_list = null;
    public ?change_field_list $html_change_field_list = null;
    public ?view_list_dsp $html_system_views = null;


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
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        $ctrl = new api();
        $json_array = json_decode($json_api_msg, true);
        $type_lists_json = $ctrl->check_api_msg($json_array, json_fields::BODY);
        return $this->set_from_json_array($type_lists_json);
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
        if (array_key_exists(api::JSON_LIST_VIEW_STYLES, $json_array)) {
            $this->set_view_styles($json_array[api::JSON_LIST_VIEW_STYLES]);
        } else {
            $usr_msg->add_err('Mandatory view_styles missing in API JSON ' . json_encode($json_array));
            $this->set_view_styles();
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
         * TODO Prio 1 activate
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
        if (array_key_exists(api::JSON_LIST_SOURCE_TYPES, $json_array)) {
            $this->set_source_types($json_array[api::JSON_LIST_SOURCE_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory source_types missing in API JSON ' . json_encode($json_array));
            $this->set_source_types();
        }
        if (array_key_exists(api::JSON_LIST_REF_TYPES, $json_array)) {
            $this->set_ref_types($json_array[api::JSON_LIST_REF_TYPES]);
        } else {
            $usr_msg->add_err('Mandatory ref_types missing in API JSON ' . json_encode($json_array));
            $this->set_ref_types();
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
        if (array_key_exists(api::JSON_LIST_SYS_LOG_STATUUS, $json_array)) {
            $this->set_sys_log_statuus($json_array[api::JSON_LIST_SYS_LOG_STATUUS]);
        } else {
            $usr_msg->add_err('Mandatory sys_log_statuus missing in API JSON ' . json_encode($json_array));
            $this->set_sys_log_statuus();
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
        $this->html_user_profiles = new user_profile();
        $this->html_user_profiles->set_from_json_array($json_array);
    }

    function set_phrase_types(array $json_array = null): void
    {
        $this->html_phrase_types = new phrase_types();
        $this->html_phrase_types->set_from_json_array($json_array);
    }

    function set_formula_types(array $json_array = null): void
    {
        $this->html_formula_types = new formula_type_list();
        $this->html_formula_types->set_from_json_array($json_array);
    }

    function set_formula_link_types(array $json_array = null): void
    {
        $this->html_formula_link_types = new formula_link_type_list();
        $this->html_formula_link_types->set_from_json_array($json_array);
    }

    function set_view_types(array $json_array = null): void
    {
        $this->html_view_types = new view_type_list();
        $this->html_view_types->set_from_json_array($json_array);
    }

    function set_view_styles(array $json_array = null): void
    {
        $this->html_view_styles = new view_style_list();
        $this->html_view_styles->set_from_json_array($json_array);
    }

    function set_view_link_types(array $json_array = null): void
    {
        $this->html_view_link_types = new view_link_type_list();
        $this->html_view_link_types->set_from_json_array($json_array);
    }

    function set_component_types(array $json_array = null): void
    {
        $this->html_component_types = new component_type_list();
        $this->html_component_types->set_from_json_array($json_array);
    }

    function set_component_link_types(array $json_array = null): void
    {
        $this->html_component_link_types = new component_link_type_list();
        $this->html_component_link_types->set_from_json_array($json_array);
    }

    function set_position_types(array $json_array = null): void
    {
        $this->html_position_types = new position_type_list();
        $this->html_position_types->set_from_json_array($json_array);
    }

    function set_source_types(array $json_array = null): void
    {
        $this->html_source_types = new source_type_list();
        $this->html_source_types->set_from_json_array($json_array);
    }

    function set_ref_types(array $json_array = null): void
    {
        $this->html_ref_types = new ref_type_list();
        $this->html_ref_types->set_from_json_array($json_array);
    }

    function set_share_types(array $json_array = null): void
    {
        $this->html_share_types = new share();
        $this->html_share_types->set_from_json_array($json_array);
    }

    function set_protection_types(array $json_array = null): void
    {
        $this->html_protection_types = new protection();
        $this->html_protection_types->set_from_json_array($json_array);
    }

    function set_languages(array $json_array = null): void
    {
        $this->html_languages = new languages();
        $this->html_languages->set_from_json_array($json_array);
    }

    function set_language_forms(array $json_array = null): void
    {
        $this->html_language_forms = new language_forms();
        $this->html_language_forms->set_from_json_array($json_array);
    }

    function set_verbs(array $json_array = null): void
    {
        $this->html_verbs = new verbs();
        $this->html_verbs->set_from_json_array($json_array, verb::class);
    }

    function set_sys_log_statuus(array $json_array = null): void
    {
        $this->html_sys_log_statuus = new sys_log_status_list();
        $this->html_sys_log_statuus->set_from_json_array($json_array);
    }

    function set_job_types(array $json_array = null): void
    {
        $this->html_job_types = new job_type_list();
        $this->html_job_types->set_from_json_array($json_array);
    }

    function set_change_action_list(array $json_array = null): void
    {
        $this->html_change_action_list = new change_action_list();
        $this->html_change_action_list->set_from_json_array($json_array);
    }

    function set_change_table_list(array $json_array = null): void
    {
        $this->html_change_table_list = new change_table_list();
        $this->html_change_table_list->set_from_json_array($json_array);
    }

    function set_change_field_list(array $json_array = null): void
    {
        $this->html_change_field_list = new change_field_list();
        $this->html_change_field_list->set_from_json_array($json_array);
    }

    function set_system_views(array $json_array = null): void
    {
        $this->html_system_views = new view_list_dsp();
        $this->html_system_views->api_mapper($json_array);
    }

    // TODO add similar functions for all cache types
    function get_html_by_id(int $id): string
    {
        $msk = $this->get_view_by_id($id);
        $wrd = new word_dsp();
        return $msk->show($wrd);
    }

    function get_view_by_id(int $id): view
    {
        return $this->html_system_views->get_by_id($id);
    }

    function get_view(string $code_id): view
    {
        return $this->html_system_views->get_by_id($code_id);
    }
    function get_html(string $code_id): string
    {
        $msk = $this->get_view($code_id);
        $wrd = new word_dsp();
        return $msk->show($wrd);
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }


}