<?php

/*

    link_del.php - remove a triple
    ------------

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
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_CONST_PATH . 'views.php';

use cfg\user\user;
use cfg\view\view;
use cfg\word\triple;
use html\html_base;
use html\view\view_navbar as view_dsp;
use shared\api;
use shared\const\views as view_shared;

// open database
$db_con = prg_start("link_del");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $html = new html_base();

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(view_shared::TRIPLE_DEL);
    $back = $_GET[api::URL_VAR_BACK] = ''; // the original calling page that should be shown after the change if finished

    // get the parameters
    $link_id = $_GET[api::URL_VAR_ID] ?? 0;
    $confirm = $_GET['confirm'];

    // delete the link or ask for confirmation
    if ($link_id > 0) {

        // create the source object to have an object to update the parameters
        $lnk = new triple($usr);
        $lnk->load_by_id($link_id);

        if ($confirm == 1) {
            $lnk->del();

            $result .= $html->dsp_go_back($back, $usr);
        } else {
            // display the view header
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar($back);

            $result .= $lnk->dsp_del($back);
        }
    } else {
        $result .= $html->dsp_go_back($back, $usr);
    }
}

echo $result;

prg_end($db_con);
