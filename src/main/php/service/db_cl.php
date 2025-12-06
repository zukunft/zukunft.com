<?php

/*

  model/service/db_cl.php - code link class that links the upfront loaded type list
  -----------------------

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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>

  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>

  http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\service;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class db_cl
{
    // list of all user types that are used
    const string SYS_USER = "system_user";
    const string PHRASE_TYPE = "phrase_type";
    const string VERB = "verb";
    const string FORMULA_TYPE = "formula_type";
    const string FORMULA_LINK_TYPE = "formula_link_type";
    const string ELEMENT_TYPE = "element_type";
    const string VIEW = "view";
    const string VIEW_TYPE = "view_type";
    const string VIEW_COMPONENT_TYPE = "component_type";
    const string VIEW_COMPONENT_LINK_TYPE = "component_link_type";
    const string VIEW_COMPONENT_POS_TYPE = "position_type";
    const string REF_TYPE = "ref_type";
    const string SOURCE_TYPE = "source_type";
    const string SHARE_TYPE = "share_type";
    const string PROTECTION_TYPE = "protection_type";
    const string LANGUAGE = "language";
    const string LANGUAGE_FORM = "language_form";
    const string USER_PROFILE = "user_profile_type";
    const string LOG_STATUS = "sys_log_status";
    const string LOG_ACTION = "change_action";
    const string LOG_TABLE = "change_table";
    const string LOG_FIELD = "change_field";
    const string JOB_TYPE = "job_type";

    /**
     * get the database row id for a given code_id
     * mainly used to group the access to the global vars within this class
     *
     * @param string $code_id the code_id string that must only be unique within the type
     * @return int the database row id
     */
    function sys_log_status_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->sys_log_sta->id($code_id);
    }

    function sys_usr_id(string $code_id): int
    {
        global $sys;
        return $sys->usr_sys->id($code_id);
    }

    function user_profile_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->usr_pro->id($code_id);
    }

    function phrase_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->phr_typ->id($code_id);
    }

    function verb_id(string $code_id): int
    {
        global $sys;
        global $sys;
        return $sys->typ_lst->vrb->id($code_id);
    }

    function formula_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->frm_typ->id($code_id);
    }

    function formula_link_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->frm_lnk_typ->id($code_id);
    }

    function element_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->elm_typ->id($code_id);
    }

    function view_id(string $code_id): int
    {
        global $sys;
        global $sys_msk_cac;
        return $sys_msk_cac->id($code_id);
    }

    function view_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->msk_typ->id($code_id);
    }

    function component_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->id($code_id);
    }

    function component_link_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cmp_lnk_typ->id($code_id);
    }

    function component_pos_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->pos_typ->id($code_id);
    }

    function ref_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->ref_typ->id($code_id);
    }

    function source_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->src_typ->id($code_id);
    }

    function share_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->shr_typ->id($code_id);
    }

    function protection_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->ptc_typ->id($code_id);
    }

    function language_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->lan->id($code_id);
    }

    function language_form_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->lan_for->id($code_id);
    }

    function job_type_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->job_typ->id($code_id);
    }

    function log_action_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cng_act->id($code_id);
    }

    function log_table_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cng_tbl->id($code_id);
    }

    function log_field_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->cng_fld->id($code_id);
    }

    /**
     * the type object base on the given database row id
     *
     * @param int $id the database row id
     * @return type_object|null the type object
     */
    function sys_log_status(int $id): ?type_object
    {
        global $sys;
        return $sys->typ_lst->sys_log_sta->get($id);
    }

    function user_profile(int $id)
    {
        global $sys;
        return $sys->typ_lst->usr_pro->get($id);
    }

    function phrase_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->phr_typ->get_by_id($id);
    }

    function formula_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->frm_typ->get_by_id($id);
    }

    function formula_link_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->frm_lnk_typ->get_by_id($id);
    }

    function element_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->elm_typ->get_by_id($id);
    }

    function view_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->msk_typ->get_by_id($id);
    }

    function component_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->get_by_id($id);
    }

    function component_link_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->cmp_lnk_typ->get_by_id($id);
    }

    function component_pos_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->pos_typ->get_by_id($id);
    }

    function share_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->shr_typ->get_by_id($id);
    }

    function protection_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->ptc_typ->get_by_id($id);
    }

    function language(int $id)
    {
        global $sys;
        return $sys->typ_lst->lan->get_by_id($id);
    }

    function language_form(int $id)
    {
        global $sys;
        return $sys->typ_lst->lan_for->get_by_id($id);
    }

    function job_type(int $id)
    {
        global $sys;
        return $sys->typ_lst->job_typ->get_by_id($id);
    }

    function log_action(int $id)
    {
        global $sys;
        return $sys->typ_lst->cng_act->get($id);
    }

    function log_table(int $id)
    {
        global $sys;
        return $sys->typ_lst->cng_tbl->get($id);
    }

    function log_field(int $id)
    {
        global $sys;
        return $sys->typ_lst->cng_fld->get($id);
    }

    /**
     * get the user specific name of a database row selected by the database id
     *
     * @param int $id
     * @return string
     */
    function sys_log_status_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->sys_log_sta->name($id);
    }

    function sys_usr_name(int $id): string
    {
        global $sys;
        return $sys->usr_sys->name($id);
    }

    function user_profile_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->usr_pro->name($id);
    }

    function phrase_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->phr_typ->name($id);
    }

    function verb_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->vrb->name($id);
    }

    function formula_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->frm_typ->name($id);
    }

    function formula_link_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->frm_lnk_typ->name($id);
    }

    function element_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->elm_typ->name($id);
    }

    function view_name(int $id): string
    {
        global $sys_msk_cac;
        return $sys_msk_cac->name($id);
    }

    function view_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->msk_typ->name($id);
    }

    function component_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->cmp_typ->name($id);
    }

    function component_link_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->cmp_lnk_typ->name($id);
    }

    function component_pos_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->pos_typ->name($id);
    }

    function ref_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->ref_typ->name($id);
    }

    function source_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->src_typ->name($id);
    }

    function share_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->shr_typ->name($id);
    }

    function protection_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->ptc_typ->name($id);
    }

    function language_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->lan->name($id);
    }

    function language_form_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->lan_for->name($id);
    }

    function job_type_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->job_typ->name($id);
    }

    function log_action_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->cng_act->name($id);
    }

    function log_table_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->cng_tbl->name($id);
    }

    function log_field_name(int $id): string
    {
        global $sys;
        return $sys->typ_lst->cng_fld->name($id);
    }

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
        case db_cl::PHRASE_TYPE:
            $result = $db_code_link->phrase_type_name($id);
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
        case db_cl::ELEMENT_TYPE:
            $result = $db_code_link->element_type_name($id);
            break;
        case db_cl::VIEW:
            $result = $db_code_link->view_name($id);
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type_name($id);
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->component_type_name($id);
            break;
        case db_cl::VIEW_COMPONENT_LINK_TYPE:
            $result = $db_code_link->component_link_type_name($id);
            break;
        case db_cl::VIEW_COMPONENT_POS_TYPE:
            $result = $db_code_link->component_pos_type_name($id);
            break;
        case db_cl::REF_TYPE:
            $result = $db_code_link->ref_type_name($id);
            break;
        case db_cl::SOURCE_TYPE:
            $result = $db_code_link->source_type_name($id);
            break;
        case db_cl::SHARE_TYPE:
            $result = $db_code_link->share_type_name($id);
            break;
        case db_cl::PROTECTION_TYPE:
            $result = $db_code_link->protection_type_name($id);
            break;
        case db_cl::LANGUAGE:
            $result = $db_code_link->language_name($id);
            break;
        case db_cl::LANGUAGE_FORM:
            $result = $db_code_link->language_form_name($id);
            break;
        case db_cl::JOB_TYPE:
            $result = $db_code_link->job_type_name($id);
            break;
        case db_cl::LOG_ACTION:
            $result = $db_code_link->log_action_name($id);
            break;
        case db_cl::LOG_TABLE:
            $result = $db_code_link->log_table_name($id);
            break;
        case db_cl::LOG_FIELD:
            $result = $db_code_link->log_field_name($id);
            break;
    }
    return $result;
}

