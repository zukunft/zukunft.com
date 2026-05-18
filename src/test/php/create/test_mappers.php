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
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_type.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_COMPONENT . 'component_link_type.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_id_object_non_sandbox.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LANGUAGE . 'language.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_SANDBOX . 'sandbox_value.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
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
include_once html_paths::GROUP . 'group.php';
include_once html_paths::HELPER . 'url_mapper.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::TYPES . 'type_object.php';
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
include_once test_paths::CREATE . 'test_languages.php';
include_once test_paths::CREATE . 'test_phrases.php';
include_once test_paths::CREATE . 'test_terms.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'groups.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'results.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'languages.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

// cfg group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_id_object_non_sandbox;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
// web group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\web\component\component as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\group\group as group_ui;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox as sandbox_ui;
use Zukunft\ZukunftCom\main\php\web\system\language as language_ui;
use Zukunft\ZukunftCom\main\php\web\types\type_object as type_object_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\term_view as view_link_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
// shared group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
// test group (alphabetic by FQN)
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
     * get an empty object based on the given class
     * @param string $class the given main class name
     * @return sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox with only a few vars filled
     */
    function class_to_object(string $class, user $usr): sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        switch ($class) {
            case user::class;
                $obj = new user();
                break;
            case word::class;
                $obj = new word($usr);
                break;
            case verb::class;
                $obj = new verb();
                break;
            case triple::class;
                $obj = new triple($usr);
                break;
            case source::class;
                $obj = new source($usr);
                break;
            case ref::class;
                $obj = new ref($usr);
                break;
            case value::class;
                $obj = new value($usr);
                break;
            case group::class;
                $obj = new group($usr);
                break;
            case formula::class;
                $obj = new formula($usr);
                break;
            case formula_link::class;
                $obj = new formula_link($usr);
                break;
            case result::class;
                $obj = new result($usr);
                break;
            case view::class;
                $obj = new view($usr);
                break;
            case view_relation::class;
                $obj = new view_relation($usr);
                break;
            case term_view::class;
                $obj = new term_view($usr);
                break;
            case component::class;
                $obj = new component($usr);
                break;
            case component_link::class;
                $obj = new component_link($usr);
                break;
            default:
                log_err('no base object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the base test object related to the given class
     * @param string $class the given main class name
     * @return sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox with only a few vars filled
     */
    function class_to_base_object(string $class): sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users($this->env);
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
            case group::class;
                $obj = $t_grp->group();
                break;
            case formula::class;
                $obj = $t_frm->formula_rename();
                break;
            case formula_link::class;
                $obj = $t_frm->formula_link();
                break;
            case result::class;
                $obj = $t_res->result();
                break;
            case view::class;
                $obj = $t_msk->view_rename();
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation();
                break;
            case term_view::class;
                $obj = $t_msk->term_view();
                break;
            case component::class;
                $obj = $t_cmp->component_rename();
                break;
            case component_link::class;
                $obj = $t_cmp->component_link();
                break;
            default:
                log_err('no base object defined for ' . $class);
        }
        return $obj;
    }

    function change_base_object(
        sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox $obj
    ): sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox
    {
        $t_wrd = new test_words($this->env);
        $t_frm = new test_formulas($this->env);
        $t_msk = new test_views($this->env);
        switch ($obj::class) {
            case user::class;
                $obj->name = users::TEST_USER_NAME_UPDATED;
                break;
            case word::class;
                $obj->set_name(words::TEST_RENAMED);
                break;
            case verb::class;
                $obj->set_name(verbs::TEST_ADD_RENAMED);
                break;
            case triple::class;
                $obj->set_name(triples::SYSTEM_TEST_RENAMED);
                break;
            case source::class;
                $obj->set_name(sources::SYSTEM_TEST_RENAMED);
                break;
            case ref::class;
                $obj->set_name(refs::SYSTEM_TEST_RENAMED);
                break;
            case value::class;
                $obj->set_value(values::SAMPLE_FLOAT);
                $obj->set_protection_by_code_id(protection_types::USER);
                break;
            case group::class;
                $obj->set_name(groups::SYSTEM_TEST_RENAMED);
                break;
            case formula::class;
                $obj->set_name(formulas::SYSTEM_TEST_RENAMED);
                break;
            case formula_link::class;
                $obj->set_formula($t_frm->formula());
                break;
            case result::class;
                $obj->set_value(results::TV_FLOAT);
                $obj->set_protection_by_code_id(protection_types::USER);
                break;
            case view::class;
                $obj->set_name(views::TEST_RENAMED_NAME);
                break;
            case view_relation::class;
                $obj->set_parent($t_msk->view_word_edit());
                $obj->set_child($t_msk->view_word_log());
                $obj->set_protection_by_code_id(protection_types::USER);
                break;
            case term_view::class;
                $obj->set_term($t_wrd->word()->term());
                $obj->set_view($t_msk->view());
                $obj->set_protection_by_code_id(protection_types::ADMIN);
                break;
            case component::class;
                $obj->set_name(components::TEST_RENAMED_NAME);
                break;
            case component_link::class;
                $obj->set_predicate(component_link_type::ALWAYS);
                $obj->set_view($t_msk->view());
                break;
            default:
                log_err('no base object defined for ' . $obj::class);
        }
        return $obj;
    }

    /**
     * get the filled test object related to the given class
     * @param string $class the given main class name
     * @return user|triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_multi|type_object|db_id_object_non_sandbox with only a few vars filled
     */
    function class_to_filled_object(string $class): user|triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_multi|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users($this->env);
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
            case group::class;
                $obj = $t_grp->group_filled();
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
    function class_to_add_filled_object(
        string       $class,
        ?data_object $cac = null
    ): triple|ref|value|result|sandbox|sandbox_value|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users($this->env);
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
                $wrd = $cac->get_first_word();
                $vrb = $cac->get_first_verb();
                $wrd2 = $cac->get_second_word();
                if ($wrd != null and $vrb != null and $wrd2 != null) {
                    $obj = $t_trp->triple_filled_add($wrd->phrase(), $vrb, $wrd2->phrase());
                } else {
                    // just a fallback that should never be used
                    $obj = $t_trp->triple_name_only();
                }
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
                $obj = $t_frm->formula_filled_add();
                break;
            case result::class;
                $obj = $t_res->result_main_filled();
                break;
            case view::class;
                $obj = $t_msk->view_filled_add();
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
     * get an object related to the given class that can be used for insert db tests
     * @param string $class the given main class name
     * @param data_object|null $cac the cache of objects created until now use e.g. to create link objects
     * @return sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox
     *         with only a few vars filled and with a name, that will be cleaned up after testing
     */
    function class_to_add_object(
        string       $class,
        Message      $msg,
        ?data_object $cac = null
    ): sandbox|sandbox_multi|sandbox_link|type_object|db_id_object_non_sandbox
    {
        $obj = null;
        $t_usr = new test_users($this->env);
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
                $obj = $t_usr->user_add();
                break;
            case word::class;
                $obj = $t_wrd->word_add();
                break;
            case verb::class;
                $obj = $t_vrb->verb_add();
                break;
            case triple::class;
                $wrd = $cac->get_first_word();
                $vrb = $cac->get_first_verb();
                $wrd2 = $cac->get_second_word();
                if ($wrd != null and $vrb != null and $wrd2 != null) {
                    $obj = $t_trp->triple_add($wrd->phrase(), $vrb, $wrd2->phrase());
                } else {
                    // just a fallback that should never be used
                    $obj = $t_trp->triple_name_only();
                }
                break;
            case source::class;
                $obj = $t_src->source_add();
                break;
            case ref::class;
                $wrd = $cac->get_first_word();
                $obj = $t_ref->reference_add($wrd->phrase());
                break;
            case group::class;
                $wrd = $cac->get_first_word();
                $obj = $t_grp->group_add($wrd->phrase());
                break;
            case value::class;
                $wrd = $cac->get_first_word();
                $obj = $t_val->value_add($wrd->phrase());
                break;
            case formula::class;
                $obj = $t_frm->formula_add();
                break;
            case formula_link::class;
                $obj = $t_frm->formula_link_add();
                break;
            case result::class;
                $obj = $t_res->result_add();
                break;
            case view::class;
                $obj = $t_msk->view_add();
                break;
            case view_relation::class;
                $obj = $t_msk->view_relation_add();
                break;
            case term_view::class;
                $obj = $t_msk->term_view_add();
                break;
            case component::class;
                $obj = $t_cmp->component_add();
                break;
            case component_link::class;
                $obj = $t_cmp->component_link_add();
                break;
            default:
                log_err('no base object defined for ' . $class);
        }
        return $obj;
    }

    /**
     * get the frontend object related to the given backend class
     * @param string $class the given main class name
     * @return word_ui|sandbox_ui|user_ui|ref_ui|type_object_ui with only a few vars filled
     */
    function class_to_ui_object(string $class): word_ui|sandbox_ui|user_ui|ref_ui|type_object_ui
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
            case group::class;
                $obj = new group_ui();
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
            case language::class;
                $obj = new language_ui(languages::DEFAULT_ID, languages::DEFAULT);
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
        } elseif ($action == change_actions::STEP) {
            $url = $this->class_to_url_step($class, $msk_id, $type, $usr_msg);
        } elseif ($action == change_actions::SEARCH) {
            $url = $this->class_to_url_search($class, $msk_id, $type, $usr_msg);
        } else {
            $msg = 'unknow action ' . $action . ' for view id ' . $msk_id;
            log_err($msg);
            $url = $msg;
        }
        return $this->test_url($url);
    }

    /**
     * get the filled test object related to the component_type
     * @param component_type|type_object_ui $typ the given main class name
     * @return user|triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_multi|type_object|db_id_object_non_sandbox|null with only a few vars filled
     */
    function component_type_to_object(
        component_type|type_object_ui $typ
    ): user|triple|ref|value|result|formula_link|view_relation|term_view|component_link|sandbox|sandbox_multi|type_object|db_id_object_non_sandbox|null
    {
        $obj = null;
        $t_usr = new test_users($this->env);
        $t_lng = new test_languages($this->env);
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
        switch ($typ->code_id) {
            case component_types::ADMIN_FORM_FIELD_USER_NAME;
            case component_types::ADMIN_FORM_FIELD_USER_EMAIL;
            case component_types::ADMIN_FORM_FIELD_USER_PASSWORD;
            case component_types::SYSTEM_BODY_SIGNUP;
            case component_types::SYSTEM_BODY_LOGIN;
            case component_types::SYSTEM_BODY_LOGIN_ACTIVATE;
            case component_types::SYSTEM_BODY_LOGIN_RESET;
            case component_types::SYSTEM_BODY_LOGOUT;
            case component_types::SYSTEM_BODY_USER_SETTINGS;
                $obj = $t_usr->user_filled();
                break;
            case component_types::ADMIN_FORM_FIELD_LANGUAGE_SYMBOL;
            case component_types::FIELD_LANGUAGE_SYMBOL;
                $obj = $t_lng->language();
                break;
            case component_types::PHRASE_NAME;
            case component_types::SELECT_PHRASE;
            case component_types::PHRASE;
            case component_types::FORM_TITLE;
            case component_types::FORM_FIELD_NAME;
            case component_types::FORM_FIELD_DESCRIPTION;
            case component_types::FORM_FIELD_SELECTION_NAME;
            case component_types::FORM_FIELD_SELECTION_DESCRIPTION;
            case component_types::FORM_FIELD_SELECTION_TEXT;
            case component_types::FORM_SELECT_PHRASE;
            case component_types::FORM_SELECT_PHRASES;
            case component_types::FORM_SELECT_PHRASE_TYPE;
            case component_types::FORM_SHARE_TYPE;
            case component_types::FORM_PROTECTION_TYPE;
            case component_types::FORM_BUTTON_CANCEL;
            case component_types::FORM_BUTTON_SAVE;
            case component_types::FORM_BUTTON_DEL;
            case component_types::FORM_BUTTON_IMPORT;
            case component_types::FORM_BUTTON_EXPORT;
            case component_types::FORM_END;
            case component_types::FORM_HIDDEN_BACK;
            case component_types::FORM_HIDDEN_STEP;
            case component_types::FORM_PREVIEW;
            case component_types::ROW_START;
            case component_types::ROW_RIGHT;
            case component_types::ROW_END;
            case component_types::SHOW_NAME;
            case component_types::SHOW_DESCRIPTION;
            case component_types::SHOW_FIELD_USAGE;
            case component_types::TEXT;
            case component_types::SYSTEM_CHANGE_LOG;
            case component_types::SYSTEM_TITLE;
            case component_types::SYSTEM_SUB_TITLE;
            case component_types::SYSTEM_SUB_TITLE_VAR;
            case component_types::SYSTEM_BODY_ABOUT;
            case component_types::SYSTEM_BODY_SETUP;
            case component_types::SYSTEM_BODY_SEARCH;
            case component_types::SYSTEM_BODY_SEARCH_FULL;
            case component_types::SYSTEM_BODY_SANDBOX;
            case component_types::SYSTEM_BODY_UNDO;
            case component_types::SYSTEM_BODY_PROCESS;
            case component_types::SYSTEM_BODY_PROCESS_PROGRESS;
            case component_types::SYSTEM_BODY_PROCESS_LIST;
            case component_types::SYSTEM_BODY_ERROR_LOG;
            case component_types::SYSTEM_BODY_ERROR_UPDATE;
            case component_types::LIST_PARENTS_OF_WORD;
            case component_types::LIST_CHILDREN_OF_WORD;
            case component_types::LINK_LIST_WORD;
            case component_types::RANK_PHRASE;
            case component_types::USED_IN_AS_TEXT;
            case component_types::USED_IN_AS_TEXT_WITH_LINK;
            case component_types::FORM_CLASS;
            case component_types::FORM_CHANGES;
            case component_types::FORM_IMPACT;
            case component_types::SYSTEM_PASTE_TABLE_CONTEXT;
            case component_types::SYSTEM_PASTE_TABLE_BODY;
            case component_types::SYSTEM_SELECTION_TEXT;
            case component_types::SYSTEM_POPUP_TITLE;
            case component_types::CALC_SHEET;
            case component_types::WORDS_UP;
            case component_types::WORDS_DOWN;
            case component_types::LINK;
            case component_types::JSON_EXPORT;
            case component_types::XML_EXPORT;
            case component_types::CSV_EXPORT;
            case component_types::ODS_EXPORT;
            case component_types::FORM_SELECT_FILE;
            case component_types::FORM_SELECT_FORMAT_EXPORT;
                $obj = $t_wrd->word_filled();
                break;
            case component_types::VERB_NAME;
            case component_types::FORM_SELECT_VERB;
            case component_types::FORM_SELECT_VERBS;
            case component_types::FORM_FIELD_PLURAL;
            case component_types::FORM_FIELD_REVERSE;
            case component_types::FORM_FIELD_PLURAL_REVERSE;
            case component_types::FORM_FIELD_NAME_IN_FORMULAS;
            case component_types::LIST_TRIPLES_OF_VERB;
            case component_types::LIST_FORMULAS_OF_VERB;
                $obj = $t_vrb->verb_filled();
                break;
            case component_types::TRIPLE_NAME;
            case component_types::FORM_FIELD_WEIGHT;
                $obj = $t_trp->triple_filled();
                break;
            case component_types::FORM_SELECT_SOURCE;
            case component_types::FORM_SELECT_SOURCES;
            case component_types::FORM_SELECT_SOURCE_TYPE;
            case component_types::LIST_VALUES_BY_SOURCE;
            case component_types::FORM_FIELD_URL;
                $obj = $t_src->source_filled();
                break;
            case component_types::FORM_SELECT_REF;
            case component_types::FORM_SELECT_REFS;
            case component_types::FORM_SELECT_REF_TYPE;
            case component_types::SYSTEM_SHOW_REF_TYPE;
            case component_types::SYSTEM_SHOW_REF_KEY;
            case component_types::SYSTEM_SHOW_REF_URL;
            case component_types::FORM_FIELD_EXTERNAL_KEY;
            case component_types::SYSTEM_SHOW_REF_SOURCE;
            case component_types::LIST_REF;
                $obj = $t_ref->ref_filled();
                break;
            case component_types::FORM_FIELD_VALUE;
            case component_types::FORM_SELECT_VALUE;
            case component_types::FORM_SELECT_VALUES;
            case component_types::FORM_SELECT_VALUE_TYPE;
            case component_types::VALUE_NAME;
            case component_types::VALUE_NUMERIC;
            case component_types::LIST_VALUES_BY_TRIPLE;
            case component_types::VALUES_RELATED;
            case component_types::NUMERIC_VALUE;
            case component_types::VALUES_ALL;
            case component_types::SYSTEM_BODY_VALUE_DETAIL;
                $obj = $t_val->value_16_filled();
                break;
            case component_types::FORM_FIELD_GROUP;
            case component_types::FORM_FIELD_GROUP_OR_PHRASES;
            case component_types::GROUP_NAME;
                $obj = $t_grp->group_filled();
                break;
            case component_types::FORM_FIELD_FORMULA_EXPRESSION;
            case component_types::FORM_FIELD_FORMULA_ALL_VAR_NEEDED;
            case component_types::FORM_LIST_FORMULAS;
            case component_types::FORM_SELECT_FORMULA;
            case component_types::FORM_SELECT_FORMULAS;
            case component_types::FORM_SELECT_FORMULA_TYPE;
            case component_types::LIST_PHRASES_OF_FORMULA;
            case component_types::LIST_FORMULAS;
            case component_types::SYSTEM_BODY_FORMULA_TEST;
            case component_types::LIST_RESULTS;
                $obj = $t_frm->formula_filled();
                break;
            case component_types::FORM_SELECT_FORMULA_LINK_TYPE;
            case component_types::FORM_SELECT_FORMULA_LINK_PRIORITY;
                $obj = $t_frm->formula_link_filled();
                break;
            case component_types::FORM_SELECT_TERM;
            case component_types::FORM_SELECT_TERMS;
                $obj = $t_msk->term_view_filled();
                break;
            case component_types::FORM_FIELD_SOURCE_GROUP;
            case component_types::FORM_FIELD_SOURCE_GROUP_OR_PHRASES;
            case component_types::FORM_SELECT_RESULT;
            case component_types::FORM_SELECT_RESULTS;
            case component_types::WORD_RESULTS;
            case component_types::FORMULA_RESULTS;
            case component_types::SYSTEM_SHOW_RESULT_DIFF;
            case component_types::SYSTEM_BODY_RESULT_EXPLAIN;
                $obj = $t_res->result_main_filled();
                break;
            case component_types::FORM_SELECT_VIEW;
            case component_types::FORM_SELECT_VIEWS;
            case component_types::FORM_SELECT_PARENT_VIEW;
            case component_types::FORM_SELECT_CHILD_VIEW;
            case component_types::FORM_SELECT_VIEW_DEFAULT;
            case component_types::FORM_SELECT_VIEW_TYPE;
            case component_types::FORM_SELECT_VIEW_STYLE;
            case component_types::FORM_TABLE_LINKED_VIEWS;
            case component_types::LIST_VIEWS;
            case component_types::SELECT_VIEW;
            case component_types::SYSTEM_SHOW_VIEW_DIFF;
            case component_types::VIEW_AFTER_CHANGE;
            case component_types::VIEW_BEFORE_CHANGE;
                $obj = $t_msk->view_filled();
                break;
            case component_types::SYSTEM_FIELD_PARENT_VIEW;
            case component_types::SYSTEM_FIELD_CHILD_VIEW;
            case component_types::SHOW_FIELD_RELATION_TYPE;
            case component_types::SHOW_FIELD_START_POS;
            case component_types::FORM_SELECT_VIEW_LINK_TYPE;
            case component_types::FORM_SELECT_VIEW_LINK_PRIORITY;
            case component_types::FORM_SELECT_VIEW_RELATION_TYPE;
            case component_types::FORM_FIELD_VIEW_RELATION_START_POS;
                $obj = $t_msk->view_relation_filled();
                break;
            case component_types::FORM_SELECT_COMPONENT;
            case component_types::FORM_SELECT_COMPONENTS;
            case component_types::FORM_SELECT_COMPONENT_TYPE;
            case component_types::FORM_SELECT_COMPONENT_STYLE;
                $obj = $t_cmp->component_filled();
                break;
            case component_types::FORM_SELECT_COMPONENT_LINK_TYPE;
            case component_types::FORM_SELECT_COMPONENT_POS_TYPE;
            case component_types::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER;
                $obj = $t_cmp->component_link_filled();
                break;
            default:
                log_warning('no object defined for component type ' . $typ->name);
        }
        return $obj;
    }

    /**
     * add the test server to a url query string
     * @param string $url_part the url query string
     * @return string the complete url for the test server
     */
    function test_url(string $url_part): string
    {
        return THIS_URL . api::MAIN_SCRIPT_EXT . url_var::PAR . $url_part;
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
        $url_array[] = [url_var::MASK, $msk_id];
        $t_usr = new test_users($this->env);
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_trp = new test_triples($this->env);
        $t_phr = new test_phrases($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $t_val = new test_values($this->env);
        $t_frm = new test_formulas($this->env);
        $t_res = new test_results($this->env);
        $t_msk = new test_views($this->env);
        $t_cmp = new test_components($this->env);
        $t_lan = new test_languages();
        $t_slg = new test_sys_log($this->env);
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
            case phrase::class;
                $obj = $t_phr->phrase_filled();
                $obj_array = $this->phrase_url($obj, $type);
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
            case language::class;
                $obj = $t_lan->language_filled();
                $obj_array = $this->language_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case sys_log::class;
                $obj = $t_slg->sys_log_filled();
                $obj_array = $this->system_log_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case phrase_list::class;
                $obj = $t_phr->phrase_list();
                $obj_array = $this->phrase_list_url($obj, $type);
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
        $t_usr = new test_users($this->env);
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
        $t_lan = new test_languages();
        $t_job = new test_jobs($this->env);
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
                $obj_array = $this->term_view_url($obj, $type);
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
            case language::class;
                $obj = $t_lan->language_filled();
                $obj_array = $this->language_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case job::class;
                $obj = $t_job->job_filled();
                $obj_array = $this->job_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                if ($msk_id == views::START_ID
                    or in_array($msk_id, views::CONFIRM_MASKS_IDS)) {
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
        $t_usr = new test_users($this->env);
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
        $t_lan = new test_languages();
        $t_job = new test_jobs($this->env);
        $t_slg = new test_sys_log($this->env);
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
                $obj_array = $this->term_view_url($obj, $type);
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
            case language::class;
                $obj = $t_lan->language_filled();
                $obj_array = $this->language_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case job::class;
                $obj = $t_job->job_filled();
                $obj_array = $this->job_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case sys_log::class;
                $obj = $t_slg->sys_log_filled();
                $obj_array = $this->system_log_url($obj);
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
        $t_usr = new test_users($this->env);
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
        $t_lan = new test_languages();
        $t_job = new test_jobs($this->env);
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
                $obj_array = $this->term_view_url($obj, $type);
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
            case language::class;
                $obj = $t_lan->language_filled();
                $obj_array = $this->language_url($obj, $type);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case job::class;
                $obj = $t_job->job_filled();
                $obj_array = $this->job_url($obj, $type);
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

    /**
     * get the filled url for a process step view (signup, login, login_activate, login_reset, logout, setup)
     * @param string $class the given main class name
     * @param int $msk_id the id of the mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string with only a few vars filled
     */
    function class_to_url_step(
        string       $class,
        int          $msk_id,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_array = [];
        $url_array[] = [url_var::MASK, $msk_id];
        $t_usr = new test_users($this->env);
        $t_phr = new test_phrases($this->env);
        $t_frm = new test_formulas($this->env);
        $t_job = new test_jobs($this->env);
        switch ($class) {
            case user::class;
                $obj = $t_usr->user_filled();
                $obj_array = $this->user_step_url($obj, $msk_id);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case formula::class;
                $obj = $t_frm->formula_filled();
                $obj_array = $this->formula_url($obj, $msk_id);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case job::class;
                $obj = $t_job->job_filled();
                $obj_array = $this->job_url($obj, $msk_id);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case phrase_list::class;
                $obj = $t_phr->phrase_list();
                $obj_array = $this->phrase_list_url($obj, $msk_id);
                $url_array = array_merge($url_array, $obj_array);
                break;
            case db_object::class;
                // setup and similar process step views don't need additional url vars
                break;
            default:
                log_err('no filled url object defined for step action ' . $class);
        }
        return $this->array_to_url_type($url_array, $type, $usr_msg);
    }

    /**
     * get the filled search url for the given class with a phrase list as context
     * @param string $class the given main class name
     * @param int $msk_id the id of the search mask
     * @param string $type the url type that should be created
     * @param user_message $usr_msg to enhance with messages to the user
     * @return string url with mask id, optional object name, and context phrase ids
     */
    function class_to_url_search(
        string       $class,
        int          $msk_id,
        string       $type,
        user_message $usr_msg
    ): string
    {
        $url_array = [];
        $url_array[] = [url_var::MASK, $msk_id];
        $t_phr = new test_phrases($this->env);
        $phr_lst = $t_phr->phrase_list();
        $obj_array = $this->phrase_list_url($phr_lst, $type);
        $url_array = array_merge($url_array, $obj_array);
        switch ($class) {
            case word::class;
                $t_wrd = new test_words($this->env);
                $obj = $t_wrd->word_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case triple::class;
                $t_trp = new test_triples($this->env);
                $obj = $t_trp->triple_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case phrase::class;
                $t_phr2 = new test_phrases($this->env);
                $obj = $t_phr2->phrase();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case verb::class;
                $t_vrb = new test_verbs($this->env);
                $obj = $t_vrb->verb_is();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case formula::class;
                $t_frm = new test_formulas($this->env);
                $obj = $t_frm->formula_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case term::class;
                $t_trm = new test_terms($this->env);
                $obj = $t_trm->term();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case language::class;
                $t_lan = new test_languages($this->env);
                $obj = $t_lan->language_filled();
                $url_array[] = [url_var::NAME, $obj->name()];
                break;
            case phrase_list::class;
                // phrase list is already encoded as context above
                break;
            case db_object::class;
                // search views without a specific object need no additional url vars
                break;
            default:
                log_err('no filled url object defined for search action ' . $class);
        }
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
        $url_array[] = [url_var::DESCRIPTION, $wrd->get_description()];
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
        $url_array[] = [url_var::USAGE, $wrd->usage];
        $url_array[] = [url_var::IMPACT, $wrd->impact];
        return $url_array;
    }

    private function verb_url(verb $vrb, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $vrb->name()];
        $url_array[] = [url_var::DESCRIPTION, $vrb->get_description()];
        $url_array[] = [url_var::PLURAL, $vrb->plural];
        $url_array[] = [url_var::REVERSE, $vrb->reverse];
        $url_array[] = [url_var::REVERSE_PLURAL, $vrb->rev_plural];
        $url_array[] = [url_var::FORMULA, $vrb->frm_name];
        $url_array[] = [url_var::USAGE, $vrb->usage];
        $url_array[] = [url_var::IMPACT, $vrb->impact];
        return $url_array;
    }

    private function triple_url(triple $trp, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $trp->name()];
        $url_array[] = [url_var::PHRASE_FROM, $trp->from_id()];
        $url_array[] = [url_var::VERB, $trp->get_verb_id()];
        $url_array[] = [url_var::PHRASE_TO, $trp->to_id()];
        $url_array[] = [url_var::NAME, $trp->name_given()];
        $url_array[] = [url_var::DESCRIPTION, $trp->get_description()];
        $url_array[] = [url_var::SHARE, $trp->share_id()];
        $url_array[] = [url_var::PROTECTION, $trp->protection_id()];
        $url_array[] = [url_var::VIEW, $trp->get_view_id()];
            $url_array[] = [url_var::USAGE, $trp->usage];
            $url_array[] = [url_var::IMPACT, $trp->impact];
        return $url_array;
    }

    private function phrase_url(phrase $trp, string $type): array
    {
        $phr_class = $trp->is_word() ? json_fields::CLASS_WORD : json_fields::CLASS_TRIPLE;
        $url_array = [];
        $url_array[] = [url_var::PHRASE_CLASS, $phr_class];
        $url_array[] = [url_var::NAME, $trp->name()];
        $url_array[] = [url_var::PHRASE_FROM, $trp->from_id()];
        $url_array[] = [url_var::VERB, $trp->get_verb_id()];
        $url_array[] = [url_var::PHRASE_TO, $trp->to_id()];
        $url_array[] = [url_var::NAME, $trp->name_given()];
        $url_array[] = [url_var::DESCRIPTION, $trp->get_description()];
        $url_array[] = [url_var::SHARE, $trp->share_id()];
        $url_array[] = [url_var::PROTECTION, $trp->protection_id()];
        $url_array[] = [url_var::VIEW, $trp->get_view_id()];
        $url_array[] = [url_var::USAGE, $trp->usage()];
        $url_array[] = [url_var::IMPACT, $trp->impact()];
        return $url_array;
    }

    private function source_url(source $src, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $src->name()];
        $url_array[] = [url_var::DESCRIPTION, $src->get_description()];
        $url_array[] = [url_var::URL, $src->url];
        $url_array[] = [url_var::TYPE, $src->type_id()];
        // TODO Prio 1 activate
        // $url_array[] = [url_var::VIEW, $src->get_view_id()];
        $url_array[] = [url_var::SHARE, $src->share_id()];
        $url_array[] = [url_var::PROTECTION, $src->protection_id()];
        $url_array[] = [url_var::USAGE, $src->usage];
        return $url_array;
    }

    private function ref_url(ref $ref, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::PHRASE, $ref->from_id()];
        $url_array[] = [url_var::EXTERNAL_KEY, $ref->get_external_key()];
        $url_array[] = [url_var::TYPE, $ref->predicate_id()];
        $url_array[] = [url_var::URL, $ref->get_url()];
        $url_array[] = [url_var::SOURCE, $ref->source_id()];
        $url_array[] = [url_var::DESCRIPTION, $ref->get_description()];
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
        $url_array[] = [url_var::NUMERIC_VALUE, $val->get_value()];
        $url_array[] = [url_var::SOURCE, $val->source_id()];
        $url_array[] = [url_var::SHARE, $val->share_id()];
        $url_array[] = [url_var::PROTECTION, $val->protection_id()];
        return $url_array;
    }

    private function group_url(group $grp, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $grp->name()];
        $url_array[] = [url_var::DESCRIPTION, $grp->get_description()];
        $url_array[] = [url_var::SOURCE, $grp->source_id()];
        $url_array[] = [url_var::SHARE, $grp->share_id()];
        $url_array[] = [url_var::PROTECTION, $grp->protection_id()];
        return $url_array;
    }

    private function phrase_list_url(phrase_list $phr_lst, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::CONTEXT, implode(',', $phr_lst->id_lst())];
        return $url_array;
    }

    private function formula_url(formula $frm, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $frm->name()];
        $url_array[] = [url_var::DESCRIPTION, $frm->get_description()];
        $url_array[] = [url_var::USER_EXPRESSION, $frm->get_usr_text()];
        // all need
        // update
        $url_array[] = [url_var::TYPE, $frm->type_id()];
        $url_array[] = [url_var::VIEW, $frm->get_view_id()];
        $url_array[] = [url_var::SHARE, $frm->share_id()];
        $url_array[] = [url_var::PROTECTION, $frm->protection_id()];
        $url_array[] = [url_var::USAGE, $frm->usage];
        $url_array[] = [url_var::IMPACT, $frm->impact];
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
        $url_array[] = [url_var::NUMERIC_VALUE, $res->get_value()];
        $url_array[] = [url_var::FORMULA, $res->formula_id()];
        $url_array[] = [url_var::SHARE, $res->share_id()];
        $url_array[] = [url_var::PROTECTION, $res->protection_id()];
        return $url_array;
    }

    private function view_url(view $msk, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $msk->name()];
        $url_array[] = [url_var::DESCRIPTION, $msk->get_description()];
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

    private function term_view_url(term_view $msk_lnk, string $type): array
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
        $url_array[] = [url_var::DESCRIPTION, $cmp->get_description()];
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

    private function language_url(language $lan, string $type): array
    {
        $url_array = [];
        $url_array[] = [url_var::NAME, $lan->name()];
        $url_array[] = [url_var::CODE_ID, $lan->code_id];
        $url_array[] = [url_var::DESCRIPTION, $lan->get_description()];
        $url_array[] = [url_var::LANGUAGE_SYMBOL, $lan->wiki_code];
        $url_array[] = [url_var::USAGE, $lan->usage];
        return $url_array;
    }

    private function user_step_url(user $usr, int $msk_id): array
    {
        $url_array = [];
        if ($msk_id == views::SIGNUP_ID) {
            $url_array[] = [url_var::USERNAME, $usr->name];
            $url_array[] = [url_var::EMAIL, $usr->email];
            $url_array[] = [url_var::USER_PASSWORD, users::TEST_USER_PASSWORD];
            $url_array[] = [url_var::USER_PASSWORD_RETYPE, users::TEST_USER_PASSWORD];
        } elseif ($msk_id == views::LOGIN_ID) {
            $url_array[] = [url_var::USERNAME, $usr->name];
            $url_array[] = [url_var::USER_PASSWORD, users::TEST_USER_PASSWORD];
        } elseif ($msk_id == views::LOGIN_ACTIVATE_ID) {
            $url_array[] = [url_var::ID, $usr->id()];
        } elseif ($msk_id == views::LOGIN_RESET_ID) {
            $url_array[] = [url_var::EMAIL, $usr->email];
        }
        // LOGOUT_ID: no additional params needed
        return $url_array;
    }

    private function system_log_url(sys_log $slg): array
    {
        $lib = new library();
        $url_array = [];
        $url_array[] = [url_var::USERNAME, $slg->usr->name()];
        $url_array[] = [url_var::LOG_TIME, $lib->time_to_url($slg->log_time)];
        $url_array[] = [url_var::LOG_FUNCTION, $slg->function_id];
        $url_array[] = [url_var::LOG_LEVEL, $slg->level_id];
        $url_array[] = [url_var::SYS_TRACE, $slg->log_trace];
        $url_array[] = [url_var::DESCRIPTION, $slg->log_text];
        $url_array[] = [url_var::LOG_STATUS, $slg->status_id];
        return $url_array;
    }

    private function job_url(job $job, int $msk_id): array
    {
        $lib = new library();
        $url_array = [];
        $url_array[] = [url_var::JOB, $job->id()];
        $url_array[] = [url_var::JOB_TYPE, $job->type_id()];
        $url_array[] = [url_var::JOB_STATUS, $job->status_id()];
        $url_array[] = [url_var::JOB_PRIORITY, $job->priority];
        $url_array[] = [url_var::JOB_PARAMETER, $job->parameter];
        $url_array[] = [url_var::JOB_CHANGE_FIELD, $job->change_field];
        $url_array[] = [url_var::JOB_ROW_ID, $job->row_id];
        if ($job->request_time !== null) {
            $url_array[] = [url_var::JOB_REQUEST_TIME, $lib->time_to_url($job->request_time)];
        }
        if ($job->start_time !== null) {
            $url_array[] = [url_var::JOB_START_TIME, $lib->time_to_url($job->start_time)];
        }
        if ($job->end_time !== null) {
            $url_array[] = [url_var::JOB_END_TIME, $lib->time_to_url($job->end_time)];
        }
        return $url_array;
    }

}