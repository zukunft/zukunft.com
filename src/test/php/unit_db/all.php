<?php

/*

    test/php/unit/test_unit_db.php - add tests to the unit test that read only from the database in a useful order
    ------------------------------
    
    the zukunft.com unit tests should test all class methods, that can be tested without writing to the database


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

use api\phrase_group_api;
use api\triple_api;
use api\value_api;
use api\word_api;

class test_unit_read_db extends test_unit
{

    function run_unit_db_tests(): void
    {
        $this->header('Start the zukunft.com unit database read only tests');

        // do the database unit tests
        (new system_unit_db_tests)->run($this);
        (new sql_db_unit_db_tests)->run($this);
        (new user_unit_db_tests)->run($this);
        (new protection_unit_db_tests)->run($this);
        (new share_unit_db_tests)->run($this);
        (new word_unit_db_tests)->run($this);
        (new word_list_unit_db_tests)->run($this);
        (new verb_unit_db_tests)->run($this);
        (new phrase_unit_db_tests)->run($this);
        (new phrase_group_unit_db_tests)->run($this);
        (new term_unit_db_tests)->run($this);
        (new term_list_unit_db_tests)->run($this);
        (new value_unit_db_tests)->run($this);
        (new formula_unit_db_tests)->run($this);
        (new formula_list_unit_db_tests)->run($this);
        (new expression_unit_db_tests)->run($this);
        (new view_unit_db_tests)->run($this);
        (new ref_unit_db_tests)->run($this);
        (new change_log_unit_db_tests)->run($this);
        (new system_log_unit_db_tests)->run($this);
        (new batch_job_unit_db_tests)->run($this);

        $this->run_api_test();

    }

    function init_unit_db_tests(): void
    {

        // add the database rows for read testing
        $this->test_triple(
            triple_api::TN_READ, verb::IS_A, word_api::TN_READ,
            triple_api::TN_READ_NAME, triple_api::TN_READ_NAME
        );
        $phr_grp = $this->add_phrase_group(array(triple_api::TN_READ_NAME), phrase_group_api::TN_READ);
        $this->test_value_by_phr_grp($phr_grp, value_api::TV_READ);

    }

}