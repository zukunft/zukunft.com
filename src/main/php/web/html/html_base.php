<?php

/*

    web/html/html_base.php - function to create the basic HTML elements used for zukunft.com
    ----------------------

    depending on the settings either pure HTML, BOOTSTRAP HTML or vue.js code is created

    The main sections of this object are
    - internal:          html code functions that are used only by this class



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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::TYPES . 'language_list.php';
include_once html_paths::CONST . 'icons.php';
include_once html_paths::HTML . 'view_menu.php';
include_once html_paths::SHARED . 'group_id_url.php';
//include_once html_paths::USER . 'user_message.php';
//include_once html_paths::SHARED_CONST . 'def.php';
//include_once html_paths::SHARED_CONST . 'files.php';
//include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
//include_once html_paths::SHARED_CONST . 'triples.php';
//include_once html_paths::SHARED_CONST . 'views.php';
//include_once html_paths::SHARED_CONST . 'words.php';
//include_once html_paths::SHARED_ENUM . 'languages.php';
//include_once html_paths::SHARED_ENUM . 'messages.php';
//include_once html_paths::SHARED_HELPER . 'Message.php';
//include_once html_paths::SHARED_TYPES . 'position_types.php';
//include_once html_paths::SHARED_TYPES . 'view_styles.php';
//include_once html_paths::SHARED . 'api.php';
//include_once html_paths::SHARED . 'url_var.php';
//include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\group_id_url;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\types\language_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class html_base
{

    // TODO Prio 2 move all html const used in zukunft.com to html_names

    // fixed elements
    const string TOGGLE_TOOLTIP = 'data-toggle="tooltip"';

    // the html input types used
    const string INPUT_TEXT = 'text';
    const string INPUT_NUMBER = 'text';
    const string INPUT_INT = 'text';
    // TODO Prio 2 add frontend validation checks
    const string INPUT_PERCENT = 'text';
    const string INPUT_SUBMIT = 'submit';
    const string INPUT_SEARCH = 'search';
    const string INPUT_CHECKBOX = 'checkbox';
    const string INPUT_FILE = 'file';
    const string INPUT_HIDDEN = 'hidden';
    const string INPUT_PASSWORD = 'password';
    const string INPUT_EMAIL = 'email'; // to validate the email in the frontend
    // the autocomplete tokens so a password manager still recognises a masked text password input
    const string AUTOCOMPLETE = 'autocomplete';
    const string AUTOCOMPLETE_CURRENT_PW = 'current-password';
    const string AUTOCOMPLETE_NEW_PW = 'new-password';
    // the id prefix of the css-only "show password" checkbox that reveals a password field
    const string SHOW_PASSWORD_PREFIX = 'show_';

    // bootstrap const string used in zukunft.com
    const string BS_FORM = 'form-control';
    const string BS_BTN = 'btn btn-space col-1';
    // a borderless button that shows only an icon, e.g. the refresh icon beside a form field label
    const string BS_BTN_ICON = 'btn btn-link p-0 ms-1 align-baseline';
    // align an element beside a form field to the bottom, so that it is in line with the input and not the label
    const string BS_ALIGN_BOTTOM = 'align-self-end';
    // the column of a text button beside a form field: the button is aligned to the bottom like the input
    // and fills the column, so that the button margin sits inside the column and not on it
    const string BS_BTN_FIELD_COL = 'col-1 d-flex align-items-end';
    const string BS_BTN_FIELD = 'btn btn-outline-success w-100';
    const string BS_BTN_SUCCESS = 'btn-outline-success';
    const string BS_BTN_CANCEL = 'btn-outline-secondary';
    // between the tooltip of an action and the reason why the action is blocked (see change_style_and_tip)
    const string TOOLTIP_SEPARATOR = ': ';
    const string BS_BTN_DEL = 'btn-outline-secondary';
    const string BS_BTN_IMPORT = 'btn-outline-secondary';
    const string BS_BTN_EXPORT = 'btn-outline-secondary';

    // TODO Prio 3 move the user interface setting to the user page, so that he can define which UI he wants to use
    const int UI_USE_BOOTSTRAP = 1; // IF FALSE a simple HTML frontend without javascript is used

    const string METHOD_POST = 'post';

    const string SIZE_FULL = 'full';
    const string SIZE_HALF = 'half';

    const string WIDTH_FULL = '800px';
    const string WIDTH_HALF = '400px';

    // all html code elements used
    const string DOC_HTML = '<!DOCTYPE html>';
    const string LANG = 'lang';
    const string META = 'meta';
    const string NAME = 'name';
    const string CONTENT = 'content';
    const string CHARSET = 'charset';
    const string HEAD = 'head';
    const string BODY = 'body';
    const string MAIN = 'main';
    const string FOOTER = 'footer';
    const string LINK = 'link';
    const string REL = 'rel';
    const string STYLESHEET = 'stylesheet';
    const string A = 'a';
    const string HREF = 'href';
    const string NAV = 'nav';
    const string IMG = 'img';
    const string SRC = 'src';
    const string ALT = 'alt';
    const string STYLE = 'style';
    // the config.yaml keys of the layout settings font, mode and colours (see theme_style), top down
    // as the confirmation of the mode switch names them
    const array LAYOUT_PATH = [words::USER, words::DEFAULT, words::FRONTEND, words::LAYOUT];
    // the config.yaml keys of the dark or light mode of the user (see theme_mode and theme_switch)
    const array MODE_PATH = [...html_base::LAYOUT_PATH, words::MODE];
    // the config.yaml keys above the colour groups page, editor and chart (see theme_style)
    const array COLOUR_PATH = [...html_base::LAYOUT_PATH, words::COLOURS];
    const string CLASS_HTML = 'class';
    const string TITLE_HTML = 'title'; // title attribute e.g. for tooltips
    const string TITLE = 'title';      // <title> element in <head>
    const string TYPE = 'type';
    // separates two short fields that share one line e.g. the about and the privacy link of the
    // footer or the last update and the source of a value; the blanks are part of the separator
    const string MIDDLE_DOT = ' &middot; ';
    const string P = 'p';
    const string DIV = 'div';
    // the parts of a use case page: the title on top, the input panel and the result panels
    const string HEADER = 'header';
    const string ASIDE = 'aside';
    const string SECTION = 'section';
    const string UL = 'ul';
    const string OL = 'ol';
    const string LI = 'li';
    const string BUTTON = 'button';
    const string HTML = 'html';
    const string FORM = 'form';
    const string ACTION = 'action';
    const string METHOD = 'method';
    const string ENCTYPE = 'enctype';
    const string INPUT = 'input';
    const string LABEL = 'label';
    const string FOR = 'for';
    const string VALUE = 'value';
    const string ID = 'id';
    const string ARIA_LABEL = 'aria-label'; // the accessible name of a control that shows only an icon
    const string PLACEHOLDER = 'placeholder';
    const string BR = 'br';
    const string TABLE = 'table';
    const string TR = 'tr';
    const string TD = 'td';
    const string TH = 'th';
    const string THEAD = 'thead';
    const string TBODY = 'tbody';
    const string SMALL = 'small';
    const string SCOPE = 'scope';
    const string DETAILS = 'details';
    // only for the calculator pages if config.yaml allows javascript for them (docs/llm/frontend.md)
    const string SCRIPT = 'script';
    const string JSON_TYPE = 'application/json';
    const string SUMMARY = 'summary';
    const string DL = 'dl';
    const string DT = 'dt';
    const string DD = 'dd';
    const string MARK = 'mark';
    const string DATALIST = 'datalist';
    const string OPTION = 'option';
    const string LIST = 'list';
    const string POPOVER = 'popover';
    const string POPOVER_TARGET = 'popovertarget';
    const string METHOD_GET = 'get';
    const string I = 'i';
    const string B = 'b';
    const string H1 = 'h1';
    const string H2 = 'h2';
    const string H3 = 'h3';
    const string H4 = 'h4';
    const string H5 = 'h5';
    const string H6 = 'h6';

    // to sort
    const string CLASS_MAIN = 'main-container';
    const string CLASS_FOOTER = 'site-footer';
    // the start of a page shown while the page is created (see loading_start)
    const string CLASS_LOADING = 'page-loading';
    const string LOADING_ANIMATION = 'page-loading-show';
    const string CLASS_LOADING_SECONDS = 'page-loading-seconds';
    const string LOADING_COUNTER = 'page-loading-sec';
    const string LOADING_COUNT_ANIMATION = 'page-loading-count';
    // the seconds up to which the loading text counts; not the php max_execution_time, because php
    // counts only its own cpu time, so a request that waits for the database runs much longer
    const int LOADING_COUNT_LIMIT = 3600;
    const string CLASS_NOTIFICATION = 'alert alert-warning ' . api::USER_MSG_CLASS;
    // green, so that the user sees at once that e.g. a save has worked
    const string CLASS_NOTIFICATION_SUCCESS = 'alert alert-success ' . api::USER_MSG_CLASS;
    const string CLASS_INPUT_SECTION = 'search-section';
    const string CLASS_INPUT = 'standard-input';
    // css-only show-password toggle classes (see the .password / .show-password rules in style_html.css)
    const string CLASS_PASSWORD_FIELD = 'password-field';
    const string CLASS_PASSWORD = 'password';
    const string CLASS_SHOW_PASSWORD = 'show-password';
    const string CLASS_PASSWORD_ICON_SHOW = 'password-icon-show'; // the eye icon shown while the password is masked
    const string CLASS_PASSWORD_ICON_HIDE = 'password-icon-hide'; // the eye-slash icon shown while it is revealed
    const string CLASS_SUBMIT = 'submit-input';
    const string CLASS_BUTTON = 'btn';
    const string CLASS_NAV = 'navbar site-header fixed-top';
    const string CLASS_LOGO = 'navbar-brand';
    const string CLASS_LOGO_BS = 'logo-nav';
    const string CLASS_LOGO_HTML = 'logo-nav';
    const string CLASS_LOGO_BIG = 'logo-big';
    const string CLASS_LOGO_FLEX = 'brand-logo';
    const string CLASS_LOGO_SECTION = 'logo-section';
    // the links to the pinned views below the search field of the start page
    const string CLASS_PINNED_SECTION = 'pinned-section';
    const string CLASS_CONTAINER = 'container';
    // the css-only tab box classes (see tab_box and the matching :target rules in style_html.css)
    const string CLASS_TABS = 'css-tabs';
    const string CLASS_TAB = 'css-tab';
    const string CLASS_TAB_LABEL = 'css-tab-label';
    const string CLASS_TAB_PANE = 'css-tab-pane';
    // the static forward page of e.g. /settings with 1 the main script incl. '?', 2 the view url var
    // and 3 the view id: the script keeps every url var of the request e.g. '9m' or the key of an
    // activation link and sets the view last, so a view var of the request never replaces the target;
    // without javascript the view opens without the url vars of the request
    const string FORWARD_PAGE = "<!DOCTYPE html>\n"
        . "<script>const p = new URLSearchParams(location.search); p.set('%2\$s', '%3\$s');"
        . " location.replace('%1\$s' + p);</script>\n"
        . "<noscript><meta http-equiv=\"refresh\" content=\"0; url=%1\$s%2\$s=%3\$s\"></noscript>\n"
        . "</html>\n";
    // the static forward page of a pinned view e.g. /pv with 1 the complete target url of config.yaml,
    // which needs no javascript, because the url vars of the request are not kept
    const string FORWARD_URL_PAGE = "<!DOCTYPE html>\n"
        . "<meta http-equiv=\"refresh\" content=\"0; url=%1\$s\">\n"
        . "</html>\n";
    // the html comments around the links to the pinned views in the static start page index.html,
    // between which the test run writes the links of config.yaml (see start_page)
    const string PINNED_VIEWS_BEGIN = '<!-- pinned views: written by the test run from config.yaml -->';
    const string PINNED_VIEWS_END = '<!-- pinned views end -->';
    // the html comments in the head of index.html and the optional pages, between which the test run
    // writes the font and the colours of config.yaml, because a static page has no php head (see static_page)
    const string THEME_BEGIN = '<!-- font and colours: written by the test run from config.yaml -->';
    const string THEME_END = '<!-- font and colours end -->';


    /*
     * page
     */

    /**
     * create a simple html page based on header, body and footer html code
     * @param string $lan the language code of the text
     * @param string $head the header HTML code
     * @param string $body the body HTML code
     * @param string $foot the footer HTML code
     * @return string the HTML page code
     */
    function page_html(string $lan, string $head, string $body, string $foot): string
    {
        return self::DOC_HTML . $this->page_lan($head . $body . $foot, $lan);
    }

    /**
     * @param int $msk_id the id of the view that the forward page opens e.g. views::SYSTEM_CONFIG_ID
     * @return string the html code of the static forward page in the docroot e.g. of /settings
     */
    function forward_page(int $msk_id): string
    {
        return sprintf(self::FORWARD_PAGE, api::MAIN_SCRIPT . url_var::PAR, url_var::MASK, $msk_id);
    }

    /**
     * @param string $url the complete url that the forward page opens e.g. the url of a pinned view
     * @return string the html code of the static forward page in the docroot e.g. of /pv
     */
    function forward_url_page(string $url): string
    {
        return sprintf(self::FORWARD_URL_PAGE, htmlspecialchars($url, ENT_QUOTES));
    }

    /**
     * write the font and the colours and the links to the pinned views between the markers of the
     * static start page, so that a changed config.yaml is shown after the next test run
     * (see docs/llm/frontend.md)
     *
     * @param string $page the html code of the start page e.g. of index.html
     * @param array $pin_lst the pinned views by url name, each with the url, name and tooltip of config.yaml
     * @param user_message|Message $msg to report a configured font or colour that css could misread
     * @return string the start page with the style and the links or the unchanged part if its markers are missing
     */
    function start_page(string $page, array $pin_lst, user_message|Message $msg): string
    {
        $links = '';
        foreach ($pin_lst as $pin) {
            $links .= $this->ref($pin[words::URL] ?? '', $pin[words::NAME] ?? '',
                $pin[words::TOOLTIP] ?? '', with_user: false);
        }
        $pinned = $this->div($links, self::CLASS_PINNED_SECTION);
        $result = $this->static_page($page, $msg);
        return $this->static_block($result, self::PINNED_VIEWS_BEGIN, self::PINNED_VIEWS_END, $pinned);
    }

    /**
     * write the font and the colours of config.yaml between the markers in the head of a static
     * page e.g. of optional/ip_reject.html, because a static page has no php head
     *
     * @param string $page the html code of the static page
     * @param user_message|Message $msg to report a configured font or colour that css could misread
     * @return string the page with the style or the unchanged page if the markers are missing
     */
    function static_page(string $page, user_message|Message $msg): string
    {
        return $this->static_block($page, self::THEME_BEGIN, self::THEME_END, $this->theme_style($msg));
    }

    /**
     * replace the html code between two markers of a static page e.g. of index.html
     *
     * @param string $page the html code of the static page
     * @param string $begin_marker the html comment before the replaced code e.g. self::THEME_BEGIN
     * @param string $end_marker the html comment after the replaced code e.g. self::THEME_END
     * @param string $block the new html code between the markers
     * @return string the page with the new code or the unchanged page if the markers are missing
     */
    private function static_block(string $page, string $begin_marker, string $end_marker, string $block): string
    {
        $begin = strpos($page, $begin_marker);
        $end = strpos($page, $end_marker);
        $result = $page;
        if ($begin === false or $end === false or $end < $begin) {
            log_err('the static page has no markers ' . $begin_marker . ' and ' . $end_marker);
        } else {
            $result = substr($page, 0, $begin) . $begin_marker . $block . substr($page, $end);
        }
        return $result;
    }


    /*
     * header & footer
     */

    /**
     * the page header for simple html pages like the login page
     * @param string $title the HTML page title
     * @param string $pod_name the name of this deployment shown in the browser tab
     * @param user_message $msg to collect render errors
     * @return string the HTML head section for simple pages
     */
    function header_html(string $title, string $pod_name, user_message $msg): string
    {
        $txt = $this->charset();
        $txt .= $this->viewport();
        $txt .= $this->title($title, $pod_name, $msg);
        $txt .= $this->stylesheet();
        $txt .= $this->theme_style($msg);
        return $this->head($txt);
    }

    /**
     * wrap the main body tag around html body code
     * @param string $txt the html body code
     * @return string the wrapped body code
     */
    function main_body(string $txt): string
    {
        return $this->body($this->main($txt));
    }

    /**
     * the page footer for simple html pages like the login page
     * @return string the HTML footer section for simple pages
     */
    function footer_html(): string
    {
        $txt = $this->nav($this->about() . ' &middot; ' . $this->privacy());
        $txt .= $this->p($this->foot_text());
        // the same invisible user message placeholder as in the standard footer
        return api::USER_MSG_PLACEHOLDER . $this->foot($txt);
    }

    /**
     * create the html code for the page header
     * @param string $title the HTML page title
     * @param user_message|Message $msg
     * @param string $style CSS class applied to the body tag e.g. center_form for the login page
     * @param string $lan the language html code id
     * @return string the HTML header and opening body tag
     */
    function header(
        string               $title,
        user_message|Message $msg,
        string               $style = "",
        string               $lan = languages::DEFAULT,
        string               $base_url = '',
    ): string
    {
        $result = $this->doctype() . "\n";
        $result .= $this->lang($lan) . "\n";
        $result .= $this->head($this->head_fill($title, $msg, $base_url)) . "\n";
        if (self::UI_USE_BOOTSTRAP) {
            $result .= '<' . self::BODY . '>';
        } else {
            if ($style <> "") {
                $result .= '<' . self::BODY . ' ' . self::CLASS_HTML . '="' . $style . '">';
            } else {
                $result .= '<' . self::BODY . '>' . "\n";
            }
        }

        return $result;
    }

    /**
     * @param string $title the title of the html page
     * @param user_message|Message $msg
     * @return string with the html code for the head section of the html header
     */
    private function head_fill(string $title, user_message|Message $msg, string $base_url = ''): string
    {
        $txt = $this->charset() . "\n";
        $txt .= $this->make_flood() . "\n";
        $txt .= $this->title($title, POD_NAME, $msg) . "\n";
        $txt .= $this->head_style($base_url) . "\n";
        $txt .= $this->theme_style($msg);
        return $txt;
    }

    /**
     * the font and the colours of config.yaml (frontend.layout) as the css variables of style_html.css
     * and the editor styles, so that config.yaml is the only place of the font and the colours: the
     * page colours and the light editor colours for every page, the dark editor colours if the browser
     * asks for the dark mode; a setting that css could misread is reported and the fallback is used
     *
     * @param user_message|Message $msg to report a configured font or colour that css could misread
     * @return string the html style element with the css variables
     */
    function theme_style(user_message|Message $msg): string
    {
        // only the chars of a font family list, so that a setting can end neither the css rule nor the style element
        $font = $this->theme_value([...html_base::LAYOUT_PATH, words::FONT], def::FALLBACK_FONT,
            '/^[A-Za-z0-9 ,"\'_-]+$/', msg_id::CONFIG_FONT_INVALID, $msg);
        [$page_light, $page_dark] = $this->theme_colours(def::FALLBACK_PAGE_COLOURS, [words::PAGE, ...html_base::COLOUR_PATH], $msg);
        [$edit_light, $edit_dark] = $this->theme_colours(def::FALLBACK_EDITOR_COLOURS, [words::EDITOR, ...html_base::COLOUR_PATH], $msg);
        return '<' . self::STYLE . '>' . "\n"
            . ':root { --font-page: ' . $font . '; }' . "\n"
            . $this->theme_css($page_light . ' ' . $edit_light, $page_dark . ' ' . $edit_dark)
            . '</' . self::STYLE . '>' . "\n";
    }

    /**
     * the css of the colours for the dark or light mode of the user (config.yaml frontend.layout.mode):
     * the light colours and if the browser asks for it the dark colours, always the light colours or
     * always the dark colours; with the colour scheme, so that the browser shows its form elements
     * and scroll bars in the same mode
     *
     * @param string $light the css variable definitions of the light mode e.g. "--bg: #eef2f8; ..."
     * @param string $dark the css variable definitions of the dark mode e.g. "--bg: #0e1524; ..."
     * @return string the css rules with the colours of the mode of the user
     */
    function theme_css(string $light, string $dark): string
    {
        $light_css = ':root { ' . $light . ' color-scheme: light; }' . "\n";
        $dark_css = ':root { ' . $dark . ' color-scheme: dark; }' . "\n";
        return match ($this->theme_mode()) {
            def::MODE_LIGHT => $light_css,
            def::MODE_DARK => $dark_css,
            default => $light_css . '@media (prefers-color-scheme: dark) { ' . $dark_css . '}' . "\n",
        };
    }

    /**
     * @return int the dark or light mode of the user e.g. def::MODE_DARK (config.yaml frontend.layout.mode)
     */
    function theme_mode(): int
    {
        global $ui_sys;

        $mode = (int)($ui_sys?->cfg?->value_by(html_base::MODE_PATH)?->number()
            ?? def::FALLBACK_MODE);
        if (!in_array($mode, [def::MODE_BROWSER, def::MODE_LIGHT, def::MODE_DARK])) {
            $mode = def::FALLBACK_MODE;
        }
        return $mode;
    }

    /**
     * the css variables of a colour group of config.yaml with a light and a dark colour per variable
     *
     * @param array $colours the css variable => the config key, the light and the dark fallback e.g. def::FALLBACK_EDITOR_COLOURS
     * @param array $names the config.yaml keys of the colour group e.g. [words::EDITOR, ...html_base::COLOUR_PATH]
     * @param user_message|Message $msg to report a configured colour that css could misread
     * @return array the css variable definitions of the light and of the dark mode e.g. ['--bg: #eef2f8; ...', '--bg: #0e1524; ...']
     */
    function theme_colours(array $colours, array $names, user_message|Message $msg): array
    {
        $light = [];
        $dark = [];
        foreach ($colours as $css_var => [$key, $colour, $dark_colour]) {
            // the dark colour has all names of the light colour, so it must not be taken for the light mode
            $light[] = $css_var . ': ' . $this->theme_colour([$key, ...$names], $colour, $msg, [words::DARK]) . ';';
            $dark[] = $css_var . ': ' . $this->theme_colour([$key, words::DARK, ...$names], $dark_colour, $msg) . ';';
        }
        return [implode(' ', $light), implode(' ', $dark)];
    }

    /**
     * @param array $names the config.yaml keys of the colour e.g. [words::LINK, words::PAGE, words::COLOURS, ...]
     * @param string $fallback the colour if the config has none or one that css could misread
     * @param user_message|Message $msg to report a configured colour that css could misread
     * @param array $without the config.yaml keys that the colour must not have e.g. [words::DARK] for a light colour
     * @return string the css colour e.g. #385eb7, rgba(0, 0, 0, 0.1) or white
     */
    private function theme_colour(array $names, string $fallback, user_message|Message $msg, array $without = []): string
    {
        // a hex colour, a rgb or rgba function or a colour name, so that a setting can end neither the css rule nor the style element
        return $this->theme_value($names, $fallback,
            '/^(#[0-9A-Fa-f]{3,8}|rgba?\([0-9., ]+\)|[A-Za-z]+)$/', msg_id::CONFIG_COLOUR_INVALID, $msg, $without);
    }

    /**
     * @param array $names the config.yaml keys of the setting
     * @param string $fallback the value if the config has none or one that does not match the pattern
     * @param string $pattern the regular expression of a value that css reads as intended
     * @param msg_id $msg_id the message that names a value that does not match the pattern
     * @param user_message|Message $msg to report a value that does not match the pattern
     * @param array $without the config.yaml keys that the setting must not have
     * @return string the configured value or the fallback
     */
    private function theme_value(
        array                $names,
        string               $fallback,
        string               $pattern,
        msg_id               $msg_id,
        user_message|Message $msg,
        array                $without = []
    ): string
    {
        global $ui_sys;

        $result = $ui_sys?->cfg?->text_by($names, $without) ?? $fallback;
        if (preg_match($pattern, $result) !== 1) {
            $msg->add($msg_id, [msg_id::VAR_VALUE => $result]);
            $result = $fallback;
        }
        return $result;
    }

    /**
     * @return string with the html code for the style of the html head
     */
    private function head_style(string $base_url = ''): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            // TODO Prio 3 check if the other bootstrap css also needs to be included
            // include the bootstrap stylesheets
            $txt = $this->stylesheet_bs($base_url) . "\n";
            // include the icon font
            $txt .= $this->stylesheet_font($base_url) . "\n";
            // include the default zukunft.com frontend style
            $txt .= $this->stylesheet($base_url) . "\n";
            // TODO Prio 2 check if still needed
            // include the bootstrap JavaScript plugins
            //$result .= $this->stylesheet_bs_js_all() . "\n";
        } else {
            // use a simple stylesheet without Javascript
            $txt = $this->stylesheet_fallback($base_url) . "\n";
        }
        return $txt;
    }

    /**
     * the html code for the navigation bar
     *
     * @param int $msk_id the mask id used to build the language switch URL
     * @param array $url_array the calling url array
     * @return string the navigation bar HTML code
     */
    /**
     * @param int $msk_id the current view id used to build back params and view-change links
     * @param array $url_array the current request parameters passed through for back-navigation
     * @param string|null $usr_name the logged-in username to display; null when not logged in
     * @param string|null $usr_role the profile/role name to display next to the username; null if not set
     * @param bool $is_admin true to offer the admin dashboard in the user menu
     * @param view_menu $mnu what the page knows for the edit menu e.g. the editor and the views of the shown word
     */
    function navbar(
        int       $msk_id = 0,
        array     $url_array = [],
        ?string   $usr_name = null,
        ?string   $usr_role = null,
        bool      $is_admin = false,
        view_menu $mnu = new view_menu()
    ): string
    {
        global $ui_sys;

        $lan_lst = $ui_sys?->typ_lst_cache?->lan ?? new language_list();
        $html = new html_base();
        $url = $html->url_back($msk_id);
        $lan_txt = $lan_lst->select_list_item($url);

        global $mtr;
        $result = $this->logo() . "\n";
        $result .= '<' . self::FORM . ' ' . self::ACTION . '="' . api::MAIN_SCRIPT . '" ' . self::CLASS_HTML . '="d-flex align-items-center my-2 my-lg-0 flex-grow-1 mx-3">' . "\n";
        // submit the search to the find view as a hidden field so the GET call is e.g. /http/view.php?m=67&pattern=ABB
        // (a query string in the form action would be dropped by the browser on a GET submit)
        $result .= $this->form_hidden(url_var::MASK, (string)views::WORD_FIND_ID) . "\n";
        $result .= $this->form_user();
        $result .= '<' . self::LABEL . ' ' . self::FOR . '="kp" ' . self::CLASS_HTML . '="visually-hidden">' . $mtr->txt(msg_id::NAVBAR_SEARCH) . '</' . self::LABEL . '>' . "\n";
        $result .= '<' . self::INPUT . ' ' . self::CLASS_HTML . '="form-control me-2" ' . self::TYPE . '="search" ' . self::NAME . '="pattern" ' . self::ID . '="kp" ' . self::PLACEHOLDER . '="' . $mtr->txt(msg_id::NAVBAR_SEARCH_PLACEHOLDER) . '" ' . self::STYLE . '="min-width: 40vw; max-width: 800px;">' . "\n";
        $result .= '<' . self::BUTTON . ' ' . self::CLASS_HTML . '="btn btn-outline-primary" ' . self::TYPE . '="submit">' . $mtr->txt(msg_id::NAVBAR_GET_NUMBERS) . '</' . self::BUTTON . '>' . "\n";
        $result .= '</' . self::FORM . '>' . "\n";
        $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="col-md-2">' . "\n";
        $result .= '<' . self::UL . ' ' . self::CLASS_HTML . '="nav navbar-nav">' . "\n";
        $result .= '<' . self::LI . ' ' . self::CLASS_HTML . '="active">' . "\n";
        $result .= '<' . self::DETAILS . ' ' . self::CLASS_HTML . '="view-menu">' . "\n";
        $result .= '<' . self::SUMMARY . '><' . self::I . ' ' . self::CLASS_HTML . '="fas fa-edit"></' . self::I . '></' . self::SUMMARY . '>' . "\n";
        $result .= $this->view_change_list($msk_id, $url_array, $is_admin, $mnu) . "\n";
        $result .= '</' . self::DETAILS . '>' . "\n";
        $result .= '<' . self::DETAILS . ' ' . self::CLASS_HTML . '="lang-menu">' . "\n";
        $result .= '<' . self::SUMMARY . '><' . self::I . ' ' . self::CLASS_HTML . '="fas fa-globe"></' . self::I . '></' . self::SUMMARY . '>' . "\n";
        $result .= $lan_txt . "\n";
        $result .= '</' . self::DETAILS . '>' . "\n";
        $result .= '<' . self::DETAILS . ' ' . self::CLASS_HTML . '="user-menu">' . "\n";
        $usr_tooltip = $usr_name !== null
            ? ($usr_role !== null ? $usr_role . ' ' : '') . $usr_name
            : '';
        // a non-ip user is logged in when the user name is set, which is shown by the dark blue icon
        $usr_icon_class = icons::USER_CIRCLE
            . ($usr_name !== null ? ' ' . styles::USER_LOGGED : '');
        $usr_icon = '<' . self::I . ' ' . self::CLASS_HTML . '="' . $usr_icon_class . '"'
            . ($usr_tooltip !== '' ? ' title="' . htmlspecialchars($usr_tooltip) . '"' : '')
            . '></' . self::I . '>';
        $result .= '<' . self::SUMMARY . '>' . $usr_icon . '</' . self::SUMMARY . '>' . "\n";
        $result .= $this->user_login_list($url_array, $usr_name, $usr_tooltip, $is_admin) . "\n";
        $result .= '</' . self::DETAILS . '>' . "\n";
        $result .= '</' . self::LI . '>' . "\n";
        $result .= '</' . self::UL . '>' . "\n";
        $result .= '</' . self::DIV . '>' . "\n";

        return $this->nav($result, self::CLASS_NAV);
    }

    /**
     * @param int $msk_id the current view id used to build the view change links
     * @param array $url_array the current request parameters used to build back-navigation links
     * @param bool $is_admin true to offer the server admin page at the end
     * @param view_menu $mnu what the page knows for the edit menu e.g. the editor and the views of the shown word
     */
    private function view_change_list(
        int       $msk_id = 0,
        array     $url_array = [],
        bool      $is_admin = false,
        view_menu $mnu = new view_menu()
    ): string
    {
        $result = $mnu->page_items;
        // the editor and the suggested views below one sub header like the entries of the page
        if ($mnu->edt_id != 0 or $mnu->view_items != '') {
            $result .= $this->list_item(msg_id::NAVBAR_ALTERNATIVE_VIEW->text(), styles::MENU_HEADER) . "\n";
        }
        if ($mnu->edt_id != 0) {
            // the editor shows the same object, so it gets the page vars of the current page
            $edt_par = [url_var::MASK => $mnu->edt_id] + self::page_url_array($url_array);
            $url_edt = $this->url_with_back($this->page_url($edt_par), $url_array);
            // like the edit icons behind the title grey with the request to log in if the user cannot save a change
            // and for a user who can no tooltip, because ref() skips a title that repeats the name
            [$edt_style, $edt_tip] = $this->change_style_and_tip('', $mnu->edt_name);
            $result .= $this->list_item($this->ref($url_edt, $mnu->edt_name, $edt_tip, $edt_style)) . "\n";
        }
        // e.g. for a word its own and assigned views, the views of its parents and the default word views
        $result .= $mnu->view_items;
        $more_names = implode(', ', $mnu->more_names);
        $more_tip = $this->tip_with_names(msg_id::NAVBAR_MORE_VIEWS_TIP, msg_id::NAVBAR_MORE_VIEWS_NAMES_TIP, $more_names);
        $result .= $this->list_item($this->ref('?view=more', msg_id::AND_MORE->text(), $more_tip)) . "\n";
        // changing or adding a view below one sub header like the entries of the page
        $result .= $this->list_item(msg_id::NAVBAR_CUSTOMIZE->text(), styles::MENU_HEADER) . "\n";
        $url_msk_edit = $this->url_with_back(api::MAIN_SCRIPT
            . '?' . url_var::MASK . '=' . views::VIEW_EDIT_ID
            . '&' . url_var::ID . '=' . $msk_id, $url_array);
        $result .= $this->list_item($this->ref($url_msk_edit, msg_id::NAVBAR_CHANGE_VIEW->text(),
                msg_id::NAVBAR_CHANGE_VIEW_TIP->text())) . "\n";
        $url_msk_add = $this->url_with_back(api::MAIN_SCRIPT
            . '?' . url_var::MASK . '=' . views::VIEW_ADD_ID, $url_array);
        $add_tip = $this->tip_with_names(msg_id::NAVBAR_ADD_VIEW_TIP, msg_id::NAVBAR_ADD_VIEW_FOR_TIP, $mnu->obj_name);
        $result .= $this->list_item($this->ref($url_msk_add, msg_id::NAVBAR_ADD_VIEW->text(), $add_tip)) . "\n";
        // the server admin page checks the access itself, the link is just not offered to the others
        if ($is_admin) {
            $result .= $this->list_item($this->ref(api::SERVER_ADMIN_SCRIPT, msg_id::NAVBAR_SERVER_ADMIN->text())) . "\n";
        }
        return $this->list_unsorted($result);
    }

    /**
     * a tooltip that names examples behind its text, e.g. "show more views e.g. Word, Triple"
     *
     * @param msg_id $tip_id the tooltip itself e.g. "show more views"
     * @param msg_id $names_tip_id the addition with the names e.g. "e.g. z$VarObjNameList$z"
     * @param string $names the names for the addition e.g. "Word, Triple"; empty for the tooltip alone
     * @return string the tooltip text in the language of the user
     */
    function tip_with_names(msg_id $tip_id, msg_id $names_tip_id, string $names): string
    {
        $lib = new library();
        $result = $tip_id->text();
        if ($names != '') {
            $names_tip = $lib->msg_var_replace($names_tip_id->text(), msg_id::VAR_NAME_LIST, $names);
            $result .= ' ' . $names_tip;
        }
        return $result;
    }

    /**
     * @param array $url_array the current request parameters used to build back-navigation links
     * @param string|null $usr_name the logged-in username; null shows login and signup, non-null shows logout
     * @param string $usr_label role and username combined e.g. "admin timon"; shown as the first non-link menu line when non-empty
     * @param bool $is_admin true to offer the admin dashboard
     */
    private function user_login_list(
        array   $url_array = [],
        ?string $usr_name = null,
        string  $usr_label = '',
        bool    $is_admin = false
    ): string
    {
        global $mtr;
        $result = '';
        if ($usr_label !== '') {
            $result .= $this->list_item($usr_label) . "\n";
        }
        if ($usr_name !== null) {
            // logout acts on a plain get, so the link carries the anti-csrf token (see request_token_valid);
            // the back target is the last normal page, so the back button of the logout page never repeats a change
            $url_logout = $this->url_with_token($this->url_with_back(api::LOGOUT_SCRIPT, self::normal_page_array($url_array)));
            $result .= $this->list_item($this->ref($url_logout, $mtr->txt(msg_id::NAVBAR_LOGOUT))) . "\n";
        } else {
            // a page that itself carries a '9'-prefixed back target (e.g. the logout page, see
            // frontend::action_logout) forwards that target as the back of the login link, so
            // that after the login the original page is shown again and not the logout page
            $login_back = self::url_par_from_back_part($url_array);
            if ($login_back == []) {
                $login_back = $url_array;
            }
            $url_login = $this->url_with_back(api::LOGIN_SCRIPT, $login_back);
            $result .= $this->list_item($this->ref($url_login, $mtr->txt(msg_id::NAVBAR_LOGIN))) . "\n";
            $url_signup = $this->url_with_back(api::SIGNUP_SCRIPT, $url_array);
            $result .= $this->list_item($this->ref($url_signup, $mtr->txt(msg_id::NAVBAR_SIGNUP))) . "\n";
        }
        // only a logged-in user can keep the mode as an own setting, an ip user follows the browser
        if ($usr_name !== null) {
            $result .= $this->theme_switch($url_array);
        }
        // also an ip user without login sees the settings, so the link opens the read only system configuration
        // by its forward page, which keeps the back target (see api::FORWARD_VIEWS)
        $url_settings = $this->url_with_back(api::SETTINGS_REL, $url_array);
        $result .= $this->list_item($this->ref($url_settings, $mtr->txt(msg_id::NAVBAR_SETTINGS))) . "\n";
        // the view itself is admin only (views::ADMIN_MASK_IDS), the link is just not offered to the others
        if ($is_admin) {
            $url_dashboard = $this->url_with_back(api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::ADMIN_MAIN_ID, $url_array);
            $result .= $this->list_item($this->ref($url_dashboard, $mtr->txt(msg_id::NAVBAR_DASHBOARD))) . "\n";
        }
        return $this->list_unsorted($result);
    }

    /**
     * the entry of the user menu that switches to the other mode, saved as the overwrite of the user of the
     * config value frontend.layout.mode through the value edit mask like a setting (see config_tree::edit_form),
     * so that the next visits keep it; if the user follows the browser, both entries are in the menu and
     * the css shows only the one to the other mode than the browser (theme-to-dark and theme-to-light)
     *
     * @param array $url_array the calling url, which is shown again after the switch
     * @return string the html list items of the switch or '' if the config has no mode value to overwrite
     */
    private function theme_switch(array $url_array): string
    {
        global $ui_sys;

        $result = '';
        $val = $ui_sys?->cfg?->value_by(html_base::MODE_PATH);
        if ($val !== null and $val->id() != 0) {
            $mode = $this->theme_mode();
            if ($mode != def::MODE_DARK) {
                $result .= $this->theme_switch_item($val->id(), $mode, def::MODE_DARK,
                    msg_id::NAVBAR_DARK_MODE, msg_id::NAVBAR_DARK_MODE_TIP, styles::THEME_TO_DARK, $url_array);
            }
            if ($mode != def::MODE_LIGHT) {
                $result .= $this->theme_switch_item($val->id(), $mode, def::MODE_LIGHT,
                    msg_id::NAVBAR_LIGHT_MODE, msg_id::NAVBAR_LIGHT_MODE_TIP, styles::THEME_TO_LIGHT, $url_array);
            }
        }
        return $result;
    }

    /**
     * @param int|string $val_id the id of the config value of the mode
     * @param int $mode the mode of the shown page e.g. def::MODE_BROWSER
     * @param int $to_mode the mode that the entry switches to e.g. def::MODE_DARK
     * @param msg_id $name the text of the entry e.g. msg_id::NAVBAR_DARK_MODE
     * @param msg_id $tip the tooltip of the entry
     * @param string $style the class that hides the entry if the browser has already this mode
     * @param array $url_array the calling url, which is shown again after the switch
     * @return string the html list item with the post form that saves the mode
     */
    private function theme_switch_item(
        int|string $val_id,
        int        $mode,
        int        $to_mode,
        msg_id     $name,
        msg_id     $tip,
        string     $style,
        array      $url_array
    ): string
    {
        $txt = $this->form_session_token();
        $txt .= $this->form_hidden(url_var::NUMERIC_VALUE, (string)$to_mode);
        // the shown mode and the yaml keys of the mode, so that the confirmation can say what has changed from what
        $txt .= $this->form_hidden(url_var::PRE . url_var::NUMERIC_VALUE, (string)$mode);
        $txt .= $this->form_hidden(url_var::CONFIG_KEY, implode(' ', html_base::MODE_PATH));
        $txt .= $this->form_hidden(url_var::MASK, (string)views::VALUE_EDIT_ID);
        $txt .= $this->form_hidden(url_var::ID, group_id_url::to_url($val_id));
        $txt .= $this->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED);
        // a submit that reads like the links of the menu, named so that the entry point runs the save
        $txt .= '<' . self::BUTTON . ' ' . self::TYPE . '="' . self::INPUT_SUBMIT . '" '
            . self::CLASS_HTML . '="' . styles::TEXT_BUTTON . '" ' . self::NAME . '="' . url_var::POST_SUBMIT . '" '
            . self::TITLE_HTML . '="' . $this->esc($tip->text()) . '">' . $this->esc($name->text())
            . '</' . self::BUTTON . '>';
        // the page is shown again after the save, so the post carries it as the back target
        $url = $this->url_with_back(api::MAIN_SCRIPT, self::normal_page_array($url_array));
        $form = $this->form_simple($url, self::METHOD_POST, $txt);
        // a fixed mode shows only the entry to the other mode, so it is never hidden by the browser setting
        return $this->list_item($form, $mode == def::MODE_BROWSER ? $style : '') . "\n";
    }

    /**
     * /**
     * a full-width notification bar shown above the footer to inform the user about a non-fatal issue
     * uses Bootstrap alert-warning styling to display in orange
     *
     * @param string $msg_txt the human-readable message to display; must already be translated
     * @param bool $is_success true to confirm a done action in green instead of the orange of an issue
     * @return string the html code for the notification bar
     */
    function dsp_notification(string $msg_txt, bool $is_success = false): string
    {
        return $this->dsp_notification_html(htmlspecialchars($msg_txt), $is_success);
    }

    /**
     * like dsp_notification but the caller supplies already-safe HTML (e.g. to embed a link);
     * the caller is responsible for escaping any user-supplied content inside $html
     *
     * @param string $html pre-rendered safe HTML content for the notification bar
     * @param bool $is_success true to confirm a done action in green instead of the orange of an issue
     * @return string the html code for the notification bar
     */
    function dsp_notification_html(string $html, bool $is_success = false): string
    {
        $class = $is_success ? self::CLASS_NOTIFICATION_SUCCESS : self::CLASS_NOTIFICATION;
        return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . $class . '">'
            . $html
            . '</' . self::DIV . '>' . "\n";
    }

    /**
     * @param bool $no_about true to omit the about link e.g. when already on the about page
     * @return string the full page footer including body and html closing tags
     */
    function footer(bool $no_about = false): string
    {
        // invisible placeholder so that a text replace can add a user message
        // to a html page loaded from the page cache (see api::USER_MSG_PLACEHOLDER)
        $result = api::USER_MSG_PLACEHOLDER . "\n";
        $result .= $this->site_footer($no_about);
        $result .= '</' . self::BODY . '>' . "\n";
        $result .= '</' . self::HTML . '>' . "\n";

        return $result;
    }

    /**
     * the start of a page that is sent before the page is created, because the creation can take
     * longer than the expected response time; it becomes visible only after the delay, so for a
     * fast page the browser keeps showing the previous page; it has no title and its header shows
     * only the logo without the search form, so the page that follows names the document and no
     * element id is used twice
     *
     * @param float $delay the seconds after which the loading text is shown
     * @param int $since the seconds since the request start shown when the text appears; counted up each second
     * @param user_message|Message $msg to report a configured font or colour that css could misread
     * @return string the html code from the doctype to the loading text and the footer
     */
    function loading_start(float $delay, int $since, user_message|Message $msg): string
    {
        global $mtr;
        $show = self::LOADING_ANIMATION . ' 0s ' . $delay . 's forwards';
        $css = '.' . self::CLASS_LOADING . ' {visibility: hidden; animation: ' . $show . ';}';
        $css .= ' @keyframes ' . self::LOADING_ANIMATION . ' {to {visibility: visible;}}';
        $loading = $mtr->txt(msg_id::PAGE_LOADING);
        if ($since < self::LOADING_COUNT_LIMIT) {
            $css .= $this->loading_count_css($delay, $since, self::LOADING_COUNT_LIMIT);
            $loading .= ' ' . $mtr->txt(msg_id::PAGE_LOADING_SINCE);
            $loading .= ' ' . $this->span('', self::CLASS_LOADING_SECONDS);
            $loading .= ' ' . $mtr->txt(msg_id::PAGE_LOADING_SECONDS);
        }
        // with the font and the colours like every page
        $head = $this->charset() . $this->head_style() . $this->theme_style($msg) . $this->style($css);
        $txt = $this->nav($this->logo(), self::CLASS_NAV);
        $txt .= $this->main($this->p($loading));
        $txt .= $this->site_footer();
        $result = $this->doctype() . '<' . self::HTML . '>' . $this->head($head);
        return $result . '<' . self::BODY . '>' . $this->div($txt, self::CLASS_LOADING);
    }

    /**
     * the css that counts the seconds of the loading text without javascript: a registered integer
     * property is animated in one step per second and shown as a css counter
     *
     * @param float $delay the seconds after which the count starts
     * @param int $since the first number shown
     * @param int $limit the last number shown
     * @return string the css rules of the count
     */
    private function loading_count_css(float $delay, int $since, int $limit): string
    {
        $var = '--' . self::LOADING_COUNTER;
        $steps = $limit - $since;
        $count = self::LOADING_COUNT_ANIMATION . ' ' . $steps . 's steps(' . $steps . ') ' . $delay . 's forwards';
        $css = ' @property ' . $var . " {syntax: '<integer>'; inherits: false; initial-value: " . $since . ';}';
        $css .= ' .' . self::CLASS_LOADING_SECONDS . ' {animation: ' . $count . ';';
        $css .= ' counter-reset: ' . self::LOADING_COUNTER . ' var(' . $var . ');}';
        $css .= ' .' . self::CLASS_LOADING_SECONDS . '::after {content: counter(' . self::LOADING_COUNTER . ');}';
        return $css . ' @keyframes ' . self::LOADING_COUNT_ANIMATION . ' {to {' . $var . ': ' . $limit . ';}}';
    }

    /**
     * @return string the html code that hides the start of the page sent by loading_start
     */
    function loading_end(): string
    {
        return $this->style('.' . self::CLASS_LOADING . ' {display: none;}');
    }

    /**
     * @param bool $no_about true to omit the about link e.g. when already on the about page
     * @return string the html footer element of a page
     */
    private function site_footer(bool $no_about = false): string
    {
        global $mtr;
        $result = '<' . self::FOOTER . ' ' . self::CLASS_HTML . '="' . self::CLASS_FOOTER . '">' . "\n";

        // for the about page this does not make sense
        $result .= '<' . self::P . '> ' . "\n";
        if (!$no_about) {
            $url = $this->url_old(rest_ctrl::URL_ABOUT);
            $result .= $this->ref($url, $mtr->txt(msg_id::SYSTEM_TITLE_ABOUT)) . ' &middot; ' . "\n";
            $result .= $this->ref(api::PRIVACY_SCRIPT, $mtr->txt(msg_id::PRIVACY_POLICY)) . ' &middot; ' . "\n";
        }
        $result .= $mtr->txt(msg_id::FOOTER_DATA_LICENCE) . ' ';
        $result .= $this->ref(def::LINK_CC0, $mtr->txt(msg_id::CC0), $mtr->txt(msg_id::CC0_LICENSE)) . ' ' . "\n";
        $result .= $mtr->txt(msg_id::FOOTER_LICENCE_UNLESS) . ' ' . "\n";
        $result .= $this->ref(def::LINK_GITHUB, $mtr->txt(msg_id::PROGRAM_CODE)) . ' ' . "\n";
        $result .= $mtr->txt(msg_id::FOOTER_OF_VERSION) . ' ' . SYSTEM_PAGE_VERSION . "\n";
        $result .= $mtr->txt(msg_id::FOOTER_UNDER_THE) . ' ' . $this->ref(def::LINK_AGPL, $mtr->txt(msg_id::AGPL3)) . ' ' . $mtr->txt(msg_id::FOOTER_LICENCE) . '. ' . "\n";
        $result .= '</' . self::P . '> ' . "\n";

        $result .= '</' . self::FOOTER . '>' . "\n";

        return $result;
    }


    /*
     * wrapper for the basic html elements used
     */

    // TODO Prio 1 use this everywhere if possible

    // the url schemes that are linked; any other scheme e.g. javascript: is never linked
    const array LINK_SCHEMES = ['http', 'https'];

    /**
     * create the html code for a link
     * the url and the link text are escaped here at the output sink, so that a user supplied
     * url e.g. of a source or reference can neither break the href attribute nor add a script,
     * and only a http, https or relative url is linked (e.g. a javascript: url is never linked)
     *
     * @param string $url the target url
     * @param string $name the text shown to the user for the link e.g. 'global warming' to show the triple global warming
     * @param string $title the tooltip of the link
     * @param string $css_class the css class name of the link
     * @param bool $name_is_html true only if the calling function guarantees that the link text is safe html e.g. an icon
     * @param bool $with_user false for a link of a static page e.g. index.html, which is the same for every user
     * @return string the html code of the link or just the text if the url scheme is not allowed
     */
    function ref(
        string $url,
        string $name,
        string $title = '',
        string $css_class = '',
        bool   $name_is_html = false,
        bool   $with_user = true
    ): string
    {
        if (!$name_is_html) {
            $name = $this->esc($name);
        }
        if (!$this->url_scheme_allowed($url)) {
            // show the text without the link and report the unexpected scheme
            log_warning('link to "' . $url . '" is blocked due to the unexpected url scheme');
            $result = $name;
        } else {
            $url = $this->url_with_id_var($url);
            if ($with_user) {
                $url = $this->url_with_user($url);
            }
            $result = '<' . self::A . ' ' . self::HREF . '="' . htmlspecialchars($url, ENT_QUOTES) . '"';
            if ($title != '' && $title != $name) {
                $result .= ' ' . self::TITLE_HTML . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
            }
            if ($css_class != '') {
                $result .= ' ' . self::CLASS_HTML . '="' . $css_class . '"';
            }
            $result .= '>';
            $result .= $name;
            $result .= '</' . self::A . '>';
        }
        return $result;
    }

    /**
     * @param string $url the target url of a link
     * @return bool true if the url is relative or uses one of the allowed schemes
     */
    private function url_scheme_allowed(string $url): bool
    {
        $result = true;
        if (preg_match('/^\s*([a-z][a-z0-9+.\-]*):/i', $url, $matches)) {
            $result = in_array(strtolower($matches[1]), self::LINK_SCHEMES);
        }
        return $result;
    }

    /**
     * create the html code to show an object
     *
     * @param int $msk_id the view id related to the object that should be shown e.g. views::FORMULA_ID
     * @param int $id the object id that should be shown
     * @param string $name the text shown to the user for the link e.g. 'global warming' to show the triple global warming
     * @param string $par additional parameter that could be added to the target url
     * @return string the html
     */
    function ref_view(int $msk_id, int $id, string $name, string $par = ''): string
    {
        // $name is always the plain object name shown as the link text, so
        // escape it here to stop a name like '<img src=x onerror=...>' from
        // running when another user browses or searches for the object
        $name = $this->esc($name);
        if ($par == '') {
            return $this->ref(api::MAIN_SCRIPT
                . '?' . url_var::MASK . '=' . $msk_id
                . '&' . url_var::ID . '=' . $id, $name);
        } else {
            return $this->ref(api::MAIN_SCRIPT
                . '?' . url_var::MASK . '=' . $msk_id
                . '&' . url_var::ID . '=' . $id
                . '&' . $par, $name);
        }
    }

    function img(string $img_path, string $alt, string $class = ''): string
    {
        $result = '<' . self::IMG
            . ' ' . self::SRC . '="' . $img_path . '"'
            . ' ' . self::ALT . '="' . htmlspecialchars($alt, ENT_QUOTES) . '"';
        if ($class !== '') {
            $result .= ' ' . self::CLASS_HTML . '="' . $class . '"';
        }
        $result .= '>';
        return $result;
    }

    /**
     * escape user settable text so it is shown literally and cannot inject html
     * (e.g. a word, triple, formula or verb name or description placed into the
     * html body); the element helpers escape values that go into an attribute
     * @param string|null $text the raw user text
     * @return string the text safe to place between html tags
     */
    function esc(?string $text): string
    {
        return htmlspecialchars($text ?? '', ENT_NOQUOTES);
    }

    /**
     * @param string $text the text that should be formatted
     * @param string $style the CSS class names
     * @return string the html code
     */
    function span(string $text, string $style = '', string $title = ''): string
    {
        $result = '<' . html_names::SPAN;
        if ($style != '') {
            $result .= ' ' . html_names::HTML_CLASS . '="' . $style . '"';
        }
        if ($title != '') {
            $result .= ' ' . html_names::TITLE . '="' . htmlspecialchars($title, ENT_QUOTES) . '" ' . self::TOGGLE_TOOLTIP;
        }
        $result .= '>' . $text . '</' . html_names::SPAN . '>';
        return $result;
    }

    /**
     * a text with a mouseover text but without the data-toggle of span(), which only a js plugin
     * would read and which a web renderer never emits (docs/llm/frontend.md)
     *
     * @param string $text the html code of the text e.g. an escaped name
     * @param string $title the mouseover text, which is escaped here
     * @return string the html code of the text with its mouseover text
     */
    function with_title(string $text, string $title): string
    {
        $title_attr = ' ' . self::TITLE_HTML . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
        return '<' . html_names::SPAN . $title_attr . '>' . $text . '</' . html_names::SPAN . '>';
    }

    /**
     * the html code of a font icon (e.g. Font Awesome) as an empty paired <i> tag
     * @param string $icon_class the css class string of the icon, always an icons::* const
     * @return string the html code e.g. '<i class="fas fa-chevron-right"></i>'
     */
    function icon(string $icon_class): string
    {
        return '<' . self::I . ' ' . self::CLASS_HTML . '="' . $icon_class . '"></' . self::I . '>';
    }

    /**
     * an icon that links to a view of an object, e.g. the edit and the delete icon of a component
     * link shown in the component list of a view
     *
     * @param int $msk_id the database id of the view to open e.g. views::COMPONENT_LINK_EDIT_ID
     * @param int|string $id the database id of the object to show in that view
     * @param string $icon the icon css class from web/const/icons.php e.g. icons::EDIT
     * @param string $title the hover tooltip of the icon
     * @param array $url_arr the url vars of the calling page for the back link
     * @return string the html code of the icon link
     */
    function icon_link(int $msk_id, int|string $id, string $icon, string $title, array $url_arr = []): string
    {
        $url = $this->url_back($msk_id, $id, $url_arr);
        return $this->ref($url, $this->icon($icon), $title, styles::HEADING_ICON_INLINE, true);
    }

    /**
     * an icon link that starts an add or an edit; a user without login gets the icon greyed out with a
     * tooltip that asks for a login, but the icon stays a link, so pressing it shows the reason of the backend
     *
     * @param string $url the url that the icon opens
     * @param string $icon the icon css class from web/const/icons.php e.g. icons::EDIT
     * @param string $tooltip the translated tooltip for a user who can save the change
     * @param string $style the css class of the icon link e.g. styles::FORM_ICON_INLINE
     * @return string the html code of the icon link
     */
    function change_icon(string $url, string $icon, string $tooltip, string $style = styles::HEADING_ICON_INLINE): string
    {
        [$style, $tooltip] = $this->change_style_and_tip($style, $tooltip);
        return $this->ref($url, $this->icon($icon), $tooltip, $style, true);
    }

    /**
     * @param string $style the css class of an add or edit icon link
     * @param string $tooltip the translated tooltip for a user who can save the change
     * @return array the css class and the tooltip, for a user without login grey and with the request to log in
     */
    function change_style_and_tip(string $style, string $tooltip): array
    {
        global $mtr;
        if (self::change_blocked()) {
            $style = trim($style . ' ' . styles::STYLE_GREY);
            $tooltip .= self::TOOLTIP_SEPARATOR . $mtr->txt(msg_id::CHANGE_LOGIN_REQUIRED);
        }
        return [$style, $tooltip];
    }

    /**
     * @return bool true if the requesting user is not logged in and therefore cannot save a change (see
     *              user::is_blocked); without a request cache the user is unknown and treated as not blocked
     */
    static function change_blocked(): bool
    {
        global $ui_sys;
        // read with ?? first, because a method call after ?-> does not guard an unset typed property
        $usr = $ui_sys->usr ?? null;
        return $usr?->is_blocked() ?? false;
    }

    /**
     * the html of a font awesome icon
     * @param string $icon the icon css class from web/const/icons.php e.g. icons::PASSWORD_SHOW
     * @param string $class_add an additional css class e.g. to toggle the icon visibility via css
     * @param string $title the hover tooltip and accessible hint of the icon
     * @return string the icon <i> element
     */
    function icon_with_title(string $icon, string $class_add = '', string $title = ''): string
    {
        $class = $icon;
        if ($class_add != '') {
            $class .= ' ' . $class_add;
        }
        $result = '<' . self::I . ' ' . self::CLASS_HTML . '="' . $class . '"';
        if ($title != '') {
            $result .= ' ' . self::TITLE . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
        }
        $result .= '></' . self::I . '>';
        return $result;
    }

    /**
     * @param string $text the superscript text e.g. the exponent "2"
     * @return string the html code that shows the given text as a superscript e.g. <sup>2</sup>
     */
    function sup(string $text): string
    {
        return '<' . html_names::SUP . '>' . $text . '</' . html_names::SUP . '>';
    }

    /**
     * @param string $text the subscript text e.g. the index "i"
     * @return string the html code that shows the given text as a subscript e.g. <sub>i</sub>
     */
    function sub(string $text): string
    {
        return '<' . html_names::SUB . '>' . $text . '</' . html_names::SUB . '>';
    }

    /*
     * wrapper for internal references used in the html code
     */

    /**
     * build a url for link a zukunft.com element
     * TODO Prio 0 deprecate and use url_new for all url creations if possible
     *
     * @param string $obj_name the object that is requested e.g. a view
     * @param int|string $id the id of the parameter e.g. 1 for math const
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string|array $par either the array with the parameters or the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    function url_old(
        string       $obj_name,
        int|string   $id = 0,
        array        $url_arr = [],
        string|array $par = '',
        string       $id_ext = ''
    ): string
    {
        $result = rest_ctrl::PATH_FIXED . $obj_name . rest_ctrl::EXT;
        if ($id <> 0) {
            if ($par != '') {
                $result .= '?' . $par . '=' . $id;
            } else {
                $result .= '?id=' . $id;
            }
            if ($id_ext != '') {
                $result .= '&' . $id_ext;
            }
        }
        return $this->url_with_back($result, $url_arr);
    }

    /**
     * TODO Prio 1 deprecate and replace if the the url_arr based back var
     * build a zukunft.com internal url based on the html one-page setup
     * o for the main object that should be shown to the user
     * v for view which contains the main object type
     * i for the database id of the main object
     * r for the related phrase (the phrases used for all components)
     * c for the related terms (the term used for all components)
     * other related phrases or term are set in the components
     *
     * @param int|string $view the code_id or the database id of the view
     * @param int|string $id the database id or name of the object e.g. 1 for the word Mathematics
     * @param string $obj_name the object that should be shown e.g. a value
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string|array $par either the array with the parameters or the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    /**
     * remove the trailing slash of a base url, because the script path already starts with one
     * @param string $base_url the absolut html path for urls or an empty string for a relative url
     * @return string the base url ready to be followed by the script path
     */
    private static function base_url_clean(string $base_url): string
    {
        $result = $base_url;
        if (str_ends_with($result, '/')) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }

    /**
     * the url of a view for an object with the calling page as the back part
     *
     * @param int|string $view the id or the code id of the view to call
     * @param int|string $id the id of the object to show, 0 for a view without an object
     * @param array $url_arr the url parameters of the calling page, which become the
     *                       '9'-prefixed back part so that the called page can return to it
     * @param string $id_ext an additional url parameter e.g. to link two objects
     * @param string $base_url the base url of the pod, empty for a relative url
     * @return string the url of the view
     */
    function url_back(
        int|string $view,
        int|string $id = 0,
        array      $url_arr = [],
        string     $id_ext = '',
        string     $base_url = ''
    ): string
    {
        $url = self::base_url_clean($base_url);
        $url .= rest_ctrl::PATH_FIXED . rest_ctrl::URL_MAIN_SCRIPT . rest_ctrl::EXT . '?';
        $url .= url_var::MASK . '=' . $view;
        // a string id e.g. the group id of a value is written in its short url form
        if (is_string($id)) {
            $url .= '&id=' . rawurlencode(group_id_url::to_url($id));
        } elseif ($id <> 0) {
            $url .= '&id=' . $id;
        }
        if ($id_ext != '') {
            $url .= '&' . $id_ext;
        }
        return $this->url_with_back($url, $url_arr);
    }

    /**
     * build a zukunft.com internal url based on the html one-page set up
     * the url_var class defines the parameters
     *
     * @param int|string $view the code_id or the database id of the view
     * @param int|string $id the database id or name of the object e.g. 1 for the word Mathematics
     * @param array $url_arr the back trace calls to return to the original url and for undo
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    static function url(
        int|string   $view,
        int|string   $id = 0,
        array        $url_arr = [],
        string       $id_ext = '',
        string       $base_url = ''
    ): string
    {
        $url = self::base_url_clean($base_url);
        $url .= rest_ctrl::PATH_FIXED . rest_ctrl::URL_MAIN_SCRIPT . rest_ctrl::EXT . url_var::PAR;
        $url .= url_var::MASK . url_var::EQ . $view;
        // a string id e.g. the group id of a value is written in its short url form
        if (is_string($id)) {
            $url .= url_var::ADD . url_var::ID . url_var::EQ . rawurlencode(group_id_url::to_url($id));
        } elseif ($id <> 0) {
            $url .= url_var::ADD . url_var::ID . url_var::EQ . $id;
        }
        if ($id_ext != '') {
            $url .= url_var::ADD . $id_ext;
        }
        if ($url_arr != []) {
            $url .= url_var::ADD . html_base::back_url_part($url_arr);
        }
        return $url;
    }

    /**
     * Build a URL parameter string with the calling params each prefixed with url_var::BACK ('9'),
     * so the target page can redirect back to the calling page after completing its action.
     *
     * only the params that identify the page are prefixed (see page_url_array), because the
     * form state of the calling page does not name the page and an already prefixed param
     * would be prefixed a second time (e.g. '99m' for a back part or '98k' for a pre value)
     *
     * @param array $url_array the URL parameters of the calling page e.g. /http/view.php?m=3&id=12
     * @return string the additional URL parameters e.g. '9m=3&9id=123'
     */
    static function back_url_part(array $url_array): string
    {
        return self::prefixed_url_part(self::page_url_array($url_array), url_var::BACK);
    }

    /**
     * Build a URL parameter string with each editable field prefixed with url_var::PRE ('8'),
     * carrying the database value the field had when the edit view is opened, so that on save
     * only the fields the user actually changed are written and a concurrent change by another
     * user is not overwritten. See docs/llm/state-and-messages.md.
     *
     * @param array $field_values the editable fields keyed by their url var, e.g. [url_var::NAME => 'USD']
     * @return string the additional URL parameters e.g. '8k=USD&8o=the%20dollar'
     */
    function pre_url_part(array $field_values): string
    {
        return $this->prefixed_url_part($field_values, url_var::PRE);
    }

    /**
     * the given url params keyed by their url var with the given prefix char added to each key
     *
     * a param that already names another url part (the '7'-prefixed link vars, the '8'-prefixed db
     * values and the '9'-prefixed back targets) is never prefixed again, because a compound key
     * like '89m' names no url var and the url mapper would report it as missing (see url_var::PREFIXES)
     *
     * @param array $url_arr the params keyed by their url var e.g. ['m' => 3, '9m' => 5]
     * @param string $prefix the prefix char of the url part e.g. url_var::PRE ('8')
     * @return array the unprefixed params with the prefix added e.g. ['8m' => 3]
     */
    static function prefixed_url_array(array $url_arr, string $prefix): array
    {
        $par = [];
        foreach ($url_arr as $key => $val) {
            [$key_prefix,] = url_var::split_prefix((string)$key);
            if ($key_prefix == '') {
                $par[$prefix . $key] = $val;
            }
        }
        return $par;
    }

    /**
     * the same as back_url_part, but as an array e.g. for the hidden fields of a form
     * so only the page-identifying params are prefixed (see page_url_array)
     *
     * @param array $url_arr the url params of the calling page
     * @return array the page params prefixed with url_var::BACK e.g. ['9m' => 3, '9id' => 123]
     */
    static function back_url_array(array $url_arr): array
    {
        return self::prefixed_url_array(self::page_url_array($url_arr), url_var::BACK);
    }

    /**
     * the page params of the last normal view page, e.g. as the back target of the logout page:
     * the page itself or, for an add, edit, del or confirm page, the page it has been opened from
     * or else the default view of its object, because returning to a change form would repeat the change
     *
     * @param array $url_arr the url params of the calling page
     * @return array the page params of the normal page, empty for the start page
     */
    static function normal_page_array(array $url_arr): array
    {
        $page_arr = self::page_url_array($url_arr);
        $msk_id = (int)($page_arr[url_var::MASK] ?? 0);
        if (!self::is_change_mask($msk_id)) {
            return $page_arr;
        }
        $back_arr = self::page_url_array(self::url_par_from_back_part($url_arr));
        $back_msk_id = (int)($back_arr[url_var::MASK] ?? 0);
        if ($back_arr != [] and !self::is_change_mask($back_msk_id)) {
            $result = $back_arr;
        } else {
            $result = self::object_page_array($url_arr, $msk_id);
        }
        return $result;
    }

    /**
     * @param int $msk_id the id of the view mask
     * @return bool true if the mask adds, changes or deletes an object or confirms such a change
     */
    private static function is_change_mask(int $msk_id): bool
    {
        return (in_array($msk_id, views::CHANGE_MASKS_IDS) or in_array($msk_id, views::CONFIRM_MASKS_IDS));
    }

    /**
     * @param array $url_arr the url params of an add, edit, del or confirm page
     * @param int $msk_id the id of the change mask, which a confirm page replaces by its origin mask
     * @return array the page params of the default view of the changed object, empty if it is unknown
     */
    private static function object_page_array(array $url_arr, int $msk_id): array
    {
        $msk = new views();
        $chg_msk_id = (int)($url_arr[url_var::ORIGIN_MASK] ?? $msk_id);
        $id = $url_arr[url_var::ID] ?? '';
        $result = [];
        // the origin mask is url input, so only a known change mask is resolved
        if (in_array($chg_msk_id, views::CHANGE_MASKS_IDS) and $id != '' and $id != '0') {
            $base_code_id = $msk->system_to_base($msk->id_to_code_id($chg_msk_id));
            if ($base_code_id != '') {
                $result = [url_var::MASK => $msk->code_id_to_id($base_code_id), url_var::ID => $id];
            }
        }
        return $result;
    }

    /**
     * the url parameters of a link that the called add view creates together with the new object,
     * each prefixed with url_var::LINK ('7'), e.g. the phrase of the formula link of a new formula
     *
     * @param array $link_vars the vars of the link keyed by their url var e.g. [url_var::PHRASE => 1]
     * @return string the additional url parameters e.g. '7p=1'
     */
    static function link_url_part(array $link_vars): string
    {
        return self::prefixed_url_part($link_vars, url_var::LINK);
    }

    /**
     * the vars of the link that is created together with a new object, taken from the '7'-prefixed
     * url params with the prefix removed; only the vars that a link may name (url_var::LINK_VARS) are
     * taken, so that a typed url cannot set e.g. the id of the new link
     *
     * @param array $url_array the url with the '7'-prefixed link vars e.g. ['7p' => 1]
     * @return array the link vars without the prefix e.g. ['p' => 1], empty if the url has none
     */
    static function link_vars(array $url_array): array
    {
        $link_vars = url_var::prefixed_vars($url_array, url_var::LINK);
        return array_intersect_key($link_vars, array_flip(url_var::LINK_VARS));
    }

    /**
     * reduce the url params of the calling page to those identifying the page (url_var::PAGE_VARS),
     * so the back part of an edit link stays short and form state or already '8'/'9'-prefixed
     * params are never prefixed again (e.g. no '99m' or '98k' compounds)
     *
     * an empty page var names nothing, so it does not travel with the link either: the id 0 that
     * url_mapper::url_to_standard adds to every request would otherwise repeat in every link of a
     * page without an object; a zero is only kept where zero is a value (url_var::PAGE_VARS_KEEP_ZERO)
     *
     * @param array $url_arr the url params of the calling page
     * @return array only the page-identifying url params with a value e.g. ['m' => 90, 'id' => 1]
     */
    static function page_url_array(array $url_arr): array
    {
        $page_arr = array_intersect_key($url_arr, array_flip(url_var::PAGE_VARS));
        return array_filter($page_arr, function ($val, $key) {
            if ($val === null or $val === '') {
                return false;
            }
            if (in_array($key, url_var::PAGE_VARS_KEEP_ZERO)) {
                return true;
            }
            // a non numeric string is never zero, so a search pattern or an id list is kept
            return $val != 0;
        }, ARRAY_FILTER_USE_BOTH);
    }

    static function pre_url_array(array $url_arr): array
    {
        return self::prefixed_url_array($url_arr, url_var::PRE);
    }

    /**
     * Build the additional URL parameters for an array, each key prefixed with the given prefix char.
     *
     * @param array $url_array the params keyed by their url var
     * @param string $prefix the prefix char e.g. url_var::BACK ('9') or url_var::PRE ('8')
     * @return string the prefixed URL parameter string e.g. '9m=3&9id=123'
     */
    static private function prefixed_url_part(array $url_array, string $prefix): string
    {
        $par = [];
        foreach ($url_array as $key => $val) {
            // the group id of a value or result is written in its short url form
            if ($key == url_var::ID) {
                $val = group_id_url::to_url($val);
            }
            $par[] = $prefix . $key . '=' . rawurlencode((string)$val);
        }
        return empty($par) ? '' : implode('&', $par);
    }

    function url_with_back(string $url, array $url_array): string
    {
        $par_ext = $this->back_url_part($url_array);
        if ($par_ext == '') {
            return $url;
        } else {
            if (str_contains($url, '?')) {
                return $url . '&' . $par_ext;
            } else {
                return $url . '?' . $par_ext;
            }
        }
    }

    /**
     * the url of the page that the given url parameters describe, e.g. to return to it
     *
     * @param array $url_arr the url parameters of the page
     * @return string the url of the page with the parameters that identify it (url_var::PAGE_VARS)
     *                or the start page if none of them has a value
     */
    function page_url(array $url_arr): string
    {
        $page_arr = self::page_url_array($url_arr);
        // without a page param the main script shows the start page, so no '?' is added
        if ($page_arr == []) {
            return api::MAIN_SCRIPT;
        }
        return api::MAIN_SCRIPT . url_var::PAR . http_build_query($page_arr);
    }

    /**
     * append the anti-csrf session token to a get action link (logout, error_update) so that the
     * server (frontend::request_token_valid) can reject a top-level cross-site get that would
     * otherwise trigger the action; the token is read from the session here, the same place
     * form_session_token reads it for the crud post forms
     * @param string $url the action url e.g. 'view.php?m=64'
     * @return string the url with the session token param appended
     */
    function url_with_token(string $url): string
    {
        $token = $_SESSION[url_var::SESSION_TOKEN] ?? '';
        $sep = str_contains($url, '?') ? '&' : '?';
        return $url . $sep . url_var::SESSION_TOKEN . '=' . rawurlencode($token);
    }

    /**
     * add the id of the logged-in user to the url of a page of this pod, so that the address bar
     * shows for whom the page is created; a user id that the url already carries is replaced
     * @param string $url the target of a link or a redirect e.g. '/http/view.php?m=3&id=272'
     * @return string e.g. '/http/view.php?m=3&id=272&u=12', unchanged if nobody is logged in
     *                or if the url is an in-page anchor or points to another site or pod
     */
    function url_with_user(string $url): string
    {
        $usr_id = $this->url_user_id();
        $result = $url;
        if ($usr_id > 0 and $this->is_page_url($url)) {
            $result = self::url_set_par($url, url_var::USER, (string)$usr_id);
        }
        return $result;
    }

    /**
     * the hidden field with the id of the logged-in user, because a get form drops the
     * parameters of its action url, so that the page after the submit also carries the user
     * @return string the html code of the hidden field or an empty string if nobody is logged in
     */
    function form_user(): string
    {
        $usr_id = $this->url_user_id();
        $result = '';
        if ($usr_id > 0) {
            $result = $this->form_hidden(url_var::USER, (string)$usr_id);
        }
        return $result;
    }

    /**
     * read from the request cache, because the url builders are called without the message
     * @return int the id of the logged-in user for the urls or 0 if nobody is logged in
     */
    private function url_user_id(): int
    {
        global $ui_sys;
        // ?? and not ?->, because the user of the cache can be unset e.g. for an anonymous render
        $usr = $ui_sys->usr ?? null;
        return $usr?->id_for_url() ?? 0;
    }

    /**
     * @param string $url the target of a link, which may be relative, absolute or an in-page anchor
     * @return bool true if the url calls the main script of this pod
     */
    private function is_page_url(string $url): bool
    {
        $parts = parse_url($url);
        $result = false;
        if ($parts !== false) {
            $path = $parts['path'] ?? '';
            // a url with only parameters e.g. '?view=more' calls the page itself
            $is_same_page = ($path == '' and isset($parts['query']));
            $is_main = ($is_same_page or str_ends_with($path, api::MAIN_SCRIPT_EXT));
            $result = ($is_main and self::is_own_host($parts));
        }
        return $result;
    }

    /**
     * @param array $parts the parse_url parts of a url
     * @return bool true if the url is relative or points to the host and port of this pod
     */
    private static function is_own_host(array $parts): bool
    {
        $own = parse_url(THIS_URL);
        $host = $parts['host'] ?? '';
        $same_host = ($host == ($own['host'] ?? '') and ($parts['port'] ?? 0) == ($own['port'] ?? 0));
        return ($host == '' or $same_host);
    }

    /**
     * set one url parameter in front of an in-page anchor and replace the value the url already carries
     * @param string $url e.g. '/http/view.php?m=3&u=5#tab'
     * @param string $key the url var e.g. url_var::USER
     * @param string $val the new value e.g. '12'
     * @return string e.g. '/http/view.php?m=3&u=12#tab'
     */
    static function url_set_par(string $url, string $key, string $val): string
    {
        $anchor_pos = strpos($url, url_var::ANCHOR);
        $anchor = $anchor_pos === false ? '' : substr($url, $anchor_pos);
        $base = $anchor_pos === false ? $url : substr($url, 0, $anchor_pos);
        $par = $key . url_var::EQ . rawurlencode($val);
        $pattern = self::par_pattern($key, '[^' . url_var::ADD . ']*');
        if (preg_match($pattern, $base)) {
            $base = preg_replace($pattern, '${1}' . $par, $base);
        } elseif (str_contains($base, url_var::PAR)) {
            $base .= url_var::ADD . $par;
        } else {
            $base .= url_var::PAR . $par;
        }
        return $base . $anchor;
    }

    /**
     * name the object of a page url by the url var of its view, because the url builders name every
     * object by url_var::ID, but e.g. the admin user edit view names its user by url_var::USER_TO_EDIT
     * @param string $url e.g. '/http/view.php?m=107&id=5'
     * @return string e.g. '/http/view.php?m=107&ue=5', unchanged for the views that use url_var::ID
     */
    function url_with_id_var(string $url): string
    {
        $result = $url;
        if ($this->is_page_url($url)) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $url_arr);
            $id_var = url_var::id_var($url_arr[url_var::MASK] ?? 0);
            if ($id_var != url_var::ID and array_key_exists(url_var::ID, $url_arr)) {
                $result = preg_replace(self::par_pattern(url_var::ID), '${1}' . $id_var . url_var::EQ, $url);
            }
        }
        return $result;
    }

    /**
     * the regular expression of a url parameter that matches only the whole key after '?' or '&',
     * so e.g. the back key '9u' or the value 'a=u' is not taken as the key 'u'
     * @param string $key the url var e.g. url_var::USER
     * @param string $val_pattern the regular expression of the value, empty to match the key only
     * @return string the pattern with the separator in the first group
     */
    private static function par_pattern(string $key, string $val_pattern = ''): string
    {
        $sep_chars = preg_quote(url_var::PAR . url_var::ADD, '/');
        $key_eq = preg_quote($key . url_var::EQ, '/');
        return '/([' . $sep_chars . '])' . $key_eq . $val_pattern . '/';
    }

    /**
     * extract the BACK-prefixed params from a URL array, stripping the prefix from each key
     *
     * @param array $url_array e.g. ['9m' => '3', '9id' => '123']
     * @return array e.g. ['m' => '3', 'id' => '123'], empty array when no BACK params are present
     */
    static function url_par_from_back_part(array $url_array): array
    {
        $back_vars = url_var::prefixed_vars($url_array, url_var::BACK);
        return array_map(fn($val) => rawurldecode((string)$val), $back_vars);
    }

    /**
     * get URL from back ('9') prefixed url params set by html_base::back_url_part().
     * After a login (or any interstitial action) completes,
     * this gives the URL the user should be sent to.
     * Returns empty string when no back params are present.
     *
     * @param array $url_array the raw $_GET array, e.g. ['9m' => '3', '9id' => '123']
     *                         '9script' (BACK . 'script'): the calling script for non-default pages e.g. /http/about.php
     *                          other BACK-prefixed params: the original query params with prefix stripped on return
     * @param string $script script to be called which should be api::MAIN_SCRIPT
     * @return string the URL to redirect to after the action e.g. '/http/view.php?m=3&id=123'
     */
    static function url_from_back(array $url_array, string $script = api::MAIN_SCRIPT): string
    {
        $back_array = html_base::url_par_from_back_part($url_array);
        return empty($back_array) ? '' : $script . '?' . http_build_query($back_array);
    }

    /**
     * build a url for link a zukunft.com element
     *
     * @param string $obj_name the object that is requested e.g. a view
     * @return string the created url
     */
    function url_api(string $obj_name): string
    {
        return THIS_URL . rest_ctrl::PATH . $obj_name . '/';
    }

    /**
     * build a url for an external webside
     *
     * @param string $url the base url of the external page
     * @param string $id the external id of the entry or subpage
     * @param string $name the name of the link as shown to the user
     * @param string $description the tooltip for the link
     * @
     * @return string the created url
     */
    function url_ex(string $url, string $id, string $name, string $description, string $style = ''): string
    {
        return $this->ref($url . $id, $name, $description, $style);
    }


    /*
     * text formatting
     */

    function text_h1(string $title, string $style = ''): string
    {
        return $this->text_h($title, self::H2, self::H1, $style);
    }

    /**
     * TODO Prio 3 kept for future use, check if it actually has been used
     * @param string $text the text to show bold, e.g. the pending link of a confirm page
     * @return string the html code of the bold text
     */
    function bold(string $text): string
    {
        return '<' . self::B . '>' . $this->esc($text) . '</' . self::B . '>';
    }

    function text_h2(string $title, string $style = ''): string
    {
        return $this->text_h($title, self::H4, self::H2, $style);
    }

    function text_h3(string $title, string $style = ''): string
    {
        return $this->text_h($title, self::H5, self::H3, $style);
    }

    function text_h4(string $title, string $style = ''): string
    {
        return $this->text_h($title, self::H6, self::H3, $style);
    }

    private function text_h(string $title, string $bs_tag, string $tag, string $style = ''): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . $bs_tag . '>' . $title . '</' . $bs_tag . '>';
        } elseif ($style != '') {
            return '<' . $tag . ' ' . self::CLASS_HTML . '="' . $style . '">' . $title . '</' . $tag . '>';
        } else {
            return '<' . $tag . '>' . $title . '</' . $tag . '>';
        }
    }


    /*
     * images
     */

    /**
     * @return string the zukunft.com logo with a link to the home page
     */
    function logo(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_BS);
        } else {
            $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_HTML);
        }
        return $this->ref(api::MAIN_SCRIPT, $img, POD_NAME, self::CLASS_LOGO, true);
    }

    /**
     * @return string the enlarged zukunft.com logo for centered display e.g. on the about page
     */
    function logo_big(): string
    {
        $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_BIG);
        return $this->ref(api::MAIN_SCRIPT, $img, POD_NAME, self::CLASS_LOGO, true);
    }

    /**
     * @return string the enlarged zukunft.com logo wrapped in a flex div for responsive centering
     */
    function logo_flex(): string
    {
        $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_FLEX);
        $ref = $this->ref(api::MAIN_SCRIPT, $img, POD_NAME, self::CLASS_LOGO, true);
        return $this->div($ref, self::CLASS_LOGO_SECTION);
    }

    /*
     * the HTML table functions used in zukunft.com
     */

    /**
     * show a text of link within a table header cell
     * @param string $header_text the text or link that should be shown
     * @param string $scope the bootstrap formatting scope
     * @param string $style the bootstrap formatting class
     * @return string the html code of the table header cell
     */
    function th(string $header_text, string $scope = '', string $style = ''): string
    {
        if ($scope != '') {
            if ($style != '') {
                return '<' . self::TH . ' ' . self::CLASS_HTML . '="' . $style . '" ' . self::SCOPE . '="' . $scope . '">' . $header_text . '</' . self::TH . '>';
            } else {
                return '<' . self::TH . ' ' . self::SCOPE . '="' . $scope . '">' . $header_text . '</' . self::TH . '>';
            }
        } else {
            if ($style != '') {
                return '<' . self::TH . ' ' . self::CLASS_HTML . '="' . $style . '">' . $header_text . '</' . self::TH . '>';
            } else {
                return '<' . self::TH . '>' . $header_text . '</' . self::TH . '>';
            }
        }
    }

    /**
     * @return string with the html code for a line feed
     */
    function lf(): string
    {
        return '<' . self::BR . '>';
    }

    /**
     * create a header column text for each string of the given array
     * @param array $header_cols array of the text or link that should be shown
     * @return string the html code of the table header cell
     */
    function th_row(array $header_cols): string
    {
        $header_text = '';
        foreach ($header_cols as $col_name) {
            $header_text .= $this->th($col_name);
        }
        return $header_text;
    }

    /**
     * show the html code as a table row
     * @param string $row_text the text or link that should be shown
     * @return string the html code of the table row
     */
    function tr(string $row_text): string
    {
        return '<' . self::TR . '>' . $row_text . '</' . self::TR . '>';
    }

    /**
     * show a text of link within a table cell
     * @param string|null $cell_text the text or link that should be shown or null to return an empty cell
     * @param string $style the bootstrap formatting class
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @param string $title the mouseover popup text of the cell, e.g. the full text of a shortened cell
     * @return string the html code of the table cell
     */
    function td(?string $cell_text = '', string $style = '', int $intent = 0, string $title = ''): string
    {
        // just for formatting the html code
        while ($intent > 0) {
            $cell_text .= '&nbsp;';
            $intent = $intent - 1;
        }
        $attributes = '';
        if ($style != '') {
            $attributes .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        // the title is user settable (e.g. the full change text of a shortened cell), so escape it
        if ($title != '') {
            $attributes .= ' ' . self::TITLE_HTML . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
        }
        return '<' . self::TD . $attributes . '>' . $cell_text . '</' . self::TD . '>';
    }

    /**
     * add the table header html maker around the give html code
     * @param string|null $html_rows the text or link that should be shown or null to return an empty cell
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @return string the html code of the table cell
     */
    function thead(?string $html_rows = '', int $intent = 0): string
    {
        // just for formatting the html code
        while ($intent > 0) {
            $html_rows .= '&nbsp;';
            $intent = $intent - 1;
        }
        return '<' . self::THEAD . '>' . $html_rows . '</' . self::THEAD . '>';
    }

    /**
     * add the table body html maker around the give html code
     * @param string|null $html_rows the text or link that should be shown or null to return an empty cell
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @return string the html code of the table cell
     */
    function tbody(?string $html_rows = '', int $intent = 0): string
    {
        // just for formatting the html code
        while ($intent > 0) {
            $html_rows .= '&nbsp;';
            $intent = $intent - 1;
        }
        return '<' . self::TBODY . '>' . $html_rows . '</' . self::TBODY . '>';
    }

    /**
     * create the html code to display a table
     * @param string $tbl_rows the html code of all rows including the header rows
     * @param string $tbl_style the size and style of the table
     * @return string the table html code
     */
    function tbl(string $tbl_rows, string $tbl_style = self::SIZE_FULL): string
    {
        return match ($tbl_style) {
            self::SIZE_HALF => $this->tbl_start_half() . $tbl_rows . $this->tbl_end(),
            styles::STYLE_BORDERLESS => $this->tbl_start_hist() . $tbl_rows . $this->tbl_end(),
            styles::STYLE_BORDERLESS_GREY => $this->tbl_start_borderless_grey() . $tbl_rows . $this->tbl_end(),
            styles::TABLE_PUR => $this->tbl_start_pur() . $tbl_rows . $this->tbl_end(),
            default => $this->tbl_start() . $tbl_rows . $this->tbl_end(),
        };
    }

    private function tbl_start(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table table-striped table-bordered">';
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->tbl_width() . '">';
        }
        return $result;
    }

    function tbl_start_half(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table ' . view_styles::COL_SM_5 . ' table-striped table-bordered">';
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->tbl_width_half() . '">';
        }
        return $result;
    }

    function tbl_start_hist(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table table-borderless text-muted">';
        } else {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="change_hist"';
        }
        return $result;
    }

    function tbl_start_pur(): string
    {
        return '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table">';
    }

    // a borderless table with the standard zukunft.com grey text (the .grey color) instead of the
    // bootstrap text-muted grey of tbl_start_hist; used for the change log table pure
    function tbl_start_borderless_grey(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table table-borderless ' . styles::STYLE_GREY . '">';
        } else {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="' . styles::STYLE_GREY . '">';
        }
        return $result;
    }

    /**
     * a table for a list of selectors
     */
    function tbl_start_select(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table ' . view_styles::COL_SM_10 . ' table-borderless">' . "\n";
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    private function tbl_end(): string
    {
        return '</' . self::TABLE . '>' . "\n";
    }

    /*
     * the form HTML elements used in zukunft.com
     */

    /**
     * create the html code to display a table
     * @param string $form_name the unique name of the html form
     * @param string $tbl_rows the html code of all rows including the header rows
     * @return string the table html code
     */
    function form(
        string $form_name,
        string $tbl_rows,
        string $submit_name = '',
        array  $url_arr = [],
        string $del_call = ''): string
    {
        return $this->form_start($form_name) . $tbl_rows . $this->form_end_with_submit($submit_name, $url_arr, $del_call);
    }

    /**
     * @param string $row_text the html code that should be wrapped into a form row
     * @return string the html code of the form row
     */
    function fr(string $row_text): string
    {
        return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . rest_ctrl::CLASS_FORM_ROW . '">' . $row_text . '</' . self::DIV . '>';
    }

    /**
     * @param string $field the name of the form field
     * @param string|null $txt_value the expected value of the form field
     * @param msg_id $label the expected value of the form field
     * @return string the html code of the form field
     */
    function form_text(string  $field,
                       ?string $txt_value = '',
                       msg_id  $label = msg_id::FORM_FIELD_NAME,
                       string  $type = '',
                       string  $attribute = ''): string
    {
        $result = '';
        if ($label == '') {
            $label = strtoupper($field[0]) . substr($field, 1) . ':';
        }
        if (self::UI_USE_BOOTSTRAP) {
            if ($txt_value == null) {
                $txt_value = '';
            }
            $result .= $this->form_field($field, $label, $txt_value, $type, $attribute);
        } else {
            $result .= $field .
                ': <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_TEXT .
                '" ' . self::NAME . '="' . $field .
                '" ' . self::VALUE . '="' . $txt_value . '">';
        }
        return $result;
    }

    /**
     * add the hidden field
     * @param string $name the internal name of the field
     * @param string $value the value that should be returned
     * @param string $form_name the form that the field joins although it is outside of it e.g. a calculator input, empty for the form around it
     * @return string the html code to add a hidden field
     */
    function form_hidden(string $name, string $value, string $form_name = ''): string
    {
        $form = '';
        if ($form_name != '') {
            $form = ' ' . self::FORM . '="' . $form_name . '"';
        }
        return '<' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_HIDDEN .
            '" ' . self::NAME . '="' . $name .
            '" ' . self::VALUE . '="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $form . '>';
    }

    /**
     * the hidden anti-csrf field carrying the session token that the server validates on a submit
     * every crud form (opened via form_start) and the login and signup forms include it so that a
     * data change without the matching token is rejected (see frontend::request_token_valid)
     * @return string the html code of the hidden session token field
     */
    function form_session_token(): string
    {
        return $this->form_hidden(url_var::SESSION_TOKEN, $_SESSION[url_var::SESSION_TOKEN] ?? '');
    }

    /**
     * end a html form with save, cancel and optional delete buttons
     * @param string $submit_name label for the save button; empty uses the default translated label
     * @param array $url_arr the url vars of the calling page to return to after cancelling
     * @param string $del_call the URL to call for the delete action; empty omits the delete button
     * @return string the HTML code for the form buttons and closing form tag
     */
    /**
     * the end of a form with the submit button, the cancel link back to the calling page and
     * an optional delete link; the same as dsp_form_end, kept for the callers that name it
     *
     * @param string $submit_name the text of the submit button, empty for the default "save"
     * @param array $url_arr the url parameters of the calling page, which the cancel link calls
     * @param string $del_call the url of the delete view or empty if the object cannot be deleted
     * @return string the html code of the form end
     */
    function form_end_with_submit(string $submit_name, array $url_arr, string $del_call = ''): string
    {
        return $this->dsp_form_end($submit_name, $url_arr, $del_call);
    }

    function button_submit(string $submit_name): string
    {
        return $this->button($submit_name, html_base::INPUT_SUBMIT);
    }

    function form_submit(string $submit_name): string
    {
        $txt = '<' . self::INPUT . ' ' . self::TYPE . '="' . self::INPUT_SUBMIT . '"';
        $txt .= ' ' . self::NAME . '="' . url_var::POST_SUBMIT . '"';
        $txt .= ' ' . self::VALUE . '="' . $submit_name . '"';
        $txt .= ' ' . self::CLASS_HTML . '="' . self::CLASS_SUBMIT . '">';
        return $txt;
    }

    /**
     * a plain input field of the simple html forms e.g. the login, with its label linked to the field
     * TODO Prio 0 use this function for all html input fields
     * @param string $type the input type e.g. self::INPUT_TEXT
     * @param string $name the submitted field name, which is the url var e.g. url_var::USERNAME
     * @param string $value the pre-filled value e.g. the username after a failed signup
     * @param string $label the translated label shown above the field, '' for a field without a label
     * @return string the html code of the label (if given) and the input field
     */
    function form_input(string $type, string $name, string $value = '', string $label = ''): string
    {
        $txt = '';
        $id = '';
        if ($label != '') {
            // the label is linked to the field by the id (like form_field), so a click on the label focuses the field
            $id = $this->field_id($name, $label);
            $txt .= $this->label($label, $id) . $this->br();
        }
        $txt .= '<' . self::INPUT . ' ' . self::TYPE . '="' . $type . '" ' . self::NAME . '="' . $name . '"';
        if ($value != '') {
            $txt .= ' ' . self::VALUE . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        if ($id != '') {
            $txt .= ' ' . self::ID . '="' . $id . '"';
        }
        $txt .= ' ' . self::CLASS_HTML . '="' . self::CLASS_INPUT . '">';
        return $txt;
    }

    /**
     * a password input with a css-only "show password" toggle (no javascript, see docs/llm/frontend.md)
     * the field is a text input masked by the .password css class (-webkit-text-security); the checkbox
     * next to it reveals the real password while it is checked via the rules in style_html.css.
     * the type is text and not password because css cannot unmask a native password field; the
     * autocomplete token keeps the password manager working on the masked text input
     *
     * @param string $name the submitted field / url var name e.g. url_var::USER_PASSWORD
     * @param string $show_label the already translated label of the show-password checkbox
     * @param string $autocomplete the autocomplete token e.g. self::AUTOCOMPLETE_NEW_PW for a new password
     * @param string $value the pre-filled value, only used where an existing password may be overwritten
     * @return string the html of the masked password field followed by its show-password checkbox
     */
    function form_input_password(
        string $name,
        string $show_label,
        string $autocomplete = self::AUTOCOMPLETE_CURRENT_PW,
        string $value = ''
    ): string
    {
        $field = '<' . self::INPUT . ' ' . self::TYPE . '="' . self::INPUT_TEXT . '" ' . self::NAME . '="' . $name . '"';
        if ($value != '') {
            $field .= ' ' . self::VALUE . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        $field .= ' ' . self::AUTOCOMPLETE . '="' . $autocomplete . '"';
        $field .= ' ' . self::CLASS_HTML . '="' . self::CLASS_INPUT . ' ' . self::CLASS_PASSWORD . '">';
        return $this->password_show_toggle($field, $name, $show_label);
    }

    /**
     * the bootstrap-styled variant of form_input_password for the admin form that may overwrite a
     * user password; reuses the bootstrap input() rendering as a masked text field and adds the same
     * css-only show-password checkbox (see form_input_password and style_html.css)
     *
     * @param string $url_id the submitted field / url var name e.g. url_var::USER_PASSWORD
     * @param msg_id $msg_id the message id of the field label
     * @param string $show_label the already translated label of the show-password checkbox
     * @param string|null $value the pre-filled password value the admin may overwrite (null if not set)
     * @return string the html of the masked password field followed by its show-password checkbox
     */
    function input_password(
        string      $url_id,
        msg_id      $msg_id,
        string      $show_label,
        string|null $value = ''
    ): string
    {
        $field = $this->input($url_id, $msg_id, $value, self::INPUT_TEXT, self::CLASS_PASSWORD);
        return $this->password_show_toggle($field, $url_id, $show_label);
    }

    /**
     * wrap a masked password input with the css-only show-password checkbox that reveals it while checked
     * @param string $field the html of the masked password input (must carry the .password css class)
     * @param string $name the field name used to build the unique id of the show-password checkbox
     * @param string $show_label the already translated label of the show-password checkbox
     * @return string the password field followed by its show-password checkbox, wrapped for the css toggle
     */
    private function password_show_toggle(string $field, string $name, string $show_label): string
    {
        $show_id = self::SHOW_PASSWORD_PREFIX . $name;
        // the checkbox is the css-only state holder (no javascript); it is visually hidden but keyboard
        // focusable and carries the accessible name because the visible switch is only an icon
        $check = '<' . self::INPUT . ' ' . self::TYPE . '="' . self::INPUT_CHECKBOX . '" '
            . self::CLASS_HTML . '="' . self::CLASS_SHOW_PASSWORD . '" ' . self::ID . '="' . $show_id . '" '
            . self::ARIA_LABEL . '="' . htmlspecialchars($show_label, ENT_QUOTES) . '">';
        // the eye icon is shown while the password is masked and the eye-slash icon while it is revealed;
        // the css in style_html.css toggles which icon is visible based on the checkbox state
        $eye = $this->icon_with_title(icons::PASSWORD_SHOW, self::CLASS_PASSWORD_ICON_SHOW, $show_label);
        $eye .= $this->icon_with_title(icons::PASSWORD_HIDE, self::CLASS_PASSWORD_ICON_HIDE, $show_label);
        $toggle = $check . $this->label($eye, $show_id);
        return $this->span($field . $toggle, self::CLASS_PASSWORD_FIELD);
    }

    /**
     * @param user_message|Message $msg to colle
     * @return string the HTML code of the about page
     */
    function about_page(user_message|Message $msg): string
    {
        global $mtr;
        $result = $this->header($mtr->txt(msg_id::SYSTEM_TITLE_ABOUT), $msg, "center_form");
        $result .= $this->about_body();
        // the about page needs no link to itself in the footer
        $result .= $this->footer(true);
        return $result;
    }

    /**
     * @return string the html code of the about text without the page footer, because the about view
     *                shows it inside the main element and the page adds the footer (see about_page)
     */
    function about_body(): string
    {
        global $mtr;
        $result = $this->dsp_form_center();
        $result .= $this->logo_big();
        $result .= '<' . self::BR . '><' . self::BR . '>';
        $result .= $mtr->txt(msg_id::ABOUT_SPONSORED_BY) . '<' . self::BR . '><' . self::BR . '>';
        $result .= 'zukunft.com AG<' . self::BR . '>';
        $result .= 'Blumentalstrasse 15<' . self::BR . '>';
        $result .= '8707 Uetikon am See<' . self::BR . '>';
        $result .= 'Switzerland<' . self::BR . '><' . self::BR . '>';
        $result .= $this->ref('mailto:timon@zukunft.com', 'timon@zukunft.com') . '<' . self::BR . '><' . self::BR . '>';
        $result .= $mtr->txt(msg_id::ABOUT_MAIN_IDEA) . ' ';
        $result .= $this->ref(def::LINK_PAPER_DELPHI, $mtr->txt(msg_id::ABOUT_PAPER_DELPHI)) . '. ';
        $result .= $mtr->txt(msg_id::ABOUT_ONCE_IMPLEMENTED) . ' ';
        $result .= $this->ref(def::LINK_PAPER_IMPERATIVE, $mtr->txt(msg_id::ABOUT_PAPER_IMPERATIVE)) . '.<br><br>';
        $result .= $mtr->txt(msg_id::ABOUT_SUPPORTS) . ' ';
        $result .= $this->ref(def::LINK_GITHUB_TREAM, $mtr->txt(msg_id::OPEN_SOURCE), $mtr->txt(msg_id::ABOUT_GITHUB_LINK)) . ' ' . $mtr->txt(msg_id::ABOUT_PORTFOLIO_MGMT) . '<br><br>';
        $tream_img = $this->img('/src/main/resources/images/TREAM_logo.jpg', 'TREAM', 'logo-tream');
        $result .= $this->ref(def::LINK_TREAM_DEMO, $tream_img, $mtr->txt(msg_id::ABOUT_TREAM_DEMO), '', true) . '<' . self::BR . '><' . self::BR . '>';
        $result .= '</' . self::DIV . '>   ';
        return $result;
    }


    /*
     * output device specific support functions for the pure HTML version
     */

    /**
     * @return string get the normal table width (should be based on the display size)
     */
    private function tbl_width(): string
    {
        return self::WIDTH_FULL;
    }

    private function tbl_width_half(): string
    {
        return self::WIDTH_HALF;
    }

    /**
     * @return string display an explaining sub line e.g. (in mio CHF)
     */
    private function line_small($line_text): string
    {
        return '<' . self::SMALL . '>' . $line_text . '</' . self::SMALL . '><' . self::BR . '>';
    }

    /**
     * converts object class name to an edit php script name
     *
     */
    function edit_url(string $class): string
    {
        return $class . rest_ctrl::UPDATE . rest_ctrl::EXT;
    }

    /**
     * display a list that can be sorted using the fixed field "order_nbr"
     * $sql_result - list of the query results
     *
     * @param array $item_lst a list of objects that have at least an id and a name
     */
    function list_sort(
        array  $item_lst,
        string $class,
        string $script_parameter,
        array  $url_arr = []): string
    {
        global $mtr;
        $btn = new button();
        $result = '';

        $row_nbr = 0;
        $num_rows = count($item_lst);
        foreach ($item_lst as $key => $item) {
            // list of all possible view entries
            $row_nbr = $row_nbr + 1;
            $edit_script = $this->edit_url($class);
            $url = $this->url_old($edit_script, $key, $url_arr);
            $result .= $this->ref($url, $item);
            if ($row_nbr > 1) {
                $url = $this->url_old($edit_script, $key, $url_arr, '&move_up=' . $key);
                $result .= $this->ref($url, $mtr->txt(msg_id::UP));
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            if ($row_nbr < $num_rows) {
                $url = $this->url_old($edit_script, $key, $url_arr, '&move_down=' . $key);
                $result .= $this->ref($url, $mtr->txt(msg_id::DOWN));
            }
            $result .= ' ';
            // TODO Prio 1 review
            //$result .= $but->del('Delete ' . $class, $class . '?id=' . $script_parameter . '&del=' . $key);
            $result .= '<' . self::BR . '>';
        }

        return $result;
    }

    /**
     * display a paged, editable list of elements e.g. verbs
     *
     * @param array $item_lst a list of objects that have at least an id and a name
     * @param string $class the object class name e.g. a view
     * @param array $url_arr the url vars of the calling page for the back / ctrl-z function
     * @return string the html code to display the list
     */
    function list(array $item_lst, string $class, array $url_arr = []): string
    {
        $result = "";

        $lib = new library();
        $class_name = $lib->class_to_name($class);

        foreach ($item_lst as $item) {
            if ($item->id() != null) {
                $url = $this->url_back($class_name . rest_ctrl::UPDATE, $item->id(), $url_arr);
                $result .= $this->ref($url, $this->esc($item->name()));
                $result .= '<' . self::BR . '>';
            }
        }
        $url_add = $this->url_back($class_name . rest_ctrl::CREATE, 0, $url_arr);
        $msg_id = $lib->class_to_add_msg_id($class);
        $result .= (new button($url_add, $url_arr))->add($msg_id);
        $result .= '<' . self::BR . '>';

        return $result;
    }

    /*
     * to dismiss / replace
     */

// ------------------------------------------------------------------
// output device specific support functions for the pure HTML version
// ------------------------------------------------------------------

// get the normal table width (should be based on the display size)
    function dsp_tbl_width(): string
    {
        return '800px';
    }

    function dsp_tbl_width_half(): string
    {
        return '400px';
    }

// display an explaining subtitle e.g. (in mio CHF)
    function dsp_line_small($line_text): string
    {
        return '<' . self::SMALL . '>' . $line_text . '</' . self::SMALL . '><' . self::BR . '>';
    }


// ------------------------
// single element functions
// ------------------------

// simply to display headline text
    function dsp_text_h1($title, $style = ''): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . self::H2 . '>' . $title . '</' . self::H2 . '>';
        } elseif ($style != '') {
            return '<' . self::H1 . ' ' . self::CLASS_HTML . '="' . $style . '">' . $title . '</' . self::H1 . '>';
        } else {
            return '<' . self::H1 . '>' . $title . '</' . self::H1 . '>';
        }
    }

    function dsp_text_h2($title, $style = ''): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . self::H4 . '>' . $title . '</' . self::H4 . '>';
        } elseif ($style != '') {
            return '<' . self::H2 . ' ' . self::CLASS_HTML . '="' . $style . '">' . $title . '</' . self::H2 . '>';
        } else {
            return '<' . self::H2 . '>' . $title . '</' . self::H2 . '>';
        }
    }

    function dsp_text_h3($title, $style = ''): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . self::H6 . '>' . $title . '</' . self::H6 . '>';
        } elseif ($style != '') {
            return '<' . self::H3 . ' ' . self::CLASS_HTML . '="' . $style . '">' . $title . '</' . self::H3 . '>';
        } else {
            return '<' . self::H3 . '>' . $title . '</' . self::H3 . '>';
        }
    }

