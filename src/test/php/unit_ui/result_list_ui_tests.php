<?php

/*

    test/unit/html/result_list.php - testing of the html frontend functions for result lists
    -------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'api_types.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class result_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();
        $t_res = new test_results($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html result list ';
        $t->header($ts);

        // test the result list display functions
        $lst = new result_list($t_res->result_list()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]));
        $test_page = $html->text_h2('result list display test');
        $test_page .= 'result list with tooltip: ' . $lst->display($msg) . '<br>';
        $test_page .= 'result list with link: ' . $lst->display_linked() . '<br>';
        $t->html_page_test($test_page, 'result_list', 'result_list', $msg);

        // the results of a phrase are listed like its values: grouped, limited by the configured
        // page size and without a table border (see value_list::list_most_relevant)
        $t->subheader($ts . 'most relevant');
        $t_trp = new test_triples($t);
        $mr_html = $t_res->result_list_solution_prio_first_row_ui()->list_most_relevant($msg);
        $test_name = 'the results are grouped like the values of a phrase';
        $t->assert_text_contains($test_name, $mr_html, styles::VALUE_LIST);
        $test_name = '... without a table';
        $t->assert_text_not_contains($test_name, $mr_html, '<' . html_base::TABLE);
        $test_name = '... each result linked to its page';
        $t->assert_text_contains($test_name, $mr_html, url_var::MASK . '=' . views::RESULT_ID);
        $test_name = '... and named by the problem of the row';
        $t->assert_text_contains($test_name, $mr_html, $t_trp->global_warming()->name());
        // negative: a phrase without results shows nothing
        $test_name = 'most relevant of an empty result list renders nothing';
        $t->assert($test_name, new result_list()->list_most_relevant($msg), '');

        $t->subheader($ts . 'api mapper');
        $test_name = 'the api mapper adds every result of the api json';
        $lst = new result_list();
        $res_lst_json = $t_res->result_list()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $lst->api_mapper(json_decode($res_lst_json, true));
        $t->assert($test_name, $lst->count(), $t_res->result_list()->count());
        $test_name = 'the api mapper reports a result without id';
        $lst = new result_list();
        $api_json = [[json_fields::NUMBER => results::TV_INT]];
        $t->assert_false($test_name, $lst->api_mapper($api_json)->is_ok());
        // the answer with each phrase once gives the same results as the answer with the phrases in each result
        $old_json = json_decode($t_res->result_list()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]), true);
        $once_json = json_decode($t_res->result_list()->api_json(
            [api_types::TEST_MODE, api_types::INCL_PHRASES, api_types::PHRASES_ONCE]), true);
        $old_lst = new result_list();
        $old_lst->api_mapper($old_json);
        $once_lst = new result_list();
        $once_ok = $once_lst->api_mapper($once_json)->is_ok();
        $old_names = $this->result_names($old_lst);
        $once_names = $this->result_names($once_lst);
        $test_name = 'the result answer with each phrase once gives the same results and phrases';
        $t->assert($test_name, $once_names, $old_names);
        $test_name = '... without a message';
        $t->assert_true($test_name, $once_ok);
        // negative: the answer is smaller, but not empty
        $test_name = '... and names at least one phrase';
        $once_phr_count = count($once_json[json_fields::PHRASES] ?? []);
        $t->assert_greater($test_name, 0, $once_phr_count);
        // each phrase of the answer is mapped once, so that an answer with many results fits in the memory of the pod;
        // the loss and the gain of the first ranking row share the phrases of their problem
        $row_lst = $t_res->result_list_solution_prio_first_row();
        $test_name = 'the results of the answer with each phrase once share the object of a phrase';
        $once_shared = $this->shares_phrase_objects(new result_list($row_lst->api_json(
            [api_types::TEST_MODE, api_types::INCL_PHRASES, api_types::PHRASES_ONCE])));
        $t->assert_true($test_name, $once_shared);
        // negative: the answer with the phrases in each result maps them per result
        $test_name = '... but not the results of the answer with the phrases in each result';
        $old_shared = $this->shares_phrase_objects(new result_list($row_lst->api_json(
            [api_types::TEST_MODE, api_types::INCL_PHRASES])));
        $t->assert_false($test_name, $old_shared);
    }

    /**
     * @param result_list $lst the mapped results
     * @return bool true if two results of the list hold the same object for a phrase that both have
     */
    private function shares_phrase_objects(result_list $lst): bool
    {
        $result = false;
        $obj_by_phr_id = [];
        foreach ($lst->lst() as $res) {
            foreach ($res->grp->lst() as $phr) {
                $obj_id = spl_object_id($phr);
                if (($obj_by_phr_id[$phr->id()] ?? null) === $obj_id) {
                    $result = true;
                }
                $obj_by_phr_id[$phr->id()] = $obj_id;
            }
        }
        return $result;
    }

    /**
     * @param result_list $lst the mapped results
     * @return array per result the number and the names of its phrases, to compare two mappings
     */
    private function result_names(result_list $lst): array
    {
        $result = [];
        foreach ($lst->lst() as $res) {
            $names = $res->phr_lst()->names();
            sort($names);
            $result[] = $res->number() . ': ' . implode(', ', $names);
        }
        return $result;
    }

}