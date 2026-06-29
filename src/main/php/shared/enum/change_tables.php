<?php

/*

    shared/enum/change_tables.php - enum of all change tables including table names of previous versions
    -----------------------------


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum change_tables: string
{

    // list of the log table with linked functionalities
    // unlike the table const in sql_db this contains also table names of previous versions
    // and an assignment of the deprecated tables names to the table names of this version
    // this list contains only tables where a direct change by the user is possible
    // so the phrase, term and figure tables are not included here, but in the sql_db list
    // TODO should only contain the table names of past program versions
    //      to combine the log in case of a renamed class
    const string USER = 'users';
    const int USER_ID = 1;
    const string USER_NAME = 'users';
    const string USER_COM = '';
    const string WORD = 'words';
    const string WORD_USR = 'user_words';
    const string VERB = 'verbs';
    const string TRIPLE = 'triples';
    const string TRIPLE_USR = 'user_triples';
    const string VALUE = 'values';
    const string VALUE_USR = 'user_values';
    const string VALUE_LINK = 'value_links';
    const string FORMULA = 'formulas';
    const string FORMULA_USR = 'user_formulas';
    const string FORMULA_LINK = 'formula_links';
    const string FORMULA_LINK_USR = 'user_formula_links';
    const string VIEW = 'views';
    const string VIEW_USR = 'user_views';
    const string VIEW_TERM_LINK = 'term_views';
    //const string VIEW_TERM_LINK_USR = 'user_term_views';
    const string VIEW_COMPONENT = 'components';
    const string VIEW_COMPONENT_USR = 'user_components';
    const string VIEW_LINK = 'component_links';
    const string VIEW_LINK_USR = 'user_component_links';
    const string REF = 'refs';
    const string REF_USR = 'user_refs';
    const string SOURCE = 'sources';
    const string SOURCE_USR = 'user_sources';
    const string RESULT = 'results';
    const string VIEW_RELATION = 'view_relations';
    const string VIEW_RELATION_USR = 'user_view_relations';

    // value sub-tables that hold the typed values (also user-changeable, registered in change_tables.csv)
    const string VALUE_TIME = 'values_time';
    const string VALUE_TEXT = 'values_text';
    const string VALUE_GEO = 'values_geo';
    const string VALUE_TIME_SERIES = 'values_time_series';
    const string VALUE_TS_DATA = 'value_ts_data';

    // system tables that are registered in change_tables.csv to complete the class to table mapping
    // TODO Prio 2 use the *_db class const
    const string CONFIG = 'config';
    const string SYS_LOG = 'sys_log';
    const string USER_STATUS = 'user_statuum';
    const string JOB_STATUS = 'job_statuum';
    const string DB_CACHE_STATUS = 'db_cache_statuum';
    const string SYS_LOG_STATUS = 'sys_log_statuum';

}