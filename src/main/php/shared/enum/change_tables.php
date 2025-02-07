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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\enum;

enum change_tables: string
{

    // list of the log table with linked functionalities
    // unlike the table const in sql_db this contains also table names of previous versions
    // and an assignment of the deprecated tables names to the table names of this version
    // this list contains only tables where a direct change by the user is possible
    // so the phrase, term and figure tables are not included here, but in the sql_db list
    // TODO should only contain the table names of past program versions
    //      to combine the log in case of a renamed class
    const USER = 'users';
    const WORD = 'words';
    const WORD_USR = 'user_words';
    const VERB = 'verbs';
    const TRIPLE = 'triples';
    const TRIPLE_USR = 'user_triples';
    const VALUE = 'values';
    const VALUE_USR = 'user_values';
    const VALUE_LINK = 'value_links';
    const FORMULA = 'formulas';
    const FORMULA_USR = 'user_formulas';
    const FORMULA_LINK = 'formula_links';
    const FORMULA_LINK_USR = 'user_formula_links';
    const VIEW = 'views';
    const VIEW_USR = 'user_views';
    const VIEW_TERM_LINK = 'view_term_links';
    //const VIEW_TERM_LINK_USR = 'user_view_term_links';
    const VIEW_COMPONENT = 'components';
    const VIEW_COMPONENT_USR = 'user_components';
    const VIEW_LINK = 'component_links';
    const VIEW_LINK_USR = 'user_component_links';
    const REF = 'refs';
    const REF_USR = 'user_refs';
    const SOURCE = 'sources';
    const SOURCE_USR = 'user_sources';

}