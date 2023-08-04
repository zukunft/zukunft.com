<?php

/*

    /web/phrase/phrase_list.php - create the html code to display a phrase list
    ---------------------------

    TODO create a value matrix based on this phrase list


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

include_once WEB_SANDBOX_PATH . 'list.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';

use api\combine_object_api;
use api\term_api;
use cfg\config;
use cfg\phrase;
use cfg\phrase_list AS phrase_list_db;
use cfg\user;
use html\html_base;
use html\html_selector;
use html\list_dsp;
use html\word\triple;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use cfg\library;

class phrase_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a phrase list based on the given json
     * @param array $json_array an api single object json message
     * @return object a term_dsp with the word or triple set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $trm = null;
        if (array_key_exists(combine_object_api::FLD_CLASS, $json_array)) {
            if ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_WORD) {
                $wrd = new word_dsp();
                $wrd->set_from_json_array($json_array);
                $trm = $wrd->phrase();
            } elseif ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_TRIPLE) {
                $trp = new triple_dsp();
                $trp->set_from_json_array($json_array);
                $trm = $trp->phrase();
            } else {
                log_err('class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected.');
            }
        } else {
            $lib = new library();
            log_err('json key ' . combine_object_api::FLD_CLASS . ' is missing in ' . $lib->dsp_array($json_array));
        }
        return $trm;
    }

    /**
     * get a phrase from the list selected by the id
     * @param int $id the id of the phrase that should be selected
     * @return phrase|null the phrase with the given id or null if nothing is found
     */
    function get_by_id(int $id): ?phrase_dsp
    {
        $result = null;
        foreach ($this->lst() as $phr) {
            if ($result == null) {
                if ($phr->id() == $id) {
                    $result = $phr;
                }
            }
        }
        return $result;
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function display_linked(): string
    {
        $result = '';
        foreach ($this->lst as $phr) {
            if ($result != '' and $phr->display_linked() != '') {
                $result .= ', ';
            }
            $result .= $phr->display_linked();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the plural of the phrases with the most useful link
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function plural(): string
    {
        return $this->display_linked() . 's';
    }

    /**
     * @returns string the html code to display the phrases for a sentence start
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function InitCap(): string
    {
        return strtoupper(substr($this->plural(), 0, 1)) . substr($this->plural(), 1);
    }

    /**
     * @returns string the html code to display the phrases as a headline
     */
    function headline(): string
    {
        $html = new html_base();
        return $html->text_h2($this->InitCap());
    }


    /*
     * info
     */

    /**
     * @return bool true if one of the phrases is of type percent
     */
    function has_percent(): bool
    {
        $result = false;
        foreach ($this->lst as $phr) {
            if ($phr->is_percent()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * modification
     */

    /**
     * removes all terms from this list that are not in the given list
     * @param phrase_list $new_lst the terms that should remain in this list
     * @returns phrase_list with the phrases of this list and the new list
     */
    function intersect(phrase_list $new_lst): phrase_list
    {
        if (!$new_lst->is_empty()) {
            if ($this->is_empty()) {
                $this->set_lst($new_lst->lst);
            } else {
                // next line would work if array_intersect could handle objects
                // $this->lst = array_intersect($this->lst, $new_lst->lst());
                $found_lst = new phrase_list();
                foreach ($new_lst->lst() as $phr) {
                    if (in_array($phr->id(), $this->id_lst())) {
                        $found_lst->add_phrase($phr);
                    }
                }
                $this->set_lst($found_lst->lst);
            }
        }
        return $this;
    }

    /**
     * @returns phrase_list with the phrases that are used in all values of the list
     */
    protected function common_phrases(): phrase_list
    {
        // get common words
        $common_phr_lst = new phrase_list();
        foreach ($this->lst as $val) {
            if ($val != null) {
                if ($val->phr_lst() != null) {
                    if ($val->phr_lst()->lst != null) {
                        $common_phr_lst->intersect($val->phr_lst());
                    }
                }
            }
        }
        return $common_phr_lst;
    }

    /**
     * @return phrase_dsp|null the dominate phrase of the list
     * used to guess which related phrase a human user might use next
     * if no phrase is dominant, the phrase is selected by the parent phrase
     */
    function mainly(): ?phrase_dsp
    {
        global $db_con;
        $phr = null;
        if ($this->count() > 1) {
            $cfg = new config();
            // TODO get from frontend config
            // $is_dominant_pct = $cfg->get(config::MIN_PCT_OF_PHRASES_TO_PRESELECT, $db_con);
            $is_dominant_pct = 0.3;
            $count_lst = array_count_values($this->id_lst());
            sort($count_lst);
            if (($count_lst[0] / $this->count()) > $is_dominant_pct) {
                $id = $count_lst[0];
                $phr = $this->get_by_id($id);
            }
        }
        return $phr;
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add_phrase(phrase_dsp $phr): bool
    {
        return parent::add_obj($phr);
    }

    /**
     * add a phrase or ... to the list also if it is already part of the list
     */
    function add(phrase_dsp $phr): void
    {
        parent::add_always($phr);
    }

    /**
     * remove all phrases of the given list from this list
     * @param phrase_list $del_lst of phrases that should be deleted
     * @return phrase_list_dsp with the remaining phrases
     */
    function remove(phrase_list_dsp $del_lst): phrase_list_dsp
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst = array_intersect($this->lst, $new_lst->lst());
            $remain_lst = new phrase_list_dsp();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $del_lst->id_lst())) {
                    $remain_lst->add_phrase($phr);
                }
            }
            $this->set_lst($remain_lst->lst);
        }
        return $this;
    }

    /**
     * @return string one string with all names of the list and reduced in size mainly for debugging
     * this function is called from dsp_id, so no other call is allowed
     */
    function dsp_name(): string
    {
        global $debug;
        $lib = new library();

        $name_lst = $this->names();
        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst);
            }
            $result .= '"';
        }

        return $result;
    }

    /**
     * return one string with all names of the list without high quotes for the user, but not necessary as a unique text
     * e.g. >Company Zurich< can be either >"Company Zurich"< or >"Company" "Zurich"<, means either a triple or two words
     *      but this "short" form probably confuses the user less and
     *      if the user cannot change the tags anyway the saving of a related value is possible
     * @return string one string with all names of the list
     */
    function name(): string
    {
        $name_lst = $this->names();
        return '"' . implode('","', $name_lst) . '"';
    }

    /**
     * @return array with all phrase names in alphabetic order
     * this function is called from dsp_id, so no call of another function is allowed
     * TODO move to a parent object for phrase list and term list
     */
    function names(): array
    {
        $name_lst = array();
        foreach ($this->lst as $phr) {
            if ($phr != null) {
                $name_lst[] = $phr->name();
            }
        }
        // TODO allow to fix the order
        asort($name_lst);
        return $name_lst;
    }

    /**
     * @return array all phrases that are part of given list and this list
     */
    function common(array $filter_lst): array
    {
        $result = array();
        $lib = new library();
        if (count($this->lst) > 0) {
            foreach ($this->lst as $phr) {
                if (isset($phr)) {
                    if (in_array($phr, $filter_lst)) {
                        $result[] = $phr;
                    }
                }
            }
            $this->lst = $result;
            $this->id_lst();
        }
        log_debug($lib->dsp_count($this->lst));
        return $result;
    }

    /**
     * TODO review
     * offer the user to add a new value for these phrases
     * similar to value.php/btn_add
     */
    function btn_add_value($back)
    {
        $result = \html\btn_add_value($this, Null, $back);
        /*
        zu_debug('phrase_list->btn_add_value');
        $val_btn_title = '';
        $url_phr = '';
        if (!empty($this->lst)) {
          $val_btn_title = "add new value similar to ".htmlentities($this->name());
        } else {
          $val_btn_title = "add new value";
        }
        $url_phr = $this->id_url_long();

        $val_btn_call  = '/http/value_add.php?back='.$back.$url_phr;
        $result .= \html\btn_add ($val_btn_title, $val_btn_call);
        zu_debug('phrase_list->btn_add_value -> done');
        */
        return $result;
    }

    /**
     * TODO review
     * shows all phrases that are part of a list
     * e.g. used to display all phrases linked to a word
     * @returns string the html code to edit a linked word
     */
    function dsp_graph(phrase $root_phr, string $back = ''): string
    {
        log_debug();
        $result = '';

        // loop over the link types
        if ($this->lst == null) {
            $result .= 'Nothing linked to ' . $root_phr->dsp_name() . ' until now. Click here to link it.';
        } else {
            $phr_lst = new phrase_list_db(new user());
            $phr_lst->set_by_api_json($this->api_array());
            $wrd_lst = $phr_lst->wrd_lst_all();
            $wrd_lst_dsp = $wrd_lst->dsp_obj();
            $result .= $wrd_lst_dsp->tbl($back);
            foreach ($this->lst as $phr) {
                // show the RDF graph for this verb
                $phr->name();
            }
        }

        return $result;
    }

}
