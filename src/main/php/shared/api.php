<?php

/*

    shared/api.php - constants used for the backend to frontend api of zukunft.com
    --------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared;

use Zukunft\ZukunftCom\main\php\shared\const\views;

class api
{

    /*
     * URL
     */

    const string HOST_SAME = '/';

    // to select the configuration part that should be updated in the frontend e.g. all, frontend or user
    const string CONFIG_ALL = 'all';
    const string CONFIG_FRONTEND = 'frontend';
    const string CONFIG_USER = 'user';

    // the url name of the main script that is used in combination with the host url
    const string SCRIPT_PATH_NAME = 'http' . DIRECTORY_SEPARATOR;
    const string SCRIPT_PATH = DIRECTORY_SEPARATOR . self::SCRIPT_PATH_NAME;
    const string SETTINGS_REL = DIRECTORY_SEPARATOR . 'settings';
    const string MAIN_SCRIPT_EXT = self::SCRIPT_PATH_NAME . 'view.php';
    const string MAIN_SCRIPT = self::SCRIPT_PATH . 'view.php';
    const string ABOUT_SCRIPT = self::SCRIPT_PATH . 'about.php';
    const string PRIVACY_SCRIPT = self::SCRIPT_PATH . 'privacy_policy.html';
    const string FIND_SCRIPT = self::SCRIPT_PATH . 'find.php';
    const string LOGIN_SCRIPT = self::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . views::LOGIN_ID;
    const string SIGNUP_SCRIPT = self::SCRIPT_PATH . 'signup.php';
    const string ERROR_LOG_SCRIPT = self::SCRIPT_PATH . 'error_log.php';
    const string URL_DEV = THIS_URL . api::MAIN_SCRIPT_EXT . url_var::PAR . url_var::MASK . url_var::EQ;


    /*
     * JSON
     */

    // json field names of the api json messages
    const string JSON_BODY = 'body';
    const string JSON_BODY_SYS_LOG = 'sys_log';

    // to include the objects that should be displayed in one api message
    const string JSON_WORD = 'word';
    const string JSON_TRIPLE = 'triple';

    //
    const string JSON_TYPE_API_CONFIG = 'api_config';
    const string JSON_TYPE_LISTS = 'type_lists';
    const string JSON_LIST_USER_PROFILES = 'user_profiles';
    const string JSON_LIST_PHRASE_TYPES = 'phrase_types';
    const string JSON_LIST_FORMULA_TYPES = 'formula_types';
    const string JSON_LIST_FORMULA_LINK_TYPES = 'formula_link_types';
    const string JSON_LIST_ELEMENT_TYPES = 'element_types';
    const string JSON_LIST_VIEW_TYPES = 'view_types';
    const string JSON_LIST_VIEW_STYLES = 'view_styles';
    const string JSON_LIST_VIEW_LINK_TYPES = 'view_link_types';
    const string JSON_LIST_VIEW_RELATION_TYPES = 'view_relation_types';
    const string JSON_LIST_COMPONENT_TYPES = 'component_types';
    // const string JSON_LIST_COMPONENT_LINK_TYPES = 'component_link_types';
    const string JSON_LIST_COMPONENT_POSITION_TYPES = 'position_types';
    const string JSON_LIST_REF_TYPES = 'ref_types';
    const string JSON_LIST_SOURCE_TYPES = 'source_types';
    const string JSON_LIST_SHARE_TYPES = 'share_types';
    const string JSON_LIST_PROTECTION_TYPES = 'protection_types';
    const string JSON_LIST_LANGUAGES = 'languages';
    const string JSON_LIST_LANGUAGE_FORMS = 'language_forms';
    const string JSON_LIST_SYS_LOG_STATUUS = 'sys_log_statuum';
    const string JSON_LIST_JOB_TYPES = 'job_types';
    const string JSON_LIST_CHANGE_LOG_ACTIONS = 'change_action_list';
    const string JSON_LIST_CHANGE_LOG_TABLES = 'change_table_list';
    const string JSON_LIST_CHANGE_LOG_FIELDS = 'change_field_list';
    const string JSON_LIST_VERBS = 'verbs';
    const string JSON_LIST_SYSTEM_VIEWS = 'system_views';
    const string JSON_LIST_PHRASE_IDS = 'phrase_ids';


    /*
     * fields
     */

    // TODO review
    // to include the objects that should be displayed in one api message
    const string API_WORD = 'word';
    const string API_TRIPLE = 'triple';

    const string DSP_VIEW_ADD = "view_add";
    const string DSP_VIEW_EDIT = "view_edit";
    const string DSP_VIEW_DEL = "view_del";
    const string DSP_COMPONENT_ADD = "component_add";
    const string DSP_COMPONENT_EDIT = "component_edit";
    const string DSP_COMPONENT_DEL = "component_del";
    const string DSP_COMPONENT_LINK = "component_link";
    const string DSP_COMPONENT_UNLINK = "component_unlink";

    /**
     * check if an api message is fine
     * @param array $api_msg the complete api message including the header and in some cases several body parts
     * @param string $body_key to select a body part of the api message
     * @return array the message body if everything has been fine or an empty array
     */
    function check_api_msg(array $api_msg, string $body_key = api::JSON_BODY): array
    {
        $msg_ok = true;
        $body = array();
        // TODO check transfer time
        // TODO check if version matches
        if ($msg_ok) {
            if (array_key_exists($body_key, $api_msg)) {
                $body = $api_msg[$body_key];
            } else {
                // TODO Prio 3 activate next line and avoid these cases
                // $msg_ok = false;
                $body = $api_msg;
                log_warning('message header missing in api message');
            }
        }
        if ($msg_ok) {
            return $body;
        } else {
            return array();
        }
    }

    /**
     * create the base url
     *
     * @param string $class the name of the class that should be requested from the backend
     * @return string the base url for the backend request
     */
    function class_to_url(string $class): string
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        return THIS_URL . url_var::API_PATH . $lib->camelize_ex_1($class);
    }

}
