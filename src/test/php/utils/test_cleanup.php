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

namespace test;

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\db\sql_par;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_type;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\ref\ref_type;
use cfg\ref\source;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use const\files as test_files;
use const\paths as test_paths;
use html\html_base;
use shared\library;
use shared\const\components;
use shared\const\formulas;
use shared\const\sources;
use shared\const\triples;
use shared\const\views;
use shared\const\words;
use shared\types\verbs;

class test_cleanup extends test_api
{
    // queries to check if removing of the test rows is complete
    const CLEAN_CHECK_WORDS = 'db/cleanup/test_words.sql';
    const CLEAN_CHECK_TRIPLES = 'db/cleanup/test_triples.sql';
    const CLEAN_CHECK_FORMULAS = 'db/cleanup/test_formulas.sql';
    const CLEAN_CHECK_SOURCES = 'db/cleanup/test_sources.sql';
    const CLEAN_CHECKS = array(
        self::CLEAN_CHECK_WORDS,
        self::CLEAN_CHECK_TRIPLES,
        self::CLEAN_CHECK_FORMULAS,
        self::CLEAN_CHECK_SOURCES
    );


    /**
     * to remove all system test rows from the database
     *
     * @return bool true if all test rows have been successful deleted
     */
    function cleanup(): bool
    {
        global $db_con;

        global $test_val_lst;

        $result = ''; // the combine error message of all cleanup actions

        // make sure that all test elements are removed even if some tests have failed to have a clean setup for the next test
        $this->header('Cleanup the test');

        if ($test_val_lst != null) {
            foreach ($test_val_lst as $val_id) {
                if ($val_id > 0) {
                    // request to delete the added test value
                    $val = new value($this->usr1);
                    $val->load_by_id($val_id);
                    // check again, because some id may be added twice
                    if ($val->is_id_set()) {
                        $msg = $val->del();
                        $result .= $msg->get_last_message();
                        $target = '';
                        $this->display('value->del test value for "' . words::TEST_RENAMED . '"', $target, $result, self::TIMEOUT_LIMIT_DB_MULTI);
                    }
                }
            }
        }

        // secure cleanup the test views
        // TODO: if a user has changed the view during the test, delete also the user views

        $result .= $this->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_TITLE_NAME);
        $result .= $this->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_VALUES_NAME);
        $result .= $this->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_RESULTS_NAME);
        $result .= $this->test_component_unlink(views::TEST_EXCLUDED_NAME, components::TEST_EXCLUDED_NAME);
        $result .= $this->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TITLE_NAME);
        $result .= $this->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TABLE_NAME);

        // load the test view
        $msk = $this->load_view(views::TEST_ADD_NAME);
        if ($msk->id() <= 0) {
            $msk = $this->load_view(views::TEST_RENAMED_NAME);
        }

        // load the test view for user 2
        $dsp_usr2 = $this->load_view(views::TEST_ADD_NAME, $this->usr2);
        if ($dsp_usr2->id() <= 0) {
            $dsp_usr2 = $this->load_view(views::TEST_RENAMED_NAME, $this->usr2);
        }

        // load the first test view component
        $cmp = $this->load_component(components::TEST_ADD_NAME);
        if ($cmp->id() <= 0) {
            $cmp = $this->load_component(components::TEST_RENAMED_NAME);
        }

        // load the first test view component for user 2
        $cmp_usr2 = $this->load_component(components::TEST_ADD_NAME, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $this->load_component(components::TEST_RENAMED_NAME, $this->usr2);
        }

        // load the second test view component
        $cmp2 = $this->load_component(components::TEST_ADD_2_NAME);

        // load the second test view component for user 2
        $cmp2_usr2 = $this->load_component(components::TEST_ADD_2_NAME, $this->usr2);

        // check if the test components have been unlinked for user 2
        if ($dsp_usr2->id() > 0 and $cmp_usr2->id() > 0) {
            $result .= $cmp_usr2->unlink($dsp_usr2);
            $target = '';
            $this->display('cleanup: unlink first component "' . $cmp_usr2->name() . '" from "' . $dsp_usr2->name() . '" for user 2', $target, $result, self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the test components have been unlinked
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $result .= $cmp->unlink($msk);
            $target = '';
            $this->display('cleanup: unlink first component "' . $cmp->name() . '" from "' . $msk->name() . '"', $target, $result, self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component
        // error at the moment: if the second user is still using the link,
        // the second user does not get the owner
        // instead a foreign key error happens
        if ($msk->id() > 0 and $cmp2->id() > 0) {
            $result .= $cmp2->unlink($msk);
            $target = '';
            $this->display('cleanup: unlink second component "' . $cmp2->name() . '" from "' . $msk->name() . '"', $target, $result, self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component for user 2
        if ($dsp_usr2->id() > 0 and $cmp2_usr2->id() > 0) {
            $result .= $cmp2_usr2->unlink($dsp_usr2);
            $target = '';
            $this->display('cleanup: unlink second component "' . $cmp2_usr2->name() . '" from "' . $dsp_usr2->name() . '" for user 2', $target, $result, self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // request to delete the added test views
        foreach (components::TEST_COMPONENTS as $cmp_name) {
            $cmp = $this->load_component($cmp_name);
            if ($cmp->id() > 0) {
                $msg = $cmp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('component->del of "' . $cmp_name . '"', $target, $result);
            }
        }

        // request to delete the added test views
        foreach (views::TEST_VIEWS as $dsp_name) {
            $msk = $this->load_view($dsp_name);
            if ($msk->id() > 0) {
                $msg = $msk->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // reload the first test view component for user 2
        $cmp_usr2 = $this->load_component(components::TEST_ADD_NAME, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $this->load_component(components::TEST_RENAMED_NAME, $this->usr2);
        }

        // request to delete the test view component for user 2
        if ($cmp_usr2->id() > 0) {
            $msg = $cmp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of first component "' . components::TEST_ADD_NAME . '" for user 2', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // reload the first test view component
        $cmp = $this->load_component(components::TEST_ADD_NAME);
        if ($cmp->id() <= 0) {
            $cmp = $this->load_component(components::TEST_RENAMED_NAME);
        }

        // request to delete the test view component
        if ($cmp->id() > 0) {
            $msg = $cmp->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of first component "' . components::TEST_ADD_NAME . '"', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // reload the second test view component
        $cmp2 = $this->load_component(components::TEST_ADD_2_NAME);

        // request to delete the second added test view component
        if ($cmp2->id() > 0) {
            $msg = $cmp2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of second component "' . components::TEST_ADD_2_NAME . '"', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // request to delete the second added test view component for user 2
        if ($cmp2_usr2->id() > 0) {
            $msg = $cmp2_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of second component "' . components::TEST_ADD_2_NAME . '" for user 2', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // reload the test view for user 2
        $dsp_usr2 = $this->load_view(views::TEST_ADD_NAME, $this->usr2);
        if ($dsp_usr2->id() <= 0) {
            $dsp_usr2 = $this->load_view(views::TEST_RENAMED_NAME, $this->usr2);
        }

        // request to delete the added test view for user 2 first
        if ($dsp_usr2->id() > 0) {
            $msg = $dsp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of view "' . views::TEST_ADD_NAME . '" for user 2', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // reload the test view
        $msk = $this->load_view(views::TEST_ADD_NAME);
        if ($msk->id() <= 0) {
            $msk = $this->load_view(views::TEST_RENAMED_NAME);
        }

        // request to delete the added test view
        if ($msk->id() > 0) {
            $msg = $msk->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of view "' . views::TEST_ADD_NAME . '"', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test views
        foreach (views::TEST_VIEWS as $dsp_name) {
            $msk = $this->load_view($dsp_name);
            if ($msk->id() > 0) {
                $msg = $msk->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // request to delete the renamed test source
        $src = $this->load_source(sources::SYSTEM_TEST_RENAMED);
        if ($src->id() > 0) {
            $msg = $src->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('source->del of "' . sources::SYSTEM_TEST_RENAMED . '"', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test sources
        foreach (sources::TEST_SOURCES as $src_name) {
            if ($src_name != sources::WIKIDATA) {
                $src = $this->load_source($src_name);
                if ($src->id() > 0) {
                    $msg = $src->del();
                    $result .= $msg->get_last_message();
                    $target = '';
                    $this->display('source->del of "' . $src_name . '"', $target, $result);
                }
            }
        }

        // request to delete the added test reference
        $ref = $this->load_ref(words::TEST_ADD, ref_type::WIKIDATA);
        if ($ref->id() > 0) {
            $msg = $ref->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('ref->del of "' . words::TEST_ADD . '"', $target, $result);
        }

        // request to delete the added test formulas
        foreach (formulas::TEST_FORMULAS as $frm_name) {
            $msk = $this->load_formula($frm_name);
            if ($msk->id() > 0) {
                $msg = $msk->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('formula->del of "' . $frm_name . '"', $target, $result);
            }
        }

        // request to delete the added test phrases
        foreach (triples::TEST_TRIPLE_STANDARD as $phr_name) {
            $phr = $this->load_phrase($phr_name);
            if ($phr->id() <> 0) {
                $msg = $phr->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('phrase->del of "' . $phr_name . '"', $target, $result);
            }
        }

        // request to delete some triples not yet covered by the other cleanup jobs
        $this->del_triple(words::YEAR_2019, verbs::IS, words::YEAR_CAP);
        $this->del_triple(words::YEAR_2020, verbs::IS, words::YEAR_CAP);
        $this->del_triple(words::TEST_2021, verbs::IS, words::YEAR_CAP);
        $this->del_triple(words::TEST_2022, verbs::IS, words::YEAR_CAP);
        $this->del_triple(words::YEAR_2020, verbs::FOLLOW, words::YEAR_2019);
        $this->del_triple(words::TEST_2021, verbs::FOLLOW, words::YEAR_2020);
        $this->del_triple(words::TEST_2022, verbs::FOLLOW, words::TEST_2021);
        $this->del_triple(words::TEST_CASH_FLOW, verbs::IS, words::TEST_FIN_REPORT);
        $this->del_triple(words::TEST_TAX_REPORT, verbs::PART_NAME, words::TEST_CASH_FLOW);
        $this->del_triple(words::TEST_CASH, verbs::PART_NAME, words::TEST_ASSETS_CURRENT);
        $this->del_triple(words::TEST_ASSETS_CURRENT, verbs::PART_NAME, words::TEST_ASSETS);
        $this->del_triple(words::TEST_SECTOR, verbs::CAN_CONTAIN, words::TEST_ENERGY);
        $this->del_triple(words::TEST_ENERGY, verbs::CAN_CONTAIN, words::TEST_WIND_ENERGY);

        // request to delete the added test word
        // TODO: if a user has changed the word during the test, delete also the user words
        $wrd = $this->load_word(words::TEST_ADD);
        if ($wrd->id() > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('word->del of "' . words::TEST_ADD . '"', $target, $result);
        }

        // request to delete the renamed test word
        $wrd = $this->load_word(words::TEST_RENAMED);
        if ($wrd->id() > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('word->del of "' . words::TEST_RENAMED . '"', $target, $result, self::TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test words
        foreach (words::TEST_WORDS as $wrd_name) {
            // ... but keep the read only test word
            if ($wrd_name != words::MATH) {
                $wrd = $this->load_word($wrd_name);
                if ($wrd->id() > 0) {
                    $msg = $wrd->del();
                    $result .= $msg->get_last_message();
                    $target = '';
                    $this->display('word->del of "' . $wrd_name . '"', $target, $result);
                }
            }
        }

        // TODO better use a info system log message
        $html = new html_base();
        $html->echo_html($db_con->seq_reset(word::class));
        //$html->echo_html($db_con->seq_reset(sql_db::TBL_GROUP_LINK));
        //$html->echo_html($db_con->seq_reset(sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK));
        $html->echo_html($db_con->seq_reset(formula::class));
        $html->echo_html($db_con->seq_reset(formula_link::class));
        $html->echo_html($db_con->seq_reset(view::class));
        $html->echo_html($db_con->seq_reset(component::class));
        $html->echo_html($db_con->seq_reset(component_link::class));
        $html->echo_html($db_con->seq_reset(source::class));

        if ($result == '') {
            return true;
        } else {
            return false;
        }

    }

    /**
     * to check if there are any system test rows still in the database (e.g. to missing foreign key cleanup)
     *
     * @return bool true if no test record needs to be removed if $just_check has been true
     */
    function cleanup_check(): bool
    {
        $result = $this->cleanup_check_queries();
        if (!$result) {
            if (!$this->cleanup()) {
                log_err('Removing of system test database rows failed');
            }
            $result = $this->cleanup_check_queries();
            if (!$result) {
                log_err('Removing of system test database rows incomplete');
            }
        }
        return $result;
    }

    /**
     * create a dummy term list based on the given names
     * @param array $names the names that should be used to create the term list
     * @return term_list
     */
    function term_list_for_tests(array $names): term_list
    {
        global $usr;

        $trm_lst = new term_list($usr);
        $pos = 1;
        foreach ($names as $name) {
            $class = match ($name) {
                triples::PI_NAME => triple::class,
                formulas::SCALE_TO_SEC, formulas::THIS_NAME, formulas::PRIOR => formula::class,
                verbs::NOT_SET, verbs::CAN_CONTAIN_NAME, verbs::CAN_CONTAIN_NAME_REVERSE => verb::class,
                default => word::class,
            };
            $trm = new term($usr);
            $trm->set_obj_from_class($class);
            $trm->set_obj_id($pos);
            $trm->set_name($name);

            // set types of some special terms
            if ($name == formulas::THIS_NAME) {
                $trm->obj()->type_cl = formula_type::THIS;
                $trm->set_obj_id(formulas::THIS_ID);
                $wrd = new word($usr);
                $wrd->set(words::THIS_ID, formula_type::THIS);
                $trm->obj()->name_wrd = $wrd;
            }
            if ($name == formulas::PRIOR) {
                $trm->obj()->type_cl = formula_type::PREV;
                $trm->set_obj_id(formulas::PRIOR_ID);
                $wrd = new word($usr);
                $wrd->set(words::PRIOR_ID, formula_type::PREV);
                $trm->obj()->name_wrd = $wrd;
            }

            $trm_lst->add($trm);
            $pos++;
        }
        return $trm_lst;
    }

    /**
     * to check if there are any system test rows still in the database (e.g. to missing foreign key cleanup)
     * @return bool true if no system test rows remain in the database
     */
    private function cleanup_check_queries(): bool
    {

        $result = true;
        foreach (self::CLEAN_CHECKS as $sql_file_name) {
            if ($result) {
                $result = $this->cleanup_check_query($sql_file_name);
            }
        }

        return $result;
    }

    /**
     * @return bool true if no system test row is found by the given query
     */
    private function cleanup_check_query(string $sql_file_name): bool
    {
        global $db_con;

        $result = true;
        $qp = new sql_par(self::class);
        $qp->name .= $sql_file_name;
        $qp->sql = $this->file($sql_file_name);
        $db_rows = $db_con->get($qp);
        if ($db_rows != false) {
            log_err('There are ' . count($db_rows) . ' unexpected system test rows detected by ' . $sql_file_name);
            $result = false;
        }

        return $result;
    }

    function html_test(string $body, string $title, string $filename, test_cleanup $t): void
    {
        $lib = new library();

        if ($title == '') {
            $title = 'test';
        } else {
            $title = 'test ' . $title;
        }
        $created_html = $this->html_page($body, $title);
        $expected_html = $t->file(test_paths::HTML . $filename . test_files::HTML);
        $t->display($filename, $lib->trim_html($expected_html), $lib->trim_html($created_html));
    }

    function html_view_test(string $body, string $filename, test_cleanup $t): void
    {
        $this->html_test($body, 'view', test_paths::VIEWS . $filename, $t);
    }

    private function html_page(string $body, string $title): string
    {
        $html = new html_base();
        return $html->header_test($title) . $body . $html->footer();
    }

}