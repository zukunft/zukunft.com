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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// for callable php files the standard zukunft.com header to load all classes and allow debugging
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

use controller\controller;
use html\html_base;
use html\view\view as view_dsp;
use html\word\word_list as word_list_dsp;
use cfg\user;
use cfg\view;
use cfg\word_list;

global $system_views;

$result = ''; // reset the html code var

// open database
$db_con = prg_start("find");
$html = new html_base();

// TODO review the http API code based on this example
// TODO but first reduce the API files
// TODO but first resolve all testing error
if (!$db_con->connected()) {
    $result = log_fatal("Cannot connect to " . SQL_DB_TYPE . " database with user " . SQL_DB_USER_MYSQL, "find.php");
} else {
    $back = $_GET[api::URL_VAR_BACK] ?? '';

    // load the session user parameters
    $usr = new user;
    $result .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        $usr->load_usr_data();

        // show view header
        $view_id = $system_views->id(controller::MC_WORD_FIND);
        $msk = new view($usr);
        $msk->load_by_id($view_id);
        $msk->load_components();
        $msk_dsp = new view_dsp($msk->api_json());
        $result .= $msk_dsp->dsp_navbar($back);

        $find_str = $_GET['pattern'];

        $result .= $html->dsp_text_h2('Find word');

        // show a search field
        /* replaced by the navbar form
        $result .= dsp_form_start("find");
        $result .= dsp_form_fld('pattern', $find_str);
        $result .= dsp_form_end();
        */

        // show the matching words to select
        // TODO replace by term or phrase list
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_like($find_str);
        $dsp_lst = new word_list_dsp($wrd_lst->api_json());
        $result .= $dsp_lst->display();

        // show the matching terms to select
        // TODO create a term list object
        //$wrd_lst = new term_list($usr);
        //$result .= $wrd_lst->dsp_like($find_str, $usr);
    }
}

echo $result;

prg_end($db_con);
