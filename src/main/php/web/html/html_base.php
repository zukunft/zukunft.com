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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::WEB . 'frontend.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::TYPES . 'language_list.php';
//include_once paths::SHARED_CONST . 'def.php';
//include_once paths::SHARED_CONST . 'files.php';
//include_once paths::SHARED_CONST . 'rest_ctrl.php';
//include_once paths::SHARED_ENUM . 'languages.php';
//include_once paths::SHARED_ENUM . 'messages.php';
//include_once paths::SHARED_TYPES . 'view_styles.php';
//include_once paths::SHARED . 'api.php';
//include_once paths::SHARED . 'url_var.php';
//include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\types\language_list;

class html_base
{

    // TODO move all html const used in zukunft.com to html_names

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

    // bootstrap const string used in zukunft.com
    const string BS_FORM = 'form-control';
    const string BS_BTN = 'btn btn-space col-1';
    const string BS_BTN_SUCCESS = 'btn-outline-success';
    const string BS_BTN_CANCEL = 'btn-outline-secondary';
    const string BS_BTN_DEL = 'btn-outline-secondary';
    const string BS_BTN_IMPORT = 'btn-outline-secondary';
    const string BS_BTN_EXPORT = 'btn-outline-secondary';

    // TODO move the user interface setting to the user page, so that he can define which UI he wants to use
    const int UI_USE_BOOTSTRAP = 1; // IF FALSE a simple HTML frontend without javascript is used

    const string METHOD_POST = 'post';

    const string SIZE_FULL = 'full';
    const string SIZE_HALF = 'half';

    const string WIDTH_FULL = '800px';
    const string WIDTH_HALF = '400px';

    // all html code elements used
    const string DOC_HTML = '<!DOCTYPE html>';
    const string HTML_START = '<html lang=';
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
    const string CLASS_HTML = 'class';
    const string TITLE_HTML = 'title'; // title attribute e.g. for tooltips
    const string TITLE = 'title';      // <title> element in <head>
    const string TYPE = 'type';
    const string P = 'p';
    const string DIV = 'div';
    const string UL = 'ul';
    const string LI = 'li';
    const string BUTTON = 'button';
    const string HTML = 'html';

    // to sort
    const string CLASS_MAIN = 'main-container';
    const string CLASS_FOOTER = 'site-footer';
    const string CLASS_INPUT_SECTION = 'search-section';
    const string CLASS_INPUT = 'standard-input';
    const string CLASS_BUTTON = 'btn';
    const string CLASS_NAV = 'navbar site-header fixed-top';
    const string CLASS_LOGO = 'navbar-brand';
    const string CLASS_LOGO_BS = 'height: 4em;';
    const string CLASS_LOGO_HTML = 'height: 5em;';
    const string CLASS_LOGO_BIG = 'height: 30%;';
    const string CLASS_LOGO_FLEX = 'brand-logo';
    const string CLASS_LOGO_SECTION = 'logo-section';


    /*
     * page
     */

    /**
     * create a simple html page based on header, body and footer html code
     * @param string $lan the language code of the text
     * @param string $head the header HTML code
     * @param string $body the body HTML code
     * @param string $foot the footer HTML code
     * @returns string the HTML page code
     */
    function page_html(string $lan, string $head, string $body, string $foot): string
    {
        return self::DOC_HTML . $this->page_lan($head . $body . $foot, $lan);
    }


    /*
     * header & footer
     */

    /**
     * the page header for simple html pages like the login page
     * @param string $title simple the HTML title used
     * @returns string the simple HTML header for unit tests
     */
    function header_html(string $title, string $pod_name): string
    {
        $txt = $this->charset();
        $txt .= $this->viewport();
        $txt .= $this->title($title, $pod_name);
        $txt .= $this->stylesheet();
        return $this->head($txt);
    }

    /**
     * wrap the main body tag around html body code
     * @param string $txt the html body code
     * @return string the warped body code
     */
    function main_body(string $txt): string
    {
        return $this->body($this->main($txt));
    }

    /**
     * the page footer for simple html pages like the login page
     * @returns string the simple HTML footer for unit tests
     */
    function footer_html(): string
    {
        $txt = $this->nav($this->about() . ' &middot; ' . $this->privacy());
        $txt .= $this->p($this->foot_text());
        return $this->foot($txt);
    }

    /**
     * create the html code fpr the page header
     * @param string $title simple the HTML title used
     * @param string $style e.g. to center for the login page
     * @param string $lan the language html code id
     * @returns string the general HTML header
     */
    function header(
        string $title,
        string $style = "",
        string $lan = languages::DEFAULT
    ): string
    {
        $result = $this->doctype() . "\n";
        $result .= $this->lang($lan) . "\n";
        $result .= $this->head($this->head_fill($title)) . "\n";
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
     * @return string with the html code for the head section of the html header
     */
    private function head_fill(string $title): string
    {
        $txt = $this->charset() . "\n";
        $txt .= $this->make_flood() . "\n";
        $txt .= $this->title($title, POD_NAME) . "\n";
        $txt .= $this->head_style() . "\n";
        return $txt;
    }

    /**
     * @return string with the html code for the style of the html head
     */
    private function head_style(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            // TODO Prio 3 check if the other bootstrap css also needs to be included
            // include the bootstrap stylesheets
            $txt = $this->stylesheet_bs() . "\n";
            // include the icon font
            $txt .= $this->stylesheet_font() . "\n";
            // include the default zukunft.com frontend style
            $txt .= $this->stylesheet() . "\n";
            // TODO Prio 2 check if still needed
            // include the bootstrap JavaScript plugins
            //$result .= $this->stylesheet_bs_js_all() . "\n";
        } else {
            // use a simple stylesheet without Javascript
            $txt = $this->stylesheet_fallback() . "\n";
        }
        return $txt;
    }

