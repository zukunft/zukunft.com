<?php

/*

  find.php - general search for a word or formula by a pattern
  --------


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

if (isset($_GET['debug'])) {
    $debug = $_GET['debug'];
} else {
    $debug = 0;
}
include_once '../src/main/php/zu_lib.php';
if ($debug > 0) {
    echo 'libs loaded<br>';
}

$result = ''; // reset the html code var

// open database
$db_con = prg_start("find");

// TODO review the http API code based on this example
// TODO but first reduce the API files
// TODO but first resolve all testing error
if ($db_con == null) {
    $result = log_fatal("Cannot connect to " . SQL_DB_TYPE . " database with user " . SQL_DB_USER,"find.php");
} else {
    $back = $_GET['back'];

    // load the session user parameters
    $usr = new user;
    $result .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // show view header
        $dsp = new view_dsp;
        $dsp->usr = $usr;
        $dsp->id = cl(DBL_VIEW_WORD_FIND);
        $result .= $dsp->dsp_navbar($back);

        $find_str = $_GET['pattern'];

        $result .= dsp_text_h2('Find word');

        // show a search field
        /* replaced by the navbar form
        $result .= dsp_form_start("find");
        $result .= dsp_form_fld('pattern', $find_str);
        $result .= dsp_form_end();
        */

        // show the matching words to select
        $wrd_lst = new word_list;
        $result .= $wrd_lst->dsp_like($find_str, $usr->id);
    }
}

echo $result;

prg_end($db_con);
