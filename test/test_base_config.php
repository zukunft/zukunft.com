<?php

/*

  test_base_config.php - for admin user to check and correct the base configuration
  --------------------

  
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
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';
//$db_con = prg_start("start test_base_config.php");

// open database
$db_con = prg_start("test_base_config");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();
$back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->id = cl(db_cl::VIEW, view::IMPORT);
    $dsp->load_obj_vars();

    if ($usr->is_admin()) {

        // load the testing functions
        include_once '../src/main/php/service/import/import_file.php';
        if ($debug > 9) {
            echo 'test base loaded<br>';
        }

        // ---------------------------------------
        // start base configuration load and check
        // ---------------------------------------

        ui_echo($dsp->dsp_navbar($back));

        ui_echo("loading of base configuration started<br>");

        import_base_config();

        ui_echo("loading of base configuration finished<br>");

        ui_echo(dsp_go_back($back, $usr));
    }
}

// Closing connection
prg_end($db_con);
