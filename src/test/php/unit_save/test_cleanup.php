<?php

/*

    test_cleanup.php - add TESTing CLEANUP functions to remove any remaining test records to the test_base class
    ---------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class testing extends test_base
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
                    $val->id = $val_id;
                    $val->load();
                    // check again, because some id may be added twice
                    if ($val->id > 0) {
                        $msg = $val->del();
                        $result .= $msg->get_last_message();
                        $target = '';
                        $this->dsp('value->del test value for "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
                    }
                }
            }
        }

        // secure cleanup the test views
        // TODO: if a user has changed the view during the test, delete also the user views

        $result .= $this->test_view_cmp_unlink(view::TN_COMPLETE, view_cmp::TN_TITLE);
        $result .= $this->test_view_cmp_unlink(view::TN_COMPLETE, view_cmp::TN_VALUES);
        $result .= $this->test_view_cmp_unlink(view::TN_COMPLETE, view_cmp::TN_RESULTS);
        $result .= $this->test_view_cmp_unlink(view::TN_TABLE, view_cmp::TN_TITLE);
        $result .= $this->test_view_cmp_unlink(view::TN_TABLE, view_cmp::TN_TABLE);

        // load the test view
        $dsp = $this->load_view(view::TN_ADD);
        if ($dsp->id <= 0) {
            $dsp = $this->load_view(view::TN_RENAMED);
        }

        // load the test view for user 2
        $dsp_usr2 = $this->load_view(view::TN_ADD, $this->usr2);
        if ($dsp_usr2->id <= 0) {
            $dsp_usr2 = $this->load_view(view::TN_RENAMED, $this->usr2);
        }

        // load the first test view component
        $cmp = $this->load_view_component(view_cmp::TN_ADD);
        if ($cmp->id <= 0) {
            $cmp = $this->load_view_component(view_cmp::TN_RENAMED);
        }

        // load the first test view component for user 2
        $cmp_usr2 = $this->load_view_component(view_cmp::TN_ADD, $this->usr2);
        if ($cmp_usr2->id <= 0) {
            $cmp_usr2 = $this->load_view_component(view_cmp::TN_RENAMED, $this->usr2);
        }

        // load the second test view component
        $cmp2 = $this->load_view_component(view_cmp::TN_ADD2);

        // load the second test view component for user 2
        $cmp2_usr2 = $this->load_view_component(view_cmp::TN_ADD2, $this->usr2);

        // check if the test components have been unlinked for user 2
        if ($dsp_usr2->id > 0 and $cmp_usr2->id > 0) {
            $result .= $cmp_usr2->unlink($dsp_usr2);
            $target = '';
            $this->dsp('cleanup: unlink first component "' . $cmp_usr2->name . '" from "' . $dsp_usr2->name . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the test components have been unlinked
        if ($dsp->id > 0 and $cmp->id > 0) {
            $result .= $cmp->unlink($dsp);
            $target = '';
            $this->dsp('cleanup: unlink first component "' . $cmp->name . '" from "' . $dsp->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component
        // error at the moment: if the second user is still using the link,
        // the second user does not get the owner
        // instead a foreign key error happens
        if ($dsp->id > 0 and $cmp2->id > 0) {
            $result .= $cmp2->unlink($dsp);
            $target = '';
            $this->dsp('cleanup: unlink second component "' . $cmp2->name . '" from "' . $dsp->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component for user 2
        if ($dsp_usr2->id > 0 and $cmp2_usr2->id > 0) {
            $result .= $cmp2_usr2->unlink($dsp_usr2);
            $target = '';
            $this->dsp('cleanup: unlink second component "' . $cmp2_usr2->name . '" from "' . $dsp_usr2->name . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // request to delete the added test views
        foreach (view_cmp::RESERVED_VIEW_COMPONENTS as $cmp_name) {
            $cmp = $this->load_view_component($cmp_name);
            if ($cmp->id > 0) {
                $msg = $cmp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->dsp('view_component->del of "' . $cmp_name . '"', $target, $result);
            }
        }

        // request to delete the added test views
        foreach (view::RESERVED_VIEWS as $dsp_name) {
            $dsp = $this->load_view($dsp_name);
            if ($dsp->id > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->dsp('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // reload the first test view component for user 2
        $cmp_usr2 = $this->load_view_component(view_cmp::TN_ADD, $this->usr2);
        if ($cmp_usr2->id <= 0) {
            $cmp_usr2 = $this->load_view_component(view_cmp::TN_RENAMED, $this->usr2);
        }

        // request to delete the test view component for user 2
        if ($cmp_usr2->id > 0) {
            $msg = $cmp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of first component "' . view_cmp::TN_ADD . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the first test view component
        $cmp = $this->load_view_component(view_cmp::TN_ADD);
        if ($cmp->id <= 0) {
            $cmp = $this->load_view_component(view_cmp::TN_RENAMED);
        }

        // request to delete the test view component
        if ($cmp->id > 0) {
            $msg = $cmp->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of first component "' . view_cmp::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the second test view component
        $cmp2 = $this->load_view_component(view_cmp::TN_ADD2);

        // request to delete the second added test view component
        if ($cmp2->id > 0) {
            $msg = $cmp2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of second component "' . view_cmp::TN_ADD2 . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the second added test view component for user 2
        if ($cmp2_usr2->id > 0) {
            $msg = $cmp2_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of second component "' . view_cmp::TN_ADD2 . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the test view for user 2
        $dsp_usr2 = $this->load_view(view::TN_ADD, $this->usr2);
        if ($dsp_usr2->id <= 0) {
            $dsp_usr2 = $this->load_view(view::TN_RENAMED, $this->usr2);
        }

        // request to delete the added test view for user 2 first
        if ($dsp_usr2->id > 0) {
            $msg = $dsp_usr2->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of view "' . view::TN_ADD . '" for user 2', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // reload the test view
        $dsp = $this->load_view(view::TN_ADD);
        if ($dsp->id <= 0) {
            $dsp = $this->load_view(view::TN_RENAMED);
        }

        // request to delete the added test view
        if ($dsp->id > 0) {
            $msg = $dsp->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('cleanup: del of view "' . view::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test views
        foreach (view::RESERVED_VIEWS as $dsp_name) {
            $dsp = $this->load_view($dsp_name);
            if ($dsp->id > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->dsp('view->del of "' . $dsp_name . '"', $target, $result);
            }
        }

        // request to delete the renamed test source
        $src = $this->load_source(source::TN_RENAMED);
        if ($src->id > 0) {
            $msg = $src->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('source->del of "' . source::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test sources
        foreach (source::RESERVED_SOURCES as $src_name) {
            if ($src_name != source::TN_READ) {
                $src = $this->load_source($src_name);
                if ($src->id > 0) {
                    $msg = $src->del();
                    $result .= $msg->get_last_message();
                    $target = '';
                    $this->dsp('source->del of "' . $src_name . '"', $target, $result);
                }
            }
        }

        // request to delete the added test reference
        $ref = $this->load_ref(word::TN_ADD, ref_type::WIKIDATA);
        if ($ref->id > 0) {
            $msg = $ref->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('ref->del of "' . word::TN_ADD . '"', $target, $result);
        }

        // request to delete the added test formulas
        foreach (formula::RESERVED_FORMULAS as $frm_name) {
            $dsp = $this->load_formula($frm_name);
            if ($dsp->id > 0) {
                $msg = $dsp->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->dsp('formula->del of "' . $frm_name . '"', $target, $result);
            }
        }

        // request to delete the added test phrases
        foreach (phrase::RESERVED_PHRASES as $phr_name) {
            $phr = $this->load_phrase($phr_name);
            if ($phr->id <> 0) {
                $msg = $phr->del();
                $result .= $msg->get_last_message();
                $target = '';
                $this->dsp('phrase->del of "' . $phr_name . '"', $target, $result);
            }
        }

        // request to delete some triples not yet covered by the other cleanup jobs
        $this->del_word_link(word::TN_2019, verb::IS_A, word::TN_YEAR);
        $this->del_word_link(word::TN_2020, verb::IS_A, word::TN_YEAR);
        $this->del_word_link(word::TN_2021, verb::IS_A, word::TN_YEAR);
        $this->del_word_link(word::TN_2022, verb::IS_A, word::TN_YEAR);
        $this->del_word_link(word::TN_2020, verb::DBL_FOLLOW, word::TN_2019);
        $this->del_word_link(word::TN_2021, verb::DBL_FOLLOW, word::TN_2020);
        $this->del_word_link(word::TN_2022, verb::DBL_FOLLOW, word::TN_2021);
        $this->del_word_link(word::TN_CASH_FLOW, verb::IS_A, word::TN_FIN_REPORT);
        $this->del_word_link(word::TN_TAX_REPORT, verb::IS_PART_OF, word::TN_CASH_FLOW);
        $this->del_word_link(word::TN_CASH, verb::IS_PART_OF, word::TN_ASSETS_CURRENT);
        $this->del_word_link(word::TN_ASSETS_CURRENT, verb::IS_PART_OF, word::TN_ASSETS);

        // request to delete the added test word
        // TODO: if a user has changed the word during the test, delete also the user words
        $wrd = $this->load_word(word::TN_ADD);
        if ($wrd->id > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('word->del of "' . word::TN_ADD . '"', $target, $result);
        }

        // request to delete the renamed test word
        $wrd = $this->load_word(word::TN_RENAMED);
        if ($wrd->id > 0) {
            $msg = $wrd->del();
            $result .= $msg->get_last_message();
            $target = '';
            $this->dsp('word->del of "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB);
        }

        // request to delete the added test words
        foreach (word::RESERVED_WORDS as $wrd_name) {
            // ... but keep the read only test word
            if ($wrd_name != word::TN_READ) {
                $wrd = $this->load_word($wrd_name);
                if ($wrd->id > 0) {
                    $msg = $wrd->del();
                    $result .= $msg->get_last_message();
                    $target = '';
                    $this->dsp('word->del of "' . $wrd_name . '"', $target, $result);
                }
            }
        }

        // TODO better use a info system log message
        echo $db_con->seq_reset(DB_TYPE_VALUE) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_WORD) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_PHRASE_GROUP_WORD_LINK) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_PHRASE_GROUP_TRIPLE_LINK) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_FORMULA) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_FORMULA_LINK) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_VIEW) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_VIEW_COMPONENT) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_VIEW_COMPONENT_LINK) . '<br>';
        echo $db_con->seq_reset(DB_TYPE_SOURCE) . '<br>';

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
        $sql = file_get_contents(PATH_TEST_IMPORT_FILES . $sql_file_name);
        $db_rows = $db_con->get_old($sql);
        if ($db_rows != false) {
            log_err('There are ' . count($db_rows) . ' unexpected system test rows detected by ' . $sql_file_name);
            $result = false;
        }

        return $result;
    }

}