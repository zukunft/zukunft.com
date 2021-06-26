<?php 

/*

  test_permission.php - TESTing of the permission level functions
  -------------------
  

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

function run_permission_test () {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  test_header('Test the user permission level increase');

  // if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
  // if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
  // if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
  // if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

  // if a user got 10 pending word or formula discussion, he can no longer add words or formula until the open discussions are less than 10
  // if a user got 5 pending word or formula discussion, he can no longer change words or formula until the open discussions are less than 5
  // if a user got 2 pending verb discussion, he can no longer add verbs until the open discussions are less than 2

  // the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

}