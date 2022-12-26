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

use api\triple_api;
use api\word_api;

class test_new_obj extends test_base
{

    /*
     * dummy objects for unit tests
     */

    public function dummy_word(): word
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_READ);
        return $wrd;
    }

    public function dummy_triple(): triple
    {
        global $usr;
        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_READ);
        return $trp;
    }

    /**
     * @return change_log_named a change log entry of a named user sandbox object with some dummy values
     */
    public function dummy_log_named(): change_log_named
    {
        $chg = new change_log_named();
        $chg->set_table(change_log_table::WORD);
        $chg->set_field(change_log_field::FLD_WORD_NAME);
        $chg->new_value = word_api::TN_READ;
        $chg->row_id = 1;
        $usr = new user();
        $usr->id = 4;
        $chg->usr = $usr;
        return $chg;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     */
    public function dummy_log_list_named(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->dummy_log_named());
        return $log_lst;
    }


    /*
     * word
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the name of the word that should be created
     * @param int|null $id to force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the created word object
     */
    function new_word(string $wrd_name, ?int $id = null, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
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
            $wrd->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
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
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $wrd->save();
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
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
                        ?int    $id = null,
                        ?string $wrd_type_code_id = null,
                        ?user   $test_usr = null): triple
    {
        global $usr;
        global $verbs;

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
            $trp->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
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
        if ($from->id() > 0 or $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load_obj_vars();
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
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load_obj_vars();
            if ($lnk_test->id() > 0) {
                // refresh the given name if needed
                if ($phrase_name <> '' and $lnk_test->name_given() <> $phrase_name) {
                    $lnk_test->set_name_given($phrase_name);
                    $lnk_test->save();
                    $lnk_test->load_obj_vars();
                }
                $result = $lnk_test;
            } else {
                // check if the backward link exists
                $lnk_test->from = $to;
                $lnk_test->verb = $vrb;
                $lnk_test->to = $from;
                $lnk_test->set_user($usr);
                $lnk_test->load_obj_vars();
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
                    $lnk_test->load_obj_vars();
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
     * @param int|null $id to force setting the id for unit testing
     * @param string|null $frm_type_code_id the id of the predefined formula type which the new formula should have
     * @param user|null $test_usr if not null the user for whom the formula should be created to test the user sandbox
     * @return formula the created formula object
     */
    function new_formula(string $frm_name, ?int $id = null, ?string $frm_type_code_id = null, ?user $test_usr = null): formula
    {
        global $usr;

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
            $frm->type_id = cl(db_cl::FORMULA_TYPE, $frm_type_code_id);
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
            $frm->set_ref_text();
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

        $ref = new ref($usr);
        $ref->phr = $phr;
        $ref->ref_type = get_ref_type($type_name);
        if ($phr->id() != 0) {
            $ref->load_obj_vars();
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id == 0) {
            $ref->phr = $phr;
            $ref->ref_type = get_ref_type($type_name);
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
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $phr_grp = $phr_lst->get_grp();

        $val = new value($usr);
        if ($phr_grp == null) {
            log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->grp = $phr_grp;
            $val->time_phr = $time_phr;
            $val->load_obj_vars();
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        global $usr;
        $val = $this->load_value($array_of_word_str);
        if ($val->id() == 0) {
            // the time separation is done here until there is a phrase series value table that can be used also to time phrases
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $time_phr = $phr_lst->time_useful();
            $phr_lst->ex_time();
            $phr_grp = $phr_lst->get_grp();

            $val = new value($usr);
            if ($phr_grp == null) {
                log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
            } else {
                $val->grp = $phr_grp;
            }
            $val->time_phr = $time_phr;
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
        $val->grp = $phr_grp;
        $val->load_obj_vars();
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
     * @param int|null $id to force setting the id for unit testing
     * @return verb the created verb object
     */
    function new_verb(string $vrb_name, ?int $id = null): verb
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

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = $this->load_view_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, $type_code_id);
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