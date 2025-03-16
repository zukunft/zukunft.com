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

use const\files;
use shared\enum\messages as shared_msg;

class Translator
{

    /**
     * @param string $msg_id the id const of the message that should be shown
     * @param string $lan the code id of the target language
     * @return string the message text in the user specific language that should be shown to the user
     */
    function txt(string $msg_id, string $lan = ''): string
    {
        if ($lan == '') {
            global $lan;
        }
        $lan_file = $lan . files::YAML;
        $msg_file = yaml_parse_file(files::TRANSLATION_PATH . $lan_file);
        $msg_text = match ($msg_id) {
            shared_msg::IP_BLOCK_PRE_ADDR => 'Your IP ',
            shared_msg::IP_BLOCK_POST_ADDR => ' is blocked at the moment because ',
            shared_msg::IP_BLOCK_SOLUTION => '. If you think, this should not be the case, ' .
                'please request the unblocking with an email to admin@zukunft.com.',
            default => $msg_id . ' (translation missing)',
        };
        if ($msg_text == $msg_id . ' (translation missing)') {
            log_warning('translation missing for ' . $msg_id);
        }
        return $msg_text;
    }

}
