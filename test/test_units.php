<?php

/*

  test_units.php - for fast internal code consistency TESTing of the technical library functions without database connection
  ------------

  because these tests are done without the database, we don't care if the is called by anybody, so we don't need the admin user test


zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';


// load the testing functions
include_once '../src/test/php/utils/test_base.php';

// ---------------
// prepare testing
// ---------------

$t = new testing();

// ------------------
// start unit testing
// ------------------

run_unit_tests($t);

// display the test results
$t->dsp_result_html();
$t->dsp_result();
