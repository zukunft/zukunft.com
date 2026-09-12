<?php

/*

    shared/enum/change_fields.php - enum of all change table fields including field names of previous versions
    -----------------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum change_fields: string
{

    // the field names in the log are for the current version the same as the field names of the single objects
    // the field names are listed here again, so that the log can include all changes even if the field name has changed
    // *_NAME is the name as used in the program or as it has been used in a previous program version
    // *_NAME_DSP is the description that should be shown to the user
    const string FLD_TABLE = 'table_id';
    // the owner of a user sandbox object; a change of it is shown as 'set owner to' with the owner
    // name instead of the raw user id (see web/log/change_log_named::what_text)
    const string FLD_USER_ID = 'user_id';
    const string FLD_WORD_NAME = 'word_name';
    const int FLD_WORD_NAME_ID = 10;
    const string FLD_WORD_NAME_COM = '';
    const string FLD_WORD_NAME_DSP = 'name';
    const string FLD_WORD_VIEW = 'view_id';
    const string FLD_WORD_PLURAL = 'plural';
    const string FLD_PHRASE_TYPE = 'phrase_type_id';
    const string FLD_VERB_NAME = 'verb_name';
    const string FLD_TRIPLE_NAME = 'triple_name';
    const string FLD_GIVEN_NAME = 'name_given';
    const string FLD_TRIPLE_VIEW = 'view_id';
    const string FLD_NUMERIC_VALUE = 'numeric_value';
    const string FLD_VALUE_GROUP = 'group_id';
    const string FLD_FORMULA_NAME = 'formula_name';
    const string FLD_FORMULA_USR_TEXT = 'resolved_text';
    const string FLD_FORMULA_REF_TEXT = 'formula_text';
    const string FLD_FORMULA_LATEX = 'latex';
    const string FLD_FORMULA_TYPE = 'formula_type_id';
    const string FLD_ALL_NEEDED = 'all_values_needed';
    const string FLD_FORMULA_ALL = 'all_values_needed';
    const string FLD_SOURCE_NAME = 'source_name';
    const string FLD_SOURCE_URL = 'url';
    const string FLD_SOURCE_DOI = 'doi';
    const string FLD_REF_KEY = 'external_key';
    const string FLD_VIEW_NAME = 'view_name';
    const string FLD_COMPONENT_NAME = 'component_name';
    const string FLD_COMPONENT_TYPE = 'component_type_id';

    // the prime identity field of each user sandbox object (its name, numeric value or external key);
    // a change of one of these is shown in the change log table pure without the field name prefix,
    // because the log row already represents that object, whereas any other field is prefixed with its
    // translated name (see web/log/change_log_named::what_text)
    const array PRIME_FIELDS = [
        self::FLD_WORD_NAME,
        self::FLD_VERB_NAME,
        self::FLD_TRIPLE_NAME,
        self::FLD_FORMULA_NAME,
        self::FLD_SOURCE_NAME,
        self::FLD_VIEW_NAME,
        self::FLD_COMPONENT_NAME,
        self::FLD_NUMERIC_VALUE,
        self::FLD_REF_KEY,
    ];

}