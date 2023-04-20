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
include_once WEB_PATH . 'formula_display.php';
include_once WEB_PATH . 'view_display.php';

use api\formula_api;
use api\phrase_group_api;
use api\ref_api;
use api\source_api;
use api\system_log_api;
use api\triple_api;
use api\value_api;
use api\verb_api;
use api\view_api;
use api\view_cmp_api;
use api\word_api;
use api_message;
use cfg\formula_type;
use cfg\job_type_list;
use cfg\phrase_type;
use cfg\ref_type_list;
use cfg\source_type;
use cfg\system_log_list;
use controller\result\result_api;
use DateTime;
use html\formula_dsp_old;
use model\batch_job;
use model\batch_job_list;
use model\change_log_action;
use model\change_log_field;
use model\change_log_list;
use model\change_log_named;
use model\change_log_table;
use model\figure;
use model\formula;
use model\formula_link;
use model\formula_list;
use model\phrase;
use model\phrase_group;
use model\phrase_list;
use model\ref;
use model\result;
use model\source;
use model\sys_log_status;
use model\system_log;
use model\term;
use model\triple;
use model\user;
use model\value;
use model\verb;
use model\view;
use model\view_cmp;
use model\view_cmp_link;
use model\word;
use model\word_list;
use view_dsp_old;

class test_new_obj extends test_base
{

    CONST DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';

    /*
     * dummy objects for unit tests
     */

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

    function dummy_phrase(): phrase
    {
        return $this->dummy_word()->phrase();
    }

    function dummy_phrase_triple(): phrase
    {
        return $this->dummy_triple()->phrase();
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


    function dummy_value(): value
    {
        global $usr;
        $grp = new phrase_group($usr, 1,  array(phrase_group_api::TN_READ));
        return new value($usr, 1, round(value_api::TV_READ, 13), $grp);
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
        $fv = $this->dummy_formula_value();
        return $fv->figure();
    }

    function dummy_formula(): formula
    {
        global $usr;
        $frm = new formula($usr);
        $frm->set(1, formula_api::TN_READ);
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

    function dummy_formula_value(): result
    {
        global $usr;
        $fv = new result($usr);
        $wrd = $this->dummy_word();
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd->phrase());
        $fv->set_id(1);
        $fv->phr_lst = $phr_lst;
        $fv->value = result_api::TV_INT;
        return $fv;
    }

    function dummy_formula_value_pct(): result
    {
        global $usr;
        $fv = new result($usr);
        $wrd_pct = $this->new_word(word_api::TN_PCT, 2, phrase_type::PERCENT);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd_pct->phrase());
        $fv->phr_lst = $phr_lst;
        $fv->value = 0.01234;
        return $fv;
    }

