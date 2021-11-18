<?php

/*

  db_code_link.php - class that links the upfront loaded type list
  ----------------

  TODO check automatically that all rows with code_id are existing in the database and add any missing rows

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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>

  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>

  http://zukunft.com

*/

class db_cl
{
    // list of all user types that are used
    const SYS_USER = "system_user";
    const WORD_TYPE = "word_type";
    const VERB = "verb";
    const FORMULA_TYPE = "formula_type";
    const FORMULA_LINK_TYPE = "formula_link_type";
    const FORMULA_ELEMENT_TYPE = "formula_element_type";
    const VIEW = "view";
    const VIEW_TYPE = "view_type";
    const VIEW_COMPONENT_TYPE = "view_component_type";
    const REF_TYPE = "ref_type";
    const SHARE_TYPE = "share_type";
    const PROTECTION_TYPE = "protection_type";
    const USER_PROFILE = "user_profile_type";
    const LOG_STATUS = "system_log_status";
    const LOG_TABLE = "change_table";
    const JOB_TYPE = "job_type";

    /**
     * get the database row id for a given code_id
     * mainly used to group the access to the global vars within this class
     *
     * @param string $code_id the code_id string that must only be unique within the type
     * @return int the database row id
     */
    function sys_log_status_id(string $code_id): int
    {
        global $sys_log_stati;
        return $sys_log_stati->id($code_id);
    }

    function sys_usr_id(string $code_id): int
    {
        global $system_users;
        return $system_users->id($code_id);
    }

    function user_profile_id(string $code_id): int
    {
        global $user_profiles;
        return $user_profiles->id($code_id);
    }

    function word_type_id(string $code_id): int
    {
        global $word_types;
        return $word_types->id($code_id);
    }

    function verb_id(string $code_id): int
    {
        global $verbs;
        return $verbs->id($code_id);
    }

    function formula_type_id(string $code_id): int
    {
        global $formula_types;
        return $formula_types->id($code_id);
    }

    function formula_link_type_id(string $code_id): int
    {
        global $formula_link_types;
        return $formula_link_types->id($code_id);
    }

    function formula_element_type_id(string $code_id): int
    {
        global $formula_element_types;
        return $formula_element_types->id($code_id);
    }

    function view_id(string $code_id): int
    {
        global $system_views;
        return $system_views->id($code_id);
    }

    function view_type_id(string $code_id): int
    {
        global $view_types;
        return $view_types->id($code_id);
    }

    function view_component_type_id(string $code_id): int
    {
        global $view_component_types;
        return $view_component_types->id($code_id);
    }

    function ref_type_id(string $code_id): int
    {
        global $ref_types;
        return $ref_types->id($code_id);
    }

    function share_type_id(string $code_id): int
    {
        global $share_types;
        return $share_types->id($code_id);
    }

    function protection_type_id(string $code_id): int
    {
        global $protection_types;
        return $protection_types->id($code_id);
    }

    function job_type_id(string $code_id): int
    {
        global $job_types;
        return $job_types->id($code_id);
    }

    function log_table_id(string $code_id): int
    {
        global $change_log_tables;
        return $change_log_tables->id($code_id);
    }

    /**
     * the type object base on the given database row id
     *
     * @param int $id the database row id
     * @return mixed the type object
     */
    function sys_log_status(int $id)
    {
        global $sys_log_stati;
        return $sys_log_stati->get($id);
    }

    function user_profile(int $id)
    {
        global $user_profiles;
        return $user_profiles->get($id);
    }

    function word_type(int $id)
    {
        global $word_types;
        return $word_types->get_by_id($id);
    }

    function formula_type(int $id)
    {
        global $formula_types;
        return $formula_types->get_by_id($id);
    }

    function formula_link_type(int $id)
    {
        global $formula_link_types;
        return $formula_link_types->get_by_id($id);
    }

    function formula_element_type(int $id)
    {
        global $formula_element_types;
        return $formula_element_types->get_by_id($id);
    }

    function view_type(int $id)
    {
        global $view_types;
        return $view_types->get_by_id($id);
    }

    function view_component_type(int $id)
    {
        global $view_component_types;
        return $view_component_types->get_by_id($id);
    }

    function share_type(int $id)
    {
        global $share_types;
        return $share_types->get_by_id($id);
    }

