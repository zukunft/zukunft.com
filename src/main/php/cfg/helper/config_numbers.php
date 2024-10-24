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

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once SHARED_PATH . 'library.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once API_SYSTEM_PATH . 'type_list.php';
include_once WEB_USER_PATH . 'user_type_list.php';

use api\value\value_list as value_list_api;
use cfg\value\value_list;


class config_numbers extends value_list
{

    // list of word that should be hidden be default for normal selections
    // TODO check on pod start that these words exists and are of hidden type
    const HIDDEN_KEYWORDS= [
        word::THIS_SYSTEM,
    ];

    // list of triples that should be hidden be default for normal selections
    // TODO check on pod start that these triples exists and are of hidden type
    const HIDDEN_KEY_TRIPLES = [
        [word::SYSTEM, word::CONFIGURATION],
    ];

    // list of words that should be admin protected because they are user for the system configuration
    // TODO check on pod start that these words exists and are admin protected
    const ADMIN_KEYWORDS = [
        word::SYSTEM,
        word::CONFIGURATION,
        word::JOB,
        word::POD,
        word::USER,
        word::TIME,
        word::YEAR,
        word::CALCULATION,
        word::AVERAGE,
        word::DATABASE,
        word::VERSION,
        word::RETRY,
        word::START,
        word::DELAY,
        word::SEC,
        word::MAX,
        word::BLOCK,
        word::SIZE,
        word::INSERT,
        word::UPDATE,
        word::DELETE,
        word::VALUE,
        word::TABLE,
        word::NAME,
        word::PHRASE,
        word::MILLISECOND,
        word::SELECT,
        word::INITIAL,
        word::ENTRY,
        word::PRESELECT,
        word::MIN,
        word::PERCENT,
        word::AUTOMATIC,
        word::CREATE,
        word::VIEW,
        word::FREEZE,
        word::URL,
    ];

    // list of triples that should be admin protected because they are user for the system configuration
    // TODO check on pod start that these triples exists and are admin protected
    const ADMIN_KEY_TRIPLES = [
        [word::START, word::DELAY],
        [word::MAX, word::DELAY],
        [word::VALUE, word::TABLE],
        [word::TABLE, word::NAME],
        [word::MAX, word::PHRASE],
        [word::BLOCK, word::SIZE],
        [word::AVERAGE, word::DELAY],
        [word::INITIAL, word::ENTRY],
        [word::MIN, word::PERCENT],
        [word::AUTOMATIC, word::CREATE],
    ];

    // list of internal tooltips (and the related word) where the default text for new users should not be changed
    // TODO check on pod start that these comments are still normal
    const INTERNAL_COMMENTS = [
        [word::TOOLTIP_COMMENT_COM, word::TOOLTIP_COMMENT],
        [word::TIME_COM, word::TIME],
        [word::YEAR_COM, word::YEAR],
        [word::CALCULATION_COM, word::CALCULATION],
        [word::AVERAGE_COM, word::AVERAGE],
        [word::DATABASE_COM, word::DATABASE],
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

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * load
     */

    /**
     *
     * @return bool true if the values of the user configuration have been loaded
     */
    function load_usr_cgf(user $usr): bool
    {
        $result = false;
        $root_phr = new phrase($this->user());
        $root_phr->load_by_name(word::SYSTEM_CONFIG);
        $phr_lst = $root_phr->all_children();
        $val_lst = new value_list($usr);
        $val_lst->load_by_phr_lst($phr_lst);
        if (!$val_lst->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /*
     * default
     */

    function default_json(): string
    {
        return '';
    }

}