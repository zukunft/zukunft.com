<?php

/*

    test/php/unit_write_workflow/triple_write_url_tests.php - persist the url based triple user workflows
    ------------------------------------------------------

    runs the same add, change and delete triple workflows as triple_url_tests, but with the do_it flag
    set to true so each confirmed step is actually written to the database; the steps snapshot into
    src/test/resources/web/html/workflow_write/ (see docs/llm/testing.md)


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

include_once test_paths::UNIT_WORKFLOW . 'triple_url_tests.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_db_load.php';

use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit_workflow\triple_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_write_url_tests extends triple_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'triple url write->', 'url write triple ');

        // remove any test triple left over from a previous run (including the user sandbox rows) so the
        // add workflow starts from a clean state; an add to an already existing triple keeps its old
        // description, which would fail the 'has written the triple' check
        $this->cleanup_test_triples($t);

        // create the from and to test words of the workflow test triple so the confirmed add can link
        // them; only test words are used so the workflows never change seeded data (e.g. the usage of
        // the math words)
        $t_db = new test_db_load($t);
        $t_db->test_word(word_names::TEST_ADD);
        $t_db->test_word(word_names::TEST_ADD_TO);

        // run the same three workflows as triple_url_tests but with do_it true so each confirmed step is
        // persisted: add creates the test triple, change modifies it and del removes it again - the add
        // must run first because the change and delete workflows load the triple it created by name
        $this->add_triple_workflow(workflows::WF_ADD_TRIPLE_NBR, true);
        $this->change_triple_workflow(workflows::WF_CHANGE_TRIPLE_NBR, true);
        $this->del_triple_workflow(workflows::WF_DEL_TRIPLE_NBR, true);


        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $this->cleanup_test_triples($t);

    }

    /**
     * TODO Prio 2 review
     * delete the workflow test triples and their from and to test words (including the user sandbox
     * rows) from the database so a run is not affected by leftovers of a previous run; the triples are
     * removed first so the words are no longer linked when they are deleted
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_triples(test_cleanup $t): void
    {
        $trp = new triple($t->usr1);
        foreach (triple_names::TEST_TRIPLES as $trp_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows; the reserved test triple is owned
            // by the system user (it is added with the system message user), so remove that row too -
            // otherwise it survives between runs and a later add keeps its old (changed) description
            $t->write_named_cleanup($trp, $trp_name);
            $t->write_named_cleanup_one($trp, $t->usr_system, $trp_name);
        }
        $wrd = new word($t->usr1);
        foreach ([word_names::TEST_ADD, word_names::TEST_ADD_TO] as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}
