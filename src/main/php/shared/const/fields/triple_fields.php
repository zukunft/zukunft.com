<?php

/*

    shared/const/fields/triple_fields.php - the triple fields used database, back and frontend
    -------------------------------------

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

class triple_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for triples
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'triple_id';
    const string FLD_FROM_COM = 'the phrase_id that is linked which can be null e.g. if a symbol is assigned to a triple (m/s is symbol for meter per second)';
    const string FLD_FROM = 'from_phrase_id';
    const string FLD_VERB_COM = 'the verb_id that defines how the phrases are linked';
    const string FLD_TO_COM = 'the phrase_id to which the first phrase is linked';
    const string FLD_TO = 'to_phrase_id';
    const string FLD_NAME_COM = 'the name used which must be unique within the terms of the user';
    const string FLD_NAME = 'triple_name';
    const string FLD_NAME_GIVEN_COM = 'the unique name manually set by the user, which can be null if the generated name should be used';
    const string FLD_NAME_GIVEN = 'name_given';
    const string FLD_NAME_AUTO_COM = 'the generated name is saved in the database for database base unique check based on the phrases and verb, which can be overwritten by the given name';
    const string FLD_NAME_AUTO = 'name_generated';
    const string FLD_DESCRIPTION_COM = 'text that should be shown to the user in case of mouseover on the triple name';
    const string FLD_WIGHT = 'weight';
    const string FLD_WIGHT_COM = 'the weight of this triple compared to others where 1 represents 100% weight';
    const string FLD_COND_ID_COM = 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
    const string FLD_COND_ID = 'triple_condition_id';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        fields::FLD_DESCRIPTION,
        self::FLD_WIGHT,
        phrase_fields::FLD_TYPE,
        fields::FLD_VIEW,
        fields::FLD_USAGE,
        fields::FLD_IMPACT,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );

}