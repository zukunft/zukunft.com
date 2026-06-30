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


class def
{

    /*
     * fallback
     */

    // configuration values used as fallback if the value is missing in the system configuration
    const int FALLBACK_DB_PAGE_ROWS = 20; // the number of database rows that should be loaded at once
    const int FALLBACK_PHRASES_RELATED = 7; // the number of related phrases to show if nothing else is defined in the user or system configuration
    // fallback for the number of open system errors shown to the user;
    // overridden by config.yaml entry frontend.lists.limit.system errors
    const int FALLBACK_USER_ERRORS = 10;
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
    // fallback for the minimal screen width in pixel to show 'side or below' components side by side;
    // overridden by config.yaml entry frontend.layout.side width.min
    const int FALLBACK_MIN_SIDE_WIDTH = 1000;
    // fallback for the screen width in pixel from which the full set of 'side or below' columns
    // (up to position_types::MAX_SIDE_COLUMNS) is shown side by side;
    // overridden by config.yaml entry frontend.layout.side width.max
    const int FALLBACK_WIDE_SIDE_WIDTH = 2800;
    const string ENCODING = 'utf-8'; // the default encoding for the backend
    const string FILE_PHP = '.php'; // the file extension for the code scripts


    /*
     * external links
     */

    const string LINK_CC0 = 'https://creativecommons.org/publicdomain/zero/1.0/';
    const string LINK_GITHUB = 'https://github.com/zukunft/zukunft.com';
    const string LINK_AGPL = 'https://www.gnu.org/licenses/agpl.html';
    const string LINK_GITHUB_TREAM = 'https://github.com/zukunft/tream';
    const string LINK_TREAM_DEMO = 'https://tream.biz/p4a/applications/tream/';
    const string LINK_PAPER_DELPHI = 'https://dx.doi.org/10.2139/ssrn.6497759';
    const string LINK_PAPER_IMPERATIVE = 'https://doi.org/10.5281/zenodo.19443909';

}
