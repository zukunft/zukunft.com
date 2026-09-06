<?php

/*

    test/php/unit_workflow/source_url_tests.php - check the url based source user workflows
    -------------------------------------------

    snapshots the html of each step of the add_source workflow; the shared run state, the frontend
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

include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_sources.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'source url->', 'url source ');

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
        // for the write tests the same workflow is used with do_it = true
        $this->add_source_workflow(workflows::WF_ADD_SOURCE_NBR);
    }

    /**
     * run the add_source workflow and snapshot the html after every user action, mirroring
     * add_word_workflow: the back excursion aborts the add without writing, then the source is
     * entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_source_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 19 for wf19
     * @param bool $do_it false to only render the steps, true to also write the new source
     */
    protected function add_source_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_source workflow creates a new source, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_SOURCE, $this->t->usr1, sources::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty source
        $url_arr = test_sources::source_new_url($this->msg);

        $this->wf_id = 0;
        $this->wf_fixed_id = sources::SYSTEM_TEST_ADD_ID;

        // the new source fields posted by the add form on save and shown again in the confirm add view
        $t_src = new test_sources($this->t);
        $add = $t_src->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add source form
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new source
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_ADD_ID);

        // user is entering the new source: the name, the type, the url, the doi and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::SOURCE_ADD_ID);

        // confirmed: confirm the new source so it is actually added (with do_it true); the confirm form
        // posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::SOURCE_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the source, so check it is now in the database
        if ($do_it) {
            $this->assert_source_in_db('add_source workflow has written the source',
                sources::SYSTEM_TEST_ADD, $this->t->usr1, sources::SYSTEM_TEST_ADD_URL);
        }
    }

    /**
     * check that the workflow test source exists in the database with the expected url, used by the
     * add write workflow to verify the confirmed step was actually persisted (mirrors
     * formula_url_tests::assert_formula_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test source in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $url the expected url of the test source in the database
     */
    private function assert_source_in_db(string $test_name, string $name, user $usr, string $url): void
    {
        $msg = new user_message();
        $src = new source($usr);
        $src->load_by_name($name, $msg);
        $this->t->assert($test_name, $src->name(), $name);
        $this->t->assert($test_name, $src->url, $url);
    }

}
