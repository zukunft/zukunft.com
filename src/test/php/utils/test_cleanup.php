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

use api\component\component as component_api;
use api\formula\formula as formula_api;
use api\phrase\phrase as phrase_api;
use api\ref\source as source_api;
use api\word\triple as triple_api;
use api\verb\verb as verb_api;
use api\view\view as view_api;
use api\word\word as word_api;
use cfg\db\sql_par;
use cfg\formula;
use cfg\formula_type;
use cfg\library;
use cfg\phrase;
use cfg\phrase_list;
use cfg\phrase_type;
use cfg\ref_type;
use cfg\db\sql_db;
use cfg\term;
use cfg\term_list;
use cfg\triple;
use cfg\value\value;
use cfg\verb;
use cfg\word;
use html\html_base;

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
                    if ($val->id() > 0) {
                        $msg = $val->del();
                        $result .= $msg->get_last_message();
                        $target = '';
                        $this->display('value->del test value for "' . word_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
                    }
                }
            }
        }

        // secure cleanup the test views
        // TODO: if a user has changed the view during the test, delete also the user views

        $result .= $this->test_component_unlink(view_api::TN_COMPLETE, component_api::TN_TITLE);
        $result .= $this->test_component_unlink(view_api::TN_COMPLETE, component_api::TN_VALUES);
        $result .= $this->test_component_unlink(view_api::TN_COMPLETE, component_api::TN_RESULTS);
        $result .= $this->test_component_unlink(view_api::TN_EXCLUDED, component_api::TN_EXCLUDED);
        $result .= $this->test_component_unlink(view_api::TN_TABLE, component_api::TN_TITLE);
        $result .= $this->test_component_unlink(view_api::TN_TABLE, component_api::TN_TABLE);

        // load the test view
        $dsp = $this->load_view(view_api::TN_ADD);
        if ($dsp->id() <= 0) {
            $dsp = $this->load_view(view_api::TN_RENAMED);
        }

        // load the test view for user 2
        $dsp_usr2 = $this->load_view(view_api::TN_ADD, $this->usr2);
        if ($dsp_usr2->id() <= 0) {
            $dsp_usr2 = $this->load_view(view_api::TN_RENAMED, $this->usr2);
        }

        // load the first test view component
        $cmp = $this->load_component(component_api::TN_ADD);
        if ($cmp->id() <= 0) {
            $cmp = $this->load_component(component_api::TN_RENAMED);
        }

        // load the first test view component for user 2
        $cmp_usr2 = $this->load_component(component_api::TN_ADD, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $this->load_component(component_api::TN_RENAMED, $this->usr2);
        }

        // load the second test view component
        $cmp2 = $this->load_component(component_api::TN_ADD2);

        // load the second test view component for user 2
        $cmp2_usr2 = $this->load_component(component_api::TN_ADD2, $this->usr2);

        // check if the test components have been unlinked for user 2
        if ($dsp_usr2->id() > 0 and $cmp_usr2->id() > 0) {
            $result .= $cmp_usr2->unlink($dsp_usr2);
            $target = '';
            $this->display('cleanup: unlink first component "' . $cmp_usr2->name() . '" from "' . $dsp_usr2->name() . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the test components have been unlinked
        if ($dsp->id() > 0 and $cmp->id() > 0) {
            $result .= $cmp->unlink($dsp);
            $target = '';
            $this->display('cleanup: unlink first component "' . $cmp->name() . '" from "' . $dsp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component
        // error at the moment: if the second user is still using the link,
        // the second user does not get the owner
        // instead a foreign key error happens
        if ($dsp->id() > 0 and $cmp2->id() > 0) {
            $result .= $cmp2->unlink($dsp);
            $target = '';
            $this->display('cleanup: unlink second component "' . $cmp2->name() . '" from "' . $dsp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component for user 2
        if ($dsp_usr2->id() > 0 and $cmp2_usr2->id() > 0) {
            $result .= $cmp2_usr2->unlink($dsp_usr2);
            $target = '';
            $this->display('cleanup: unlink second component "' . $cmp2_usr2->name() . '" from "' . $dsp_usr2->name() . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // request to delete the added test views
        foreach (component_api::TEST_COMPONENTS as $cmp_name) {
            $cmp = $this->load_component($cmp_name);
            if ($cmp->id() > 0) {
                $msg = $cmp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('component->del of "' . $cmp_name . '"', $target, $result);
            }
        }

        // request to delete the added test views
        foreach (view_api::TEST_VIEWS as $dsp_name) {
            $dsp = $this->load_view($dsp_name);
            if ($dsp->id() > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // reload the first test view component for user 2
        $cmp_usr2 = $this->load_component(component_api::TN_ADD, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $this->load_component(component_api::TN_RENAMED, $this->usr2);
        }

        // request to delete the test view component for user 2
        if ($cmp_usr2->id() > 0) {
            $msg = $cmp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of first component "' . component_api::TN_ADD . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the first test view component
        $cmp = $this->load_component(component_api::TN_ADD);
        if ($cmp->id() <= 0) {
            $cmp = $this->load_component(component_api::TN_RENAMED);
        }

        // request to delete the test view component
        if ($cmp->id() > 0) {
            $msg = $cmp->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of first component "' . component_api::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the second test view component
        $cmp2 = $this->load_component(component_api::TN_ADD2);

        // request to delete the second added test view component
        if ($cmp2->id() > 0) {
            $msg = $cmp2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of second component "' . component_api::TN_ADD2 . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the second added test view component for user 2
        if ($cmp2_usr2->id() > 0) {
            $msg = $cmp2_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of second component "' . component_api::TN_ADD2 . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the test view for user 2
        $dsp_usr2 = $this->load_view(view_api::TN_ADD, $this->usr2);
        if ($dsp_usr2->id() <= 0) {
            $dsp_usr2 = $this->load_view(view_api::TN_RENAMED, $this->usr2);
        }

        // request to delete the added test view for user 2 first
        if ($dsp_usr2->id() > 0) {
            $msg = $dsp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of view "' . view_api::TN_ADD . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the test view
        $dsp = $this->load_view(view_api::TN_ADD);
        if ($dsp->id() <= 0) {
            $dsp = $this->load_view(view_api::TN_RENAMED);
        }

        // request to delete the added test view
        if ($dsp->id() > 0) {
            $msg = $dsp->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('cleanup: del of view "' . view_api::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test views
        foreach (view_api::TEST_VIEWS as $dsp_name) {
            $dsp = $this->load_view($dsp_name);
            if ($dsp->id() > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // request to delete the renamed test source
        $src = $this->load_source(source_api::TN_RENAMED);
        if ($src->id() > 0) {
            $msg = $src->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('source->del of "' . source_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test sources
        foreach (source_api::TEST_SOURCES as $src_name) {
            if ($src_name != source_api::TN_READ) {
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
        $ref = $this->load_ref(word_api::TN_ADD, ref_type::WIKIDATA);
        if ($ref->id() > 0) {
            $msg = $ref->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('ref->del of "' . word_api::TN_ADD . '"', $target, $result);
        }

        // request to delete the added test formulas
        foreach (formula_api::TEST_FORMULAS as $frm_name) {
            $dsp = $this->load_formula($frm_name);
            if ($dsp->id() > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('formula->del of "' . $frm_name . '"', $target, $result);
            }
        }

        // request to delete the added test phrases
        foreach (phrase_api::TEST_TRIPLE_STANDARD as $phr_name) {
            $phr = $this->load_phrase($phr_name);
            if ($phr->id() <> 0) {
                $msg = $phr->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->display('phrase->del of "' . $phr_name . '"', $target, $result);
            }
        }

        // request to delete some triples not yet covered by the other cleanup jobs
        $this->del_triple(word_api::TN_2019, verb::IS, word_api::TN_YEAR);
        $this->del_triple(word_api::TN_2020, verb::IS, word_api::TN_YEAR);
        $this->del_triple(word_api::TN_2021, verb::IS, word_api::TN_YEAR);
        $this->del_triple(word_api::TN_2022, verb::IS, word_api::TN_YEAR);
        $this->del_triple(word_api::TN_2020, verb::FOLLOW, word_api::TN_2019);
        $this->del_triple(word_api::TN_2021, verb::FOLLOW, word_api::TN_2020);
        $this->del_triple(word_api::TN_2022, verb::FOLLOW, word_api::TN_2021);
        $this->del_triple(word_api::TN_CASH_FLOW, verb::IS, word_api::TN_FIN_REPORT);
        $this->del_triple(word_api::TN_TAX_REPORT, verb::IS_PART_OF, word_api::TN_CASH_FLOW);
        $this->del_triple(word_api::TN_CASH, verb::IS_PART_OF, word_api::TN_ASSETS_CURRENT);
        $this->del_triple(word_api::TN_ASSETS_CURRENT, verb::IS_PART_OF, word_api::TN_ASSETS);
        $this->del_triple(word_api::TN_SECTOR, verb::CAN_CONTAIN, word_api::TN_ENERGY);
        $this->del_triple(word_api::TN_ENERGY, verb::CAN_CONTAIN, word_api::TN_WIND_ENERGY);

        // request to delete the added test word
        // TODO: if a user has changed the word during the test, delete also the user words
        $wrd = $this->load_word(word_api::TN_ADD);
        if ($wrd->id() > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('word->del of "' . word_api::TN_ADD . '"', $target, $result);
        }

        // request to delete the renamed test word
        $wrd = $this->load_word(word_api::TN_RENAMED);
        if ($wrd->id() > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->display('word->del of "' . word_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test words
        foreach (word_api::TEST_WORDS as $wrd_name) {
            // ... but keep the read only test word
            if ($wrd_name != word_api::TN_READ) {
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
        $html->echo_html($db_con->seq_reset(sql_db::TBL_WORD));
        //$html->echo_html($db_con->seq_reset(sql_db::TBL_GROUP_LINK));
        //$html->echo_html($db_con->seq_reset(sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_FORMULA));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_FORMULA_LINK));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_VIEW));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_COMPONENT));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_COMPONENT_LINK));
        $html->echo_html($db_con->seq_reset(sql_db::TBL_SOURCE));

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
     * create a dummy phrase list based on the given names
     * @param array $names the names that should be used to create the phrase list
     * @return phrase_list
     */
    function phrase_list_for_tests(array $names): phrase_list
    {
        global $usr;

        $phr_lst = new phrase_list($usr);
        $pos = 1;
        foreach ($names as $name) {
            $class = match ($name) {
                triple_api::TN_PI_NAME => triple::class,
                default => word::class,
            };
            $phr = new phrase($usr, $pos, $name);

            // set types of some special terms
            if ($name == word_api::TN_2020) {
                $phr->obj()->set_type(phrase_type::TIME);
            }

            $phr_lst->add($phr);
            $pos++;
        }
        return $phr_lst;
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
                triple_api::TN_PI_NAME => triple::class,
                formula_api::TN_READ, formula_api::TN_READ_THIS, formula_api::TN_READ_PRIOR => formula::class,
                verb_api::TN_READ, verb::CAN_CONTAIN_NAME, verb::CAN_CONTAIN_NAME_REVERSE => verb::class,
                default => word::class,
            };
            $trm = new term($usr);
            $trm->set_obj_from_class($class);
            $trm->set_obj_id($pos);
            $trm->set_name($name);

            // ste types of some special terms
            if ($name == formula_api::TN_READ_THIS) {
                $trm->obj()->type_cl = formula_type::THIS;
                $trm->set_obj_id(18, $class);
                $wrd = new word($usr);
                $wrd->set(174, formula_type::THIS);
                $trm->obj()->name_wrd = $wrd;
            }
            if ($name == formula_api::TN_READ_PRIOR) {
                $trm->obj()->type_cl = formula_type::PREV;
                $trm->set_obj_id(20, $class);
                $wrd = new word($usr);
                $wrd->set(176, formula_type::PREV);
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
        $qp->sql = file_get_contents(PATH_TEST_FILES . $sql_file_name);
        $db_rows = $db_con->get($qp);
        if ($db_rows != false) {
            log_err('There are ' . count($db_rows) . ' unexpected system test rows detected by ' . $sql_file_name);
            $result = false;
        }

        return $result;
    }

    function html_test(string $body, string $filename, test_cleanup $t): void
    {
        $lib = new library();

        $created_html = $this->html_page($body);
        $expected_html = $t->file('web/html/' . $filename . '.html');
        $t->display($filename, $lib->trim_html($expected_html), $lib->trim_html($created_html));
    }

    function html_view_test(string $body, string $filename, test_cleanup $t): void
    {
        $this->html_test($body, 'views/' . $filename, $t);
    }

    private function html_page(string $body): string
    {
        $html = new html_base();
        return $html->header_test('test') . $body . $html->footer();
    }

}