<?php

/*

    /web/phrase/term_list_dsp.php - the display extension of the api phrase list object
    -----------------------------

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

use api\combine_object_api;
use api\term_api;
use html\html_selector;
use html\list_dsp;
use html\phrase\term as term_dsp;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\formula\formula as formula_dsp;
use html\verb\verb as verb_dsp;
use cfg\formula;
use cfg\library;
use cfg\triple;
use cfg\verb;
use cfg\word;

include_once WEB_SANDBOX_PATH . 'list.php';

class term_list extends list_dsp
{


    /*
     * set and get
     */

    /**
     * set the vars of a term object based on the given json
     * @param array $json_array an api single object json message
     * @return object a term_dsp with the word, triple, formula or verb set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $trm = null;
        if (array_key_exists(combine_object_api::FLD_CLASS, $json_array)) {
            if ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_WORD) {
                $wrd = new word_dsp();
                $wrd->set_from_json_array($json_array);
                $trm = $wrd->term();
            } elseif ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_TRIPLE) {
                $trp = new triple_dsp();
                $trp->set_from_json_array($json_array);
                $trm = $trp->term();
            } elseif ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_FORMULA) {
                $frm = new formula_dsp();
                $frm->set_from_json_array($json_array);
                $trm = $frm->term();
            } elseif ($json_array[combine_object_api::FLD_CLASS] == term_api::CLASS_VERB) {
                $vrb = new verb_dsp();
                $vrb->set_from_json_array($json_array);
                $trm = $vrb->term();
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
    function display(): string
    {
        $result = '';
        foreach ($this->lst as $trm) {
            if ($result != '' and $trm->display() != '') {
                $result .= ', ';
            }
            $result .= $trm->display();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function display_linked(): string
    {
        $result = '';
        foreach ($this->lst as $trm) {
            if ($result != '' and $trm->display_linked() != '') {
                $result .= ', ';
            }
            $result .= $trm->display_linked();
        }
        return $result;
    }

    function add(object $obj): bool
    {
        return $this->add_obj($obj);
    }
}
