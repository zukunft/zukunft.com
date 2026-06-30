<?php

/*

    shared/const/fields/ref_fields.php - the reference fields used database, back and frontend
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const\fields;

class ref_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for references
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'ref_id';
    const string FLD_USER_COM = 'the user who has created or adjusted the reference';
    const string FLD_EX_KEY_COM = 'the unique external key used in the other system';
    const string FLD_EX_KEY = 'external_key';
    const string FLD_TYPE = 'ref_type_id';
    const string FLD_SOURCE_COM = 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
    const string FLD_SOURCE = 'source_id';
    const string FLD_PHRASE_COM = 'the phrase for which the external data should be synchronised';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_EX_KEY,
        fields::FLD_URL,
        fields::FLD_DESCRIPTION,
        fields::FLD_EXCLUDED
    );

}