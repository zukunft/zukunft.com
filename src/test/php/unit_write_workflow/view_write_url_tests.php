<?php

/*

    test/php/unit_write_workflow/view_write_url_tests.php - check the view workflows incl. the db write
    ----------------------------------------------------

    runs the same steps as view_url_tests but with do_it true, so each confirmed step is written to
    the database and the snapshots go into the parallel workflow_write folder (docs/llm/testing.md)

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

include_once paths::MODEL_VIEW . 'view.php';
include_once paths::SHARED_CONST . 'views.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'view_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\unit_workflow\view_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_write_url_tests extends view_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'view url write->', 'url write view ');

        // remove any test view left over from a previous run
        $this->cleanup_test_views($t);

        // run the same workflow as view_url_tests but with do_it true
        // so the confirmed step is persisted and check if the database is actually updated
        $this->add_view_workflow(workflows::WF_ADD_VIEW_NBR, true);

        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case the workflow did not persist as expected
        $this->cleanup_test_views($t);

    }

    /**
     * delete the workflow test views including the user sandbox rows
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_views(test_cleanup $t): void
    {
        $msk = new view($t->usr1);
        // only the view added by this workflow, because the other test views of views::TEST_VIEWS
        // are created and removed by the view write tests
        $t->write_named_cleanup($msk, views::TEST_ADD_NAME);
        $t->write_named_cleanup_one($msk, $t->usr_system, views::TEST_ADD_NAME);
    }

}
