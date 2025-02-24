<?php

/*

    shared/helper/Translator.php - translates a message for the user into the user language
    ----------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\helper;

include_once SHARED_ENUM_PATH . 'messages.php';

use shared\enum\messages as shared_msg;

class Translator
{

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
            shared_msg::IP_BLOCK_PRE_ADDR => 'Your IP ',
            shared_msg::IP_BLOCK_POST_ADDR => ' is blocked at the moment because ',
            shared_msg::IP_BLOCK_SOLUTION => '. If you think, this should not be the case, ' .
                'please request the unblocking with an email to admin@zukunft.com.',
            default => $message_id . ' (translation missing)',
        };
        if ($msg_text == $message_id . ' (translation missing)') {
            log_warning('translation missing for ' . $message_id);
        }
        return $msg_text;
    }

}
