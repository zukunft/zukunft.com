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

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED . 'json_fields.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'source_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
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
        $chg_id = $t->last_user_change_id($t->usr1);
        $this->add_source_workflow(workflows::WF_ADD_SOURCE_NBR, true);
        $src_id = $this->last_used_after_add($t, $chg_id);
        // the change sets the source again, so the default of the add is removed before
        $t->cleanup_last_used();
        $this->change_source_workflow(workflows::WF_CHANGE_SOURCE_NBR, true);
        $test_name = 'the changed source is the last used source of the user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert($test_name, $usr_db->source_id(), $src_id);
        $this->del_source_workflow(workflows::WF_DEL_SOURCE_NBR, true);
        $test_name = 'the deleted source is no longer the last used source of the user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert_null($test_name, $usr_db->source_id());

        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case the workflow did not persist as expected
        $this->cleanup_test_sources($t);
        $t->cleanup_last_used();

    }

    /**
     * the confirmed add of a source makes it the last used source of the requesting user only and
     * without a change log row, and an import of another source leaves this default unchanged,
     * because the import saves each source but is no write of a user request (see user::save_last_used)
     *
     * @param test_cleanup $t the test environment
     * @param int $chg_id the newest change log row of the user row of usr1 before the add
     * @return int the database id of the added test source
     */
    private function last_used_after_add(test_cleanup $t, int $chg_id): int
    {
        $msg = new user_message($t->usr1);
        $t->subheader($this->ts . 'last used source');
        $src = new source($t->usr1);
        $src_id = $src->load_by_name(sources::SYSTEM_TEST_ADD, $msg);
        $test_name = 'the added source is the last used source of the user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert($test_name, $usr_db->source_id(), $src_id);
        $test_name = '... without a change log row of the user';
        $usr_chg_id = $t->last_user_change_id($t->usr1);
        $t->assert($test_name, $usr_chg_id, $chg_id);
        $test_name = '... and not of another user';
        $usr2_db = $t->user_from_db($t->usr2);
        $t->assert_null($test_name, $usr2_db->source_id());

        // negative: an import of another source keeps the default of the importing user
        $test_name = 'an import of a source writes the source';
        $json = [json_fields::SOURCES => [[
            json_fields::NAME => sources::SYSTEM_TEST_ADD_API,
            json_fields::URL => sources::SYSTEM_TEST_ADD_API_URL
        ]]];
        $imp = new import();
        $imp->put_json_direct(json_encode($json), $msg);
        $src_imp = new source($t->usr1);
        $imp_id = $src_imp->load_by_name(sources::SYSTEM_TEST_ADD_API, $msg);
        $t->assert_greater_zero($test_name, $imp_id);
        $test_name = '... but keeps the last used source of the importing user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert($test_name, $usr_db->source_id(), $src_id);
        $t->assert_msg($test_name, $msg);
        return $src_id;
    }

    /**
     * delete the workflow test sources including the user sandbox rows
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_sources(test_cleanup $t): void
    {
        $src = new source($t->usr1);
        // remove the change log entries of the test sources before the rows themselves, because a
        // change log entry must never point to a deleted test row (see docs/llm/testing.md) and
        // because the change log is shown on the source page: without this the entries of a
        // previous run stay in the database and the workflow page snapshots change with every run
        $t->cleanup_change_log($src, sources::TEST_SOURCES);
        foreach (sources::TEST_SOURCES as $src_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows
            $t->write_named_cleanup($src, $src_name);
            $t->write_named_cleanup_one($src, $t->usr_system, $src_name);
        }
    }

}
