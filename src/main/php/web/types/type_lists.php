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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\web\types;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\def;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

//include_once html_paths::COMPONENT . 'component.php';
//include_once html_paths::CONST . 'def.php';
//include_once html_paths::FORMULA . 'formula.php';
//include_once html_paths::REF . 'ref.php';
//include_once html_paths::REF . 'source.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'change_action_list.php';
include_once html_paths::TYPES . 'change_table_list.php';
include_once html_paths::TYPES . 'change_field_list.php';
include_once html_paths::TYPES . 'sys_log_status_list.php';
include_once html_paths::TYPES . 'user_profile.php';
include_once html_paths::TYPES . 'job_type_list.php';
include_once html_paths::TYPES . 'language_list.php';
include_once html_paths::TYPES . 'language_form_list.php';
include_once html_paths::TYPES . 'share.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::TYPES . 'verbs.php';
include_once html_paths::TYPES . 'phrase_type_list.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'formula_link_type_list.php';
include_once html_paths::TYPES . 'source_type_list.php';
include_once html_paths::TYPES . 'ref_type_list.php';
include_once html_paths::TYPES . 'view_type_list.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::TYPES . 'view_link_type_list.php';
include_once html_paths::TYPES . 'view_relation_type_list.php';
include_once html_paths::TYPES . 'component_type_list.php';
include_once html_paths::TYPES . 'component_link_type_list.php';
include_once html_paths::TYPES . 'position_type_list.php';
//include_once html_paths::VERB . 'verb.php';
//include_once html_paths::VIEW . 'view.php';
//include_once html_paths::VIEW . 'view_list.php';
//include_once html_paths::WORD . 'triple.php';
//include_once html_paths::WORD . 'word.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'source_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'ref_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'view_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'component_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase as phrase_cfg;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\ref_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\view_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\component_fields;

class type_lists
{

    /*
     * object vars
     */

    public ?user_profile $usr_pro = null;
    public ?phrase_type_list $phr_typ = null;
    public ?formula_type_list $frm_typ = null;
    public ?formula_link_type_list $frm_lnk_typ = null;
    public ?view_type_list $msk_typ = null;
    public ?view_style_list $msk_sty = null;
    public ?view_link_type_list $msk_lnk_typ = null;
    public ?view_relation_type_list $mrl_typ = null;
    public ?component_type_list $cmp_typ = null;
    public ?component_link_type_list $cmp_lnk_typ = null;
    public ?position_type_list $pos_typ = null;
    public ?source_type_list $src_typ = null;
    public ?ref_type_list $ref_typ = null;
    public ?share $shr_typ = null;
    public ?protection $ptc_typ = null;
    public ?language_list $lan = null;
    public ?language_form_list $lan_for = null;
    public ?verbs $vrb = null;
    public ?sys_log_status_list $sys_log_sta = null;
    public ?job_type_list $job_typ = null;
    public ?change_action_list $cng_act = null;
    public ?change_table_list $cng_tbl = null;
    public ?change_field_list $cng_fld = null;
    public ?view_list_ui $msk_sys = null;


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
     * type list by class
     */

    /**
     * get the type list related to a given object class
     * similar to cfg type_list::class_to_type_object but returning the cached frontend type list
     * e.g. the phrase type list for a word or triple
     *
     * @param string $class the class name of the object whose type list is requested e.g. word::class
     * @return type_list|null the matching type list or null with an error if the object has no type list
     */
    function class_to_type_list(string $class): ?type_list
    {
        if (in_array($class, def::TYPE_CLASSES)) {
            return match ($class) {
                word_ui::class, triple::class => $this->phr_typ,
                source::class => $this->src_typ,
                ref::class => $this->ref_typ,
                formula::class => $this->frm_typ,
                view::class => $this->msk_typ,
                component::class => $this->cmp_typ,
                default => $this->no_type_list($class),
            };
        } else {
            return null;
        }
    }

