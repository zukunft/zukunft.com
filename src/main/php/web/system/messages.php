<?php

/*

    web/system/messages.php - the language specific UI messages
    -----------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\system;

class messages
{

    // text to be shown in buttons
    const SEARCH_MAIN = 'search_main';
    const WORD_ADD = 'word_add';
    const WORD_EDIT = 'word_edit';
    const WORD_DEL = 'word_del';
    const WORD_UNLINK = 'unlink_word';
    const VERB_ADD = 'verb_add';
    const VERB_EDIT = 'verb_edit';
    const VERB_DEL = 'verb_del';
    const TRIPLE_ADD = 'triple_add';
    const TRIPLE_EDIT = 'triple_edit';
    const TRIPLE_DEL = 'triple_del';
    const VALUE_ADD = 'value_add';
    const VALUE_ADD_SIMILAR = 'value_add_similar';
    const VALUE_EDIT = 'value_edit';
    const VALUE_DEL = 'value_del';
    const FORMULA_ADD = 'formula_add';
    const FORMULA_EDIT = 'formula_edit';
    const FORMULA_DEL = 'formula_del';
    const FORMULA_LINK = 'formula_link';
    const FORMULA_UNLINK = 'formula_unlink';
    const VIEW_ADD = 'view_add';
    const VIEW_EDIT = 'view_edit';
    const VIEW_DEL = 'view_del';
    const COMPONENT_ADD = 'component_add';
    const COMPONENT_EDIT = 'component_edit';
    const COMPONENT_DEL = 'component_del';
    const COMPONENT_LINK = 'component_link';
    const COMPONENT_UNLINK = 'component_unlink';
    const PLEASE_SELECT = 'please_select';
    const IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    const IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    const IP_BLOCK_SOLUTION = 'ip_block_solution';
    const FORM_WORD_ADD_TITLE = 'form_title_word_add';
    const FORM_WORD_EDIT_TITLE = 'form_title_word_edit';
    const FORM_WORD_DEL_TITLE = 'form_title_word_del';
    const FORM_VERB_ADD_TITLE = 'form_title_verb_add';
    const FORM_VERB_EDIT_TITLE = 'form_title_verb_edit';
    const FORM_VERB_DEL_TITLE = 'form_title_verb_del';
    const FORM_TRIPLE_ADD_TITLE = 'form_title_triple_add';
    const FORM_TRIPLE_EDIT_TITLE = 'form_title_triple_edit';
    const FORM_TRIPLE_DEL_TITLE = 'form_title_triple_del';
    const FORM_SOURCE_ADD_TITLE = 'form_title_source_add';
    const FORM_SOURCE_EDIT_TITLE = 'form_title_source_edit';
    const FORM_SOURCE_DEL_TITLE = 'form_title_source_del';
    const FORM_REF_ADD_TITLE = 'form_title_ref_add';
    const FORM_REF_EDIT_TITLE = 'form_title_ref_edit';
    const FORM_REF_DEL_TITLE = 'form_title_ref_del';
    const FORM_FORMULA_ADD_TITLE = 'form_title_formula_add';
    const FORM_FORMULA_EDIT_TITLE = 'form_title_formula_edit';
    const FORM_FORMULA_DEL_TITLE = 'form_title_formula_del';
    const FORM_VIEW_ADD_TITLE = 'form_title_view_add';
    const FORM_VIEW_EDIT_TITLE = 'form_title_view_edit';
    const FORM_VIEW_DEL_TITLE = 'form_title_view_del';
    const FORM_COMPONENT_ADD_TITLE = 'form_component_add_title';
    const FORM_COMPONENT_EDIT_TITLE = 'form_component_view_edit';
    const FORM_COMPONENT_DEL_TITLE = 'form_component_view_del';
    const FORM_FIELD_NAME = 'form_field_name';
    const FORM_FIELD_DESCRIPTION = 'form_field_description';
    const FORM_SELECT_SHARE = 'form_select_share';
    const FORM_SELECT_PROTECTION = 'form_select_protection';
    const FORM_BUTTON_CANCEL = 'form_button_cancel';
    const FORM_BUTTON_SAVE = 'form_button_save';
    const FORM_WORD_FLD_NAME = 'form_word_fld_name';
    const UNDO = 'undo';
    const UNDO_ADD = 'undo_add';
    const UNDO_EDIT = 'undo_edit';
    const UNDO_DEL = 'undo_del';

    // other text to be shown to users

    // language elements to create a text
    CONST FOR = ' for '; // e.g. to indicate which phrases a value is assigned to
    CONST OF = ' of ';   // e.g. to indicate which word would be deleted

    /**
     * @param string $message_id the id const of the message that should be shown
     * @return string the message text in the user specific language that should be shown to the user
     */
    function txt(string $message_id): string
    {
        // to be replaced with a get_cfg function
        $user_language = 'en';
        // $msg_file = yaml_parse_file('/resources/translation/en.yaml');
        $msg_text = match ($message_id) {
            self::SEARCH_MAIN => 'find a word or formula',
            self::WORD_ADD, self::TRIPLE_ADD => 'add new word',
            self::WORD_EDIT, self::TRIPLE_EDIT => 'rename word',
            self::WORD_DEL, self::TRIPLE_DEL => 'delete word',
            self::VERB_ADD => 'add new verb',
            self::VERB_EDIT => 'change verb',
            self::VERB_DEL => 'delete verb',
            self::VALUE_ADD => 'add new value',
            self::VALUE_ADD_SIMILAR => 'add new value similar to ',
            self::VALUE_EDIT => 'change value',
            self::VALUE_DEL => 'delete value',
            self::FORMULA_ADD => 'add new formula',
            self::FORMULA_EDIT => 'change formula',
            self::FORMULA_DEL => 'delete this formula',
            self::FORMULA_LINK => 'assign formula to ',
            self::FORMULA_UNLINK => 'unassigned formula from ',
            self::WORD_UNLINK => 'Unlink word',
            self::VIEW_ADD => 'create a new view',
            self::VIEW_EDIT => 'adjust the view',
            self::VIEW_DEL => 'delete view',
            self::COMPONENT_ADD => 'create component',
            self::COMPONENT_EDIT => 'adjust component',
            self::COMPONENT_DEL => 'delete component',
            self::COMPONENT_LINK => 'add component',
            self::COMPONENT_UNLINK => 'remove component',
            self::PLEASE_SELECT => 'please select ...',
            self::IP_BLOCK_PRE_ADDR => 'Your IP ',
            self::IP_BLOCK_POST_ADDR => ' is blocked at the moment because ',
            self::IP_BLOCK_SOLUTION => '. If you think, this should not be the case, ' .
                'please request the unblocking with an email to admin@zukunft.com.',
            self::FORM_WORD_ADD_TITLE => 'Add a new word',
            self::FORM_WORD_EDIT_TITLE => 'Change word',
            self::FORM_WORD_DEL_TITLE => 'Delete word',
            self::FORM_VERB_ADD_TITLE => 'Add a new verb',
            self::FORM_VERB_EDIT_TITLE => 'Change verb',
            self::FORM_VERB_DEL_TITLE => 'Delete verb',
            self::FORM_TRIPLE_ADD_TITLE => 'Add a new triple',
            self::FORM_TRIPLE_EDIT_TITLE => 'Change triple',
            self::FORM_TRIPLE_DEL_TITLE => 'Delete triple',
            self::FORM_SOURCE_ADD_TITLE => 'Add a new source',
            self::FORM_SOURCE_EDIT_TITLE => 'Change source',
            self::FORM_SOURCE_DEL_TITLE => 'Delete source',
            self::FORM_REF_ADD_TITLE => 'Add a new ref',
            self::FORM_REF_EDIT_TITLE => 'Change ref',
            self::FORM_REF_DEL_TITLE => 'Delete ref',
            self::FORM_FORMULA_ADD_TITLE => 'Add a new formula',
            self::FORM_FORMULA_EDIT_TITLE => 'Change formula',
            self::FORM_FORMULA_DEL_TITLE => 'Delete formula',
            self::FORM_VIEW_ADD_TITLE => 'Add a new view',
            self::FORM_VIEW_EDIT_TITLE => 'Change view',
            self::FORM_VIEW_DEL_TITLE => 'Delete view',
            self::FORM_COMPONENT_ADD_TITLE => 'Add a view element',
            self::FORM_COMPONENT_EDIT_TITLE => 'Change element',
            self::FORM_COMPONENT_DEL_TITLE => 'Delete element',
            self::FORM_WORD_FLD_NAME => 'Word name',
            self::UNDO => 'undo',
            self::UNDO_ADD => 'delete added',
            self::UNDO_EDIT => 'undo edit',
            self::UNDO_DEL => 'restore',
            self::FOR => ' for ',
            self::OF => ' of ',
            default => $message_id . ' (translation missing)',
        };
        if ($msg_text == $message_id . ' (translation missing)') {
            log_warning('translation missing for ' . $message_id);
        }
        return $msg_text;
    }

}
