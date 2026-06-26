<?php

/*

    test/php/unit_workflow/triple_url_tests.php - check the url based change_triple user workflow
    -------------------------------------------

    snapshots the html of each step of the change_triple workflow; the shared run state, the
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

include_once paths::MODEL_WORD . 'triple.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'triple url->', 'url triple ');

        $this->change_triple_workflow(workflows::WF_CHANGE_TRIPLE_NBR);
    }

    /**
     * run the change_triple edit workflow and snapshot the html after every user action
     *
     * the workflow runs on the seeded 'mathematical constant' triple with do_it false, so no db row
     * is written; it snapshots into src/test/resources/web/html/workflow/change_triple_wf<nbr>/, the
     * file name built from the cumulative actions (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 5 for wf5
     */
    private function change_triple_workflow(int $wf_nbr): void
    {
        // resolve the database id of the 'mathematical constant' triple by name and set the fixed
        // snapshot id so the snapshot does not depend on the id assigned by the initial data load
        $trp = new triple($this->t->usr1);
        $this->wf_id = $trp->load_by_name(triple_names::MATH_CONST);
        $this->wf_fixed_id = triple_names::MATH_CONST_ID;
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_TRIPLE);

        // the pending change posted by the edit form on save and shown again in the confirm view
        $t_trp = new test_triples($this->t);
        $change = $t_trp->change_url_array($this->wf_id);

        // show: display the test triple in its default triple view
        $this->assert_workflow_step(url_var::ACTION_SHOW, views::TRIPLE_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SHOW;

        // edit: open the triple edit view
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::TRIPLE_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // back: leave the edit view without a change and return to the triple view
        $this->assert_workflow_step(url_var::ACTION_BACK, views::TRIPLE_ID);

        // save: press save on the edit form which shows the confirm change view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::TRIPLE_EDIT_ID, $change);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // update_confirmed: confirm the pending change so it is written to the database (do_it false
        // here, so nothing is actually written)
        $this->assert_workflow_step(url_var::ACTION_CONFIRMED, views::CONFIRM_EDIT_ID, $change);

        // cancel: cancel the change and return to the triple view
        $this->assert_workflow_step(url_var::ACTION_CANCEL, views::TRIPLE_ID);
    }

}