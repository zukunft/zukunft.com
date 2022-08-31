<?php

/*

    test_units.php - for fast internal code consistency TESTing of the technical library functions without database connection
    ------------

    because these tests are done without the database, we don't care if the is called by anybody, so we don't need the admin user test


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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';


// load the testing functions
include_once '../src/test/php/utils/test_base.php';

// ---------------
// prepare testing
// ---------------

$t = new test_unit();

// ------------------
// start unit testing
// ------------------

$t->run_unit();

// display the test results
$t->dsp_result_html();
$t->dsp_result();
