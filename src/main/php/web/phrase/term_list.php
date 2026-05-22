<?php

/*

    web/phrase/term_list.php - the display extension of the api phrase list object
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

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::SANDBOX . 'sandbox_list_named.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list_named;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;

class term_list extends sandbox_list_named
{


    /*
     * set and get
     */

    /**
     * set the vars of a term object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new term());
    }


    /**
     * get a word from the term list selected by the word id
     *
     * @param int $id the word id (not the term id!)
     * @return word|null the word object from the list or null
     */
    function word_by_id(int $id): ?word
    {
        $wrd = null;
        $trm = new term();
        $trm->set_term_obj(new word());
        $trm->set_id_from_obj($id, word::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get($trm_id);
            if ($trm != null) {
                $wrd = $trm->get_word();
            }
        }
        return $wrd;
    }

    /**
     * get a triple from the term list selected by the triple id
     *
     * @param int $id the triple id (not the term id!)
     * @return triple|null the triple object from the list or null
     */
    function triple_by_id(int $id): ?triple
    {
        $trp = null;
        $trm = new term();
        $trm->set_term_obj(new triple());
        $trm->set_id_from_obj($id, triple::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get($trm_id);
            if ($trm != null) {
                $trp = $trm->get_triple();
            }
        }
        return $trp;
    }

    /**
     * get a formula from the term list selected by the formula id
     *
     * @param int $id the formula id (not the term id!)
     * @return formula|null the formula object from the list or null
     */
    function formula_by_id(int $id): ?formula
    {
        $frm = null;
        $trm = new term();
        $trm->set_term_obj(new formula());
        $trm->set_id_from_obj($id, formula::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get($trm_id);
            if ($trm != null) {
                $frm = $trm->get_formula();
            }
        }
        return $frm;
    }

    /**
     * get a verb from the term list selected by the verb id
     *
     * @param int $id the verb id (not the term id!)
     * @return verb|null the verb object from the list or null
     */
    function verb_by_id(int $id): ?verb
    {
        $vrb = null;
        $trm = new term();
        $trm->set_term_obj(new verb());
        $trm->set_id_from_obj($id, verb::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get($trm_id);
            if ($trm != null) {
                $vrb = $trm->get_verb();
            }
        }
        return $vrb;
    }


    /*
     * sort
     */

    /**
     * sort this term list in place so that the term with the highest impact is first
     * the impact is the system calculated relevance of the wrapped word, triple, formula or verb
     * @return void
     */
    function sort_by_impact(): void
    {
        $lst = $this->lst();
        usort($lst, fn(term $a, term $b) => $b->impact() <=> $a->impact());
        $this->set_lst($lst);
    }


    /*
     * display
     */

    /**
     * create the html links of the terms ordered by impact, highest impact first
     * e.g. used on the search result page to show the most relevant terms matching the pattern
     * unlike name_link (which sorts by name) the list is sorted by the system calculated impact
     *
     * @param string $back the back trace url for the undo functionality
     * @param int $limit the max number of terms to show
     * @return string the html links of the terms with the highest impact first
     */
    function name_link_by_impact(string $back = '', int $limit = config::LIMIT_SEARCH_LIST): string
    {
        $this->sort_by_impact();
        return implode(', ', $this->names_linked($back, $limit));
    }


    /*
     * base
     */

    /**
     * get a term from the term list selected by the word, triple, formula or verb id
     *
     * @param int $id the word, triple, formula or verb id (not the term id!)
     * @param string $class the word, triple, formula or verb class name
     * @return term|null the word object from the list or null
     */
    function term_by_obj_id(int $id, string $class): ?term
    {
        $trm = new term();
        $trm->set_obj_from_class($class);
        $trm->set_obj_id($id);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get($trm_id);
        }
        return $trm;
    }

}
