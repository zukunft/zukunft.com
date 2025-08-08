<?php

/*

    unit_read/system_view_read_tests.php - testing of the system views with direct data reload from the database
    ------------------------------------
  

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

namespace unit_read;

use cfg\component\component;
use cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use cfg\formula\formula;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use html\frontend;
use html\helper\data_object as data_object_dsp;
use html\html_base;
use shared\const\views;
use shared\const\views as view_shared;
use shared\const\words;
use test\test_cleanup;

class system_views_read_tests
{

    /**
     * get the api message and forward it to the ui
     * TODO move all other HTML frontend tests here
     *
     * @param test_cleanup $t
     * @return void
     */
    function run(test_cleanup $t): void
    {
        // create the stable test context that is not based on the database so that the test results rarely change
        $ui = new frontend('system_views_read_tests');
        $ui->load_cache();
        $cfg = new data_object_dsp();
        $cfg->typ_lst_cache = $ui->typ_lst_cache;
        //$cfg = new data_object_dsp();
        $cfg->set_view_list($t->view_list_dsp());
        // create the test pages
        $t->assert_view(views::WORD_CODE_ID, $t->usr1, new word($t->usr1), 1, $cfg);
        $t->assert_view(views::WORD_ADD, $t->usr1, new word($t->usr1));
        $t->assert_view(views::WORD_EDIT, $t->usr1, new word($t->usr1), 1, $cfg);
        $t->assert_view(views::WORD_DEL, $t->usr1, new word($t->usr1), 1, $cfg);
        $t->assert_view(views::VERB_CODE_ID, $t->usr1, new verb(), 1, $cfg);
        $t->assert_view(views::VERB_ADD, $t->usr1, new verb());
        $t->assert_view(views::VERB_EDIT, $t->usr1, new verb(), 1, $cfg);
        $t->assert_view(views::VERB_DEL, $t->usr1, new verb(), 1, $cfg);
        //$t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), 1, $cfg);
        $t->assert_view(views::TRIPLE_ADD, $t->usr1, new triple($t->usr1));
        $t->assert_view(views::TRIPLE_EDIT, $t->usr1, new triple($t->usr1), 1, $cfg);
        $t->assert_view(views::TRIPLE_DEL, $t->usr1, new triple($t->usr1), 1, $cfg);
        //$t->assert_view(views::SOURCE, $t->usr1, new source($t->usr1), 1, $cfg);
        $t->assert_view(views::SOURCE_ADD, $t->usr1, new source($t->usr1));
        $t->assert_view(views::SOURCE_EDIT, $t->usr1, new source($t->usr1), 1, $cfg);
        $t->assert_view(views::SOURCE_DEL, $t->usr1, new source($t->usr1), 1, $cfg);
        // TODO add:
        // REF
        $t->assert_view(views::REF_ADD, $t->usr1, new ref($t->usr1));
        // VALUE
        // GROUP
        //$t->assert_view(views::GROUP_ADD, $t->usr1, new group($t->usr1));
        // FORMULA
        $t->assert_view(views::FORMULA_ADD, $t->usr1, new formula($t->usr1));
        $t->assert_view(views::FORMULA_EDIT, $t->usr1, new formula($t->usr1), 1, $cfg);
        $t->assert_view(views::FORMULA_DEL, $t->usr1, new formula($t->usr1), 1, $cfg);
        // FORMULA TEST
        // RESULT
        // VIEW
        $t->assert_view(views::VIEW_ADD, $t->usr1, new view($t->usr1));
        $t->assert_view(views::VIEW_EDIT, $t->usr1, new view($t->usr1), 1, $cfg);
        $t->assert_view(views::VIEW_DEL, $t->usr1, new view($t->usr1), 1, $cfg);
        // COMPONENT
        $t->assert_view(views::COMPONENT_ADD, $t->usr1, new component($t->usr1));
        $t->assert_view(views::COMPONENT_EDIT, $t->usr1, new component($t->usr1), 1, $cfg);
        $t->assert_view(views::COMPONENT_DEL, $t->usr1, new component($t->usr1), 1, $cfg);
        // USER
        // LANGUAGE
        // SYS LOG
        // CHANGE LOG
        // IMPORT
        // EXPORT
        // PROCESS
        // FIND
        //$t->assert_view(view_shared::DSP_COMPONENT_ADD, $t->usr1, new component($t->usr1), 1, $cfg);
        // TODO add the frontend reaction tests e.g. call the view.php script with the reaction to add a word


        // start the test section (ts)
        $ts = 'unit web frontend ';
        $t->header($ts);

        $html = new html_base();
        $target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../../../main/resources/style/style.css" /> </head> <body class="center_form">'));
        $target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
        $result = htmlspecialchars(trim($html->header('Header test', 'center_form')));
        $t->dsp_contains(", dsp_header", $target, $result);

        // check if the about page contains at least some basic keywords
        // TODO activate Prio 3: $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
        $target = 'zukunft.com AG';
        if (strpos($result, $target) > 0) {
            $result = $target;
        } else {
            $result = '';
        }
        // about does not return a page for unknown reasons at the moment
        // $t->dsp_contains(', frontend about.php '.$result.' contains at least ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $is_connected = $t->dsp_web_test(
            'http/privacy_policy.html',
            'Swiss purpose of data protection',
            ', frontend privacy_policy.php contains at least');
        $is_connected = $t->dsp_web_test(
            'http/error_update.php?id=1',
            'not permitted',
            ', frontend error_update.php contains at least', $is_connected);
        $t->dsp_web_test(
            'http/find.php?pattern=' . words::ABB,
            words::ABB,
            ', frontend find.php contains at least', $is_connected);

    }

}