    /**
     * the html code for the navigation bar
     *
     * @param int $msk_id
     * @return string the general HTML footer
     */
    function navbar(int $msk_id = 0): string
    {
        global $sys;

        $api_json = $sys->typ_lst->lan->api_json_array();
        $ui_lst = new language_list();
        $ui_lst->set_from_json_array($api_json, language::class);
        $html = new html_base();
        $url = $html->url_new($msk_id);
        $lan_lst = $ui_lst->select_list_item($url);

        $result = $this->logo() . "\n";
        $result .= '<form action="/http/find.php" class="d-flex align-items-center my-2 my-lg-0 flex-grow-1 mx-3">' . "\n";
        $result .= '<label for="kp" class="visually-hidden">Search</label>' . "\n";
        $result .= '<input class="form-control me-2" type="search" name="pattern" id="kp" placeholder="word or formula" style="min-width: 40vw; max-width: 800px;">' . "\n";
        $result .= '<button class="btn btn-outline-primary" type="submit">Get numbers</button>' . "\n";
        $result .= '</form>' . "\n";
        $result .= '<div class="col-md-2">' . "\n";
        $result .= '<ul class="nav navbar-nav">' . "\n";
        $result .= '<li class="active">' . "\n";
        $result .= '<details class="view-menu">' . "\n";
        $result .= '<summary><i class="fas fa-edit"></i></summary>' . "\n";
        $result .= '<ul>' . "\n";
        $result .= '<li><a href="/http/view.php?m=view_change&id=2">alternative</a></li>' . "\n";
        $result .= '<li><a href="?view=more">... more</a></li>' . "\n";
        $result .= '<li><a href="/http/view.php?m=view_edit&id=1">change view</a></li>' . "\n";
        $result .= '<li><a href="/http/view.php?m=view_add">add view</a></li>' . "\n";
        $result .= '</ul>' . "\n";
        $result .= '</details>' . "\n";
        $result .= '<details class="lang-menu">' . "\n";
        $result .= '<summary><i class="fas fa-globe"></i></summary>' . "\n";
        $result .= $lan_lst . "\n";
        $result .= '</details>' . "\n";
        $result .= '<details class="user-menu">' . "\n";
        $result .= '<summary><i class="fas fa-user-circle"></i></summary>' . "\n";
        $result .= '<ul>' . "\n";
        $result .= '<li><a href="/http/login.php">log in</a></li>' . "\n";
        $result .= '<li><a href="/http/signup.php">Sign in</a></li>' . "\n";
        $result .= '<li><a href="/settings">Settings</a></li>' . "\n";
        $result .= '</ul>' . "\n";
        $result .= '</details>' . "\n";
        $result .= '</li>' . "\n";
        $result .= '</ul>' . "\n";
        $result .= '</div>' . "\n";

        return $this->nav($result, self::CLASS_NAV);
    }

    /**
     * @param bool $no_about
     * @return string the general HTML footer
     */
    function footer(bool $no_about = false): string
    {
        global $sys;
        global $mtr;
        $result = '<' . self::FOOTER . ' ' . self::CLASS_HTML . '="' . self::CLASS_FOOTER . '">' . "\n";

        // for the about page this does not make sense
        $result .= '<' . self::P . '> ' . "\n";
        if (!$no_about) {
            $url = $this->url(rest_ctrl::URL_ABOUT);
            $result .= $this->ref($url, $mtr->txt(msg_id::SYSTEM_TITLE_ABOUT)) . ' &middot; ' . "\n";
            $result .= $this->ref(api::PRIVACY_SCRIPT_REL, $mtr->txt(msg_id::PRIVACY_POLICY)) . ' &middot; ' . "\n";
        }
        $result .= 'All structured data is available under the ';
        $result .= $this->ref('https://creativecommons.org/publicdomain/zero/1.0/', $mtr->txt(msg_id::CC0), $mtr->txt(msg_id::CC0_LICENSE)) . ' ' . "\n";
        $result .= 'Licence unless otherwise stated and the ' . "\n";
        $result .= $this->ref('https://github.com/zukunft/zukunft.com', $mtr->txt(msg_id::PROGRAM_CODE)) . ' ' . "\n";
        $result .= 'of this version ' . SYSTEM_CODE_VERSION . "\n";
        $result .= 'under the ' . $this->ref('https://www.gnu.org/licenses/agpl.html', $mtr->txt(msg_id::AGPL3)) . ' Licence. ' . "\n";
        $result .= '</' . self::P . '> ' . "\n";

        $result .= '</' . self::FOOTER . '>' . "\n";
        $result .= '</' . self::BODY . '>' . "\n";
        $result .= '</' . self::HTML . '>' . "\n";

        return $result;
    }


    /*
     * wrapper for the basic html elements used
     */

    // TODO Prio 1 use this everywhere if possible

