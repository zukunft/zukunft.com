<?php

/*

    test/php/unit_read/element_group_read_tests.php - read the number of a formula element group from the database
    -----------------------------------------------

    uses the stored rows of the split walk of portfolio_split_calculator.json,
    whose formulas read the row before with the operand "cumulative deviation","prior"


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_ELEMENT . 'element_group.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_db_load.php';

use Zukunft\ZukunftCom\main\php\cfg\element\element_group;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class element_group_read_tests
{

    function run(test_cleanup $t): void
    {
        // init
        $t_db = new test_db_load($t);
        $lib = new library();
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db read element group ';
        $t->header($ts);

        // the groups of the walk formula "cumulative deviation" = "cumulative deviation","prior"
        // + "split count" * "unit value" - "target share" * "holding value"
        $frm = $t_db->load_formula(formula_names::WALK_DEVIATION);
        $trm_lst = $frm->load_exp_terms($msg);
        $grp_lst = $frm->expression($msg, $trm_lst)->element_grp_lst($msg, $trm_lst)->lst();

        $t->subheader($ts . 'operand of several phrases');

        $test_name = 'the operand "cumulative deviation","prior" of the walk formula is one element group';
        $prior_names = [triple_names::CUMULATIVE_DEVIATION, formula_names::PRIOR];
        $prior_grp = $this->group($grp_lst, $lib->dsp_array($prior_names));
        $t->assert_true($test_name, !is_null($prior_grp));
        if (!is_null($prior_grp)) {
            $test_name = '... that reads the previous row';
            $t->assert_true($test_name, $prior_grp->reads_prior_row());
            $test_name = '... and whose symbol is part of the formula text, so that its number replaces it';
            $t->assert_text_contains($test_name, (string)$frm->ref_text, $prior_grp->build_symbol());

            $t->subheader($ts . 'previous row');

            // LISN follows LISP, whose deviation of the first child is 58500 in portfolio_split_calculator.json
            $test_name = 'a row in the middle of the walk reads the cumulative deviation of the row before';
            $lisn = [word_names::LISN, triple_names::FIRST_CHILD_PORTFOLIO, words::CHF];
            $prior_grp->phr_lst = $t_db->load_phrase_list($lisn);
            $t->assert($test_name, $this->one_number($prior_grp, $msg), '58500');
            $msg->reset();

            $test_name = 'the first row of the walk reads 0, because no deviation is carried in';
            $lisp = [word_names::LISP, triple_names::FIRST_CHILD_PORTFOLIO, words::CHF];
            $prior_grp->phr_lst = $t_db->load_phrase_list($lisp);
            $t->assert($test_name, $this->one_number($prior_grp, $msg), '0');
            $msg->reset();

            // negative: LISN is a row of the holdings and stock of the kinds of holding, so the previous row is not clear
            $test_name = 'a context with two follower chains reads no number';
            $two_chains = [word_names::LISN, word_names::STOCK, triple_names::FIRST_CHILD_PORTFOLIO, words::CHF];
            $prior_grp->phr_lst = $t_db->load_phrase_list($two_chains);
            $t->assert_true($test_name, $prior_grp->figures($msg)->fig_missing);
            $msg->reset();
        }

        $t->subheader($ts . 'operand of a row');

        // the count of LISP of the first child is 4 in portfolio_split_calculator.json
        $test_name = 'the operand "split count" selects the count of the row';
        $count_grp = $this->group($grp_lst, triple_names::SPLIT_COUNT);
        $t->assert_true($test_name, !is_null($count_grp));
        if (!is_null($count_grp)) {
            $test_name = '... which is the stored count of LISP of the first child';
            $count_ctx = [word_names::LISP, triple_names::FIRST_CHILD_PORTFOLIO];
            $count_grp->phr_lst = $t_db->load_phrase_list($count_ctx);
            $t->assert($test_name, $this->one_number($count_grp, $msg), '4');
            $msg->reset();
        }

        // the unit value of LISP is 117000 CHF in portfolio_split_calculator.json
        $test_name = 'the operand "unit value" selects the unit value of the row';
        $unit_grp = $this->group($grp_lst, triple_names::UNIT_VALUE);
        $t->assert_true($test_name, !is_null($unit_grp));
        if (!is_null($unit_grp)) {
            $test_name = '... which is the stored unit value of LISP';
            $unit_grp->phr_lst = $t_db->load_phrase_list([word_names::LISP, words::CHF]);
            $t->assert($test_name, $this->one_number($unit_grp, $msg), '117000');
            $msg->reset();
        }
    }

    /**
     * @param array $grp_lst the element groups of a formula
     * @param string $name the name of the wanted group e.g. "cumulative deviation,prior"
     * @return element_group|null the group with the name or null if the formula has none
     */
    private function group(array $grp_lst, string $name): ?element_group
    {
        $result = null;
        foreach ($grp_lst as $grp) {
            if ($grp->name() == $name) {
                $result = $grp;
            }
        }
        return $result;
    }

    /**
     * @param element_group $grp the element group with the context of its number
     * @param user_message $msg to report a problem of the load
     * @return string the one number that the group selects, '' if it selects none or several
     */
    private function one_number(element_group $grp, user_message $msg): string
    {
        $fig_lst = $grp->figures($msg);
        $result = '';
        if (count($fig_lst->lst()) == 1) {
            $result = (string)$fig_lst->lst()[0]->number();
        }
        return $result;
    }

}
