<?php

/*

    shared/enum/messages.php - enum of the user message ids and the text in the default language
    ------------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\enum;

enum messages: string
{

    // start and end maker for message id within a text to allow changing the order of vars within a message
    const VAR_START = '<!mid';
    const VAR_END = '!>';

    // unique message keys
    // *_txt sample translation to test the English mapping
    case IS_RESERVED = 'is_reserved';
    case IS_RESERVED_TXT = 'is a reserved';
    case RESERVED_NAME = 'reserved_name';
    case NOT_SIMILAR = 'not_similar';
    case RELOAD = 'reload';
    case OF_DEFAULT = 'of_default';
    case FAILED = 'failed';
    case DONE = 'done';

    // special message id placeholders
    case ERROR = 'error';
    case NONE = '';

    // for the change log
    case LOG_ADD = 'added';
    case LOG_UPDATE = 'changed';
    case LOG_DEL = 'deleted';
    case LOG_LINK = 'linked';
    case LOG_TO = 'to';

    // text to be shown in buttons
    case ADD = 'add';
    case EDIT = 'edit';
    case DEL = 'del';
    case SEARCH_MAIN = 'search_main';
    case WORD_ADD = 'word_add';
    case WORD_EDIT = 'word_edit';
    case WORD_DEL = 'word_del';
    case WORD_UNLINK = 'unlink_word';
    case VERB_ADD = 'verb_add';
    case VERB_EDIT = 'verb_edit';
    case VERB_DEL = 'verb_del';
    case TRIPLE_ADD = 'triple_add';
    case TRIPLE_EDIT = 'triple_edit';
    case TRIPLE_DEL = 'triple_del';
    case VALUE_ADD = 'value_add';
    case VALUE_ADD_SIMILAR = 'value_add_similar';
    case VALUE_EDIT = 'value_edit';
    case VALUE_DEL = 'value_del';
    case FORMULA_ADD = 'formula_add';
    case FORMULA_EDIT = 'formula_edit';
    case FORMULA_DEL = 'formula_del';
    case FORMULA_LINK = 'formula_link';
    case FORMULA_UNLINK = 'formula_unlink';
    case VIEW_ADD = 'view_add';
    case VIEW_EDIT = 'view_edit';
    case VIEW_DEL = 'view_del';
    case COMPONENT_ADD = 'component_add';
    case COMPONENT_EDIT = 'component_edit';
    case COMPONENT_DEL = 'component_del';
    case COMPONENT_LINK = 'component_link';
    case COMPONENT_UNLINK = 'component_unlink';
    case PLEASE_SELECT = 'please_select';
    case FORM_WORD_ADD_TITLE = 'form_title_word_add';
    case FORM_WORD_EDIT_TITLE = 'form_title_word_edit';
    case FORM_WORD_DEL_TITLE = 'form_title_word_del';
    case FORM_VERB_ADD_TITLE = 'form_title_verb_add';
    case FORM_VERB_EDIT_TITLE = 'form_title_verb_edit';
    case FORM_VERB_DEL_TITLE = 'form_title_verb_del';
    case FORM_TRIPLE_ADD_TITLE = 'form_title_triple_add';
    case FORM_TRIPLE_EDIT_TITLE = 'form_title_triple_edit';
    case FORM_TRIPLE_DEL_TITLE = 'form_title_triple_del';
    case FORM_SOURCE_ADD_TITLE = 'form_title_source_add';
    case FORM_SOURCE_EDIT_TITLE = 'form_title_source_edit';
    case FORM_SOURCE_DEL_TITLE = 'form_title_source_del';
    case FORM_REF_ADD_TITLE = 'form_title_ref_add';
    case FORM_REF_EDIT_TITLE = 'form_title_ref_edit';
    case FORM_REF_DEL_TITLE = 'form_title_ref_del';
    case FORM_GROUP_ADD_TITLE = 'form_title_group_add';
    case FORM_GROUP_EDIT_TITLE = 'form_title_group_edit';
    case FORM_GROUP_DEL_TITLE = 'form_title_group_del';
    case FORM_VALUE_ADD_TITLE = 'form_title_value_add';
    case FORM_VALUE_EDIT_TITLE = 'form_title_value_edit';
    case FORM_VALUE_DEL_TITLE = 'form_title_value_del';
    case FORM_FORMULA_ADD_TITLE = 'form_title_formula_add';
    case FORM_FORMULA_EDIT_TITLE = 'form_title_formula_edit';
    case FORM_FORMULA_DEL_TITLE = 'form_title_formula_del';
    case FORM_RESULT_ADD_TITLE = 'form_title_result_add';
    case FORM_RESULT_EDIT_TITLE = 'form_title_result_edit';
    case FORM_RESULT_DEL_TITLE = 'form_title_result_del';
    case FORM_VIEW_ADD_TITLE = 'form_title_view_add';
    case FORM_VIEW_EDIT_TITLE = 'form_title_view_edit';
    case FORM_VIEW_DEL_TITLE = 'form_title_view_del';
    case FORM_COMPONENT_ADD_TITLE = 'form_title_component_add';
    case FORM_COMPONENT_EDIT_TITLE = 'form_title_component_edit';
    case FORM_COMPONENT_DEL_TITLE = 'form_title_component_del';
    case FORM_FIELD_NAME = 'form_field_name';
    case FORM_FIELD_DESCRIPTION = 'form_field_description';
    case FORM_FIELD_PLURAL = 'form_field_plural';
    case FORM_FIELD_FORMULA_EXPRESSION = 'form_field_formula_expression';
    case FORM_FIELD_FORMULA_ALL_VARS = 'form_field_formula_all_vars';
    case FORM_TRIPLE_PHRASE_FROM = 'form_triple_phrase_from';
    case FORM_TRIPLE_PHRASE_TO = 'form_triple_phrase_to';
    case FORM_TRIPLE_VERB = 'form_triple_verb';
    case FORM_PHRASE_TYPE_FROM = 'form_phrase_type_from';
    case FORM_PHRASE_TYPE_TO = 'form_phrase_type_to';
    case FORM_SELECT_PHRASE_TYPE = 'form_select_phrase_type';
    case FORM_SELECT_SOURCE_TYPE = 'form_select_source_type';
    case FORM_SELECT_REF_TYPE = 'form_select_ref_type';
    case FORM_SELECT_FORMULA_TYPE = 'form_select_formula_type';
    case FORM_SELECT_VIEW_TYPE = 'form_select_view_type';
    case FORM_SELECT_COMPONENT_TYPE = 'form_select_component_type';
    case SELECT_VIEW = 'select_view';
    case FORM_SELECT_SHARE = 'form_select_share';
    case FORM_SELECT_PROTECTION = 'form_select_protection';
    case FORM_BUTTON_CANCEL = 'form_button_cancel';
    case FORM_BUTTON_SAVE = 'form_button_save';
    case FORM_BUTTON_DEL = 'form_button_del';
    case FORM_WORD_FLD_NAME = 'form_word_fld_name';
    case UNDO = 'undo';
    case FIND = 'find';
    case REMOVE_FILTER = 'remove filter';
    case UNDO_ADD = 'undo_add';
    case UNDO_EDIT = 'undo_edit';
    case UNDO_DEL = 'undo_del';

    // IP filter
    case IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    case IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    case IP_BLOCK_SOLUTION = 'ip_block_solution';

    // language elements to create a text
    case FOR = ' for '; // e.g. to indicate which phrases a value is assigned to
    case OF = ' of ';   // e.g. to indicate which word would be deleted


    /**
     * @return string with the text for the user in the default language
     */
    public function text(string $lan = ''): string
    {
        global $mtr;
        if ($lan == language_codes::SYS) {
            if ($mtr->has($this)) {
                return $mtr->txt($this);
            } else {
                return $this->value;
            }
        } else {
            return $mtr->txt($this);
        }

    }

    public static function get(string $name): messages
    {
        foreach (self::cases() as $msg_id) {
            if ($name === $msg_id->value) {
                return $msg_id;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class );
    }
}