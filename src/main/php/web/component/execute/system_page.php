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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'config_tree.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::PHRASE . 'term_list.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SYSTEM . 'job.php';
include_once html_paths::SYSTEM . 'job_list.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::SYSTEM . 'system_time_list.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_log_sum_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WEB . 'frontend.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'sys_log_statuum.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_CONST . 'components.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED_HELPER . 'Translator.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config_tree;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list;
use Zukunft\ZukunftCom\main\php\web\system\job;
use Zukunft\ZukunftCom\main\php\web\system\job_list;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\system\system_time_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user_log_sum_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;
use DateTimeInterface;

class system_page extends component
{

    // the menu name of each view version in the order of views::DEFAULT_VIEW_VERSIONS
    const array VIEW_VERSION_NAMES = [
        msg_id::VIEW_VERSION_DEFAULT,
        msg_id::VIEW_VERSION_DETAIL,
        msg_id::VIEW_VERSION_RANGE,
        msg_id::VIEW_VERSION_DETAIL_RANGE,
    ];

    // the labels of the tabs of a process catch page; the position in the list is the tab number - 1
    // (components::PROCESS_CATCH_TAB_NBR), so the label is not a ui message of the tab component,
    // which must be unique for each component
    const array PROCESS_CATCH_TABS = [
        msg_id::PROCESS_CATCH_TAB_ONE,
        msg_id::PROCESS_CATCH_TAB_TWO,
        msg_id::PROCESS_CATCH_TAB_THREE,
        msg_id::PROCESS_CATCH_TAB_FOUR,
        msg_id::PROCESS_CATCH_TAB_FIVE,
    ];
    // the tab that a process catch page shows if the url selects none
    const int PROCESS_CATCH_TAB_DEFAULT = 1;