// after simple add views e.g. for a value automatically go back to the calling page
    /**
     * redirect to the calling page after an action, so that a reload never repeats the action
     *
     * @param array $url_arr the url parameters of the calling page
     * @param object|null $usr unused, kept for the callers that name the parameters by position;
     *                         untyped on purpose, so html_base needs no include of the user class
     * @return string always empty, because the redirect header replaces the page
     */
    function dsp_go_back(array $url_arr, ?object $usr = null): string
    {
        $result = '';

        if ($url_arr == []) {
            log_err("Internal error: go back page missing.", "dsp_header->dsp_go_back");
            header("Location: " . $this->url_with_user(api::MAIN_SCRIPT)); // go back to the fallback page
        } else {
            header("Location: " . $this->url_with_user($this->url_with_id_var($this->page_url($url_arr))));
        }

        return $result;
    }

// display a simple text button
    function dsp_btn_text($btn_name, $call): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= $this->ref($call, $btn_name, '', 'btn btn-outline-secondary btn-space');
        } else {
            $result .= $this->ref($call, $btn_name);
        }
        return $result;
    }

    /**
     * display an error text to the user; use this function always for easy redesign of the error messages
     * @param string $err_text the html code of the error text e.g. a translated message with <b> tags
     * @return string the error text as a red inline text
     */
    function dsp_err(string $err_text): string
    {
        return $this->span($err_text, styles::TEXT_DANGER);
    }

