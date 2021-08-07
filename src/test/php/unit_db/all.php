<?php

/*

  all.php - run all unit database read only tests in a useful order
  -----------------

  the zukunft.com unit tests should test all class methods, that can be tested without writing to the database


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

function run_unit_db_tests()
{
    test_header('Start the zukunft.com unit database read only tests');

    // do the database unit tests
    run_formula_unit_db_tests();
    run_protection_unit_db_tests();
    run_ref_unit_db_tests();
    run_share_unit_db_tests();
    run_user_unit_db_tests();
    run_verb_unit_db_tests();
    run_view_unit_db_tests();
    run_word_unit_db_tests();
}