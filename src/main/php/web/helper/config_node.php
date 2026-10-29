<?php

/*

    web/helper/config_node.php - one key of the read only tree of the system configuration
    --------------------------

    a key is either a setting with a value and an optional unit e.g. "start delay: 1 sec"
    or a group of keys below it e.g. "database"

    $cfn is the suggested var name


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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\json_fields;

class config_node
{

    /*
     * object vars
     */

    public string $name = '';
    // the current database value of a setting as text, '' for a group or a setting without a database value
    public string $value = '';
    // the id of the database value, which a user overwrite names, 0 if there is no database value
    public int|string $id = 0;
    // the value of config.yaml, '' if the database value is the same
    public string $file_value = '';
    // the unit of the value e.g. "sec", '' if the value has none
    public string $unit = '';
    // true for a text setting, which is changed with a text input
    public bool $is_text = false;
    // the code id of the word of the key e.g. words::FONT_CODE_ID for the setting with the font selection
    public string $code_id = '';
    // the phrase ids of a text setting without a database value, with which its first save adds the value
    public array $phr_ids = [];
    // the tooltip-comment of the key in config.yaml
    public string $tooltip = '';
    // the keys below this key in the order of config.yaml
    public array $children = [];


    /*
     * construct and map
     */

    /**
     * @param array $json the node of the api message (see cfg/helper/config_tree::nodes)
     */
    function __construct(array $json = [])
    {
        $this->api_mapper($json);
    }

    /**
     * @param array $json the node of the api message with name, value, value id, config.yaml value, unit,
     *                    the text flag, the phrase ids of a new text, the code id of the key word, tooltip and children
     * @return void
     */
    function api_mapper(array $json): void
    {
        $this->name = (string)($json[json_fields::NAME] ?? '');
        $this->value = (string)($json[json_fields::TEXT_VALUE] ?? '');
        $id = $json[json_fields::ID] ?? 0;
        // a malformed id names no value to overwrite, so like a malformed child it never stops the page
        $this->id = (is_int($id) or is_string($id)) ? $id : 0;
        $this->file_value = (string)($json[json_fields::CONFIG_FILE_VALUE] ?? '');
        $this->unit = (string)($json[json_fields::CONFIG_UNIT] ?? '');
        $this->is_text = (($json[json_fields::CONFIG_IS_TEXT] ?? false) === true);
        $this->code_id = (string)($json[json_fields::CODE_ID] ?? '');
        $phr_ids = (array)($json[json_fields::CONFIG_PHRASE_IDS] ?? []);
        // only the ids, so that a malformed entry never ends in the url of the save
        $int_ids = array_filter($phr_ids, 'is_int');
        $this->phr_ids = array_values($int_ids);
        $this->tooltip = (string)($json[json_fields::DESCRIPTION] ?? '');
        $this->children = [];
        foreach ($json[json_fields::CONFIG_CHILDREN] ?? [] as $child_json) {
            // skipped like a malformed top level node (see config_tree::api_mapper), so a broken api message never stops the page
            if (is_array($child_json)) {
                $this->children[] = new config_node($child_json);
            }
        }
    }


    /*
     * info
     */

    /**
     * @return bool true for a text setting without a database value, whose first save adds the value
     */
    function is_new(): bool
    {
        return $this->phr_ids != [];
    }

    /**
     * @return bool true if no key is below this key, so it is shown as a row with its value
     */
    function is_leaf(): bool
    {
        return $this->children == [];
    }

    /**
     * @param string $pattern the search of the user e.g. "cache"
     * @return bool true if the name or the tooltip contains the search, ignoring the case
     */
    function matches(string $pattern): bool
    {
        $in_name = mb_stripos($this->name, $pattern) !== false;
        $in_tip = mb_stripos($this->tooltip, $pattern) !== false;
        return ($pattern != '' and ($in_name or $in_tip));
    }

}
