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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UTILS . 'test_api.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_db_load;

class test_cleanup extends test_api
{
    // queries to check if removing of the test rows is complete
    const string CLEAN_CHECK_WORDS = 'db/cleanup/test_words.sql';
    const string CLEAN_CHECK_TRIPLES = 'db/cleanup/test_triples.sql';
    const string CLEAN_CHECK_FORMULAS = 'db/cleanup/test_formulas.sql';
    const string CLEAN_CHECK_SOURCES = 'db/cleanup/test_sources.sql';
    const array CLEAN_CHECKS = array(
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

        $t_db = new test_db_load($this);
        $usr_msg = new user_message($this->usr1);

        $result = ''; // the combine error message of all cleanup actions

        // start the test section (ts)
        // make sure that all test elements are removed even if some tests have failed to have a clean setup for the next test
        $ts = 'db cleanup ';
        $this->header($ts);

        if ($test_val_lst != null) {
            foreach ($test_val_lst as $val_id) {
                if ($val_id > 0) {
                    // request to delete the added test value
                    $val = new value($this->usr1);
                    $val->load_by_id($val_id);
                    // check again, because some id may be added twice
                    if ($val->is_id_set()) {
                        $val->del($usr_msg);
                        $result .= $usr_msg->get_last_message();
                        $target = '';
                        $this->assert('value->del test value for "' . words::TEST_RENAMED . '"', $result, $target, self::TIMEOUT_LIMIT_DB_MULTI);
                    }
                }
            }
        }

        // secure cleanup the test views
        // TODO: if a user has changed the view during the test, delete also the user views

        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_TITLE_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_VALUES_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_COMPLETE_NAME, components::TEST_RESULTS_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_EXCLUDED_NAME, components::TEST_EXCLUDED_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TITLE_NAME);
        $result .= $t_db->test_component_unlink(views::TEST_TABLE_NAME, components::TEST_TABLE_NAME);

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

        // load the first test view component
        $cmp = $t_db->load_component(components::TEST_ADD_NAME);
        if ($cmp->id() <= 0) {
            $cmp = $t_db->load_component(components::TEST_RENAMED_NAME);
        }

        // load the first test view component for user 2
        $cmp_usr2 = $t_db->load_component(components::TEST_ADD_NAME, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $cmp_usr2 = $t_db->load_component(components::TEST_RENAMED_NAME, $this->usr2);
        }

        // load the second test view component
        $cmp2 = $t_db->load_component(components::TEST_ADD_2_NAME);

        // load the second test view component for user 2
        $cmp2_usr2 = $t_db->load_component(components::TEST_ADD_2_NAME, $this->usr2);

