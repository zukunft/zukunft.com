<?php

/*

    test/php/unit_write_workflow/source_write_url_tests.php - check the url based source workflows incl. db write
    -------------------------------------------------------

    runs the same workflows as source_url_tests but with do_it true, so each confirmed step is
    written to the database and the snapshots go into the parallel workflow_write folder; the
    change workflow runs after the add, because it changes the source the add has written
    (see docs/llm/testing.md)

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

include_once paths::MODEL_REF . 'source.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'source_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\unit_workflow\source_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_write_url_tests extends source_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'source url write->', 'url write source ');

        // remove any test source left over from a previous run
        $this->cleanup_test_sources($t);

        // run the same workflows as source_url_tests but with do_it true
        // so each confirmed step is persisted and check if the database is actually updated
        // the add must run first because the change and delete workflows load the source it created
        $this->add_source_workflow(workflows::WF_ADD_SOURCE_NBR, true);
        $this->change_source_workflow(workflows::WF_CHANGE_SOURCE_NBR, true);
        $this->del_source_workflow(workflows::WF_DEL_SOURCE_NBR, true);

        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case the workflow did not persist as expected
        $this->cleanup_test_sources($t);

    }

    /**
     * delete the workflow test sources including the user sandbox rows
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_sources(test_cleanup $t): void
    {
        $src = new source($t->usr1);
        foreach (sources::TEST_SOURCES as $src_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows
            $t->write_named_cleanup($src, $src_name);
            $t->write_named_cleanup_one($src, $t->usr_system, $src_name);
        }
    }

}
