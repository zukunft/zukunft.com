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

use cfg\const\files;
use shared\enum\language_codes;
use shared\enum\messages as msg_id;

class Translator
{

    // structure elements of the translation yaml
    const MESSAGES = "messages";
    const TEXT = "text";

    private array $msg_file = [];
    private string $lan = '';

    function __construct(string $lan)
    {
        $this->msg_file = $this->read($lan);
        $this->lan = $lan;
    }

    /**
     * create a text message for the user by default in the user language
     *
     * @param msg_id $msg_id the id const of the message that should be shown
     * @param string $lan the code id of the target language
     * @return string the message text in the user specific language that should be shown to the user
     */
    function txt(msg_id $msg_id, string $lan = ''): string
    {
        if ($lan == $this->lan or $lan == '') {
            $msg_file = $this->msg_file;
        } else {
            $msg_file = $this->read($lan);
        }
        if (array_key_exists($msg_id->value, $msg_file)) {
            $msg_text = $msg_file[$msg_id->value];
            if (is_array($msg_text)) {
                if (array_key_exists(self::TEXT, $msg_text)) {
                    $msg_text = $msg_text[self::TEXT];
                } else {
                    $msg_text = self::TEXT . ' element missing for ' . $msg_id->value;
                    log_warning($msg_text);
                }
            }
        } else {
            if ($lan == language_codes::SYS or $lan == '') {
                $msg_text = $msg_id->value;
            } else {
                $msg_text = 'translation missing for ' . $msg_id->value;
                log_warning($msg_text);
            }
        }
        return $msg_text;
    }

    function has(msg_id $msg_id): bool
    {
        return array_key_exists($msg_id->value, $this->msg_file);
    }

    function get(?string $msg_id_txt): msg_id
    {
        if ($msg_id_txt == null) {
            return msg_id::NONE;
        } else {
            try {
                return msg_id::get($msg_id_txt);
            } catch (\ValueError $error) {
                log_err($error);
                return msg_id::ERROR;
            }
        }
    }

    private function read(string $lan = ''): array
    {
        $file_path = files::TRANSLATION_PATH . $lan . files::YAML;;
        $result = yaml_parse_file($file_path);
        if ($result === false) {
            log_warning('translation file ' . $file_path . ' missing');
            return [];
        } else {
            if (array_key_exists(self::MESSAGES, $result)) {
                return $result[self::MESSAGES];
            } else {
                log_warning(self::MESSAGES . ' key missing in translation file ' . $file_path);
                return [];
            }
        }
    }

}
