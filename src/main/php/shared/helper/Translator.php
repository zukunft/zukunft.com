<?php

/*

    shared/helper/Translator.php - translates a message for the user into the user language
    ----------------------------

    $mtr is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\shared\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\files AS files_shared;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use ValueError;

class Translator
{

    // structure elements of the translation yaml
    const string MESSAGES = "messages";
    const string TEXT = "text";

    // the message id of a database field, table or action is its code id with the matching prefix
    // e.g. the field 'numeric_value' (change_fields.csv) uses 'system_db_field_numeric_value'
    const string DB_FIELD_PREFIX = "system_db_field_";
    const string DB_TABLE_PREFIX = "system_db_table_";
    const string DB_ACTION_PREFIX = "system_db_action_";

    private array $msg_file = [];
    private string $lan = '';

    function __construct(string $lan = language_codes::SYS)
    {
        $this->msg_file = $this->read($lan);
        $this->lan = $lan;
    }

    /**
     * create a text message for the user by default in the user language
     *
     * @param msg_id $msg_id the id const of the message that should be shown
     * @param string $lan the code id of the target language
     * @return string the message text in the user-specific language that should be shown to the user
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

    /**
     * translate a database field name into the user language
     * the message id of a field is its code id from change_fields.csv prefixed with DB_FIELD_PREFIX,
     * e.g. the field 'numeric_value' is translated via the message id 'system_db_field_numeric_value'
     *
     * @param string $db_field_code_id the database field code id as defined in change_fields.csv
     * @param string $lan the code id of the target language
     * @return string the translated database field name in the user-specific language
     */
    function text_db_field(string $db_field_code_id, string $lan = ''): string
    {
        return $this->txt($this->get(self::DB_FIELD_PREFIX . $db_field_code_id), $lan);
    }

    /**
     * translate a database table name into the user language
     * the message id of a table is its code id from change_tables.csv prefixed with DB_TABLE_PREFIX,
     * e.g. the table 'values' is translated via the message id 'system_db_table_values'
     *
     * @param string $db_table_code_id the database table code id as defined in change_tables.csv
     * @param string $lan the code id of the target language
     * @return string the translated database table name in the user-specific language
     */
    function text_db_table(string $db_table_code_id, string $lan = ''): string
    {
        return $this->txt($this->get(self::DB_TABLE_PREFIX . $db_table_code_id), $lan);
    }

    /**
     * translate a database change action into the user language
     * the message id of an action is its code id from change_actions.csv prefixed with DB_ACTION_PREFIX,
     * e.g. the action 'add' is translated via the message id 'system_db_action_add'
     *
     * @param string $db_action_code_id the database action code id as defined in change_actions.csv
     * @param string $lan the code id of the target language
     * @return string the translated database change action in the user-specific language
     */
    function text_db_action(string $db_action_code_id, string $lan = ''): string
    {
        return $this->txt($this->get(self::DB_ACTION_PREFIX . $db_action_code_id), $lan);
    }

    /**
     * translate a json field name into the user language
     * the json field is mapped to its database field code id via json_fields::json_field_to_db_field
     * and then reuses the database field translation, e.g. the json field 'verb' is
     * translated like the database field 'verb_id'
     *
     * @param string $json_field the json field name as defined in json_fields.php
     * @param string $lan the code id of the target language
     * @return string the translated json field name in the user-specific language
     */
    function text_json_field(string $json_field, string $lan = ''): string
    {
        return $this->text_db_field(json_fields::json_field_to_db_field($json_field), $lan);
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
            } catch (ValueError $error) {
                $msg = new user_message();
                $msg->add(msg_id::MISSING_TRANSLATION, [
                    msg_id::VAR_MESSAGE_ID => $msg_id_txt,
                    msg_id::VAR_LANGUAGE => $this->lan,
                    msg_id::VAR_ERROR_TEXT => $error->getMessage()
                ]);
                $msg_txt = $msg->var_message_text();
                log_err($msg_txt);
                return msg_id::ERROR_TEXT;
            }
        }
    }

    private function read(string $lan = ''): array
    {
        $file_path = files_shared::TRANSLATION_PATH . $lan . files::YAML;;
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
