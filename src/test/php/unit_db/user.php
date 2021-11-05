<?php

/*

  test/unit_db/user.php - database unit testing of the user profile handling
  -----------------------------


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

function run_user_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the user profile handling');

    $t->subheader('User profile tests');

    // load the user_profile types
    $lst = new user_profile_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_user_profile->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::USER_PROFILE, user_profile::NORMAL);
    $target = 1;
    $t->dsp('unit_db_user_profile->check ' . user_profile::NORMAL, $result, $target);

}

