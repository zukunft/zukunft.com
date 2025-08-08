<?php

/*

    web/group/group.php - the extension of the phrase group api object to create the HTML code to display a word or triple
    -------------------

    mainly links to the word and triple display functions

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - modify:            change potentially all object and all variables of this list with one function call
    - info:              functions to make code easier to read


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

namespace html\group;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED . 'json_fields.php';

use html\phrase\phrase as phrase;
use html\phrase\phrase_list as phrase_list;
use html\sandbox\sandbox_named as sandbox_named;
use html\user\user_message;
use html\word\triple as triple;
use html\word\word as word;
use shared\json_fields;

class group extends sandbox_named
{

    /*
     * object vars
     */

    // list of word and triple objects
    private array $lst;

    // memory vs speed optimize vars
    private array $name_pos_lst;
    private bool $lst_dirty;
    private string $name_tip;
    private bool $name_tip_dirty;
    private string $name_link;
    private bool $name_link_dirty;


    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $this->reset();
        parent::__construct($api_json);
    }

    function reset(): void
    {
        $this->name_tip = '';
        $this->name_link = '';
        $this->lst = [];
        $this->set_dirty();
        $this->set_dirty();
    }


    /*
     * set and get
     */

    function set_lst($lst): void
    {
        $this->lst = $lst;
        $this->set_dirty();
    }

    function set_dirty(): void
    {
        $this->lst_dirty = true;
        $this->name_tip_dirty = true;
        $this->name_link_dirty = true;
    }

    function unset_name_tip_dirty(): void
    {
        $this->name_tip_dirty = false;
    }

    function unset_name_link_dirty(): void
    {
        $this->name_link_dirty = false;
    }

    /**
     * @returns array the protected list of phrases
     */
    function lst(): array
    {
        return $this->lst;
    }

    function set_lst_dsp(array $lst): void
    {
        $phr_lst_dsp = array();
        foreach ($lst as $phr) {
            $phr_lst_dsp[] = $phr->dsp_obj();
        }
        $this->set_lst($phr_lst_dsp);
    }

    /**
     * @returns array with all unique phrase ids og this list
     */
    private function name_pos_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $key => $phr) {
                if (!in_array($phr->name(), array_keys($result))) {
                    $result[$phr->name()] = $key;
                }
            }
            $this->name_pos_lst = $result;
            $this->lst_dirty = false;
        } else {
            $result = $this->name_pos_lst;
        }
        return $result;
    }

    /**
     * @returns phrase_list the list of phrases as an object
     */
    function phr_lst(): phrase_list
    {
        $result = new phrase_list();
        $result->set_lst($this->lst());
        return $result;
    }


    /*
     * api
     */

    /**
     * set the vars of this phrase list bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        if (array_key_exists(json_fields::ID, $json_array)) {
            $usr_msg = parent::api_mapper($json_array);
            if (array_key_exists(json_fields::PHRASES, $json_array)) {
                $phr_lst = $json_array[json_fields::PHRASES];
                foreach ($phr_lst as $phr_json) {
                    $this->set_phrase_from_json_array($phr_json);
                }
            }
        } else {
            $usr_msg = new user_message();
            // create phrase group based on the phrase list as fallback
            foreach ($json_array as $phr_json) {
                $this->set_phrase_from_json_array($phr_json);
            }
        }
        return $usr_msg;
    }

    /**
     * @param array $phr_json the json array of a phrase
     * @return void
     */
    private function set_phrase_from_json_array(array $phr_json): void
    {
        $wrd_or_trp = new word();
        if (array_key_exists(json_fields::OBJECT_CLASS, $phr_json)) {
            if ($phr_json[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
                $wrd_or_trp = new triple();
            }
        }
        $wrd_or_trp->api_mapper($phr_json);
        $phr = new phrase();
        $phr->set_obj($wrd_or_trp);
        $this->lst[] = $phr;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(): array
    {
        //$vars = array();
        $phr_lst_vars = array();
        //$vars[json_fields::ID] = $this->id();
        foreach ($this->lst as $phr) {
            $phr_lst_vars[] = $phr->api_array();
        }
        //$vars[json_fields::PHRASES] = $phr_lst_vars;
        return $phr_lst_vars;
    }


    /*
     * modify
     */

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase $phr): bool
    {
        $result = false;
        if (!in_array($phr->id(), $this->name_pos_lst())) {
            $this->lst[] = $phr;
            $this->set_dirty();
            $result = true;
        }
        return $result;
    }


    /*
     * info
     */

    function has_percent(): bool
    {
        return $this->phr_lst()->has_percent();
    }

    /**
     * @return bool if the id of the group is valid
     */
    function is_id_set(): bool
    {
        if ($this->id() != 0) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * base
     */

    /**
     * the name of the phrase group with the tooltip 
     * or the names of the phrases with the tooltip
     * @param phrase_list|null $phr_lst_exclude list of phrases already shown in the header and should be excluded
     * @param string $sep the separator between the phrase names
     * @return string the html code to show the group name
     */
    function name_tip(phrase_list $phr_lst_exclude = null, string $sep = ', '): string
    {
        $result = '';
        if ($this->name_tip_dirty or $phr_lst_exclude != null) {
            if ($this->name() <> '') {
                $result .= parent::name_tip();
            } else {
                $lst_to_show = $this->phr_lst();
                if ($phr_lst_exclude != null) {
                    if (!$phr_lst_exclude->is_empty()) {
                        $lst_to_show->remove($phr_lst_exclude);
                    }
                }
                foreach ($lst_to_show->lst() as $phr) {
                    if ($result <> '') {
                        $result .= $sep;
                    }
                    $result .= $phr->name_tip();
                }
            }
            $this->name_tip = $result;
            $this->name_tip_dirty = false;
        } else {
            $result = $this->name_tip;
        }
        return $result;
    }

    /**
     * @param phrase_list|null $phr_lst_header list of phrases already shown in the header and don't need to be included in the result
     * @return string
     */
    function name_link_list(phrase_list $phr_lst_header = null): string
    {
        $result = '';
        if ($this->name_link_dirty or $phr_lst_header != null) {
            if ($this->name() <> '') {
                $result .= $this->name_link();
            } else {
                $lst_to_show = $this->phr_lst();
                if ($phr_lst_header != null) {
                    if (!$phr_lst_header->is_empty()) {
                        $lst_to_show->remove($phr_lst_header);
                    }
                }
                foreach ($lst_to_show->lst() as $phr) {
                    if ($result <> '') {
                        $result .= ', ';
                    }
                    $result .= $phr->name_link();
                }
            }
            $this->name_link = $result;
            $this->name_link_dirty = false;
        } else {
            $result = $this->name_link;
        }
        return $result;
    }

}
