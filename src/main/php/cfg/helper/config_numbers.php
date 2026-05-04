<?php

/*

    model/helper/config_numbers.php - additional behavior for the system and user config graph value tree
    -----------------------------


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
use Exception;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::SHARED . 'api.php';
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
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\api;
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

    // remember the time of the last refresh to reduced the number of database accesses
    private ?DateTime $last_update = null;


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
        words::AUTOMATIC,
        words::AVERAGE,
        words::BACKEND,
        words::BLOCK,
        words::CALCULATION,
        words::COLUMNS,
        words::CONFIGURATION,
        words::CHANGE,
        words::CREATE,
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
        words::PHRASE,
        words::POD,
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
            if (!$this->is_cache_valid($typ, $usr, $phr)) {
                $sys->times->switch(system_time_type::LOAD_SYS_CONFIG);
                $phr_sys_cfg = new phrase($usr);
                $phr_sys_cfg->load_by_name(triples::SYSTEM_CONFIG);
                // TODO Prio 3 speed: loading the phrases upfront with $phr_lst = $root_phr->all_children(); may be faster
                $this->load_by_phr($phr_sys_cfg);
                // TODO Prio 2 speed: it may be faster if the phrase is included in the sql select
                if ($phr != null) {
                    // TODO Prio 1 activate
                    //$this->filter_by_phrase($phr);
                    log_debug('filter by phrase');
                }
                if (!$this->is_empty()) {
                    log_debug($this->count() . ' config values loaded');
                    $this->load_phrases();
                } else {
                    log_err('no config values loaded');
                    $usr_msg->add_id(msg_id::CONFIG_EMPTY);
                }
                if ($usr_msg->is_ok()) {
                    $sys->times->switch(system_time_type::WRITE_CONFIG_CACHE);
                    $this->write_cache($typ, $usr, $phr);
                }
            } else {
                log_err('cannot read cache, but seems to be valid, which should never be the case');
            }
        }
        return $usr_msg;
    }

    private function is_cache_valid(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): bool
    {
        if (CACHE_LOCATION == ENV_CACHE_DATABASE) {
            return $this->is_db_cache_valid($typ, $usr, $phr);
        } else {
            return $this->is_file_cache_valid($typ, $usr, $phr);
        }
    }

    private function is_db_cache_valid(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): bool
    {
        $cac_time = $this->last_update;
        if ($cac_time === null) {
            return false;
        } else {
            // TODO Prio 1 use a trigger to set the time but refresh at least once a day
            $cfg_time = new DateTime;
            try {
                $cfg_time->modify('-1 day');
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
            if ($cfg_time < $cac_time) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function is_file_cache_valid(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): bool
    {
        $file_path = $this->cache_file($usr, $phr);
        if (file_exists($file_path)) {
            $cac_time = filemtime($file_path);
            $cfg_time = filemtime(files::SYSTEM_CONFIG);
            if ($cfg_time < $cac_time) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function write_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): void
    {
        if (CACHE_LOCATION == ENV_CACHE_DATABASE) {
            $this->write_db_cache($typ, $usr, $phr);
        } else {
            $this->write_file_cache($typ, $usr, $phr);
        }
    }

    private function write_db_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): void
    {
        $cac = new db_cache($usr);
        $cac->type_id = $typ->id;
        $cac->data = $this->api_json_array(new api_type_list([api_types::PHRASE_NAMES]));
        $cac->usr = $usr;
        $cac->status_id = db_cache_statuum::CLEAN_ID;
        $cac->last_update = $this->last_update;
        if ($cac->last_update === null) {
            $cac->last_update = new DateTime();
        }
        $msg = new user_message($usr);
        $cac->save($msg);
    }

    private function write_file_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        ?phrase                   $phr = null
    ): void
    {
        $file_name = $this->cache_file($usr, $phr);
        $array = $this->cache_array();
        $json = json_encode($array);
        file_put_contents($file_name, $json);
    }

    private function read_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        user_message              $usr_msg,
        ?phrase                   $phr = null
    ): bool
    {
        if (CACHE_LOCATION == ENV_CACHE_DATABASE) {
            return $this->read_db_cache($typ, $usr, $usr_msg, $phr);
        } else {
            return $this->read_file_cache($typ, $usr, $usr_msg, $phr);
        }
    }

    private function read_db_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        user_message              $usr_msg,
        ?phrase                   $phr = null
    ): bool
    {
        $cac = new db_cache($usr);
        if ($typ?->code_id == db_cache_types::SYSTEM_CONFIG) {
            $cac->load_by_type(db_cache_types::SYSTEM_CONFIG);
        } elseif ($typ?->code_id == db_cache_types::FRONTEND_CONFIG) {
            $cac->load_by_type(db_cache_types::FRONTEND_CONFIG);
        } elseif ($typ?->code_id == db_cache_types::USER_CONFIG) {
            $cac->load_by_type(db_cache_types::USER_CONFIG);
        } else {
            log_err('unknown cache type ' . $phr->name());
        }
        if ($cac->data !== null) {
            $this->api_mapper($cac->data, $usr_msg);
            return true;
        } else {
            return false;
        }
    }

    private function read_file_cache(
        db_cache_type|type_object $typ,
        user                      $usr,
        user_message              $usr_msg,
        ?phrase                   $phr = null
    ): bool
    {
        $file_name = $this->cache_file($usr, $phr);
        $json = file_get_contents($file_name);
        if ($json !== false) {
            $array = json_decode($json, true);
            if (is_array($array)) {
                $this->api_mapper($array, $usr_msg);
                return true;
            } else {
                log_err('config json seems to have a problem ' . $json);
                return false;
            }
        } else {
            return false;
        }
    }

    private function cache_file(user $usr, ?phrase $phr = null): string
    {
        $file_path = paths::CACHE . files::CACHE_CONFIG . files::SEP . $usr->name();
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

}