<?php

/*

    web\system\messages.php - the language specific UI messages
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

    const WORD_RENAME = 'rename_word';
    const WORD_DELETE = 'delete_word';
    const WORD_UNLINK = 'unlink_word';

    public function txt(string $message_id): string
    {
        $msg_text = '';
        // to be replaced with a get_cfg function
        $user_language = 'en';
        // $msg_file = yaml_parse_file('/resources/translation/en.yaml');
        if ($message_id == self::WORD_RENAME) {
            $msg_text = 'rename word';
        }
        if ($message_id == self::WORD_DELETE) {
            $msg_text = 'Delete word';
        }
        if ($message_id == self::WORD_UNLINK) {
            $msg_text = 'Unlink word';
        }
        return $msg_text;
    }

}
