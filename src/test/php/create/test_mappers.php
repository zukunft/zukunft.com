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
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_HELPER . 'db_id_object_non_sandbox.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_value.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_id_object_non_sandbox;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\component as component_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox as sandbox_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
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

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * map
     */

    /**
     * get the base test object related to the given class
     * @param string $class the given main class name
     * @return sandbox|sandbox_value|type_object|db_id_object_non_sandbox wit only a few vars filled
     */
    function class_to_base_object(string $class): sandbox|sandbox_value|type_object|db_id_object_non_sandbox
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
            case result::class;
                $obj = $t_res->result();
                break;
            case view::class;
                $obj = $t_msk->view();
                break;
            case component::class;
                $obj = $t_cmp->component();
                break;
            default:
                log_err('no base object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the filled test object related to the given class
     * @param string $class the given main class name
     * @return triple|ref|value|result|sandbox|sandbox_value|type_object|db_id_object_non_sandbox wit only a few vars filled
     */
    function class_to_filled_object(string $class): triple|ref|value|result|sandbox|sandbox_value|type_object|db_id_object_non_sandbox
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
            case result::class;
                $obj = new result_ui();
                break;
            case view::class;
                $obj = new view_ui();
                break;
            case component::class;
                $obj = new component_ui();
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
     * @return string with only a few vars filled
     */
    function class_to_filled_url(string $class, int $msk_id, string $action): string
    {
        if ($action == change_actions::SHOW) {
            $result = $this->class_to_url_show($class, $msk_id);
        } elseif ($action == change_actions::ADD) {
            $result = $this->class_to_url_add($class, $msk_id);
        } elseif ($action == change_actions::UPDATE) {
            $result = $this->class_to_url_edit($class, $msk_id);
        } elseif ($action == change_actions::DELETE) {
            $result = $this->class_to_url_del($class, $msk_id);
        } else {
            $msg = 'unknow action ' . $action . ' for view id ' . $msk_id;
            log_err($msg);
            $result = $msg;
        }
        return $result;
    }

    /**
     * TODO Prio 1 review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @return string with only a few vars filled
     */
    function class_to_url_show(string $class, int $msk_id): string
    {
        $url = api::HOST_TESTING . api::MAIN_SCRIPT . url_var::PAR;
        $url .= $this->url_par(url_var::MASK_HUMAN, $msk_id);
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
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::IP, $obj->ip_addr);
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::PLURAL, $obj->plural);
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                break;
            case verb::class;
                $obj = $t_vrb->verb_is();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::FROM_ID_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::VERB_LONG, $obj->verb_id());
                $url .= $this->url_par(url_var::TO_ID_LONG, $obj->to_id());
                $url .= $this->url_par(url_var::NAME, $obj->name_given());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::PHRASE_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::TYPE, $obj->predicate_id());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::EXTERNAL_KEY, $obj->external_key());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::FORMULA_LONG, $obj->formula_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url .= $this->url_par(url_var::ID, $obj->id());
        $url .= $this->url_par(url_var::ACTION_LONG, url_var::CRUD_READ, true);
        return $url;
    }

    /**
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @return string with only a few vars filled
     */
    function class_to_url_add(string $class, int $msk_id): string
    {
        $url = api::HOST_TESTING . api::MAIN_SCRIPT . url_var::PAR;
        $url .= $this->url_par(url_var::MASK_HUMAN, $msk_id);
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
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::IP, $obj->ip_addr);
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::PLURAL, $obj->plural);
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                break;
            case verb::class;
                $obj = $t_vrb->verb_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::FROM_ID_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::VERB_LONG, $obj->verb_id());
                $url .= $this->url_par(url_var::TO_ID_LONG, $obj->to_id());
                $url .= $this->url_par(url_var::NAME, $obj->name_given());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::PHRASE_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::TYPE, $obj->predicate_id());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::EXTERNAL_KEY, $obj->external_key());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::FORMULA_LONG, $obj->formula_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url .= $this->url_par(url_var::ID, $obj->id());
        $url .= $this->url_par(url_var::ACTION_LONG, url_var::CRUD_CREATE, true);
        return $url;
    }

    /**
     * TODO review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @return string with only a few vars filled
     */
    function class_to_url_edit(string $class, int $msk_id): string
    {
        $url = api::HOST_TESTING . api::MAIN_SCRIPT . url_var::PAR;
        $url .= $this->url_par(url_var::MASK_HUMAN, $msk_id);
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
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::IP, $obj->ip_addr);
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::PLURAL, $obj->plural);
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case verb::class;
                $obj = $t_vrb->verb_is_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::PLURAL, $obj->plural());
                $url .= $this->url_par(url_var::REVERSE, $obj->reverse());
                $url .= $this->url_par(url_var::REVERSE_PLURAL, $obj->reverse_plural());
                $url .= $this->url_par(url_var::FORMULA, $obj->formula_name());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::FROM_ID_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::VERB_LONG, $obj->verb_id());
                $url .= $this->url_par(url_var::TO_ID_LONG, $obj->to_id());
                $url .= $this->url_par(url_var::NAME, $obj->name_given());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                $url .= $this->url_par(url_var::VIEW_LONG, $obj->view_id());
                break;
            case source::class;
                $obj = $t_src->source_filled_included();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::PHRASE_LONG, $obj->from_id());
                $url .= $this->url_par(url_var::TYPE, $obj->predicate_id());
                $url .= $this->url_par(url_var::URL, $obj->url());
                $url .= $this->url_par(url_var::EXTERNAL_KEY, $obj->external_key());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::SOURCE_LONG, $obj->source_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::PHRASE_LIST_LONG, implode(',',$obj->ids()));
                $url .= $this->url_par(url_var::NUMERIC_VALUE_LONG, $obj->value());
                $url .= $this->url_par(url_var::FORMULA_LONG, $obj->formula_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::TYPE, $obj->type_id());
                $url .= $this->url_par(url_var::SHARE, $obj->share_id());
                $url .= $this->url_par(url_var::PROTECTION, $obj->protection_id());
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url .= $this->url_par(url_var::ID, $obj->id());
        $url .= $this->url_par(url_var::ACTION_LONG, url_var::CRUD_UPDATE, true);
        return $url;
    }

    /**
     * TODO Prio 2 review
     * get the filled url object related to the given class
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @return string with only a few vars filled
     */
    function class_to_url_del(string $class, int $msk_id): string
    {
        $url = api::HOST_TESTING . api::MAIN_SCRIPT . url_var::PAR;
        $url .= $this->url_par(url_var::MASK_HUMAN, $msk_id);
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
                $url .= $this->url_par(url_var::NAME, $obj->name());
                break;
            case word::class;
                $obj = $t_wrd->word_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case verb::class;
                $obj = $t_vrb->verb_is_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::PLURAL, $obj->plural());
                $url .= $this->url_par(url_var::REVERSE, $obj->reverse());
                $url .= $this->url_par(url_var::REVERSE_PLURAL, $obj->reverse_plural());
                $url .= $this->url_par(url_var::FORMULA, $obj->formula_name());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case triple::class;
                $obj = $t_trp->triple_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::NAME, $obj->name_given());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case source::class;
                $obj = $t_src->source_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                break;
            case ref::class;
                $obj = $t_ref->reference_plus();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                break;
            case value::class;
                $obj = $t_val->value_16_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                break;
            case group::class;
                $obj = $t_grp->group_zh_2020();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                $url .= $this->url_par(url_var::USAGE, $obj->usage());
                $url .= $this->url_par(url_var::IMPACT, $obj->impact());
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                break;
            case view::class;
                $obj = $t_msk->view_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                break;
            case component::class;
                $obj = $t_cmp->component_filled();
                $url .= $this->url_par(url_var::NAME, $obj->name());
                $url .= $this->url_par(url_var::DESCRIPTION, $obj->description());
                break;
            case db_object::class;
                // for the start page no additional vars in the url are needed
                $obj = new db_object();
                break;
            default:
                $obj = $t_wrd->word_filled();
                log_err('no filled url object defined for ' . $class);
        }
        $url .= $this->url_par(url_var::ID, $obj->id());
        $url .= $this->url_par(url_var::ACTION_LONG, url_var::CRUD_REMOVE, true);
        return $url;
    }

    private function url_par(string $name, ?string $par, bool $last = false): string
    {
        if ($par == null) {
            return '';
        } else {
            if ($last) {
                return $name . url_var::EQ . urlencode($par);
            } else {
                return $name . url_var::EQ . urlencode($par) . url_var::ADD;
            }
        }
    }


}