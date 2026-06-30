<?php

/*

    shared/const/fields/component_fields.php - the component fields used database, back and frontend
    ----------------------------------------

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

class component_fields
{

    /*
     * db const
     */

    // the database and JSON object field names used only for view components
    // *_COM: the description of the field
    // *_SQL_TYP: the sql field type used for this field
    const string FLD_ID = 'component_id';
    const string FLD_NAME_COM = 'the unique name used to select a component by the user';
    const string FLD_NAME = 'component_name';
    const string FLD_DESCRIPTION_COM = 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
    const string FLD_TYPE_COM = 'to select the predefined functionality';
    const string FLD_TYPE = 'component_type_id';
    const string FLD_UI_MSG_ID_COM = 'used for system components the id to select the language specific user interface message e.g. "add word"';
    const string FLD_UI_MSG_ID = 'ui_msg_code_id';
    const string FLD_UI_MSG_ID_VARS = 'ui_msg_code_id_vars';
    const string FLD_UI_MSG_ID_VARS_COM = 'used for system components the id to select the language specific user interface message where some variable placeholders are replaced with system values';
    const string FLD_UI_MSG_ID_EXCEPTION = 'ui_msg_code_id_exception';
    const string FLD_UI_MSG_ID_EXCEPTION_COM = 'used for system components the id to select the language specific user interface exception message e.g. if the system value is zero';
    const string FLD_UI_MSG_VAL_EXCEPTION = 'ui_msg_value_exception';
    const string FLD_UI_MSG_VAL_EXCEPTION_COM = 'used for system components the value to select the exception message e.g. 0 (zero)';
    // TODO move the lined phrases to a component phrase link table for n:m relation with a type for each link
    const string FLD_ROW_PHRASE_COM = 'for a tree the related value the start node';
    const string FLD_ROW_PHRASE = 'word_id_row';
    const string FLD_COL_PHRASE_COM = 'to define the type for the table columns';
    const string FLD_COL_PHRASE = 'word_id_col';
    const string FLD_COL2_PHRASE_COM = 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
    const string FLD_COL2_PHRASE = 'word_id_col2';
    const string FLD_FORMULA_COM = 'used for type 6';
    const string FLD_LINK_COMP_COM = 'to link this component to another component';
    const string FLD_LINK_COMP = 'linked_component_id';
    const string FLD_LINK_COMP_TYPE_COM = 'to define how this entry links to the other entry';
    const string FLD_LINK_COMP_TYPE = 'component_link_type_id';
    const string FLD_LINK_TYPE_COM = 'e.g. for type 4 to select possible terms';
    const string FLD_LINK_TYPE = 'link_type_id';

    // all database field names excluding the id
    // used to identify if there are some user-specific changes
    // and to fix the order in a useful way for the change confirm view
    const array ALL_NAMES = array(
        self::FLD_NAME,
        fields::FLD_DESCRIPTION,
        self::FLD_TYPE,
        fields::FLD_STYLE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula_fields::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        fields::FLD_USAGE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );

}
