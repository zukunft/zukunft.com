<?php

/*

    web/helper/config_tree.php - the read only tree of the system configuration in config.yaml
    --------------------------

    shown on the system configuration view with a search on the names and tooltips (url_var::PATTERN)
    and a scope that shows one top level branch only (url_var::CONFIG_PART), all without javascript

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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'config_node.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class config_tree
{

    /*
     * const
     */

    // between the keys of the path of a search hit e.g. "database › cache › page"
    const string PATH_SEPARATOR = ' › ';
    // between the keys of the html id of a node e.g. "cfg-database/cache"
    const string ID_SEPARATOR = '/';
    const string NODE_ID_PREFIX = 'cfg-';
    // the html id of the key names that the search suggests
    const string KEY_LIST_ID = 'config-keys';
    // the url var and the value of the scope that shows the whole tree
    const string SCOPE_ALL = api::CONFIG_ALL;
    // the top level keys of each column of the tree from left to right; a top level key that is
    // not listed goes to the last column in the order of config.yaml, but before LAST_KEY
    const array COLUMN_KEYS = [
        [words::SELECT],
        [words::USER],
        [words::TEST],
    ];
    // the top level key shown at the end of the last column
    const string LAST_KEY = words::POD;


    /*
     * object vars
     */

    // the top level keys of the system configuration in the order of config.yaml
    public array $nodes = [];


    /*
     * construct and map
     */

    /**
     * @param array $json the top level nodes of the api message (see cfg/helper/config_tree::nodes)
     */
    function __construct(array $json = [])
    {
        $this->api_mapper($json);
    }

    /**
     * @param array $json the list of the top level nodes of the api message
     * @return void
     */
    function api_mapper(array $json): void
    {
        $this->nodes = [];
        foreach ($json as $node_json) {
            if (is_array($node_json)) {
                $this->nodes[] = new config_node($node_json);
            }
        }
    }


    /*
     * load
     */

    /**
     * get the tree from the backend with the current config values of the requesting user
     *
     * @param user_message $msg to report that the backend has not sent the tree e.g. because the values cannot be loaded
     * @return bool true if the tree has at least one key
     */
    function load(user_message $msg): bool
    {
        $rest = new rest_call();
        $data = [url_var::CONFIG_PART => api::CONFIG_TREE];
        $json_body = $rest->api_get(config::class, $data, $msg);
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
        } else {
            $this->api_mapper($json_body);
        }
        return $this->nodes != [];
    }


    /*
     * select
     */

    /**
     * @param string $scope the name of a top level key e.g. "database" or "all" or '' for the whole tree
     * @return array the top level nodes of the scope, empty if no top level key has that name
     */
    function branch(string $scope): array
    {
        $result = $this->nodes;
        if ($scope != '' and $scope != self::SCOPE_ALL) {
            $result = $this->named($this->nodes, $scope);
        }
        return $result;
    }

    /**
     * the top level nodes split into the columns of COLUMN_KEYS, e.g. select, user and test with
     * the other keys and pod last; a column without a node of the given ones is empty
     *
     * @param array $nodes the top level nodes e.g. of one scope
     * @return array per column the nodes from top to bottom
     */
    function columns(array $nodes): array
    {
        $result = [];
        foreach (self::COLUMN_KEYS as $keys) {
            $col_nodes = [];
            foreach ($keys as $key) {
                $col_nodes = array_merge($col_nodes, $this->named($nodes, $key));
            }
            $result[] = $col_nodes;
        }
        $last_col = count($result) - 1;
        foreach ($nodes as $cfn) {
            if (!$this->is_placed($cfn->name)) {
                $result[$last_col][] = $cfn;
            }
        }
        $result[$last_col] = array_merge($result[$last_col], $this->named($nodes, self::LAST_KEY));
        return $result;
    }

    /**
     * the keys whose name or tooltip contains the search, each with the path of its parent keys
     *
     * @param array $nodes the nodes to search in, incl. all keys below them
     * @param string $pattern the search of the user e.g. "cache"
     * @param array $path the names of the parent keys of the given nodes
     * @return array per hit the path of the parent keys and the matching node, in the order of config.yaml
     */
    function hits(array $nodes, string $pattern, array $path = []): array
    {
        $result = [];
        foreach ($nodes as $cfn) {
            if ($cfn->matches($pattern)) {
                $result[] = [$path, $cfn];
            }
            $child_path = array_merge($path, [$cfn->name]);
            $result = array_merge($result, $this->hits($cfn->children, $pattern, $child_path));
        }
        return $result;
    }

    /**
     * @param array $nodes the nodes whose names are collected incl. all keys below them
     * @return array the names of all keys, each once and sorted, for the suggestions of the search
     */
    function key_names(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $cfn) {
            $result[] = $cfn->name;
            $result = array_merge($result, $this->key_names($cfn->children));
        }
        $result = array_values(array_unique($result));
        sort($result);
        return $result;
    }


    /*
     * html
     */

    /**
     * the search form and either the hits of the search or the tree of the scope
     *
     * @param string $pattern the search of the user, '' to show the tree
     * @param string $scope the name of the top level key to show, '' or "all" for the whole tree
     * @return string the html code of the configuration page body
     */
    function html(string $pattern, string $scope): string
    {
        global $mtr;
        $html = new html_base();
        $nodes = $this->branch($scope);
        $result = $this->search_form($pattern, $scope);
        if ($nodes == [] and $this->nodes != []) {
            $result .= $html->p($mtr->txt(msg_id::CONFIG_TREE_SCOPE_UNKNOWN));
        } elseif ($pattern != '') {
            $result .= $this->hit_list($nodes, $pattern);
        } else {
            $result .= $this->columns_html($nodes);
        }
        return $result;
    }

    /**
     * the top level keys in the columns of COLUMN_KEYS, each key as a card; an empty column is
     * left out, so that a scope with one top level key uses the full width
     *
     * @param array $nodes the top level nodes of the scope
     * @return string the html code of the columns
     */
    function columns_html(array $nodes): string
    {
        $html = new html_base();
        $cols = '';
        foreach ($this->columns($nodes) as $col_nodes) {
            $items = '';
            foreach ($col_nodes as $cfn) {
                $items .= $html->list_item($this->node_html($cfn, ''));
            }
            if ($items != '') {
                $cols .= $html->div($html->list_unsorted($items, styles::CONFIG_TREE), styles::CONFIG_COLUMN);
            }
        }
        return $html->div($cols, styles::CONFIG_COLUMNS);
    }

    /**
     * the search with the key names as suggestions and the links to show one top level key only
     *
     * @param string $pattern the search of the request to show it again
     * @param string $scope the shown top level key, which the search keeps
     * @return string the html code of the get form
     */
    function search_form(string $pattern, string $scope): string
    {
        global $mtr;
        $html = new html_base();
        $txt = $html->form_hidden(url_var::MASK, (string)views::SYSTEM_CONFIG_ID);
        if ($scope != '') {
            $txt .= $html->form_hidden(url_var::CONFIG_PART, $scope);
        }
        $names = $this->key_names($this->nodes);
        $search = $html->search_input(url_var::PATTERN, $pattern, self::KEY_LIST_ID, $names);
        $search .= $html->button_submit($mtr->txt(msg_id::FIND));
        $txt .= $html->div($search, styles::CONFIG_SEARCH);
        $txt .= $this->scope_links($pattern, $scope);
        $form = $html->form_simple(api::MAIN_SCRIPT, html_base::METHOD_GET, $txt);
        return $html->div($form, styles::CONFIG_TOOLBAR);
    }

    /**
     * the nodes of one level as a list with all entries, because the whole config always fits on one page
     *
     * @param array $nodes the nodes of the level
     * @param string $path the path of the parent key used for the html ids
     * @param string $parent_tip the tooltip of the parent key if the level is its only key, '' otherwise
     * @return string the html code of the level
     */
    private function tree_html(array $nodes, string $path, string $parent_tip): string
    {
        $html = new html_base();
        $items = '';
        foreach ($nodes as $cfn) {
            $items .= $html->list_item($this->node_html($cfn, $path, $parent_tip));
        }
        return $html->list_unsorted($items, styles::CONFIG_TREE);
    }

    /**
     * @param config_node $cfn the key to show
     * @param string $path the path of the parent key
     * @param string $parent_tip the tooltip of the parent key if this is its only key, '' otherwise
     * @return string a row with the value of a setting or the closed part with the keys below a group
     */
    private function node_html(config_node $cfn, string $path, string $parent_tip = ''): string
    {
        $html = new html_base();
        $cfn_path = $this->child_path($path, $cfn->name);
        // the only key below a key e.g. "entries" of "initial" is described by the tooltip of its parent
        $tip = ($cfn->tooltip != '') ? $cfn->tooltip : $parent_tip;
        if ($cfn->is_leaf()) {
            $result = $this->leaf_row($cfn, $parent_tip);
        } else {
            $only_child_tip = (count($cfn->children) == 1) ? $tip : '';
            $body = $this->tree_html($cfn->children, $cfn_path, $only_child_tip);
            $id = self::NODE_ID_PREFIX . $cfn_path;
            $result = $html->details($html->esc($cfn->name), $body, styles::CONFIG_NODE, $id, $tip);
        }
        return $result;
    }

    /**
     * @param config_node $cfn the setting to show
     * @param string $parent_tip the tooltip of the parent key if this is its only setting, '' otherwise
     * @return string the html code of the row with the name and the value with its unit e.g. "1 sec"
     */
    private function leaf_row(config_node $cfn, string $parent_tip): string
    {
        $html = new html_base();
        $name = $html->esc($cfn->name);
        // the tooltip of the parent on the name and not on the row, so the row has no underline of an own tooltip
        if ($cfn->tooltip == '' and $parent_tip != '') {
            $name = $html->with_title($name, $parent_tip);
        }
        return $html->definition($name, $this->value_html($cfn), $cfn->tooltip);
    }

    /**
     * @param config_node $cfn the setting to show
     * @return string the html code of the current value e.g. "2 sec" and the config.yaml value
     *                behind it if the database differs or has no value e.g. "in config.yaml 1 sec"
     */
    private function value_html(config_node $cfn): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';
        if ($cfn->value != '') {
            $result = $this->with_unit($cfn->value, $cfn->unit);
        }
        if ($cfn->file_value != '') {
            $label = $mtr->txt(msg_id::CONFIG_TREE_FILE_VALUE);
            $file_txt = $label . ' ' . $this->with_unit($cfn->file_value, $cfn->unit);
            $result .= $html->span($file_txt, styles::CONFIG_FILE_VALUE);
        }
        return $result;
    }

    /**
     * @param string $value the value of a setting as text
     * @param string $unit the unit of the setting e.g. "sec" or ''
     * @return string the html code of the value with the unit in its own lighter style e.g. "1 sec"
     */
    private function with_unit(string $value, string $unit): string
    {
        $html = new html_base();
        $result = $html->esc($value);
        if ($unit != '') {
            $result .= $html->span($html->esc($unit), styles::CONFIG_UNIT);
        }
        return $result;
    }

    /**
     * the number of hits with the link to remove the search and one block per parent key
     * with its path and the matching keys, in which the search is highlighted
     *
     * @param array $nodes the nodes of the scope to search in
     * @param string $pattern the search of the user
     * @return string the html code of the hit list
     */
    function hit_list(array $nodes, string $pattern): string
    {
        global $mtr;
        $html = new html_base();
        $hits = $this->hits($nodes, $pattern);
        $blocks = [];
        foreach ($hits as [$path, $cfn]) {
            $key = implode(self::PATH_SEPARATOR, $path);
            $blocks[$key] = ($blocks[$key] ?? '') . $this->hit_row($cfn, $pattern);
        }
        $clear = $html->ref($this->url('', ''), $mtr->txt(msg_id::REMOVE_FILTER));
        $count = $html->span(count($hits) . ' ' . $mtr->txt(msg_id::CONFIG_TREE_MATCHES));
        $result = $html->div($count . ' ' . $clear, styles::CONFIG_HIT_HEAD);
        if ($hits == []) {
            $result .= $html->p($mtr->txt(msg_id::CONFIG_TREE_NO_MATCH));
        }
        foreach ($blocks as $key => $rows) {
            $path = $html->div($html->esc((string)$key), styles::CONFIG_PATH);
            $result .= $html->div($path . $rows, styles::CONFIG_HIT);
        }
        return $result;
    }

    /**
     * @param config_node $cfn a key that matches the search
     * @param string $pattern the search of the user
     * @return string the row of the key with the highlighted search in its name and tooltip
     */
    private function hit_row(config_node $cfn, string $pattern): string
    {
        $html = new html_base();
        $definition = $this->value_html($cfn);
        if (mb_stripos($cfn->tooltip, $pattern) !== false) {
            $definition .= ' ' . $html->span($this->marked($cfn->tooltip, $pattern));
        }
        return $html->definition($this->marked($cfn->name, $pattern), $definition);
    }

    /**
     * @param string $txt the text of a key e.g. its name
     * @param string $pattern the search of the user
     * @return string the escaped text with each match of the search highlighted, ignoring the case
     */
    function marked(string $txt, string $pattern): string
    {
        $html = new html_base();
        $result = '';
        $rest = $txt;
        $len = mb_strlen($pattern);
        $pos = $this->match_pos($rest, $pattern);
        while ($pos !== false) {
            $result .= $html->esc(mb_substr($rest, 0, $pos));
            $result .= $html->mark($html->esc(mb_substr($rest, $pos, $len)));
            $rest = mb_substr($rest, $pos + $len);
            $pos = $this->match_pos($rest, $pattern);
        }
        return $result . $html->esc($rest);
    }

    /**
     * @param string $txt the text to search in
     * @param string $pattern the search of the user
     * @return int|false the position of the first match ignoring the case or false if there is none
     */
    private function match_pos(string $txt, string $pattern): int|false
    {
        $result = false;
        if ($pattern != '') {
            $result = mb_stripos($txt, $pattern);
        }
        return $result;
    }

    /**
     * @param string $pattern the search of the user, '' for none
     * @param string $scope the top level key, '' or "all" for the whole tree
     * @return string the url of the configuration page with the search and the scope
     */
    private function url(string $pattern, string $scope): string
    {
        $html = new html_base();
        $par = [];
        if ($scope != '' and $scope != self::SCOPE_ALL) {
            $par[url_var::CONFIG_PART] = $scope;
        }
        if ($pattern != '') {
            $par[url_var::PATTERN] = $pattern;
        }
        return $html->url_back(views::SYSTEM_CONFIG_ID, 0, [], http_build_query($par));
    }

    /**
     * a link for each top level key and for the whole tree; the shown one is no link
     *
     * @param string $pattern the search of the request, which the links keep
     * @param string $scope the shown top level key
     * @return string the html code of the scope links
     */
    private function scope_links(string $pattern, string $scope): string
    {
        global $mtr;
        $html = new html_base();
        $items = '';
        $names = [self::SCOPE_ALL];
        foreach ($this->nodes as $cfn) {
            $names[] = $cfn->name;
        }
        foreach ($names as $name) {
            $label = $name;
            if ($name == self::SCOPE_ALL) {
                $label = $mtr->txt(msg_id::CONFIG_TREE_ALL);
            }
            $is_shown = ($name == $scope or ($scope == '' and $name == self::SCOPE_ALL));
            $link = $html->esc($label);
            $style = styles::CONFIG_SCOPE_SHOWN;
            if (!$is_shown) {
                $link = $html->ref($this->url($pattern, $name), $label);
                $style = '';
            }
            $items .= $html->list_item($link, $style);
        }
        return $html->list_unsorted($items, styles::CONFIG_SCOPE);
    }

    /**
     * @param array $nodes the nodes of one level
     * @param string $name the name of the key
     * @return array the nodes of that name, empty if there is none
     */
    private function named(array $nodes, string $name): array
    {
        $result = [];
        foreach ($nodes as $cfn) {
            if ($cfn->name == $name) {
                $result[] = $cfn;
            }
        }
        return $result;
    }

    /**
     * @param string $name the name of a top level key
     * @return bool true if COLUMN_KEYS or LAST_KEY place the key
     */
    private function is_placed(string $name): bool
    {
        $result = ($name == self::LAST_KEY);
        foreach (self::COLUMN_KEYS as $keys) {
            if (in_array($name, $keys)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param string $path the path of the parent key, '' for a top level key
     * @param string $name the name of the key
     * @return string the path of the key e.g. "database/cache" without blanks, so it can be an html id
     */
    private function child_path(string $path, string $name): string
    {
        $result = str_replace(' ', '_', $name);
        if ($path != '') {
            $result = $path . self::ID_SEPARATOR . $result;
        }
        return $result;
    }

}
