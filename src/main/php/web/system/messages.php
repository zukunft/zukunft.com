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
    const PLEASE_SELECT = 'please_select';
    const IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    const IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    const IP_BLOCK_SOLUTION = 'ip_block_solution';

    public function txt(string $message_id): string
    {
        $msg_text = '';
        // to be replaced with a get_cfg function
        $user_language = 'en';
        // $msg_file = yaml_parse_file('/resources/translation/en.yaml');
        switch ($message_id) {
            case self::WORD_RENAME:
                $msg_text = 'rename word';
                break;
            case self::WORD_DELETE:
                $msg_text = 'Delete word';
                break;
            case self::WORD_UNLINK:
                $msg_text = 'Unlink word';
                break;
            case self::PLEASE_SELECT:
                $msg_text = 'please select ...';
                break;
            case self::IP_BLOCK_PRE_ADDR:
                $msg_text = 'Your IP ';
                break;
            case self::IP_BLOCK_POST_ADDR:
                $msg_text = ' is blocked at the moment because ';
                break;
            case self::IP_BLOCK_SOLUTION:
                $msg_text = '. If you think, this should not be the case, ' .
                    'please request the unblocking with an email to admin@zukunft.com.';
                break;
            default:
                $msg_text = $message_id . ' (translation missing)';
                log_warning('translation missing for '.  $message_id);
        }
        return $msg_text;
    }

}