    /**
     * HTML for a page title
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @param array $url_array the URL params; used to add the search pattern to the search result title
     * @return string the html code to start a new form and display the tile
     */
    function system_tile(?msg_id $ui_msg_code_id = null, array $url_array = []): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            // on the search page show "search result for <pattern>" instead of the plain "search" title
            if ($ui_msg_code_id == msg_id::FORM_TITLE_SEARCH) {
                $result .= $html->text_h2($this->search_title($url_array));
            } else {
                $result .= $html->text_h2($mtr->txt($ui_msg_code_id));
            }
        }
        return $result;
    }

    /**
     * HTML for a page title that names the object shown on the page e.g. User "zukunft.com system test"
     * the object word comes from the message id of the component, so the same component type can title
     * the default page of any object, and the name is the name of the object the page has been called for
     *
     * @param msg_id|null $ui_msg_code_id the message id of the object word in the user-specific frontend language
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the object shown on the page
     * @return string the html code of the page title
     */
    function title_with_object_name(
        ?msg_id                                               $ui_msg_code_id = null,
        db_object|type_object|combine_named|sandbox_list|null $dbo = null
    ): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $title = $mtr->txt($ui_msg_code_id);
            if ($dbo != null) {
                // escape the user settable name, because the title is shown as raw html
                $title .= ' "' . $html->esc($dbo->name()) . '"';
            }
            $result = $html->text_h2($title);
        }
        return $result;
    }

    /**
     * HTML for a page title that names the phrase the page works on e.g. Table editor for populism solution
     *
     * @param msg_id|null $ui_msg_code_id the message id of the title in the user-specific frontend language
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the phrase of the page; without a name only the title is shown
     * @return string the html code of the page title
     */
    function title_for_phrase(
        ?msg_id                                               $ui_msg_code_id = null,
        db_object|type_object|combine_named|sandbox_list|null $dbo = null
    ): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $title = $mtr->txt($ui_msg_code_id);
            $name = $dbo?->name() ?? '';
            if ($name != '') {
                // escape the user settable name, because the title is shown as raw html
                $title .= $mtr->txt(msg_id::FOR) . $html->esc($name);
            }
            $result = $html->text_h2($title);
        }
        return $result;
    }

    /**
     * the "..." in the right corner below the title of a default, detail, range or detail with
     * range view that opens the menu to show the same object in one of the other three versions
     *
     * @param int $msk_id the database id of the view that shows the object e.g. views::WORD_ID
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the object shown on the page
     * @return string the html code of the menu or '' if the view is not a version of a default view
     */
    function related_view_selector(
        int                                                   $msk_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo
    ): string
    {
        $html = new html_base();
        $result = '';
        $versions = $this->view_versions($msk_id);
        // only an object with a db id can be shown in another view
        if ($versions != [] and $dbo instanceof db_object) {
            $items = '';
            foreach ($versions as $pos => $view_id) {
                if ($view_id != $msk_id) {
                    $url = html_base::url($view_id, $dbo->id());
                    $items .= $html->list_item($html->ref($url, self::VIEW_VERSION_NAMES[$pos]->text()));
                }
            }
            $menu = $html->popup_menu(msg_id::THREE_POINTS->text(), $items,
                styles::MENU_VIEW_VERSION, msg_id::VIEW_VERSION_TIP->text());
            $result = $html->div($menu, styles::VIEW_VERSION_CORNER);
        }
        return $result;
    }

    /**
     * @param int $msk_id the database id of a view e.g. views::WORD_DEFAULT_DETAIL_ID
     * @return array the ids of the four versions of the default view that the given view is one of
     *               or an empty array if the view is not a version of a default view
     */
    private function view_versions(int $msk_id): array
    {
        $result = [];
        foreach (views::DEFAULT_VIEW_VERSIONS as $versions) {
            if (in_array($msk_id, $versions)) {
                $result = $versions;
            }
        }
        return $result;
    }

    /**
     * build the search page title; with a pattern it reads "search result for <pattern>",
     * without a pattern it falls back to the plain search title
     * @param array $url_array the URL params; the pattern is read from url_var::PATTERN with the
     *                         human-readable url_var::PATTERN_HUMAN as fallback (see body_search)
     * @return string the language specific search page title
     */
    private function search_title(array $url_array): string
    {
        global $mtr;
        $lib = new library();

        $pattern = $url_array[url_var::PATTERN] ?? $url_array[url_var::PATTERN_HUMAN] ?? '';
        if ($pattern == '') {
            $title = $mtr->txt(msg_id::FORM_TITLE_SEARCH);
        } else {
            // escape the user supplied pattern before it is placed into the html title, so a
            // reflected xss like ?pattern=<script>... is shown as text instead of executed
            $html = new html_base();
            $title = $lib->msg_var_replace(
                $mtr->txt(msg_id::FORM_TITLE_SEARCH_RESULT),
                msg_id::VAR_PATTERN,
                $html->esc($pattern));
        }
        return $title;
    }

    /**
     * HTML for a subtitle
     * the formulas and the values subtitle of a word or triple page also show the icon to add a formula
     * or a value for the shown phrase (see ui_list::formula_add_link and ui_list::value_add_link)
     *
     * @param user_message $msg to report a problem of reading the config e.g. the frontend type for the add icon
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the object of the page e.g. the word of the formulas subtitle
     * @param array $url_arr the url of the shown page as back target of the add icon
     * @param int $msk_id the database id of the shown view; only a default view of a phrase gets the add icon
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile(
        user_message                                          $msg,
        ?msg_id                                               $ui_msg_code_id = null,
        db_object|type_object|combine_named|sandbox_list|null $dbo = null,
        array                                                 $url_arr = [],
        int                                                   $msk_id = 0
    ): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        // only the default view of a word or triple offers to add, not e.g. its edit or delete form
        if ($dbo instanceof db_object and in_array($msk_id, views::PHRASE_DEFAULT_IDS)) {
            $list = new ui_list();
            // the formulas and the values subtitle of a word or triple page offer to add one for the phrase
            $icon = match ($ui_msg_code_id) {
                msg_id::FORM_SUB_TITLE_FORMULAS => $list->formula_add_link($dbo->phrase(), $url_arr),
                msg_id::FORM_SUB_TITLE_VALUES => $list->value_add_link($dbo->phrase(), $msg, $url_arr),
                default => '',
            };
            // like the edit icon of the page title the icon stays on the line of the subtitle
            if ($icon != '') {
                $result = $html->div($result . $icon, styles::HEADING_LINE);
            }
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
        if ($value_exception !== null and $value_numeric === $value_exception) {
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
                    // a single usage needs the singular, because "Used 1 times" is wrong
                    if ($value_numeric === 1) {
                        $msg_txt = msg_id::SYS_MSG_USAGE_ONE;
                    } else {
                        $msg_txt = msg_id::SYS_MSG_USAGE;
                    }
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

    /**
     * show a view zoomed to 1/3 of its original size as a preview so that the user can see a
     * preview of the original page based on a different view mask
     */
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

    /**
     * request from the user the values relevant for the initial setup
     * so the main question ist that the user confirms the admin username and password from the .env for
     * or fill the admin user of the .env file entry is empty
     *
     * additional all values from the .env.example file should be show and be changeable
     *
     */
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

    /**
     * build the password reset request form HTML
     *
     * @param array $url_array the URL params; back params are forwarded as hidden fields
     * @return string the complete reset page body HTML
     */
    function reset_body(array $url_array): string
    {
        global $mtr;

        $html = new html_base();

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_RESET_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $back_url = html_base::url_from_back($url_array);
        $form_str = $mtr->txt(msg_id::RESET_PROMPT) . $html->br2() . $web_usr->form_reset($extra_hidden, $back_url);

        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    /**
     * HTML shown on the logout confirmation page with a button back to the last normal page seen before
     * the logout, which the logout link has set as the '9'-prefixed back target (see html_base::normal_page_array)
     * @param array $url_arr the url of the logout page with the '9'-prefixed back target
     * @return string the logout page body HTML
     */
    function logout_body(array $url_arr = []): string
    {
        global $mtr;

        $html = new html_base();
        // normalized again, because a logout url from elsewhere may point back to a change form
        $back_url = $html->page_url(html_base::normal_page_array(html_base::url_par_from_back_part($url_arr)));
        $back_btn = $html->ref($back_url, $mtr->txt(msg_id::FORM_FIELD_BACK), '',
            html_base::BS_BTN . ' ' . html_base::BS_BTN_CANCEL);
        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($html->p($mtr->txt(msg_id::LOGOUT_NOTICE)) . $back_btn, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    /**
     * the HTML code to show the search results with words, verbs, triple, formulas
     * and allow to narrow or widen the selection
     * based on the context (foaf terms) and "fixed" selections like the type or the share or protection
     * limit the number of search and selection fields so that it matches a small screen
     *
     * @param user_message $msg with the requesting user for whom the terms are searched
     * @param array $url_array the URL params; the search pattern is read from url_var::PATTERN
     *                          (the key the navbar search form submits) and falls back to the
     *                          human-readable url_var::PATTERN_HUMAN alias, because the human->standard
     *                          url remap only runs for human mask urls (mask_id=), not for a short mask (m=)
     * @param term_list|null $trm_lst the terms matching the pattern; normally null so the list is
     *                                loaded from the backend via the api, but it can be injected
     *                                (e.g. for unit tests) to render a pre-loaded list without a backend call
     * @return string with the HTML code of the search body
     */
    function body_search(user_message $msg, array $url_array = [], ?term_list $trm_lst = null): string
    {
        $result = '';

        // the search input field is part of the navbar search form, so it is not repeated here
        $pattern = $url_array[url_var::PATTERN] ?? $url_array[url_var::PATTERN_HUMAN] ?? '';

        // TODO Prio 0: if the search pattern is 'global' show the title 'search results for 'global*'

        // show the terms (word, triple, formula or verb) that match the search pattern,
        // most relevant (highest impact) first
        if ($trm_lst === null and $pattern != '') {
            $trm_lst = new term_list();
            $trm_lst->get_by_pattern($msg, $pattern);
        }
        if ($trm_lst !== null and !$trm_lst->is_empty()) {
            $result .= $trm_lst->links_with_context($url_array);
        } elseif ($pattern != '') {
            // nothing has been found, so say so and offer to create what the user has looked for
            $result .= $this->no_search_result($pattern);
            $result .= $this->add_word_button($pattern);
        }

        return $result;
    }

    /**
     * the message of a search that has found no word, triple, verb or formula, shown above the
     * button that offers to add the search pattern as a new word
     *
     * @param string $pattern the search pattern that has not found a term
     * @return string the html code of the no result message
     */
    private function no_search_result(string $pattern): string
    {
        global $mtr;
        $lib = new library();

        $html = new html_base();
        // escape the user supplied pattern before it is placed into the html, so a reflected
        // xss like ?pattern=<script>... is shown as text instead of executed (see search_title)
        $text = $lib->msg_var_replace(
            $mtr->txt(msg_id::INFO_NO_SEARCH_RESULT),
            msg_id::VAR_PATTERN,
            $html->esc($pattern));
        return $html->div($text);
    }

    /**
     * the button of a search without a result that opens the confirm page of the add word
     * workflow with the search pattern as the suggested name, so that the user can add the
     * word they have looked for without typing the name again
     *
     * the confirm step only shows the pending word and writes nothing, so the link needs no
     * session token, and it sets the back target itself (see frontend::url_to_action)
     *
     * @param string $pattern the search pattern that has not found a term
     * @return string the html code of the add word button
     */
    private function add_word_button(string $pattern): string
    {
        global $mtr;
        $lib = new library();

        $html = new html_base();
        $par = url_var::STEP . url_var::EQ . url_var::STEP_CONFIRM
            . url_var::ADD . url_var::NAME . url_var::EQ . urlencode($pattern);
        $url = $html->url_back(views::WORD_ADD_DETAIL_ID, 0, [], $par);
        // ref escapes the pattern, which is user input reflected from the url
        $btn_text = $lib->msg_var_replace(
            $mtr->txt(msg_id::SEARCH_ADD_WORD),
            msg_id::VAR_NAME,
            $pattern);
        return $html->ref($url, $btn_text, '', html_base::CLASS_BUTTON . ' ' . html_base::BS_BTN_SUCCESS);
    }

    // TODO Prio 0 fill with real code

    /**
     * like body_search but with all possible fields
     * @return string
     */
    function body_search_full(): string
    {
        return 'body_search_full placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code to show all relations of a value
     * e.g. where it is used and are it causes an impact
     */
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

    /**
     * render the admin error-update page body: a table of unresolved program issues an admin can
     * review and re-status; mirrors the display path of /http/error_update.php — the actual sys_log
     * status change is dispatched by the URL action handler so this body only renders the data
     *
     * @param sys_log_list|null $errors pre-loaded list of unresolved program issues; when null or
     *                                  empty the "no open errors" notice is shown
     * @param user_dsp|null $usr the session user; when null or not an admin a permission notice is
     *                           rendered instead of the issue list — default-deny matches the
     *                           legacy /http/error_update.php behaviour where anyone but an admin
     *                           saw the permission notice; the same user is forwarded to the
     *                           per-row renderer so each status-change link carries the right context
     * @param array $url_arr the url vars of the calling page, forwarded to each row's
     *                       status-change link so navigation is preserved
     * @return string the HTML body for the error_update page
     */
    function error_update(
        user_message  $msg,
        ?sys_log_list $errors = null,
        ?user_dsp     $usr = null,
        array         $url_arr = []
    ): string
    {
        global $mtr;

        $html = new html_base();

        // default-deny: only an explicit admin sees the issue list; everyone else gets the permission notice
        if ($usr === null or !$usr->is_admin()) {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_PERMISSION_DENIED));
        } elseif ($errors !== null and !$errors->is_empty()) {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_PROGRAM_ISSUES))
                . $errors->get_html($msg, $usr, $url_arr);
        } else {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_NO_OPEN));
        }
        return $result;
    }

    /**
     * the body of a system view that is already part of the system views, but whose data and actions are not yet
     * implemented (see docs/llm/pending.md), so the user gets a hint instead of an empty page
     *
     * @return string the html code of the not yet available hint
     */
    function not_yet_available(): string
    {
        global $mtr;
        $html = new html_base();
        return $html->text_h3($mtr->txt(msg_id::INFO_VIEW_NOT_YET_AVAILABLE));
    }

    /**
     * the tree of the system configuration with the search and the scope of the url,
     * which also an ip user without login can see, but only a logged-in user can change for the own
     * and an admin for all users
     *
     * @param user_message $msg to report a problem of the tree or config load
     * @param array $url_arr the url vars of the page with the search (url_var::PATTERN) and the scope (url_var::CONFIG_PART)
     * @param bool $test_mode true to show the given tree without a backend call
     * @param config_tree $cft the tree to show in the test mode
     * @return string the html code of the configuration tree
     */
    function config_tree(user_message $msg, array $url_arr, bool $test_mode, config_tree $cft = new config_tree()): string
    {
        if (!$test_mode) {
            $cft->load($msg);
        }
        $pattern = (string)($url_arr[url_var::PATTERN] ?? '');
        $scope = (string)($url_arr[url_var::CONFIG_PART] ?? '');
        // the same predicate as for the own page, because a change is saved as the overwrite of the user
        $cft->can_edit = frontend::shows_personal_page($msg);
        $cft->is_admin = $this->is_admin($msg);
        return $cft->html($pattern, $scope);
    }

    /**
     * the jobs of the requesting user with the pending jobs on top and the buttons to downgrade or cancel a job
     *
     * @param user_message $msg with the requesting user to report a problem of the job load
     * @param int $msk_id the id of the shown view, so that a job button returns to this view
     * @param bool $test_mode true to show the given jobs without a backend call
     * @param job_list $jobs the jobs to show in the test mode
     * @return string the html code of the job table
     */
    function user_jobs(user_message $msg, int $msk_id, bool $test_mode, job_list $jobs = new job_list()): string
    {
        if (!$test_mode) {
            $jobs->load_by_user($msg);
        }
        return $jobs->display_with_actions($this->is_admin($msg), $msk_id);
    }

    /**
     * the jobs of all users for an admin with the pending jobs on top and the buttons to up- or downgrade or cancel
     * a job; a user who is not an admin gets the hint that only an admin can see the jobs of all users
     *
     * @param user_message $msg with the requesting user to report a problem of the job load
     * @param int $msk_id the id of the shown view, so that a job button returns to this view
     * @param bool $test_mode true to show the given jobs without a backend call
     * @param job_list $jobs the jobs to show in the test mode
     * @return string the html code of the job table or the admin only hint
     */
    function all_jobs(user_message $msg, int $msk_id, bool $test_mode, job_list $jobs = new job_list()): string
    {
        global $mtr;
        $html = new html_base();
        if ($this->is_admin($msg)) {
            if (!$test_mode) {
                $jobs->load_all($msg);
            }
            $result = $jobs->display_with_actions(true, $msk_id);
        } else {
            $result = $html->text_h3($mtr->txt(msg_id::JOB_LIST_ALL_ONLY_ADMIN));
        }
        return $result;
    }

    /**
     * @param user_message $msg with the requesting user
     * @return bool true if the requesting user is an admin
     */
    private function is_admin(user_message $msg): bool
    {
        $result = false;
        if ($msg->usr != null) {
            $result = $msg->usr->is_admin();
        }
        return $result;
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

    /*
     * admin dashboard
     *
     * each column shows its critical entries directly and the other entries behind a plus sign,
     * like a node of the settings tree, so that the admin sees at once what needs an action
     */

    /**
     * the subtitle of the admin dashboard with the requesting admin, the pod and the creation time of the page,
     * so that a printed or shared dashboard shows from where and from when the entries are
     *
     * @param user_message $msg with the requesting admin
     * @param DateTime|null $now the time of the data snap e.g. fixed by a test; null for the time of the request
     * @return string the html code of the subtitle
     */
    function admin_subtitle(user_message $msg, ?DateTime $now = null): string
    {
        global $mtr;
        global $ui_sys;
        $lib = new library();
        $html = new html_base();
        $now = $now ?? new DateTime();
        $format = $ui_sys?->cfg?->date_time_format() ?? shared_config::DEFAULT_DATE_TIME_FORMAT;
        $txt = $mtr->txt(msg_id::ADMIN_SUBTITLE);
        $txt = $lib->msg_var_replace($txt, msg_id::VAR_USER_NAME, $html->esc($msg->usr?->name() ?? ''));
        $txt = $lib->msg_var_replace($txt, msg_id::VAR_POD_NAME, $html->esc(POD_NAME));
        $txt = $lib->msg_var_replace($txt, msg_id::VAR_TIME, $now->format($format));
        return $html->text_h3($txt);
    }

    /**
     * the slowest user requests of the configured last days: a request that took at least the configured
     * warning time is critical and the other written requests are shown behind the plus sign
     *
     * @param user_message $msg with the requesting admin to report a problem of the load or the config lookup
     * @param bool $test_mode true to show the given request times without a backend call
     * @param system_time_list $times the request times to show in the test mode
     * @return string the html code of the slowest user requests of the admin dashboard
     */
    function admin_url_delay(user_message $msg, bool $test_mode, system_time_list $times = new system_time_list()): string
    {
        global $mtr;
        if (!$test_mode) {
            $times = new system_time_list();
            $times->load($msg);
        }
        $limit = system_time_list::critical_limit($msg);
        $size = sys_log_list::display_limit($msg);
        $sorted = $times->slowest_first();
        $critical = $sorted->critical(true, $limit);
        $more = $sorted->critical(false, $limit);
        return $this->admin_split(
            msg_id::ADMIN_TITLE_URL_DELAY,
            $critical->is_empty() ? '' : $critical->head($size)->display(),
            $more->is_empty() ? '' : $more->head($size)->display(),
            $more->count(),
            $mtr->txt(msg_id::ADMIN_NO_SLOW_REQUESTS));
    }

    /**
     * the users with the most server time of the configured last days: a user that has reached at least one
     * of the configured thresholds is critical and the other top users are shown behind the plus sign
     *
     * @param user_message $msg with the requesting admin to report a problem of the load or the config lookup
     * @param bool $test_mode true to show the given summed sessions without a backend call
     * @param user_log_sum_list $usr_log_sum_lst the summed sessions per user to show in the test mode
     * @return string the html code of the users with the most server time of the admin dashboard
     */
    function admin_user_load(
        user_message      $msg,
        bool              $test_mode,
        user_log_sum_list $usr_log_sum_lst = new user_log_sum_list()
    ): string
    {
        global $mtr;
        if (!$test_mode) {
            $usr_log_sum_lst = new user_log_sum_list();
            $usr_log_sum_lst->load($msg);
        }
        $limits = user_log_sum_list::critical_limits($msg);
        $sorted = $usr_log_sum_lst->most_first();
        $critical = $sorted->critical(true, $limits);
        $more = $sorted->critical(false, $limits);
        // the backend sends only the configured top users, so all of them are shown
        return $this->admin_split(
            msg_id::ADMIN_TITLE_USER_LOAD,
            $critical->is_empty() ? '' : $critical->display(),
            $more->is_empty() ? '' : $more->display(),
            $more->count(),
            $mtr->txt(msg_id::ADMIN_NO_USER_LOAD));
    }

    /**
     * the failed logins grouped by the typed name: a name with at least the configured number of failed logins
     * is critical, because it can be a brute force attack
     *
     * @param user_message $msg with the requesting admin to report a problem of the load
     * @param bool $test_mode true to show the given log entries without a backend call
     * @param sys_log_list $errors the not closed log entries to show in the test mode
     * @return string the html code of the failed logins of the admin dashboard
     */
    function admin_login_fails(user_message $msg, bool $test_mode, sys_log_list $errors = new sys_log_list()): string
    {
        global $mtr;
        if (!$test_mode) {
            // a fresh list, because the given list is shared by the columns of the page
            $errors = new sys_log_list();
            $errors->load_by_user(sys_log_list::DSP_ALL, $msg, sys_log_list::ADMIN_READ_SIZE);
        }
        log_debug('admin login fails of ' . $errors->count() . ' log entries', url_var::DEBUG_LEVEL_MAIN_STEP);
        $limit = sys_log_list::login_fail_limit($msg);
        $size = sys_log_list::display_limit($msg);
        $critical = [];
        $more = [];
        foreach ($errors->login_fail_groups() as $group) {
            if ($group[sys_log_list::GROUP_COUNT] >= $limit) {
                $critical[] = $group;
            } else {
                $more[] = $group;
            }
        }
        return $this->admin_split(
            msg_id::ADMIN_TITLE_LOGIN_FAILS,
            $this->admin_login_fail_table(array_slice($critical, 0, $size)),
            $this->admin_login_fail_table(array_slice($more, 0, $size)),
            count($more),
            $mtr->txt(msg_id::ADMIN_NO_LOGIN_FAILS));
    }

    /**
     * the new system errors without a solver, the most critical and then the newest first;
     * the failed logins have their own column
     *
     * @param user_message $msg with the requesting admin to report a problem of the load
     * @param bool $test_mode true to show the given errors without a backend call
     * @param sys_log_list $errors the not closed errors to show in the test mode
     * @return string the html code of the unassigned errors of the admin dashboard
     */
    function admin_errors_unassigned(user_message $msg, bool $test_mode, sys_log_list $errors = new sys_log_list()): string
    {
        if (!$test_mode) {
            // a fresh list, because the given list is shared by the columns of the page
            $errors = new sys_log_list();
            $errors->load_by_user(sys_log_list::DSP_ALL, $msg, sys_log_list::ADMIN_READ_SIZE);
        }
        $err_lst = $errors->login_fails(false)->filtered([sys_log_statuum::OPEN], true)->critical_first(true);
        return $this->admin_errors(msg_id::ADMIN_TITLE_ERRORS_UNASSIGNED, $err_lst, $msg);
    }

    /**
     * the system errors that a developer has taken or marked as solved but that are not yet closed,
     * the most critical and then the longest not updated first
     *
     * @param user_message $msg with the requesting admin to report a problem of the load
     * @param bool $test_mode true to show the given errors without a backend call
     * @param sys_log_list $errors the not closed errors to show in the test mode
     * @return string the html code of the pending error fixes of the admin dashboard
     */
    function admin_errors_delayed_fix(user_message $msg, bool $test_mode, sys_log_list $errors = new sys_log_list()): string
    {
        if (!$test_mode) {
            // a fresh list, because the given list is shared by the columns of the page
            $errors = new sys_log_list();
            $errors->load_by_user(sys_log_list::DSP_ALL, $msg, sys_log_list::ADMIN_READ_SIZE);
        }
        $pending = [sys_log_statuum::ASSIGNED, sys_log_statuum::RESOLVED];
        $err_lst = $errors->login_fails(false)->filtered($pending, false)->critical_first(false);
        return $this->admin_errors(msg_id::ADMIN_TITLE_ERRORS_DELAYED_FIX, $err_lst, $msg);
    }

    /**
     * the open system jobs with the longest waiting first: a job that has waited longer than the configured
     * max delay is critical
     *
     * @param user_message $msg with the requesting admin to report a problem of the job load
     * @param bool $test_mode true to show the given jobs without a backend call
     * @param job_list $jobs the jobs to show in the test mode; without an open job the column shows only
     *                       a text without a table like the other columns
     * @param DateTime|null $now the time of the data snap e.g. fixed by a test; null for the time of the request
     * @return string the HTML code of the delayed-jobs column
     */
    function admin_jobs_delayed(
        user_message $msg,
        bool         $test_mode,
        job_list     $jobs = new job_list(),
        ?DateTime    $now = null
    ): string
    {
        global $mtr;
        $html = new html_base();
        if (!$test_mode) {
            // a fresh list, because the given list is part of the page context
            $jobs = new job_list();
            $jobs->load_all($msg);
        }
        $now = $now ?? new DateTime();
        $max_delay = job_list::max_delay($msg);
        $critical = '';
        $more = '';
        $more_count = 0;
        foreach ($jobs->open_oldest_first() as $job_obj) {
            if ($job_obj->is_delayed($now, $max_delay)) {
                $critical .= $html->tr($job_obj->display());
            } else {
                $more .= $html->tr($job_obj->display());
                $more_count++;
            }
        }
        // job::header() already returns a full <tr><th>…</th></tr> row, so a fresh job instance renders the header
        $header = new job()->header();
        return $this->admin_split(
            msg_id::ADMIN_TITLE_JOBS_DELAYED,
            $critical == '' ? '' : $html->tbl($header . $critical),
            $more == '' ? '' : $html->tbl($header . $more),
            $more_count,
            $mtr->txt(msg_id::ADMIN_NO_DELAYED_JOBS));
    }

    /**
     * @param msg_id $title the headline of the section
     * @param sys_log_list $err_lst the selected and sorted errors
     * @param user_message $msg to report a problem of the config lookup
     * @return string the html code of the error column with the critical errors and the others behind a plus sign
     */
    private function admin_errors(msg_id $title, sys_log_list $err_lst, user_message $msg): string
    {
        global $mtr;
        $size = sys_log_list::display_limit($msg);
        $critical = $err_lst->critical(true);
        $more = $err_lst->critical(false);
        return $this->admin_split(
            $title,
            $critical->is_empty() ? '' : $critical->head($size)->display(),
            $more->is_empty() ? '' : $more->head($size)->display(),
            $more->count(),
            $mtr->txt(msg_id::ADMIN_NO_ERRORS));
    }

    /**
     * @param array $groups the failed logins grouped by the typed name (see sys_log_list::login_fail_groups)
     * @return string the html table of the failed logins or an empty string if there is none
     */
    private function admin_login_fail_table(array $groups): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';
        if (count($groups) > 0) {
            $rows = $html->tr($html->th($mtr->txt(msg_id::ADMIN_HEAD_USER_NAME))
                . $html->th($mtr->txt(msg_id::ADMIN_HEAD_ATTEMPTS))
                . $html->th($mtr->txt(msg_id::ADMIN_HEAD_LAST_TRY)));
            foreach ($groups as $group) {
                $rows .= $html->tr($html->td($html->esc($group[sys_log_list::GROUP_NAME]))
                    . $html->td((string)$group[sys_log_list::GROUP_COUNT])
                    . $html->td($group[sys_log_list::GROUP_LAST]?->format(DateTimeInterface::ATOM) ?? ''));
            }
            $result = $html->tbl($rows);
        }
        return $result;
    }

    /**
     * one column of the admin dashboard with the critical entries shown directly and the other entries behind
     * a plus sign, which opens without javascript like a node of the settings tree
     *
     * @param msg_id $title the headline of the column
     * @param string $critical the html code of the critical entries or an empty string if there is none
     * @param string $more the html code of the not critical entries or an empty string if there is none
     * @param int $more_count the number of the not critical entries, which can be more than the shown entries
     * @param string $none the html code shown if the column has no entry at all
     * @return string the html code of the column
     */
    private function admin_split(msg_id $title, string $critical, string $more, int $more_count, string $none): string
    {
        global $mtr;
        // one debug line per finished column to see with '&debug=9' which column stops the admin page
        log_debug('admin column ' . $title->value . ' done', url_var::DEBUG_LEVEL_MAIN_STEP);
        $html = new html_base();
        $body = $critical;
        if ($body == '' and $more_count > 0) {
            $body = $mtr->txt(msg_id::ADMIN_NO_CRITICAL);
        } elseif ($body == '') {
            $body = $none;
        }
        if ($more_count > 0) {
            $summary = $more_count . ' ' . $mtr->txt(msg_id::ADMIN_NOT_CRITICAL);
            $body .= $html->details($summary, $more, styles::ADMIN_MORE);
        }
        return $this->admin_section($title, $body);
    }

    /**
     * @param msg_id $title the headline of the section
     * @param string $body the html code of the section
     * @return string the html code of one column of the admin dashboard
     */
    private function admin_section(msg_id $title, string $body): string
    {
        global $mtr;
        $html = new html_base();
        // the column limits its tables to its width, so that e.g. a long url does not widen the column
        return $html->div($html->text_h4($mtr->txt($title)) . $body, styles::ADMIN_COLUMN);
    }


    /*
     * process catch
     */

    /**
     * @param msg_id|null $ui_msg_code_id the message id of the text e.g. the explain text of a process catch page
     * @return string the translated text as a paragraph or an empty string if the component has no text
     */
    function text_explain(?msg_id $ui_msg_code_id): string
    {
        global $mtr;
        $result = '';
        if ($ui_msg_code_id != null) {
            $result = new html_base()->p($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    /**
     * the name of the view as the page title and its description as the lead text, e.g. of a use
     * case page; the view of the page is in the request cache (see view_exe::show)
     *
     * @param int $msk_id the database id of the view that shows the component
     * @param data_object|null $cfg the request cache with the view of the page
     * @return string the html code of the title and the lead text or an empty string if the view is unknown
     */
    function view_title(int $msk_id, ?data_object $cfg): string
    {
        $result = '';
        $msk = $cfg?->msk;
        if ($msk != null and $msk->id() == $msk_id) {
            $html = new html_base();
            // the same heading as the phrase title, so the page title is bigger than its sections
            $result = $html->text_h2($msk->name()) . $html->p($msk->get_description());
        }
        return $result;
    }

    /**
     * the title of a calculator page: the calculator label of the page phrase, or of the phrase it
     * is built from, else the phrase name, and the calculator hint as the lead text
     * (rule 6 of docs/llm/new_view_sample_pv_balance.md)
     *
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the phrase of the page
     * @param data_object|null $cfg the request cache with the calculator triples of the page
     * @return string the html code of the title or an empty string if the page has no phrase
     */
    function calculator_title(
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        ?data_object                                          $cfg
    ): string
    {
        $result = '';
        if (($dbo instanceof word or $dbo instanceof triple) and $dbo->name() != '') {
            $html = new html_base();
            $calc = new calculator($dbo->phrase(), $cfg?->phr_lst ?? new phrase_list());
            // escape the user settable texts, because the title is shown as raw html;
            // a h4, because the use case style of style_html.css formats the h4 of the header
            $result = $html->block(html_base::H4, $html->esc($calc->title()));
            if ($calc->lead() != '') {
                $result .= $html->p($html->esc($calc->lead()));
            }
        }
        return $result;
    }

    /**
     * the input panel of a calculator page (docs/llm/new_view_sample_pv_balance.md section 3.1):
     * the heading, the input fields and the save button in the inputs frame, then a collapsed
     * block with the assumptions that the calculator lists and after them the other values that
     * the results of the page use; the headings are the labels of the groups, else the default text
     *
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object|null $cfg the request cache with the calculator triples and the values of the page
     * @param string $form_name the name of the save form that the fields join
     * @param string|int $msk_key the code id or the id of the view that the save shows again
     * @param array $url_arr the url parameters of the page with the entered numbers
     * @return string the html code of the inputs or an empty string if the calculator has no input and no assumption
     */
    function calculator_input(
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        user_message                                          $msg,
        ?data_object                                          $cfg,
        string                                                $form_name,
        string|int                                            $msk_key,
        array                                                 $url_arr
    ): string
    {
        global $mtr;
        $result = '';
        if (($dbo instanceof word or $dbo instanceof triple) and $cfg != null) {
            $html = new html_base();
            $list = new ui_list();
            $calc = new calculator($dbo->phrase(), $cfg->phr_lst);
            $inputs = $calc->items(triples::CALCULATOR_INPUT);
            $assumptions = $calc->items(triples::CALCULATOR_ASSUMPTION);
            if ($inputs != [] or $assumptions != []) {
                $heading = $calc->own_label($calc->group(triples::CALCULATOR_INPUT))
                    ?: $mtr->txt(msg_id::CALCULATOR_INPUTS);
                $result = $html->div($html->block(html_base::H2, $html->esc($heading))
                    . $list->calculator_fields($calc, $inputs, $dbo, $msg, $cfg, $form_name, $url_arr)
                    . $this->calculator_button_save($form_name, $msk_key, $dbo),
                    styles::INPUTS);
                $listed = array_map(fn($item) => $calc->item_phrase($item)?->name(), $assumptions);
                $fields = $list->calculator_fields($calc, $assumptions, $dbo, $msg, $cfg, $form_name, $url_arr, true)
                    . $list->assumption_fields($dbo, $msg, $cfg, $form_name, $url_arr, $listed);
                if ($fields != '') {
                    $summary = $calc->own_label($calc->group(triples::CALCULATOR_ASSUMPTION))
                        ?: $mtr->txt(msg_id::CALCULATOR_ASSUMPTIONS);
                    $result .= $html->details($html->esc($summary), $fields);
                }
            }
        }
        return $result;
    }

    /**
     * the button that saves the changed numbers of a calculator page and then shows the page again
     * (see frontend::action_calculator_save); a post form, because it changes data, which every
     * number field joins by its form attribute
     *
     * @param string $form_name the id of the form that the fields of the page join
     * @param string|int $msk_key the code id or the id of the view that the save shows again
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the phrase of the page
     * @return string the html code of the save form or an empty string if the component is shown on no page
     */
    private function calculator_button_save(
        string                                                $form_name,
        string|int                                            $msk_key,
        db_object|type_object|combine_named|sandbox_list|null $dbo
    ): string
    {
        global $mtr;
        $result = '';
        // e.g. a preview of the component has no page to save
        if ($msk_key != '' and $msk_key != 0) {
            $html = new html_base();
            $page_vars = $html->form_hidden(url_var::MASK, (string)$msk_key);
            $page_vars .= $html->form_hidden(url_var::ID, (string)$this->process_catch_phrase_id($dbo));
            $page_vars .= $html->form_hidden(url_var::ACTION, url_var::ACTION_SAVE);
            $button = $html->button_bs($html->esc($mtr->txt(msg_id::CALCULATOR_SAVE)),
                styles::PROCESS_CATCH_BUTTON_REFRESH, '', url_var::POST_SUBMIT);
            $result = $html->form_post($form_name, $page_vars . $button);
        }
        return $result;
    }

    /**
     * the result panel of a calculator page (docs/llm/new_view_sample_pv_balance.md section 3.1):
     * the label of the result columns as the heading, the main table and then a collapsed block
     * with the detail tables, the notes of the formulas and the numbered sources; the summary of
     * the block is the label of the detail tables, else the default text
     *
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load or a stored result that differs
     * @param data_object|null $cfg the request cache with the calculator triples, the values and the results of the page
     * @return string the html code of the results or an empty string if the calculator has no result
     */
    function calculation_result(
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        user_message                                          $msg,
        ?data_object                                          $cfg
    ): string
    {
        global $mtr;
        $result = '';
        $calc = null;
        if (($dbo instanceof word or $dbo instanceof triple) and $cfg != null) {
            $calc = new calculator($dbo->phrase(), $cfg->phr_lst);
        }
        // a phrase without calculator triples loads no result, because it shows none
        if ($calc?->is_calculator()) {
            $html = new html_base();
            $list = new ui_list();
            $lst = $list->calculator_numbers($dbo, $cfg, $msg);
            $table = $list->calculation_table($calc, $dbo, $lst, $msg);
            if ($table != '') {
                $heading = $calc->own_label($calc->group(triples::CALCULATOR_RESULT_COLUMN));
                $result = ($heading == '' ? '' : $html->block(html_base::H2, $html->esc($heading))) . $table;
            }
            $notes = $list->formula_notes($dbo, $msg, $cfg);
            $more = $list->calculation_detail_tables($calc, $dbo, $lst, $msg)
                . ($notes == '' ? '' : $html->div($notes, styles::CALC_NOTES))
                . $list->calculator_sources($calc, $dbo, $msg, $cfg);
            if ($more != '') {
                $summary = $calc->own_label($calc->group(triples::CALCULATOR_DETAIL_TABLE))
                    ?: $mtr->txt(msg_id::CALCULATOR_DETAILS);
                $result .= $html->details($html->esc($summary), $html->div($more, styles::CALC_MORE));
            }
            // the only page with javascript: it repeats the calculation when an input changes
            if ($this->calculator_uses_javascript($msg)) {
                $result .= $list->calculator_script($calc, $dbo, $lst, $cfg, $msg);
            }
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of the config value
     * @return bool true if config.yaml allows javascript for the calculator pages (user > default >
     *              frontend > use javascript > for calculator), the default of config.yaml if the value is missing
     */
    private function calculator_uses_javascript(user_message $msg): bool
    {
        global $ui_sys;
        $default = (int)shared_config::DEFAULT_CALCULATOR_JAVASCRIPT;
        $flag = $ui_sys?->cfg?->get_by([triples::USE_JAVASCRIPT, triples::FOR_CALCULATOR], $msg, $default) ?? $default;
        return (bool)(float)$flag;
    }

    /**
     * the input fields of a process catch page or of one of its tabs; a placeholder with the name of
     * the component until the fields are read from the field triples of the question phrase
     *
     * @param string $cmp_name the name of the component that names the page or the tab of the fields
     * @param string|null $cmp_code_id the code id of the component that names the tab of the fields
     *                                 e.g. components::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS or null on a page without tabs
     * @param array $url_arr the url vars of the page with the selected tab
     * @return string the html code of the fields or an empty string if the fields belong to a tab that is not selected
     */
    function process_catch_fields(
        string                                                $cmp_name,
        ?string                                               $cmp_code_id,
        array                                                 $url_arr,
        db_object|type_object|combine_named|sandbox_list|null $dbo = null,
        ?data_object                                          $dto = null,
        string                                                $form_name = '',
        user_message                                          $msg = new user_message()
    ): string
    {
        global $mtr;
        $result = '';
        $nbr = $this->process_catch_tab_nbr($cmp_code_id);
        // the fields of a tab are only shown if the tab is selected, so the page shows one tab at a time
        if ($nbr == 0 or $nbr == $this->process_catch_tab_selected($url_arr)) {
            $html = new html_base();
            $result = new ui_list()->input_fields($dbo, $msg, $dto, $form_name, $url_arr);
            // a page whose phrase has no field yet says so instead of showing nothing
            if ($result == '') {
                $result = $html->p($mtr->txt(msg_id::PROCESS_CATCH_FIELDS_PENDING) . ' ' . $html->esc($cmp_name));
            }
        }
        return $result;
    }

    /**
     * one tab of a process catch page: the selected tab as text and every other tab as a link to the
     * same page and question phrase with the tab selected, so the page needs no javascript
     *
     * @param string|null $cmp_code_id the code id of the tab component e.g. components::PROCESS_CATCH_DETAIL_TAB_TWO
     * @param string $msk_code_id the code id of the process catch page that shows the tab
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of the page
     * @param array $url_arr the url vars of the page with the selected tab
     * @return string the html code of the tab or an empty string if the component names no tab
     */
    function process_catch_tab(
        ?string                                               $cmp_code_id,
        string                                                $msk_code_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        array                                                 $url_arr
    ): string
    {
        global $mtr;
        $result = '';
        $nbr = $this->process_catch_tab_nbr($cmp_code_id);
        if ($nbr > 0) {
            $html = new html_base();
            $label = $mtr->txt(self::PROCESS_CATCH_TABS[$nbr - 1]);
            if ($nbr == $this->process_catch_tab_selected($url_arr)) {
                $result = $html->span($html->esc($label), styles::PROCESS_CATCH_TAB_SELECTED);
            } else {
                $url = $html->url_back($msk_code_id, $this->process_catch_phrase_id($dbo), [],
                    url_var::TAB . url_var::EQ . $nbr);
                $result = $html->ref($url, $label, '', styles::PROCESS_CATCH_TAB);
            }
        }
        return $result;
    }

    /**
     * @param string $msk_code_id the code id of the process catch page that shows the button
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of the page
     * @return string the html code of the button that opens the next page or an empty string on the last page
     */
    function process_catch_button_next(
        string                                                $msk_code_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo
    ): string
    {
        return $this->process_catch_button(views::PROCESS_CATCH_NEXT, msg_id::SYSTEM_BUTTON_NEXT,
            styles::PROCESS_CATCH_BUTTON_NEXT, $msk_code_id, $dbo);
    }

    /**
     * @param string $msk_code_id the code id of the process catch page that shows the button
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of the page
     * @return string the html code of the button that opens the page before or an empty string on the first page
     */
    function process_catch_button_back(
        string                                                $msk_code_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo
    ): string
    {
        return $this->process_catch_button(views::PROCESS_CATCH_BACK, msg_id::SYSTEM_BUTTON_BACK,
            styles::PROCESS_CATCH_BUTTON_BACK, $msk_code_id, $dbo);
    }

    /**
     * the button that shows the process catch page again with the entered values e.g. to calculate the
     * result with the changed assumptions; a form submit, because only a submit sends the inputs
     *
     * @param msg_id $label the text of the button e.g. msg_id::PROCESS_CATCH_BUTTON_REFRESH
     * @param string $form_name the id of the form that the inputs of the page join e.g. the code id of the page
     * @param string $msk_code_id the code id of the process catch page that is shown again
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of the page
     * @param array $url_arr the url vars of the page with the selected tab, which the refresh keeps if the user has selected one
     * @return string the html code of the button or an empty string if the component is shown on no page
     */
    function process_catch_button_refresh(
        msg_id                                                $label,
        string                                                $form_name,
        string|int                                            $msk_key,
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        array                                                 $url_arr
    ): string
    {
        global $mtr;
        $result = '';
        // e.g. a preview of the component has no page to show again
        if ($msk_key != '' and $msk_key != 0) {
            $html = new html_base();
            $page_vars = $html->form_hidden(url_var::MASK, (string)$msk_key);
            $page_vars .= $html->form_hidden(url_var::ID, (string)$this->process_catch_phrase_id($dbo));
            // the tab is only kept if the user has selected one, so a page without tabs gets no tab var
            if (array_key_exists(url_var::TAB, $url_arr)) {
                $page_vars .= $html->form_hidden(url_var::TAB, (string)$this->process_catch_tab_selected($url_arr));
            }
            $button = $html->button($html->esc($mtr->txt($label)), html_base::INPUT_SUBMIT,
                styles::PROCESS_CATCH_BUTTON_REFRESH);
            $result = $html->form_get($form_name, $page_vars . $button);
        }
        return $result;
    }

    /**
     * the input of a place by its name or postal code; it joins the form of the refresh button by the
     * form attribute, so the entered place is shown again after the refresh
     *
     * @param msg_id $label the label of the input e.g. msg_id::GEOLOCATOR_LABEL
     * @param string $form_name the id of the form that sends the input e.g. the code id of the page
     * @param array $url_arr the url vars of the page with the place entered before
     * @return string the html code of the place input with its label
     */
    function geolocator(msg_id $label, string $form_name, array $url_arr): string
    {
        $place = $url_arr[url_var::PLACE] ?? '';
        return new html_base()->form_field(url_var::PLACE, $label, $place, form_name: $form_name);
    }

    /**
     * the button that opens the next or the page before of a process catch for the same question phrase
     *
     * @param array $targets the page that the button opens by the code id of the page e.g. views::PROCESS_CATCH_NEXT
     * @param msg_id $label the text of the button e.g. msg_id::SYSTEM_BUTTON_NEXT
     * @param string $style the css class of the button e.g. styles::PROCESS_CATCH_BUTTON_NEXT
     * @param string $msk_code_id the code id of the process catch page that shows the button
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of the page
     * @return string the html code of the button or an empty string if the page has no page to open
     */
    private function process_catch_button(
        array                                                 $targets,
        msg_id                                                $label,
        string                                                $style,
        string                                                $msk_code_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo
    ): string
    {
        global $mtr;
        $result = '';
        // e.g. the result page has no next page, so a misplaced next button is not shown
        if (array_key_exists($msk_code_id, $targets)) {
            $html = new html_base();
            $url = $html->url_back($targets[$msk_code_id], $this->process_catch_phrase_id($dbo));
            $result = $html->ref($url, $mtr->txt($label), '', $style);
        }
        return $result;
    }

    /**
     * @param string|null $cmp_code_id the code id of a tab component or of the fields of a tab
     * @return int the number of the tab starting with 1 or 0 if the component belongs to no tab
     */
    private function process_catch_tab_nbr(?string $cmp_code_id): int
    {
        return components::PROCESS_CATCH_TAB_NBR[$cmp_code_id ?? ''] ?? 0;
    }

    /**
     * @param array $url_arr the url vars of the page
     * @return int the number of the tab that the url selects or the first tab if the url selects none or an unknown tab
     */
    private function process_catch_tab_selected(array $url_arr): int
    {
        $result = (int)($url_arr[url_var::TAB] ?? self::PROCESS_CATCH_TAB_DEFAULT);
        // e.g. an edited link with tab 9 would else hide the fields of every tab
        if ($result < 1 or $result > count(self::PROCESS_CATCH_TABS)) {
            $result = self::PROCESS_CATCH_TAB_DEFAULT;
        }
        return $result;
    }

    /**
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the question phrase of a process catch page
     * @return int the phrase id that selects the question phrase in the url, which is negative for a triple
     */
    private function process_catch_phrase_id(db_object|type_object|combine_named|sandbox_list|null $dbo): int
    {
        $result = 0;
        if ($dbo instanceof word or $dbo instanceof triple) {
            $result = $dbo->phrase()->id();
        }
        return $result;
    }

}
