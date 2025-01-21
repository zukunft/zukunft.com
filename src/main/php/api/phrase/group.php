<?php

/*

    api/phrase/phrase_group.php - the minimal phrase group object used for the back- to frontend api
    ---------------------------


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

namespace api\phrase;

include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'words.php';
include_once API_SANDBOX_PATH . 'sandbox_named.php';

use api\word\word as word_api;
use api\phrase\phrase_list as phrase_list_api;
use api\sandbox\sandbox_named as sandbox_named_api;
use html\phrase\phrase_group as phrase_group_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use JsonSerializable;
use shared\json_fields;
use shared\words;

class group extends sandbox_named_api implements JsonSerializable
{

    /*
     * const for system testing
     */

    // persevered phrase group names for unit and integration tests
    const TN_READ = 'Pi (math)';
    const TN_RENAMED = 'Pi';

    // persevered group names for database write tests
    const TN_ADD_PRIME_FUNC = 'System Test Group prime added via sql function';
    const TN_ADD_PRIME_SQL = 'System Test Group prime added via sql insert';
    const TN_ADD_MOST_FUNC = 'System Test Group main added via sql function';
    const TN_ADD_MOST_SQL = 'System Test Group main added via sql insert';
    const TN_ADD_BIG_FUNC = 'System Test Group big added via sql function';
    const TN_ADD_BIG_SQL = 'System Test Group big added via sql insert';

    const TN_ZH_2019 = words::TN_INHABITANT . 's in the city of ' . words::TN_ZH . ' (' . words::TN_2019 . ')';
    const TN_CH_INCREASE_2020 = words::TN_INCREASE . ' in ' . words::TN_CH . '\'s ' . words::TN_INHABITANT . 's from ' . words::TN_2019 . ' to ' . words::TN_2020 . ' in ' . words::TN_PCT;
    const TN_ZH_2019_IN_MIO = self::TN_ZH_2019 . ' in ' . words::TN_MIO;
    const TN_CH_2019 = words::TN_INHABITANT . ' of ' . words::TN_CH . ' in Mio (' . words::TN_2019 . ')';

    const TN_TIME_VALUE = 'zukunft.com beta launch date';
    const TD_TIME_VALUE = 'the expected launch date of the first beta version of zukunft.com';
    const TN_TEXT_VALUE = 'zukunft.com pod URL';
    const TD_TEXT_VALUE = 'URL of this zukunft.com pod from the system configuration';

    const TN_GEO_VALUE = 'zukunft.com development geolocation';
    const TD_GEO_VALUE = 'the geolocation of the initial development of zukunft.com';

    // list of predefined group names used for system testing that are expected to be never renamed
    const RESERVED_GROUP_NAMES = [
        self::TN_READ,
        self::TN_ZH_2019,
        self::TN_CH_2019
    ];

    // list of group names and the related phrases that are used for system testing
    // and that should be created before the system test starts
    const TEST_GROUPS_CREATE = [
        [self::TN_READ,
            [words::PI, words::MATH]],
        [self::TN_CH_2019,
            [words::TN_INHABITANTS, words::TN_COUNTRY, words::TN_CH, words::TN_2019, words::TN_MIO]]
    ];


    /*
     * object vars
     */

    // list of word_min and triple_min objects
    private array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;
    private bool $name_dirty;

    /*
     * construct and map
     */

    function __construct(int $id = 0, array $phr_lst = array(), string $name = '')
    {
        parent::__construct($id, $name);
        $this->lst = [];

        $this->id_lst = array();
        $this->lst_dirty = false;
        $this->name_dirty = true;


        // fill the phrase group based on the parameters included in new call
        $phr_id = 1; // if now id is given, create a dummy id for testing
        if (count($phr_lst) > 0) {
            foreach ($phr_lst as $phr_str) {
                $phr = new phrase($phr_str);
                $this->add($phr);
                $phr_id++;
            }
        }
    }

    /*
     * set and get
     */

    function set_lst($lst): void
    {
        $this->lst = $lst;
        $this->set_dirty();
    }

    function reset_lst(): void
    {
        $this->lst = array();
        $this->set_dirty();
    }

    function set_dirty(): void
    {
        $this->lst_dirty = true;
        $this->name_dirty = true;
    }

    function unset_name_dirty(): void
    {
        $this->name_dirty = false;
    }

    /**
     * @returns array the protected list of phrases
     */
    function lst(): array
    {
        return $this->lst;
    }

    function name_dirty(): bool
    {
        return $this->name_dirty;
    }

    /**
     * @returns array with all unique phrase ids og this list
     */
    private function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $phr) {
                if (!in_array($phr->id(), $result)) {
                    $result[] = $phr->id();
                }
            }
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase $phr): bool
    {
        $result = false;
        if (!in_array($phr->id(), $this->id_lst())) {
            $this->lst[] = $phr;
            $this->set_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * @returns phrase_list_api the list of phrases as an object
     */
    function phr_lst(): phrase_list_api
    {
        $result = new phrase_list_api();
        $result->set_lst($this->lst());
        return $result;
    }

    /*
     * info
     */

    function has_percent(): bool
    {
        return $this->phr_lst()->has_percent();
    }


    /*
     * cast
     */

    /**
     * @returns phrase_group_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_group_dsp
    {
        $result = new phrase_group_dsp();
        $result->set_id($this->id());
        $result->set_lst_dsp($this->lst());
        return $result;
    }

    /**
     * @returns phrase_list_dsp the list of phrases as an object
     */
    function phr_lst_dsp(): phrase_list_dsp
    {
        $result = new phrase_list_dsp();
        $result->set_lst($this->lst());
        return $result;
    }

    function load_phrases(): bool
    {
        return $this->load_phrases();
    }

    /*
     * interface
     */

    /**
     * @return array with the value vars including the protected vars
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        $vars[json_fields::PHRASES] = json_decode(json_encode($this->phr_lst()));
        return $vars;
    }

}
