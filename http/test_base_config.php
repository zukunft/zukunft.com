<?php

/*

  test_base_config.php - for admin user to check and correct the base configuration
  --------------------

  
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
if (isset($_GET['debug'])) {
    $debug = $_GET['debug'];
} else {
    $debug = 0;
}
include_once '../src/main/php/zu_lib.php';
if ($debug > 1) {
    echo 'lib loaded<br>';
}
$db_con = prg_start("start test_base_config.php", "", $debug - 10);

// open database
$db_con = prg_start("test_base_config", "", $debug);

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get($debug - 1);
$back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(DBL_VIEW_IMPORT);
    $dsp->usr = $usr;
    $dsp->load($debug - 1);

    if ($usr->is_admin($debug)) {

        // load the testing functions
        include_once '../src/main/php/service/import/import_file.php';
        if ($debug > 9) {
            echo 'test base loaded<br>';
        }

        // ---------------------------------------
        // start base configuration load and check
        // ---------------------------------------

        ui_echo($dsp->dsp_navbar($back, $debug-1));

        ui_echo("loading of base configuration started<br>");

        import_base_config($debug);

        ui_echo("loading of base configuration finished<br>");

        ui_echo(dsp_go_back($back, $usr, $debug-1));
    }
}

// Closing connection
prg_end($db_con, $debug);