    /**
     * map a type-id db field name to its preloaded type list so a caller can show the type name instead of the id
     * e.g. the share field to the share type list for the confirm-change preview
     *
     * @param string $db_fld the database field name e.g. fields::FLD_SHARE
     * @return type_list|null the matching type list or null if the field does not carry a type id
     */
    function field_to_type_list(string $db_fld): ?type_list
    {
        $result = match ($db_fld) {
            fields::FLD_SHARE => $this->shr_typ,
            fields::FLD_PROTECT => $this->ptc_typ,
            fields::FLD_STYLE => $this->msk_sty,
            phrase_cfg::FLD_TYPE => $this->phr_typ,
            source_fields::FLD_TYPE => $this->src_typ,
            ref_fields::FLD_TYPE => $this->ref_typ,
            formula_fields::FLD_TYPE => $this->frm_typ,
            view_fields::FLD_TYPE => $this->msk_typ,
            component_fields::FLD_TYPE => $this->cmp_typ,
            default => null,
        };
        return $result;
    }

    /**
     * log an error that the given object class does not have a type list and return null
     * @param string $class the class name of the object that does not have a type list
     * @return type_list|null always null
     */
    private function no_type_list(string $class): ?type_list
    {
        log_err('no type list defined for the class ' . $class);
        return null;
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
        if ($json_array == null) {
            $msg = new user_message();
            $msg->add(msg_id::API_MESSAGE_EMPTY, [
                msg_id::VAR_REQUEST => 'type_lists'
            ]);
            return $msg;
        } else {
            $type_lists_json = $ctrl->check_api_msg($json_array, json_fields::BODY);
            return $this->set_from_json_array($type_lists_json);
        }
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
            $usr_msg->add_error_text('Mandatory user profiles missing in API JSON ' . json_encode($json_array));
            $this->set_user_profiles([]);
        }
        if (array_key_exists(api::JSON_LIST_PHRASE_TYPES, $json_array)) {
            $this->set_phrase_types($json_array[api::JSON_LIST_PHRASE_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory phrase_types missing in API JSON ' . json_encode($json_array));
            $this->set_phrase_types([]);
        }
        if (array_key_exists(api::JSON_LIST_FORMULA_TYPES, $json_array)) {
            $this->set_formula_types($json_array[api::JSON_LIST_FORMULA_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory formula_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_types([]);
        }
        if (array_key_exists(api::JSON_LIST_FORMULA_LINK_TYPES, $json_array)) {
            $this->set_formula_link_types($json_array[api::JSON_LIST_FORMULA_LINK_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory formula_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_formula_link_types([]);
        }
        if (array_key_exists(api::JSON_LIST_VIEW_TYPES, $json_array)) {
            $this->set_view_types($json_array[api::JSON_LIST_VIEW_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory view_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_types([]);
        }
        if (array_key_exists(api::JSON_LIST_VIEW_STYLES, $json_array)) {
            $this->set_view_styles($json_array[api::JSON_LIST_VIEW_STYLES]);
        } else {
            $usr_msg->add_error_text('Mandatory view_styles missing in API JSON ' . json_encode($json_array));
            $this->set_view_styles([]);
        }
        if (array_key_exists(api::JSON_LIST_VIEW_LINK_TYPES, $json_array)) {
            $this->set_view_link_types($json_array[api::JSON_LIST_VIEW_LINK_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory view_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_link_types([]);
        }
        if (array_key_exists(api::JSON_LIST_VIEW_RELATION_TYPES, $json_array)) {
            $this->set_view_relation_types($json_array[api::JSON_LIST_VIEW_RELATION_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory view_relation_types missing in API JSON ' . json_encode($json_array));
            $this->set_view_relation_types([]);
        }
        if (array_key_exists(api::JSON_LIST_COMPONENT_TYPES, $json_array)) {
            $this->set_component_types($json_array[api::JSON_LIST_COMPONENT_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory component_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_types([]);
        }
        if (array_key_exists(api::JSON_LIST_COMPONENT_LINK_TYPES, $json_array)) {
            $this->set_component_link_types($json_array[api::JSON_LIST_COMPONENT_LINK_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory component_link_types missing in API JSON ' . json_encode($json_array));
            $this->set_component_link_types([]);
        }
        if (array_key_exists(api::JSON_LIST_COMPONENT_POSITION_TYPES, $json_array)) {
            $this->set_position_types($json_array[api::JSON_LIST_COMPONENT_POSITION_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory position_types missing in API JSON ' . json_encode($json_array));
            $this->set_position_types([]);
        }
        if (array_key_exists(api::JSON_LIST_SOURCE_TYPES, $json_array)) {
            $this->set_source_types($json_array[api::JSON_LIST_SOURCE_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory source_types missing in API JSON ' . json_encode($json_array));
            $this->set_source_types([]);
        }
        if (array_key_exists(api::JSON_LIST_REF_TYPES, $json_array)) {
            $this->set_ref_types($json_array[api::JSON_LIST_REF_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory ref_types missing in API JSON ' . json_encode($json_array));
            $this->set_ref_types([]);
        }
        if (array_key_exists(api::JSON_LIST_SHARE_TYPES, $json_array)) {
            $this->set_share_types($json_array[api::JSON_LIST_SHARE_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory share_types missing in API JSON ' . json_encode($json_array));
            $this->set_share_types([]);
        }
        if (array_key_exists(api::JSON_LIST_PROTECTION_TYPES, $json_array)) {
            $this->set_protection_types($json_array[api::JSON_LIST_PROTECTION_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory protection_types missing in API JSON ' . json_encode($json_array));
            $this->set_protection_types([]);
        }
        if (array_key_exists(api::JSON_LIST_LANGUAGES, $json_array)) {
            $this->set_languages($json_array[api::JSON_LIST_LANGUAGES]);
        } else {
            $usr_msg->add_error_text('Mandatory languages missing in API JSON ' . json_encode($json_array));
            $this->set_languages([]);
        }
        if (array_key_exists(api::JSON_LIST_LANGUAGE_FORMS, $json_array)) {
            $this->set_language_forms($json_array[api::JSON_LIST_LANGUAGE_FORMS]);
        } else {
            $usr_msg->add_error_text('Mandatory language_forms missing in API JSON ' . json_encode($json_array));
            $this->set_language_forms([]);
        }
        if (array_key_exists(api::JSON_LIST_VERBS, $json_array)) {
            $this->set_verbs($json_array[api::JSON_LIST_VERBS]);
        } else {
            $usr_msg->add_error_text('Mandatory verbs missing in API JSON ' . json_encode($json_array));
            $this->set_verbs([]);
        }
        if (array_key_exists(api::JSON_LIST_SYSTEM_VIEWS, $json_array)) {
            $this->set_system_views($json_array[api::JSON_LIST_SYSTEM_VIEWS]);
        } else {
            //$usr_msg->add_error_text('Mandatory system_views missing in API JSON ' . json_encode($json_array));
            $this->set_system_views([]);
        }
        if (array_key_exists(api::JSON_LIST_SYS_LOG_STATUUS, $json_array)) {
            $this->set_sys_log_statuum($json_array[api::JSON_LIST_SYS_LOG_STATUUS]);
        } else {
            $usr_msg->add_error_text('Mandatory sys_log_statuum missing in API JSON ' . json_encode($json_array));
            $this->set_sys_log_statuum([]);
        }
        if (array_key_exists(api::JSON_LIST_JOB_TYPES, $json_array)) {
            $this->set_job_types($json_array[api::JSON_LIST_JOB_TYPES]);
        } else {
            $usr_msg->add_error_text('Mandatory job_types missing in API JSON ' . json_encode($json_array));
            $this->set_job_types([]);
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_ACTIONS, $json_array)) {
            $this->set_change_action_list($json_array[api::JSON_LIST_CHANGE_LOG_ACTIONS]);
        } else {
            $usr_msg->add_error_text('Mandatory change_action_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_action_list([]);
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_TABLES, $json_array)) {
            $this->set_change_table_list($json_array[api::JSON_LIST_CHANGE_LOG_TABLES]);
        } else {
            $usr_msg->add_error_text('Mandatory change_table_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_table_list([]);
        }
        if (array_key_exists(api::JSON_LIST_CHANGE_LOG_FIELDS, $json_array)) {
            $this->set_change_field_list($json_array[api::JSON_LIST_CHANGE_LOG_FIELDS]);
        } else {
            $usr_msg->add_error_text('Mandatory change_field_list missing in API JSON ' . json_encode($json_array));
            $this->set_change_field_list([]);
        }
        return $usr_msg;
    }

    function set_user_profiles(?array $json_array = null): void
    {
        $this->usr_pro = new user_profile();
        $this->usr_pro->set_from_json_array($json_array);
    }

    function set_phrase_types(?array $json_array = null): void
    {
        $this->phr_typ = new phrase_type_list();
        $this->phr_typ->set_from_json_array($json_array);
    }

    function set_formula_types(?array $json_array = null): void
    {
        $this->frm_typ = new formula_type_list();
        $this->frm_typ->set_from_json_array($json_array);
    }

    function set_formula_link_types(?array $json_array = null): void
    {
        $this->frm_lnk_typ = new formula_link_type_list();
        $this->frm_lnk_typ->set_from_json_array($json_array);
    }

    function set_view_types(?array $json_array = null): void
    {
        $this->msk_typ = new view_type_list();
        $this->msk_typ->set_from_json_array($json_array);
    }

    function set_view_styles(?array $json_array = null): void
    {
        $this->msk_sty = new view_style_list();
        $this->msk_sty->set_from_json_array($json_array);
    }

    function set_view_link_types(?array $json_array = null): void
    {
        $this->msk_lnk_typ = new view_link_type_list();
        $this->msk_lnk_typ->set_from_json_array($json_array);
    }

    function set_view_relation_types(?array $json_array = null): void
    {
        $this->mrl_typ = new view_relation_type_list();
        $this->mrl_typ->set_from_json_array($json_array);
    }

    function set_component_types(?array $json_array = null): void
    {
        $this->cmp_typ = new component_type_list();
        $this->cmp_typ->set_from_json_array($json_array);
    }

    function set_component_link_types(?array $json_array = null): void
    {
        $this->cmp_lnk_typ = new component_link_type_list();
        $this->cmp_lnk_typ->set_from_json_array($json_array);
    }

    function set_position_types(?array $json_array = null): void
    {
        $this->pos_typ = new position_type_list();
        $this->pos_typ->set_from_json_array($json_array);
    }

    function set_source_types(?array $json_array = null): void
    {
        $this->src_typ = new source_type_list();
        $this->src_typ->set_from_json_array($json_array);
    }

    function set_ref_types(?array $json_array = null): void
    {
        $this->ref_typ = new ref_type_list();
        $this->ref_typ->set_from_json_array($json_array, ref_type::class);
    }

    function set_share_types(?array $json_array = null): void
    {
        $this->shr_typ = new share();
        $this->shr_typ->set_from_json_array($json_array);
    }

    function set_protection_types(?array $json_array = null): void
    {
        $this->ptc_typ = new protection();
        $this->ptc_typ->set_from_json_array($json_array);
    }

    function set_languages(?array $json_array = null): void
    {
        $this->lan = new language_list();
        $this->lan->set_from_json_array($json_array, language::class);
    }

    function set_language_forms(?array $json_array = null): void
    {
        $this->lan_for = new language_form_list();
        $this->lan_for->set_from_json_array($json_array);
    }

    function set_verbs(?array $json_array = null): void
    {
        $this->vrb = new verbs();
        $this->vrb->set_from_json_array($json_array, verb::class);
    }

    function set_sys_log_statuum(?array $json_array = null): void
    {
        $this->sys_log_sta = new sys_log_status_list();
        $this->sys_log_sta->set_from_json_array($json_array);
    }

    function set_job_types(?array $json_array = null): void
    {
        $this->job_typ = new job_type_list();
        $this->job_typ->set_from_json_array($json_array);
    }

    function set_change_action_list(?array $json_array = null): void
    {
        $this->cng_act = new change_action_list();
        $this->cng_act->set_from_json_array($json_array);
    }

    function set_change_table_list(?array $json_array = null): void
    {
        $this->cng_tbl = new change_table_list();
        $this->cng_tbl->set_from_json_array($json_array);
    }

    function set_change_field_list(?array $json_array = null): void
    {
        $this->cng_fld = new change_field_list();
        $this->cng_fld->set_from_json_array($json_array);
    }

    function set_system_views(?array $json_array = null): void
    {
        $this->msk_sys = new view_list_ui();
        $this->msk_sys->api_mapper($json_array);
    }

    // TODO add similar functions for all cache types
    function get_html_by_id(int $id): string
    {
        $msk = $this->get_view_by_id($id);
        $wrd = new word_ui();
        return $msk->show($wrd);
    }

    function get_view_by_id(int $id): ?view
    {
        return $this->msk_sys->get($id);
    }

    function get_view(string $code_id): ?view
    {
        return $this->msk_sys->get_by_code_id($code_id);
    }

    function get_html(string $code_id): string
    {
        $msk = $this->get_view($code_id);
        $wrd = new word_ui();
        return $msk->show($wrd);
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }


}