<?php

/*

  test_display.php - TESTing of the DISPLAY functions
  ----------------
  

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

use api\view_api;
use api\word_api;
use controller\controller;
use html\button;
use html\html_base;
use html\msg;
use cfg\view;
use cfg\component_dsp_old;
use cfg\view_cmp_type;
use test\test_cleanup;
use const test\TW_ABB;

function run_display_test(test_cleanup $t): void
{

    global $usr;
    global $component_types;
    $html = new html_base();

    $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

    $t->header('Test the view_display class (classes/view_display.php)');

    // test the usage of a view to create the HTML code
    $wrd = $t->load_word(word_api::TN_READ);
    $dsp = new view($usr);
    $dsp->load_by_name(view_api::TN_READ_RATIO, view::class);
    //$result = $dsp->display($wrd, $back);
    $target = true;
    //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);


    $t->header('Test the view component display class (classes/component_dsp.php)');

    // test if a simple text component can be created
    $cmp = new component_dsp_old($usr);
    $cmp->type_id = $component_types->id(view_cmp_type::TEXT);
    $cmp->set_id(1);
    $cmp->set_name(view_api::TN_READ_NESN_2016);
    $result = $cmp->dsp_obj()->html();
    $target = view_api::TN_READ_NESN_2016;
    $t->display('component_dsp->text', $target, $result);


    $t->header('Test the display button class (src/main/php/web/html/button.php )');

    $url = $html->url(controller::DSP_WORD_ADD);
    $back = '1';
    $target = '<a href="/http/word_add.php" title="Add test"><img src="/src/main/resources/images/button_add.svg" alt="Add test"></a>';
    $target = '<a href="/http/word_add.php" title="add new word">';
    $result = (new button($url, $back))->add(msg::WORD_ADD);
    $t->dsp_contains(", btn_add", $target, $result);

    $url = $html->url(controller::DSP_WORD_EDIT);
    $target = '<a href="/http/view.php" title="Edit test"><img src="/src/main/resources/images/button_edit.svg" alt="Edit test"></a>';
    $target = '<a href="/http/word_edit.php" title="rename word"><i class="far fa-edit"></i></a>';
    $result = (new button($url, $back))->edit(msg::WORD_EDIT);
    $t->dsp_contains(", btn_edit", $target, $result);

    $url = $html->url(controller::DSP_WORD_DEL);
    $target = '<a href="/http/view.php" title="Del test"><img src="/src/main/resources/images/button_del.svg" alt="Del test"></a>';
    $target = '<a href="/http/word_del.php" title="Delete word"><i class="far fa-times-circle"></i></a>';
    $result = (new button($url, $back))->del(msg::WORD_DEL);
    $t->dsp_contains(", btn_del", $target, $result);

    $url = $html->url(controller::DSP_WORD);
    $target = '<a href="/http/view.php" title="Undo test"><img src="/src/main/resources/images/button_undo.svg" alt="Undo test"></a>';
    $target = '<a href="/http/word.php" title="undo"><img src="/src/main/resources/images/button_undo.svg" alt="undo"></a>';
    $result = (new button($url, $back))->undo(msg::UNDO);
    $t->display(", btn_undo", $target, $result);

    $url = $html->url(controller::DSP_WORD_ADD);
    $target = '<a href="/http/view.php" title="Find test"><img src="/src/main/resources/images/button_find.svg" alt="Find test"></a>';
    $target = '<a href="/http/word_add.php" title=""><img src="/src/main/resources/images/button_find.svg" alt=""></a>';
    $result = (new button($url, $back))->find();
    $t->display(", btn_find", $target, $result);

    $url = $html->url(controller::DSP_WORD_ADD);
    $target = '<a href="/http/view.php" title="Show all test"><img src="/src/main/resources/images/button_filter_off.svg" alt="Show all test"></a>';
    $target = '<a href="/http/word_add.php" title=""><img src="/src/main/resources/images/button_filter_off.svg" alt=""></a>';
    $result = (new button($url, $back))->unfilter();
    $t->display(", btn_unfilter", $target, $result);

    $url = $html->url(controller::DSP_WORD_ADD);
    $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
    $target = '<h6></h6><a href="/http/word_add.php&confirm=1" title="Yes">Yes</a>/<a href="/http/word_add.php&confirm=-1" title="No">No</a>';
    $result = (new button($url, $back))->yesno();
    $t->display(", btn_yesno", $target, $result);

    $url = $html->url(controller::DSP_WORD_ADD);
    $target = '<a href="/http/view.php?words=1" title="back"><img src="/src/main/resources/images/button_back.svg" alt="back"></a>';
    $result = (new button($url, $back))->back();
    $t->display(", btn_back", $target, $result);


    $t->header('Test the display HTML class');

    $target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../../../../main/resources/style/style.css" /> </head> <body class="center_form">'));
    $target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
    $result = htmlspecialchars(trim($html->header('Header test', 'center_form')));
    $t->dsp_contains(", dsp_header", $target, $result);


    $t->header('Test general frontend scripts (e.g. /about.php)');

    // check if the about page contains at least some basic keywords
    // TODO reactivate: $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
    $target = 'zukunft.com AG';
    if (strpos($result, $target) > 0) {
        $result = $target;
    } else {
        $result = '';
    }
    // about does not return a page for unknown reasons at the moment
    //$t->dsp_contains(', frontend about.php '.$result.' contains at least '.$target, $target, $result, TIMEOUT_LIMIT_PAGE);

    $is_connected = $t->dsp_web_test(
        'http/privacy_policy.html',
        'Swiss purpose of data protection',
        ', frontend privacy_policy.php contains at least');
    $is_connected = $t->dsp_web_test(
        'http/error_update.php?id=1',
        'not permitted',
        ', frontend error_update.php contains at least', $is_connected);
    $t->dsp_web_test(
        'http/find.php?pattern=' . TW_ABB,
        TW_ABB,
        ', frontend find.php contains at least', $is_connected);

}