<?php

/*

    shared/const/def.php - general system definitions used in frontend and backend
    --------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

// for the config keys of the colour fallbacks
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';

class def
{

    /*
     * general
     */

    // TODO Prio 1 use this for all named test rows and add a post cleanup check that now change log of a test is remaining
    // general prefix for named test objects that should never be used for any real object
    const string TEST_PREFIX = 'zukunft.com System TesT ';

    /*
     * CAUTION! debug setting -> set always to false after debugging!
     * true to show in the output of e.g. test/test.php if an api answer comes from the cache
     * (the api sends api::CACHE_HEADER and rest_call::api_call shows it)
     */
    const bool DEBUG_API_CACHE = false;
    // true to show each step of the phrase data (api/phraseData) with its number of objects and,
    // by log_debug, the seconds since the last message, to find the slow step
    const bool DEBUG_PHRASE_DATA = false;


    /*
     * fallback
     */

    // configuration values used as fallback if the value is missing in the system configuration
    const int FALLBACK_DB_PAGE_ROWS = 20; // the number of database rows that should be loaded at once
    const int FALLBACK_PHRASES_RELATED = 7; // the number of related phrases to show if nothing else is defined in the user or system configuration
    // fallback for the number of open system errors shown to the user;
    // overridden by config.yaml entry frontend.lists.limit.system errors
    const int FALLBACK_USER_ERRORS = 10;
    // fallback for the response time in milliseconds from which a request is critical on the admin dashboard;
    // overridden by config.yaml entry pod.limits.usage.response time.millisecond.warning
    const int FALLBACK_RESPONSE_TIME_WARNING = 2000;
    // fallback for the seconds after which a page that is not yet complete shows that the data is being collected;
    // overridden by config.yaml entry user.default.frontend.behaviour.response time.target
    const float FALLBACK_RESPONSE_TIME_TARGET = 1.0;
    // fallback for the number of failed logins of the same name from which they are critical on the admin dashboard;
    // overridden by config.yaml entry pod.limits.max login.per day.by ip-address
    const int FALLBACK_MAX_LOGIN = 5;
    // fallback for the seconds an open job may wait before it is critical on the admin dashboard;
    // overridden by config.yaml entry system.job.max delay
    const int FALLBACK_JOB_MAX_DELAY = 3600;
    // fallbacks for the server use of a user in the dashboard days from which the user is critical on the admin dashboard;
    // overridden by config.yaml entry pod.limits.user.log.dashboard.threshold
    const int FALLBACK_USER_LOG_MAX_REQUESTS = 10000;
    const int FALLBACK_USER_LOG_MAX_UPDATES = 1000;
    const int FALLBACK_USER_LOG_MAX_REQUEST_MS = 600000;
    const int FALLBACK_USER_LOG_MAX_UPDATE_MS = 60000;
    // fallback for the per-verb limit of related phrases shown in the page title (Zurich -> city, canton, ...);
    // overridden by config.yaml entry frontend.lists.limit.related per verb once the $cfg accessor is wired
    const int LIMIT_RELATED_PER_VERB = 2;
    // fallback separator between the category and the type subtitle in a page title;
    // overridden by config.yaml entry frontend.lists.separator.category
    const string FALLBACK_CATEGORY_SEPARATOR = ' / ';
    // fallback separator between list entries e.g. the share and protection subtitle in a page title;
    // overridden by config.yaml entry frontend.lists.separator.entry
    const string FALLBACK_ENTRY_SEPARATOR = ', ';
    // fallback separator between the object, view and pod name in the html (browser tab) page title;
    // overridden by config.yaml entry frontend.lists.separator.title
    const string FALLBACK_TITLE_SEPARATOR = ' - ';
    // between the label and the value of a read only field e.g. 'Plural: are' on the verb page
    const string FALLBACK_LABEL_SEPARATOR = ': ';
    // fallback for the minimal screen width in pixel to show 'side or below' components side by side;
    // overridden by config.yaml entry frontend.layout.side width.min
    const int FALLBACK_MIN_SIDE_WIDTH = 1000;
    // fallback for the screen width in pixel from which the full set of 'side or below' columns
    // (up to position_types::MAX_SIDE_COLUMNS) is shown side by side;
    // overridden by config.yaml entry frontend.layout.side width.max
    const int FALLBACK_WIDE_SIDE_WIDTH = 2800;
    // fallback for the css font family of every page, its buttons and the editors, which the page
    // head sets as --font-page (html_base::theme_style); overridden by config.yaml entry frontend.layout.font
    const string FALLBACK_FONT = 'sans-serif';
    // the dark or light mode of a user (config.yaml frontend.layout.mode): the pages follow the
    // setting of the browser, are always light or always dark (see html_base::theme_css);
    // light by default, so that a page looks the same for every user until the user switches
    const int MODE_BROWSER = 0;
    const int MODE_LIGHT = 1;
    const int MODE_DARK = 2;
    const int FALLBACK_MODE = self::MODE_LIGHT;
    // fallback for the colours of the pages in the light and in the dark mode, which the page head
    // sets as css variables (html_base::theme_style) for style_html.css; the css variable => the
    // config.yaml key below frontend.layout.colours.page, the light colour and the dark colour of
    // the key below frontend.layout.colours.page.dark; each colour has the same value as in config.yaml
    const array FALLBACK_PAGE_COLOURS = [
        '--page-grey' => [words::GREY, '#9ea09e', '#8b95a8'], // the zukunft.com grey e.g. of the text and the main button
        '--page-link' => [words::LINK, '#385eb7', '#9db6f2'], // the blue of the links
        '--page-deep' => [words::DEEP, '#274b8f', '#c6d5f5'], // the dark blue e.g. of a value or a logged in user
        '--page-border' => [words::BORDER, '#b9c6d6', '#33456a'], // the grey blue lines e.g. of a panel
        '--page-white' => [words::WHITE, '#ffffff', '#0f1828'], // the background of the page, a field or a menu
        '--page-rule' => [words::RULE, '#dee2e6', '#2a3a57'], // the light lines e.g. between the rows of a table
        '--page-panel' => [words::PANEL, '#f9fafb', '#18233a'], // the light background e.g. of the header or a panel
        '--page-input' => [words::INPUT, '#ccc', '#33456a'], // the border of an input field and the user menu
        '--page-changed' => [words::CHANGED, '#6b86c2', '#93a9dc'], // a changed or assumed value
        '--page-tab' => [words::TAB, '#f8f9fa', '#1a263d'], // the background of a not selected tab
        '--page-highlight' => [words::HIGHLIGHT, '#dbe4f5', '#233761'], // e.g. a search hit or a section row
        '--page-shadow' => [words::SHADOW, 'rgba(0, 0, 0, 0.1)', 'rgba(0, 0, 0, 0.5)'], // the shadow of the user menu
        '--page-menu' => [words::MENU, '#f0f0f0', '#1a263d'], // a menu entry when the mouse is over it
        '--page-faint' => [words::FAINT, '#aaa', '#5c6a85'], // the sort icon
        '--page-label' => [words::LABEL, '#777', '#8492ad'], // the sub header of a menu
        '--page-file' => [words::FILE, '#6e706e', '#b4c1da'], // a setting with the value of config.yaml
        '--page-negative' => [words::NEGATIVE, '#dc3545', '#f08a80'], // a negative number
        '--page-hover' => [words::HOVER, '#7a9fbf', '#4f6fa8'], // a button when the mouse is over it
        '--page-black' => [words::BLACK, 'black', '#e3eaf7'], // the fraction line of a formula
    ];
    // fallback for the colours of the calculator and the formula editor in the light and in the
    // dark mode; the css variable => the config.yaml key below frontend.layout.colours.editor,
    // the light colour and the dark colour of the key below frontend.layout.colours.editor.dark
    const array FALLBACK_EDITOR_COLOURS = [
        '--bg' => [words::BACKGROUND, '#eef2f8', '#0e1524'],
        '--surface' => [words::SURFACE, '#ffffff', '#151f33'],
        '--surface-2' => [words::SHEET, '#f6f8fc', '#1a263d'],
        '--ink' => [words::INK, '#1b2f5c', '#e3eaf7'],
        '--ink-2' => [words::SUBTEXT, '#4b5d83', '#b4c1da'],
        '--muted' => [words::MUTED, '#7b88a3', '#8492ad'],
        '--line' => [words::LINE, '#d2dbe9', '#2a3a57'],
        '--accent' => [words::ACCENT, '#385eb7', '#7d9ef0'],
        '--accent-deep' => [words::DEEP, '#274b8f', '#a9c0f7'],
        '--accent-soft' => [words::SOFT, '#dbe4f5', '#233761'],
        '--shared' => [words::SHARED, '#8a5a07', '#e9b45a'],
        '--shared-soft' => [triples::SHARED_SOFT, '#fbf0db', '#3a2e17'],
        '--danger' => [words::DANGER, '#c23b30', '#f08a80'],
        '--danger-soft' => [triples::DANGER_SOFT, '#fbe4e1', '#42221f'],
        '--ok' => [words::SUCCESS, '#2f7d55', '#7fd1a3'],
        '--ok-soft' => [triples::SUCCESS_SOFT, '#dff1e7', '#1d3a2b'],
        // the live page of the calculator editor, which looks like the page in the light mode
        '--pv-text' => [triples::PREVIEW_TEXT, '#274b8f', '#c6d5f5'],
        '--pv-label' => [triples::PREVIEW_LABEL, '#385eb7', '#9db6f2'],
        '--pv-muted' => [triples::PREVIEW_MUTED, '#9ea09e', '#8b95a8'],
        '--pv-panel' => [triples::PREVIEW_PANEL, '#f9fafb', '#18233a'],
        '--pv-border' => [triples::PREVIEW_BORDER, '#b9c6d6', '#33456a'],
        '--pv-field' => [triples::PREVIEW_FIELD, '#ffffff', '#0f1828'],
        '--pv-rule' => [triples::PREVIEW_RULE, '#dee2e6', '#2a3a57'],
        '--pv-sec' => [triples::PREVIEW_SECTION, '#dbe4f5', '#233761'],
        '--pv-assumed' => [triples::PREVIEW_ASSUMED, '#6b86c2', '#93a9dc'],
        '--pv-neg' => [triples::PREVIEW_NEGATIVE, '#dc3545', '#f08a80'],
    ];
    // fallback for the colours of the table charts (web\value\table_chart) in the light and in the
    // dark mode; the css variable => the config.yaml key below frontend.layout.colours.chart, the
    // light colour and the dark colour of the key below frontend.layout.colours.chart.dark
    const array FALLBACK_CHART_COLOURS = [
        '--bg' => [words::BACKGROUND, '#fbfaf7', '#1b1b1a'],
        '--fg' => [words::TEXT, '#1d1d1b', '#ecebe6'],
        '--muted' => [words::MUTED, '#77756f', '#9a978f'],
        '--grid' => [words::GRID, '#e3e0d8', '#353431'],
        '--range' => [words::RANGE, '#f0c9a8', '#6b3d22'], // the range bar
        '--accent' => [words::ACCENT, '#c4561c', '#f08a4b'], // the number of a range and a hovered point
        '--accent2' => [words::POINT, '#2d6a8a', '#4f93b8'],
        '--hover' => [words::HOVER, '#f4efe6', '#262523'], // the hovered row
        '--point-text' => [words::LABEL, '#fff', '#fff'], // the number inside a point
    ];
    // fallback for the number of the most relevant values read for one phrase and used by the
    // phrase values view to pick the column phrases;
    // overridden by config.yaml entry frontend.lists.limit.values.read
    const int FALLBACK_PHRASE_VALUES_READ = 1000;
    // fallback for the number of triples read for one verb and sent to the verb page;
    // overridden by config.yaml entry frontend.lists.limit.triples.read
    const int FALLBACK_VERB_TRIPLES_READ = 100;
    const string ENCODING = 'utf-8'; // the default encoding for the backend
    const string FILE_PHP = '.php'; // the file extension for the code scripts
    const string PROJECT_PATH = '/zukunft.com/'; // the default project path used to filter the exception trace


    /*
     * external links
     */

    const string LINK_CC0 = 'https://creativecommons.org/publicdomain/zero/1.0/';
    const string LINK_GITHUB = 'https://github.com/zukunft/zukunft.com';
    const string LINK_AGPL = 'https://www.gnu.org/licenses/agpl.html';
    const string LINK_GITHUB_TREAM = 'https://github.com/zukunft/tream';
    const string LINK_TREAM_DEMO = 'https://tream.biz/p4a/applications/tream/';
    const string LINK_DOI = 'https://doi.org/'; // the base url to create the link of a doi e.g. of a source
    const string LINK_PAPER_DELPHI = 'https://dx.doi.org/10.2139/ssrn.6497759';
    const string LINK_PAPER_IMPERATIVE = 'https://doi.org/10.5281/zenodo.19443909';

}
