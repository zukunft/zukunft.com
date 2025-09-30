<?php

/*

    web/component/execute/system_page.php - create the html code for fixed system pages
    -------------------------------------

    to create the HTML code to display a fixed system page

    The main sections of this object are
    - object vars:       the variables of this word object


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class system_page extends component
{

    /**
     * HTML for a page title
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user specific frontend language
     * @return string the html code to start a new form and display the tile
     */
    function system_tile(?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h2($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    /**
     * HTML for a subtitle
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user specific frontend language
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile(?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    // TODO Prio 0 fill with real code
    function preview(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function about_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function setup_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function signup_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function login_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function activate_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function reset_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function logout_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function body_search(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function body_search_full(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function value_details(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function result_explain(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function formula_test(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function sandbox(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function undo(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function user_setting(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function process(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function error_log(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function error_update(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function process_progress(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    function process_list(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

}
