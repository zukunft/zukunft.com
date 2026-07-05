<?php

/*

    test/php/unit_workflow/formula_url_tests.php - check the url based add_formula user workflow
    --------------------------------------------

    snapshots the html of each step of the add_formula workflow; the shared run state, the
    frontend setup and the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_FORMULA . 'formula.php';
include_once test_paths::CREATE . 'test_formulas.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'formula url->', 'url formula ');

        $this->add_formula_workflow(workflows::WF_ADD_FORMULA_NBR);
    }

    /**
     * run the add_formula edit workflow and snapshot the html after every user action, mirroring
     * add_triple_workflow: the back and cancel excursions abort the add without writing, then the add is
     * redone and the final confirm would add the formula (do_it false here, so nothing is written).
     * snapshots go into src/test/resources/web/html/workflow/add_formula_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 14 for wf14
     * @param bool $do_it false to only render the steps, true to also write the new formula
     */
    protected function add_formula_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_formula workflow creates a new formula, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_FORMULA, formula_names::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty formula
        $url_arr = test_formulas::formula_new_url();

        $this->wf_id = 0;
        $this->wf_fixed_id = formula_names::SYSTEM_TEST_ADD_ID;

        // the new formula fields posted by the add form on save and shown again in the confirm add view
        $t_frm = new test_formulas($this->t);
        $add = $t_frm->add_url_array();

        // edit: open the empty add formula form
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new formula
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // user is entering the new formula: the name, the expression and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_ADD_ID);

        // cancel: discard the new formula in the confirm view and return to the start view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::START_ID);

        // edit: re-open the add form to redo the new formula
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // user is entering the new formula again
        $url_arr = $add + $url_arr;

        // save: press save again which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_ADD_ID);

        // confirmed: confirm the new formula so it is actually added (with do_it true); the confirm form
        // posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::FORMULA_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the formula, so check it is now in the database; the reserved
        // 'System Test Formula' is added as the system base, so read it as the system user (a stale usr1
        // sandbox overlay from a previous run must not mask the freshly written base value)
        if ($do_it) {
            $this->assert_formula_in_db('add_formula workflow has written the formula',
                formula_names::SYSTEM_TEST_ADD, $this->t->usr_system, formula_names::SYSTEM_TEST_ADD_COM);
        }
    }

    /**
     * check that the workflow test formula exists in the database with the expected description, used by
     * the add write workflow to verify the confirmed step was actually persisted (mirrors
     * triple_url_tests::assert_triple_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test formula in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test formula in the database
     */
    private function assert_formula_in_db(string $test_name, string $name, user $usr, string $description): void
    {
        $frm = new formula($usr);
        $frm->load_by_name($name);
        $this->t->assert($test_name, $frm->name(), $name);
        $this->t->assert($test_name, $frm->description, $description);
    }

}