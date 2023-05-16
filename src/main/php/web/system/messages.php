<?php

/*

    /web/system/messages.php - the language specific UI messages
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

namespace html;

class msg
{

    // text to be shon in buttons
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
    const PLEASE_SELECT = 'please_select';
    const IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    const IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    const IP_BLOCK_SOLUTION = 'ip_block_solution';
    const FORM_WORD_ADD_TITLE = 'form_word_add_title';
    const FORM_WORD_FLD_NAME = 'form_word_fld_name';
    const UNDO_ADD = 'undo_add';
    const UNDO_EDIT = 'undo_edit';
    const UNDO_DEL = 'undo_del';

    // language elements to create a text
    CONST FOR = 'for'; // e.g. to indicate which phrases a value is assigned to
    CONST OF = 'of';   // e.g. to indicate which word would be deleted

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
            self::WORD_DEL, self::TRIPLE_DEL => 'Delete word',
            self::VERB_ADD => 'add new verb',
            self::VERB_EDIT => 'change verb',
            self::VERB_DEL => 'delete verb',
            self::VALUE_ADD => 'add new value',
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
            self::PLEASE_SELECT => 'please select ...',
            self::IP_BLOCK_PRE_ADDR => 'Your IP ',
            self::IP_BLOCK_POST_ADDR => ' is blocked at the moment because ',
            self::IP_BLOCK_SOLUTION => '. If you think, this should not be the , ' .
                'please request the unblocking with an email to admin@zukunft.com.',
            self::FORM_WORD_ADD_TITLE => 'Add a new word',
            self::FORM_WORD_FLD_NAME => 'Word name',
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
