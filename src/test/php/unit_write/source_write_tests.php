<?php

/*

    test/php/unit_write/source_tests.php - write test SOURCES to the database and check the results
    ------------------------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com


*/

namespace unit_write;

include_once SHARED_CONST_PATH . 'sources.php';

use cfg\ref\source;
use shared\const\sources;
use test\test_cleanup;

class source_write_tests
{

    function run(test_cleanup $t): void
    {

        $t->header('source db write tests');

        $t->subheader('source prepared write');
        $test_name = 'add source ' . sources::SYSTEM_TEST_ADD_VIA_SQL . ' via sql insert';
        $t->assert_write_via_func_or_sql($test_name, $t->source_add_by_sql(), false);
        $test_name = 'add source ' . sources::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t->source_add_by_func(), true);

        $t->subheader('source write sandbox tests for ' . sources::SYSTEM_TEST_ADD);
        $t->assert_write_named($t->source_filled_add(), sources::SYSTEM_TEST_ADD);

        /*
        TODO remove but check upfront the replacement


        // check if undo all specific changes removes the user source
        $src_usr2 = new source($t->usr2);
        $src_usr2->load_by_name(sources::TN_RENAMED, source::class);
        $src_usr2->set_url(sources::TU_ADD);
        $src_usr2->description = sources::TD_ADD;
        $result = $src_usr2->save()->get_last_message();
        $target = '';
        $t->display('source->save undo the user source fields beside the name for "' . sources::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific source changes have been saved
        $src_usr2_reloaded = new source($t->usr2);
        $src_usr2_reloaded->load_by_name(sources::TN_RENAMED, source::class);
        $result = $src_usr2_reloaded->url();
        $target = sources::TU_ADD;
        $t->display('source->load url for "' . sources::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
        $result = $src_usr2_reloaded->description;
        $target = sources::TD_ADD;
        $t->display('source->load description for "' . sources::TN_RENAMED . '" unchanged now also for user 2', $target, $result);

        // clean up by deleting all add test sources
        $src_usr2_reloaded->del();
        $src_renamed->del();

        */

        // TODO create and check the display functions
        // TODO test the import of a source with a non system with the code id and check if the warning message is created and the update is rejected
        // TODO test the import of a source with the code id does not create a warning if the code_id already matches
        // TODO test if the import does not change the code_id if a normal user imports a source

        // cleanup - fallback delete
        $src = new source($t->usr1);
        foreach (sources::TEST_SOURCES as $src_name) {
            $t->write_named_cleanup($src, $src_name);
        }

    }

    function create_test_sources(test_cleanup $t): void
    {

        $t->header('Check if all base sources are exist');

        $t->test_source(sources::WIKIDATA);

    }

}