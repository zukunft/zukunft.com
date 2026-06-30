<?php

/*

    shared/const/fields/phrase_fields.php - the phrase fields used database, back and frontend
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

class phrase_fields
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields shared by words and triples (phrases)
    // *_COM: the description of the field
    const string FLD_TYPE_COM = 'to link coded functionality to phrases e.g. to exclude measure words from a percent result';
    const string FLD_TYPE = 'phrase_type_id';

}