    function ref(string $url, string $name, string $title = '', string $style = ''): string
    {
        $result = '<' . self::A . ' ' . self::HREF . '="' . $url . '"';
        if ($title != '') {
            $result .= ' ' . self::TITLE_HTML . '="' . $title . '"';
        } else {
            $result .= ' ' . self::TITLE_HTML . '="' . $name . '"';
        }
        if ($style != '') {
            $result .= ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        $result .= '>';
        $result .= $name;
        $result .= '</' . self::A . '>';
        return $result;
    }

    function img(string $img_path, string $alt, string $style = ''): string
    {
        return '<' . self::IMG
            . ' ' . self::SRC . '="' . $img_path . '"'
            . ' ' . self::ALT . '="' . $alt . '"'
            . ' ' . self::STYLE . '="' . $style . '">';
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
            $result .= ' ' . html_names::TITLE . '="' . $title . '" ' . self::TOGGLE_TOOLTIP;
        }
        $result .= '>' . $text . '</' . html_names::SPAN . '>';
        return $result;
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
     * @param string|null $back the back trace calls to return to the original url and for undo
     * @param string|array $par either the array with the parameters or the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    function url(string       $obj_name,
                 int|string   $id = 0,
                 ?string      $back = '',
                 string|array $par = '',
                 string       $id_ext = ''): string
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
        if ($back != '') {
            $result .= '&back=' . $back;
        }
        return $result;
    }

    /**
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
     * @param string|null $back the back trace calls to return to the original url and for undo
     * @param string|array $par either the array with the parameters or the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    function url_new(int|string   $view,
                     int|string   $id = 0,
                     string       $obj_name = '',
                     ?string      $back = '',
                     string|array $par = '',
                     string       $id_ext = ''
    ): string
    {
        $result = rest_ctrl::PATH_FIXED . rest_ctrl::URL_MAIN_SCRIPT . rest_ctrl::EXT . '?';
        $result .= url_var::MASK . '=' . $view;
        if (is_string($id)) {
            $result .= '&id=' . $id;
        } elseif ($id <> 0) {
            $result .= '&id=' . $id;
        }
        if ($id_ext != '') {
            $result .= '&' . $id_ext;
        }
        if ($back != '') {
            $result .= '&back=' . $back;
        }
        return $result;
    }

    /**
     * build a url for link a zukunft.com element
     *
     * @param string $obj_name the object that is requested e.g. a view
     * @return string the created url
     */
    function url_api(string $obj_name): string
    {
        return $this->host() . rest_ctrl::PATH . $obj_name . '/';
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

    /**
     * TODO change based on the environment
     * @return string the host name of the api
     */
    private function host(): string
    {
        return frontend::HOST_DEV;
    }

    /*
     * text formatting
     */

    function text_h1(string $title, string $style = ''): string
    {
        return $this->text_h($title, 2, 1, $style);
    }

    function text_h2(string $title, string $style = ''): string
    {
        return $this->text_h($title, 4, 2, $style);
    }

    function text_h3(string $title, string $style = ''): string
    {
        return $this->text_h($title, 5, 3, $style);
    }

    private function text_h(string $title, int $bs_i, int $i, string $style = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= '<h' . $bs_i . '>' . $title . '</h' . $bs_i . '>';
        } else {
            if ($style <> "") {
                $result .= '<h' . $i . ' class="' . $style . '">' . $title . '</h' . $i . '>';
            } else {
                $result .= "<h' . $i . '>" . $title . "</h' . $i . '>";
            }
        }
        return $result;
    }


    /*
     * images
     */

