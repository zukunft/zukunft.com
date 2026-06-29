<?php

/*

    shared/const/fields/value_fields.php - the value fields used database, back and frontend
    ------------------------------------

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

class value_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for values
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'group_id';
    const string FLD_VALUE = 'numeric_value';
    // TODO move the sandbox value object
    const string FLD_VALUE_TEXT = 'text_value';
    const string FLD_VALUE_TIME = 'time_value';
    const string FLD_VALUE_GEO = 'geo_value';
    const string FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const string FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const string FLD_VALUE_TS_ID = 'value_time_series_id';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_VALUE,
        source_fields::FLD_ID,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );

}