// display a box with the history and the links
    function dsp_link_hist_box($comp_name, $comp_html,
                               $nbrs_name, $nbrs_html,
                               $hist_name, $hist_html,
                               $link_name, $link_html): string
    {

        $result = "";

        $comp_id = str_replace(' ', '_', strtolower($comp_name));
        $nbrs_id = str_replace(' ', '_', strtolower($nbrs_name));
        $hist_id = str_replace(' ', '_', strtolower($hist_name));
        $link_id = str_replace(' ', '_', strtolower($link_name));

        $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . view_styles::COL_SM_5 . '">';
        $result .= '<' . self::UL . ' ' . self::CLASS_HTML . '="nav nav-tabs">';
        $result .= '  <' . self::LI . ' ' . self::CLASS_HTML . '="nav-item">';
        $result .= '    <' . self::A . ' ' . self::CLASS_HTML . '="nav-link active" ' . self::ID . '="' . $comp_id . '-tab" data-toggle="tab" ' . self::HREF . '="#' . $comp_id . '" role="tab" aria-controls="' . $comp_id . '" aria-selected="true">' . $comp_name . '</' . self::A . '>';
        $result .= '  </' . self::LI . '>';
        if ($nbrs_name <> '') {
            $result .= '  <' . self::LI . ' ' . self::CLASS_HTML . '="nav-item">';
            $result .= '    <' . self::A . ' ' . self::CLASS_HTML . '="nav-link" ' . self::ID . '="' . $nbrs_id . '-tab" data-toggle="tab" ' . self::HREF . '="#' . $nbrs_id . '" role="tab" aria-controls="' . $nbrs_id . '" aria-selected="false">' . $nbrs_name . '</' . self::A . '>';
            $result .= '  </' . self::LI . '>';
        }
        $result .= '  <' . self::LI . ' ' . self::CLASS_HTML . '="nav-item">';
        $result .= '    <' . self::A . ' ' . self::CLASS_HTML . '="nav-link" ' . self::ID . '="' . $hist_id . '-tab" data-toggle="tab" ' . self::HREF . '="#' . $hist_id . '" role="tab" aria-controls="' . $hist_id . '" aria-selected="false">' . $hist_name . '</' . self::A . '>';
        $result .= '  </' . self::LI . '>';
        $result .= '  <' . self::LI . ' ' . self::CLASS_HTML . '="nav-item">';
        $result .= '    <' . self::A . ' ' . self::CLASS_HTML . '="nav-link" ' . self::ID . '="' . $link_id . '-tab" data-toggle="tab" ' . self::HREF . '="#' . $link_id . '" role="tab" aria-controls="' . $link_id . '" aria-selected="false">' . $link_name . '</' . self::A . '>';
        $result .= '  </' . self::LI . '>';
        $result .= '</' . self::UL . '>';
        $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="tab-content border-right border-bottom border-left rounded-bottom" ' . self::ID . '="comp-hist-tab-content">';
        $result .= '  <' . self::DIV . ' ' . self::CLASS_HTML . '="tab-pane fade active show" ' . self::ID . '="' . $comp_id . '" role="tabpanel" aria-labelledby="' . $comp_id . '-tab">';
        $result .= '    <' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . '">';
        $result .= $comp_html;
        $result .= '    </' . self::DIV . '>';
        $result .= '  </' . self::DIV . '>';
        if ($nbrs_name <> '') {
            $result .= '  <' . self::DIV . ' ' . self::CLASS_HTML . '="tab-pane fade" ' . self::ID . '="' . $nbrs_id . '" role="tabpanel" aria-labelledby="' . $nbrs_id . '-tab">';
            $result .= '    <' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . '">';
            $result .= $nbrs_html;
            $result .= '    </' . self::DIV . '>';
            $result .= '  </' . self::DIV . '>';
        }
        $result .= '  <' . self::DIV . ' ' . self::CLASS_HTML . '="tab-pane fade" ' . self::ID . '="' . $hist_id . '" role="tabpanel" aria-labelledby="' . $hist_id . '-tab">';
        $result .= '    <' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . '">';
        $result .= $hist_html;
        $result .= '    </' . self::DIV . '>';
        $result .= '  </' . self::DIV . '>';
        $result .= '  <' . self::DIV . ' ' . self::CLASS_HTML . '="tab-pane fade" ' . self::ID . '="' . $link_id . '" role="tabpanel" aria-labelledby="' . $link_id . '-tab">';
        $result .= '    <' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . '">';
        $result .= $link_html;
        $result .= '    </' . self::DIV . '>';
        $result .= '  </' . self::DIV . '>';
        $result .= '</' . self::DIV . '>'; // of tab content

        return $result;
    }

    /**
     * a tab box that shows the given tabs side by side, the first tab active by default and the tab
     * whose id matches the url fragment (e.g. '#changes') active otherwise; one section per tab holds
     * the tab label and its content so that the pure css :target rule (see style_html.css) both shows
     * the selected content and highlights its label - no javascript (see docs/llm/frontend.md)
     * e.g. on the word page a "Views" tab next to a "Changes" tab
     *
     * @param array $tabs an ordered map of tab label => tab html content
     * @return string the html code of the tab box or an empty string if no tab has content
     */
    function tab_box(array $tabs): string
    {
        $result = '';
        // drop tabs without content so an empty change log or view list shows no empty tab
        $tabs = array_filter($tabs, fn($content) => $content !== '');
        if ($tabs != []) {
            $sections = '';
            foreach ($tabs as $label => $content) {
                $tab_id = $this->tab_id($label);
                // one section per tab: the label link sets the '#<tab_id>' fragment and the css :target
                // rule on the section then shows this content and highlights this label (style_html.css)
                $label_link = $this->ref('#' . $tab_id, $label, '', self::CLASS_TAB_LABEL);
                $pane = $this->div($this->div($content, self::CLASS_CONTAINER), self::CLASS_TAB_PANE);
                $sections .= $this->div($label_link . $pane, self::CLASS_TAB, $tab_id);
            }
            $result = $this->div($sections, self::CLASS_TABS);
        }
        return $result;
    }

    /**
     * the html id of a tab of a tab box, which is also the url fragment that selects the tab
     * (the css :target rule of style_html.css); public, so that a link from another page can open
     * a page with one tab selected e.g. the all user overwrites column links to the 'others' tab
     * of the changed object
     *
     * @param string $label the translated tab label as tab_box gets it
     * @return string the html id of the tab
     */
    function tab_id(string $label): string
    {
        return str_replace(' ', '_', strtolower($label));
    }