    /**
     * @returns string the zukunft.com logo with a link to the home page
     */
    function logo(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_BS);
        } else {
            $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_HTML);
        }
        return $this->ref(api::MAIN_SCRIPT_REL, $img, POD_NAME, self::CLASS_LOGO);
    }

    /**
     * @returns string the increased zukunft.com logo to display it in the center
     */
    function logo_big(): string
    {
        $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_BIG);
        return $this->ref(api::MAIN_SCRIPT_REL, $img, POD_NAME, self::CLASS_LOGO);
    }

    /**
     * @returns string the increased zukunft.com logo to display it in the center
     */
    function logo_flex(): string
    {
        $img = $this->img(files::LOGO, POD_NAME, self::CLASS_LOGO_FLEX);
        $ref = $this->ref(api::MAIN_SCRIPT_REL, $img, POD_NAME, self::CLASS_LOGO);
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
                return '<th class="' . $style . '" scope="' . $scope . '">' . $header_text . '</th>';
            } else {
                return '<th scope="' . $scope . '">' . $header_text . '</th>';
            }
        } else {
            if ($style != '') {
                return '<th class="' . $style . '">' . $header_text . '</th>';
            } else {
                return '<th>' . $header_text . '</th>';
            }
        }
    }

    /**
     * @return string with the html code for a line feed
     */
    function lf(): string
    {
        return '<br>';
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
        return '<tr>' . $row_text . '</tr>';
    }

    /**
     * show a text of link within a table cell
     * @param string|null $cell_text the text or link that should be shown or null to return an empty cell
     * @param string $style the bootstrap formatting class
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @return string the html code of the table cell
     */
    function td(?string $cell_text = '', string $style = '', int $intent = 0): string
    {
        // just for formatting the html code
        while ($intent > 0) {
            $cell_text .= '&nbsp;';
            $intent = $intent - 1;
        }
        if ($style != '') {
            return '<td class="' . $style . '">' . $cell_text . '</td>';
        } else {
            return '<td>' . $cell_text . '</td>';
        }
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
        return '<thead>' . $html_rows . '</thead>';
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
        return '<tbody>' . $html_rows . '</tbody>';
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
            styles::TABLE_PUR => $this->tbl_start_pur() . $tbl_rows . $this->tbl_end(),
            default => $this->tbl_start() . $tbl_rows . $this->tbl_end(),
        };
    }

    private function tbl_start(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">';
        } else {
            $result = '<table style="width:' . $this->tbl_width() . '">';
        }
        return $result;
    }

    function tbl_start_half(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table ' . view_styles::COL_SM_5 . ' table-striped table-bordered">';
        } else {
            $result = '<table style="width:' . $this->tbl_width_half() . '">';
        }
        return $result;
    }

    function tbl_start_hist(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-borderless text-muted">';
        } else {
            $result = '<table class="change_hist"';
        }
        return $result;
    }

    function tbl_start_pur(): string
    {
        return '<table class="table">';
    }

    /**
     * a table for a list of selectors
     */
    function tbl_start_select(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table ' . view_styles::COL_SM_10 . ' table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    private function tbl_end(): string
    {
        return '</table>' . "\n";
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
        string $back = '',
        string $del_call = ''): string
    {
        return $this->form_start($form_name) . $tbl_rows . $this->form_end_with_submit($submit_name, $back, $del_call);
    }

    /**
     * @param string $row_text the html code that should be wrapped into a form row
     * @return string the html code of the form row
     */
    function fr(string $row_text): string
    {
        return '<div class="' . rest_ctrl::CLASS_FORM_ROW . '">' . $row_text . '</div>';
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
                ': <input type="' . html_base::INPUT_TEXT .
                '" name="' . $field .
                '" value="' . $txt_value . '">';
        }
        return $result;
    }

    /**
     * add the hidden field
     * @param string $name the internal name of the field
     * @param string $value the value that should be returned
     * @@returns string the html code to add a hidden field
     */
    function form_hidden(string $name, string $value): string
    {
        return '<input type="' . html_base::INPUT_HIDDEN .
            '" name="' . $name .
            '" value="' . $value . '">';
    }

    /**
     * end a html form
     */
    function form_end_with_submit(string $submit_name, string $back, $del_call = ''): string
    {
        global $mtr;
        $result = '';
        $but = new button();
        if (self::UI_USE_BOOTSTRAP) {
            if ($submit_name == "") {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">Save</button>';
            } else {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">' . $submit_name . '</button>';
            }
            if ($back <> "") {
                if (is_numeric($back)) {
                    $result .= $this->ref(api::MAIN_SCRIPT_REL . '?' . url_var::WORDS_HUMAN . '=' . $back, $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', 'btn btn-outline-secondary btn-space');
                } else {
                    $result .= $this->ref($back, $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', 'btn btn-outline-secondary btn-space');
                }
            }
            if ($del_call <> '') {
                $result .= $this->ref($del_call, $mtr->txt(msg_id::SYSTEM_POPUP_TITLE_DELETE), '', 'btn btn-outline-danger');
            }
        } else {
            if ($submit_name == "") {
                $result .= '<input type="' . html_base::INPUT_SUBMIT .
                    '">';
            } else {
                $result .= '<input type="' . html_base::INPUT_SUBMIT .
                    '" value="' . $submit_name . '">';
            }
            if ($back <> "") {
                $result .= $but->back($back);
            }
            if ($del_call <> "") {
                $result .= $but->del(msg_id::DEL, $del_call);
            }
        }
        $result .= '</form>';
        return $result;
    }

    function button_submit(string $submit_name): string
    {
        return $this->button($submit_name, html_base::INPUT_SUBMIT);
    }

    function form_submit(string $submit_name): string
    {
        return $this->form_input(html_base::INPUT_SUBMIT, url_var::POST_SUBMIT, $submit_name);
    }

    // TODO Prio 0 use this function for all html input fields
    function form_input(string $type, string $name, string $value = ''): string
    {
        $txt = '<input type="' . $type . '" name="' . $name . '"';
        if ($value != '') {
            $txt .= ' value="' . $value . '"';
        }
        $txt .= ' class="' . self::CLASS_INPUT . '">';
        return $txt;
    }

    /**
     * @return string the HTML code of the about page
     */
    function about_page(): string
    {
        $result = $this->header('about', "center_form"); // reset the html code var

        $result .= $this->about_body();

        return $result;
    }

    function about_body(): string
    {
        global $mtr;
        $result = $this->dsp_form_center();
        $result .= $this->logo_big();
        $result .= '<br><br>';
        $result .= 'is sponsored by <br><br>';
        $result .= 'zukunft.com AG<br>';
        $result .= 'Blumentalstrasse 15<br>';
        $result .= '8707 Uetikon am See<br>';
        $result .= 'Switzerland<br><br>';
        $result .= $this->ref('mailto:timon@zukunft.com', 'timon@zukunft.com') . '<br><br>';
        $result .= 'One of the main ideas is to use ';
        $result .= $this->ref('https://dx.doi.org/10.2139/ssrn.6497759', 'Real-Time Delphi Based on the Giant Global Graph: A Framework for Fair and Evidence-Based Decision-Making') . '. ';
        $result .= 'Once implemented it might be possible ';
        $result .= $this->ref('https://doi.org/10.5281/zenodo.19443909', 'Implementing the Categorical Imperative in Practice') . '.<br><br>';
        $result .= 'zukunft.com AG also supports the ';
        $result .= $this->ref("https://github.com/zukunft/tream", $mtr->txt(msg_id::OPEN_SOURCE), "github.com link") . ' Portfolio Management System<br><br>';
        $tream_img = $this->img('/src/main/resources/images/TREAM_logo.jpg', 'TREAM', 'height: 20%;');
        $result .= $this->ref('https://tream.biz/p4a/applications/tream/', $tream_img, 'TREAM demo') . '<br><br>';
        $result .= '</div>   ';
        $result .= $this->footer(true);

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
        return "<small>" . $line_text . "</small><br>";
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
        string $back = ''): string
    {
        $but = new button();
        $result = '';

        $row_nbr = 0;
        $num_rows = count($item_lst);
        foreach ($item_lst as $key => $item) {
            // list of all possible view entries
            $row_nbr = $row_nbr + 1;
            $edit_script = $this->edit_url($class);
            $url = $this->url($edit_script, $key, $back);
            $result .= $this->ref($url, $item);
            if ($row_nbr > 1) {
                $url = $this->url($edit_script, $key, $back, '&move_up=' . $key);
                $result .= $this->ref($url, 'up');
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            if ($row_nbr < $num_rows) {
                $url = $this->url($edit_script, $key, $back, '&move_down=' . $key);
                $result .= $this->ref($url, 'down');
            }
            $result .= ' ';
            // TODO Prio 1 review
            //$result .= $but->del('Delete ' . $class, $class . '?id=' . $script_parameter . '&del=' . $key);
            $result .= '<br>';
        }

        return $result;
    }

    /**
     * display a list of elements
     * the list should be paged and the items should be edible
     *
     * e,g, to display a list of verbs
     * similar to the table function, which is used for values and formula results
     *
     * @param array $item_lst a list of objects that have at least an id and a name
     * @param string $class the object that is requested e.g. a view
     * @param string $back the target for the back / ctrl-z function
     * @returns string with the html code to display the list
     */
    function list(array $item_lst, string $class, string $back = ''): string
    {
        $result = "";

        $lib = new library();
        $class_name = $lib->class_to_name($class);

        foreach ($item_lst as $item) {
            if ($item->id() != null) {
                $url = $this->url($class_name . rest_ctrl::UPDATE, $item->id(), $back);
                $result .= $this->ref($url, $item->name());
                $result .= '<br>';
            }
        }
        $url_add = $this->url($class_name . rest_ctrl::CREATE, 0, $back);
        $msg_id = $lib->class_to_add_msg_id($class);
        $result .= (new button($url_add, $back))->add($msg_id);
        $result .= '<br>';

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
        return "<small>" . $line_text . "</small><br>";
    }


// ------------------------
// single element functions
// ------------------------

// simply to display headline text
    function dsp_text_h1($title, $style = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= "<h2>" . $title . "</h2>";
        } else {
            if ($style <> "") {
                $result .= '<h1 class="' . $style . '">' . $title . '</h1>';
            } else {
                $result .= "<h1>" . $title . "</h1>";
            }
        }
        return $result;
    }

    function dsp_text_h2($title, $style = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= "<h4>" . $title . "</h4>";
        } else {
            if ($style <> "") {
                $result .= '<h2 class="' . $style . '">' . $title . '</h2>';
            } else {
                $result .= "<h2>" . $title . "</h2>";
            }
        }
        return $result;
    }

    function dsp_text_h3($title, $style = ''): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= "<h6>" . $title . "</h6>";
        } else {
            if ($style <> "") {
                $result .= '<h3 class="' . $style . '">' . $title . '</h3>';
            } else {
                $result .= '<h3>' . $title . '</h3>';
            }
        }
        return $result;
    }

