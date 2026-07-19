<?php

/*

    model/helper/config_numbers.php - additional behavior for the system and user config graph value tree
    -----------------------------

    $cfg is the suggested var name

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use DateTime;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'db_cache.php';
include_once paths::MODEL_HELPER . 'db_cache_type.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VALUE . 'value_text.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'language_codes.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'db_cache_statuum.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_base;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;


class config_numbers extends value_list
{

    /*
     * object vars
     */

    // fast lookup rows from the cached config json to read config values without
    // building all value and phrase objects on every request (see set_cache_json);
    // each row contains the flipped phrase names, the raw value and the text flag
    private array $lookup_rows = [];
    // the cached config json to build the complete value objects only on demand
    private ?array $cache_json = null;

    // the db cache types used to cache the config values, one cache entry per type and user
    const array CACHE_TYPES = [
        db_cache_types::SYSTEM_CONFIG,
        db_cache_types::FRONTEND_CONFIG,
        db_cache_types::USER_CONFIG,
    ];

    // the phrase names that select the pod switch for each database cache
    // (db_cache_types code id => phrase names; see config.yaml "database > cache")
    const array CACHE_ALLOWED_NAMES = [
        db_cache_types::SYSTEM_CONFIG => [words::ALLOWED, words::SYSTEM, words::CONFIG, words::CACHE],
        db_cache_types::TYPES => [words::ALLOWED, words::TYPE, words::CACHE],
        db_cache_types::USER_CONFIG => [words::ALLOWED, words::USER, words::CONFIG, words::CACHE],
        db_cache_types::FRONTEND_CONFIG => [words::ALLOWED, words::FRONTEND, words::CONFIG, words::CACHE],
        db_cache_types::USER_FRONTEND_CONFIG => [words::ALLOWED, triples::USER_FRONTEND, words::CONFIG, words::CACHE],
        db_cache_types::PAGE_CACHE => [words::ALLOWED, words::DATA, words::PAGE, words::CACHE],
        db_cache_types::UI_CACHE => [words::ALLOWED, words::PRELOAD, words::CACHE],
    ];

    // the phrase names that select the pod switch for the html page cache (db_cache_pages table)
    const array CACHE_PAGES_ALLOWED_NAMES = [words::ALLOWED, words::HTML, words::PAGE, words::CACHE];

    // list of word that should be hidden be default for normal selections
    // TODO check on pod start that these words exists and are of hidden type
    const array HIDDEN_KEYWORDS = [
        words::THIS_SYSTEM,
    ];

    // list of triples that should be hidden be default for normal selections
    // TODO check on pod start that these triples exists and are of hidden type
    const array HIDDEN_KEY_TRIPLES = [
        [words::SYSTEM, words::CONFIGURATION],
    ];

    // list of words that should be admin protected because they are user for the system configuration
    // TODO check on pod start that these words exists and are admin protected
    const array ADMIN_KEYWORDS = [
        words::ALLOWED,
        words::AUTOMATIC,
        words::AVERAGE,
        words::BACKEND,
        words::BLOCK,
        words::CACHE,
        words::CALCULATION,
        words::COLUMNS,
        words::CONFIG,
        words::CONFIGURATION,
        words::CHANGE,
        words::CREATE,
        words::DATA,
        words::DATABASE,
        words::DAILY,
        words::DEFAULT,
        words::DELAY,
        words::DELETE,
        words::ENTRY,
        words::EXPECTED,
        words::FILE,
        words::FORMULA,
        words::FREEZE,
        words::FRONTEND,
        words::FUTURE,
        words::HTML,
        words::INITIAL,
        words::IMPORT,
        words::INSERT,
        words::IP,
        words::JOB,
        words::MAX,
        words::MILLISECOND,
        words::MIN,
        words::MIN,
        words::NAME,
        words::PERCENT,
        words::PAGE,
        words::PHRASE,
        words::POD,
        words::PRELOAD,
        words::PRESELECT,
        words::READ,
        words::RETRY,
        words::SEC,
        words::SELECT,
        words::SIZE,
        words::SOURCE,
        words::START,
        words::SYS_CONF_VALUE,
        words::SYSTEM,
        words::TABLE,
        words::TIME,
        words::TRIPLE,
        words::TYPE,
        words::UPDATE,
        words::URL,
        words::USER,
        words::VALUE,
        words::VERSION,
        words::VIEW,
        words::YEAR,
    ];

    // list of triples that should be admin protected because they are user for the system configuration
    // TODO check on pod start that these triples exists and are admin protected
    const array ADMIN_KEY_TRIPLES = [
        [words::DATABASE, words::CHANGE],
        [words::START, words::DELAY],
        [words::MAX, words::DELAY],
        [words::SYS_CONF_VALUE, words::TABLE],
        [words::TABLE, words::NAME],
        [words::MAX, words::PHRASE],
        [words::MAX, words::CHANGE],
        [words::IP, words::USER],
        [words::BLOCK, words::SIZE],
        [words::AVERAGE, words::DELAY],
        [words::INITIAL, words::ENTRY],
        [words::MIN, words::PERCENT],
        [words::AUTOMATIC, words::CREATE],
        [words::MIN, words::COLUMNS],
        [words::MAX, words::COLUMNS],
        [words::FUTURE, words::PERCENT],
        [words::VALUE, words::TABLE],
        [words::EXPECTED, words::TIME],
        [words::FILE, words::READ],
        [words::USER, words::FRONTEND],
    ];

    // list of internal tooltips (and the related word) where the default text for new users should not be changed
    // TODO check on pod start that these comments are still normal
    const array INTERNAL_COMMENTS = [
        [words::TOOLTIP_COMMENT_COM, words::TOOLTIP_COMMENT],
        [words::SYS_CONF_VALUE_COM, words::SYS_CONF_VALUE],
        [words::SYS_CONF_SOURCE_COM, words::SYS_CONF_SOURCE],
        [words::SYS_CONF_USER_COM, words::SYS_CONF_USER],
        [words::TIME_COM, words::TIME],
        [words::YEAR_COM, words::YEAR],
        [words::CALCULATION_COM, words::CALCULATION],
        [words::MIN_COM, words::MIN],
        [words::MAX_COM, words::MAX],
        [words::AVERAGE_COM, words::AVERAGE],
        [words::DEFAULT_COM, words::DEFAULT],
        [words::DATABASE_COM, words::DATABASE],
        [words::LISTS, words::LISTS_COM],
        [words::MOST, words::MOST_COM],
        [words::RELEVANT, words::RELEVANT_COM],
    ];


    /*
     * set and get
     */

    /**
     * get a frontend config value selected by the phrase names
     *
     * @param array $names with the phrase names to select the config value
     * @param int|float|string|null $fallback if not null the fallback value that should be used
     *                                        if the configuration value is not found
     * @return int|float|string|null with the user-specific config value
     */
    function get_by(array $names, int|float|string|null $fallback = null): int|float|string|null
    {
        $val = $this->get_by_names($names);
        $num = $val?->number();
        if ($num == 0 or $num == null) {
            if ($fallback != null) {
                $num = $fallback;
                log_warning('use fallback configuration value for '
                    . implode(', ', $names) . ': ' . $fallback);
            }
        }
        return $num;
    }

    /**
     * get the first config value that is assigned to all given phrase names
     * from the fast lookup rows of the cached config json if available (see set_cache_json)
     * or from the loaded value objects e.g. after a fresh database load
     *
     * @param array $names the phrase names to select the config value
     * @return value_base|null the matching config value or null if no value matches
     */
    function get_by_names(array $names): ?value_base
    {
        $result = null;
        if ($this->lookup_rows == []) {
            $result = parent::get_by_names($names);
        } else {
            foreach ($this->lookup_rows as $lookup_row) {
                if ($result == null) {
                    $match = true;
                    foreach ($names as $name) {
                        if (!array_key_exists($name, $lookup_row[0])) {
                            $match = false;
                        }
                    }
                    if ($match) {
                        $result = $this->lookup_value($lookup_row[1], $lookup_row[2]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * use the cached config json for the value lookups of this request (see get_by_names),
     * which is much faster than building all value and phrase objects upfront;
     * the complete objects are only built on demand (see fill_from_cache_json)
     *
     * @param array $api_json the cached config json as created by cache_array
     * @return bool true if at least one config lookup row has been found
     */
    function set_cache_json(array $api_json): bool
    {
        $this->cache_json = $api_json;
        $this->lookup_rows = [];
        foreach ($api_json as $api_row) {
            $names = [];
            foreach ($api_row[json_fields::PHRASES] ?? [] as $phr_row) {
                if (array_key_exists(json_fields::NAME, $phr_row)) {
                    $names[$phr_row[json_fields::NAME]] = true;
                }
            }
            $is_text = array_key_exists(json_fields::TEXT_VALUE, $api_row);
            $raw_value = $api_row[json_fields::NUMBER] ?? $api_row[json_fields::TEXT_VALUE] ?? null;
            if ($names != [] and $raw_value !== null) {
                $this->lookup_rows[] = [$names, $raw_value, $is_text];
            }
        }
        return $this->lookup_rows != [];
    }

    /**
     * @param float|string $raw_value the config value from the cached json
     * @param bool $is_text true if the cached json contains a text value
     * @return value_base a config value object with just the value set for the get_by callers
     */
    private function lookup_value(float|string $raw_value, bool $is_text): value_base
    {
        if ($is_text) {
            $val = new value_text($this->get_user());
            $val->set_value($raw_value);
        } else {
            $val = new value($this->get_user(), (float)$raw_value);
        }
        return $val;
    }


    /*
     * load
     */

    /**
     * load the system configuration from the database to this object
     *
     * @param db_cache_type|type_object|null $typ the configaration type that should be loaded
     * @param user|null $usr for whom the configuration should be loaded
     * @param phrase|null $phr to select either the user or frontend configuration values
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_cfg(
        db_cache_type|type_object|null $typ = null,
        user|null $usr = null,
        ?phrase $phr = null
    ): user_message
    {
        global $sys;

        // the config values are user specific, so a user is always needed
        if ($usr == null) {
            $usr = $this->get_user();
        }
        // TODO Prio 2 review fallback type
        if ($typ == null) {
            $typ = $sys->typ_lst->cac_typ->get_by_code_id(db_cache_types::SYSTEM_CONFIG);
        }
        if ($typ == null) {
            log_warning('use fallback system type');
            $typ = new db_cache_type();
            $typ->id = db_cache_types::SYSTEM_CONFIG_ID;
            $typ->code_id = db_cache_types::SYSTEM_CONFIG;
            $typ->name = db_cache_types::SYSTEM_CONFIG_NAME;
        }
        $usr_msg = new user_message($usr);
        $sys->times->switch(system_time_type::LOAD_CONFIG_CACHE);
        if (!$this->read_cache($typ, $usr, $usr_msg, $phr)) {
            $sys->times->switch(system_time_type::LOAD_SYS_CONFIG);
            $phr_sys_cfg = new phrase($usr);
            $phr_sys_cfg->load_by_name(triples::SYSTEM_CONFIG);
            // the snap time is taken before the read, so that a change during the read is never cached as included
            $snap_time = new DateTime();
            // TODO Prio 3 speed: loading the phrases upfront with $phr_lst = $root_phr->all_children(); may be faster
            $this->load_by_phr($phr_sys_cfg);
            // all config values are sent to the frontend, also the values of the other config parts
            // and their phrases, because the additional context is expected to be useful
            // and the complete config is expected to stay small
            // (see docs/llm/architecture.md if the config gets too big)
            if (!$this->is_empty()) {
                log_debug($this->count() . ' config values loaded');
                $this->load_phrases();
            } else {
                log_warning('no config values loaded');
                $usr_msg->add_id(msg_id::CONFIG_EMPTY);
            }
            if ($usr_msg->is_ok()) {
                $sys->times->switch(system_time_type::WRITE_CONFIG_CACHE);
                $this->write_cache($typ, $usr, $snap_time, $phr);
            }
        }
        return $usr_msg;
    }

    /**
     * read the config part of the given user from the cache
     *
     * @param db_cache_type|type_object $typ the config cache type e.g. the frontend config
     * @param user $usr for whom the configuration is cached, because each user can overwrite the config values
     * @param user_message $usr_msg to report the problems of the api mapping
     * @param phrase|null $phr to select either the user or frontend configuration values
     * @return bool true if this list has been filled with the cached config values
     */
    private function read_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        user_message              $usr_msg,
        ?phrase                   $phr = null
    ): bool
    {
        $result = false;
        if ($this->cache_allowed_by_pod($typ->code_id)) {
            if (CACHE_LOCATION == ENV_CACHE_DATABASE) {
                $result = $this->read_db_cache($typ, $usr, $usr_msg);
            } else {
                $result = $this->read_file_cache($usr, $usr_msg, $phr);
            }
        }
        return $result;
    }

    private function read_db_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        user_message              $usr_msg
    ): bool
    {
        $result = false;
        $cac = $this->cache_entry($typ, $usr);
        if ($cac != null) {
            if (!$cac->is_outdated()) {
                if (is_array($cac->data)) {
                    $result = $this->set_cache_json($cac->data);
                }
            }
        }
        return $result;
    }

    /**
     * build the complete config value objects from the cached json
     * needed only if more than the value lookup is used e.g. for the config api message
     *
     * @param user_message $usr_msg to report the problems of the json mapping
     * @return void
     */
    function fill_from_cache_json(user_message $usr_msg): void
    {
        if ($this->cache_json !== null and $this->is_empty()) {
            $this->api_mapper($this->cache_json, $usr_msg);
        }
    }

    private function read_file_cache(
        user         $usr,
        user_message $usr_msg,
        ?phrase      $phr = null
    ): bool
    {
        $result = false;
        if ($this->is_file_cache_valid($usr, $phr)) {
            $json = file_get_contents($this->cache_file($usr, $phr));
            $array = json_decode($json, true);
            if (is_array($array)) {
                $result = $this->set_cache_json($array);
            } else {
                log_err('config json seems to have a problem ' . $json);
            }
        }
        return $result;
    }

    /**
     * write the config part of the given user to the cache
     *
     * @param db_cache_type|type_object $typ the config cache type e.g. the frontend config
     * @param user $usr for whom the configuration is cached, because each user can overwrite the config values
     * @param DateTime $snap_time the time of the config snap, so never the time of this write
     * @param phrase|null $phr to select either the user or frontend configuration values
     * @return void
     */
    private function write_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        DateTime                  $snap_time,
        ?phrase                   $phr = null
    ): void
    {
        if ($this->cache_allowed_by_pod($typ->code_id)) {
            if (CACHE_LOCATION == ENV_CACHE_DATABASE) {
                $this->write_db_cache($typ, $usr, $snap_time);
            } else {
                $this->write_file_cache($usr, $phr);
            }
        }
    }

    /**
     * true if the pod settings allow to use the given config cache
     * this config object itself holds the switches once it is loaded;
     * an instance that is still empty (e.g. reading its own cache on startup)
     * asks the already loaded global config and defaults to cache on
     *
     * @param string $type_code_id the code id of the db_cache type e.g. "system_config"
     * @return bool false if this config cache should not be used
     */
    private function cache_allowed_by_pod(string $type_code_id): bool
    {
        global $cfg;

        $conf = $this;
        if ($conf->is_empty() and $cfg != null) {
            $conf = $cfg;
        }
        return $conf->cache_allowed($type_code_id);
    }

    private function write_db_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        DateTime                  $snap_time
    ): void
    {
        $cac = $this->cache_entry($typ, $usr);
        if ($cac != null) {
            // the entry of this type and user is loaded upfront, so that it is updated and not added a second time
            $cac->type_id = $typ->id;
            $cac->data = $this->cache_array();
            // the cache entry belongs to the user of the config values,
            // but the row is written as the system user because filling the cache is a system
            // action that must also work for an ip user who cannot change data
            // (like the html page cache, see frontend::save_html_page)
            $cac->usr = $usr;
            $cac->status_id = db_cache_statuum::CLEAN_ID;
            $cac->last_update = $snap_time;
            $save_msg = new user_message(user::system());
            if (!$cac->save($save_msg)) {
                // a failure is only logged because the user already has the config values
                log_warning('caching the config for ' . $usr->dsp_id()
                    . ' failed because ' . $save_msg->all_message_text());
            }
        }
    }

    private function write_file_cache(user $usr, ?phrase $phr = null): void
    {
        $file_name = $this->cache_file($usr, $phr);
        $array = $this->cache_array();
        $json = json_encode($array);
        file_put_contents($file_name, $json);
    }

    /**
     * get the database cache entry of the given config part for the given user
     * with an empty entry if the config part has not yet been cached
     *
     * @param db_cache_type|type_object $typ the config cache type e.g. the frontend config
     * @param user $usr for whom the configuration is cached, because each user can overwrite the config values
     * @return db_cache|null the cache entry of this config part and user
     *                       or null if the config part is not expected to be cached
     */
    private function cache_entry(
        db_cache_type|type_object $typ,
        user                      $usr
    ): ?db_cache
    {
        $cac = null;
        if (!in_array($typ->code_id, self::CACHE_TYPES)) {
            log_err('unknown config cache type ' . $typ->code_id);
        } else {
            $cac = new db_cache($usr);
            $cac->load_by_type_and_user($typ->code_id);
        }
        return $cac;
    }

    private function is_file_cache_valid(user $usr, ?phrase $phr = null): bool
    {
        $result = false;
        $file_path = $this->cache_file($usr, $phr);
        if (file_exists($file_path)) {
            $cac_time = filemtime($file_path);
            $cfg_time = filemtime(files::SYSTEM_CONFIG);
            $result = $cfg_time < $cac_time;
        }
        return $result;
    }

    private function cache_file(user $usr, ?phrase $phr = null): string
    {
        // key the cache file by the integer user id, never by the (unvalidated) user name: a name
        // is taken verbatim from signup, so a name like "x/../../http/pwn" would otherwise let
        // write_file_cache() write/read outside cache/ (path traversal) or collide with another
        // user's cache entry (poisoning); the db cache keys by user_id for the same reason. the
        // phrase part is always a fixed config phrase const, not user input
        $file_path = paths::CACHE . files::CACHE_CONFIG . files::SEP . $usr->id();
        if ($phr != null) {
            $file_path .= files::SEP . $phr->name();
        }
        $file_path .= files::JSON;
        return $file_path;
    }

    /**
     * load the system configuration values relevant for the frontend
     *
     * @param user $usr for whom the configuration should be loaded
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_frontend_cfg(user $usr): user_message
    {
        global $sys;
        $phr = new phrase($usr);
        $phr->load_by_name(api::CONFIG_FRONTEND);
        $typ = $sys->typ_lst->cac_typ->get_by_code_id(db_cache_types::FRONTEND_CONFIG);
        return $this->load_cfg($typ, $usr, $phr);
    }

    /**
     * load the system configuration values that the user can change
     *
     * @param user $usr for whom the configuration should be loaded
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_usr_cfg(user $usr): user_message
    {
        global $sys;
        $phr = new phrase($usr);
        $phr->load_by_name(api::CONFIG_USER);
        $typ = $sys->typ_lst->cac_typ->get_by_code_id(db_cache_types::USER_CONFIG);
        return $this->load_cfg($typ, $usr, $phr);
    }


    /*
     * mapping
     */

    private function cache_array(): array
    {
        return $this->api_json_array(new api_type_list([api_types::PHRASE_NAMES]));
    }

    /*
     * default
     */

    function default_json(): string
    {
        return '';
    }


    /*
     * predefined
     */

    /**
     * @return string the code_id of the user frontend language
     */
    function language(): string
    {
        return $this->get_by([
            words::LANGUAGE,
            words::USER,
            words::FRONTEND],
            language_codes::SYS
        );
    }

    /**
     * the pod setting that decides if the given database cache is used
     * a pod without the switch uses the cache, so only an explicit false in config.yaml
     * switches a cache off e.g. to debug with always fresh data
     * no fallback is given to get_by, because get_by would replace the false of the config value with it
     * @param string $type_code_id the code id of the db_cache type e.g. "types"
     * @return bool false if this database cache should not be used
     */
    function cache_allowed(string $type_code_id): bool
    {
        $result = true;
        $names = self::CACHE_ALLOWED_NAMES[$type_code_id] ?? [];
        if ($names == []) {
            // a warning is enough, because not using the cache is the safe fallback
            // and a log_err would stop the automatic tests that check exactly this case
            log_warning('unknown database cache switch ' . $type_code_id);
            $result = false;
        } else {
            $val = $this->get_by_names($names);
            if ($val !== null) {
                $result = $val->number() != 0;
            }
        }
        return $result;
    }

    /**
     * the pod setting that decides if the rendered html pages are cached in the db_cache_pages table
     * a pod without the switch uses the cache, so only an explicit false in config.yaml switches it off
     * @return bool false if the rendered html pages should not be cached
     */
    function page_cache_allowed(): bool
    {
        $result = true;
        $val = $this->get_by_names(self::CACHE_PAGES_ALLOWED_NAMES);
        if ($val !== null) {
            $result = $val->number() != 0;
        }
        return $result;
    }

    /**
     * the pod permission that decides if a user without login can change data in the database
     * no fallback is given to get_by, because get_by would replace the false of the config value with it
     * the yaml false is imported as the number zero, and a missing permission is as restrictive as the
     * default of config.yaml, so only an explicit true permits the changes of an ip user
     * @return bool false if a user without login is not permitted to change any data of this pod
     */
    function ip_user_can_change(): bool
    {
        $permitted = $this->get_by([
            words::ALLOWED,
            triples::IP_USER,
            triples::DATABASE_CHANGE]
        );
        return $permitted != 0;
    }

}
