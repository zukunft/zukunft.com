<?php

/*

    shared/const/fields/word_fields.php - the word fields used database, back and frontend
    -----------------------------------

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

class word_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'word_id'; // TODO change the user_id field comment to 'the user who has changed the standard word'
    const string FLD_NAME_COM = 'the text used for searching';
    const string FLD_NAME = 'word_name';
    const string FLD_DESCRIPTION_COM = 'to be replaced by a language form entry';
    const string FLD_TYPE_COM = 'to link coded functionality to words e.g. to exclude measure words from a percent result';
    const string FLD_PLURAL_COM = 'to be replaced by a language form entry; TODO to be move to language forms';
    const string FLD_PLURAL = 'plural'; // TODO move to language types

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_NAME,
        self::FLD_PLURAL,
        fields::FLD_DESCRIPTION,
        phrase_fields::FLD_TYPE,
        fields::FLD_VIEW,
        fields::FLD_USAGE,
        fields::FLD_IMPACT,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );

}