// -----------------------
// table element functions
// -----------------------

    function dsp_tbl_start(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table table-striped table-bordered">' . "\n";
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->dsp_tbl_width() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_half(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table ' . view_styles::COL_SM_5 . ' table-borderless">' . "\n";
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_hist(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table table-borderless text-muted">' . "\n";
        } else {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="change_hist"' . "\n";
        }
        return $result;
    }

// a table for a list of selectors
    function dsp_tbl_start_select(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<' . self::TABLE . ' ' . self::CLASS_HTML . '="table ' . view_styles::COL_SM_10 . ' table-borderless">' . "\n";
        } else {
            $result = '<' . self::TABLE . ' ' . self::STYLE . '="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_end(): string
    {
        $result = '</' . self::TABLE . '>' . "\n";
        return $result;
    }

// -------------------------
// formula element functions
// -------------------------

// start a html form; the form name must be identical with the php script name
    function dsp_form_start($form_name): string
    {
        // switch on post forms for private values
        // return '<form action="'.$form_name.'.php" method="post" id="'.$form_name.'">';
        return '<' . self::FORM . ' ' . self::ACTION . '="' . $form_name . '.php" ' . self::ID . '="' . $form_name . '">';
    }

// end a html form
    /**
     * the end of a form with the submit button, the cancel link back to the calling page and
     * an optional delete link
     *
     * @param string $submit_name the text of the submit button, empty for the default "save"
     * @param array $url_arr the url parameters of the calling page, which the cancel link calls;
     *                       an empty array shows no cancel link, because no page is known
     * @param string $del_call the url of the delete view or empty if the object cannot be deleted
     * @return string the html code of the form end
     */
    function dsp_form_end(string $submit_name, array $url_arr, string $del_call = ''): string
    {
        global $mtr;
        $btn = new button();
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            if ($submit_name == "") {
                $result .= '<' . self::BUTTON . ' ' . self::TYPE . '="submit" ' . self::CLASS_HTML . '="btn btn-outline-success btn-space">' . $mtr->txt(msg_id::FORM_BUTTON_SAVE) . '</' . self::BUTTON . '>';
            } else {
                $result .= '<' . self::BUTTON . ' ' . self::TYPE . '="submit" ' . self::CLASS_HTML . '="btn btn-outline-success btn-space">' . $submit_name . '</' . self::BUTTON . '>';
            }
            if ($url_arr != []) {
                $result .= $this->ref($this->page_url($url_arr), $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', 'btn btn-outline-secondary btn-space');
            }
            if ($del_call <> '') {
                $result .= $this->ref($del_call, $mtr->txt(msg_id::SYSTEM_TITLE_OBJECT_NAMED_DELETE), '', 'btn btn-outline-danger');
            }
        } else {
            if ($submit_name == "") {
                $result .= '<' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_SUBMIT . '">';
            } else {
                $result .= '<' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_SUBMIT .
                    '" ' . self::VALUE . '="' . $submit_name . '">';
            }
            if ($url_arr != []) {
                $result .= $btn->back($url_arr);
            }
            if ($del_call <> "") {
                $result .= $btn->del(msg_id::DEL, $del_call);
            }
        }
        $result .= '</' . self::FORM . '>';
        return $result;
    }

    /**
     * centre a html page
     * @param string $txt the html code that should be moved to the centre
     * @return string the centred html code
     */
    function div_center(string $txt): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . ' ' . styles::TEXT_CENTER . '">' . $txt . '</' . self::DIV . '>';
        } else {
            return '<' . self::DIV . ' ' . self::CLASS_HTML . '="center_form">' . $txt . '</' . self::DIV . '>';
        }
    }

    function dsp_form_center(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . self::CLASS_CONTAINER . ' ' . styles::TEXT_CENTER . '">';
        } else {
            return '<' . self::DIV . ' ' . self::CLASS_HTML . '="center_form">';
        }
    }

