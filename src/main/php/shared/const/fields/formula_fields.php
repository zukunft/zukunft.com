<?php

/*

    shared/const/fields/formula_fields.php - the formula fields used database, back and frontend
    --------------------------------------

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

namespace Zukunft\ZukunftCom\main\php\shared\const\fields;

class formula_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for formulas
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'formula_id';
    const string FLD_NAME_COM = 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)';
    const string FLD_NAME = 'formula_name';
    const string FLD_TYPE_COM = 'the id of the formula type';
    const string FLD_TYPE = 'formula_type_id';
    const string FLD_FORMULA_TEXT_COM = 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
    const string FLD_FORMULA_TEXT = 'formula_text';
    const string FLD_FORMULA_USER_TEXT_COM = 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
    const string FLD_FORMULA_USER_TEXT = 'resolved_text';
    const string FLD_LATEX_COM = 'the formula in latex format';
    const string FLD_LATEX = 'latex';
    const string FLD_DESCRIPTION_COM = 'text to be shown to the user for mouse over; to be replaced by a language form entry';
    const string FLD_ALL_NEEDED_COM = 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
    const string FLD_ALL_NEEDED = 'all_values_needed';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_NAME,
        self::FLD_FORMULA_TEXT,
        self::FLD_FORMULA_USER_TEXT,
        self::FLD_LATEX,
        fields::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_ALL_NEEDED,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );

}