    function protection_type(int $id)
    {
        global $protection_types;
        return $protection_types->get_by_id($id);
    }

    function job_type(int $id)
    {
        global $job_types;
        return $job_types->get_by_id($id);
    }

    function log_table(int $id)
    {
        global $change_log_tables;
        return $change_log_tables->get($id);
    }

    /**
     * get the user specific name of a database row selected by the database id
     *
     * @param int $id
     * @return string
     */
    function sys_log_status_name(int $id): string
    {
        global $sys_log_stati;
        return $sys_log_stati->name($id);
    }

    function sys_usr_name(int $id): string
    {
        global $system_users;
        return $system_users->name($id);
    }

    function user_profile_name(int $id): string
    {
        global $user_profiles;
        return $user_profiles->name($id);
    }

    function word_type_name(int $id): string
    {
        global $word_types;
        return $word_types->name($id);
    }

    function verb_name(int $id): string
    {
        global $verbs;
        return $verbs->name($id);
    }

    function formula_type_name(int $id): string
    {
        global $formula_types;
        return $formula_types->name($id);
    }

    function formula_link_type_name(int $id): string
    {
        global $formula_link_types;
        return $formula_link_types->name($id);
    }

    function formula_element_type_name(int $id): string
    {
        global $formula_element_types;
        return $formula_element_types->name($id);
    }

    function view_name(int $id): string
    {
        global $system_views;
        return $system_views->name($id);
    }

    function view_type_name(int $id): string
    {
        global $view_types;
        return $view_types->name($id);
    }

    function view_component_type_name(int $id): string
    {
        global $view_component_types;
        return $view_component_types->name($id);
    }

    function ref_type_name(int $id): string
    {
        global $ref_types;
        return $ref_types->name($id);
    }

    function share_type_name(int $id): string
    {
        global $share_types;
        return $share_types->name($id);
    }

    function protection_type_name(int $id): string
    {
        global $protection_types;
        return $protection_types->name($id);
    }

    function job_type_name(int $id): string
    {
        global $job_types;
        return $job_types->name($id);
    }

    function log_table_name(int $id): string
    {
        global $change_log_tables;
        return $change_log_tables->name($id);
    }

}


/**
 * get the database id of a predefined type e.g. word type, formula type, ...
 * shortcut name for db_code_link for better code reading
 * TODO it is more data saving to use get on the global object e.g. $user_profile->get($code_id)
 *
 * @param string $type e.g. word_type or formulas_type to select the list of unique code ids
 * @param string $code_id the code id that must be unique within the given type
 * @return int the database prime key row id
 */
function cl(string $type, string $code_id): int
{
    $result = 0;
    $db_code_link = new db_cl();
    switch ($type) {
        case db_cl::LOG_STATUS:
            $result = $db_code_link->sys_log_status_id($code_id);
            break;
        case db_cl::SYS_USER:
            $result = $db_code_link->sys_usr_id($code_id);
            break;
        case db_cl::USER_PROFILE:
            $result = $db_code_link->user_profile_id($code_id);
            break;
        case db_cl::WORD_TYPE:
            $result = $db_code_link->word_type_id($code_id);
            break;
        case db_cl::VERB:
            $result = $db_code_link->verb_id($code_id);
            break;
        case db_cl::FORMULA_TYPE:
            $result = $db_code_link->formula_type_id($code_id);
            break;
        case db_cl::FORMULA_LINK_TYPE:
            $result = $db_code_link->formula_link_type_id($code_id);
            break;
        case db_cl::FORMULA_ELEMENT_TYPE:
            $result = $db_code_link->formula_element_type_id($code_id);
            break;
        case db_cl::VIEW:
            $result = $db_code_link->view_id($code_id);
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type_id($code_id);
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->view_component_type_id($code_id);
            break;
        case db_cl::REF_TYPE:
            $result = $db_code_link->ref_type_id($code_id);
            break;
        case db_cl::SHARE_TYPE:
            $result = $db_code_link->share_type_id($code_id);
            break;
        case db_cl::PROTECTION_TYPE:
            $result = $db_code_link->protection_type_id($code_id);
            break;
        case db_cl::JOB_TYPE:
            $result = $db_code_link->job_type_id($code_id);
            break;
        case db_cl::LOG_TABLE:
            $result = $db_code_link->log_table_id($code_id);
            break;
    }
    return $result;
}

