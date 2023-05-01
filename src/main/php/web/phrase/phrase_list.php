<?php

/*

    /web/phrase/phrase_list.php - create the html code to display a phrase list
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

namespace html\phrase;

include_once WEB_SANDBOX_PATH . 'list.php';

use api\combine_object_api;
use api\term_api;
use html\html_base;
use html\html_selector;
use html\list_dsp;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use model\library;

class phrase_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a phrase object based on the given json
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

    /**
     * @returns string the html code to select a phrase out of this list
     */
    function selector(string $name = '', string $form = '', string $label = '', int $selected = 0): string
    {
        $sel = new html_selector;
        $sel->name = $name;
        $sel->form = $form;
        $sel->label = $label;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;

        return $sel->display();
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
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add_phrase(phrase_dsp $phr): bool
    {
        return parent::add_obj($phr);
    }
    function remove(phrase_list $del_lst): phrase_list
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst = array_intersect($this->lst, $new_lst->lst());
            $remain_lst = new phrase_list();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $del_lst->id_lst())) {
                    $remain_lst->add_phrase($phr);
                }
            }
            $this->set_lst($remain_lst->lst);
        }
        return $this;
    }

}
