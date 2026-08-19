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

include_once paths::API_OBJECT . 'ui_config.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_COMPONENT . 'component_list.php';
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
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'source_types.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'word_names.php';
//include_once test_paths::UNIT_WRITE . 'a_selected_test.php';
include_once test_paths::UNIT_WRITE . 'component_link_write_tests.php';
include_once test_paths::UNIT_WRITE . 'component_write_tests.php';
include_once test_paths::UNIT_WRITE . 'formula_link_write_tests.php';
include_once test_paths::UNIT_WRITE . 'formula_write_tests.php';
include_once test_paths::UNIT_WRITE . 'group_write_tests.php';
include_once test_paths::UNIT_WRITE . 'source_write_tests.php';
include_once test_paths::UNIT_WRITE . 'triple_write_tests.php';
include_once test_paths::UNIT_WRITE . 'value_write_tests.php';
include_once test_paths::UNIT_WRITE . 'view_write_tests.php';
include_once test_paths::UNIT_WRITE . 'view_relation_write_tests.php';
include_once test_paths::UNIT_WRITE . 'view_link_write_tests.php';
include_once test_paths::UNIT_WRITE . 'word_write_tests.php';
//include_once test_paths::UTILS . 'all_tests.php';
include_once test_paths::UTILS . 'test_base.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\api\ui_config;
use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_list;
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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\source_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\unit_write\a_selected_test;
use Zukunft\ZukunftCom\test\php\unit_write\component_link_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\component_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\formula_link_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\formula_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\group_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\source_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\triple_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\value_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\view_link_write_tests;
use Zukunft\ZukunftCom\test\php\unit_write\view_relation_write_tests;
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
     * create a simple word without database saving
     *
     * @param user_message $msg to collect the messages and with the user that should be owner (default is used if not set)
     * @param string $wrd_name the name of the word which should be loaded
     * @return word the word with the name set
     */
    function create_word(user_message $msg, string $wrd_name): word
    {
        $this->set_user($msg);
        $wrd = new word($msg->usr);
        $wrd->set_name($wrd_name);
        return $wrd;
    }

    /**
     * save a simple word in the database
     *
     * @param user_message $msg to collect the messages and with the user that should be owner (default is used if not set)
     * @param string $wrd_name the name of the word, which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @return word the word that is saved in the database by name
     */
    function add_word(
        user_message $msg,
        string       $wrd_name,
        ?string      $wrd_type_code_id = null
    ): word
    {
        global $sys;
        $this->set_user($msg);

        // add the word only if it does not yet exist
        $wrd = $this->load_word($msg, $wrd_name);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            if (!$wrd->save($msg)) {
                log_err('add word failed due to: ' . $msg->text());
            }
        }

        // report if failed
        if ($wrd->id <= 0) {
            log_err('Cannot create word ' . $wrd_name);
        }

        // include if it has been excluded
        if ($wrd->id > 0) {
            if ($wrd->excluded) {
                $wrd->include();
                if (!$wrd->save($msg)) {
                    log_err('cannot include word ' . $wrd->dsp_id() . ' due to ' . $msg->text());
                }
            }
        }

        // set type if requested
        if ($wrd_type_code_id != null) {
            $wrd->type_id = $sys->typ_lst->phr_typ->id($wrd_type_code_id);
            if (!$wrd->save($msg)) {
                log_err('add formula failed due to: ' . $msg->text());
            }
        }

        return $wrd;
    }

    /**
     * load a word from the database
     *
     * @param user_message $msg to collect the messages and with the user that should be owner (default is used if not set)
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $usr to load the word from the view of another user than the message user
     * @return word the word loaded from the database by name
     */
    function load_word(user_message $msg, string $wrd_name, ?user $usr = null): word
    {
        $this->set_user($msg);
        $wrd = new word($usr ?? $msg->usr);
        $wrd->load_by_name($wrd_name, $msg);
        return $wrd;
    }

    /**
     * check if a word object could have been added to the database
     *
     * @param user_message $msg to collect the test fail messages and with the user that should be owner (default is used if not set)
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @return word the word that is saved in the database by name
     */
    function test_word(
        user_message $msg,
        string       $wrd_name,
        ?string      $wrd_type_code_id = null
    ): word
    {
        $this->set_user($msg);
        // run each test even if previous tests have failed
        $add_msg = new user_message($msg->usr);
        $wrd = $this->add_word($add_msg, $wrd_name, $wrd_type_code_id);
        $this->env->assert('test_word', $wrd->name(), $wrd_name, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->merge($add_msg); // collect the messages
        return $wrd;
    }


    /*
     * triple test creation
     */

    /**
     * load a triple by the linked phrase ids without creating it
     *
     * @param user_message $msg to collect the test fail messages and with the user that should be owner (default is used if not set)
     * @param string $from_name the name of child phrase
     * @param string $verb_code_id the code id of the predicate
     * @param string $to_name the name of parent phrase
     * @return triple
     */
    function load_triple(
        user_message $msg,
        string       $from_name,
        string       $verb_code_id,
        string       $to_name
    ): triple
    {
        global $sys;
        $this->set_user($msg);

        $wrd_from = $this->load_word($msg, $from_name);
        $wrd_to = $this->load_word($msg, $to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $sys->verb($verb_code_id);

        $lnk_test = new triple($msg->usr);
        if ($from->id() > 0 and $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->load_by_link_id($from->id(), $msg, $vrb->id(), $to->id());
        }
        return $lnk_test;
    }

    /**
     * create a simple triple without database saving
     *
     * @param user_message $msg to collect the messages and with the user that should be owner (default is used if not set)
     * @param string $from_name
     * @param string $verb_code_id
     * @param string $to_name
     * @param user|null $test_usr
     * @return triple
     */
    function create_triple(
        user_message $msg,
        string       $from_name,
        string       $verb_code_id,
        string       $to_name,
        ?user        $test_usr = null): triple
    {
        global $sys;
        $this->set_user($msg);

        $wrd_from = $this->create_word($msg, $from_name);
        $wrd_to = $this->create_word($msg, $to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $sys->verb($verb_code_id);

        $lnk_test = new triple($msg->usr);
        $lnk_test->set_from($from);
        $lnk_test->set_verb($vrb);
        $lnk_test->set_to($to);
        return $lnk_test;
    }

    /**
     * check if a triple exists and if not create it if requested
     *
     * @param user_message $msg to collect the test fail messages and with the user that should be owner (default is used if not set)
     * @param string $from_name a phrase name
     * @param string $to_name a phrase name
     * @param string $target the expected name of the triple
     * @param string $name_given the name that the triple should be set to
     * @param bool $auto_create if true the related words should be created if the phrase does not exist
     * @return triple the loaded or created triple
     */
    function test_triple(
        user_message $msg,
        string $from_name,
        string $verb_code_id,
        string $to_name,
        string $target = '',
        string $name_given = '',
        bool   $auto_create = true
    ): triple
    {
        global $sys;
        $this->set_user($msg);
        // run each test even if previous tests have failed
        $add_msg = new user_message($msg->usr);

        $result = new triple($msg->usr);

        // load the phrases to link and create words if needed
        $from = $this->load_phrase($from_name, $add_msg);
        if ($from->id() == 0 and $auto_create) {
            $from = $this->add_word($add_msg, $from_name)->phrase();
        }
        if ($from->id() == 0) {
            log_err('Cannot get phrase ' . $from_name);
        }
        $to = $this->load_phrase($to_name, $add_msg);
        if ($to->id() == 0 and $auto_create) {
            $to = $this->add_word($add_msg, $to_name)->phrase();
        }
        if ($to->id() == 0) {
            log_err('Cannot get phrase ' . $to_name);
        }

        // load the verb
        $vrb = $sys->verb($verb_code_id);

        // check if the triple exists or create a new if needed
        $trp = new triple($msg->usr);
        if ($vrb == null) {
            log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            if ($from->id() == 0 or $vrb->id() == 0 or $to->id() == 0) {
                log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
            } else {
                // check if the forward link exists
                $trp->load_by_link_id($from->id(), $msg, $vrb->id(), $to->id());
                if ($trp->id() > 0) {
                    // refresh the given name if needed
                    if ($name_given <> '' and $trp->name(true) <> $name_given) {
                        $trp->name_given = $name_given;
                        $trp->set_name($name_given);
                        if (!$trp->save($msg)) {
                            log_err('save triple failed due to: ' . $msg->get_last_message());
                        }
                        $trp->load_by_id($trp->id(), $msg);
                    }
                    $result = $trp;
                } else {
                    // check if the backward link exists
                    $trp->set_from($to);
                    $trp->set_verb($vrb);
                    $trp->set_to($from);
                    $trp->set_user($msg->usr);
                    $trp->load_by_link_id($to->id(), $add_msg, $vrb->id(), $from->id());
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
                        if (!$trp->save($add_msg)) {
                            log_err('save triple failed due to: ' . $add_msg->text());
                        }
                        $trp->load_by_id($trp->id(), $add_msg);
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

        $this->env->assert('test_triple', $result_text, $target, test_base::TIMEOUT_LIMIT_DB);
        $msg->merge($add_msg); // collect the messages
        return $result;
    }

    function del_triple(
        user_message $msg,
        string $from_name,
                        string $verb_code_id,
                        string $to_name
    ): bool
    {
        $trp = $this->load_triple($msg, $from_name, $verb_code_id, $to_name);
        if ($trp->id() <> 0) {
            $trp->del(new user_message($this->env->usr1));
            return true;
        } else {
            return false;
        }
    }

    function del_triple_by_name(string $name): bool
    {
        $msg = new user_message();
        $trp = new triple($msg->usr);
        $trp->load_by_name($name, $msg);
        if ($trp->id() <> 0) {
            $trp->del(new user_message($this->env->usr1));
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
        $msg = new user_message();
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $grp = new group($test_usr);
        $grp->load_by_name($grp_name, $msg);
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
        $msg = new user_message();
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }
        $grp = $this->load_group($grp_name);
        if (!$grp->is_saved()) {
            $phr_lst = new phrase_list($test_usr);
            $phr_lst->load_by_names($phr_names, $msg);
            $grp = $this->create_group($phr_lst, $test_usr);
            $grp->set_name($grp_name);
            $msg = new user_message($test_usr);
            if (!$grp->save($msg)) {
                log_err('add group failed due to: ' . $msg->text());
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
        global $sys;

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
            $frm->type_id = $sys->typ_lst->frm_typ->id($frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        $msg = new user_message();
        $frm = new formula($this->env->usr1);
        $frm->load_by_name($frm_name, $msg);
        return $frm;
    }

    /**
     * get or create a formula
     */
    function add_formula(
        string       $frm_name,
        string       $frm_text,
        user_message $msg
    ): formula
    {
        $frm = $this->load_formula($frm_name);
        if ($frm->id() == 0) {
            $frm->set_name($frm_name);
        }
        // update also an existing formula if the expression differs, because returning a
        // formula with another expression than requested would silently change the test setup
        if ($frm->id() == 0 or $frm->usr_text != $frm_text) {
            // use the setter, because it marks the ref text as dirty, so that the ref text of
            // a loaded formula is regenerated and not kept based on the previous expression
            $frm->set_user_text($frm_text, $msg);
            $frm->save($msg);
            // TODO add this check to all add functions
            if (!$msg->is_ok()) {
                $reason = $msg->all_message_text();
                log_warning('add formula failed due to: ' . $reason);
            }
        }

        // include if it has been excluded
        if ($frm->id() > 0) {
            if ($frm->excluded) {
                $frm->include();
                if (!$frm->save($msg)) {
                    log_err('cannot include formula ' . $frm->dsp_id() . ' due to ' . $msg->text());
                }
            }
        }

        return $frm;
    }

    function test_formula(
        user_message $msg,
        string       $frm_name,
        string       $frm_text
    ): formula
    {
        $this->set_user($msg);
        // run each test even if previous tests have failed
        $add_msg = new user_message($msg->usr);
        $frm = $this->add_formula($frm_name, $frm_text, $add_msg);
        // adding the formula writes to the database, so a db timeout is used to avoid a false timeout
        $this->env->assert('formula', $frm->name(), $frm_name, $this->env::TIMEOUT_LIMIT_DB);
        $msg->merge($add_msg); // collect the messages
        return $frm;
    }


    /*
     * reference test creation
     */

    function load_ref(string $wrd_name, string $type_name, user_message $msg): ref
    {
        $wrd = $this->load_word($msg, $wrd_name);
        $phr = $wrd->phrase();

        global $sys;
        $ref = new ref($this->env->usr1);
        if ($phr->id() != 0) {
            // TODO check if type name is the code id or really the name
            $ref->load_by_link_ids($phr->id(), $sys->typ_lst->ref_typ->id($type_name), $msg);
        }
        return $ref;
    }

    function add_ref(
        user_message $msg,
        string       $wrd_name,
        string       $external_key,
        string       $type_name
    ): ref
    {
        global $sys;
        $wrd = $this->test_word($msg, $wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name, $msg);
        if ($ref->id() == 0) {
            $ref->set_phrase($phr);
            // TODO check if type name is the code id or really the name
            $ref->set_predicate_id($sys->typ_lst->ref_typ->id($type_name));
            $ref->set_external_key($external_key);
            $msg = new user_message($this->env->usr1);
            if (!$ref->save($msg)) {
                log_err('add ref failed due to: ' . $msg->get_last_message());
            }
        }
        return $ref;
    }

    function test_ref(
        user_message $msg,
        string       $wrd_name,
        string       $external_key,
        string       $type_name
    ): ref
    {
        $ref = $this->add_ref($msg, $wrd_name, $external_key, $type_name);
        $target = $external_key;
        $this->env->assert('ref', $ref->get_external_key(), $target);
        return $ref;
    }

    function load_phrase(string $phr_name, user_message $msg): phrase
    {
        $phr = new phrase($this->env->usr1);
        $phr->load_by_name($phr_name, $msg);
        $phr->load_obj($msg);
        return $phr;
    }

    /**
     * test if a phrase with the given name exists, but does not create it, if it has not yet been created
     * @param string $phr_name name of the phrase to test
     * @return phrase the loaded phrase object
     */
    function test_phrase(
        string       $phr_name,
        user_message $msg
    ): phrase
    {
        $phr = $this->load_phrase($phr_name, $msg);
        $this->env->assert('phrase', $phr->name(true), $phr_name);
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list(array $array_of_word_str): word_list
    {
        $msg = new user_message();
        $wrd_lst = new word_list($this->env->usr1);
        $wrd_lst->load_by_names($array_of_word_str, $msg);
        return $wrd_lst;
    }

    function test_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = $this->load_word_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $wrd_lst->name();
        $this->env->assert(', word list', $result, $target);
        return $wrd_lst;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_phrase_list(array $array_of_word_str): phrase_list
    {
        $msg = new user_message();
        $phr_lst = new phrase_list($this->env->usr1);
        $phr_lst->load_by_names($array_of_word_str, $msg);
        return $phr_lst;
    }

    function test_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->env->assert(', phrase list', $result, $target);
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
        $msg = new user_message();
        $phr_grp = new group($this->env->usr1);
        $phr_grp->name = $phrase_group_name;
        $phr_grp->load_by_obj_vars($msg);
        return $phr_grp;
    }

    /**
     * add a phrase group to the database
     * @param array $array_of_phrase_str the phrase names
     * @param string $name the name that should be shown to the user
     * @return group the phrase group object including the database is
     */
    function add_phrase_group(array $array_of_phrase_str, string $name, user_message $msg): group
    {
        $grp = new group($this->env->usr1);
        $grp->get_by_phrase_list($this->load_phrase_list($array_of_phrase_str), $msg, $name);
        return $grp;
    }

    /**
     * delete a phrase group from the database
     * @param string $phrase_group_name the name that should be shown to the user
     * @return bool true if the phrase group has been deleted
     */
    function del_phrase_group(string $phrase_group_name): bool
    {
        $msg = new user_message($this->env->usr1);
        $phr_grp = $this->load_phrase_group_by_name($phrase_group_name);
        return $phr_grp->del($msg);
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $msg = new user_message();
        $val = new value($this->env->usr1);
        $val->load_by_id($id, $msg);
        return $val;
    }

    function load_value(array $array_of_word_str): value
    {
        $msg = new user_message();

        // the time separation is done here until there is a phrase series value table that can be used also to time phrases
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $phr_grp = $phr_lst->get_grp_id();

        $val = new value($this->env->usr1);
        if ($phr_grp == null) {
            log_warning('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->load_by_grp($phr_grp, $msg);
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        $val = $this->load_value($array_of_word_str);
        if (!$val->is_saved()) {
            $msg = new user_message($this->env->usr1);
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $phr_grp = $phr_lst->get_grp_id();

            // add missing words
            if (count($array_of_word_str) > $phr_lst->count()) {
                foreach ($array_of_word_str as $wrd_txt) {
                    $this->add_word($msg, $wrd_txt);
                }
                // retry
                $phr_lst = $this->load_phrase_list($array_of_word_str);
                $phr_grp = $phr_lst->get_grp_id();
            }

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
            if (!$val->save($msg)) {
                log_err('add value failed due to: ' . $msg->get_last_message());
            }
        }

        return $val;
    }

    function test_value(array $array_of_word_str, float $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->get_value();
        $this->env->assert(', value->load for ' . $val->name(), $result, $target);
        return $val;
    }

    function load_value_by_phr_grp(group $phr_grp): value
    {
        $msg = new user_message();
        $val = new value($this->env->usr1);
        $val->load_by_grp($phr_grp, $msg);
        return $val;
    }

    function add_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if (!$val->is_saved()) {
            $val->set_grp($phr_grp);
            $val->set_number($target);
            $msg = new user_message($this->env->usr1);
            if (!$val->save($msg)) {
                log_err('add value by group failed due to: ' . $msg->get_last_message());
            }
        }

        return $val;
    }

    function test_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->add_value_by_phr_grp($phr_grp, $target);
        $result = $val->number();
        $this->env->assert(', value->load for ' . $val->name(), $result, $target);
        return $val;
    }

    function del_value_by_phr_grp(group $phr_grp): bool
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        $msg = new user_message($this->env->usr1);
        return $val->del($msg);
    }


    /*
     * source test creation
     */

    function load_source(string $src_name): source
    {
        $msg = new user_message();
        $src = new source($this->env->usr1);
        $src->load_by_name($src_name, $msg);
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id() == 0) {
            $src->set_name($src_name);
            $msg = new user_message($this->env->usr1);
            if (!$src->save($msg)) {
                log_err('add source failed due to: ' . $msg->get_last_message());
            }
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->env->assert('source', $src->name(), $src_name);
        return $src;
    }

    /**
     * @return array json message to test if adding a new word via the api works fine
     */
    function word_put_json(user_message $msg): array
    {
        global $db_con;
        $msg_api = new api_message();
        $pod_name = $msg_api->api_site_name($db_con);
        $t_wrd = new test_words($this->env);
        $wrd = $t_wrd->word_add_via_api();
        $body_array = $wrd->api_json_array(new api_type_list([]), $msg);
        return $msg_api->api_header_array($pod_name, word::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a word via the api works fine
     */
    function word_post_json(user_message $msg): array
    {
        global $db_con;
        $msg_api = new api_message();
        $pod_name = $msg_api->api_site_name($db_con);
        $t_wrd = new test_words($this->env);
        $wrd = $t_wrd->word_update_via_api();
        $body_array = $wrd->api_json_array(new api_type_list([]), $msg);
        return $msg_api->api_header_array($pod_name, word::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new source via the api works fine
     */
    function source_put_json(user_message $msg): array
    {
        global $sys;
        global $db_con;
        $msg_api = new api_message();
        $pod_name = $msg_api->api_site_name($db_con);
        $src = new source($this->env->usr1);
        $src->set_name(sources::SYSTEM_TEST_ADD_API);
        $src->description = sources::SYSTEM_TEST_ADD_API_COM;
        $src->url = sources::SYSTEM_TEST_ADD_API_URL;
        $src->doi = sources::TEST_DOI;
        $src->type_id = $sys->typ_lst->src_typ->id(source_types::PDF);
        $body_array = $src->api_json_array(new api_type_list([]), $msg);
        return $msg_api->api_header_array($pod_name, source::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a source via the api works fine
     */
    function source_post_json(user_message $msg): array
    {
        global $db_con;
        $msg_api = new api_message();
        $pod_name = $msg_api->api_site_name($db_con);
        $src = new source($this->env->usr1);
        $src->set_name(sources::SYSTEM_TEST_UPD_API);
        $src->description = sources::SYSTEM_TEST_UPD_API_COM;
        $body_array = $src->api_json_array(new api_type_list([]), $msg);
        return $msg_api->api_header_array($pod_name, source::class, $this->env->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new reference via the api works fine
     */
    function reference_put_json(user_message $msg): array
    {
        global $db_con;
        global $sys;
        $t_wrd = new test_words($this->env);
        $msg_api = new api_message();
        $pod_name = $msg_api->api_site_name($db_con);
        $ref = new ref($this->env->usr1);
        $ref->set_phrase($t_wrd->word()->phrase());
        $ref->set_external_key(refs::SYSTEM_TEST_API_ADD_KEY);
        $ref->description = refs::SYSTEM_TEST_API_ADD_COM;
        $ref->url = refs::SYSTEM_TEST_API_ADD_URL;
        $ref->predicate_id = $sys->typ_lst->ref_typ->id(source_types::PDF);
        $body_array = $ref->api_json_array(new api_type_list([]), $msg);
        return $msg_api->api_header_array($pod_name, ref::class, $this->env->usr1, $body_array);
    }

    /*
     * view test creation
     */

    /**
     * load a view and if the test user is set for a specific user
     */
    function load_view(string $dsp_name, ?user $test_usr = null): view
    {
        $msg = new user_message();
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $msk = new view($test_usr);
        $msk->load_by_name($dsp_name, $msg);
        return $msk;
    }

    function add_view(string $dsp_name, user $test_usr, user_message $msg): view
    {
        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() == 0) {
            $msk->set_user($test_usr);
            $msk->set_name($dsp_name);
            $msk->save($msg);
            if (!$msg->is_ok()) {
                log_err('add view failed due to: ' . $msg->get_last_message());
            }
        }
        return $msk;
    }

    function test_view(string $dsp_name, user $test_usr, user_message $msg): view
    {
        $msk = $this->add_view($dsp_name, $test_usr, $msg);
        $this->env->assert('view', $msk->name(), $dsp_name, test_base::TIMEOUT_LIMIT_DB);
        return $msk;
    }

    function del_view(string $dsp_name, user $test_usr, user_message $msg): bool
    {
        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() != 0) {
            $msk->del_links($msg);
            $msk->del($msg);
        }
        return $msg->is_ok();
    }


    /*
     * component test creation
     */

    function load_component(string $cmp_name, user_message $msg, ?user $test_usr = null): component
    {
        if ($test_usr == null) {
            $test_usr = $this->env->usr1;
        }

        $cmp = new component($test_usr);
        $cmp->load_by_name($cmp_name, $msg);
        return $cmp;
    }

    function add_component(string $cmp_name, user_message $msg, user $test_usr, string $type_code_id = ''): component
    {
        global $sys;

        $cmp = $this->load_component($cmp_name, $msg, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $sys->typ_lst->cmp_typ->id($type_code_id);
            }
            if (!$cmp->save($msg)) {
                log_err('add component failed due to: ' . $msg->get_last_message());
            }
        }
        return $cmp;
    }

    function test_component(
        user_message $msg,
        string       $cmp_name,
        string       $type_code_id = ''
    ): component
    {
        $this->set_user($msg);

        $cmp = $this->add_component($cmp_name, $msg, $msg->usr, $type_code_id);
        $this->env->assert('view component', $cmp->name(), $cmp_name);
        return $cmp;
    }

    function test_component_lnk(
        string $dsp_name,
        string $cmp_name,
        int    $pos
    ): component_link
    {
        $msg = new user_message($this->env->usr1);
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name, $msg);
        $lnk = new component_link($this->env->usr1);
        $lnk->reset(true);
        $lnk->set_view($msk);
        $lnk->set_component($cmp);
        $lnk->order_nbr = $pos;
        $lnk->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $this->env->assert('view component link', $result, $target);
        return $lnk;
    }

    function test_component_unlink(string $dsp_name, string $cmp_name): string
    {
        $msg = new user_message($this->env->usr1);
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name, $msg);
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $cmp->unlink($msk, $msg);
        }
        return $msg->get_last_message();
    }

    function test_formula_link(string $formula_name, string $word_name, bool $auto_create = true): string
    {
        $result = '';
        $msg = new user_message($this->env->usr1);

        $frm = new formula($this->env->usr1);
        $frm->load_by_name($formula_name, $msg);
        $wrd = new word($this->env->usr1);
        $wrd->load_by_name($word_name, $msg);
        if ($frm->id() > 0 and $wrd->id() <> 0) {
            $frm_lnk = new formula_link($this->env->usr1);
            $frm_lnk->load_by_link($frm, $wrd->phrase(), $msg);
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->formula()->name() . ' is linked to ' . $frm_lnk->phrase()->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                // creating and loading the formula link writes to the database, so a db timeout is used
                $this->env->assert('formula_link', $result, $target, $this->env::TIMEOUT_LIMIT_DB);
            } else {
                if ($auto_create) {
                    $frm_lnk->set_formula($frm);
                    $frm_lnk->set_phrase($wrd->phrase());
                    $frm_lnk->save($msg);
                    if (!$msg->is_ok()) {
                        log_err('add formula link failed due to: ' . $msg->get_last_message());
                    }
                }
            }
        }
        return $result;
    }

    /**
     * check if the database rows used for unit testing are created
     * and create any missing
     *
     * @param test_cleanup $t object with the user for testing and to collect the error messages
     * @return void maybe return true if all tests are successful
     * TODO Prio 2 use a user_message object with the given user as parameter
     */
    function create_unit_test_db_entries(test_cleanup $t): void
    {
        new view_relation_write_tests()->create_base_view_relations($t);
        new view_link_write_tests()->create_base_view_links($t);
    }

    /**
     * create all database entries used for the read db unit tests
     * the created database rows can be accessed by the users
     * but are not expected to be changed and cannot be changed
     * all entries should be remove once the tests are done
     *
     * to if the test db entries for the unit tests are created
     * use the ... function
     * the db rows used for unit testing does not need to be removed after testing
     *
     * @param all_tests|a_selected_test $t the test object to collect the errors and calculate the execution times
     * @return void
     */
    function create_test_db_entries(all_tests|a_selected_test $t): void
    {
        new word_write_tests()->create_test_words($t);
        new triple_write_tests()->create_test_triples($t);
        new triple_write_tests()->create_base_times($t);
        new group_write_tests()->create_test_groups($t);
        new source_write_tests()->create_test_sources($t);
        new formula_write_tests()->create_test_formulas($t);
        new formula_link_write_tests()->create_test_formula_links($t);
        new view_write_tests()->create_test_views($t);
        new component_write_tests()->create_test_components($t);
        new component_link_write_tests()->create_test_component_links($t);
        new value_write_tests()->create_test_values($t);
    }

    /**
     * check the api test files whose content still contains database ids that are not yet fixed
     * e.g. the component ids shift as soon as a component is added to a seed view and the change
     * log ids shift with every additional change written by the setup, so these files cannot be
     * pinned like the other api test files and have to be recreated after a database reset
     *
     * updates the files only if test_files::AUTO_UPDATE_TEST_FILES is true, so that a normal run
     * just reports the difference and the developer decides if the new content is expected
     *
     * @param test_cleanup $t the test object to collect the errors and calculate the execution times
     * @param user_message $msg with the user for whom the api message should be created
     * @return bool true if all checked files match the database
     */
    function update_files_with_not_yet_fixed_db_id(test_cleanup $t, user_message $msg): bool
    {
        // start the test section (ts)
        $ts = 'db read files with not yet fixed db id ';

        $t->subheader($ts . 'api');

        // the components of a view, because the component ids shift with each added seed component
        $result = $this->update_api_list_file(
            $t, component_list::class, views::WORD_ADD_ID, url_var::VIEW);

        // the changes of a word, because the change log ids shift with each additional setup change
        if (!$this->update_api_chg_list_file($t, word::class, word_names::MATH_ID)) {
            $result = false;
        }

        // the changes of a single word field
        if (!$this->update_api_chg_list_file(
            $t, word::class, word_names::MATH_ID, change_fields::FLD_WORD_NAME)) {
            $result = false;
        }

        return $result;
    }

    /**
     * check one api list file and update it if the auto update flag is set
     *
     * @param test_cleanup $t the test object to collect the errors and calculate the execution times
     * @param string $class the class of the list that should be checked e.g. component_list::class
     * @param array|string $ids the database ids of the db rows that should be used for testing
     * @param string $id_fld the field name for the object id e.g. view_id
     * @return bool true if the file matches the database
     */
    private function update_api_list_file(
        test_cleanup $t,
        string       $class,
        array|string $ids,
        string       $id_fld
    ): bool
    {
        $result = $t->assert_api_get_list($class, $ids, $id_fld);

        // easy one click update of the expected result if the test_files::AUTO_UPDATE_TEST_FILES flag is true
        if (!$result and test_files::AUTO_UPDATE_TEST_FILES) {
            $lib = new library();
            $created = $t->assert_result_api_get_list($class, $ids, $id_fld);
            if ($this->api_json_usable($created)) {
                // remove the volatile fields e.g. the change time before saving, so that the
                // stored file does not change with every database reset; the compare ignores
                // the volatile fields anyway (see test_api::json_remove_volatile)
                $created = $t->json_remove_volatile($created);
                $filepath = test_paths::RESOURCE . $t->assert_parameter_api_list_filepath($class, $id_fld);
                $t->update_path_file($filepath, $lib->json_for_dev($created));
            }
        }

        return $result;
    }

    /**
     * check one api change log file and update it if the auto update flag is set
     *
     * @param test_cleanup $t the test object to collect the errors and calculate the execution times
     * @param string $class the class of the object whose changes should be checked e.g. word::class
     * @param int|string $id the database id of the object whose changes should be checked
     * @param string $fld the field name to check the changes of one field only e.g. word_name
     * @return bool true if the file matches the database
     */
    private function update_api_chg_list_file(
        test_cleanup $t,
        string       $class,
        int|string   $id,
        string       $fld = ''
    ): bool
    {
        $result = $t->assert_api_chg_list($class, $id, $fld);

        // easy one click update of the expected result if the test_files::AUTO_UPDATE_TEST_FILES flag is true
        if (!$result and test_files::AUTO_UPDATE_TEST_FILES) {
            $lib = new library();
            $created = $t->assert_result_api_chg_list($class, $id, $fld);
            if ($this->api_json_usable($created)) {
                // remove the volatile fields e.g. the change time before saving, so that the
                // stored file does not change with every database reset; the compare ignores
                // the volatile fields anyway (see test_api::json_remove_volatile)
                $created = $t->json_remove_volatile($created);
                $filepath = test_paths::RESOURCE . $t->assert_parameter_api_chg_list_filepath($class, $id, $fld);
                $t->update_path_file($filepath, $lib->json_for_dev($created));
            }
        }

        return $result;
    }

    /**
     * reload the types from the database
     * and checks if it matches the expected user interface type list received from the api
     * file api/ui_config/ui_config.json
     * and check the backend type list test
     * file src/test/resources/api/type_lists/type_lists.json
     *
     * @param all_tests $t the test object to collect the errors and calculate the execution times
     * @param user_message $msg with the user for whom the api message should be created which can differ from the session user
     * @return bool true if everything is fine and if false a repeat is suggested
     */
    function type_list_check(test_cleanup $t, user_message $msg): bool
    {
        // start the test section (ts)
        $ts = 'db read types and system views ';

        $t->subheader($ts . 'api');

        $ui_cfg = new ui_config();
        $ui_cfg->reload($msg);
        $result = $t->assert_api($ui_cfg, '', [api_types::HEADER, api_types::INCL_COMPONENTS]);

        // easy one click update of the expected result if the test_files::AUTO_UPDATE_TEST_FILES flag is true
        if (!$result and test_files::AUTO_UPDATE_TEST_FILES) {
            $lib = new library();
            $created = $t->assert_result_api_get($ui_cfg, $msg, [api_types::HEADER, api_types::INCL_COMPONENTS]);
            if ($this->api_json_usable($created)) {
                $filepath = test_paths::RESOURCE . $t->assert_parameter_api_list_filepath($ui_cfg::class);
                $t->update_path_file($filepath, $lib->json_for_dev($created));
            }
        }

        // check if the list of types matches the expected json file
        // called upfront also from the reset db run because this is used for the unit tests
        $result = $t->assert_api_get_list(type_lists::class);

        // easy one click update of the expected result if the test_files::AUTO_UPDATE_TEST_FILES flag is true
        if (!$result and test_files::AUTO_UPDATE_TEST_FILES) {
            $lib = new library();
            $created = $t->assert_result_api_get_list(type_lists::class);
            if ($this->api_json_usable($created)) {
                $filepath = test_paths::RESOURCE . $t->assert_parameter_api_list_filepath(type_lists::class);
                $t->update_path_file($filepath, $lib->json_for_dev($created));
            }
        }

        return $result;
    }

    /**
     * true if the api response json can be used to update the expected test resource:
     * the response must exist and must contain more than just a message,
     * because e.g. the login rejection of the pod ("This pod does not allow changes
     * without a login ...") or an unreachable deployment must never overwrite
     * a type list resource with the error message
     * the failure itself is already reported by the assert of the calling function
     *
     * @param array|null $created the decoded json of the api response or null if the call failed
     * @return bool true if the json is a usable api object and not just an error message
     */
    private function api_json_usable(?array $created): bool
    {
        $usable = false;
        if ($created !== null) {
            if (array_keys($created) != [json_fields::MSG]) {
                $usable = true;
            }
        }
        return $usable;
    }

    function csv_recreate(user_message $msg): bool
    {
        global $db_con;
        $lib = new library();

        $diff = '';
        foreach (def::MAIN_CLASSES as $class) {
            $csv_db = $db_con->csv_from_class($class, $msg);
            $csv_file_path = $lib->class_csv_file_path($class);
            $csv_file = file($csv_file_path);
            if ($csv_file === false) {
                log_err('csv file ' . $csv_file_path . ' for fixed base table entries not found');
            } else {
                // strip sensitive fields before comparing
                if ($class == user::class) {
                    $csv_db = $lib->csv_clear_col($csv_db, user_db::FLD_PASSWORD);
                    $csv_file = $lib->csv_clear_col($csv_file, user_db::FLD_PASSWORD);
                    $csv_db = $lib->csv_clear_col($csv_db, user_db::FLD_ACTIVATION_TIMEOUT);
                    $csv_file = $lib->csv_clear_col($csv_file, user_db::FLD_ACTIVATION_TIMEOUT);
                    $csv_db = $lib->csv_clear_col($csv_db, user_db::FLD_USES_SANDBOX);
                    $csv_file = $lib->csv_clear_col($csv_file, user_db::FLD_USES_SANDBOX);
                }
                $diff = $lib->diff_msg($csv_db, $csv_file);
                if ($diff != '') {
                    $target = implode("", $csv_db);
                    log_err('after database reset these ' . $lib->class_to_name($class)
                        . 's have been unexpected changed in ' . $csv_file_path . ': ' . $diff
                        . ' target is ' . substr($target, 0, 1000));
                    if (test_files::AUTO_UPDATE_TEST_FILES) {
                        // accept the current database content as the new expected csv
                        $this->env->update_path_file($csv_file_path, $target);
                    }
                }
            }
        }
        if ($diff == '') {
            return true;
        } else {
            return false;
        }

    }


    /*
     * internal
     */

    /**
     * if missing use the default test user
     *
     * @param user_message $msg the the user that should be used to perform the tests
     * @return void
     */
    function set_user(user_message $msg): void
    {
        if ($msg->usr == null) {
            $msg->usr = $this->env->usr1;
        }
    }

}