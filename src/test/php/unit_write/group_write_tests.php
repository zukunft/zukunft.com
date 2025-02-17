<?php

/*

    test/php/unit_write/phrase_group_tests.php - write test PHRASE GROUPS to the database and check the results
    ------------------------------------------
  

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


namespace unit_write;

include_once SHARED_CONST_PATH . 'triples.php';

use cfg\db\sql_type;
use cfg\group\group;
use cfg\phrase\phrase_list;
use cfg\word\word;
use cfg\word\word_list;
use shared\const\groups;
use shared\const\triples;
use shared\const\words;
use test\all_tests;
use test\test_cleanup;

class group_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $grp_add_lst = [
            [groups::TN_ADD_PRIME_FUNC, true, words::TEST_ADD_GROUP_PRIME_FUNC, sql_type::PRIME, 'function', words::TEST_RENAMED_GROUP_PRIME_FUNC],
            [groups::TN_ADD_PRIME_SQL, false, words::TEST_ADD_GROUP_PRIME_SQL, sql_type::PRIME, 'insert', words::TEST_RENAMED_GROUP_PRIME_SQL],
            [groups::TN_ADD_MOST_FUNC, true, words::TEST_ADD_GROUP_MOST_FUNC, sql_type::MOST, 'function', words::TEST_RENAMED_GROUP_MOST_FUNC],
            [groups::TN_ADD_MOST_SQL, false, words::TEST_ADD_GROUP_MOST_SQL, sql_type::MOST, 'insert', words::TEST_RENAMED_GROUP_MOST_SQL],
            [groups::TN_ADD_BIG_FUNC, true, words::TEST_ADD_GROUP_BIG_FUNC, sql_type::BIG, 'function', words::TEST_RENAMED_GROUP_BIG_FUNC],
            [groups::TN_ADD_BIG_SQL, false, words::TEST_ADD_GROUP_BIG_SQL, sql_type::BIG, 'insert', words::TEST_RENAMED_GROUP_BIG_SQL],
        ];


        $t->header('group db write tests');

        $t->subheader('group add the system test words to avoid dependencies on group testing');
        $wrd_add_lst = [];
        foreach ($grp_add_lst as $grp_add) {
            $wrd_add_lst[] = $t->test_word($grp_add[2]);
        }

        $t->subheader('group add');
        $i = 0;
        foreach ($grp_add_lst as $grp_add) {
            $grp_name = $grp_add[0];
            $test_name = 'add prime group name ' . $grp_name . ' via sql ' . $grp_add[4];
            if ($grp_add[3] == sql_type::PRIME) {
                $phr_lst = $t->phrase_list_small();
            } elseif ($grp_add[3] == sql_type::MOST) {
                $phr_lst = $t->phrase_list();
            } else {
                $phr_lst = $t->phrase_list_17_plus();
            }
            $this->group_add($wrd_add_lst[$i], $grp_name, $grp_add[1], $phr_lst, $test_name, $t);
            $i++;
        }

        $t->subheader('group rename');
        $i = 0;
        foreach ($grp_add_lst as $grp_add) {
            $test_case = rand(1, 2);
            $grp_name = $grp_add[0];
            $new_name = $grp_add[5];
            $test_name = 'rename prime group name from ' . $grp_name . ' to ' . $new_name . ' via sql ' . $grp_add[4];
            //$this->group_rename($grp_name, $new_name, $grp_add[1], $test_case, $test_name, $t);
        }

        $t->subheader('group del');
        foreach ($grp_add_lst as $grp_add) {
            $grp_name = $grp_add[0];
            $test_name = 'del prime group name ' . $grp_name . ' via sql ' . $grp_add[4];
            $this->group_del($grp_name, $grp_add[1], $test_name, $t);
        }


        // test if the time word is correctly excluded
        // TODO move to phrase list tests
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $phr_grp = new group($usr);
        $phr_grp->load_by_phr_lst($wrd_lst->phrase_lst());
        $result = $phr_grp->id();
        //if ($result > 0 and $result != $id_without_year) {
        // actually the group id with time word is supposed to be the same as the phrase group id without time word because the time word is not included in the phrase group
        if (is_numeric($result)) {
            if ($result > 0) {
                $target = $result;
            }
        } else {
            if ($result != '') {
                $target = $result;
            }
        }
        $t->display('phrase_group->load by ids excluding time for ' . implode(",", $wrd_lst->names()), $target, $result);

        // load based on id
        if ($phr_grp->is_id_set()) {
            $phr_grp_reload = new group($usr);
            $phr_grp_reload->load_by_id($phr_grp->id());
            $wrd_lst_reloaded = $phr_grp_reload->phrase_list()->words();
            $result = array_diff(
                array(words::MIO, words::ZH, words::CANTON, words::INHABITANTS, words::CH),
                $wrd_lst_reloaded->names()
            );
        }
        $target = array(4 => words::CH);
        $t->display('phrase_group->load for id ' . $phr_grp->id(), $target, $result);

        // test getting the phrase group id based on word and word link ids
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(triples::CITY_ZH, words::INHABITANTS));
        $zh_city_grp = $phr_lst->get_grp_id();
        $result = $zh_city_grp->get_id();
        if ($result > 0) {
            $target = $result;
        }
        $t->display('phrase_group->load by ids for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test names
        $result = implode(",", $zh_city_grp->names());
        $target = words::INHABITANTS . ',' . triples::CITY_ZH;
        $t->display('phrase_group->names', $target, $result);

        // test if the phrase group links are correctly recreated when a group is updated
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS));
        $grp = $phr_lst->get_grp_id();
        $grp_check = new group($usr);
        $grp_check->set_id($grp->id());
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phrase_list()->id_lst();
        $t->display('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // second test if the phrase group links are correctly recreated when a group is updated
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $grp = $phr_lst->get_grp_id();
        $grp_check = new group($usr);
        $grp_check->set_id($grp->id());
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phrase_list()->id_lst();
        $t->display('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test value
        // test value_scaled


        // load based on wrd and lnk lst
        // load based on wrd and lnk ids
        // maybe if cleanup removes the unneeded group

        // test the user sandbox for the user names
        // test if the search links are correctly created

    }

    /**
     * create some fixed group names that are used for db read unit testing
     * these words are not expected to be changed and cannot be changed by the normal users
     *
     * @param all_tests $t the test object with the test settings
     * @return void
     */
    function create_test_groups(all_tests $t): void
    {
        $t->header('group check test group names');

        foreach (groups::TEST_GROUPS_CREATE as $group) {
            $grp_name = $group[0];
            $phr_names = $group[1];
            $t->test_group($phr_names, $grp_name, $t->usr1);
        }
    }

    /**
     * test adding a group name that differs from the generated name
     *
     * @param word $wrd the word object that makes the phrase list unique
     * @param string $grp_name the name of the group
     * @param bool $use_func true if the sql function with log should be used
     * @param phrase_list $phr_lst the prase list either for a prime main or big group id
     * @param string $test_name the unique description of the test for the developer
     * @param test_cleanup $t the test object with the test settings
     * @return void
     */
    function group_add(
        word         $wrd,
        string       $grp_name,
        bool         $use_func,
        phrase_list  $phr_lst,
        string       $test_name,
        test_cleanup $t
    ): void
    {
        $grp = new group($t->usr1);
        $grp->load_by_name($grp_name);
        if (!$grp->is_saved()) {
            $phr_lst->add($wrd->phrase());
            $grp->set_phrase_list($phr_lst);
            $grp->set_name($grp_name);
            $grp->save($use_func);
            $grp->reset();
            $grp->load_by_name($grp_name);
            $t->assert_true($test_name, $grp->isset());
        }
    }

    /**
     * test renaming a group name and switch back to the generated name
     *
     * @param string $old_name used to select the group to rename
     * @param string $new_name the target name of the group
     * @param bool $use_func true if the sql function with log should be used
     * @param int $test_case indicator to select the user
     * @param string $test_name the unique description of the test for the developer
     * @param test_cleanup $t the test object with the test settings
     * @return void
     */
    function group_rename(
        string       $old_name,
        string       $new_name,
        bool         $use_func,
        int          $test_case,
        string       $test_name,
        test_cleanup $t
    ): void
    {
        $grp = new group($t->usr1);
        $grp->load_by_name($old_name);
        if ($grp->is_saved()) {
            $id = $grp->id();
            if ($test_case == 2) {
                $grp->set_user($t->usr2);
            } else {
                $grp->set_user($t->usr1);
            }
            $grp->set_name($new_name);
            $grp->save($use_func);
            $grp->reset();
            $grp->load_by_id($id);
            $t->assert($test_name, $grp->name(), $new_name);
        }
    }

    /**
     * test deleting a group name and switch back to the generated name
     *
     * @param string $grp_name the name of the group
     * @param bool $use_func true if the sql function with log should be used
     * @param string $test_name the unique description of the test for the developer
     * @param test_cleanup $t the test object with the test settings
     * @return void
     */
    function group_del(
        string       $grp_name,
        bool         $use_func,
        string       $test_name,
        test_cleanup $t
    ): void
    {
        $grp = new group($t->usr1);
        $grp->load_by_name($grp_name);
        if ($grp->is_saved()) {
            $id = $grp->id();
            $grp->del($use_func);
            $grp->reset();
            $grp->load_by_id($id);
            $t->assert($test_name, $grp->name(), $grp->name_generated());
        }
    }

}