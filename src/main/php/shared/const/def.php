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
    const bool DEBUG_PHRASE_DATA = true;


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
