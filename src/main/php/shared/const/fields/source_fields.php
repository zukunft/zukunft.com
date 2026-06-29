<?php

/*

    shared/const/fields/source_fields.php - the source fields used database, back and frontend
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

class source_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for sources
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'source_id';
    const string FLD_NAME_COM = 'the unique name of the source used e.g. as the primary search key';
    const string FLD_NAME = 'source_name';
    const string FLD_DESCRIPTION_COM = 'the user-specific description of the source for mouse over helps';
    const string FLD_TYPE_COM = 'link to the source type';
    const string FLD_TYPE = 'source_type_id';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_NAME,
        fields::FLD_DESCRIPTION,
        fields::FLD_URL,
        self::FLD_TYPE,
        fields::FLD_USAGE,
        fields::FLD_EXCLUDED
    );

}