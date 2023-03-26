<?php

/*

    model/view/view_component_type.php - ENUM of the view component types
    ----------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace model;

class view_cmp_type
{
    // list of the view component types that have a coded functionality
    const TEXT = "text";
    const WORD = "fixed";
    const WORD_SELECT = "word_select";
    const WORDS_UP = "word_list_up";
    const WORDS_DOWN = "word_list_down";
    const PHRASE_NAME = "word_name";
    const WORD_VALUE = "word_value_list";
    const VALUES_ALL = "values_all";
    const VALUES_RELATED = "values_related";
    const FORMULAS = "formula_list";
    const FORMULA_RESULTS = "formula_results";
    const JSON_EXPORT = "json_export";
    const XML_EXPORT = "xml_export";
    const CSV_EXPORT = "csv_export";
    const VIEW_SELECT = "view_select";
    const LINK = "link";

}
