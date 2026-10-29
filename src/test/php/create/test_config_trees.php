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

include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_HELPER . 'config_tree.php';
include_once html_paths::HELPER . 'config_tree.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED . 'json_fields.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_tree;
use Zukunft\ZukunftCom\main\php\web\helper\config_tree as config_tree_ui;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_config_trees
{

    // the keys of the test part of config.yaml, so that the tests name what they check
    // the settings of the pod, which only an admin can change
    const string SYSTEM = words::SYSTEM;
    const string SYSTEM_TIP = 'the settings of the pod, which only an admin can change';
    const string DATABASE = 'database';
    const string DATABASE_TIP = 'the database settings of the master pod';
    const string RETRY = 'retry';
    const string START_DELAY = 'start delay';
    const string START_DELAY_TIP = 'initial delay in seconds for the first connection retry';
    const int START_DELAY_VALUE = 1;
    // the database value that differs from config.yaml, so the tree shows both
    const int START_DELAY_DB = 2;
    const string MAX_DELAY = 'max delay';
    const int MAX_DELAY_VALUE = 3600;
    // the number a user saves instead, to check the confirmation of the change
    const int MAX_DELAY_NEW = 1800;
    // the id of the database value, which only the max delay, the types, the initial select setting, the font and
    // the colour mode have, so a tree with an editing user shows the input for the initial select entries and the
    // font and the colour mode selection only, because max delay is a setting of the pod for an admin and a
    // boolean is not yet editable
    const int MAX_DELAY_ID = 12;
    const int TYPES_ID = 13;
    const string SELECT_INITIAL = 'initial';
    const int SELECT_INITIAL_ID = 14;
    // the number a user saves instead, to check the confirmation of the change
    const int SELECT_INITIAL_NEW = 4;
    const string SEC = words::SEC;
    // a key with one setting that is no unit and that has no database value
    const string ROW_LIMIT = 'row limit';
    // the tooltip of the key, which also describes its only setting
    const string ROW_LIMIT_TIP = 'the number of rows loaded at once';
    const string ROW_LIMIT_SETTING = words::DEFAULT;
    const int ROW_LIMIT_VALUE = 1000;
    // a phrase of a database value, which makes it a value with more phrases than the row limit setting
    const string EXTRA = 'extra';
    const string CACHE = 'cache';
    const string PAGE = 'page';
    // true as the import saves a boolean of config.yaml in the database
    const int PAGE_DB = 1;
    // a boolean that is true in config.yaml but switched off in the database
    const string TYPES = 'types';
    const int TYPES_DB = 0;
    // a long tooltip, which is still shown completely as the mouseover text and never cut
    const string PAGE_TIP = 'true to save the html code of a page that shows only data and no form, so that '
        . 'the next request of the same page by any user is served from the cache without a backend call';
    const string USER = 'user';
    const string LANGUAGE = 'language';
    const string LANGUAGE_VALUE = 'en';
    const string LANGUAGE_TIP = 'the language of the user frontend';
    // the top level keys of the three columns and a long list that is shown at once
    const string SELECT = words::SELECT;
    const string SELECT_TIP = 'the default number of entries of a selection list';
    const array SELECT_ENTRIES = [self::SELECT_INITIAL => 5, 'suggested' => 3, 'more' => 20, 'columns' => 10, 'max' => 2000];
    const string TEST = words::TEST;
    const string TIMEOUT = 'timeout';
    const int TIMEOUT_VALUE = 30;
    const string POD = words::POD;
    const string POD_NAME = 'name';
    const string POD_NAME_VALUE = 'zukunft.com';
    const string SCRIPT_KEY = '<script>alert(1)</script>';
    const int SCRIPT_KEY_VALUE = 5;
    // the start of the escaped script key, which a browser shows as text
    const string SCRIPT_KEY_ESCAPED = '&lt;script&gt;';
    // two pinned views of the start page like in config.yaml: the url of pv and the view name of cbam
    // have a database value, the empty view name of pv has none, because the import does not save an
    // empty text, so its first save adds the value with the phrases of its yaml path
    const string STARTUP = 'startup';
    const string PV = 'pv';
    const string CBAM = 'cbam';
    const string URL = 'url';
    const string URL_VALUE = '/http/view.php?m=4&id=1954';
    const string VIEW_NAME = 'view name';
    const string VIEW_NAME_VALUE = 'Calculator';
    // the font of the user, whose word has the font code id, so the tree offers the font selection
    const string FONT = words::FONT;
    const string FONT_VALUE = 'serif';
    const int FONT_ID = 15;
    // the dark or light mode of the user, whose triple has the colour mode code id, so the tree offers
    // the selection of the light or the dark colours
    const string COLOUR_MODE = triples::COLOUR_MODE;
    const int COLOUR_MODE_VALUE = def::MODE_DARK;
    const int COLOUR_MODE_ID = 16;
    // the code ids of the words and triples like in the database (see base_phrases.json)
    const array CODE_IDS = [
        self::FONT => words::FONT_CODE_ID,
        self::COLOUR_MODE => triples::COLOUR_MODE_CODE_ID,
    ];


    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;
    // the id of each phrase by name (see phrase_id)
    private array $phr_ids = [];

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
            self::SYSTEM => [
                words::TOOLTIP_COMMENT => self::SYSTEM_TIP,
                self::DATABASE => [
                    words::TOOLTIP_COMMENT => self::DATABASE_TIP,
                    self::RETRY => [
                        self::START_DELAY => [
                            self::SEC => self::START_DELAY_VALUE,
                            words::TOOLTIP_COMMENT => self::START_DELAY_TIP,
                        ],
                        self::MAX_DELAY => [self::SEC => self::MAX_DELAY_VALUE],
                    ],
                    self::ROW_LIMIT => [
                        self::ROW_LIMIT_SETTING => self::ROW_LIMIT_VALUE,
                        words::TOOLTIP_COMMENT => self::ROW_LIMIT_TIP,
                    ],
                    self::CACHE => [
                        self::PAGE => [
                            words::SYS_CONF_VALUE => true,
                            words::TOOLTIP_COMMENT => self::PAGE_TIP,
                        ],
                        self::TYPES => [words::SYS_CONF_VALUE => true],
                    ],
                ],
                self::TEST => [
                    self::TIMEOUT => [self::SEC => self::TIMEOUT_VALUE],
                ],
                self::POD => [
                    self::POD_NAME => self::POD_NAME_VALUE,
                ],
                self::STARTUP => [
                    self::PV => [self::URL => self::URL_VALUE, self::VIEW_NAME => ''],
                    self::CBAM => [self::VIEW_NAME => self::VIEW_NAME_VALUE],
                ],
            ],
            self::USER => [
                self::LANGUAGE => [
                    words::SYS_CONF_VALUE => self::LANGUAGE_VALUE,
                    words::TOOLTIP_COMMENT => self::LANGUAGE_TIP,
                ],
                self::SCRIPT_KEY => self::SCRIPT_KEY_VALUE,
                self::FONT => [words::SYS_CONF_VALUE => self::FONT_VALUE],
                self::COLOUR_MODE => [words::SYS_CONF_VALUE => def::MODE_LIGHT],
            ],
            self::SELECT => array_merge([words::TOOLTIP_COMMENT => self::SELECT_TIP], self::SELECT_ENTRIES),
        ];
    }

    /**
     * @return config_numbers the database values of the test part of config.yaml as the cached json
     *                        rows of the api part "all", which are the same as in config.yaml like on a
     *                        fresh pod except the start delay and the types cache that differ and the row
     *                        limit setting that has only a value with one more phrase
     */
    function cfg(): config_numbers
    {
        $rows = [
            $this->cfg_row([self::SYSTEM, self::DATABASE, self::RETRY, self::START_DELAY, self::SEC], self::START_DELAY_DB),
            $this->cfg_row([self::SYSTEM, self::DATABASE, self::RETRY, self::MAX_DELAY, self::SEC], self::MAX_DELAY_VALUE, self::MAX_DELAY_ID),
            $this->cfg_row([self::SYSTEM, self::DATABASE, self::ROW_LIMIT, self::ROW_LIMIT_SETTING, self::EXTRA], self::ROW_LIMIT_VALUE),
            $this->cfg_row([self::SYSTEM, self::DATABASE, self::CACHE, self::PAGE], self::PAGE_DB),
            $this->cfg_row([self::SYSTEM, self::DATABASE, self::CACHE, self::TYPES], self::TYPES_DB, self::TYPES_ID),
            [json_fields::PHRASES => $this->phrase_rows([self::USER, self::LANGUAGE]),
                json_fields::TEXT_VALUE => self::LANGUAGE_VALUE],
            $this->cfg_row([self::USER, self::SCRIPT_KEY], self::SCRIPT_KEY_VALUE),
            [json_fields::PHRASES => $this->phrase_rows([self::USER, self::FONT]),
                json_fields::TEXT_VALUE => self::FONT_VALUE, json_fields::ID => self::FONT_ID],
            $this->cfg_row([self::USER, self::COLOUR_MODE], self::COLOUR_MODE_VALUE, self::COLOUR_MODE_ID),
            $this->cfg_row([self::SYSTEM, self::TEST, self::TIMEOUT, self::SEC], self::TIMEOUT_VALUE),
            [json_fields::PHRASES => $this->phrase_rows([self::SYSTEM, self::POD, self::POD_NAME]),
                json_fields::TEXT_VALUE => self::POD_NAME_VALUE],
            [json_fields::PHRASES => $this->phrase_rows([self::SYSTEM, self::STARTUP, self::PV, self::URL]),
                json_fields::TEXT_VALUE => self::URL_VALUE],
            [json_fields::PHRASES => $this->phrase_rows([self::SYSTEM, self::STARTUP, self::CBAM, self::VIEW_NAME]),
                json_fields::TEXT_VALUE => self::VIEW_NAME_VALUE],
        ];
        foreach (self::SELECT_ENTRIES as $key => $number) {
            $id = ($key == self::SELECT_INITIAL) ? self::SELECT_INITIAL_ID : 0;
            $rows[] = $this->cfg_row([self::SELECT, $key], $number, $id);
        }
        $cfg = new config_numbers($this->env->usr1);
        $cfg->set_cache_json($rows);
        return $cfg;
    }

    /**
     * @return config_tree_ui the frontend tree of the test part of config.yaml as the backend sends it
     */
    function tree_ui(): config_tree_ui
    {
        $cft = new config_tree();
        return new config_tree_ui($cft->nodes($this->yaml(), $this->cfg()));
    }

    /**
     * @param array $keys the yaml keys of a setting below "system configuration"
     * @param int $number the database value of the setting
     * @param int $id the id of the value, 0 for a row without one
     * @return array the cached json row of the number value of the setting
     */
    private function cfg_row(array $keys, int $number, int $id = 0): array
    {
        $row = [json_fields::PHRASES => $this->phrase_rows($keys), json_fields::NUMBER => $number];
        if ($id != 0) {
            $row[json_fields::ID] = $id;
        }
        return $row;
    }

    /**
     * @param array $keys the yaml keys of a setting below "system configuration"
     * @return array the json phrases of the value, which are the keys of the whole yaml path, each with
     *               the same id in every row like in the database
     */
    private function phrase_rows(array $keys): array
    {
        $names = array_merge(config_tree::ROOT_KEYS, $keys);
        return array_map(fn(string $name) => $this->phrase_row($name), $names);
    }

    /**
     * @param string $name a yaml key of the test part of config.yaml
     * @return array the json phrase with the id, the name and the code id of a word that has one
     */
    private function phrase_row(string $name): array
    {
        $row = [json_fields::ID => $this->phrase_id($name), json_fields::NAME => $name];
        if (array_key_exists($name, self::CODE_IDS)) {
            $row[json_fields::CODE_ID] = self::CODE_IDS[$name];
        }
        return $row;
    }

    /**
     * @param string $name a yaml key of the test part of config.yaml
     * @return int the id of the word with that name, numbered in the order of first use
     */
    function phrase_id(string $name): int
    {
        $this->phr_ids[$name] ??= count($this->phr_ids) + 1;
        return $this->phr_ids[$name];
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
