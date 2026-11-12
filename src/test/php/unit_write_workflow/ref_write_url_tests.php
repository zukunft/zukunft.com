<?php

/*

    test/php/unit_write_workflow/ref_write_url_tests.php - check the reference workflows incl. the db write
    ---------------------------------------------------

    runs the same steps as ref_url_tests but with do_it true, so each confirmed step is written
    to the database and the snapshots go into the parallel workflow_write folder (docs/llm/testing.md)

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

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_refs.php';
include_once test_paths::UNIT_WORKFLOW . 'ref_url_tests.php';

use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\unit_workflow\ref_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_write_url_tests extends ref_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'ref url write->', 'url write ref ');

        // remove any test reference left over from a previous run; a reference has no name, so the
        // factory cleanup removes the test references by their external keys
        $t_ref = new test_refs($t);
        $t_ref->cleanup($this->ts);

        // run the same workflows as ref_url_tests but with do_it true
        // so each confirmed step is persisted and check if the database is actually updated
        // the add must run first because the change and delete workflows load the reference it created
        $this->add_ref_workflow(workflows::WF_ADD_REF_NBR, true);
        $this->change_ref_workflow(workflows::WF_CHANGE_REF_NBR, true);
        $this->del_ref_workflow(workflows::WF_DEL_REF_NBR, true);

        // the refresh icon of a wikipedia reference is answered by the backend, which reads the live
        // wikipedia page, so this workflow runs only here and not in the read tests; the values of
        // the live pages are compared first, so that a change of a page is reported as such
        $this->wikipedia_page_tests();
        $this->cleanup_wikipedia_refresh();
        $this->wikipedia_refresh_workflow(workflows::WF_WIKIPEDIA_REFRESH_NBR);
        $this->cleanup_wikipedia_refresh();

        // the same refresh on a word without wikidata reference adds the one that wikipedia names
        $this->cleanup_wikidata_refresh();
        $this->wikidata_refresh_workflow(workflows::WF_WIKIDATA_REFRESH_NBR);
        $this->cleanup_wikidata_refresh();

        // the refresh icon of a wikidata reference imports the values of the live wikidata entity
        $this->cleanup_wikidata_import();
        $this->wikidata_import_workflow(workflows::WF_WIKIDATA_IMPORT_NBR);
        $this->cleanup_wikidata_import();

        // the confirm of a description with the change, the delete and the add of a reference writes
        // overlays of usr1 on the base data, so it runs only here and is undone before and after
        $this->cleanup_ref_rows();
        $this->ref_rows_workflow(workflows::WF_REF_ROWS_NBR);
        $this->cleanup_ref_rows();

        // the same reference row confirm cases as the snapshot test, each written and undone by itself
        $this->ref_row_confirm_workflow(workflows::WF_REF_ROW_CONFIRM_NBR, true);

        // cleanup - fallback delete in case the workflow did not persist as expected
        $t_ref->cleanup($this->ts);

    }

}
