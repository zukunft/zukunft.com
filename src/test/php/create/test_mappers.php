<?php

/*

    test/create/test_mappers.php - mapper e.g. the map the class to a test object
    ----------------------------


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_HELPER . 'db_id_object_non_sandbox.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_SANDBOX . 'sandbox_value.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_relation.php';
include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_link.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::HELPER . 'url_mapper.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::VIEW . 'term_view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_id_object_non_sandbox;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\component as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox as sandbox_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;
use Zukunft\ZukunftCom\main\php\web\view\term_view as view_link_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_mappers
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * map
     */

    /**
     * get the base test object related to the given class
     * @param string $class the given main class name
     * @return sandbox|sandbox_value|sandbox_link|type_object|db_id_object_non_sandbox wit only a few vars filled
     */
    function class_to_base_object(string $class): sandbox|sandbox_value|sandbox_link|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_ip();
                break;
            case word::class;
                $obj = $t_wrd->word();
                break;
            case verb::class;
                $obj = $t_vrb->verb();
                break;
            case triple::class;
                $obj = $t_trp->triple();
                break;
            case source::class;
                $obj = $t_src->source();
                break;
            case ref::class;
                $obj = $t_ref->reference();
                break;
            case value::class;
                $obj = $t_val->value();
                break;
            case formula::class;
                $obj = $t_frm->formula();
                break;
            case formula_link::class;
                $obj = $t_frm->formula_link();
                break;
            case result::class;
                $obj = $t_res->result();
                break;
            case view::class;
                $obj = $t_msk->view();
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation();
                break;
            case term_view::class;
                $obj = $t_msk->term_view();
                break;
            case component::class;
                $obj = $t_cmp->component();
                break;
            case component_link::class;
                $obj = $t_cmp->component_link();
                break;
            default:
                log_err('no base object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the filled test object related to the given class
     * @param string $class the given main class name
     * @return triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_value|type_object|db_id_object_non_sandbox wit only a few vars filled
     */
    function class_to_filled_object(string $class): triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_value|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                break;
            case verb::class;
                $obj = $t_vrb->verb_filled();
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                break;
            case source::class;
                $obj = $t_src->source_filled();
                break;
            case ref::class;
                $obj = $t_ref->ref_filled();
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                break;
            case formula_link::class;
                $obj = $t_frm->formula_link_filled();
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation_filled();
                break;
            case term_view::class;
                $obj = $t_msk->term_view_filled();
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                break;
            case component_link::class;
                $obj = $t_cmp->component_link_filled();
                break;
            default:
                log_err('no filled object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the object to test adding a new object e.g. via api to the database related to the given class
     * @param string $class the given main class name
     * @return triple|ref|value|result|sandbox|sandbox_value|type_object|db_id_object_non_sandbox wit only a few vars filled
     */
    function class_to_add_object(string $class): triple|ref|value|result|sandbox|sandbox_value|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                break;
            case word::class;
                $obj = $t_wrd->word_filled_add();
                break;
            case verb::class;
                $obj = $t_vrb->verb_filled();
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                break;
            case source::class;
                $obj = $t_src->source_filled();
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation_filled();
                break;
            default:
                log_err('no add object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the frontend object related to the given backend class
     * @param string $class the given main class name
     * @return word_ui|sandbox_ui|user_ui|ref_ui with only a few vars filled
     */
    function class_to_ui_object(string $class): word_ui|sandbox_ui|user_ui|ref_ui
    {
        $obj = null;
        switch ($class) {
            case user::class;
                $obj = new user_ui();
                break;
            case word::class;
                $obj = new word_ui();
                break;
            case verb::class;
                $obj = new verb_ui();
                break;
            case triple::class;
                $obj = new triple_ui();
                break;
            case source::class;
                $obj = new source_ui();
                break;
            case ref::class;
                $obj = new ref_ui();
                break;
            case value::class;
                $obj = new value_ui();
                break;
            case formula::class;
                $obj = new formula_ui();
                break;
            case formula_link::class;
                $obj = new formula_link_ui();
                break;
            case result::class;
                $obj = new result_ui();
                break;
            case view::class;
                $obj = new view_ui();
                break;
            case view_relation::class;
                $obj = new view_relation_ui();
                break;
            case term_view::class;
                $obj = new view_link_ui();
                break;
            case component::class;
                $obj = new component_ui();
                break;
            case component_link::class;
                $obj = new component_link_ui();
                break;
            default:
                log_err('no frontend object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the filled url object related to the given class and action
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $action
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_filled_url(
        string       $class,
        int          $msk_id,
        string       $action,
        string       $type = url_var::MASK_HUMAN,
        user_message $usr_msg = new user_message()
    ): string
    {
        if ($action == change_actions::SHOW) {
            $url = $this->class_to_url_show($class, $msk_id, $type, $usr_msg);
        } elseif ($action == change_actions::ADD) {
            $url = $this->class_to_url_add($class, $msk_id, $type, $usr_msg);
        } elseif ($action == change_actions::UPDATE) {
            $url = $this->class_to_url_edit($class, $msk_id, $type, $usr_msg);
        } elseif ($action == change_actions::DELETE) {
            $url = $this->class_to_url_del($class, $msk_id, $type, $usr_msg);
        } elseif ($action == change_actions::SUB) {
            $url = $this->class_to_url_edit($class, $msk_id, $type, $usr_msg);
        } else {
            $msg = 'unknow action ' . $action . ' for view id ' . $msk_id;
            log_err($msg);
            $url = $msg;
        }
        return $this->test_url($url);
    }

    /**
     * add the test server to a url query string
     * @param string $url_part the url query string
     * @return string the complete url for the test server
     */
    function test_url(string $url_part): string
    {
        return api::HOST_TESTING . api::MAIN_SCRIPT . url_var::PAR . $url_part;
    }

    /**
     * TODO Prio 1 review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_url_show(
        string       $class,
        int          $msk_id,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_array[] = [url_var::MASK => $msk_id];
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                $url_array[] = [url_var::IP, $obj->ip_addr];
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $obj_array = $this->word_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case verb::class;
                $obj = $t_vrb->verb_is();
                $obj_array = $this->verb_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $obj_array = $this->triple_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $obj_array = $this->source_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                $obj_array = $this->ref_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $obj_array = $this->value_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $obj_array = $this->formula_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $obj_array = $this->result_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $obj_array = $this->view_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $obj_array = $this->component_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url_array[] = [url_var::ID, $obj->id()];
        $url_array[] = [url_var::ACTION, url_var::CRUD_READ, true];
        return $this->array_to_url_type($url_array, $type, $usr_msg);
    }

    /**
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_url_add(
        string       $class,
        int          $msk_id,
        string       $type = url_var::MASK_HUMAN,
        user_message $usr_msg = new user_message()
    ): string
    {
        $url_array[] = [url_var::MASK, $msk_id];
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_grp = new test_groups($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                $url_array[] = [url_var::IP, $obj->ip_addr];
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $obj_array = $this->word_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case verb::class;
                $obj = $t_vrb->verb_filled();
                $obj_array = $this->verb_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $obj_array = $this->triple_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $obj_array = $this->source_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case ref::class;
                $obj = $t_ref->ref_filled();
                $obj_array = $this->ref_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $obj_array = $this->value_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $obj_array = $this->group_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $obj_array = $this->formula_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula_link::class;
                $obj = $t_frm->formula_link_filled();
                $obj_array = $this->formula_link_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $obj_array = $this->result_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $obj_array = $this->view_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation_filled();
                $obj_array = $this->view_relation_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case term_view::class;
                $obj = $t_msk->term_view_filled();
                $obj_array = $this->view_link_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $obj_array = $this->component_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case component_link::class;
                $obj = $t_cmp->component_link_filled();
                $obj_array = $this->component_link_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                if ($msk_id == views::START_ID) {
                    // for the start page no additional vars in the url are needed
                    $obj = new db_object();
                } else {
                    $obj = $t_wrd->word_filled();
                    log_err('no filled url object defined for ' . $class);
                }
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url_array[] = [url_var::ID, $obj->id()];
        $url_array[] = [url_var::ACTION, url_var::CRUD_CREATE, true];
        return $this->array_to_url_type($url_array, $type, $usr_msg);
    }

    /**
     * TODO review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_url_edit(
        string       $class,
        int          $msk_id,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_array[] = [url_var::MASK, $msk_id];
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_grp = new test_groups($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                $url_array[] = [url_var::IP, $obj->ip_addr];
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $obj_array = $this->word_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case verb::class;
                $obj = $t_vrb->verb_is_filled();
                $obj_array = $this->verb_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $obj_array = $this->triple_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case source::class;
                $obj = $t_src->source_filled_included();
                $obj_array = $this->source_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case ref::class;
                $obj = $t_ref->ref_filled();
                $obj_array = $this->ref_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $obj_array = $this->value_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $obj_array = $this->group_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $obj_array = $this->formula_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $obj_array = $this->result_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $obj_array = $this->view_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $obj_array = $this->component_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url_array[] = [url_var::ID, $obj->id()];
        $url_array[] = [url_var::ACTION, url_var::CRUD_UPDATE, true];
        return $this->array_to_url_type($url_array, $type, $usr_msg);
    }

    /**
     * TODO Prio 2 review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_url_del(
        string       $class,
        int          $msk_id,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_array = [];
        $url_array[] = [url_var::MASK, $msk_id];
        $t_usr = new test_users();
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_grp = new test_groups($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $obj_array = $this->word_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case verb::class;
                $obj = $t_vrb->verb_is_filled();
                $obj_array = $this->verb_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $obj_array = $this->triple_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $obj_array = $this->source_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case ref::class;
                $obj = $t_ref->ref_filled();
                $obj_array = $this->ref_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $obj_array = $this->value_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $obj_array = $this->group_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $obj_array = $this->formula_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $obj_array = $this->result_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $obj_array = $this->view_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $obj_array = $this->component_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url_array[] = [url_var::ID, $obj->id()];
        $url_array[] = [url_var::ACTION, url_var::CRUD_DELETE, true];
        return $this->array_to_url_type($url_array, $type, $usr_msg);
    }

    private function array_to_url_type(
        array        $url_array,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_map = new url_mapper();
        if ($type == url_var::MASK_HUMAN) {
            $url = $url_map->standard_url_to_human($url_array, $usr_msg);
        } elseif ($type == url_var::MASK_POD) {
            $url = $url_map->standard_url_to_pod($url_array, $usr_msg);
        } else {
            $url = $url_map->array_to_url($url_array);
        }
        return $url;
    }

    // TODO Prio 1 check if all object fields are included e.g. view of source is missing

    private function word_url(word $wrd, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $wrd->name()];
        $url_array[] = [url_var::DESCRIPTION, $wrd->description()];
        // TODO Prio 2 activate
        /*
        if ($type == url_var::MASK_POD) {
            $url_array[] = [url_var::TYPE, $wrd->type_id()];
        } else {
            $url_array[] = [url_var::TYPE, $wrd->type_name()];
        }
        */
        $url_array[] = [url_var::TYPE, $wrd->type_id()];
        $url_array[] = [url_var::PLURAL, $wrd->plural];
        $url_array[] = [url_var::SHARE, $wrd->share_id()];
        $url_array[] = [url_var::PROTECTION, $wrd->protection_id()];
        $url_array[] = [url_var::VIEW, $wrd->get_view_id()];
        $url_array[] = [url_var::USAGE, $wrd->usage()];
        $url_array[] = [url_var::IMPACT, $wrd->get_impact()];
        return $url_array;
    }

    private function verb_url(verb $vrb, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $vrb->name()];
        $url_array[] = [url_var::DESCRIPTION, $vrb->description()];
        $url_array[] = [url_var::PLURAL, $vrb->plural()];
        $url_array[] = [url_var::REVERSE, $vrb->reverse()];
        $url_array[] = [url_var::REVERSE_PLURAL, $vrb->reverse_plural()];
        $url_array[] = [url_var::FORMULA, $vrb->formula_name()];
        $url_array[] = [url_var::USAGE, $vrb->usage()];
        $url_array[] = [url_var::IMPACT, $vrb->get_impact()];
        return $url_array;
    }

    private function triple_url(triple $trp, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $trp->name()];
        $url_array[] = [url_var::PHRASE_FROM, $trp->from_id()];
        $url_array[] = [url_var::VERB, $trp->verb_id()];
        $url_array[] = [url_var::PHRASE_TO, $trp->to_id()];
        $url_array[] = [url_var::NAME, $trp->name_given()];
        $url_array[] = [url_var::DESCRIPTION, $trp->description()];
        $url_array[] = [url_var::SHARE, $trp->share_id()];
        $url_array[] = [url_var::PROTECTION, $trp->protection_id()];
        $url_array[] = [url_var::VIEW, $trp->get_view_id()];
        $url_array[] = [url_var::USAGE, $trp->usage()];
        $url_array[] = [url_var::IMPACT, $trp->get_impact()];
        return $url_array;
    }

    private function source_url(source $src, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $src->name()];
        $url_array[] = [url_var::DESCRIPTION, $src->description()];
        $url_array[] = [url_var::URL, $src->url()];
        $url_array[] = [url_var::TYPE, $src->type_id()];
        // TODO Prio 1 activate
        // $url_array[] = [url_var::VIEW, $src->get_view_id()];
        $url_array[] = [url_var::SHARE, $src->share_id()];
        $url_array[] = [url_var::PROTECTION, $src->protection_id()];
        $url_array[] = [url_var::USAGE, $src->usage()];
        return $url_array;
    }

    private function ref_url(ref $ref, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::PHRASE, $ref->from_id()];
        $url_array[] = [url_var::EXTERNAL_KEY, $ref->external_key()];
        $url_array[] = [url_var::TYPE, $ref->predicate_id()];
        $url_array[] = [url_var::URL, $ref->url()];
        $url_array[] = [url_var::SOURCE, $ref->source_id()];
        $url_array[] = [url_var::DESCRIPTION, $ref->description()];
        // TODO Prio 1 activate
        //$url_array[] = [url_var::VIEW, $ref->get_view_id()];
        $url_array[] = [url_var::SHARE, $ref->share_id()];
        $url_array[] = [url_var::PROTECTION, $ref->protection_id()];
        return $url_array;
    }

    private function value_url(value $val, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $val->name()];
        $url_array[] = [url_var::PHRASE_LIST, implode(',', $val->ids())];
        $url_array[] = [url_var::NUMERIC_VALUE, $val->value()];
        $url_array[] = [url_var::SOURCE, $val->source_id()];
        $url_array[] = [url_var::SHARE, $val->share_id()];
        $url_array[] = [url_var::PROTECTION, $val->protection_id()];
        return $url_array;
    }

    private function group_url(group $grp, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $grp->name()];
        $url_array[] = [url_var::DESCRIPTION, $grp->description()];
        $url_array[] = [url_var::SOURCE, $grp->source_id()];
        $url_array[] = [url_var::SHARE, $grp->share_id()];
        $url_array[] = [url_var::PROTECTION, $grp->protection_id()];
        return $url_array;
    }

    private function formula_url(formula $frm, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $frm->name()];
        $url_array[] = [url_var::DESCRIPTION, $frm->description()];
        $url_array[] = [url_var::USER_EXPRESSION, $frm->get_usr_text()];
        // all need
        // update
        $url_array[] = [url_var::TYPE, $frm->type_id()];
        $url_array[] = [url_var::VIEW, $frm->get_view_id()];
        $url_array[] = [url_var::SHARE, $frm->share_id()];
        $url_array[] = [url_var::PROTECTION, $frm->protection_id()];
        $url_array[] = [url_var::USAGE, $frm->usage()];
        $url_array[] = [url_var::IMPACT, $frm->get_impact()];
        return $url_array;
    }

    private function formula_link_url(formula_link $frm_lnk, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::FORMULA, $frm_lnk->formula_id()];
        $url_array[] = [url_var::PHRASE, $frm_lnk->phrase_id()];
        $url_array[] = [url_var::NAME, $frm_lnk->name()];
        $url_array[] = [url_var::POSITION, $frm_lnk->order_nbr];
        $url_array[] = [url_var::TYPE, $frm_lnk->predicate_id()];
        $url_array[] = [url_var::SHARE, $frm_lnk->share_id()];
        $url_array[] = [url_var::PROTECTION, $frm_lnk->protection_id()];
        return $url_array;
    }

    private function result_url(result $res, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $res->name()];
        $url_array[] = [url_var::PHRASE_LIST, implode(',', $res->ids())];
        $url_array[] = [url_var::NUMERIC_VALUE, $res->value()];
        $url_array[] = [url_var::FORMULA, $res->formula_id()];
        $url_array[] = [url_var::SHARE, $res->share_id()];
        $url_array[] = [url_var::PROTECTION, $res->protection_id()];
        return $url_array;
    }

    private function view_url(view $msk, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $msk->name()];
        $url_array[] = [url_var::DESCRIPTION, $msk->description()];
        $url_array[] = [url_var::TYPE, $msk->type_id()];
        $url_array[] = [url_var::SHARE, $msk->share_id()];
        $url_array[] = [url_var::PROTECTION, $msk->protection_id()];
        return $url_array;
    }

    private function view_relation_url(view_relation $mrl, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::VIEW_PARENT, $mrl->parent()?->id()];
        $url_array[] = [url_var::VIEW_CHILD, $mrl->child()?->id()];
        $url_array[] = [url_var::NAME, $mrl->name()];
        $url_array[] = [url_var::DESCRIPTION, $mrl->description];
        $url_array[] = [url_var::TYPE, $mrl->predicate_id()];
        $url_array[] = [url_var::SHARE, $mrl->share_id()];
        $url_array[] = [url_var::PROTECTION, $mrl->protection_id()];
        return $url_array;
    }

    private function view_link_url(term_view $msk_lnk, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::VIEW, $msk_lnk->get_view()->id()];
        $url_array[] = [url_var::TERM, $msk_lnk->term()->id()];
        $url_array[] = [url_var::DESCRIPTION, $msk_lnk->description];
        $url_array[] = [url_var::TYPE, $msk_lnk->predicate_id()];
        $url_array[] = [url_var::SHARE, $msk_lnk->share_id()];
        $url_array[] = [url_var::PROTECTION, $msk_lnk->protection_id()];
        return $url_array;
    }

    private function component_url(component $cmp, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $cmp->name()];
        $url_array[] = [url_var::DESCRIPTION, $cmp->description()];
        $url_array[] = [url_var::TYPE, $cmp->type_id()];
        $url_array[] = [url_var::SHARE, $cmp->share_id()];
        $url_array[] = [url_var::PROTECTION, $cmp->protection_id()];
        return $url_array;
    }

    private function component_link_url(component_link $cmp_lnk, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::VIEW, $cmp_lnk->get_view()->id()];
        $url_array[] = [url_var::COMPONENT, $cmp_lnk->get_component()->id()];
        $url_array[] = [url_var::POSITION, $cmp_lnk->order_nbr];
        $url_array[] = [url_var::POSITION_TYPE, $cmp_lnk->get_pos_type_id()];
        $url_array[] = [url_var::STYLE, $cmp_lnk->get_style_id()];
        $url_array[] = [url_var::TYPE, $cmp_lnk->predicate_id()];
        $url_array[] = [url_var::SHARE, $cmp_lnk->share_id()];
        $url_array[] = [url_var::PROTECTION, $cmp_lnk->protection_id()];
        return $url_array;
    }

}