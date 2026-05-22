<?php

/*

    test/php/unit/term_list.php - unit tests related to a term list
    ---------------------------


    zukunft.com - calc with words

    copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\trm_ids;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list as term_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class term_list_tests
{

    /**
     * execute all term list unit tests and add the test result to the given testing object
     * @param test_cleanup $t the testing object to cumulate the testing results
     */
    function run(test_cleanup $t): void
    {
        global $usr;

        // init
        $sc = new sql_creator();
        $t_trm = new test_terms($t);
        $t->name = 'term_list->';
        $t->resource_path = 'db/term/';

        // start the test section (ts)
        $ts = 'unit term list ';
        $t->header($ts);

        $t->subheader($ts . 'term list display');



        $t->subheader($ts . 'sql statement creation');

        // load only the names
        $phr_lst = new term_list($usr);
        $t->assert_sql_names($sc, $phr_lst, new term($usr));
        $t->assert_sql_names($sc, $phr_lst, new term($usr), verbs::IS_NAME);

        $test_name = 'load terms by ids';
        $trm_lst = new term_list($usr);
        $trm_ids = new trm_ids(array(3, -2, 4, -7));
        $t->assert_sql_by_ids($test_name, $sc, $trm_lst, $trm_ids);
        $lst = $t_trm->term_list();
        $t->assert_sql_like($sc, $lst);

        /*
         * TODO Prio 2 activate
        $test_name = 'like speed test';
        $lst = $t_trm->list_huge($t, 1000);
        $t->assert_sql_like($sc, $lst);
        */

        $t->subheader($ts . 'api');

        $trm_lst = $t_trm->term_list_short();
        $t->assert_api($trm_lst);


        $t->subheader($ts . 'html frontend');

        $trm_lst = $t_trm->term_list_short();
        $t->assert_api_to_ui($trm_lst, new term_list_ui());


        $t->subheader($ts . 'sort by impact');

        // build a frontend term list with two words of different impact
        $wrd_low = new word_ui();
        $wrd_low->set_id(1);
        $wrd_low->set_name('low impact term');
        $wrd_low->impact = 1.0;
        $wrd_high = new word_ui();
        $wrd_high->set_id(2);
        $wrd_high->set_name('high impact term');
        $wrd_high->impact = 9.0;
        $trm_lst = new term_list_ui();
        $trm_lst->add($wrd_low->term());
        $trm_lst->add($wrd_high->term());

        // positive: term->impact returns the impact of the wrapped word
        $test_name = 'term->impact returns the impact of the wrapped word';
        $t->assert($test_name, $wrd_high->term()->impact() == 9.0, true);

        // positive: sort_by_impact orders the list with the highest impact term first
        $test_name = 'term_list->sort_by_impact shows the highest impact term first';
        $trm_lst->sort_by_impact();
        $t->assert($test_name, $trm_lst->names(), ['high impact term', 'low impact term']);

        // positive: name_link_by_impact lists the highest impact term before the lower one
        $test_name = 'term_list->name_link_by_impact lists the highest impact term first';
        $links = $trm_lst->name_link_by_impact();
        $t->assert($test_name, strpos($links, 'high impact term') < strpos($links, 'low impact term'), true);

        // negative: an empty term list renders no impact links
        $test_name = 'term_list->name_link_by_impact of an empty list is empty';
        $empty_lst = new term_list_ui();
        $t->assert($test_name, $empty_lst->name_link_by_impact(), '');


        $t->subheader($ts . 'links in three columns');

        // build a frontend term list with five words of descending impact
        $col_lst = new term_list_ui();
        for ($i = 1; $i <= 5; $i++) {
            $wrd = new word_ui();
            $wrd->set_id($i);
            $wrd->set_name('term ' . $i);
            $wrd->impact = 6.0 - $i;
            $col_lst->add($wrd->term());
        }
        $cols_html = $col_lst->links_with_context();

        // positive: the result is spread over exactly three balanced columns (col-md-4)
        $test_name = 'term_list->links_with_context shows three columns';
        $t->assert($test_name, substr_count($cols_html, 'col-md-4') == 3, true);

        // positive: the terms keep the impact order across the columns (highest impact term first)
        $test_name = 'term_list->links_with_context lists the highest impact term first';
        $t->assert($test_name, strpos($cols_html, 'term 1') < strpos($cols_html, 'term 5'), true);

        // positive: each of the five entries has a "fas fa-edit" link to its word edit page (m=3)
        $test_name = 'term_list->links_with_context adds a fas fa-edit link per entry';
        $t->assert($test_name, substr_count($cols_html, 'fas fa-edit') == 5, true);

        // positive: the edit link points to the word edit view (views::WORD_EDIT_ID = 3) of the term
        $test_name = 'term_list->links_with_context edit link points to the term edit page';
        $t->assert($test_name, str_contains($cols_html, 'm=3&id=1'), true);

        // negative: an empty term list renders no columns
        $test_name = 'term_list->links_with_context of an empty list is empty';
        $t->assert($test_name, (new term_list_ui())->links_with_context(), '');

    }

    /**
     * create a term list test object without using a database connection
     * that matches the all members of word with id 1 (math const)
     */
    function get_term_list_related(test_cleanup $t): term_list
    {
        global $usr;
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $trm_lst = new term_list($usr);
        $trm_lst->add($t_trp->triple_pi()->term());
        $trm_lst->add($t_wrd->word()->term());
        return $trm_lst;
    }

    /**
     * create the standard filled term object
     */
    private function get_term(int $id, string $name, int $type): term
    {
        global $usr;
        $result = null;
        if ($type == 1) {
            $wrd = new word($usr);
            $wrd->set($id, $name);
            $result = $wrd->term();
        } elseif ($type == 2)  {
            $trp = new triple($usr);
            $trp->set($id, $name);
            $result = $trp->term();
        } elseif ($type == 3)  {
            $frm = new formula($usr);
            $frm->set($id, $name);
            $result = $frm->term();
        } elseif ($type == 4)  {
            $vrb = new verb();
            $vrb->set($id, $name);
            $result = $vrb->term();
        }
        return $result;
    }

}