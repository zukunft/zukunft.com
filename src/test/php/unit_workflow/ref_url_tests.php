<?php

/*

    test/php/unit_workflow/ref_url_tests.php - check the url based reference user workflows
    ----------------------------------------

    snapshots the html of each step of the add_ref workflow; the shared run state, the frontend
    setup and the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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

include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_refs.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'ref url->', 'url ref ');

        // this test has no pure read (url_to_html) and no routing-only (url_to_action) tests yet;
        // when one is added, use the url_to_html_tests / url_to_action_tests split of word_url_tests
        $this->workflow_tests($t);
    }

    /**
     * the combined workflow snapshot tests: every step chains url_to_action (routing, with
     * do_it false so nothing is written) and url_to_html (render) like a real user request
     *
     * @param test_cleanup $t the test environment
     */
    private function workflow_tests(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write tests the same workflows are used with do_it = true
        $this->add_ref_workflow(workflows::WF_ADD_REF_NBR);
    }

    /**
     * run the add_ref workflow and snapshot the html after every user action, mirroring
     * add_source_workflow: the back excursion aborts the add without writing, then the reference
     * is entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_ref_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 28 for wf28
     * @param bool $do_it false to only render the steps, true to also write the new reference
     */
    protected function add_ref_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_ref workflow creates a new reference, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_REF, $this->t->usr1, refs::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty reference
        $url_arr = test_refs::ref_new_url($this->msg);

        $this->wf_id = 0;
        $this->wf_fixed_id = refs::SYSTEM_TEST_ADD_ID;

        // the new reference fields posted by the add form on save and shown again in the confirm add view
        $t_ref = new test_refs($this->t);
        $add = $t_ref->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add reference form
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new reference
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_ADD_ID);

        // user is entering the new reference: the phrase, the external key, the type, the url and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::REF_ADD_ID);

        // confirmed: confirm the new reference so it is actually added (with do_it true); the confirm
        // form posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::REF_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the reference, so check it is now in the database
        if ($do_it) {
            $this->assert_ref_in_db('add_ref workflow has written the reference',
                refs::SYSTEM_TEST_ADD, $this->t->usr1, refs::SYSTEM_TEST_ADD_COM);
        }
    }

    /**
     * check that the workflow test reference exists in the database with the expected description,
     * used by the add write workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_in_db); a reference has no name, so it is selected by its
     * external key
     *
     * @param string $test_name the description of the assertion
     * @param string $key the expected external key of the test reference in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test reference in the database
     */
    private function assert_ref_in_db(string $test_name, string $key, user $usr, string $description): void
    {
        $msg = new user_message();
        $ref = new ref($usr);
        $ref->load_by_ex_key($key, $msg);
        $this->t->assert($test_name, $ref->get_external_key(), $key);
        $this->t->assert($test_name, $ref->description, $description);
    }

}