// after simple add views e.g. for a value automatically go back to the calling page
    function dsp_go_back($back, $usr): string
    {
        log_debug('dsp_go_back(' . $back . ')');

        $result = '';

        if ($back == '') {
            log_err("Internal error: go back page missing.", "dsp_header->dsp_go_back");
            header("Location: view.php?words=1"); // go back to the fallback page
        } else {
            if (is_numeric($back)) {
                header("Location: view.php?words=" . $back); // go back to the calling page and try to avoid double change script calls
            } else {
                header("Location: " . $back); // go back to the calling page and try to avoid double change script calls
            }
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

// simply to display an error text interactively to the user; use this function always for easy redesign of the error messages
    function dsp_err($err_text): string
    {
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            $result .= '<style class="text-danger">' . $err_text . '</style>';
        } else {
            $result .= '<style color="red">' . $err_text . '</style>';
        }
        return $result;
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

        $result .= '<div class="' . view_styles::COL_SM_5 . '">';
        $result .= '<ul class="nav nav-tabs">';
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link active" id="' . $comp_id . '-tab" data-toggle="tab" href="#' . $comp_id . '" role="tab" aria-controls="' . $comp_id . '" aria-selected="true">' . $comp_name . '</a>';
        $result .= '  </li>';
        if ($nbrs_name <> '') {
            $result .= '  <li class="nav-item">';
            $result .= '    <a class="nav-link"        id="' . $nbrs_id . '-tab" data-toggle="tab" href="#' . $nbrs_id . '" role="tab" aria-controls="' . $nbrs_id . '" aria-selected="false">' . $nbrs_name . '</a>';
            $result .= '  </li>';
        }
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link"        id="' . $hist_id . '-tab" data-toggle="tab" href="#' . $hist_id . '" role="tab" aria-controls="' . $hist_id . '" aria-selected="false">' . $hist_name . '</a>';
        $result .= '  </li>';
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link"        id="' . $link_id . '-tab" data-toggle="tab" href="#' . $link_id . '" role="tab" aria-controls="' . $link_id . '" aria-selected="false">' . $link_name . '</a>';
        $result .= '  </li>';
        $result .= '</ul>';
        $result .= '<div class="tab-content border-right border-bottom border-left rounded-bottom" id="comp-hist-tab-content">';
        $result .= '  <div class="tab-pane fade active show" id="' . $comp_id . '" role="tabpanel" aria-labelledby="' . $comp_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $comp_html;
        $result .= '    </div>';
        $result .= '  </div>';
        if ($nbrs_name <> '') {
            $result .= '  <div class="tab-pane fade" id="' . $nbrs_id . '" role="tabpanel" aria-labelledby="' . $nbrs_id . '-tab">';
            $result .= '    <div class="container">';
            $result .= $nbrs_html;
            $result .= '    </div>';
            $result .= '  </div>';
        }
        $result .= '  <div class="tab-pane fade" id="' . $hist_id . '" role="tabpanel" aria-labelledby="' . $hist_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $hist_html;
        $result .= '    </div>';
        $result .= '  </div>';
        $result .= '  <div class="tab-pane fade" id="' . $link_id . '" role="tabpanel" aria-labelledby="' . $link_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $link_html;
        $result .= '    </div>';
        $result .= '  </div>';
        $result .= '</div>'; // of tab content

        return $result;
    }

// -----------------------
// table element functions
// -----------------------

    function dsp_tbl_start(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_half(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table ' . view_styles::COL_SM_5 . ' table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_hist(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-borderless text-muted">' . "\n";
        } else {
            $result = '<table class="change_hist"' . "\n";
        }
        return $result;
    }

// a table for a list of selectors
    function dsp_tbl_start_select(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            $result = '<table class="table ' . view_styles::COL_SM_10 . ' table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_end(): string
    {
        $result = '</table>' . "\n";
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
        return '<form action="' . $form_name . '.php" id="' . $form_name . '">';
    }

// end a html form
    function dsp_form_end($submit_name, $back, $del_call = ''): string
    {
        global $mtr;
        $but = new button();
        $result = '';
        if (self::UI_USE_BOOTSTRAP) {
            if ($submit_name == "") {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">Save</button>';
            } else {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">' . $submit_name . '</button>';
            }
            if ($back <> "") {
                if (is_numeric($back)) {
                    $result .= $this->ref(api::MAIN_SCRIPT_REL . '?' . url_var::WORDS_HUMAN . '=' . $back, $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', 'btn btn-outline-secondary btn-space');
                } else {
                    $result .= $this->ref($back, $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', 'btn btn-outline-secondary btn-space');
                }
            }
            if ($del_call <> '') {
                $result .= $this->ref($del_call, $mtr->txt(msg_id::SYSTEM_POPUP_TITLE_DELETE), '', 'btn btn-outline-danger');
            }
        } else {
            if ($submit_name == "") {
                $result .= '<input type="' . html_base::INPUT_SUBMIT .
                    '">';
            } else {
                $result .= '<input type="' . html_base::INPUT_SUBMIT .
                    '" value="' . $submit_name . '">';
            }
            if ($back <> "") {
                $result .= $but->back($back);
            }
            if ($del_call <> "") {
                $result .= $but->del(msg_id::DEL, $del_call);
            }
        }
        $result .= '</form>';
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
            return '<div class="container text-center">' . $txt . '</div>';
        } else {
            return '<div class="center_form">' . $txt . '</div>';
        }
    }

    function dsp_form_center(): string
    {
        if (self::UI_USE_BOOTSTRAP) {
            return '<div class="container text-center">';
        } else {
            return '<div class="center_form">';
        }
    }

// add the element id, which should always be using the field "id"
    function dsp_form_id($id): string
    {
        return '<input type="' . html_base::INPUT_HIDDEN .
            '" name="id" value="' . $id . '">';
    }

    /**
     * html hidden field
     * @param string $field the name of the hidden
     * @param int $id
     * @return string the html code for a hidden form field
     */
    function dsp_form_hidden(string $field, int $id): string
    {
        return '<input type="' . html_base::INPUT_HIDDEN .
            '" name="' . $field .
            '" value="' . $id . '">';
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
                ': <input type="' . html_base::INPUT_TEXT .
                '" name="' . $field .
                '" value="' . $txt_value . '">';
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
                ': <input type="' . html_base::INPUT_TEXT .
                '" name="' . $field .
                '" class="resizedTextbox" value="' . $txt_value . '">';
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
            $result .= '<div class="form-check-inline">';
            $result .= '<label class="form-check-label">';
            $result .= '<input class="form-check-input" type="checkbox" name="' . $field . '"';
            if ($is_checked) {
                $result .= ' checked';
            }
            $result .= '>' . $label . '</label>';
            $result .= '</div>';
        } else {
            $result .= '  <input type="' . html_base::INPUT_CHECKBOX .
                '" name="' . $field . '"';
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
        $result .= ' <form action="/view.php?m=import" method="post" enctype="multipart/form-data">';
        $result .= '   Select JSON to upload:';
        $result .= '   <input type="' . html_base::INPUT_FILE .
            '" name="fileToUpload" id="fileToUpload">';
        $result .= '   <input type="' . html_base::INPUT_SUBMIT .
            '" value="Upload JSON" name="submit">';
        $result .= ' </form>';
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
        return '<label for="' . $for . '">' . $text . '</label>';
    }

    /**
     * translate and create the html code for a label
     * TODO use if if possible
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
     * create the HTML code for an input field
     * @param string $url_id the url id of the input field e.g. Name
     * @param msg_id $msg_id the msg_id of the title of the input field e.g. Name
     * @param string $value the suggested value which is in most cases the value already saved in the db
     * @param string $type the type of the input e.g. a text or if not set a submit field
     * @param string $class_add the formatting code to adjust the formatting e.g. extend the description to the full screen width
     * @param string $placeholder
     * @return string the HTML code for the field
     */
    function input(
        string $url_id,
        msg_id $msg_id,
        string $value = '',
        string $type = '',
        string $class_add = '',
        string $placeholder = ''): string
    {
        global $mtr;
        $name = $mtr->txt($msg_id);
        if ($name != '') {
            $name = ' name="' . $name . '"';
        }
        if ($url_id != '') {
            $id = strtolower($url_id);
        } else {
            $id = '1';
        }
        if ($value != '') {
            $value = ' value="' . $value . '"';
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
        return '<input' . $class . $type . $name . $id . $value . $placeholder . '>';
    }

    function div_form(string $text, string $style = ''): string
    {
        return $this->div_bs($text, 'form-group ' . $style);
    }

    function div_row(string $text, string $style = ''): string
    {
        return $this->div_bs($text, 'row ' . $style);
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
        if ($style_id != null) {
            global $sys;
            $style = $sys->typ_lst->msk_sty->get($style_id);
            if ($style != null) {
                return $style->get_code_id();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * start a html form; the form name must be identical with the php script name
     * @param string $form_name the name and id of the form
     * @returns string the HTML code to start a form
     */
    function form_start(string $form_name): string
    {
        // switch on post forms for private values
        $action = ' action="' . api::HOST_SAME . api::MAIN_SCRIPT . '"';
        $id = ' id="' . $form_name . '"';

        return '<form' . $action . $id . '>';
    }

    /**
     * start a html form; the form name must be identical with the php script name
     * @param string $action the script name
     * @param string $method post, get of any other html form method
     * @param string $txt the inner html code of the form
     * @returns string the HTML code of form
     */
    function form_simple(string $action, string $method, string $txt): string
    {
        // switch on post forms for private values
        $action = ' action="' . $action . '"';
        $method = ' method="' . $method . '"';

        return '<form' . $action . $method . '>' . $txt . '</form>';
    }

    /**
     * create the HTML code for an input field including the label
     * @param string $url_id the id of the input field e.g. n
     * @param msg_id $msg_id the msg_id of the title of the input field e.g. Name
     * @param string $value the suggested value which is in most cases the value already saved in the db
     * @param string $type the type of the input e.g. a text or if not set a submit field
     * @param string $input_class the formatting code to change the input type
     * @param string $style the formatting code to adjust the formatting e.g. extend the description to the full screen width
     * @return string the HTML code for the field with the label
     */
    function form_field(
        string $url_id,
        msg_id $msg_id,
        string $value = '',
        string $type = html_base::INPUT_TEXT,
        string $input_class = '',
        string $style = view_styles::COL_SM_12
    ): string
    {
        // TODO Prio 2 move mtr to label
        global $mtr;
        $name = $mtr->txt($msg_id);
        if (self::UI_USE_BOOTSTRAP) {
            $text = $this->label($name, $url_id);
            $text .= $this->input($url_id, $msg_id, $value, $type, $input_class);
            return $this->div_form($text, $style);
        } else {
            return $this->input($url_id, $msg_id, $value, $type);
        }
    }

    /**
     * @return string html code to end a form
     */
    function form_end(): string
    {
        return '</form>';
    }

    /**
     * @return string html code to combine the next elements to one row
     */
    function row_start(): string
    {
        $result = '<div class="row ';
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
        $result .= '<div class="row ';
        $result .= view_styles::COL_SM_12;
        $result .= ' justify-content-end">';
        return $result;
    }

    /**
     * @return string html code to end a form
     */
    function row_end(): string
    {
        return '</div>';
    }

    /*
     * base
     */

    function br2(): string
    {
        return '<br><br>';
    }

    function br(): string
    {
        return '<br>';
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
        echo $txt . '<br>';
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
     * TODO create a auto refresh page for async processes and the HTML front end without JavaScript
     * TODO create a db table, where the async process can drop the status
     * TODO add the refresh frequency setting to the general and user settings
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
     * TODO: to be adjusted depending on the display language
     * @param string $txt the html code
     * @param string $lan the language code of the text
     * @return string the warped code
     */
    private function page_lan(string $txt, string $lan): string
    {
        return '<html lang="' . $lan . '">' . $txt . '</html>';
    }

    /**
     * wrap the head tag around html header code
     * @param string $txt the html header code
     * @return string the warped header code
     */
    private function head(string $txt): string
    {
        return '<' . self::HEAD . '>' . $txt . '</' . self::HEAD . '>';
    }

    /**
     * wrap the body tag around html body code
     * @param string $txt the html body code
     * @return string the warped body code
     */
    private function body(string $txt): string
    {
        return '<' . self::BODY . '>' . $txt . '</' . self::BODY . '>';
    }

    /**
     * wrap the body tag around html body code
     * @param string $txt the html body code
     * @return string the warped body code
     */
    function main(string $txt): string
    {
        return '<' . self::MAIN . ' ' . self::CLASS_HTML . '="' . self::CLASS_MAIN . '">' . $txt . '</' . self::MAIN . '>';
    }

    /**
     * wrap the footer tag around html footer code
     * @param string $txt the html footer code
     * @return string the warped footer code
     */
    private function foot(string $txt): string
    {
        return '<' . self::FOOTER . ' ' . self::CLASS_HTML . '="' . self::CLASS_FOOTER . '">' . $txt . '</' . self::FOOTER . '>';
    }

    /**
     * wrap the div tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the warped html code
     */
    function div(string $txt, string $style = ''): string
    {
        if ($style != '') {
            $style = ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . self::DIV . $style . '>' . $txt . '</' . self::DIV . '>';
    }

    /**
     * wrap the nav tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the warped html code
     */
    function nav(string $txt, string $style = ''): string
    {
        if ($style != '') {
            $style = ' ' . self::CLASS_HTML . '="' . $style . '"';
        }
        return '<' . self::NAV . ' ' . $style . '>' . $txt . '</' . self::NAV . '>';
    }

    /**
     * wrap the div tag around html code
     * @param string $txt the html code
     * @param string $style the html class name
     * @return string the warped html code
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
     * @return string the warped paragraph code
     */
    public function p(string $txt): string
    {
        return '<' . self::P . '>' . $txt . '</' . self::P . '>';
    }

    /**
     * wrap the paragraph button around html code
     * @param string $txt the html code
     * @return string the warped paragraph code
     */
    private function button(string $txt, string $typ, string $class = ''): string
    {
        if ($class != '') {
            $class = self::CLASS_BUTTON . ' ' . $class;
        } else {
            $class = self::CLASS_BUTTON;
        }
        return '<' . self::BUTTON . ' ' . self::TYPE . '="' . $typ . '" ' . self::CLASS_HTML . '="' . $class . '">' . $txt . '</' . self::BUTTON . '>';
    }

    function button_bs(string $text, string $style = '', string $type = ''): string
    {
        if ($style == '') {
            $style = self::BS_BTN_SUCCESS;
        }
        $class = ' ' . self::CLASS_HTML . '="' . self::BS_BTN . ' ' . $style . '"';
        if ($type == '') {
            $type = self::INPUT_SUBMIT;
        }
        $type = ' ' . self::TYPE . '="' . $type . '"';
        return '<' . self::BUTTON . $class . $type . '>' . $text . '</' . self::BUTTON . '>';
    }

    /**
     * TODO Prio 1 translate
     * return the about text in the frontend language
     * @return string the warped nav code
     */
    private function about(): string
    {
        global $mtr;
        return $this->ref(api::ABOUT_SCRIPT_REL, $mtr->txt(msg_id::SYSTEM_TITLE_ABOUT));
    }

    /**
     * TODO Prio 1 translate
     * return the about text in the frontend language
     * @return string the warped nav code
     */
    private function privacy(): string
    {
        global $mtr;
        return $this->ref(api::PRIVACY_SCRIPT_REL, $mtr->txt(msg_id::PRIVACY_POLICY));
    }

    /**
     * TODO Prio 1 translate
     * return the about text in the frontend language
     * @return string the warped nav code
     */
    private function foot_text(): string
    {
        global $mtr;
        $txt = 'All structured data is available under the ';
        $txt .= $this->ref('https://creativecommons.org/publicdomain/zero/1.0/', $mtr->txt(msg_id::CC0), $mtr->txt(msg_id::CC0_LICENSE)) . ' ';
        $txt .= $this->ref('https://github.com/zukunft/zukunft.com', $mtr->txt(msg_id::PROGRAM_CODE)) . ' ';
        $txt .= 'under the ' . $this->ref('https://www.gnu.org/licenses/agpl.html', $mtr->txt(msg_id::AGPL3)) . ' Licence';
        return $txt;
    }

    /**
     * html list item entry
     * @param string $txt the html code that should be a list item
     * @return string the html code of a list item
     */
    function list_item(string $txt): string
    {
        return '<' . self::LI . '>' . $txt . '</' . self::LI . '>';
    }

    /**
     * html unsorted list
     * @param string $txt the html code of the list entries
     * @return string the html code of a unsorted list
     */
    function list_unsorted(string $txt): string
    {
        return '<' . self::UL . '>' . $txt . '</' . self::UL . '>';
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
        return self::HTML_START . '"' . $lan . '">';
    }

    /**
     * @return string with the charset for the html pages
     */
    private function charset(): string
    {
        return '<' . self::META . ' ' . self::CHARSET . '="' . def::ENCODING . '">';
    }

    /**
     * @return string with the charset for the html pages
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
     * @return string the warped title text
     */
    private function title(string $txt, string $pod_name): string
    {
        if ($txt == "") {
            $txt = $pod_name;
        } else {
            if ($pod_name <> "") {
                $txt = $txt . ' (' . $pod_name . ')';
            }
        }
        return '<' . self::TITLE . '>' . $txt . '</' . self::TITLE . '>';
    }

    /**
     * @return string use a simple stylesheet without JavaScript
     */
    private function stylesheet(): string
    {
        return $this->link_style(files::STYLE_HTML);
    }

    /**
     * @return string use a simple stylesheet without JavaScript and without bootstrap (maybe not needed any more)
     */
    private function stylesheet_fallback(): string
    {
        return $this->link_style(files::STYLE_FALLBACK);
    }

    /**
     * @return string the bootstrap stylesheet
     */
    private function stylesheet_bs(): string
    {
        return $this->link_style(files::STYLE_BS);
    }

    /**
     * @return string all JavaScript bootstrap stylesheets
     */
    private function stylesheet_bs_js_all(): string
    {
        return $this->link_style(paths::EXT_LIB_BS_JS);
    }

    /**
     * @return string the font stylesheet
     */
    private function stylesheet_font(): string
    {
        return $this->link_style(files::STYLE_FONT);
    }

    /**
     * @return string use a simple stylesheet without JavaScript
     */
    private function link_style(string $stylesheet): string
    {
        return '<' . self::LINK . ' ' . self::REL . '="' . self::STYLESHEET . '" ' . self::HREF . '="' . $stylesheet . '">';
    }

}
