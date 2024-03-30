<?php

/*

    test/php/unit_write/all_write_tests.php - add all db write tests to the test class
    ---------------------------------------
    
    the zukunft.com database write tests should test all class methods, that have not been tested by the unit and db read tests


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

use test\all_tests;
use unit_read\all_unit_read_tests;

class all_unit_write_tests extends all_unit_read_tests
{

    function run_db_write_tests(all_tests $t): void
    {
        $this->header('Start the zukunft.com database write tests');

        (new word_tests)->run($t);
        (new word_list_tests)->run($t);
        (new verb_tests)->run($t);
        (new triple_tests)->run($t);
        (new phrase_tests)->run($t);
        (new phrase_list_tests)->run($t);
        (new phrase_group_tests)->run($t);
        (new phrase_group_list_tests)->run($t);
        (new graph_tests)->run($t);
        (new term_tests)->run($t);
        //(new term_list_tests)->run($t);
        (new value_tests)->run($t);
        (new source_tests)->run($t);
        (new ref_tests)->run($t);
        (new expression_tests)->run($t);
        (new formula_tests)->run($t);
        (new formula_tests)->run_list($t);
        (new formula_link_tests)->run($t);
        (new formula_link_tests)->run_list($t);
        (new formula_trigger_tests)->run($t);
        (new result_tests)->run($t);
        // TODO activate Prio 1
        //(new result_tests)->run_list($t);
        (new element_tests)->run($t);
        (new element_tests)->run_list($t);
        (new element_group_tests)->run($t);
        (new job_tests)->run($t);
        (new job_tests)->run_list($t);
        (new view_tests)->run($t);
        (new component_tests)->run($t);
        (new component_link_tests)->run($t);

    }

}