        // check if the test components have been unlinked for user 2
        if ($msk_usr2->id() > 0 and $cmp_usr2->id() > 0) {
            $test_name = 'cleanup: unlink first component "' . $cmp_usr2->name() . '" from "' . $msk_usr2->name() . '" for user 2';
            $this->assert_true($test_name, $cmp_usr2->unlink($msk_usr2, $usr_msg), self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the test components have been unlinked
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $test_name = 'cleanup: unlink first component "' . $cmp->name() . '" from "' . $msk->name() . '"';
            $this->assert_true($test_name, $cmp->unlink($msk, $usr_msg), self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component
        // error at the moment: if the second user is still using the link,
        // the second user does not get the owner
        // instead a foreign key error happens
        if ($msk->id() > 0 and $cmp2->id() > 0) {
            $test_name = 'cleanup: unlink second component "' . $cmp2->name() . '" from "' . $msk->name() . '"';
            $this->assert_true($test_name, $cmp2->unlink($msk, $usr_msg), self::TIMEOUT_LIMIT_DB_MULTI);
        }

        // unlink the second component for user 2
        if ($msk_usr2->id() > 0 and $cmp2_usr2->id() > 0) {
            $test_name = 'cleanup: unlink second component "' . $cmp2_usr2->name() . '" from "' . $msk_usr2->name() . '" for user 2';
            $this->assert_true($test_name, $cmp2_usr2->unlink($msk_usr2, $usr_msg), self::TIMEOUT_LIMIT_DB_MULTI);
        }

        foreach (components::TEST_COMPONENTS as $cmp_name) {
            $cmp = $t_db->load_component($cmp_name);
            if ($cmp->id() > 0) {
                $test_name = 'request to delete the added test views of "' . $cmp_name . '"';
                $this->assert_true($test_name, $cmp->del($usr_msg), self::TIMEOUT_LIMIT_DB_MULTI);
            }
        }

        // request to delete the added test views
        foreach (views::TEST_VIEWS as $dsp_name) {
            $msk = $t_db->load_view($dsp_name);
            if ($msk->id() > 0) {
                $test_name = '';
                $msk->del($usr_msg);
                $result .= $usr_msg->get_last_message();
                $target = '';
                $this->assert('view->del of "' . $dsp_name . '"', $result, $target);
            }
        }

        $test_name = 'reload the first test view component "' . components::TEST_ADD_NAME . '" for user 2';
        $cmp_usr2 = $t_db->load_component(components::TEST_ADD_NAME, $this->usr2);
        if ($cmp_usr2->id() <= 0) {
            $test_name .= ' or "' . components::TEST_RENAMED_NAME . '"';
            $cmp_usr2 = $t_db->load_component(components::TEST_RENAMED_NAME, $this->usr2);
        }

        $test_name .= ' and request to delete the test view component  for user 2';
        if ($cmp_usr2->id() > 0) {
            $this->assert_true($test_name, $cmp_usr2->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'reload the first test view component "' . components::TEST_ADD_NAME . '"';
        $cmp = $t_db->load_component(components::TEST_ADD_NAME);
        if ($cmp->id() <= 0) {
            $test_name .= ' or "' . components::TEST_RENAMED_NAME . '"';
            $cmp = $t_db->load_component(components::TEST_RENAMED_NAME);
        }

        $test_name .= ' and request to delete the test view component';
        if ($cmp->id() > 0) {
            $this->assert_true($test_name, $cmp->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'request to delete the second added test view component "' . components::TEST_ADD_2_NAME . '"';
        $cmp2 = $t_db->load_component(components::TEST_ADD_2_NAME);
        if ($cmp2->id() > 0) {
            $this->assert_true($test_name, $cmp2->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'request to delete the second added test view component "' . components::TEST_ADD_2_NAME . '" for user 2';
        $cmp2_usr2 = $t_db->load_component(components::TEST_ADD_2_NAME, $this->usr2);
        if ($cmp2_usr2->id() > 0) {
            $this->assert_true($test_name, $cmp2_usr2->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'reload the test view "' . views::TEST_ADD_NAME . '"  for user 2';
        $msk_usr2 = $t_db->load_view(views::TEST_ADD_NAME, $this->usr2);
        if ($msk_usr2->id() <= 0) {
            $test_name .= ' or "' . views::TEST_RENAMED_NAME . '"';
            $msk_usr2 = $t_db->load_view(views::TEST_RENAMED_NAME, $this->usr2);
        }

        $test_name .= ' and request to delete the added test view for user 2 first';
        if ($msk_usr2->id() > 0) {
            $this->assert_true($test_name, $msk_usr2->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'reload the test view "' . views::TEST_ADD_NAME . '"';
        $msk = $t_db->load_view(views::TEST_ADD_NAME);
        if ($msk->id() <= 0) {
            $test_name .= ' or "' . views::TEST_RENAMED_NAME . '"';
            $msk = $t_db->load_view(views::TEST_RENAMED_NAME);
        }

        $test_name .= ' and request to delete it';
        if ($msk->id() > 0) {
            $this->assert_true($test_name, $msk->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name_loop = 'request to delete the added test views';
        foreach (views::TEST_VIEWS as $msk_name) {
            $test_name = $test_name_loop . ' "' . $msk_name . '"';
            $msk = $t_db->load_view($msk_name);
            if ($msk->id() > 0) {
                $this->assert_true($test_name, $msk->del($usr_msg), self::TIMEOUT_LIMIT_DB);
            }
        }

        $test_name = 'request to delete the renamed test source "' . sources::SYSTEM_TEST_RENAMED . '"';
        $src = $t_db->load_source(sources::SYSTEM_TEST_RENAMED);
        if ($src->id() > 0) {
            $this->assert_true($test_name, $src->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name_loop = 'request to delete the added test sources';
        foreach (sources::TEST_SOURCES as $src_name) {
            $test_name = $test_name_loop . ' "' . $src_name . '"';
            if ($src_name != sources::WIKIDATA) {
                $src = $t_db->load_source($src_name);
                if ($src->id() > 0) {
                    $this->assert_true($test_name, $src->del($usr_msg), self::TIMEOUT_LIMIT_DB);
                }
            }
        }

        $test_name = 'request to delete the added test reference "' . words::TEST_ADD . '" to "' . ref_types::WIKIDATA . '"';
        $ref = $t_db->load_ref(words::TEST_ADD, ref_types::WIKIDATA);
        if ($ref->id() > 0) {
            $this->assert_true($test_name, $ref->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name_loop = 'request to delete the added test formulas';
        foreach (formulas::TEST_FORMULAS as $frm_name) {
            $test_name = $test_name_loop . ' "' . $frm_name . '"';
            $frm = $t_db->load_formula($frm_name);
            if ($frm->id() > 0) {
                $this->assert_true($test_name, $frm->del($usr_msg), self::TIMEOUT_LIMIT_DB);
            }
        }

        $test_name_loop = 'request to delete the added test phrases';
        foreach (triples::TEST_TRIPLES as $phr_name) {
            $test_name = $test_name_loop . ' "' . $phr_name . '"';
            $phr = $t_db->load_phrase($phr_name);
            if ($phr->id() <> 0) {
                $this->assert_true($test_name, $phr->del($usr_msg), self::TIMEOUT_LIMIT_DB);
            }
        }

        // request to delete some triples not yet covered by the other cleanup jobs
        $t_db->del_triple(words::YEAR_2019, verbs::IS, words::YEAR_CAP);
        $t_db->del_triple(words::YEAR_2020, verbs::IS, words::YEAR_CAP);
        $t_db->del_triple(words::TEST_2021, verbs::IS, words::YEAR_CAP);
        $t_db->del_triple(words::TEST_2022, verbs::IS, words::YEAR_CAP);
        $t_db->del_triple(words::YEAR_2020, verbs::FOLLOW, words::YEAR_2019);
        $t_db->del_triple(words::TEST_2021, verbs::FOLLOW, words::YEAR_2020);
        $t_db->del_triple(words::TEST_2022, verbs::FOLLOW, words::TEST_2021);
        $t_db->del_triple(words::TEST_CASH_FLOW, verbs::IS, words::TEST_FIN_REPORT);
        $t_db->del_triple(words::TEST_TAX_REPORT, verbs::PART_NAME, words::TEST_CASH_FLOW);
        $t_db->del_triple(words::TEST_CASH, verbs::PART_NAME, words::TEST_ASSETS_CURRENT);
        $t_db->del_triple(words::TEST_ASSETS_CURRENT, verbs::PART_NAME, words::TEST_ASSETS);
        $t_db->del_triple(words::TEST_SECTOR, verbs::CAN_CONTAIN, words::TEST_ENERGY);
        $t_db->del_triple(words::TEST_ENERGY, verbs::CAN_CONTAIN, words::TEST_WIND_ENERGY);

        // request to delete the added test word
        // TODO: if a user has changed the word during the test, delete also the user words
        $test_name = 'request to delete the added test word "' . words::TEST_ADD . '"';
        $wrd = $t_db->load_word(words::TEST_ADD);
        if ($wrd->id() > 0) {
            $this->assert_true($test_name, $wrd->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name = 'request to delete the renamed test word  of "' . words::TEST_RENAMED . '"';
        $wrd = $t_db->load_word(words::TEST_RENAMED);
        if ($wrd->id() > 0) {
            $this->assert_true($test_name, $wrd->del($usr_msg), self::TIMEOUT_LIMIT_DB);
        }

        $test_name_loop = 'request to delete the added test words';
        foreach (words::TEST_WORDS as $wrd_name) {
            $test_name = $test_name_loop . ' "' . $wrd_name . '"';
            if ($wrd_name != words::MATH) {
                $wrd = $t_db->load_word($wrd_name);
                if ($wrd->id() > 0) {
                    $this->assert_true($test_name, $wrd->del($usr_msg), self::TIMEOUT_LIMIT_DB);
                }
            } else {
                log_info(' ... but keep the read only test word ' . words::MATH);
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

    function html_page_test(string $body, string $title, string $filename): void
    {
        $this->html_test($body, $title, test_paths::VIEW_FUNCTIONS . $filename);
    }

    function html_view_test(string $body, string $filename): void
    {
        $this->html_test($body, 'view', test_paths::VIEWS . $filename);
    }

    /**
     * check if a generated html page matches the fixed html page saved in the resource path
     * @param string $body the generated html page body
     * @param string $title the page title name
     * @param string $file_path the file path starting from the resource path for the html resources
     * @return void
     */
    private function html_test(string $body, string $title, string $file_path): void
    {
        $lib = new library();

        if ($title == '') {
            $title = 'test';
        } else {
            $title = 'test ' . $title;
        }
        $created_html = $this->html_page($body, $title);
        $expected_html = $this->file(test_paths::HTML . $file_path . test_files::HTML);
        $this->assert($file_path, $lib->trim_html($created_html), $lib->trim_html($expected_html));
    }

    private function html_page(string $body, string $title): string
    {
        $html = new html_base();
        return $html->header_test($title) . $body . $html->footer();
    }

}