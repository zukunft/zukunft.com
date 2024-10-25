<?php

/*

    /web/phrase_group_dsp.php - the extension of the phrase group api object to create the HTML code to display a word or triple
    ------------------------

    mainly links to the word and triple display functions


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

namespace html\phrase;

include_once WEB_SANDBOX_PATH . 'sandbox_named.php';

use api\api;
use api\phrase\phrase as phrase_api;
use html\sandbox\sandbox_named as sandbox_named_dsp;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;

class phrase_group extends sandbox_named_dsp
{

    /*
     * object vars
     */

    // list of word_min and triple_min objects
    private array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;
    private string $name_linked;
    private bool $name_dirty;


    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $this->set_name('');
        $this->set_dirty();
        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    /**
     * set the vars of this phrase list bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(api::FLD_ID, $json_array)) {
            parent::set_from_json_array($json_array);
            if (array_key_exists(api::FLD_PHRASES, $json_array)) {
                $phr_lst = $json_array[api::FLD_PHRASES];
                foreach ($phr_lst as $phr_json) {
                    $this->set_phrase_from_json_array($phr_json);
                }
            }
        } else {
            // create phrase group based on the phrase list as fallback
            foreach ($json_array as $phr_json) {
                $this->set_phrase_from_json_array($phr_json);
            }
        }
    }

    /**
     * @param array $phr_json the json array of a phrase
     * @return void
     */
    private function set_phrase_from_json_array(array $phr_json): void
    {
        $wrd_or_trp = new word_dsp();
        if (array_key_exists(api::FLD_PHRASE_CLASS, $phr_json)) {
            if ($phr_json[api::FLD_PHRASE_CLASS] == phrase_api::CLASS_TRIPLE) {
                $wrd_or_trp = new triple_dsp();
            }
        }
        $wrd_or_trp->set_from_json_array($phr_json);
        $phr = new phrase_dsp();
        $phr->set_obj($wrd_or_trp);
        $this->lst[] = $phr;
    }

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
    private function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $phr) {
                if (!in_array($phr->id, $result)) {
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
     * @returns phrase_list_dsp the list of phrases as an object
     */
    function phr_lst(): phrase_list_dsp
    {
        $result = new phrase_list_dsp();
        $result->set_lst($this->lst());
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase_dsp $phr): bool
    {
        $result = false;
        if (!in_array($phr->id(), $this->id_lst())) {
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


    /*
     * display
     */

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases already shown in the header
     * @return string
     */
    function display(phrase_list_dsp $phr_lst_header = null): string
    {
        $result = '';
        if ($this->name_dirty() or $phr_lst_header != null) {
            if ($this->name() <> '') {
                $result .= $this->name();
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
                    $result .= $phr->display();
                }
            }
            $this->unset_name_dirty();
        } else {
            $result = $this->name();
        }
        return $result;
    }

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases already shown in the header and don't need to be include in the result
     * @return string
     */
    function display_linked(phrase_list_dsp $phr_lst_header = null): string
    {
        $result = '';
        if ($this->name_dirty() or $phr_lst_header != null) {
            if ($this->name() <> '') {
                $result .= $this->name();
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
                    $result .= $phr->display_linked();
                }
            }
            $this->unset_name_dirty();
        } else {
            $result = $this->name();
        }
        return $result;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(): array
    {
        //$vars = array();
        $phr_lst_vars = array();
        //$vars[api::FLD_ID] = $this->id();
        foreach ($this->lst as $phr) {
            $phr_lst_vars[] = $phr->api_array();
        }
        //$vars[api::FLD_PHRASES] = $phr_lst_vars;
        return $phr_lst_vars;
    }


    /*
     * info
     */

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

}
