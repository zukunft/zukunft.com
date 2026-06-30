<?php

/*

    shared/const/fields/result_fields.php - the result fields used database, back and frontend
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

class result_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for results
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'group_id';
    const string FLD_SOURCE = 'source_';
    // TODO replace with result_fields::FLD_SOURCE . group_fields::FLD_ID
    const string FLD_SOURCE_GRP = 'source_group_id';
    // TODO replace with group_fields::FLD_ID
    const string FLD_GRP = 'group_id';
    const string FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const string FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const string FLD_RESULT_TS_ID = 'result_time_series_id';

}
