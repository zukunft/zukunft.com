<?php

/*

    web/sandbox/sandbox_list.php - a base object for a named list objects
    ----------------------------


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

namespace html\sandbox;

include_once WEB_SANDBOX_PATH . 'sandbox_list.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_WORD_PATH . 'triple_list.php';
include_once WEB_USER_PATH . 'user.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'word_list.php';

use html\phrase\phrase;
use html\phrase\term;
use html\word\triple_list;
use html\user\user_message;
use html\word\triple;
use html\word\word;
use html\word\word_list;

class sandbox_list_named extends list_dsp
{

    // memory vs speed optimize vars for faster finding the list position by the object name
    private array $name_pos_lst;
    private bool $lst_name_dirty;

    /*
     * construct and map
     */

    /**
     * @param string|null $api_json string with the api json message to fill the list
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     */
    function __construct(?string $api_json = null)
    {
        $this->name_pos_lst = array();
        $this->lst_name_dirty = false;

        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    public function set_lst_dirty(): bool
    {
        parent::set_lst_dirty();
        $this->lst_name_dirty = true;
        return true;
    }

    /**
     * to be called after the lists have been updated
     * but the index list have not yet been updated
     */
    protected function set_lst_clean(): void
    {
        $this->lst_name_dirty = false;
    }


    /*
     * modify
     */

    /**
     * add one named object e.g. a word to the list, but only if it is not yet part of the list
     * @param sandbox_named|triple|phrase|term|null $to_add the named object e.g. a word object that should be added
     * @returns bool true the object has been added
     */
    function add(sandbox_named|triple|phrase|term|null $to_add): bool
    {
        $result = false;
        if ($to_add != null) {
            if ($this->is_empty()) {
                $result = $this->add_obj($to_add);
            } else {
                if (!in_array($to_add->id(), $this->ids())) {
                    if ($to_add->id() != 0) {
                        $result = $this->add_obj($to_add);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * add a named object to the list that does not yet have an id but has a name
     * @param sandbox_named|triple|phrase|term|null $to_add the named user sandbox object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    function add_by_name(sandbox_named|triple|phrase|term|null $to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        if (!in_array($to_add->name(), array_keys($this->name_pos_lst())) or $allow_duplicates) {
            // if a sandbox object has a name, but not (yet) an id, add it nevertheless to the list
            if ($to_add->id() == null) {
                $this->set_lst_dirty();
            }
            $result = parent::add_obj($to_add, $allow_duplicates);
        }
        return $result;
    }

    /**
     * add the names and other variables from the given list and add missing words, triples, ...
     * select the related object by the id
     *
     * @param sandbox_list_named $lst_new a list of sandbox object e.g. that might have more vars set e.g. the name
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_id(sandbox_list_named $lst_new): user_message
    {
        $usr_msg = new user_message();
        foreach ($lst_new->lst() as $sbx_new) {
            if ($sbx_new->id() != 0 and $sbx_new->name() != '') {
                $sbx_old = $this->get_by_id($sbx_new->id());
                if ($sbx_old != null) {
                    $sbx_old->fill($sbx_new);
                } else {
                    $this->add($sbx_new);
                }
            } else {
                $usr_msg->add_message('id or name of word ' . $sbx_new->dsp_id() . ' missing');
            }
        }
        return $usr_msg;
    }

    /**
     * add the ids and other variables from the given list and add missing words, triples, ...
     * select the related object by the name
     *
     * @param sandbox_list_named $lst_new a list of sandbox object e.g. that might have more vars set e.g. the db id
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_name(sandbox_list_named $lst_new): user_message
    {
        $usr_msg = new user_message();
        foreach ($lst_new->lst() as $sbx_new) {
            if ($sbx_new->id() != 0 and $sbx_new->name() != '') {
                $sbx_old = $this->get_by_name($sbx_new->name());
                if ($sbx_old != null) {
                    $sbx_old->fill($sbx_new);
                } else {
                    $this->add($sbx_new);
                }
            } else {
                $usr_msg->add_message('id or name of word ' . $sbx_new->dsp_id() . ' missing');
            }
        }
        return $usr_msg;
    }


    /*
     * search
     */

    /**
     * find an object from the loaded list by name using the hash
     * should be cast by the child function get_by_name
     *
     * @param string $name the unique name of the object that should be returned
     * @return term|phrase|triple|word|null the found user sandbox object or null if no name is found
     */
    function get_by_name(string $name): term|phrase|triple|word|null
    {
        $key_lst = $this->name_pos_lst();
        $pos = null;
        if (key_exists($name, $key_lst)) {
            $pos = $key_lst[$name];
        }
        if ($pos !== null) {
            return $this->get($pos);
        } else {
            return null;
        }
    }

    /**
     * filters a word list by names
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param array $names with the words that should be removed
     * @returns sandbox_list_named with only the remaining words
     */
    function filter_by_name(array $names): sandbox_list_named
    {
        log_debug('->filter_by_name ' . $this->dsp_id());
        $result = clone $this;
        $result->reset();

        // check and adjust the parameters
        if (count($names) <= 0) {
            log_warning('Phrases to delete are missing.', 'word_list->filter');
        }

        foreach ($this->lst() as $wrd) {
            if (!in_array($wrd->name(), $names)) {
                $result->add_by_name($wrd);
            }
        }

        return $result;
    }

    /**
     * select a word list by names
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2016", "2017"
     *
     * @param array $names with the words that should be removed
     * @returns sandbox_list_named with only the remaining words
     */
    function select_by_name(array $names): sandbox_list_named
    {
        log_debug('->filter_by_name ' . $this->dsp_id());
        $result = clone $this;
        $result->reset();

        // check and adjust the parameters
        if (count($names) <= 0) {
            log_warning('Phrases to delete are missing.', 'word_list->filter');
        }

        foreach ($this->lst() as $wrd) {
            if (in_array($wrd->name(), $names)) {
                $result->add_by_name($wrd);
            }
        }

        return $result;
    }


    /*
     * modify
     */

    /**
     * TODO add a unit test
     * @returns array with all unique names of this list with the keys within this list
     */
    protected function name_pos_lst(): array
    {
        $result = array();
        if ($this->lst_name_dirty) {
            foreach ($this->lst() as $key => $obj) {
                if (!in_array($obj->name(), $result)) {
                    $result[$obj->name()] = $key;
                }
            }
            $this->name_pos_lst = $result;
            $this->lst_name_dirty = false;
        } else {
            $result = $this->name_pos_lst;
        }
        return $result;
    }


}
