<?php

/*

    test/php/create/test_config_trees.php - create the parts of config.yaml for the config tree unit tests
    -------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_HELPER . 'config_tree.php';
include_once html_paths::HELPER . 'config_tree.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED . 'json_fields.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\config_tree;
use Zukunft\ZukunftCom\main\php\web\helper\config_tree as config_tree_ui;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_config_trees
{

    // the keys of the test part of config.yaml, so that the tests name what they check
    const string DATABASE = 'database';
    const string DATABASE_TIP = 'the database settings of the master pod';
    const string RETRY = 'retry';
    const string START_DELAY = 'start delay';
    const string START_DELAY_TIP = 'initial delay in seconds for the first connection retry';
    const string MAX_DELAY = 'max delay';
    const string SEC = words::SEC;
    // a key with one setting that is no unit
    const string ROW_LIMIT = 'row limit';
    const string ROW_LIMIT_SETTING = words::DEFAULT;
    const string CACHE = 'cache';
    const string PAGE = 'page';
    // longer than config_tree_ui::TOOLTIP_MAX, so that the full text is shown by a button
    const string PAGE_TIP = 'true to save the html code of a page that shows only data and no form, so that '
        . 'the next request of the same page by any user is served from the cache without a backend call';
    const string USER = 'user';
    const string LANGUAGE = 'language';
    const string LANGUAGE_VALUE = 'en';
    const string LANGUAGE_TIP = 'the language of the user frontend';
    // the top level keys of the three columns and a long list that is shown at once
    const string SELECT = words::SELECT;
    const string SELECT_TIP = 'the default number of entries of a selection list';
    const array SELECT_ENTRIES = ['initial' => 5, 'suggested' => 3, 'more' => 20, 'columns' => 10, 'max' => 2000];
    const string TEST = words::TEST;
    const string TIMEOUT = 'timeout';
    const string POD = words::POD;
    const string POD_NAME = 'name';
    const string POD_NAME_VALUE = 'zukunft.com';
    const string SCRIPT_KEY = '<script>alert(1)</script>';
    // the start of the escaped script key, which a browser shows as text
    const string SCRIPT_KEY_ESCAPED = '&lt;script&gt;';


    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * yaml
     */

    /**
     * @return array a part of config.yaml below "system configuration" with each leaf style:
     *               a plain value, a sys-conf-value, a unit key, a long tooltip and a key with html,
     *               and a top level key for each column incl. a long list
     */
    function yaml(): array
    {
        return [
            self::DATABASE => [
                words::TOOLTIP_COMMENT => self::DATABASE_TIP,
                self::RETRY => [
                    self::START_DELAY => [
                        self::SEC => 1,
                        words::TOOLTIP_COMMENT => self::START_DELAY_TIP,
                    ],
                    self::MAX_DELAY => [self::SEC => 3600],
                ],
                self::ROW_LIMIT => [self::ROW_LIMIT_SETTING => 1000],
                self::CACHE => [
                    self::PAGE => [
                        words::SYS_CONF_VALUE => true,
                        words::TOOLTIP_COMMENT => self::PAGE_TIP,
                    ],
                ],
            ],
            self::USER => [
                self::LANGUAGE => [
                    words::SYS_CONF_VALUE => self::LANGUAGE_VALUE,
                    words::TOOLTIP_COMMENT => self::LANGUAGE_TIP,
                ],
                self::SCRIPT_KEY => 5,
            ],
            self::SELECT => array_merge([words::TOOLTIP_COMMENT => self::SELECT_TIP], self::SELECT_ENTRIES),
            self::TEST => [
                self::TIMEOUT => [self::SEC => 30],
            ],
            self::POD => [
                self::POD_NAME => self::POD_NAME_VALUE,
            ],
        ];
    }

    /**
     * @return config_tree_ui the frontend tree of the test part of config.yaml as the backend sends it
     */
    function tree_ui(): config_tree_ui
    {
        $cft = new config_tree();
        return new config_tree_ui($cft->nodes($this->yaml()));
    }

    /**
     * @param array $nodes the json nodes of the backend tree
     * @param string $name the name of the key to find at any level
     * @return array the json node of the first key with that name or an empty array
     */
    function node_by_name(array $nodes, string $name): array
    {
        $result = [];
        foreach ($nodes as $node) {
            if ($result == [] and $node[json_fields::NAME] == $name) {
                $result = $node;
            }
            if ($result == []) {
                $result = $this->node_by_name($node[json_fields::CONFIG_CHILDREN] ?? [], $name);
            }
        }
        return $result;
    }

}