// add the element id, which should always be using the field "id"
    function dsp_form_id($id): string
    {
        return '<' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_HIDDEN .
            '" ' . self::NAME . '="' . self::ID . '" ' . self::VALUE . '="' . $id . '">';
    }

    /**
     * html hidden field
     * @param string $field the name of the hidden
     * @param int $id
     * @return string the html code for a hidden form field
     */
    function dsp_form_hidden(string $field, int $id): string
    {
        return '<' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_HIDDEN .
            '" ' . self::NAME . '="' . $field .
            '" ' . self::VALUE . '="' . $id . '">';
    }

    // TODO Prio 0 easy deprecate and use
// add the text field to a form
    function dsp_form_text($field, $txt_value, msg_id $label, $class = view_styles::COL_SM_4, $attribute = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= $this->form_field($field, $label, $txt_value, $class, $attribute);
        } else {
            $result .= '' . $field .
                ': <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_TEXT .
                '" ' . self::NAME . '="' . $field .
                '" ' . self::VALUE . '="' . $txt_value . '">';
        }
        return $result;
    }

// add the text big field to a form
    function dsp_form_text_big($field, $txt_value, msg_id $label, $class = view_styles::COL_SM_4, $attribute = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= $this->form_field($field, $label, $txt_value, $class, $attribute);
        } else {
            $result .= '' . $field .
                ': <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_TEXT .
                '" ' . self::NAME . '="' . $field .
                '" ' . self::CLASS_HTML . '="resizedTextbox" ' . self::VALUE . '="' . $txt_value . '">';
        }
        return $result;
    }

    /**
     * add the field to a form
     */
    function dsp_form_fld_checkbox($field, $is_checked, $label): string
    {
        $result = '';
        if ($label == '') {
            $label = $field;
        }
        if (self::UI_USE_BOOTSTRAP) {
            $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="form-check-inline">';
            $result .= '<' . self::LABEL . ' ' . self::CLASS_HTML . '="form-check-label">';
            $result .= '<' . self::INPUT . ' ' . self::CLASS_HTML . '="form-check-input" ' . self::TYPE . '="checkbox" ' . self::NAME . '="' . $field . '"';
            if ($is_checked) {
                $result .= ' checked';
            }
            $result .= '>' . $label . '</' . self::LABEL . '>';
            $result .= '</' . self::DIV . '>';
        } else {
            $result .= '  <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_CHECKBOX .
                '" ' . self::NAME . '="' . $field . '"';
            if ($is_checked) {
                $result .= ' checked';
            }
            $result .= '> ';
            $result .= $label;
        }
        return $result;
    }

    /**
     * display a file selector form
     */
    function dsp_form_file_select(): string
    {
        $result = '';
        /*
        if (self::UI_USE_BOOTSTRAP) {
          $result .= ' <form>';
          $result .= '  <div class="custom-file">';
          $result .= '    <input type="' . html_base::INPUT_FILE .
                '" class="custom-file-input" id="fileToUpload">';
          $result .= '    <label class="custom-file-label" for="fileToUpload">Choose file</label>';
          $result .= '  </div>';
          //$result .= '  <button type="submit" id="submit" name="import" class="btn-submit">Import</button>';
          $result .= '</form>';

          $result .= '<script>';
          $result .= '$(".custom-file-input").on("change", function() {';
          $result .= '  var fileName = $(this).val().split("\\\\").pop();';
          $result .= '  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);';
          $result .= '});';
          $result .= '</script> ';
        } else {
        */
        $result .= ' <' . self::FORM . ' ' . self::ACTION . '="'
            . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::IMPORT . '" ' . self::METHOD . '="post" ' . self::ENCTYPE . '="multipart/form-data">';
        $result .= $this->form_user();
        $result .= '   Select JSON to upload:';
        $result .= '   <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_FILE .
            '" ' . self::NAME . '="fileToUpload" ' . self::ID . '="fileToUpload">';
        $result .= '   <' . self::INPUT . ' ' . self::TYPE . '="' . html_base::INPUT_SUBMIT .
            '" ' . self::VALUE . '="Upload JSON" ' . self::NAME . '="submit">';
        $result .= ' </' . self::FORM . '>';
        //}
        return $result;
    }


    /*
     * base elements - functions for all html elements used in zukunft.com
     */

    /**
     * create the html code for a label
     * TODO Prio 1
     * @param string $text the translated text to be shown as a label
     * @param string $for the url id of the html form field
     * @return string the html code to show the label
     */
    function label(string $text, string $for = ''): string
    {
        if ($for == '') {
            $for = strtolower($text);
        }
        return '<' . self::LABEL . ' ' . self::FOR . '="' . $for . '">' . $text . '</' . self::LABEL . '>';
    }

    /**
     * a text button that submits the form only to refresh some fields, e.g. the 'find and next' of the
     * phrase selection of a new value; as the first submit of a form the enter key triggers it
     *
     * @param string $text the translated text shown to the user
     * @param string $refresh the url_var::REFRESH value that says which fields are refreshed
     * @return string the html code of the refresh button in a column of its own
     */
    function button_refresh_text(string $text, string $refresh): string
    {
        $btn = $this->button_link(htmlspecialchars($text, ENT_QUOTES), url_var::REFRESH, $refresh, '', self::BS_BTN_FIELD);
        return $this->div($btn, self::BS_BTN_FIELD_COL);
    }

    /**
     * a submit button that looks like a link, e.g. the 'add' of the component list of a view; it is
     * a submit and not a link, because only a submit sends the value that the user has selected in
     * the form (see docs/llm/frontend.md: no javascript)
     *
     * @param string $text the text shown to the user
     * @param string $form_name the form that the button submits, needed if the button is not inside it
     * @return string the html code of the submit button that looks like a link
     */
    function button_submit_text(string $text, string $form_name = ''): string
    {
        return $this->button_link(htmlspecialchars($text, ENT_QUOTES),
            url_var::POST_SUBMIT, '', $form_name, styles::TEXT_BUTTON);
    }

    /**
     * @param string $inner the html code shown inside the button e.g. an icon or an escaped text
     * @param string $name the url var that the button posts e.g. url_var::REFRESH
     * @param string $value the value posted for the url var, empty for a plain submit
     * @param string $form_name the form that the button submits, empty if the button is inside it
     * @param string $style the css class of the button e.g. styles::TEXT_BUTTON for a plain text
     * @return string the html code of a submit button that is styled like a link
     */
    private function button_link(
        string $inner,
        string $name,
        string $value,
        string $form_name = '',
        string $style = self::BS_BTN_ICON
    ): string
    {
        $form_attribute = '';
        if ($form_name != '') {
            $form_attribute = ' ' . self::FORM . '="' . $form_name . '"';
        }
        return '<' . self::BUTTON
            . ' ' . self::CLASS_HTML . '="' . $style . '"'
            . ' ' . self::TYPE . '="' . self::INPUT_SUBMIT . '"'
            . ' ' . self::NAME . '="' . $name . '"'
            . ' ' . self::VALUE . '="' . htmlspecialchars($value, ENT_QUOTES) . '"'
            . $form_attribute . '>'
            . $inner
            . '</' . self::BUTTON . '>';
    }

    /**
     * translate and create the html code for a label
     * TODO Prio 3 use if if possible
     * @param msg_id $msg_id message id that should be translated to the text to be shown as a label
     * @param string $for the url id of the html form field
     * @return string the html code to show the label
     */
    function label_lan(msg_id $msg_id, string $for = ''): string
    {
        global $mtr;
        return $this->label($mtr->txt($msg_id), $for);
    }

    /**
     * @param string $url_id the url id of the input field e.g. url_var::LATEX
     * @param msg_id $msg_id the msg_id of the title of the input field e.g. LaTeX
     * @return string the html id of the input field, e.g. to point the label of a neighbour column to it
     */
    function form_field_id(string $url_id, msg_id $msg_id): string
    {
        global $mtr;
        return $this->field_id($url_id, $mtr->txt($msg_id));
    }

    /**
     * the user-readable html id of a form field, derived from its translated label
     * (e.g. id="mask"); a disambiguating url-var suffix is kept so the id stays unique
     * when one label is reused across several forms on a page (e.g. an add form whose
     * url var carries "_add" -> id="name_add" next to the edit form's id="name")
     * @param string $url_id the url var of the field, optionally with a "_..." suffix
     * @param string $label the translated field label, '' for an unlabelled field
     * @return string the html id, matching the <label for> built in form_field
     */
    private function field_id(string $url_id, string $label): string
    {
        if ($label != '') {
            // a label may contain spaces ("Plural reverse") or signs ("place name / post code"); an html
            // id may not contain spaces and is easier to select without signs, so each run of them is one '_'
            $id = trim(preg_replace('/[^\p{L}\p{N}_-]+/u', '_', strtolower($label)), '_');
            $sep = strpos($url_id, '_');
            if ($sep !== false) {
                $id .= substr($url_id, $sep);
            }
        } elseif ($url_id != '') {
            $id = strtolower($url_id);
        } else {
            $id = '1';
        }
        return $id;
    }

    /**
     * create the HTML code for an input field
     * the submitted field name is the url var ($url_id) so the form posts keys the
     * url mapper understands (e.g. name="m"); the user-readable html id comes from the
     * translated $msg_id (e.g. id="mask") and matches the <label for> set in form_field.
     * the label text must never be the submit name (that breaks url_to_standard, see url_mapper)
     * @param string $url_id the url id of the input field e.g. url_var::NAME
     * @param msg_id $msg_id the msg_id of the title of the input field e.g. Name
     * @param string|null $value the suggested value which is in most cases the value already saved in the db
     * @param string $type the type of the input e.g. a text or if not set a submit field
     * @param string $class_add the formatting code to adjust the formatting e.g. extend the description to the full screen width
     * @param string $placeholder
     * @param string $form_name the form that the field is posted with if it is placed outside of it, '' for the surrounding form
     * @return string the HTML code for the field
     */
    function input(
        string      $url_id,
        msg_id      $msg_id,
        string|null $value = '',
        string      $type = '',
        string      $class_add = '',
        string      $placeholder = '',
        string      $form_name = ''): string
    {
        $name = '';
        if ($url_id != '') {
            $name = ' name="' . $url_id . '"';
        }
        $id = $this->form_field_id($url_id, $msg_id);
        if ($value != '') {
            // escape the value so a name/description with " < > & cannot break out of the
            // attribute; the browser decodes it back so the resubmitted value is unchanged
            $value = ' value="' . htmlspecialchars($value, ENT_QUOTES) . '"';
        }
        if ($type != '') {
            $type = ' type="' . $type . '"';
        }
        if ($class_add != '' and $class_add[0] != ' ') {
            $class_add = ' ' . $class_add;
        }
        $class = ' class="' . self::BS_FORM . $class_add . '"';
        if ($placeholder != '') {
            $placeholder = ' placeholder="' . $placeholder . '"';
        }
        $id = ' id="' . $id . '"';
        $form = '';
        if ($form_name != '') {
            $form = ' ' . self::FORM . '="' . $form_name . '"';
        }
        return '<' . self::INPUT . $class . $type . $name . $id . $value . $placeholder . $form . '>';
    }

    /**
     * a number input with the limits and the step that the browser enforces, e.g. an input field
     * of a use case page; the id equals the name, so a label can point to it
     *
     * @param string $name the url var of the number e.g. url_var::NUMERIC_VALUE with the phrase id
     * @param float|null $value the number shown, null for an empty field
     * @param float|null $min the lowest number accepted, null for no limit
     * @param float|null $max the highest number accepted, null for no limit
     * @param float|null $step the increment of the number, null for any number
     * @param string $form_name the form that the input joins by its form attribute, '' for the surrounding form
     * @param int|null $decimals the minimum decimals of the shown number e.g. 2 for a share, null for the number as it is
     * @return string the html code of the number input
     */
    function input_number(
        string $name,
        ?float $value,
        ?float $min,
        ?float $max,
        ?float $step,
        string $form_name = '',
        ?int   $decimals = null
    ): string
    {
        $attr = ' ' . self::TYPE . '="number" ' . self::NAME . '="' . $name . '" ' . self::ID . '="' . $name . '"';
        foreach (['value' => $value, 'min' => $min, 'max' => $max, 'step' => $step] as $key => $num) {
            if ($num !== null) {
                // only the shown number gets the decimals e.g. 0.90 for a share
                $attr .= ' ' . $key . '="' . $this->number_value($num, $key == 'value' ? $decimals : null) . '"';
            }
        }
        if ($form_name != '') {
            $attr .= ' ' . self::FORM . '="' . $form_name . '"';
        }
        return '<' . self::INPUT . $attr . '>';
    }

    /**
     * the number as an html attribute needs it; the decimals are a minimum, so that a field or an
     * option never holds a rounded copy of the number, which would be the input of the calculation
     *
     * @param float $num a number
     * @param int|null $decimals the minimum number of decimals e.g. 2 for a share, null for the number as it is
     * @return string the number with a point, with at least the decimals e.g. "1.00" and "0.855",
     *                or else without trailing zeros e.g. "0.5"
     */
    function number_value(float $num, ?int $decimals = null): string
    {
        $result = rtrim(rtrim(sprintf('%.12F', $num), '0'), '.');
        if ($decimals !== null and $this->decimals($result) < $decimals) {
            $result = number_format($num, $decimals, '.', '');
        }
        return $result;
    }

    /**
     * @param string $num_txt a number with a point and without trailing zeros e.g. "0.855"
     * @return int the number of decimals e.g. 3, 0 for a number without a point
     */
    private function decimals(string $num_txt): int
    {
        $pos = strpos($num_txt, '.');
        $result = 0;
        if ($pos !== false) {
            $result = strlen($num_txt) - $pos - 1;
        }
        return $result;
    }

    function div_form(string $text, string $style = ''): string
    {
        return $this->div_bs($text, 'form-group ' . $style);
    }

    function div_row(string $text, string $style = ''): string
    {
        return $this->div_bs($text, 'row ' . $style);
    }

    /**
     * a column of a row that is moved below the previous column on small screens
     * because the columns of a row wrap if the screen is smaller than the sum of the column widths
     *
     * @param string $text the html code to show within the column
     * @param int $min_width the minimal column width in pixel before the column is wrapped to a new line
     * @return string the html code of the wrapping column
     */
    function div_col_min_width(string $text, int $min_width): string
    {
        return '<div class="col" style="min-width: ' . $min_width . 'px">' . $text . '</div>';
    }

    /**
     * combine the given columns to one row that shows them side by side on wide screens
     * and wraps them onto fewer rows (down to a single stacked column) as the screen gets
     * narrower than the configured side widths;
     * used for the fixed columns of a 'side or below' view group (view_exe::dsp_entries)
     * and for the data driven columns of the phrase values view (value_list::columns_by_phrase)
     *
     * @param array $col_lst the html code of the columns; empty columns are skipped
     * @param user_message $msg to enrich with problems and suggested solutions
     * @return string the html code of the row with the wrapping columns
     */
    function div_row_wrapping_cols(array $col_lst, user_message $msg): string
    {
        global $ui_sys;

        if ($ui_sys?->cfg !== null) {
            $min_width = (int)$ui_sys->cfg->get_by(
                [triples::SIDE_WIDTH, words::MIN, words::LAYOUT, words::FRONTEND, words::USER],
                $msg, def::FALLBACK_MIN_SIDE_WIDTH);
            $wide_width = (int)$ui_sys->cfg->get_by(
                [triples::SIDE_WIDTH, words::MAX, words::LAYOUT, words::FRONTEND, words::USER],
                $msg, def::FALLBACK_WIDE_SIDE_WIDTH);
        } else {
            $min_width = def::FALLBACK_MIN_SIDE_WIDTH;
            $wide_width = def::FALLBACK_WIDE_SIDE_WIDTH;
        }
        // size each column so that up to MAX_SIDE_COLUMNS fit at the configured wide width
        // and the flex row wraps to fewer columns as the screen gets narrower;
        // never narrower than half the min side width so two columns still fit above it
        $col_width = max(
            (int)round($wide_width / position_types::MAX_SIDE_COLUMNS),
            (int)round($min_width / 2));
        $cols = '';
        foreach ($col_lst as $col) {
            if ($col != '') {
                $cols .= $this->div_col_min_width($col, $col_width);
            }
        }
        return $this->div_row($cols);
    }

    function add_style(string $text, ?int $style_id = null): string
    {
        if ($style_id != null and $text != '') {
            $style_txt = $this->get_style_code($style_id);
            $text = $this->div_bs($text, $style_txt);
        }
        return $text;
    }

    function get_style_code(?int $style_id = null): string
    {
        global $ui_sys;
        $result = '';
        if ($style_id != null) {
            $result = $ui_sys?->typ_lst_cache?->msk_sty?->get_code_id($style_id) ?? '';
        }
        return $result;
    }

    /**
     * start a html form; the form name must be identical with the php script name
     * @param string $form_name the name and id of the form
     * @return string the HTML code to start a form
     */
    function form_start(string $form_name): string
    {
        // use a GET form for now so the submitted field values stay visible in the url for debugging;
        // the named submit button (POST_SUBMIT) still routes the submit through url_to_action, so this
        // can be switched back to method="post" once the confirm / write workflow is stable
        $action = ' ' . self::ACTION . '="' . api::HOST_SAME . api::MAIN_SCRIPT_EXT . '"';
        $id = ' ' . self::ID . '="' . $form_name . '"';

        // every crud form carries the anti-csrf session token as its first hidden field so the
        // server can reject a data change that does not carry the matching token (see form_session_token)
        return '<' . self::FORM . $action . $id . '>' . $this->form_session_token() . $this->form_user();
    }

    /**
     * start a html form; the form name must be identical with the php script name
     * @param string $action the script name
     * @param string $method post, get or any other html form method
     * @param string $txt the inner html code of the form
     * @return string the HTML code of the form
     */
    function form_simple(string $action, string $method, string $txt): string
    {
        // switch on post forms for private values
        $action = ' ' . self::ACTION . '="' . $action . '"';
        $method = ' ' . self::METHOD . '="' . $method . '"';

        return '<' . self::FORM . $action . $method . '>' . $this->form_user() . $txt . '</' . self::FORM . '>';
    }

    /**
     * a form that only shows a page again with the entered values e.g. the refresh of a process catch
     * page; the inputs of other components join it by their form attribute; it keeps the user id of
     * form_user, so the user stays logged in, but it has no named submit, because it never changes data
     *
     * @param string $form_name the id of the form that the inputs of the page name in their form attribute
     * @param string $txt the inner html code of the form e.g. the hidden page vars and the submit button
     * @return string the HTML code of the form
     */
    function form_get(string $form_name, string $txt): string
    {
        $action = ' ' . self::ACTION . '="' . api::HOST_SAME . api::MAIN_SCRIPT_EXT . '"';
        $id = ' ' . self::ID . '="' . $form_name . '"';
        return '<' . self::FORM . $action . $id . '>' . $this->form_user() . $txt . '</' . self::FORM . '>';
    }

    /**
     * a form that changes data and that the inputs of other components join by their form attribute
     * e.g. the save of a calculator page; it posts the anti-csrf session token and the user id,
     * and its submit button posts POST_SUBMIT, so that the entry point runs url_to_action
     *
     * @param string $form_name the id of the form that the inputs of the page name in their form attribute
     * @param string $txt the inner html code of the form e.g. the hidden page vars and the submit button
     * @return string the HTML code of the form
     */
    function form_post(string $form_name, string $txt): string
    {
        $action = ' ' . self::ACTION . '="' . api::HOST_SAME . api::MAIN_SCRIPT_EXT . '"';
        $method = ' ' . self::METHOD . '="' . self::METHOD_POST . '"';
        $id = ' ' . self::ID . '="' . $form_name . '"';
        return '<' . self::FORM . $action . $method . $id . '>'
            . $this->form_session_token() . $this->form_user() . $txt . '</' . self::FORM . '>';
    }

    /**
     * create the HTML code for an input field including the label
     * @param string $url_id the id of the input field e.g. n
     * @param msg_id $msg_id the msg_id of the title of the input field e.g. Name
     * @param string|int|float|null $value the suggested value which is in most cases the value already saved in the db;
     *                               float is part of the union because a coercion of a float to string|int
     *                               truncates e.g. a triple weight of 0.5 to 0 (int is tried before string)
     * @param string $type the type of the input e.g. a text or if not set a submit field
     * @param string $input_class the formatting code to change the input type
     * @param string $style the formatting code to adjust the formatting e.g. extend the description to the full screen width
     * @param string $form_name the form that the field is posted with if it is placed outside of it, '' for the surrounding form
     * @return string the HTML code for the field with the label
     */
    function form_field(
        string                $url_id,
        msg_id                $msg_id,
        string|int|float|null $value = '',
        string                $type = html_base::INPUT_TEXT,
        string                $input_class = '',
        string                $style = view_styles::COL_SM_12,
        string                $form_name = ''
    ): string
    {
        // TODO Prio 2 move mtr to label
        global $mtr;
        $name = $mtr->txt($msg_id);
        if (self::UI_USE_BOOTSTRAP) {
            // the label for must equal the input id (field_id) so the pair stays linked
            $text = $this->label($name, $this->field_id($url_id, $name));
            $text .= $this->input($url_id, $msg_id, $value, $type, $input_class, '', $form_name);
            return $this->div_form($text, $style);
        } else {
            return $this->input($url_id, $msg_id, $value, $type, '', '', $form_name);
        }
    }

    /**
     * @return string html code to end a form
     */
    function form_end(): string
    {
        return '</' . self::FORM . '>';
    }

    /**
     * @return string html code to combine the next elements to one row
     */
    function row_start(): string
    {
        $result = '<' . self::DIV . ' ' . self::CLASS_HTML . '="row ';
        $result .= view_styles::COL_SM_12;
        $result .= '">';
        return $result;
    }

    /**
     * @return string html code to combine the next elements to one row and align to the right
     */
    function row_right(): string
    {
        $result = $this->lf();
        $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="row ';
        $result .= view_styles::COL_SM_12;
        $result .= ' justify-content-end">';
        return $result;
    }

    /**
     * @return string html code to combine the next elements to one row and center them
     */
    function row_center(): string
    {
        $result = $this->lf();
        $result .= '<' . self::DIV . ' ' . self::CLASS_HTML . '="row ';
        $result .= view_styles::COL_SM_12;
        $result .= ' ' . styles::JUSTIFY_CENTER . '">';
        return $result;
    }

    /**
     * @return string html code to end a row div
     */
    function row_end(): string
    {
        return '</' . self::DIV . '>';
    }

    /**
     * @param string $summary the text of the always shown summary line
     * @return string html code to start a collapsed block that details_end closes
     */
    function details_start(string $summary): string
    {
        return '<' . self::DETAILS . '><' . self::SUMMARY . '>' . $summary . '</' . self::SUMMARY . '>';
    }

    /**
     * @return string html code to end a collapsed block that details_start has opened
     */
    function details_end(): string
    {
        return '</' . self::DETAILS . '>';
    }

    /*
     * base
     */

    function br2(): string
    {
        return '<' . self::BR . '><' . self::BR . '>';
    }

    function br(): string
    {
        return '<' . self::BR . '>';
    }


    /*
     * display interface
     *
     * all interface functions that should be used
     * depending on the settings either pure HTML, BOOTSTRAP HTML or JavaScript functions are called
     */

    /**
     * display a html text immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo_html(string $txt): void
    {
        echo $txt . '<' . self::BR . '>';
    }

    /**
     * display a message immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo(string $txt): void
    {
        echo $txt;
        echo "\n";
    }

    /**
     *display a progress bar
     * TODO Prio 2 create a auto refresh page for async processes and the HTML front end without JavaScript
     * TODO Prio 2 create a db table, where the async process can drop the status
     * TODO Prio 3 add the refresh frequency setting to the general and user settings
     */
    function ui_progress($id, $value, $max, $text): string
    {
        echo $text;
        return $text;
    }


    /*
     * internal
     */

    /**
     * wrap the html tag around html code
     * TODO Prio 2 to be adjusted depending on the display language
     * @param string $txt the html code
     * @param string $lan the language code of the text
     * @return string the wrapped code
     */
    private function page_lan(string $txt, string $lan): string
    {
        return '<' . self::HTML . ' ' . self::LANG . '="' . $lan . '">' . $txt . '</' . self::HTML . '>';
    }

    /**
     * wrap the head tag around html header code
     * @param string $txt the html header code
     * @return string the wrapped header code
     */
    private function head(string $txt): string
    {
        return '<' . self::HEAD . '>' . $txt . '</' . self::HEAD . '>';
    }

    /**
     * wrap the style tag around css code
     * @param string $css the css rules
     * @return string the style element
     */
    private function style(string $css): string
    {
        return '<' . self::STYLE . '>' . $css . '</' . self::STYLE . '>';
    }

    /**
     * wrap the body tag around html body code
     * @param string $txt the html body code
     * @return string the wrapped body code
     */
    private function body(string $txt): string
    {
        return '<' . self::BODY . '>' . $txt . '</' . self::BODY . '>';
    }

    /**
     * wrap the main tag around html body code
     * @param string $txt the html body code
     * @return string the wrapped main section code
     */
    function main(string $txt): string
    {
        return '<' . self::MAIN . ' ' . self::CLASS_HTML . '="' . self::CLASS_MAIN . '">' . $txt . '</' . self::MAIN . '>';
    }

    /**
     * wrap the footer tag around html footer code
     * @param string $txt the html footer code
     * @return string the wrapped footer code
     */
    private function foot(string $txt): string
    {
        return '<' . self::FOOTER . ' ' . self::CLASS_HTML . '="' . self::CLASS_FOOTER . '">' . $txt . '</' . self::FOOTER . '>';
    }

    /**
     * wrap the div tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @param string $id the html id e.g. used as the css :target anchor of a tab section
     * @return string the wrapped html code
     */
    function div(string $txt, string $style = '', string $id = ''): string
    {
        return $this->block(self::DIV, $txt, $style, $id);
    }

    /**
     * wrap a block tag e.g. the header, aside or section of a use case page around html code
     * @param string $tag the html block tag e.g. self::SECTION
     * @param string $txt the html code
     * @param string $style the html class name
     * @param string $id the html id
     * @return string the wrapped html code
     */
    function block(string $tag, string $txt, string $style = '', string $id = ''): string
    {
        $attr = '';
        if ($style != '') {
            $attr .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        if ($id != '') {
            $attr .= ' ' . self::ID . '="' . $id . '"';
        }
        return '<' . $tag . $attr . '>' . $txt . '</' . $tag . '>';
    }

    /**
     * wrap the h1 heading tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    static function h1(string $txt, string $style = ''): string
    {
        return self::hx(self::H1, $txt, $style);
    }

    /**
     * wrap the h2 heading tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    static function h2(string $txt, string $style = ''): string
    {
        return self::hx(self::H2, $txt, $style);
    }

    /**
     * wrap the h3 heading tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    static function h3(string $txt, string $style = ''): string
    {
        return self::hx(self::H3, $txt, $style);
    }

    /**
     * wrap the h4 heading tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    static function h4(string $txt, string $style = ''): string
    {
        return self::hx(self::H4, $txt, $style);
    }

    /**
     * wrap the given heading tag around html code; the shared helper for h1 to h4
     * @param string $tag the heading tag const e.g. self::H1
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    private static function hx(string $tag, string $txt, string $style = ''): string
    {
        if ($style != '') {
            $style = ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . $tag . $style . '>' . $txt . '</' . $tag . '>';
    }

    /**
     * concatenate two text snippets with the given separator if both are set
     *
     * @param string $left the first text snippet e.g. the category or the share
     * @param string $right the second text snippet e.g. the type or the protection
     * @param string $separator the separator shown between the two snippets if both are set
     * @return string "$left$separator$right" or just the set snippet or '' if both are empty
     */
    function concat_text(string $left, string $right, string $separator): string
    {
        $result = $left;
        if ($left !== '' and $right !== '') {
            $result .= $separator . $right;
        } elseif ($right !== '') {
            $result = $right;
        }
        return $result;
    }

    /**
     * concatenate two text snippets with the configured list entry separator e.g. ", "
     *
     * @param string $left the first text snippet
     * @param string $right the second text snippet
     * @return string the two snippets joined by the entry separator
     */
    function concat_entry_text(string $left, string $right, user_message $msg): string
    {
        global $ui_sys;
        $separator = $ui_sys?->cfg?->get_by(
            [words::ENTRY, words::SEPARATOR, words::LISTS, words::FRONTEND, words::USER],
            $msg
        ) ?? def::FALLBACK_ENTRY_SEPARATOR;
        return $this->concat_text($left, $right, $separator);
    }

    /**
     * concatenate two text snippets with the configured list category separator e.g. " / "
     *
     * @param string $left the first text snippet
     * @param string $right the second text snippet
     * @return string the two snippets joined by the category separator
     */
    function concat_category_text(string $left, string $right, user_message $msg): string
    {
        global $ui_sys;
        $separator = $ui_sys?->cfg?->get_by(
            [words::CATEGORY, words::SEPARATOR, words::LISTS, words::FRONTEND, words::USER],
            $msg
        ) ?? def::FALLBACK_CATEGORY_SEPARATOR;
        return $this->concat_text($left, $right, $separator);
    }

    /**
     * concatenate two text snippets with the configured page-title separator e.g. " - "
     * used to join the object, view and pod name in the html (browser tab) page title
     *
     * @param string $left the first text snippet
     * @param string $right the second text snippet
     * @param user_message|Message $msg to collect the data retriavel errors
     * @return string the two snippets joined by the title separator
     */
    function concat_title_text(string $left, string $right, user_message|Message $msg): string
    {
        global $ui_sys;
        $separator = $ui_sys?->cfg?->get_by(
            [words::TITLE, words::SEPARATOR, words::LISTS, words::FRONTEND, words::USER],
            $msg
        ) ?? def::FALLBACK_TITLE_SEPARATOR;
        return $this->concat_text($left, $right, $separator);
    }

    /**
     * wrap the nav tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    function nav(string $txt, string $style = ''): string
    {
        if ($style != '') {
            $style = ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . self::NAV . ' ' . $style . '>' . $txt . '</' . self::NAV . '>';
    }

    /**
     * wrap the div tag around html code using the bootstrap default class if no style is given
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the wrapped html code
     */
    function div_bs(string $txt, string $style = ''): string
    {
        if ($style == '') {
            $style = view_styles::DEFAULT;
        }
        return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . $style . '">' . $txt . '</' . self::DIV . '>';
    }

    /**
     * wrap the paragraph tag around html code
     * @param string $txt the html code
     * @return string the wrapped paragraph code
     */
    public function p(string $txt): string
    {
        return '<' . self::P . '>' . $txt . '</' . self::P . '>';
    }

    /**
     * wrap the button tag around html code
     * @param string $txt the button label html code
     * @param string $typ the button type e.g. submit
     * @param string $class additional CSS classes for the button
     * @param string $name the url key that the button posts e.g. url_var::POST_SUBMIT for a data-changing submit, '' for none
     * @return string the wrapped button code
     */
    function button(string $txt, string $typ, string $class = '', string $name = ''): string
    {
        if ($class != '') {
            $class = self::CLASS_BUTTON . ' ' . $class;
        } else {
            $class = self::CLASS_BUTTON;
        }
        $name_attr = '';
        if ($name != '') {
            $name_attr = ' ' . self::NAME . '="' . $name . '"';
        }
        return '<' . self::BUTTON . ' ' . self::TYPE . '="' . $typ . '" ' . self::CLASS_HTML . '="' . $class . '"'
            . $name_attr . '>' . $txt . '</' . self::BUTTON . '>';
    }

    /**
     * @param string $text the html code shown on the button e.g. a translated text or an icon
     * @param string $style the bootstrap button style, '' for the outlined success style
     * @param string $type the button type, '' for a submit button
     * @param string $name the url key that the button posts, '' for none
     * @param string $label the accessible name and mouseover text of a button that shows only an icon, '' for none
     * @return string the html code of the button
     */
    function button_bs(string $text, string $style = '', string $type = '', string $name = '', string $label = ''): string
    {
        if ($style == '') {
            $style = self::BS_BTN_SUCCESS;
        }
        $class = ' ' . self::CLASS_HTML . '="' . self::BS_BTN . ' ' . $style . '"';
        if ($type == '') {
            $type = self::INPUT_SUBMIT;
        }
        $type = ' ' . self::TYPE . '="' . $type . '"';
        // a named submit button posts its name as a url key (e.g. POST_SUBMIT) so the entry point can
        // tell a data-changing submit apart from a plain navigation and run url_to_action
        $name = $name == '' ? '' : ' ' . self::NAME . '="' . $name . '"';
        // a screen reader has no text to read on an icon, so the label names the action
        $label_attr = '';
        if ($label != '') {
            $label_txt = htmlspecialchars($label, ENT_QUOTES);
            $label_attr = ' ' . self::ARIA_LABEL . '="' . $label_txt . '"';
            $label_attr .= ' ' . self::TITLE_HTML . '="' . $label_txt . '"';
        }
        return '<' . self::BUTTON . $class . $type . $name . $label_attr . '>' . $text . '</' . self::BUTTON . '>';
    }

    /**
     * @return string the link to the about page in the frontend language
     */
    private function about(): string
    {
        global $mtr;
        return $this->ref(api::ABOUT_SCRIPT, $mtr->txt(msg_id::SYSTEM_TITLE_ABOUT));
    }

    /**
     * @return string the link to the privacy policy page in the frontend language
     */
    private function privacy(): string
    {
        global $mtr;
        return $this->ref(api::PRIVACY_SCRIPT, $mtr->txt(msg_id::PRIVACY_POLICY));
    }

    /**
     * @return string the footer for the about page in the frontend language
     */
    private function foot_text(): string
    {
        global $mtr;
        $txt = $mtr->txt(msg_id::FOOTER_DATA_LICENCE) . ' ';
        $txt .= $this->ref(def::LINK_CC0,
                $mtr->txt(msg_id::CC0), $mtr->txt(msg_id::CC0_LICENSE)) . ' ';
        $txt .= $this->ref(def::LINK_GITHUB,
                $mtr->txt(msg_id::PROGRAM_CODE)) . ' ';
        $txt .= $mtr->txt(msg_id::FOOTER_UNDER_THE) . ' ';
        $txt .= $this->ref(def::LINK_AGPL,
                $mtr->txt(msg_id::AGPL3)) . ' ' . $mtr->txt(msg_id::FOOTER_LICENCE);
        return $txt;
    }

    /**
     * html list item entry
     * @param string $txt the html code that should be a list item
     * @return string the html code of a list item
     */
    function list_item(string $txt, string $style = ''): string
    {
        $attr = '';
        if ($style != '') {
            $attr .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . self::LI . $attr . '>' . $txt . '</' . self::LI . '>';
    }

    /**
     * a part that the user can open and close without javascript, e.g. a node of a tree
     *
     * @param string $summary the html code of the always shown line
     * @param string $body the html code shown if the part is open
     * @param string $style the css class of the part
     * @param string $id the html id e.g. the anchor of a tree node
     * @param string $title the mouseover text of the summary line
     * @return string the html code of the details element
     */
    function details(string $summary, string $body, string $style = '', string $id = '', string $title = ''): string
    {
        $attr = '';
        if ($style != '') {
            $attr .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        if ($id != '') {
            $attr .= ' ' . self::ID . '="' . htmlspecialchars($id, ENT_QUOTES) . '"';
        }
        $title_attr = '';
        if ($title != '') {
            $title_attr = ' ' . self::TITLE_HTML . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
        }
        $result = '<' . self::DETAILS . $attr . '>';
        $result .= '<' . self::SUMMARY . $title_attr . '>' . $summary . '</' . self::SUMMARY . '>';
        return $result . $body . '</' . self::DETAILS . '>';
    }

    /**
     * one row of a description list, e.g. a setting and its value
     *
     * @param string $term the html code of the term e.g. the setting name
     * @param string $definition the html code of the definition e.g. the value with its unit
     * @param string $title the mouseover text of the term
     * @return string the html code of the description list with the one row
     */
    function definition(string $term, string $definition, string $title = ''): string
    {
        $title_attr = '';
        if ($title != '') {
            $title_attr = ' ' . self::TITLE_HTML . '="' . htmlspecialchars($title, ENT_QUOTES) . '"';
        }
        $result = '<' . self::DL . '>';
        $result .= '<' . self::DT . $title_attr . '>' . $term . '</' . self::DT . '>';
        $result .= '<' . self::DD . '>' . $definition . '</' . self::DD . '>';
        return $result . '</' . self::DL . '>';
    }

    /**
     * @param string $txt the already escaped text that matches a search
     * @return string the html code of the highlighted text
     */
    function mark(string $txt): string
    {
        return '<' . self::MARK . '>' . $txt . '</' . self::MARK . '>';
    }

    /**
     * a search input with the given suggestions, which the browser shows without javascript
     *
     * @param string $name the url var of the search e.g. url_var::PATTERN
     * @param string $value the search of the request to show it again
     * @param string $list_id the html id of the suggestions
     * @param array $suggestions the texts the browser suggests e.g. the names of all settings
     * @return string the html code of the search input and its suggestions
     */
    function search_input(string $name, string $value, string $list_id, array $suggestions): string
    {
        $result = '<' . self::INPUT . ' ' . self::TYPE . '="' . self::INPUT_SEARCH . '"';
        $result .= ' ' . self::NAME . '="' . $name . '"';
        $result .= ' ' . self::LIST . '="' . $list_id . '"';
        $result .= ' ' . self::VALUE . '="' . htmlspecialchars($value, ENT_QUOTES) . '">';
        $result .= '<' . self::DATALIST . ' ' . self::ID . '="' . $list_id . '">';
        foreach ($suggestions as $suggestion) {
            $result .= '<' . self::OPTION . ' ' . self::VALUE . '="' . htmlspecialchars($suggestion, ENT_QUOTES) . '">';
            $result .= '</' . self::OPTION . '>';
        }
        return $result . '</' . self::DATALIST . '>';
    }

    /**
     * a button that shows a long text above the page without javascript (html popover)
     *
     * @param string $id the html id of the text, unique on the page
     * @param string $label the html code of the button
     * @param string $txt the already escaped long text
     * @return string the html code of the button and the text it opens
     */
    function popover(string $id, string $label, string $txt): string
    {
        $safe_id = htmlspecialchars($id, ENT_QUOTES);
        $result = '<' . self::BUTTON . ' ' . self::TYPE . '="' . self::BUTTON . '"';
        $result .= ' ' . self::POPOVER_TARGET . '="' . $safe_id . '">' . $label . '</' . self::BUTTON . '>';
        $result .= '<' . self::DIV . ' ' . self::ID . '="' . $safe_id . '" ' . self::POPOVER . '>';
        return $result . $txt . '</' . self::DIV . '>';
    }

    /**
     * html unsorted list
     * @param string $txt the html code of the list entries
     * @param string $style the css class of the list e.g. styles::VALUE_ITEMS
     * @return string the html code of a unsorted list
     */
    function list_unsorted(string $txt, string $style = ''): string
    {
        $attr = '';
        if ($style != '') {
            $attr .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . self::UL . $attr . '>' . $txt . '</' . self::UL . '>';
    }

    /**
     * @return string to use the flex option on pages
     */
    private function viewport(): string
    {
        return '<' . self::META . ' name="viewport" content="width=device-width, initial-scale=1.0">';
    }


    /*
     * internal
     */

    /**
     * @return string the html file header
     */
    private function doctype(): string
    {
        return self::DOC_HTML;
    }

    /**
     * @return string the html language selection
     */
    private function lang(string $lan): string
    {
        // the bootstrap components e.g. a table or an input follow the dark mode only if the user has
        // chosen it, because without javascript the setting of the browser is known only to the css
        $theme = '';
        if ($this->theme_mode() == def::MODE_DARK) {
            $theme = ' data-bs-theme="dark"';
        }
        return '<' . self::HTML . ' ' . self::LANG . '="' . $lan . '"' . $theme . '>';
    }

    /**
     * @return string with the charset for the html pages
     */
    private function charset(): string
    {
        return '<' . self::META . ' ' . self::CHARSET . '="' . def::ENCODING . '">';
    }

    /**
     * @return string the viewport meta tag for responsive pages
     */
    private function make_flood(): string
    {
        return '<' . self::META . ' '
            . self::NAME . '="viewport" '
            . self::CONTENT . '="width=device-width, initial-scale=1.0">';
    }

    /**
     * wrap the title tag around html title text
     * @param string $txt the title text
     * @param string $pod_name the name of the pod
     * @param user_message|Message $msg to collect render errors
     * @return string the warped title text
     */
    private function title(string $txt, string $pod_name, user_message|Message $msg): string
    {
        // append the pod name with the configured title separator e.g. "US dollar - Word (default) - zukunft.com"
        if ($txt == $pod_name) {
            $title = $pod_name;
        } else {
            $title = $this->concat_title_text($txt, $pod_name, $msg);
        }
        // escape the title text: it is fed the raw object and view name, so an object renamed to
        // "</title><script>..." would otherwise break out of the head title and inject script (xss)
        return '<' . self::TITLE . '>' . $this->esc($title) . '</' . self::TITLE . '>';
    }

    /**
     * @return string use a simple stylesheet without JavaScript
     */
    private function stylesheet(string $base_url = ''): string
    {
        return $this->link_style(files::STYLE_HTML, $base_url);
    }

    /**
     * @return string use a simple stylesheet without JavaScript and without bootstrap (maybe not needed any more)
     */
    private function stylesheet_fallback(string $base_url = ''): string
    {
        return $this->link_style(files::STYLE_FALLBACK, $base_url);
    }

    /**
     * @return string the bootstrap stylesheet
     */
    private function stylesheet_bs(string $base_url = ''): string
    {
        return $this->link_style(files::STYLE_BS, $base_url);
    }

    /**
     * @return string all JavaScript bootstrap stylesheets
     */
    private function stylesheet_bs_js_all(): string
    {
        return $this->link_style(html_paths::EXT_LIB_BS_JS);
    }

    /**
     * @return string the font stylesheet
     */
    private function stylesheet_font(string $base_url = ''): string
    {
        return $this->link_style(files::STYLE_FONT, $base_url);
    }

    /**
     * the data of a page script as json, which the browser never runs as code; a "<" is written
     * as its unicode escape, so a text in the data cannot close the script tag
     *
     * @param string $id the html id by which the script finds the data
     * @param array $data the data of the script
     * @return string the html code of the data block
     */
    function script_data(string $id, array $data): string
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
        return '<' . self::SCRIPT . ' ' . self::TYPE . '="' . self::JSON_TYPE . '" ' . self::ID . '="' . $id . '">'
            . $json . '</' . self::SCRIPT . '>';
    }

    /**
     * @param string $url the path of the script file e.g. files::CALCULATOR_SCRIPT
     * @return string the html code that loads the script
     */
    function script_src(string $url): string
    {
        return '<' . self::SCRIPT . ' ' . self::SRC . '="' . $url . '"></' . self::SCRIPT . '>';
    }

    /**
     * @param string $stylesheet path to the stylesheet file
     * @param string $base_url e.g. the pod url to turn the root relative path into an
     *                         absolute url, so that the stylesheet is also found by a page
     *                         that is not served by the pod (e.g. a html test snapshot)
     * @return string the HTML link element for the stylesheet
     */
    function link_style(string $stylesheet, string $base_url = ''): string
    {
        $url = $stylesheet;
        if ($base_url != '') {
            // without the trailing slash of the base url, because the path starts with one
            $url = rtrim($base_url, '/') . $stylesheet;
        }
        return '<' . self::LINK . ' ' . self::REL . '="' . self::STYLESHEET . '" '
            . self::HREF . '="' . $url . '">';
    }

}
