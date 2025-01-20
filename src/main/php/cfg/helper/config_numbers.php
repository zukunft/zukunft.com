<?php

/*

    cfg/helper/config_numbers.php - additional behavior for the system and user config graph value tree
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

namespace cfg\helper;

include_once API_SYSTEM_PATH . 'type_list.php';
include_once API_VALUE_PATH . 'value_list.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';
include_once SHARED_PATH . 'words.php';
include_once SHARED_PATH . 'triples.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once API_SYSTEM_PATH . 'type_list.php';
include_once WEB_USER_PATH . 'user_type_list.php';

use api\value\value_list as value_list_api;
use cfg\phrase\phrase;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value_list;
use cfg\word\word;
use shared\api;
use shared\triples;
use shared\words;


class config_numbers extends value_list
{

    // list of word that should be hidden be default for normal selections
    // TODO check on pod start that these words exists and are of hidden type
    const HIDDEN_KEYWORDS= [
        words::THIS_SYSTEM,
    ];

    // list of triples that should be hidden be default for normal selections
    // TODO check on pod start that these triples exists and are of hidden type
    const HIDDEN_KEY_TRIPLES = [
        [words::SYSTEM, words::CONFIGURATION],
    ];

    // list of words that should be admin protected because they are user for the system configuration
    // TODO check on pod start that these words exists and are admin protected
    const ADMIN_KEYWORDS = [
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
        words::FREEZE,
        words::FRONTEND,
        words::FUTURE,
        words::INITIAL,
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
        words::RETRY,
        words::SEC,
        words::SELECT,
        words::SIZE,
        words::START,
        words::SYS_CONF_VALUE,
        words::SYSTEM,
        words::TABLE,
        words::TIME,
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
    const ADMIN_KEY_TRIPLES = [
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
    ];

    // list of internal tooltips (and the related word) where the default text for new users should not be changed
    // TODO check on pod start that these comments are still normal
    const INTERNAL_COMMENTS = [
        [words::TOOLTIP_COMMENT_COM, words::TOOLTIP_COMMENT],
        [words::SYS_CONF_VALUE_COM, words::SYS_CONF_VALUE],
        [words::TIME_COM, words::TIME],
        [words::YEAR_COM, words::YEAR],
        [words::CALCULATION_COM, words::CALCULATION],
        [words::MIN_COM, words::MIN],
        [words::MAX_COM, words::MAX],
        [words::AVERAGE_COM, words::AVERAGE],
        [words::DEFAULT_COM, words::DEFAULT],
        [words::DATABASE_COM, words::DATABASE],
    ];


    /*
     * cast
     */

    /**
     * @return value_list_api the object type list frontend api object
     */
    function api_obj(): value_list_api
    {
        return parent::api_obj();
    }


    /*
     * load
     */

    /**
     * load the system configuration from the database to this object
     *
     * @param user $usr for whom the configuration should be loaded
     * @param phrase|null $phr to select either the user or frontend configuration values
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_cfg(user $usr, ?phrase $phr = null): user_message
    {
        $usr_msg = new user_message();
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
            log_debug('no config values loaded');
            $usr_msg->add_message('configuration is empty');
        }
        return $usr_msg;
    }

    /**
     * load the system configuration values relevant for the frontend
     *
     * @param user $usr for whom the configuration should be loaded
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_frontend_cfg(user $usr): user_message
    {
        $phr = new phrase($usr);
        $phr->load_by_name(api::CONFIG_FRONTEND);
        return $this->load_cfg($usr, $phr);
    }

    /**
     * load the system configuration values that the user can change
     *
     * @param user $usr for whom the configuration should be loaded
     * @return user_message if something strange happened the message code ids and the parameters for humans
     */
    function load_usr_cfg(user $usr): user_message
    {
        $phr = new phrase($usr);
        $phr->load_by_name(api::CONFIG_USER);
        return $this->load_cfg($usr, $phr);
    }


    /*
     * default
     */

    function default_json(): string
    {
        return '';
    }

}