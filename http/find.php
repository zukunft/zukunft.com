<?php

/*

  find.php - general search for a word or formula by a pattern
  --------


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

use html\html_base;
use html\view\view_dsp_old;
use model\user;
use model\view;
use model\word_list;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

global $system_views;

$result = ''; // reset the html code var

// open database
$db_con = prg_start("find");
$html = new html_base();

// TODO review the http API code based on this example
// TODO but first reduce the API files
// TODO but first resolve all testing error
if ($db_con == null) {
    $result = log_fatal("Cannot connect to " . SQL_DB_TYPE . " database with user " . SQL_DB_USER_MYSQL, "find.php");
} else {
    $back = $_GET['back'];

    // load the session user parameters
    $usr = new user;
    $result .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        load_usr_data();

        // show view header
        $dsp = new view_dsp_old($usr);
        $dsp->set_id($system_views->id(view::WORD_FIND));
        $result .= $dsp->dsp_navbar($back);

        $find_str = $_GET['pattern'];

        $result .= $html->dsp_text_h2('Find word');

        // show a search field
        /* replaced by the navbar form
        $result .= dsp_form_start("find");
        $result .= dsp_form_fld('pattern', $find_str);
        $result .= dsp_form_end();
        */

        // show the matching words to select
        $wrd_lst = new word_list($usr);
        $result .= $wrd_lst->dsp_obj()->dsp_like($find_str, $usr);

        // show the matching terms to select
        // TODO create a term list object
        //$wrd_lst = new term_list($usr);
        //$result .= $wrd_lst->dsp_like($find_str, $usr);
    }
}

echo $result;

prg_end($db_con);
