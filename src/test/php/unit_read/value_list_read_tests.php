<?php

/*

    test/php/unit_read/value_list.php - database unit testing of the value list functions
    ---------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::SERVICE . 'config.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_value_list.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class value_list_read_tests
{

    // a part of the text of msg_id::LIST_ROW_LIMIT_REACHED that does not depend on its vars
    const string ROW_LIMIT_TEXT = 'has reached the row limit';
    // the page size of the sorted result list test, smaller than the number of results of the test phrase
    const int RESULT_PAGE_SIZE = 5;

    function run(test_cleanup $t): void
    {
        $msg = new user_message();

        // init
        $t_phr = new test_phrases($t);
        $t->name = 'value list_read db->';
        $t->resource_path = 'db/value/';

        // start the test section (ts)
        $ts = 'db read value list ';
        $t->header($ts);

        $t->subheader($ts . 'related');

        // load by phrase
        // the pi number value is keyed by the "Pi (math)" triple (see the pi value in units.json)
        $test_name = 'Load a value list by phrase pi';
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_phr($t_phr->phrase_pi_math(), $msg);
        $result = $val_lst->dsp_id();
        $target = '3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -' . triple_names::PI_ID . ',,,) for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // load by ids
        // TODO Prio 0 fix the test so that it loads both values
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_ids([values::PI_MATH_ID,values::E_ID], $msg);
        $pi = new value($t->usr1);
        $pi->load_by_id(values::PI_MATH_ID, $msg);
        $e = new value($t->usr1);
        $e->load_by_id(values::E_ID, $msg);
        $target_lst = new value_list($t->usr1);
        $target_lst->add($pi);
        if ($e->id != 0) {
            $target_lst->add($e);
        }
        $test_name = 'Loading pi and e via value list is the same as single loading';
        $target = $target_lst->dsp_id();
        $result = $val_lst->dsp_id();
        // TODO Prio 2 check why order may changes
        if ($target != $result) {
            $target = '2.718281828459 / 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
                . triple_names::E_NUM_ID . ',,, / -' . triple_names::PI_ID . ',,,) for user 3 (zukunft.com system test)';
        }
        if ($target != $result) {
            $target = '3.1415926535898 / 2.718281828459 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
                . triple_names::PI_ID . ',,, / -' . triple_names::E_NUM_ID . ',,,) for user 3 (zukunft.com system test)';
        }
        $t->assert($test_name, $result, $target);
        $target = '3.1415926535898 / 2.718281828459 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
            . triple_names::PI_ID . ',,, / -' . triple_names::E_NUM_ID . ',,,) for user 3 (zukunft.com system test)';
        if ($target != $result) {
            $target = '2.718281828459 / 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
                . triple_names::E_NUM_ID . ',,, / -' . triple_names::PI_ID . ',,,) for user 3 (zukunft.com system test)';
        }
        $test_name = 'A value list with pi and e matches the expected result';
        $t->assert($test_name, $val_lst->dsp_id(), $target);

        // load values related to all phrases of a list
        $test_name = 'Load the the inhabitants of canton Zurich over time';
        $val_lst = new value_list($t->usr1);
        $phr_lst = $t_phr->ch_inhabitant_phrase_list();
        $val_lst->load_by_phr_lst($phr_lst, $msg);
        $result = $val_lst->dsp_id();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->assert_text_contains($test_name, $result, $target);
        $test_name = '... with all years, not only the latest';
        $t->assert_text_contains($test_name, $result, values::CH_INHABITANTS_2019_IN_MIO);

        // load values related to any phrase of a list
        // use the phrases that carry the values in the seeded database
        $test_name = 'Load the list of math const';
        $val_lst = new value_list($t->usr1);
        $phr_lst = $t_phr->phrase_list_math_const_db();
        $val_lst->load_by_phr_lst($phr_lst, $msg, true);
        $result = $val_lst->dsp_id();
        $target = '2.718281828459 / 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
            . triple_names::E_NUM_ID . ',,, / -' . triple_names::PI_ID . ',,,) for user 3 (zukunft.com system test)';
        if ($target != $result) {
            $target = '3.1415926535898 / 2.718281828459 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -'
                . triple_names::PI_ID . ',,, / -' . triple_names::E_NUM_ID . ',,,) for user 3 (zukunft.com system test)';
        }
        $t->assert($test_name, $result, $target);
        // the phrases of all values are loaded with one query for the whole list
        $test_name = '... and the phrase load of the list names the phrases of each value';
        $val_lst->load_phrases($msg);
        $named = [];
        foreach ($val_lst->lst() as $val) {
            $phr_lst = $val->grp()->phrase_list();
            $named[] = (!$phr_lst->is_empty() and $phr_lst->loaded());
        }
        $t->assert($test_name, $named, [true, true]);
        // a list that fills the row limit may miss rows, so the user is told
        $test_name = 'a list load that reaches the row limit is reported to the user';
        $lim_msg = new user_message();
        $lim_lst = new value_list($t->usr1);
        $lim_lst->load_by_phr_lst($t_phr->phrase_list_math_const_db(), $lim_msg, true, 2);
        $t->assert_text_contains($test_name, $lim_msg->text(), self::ROW_LIMIT_TEXT);
        // negative: a list below the limit is complete and not reported
        $test_name = '... but not a list below the row limit';
        $all_msg = new user_message();
        $all_lst = new value_list($t->usr1);
        $all_lst->load_by_phr_lst($t_phr->phrase_list_math_const_db(), $all_msg, true);
        $t->assert_text_not_contains($test_name, $all_msg->text(), self::ROW_LIMIT_TEXT);

        // the query is sorted by the key of the rows, so the pages of a list neither repeat nor miss a row
        $test_name = 'the pages of a sorted list load give each value of the list once';
        // a page of one row reaches the row limit, so its warning gets a message of its own
        $page_msg = new user_message();
        $page_ids = $this->page_ids(new value_list($t->usr1), $t_phr->phrase_list_math_const_db(), true, 1, 2, $page_msg);
        $all_ids = $this->sorted_ids($all_lst);
        $sorted_page_ids = $page_ids;
        sort($sorted_page_ids);
        $t->assert($test_name, $sorted_page_ids, $all_ids);
        // negative: an unsorted query may return the same row on both pages
        $test_name = '... and not the same value on two pages';
        $unique_count = count(array_unique($page_ids));
        $t->assert($test_name, $unique_count, count($all_ids));
        $test_name = '... and the same value on the first page in every load';
        $first_ids = $this->page_ids(new value_list($t->usr1), $t_phr->phrase_list_math_const_db(), true, 1, 1, $page_msg);
        $first_page_id = $page_ids[0] ?? '';
        $t->assert($test_name, $first_ids, [$first_page_id]);

        // the results are sorted by the phrase ids and the formula too, so their pages are complete as well
        $test_name = 'the result list of a sorted load has more results than one page';
        $res_phr_lst = new phrase_list($t->usr1);
        $res_phr_lst->add_name(triple_names::ONE_CLICK_HIDE_POPULISM, $msg);
        $res_all_lst = new result_list($t->usr1);
        $res_all_lst->load_by_phrase_list($res_phr_lst, $msg);
        $res_all_ids = $this->sorted_ids($res_all_lst);
        $res_count = count($res_all_ids);
        $t->assert_greater($test_name, self::RESULT_PAGE_SIZE, $res_count);
        $test_name = '... and its pages give each result of the list once';
        $res_pages = (int)ceil($res_count / self::RESULT_PAGE_SIZE);
        $res_page_ids = $this->page_ids(new result_list($t->usr1), $res_phr_lst, false, self::RESULT_PAGE_SIZE, $res_pages, $page_msg);
        $sorted_res_page_ids = $res_page_ids;
        sort($sorted_res_page_ids);
        $t->assert($test_name, $sorted_res_page_ids, $res_all_ids);
        // negative: an unsorted query may repeat a result on two pages and miss another
        $test_name = '... without a result on two pages';
        $res_unique_count = count(array_unique($res_page_ids));
        $t->assert($test_name, $res_unique_count, $res_count);

        // load by phrase list
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triples::SYSTEM_CONFIG, $msg);
        $phr_lst = $phr->all_children($msg);
        $val_lst = new value_list($t->usr1);
        // TODO Prio 2 activate
        // TODO Prio 2 add the word "System configuration" to the list of index word for each pod
        //      and for fast value selection and always the word in the group
        //      so that a selection of the complete system configuration can be done with one phrase
        //$val_lst->load_by_phr_lst($phr_lst);
        $test_name = 'System configuration values contain also the default number of years';
        $target = 10;
        //$t->assert_contains($test_name, $val_lst->numbers(), [$target]);

        // ... based on the phrase list
        // check only that the pi number is found, because beside pi the result can contain
        // e.g. a system configuration value that hitchhikes on the word "mathematics" and
        // whose group ids change with every database setup
        $test_name = 'the values of the math phrases contain pi';
        $phr_lst = $t_phr->phrase_list_prime_db();
        $val_lst = $phr_lst->val_lst($msg);
        $result = $val_lst->dsp_id();
        $t->assert_text_contains($test_name, $result, (string)values::PI);

    }

    /**
     * @param sandbox_value_list $lst an empty value or result list of the user whose rows are loaded
     * @param phrase_list $phr_lst the phrases of the list load
     * @param bool $or true to load the rows of any phrase, false the rows of all phrases
     * @param int $size the number of rows per page
     * @param int $pages the number of pages loaded from the first page on
     * @param user_message $msg to collect the warning of each page that fills the row limit
     * @return array the ids of the rows of all pages in the order of the pages
     */
    private function page_ids(
        sandbox_value_list $lst,
        phrase_list        $phr_lst,
        bool               $or,
        int                $size,
        int                $pages,
        user_message       $msg
    ): array
    {
        $class = value::class;
        if ($lst instanceof result_list) {
            $class = result::class;
        }
        $result = [];
        for ($page = 0; $page < $pages; $page++) {
            $page_lst = clone $lst;
            $page_lst->load_by_phr_lst_multi($phr_lst, $msg, $class, $or, $size, $page);
            $result = [...$result, ...$this->ids($page_lst)];
        }
        return $result;
    }

    /**
     * @param sandbox_value_list $lst a loaded value or result list
     * @return array the ids of the rows sorted, so that two lists with the same rows give the same array
     */
    private function sorted_ids(sandbox_value_list $lst): array
    {
        $result = $this->ids($lst);
        sort($result);
        return $result;
    }

    /**
     * @param sandbox_value_list $lst a loaded value or result list
     * @return array the ids of the rows as text in the order of the list
     */
    private function ids(sandbox_value_list $lst): array
    {
        $result = [];
        foreach ($lst->lst() as $val) {
            $result[] = (string)$val->id();
        }
        return $result;
    }

}