    function dummy_source(): source
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
        $ref->set(1);
        $ref->phr = $this->dummy_word()->phrase();
        $ref->external_key = ref_api::TK_READ;
        return $ref;
    }

    function dummy_view(): view
    {
        global $usr;
        $dsp = new view($usr);
        $dsp->set(1, view_api::TN_READ);
        return $dsp;
    }

    function dummy_component(): view_cmp
    {
        global $usr;
        $dsp = new view_cmp($usr);
        $dsp->set(1, view_cmp_api::TN_READ);
        return $dsp;
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
        $job = new batch_job($sys_usr);
        $job->set_id(1);
        $job->start_time = new DateTime();
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
        $this->dsp('testing->add_word', $target, $wrd->name());
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
     * check if a word link exists and if not and requested create it
     * $phrase_name should be set if the standard name for the link should not be used
     */
    function test_triple(string $from_name,
                         string $verb_code_id,
                         string $to_name,
                         string $target = '',
                         string $phrase_name = '',
                         bool   $autocreate = true): triple
    {
        global $usr;
        global $verbs;

        $result = '';

        // create the words if needed
        $wrd_from = $this->load_word($from_name);
        if ($wrd_from->id() <= 0 and $autocreate) {
            $wrd_from->set_name($from_name);
            $wrd_from->save();
            $wrd_from->load_by_name($from_name);
        }
        $wrd_to = $this->load_word($to_name);
        if ($wrd_to->id() <= 0 and $autocreate) {
            $wrd_to->set_name($to_name);
            $wrd_to->save();
            $wrd_to->load_by_name($to_name);
        }
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new triple($usr);
        if ($from->id() == 0 or $to->id() == 0) {
            log_err("Words " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            // check if the forward link exists
            $lnk_test->load_by_link($from->id(), $vrb->id(), $to->id());
            if ($lnk_test->id() > 0) {
                // refresh the given name if needed
                if ($phrase_name <> '' and $lnk_test->name_given() <> $phrase_name) {
                    $lnk_test->set_name_given($phrase_name);
                    $lnk_test->save();
                    $lnk_test->load_by_id($lnk_test->id());
                }
                $result = $lnk_test;
            } else {
                // check if the backward link exists
                $lnk_test->from = $to;
                $lnk_test->verb = $vrb;
                $lnk_test->to = $from;
                $lnk_test->set_user($usr);
                $lnk_test->load_by_link($to->id(), $vrb->id(), $from->id());
                $result = $lnk_test;
                // create the link if requested
                if ($lnk_test->id() <= 0 and $autocreate) {
                    $lnk_test->from = $from;
                    $lnk_test->verb = $vrb;
                    $lnk_test->to = $to;
                    if ($lnk_test->name_given() <> $phrase_name) {
                        $lnk_test->set_name_given($phrase_name);
                    }
                    $lnk_test->save();
                    $lnk_test->load_by_id($lnk_test->id());
                }
            }
        }
        // fallback setting of target f
        $result_text = '';
        if ($lnk_test->id() > 0) {
            $result_text = $lnk_test->name();
            if ($target == '') {
                $target = $lnk_test->name();
            }
        }
        $this->dsp('word link', $target, $result_text, TIMEOUT_LIMIT_DB);
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
        $this->dsp('formula', $frm_name, $frm->name());
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
        $this->dsp('ref', $target, $ref->external_key);
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
        $this->dsp('phrase', $phr_name, $phr->name());
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
        $this->dsp(', word list', $target, $result);
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
        $this->dsp(', phrase list', $target, $result);
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
        $this->dsp(', value->load for ' . $val->name(), $target, $result);
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
        $this->dsp(', value->load for ' . $val->name(), $target, $result);
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
        $this->dsp('source', $src_name, $src->name());
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
        $ref->phr = $this->dummy_word()->phrase()->api_obj();
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
        $this->dsp('view', $dsp_name, $dsp->name());
        return $dsp;
    }


    /*
     * component test creation
     */

    function load_view_component(string $cmp_name, ?user $test_usr = null): view_cmp
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = new view_cmp($test_usr);
        $cmp->load_by_name($cmp_name, view_cmp::class);
        return $cmp;
    }

    function add_view_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): view_cmp
    {
        global $usr;
        global $view_component_types;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = $this->load_view_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $view_component_types->id($type_code_id);
            }
            $cmp->save();
        }
        return $cmp;
    }

    function test_view_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): view_cmp
    {
        $cmp = $this->add_view_component($cmp_name, $type_code_id, $test_usr);
        $this->dsp('view component', $cmp_name, $cmp->name());
        return $cmp;
    }

    function test_view_cmp_lnk(string $dsp_name, string $cmp_name, int $pos): view_cmp_link
    {
        global $usr;
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_view_component($cmp_name);
        $lnk = new view_cmp_link($usr);
        $lnk->fob = $dsp;
        $lnk->tob = $cmp;
        $lnk->order_nbr = $pos;
        $result = $lnk->save();
        $target = '';
        $this->dsp('view component link', $target, $result);
        return $lnk;
    }

    function test_view_cmp_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_view_component($cmp_name);
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
                $this->dsp('formula_link', $target, $result);
            } else {
                if ($autocreate) {
                    $frm_lnk->save();
                }
            }
        }
        return $result;
    }


}