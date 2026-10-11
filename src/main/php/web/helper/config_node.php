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
    // the value of a setting as text, '' for a group
    public string $value = '';
    // the unit of the value e.g. "sec", '' if the value has none
    public string $unit = '';
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
     * @param array $json the node of the api message with name, value, unit, tooltip and children
     * @return void
     */
    function api_mapper(array $json): void
    {
        $this->name = (string)($json[json_fields::NAME] ?? '');
        $this->value = (string)($json[json_fields::TEXT_VALUE] ?? '');
        $this->unit = (string)($json[json_fields::CONFIG_UNIT] ?? '');
        $this->tooltip = (string)($json[json_fields::DESCRIPTION] ?? '');
        $this->children = [];
        foreach ($json[json_fields::CONFIG_CHILDREN] ?? [] as $child_json) {
            $this->children[] = new config_node($child_json);
        }
    }


    /*
     * info
     */

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
