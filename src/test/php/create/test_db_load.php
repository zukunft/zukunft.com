<?php

/*

    test/create/test_objects.php - base parameters for creating the test objects
    ----------------------------

    TODO create all test object from these classes like test_values
    TODO shorten the names e.g. if the phrase is most often used use the function name canton() for the phrase

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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'source_types.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once test_paths::UNIT_WRITE . 'component_link_write_tests.php';
include_once test_paths::UNIT_WRITE . 'component_write_tests.php';
include_once test_paths::UNIT_WRITE . 'formula_link_write_tests.php';
include_once test_paths::UNIT_WRITE . 'formula_write_tests.php';
include_once test_paths::UNIT_WRITE . 'group_write_tests.php';
include_once test_paths::UNIT_WRITE . 'source_write_tests.php';
include_once test_paths::UNIT_WRITE . 'triple_write_tests.php';
include_once test_paths::UNIT_WRITE . 'value_write_tests.php';
include_once test_paths::UNIT_WRITE . 'view_write_tests.php';
include_once test_paths::UNIT_WRITE . 'word_write_tests.php';
//include_once test_paths::UTILS . 'all_tests.php';
include_once test_paths::UTILS . 'test_base.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_lists;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\source_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_type;
use Zukunft\ZukunftCom\test\php\unit_write\component_link_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\component_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\formula_link_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\formula_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\group_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\source_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\triple_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\value_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\view_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\word_write_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_db_load
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
     * word
     */

    /**
     * create and fill word object without using the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word with the name set
     */
    function create_word(string $wrd_name, ?user $test_usr = null): word
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $wrd = new word($test_usr);
        $wrd->set_name($wrd_name);
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
    function add_word(
        string    $wrd_name,
        ?string   $wrd_type_code_id = null,
        ?user     $test_usr = null,
    ): word
    {
        global $phr_typ_cac;
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $result = $wrd->save()->get_last_message();
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
        }
        if ($wrd->id <= 0) {
            log_err('Cannot create word ' . $wrd_name);
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = $phr_typ_cac->id($wrd_type_code_id);
            $result = $wrd->save()->get_last_message();
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
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
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $wrd = new word($test_usr);
        $wrd->load_by_name($wrd_name);
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
    function test_word(
        string    $wrd_name,
        ?string   $wrd_type_code_id = null,
        ?user     $test_usr = null
    ): word
    {
        $wrd = $this->add_word($wrd_name, $wrd_type_code_id, $test_usr);
        $this->env->assert('add_word', $wrd->name(), $wrd_name, test_base::TIMEOUT_LIMIT_DB_MULTI);
        return $wrd;
    }

    /**
     * check if an object could have been added to the database
     * TODO deprecate and replace with asser_write_sandbox
     *
     * @param sandbox $sbx the filled sandbox object that should be created or updated in the database
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return bool true if the object has been created of updated
     */
    function assert_db_sandbox_object(sandbox $sbx, ?user $test_usr = null): bool
    {
        $test_name = 'db ';
        $result = '';
        $target = '';
        $db_obj = clone $sbx;
        $db_obj->reset();
        if ($sbx->is_named_obj()) {
            $target = $sbx->name();
            $test_name .= $target;
            if ($db_obj->load_by_name($sbx->name())) {
                if ($sbx->id() == 0) {
                    $sbx->id = $db_obj->id();
                    $sbx->save();
                    $test_name .= ' update ';
                } elseif ($sbx->id() == $db_obj->id()) {
                    $sbx->save();
                    $test_name .= ' update ';
                } else {
                    log_err($sbx::class . ' has id ' . $db_obj->id() . ' in the database but not yet supported by assert_db_sandbox_object');
                }
            } else {
                $test_name .= ' add ';
                $sbx->save();
            }
        } else {
            log_err($sbx::class . ' not yet supported by assert_db_sandbox_object');
        }
        $test_name .= ' of ' . $sbx::class . ' ' . $target;
        $db_obj->reset();
        if ($db_obj->load_by_id($sbx->id())) {
            $target = $db_obj->name();
        }
        return $this->env->assert($test_name, $result, $target);
    }

    /*
     * triple test creation
     */

    /**
     * load a triple by the linked phrase ids without creating it
     * @param string $from_name the name of child phrase
     * @param string $verb_code_id the code id of the predicate
     * @param string $to_name the name of parent phrase
     * @return triple
     */
    function load_triple(
        string    $from_name,
        string    $verb_code_id,
        string    $to_name
    ): triple
    {
        global $vrb_cac;

        $wrd_from = $this->load_word($from_name, $this->env->usr1);
        $wrd_to = $this->load_word($to_name, $this->env->usr1);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $vrb_cac->get_verb($verb_code_id);

        $lnk_test = new triple($this->env->usr1);
        if ($from->id() > 0 and $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->load_by_link_id($from->id(), $vrb->id(), $to->id());
        }
        return $lnk_test;
    }

    function create_triple(
        string $from_name,
        string $verb_code_id,
        string $to_name,
        ?user  $test_usr = null): triple
    {
        global $vrb_cac;

        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $wrd_from = $this->create_word($from_name);
        $wrd_to = $this->create_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $vrb_cac->get_verb($verb_code_id);

        $lnk_test = new triple($test_usr);
        $lnk_test->set_from($from);
        $lnk_test->set_verb($vrb);
        $lnk_test->set_to($to);
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
    function test_triple(
        string    $from_name,
        string    $verb_code_id,
        string    $to_name,
        string    $target = '',
        string    $name_given = '',
        bool      $auto_create = true
    ): triple
    {
        global $vrb_cac;

        $result = new triple($this->env->usr1);

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
        $vrb = $vrb_cac->get_verb($verb_code_id);

        // check if the triple exists or create a new if needed
        $trp = new triple($this->env->usr1);
        if ($vrb == null) {
            log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            if ($from->id() == 0 or $vrb->id() == 0 or $to->id() == 0) {
                log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
            } else {
                // check if the forward link exists
                $trp->load_by_link_id($from->id(), $vrb->id(), $to->id());
                if ($trp->id() > 0) {
                    // refresh the given name if needed
                    if ($name_given <> '' and $trp->name(true) <> $name_given) {
                        $trp->name_given = $name_given;
                        $trp->set_name($name_given);
                        $result = $trp->save()->get_last_message();
                        if ($result != '') {
                            log_err('save triple failed due to: ' . $result);
                        }
                        $trp->load_by_id($trp->id());
                    }
                    $result = $trp;
                } else {
                    // check if the backward link exists
                    $trp->set_from($to);
                    $trp->set_verb($vrb);
                    $trp->set_to($from);
                    $trp->set_user($this->env->usr1);
                    $trp->load_by_link_id($to->id(), $vrb->id(), $from->id());
                    $result = $trp;
                    // create the link if requested
                    if ($trp->id() <= 0 and $auto_create) {
                        $trp->set_from($from);
                        $trp->set_verb($vrb);
                        $trp->set_to($to);
                        if ($trp->name(true) <> $name_given) {
                            $trp->name_given = $name_given;
                            $trp->set_name($name_given);
                        }
                        $save_result = $trp->save()->get_last_message();
                        if ($save_result != '') {
                            log_err('save triple failed due to: ' . $save_result);
                        }
                        $trp->load_by_id($trp->id());
                    }
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

        $this->env->display('test_triple', $target, $result_text, test_base::TIMEOUT_LIMIT_DB);
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

    function del_triple_by_name(string $name): bool
    {
        $trp = new triple($this->env->usr1);
        $trp->load_by_name($name);
        if ($trp->id() <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }


    /*
     * group test creation
     */

    /**
     * load a word from the database
     *
     * @param string $grp_name the name of the group which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group loaded from the database by name
     */
    function load_group(string $grp_name, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $grp = new group($test_usr);
        $grp->load_by_name($grp_name);
        return $grp;
    }

    /**
     * create group object based on the phrase list without using the database
     *
     * @param phrase_list $phr_lst with the phrases to identify the group
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the word with the name set
     */
    function create_group(phrase_list $phr_lst, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $grp = new group($test_usr);
        $grp->set_phrase_list($phr_lst);
        return $grp;
    }

    /**
     * save the just created group object in the database
     *
     * @param array $phr_names with the phrases to identify the group
     * @param string $grp_name the group name that should be used
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group that is saved in the database by name
     */
    function add_group(array $phr_names, string $grp_name, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $grp = $this->load_group($grp_name);
        if (!$grp->is_saved()) {
            $phr_lst = new phrase_list($test_usr);
            $phr_lst->load_by_names($phr_names);
            $grp = $this->create_group($phr_lst, $test_usr);
            $grp->set_name($grp_name);
            $result = $grp->save()->get_last_message();
            if ($result != '') {
                log_err('add group failed due to: ' . $result);
            }
        }
        return $grp;
    }

    /**
     * check if a group object could have been added to the database
     *
     * @param array $phr_names with the phrases to identify the group
     * @param string $grp_name the group name that should be used
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group that is saved in the database
     */
    function test_group(array $phr_names, string $grp_name, ?user $test_usr = null): group
    {
        $grp = $this->add_group($phr_names, $grp_name, $test_usr);
        $this->env->assert('test_group', $grp->name(), $grp_name);
        return $grp;
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
        global $frm_typ_cac;

        if ($id == null) {
            $id = $this->env->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $frm = new formula($test_usr);
        $frm->id = $id;
        $frm->set_name($frm_name);

        if ($frm_type_code_id != null) {
            $frm->type_id = $frm_typ_cac->id($frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        $frm = new formula($this->env->usr1);
        $frm->load_by_name($frm_name);
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
            $result = $frm->save()->get_last_message();
            // TODO add this check to all add functions
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
        }
        return $frm;
    }

    function test_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->add_formula($frm_name, $frm_text);
        $this->env->display('formula', $frm_name, $frm->name());
        return $frm;
    }


    /*
     * reference test creation
     */

    function load_ref(string $wrd_name, string $type_name): ref
    {

        $wrd = $this->load_word($wrd_name);
        $phr = $wrd->phrase();

        global $ref_typ_cac;
        $ref = new ref($this->env->usr1);
        if ($phr->id() != 0) {
            // TODO check if type name is the code id or really the name
            $ref->load_by_link_ids($phr->id(), $ref_typ_cac->id($type_name));
        }
        return $ref;
    }

    function add_ref(
        string    $wrd_name,
        string    $external_key,
        string    $type_name
    ): ref
    {
        global $ref_typ_cac;
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id() == 0) {
            $ref->set_phrase($phr);
            // TODO check if type name is the code id or really the name
            $ref->set_predicate_id($ref_typ_cac->id($type_name));
            $ref->set_external_key($external_key);
            $result = $ref->save()->get_last_message();
            if ($result != '') {
                log_err('add ref failed due to: ' . $result);
            }
        }
        return $ref;
    }

    function test_ref(
        string    $wrd_name,
        string    $external_key,
        string    $type_name
    ): ref
    {
        $ref = $this->add_ref($wrd_name, $external_key, $type_name);
        $target = $external_key;
        $this->env->display('ref', $target, $ref->external_key());
        return $ref;
    }

    function load_phrase(string $phr_name): phrase
    {
        $phr = new phrase($this->env->usr1);
        $phr->load_by_name($phr_name);
        $phr->load_obj();
        return $phr;
    }

    /**
     * test if a phrase with the given name exists, but does not create it, if it has not yet been created
     * @param string $phr_name name of the phrase to test
     * @return phrase the loaded phrase object
     */
    function test_phrase(
        string    $phr_name
    ): phrase
    {
        $phr = $this->load_phrase($phr_name);
        $this->env->display('phrase', $phr_name, $phr->name(true));
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = new word_list($this->env->usr1);
        $wrd_lst->load_by_names($array_of_word_str);
        return $wrd_lst;
    }

    function test_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = $this->load_word_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $wrd_lst->name();
        $this->env->display(', word list', $target, $result);
        return $wrd_lst;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = new phrase_list($this->env->usr1);
        $phr_lst->load_by_names($array_of_word_str);
        return $phr_lst;
    }

    function test_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->env->display(', phrase list', $target, $result);
        return $phr_lst;
    }

    /**
     * load a phrase group by the list of phrase names
     * @param array $array_of_phrase_str with the names of the words or triples
     * @return group|null
     */
    function load_phrase_group(array $array_of_phrase_str): ?group
    {
        return $this->load_phrase_list($array_of_phrase_str)->get_grp_id();
    }

    /**
     * load a phrase group by the name
     * which can be either the name set by the users
     * or the automatically created name based on the phrases
     * @param string $phrase_group_name
     * @return group
     */
    function load_phrase_group_by_name(string $phrase_group_name): group
    {
        $phr_grp = new group($this->env->usr1);
        $phr_grp->name = $phrase_group_name;
        $phr_grp->load_by_obj_vars();
        return $phr_grp;
    }

    /**
     * add a phrase group to the database
     * @param array $array_of_phrase_str the phrase names
     * @param string $name the name that should be shown to the user
     * @return group the phrase group object including the database is
     */
    function add_phrase_group(array $array_of_phrase_str, string $name): group
    {
        $grp = new group($this->env->usr1);
        $grp->get_by_phrase_list($this->load_phrase_list($array_of_phrase_str), $name);
        return $grp;
    }

    /**
     * delete a phrase group from the database
     * @param string $phrase_group_name the name that should be shown to the user
     * @return bool true if the phrase group has been deleted
     */
    function del_phrase_group(string $phrase_group_name): bool
    {
        $phr_grp = $this->load_phrase_group_by_name($phrase_group_name);
        if ($phr_grp->del()) {
            return true;
        } else {
            return false;
        }
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $val = new value($this->env->usr1);
        $val->load_by_id($id);
        return $val;
    }

    function load_value(array $array_of_word_str): value
    {

        // the time separation is done here until there is a phrase series value table that can be used also to time phrases
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $phr_grp = $phr_lst->get_grp_id();

        $val = new value($this->env->usr1);
        if ($phr_grp == null) {
            log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->load_by_grp($phr_grp);
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        $val = $this->load_value($array_of_word_str);
        if (!$val->is_saved()) {
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $phr_grp = $phr_lst->get_grp_id();

            // getting the latest value if selected without time phrase should be done when reading the value
            //$time_phr = $phr_lst->time_useful();
            //$phr_lst->ex_time();

            $val = new value($this->env->usr1);
            if ($phr_grp == null) {
                log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
            } else {
                $val->set_grp($phr_grp);
            }
            $val->set_number($target);
            $result = $val->save()->get_last_message();
            if ($result != '') {
                log_err('add value failed due to: ' . $result);
            }
        }

        return $val;
    }

    function test_value(array $array_of_word_str, float $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->value();
        $this->env->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function load_value_by_phr_grp(group $phr_grp): value
    {
        $val = new value($this->env->usr1);
        $val->load_by_grp($phr_grp);
        return $val;
    }

    function add_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if (!$val->is_saved()) {
            $val->set_grp($phr_grp);
            $val->set_number($target);
            $result = $val->save()->get_last_message();
            if ($result != '') {
                log_err('add value by group failed due to: ' . $result);
            }
        }

        return $val;
    }

    function test_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->add_value_by_phr_grp($phr_grp, $target);
        $result = $val->number();
        $this->env->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function del_value_by_phr_grp(group $phr_grp): bool
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if ($val->del()) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * source test creation
     */

    function load_source(string $src_name): source
    {
        $src = new source($this->env->usr1);
        $src->load_by_name($src_name);
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id() == 0) {
            $src->set_name($src_name);
            $result = $src->save()->get_last_message();
            if ($result != '') {
                log_err('add source failed due to: ' . $result);
            }
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->env->display('source', $src_name, $src->name());
        return $src;
    }

    /**
     * @return array json message to test if adding a new word via the api works fine
     */
    function word_put_json(): array
    {
        global $db_con;
        global $phr_typ_cac;
        $msg = new api_message();
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::TEST_ADD_API);
        $wrd->description = words::TEST_ADD_API_COM;
        $wrd->type_id = $phr_typ_cac->id(phrase_type::NORMAL);
        $body_array = $wrd->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, word::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a word via the api works fine
     */
    function word_post_json(): array
    {
        global $db_con;
        $msg = new api_message();
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::TEST_UPD_API);
        $wrd->description = words::TEST_UPD_API_COM;
        $body_array = $wrd->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, word::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new source via the api works fine
     */
    function source_put_json(): array
    {
        global $db_con;
        global $src_typ_cac;
        $msg = new api_message();
        $src = new source($this->env->usr1);
        $src->set_name(sources::SYSTEM_TEST_ADD_API);
        $src->description = sources::SYSTEM_TEST_ADD_API_COM;
        $src->set_url(sources::SYSTEM_TEST_ADD_API_URL);
        $src->type_id = $src_typ_cac->id(source_types::PDF);
        $body_array = $src->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, source::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a source via the api works fine
     */
    function source_post_json(): array
    {
        global $db_con;
        $msg = new api_message();
        $src = new source($this->env->usr1);
        $src->set_name(sources::SYSTEM_TEST_UPD_API);
        $src->description = sources::SYSTEM_TEST_UPD_API_COM;
        $body_array = $src->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, source::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new reference via the api works fine
     */
    function reference_put_json(): array
    {
        global $db_con;
        global $reference_types;
        $t_wrd = new test_words($this->env);
        $msg = new api_message();
        $ref = new ref($this->env->usr1);
        $ref->set_phrase($t_wrd->word()->phrase());
        $ref->set_external_key(refs::SYSTEM_TEST_API_ADD_KEY);
        $ref->description = refs::SYSTEM_TEST_API_ADD_COM;
        $ref->set_url(refs::SYSTEM_TEST_API_ADD_URL);
        $ref->predicate_id = $reference_types->id(source_types::PDF);
        $body_array = $ref->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, ref::class, $this->env->usr1, $body_array);
    }

    /*
     * view test creation
     */

    /**
     * load a view and if the test user is set for a specific user
     */
    function load_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $msk = new view($test_usr);
        $msk->load_by_name($dsp_name);
        return $msk;
    }

    function add_view(string $dsp_name, user $test_usr): view
    {
        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() == 0) {
            $msk->set_user($test_usr);
            $msk->set_name($dsp_name);
            $result = $msk->save()->get_last_message();
            if ($result != '') {
                log_err('add view failed due to: ' . $result);
            }
        }
        return $msk;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $msk = $this->add_view($dsp_name, $test_usr);
        $this->env->display('view', $dsp_name, $msk->name(), test_base::TIMEOUT_LIMIT_DB);
        return $msk;
    }

    function del_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() != 0) {
            $msk->del_links();
            $msk->del();
        }
        return $msk;
    }


    /*
     * component test creation
     */

    function load_component(string $cmp_name, ?user $test_usr = null): component
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $cmp = new component($test_usr);
        $cmp->load_by_name($cmp_name);
        return $cmp;
    }

    function add_component(string $cmp_name, user $test_usr, string $type_code_id = ''): component
    {
        global $cmp_typ_cac;

        $cmp = $this->load_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $cmp_typ_cac->id($type_code_id);
            }
            $result = $cmp->save()->get_last_message();
            if ($result != '') {
                log_err('add component failed due to: ' . $result);
            }
        }
        return $cmp;
    }

    function test_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): component
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $cmp = $this->add_component($cmp_name, $test_usr, $type_code_id);
        $this->env->display('view component', $cmp_name, $cmp->name());
        return $cmp;
    }

    function test_component_lnk(string $dsp_name, string $cmp_name, int $pos): component_link
    {
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        $lnk = new component_link($this->env->usr1);
        $lnk->reset();
        $lnk->set_view($msk);
        $lnk->set_component($cmp);
        $lnk->order_nbr = $pos;
        $result = $lnk->save()->get_last_message();
        $target = '';
        $this->env->display('view component link', $target, $result);
        return $lnk;
    }

    function test_component_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $result = $cmp->unlink($msk);
        }
        return $result;
    }

    function test_formula_link(string $formula_name, string $word_name, bool $auto_create = true): string
    {
        $result = '';

        $frm = new formula($this->env->usr1);
        $frm->load_by_name($formula_name);
        $wrd = new word($this->env->usr1);
        $wrd->load_by_name($word_name);
        if ($frm->id() > 0 and $wrd->id() <> 0) {
            $frm_lnk = new formula_link($this->env->usr1);
            $frm_lnk->load_by_link($frm, $wrd->phrase());
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->formula()->name() . ' is linked to ' . $frm_lnk->phrase()->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                $this->env->display('formula_link', $target, $result);
            } else {
                if ($auto_create) {
                    $frm_lnk->set_formula($frm);
                    $frm_lnk->set_phrase($wrd->phrase());
                    $result = $frm_lnk->save()->get_last_message();
                    if ($result != '') {
                        log_err('add formula link failed due to: ' . $result);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * create all database entries used for the read db unit tests
     * the created database rows can be accessed by the users but are not expected to be changed and cannot be changed
     *
     * @param all_tests $t the test object to collect the errors and calculate the execution times
     * @return void
     */
    function create_test_db_entries(all_tests $t): void
    {
        new word_write_tests()->create_test_words($t);
        new triple_write_tests()->create_test_triples($t);
        new triple_write_tests()->create_base_times($t);
        new group_write_tests()->create_test_groups($t);
        new source_write_tests()->create_test_sources($t);
        new formula_write_tests()->create_test_formulas($t);
        new formula_link_write_tests()->create_test_formula_links($t);
        new view_write_tests()->create_test_views($t);
        // new view_link_write_tests()->create_test_views($t);
        new component_write_tests()->create_test_components($t);
        new component_link_write_tests()->create_test_component_links($t);
        new value_write_tests()->create_test_values($t);
    }

    /**
     * update the list of types json file
     * called upfront also from the reset db run because this is used for the unit tests
     *
     * @param all_tests $t the test object to collect the errors and calculate the execution times
     * @return void
     */
    function type_list_recreate(test_cleanup $t): void
    {
        global $db_con;

        $t->subheader('API unit db tests of preloaded types');
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, $t->usr1);
        $t->assert_api($sys_typ_lst, '', [api_type::HEADER]);

    }

}