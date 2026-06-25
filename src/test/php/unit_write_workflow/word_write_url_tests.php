<?php

/*

    test/php/unit_write_workflow/word_write_url_tests.php - persist the url based word user workflows
    ----------------------------------------------------

    runs the same add, change and delete word workflows as word_url_tests, but with the do_it flag set
    to true so each confirmed step is actually written to the database; the steps snapshot into
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

include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once paths::MODEL_WORD . 'word.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';

use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_write_url_tests extends word_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'word url write->', 'url write word ');

        // run the same three workflows as word_url_tests but with do_it true so each confirmed step is
        // persisted: add creates the test word, change modifies it, del removes it again - the add must
        // run first because the change and delete workflows load the word it created by name
        $this->add_word_workflow(workflows::WF_ADD_WORD_NBR, true);
        $this->change_word_workflow(workflows::WF_CHANGE_WORD_NBR, true);
        $this->del_word_workflow(workflows::WF_DEL_WORD_NBR, true);


        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $wrd = new word($t->usr1);
        foreach (word_names::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

    }

}