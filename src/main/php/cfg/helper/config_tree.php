<?php

/*

    model/helper/config_tree.php - the read only tree of the system configuration in config.yaml
    ----------------------------

    the tree keeps the key order and the tooltips of config.yaml, which the values of the
    configuration in the database lose, and adds the current database value of each setting,
    so that the frontend can show the configuration as a tree

    $cft is the suggested var name


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class config_tree
{

    // the keys of config.yaml that describe their parent key instead of being a setting
    // (see import::get_data_object_yaml_loop, which reads them the same way)
    const array META_KEYS = [
        words::TOOLTIP_COMMENT,
        words::SYS_CONF_VALUE,
        words::SYS_CONF_SOURCE,
        words::SYS_CONF_SOURCE_COM,
        words::SYS_CONF_USER,
    ];

    // the keys that config.yaml uses as the unit of the only setting of a key e.g. "start delay: {sec: 1}";
    // listed, because the same shape also names a setting e.g. "row limit: {default: 1000}", so a new unit
    // in config.yaml needs its entry here
    const array UNIT_KEYS = [
        words::SEC,
        words::MILLISECOND,
        words::MB,
        words::DAYS,
    ];

    // the keys of config.yaml above the shown tree, which are also phrases of each config value
    const array ROOT_KEYS = [
        words::THIS_SYSTEM,
        triples::SYSTEM_CONFIG,
    ];


    /*
     * api
     */

    /**
     * the tree of the system configuration below "zukunft.com > system configuration" as the
     * api message for the frontend with the structure, the order and the tooltips of config.yaml
     * and the current database value of each setting
     *
     * @param config_numbers $cfg the loaded config values of the requesting user (api part "all")
     * @param user_message $msg to report that config.yaml cannot be read or converted to json
     * @return string the json of the top level nodes or '' if config.yaml cannot be read or converted
     */
    function api_json(config_numbers $cfg, user_message $msg): string
    {
        $result = '';
        $yaml = yaml_parse_file(files::SYSTEM_CONFIG);
        if (is_array($yaml)) {
            $sys_cfg = $yaml[words::THIS_SYSTEM][triples::SYSTEM_CONFIG] ?? [];
            $json = json_encode($this->nodes($sys_cfg, $cfg));
            // e.g. a text of config.yaml that is no valid utf-8, which the user cannot fix
            if ($json === false) {
                log_err_msg('config tree cannot be converted to json: ' . json_last_error_msg(), $msg);
            } else {
                $result = $json;
            }
        } else {
            $msg->add_id(msg_id::CONFIG_NOT_LOADED);
        }
        return $result;
    }


    /*
     * convert
     */

    /**
     * the nodes of the given part of config.yaml in the order of the file, without the keys
     * that only describe the parent key e.g. its tooltip-comment
     *
     * @param array $yaml the parsed yaml below one key e.g. below "system configuration"
     * @param config_numbers $cfg the loaded config values whose phrases are the keys of the yaml path
     * @param array $path the yaml keys above the given part, which are also phrases of its values
     * @return array the json array of the nodes, each with name and optional value, unit, tooltip and children
     */
    function nodes(array $yaml, config_numbers $cfg, array $path = self::ROOT_KEYS): array
    {
        $result = [];
        foreach ($yaml as $key => $entry) {
            if (!in_array($key, self::META_KEYS, true)) {
                $result[] = $this->node((string)$key, $entry, $cfg, $path);
            }
        }
        return $result;
    }

    /**
     * one key of config.yaml as a json node
     *
     * the sys-conf-value is the value of the key, and a key whose only setting is named by a unit
     * of UNIT_KEYS shows that unit, e.g. "start delay: {sec: 1}" is "start delay: 1 sec"
     *
     * @param string $name the yaml key e.g. "start delay"
     * @param mixed $entry the yaml content of the key, a number or text for a plain setting
     * @param config_numbers $cfg the loaded config values
     * @param array $path the yaml keys above this key
     * @return array the json node of the key
     */
    private function node(string $name, mixed $entry, config_numbers $cfg, array $path): array
    {
        $result = [json_fields::NAME => $name];
        $node_path = array_merge($path, [$name]);
        if (is_array($entry)) {
            $settings = array_diff_key($entry, array_flip(self::META_KEYS));
            $unit = $this->unit_key($entry, $settings);
            if (array_key_exists(words::TOOLTIP_COMMENT, $entry)) {
                $result[json_fields::DESCRIPTION] = $this->text($entry[words::TOOLTIP_COMMENT]);
            }
            if (array_key_exists(words::SYS_CONF_VALUE, $entry)) {
                $result = $this->with_value($result, $entry[words::SYS_CONF_VALUE], $node_path, $cfg);
            }
            if ($unit != '') {
                $result = $this->with_value($result, $settings[$unit], array_merge($node_path, [$unit]), $cfg);
                $result[json_fields::CONFIG_UNIT] = $unit;
            } elseif ($settings != []) {
                $result[json_fields::CONFIG_CHILDREN] = $this->nodes($settings, $cfg, $node_path);
            }
        } else {
            $result = $this->with_value($result, $entry, $node_path, $cfg);
        }
        return $result;
    }

    /**
     * add the current database value of a setting and its config.yaml value if they differ
     *
     * @param array $node the json node of the setting
     * @param mixed $file_value the value of the setting in config.yaml e.g. true
     * @param array $names the yaml keys of the setting, which are exactly the phrases of its value
     * @param config_numbers $cfg the loaded config values
     * @return array the json node with the database value and the config.yaml value if it differs or no database value exists
     */
    private function with_value(array $node, mixed $file_value, array $names, config_numbers $cfg): array
    {
        $db_value = $cfg->get_by_names_exact($names)?->number();
        $is_same = ($db_value !== null and $this->is_same($file_value, $db_value));
        if ($is_same) {
            // the text of config.yaml, so that e.g. a boolean is shown as true and not as 1
            $node[json_fields::TEXT_VALUE] = $this->text($file_value);
        } else {
            if ($db_value !== null) {
                $node[json_fields::TEXT_VALUE] = (string)$db_value;
            }
            $node[json_fields::CONFIG_FILE_VALUE] = $this->text($file_value);
        }
        return $node;
    }

    /**
     * @param mixed $file_value the value of config.yaml, which the import saves as a text or as a number
     * @param float|string $db_value the current database value of the same setting
     * @return bool true if the database value is the config.yaml value e.g. 1 for true
     */
    private function is_same(mixed $file_value, float|string $db_value): bool
    {
        $result = false;
        if (is_string($file_value)) {
            $result = ((string)$db_value === $file_value);
        } elseif (is_numeric($db_value)) {
            $result = ((float)$db_value == (float)$file_value);
        }
        return $result;
    }

    /**
     * @param array $entry the yaml content of the key incl. the meta keys
     * @param array $settings the yaml content of the key without the meta keys
     * @return string the unit of the value of the key e.g. "sec" or '' if the only setting is no unit
     */
    private function unit_key(array $entry, array $settings): string
    {
        $result = '';
        $only_key = array_key_first($settings);
        $is_single = (count($settings) == 1 and !array_key_exists(words::SYS_CONF_VALUE, $entry));
        $is_unit = ($is_single and in_array($only_key, self::UNIT_KEYS, true));
        if ($is_unit and !is_array($settings[$only_key])) {
            $result = (string)$only_key;
        }
        return $result;
    }

    /**
     * @param mixed $entry a number, text or boolean of config.yaml
     * @return string the text shown to the user e.g. "true" for a boolean
     */
    private function text(mixed $entry): string
    {
        $result = (string)$entry;
        if (is_bool($entry)) {
            $result = var_export($entry, true);
        }
        return $result;
    }

}