/**
 * get the user specific name of a code linked database row
 * e.g. cl_name(db_cl::)
 *
 * @param string $type
 * @param int $id
 * @return string the user specific name of the type
 */
function cl_name(string $type, int $id): string
{
    $result = '';
    $db_code_link = new db_cl();
    switch ($type) {
        case db_cl::LOG_STATUS:
            $result = $db_code_link->sys_log_status_name($id);
            break;
        case db_cl::SYS_USER:
            $result = $db_code_link->sys_usr_name($id);
            break;
        case db_cl::USER_PROFILE:
            $result = $db_code_link->user_profile_name($id);
            break;
        case db_cl::WORD_TYPE:
            $result = $db_code_link->word_type_name($id);
            break;
        case db_cl::VERB:
            $result = $db_code_link->verb_name($id);
            break;
        case db_cl::FORMULA_TYPE:
            $result = $db_code_link->formula_type_name($id);
            break;
        case db_cl::FORMULA_LINK_TYPE:
            $result = $db_code_link->formula_link_type_name($id);
            break;
        case db_cl::FORMULA_ELEMENT_TYPE:
            $result = $db_code_link->formula_element_type_name($id);
            break;
        case db_cl::VIEW:
            $result = $db_code_link->view_name($id);
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type_name($id);
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->view_component_type_name($id);
            break;
        case db_cl::REF_TYPE:
            $result = $db_code_link->ref_type_name($id);
            break;
        case db_cl::SHARE_TYPE:
            $result = $db_code_link->share_type_name($id);
            break;
        case db_cl::PROTECTION_TYPE:
            $result = $db_code_link->protection_type_name($id);
            break;
        case db_cl::JOB_TYPE:
            $result = $db_code_link->job_type_name($id);
            break;
        case db_cl::LOG_TABLE:
            $result = $db_code_link->log_table_name($id);
            break;
    }
    return $result;
}

/**
 * get a predefined type object e.g. word type, formula type, ...
 *
 * @param string $type e.g. word_type or formulas_type to select the list of unique code ids
 * @param string $code_id the code id that must be unique within the given type
 * @return user_type the loaded type object
 */
function get_type(string $type, string $code_id): user_type
{
    $result = null;
    $db_code_link = new db_cl();
    switch ($type) {
        case db_cl::LOG_STATUS:
            $result = $db_code_link->sys_log_status($db_code_link->sys_log_status_id($code_id));
            break;
        case db_cl::USER_PROFILE:
            $result = $db_code_link->user_profile($db_code_link->user_profile_id($code_id));
            break;
        case db_cl::WORD_TYPE:
            $result = $db_code_link->word_type($db_code_link->word_type_id($code_id));
            break;
        /* switched off, because it returns an extended object
        case db_cl::VERB:
            $result = $db_code_link->verb($db_code_link->verb_id($code_id));
            break;
        case db_cl::VIEW:
            $result = $db_code_link->view($db_code_link->view_type_id($code_id));
            break;
        */
        case db_cl::FORMULA_TYPE:
            $result = $db_code_link->formula_type($db_code_link->formula_type_id($code_id));
            break;
        case db_cl::FORMULA_LINK_TYPE:
            $result = $db_code_link->formula_link_type($db_code_link->formula_link_type_id($code_id));
            break;
        case db_cl::FORMULA_ELEMENT_TYPE:
            $result = $db_code_link->formula_element_type($db_code_link->formula_element_type_id($code_id));
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type($db_code_link->view_type_id($code_id));
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->view_component_type($db_code_link->view_component_type_id($code_id));
            break;
        // db_cl::REF_TYPE is excluded here because it returns an extended object
        case db_cl::SHARE_TYPE:
            $result = $db_code_link->share_type($db_code_link->share_type_id($code_id));
            break;
        case db_cl::PROTECTION_TYPE:
            $result = $db_code_link->protection_type($db_code_link->protection_type_id($code_id));
            break;
        case db_cl::JOB_TYPE:
            $result = $db_code_link->job_type($db_code_link->job_type_id($code_id));
            break;
        case db_cl::LOG_TABLE:
            $result = $db_code_link->log_table($db_code_link->log_table_id($code_id));
            break;
    }
    return $result;
}
