<?php

/*

    /http/view.php - create the HTML code to display a zukunft.com view
    --------------

    - the view contains the overall formatting like page size
    - the view component links to words, values or formulas
    - a view component can be linked to a view or a view component define by the view_link_type

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

include_once 'const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

// load the mian frontend class
include_once paths::WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;

// reset the html code var
$html_str = '';

// open database
$db_con = prg_start("view", '', false);

if ($db_con->is_open()) {

    // load the session user parameters
    // TODO Prio 2 create a session object and include the user in the prg_start return object
    $usr = new user;
    $html_str .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {
        $usr->load_usr_data();

        $usr_dsp = new user_dsp();
        $usr_dsp->set_from_json($usr->api_json());

        // load the user changeable configuration once via api
        // TODO Prio 1 load the config from cache if nothing has been changed
        global $cfg;
        $cfg = new config();
        $cfg->load();

        $ui = new frontend('view');
        $ui->load_cache();
        $url_array = $_GET;
        // TODO Prio 1 remove temp overwrite for debug
        //$url = api::URL_DEV . views::WORD_EDIT_ID . url_var::ADD_ID . words::MATH_ID;
        //$url = 'http://localhost/http/view.php?m=3&id=1';
        //$url_part = parse_url($url);
        //parse_str($url_part["query"], $url_array);
        $html_str .= $ui->url_to_html($url_array, $usr_dsp, $ui->dto);
    }

    // close the database
    prg_end($db_con);
} else {
    $html_str .= 'database connection lost';
}

// show the page
echo $html_str;