/**
 * get a predefined type object e.g. word type, formula type, ...
 *
 * @param string $type e.g. phrase_type or formulas_type to select the list of unique code ids
 * @param string $code_id the code id that must be unique within the given type
 * @return type_object the loaded type object
 */
function get_type(string $type, string $code_id): type_object
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
        case db_cl::PHRASE_TYPE:
            $result = $db_code_link->phrase_type($db_code_link->phrase_type_id($code_id));
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
        case db_cl::ELEMENT_TYPE:
            $result = $db_code_link->element_type($db_code_link->element_type_id($code_id));
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type($db_code_link->view_type_id($code_id));
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->component_type($db_code_link->component_type_id($code_id));
            break;
        case db_cl::VIEW_COMPONENT_LINK_TYPE:
            $result = $db_code_link->component_link_type($db_code_link->component_link_type_id($code_id));
            break;
        case db_cl::VIEW_COMPONENT_POS_TYPE:
            $result = $db_code_link->component_pos_type($db_code_link->component_pos_type_id($code_id));
            break;
        // db_cl::REF_TYPE is excluded here because it returns an extended object
        // db_cl::SOURCE_TYPE is excluded here because it returns an extended object
        case db_cl::SHARE_TYPE:
            $result = $db_code_link->share_type($db_code_link->share_type_id($code_id));
            break;
        case db_cl::PROTECTION_TYPE:
            $result = $db_code_link->protection_type($db_code_link->protection_type_id($code_id));
            break;
        case db_cl::LANGUAGE:
            $result = $db_code_link->language($db_code_link->language_id($code_id));
            break;
        case db_cl::LANGUAGE_FORM:
            $result = $db_code_link->language_form($db_code_link->language_form_id($code_id));
            break;
        case db_cl::JOB_TYPE:
            $result = $db_code_link->job_type($db_code_link->job_type_id($code_id));
            break;
        case db_cl::LOG_ACTION:
            $result = $db_code_link->log_action($db_code_link->log_action_id($code_id));
            break;
        case db_cl::LOG_TABLE:
            $result = $db_code_link->log_table($db_code_link->log_table_id($code_id));
            break;
        case db_cl::LOG_FIELD:
            $result = $db_code_link->log_field($db_code_link->log_field_id($code_id));
            break;
    }
    return $result;
}
