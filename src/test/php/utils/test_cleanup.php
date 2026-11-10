<?php

/*

    testing.php - add the TESTING cleanup functions to remove any remaining test records to the test_base class
    -----------


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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UTILS . 'test_api.php';
include_once test_paths::CREATE . 'test_const.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;

class test_cleanup extends test_api
{

    // the column name parts that mark the name of a test row in a cleanup check query e.g. word_name or name_generated
    const string NAME_FIELD_END = '_name';
    const string NAME_FIELD_START = 'name_';

    /*
     * object vars
     */

    // the ids of the test values added during the write tests (see value_write_tests),
    // so that the cleanup can remove them even if a test has failed before its own cleanup
    public array $test_val_ids = [];

    // the keys per cleanup check query of the rows that create_test_db_entries has added; they belong to the whole
    // write section, so a check_cleanup within it skips them and only the full cleanup removes them
    private array $created_row_keys = [];

    /**
     * remember the test rows that exist after create_test_db_entries, so that a check_cleanup of a single
     * write test reports only the leftovers of that test (see docs/llm/delete.md, test cleanup)
     */
    function remember_created_rows(): void
    {
        $this->created_row_keys = [];
        foreach (test_files::CLEAN_CHECKS as $sql_file_name) {
            foreach ($this->cleanup_check_rows(new user_message($this->usr_system), $sql_file_name) as $row) {
                if (is_array($row)) {
                    $this->created_row_keys[$sql_file_name][] = $this->created_row_key($row);
                }
            }
        }
    }

    /**
     * @param array $row a row of a cleanup check query with the column names, the id first
     * @return string the name of the row, because a write test may delete a row of create_test_db_entries and a later
     *                test adds it again with a new id, or the id for a row without a name e.g. a change log row
     */
    private function created_row_key(array $row): string
    {
        $key = (string)$row[0];
        $found = false;
        foreach ($row as $fld => $val) {
            $is_name = $this->is_name_field($fld);
            if (!$found and $is_name and $val != '') {
                $key = (string)$val;
                $found = true;
            }
        }
        return $key;
    }

    /**
     * @param int|string $fld the column name or the position of a value in a database row
     * @return bool true if the column holds the name of the row e.g. word_name or name_generated of an unnamed triple
     */
    private function is_name_field(int|string $fld): bool
    {
        return (is_string($fld) and (str_ends_with($fld, self::NAME_FIELD_END) or str_starts_with($fld, self::NAME_FIELD_START)));
    }


    /*
     * execute
     */


    /**
     * simple clean-up of the standard objects
     * @return bool
     */
    function cleanup_objects(): bool
    {
        $ts = 'cleanup all ';
        $this->cleanup_objects_ex_user();
        $t_usr = new test_users();
        $t_usr->cleanup($ts, $this);
        return true;
    }

    /**
     * simple clean-up of the standard objects excluding the test users
     * @return bool
     */
    function cleanup_objects_ex_user(): bool
    {
        $ts = 'cleanup all ex users ';
        $t_cmp = new test_components($this);
        $t_msk = new test_views($this);
        $t_res = new test_results($this);
        $t_frm = new test_formulas($this);
        $t_grp = new test_groups($this);
        $t_val = new test_values($this);
        $t_ref = new test_refs($this);
        $t_src = new test_sources($this);
        $t_trp = new test_triples($this);
        $t_vrb = new test_verbs($this);
        $t_wrd = new test_words($this);
        $t_cmp->cleanup($ts);
        $t_msk->cleanup($ts);
        //$t_res->cleanup($ts);
        $t_frm->cleanup($ts);
        $t_grp->cleanup($ts);
        // the values are removed by their phrase names, so they must go before the test words,
        // because a value whose word is already deleted cannot be found by its name any more
        $t_val->cleanup($ts);
        $t_ref->cleanup($ts);
        $t_src->cleanup($ts);
        $t_trp->cleanup($ts);
        $t_vrb->cleanup($ts);
        $t_wrd->cleanup($ts);
        return true;
    }

    /**
     * TODO Prio 2 use the user message object instead of a string
     * TODO Prio 2 split and use more internal functions for the parts
     * to remove all system test rows from the database
     *
     * @return bool true if all test rows have been successfully deleted
     */
    function cleanup(user_message $msg): bool
    {
        global $db_con;

        $t_db = new test_db_load($this);

        $result = ''; // the combine error message of all cleanup actions

        // start the test section (ts)
        // make sure that all test elements are removed even if some tests have failed to have a clean setup for the next test
        $ts = 'db cleanup ';
        $this->header($ts);
        // the full cleanup removes also the rows of create_test_db_entries, so the next check covers them again
        $this->created_row_keys = [];

        $this->cleanup_test_values($this->test_val_ids, $msg);

        // remove the fixed test groups that create_test_groups has added for the write tests like
        // the test words below, because the next read or write tests add them again if needed
        $t_grp = new test_groups($this);
        $t_grp->cleanup($ts);

        // secure cleanup the test views
        // TODO Prio 2 if a user has changed the view during the test, delete also the user views

        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_TITLE_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_VALUES_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_RESULTS_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_EXCLUDED_NAME, components::TEST_EXCLUDED_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TITLE_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TABLE_NAME);

        // a term view is not expected after a database reset, so the link that the write tests
        // create is removed here (see test_db_load::test_term_view_unlink)
        // with a message of its own, because the shared message may already carry the leftover report
        $trm_msk_msg = $msg->clone_reset();
        $result .= $t_db->test_term_view_unlink($trm_msk_msg, new test_views($this)->term_view());
        $msg->merge($trm_msk_msg);

        // load the test view
        $msk = $t_db->load_view(views::TEST_ADD_NAME);
        if ($msk->id() <= 0) {
            $msk = $t_db->load_view(views::TEST_RENAMED_NAME);
        }

        // load the test view for user 2
        $msk_usr2 = $t_db->load_view(views::TEST_ADD_NAME, $this->usr2);
        if ($msk_usr2->id() <= 0) {
            $msk_usr2 = $t_db->load_view(views::TEST_RENAMED_NAME, $this->usr2);
        }

        // each step of the cleanup is independent, so each one loads and deletes with an own message that is
        // merged into $msg after the step, because no load starts on an error of an earlier step
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below

        // load the first test view component
        $cmp = $t_db->load_component(components::TEST_ADD_NAME, $stp_msg);
        if ($cmp->id() <= 0) {
            $cmp = $t_db->load_component(components::TEST_RENAMED_NAME, $stp_msg);
        }

        // load the first test view component for user 2
        $cmp_usr2 = $t_db->load_component(components::TEST_ADD_NAME, $stp_msg, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $t_db->load_component(components::TEST_RENAMED_NAME, $stp_msg, $this->usr2);
        }

        // load the second test view component
        $cmp2 = $t_db->load_component(components::TEST_ADD_2_NAME, $stp_msg);

        // load the second test view component for user 2
        $cmp2_usr2 = $t_db->load_component(components::TEST_ADD_2_NAME, $stp_msg, $this->usr2);
        $msg->merge($stp_msg);

        // check if the test components have been unlinked for user 2
        if ($msk_usr2->id() > 0 and $cmp_usr2->id() > 0) {
            $test_name = 'cleanup: unlink first component "' . $cmp_usr2->name() . '" from "' . $msk_usr2->name() . '" for user 2';
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $cmp_usr2->unlink($msk_usr2, $stp_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            $msg->merge($stp_msg);
        }

        // check if the test components have been unlinked
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $test_name = 'cleanup: unlink first component "' . $cmp->name() . '" from "' . $msk->name() . '"';
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $cmp->unlink($msk, $stp_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            $msg->merge($stp_msg);
        }

        // unlink the second component
        // error at the moment: if the second user is still using the link,
        // the second user does not get the owner
        // instead a foreign key error happens
        if ($msk->id() > 0 and $cmp2->id() > 0) {
            $test_name = 'cleanup: unlink second component "' . $cmp2->name() . '" from "' . $msk->name() . '"';
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $cmp2->unlink($msk, $stp_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            $msg->merge($stp_msg);
        }

        // unlink the second component for user 2
        if ($msk_usr2->id() > 0 and $cmp2_usr2->id() > 0) {
            $test_name = 'cleanup: unlink second component "' . $cmp2_usr2->name() . '" from "' . $msk_usr2->name() . '" for user 2';
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $cmp2_usr2->unlink($msk_usr2, $stp_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            $msg->merge($stp_msg);
        }

        // request to delete the added test views
        foreach (views::TEST_VIEWS as $dsp_name) {
            $msk = $t_db->load_view($dsp_name);
            if ($msk->id() > 0) {
                // with a message of its own, because the shared message may already carry e.g. the
                // leftover report of check_cleanup, which is no problem of this delete
                $usr_msg_del = $msg->clone_reset();
                $msk->del($usr_msg_del);
                $result .= $usr_msg_del->get_last_message();
                // deleting the view writes to the database, so a db timeout is used to avoid a false timeout
                $this->assert_msg('view->del of "' . $dsp_name . '"', $usr_msg_del, self::TIMEOUT_LIMIT_DB);
                $msg->merge($usr_msg_del);
            }
        }

        foreach (components::TEST_COMPONENTS as $cmp_name) {
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $cmp = $t_db->load_component($cmp_name, $stp_msg);
            if ($cmp->id() > 0) {
                $test_name = 'request to delete the added test views of "' . $cmp_name . '"';
                $this->assert_true($test_name, $cmp->del($stp_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            }
            $msg->merge($stp_msg);
        }

        $test_name = 'reload the first test view component "' . components::TEST_ADD_NAME . '" for user 2';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $cmp_usr2 = $t_db->load_component(components::TEST_ADD_NAME, $stp_msg, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $test_name .= ' or "' . components::TEST_RENAMED_NAME . '"';
            $cmp_usr2 = $t_db->load_component(components::TEST_RENAMED_NAME, $stp_msg, $this->usr2);
        }

        $test_name .= ' and request to delete the test view component  for user 2';
        if ($cmp_usr2->id() > 0) {
            $this->assert_true($test_name, $cmp_usr2->del($stp_msg), self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name = 'reload the first test view component "' . components::TEST_ADD_NAME . '"';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $cmp = $t_db->load_component(components::TEST_ADD_NAME, $stp_msg);
        if ($cmp->id() <= 0) {
            $test_name .= ' or "' . components::TEST_RENAMED_NAME . '"';
            $cmp = $t_db->load_component(components::TEST_RENAMED_NAME, $stp_msg);
        }

        $test_name .= ' and request to delete the test view component';
        if ($cmp->id() > 0) {
            $this->assert_true($test_name, $cmp->del($stp_msg), self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name = 'request to delete the second added test view component "' . components::TEST_ADD_2_NAME . '"';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $cmp2 = $t_db->load_component(components::TEST_ADD_2_NAME, $stp_msg);
        if ($cmp2->id() > 0) {
            $this->assert_true($test_name, $cmp2->del($stp_msg), self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name = 'request to delete the second added test view component "' . components::TEST_ADD_2_NAME . '" for user 2';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $cmp2_usr2 = $t_db->load_component(components::TEST_ADD_2_NAME, $stp_msg, $this->usr2);
        if ($cmp2_usr2->id() > 0) {
            $this->assert_true($test_name, $cmp2_usr2->del($stp_msg), self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name = 'reload the test view "' . views::TEST_ADD_NAME . '"  for user 2';
        $msk_usr2 = $t_db->load_view(views::TEST_ADD_NAME, $this->usr2);
        if ($msk_usr2->id() <= 0) {
            $test_name .= ' or "' . views::TEST_RENAMED_NAME . '"';
            $msk_usr2 = $t_db->load_view(views::TEST_RENAMED_NAME, $this->usr2);
        }

        $test_name .= ' and request to delete the added test view for user 2 first';
        if ($msk_usr2->id() > 0) {
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $msk_usr2->del($stp_msg), self::TIMEOUT_LIMIT_DB);
            $msg->merge($stp_msg);
        }

        $test_name = 'reload the test view "' . views::TEST_ADD_NAME . '"';
        $msk = $t_db->load_view(views::TEST_ADD_NAME);
        if ($msk->id() <= 0) {
            $test_name .= ' or "' . views::TEST_RENAMED_NAME . '"';
            $msk = $t_db->load_view(views::TEST_RENAMED_NAME);
        }

        $test_name .= ' and request to delete it';
        if ($msk->id() > 0) {
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $msk->del($stp_msg), self::TIMEOUT_LIMIT_DB);
            $msg->merge($stp_msg);
        }

        $test_name_loop = 'request to delete the added test views';
        foreach (views::TEST_VIEWS as $msk_name) {
            $test_name = $test_name_loop . ' "' . $msk_name . '"';
            $msk = $t_db->load_view($msk_name);
            if ($msk->id() > 0) {
                $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
                $this->assert_true($test_name, $msk->del($stp_msg), self::TIMEOUT_LIMIT_DB);
                $msg->merge($stp_msg);
            }
        }

        $test_name = 'request to delete the renamed test source "' . sources::SYSTEM_TEST_RENAMED . '"';
        $src = $t_db->load_source(sources::SYSTEM_TEST_RENAMED);
        if ($src->id() > 0) {
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $this->assert_true($test_name, $src->del($stp_msg), self::TIMEOUT_LIMIT_DB);
            $msg->merge($stp_msg);
        }

        $test_name_loop = 'request to delete the added test sources';
        foreach (sources::TEST_SOURCES as $src_name) {
            $test_name = $test_name_loop . ' "' . $src_name . '"';
            if ($src_name != sources::WIKIDATA) {
                $src = $t_db->load_source($src_name);
                if ($src->id() > 0) {
                    $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
                    $this->assert_true($test_name, $src->del($stp_msg), self::TIMEOUT_LIMIT_DB);
                    $msg->merge($stp_msg);
                }
            }
        }

        $test_name = 'request to delete the added test reference "' . word_names::TEST_ADD . '" to "' . ref_types::WIKIDATA . '"';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $ref = $t_db->load_ref(word_names::TEST_ADD, ref_types::WIKIDATA, $stp_msg);
        if ($ref->id() > 0) {
            $this->assert_true($test_name, $ref->del($stp_msg), self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name_loop = 'request to delete the added test formulas';
        foreach (formula_names::TEST_FORMULAS as $frm_name) {
            $test_name = $test_name_loop . ' "' . $frm_name . '"';
            $frm = $t_db->load_formula($frm_name);
            if ($frm->id() > 0) {
                $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
                $this->assert_true($test_name, $frm->del($stp_msg), self::TIMEOUT_LIMIT_DB);
                $msg->merge($stp_msg);
            }
            // remove the corresponding formula word
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $wrd = $t_db->load_word($stp_msg, $frm_name);
            if ($wrd->id() > 0) {
                $this->assert_true($test_name, $wrd->del($stp_msg), self::TIMEOUT_LIMIT_DB);
            }
            $msg->merge($stp_msg);
        }

        $test_name_loop = 'request to delete the added test phrases';
        foreach (triple_names::TEST_TRIPLES as $phr_name) {
            $test_name = $test_name_loop . ' "' . $phr_name . '"';
            $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
            $phr = $t_db->load_phrase($phr_name, $stp_msg);
            if ($phr->id() <> 0) {
                $this->assert_true($test_name, $phr->del($stp_msg), self::TIMEOUT_LIMIT_DB);
            }
            $msg->merge($stp_msg);
        }

        $this->cleanup_unnamed_test_triples($msg);

        // request to delete the added test word
        // TODO Prio 2 if a user has changed the word during the test, delete also the user words
        $test_name = 'request to delete the added test word "' . word_names::TEST_ADD . '"';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $wrd = $t_db->load_word($stp_msg, word_names::TEST_ADD);
        if ($wrd->id() > 0) {
            $wrd->del($stp_msg);
            // assert via the message, so a failing delete shows the reason instead of a bare false
            $this->assert_msg($test_name, $stp_msg, self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name = 'request to delete the renamed test word  of "' . word_names::TEST_RENAMED . '"';
        $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
        $wrd = $t_db->load_word($stp_msg, word_names::TEST_RENAMED);
        if ($wrd->id() > 0) {
            $wrd->del($stp_msg);
            $this->assert_msg($test_name, $stp_msg, self::TIMEOUT_LIMIT_DB);
        }
        $msg->merge($stp_msg);

        $test_name_loop = 'request to delete the added test words';
        foreach (word_names::TEST_WORDS as $wrd_name) {
            $test_name = $test_name_loop . ' "' . $wrd_name . '"';
            if ($wrd_name != word_names::MATH) {
                $stp_msg = $msg->clone_reset(); // a buffer of this cleanup step, merged below
                $wrd = $t_db->load_word($stp_msg, $wrd_name);
                if ($wrd->id() > 0) {
                    $owner = $wrd->owner($stp_msg);
                    // the requesting user of this step is the owner, because the message is only used for this word
                    $stp_msg->usr = $owner;
                    // reload the word as owner
                    // TODO Prio 1 also reload the other objects as owner before trying to delete them
                    $wrd = $t_db->load_word($stp_msg, $wrd_name);
                    $wrd->del($stp_msg);
                    $this->assert_msg($test_name, $stp_msg, self::TIMEOUT_LIMIT_DB);
                } else {
                    // a word that the test user has excluded is not loaded for this user,
                    // so it is removed via the system user (see write_named_cleanup_force)
                    $this->write_named_cleanup(new word($this->usr1), $wrd_name);
                }
                $msg->merge($stp_msg);
            } else {
                log_info(' ... but keep the read only test word ' . word_names::MATH);
            }
        }

        // TODO Prio 3 better use a info system log message
        // route through the timestamped writer so every 'Next database id' line starts with a timestamp
        echo_timestamped($db_con->seq_reset(word::class, $msg));
        echo_timestamped($db_con->seq_reset(formula::class, $msg));
        echo_timestamped($db_con->seq_reset(formula_link::class, $msg));
        echo_timestamped($db_con->seq_reset(view::class, $msg));
        echo_timestamped($db_con->seq_reset(component::class, $msg));
        echo_timestamped($db_con->seq_reset(component_link::class, $msg));
        echo_timestamped($db_con->seq_reset(source::class, $msg));

        $this->cleanup_guest_users();

        // the deletes above write change log entries naming the test rows themselves,
        // so remove them by the reserved test name pattern as the last cleanup step
        $this->cleanup_change_log_deleted();

        if ($result == '') {
            return true;
        } else {
            return false;
        }

    }

    /**
     * remove the test triples without a name of triple_names::TEST_TRIPLES, because a word that
     * a triple uses is only excluded by its delete (see word::used_by_data)
     * public to remove them also before the test words in the word write tests
     *
     * @param user_message $msg the shared message of the cleanup
     * @return void
     */
    function cleanup_unnamed_test_triples(user_message $msg): void
    {
        $t_db = new test_db_load($this);
        // the time test words are linked to the year and to the previous year like in
        // word_write_tests::create_test_words; only a triple whose "from" is a test word is a test row:
        // the triples of the real year words are seed rows with a pinned id (e.g. triple_names::YEAR_2019_ID)
        // and deleting one excludes it for the test user, which takes it out of every later read
        $prev_word_name = null;
        foreach (word_names::TEST_WORDS_TIME_YEAR as $word_name) {
            if (in_array($word_name, word_names::TEST_WORDS)) {
                $t_db->del_triple($msg, $word_name, verbs::IS, words::YEAR_CAP);
                if ($prev_word_name != null) {
                    $t_db->del_triple($msg, $word_name, verbs::FOLLOW, $prev_word_name);
                }
            }
            $prev_word_name = $word_name;
        }
        $t_db->del_triple($msg, word_names::TEST_CASH_FLOW, verbs::IS, word_names::TEST_FIN_REPORT);
        $t_db->del_triple($msg, word_names::TEST_TAX_REPORT, verbs::PART_NAME, word_names::TEST_CASH_FLOW);
        $t_db->del_triple($msg, word_names::TEST_CASH, verbs::PART_NAME, word_names::TEST_ASSETS_CURRENT);
        $t_db->del_triple($msg, word_names::TEST_ASSETS_CURRENT, verbs::PART_NAME, word_names::TEST_ASSETS);
        $t_db->del_triple($msg, word_names::TEST_SECTOR, verbs::CAN_CONTAIN, word_names::TEST_ENERGY);
        $t_db->del_triple($msg, word_names::TEST_ENERGY, verbs::CAN_CONTAIN, word_names::TEST_WIND_ENERGY);
        // a test row although its "from" is a seed word, because word_write_tests links it to the test parent word (add_child)
        $t_db->del_triple($msg, word_names::MATH, verbs::IS, word_names::TEST_PARENT);
    }

    /**
     * remove the given test values
     *
     * @param array $val_ids the ids of the values e.g. test_val_ids for the full cleanup or the values that one test has added
     * @param user_message $msg the shared message of the cleanup, which may already carry e.g. the
     *                          leftover report of check_cleanup; the problems of the deletes are added
     * @return void
     */
    function cleanup_test_values(array $val_ids, user_message $msg): void
    {
        foreach ($val_ids as $val_id) {
            // the id of a value is the id of its group, which is a text for more than a few phrases
            if ($val_id !== 0 and $val_id !== '') {
                // with a message of its own, because del() writes nothing if the message is not ok
                $val_msg = new user_message($this->usr1);
                $val = new value($this->usr1);
                // check again via the returned id, because some id may be added twice
                // and the query creation already sets the id of the value
                if ($val->load_by_id($val_id, $val_msg) != 0) {
                    $this->del_value($val, $val_msg);
                    $this->assert_msg('value->del test value ' . $val_id, $val_msg, self::TIMEOUT_LIMIT_DB_MULTI);
                } else {
                    // a test that has removed its value itself may have left the group row behind
                    $this->del_group_row($val_id, $val_msg);
                }
                $msg->merge($val_msg);
            }
        }
    }

    /**
     * remove the anonymous users that the test calls to localhost have created, because such a
     * user is a leftover of the calling test (ui, workflow or write) and not a fixed row
     *
     * @return void
     */
    function cleanup_guest_users(): void
    {
        foreach (users::TEST_GUEST_NAMES as $usr_name) {
            $this->write_named_cleanup_user($usr_name, $this->usr_system);
        }
    }

    /**
     * remove a test value together with the group row that its creation has added, because
     * the value delete keeps the group row, which would otherwise stay as a leftover of the test
     *
     * @param value $val the test value to remove
     * @param user_message $msg to collect the messages of the delete
     * @return bool true if the value and its group row are removed
     */
    function del_value(value $val, user_message $msg): bool
    {
        $grp_id = $val->grp()->id();
        $val->del($msg, false);
        $this->del_group_row($grp_id, $msg);
        return $msg->is_ok();
    }

    /**
     * remove the group row of a test value if one exists, e.g. because a test has named the group
     * or saved a description; a group works via its id alone, so the row is only needed for the
     * name or the description (see group::load_by_id) and is checked after the value delete
     *
     * @param int|string $grp_id the id of the group of the test value
     * @param user_message $msg to collect the messages of the delete
     * @return void
     */
    private function del_group_row(int|string $grp_id, user_message $msg): void
    {
        global $db_con;

        $grp = new group($this->usr1);
        $grp->load_by_id($grp_id, $msg);
        if ($grp->is_saved()) {
            $grp->del($msg);
            // the reload reports if the logged delete of the group has not removed the row
            $grp->reset();
            $grp->load_by_id($grp_id, $msg);
            $this->assert_false('the group row of the test value ' . $grp_id . ' is removed', $grp->is_saved());
        }
    }

    /*
     * test
     */

    /**
     * test with general queries if there are any test rows left in the database incl. the change log.
     * reports what has been left over so that the issue can be fixed.
     * removes any remaining the test datasets from the database using different methods
     * @param user_message $msg with the user messages that occurred until now
     * @return bool true if the clean-up was successful
     */
    function check_cleanup(user_message $msg, string $class): bool
    {
        // the test cleanups delete the test rows via del(), which itself writes change log entries
        // naming the test rows, so purge them by the reserved test name pattern before the overall
        // check that no test row is left in any table incl. the change log
        $this->cleanup_change_log_deleted();
        $chk_msg = $msg->clone_reset(); // the leftover report, merged after the cleanup, because no load starts on an earlier error
        if (!$this->cleanup_check_queries($chk_msg, $class)) {
            $msg_start = 'there are ';
            $msg_text = 'unexpected system test rows in the database that could ';
            if ($this->cleanup($msg)) {
                if ($this->cleanup_check_queries($msg, $class)) {
                    $msg_start = 'there have been ';
                    $msg_text .= 'habe been removed: ';
                } else {
                    $msg_start = 'there are still ';
                    $msg_text .= 'NOT be removed: ';
                }
            } else {
                $msg_text .= 'NOT be fully removed: ';
            }
            $msg->merge($chk_msg);
            $err_txt = $msg->all_message_text();
            $msg_txt = $msg_start . $msg_text . $err_txt;
            if ($err_txt != '') {
                log_err($msg_txt);
            } else {
                log_warning($msg_txt);
            }
        }
        return $msg->is_ok();
    }


    /*
     * internal
     */

    /**
     * test if there are any system test rows still in the database using a list of general queries
     * e.g. to missing foreign key clean-up.
     * always run all queries to get an overview about all remaining rows
     * @return bool true if no system test rows remain in the database
     */
    private function cleanup_check_queries(user_message $msg, string $class): bool
    {

        foreach (test_files::CLEAN_CHECKS as $sql_file_name) {
            if (!$this->cleanup_check_query($msg, $class, $sql_file_name)) {
                log_warning('cleanup check failed for ' . $sql_file_name);
            };
        }

        return $msg->is_ok();
    }

    /**
     * @return bool true if the given query finds no system test row
     */
    private function cleanup_check_query(user_message $msg, string $class, string $sql_file_name): bool
    {
        $created_keys = $this->created_row_keys[$sql_file_name] ?? [];
        foreach ($this->cleanup_check_rows($msg, $sql_file_name) as $row) {
            // a row of create_test_db_entries is no leftover until the full cleanup has run
            $is_created = (is_array($row) and in_array($this->created_row_key($row), $created_keys));
            if (!$is_created) {
                $name = 'name missing';
                if (is_array($row)) {
                    if (count($row) > 2) {
                        $name = $row[2] . ' (' . $row[0] . ')';
                    } else {
                        $name = library::dsp_array($row);
                    }
                } elseif (is_string($row)) {
                    $name = $row;
                }

                $msg->add(msg_id::DB_CLEANUP_ERROR, [
                    msg_id::VAR_CLASS_NAME => $class,
                    msg_id::VAR_NAME => $name,
                    msg_id::VAR_FILE_NAME => $sql_file_name
                ]);
            }
        }

        return $msg->is_ok();
    }

    /**
     * @param user_message $msg to collect the problems of the query
     * @param string $sql_file_name the cleanup check query e.g. test_files::CLEAN_CHECK_WORDS
     * @return array the system test rows that the query finds
     */
    private function cleanup_check_rows(user_message $msg, string $sql_file_name): array
    {
        global $db_con;

        $qp = new sql_par(self::class);
        $qp->name .= $sql_file_name;
        $qp->sql = $this->file($sql_file_name);
        $db_rows = $db_con->get($qp, $msg);
        if ($db_rows === false) {
            return [];
        }
        return $db_rows;
    }

    /**
     * create a dummy term list based on the given names
     * @param array $names the names that should be used to create the term list
     * @return term_list
     */
    function term_list_for_tests(array $names): term_list
    {

        $trm_lst = new term_list($this->usr1);
        $pos = 1;
        foreach ($names as $name) {
            $class = match ($name) {
                triple_names::PI_NAME => triple::class,
                formula_names::SCALE_TO_SEC, formula_names::THIS_NAME, formula_names::PRIOR => formula::class,
                verbs::NOT_SET, verbs::CAN_CONTAIN_NAME, verbs::CAN_CONTAIN_NAME_REVERSE => verb::class,
                default => word::class,
            };
            $trm = new term($this->usr1);
            $trm->set_obj_from_class($class);
            $trm->set_obj_id($pos);
            $trm->set_name($name);

            // set types of some special terms
            if ($name == formula_names::THIS_NAME) {
                $trm->obj()->type_cl = formula_type::THIS;
                $trm->set_obj_id(formula_names::THIS_ID);
                $wrd = new word($this->usr1);
                $wrd->set(word_names::THIS_ID, formula_type::THIS);
                $trm->obj()->name_phr = $wrd->phrase();
            }
            if ($name == formula_names::PRIOR) {
                $trm->obj()->type_cl = formula_type::PREV;
                $trm->set_obj_id(formula_names::PRIOR_ID);
                $wrd = new word($this->usr1);
                $wrd->set(word_names::PRIOR_ID, formula_type::PREV);
                $trm->obj()->name_phr = $wrd->phrase();
            }

            $trm_lst->add($trm);
            $pos++;
        }
        return $trm_lst;
    }

    function html_page_test(
        string          $body,
        string          $title,
        string          $filename,
        user_message_ui $msg = new user_message_ui(),
        string          $base_url = '',
        string          $lan = languages::DEFAULT
    ): bool
    {
        return $this->html_test($body, $title, test_paths::VIEW_FUNCTIONS . $filename, $msg, $base_url, $lan);
    }

    function html_view_test(string $body, string $filename, user_message_ui $msg = new user_message_ui()): bool
    {
        return $this->html_test($body, 'view', test_paths::VIEWS . $filename, $msg);
    }

    /**
     * @param string $body the start page ranking rendered for one selection of its sub menus
     * @param string $filename the snapshot name e.g. "start_page_dls_20_dla_chart"
     * @param user_message_ui $msg the messages of the rendering, shown in the snapshot
     * @return bool true if the page matches the snapshot in the start_page resource folder
     */
    function html_start_page_test(string $body, string $filename, user_message_ui $msg): bool
    {
        return $this->html_test($body, 'start page', test_paths::START_PAGE . $filename, $msg);
    }

    /**
     * @param string $body the settings page rendered with the test part of config.yaml
     * @param string $filename the snapshot name e.g. "system_config_read_only"
     * @param user_message_ui $msg the messages of the rendering, shown in the snapshot
     * @return bool true if the page matches the snapshot in the config_page resource folder
     */
    function html_config_page_test(string $body, string $filename, user_message_ui $msg): bool
    {
        return $this->html_test($body, 'settings', test_paths::CONFIG_PAGE . $filename, $msg);
    }

    /**
     * check if a generated html page matches the fixed html page saved in the resource path
     * @param string $body the generated html page body
     * @param string $title the page title name
     * @param string $file_path the file path starting from the resource path for the html resources
     * @param user_message_ui $msg to collect the data retriaval messages
     * @return bool
     */
    private function html_test(
        string          $body,
        string          $title,
        string          $file_path,
        user_message_ui $msg,
        string          $base_url = '',
        string          $lan = languages::DEFAULT
    ): bool
    {
        if ($title == '') {
            $title = 'test';
        } else {
            $title = 'test ' . $title;
        }
        $created_html = $this->html_page($body, $title, $msg, $base_url, $lan);
        $resource_file = test_paths::RESOURCE . test_paths::HTML . $file_path . test_files::HTML;
        // the object page snapshot renders a complete html page (the all-component-types page renders every
        // component type) and compares it against a file, so a long timeout is used to avoid a false timeout
        return $this->assert_file(
            $file_path, $created_html, $resource_file, test_files::HTML, test_const::DUMMY_SESSION_TOKEN, self::TIMEOUT_LIMIT_LONG);
    }

    private function html_page(
        string          $body,
        string          $title,
        user_message_ui $msg,
        string          $base_url = '',
        string          $lan = languages::DEFAULT
    ): string
    {
        $html = new html_base();
        // a caller can set the pod url as the base url, so that its snapshot opened from the
        // file system or an ide preview server still finds the styles and the icon font;
        // the language is set once by the caller and only passed through, never overwritten
        return $html->header($title, $msg, '', $lan, $base_url)
            . $html->navbar(views::START_ID)
            . $html->main($body)
            . $html->footer();
    }

}