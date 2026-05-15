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
include_once html_paths::USER . 'user.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'Translator.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class system_page extends component
{

    /**
     * HTML for a page title
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
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
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile(
        ?msg_id $ui_msg_code_id = null
    ): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    /**
     * HTML for a subtitle
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile_var(
        ?msg_id $ui_msg_code_id = null,
        ?int    $value_numeric = null,
        ?msg_id $ui_msg_code_id_vars = null,
        ?int    $value_exception = null,
        ?msg_id $ui_msg_code_id_exception = null
    ): string
    {
        global $mtr;
        $lib = new library();

        $html = new html_base();
        $result = '';
        if ($value_exception != null and $value_numeric == $value_exception) {
            if ($ui_msg_code_id_exception != null) {
                $result .= $html->text_h3($mtr->txt($ui_msg_code_id_exception));
            }
        } else {
            if ($ui_msg_code_id != null) {
                $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
            }
            if ($ui_msg_code_id_vars != null) {
                $msg_id = $mtr->txt($ui_msg_code_id_vars);
                $msg_txt = '';

                // TODO Prio 1 move to a general function
                if ($msg_id == msg_id::FORM_SUB_TITLE_VAR_USAGE->value) {
                    $msg_txt = msg_id::SYS_MSG_USAGE;
                    $msg_txt = $lib->msg_var_replace(
                        $msg_txt->value,
                        msg_id::VAR_USAGE,
                        $value_numeric);
                }

                $result .= ' ' . $html->text_h3($msg_txt);
            }
        }
        return $result;
    }

    // TODO Prio 0 fill with real code
    function preview(): string
    {
        return 'preview placeholder';
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
        return 'setup_body placeholder';
    }

    /**
     * build the signup form HTML
     *
     * @param array $url_array the POST parameters from the signup form submission; used to pre-fill fields after a validation error
     * @return string the complete signup page body HTML (without notification bar; caller renders that separately)
     */
    function signup_body(array $url_array): string
    {
        global $mtr;

        $html = new html_base();
        $usr_name = $url_array[url_var::USERNAME] ?? '';
        $email = $url_array[url_var::EMAIL] ?? '';

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::SIGNUP_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $form_str = $web_usr->form_signup($extra_hidden, $usr_name, $email);

        $result = $html->p($mtr->txt(msg_id::SIGNUP_ALPHA_NOTICE));
        $result .= $html->p($html->dsp_err($mtr->txt(msg_id::SIGNUP_DATA_WARNING)));
        $result .= $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    function login_body(array $url_array = []): string
    {

        $html = new html_base();
        $_SESSION[url_var::SESSION_LOGGED] = false;

        // embed the login mask and any BACK-prefixed params as hidden fields so they survive the POST
        // view.php dispatches on url_var::MASK in url_to_action; without LOGIN_ID the POST has no routing key and action_login is never called
        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $back_url = html_base::url_from_back($url_array);
        $form_str = $web_usr->form_login($extra_hidden, $back_url);

        return $html->logo_flex() . $html->br2() . $html->div($form_str, html_base::CLASS_INPUT_SECTION);
    }

    /**
     * build the account activation (password change) form HTML
     *
     * @param array $url_array the URL params; expects id and optionally key from the activation link
     * @return string the complete activation page body HTML
     */
    function activate_body(array $url_array): string
    {
        $html = new html_base();
        $usr_id = (int)($url_array[url_var::ID] ?? 0);
        $key = $url_array[url_var::POST_KEY] ?? '';

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_ACTIVATE_ID);
        foreach (url_var::back_par($url_array) as $key_par => $val) {
            $extra_hidden .= $html->form_hidden($key_par, $val);
        }

        $web_usr = new user_dsp();
        $form_str = $web_usr->form_activate($extra_hidden, $usr_id, $key);

        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    // TODO Prio 0 fill with real code
    function reset_body(): string
    {
        return 'reset_body placeholder';
    }

    /**
     * HTML shown on the logout confirmation page
     * @return string the logout page body HTML
     */
    function logout_body(): string
    {
        global $mtr;

        $html = new html_base();
        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($html->p($mtr->txt(msg_id::LOGOUT_NOTICE)), html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    // TODO Prio 0 fill with real code
    function body_search(): string
    {
        return 'body_search placeholder';
    }

    // TODO Prio 0 fill with real code
    function body_search_full(): string
    {
        return 'body_search_full placeholder';
    }

    // TODO Prio 0 fill with real code
    function value_details(): string
    {
        return 'value_details placeholder';
    }

    // TODO Prio 0 fill with real code
    function result_explain(): string
    {
        return 'result_explain placeholder';
    }

    // TODO Prio 0 fill with real code
    function formula_test(): string
    {
        return 'formula_test placeholder';
    }

    // TODO Prio 0 fill with real code
    function sandbox(): string
    {
        return 'sandbox placeholder';
    }

    // TODO Prio 0 fill with real code
    function undo(): string
    {
        return 'undo placeholder';
    }

    // TODO Prio 0 fill with real code
    function user_setting(): string
    {
        return 'user_setting placeholder';
    }

    // TODO Prio 0 fill with real code
    function process(): string
    {
        return 'process placeholder';
    }

    // TODO Prio 0 fill with real code
    function error_log(): string
    {
        return 'error_log placeholder';
    }

    // TODO Prio 0 fill with real code
    function error_update(): string
    {
        return 'error_update placeholder';
    }

    // TODO Prio 0 fill with real code
    function process_progress(): string
    {
        return 'process_progress placeholder';
    }

    // TODO Prio 0 fill with real code
    function process_list(): string
    {
        return 'process_list placeholder';
    }

}
