<?php

/*

    shared/const/fields/fields.php - general field names used for the database, back- and frontend
    ------------------------------

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

class fields
{

    /*
     * const
     */

    // fields names used for several objects
    // *_COM: the description of the field

    // named
    const string FLD_DESCRIPTION_COM = 'the user-specific description for mouse over helps';
    const string FLD_DESCRIPTION = 'description'; // field name for the description of a named object (word, source, view, ...)

    // type
    const string FLD_TYPE_NAME_COM = 'the user-specific name of a type; types are used to assign code to a db row';
    const string FLD_TYPE_NAME = 'type_name';  // field name for the user-specific type name (type objects)
    const string FLD_INACTIVE_COM = 'true if the object is not yet active e.g. because it is moved to the prime objects with a 16 bit id';
    const string FLD_INACTIVE = 'inactive';    // field name for the inactive flag (word and triple)

    // code
    const string FLD_CODE_ID_COM = 'field name for the code link e.g. for words used for the system configuration';
    const string FLD_CODE_ID = 'code_id';      // field name for the code id (named and type objects)

    // flex view
    const string FLD_VIEW_COM = 'the default display mask for the object';
    const string FLD_VIEW = 'view_id';         // field name for the default view of an object (word, triple, formula, user, ...)
    const string FLD_STYLE_COM = 'the default display style for the object';
    const string FLD_STYLE = 'view_style_id';  // field name for the default style (view and component)

    // link
    const string FLD_URL_COM = 'the concrete url for the entry';
    const string FLD_URL = 'url';              // field name for an external url (source and ref)

    // value
    const string FLD_VALUE_COM = 'the configuration value';
    const string FLD_VALUE = 'value';          // field name for the configuration value

    // update
    const string FLD_LAST_UPDATE_COM = 'timestamp of the last update';
    const string FLD_LAST_UPDATE = 'last_update'; // field name for the last update timestamp (formula, ref, db cache, ...)

    // impact
    const string FLD_USAGE_COM = 'the number of linked objects (values, triples and formulas) to the object (e.g. word), which gives an indication of the importance and is used as fallback value for sorting';
    const string FLD_USAGE = 'usage';          // field name for the usage counter (phrase, term, source, ...)
    const string FLD_IMPACT_COM = 'a cached number used for default sorting of objects and an indication of the importance as defined by the formula specified in the user config by the words "impact calculation" e.g. for math const the time of discovery is used or for currencies the average daily turnover  and is used as fallback value for sorting';
    const string FLD_IMPACT = 'impact';        // field name for the impact value (phrase, term, word)

    // all sandbox
    const string FLD_EXCLUDED_COM = 'true if a user, but not all, have removed it';
    const string FLD_EXCLUDED = 'excluded';    // field name to delete the object only for one user (all sandbox objects)
    const string FLD_SHARE_COM = 'to restrict the access';
    const string FLD_SHARE = "share_type_id";  // field name for the share permission
    const string FLD_PROTECT_COM = 'to protect against unwanted changes';
    const string FLD_PROTECT = "protect_id";   // field name for the protection level

}
