<?php 

/*

  test_permission.php - TESTing of the permission level functions
  -------------------
  

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

use test\test_cleanup;

function run_permission_test (test_cleanup $t) {


    $t->header('Test the user permission level increase');

  // if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
  // if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
  // if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
  // if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

  // if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
  // if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
  // if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

  // the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

}