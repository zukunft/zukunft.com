<?php

/*

    test/unit/config_tests.php - unit testing of the system configuration
    --------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_HELPER . 'config_tree.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::SERVICE . 'config.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'language_codes.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';
include_once test_paths::CREATE . 'test_config_trees.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_tree;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\test\php\create\test_config_trees;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class config_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message();
        $sc = new sql_creator();
        $t_val = new test_values($t);
        $t_wrd = new test_words($t);
        $t->name = 'config->';
        $t->resource_path = 'db/system/';

        // start the test section (ts)
        $ts = 'unit config ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $cfg = new config();
        $t->assert_sql_table_create($cfg);
        $t->assert_sql_index_create($cfg);

        $t->subheader($ts . 'sql write insert');
        $cfg = new config();
        $cfg->code_id = config::VERSION_DB;
        $cfg->value = def::FIRST_VERSION;
        $t->assert_sql_insert($sc, $cfg, [sql_type::LOG]);
        // an unrelated problem that the request message already carries must never cut the body
        // of the generated sql function (a cut body has broken the forced database reset with an
        // empty 'BEGIN RETURN' function); the sql build skips steps only on own build problems
        $test_name = 'a pre-filled request message does not cut the sql function body';
        $msg_nok = new user_message($t->usr1); // a pre-filled message of this negative test block
        $msg_nok->add(msg_id::ID_AND_NAME_MISSING, []);
        $qp = $cfg->sql_insert($sc, $msg_nok, new sql_type_list([sql_type::LOG]));
        $t->assert_text_contains($test_name, $qp->sql, sql::INSERT);
        $cfg_db = clone $cfg;
        $cfg->value = def::PRG_VERSION;
        $cfg->name = config::VERSION_DB_NAME;
        $cfg->description = config::VERSION_DB_COM;
        $t->assert_sql_update($sc, $cfg, $cfg_db, [sql_type::LOG]);


        $t->subheader($ts . 'ip user permission');

        // the pod permission that decides if a user without login can change data in the database
        $test_name = 'an ip user can change data if this pod permits it';
        $t->assert_true($test_name, $t_val->config_ip_user_change(true)->ip_user_can_change());
        $test_name = 'an ip user cannot change data if this pod does not permit it';
        $t->assert_false($test_name, $t_val->config_ip_user_change(false)->ip_user_can_change());
        // a missing permission is as restrictive as the default of config.yaml
        $test_name = 'an ip user cannot change data if the permission is missing';
        $t->assert_false($test_name, $t_val->config_empty()->ip_user_can_change());


        $t->subheader($ts . 'database cache switches');

        // each database cache can be switched off by a pod setting in config.yaml
        // and a pod without the switch uses the cache
        $names = config_numbers::CACHE_ALLOWED_NAMES[db_cache_types::TYPES];
        $test_name = 'the types cache is used if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'the types cache is not used if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'a pod without the switch uses the types cache';
        $t->assert_true($test_name, $t_val->config_empty()->cache_allowed(db_cache_types::TYPES));
        $test_name = 'the system config switch does not change the types cache';
        $names = config_numbers::CACHE_ALLOWED_NAMES[db_cache_types::SYSTEM_CONFIG];
        $t->assert_true($test_name, $t_val->config_cache_switch($names, false)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'an unknown cache switch is reported and the cache is not used';
        $t->assert_false($test_name, $t_val->config_empty()->cache_allowed('unexpected_cache_type'));

        $t->subheader($ts . 'cached json lookup');

        // the config lookup uses the flat rows of the cached json
        // without building the value and phrase objects on every request
        $names = config_numbers::CACHE_ALLOWED_NAMES[db_cache_types::TYPES];
        $cfg_objects = $t_val->config_cache_switch($names, false);
        $api_json = $cfg_objects->api_json_array(new api_type_list([api_types::PHRASE_NAMES]), $msg);
        $cfg_cached = $t_val->config_empty();
        $test_name = 'the cached config json fills the lookup rows';
        $t->assert_true($test_name, $cfg_cached->set_cache_json($api_json));
        $test_name = 'the lookup from the cached json returns the same value as from the objects';
        $t->assert($test_name, $cfg_cached->get_by($names), $cfg_objects->get_by($names));
        $test_name = 'the cache switch from the cached json matches the object based switch';
        $t->assert($test_name,
            $cfg_cached->cache_allowed(db_cache_types::TYPES),
            $cfg_objects->cache_allowed(db_cache_types::TYPES));
        $test_name = 'a name not in the cached json returns the fallback';
        $t->assert($test_name, $cfg_cached->get_by([words::MILLISECOND], 42), 42);
        $test_name = 'an empty cached json does not fill the lookup rows';
        $t->assert_false($test_name, $t_val->config_empty()->set_cache_json([]));

        // a text config value e.g. the language code is restored from the cached json
        $api_json[] = [
            json_fields::PHRASES => [
                [json_fields::NAME => words::LANGUAGE],
                [json_fields::NAME => words::USER],
                [json_fields::NAME => words::FRONTEND],
            ],
            json_fields::TEXT_VALUE => language_codes::SYS,
        ];
        $cfg_cached = $t_val->config_empty();
        $cfg_cached->set_cache_json($api_json);
        $test_name = 'a text config value is read from the cached json';
        $t->assert($test_name, $cfg_cached->language(), language_codes::SYS);

        // the html page cache (db_cache_pages) has its own pod switch
        $names = config_numbers::CACHE_PAGES_ALLOWED_NAMES;
        $test_name = 'the html pages are cached if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->page_cache_allowed());
        $test_name = 'the html pages are not cached if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->page_cache_allowed());
        $test_name = 'a pod without the switch caches the html pages';
        $t->assert_true($test_name, $t_val->config_empty()->page_cache_allowed());

        // the values and the formulas of the symbols and of the aliases of a phrase are shown
        // with the phrase's own unless the pod switches it off, each with a switch of its own
        $names = config_numbers::SYMBOL_VALUES_INCLUDE_NAMES;
        $test_name = 'the values of the symbols are included if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->symbol_values_included());
        $test_name = 'the values of the symbols are not included if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->symbol_values_included());
        $test_name = 'a pod without the switch includes the values of the symbols';
        $t->assert_true($test_name, $t_val->config_empty()->symbol_values_included());
        $names = config_numbers::ALIAS_VALUES_INCLUDE_NAMES;
        $test_name = 'the values of the aliases are included if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->alias_values_included());
        $test_name = 'the values of the aliases are not included if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->alias_values_included());
        $names = config_numbers::SYMBOL_FORMULAS_INCLUDE_NAMES;
        $test_name = 'the formulas of the symbols are included if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->symbol_formulas_included());
        $test_name = 'the formulas of the symbols are not included if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->symbol_formulas_included());
        $names = config_numbers::ALIAS_FORMULAS_INCLUDE_NAMES;
        $test_name = 'the formulas of the aliases are included if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->alias_formulas_included());
        $test_name = 'the formulas of the aliases are not included if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->alias_formulas_included());

        // with both switches off the selection of a phrase is the phrase alone, which needs no
        // database read, so the pod config is replaced for this check and restored afterwards
        global $cfg;
        $cfg_pod = $cfg;
        $cfg = $t_val->config_switches_off([
            config_numbers::SYMBOL_VALUES_INCLUDE_NAMES, config_numbers::ALIAS_VALUES_INCLUDE_NAMES]);
        $test_name = 'with the switches off the value selection is the phrase alone';
        $phr_chf = $t_wrd->word_chf()->phrase();
        $t->assert($test_name, $phr_chf->value_selection($msg)->names(), [words::CHF]);
        $cfg = $cfg_pod;


        $t->subheader($ts . 'tree');

        // the tree of config.yaml keeps the key order and the tooltips for the frontend
        $test_name = 'the tooltip-comment is the tooltip of its key';
        $t_cft = new test_config_trees($t);
        $cft = new config_tree();
        $nodes = $cft->nodes($t_cft->yaml(), $t_cft->cfg());
        $db_node = $t_cft->node_by_name($nodes, test_config_trees::DATABASE);
        $t->assert($test_name, $db_node[json_fields::DESCRIPTION] ?? '', test_config_trees::DATABASE_TIP);
        $test_name = '... and never a key of its own';
        $t->assert($test_name, $t_cft->node_by_name($nodes, words::TOOLTIP_COMMENT), []);
        $test_name = 'the sys-conf-value is the value of its key';
        $lan_node = $t_cft->node_by_name($nodes, test_config_trees::LANGUAGE);
        $t->assert($test_name, $lan_node[json_fields::TEXT_VALUE] ?? '', test_config_trees::LANGUAGE_VALUE);
        $test_name = '... and never a key of its own';
        $t->assert($test_name, $t_cft->node_by_name($nodes, words::SYS_CONF_VALUE), []);
        // negative: the same value in the database needs no config.yaml value
        $test_name = '... without the config.yaml value if the database has the same value';
        $t->assert($test_name, $lan_node[json_fields::CONFIG_FILE_VALUE] ?? '', '');
        $test_name = 'the only setting of a key is its database value with the setting key as unit';
        $delay_node = $t_cft->node_by_name($nodes, test_config_trees::START_DELAY);
        $t->assert($test_name, $delay_node[json_fields::TEXT_VALUE] ?? '', (string)test_config_trees::START_DELAY_DB);
        $t->assert($test_name, $delay_node[json_fields::CONFIG_UNIT] ?? '', test_config_trees::SEC);
        $test_name = '... with the config.yaml value if the database value differs';
        $t->assert($test_name, $delay_node[json_fields::CONFIG_FILE_VALUE] ?? '', (string)test_config_trees::START_DELAY_VALUE);
        // negative: a value with one more phrase belongs to another setting, so the row limit has no database value
        $test_name = 'a setting without a database value has only the config.yaml value';
        $default_node = $t_cft->node_by_name($nodes, test_config_trees::ROW_LIMIT_SETTING);
        $t->assert($test_name, $default_node[json_fields::TEXT_VALUE] ?? '', '');
        $t->assert($test_name, $default_node[json_fields::CONFIG_FILE_VALUE] ?? '', (string)test_config_trees::ROW_LIMIT_VALUE);
        $test_name = 'the config value of exactly the given phrases ignores a value with more phrases';
        $default_names = array_merge(config_tree::ROOT_KEYS,
            [test_config_trees::DATABASE, test_config_trees::ROW_LIMIT, test_config_trees::ROW_LIMIT_SETTING]);
        $t->assert_true($test_name, $t_cft->cfg()->get_by_names_exact($default_names) === null);
        // negative: a key with two settings is a group without a value or a unit
        $test_name = 'a key with two settings has no value and no unit';
        $retry_node = $t_cft->node_by_name($nodes, test_config_trees::RETRY);
        $t->assert($test_name, ($retry_node[json_fields::TEXT_VALUE] ?? '') . ($retry_node[json_fields::CONFIG_UNIT] ?? ''), '');
        $test_name = 'a key with one setting that is no unit keeps the setting as its own key';
        $limit_node = $t_cft->node_by_name($nodes, test_config_trees::ROW_LIMIT);
        $limit_names = array_column($limit_node[json_fields::CONFIG_CHILDREN] ?? [], json_fields::NAME);
        $t->assert($test_name, $limit_names, [test_config_trees::ROW_LIMIT_SETTING]);
        $test_name = '... but its settings in the order of config.yaml';
        $retry_names = array_column($retry_node[json_fields::CONFIG_CHILDREN] ?? [], json_fields::NAME);
        $t->assert($test_name, $retry_names, [test_config_trees::START_DELAY, test_config_trees::MAX_DELAY]);
        $test_name = 'a boolean setting saved as 1 is shown as true';
        $page_node = $t_cft->node_by_name($nodes, test_config_trees::PAGE);
        $t->assert($test_name, $page_node[json_fields::TEXT_VALUE] ?? '', 'true');

    }

}