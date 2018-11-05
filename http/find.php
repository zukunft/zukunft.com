<?php 

/*

  find.php - general search for a word or formula by a pattern
  --------


zukunft.com - calc with words

copyright 1995-2018 by zukunft.com AG, Zurich

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

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start("find", "", $debug);

  $result = ''; // reset the html code var

  $back      = $_GET['back'];
  
  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    // show view header
    $dsp = new view_dsp;
    $dsp->usr = $usr;
    $dsp->id = cl(SQL_VIEW_WORD_FIND);
    $result .= $dsp->top_right($back, $debug-1);

    $find_str = $_GET['pattern'];

    $result .= dsp_text_h2('Find word');

    // show a search field
    $result .= dsp_form_start("find");
    $result .= dsp_form_fld('pattern', $find_str);
    $result .= dsp_form_end();

    // show the matching words to select
    $wrd_lst = New word_list;
    $result .= $wrd_lst->dsp_like($find_str, $usr->id, $debug-1);
  }

  echo $result;

zu_end($link, $debug);
?>
