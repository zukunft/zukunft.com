<?php

/*

    test/php/unit_write_workflow/formula_write_url_tests.php - persist the url based formula user workflows
    --------------------------------------------------------

    runs the same add, change and delete formula workflows as formula_url_tests,
    but with the do_it flag set to true
    so each confirmed step is actually written to the database


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

namespace Zukunft\ZukunftCom\test\php\unit_write_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UNIT_WORKFLOW . 'formula_url_tests.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_WORD . 'word.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_db_load.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit_workflow\formula_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_write_url_tests extends formula_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'formula url write->', 'url write formula ');

        // remove any test formula left over from a previous run
        $this->cleanup_test_formulas($t);

        // create the test word so that the test formula can be assigned to a word
        $t_db = new test_db_load($t);
        $t_db->test_word(word_names::TEST_ADD);

        // run the same three workflows as formula_url_tests but with do_it true
        // so each confirmed step is persisted and check if the database is actually updated
        $this->add_formula_workflow(workflows::WF_ADD_FORMULA_NBR, true);
        $this->change_formula_workflow(workflows::WF_CHANGE_FORMULA_NBR, true);
        $this->del_formula_workflow(workflows::WF_DEL_FORMULA_NBR, true);

        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $this->cleanup_test_formulas($t);

    }

    /**
     * TODO Prio 2 review
     * delete the workflow test formulas and their assigned words (including the user sandbox rows)
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_formulas(test_cleanup $t): void
    {
        $frm = new formula($t->usr1);
        foreach (formula_names::TEST_FORMULAS as $frm_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows
            $t->write_named_cleanup($frm, $frm_name);
            $t->write_named_cleanup_one($frm, $t->usr_system, $frm_name);
        }
        $wrd = new word($t->usr1);
        foreach (word_names::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}
