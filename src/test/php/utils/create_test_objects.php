<?php

/*

    test/utils/create_test_objects.php - create the standard object for testing
    ----------------------------------

    object adding, loading and testing functions

    create_* to create an object mainly used to shorten the code in unit tests
    add_* to create an object and save it in the database to prepare the testing (not used for all classes)
    load_* just load the object, but does not create the object
    test_* additional creates the object if needed and checks if it has been persistent

    * is for the name of the class, so the long name e.g. word not wrd


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

namespace test;

include_once API_REF_PATH . 'ref.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_VIEW_PATH . 'component.php';
include_once MODEL_VIEW_PATH . 'component_list.php';
include_once WEB_FORMULA_PATH . 'formula_display.php';
include_once WEB_VIEW_PATH . 'view_dsp_old.php';

use api\component_api;
use api\formula_api;
use api\phrase_group_api;
use api\ref_api;
use api\result_api;
use api\source_api;
use api\triple_api;
use api\type_lists_api;
use api\value_api;
use api\verb_api;
use api\view_api;
use api\word_api;
use api_message;
use html\word\word as word_dsp;
use cfg\formula_type;
use cfg\formula_type_list;
use cfg\job_type_list;
use cfg\language;
use cfg\language_form_list;
use cfg\language_list;
use cfg\phrase_type;
use cfg\protection_type_list;
use cfg\ref_type_list;
use cfg\share_type_list;
use cfg\source_type;
use cfg\source_type_list;
use cfg\type_lists;
use cfg\user_profile_list;
use cfg\verb_list;
use cfg\view_cmp_pos_type_list;
use cfg\view_cmp_type_list;
use cfg\view_type_list;
use cfg\word_type_list;
use controller\controller;
use controller\log\system_log_api;
use DateTime;
use formula\formula_dsp_old;
use html\view\view_dsp_old;
use model\batch_job;
use model\batch_job_list;
use model\change_log_action;
use model\change_log_field;
use model\change_log_list;
use model\change_log_named;
use model\change_log_table;
use model\component;
use model\component_list;
use model\figure;
use model\figure_list;
use model\formula;
use model\formula_element_type_list;
use model\formula_link;
use model\formula_link_type_list;
use model\formula_list;
use model\phrase;
use model\phrase_group;
use model\phrase_list;
use model\ref;
use model\result;
use model\result_list;
use model\source;
use model\sys_log_status;
use model\system_log;
use model\system_log_list;
use model\term;
use model\term_list;
use model\triple;
use model\triple_list;
use model\user;
use model\value;
use model\value_list;
use model\verb;
use model\view;
use model\view_cmp_type;
use model\component_link;
use model\view_list;
use model\word;
use model\word_list;

class create_test_objects extends test_base
{

    const DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';

    /*
     * dummy objects for unit tests
     */

    function dummy_type_lists_api(): type_lists_api
    {
        global $db_con;
        global $usr;
        $user_profiles = new user_profile_list();
        $phrase_types = new word_type_list();
        $formula_types = new formula_type_list();
        $formula_link_types = new formula_link_type_list();
        $formula_element_types = new formula_element_type_list();
        $view_types = new view_type_list();
        $component_types = new view_cmp_type_list();
        //$component_link_types = new component_link_type_list();
        $component_position_types = new view_cmp_pos_type_list();
        $ref_types = new ref_type_list();
        $source_types = new source_type_list();
        $share_types = new share_type_list();
        $protection_types = new protection_type_list();
        $languages = new language_list();
        $language_forms = new language_form_list();
        $sys_log_stati = new sys_log_status();
        $job_types = new job_type_list();
        $change_log_actions = new change_log_action();
        $change_log_tables = new change_log_table();
        $change_log_fields = new change_log_field();
        $verbs = new verb_list();

        $user_profiles->load_dummy();
        $phrase_types->load_dummy();
        $formula_types->load_dummy();
        $formula_link_types->load_dummy();
        $formula_element_types->load_dummy();
        $view_types->load_dummy();
        $component_types->load_dummy();
        //$component_link_types->load_dummy();
        $component_position_types->load_dummy();
        $ref_types->load_dummy();
        $source_types->load_dummy();
        $share_types->load_dummy();
        $protection_types->load_dummy();
        $languages->load_dummy();
        $language_forms->load_dummy();
        $sys_log_stati->load_dummy();
        $job_types->load_dummy();
        $change_log_actions->load_dummy();
        $change_log_tables->load_dummy();
        $change_log_fields->load_dummy();
        $verbs->load_dummy();

        $lst = new type_lists_api($db_con);
        $lst->add($user_profiles->api_obj(), controller::API_LIST_USER_PROFILES);
        $lst->add($phrase_types->api_obj(), controller::API_LIST_PHRASE_TYPES);
        $lst->add($formula_types->api_obj(), controller::API_LIST_FORMULA_TYPES);
        $lst->add($formula_link_types->api_obj(), controller::API_LIST_FORMULA_LINK_TYPES);
        $lst->add($formula_element_types->api_obj(), controller::API_LIST_FORMULA_ELEMENT_TYPES);
        $lst->add($view_types->api_obj(), controller::API_LIST_VIEW_TYPES);
        $lst->add($component_types->api_obj(), controller::API_LIST_COMPONENT_TYPES);
        //$lst->add($component_link_types->api_obj(), controller::API_LIST_VIEW_COMPONENT_LINK_TYPES);
        $lst->add($component_position_types->api_obj(), controller::API_LIST_COMPONENT_POSITION_TYPES);
        $lst->add($ref_types->api_obj(), controller::API_LIST_REF_TYPES);
        $lst->add($source_types->api_obj(), controller::API_LIST_SOURCE_TYPES);
        $lst->add($share_types->api_obj(), controller::API_LIST_SHARE_TYPES);
        $lst->add($protection_types->api_obj(), controller::API_LIST_PROTECTION_TYPES);
        $lst->add($languages->api_obj(), controller::API_LIST_LANGUAGES);
        $lst->add($language_forms->api_obj(), controller::API_LIST_LANGUAGE_FORMS);
        $lst->add($sys_log_stati->api_obj(), controller::API_LIST_SYS_LOG_STATI);
        $lst->add($job_types->api_obj(), controller::API_LIST_JOB_TYPES);
        $lst->add($change_log_actions->api_obj(), controller::API_LIST_CHANGE_LOG_ACTIONS);
        $lst->add($change_log_tables->api_obj(), controller::API_LIST_CHANGE_LOG_TABLES);
        $lst->add($change_log_fields->api_obj(), controller::API_LIST_CHANGE_LOG_FIELDS);
        $lst->add($verbs->api_obj(), controller::API_LIST_VERBS);

        $system_views = $this->dummy_view_list();
        $lst->add($system_views->api_obj(), controller::API_LIST_SYSTEM_VIEWS);

        return $lst;
    }

    function dummy_user(): user
    {
        $usr = new user();
        $usr->set(2, user::SYSTEM_TEST_NAME, user::SYSTEM_TEST_EMAIL);
        return $usr;
    }

    function dummy_word(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_READ);
        $wrd->description = word_api::TD_READ;
        $wrd->set_type(phrase_type::MATH_CONST);
        return $wrd;
    }

    function dummy_word_dsp(): word_dsp
    {
        $wrd = $this->dummy_word();
        return new word_dsp($wrd->api_json());
    }

    function dummy_word_pi(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(2, word_api::TN_CONST);
        $wrd->description = word_api::TD_CONST;
        $wrd->set_type(phrase_type::MATH_CONST);
        return $wrd;
    }

    function dummy_word_2019(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(9, word_api::TN_2019);
        return $wrd;
    }

    function dummy_word_mio(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(155, word_api::TN_MIO_SHORT);
        return $wrd;
    }

    function dummy_word_ch(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(181, word_api::TN_CH);
        return $wrd;
    }

    function dummy_word_canton(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(182, word_api::TN_CANTON);
        return $wrd;
    }

    function dummy_word_city(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(183, word_api::TN_CITY);
        return $wrd;
    }

    function dummy_word_zh(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(184, word_api::TN_ZH);
        return $wrd;
    }

    function dummy_word_inhabitant(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(186, word_api::TN_INHABITANT);
        return $wrd;
    }

    function dummy_word_list(): word_list
    {
        global $usr;
        $lst = new word_list($usr);
        $lst->add($this->dummy_word());
        return $lst;
    }

    /**
     * @return verb a user defined verb
     */
    function dummy_verb(): verb
    {
        global $usr;
        $vrb = new verb(1, verb_api::TN_READ, verb_api::TC_READ);
        $vrb->set_user($usr);
        return $vrb;
    }

    /**
     * @return verb a standard verb with user null
     */
    function dummy_verb_is(): verb
    {
        return new verb(2, verb_api::TN_IS_A, verb_api::TC_IS_A);
    }

    function dummy_triple(): triple
    {
        global $usr;
        // create first the words used for the triple
        $wrd_math = $this->dummy_word();
        $vrb = $this->dummy_verb_is();
        $wrd_pi = new word($usr);
        $wrd_pi->set(2, word_api::TN_READ);

        // create the triple itself
        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_READ_NAME);
        $trp->set_from($wrd_pi->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($wrd_math->phrase());
        $trp->set_type(phrase_type::MATH_CONST);
        return $trp;
    }

    function dummy_triple_list(): triple_list
    {
        global $usr;
        $lst = new triple_list($usr);
        $lst->add($this->dummy_triple());
        return $lst;
    }

    function dummy_phrase(): phrase
    {
        return $this->dummy_word()->phrase();
    }

    function dummy_phrase_triple(): phrase
    {
        return $this->dummy_triple()->phrase();
    }

    function dummy_phrase_list(): phrase_list
    {
        global $usr;
        $lst = new phrase_list($usr);
        $lst->add($this->dummy_phrase());
        $lst->add($this->dummy_phrase_triple());
        return $lst;
    }

    function dummy_phrase_group(): phrase_group
    {
        global $usr;
        $lst = $this->dummy_phrase_list();
        $grp = $lst->get_grp();
        $grp->grp_name = phrase_group_api::TN_READ;
        return $grp;
    }

    function dummy_term(): term
    {
        return $this->dummy_word()->term();
    }

    function dummy_term_triple(): term
    {
        return $this->dummy_triple()->term();
    }

    function dummy_term_formula(): term
    {
        return $this->dummy_formula()->term();
    }

    function dummy_term_verb(): term
    {
        return $this->dummy_verb()->term();
    }

    function dummy_term_list(): term_list
    {
        global $usr;
        $lst = new term_list($usr);
        $lst->add($this->dummy_term());
        $lst->add($this->dummy_term_triple());
        $lst->add($this->dummy_term_formula());
        $lst->add($this->dummy_term_verb());
        return $lst;
    }

    function dummy_value(): value
    {
        global $usr;
        $grp = new phrase_group($usr, 1, array(phrase_group_api::TN_READ));
        return new value($usr, 1, round(value_api::TV_READ, 13), $grp);
    }

    function dummy_value_list(): value_list
    {
        global $usr;
        $lst = new value_list($usr);
        $lst->add($this->dummy_value());
        return $lst;
    }

    function dummy_formula(): formula
    {
        global $usr;
        $frm = new formula($usr);
        $frm->set(1, formula_api::TN_READ);
        $frm->set_user_text(formula_api::TF_READ);
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    function dummy_formula_list(): formula_list
    {
        global $usr;
        $lst = new formula_list($usr);
        $lst->add($this->dummy_formula());
        return $lst;
    }

    function dummy_result(): result
    {
        global $usr;
        $res = new result($usr);
        $wrd = $this->dummy_word();
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd->phrase());
        $res->set_id(1);
        $res->phr_lst = $phr_lst;
        $res->value = result_api::TV_INT;
        return $res;
    }

    function dummy_result_pct(): result
    {
        global $usr;
        $res = new result($usr);
        $wrd_pct = $this->new_word(word_api::TN_PCT, 2, phrase_type::PERCENT);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd_pct->phrase());
        $res->phr_lst = $phr_lst;
        $res->value = 0.01234;
        return $res;
    }

    function dummy_result_list(): result_list
    {
        global $usr;
        $lst = new result_list($usr);
        $lst->add($this->dummy_result());
        $lst->add($this->dummy_result_pct());
        return $lst;
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function dummy_figure_value(): figure
    {
        $val = $this->dummy_value();
        return $val->figure();
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function dummy_figure_result(): figure
    {
        $res = $this->dummy_result();
        return $res->figure();
    }

    function dummy_figure_list(): figure_list
    {
        global $usr;
        $lst = new figure_list($usr);
        $lst->add($this->dummy_figure_value());
        $lst->add($this->dummy_figure_result());
        return $lst;
    }

    function dummy_source(): source
    {
        global $usr;
        $src = new source($usr);
        $src->set(2, source_api::TN_READ_API, source_type::PDF);
        $src->description = source_api::TD_READ_API;
        $src->url = source_api::TU_READ_API;
        return $src;
    }

    function dummy_source1(): source
    {
        global $usr;
        $src = new source($usr);
        $src->set(1, source_api::TN_READ_API, source_type::PDF);
        $src->description = source_api::TD_READ_API;
        $src->url = source_api::TU_READ_API;
        return $src;
    }

    function dummy_reference(): ref
    {
        global $usr;
        $ref = new ref($usr);
        $ref->set(3);
        $ref->phr = $this->dummy_word_pi()->phrase();
        $ref->source = $this->dummy_source1();
        $ref->external_key = ref_api::TK_READ;
        $ref->url = ref_api::TU_READ;
        $ref->description = ref_api::TD_READ;
        return $ref;
    }

    function dummy_view(): view
    {
        global $usr;
        $dsp = new view($usr);
        $dsp->set(1, view_api::TN_READ);
        $dsp->description = view_api::TD_READ;
        $dsp->code_id = view_api::TI_READ;
        return $dsp;
    }

    function dummy_view_with_components(): view
    {
        $dsp = $this->dummy_view();
        $dsp->cmp_lst = $this->dummy_component_list();
        return $dsp;
    }

    function dummy_view_word_add(): view
    {
        global $usr;
        $dsp = new view($usr);
        $dsp->set(2, view_api::TN_FORM);
        $dsp->description = view_api::TD_FORM;
        $dsp->code_id = view_api::TI_FORM;
        $dsp->cmp_lst = $this->dummy_components_word_add();
        return $dsp;
    }

    function dummy_view_list(): view_list
    {
        global $usr;
        $lst = new view_list($usr);
        $lst->add($this->dummy_view_with_components());
        $lst->add($this->dummy_view_word_add());
        return $lst;
    }

    function dummy_component(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(1, component_api::TN_READ, view_cmp_type::PHRASE_NAME);
        $cmp->description = component_api::TD_READ;
        return $cmp;
    }

    function dummy_component_word_add_title(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(1, component_api::TN_FORM_TITLE, view_cmp_type::FORM_TITLE);
        $cmp->description = component_api::TD_FORM_TITLE;
        $cmp->code_id = component_api::TI_FORM_TITLE;
        return $cmp;
    }

    function dummy_component_word_add_back_stack(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(2, component_api::TN_FORM_BACK, view_cmp_type::FORM_BACK);
        $cmp->description = component_api::TD_FORM_BACK;
        $cmp->code_id = component_api::TI_FORM_BACK;
        return $cmp;
    }

    function dummy_component_word_add_button_confirm(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(3, component_api::TN_FORM_CONFIRM, view_cmp_type::FORM_CONFIRM);
        $cmp->description = component_api::TD_FORM_CONFIRM;
        $cmp->code_id = component_api::TI_FORM_CONFIRM;
        return $cmp;
    }

    function dummy_component_word_add_name(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(4, component_api::TN_FORM_NAME, view_cmp_type::FORM_NAME);
        $cmp->description = component_api::TD_FORM_NAME;
        $cmp->code_id = component_api::TI_FORM_NAME;
        return $cmp;
    }

    function dummy_component_word_add_description(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(5, component_api::TN_FORM_DESCRIPTION, view_cmp_type::FORM_DESCRIPTION);
        $cmp->description = component_api::TD_FORM_DESCRIPTION;
        $cmp->code_id = component_api::TI_FORM_DESCRIPTION;
        return $cmp;
    }

    function dummy_component_word_add_cancel(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(6, component_api::TN_FORM_CANCEL, view_cmp_type::FORM_CANCEL);
        $cmp->description = component_api::TD_FORM_CANCEL;
        $cmp->code_id = component_api::TI_FORM_CANCEL;
        return $cmp;
    }

    function dummy_component_word_add_save(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(7, component_api::TN_FORM_SAVE, view_cmp_type::FORM_SAVE);
        $cmp->description = component_api::TD_FORM_SAVE;
        $cmp->code_id = component_api::TI_FORM_SAVE;
        return $cmp;
    }

    function dummy_component_word_add_form_end(): component
    {
        global $usr;
        $cmp = new component($usr);
        $cmp->set(8, component_api::TN_FORM_END, view_cmp_type::FORM_END);
        $cmp->description = component_api::TD_FORM_END;
        $cmp->code_id = component_api::TI_FORM_END;
        return $cmp;
    }

    function dummy_component_list(): component_list
    {
        global $usr;
        $lst = new component_list($usr);
        $lst->add($this->dummy_component());
        return $lst;
    }

    function dummy_components_word_add(): component_list
    {
        global $usr;
        $lst = new component_list($usr);
        $lst->add($this->dummy_component_word_add_title());
        $lst->add($this->dummy_component_word_add_back_stack());
        $lst->add($this->dummy_component_word_add_button_confirm());
        $lst->add($this->dummy_component_word_add_name());
        $lst->add($this->dummy_component_word_add_description());
        $lst->add($this->dummy_component_word_add_cancel());
        $lst->add($this->dummy_component_word_add_save());
        $lst->add($this->dummy_component_word_add_form_end());
        return $lst;
    }

    function dummy_language(): language
    {
        return new language(language::DEFAULT, language::TN_READ, 'English is the default', 1);
    }

    /**
     * @return change_log_named a change log entry of a named user sandbox object with some dummy values
     */
    function dummy_log_named(): change_log_named
    {
        global $usr_sys;

        $chg = new change_log_named();
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_log_action::ADD);
        $chg->set_table(change_log_table::WORD);
        $chg->set_field(change_log_field::FLD_WORD_NAME);
        $chg->new_value = word_api::TN_READ;
        $chg->row_id = 1;
        $chg->usr = $usr_sys;
        return $chg;
    }

    /**
     * @return system_log a system error entry
     */
    function dummy_sys_log(): system_log
    {
        global $sys_log_stati;
        $sys = new system_log();
        $sys->set_id(1);
        $sys->log_time = new DateTime(system_log_api::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = system_log_api::TV_LOG_TEXT;
        $sys->log_trace = system_log_api::TV_LOG_TRACE;
        $sys->function_name = system_log_api::TV_FUNC_NAME;
        $sys->solver_name = system_log_api::TV_SOLVE_ID;
        $sys->status_name = $sys_log_stati->id(sys_log_status::OPEN);
        return $sys;
    }

    /**
     * @return system_log a system error entry
     */
    function dummy_sys_log2(): system_log
    {
        global $sys_log_stati;
        $sys = new system_log();
        $sys->set_id(2);
        $sys->log_time = new DateTime(system_log_api::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = system_log_api::T2_LOG_TEXT;
        $sys->log_trace = system_log_api::T2_LOG_TRACE;
        $sys->function_name = system_log_api::T2_FUNC_NAME;
        $sys->solver_name = system_log_api::TV_SOLVE_ID;
        $sys->status_name = $sys_log_stati->id(sys_log_status::CLOSED);
        return $sys;
    }

    /**
     * @return batch_job a batch job entry with some dummy values
     */
    function dummy_job(): batch_job
    {
        $sys_usr = $this->system_user();
        $job = new batch_job($sys_usr, new DateTime(system_log_api::TV_TIME));
        $job->set_id(1);
        $job->start_time = new DateTime(system_log_api::TV_TIME);
        $job->set_type(job_type_list::BASE_IMPORT);
        $job->row_id = 1;
        return $job;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     *
     * TODO add at least one sample for rename and delete
     * TODO add at least one sample for verb, triple, value, formula, source, ref, view and component
     */
    function dummy_change_log_list_named(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->dummy_log_named());
        return $log_lst;
    }

    /**
     * @return system_log_list a list of system error entries with some dummy values
     */
    function dummy_system_log_list(): system_log_list
    {
        $sys_lst = new system_log_list();
        $sys_lst->add($this->dummy_sys_log());
        $sys_lst->add($this->dummy_sys_log2());
        return $sys_lst;
    }

    /**
     * @return batch_job_list a list of batch job entries with some dummy values
     */
    function dummy_job_list(): batch_job_list
    {
        $sys_usr = $this->system_user();
        $job_lst = new batch_job_list($sys_usr);
        $job_lst->add($this->dummy_job());
        return $job_lst;
    }

    /**
     * @return user the system user for the database updates
     */
    function system_user(): user
    {
        $sys_usr = new user;
        $sys_usr->set_id(SYSTEM_USER_ID);
        $sys_usr->name = "zukunft.com system";
        $sys_usr->code_id = 'system';
        $sys_usr->dec_point = ".";
        $sys_usr->thousand_sep = "'";
        $sys_usr->percent_decimals = 2;
        $sys_usr->profile_id = 5;
        return $sys_usr;
    }


    /**
     * set the all values of the frontend object based on a backend object using the api object
     * @param object $model_obj the frontend object with the values of the backend object
     */
    function dsp_obj(object $model_obj, object $dsp_obj): object
    {
        $dsp_obj->set_from_json($model_obj->api_obj()->get_json());
        return $dsp_obj;
    }


    /*
     * word
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the name of the word that should be created
     * @param int $id to force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the created word object
     */
    function new_word(string $wrd_name, int $id = 0, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        global $usr;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $wrd = new word($test_usr);
        $wrd->set_id($id);
        $wrd->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $wrd->set_type($wrd_type_code_id);
        }
        return $wrd;
    }

    /**
     * load a word from the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word loaded from the database by name
     */
    function load_word(string $wrd_name, ?user $test_usr = null): word
    {
        global $usr;
        if ($test_usr == null) {
            $test_usr = $usr;
        }
        $wrd = new word($test_usr);
        $wrd->load_by_name($wrd_name);
        return $wrd;
    }

    /**
     * save the just created word object in the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function add_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        global $phrase_types;
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $wrd->save();
        }
        if ($wrd->id() <= 0) {
            log_err('Cannot create word ' . $wrd_name);
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = $phrase_types->id($wrd_type_code_id);
            $wrd->save();
        }
        return $wrd;
    }

    /**
     * check if a word object could have been added to the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function test_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->add_word($wrd_name, $wrd_type_code_id, $test_usr);
        $target = $wrd_name;
        $this->display('testing->add_word', $target, $wrd->name());
        return $wrd;
    }

    /*
     * triple test creation
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the given name of the triple that should be created
     * @param string $from_name the name of the child word e.g. zurich
     * @param string $verb_code_id the code id of the child to parent relation e.g. is a
     * @param string $to_name the name of the parent word e.g. city
     * @param int|null $id t force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return triple the created triple object
     */
    function new_triple(string  $wrd_name,
                        string  $from_name,
                        string  $verb_code_id,
                        string  $to_name,
                        int     $id = 0,
                        ?string $wrd_type_code_id = null,
                        ?user   $test_usr = null): triple
    {
        global $usr;
        global $verbs;
        global $phrase_types;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $trp = new triple($test_usr);
        $trp->set_id($id);
        $trp->from = $this->new_word($from_name)->phrase();
        $trp->verb = $verbs->get_verb($verb_code_id);
        $trp->to = $this->new_word($to_name)->phrase();
        $trp->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $trp->type_id = $phrase_types->id($wrd_type_code_id);
        }
        return $trp;
    }

    function load_triple(string $from_name,
                         string $verb_code_id,
                         string $to_name): triple
    {
        global $usr;
        global $verbs;

        $wrd_from = $this->load_word($from_name);
        $wrd_to = $this->load_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new triple($usr);
        if ($from->id() > 0 and $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->load_by_link($from->id(), $vrb->id(), $to->id());
        }
        return $lnk_test;
    }

    /**
     * check if a triple exists and if not create it if requested
     * @param string $from_name a phrase name
     * @param string $to_name a phrase name
     * @param string $target the expected name of the triple
     * @param string $name_given the name that the triple should be set to
     * @param bool $auto_create if true the related words should be created if the phrase does not exist
     * @return triple the loaded or created triple
     */
    function test_triple(string $from_name,
                         string $verb_code_id,
                         string $to_name,
                         string $target = '',
                         string $name_given = '',
                         bool   $auto_create = true): triple
    {
        global $usr;
        global $verbs;

        $result = new triple($usr);

        // load the phrases to link and create words if needed
        $from = $this->load_phrase($from_name);
        if ($from->id() == 0 and $auto_create) {
            $from = $this->add_word($from_name)->phrase();
        }
        if ($from->id() == 0) {
            log_err('Cannot get phrase ' . $from_name);
        }
        $to = $this->load_phrase($to_name);
        if ($to->id() == 0 and $auto_create) {
            $to = $this->add_word($to_name)->phrase();
        }
        if ($to->id() == 0) {
            log_err('Cannot get phrase ' . $to_name);
        }

        // load the verb
        $vrb = $verbs->get_verb($verb_code_id);

        // check if the triple exists or create a new if needed
        $trp = new triple($usr);
        if ($from->id() == 0 or $to->id() == 0) {
            log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            // check if the forward link exists
            $trp->load_by_link($from->id(), $vrb->id(), $to->id());
            if ($trp->id() > 0) {
                // refresh the given name if needed
                if ($name_given <> '' and $trp->name(true) <> $name_given) {
                    $trp->set_name_given($name_given);
                    $trp->save();
                    $trp->load_by_id($trp->id());
                }
                $result = $trp;
            } else {
                // check if the backward link exists
                $trp->from = $to;
                $trp->verb = $vrb;
                $trp->to = $from;
                $trp->set_user($usr);
                $trp->load_by_link($to->id(), $vrb->id(), $from->id());
                $result = $trp;
                // create the link if requested
                if ($trp->id() <= 0 and $auto_create) {
                    $trp->from = $from;
                    $trp->verb = $vrb;
                    $trp->to = $to;
                    if ($trp->name(true) <> $name_given) {
                        $trp->set_name_given($name_given);
                    }
                    $trp->save();
                    $trp->load_by_id($trp->id());
                }
            }
        }

        // assume the target name if not given
        $result_text = '';
        if ($trp->id() > 0) {
            $result_text = $trp->name(true);
            if ($target == '') {
                $target = $trp->name(true);
            }
        }

        $this->display('test_triple', $target, $result_text, TIMEOUT_LIMIT_DB);
        return $result;
    }

    function del_triple(string $from_name,
                        string $verb_code_id,
                        string $to_name): bool
    {
        $trp = $this->load_triple($from_name, $verb_code_id, $to_name);
        if ($trp->id() <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }


    /*
     * formula test creation
     */

    /**
     * create a new formula e.g. for unit testing with a given type
     *
     * @param string $frm_name the name of the formula that should be created
     * @param int $id to force setting the id for unit testing
     * @param string|null $frm_type_code_id the id of the predefined formula type which the new formula should have
     * @param user|null $test_usr if not null the user for whom the formula should be created to test the user sandbox
     * @return formula the created formula object
     */
    function new_formula(string $frm_name, int $id = 0, ?string $frm_type_code_id = null, ?user $test_usr = null): formula
    {
        global $usr;
        global $formula_types;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $frm = new formula($test_usr);
        $frm->set_id($id);
        $frm->set_name($frm_name);

        if ($frm_type_code_id != null) {
            $frm->type_id = $formula_types->id($frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        global $usr;
        $frm = new formula_dsp_old($usr);
        $frm->load_by_name($frm_name, formula::class);
        return $frm;
    }

    /**
     * get or create a formula
     */
    function add_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->load_formula($frm_name);
        if ($frm->id() == 0) {
            $frm->set_name($frm_name);
            $frm->usr_text = $frm_text;
            $frm->generate_ref_text();
            $frm->save();
        }
        return $frm;
    }

    function test_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->add_formula($frm_name, $frm_text);
        $this->display('formula', $frm_name, $frm->name());
        return $frm;
    }


    /*
     * reference test creation
     */

    function load_ref(string $wrd_name, string $type_name): ref
    {
        global $usr;

        $wrd = $this->load_word($wrd_name);
        $phr = $wrd->phrase();

        $lst = new ref_type_list();
        $ref = new ref($usr);
        $ref->phr = $phr;
        $ref->ref_type = $lst->get_ref_type($type_name);
        if ($phr->id() != 0) {
            $ref->load_obj_vars();
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $lst = new ref_type_list();
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id() == 0) {
            $ref->phr = $phr;
            $ref->ref_type = $lst->get_ref_type($type_name);
            $ref->external_key = $external_key;
            $ref->save();
        }
        $target = $external_key;
        $this->display('ref', $target, $ref->external_key);
        return $ref;
    }

    function load_phrase(string $phr_name): phrase
    {
        global $usr;
        $phr = new phrase($usr);
        $phr->load_by_name($phr_name);
        $phr->load_obj();
        return $phr;
    }

    /**
     * test if a phrase with the given name exists, but does not create it, if it has not yet been created
     * @param string $phr_name name of the phrase to test
     * @return phrase the loaded phrase object
     */
    function test_phrase(string $phr_name): phrase
    {
        $phr = $this->load_phrase($phr_name);
        $this->display('phrase', $phr_name, $phr->name(true));
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list(array $array_of_word_str): word_list
    {
        global $usr;
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names($array_of_word_str);
        return $wrd_lst;
    }

    function test_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = $this->load_word_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $wrd_lst->name();
        $this->display(', word list', $target, $result);
        return $wrd_lst;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_phrase_list(array $array_of_word_str): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names($array_of_word_str);
        return $phr_lst;
    }

    function test_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->display(', phrase list', $target, $result);
        return $phr_lst;
    }

    /**
     * load a phrase group by the list of phrase names
     * @param array $array_of_phrase_str with the names of the words or triples
     * @return phrase_group
     */
    function load_phrase_group(array $array_of_phrase_str): phrase_group
    {
        return $this->load_phrase_list($array_of_phrase_str)->get_grp();
    }

    /**
     * load a phrase group by the name
     * which can be either the name set by the users
     * or the automatically created name based on the phrases
     * @param string $phrase_group_name
     * @return phrase_group
     */
    function load_phrase_group_by_name(string $phrase_group_name): phrase_group
    {
        global $usr;
        $phr_grp = new phrase_group($usr);
        $phr_grp->grp_name = $phrase_group_name;
        $phr_grp->load();
        return $phr_grp;
    }

    /**
     * add a phrase group to the database
     * @param array $array_of_phrase_str the phrase names
     * @param string $phrase_group_name the name that should be shown to the user
     * @return phrase_group the phrase group object including the database is
     */
    function add_phrase_group(array $array_of_phrase_str, string $phrase_group_name): phrase_group
    {
        global $usr;
        $phr_grp = new phrase_group($usr);
        $phr_grp->phr_lst = $this->load_phrase_list($array_of_phrase_str);
        $phr_grp->grp_name = $phrase_group_name;
        $phr_grp->get();
        return $phr_grp;
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $val = new value($usr);
        $val->load_by_id($id, value::class);
        return $val;
    }

    function load_value(array $array_of_word_str): value
    {
        global $usr;

        // the time separation is done here until there is a phrase series value table that can be used also to time phrases
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $phr_grp = $phr_lst->get_grp();

        $val = new value($usr);
        if ($phr_grp == null) {
            log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->load_by_grp($phr_grp);
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        global $usr;
        $val = $this->load_value($array_of_word_str);
        if ($val->id() == 0) {
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $phr_grp = $phr_lst->get_grp();

            // getting the latest value if selected without time phrase should be done when reading the value
            //$time_phr = $phr_lst->time_useful();
            //$phr_lst->ex_time();

            $val = new value($usr);
            if ($phr_grp == null) {
                log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
            } else {
                $val->grp = $phr_grp;
            }
            $val->set_number($target);
            $val->save();
        }

        return $val;
    }

    function test_value(array $array_of_word_str, float $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->number();
        $this->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function load_value_by_phr_grp(phrase_group $phr_grp): value
    {
        global $usr;

        $val = new value($usr);
        $val->load_by_grp($phr_grp);
        return $val;
    }

    function add_value_by_phr_grp(phrase_group $phr_grp, float $target): value
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if ($val->id() == 0) {
            $val->grp = $phr_grp;
            $val->set_number($target);
            $val->save();
        }

        return $val;
    }

    function test_value_by_phr_grp(phrase_group $phr_grp, float $target): value
    {
        $val = $this->add_value_by_phr_grp($phr_grp, $target);
        $result = $val->number();
        $this->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    /**
     * create a new verb e.g. for unit testing with a given type
     *
     * @param string $vrb_name the name of the verb that should be created
     * @param int $id to force setting the id for unit testing
     * @return verb the created verb object
     */
    function new_verb(string $vrb_name, int $id = 0): verb
    {
        global $usr;
        if ($id == null) {
            $id = $this->next_seq_nbr();
        }

        $vrb = new verb();
        $vrb->set_id($id);
        $vrb->set_name($vrb_name);
        $vrb->set_user($usr);

        return $vrb;
    }


    /*
     * source test creation
     */

    function load_source(string $src_name): source
    {
        global $usr;
        $src = new source($usr);
        $src->load_by_name($src_name);
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id() == 0) {
            $src->set_name($src_name);
            $src->save();
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->display('source', $src_name, $src->name());
        return $src;
    }

    /**
     * @return array json message to test if adding a new word via the api works fine
     */
    function word_put_json(): array
    {
        global $db_con;
        global $phrase_types;
        $msg = new api_message($db_con, word::class);
        $wrd = new word_api();
        $wrd->name = word_api::TN_ADD_API;
        $wrd->description = word_api::TD_ADD_API;
        $wrd->type_id = $phrase_types->id(phrase_type::NORMAL);
        $msg->add_body($wrd);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if updating of a word via the api works fine
     */
    function word_post_json(): array
    {
        global $db_con;
        $msg = new api_message($db_con, word::class);
        $wrd = new word_api();
        $wrd->name = word_api::TN_UPD_API;
        $wrd->description = word_api::TD_UPD_API;
        $msg->add_body($wrd);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if adding a new source via the api works fine
     */
    function source_put_json(): array
    {
        global $db_con;
        global $source_types;
        $msg = new api_message($db_con, source::class);
        $src = new source_api();
        $src->name = source_api::TN_ADD_API;
        $src->description = source_api::TD_ADD_API;
        $src->url = source_api::TU_ADD_API;
        $src->type_id = $source_types->id(source_type::PDF);
        $msg->add_body($src);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if updating of a source via the api works fine
     */
    function source_post_json(): array
    {
        global $db_con;
        $msg = new api_message($db_con, source::class);
        $src = new source_api();
        $src->name = source_api::TN_UPD_API;
        $src->description = source_api::TD_UPD_API;
        $msg->add_body($src);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if adding a new reference via the api works fine
     */
    function reference_put_json(): array
    {
        global $db_con;
        global $reference_types;
        $msg = new api_message($db_con, ref::class);
        $ref = new ref_api();
        $ref->phrase_id = $this->dummy_word()->phrase()->id();
        $ref->external_key = ref_api::TK_ADD_API;
        $ref->description = ref_api::TD_ADD_API;
        $ref->url = ref_api::TU_ADD_API;
        $ref->type_id = $reference_types->id(source_type::PDF);
        $msg->add_body($ref);
        return $msg->get_json_array();
    }

    /*
     * view test creation
     */

    /**
     * load a view and if the test user is set for a specific user
     */
    function load_view(string $dsp_name, ?user $test_usr = null): view
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $dsp = new view_dsp_old($test_usr);
        $dsp->load_by_name($dsp_name, view::class);
        return $dsp;
    }

    function add_view(string $dsp_name, ?user $test_usr = null): view
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $dsp = $this->load_view($dsp_name, $test_usr);
        if ($dsp->id() == 0) {
            $dsp->set_user($test_usr);
            $dsp->set_name($dsp_name);
            $dsp->save();
        }
        return $dsp;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        $dsp = $this->add_view($dsp_name, $test_usr);
        $this->display('view', $dsp_name, $dsp->name());
        return $dsp;
    }


    /*
     * component test creation
     */

    function load_component(string $cmp_name, ?user $test_usr = null): component
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = new component($test_usr);
        $cmp->load_by_name($cmp_name, component::class);
        return $cmp;
    }

    function add_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): component
    {
        global $usr;
        global $component_types;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = $this->load_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $component_types->id($type_code_id);
            }
            $cmp->save();
        }
        return $cmp;
    }

    function test_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): component
    {
        $cmp = $this->add_component($cmp_name, $type_code_id, $test_usr);
        $this->display('view component', $cmp_name, $cmp->name());
        return $cmp;
    }

    function test_view_cmp_lnk(string $dsp_name, string $cmp_name, int $pos): component_link
    {
        global $usr;
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        $lnk = new component_link($usr);
        $lnk->fob = $dsp;
        $lnk->tob = $cmp;
        $lnk->order_nbr = $pos;
        $result = $lnk->save();
        $target = '';
        $this->display('view component link', $target, $result);
        return $lnk;
    }

    function test_view_cmp_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        if ($dsp->id() > 0 and $cmp->id() > 0) {
            $result = $cmp->unlink($dsp);
        }
        return $result;
    }

    function test_formula_link(string $formula_name, string $word_name, bool $autocreate = true): string
    {
        global $usr;

        $result = '';

        $frm = new formula($usr);
        $frm->load_by_name($formula_name, formula::class);
        $phr = new word($usr);
        $phr->load_by_name($word_name, word::class);
        if ($frm->id() > 0 and $phr->id() <> 0) {
            $frm_lnk = new formula_link($usr);
            $frm_lnk->fob = $frm;
            $frm_lnk->tob = $phr;
            $frm_lnk->load_obj_vars();
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->fob->name() . ' is linked to ' . $frm_lnk->tob->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                $this->display('formula_link', $target, $result);
            } else {
                if ($autocreate) {
                    $frm_lnk->save();
                }
            }
        }
        return $result;
    }

    /**
     * create all database entries used for the read db unit tests
     * @return void
     */
    function create_test_db_entries(test_unit_read_db $t): void
    {
        create_test_words($t);
        create_test_phrases($t);
        create_test_sources($t);
        create_base_times($t);
        create_test_formulas($t);
        create_test_formula_links($t);
        create_test_views($t);
        create_test_components($t);
        create_test_component_links($t);
        create_test_values($t);
    }

}