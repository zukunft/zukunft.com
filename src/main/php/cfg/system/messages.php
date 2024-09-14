<?php

/*

    /cfg/system/messages.php - the language specific backend messages
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

class messages
{

    // backend messages in the default language
    const RESERVED_IS = 'is a reserved';
    const RESERVED_NAME = 'name for system testing. Please use another name';
    const MISSING = ' message missing';
    const MISSING_TRANSLATION = ' translation missing';

    /**
     * create a text message for the user by default in the user language
     * TODO read translation from yaml and use it
     *
     * @param string $message_id the id const of the message that should be shown
     * @param int $language_id the id const of the message that should be shown
     * @return string the message text in the user specific language that should be shown to the user
     */
    function txt(string $message_id, int $language_id = language::DEFAULT_ID): string
    {
        // to be replaced with a get_cfg function
        $user_language = 'en';
        // $msg_file = yaml_parse_file('/resources/translation/en.yaml');
        $msg_text = match ($message_id) {
            self::RESERVED_IS => 'is a reserved',
            self::RESERVED_NAME => 'name for system testing. Please use another name',
            default => $message_id . self::MISSING,
        };
        if ($msg_text == $message_id . self::MISSING) {
            log_warning($msg_text);
        }
        return $msg_text;
    }

}
