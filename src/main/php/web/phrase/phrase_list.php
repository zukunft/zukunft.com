<?php

/*

    web/phrase/phrase_list.php - create the html code to display a phrase list
    --------------------------

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

include_once WEB_SANDBOX_PATH . 'sandbox_list_named.php';
//include_once WEB_HELPER_PATH . 'config.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
//include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_USER_PATH . 'user_message.php';
//include_once WEB_VERB_PATH . 'verb.php';
include_once WEB_VERB_PATH . 'verb_list.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'word_list.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';

use html\formula\formula;
use html\helper\config;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use html\rest_ctrl as api_dsp;
use html\sandbox\sandbox_list_named;
use html\user\user_message;
use html\verb\verb;
use html\verb\verb_list;
use html\word\triple;
use html\word\word;
use html\word\word_list;
use shared\api;
use shared\enum\foaf_direction;
use shared\library;

class phrase_list extends sandbox_list_named
{

    /*
     * set and get
     */

    /**
     * set the vars of a phrase list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new phrase_dsp());
    }


    /*
     * load
     */

    /**
     * add the phrases related to the given phrase to the list
     * @param phrase_dsp $phr
     * @param foaf_direction $direction
     * @param verb_list|null $link_types
     * @return bool
     */
    function load_related(phrase_dsp $phr, foaf_direction $direction, ?verb_list $link_types = null): bool
    {
        $result = false;

        // TODO move the
        $api = new api_dsp();
        $data = array();
        $data[api::URL_VAR_PHRASE] = $phr->id();
        $data[api::URL_VAR_DIRECTION] = $direction->value;
        $data[api::URL_VAR_LEVELS] = 1;
        $json_body = $api->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * add the phrases related to the given formula to the list
     * @param formula $frm
     * @return bool
     */
    function load_by_formula(formula $frm): bool
    {
        $result = false;

        // TODO move the
        $api = new api_dsp();
        $data = array();
        $data[api::URL_VAR_FORMULAS] = $frm->id();
        $json_body = $api->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * get all phrases that are connected to the given phrase
     * selected by the given verb
     * @param phrase $phr the parent phrase
     * @param verb|null $vrb the verb to filter the child phrases
     * @return phrase_list the filtered children
     */
    function children(phrase $phr, verb|null $vrb = null): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if ($trp->verb()->id() == $vrb?->id() or $vrb == null) {
                    if ($trp->from()->id() == $phr->id()) {
                        $result->add($trp);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get all phrases that are connected to the given phrase
     * selected by the given verb
     * @param phrase $phr the parent phrase
     * @param verb|null $vrb the verb to filter the child phrases
     * @return phrase_list the filtered children
     */
    function parents(phrase $phr, verb|null $vrb = null): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if ($trp->verb()->id() == $vrb?->id() or $vrb == null) {
                    if ($trp->to()->id() == $phr->id()) {
                        $result->add($trp);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get the most useful time for the given phrases
     * similar to the backend function with the same name
     * TODO: review
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return phrase|null with the most useful time phrase
     */
    function assume_time(?term_list $trm_lst = null): ?phrase
    {
        $time_phr = null;
        $wrd_lst = $this->wrd_lst_all();
        $time_wrd = $wrd_lst->assume_time($trm_lst);
        if (isset($time_wrd)) {
            $time_phr = $time_wrd;
        }
        return $time_phr;
    }

    /**
     * build a word list including the triple words or in other words flatten the list e.g. for parent inclusions
     * @return word_list with all words of the phrases split into single words
     */
    function wrd_lst_all(): word_list
    {
        log_debug('phrase_list->wrd_lst_all for ' . $this->dsp_id());

        $wrd_lst = new word_list();

        // fill the word list
        foreach ($this->lst() as $phr) {
            if ($phr->obj() == null) {
                log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
            } else {
                if ($phr->obj()->id() == 0) {
                    log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
                } else {
                    if ($phr->name() == '') {
                        $phr->load();
                        log_warning('Phrase ' . $phr->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                    }
                    // TODO check if old can ge removed: if ($phr->id() > 0) {
                    if (get_class($phr->obj()) == word::class) {
                        $wrd_lst->add($phr->obj());
                    } elseif (get_class($phr->obj()) == triple::class) {
                        // use the recursive triple function to include the foaf words
                        $sub_wrd_lst = $phr->obj()->wrd_lst();
                        foreach ($sub_wrd_lst->lst() as $wrd) {
                            if ($wrd->name() == '') {
                                $wrd->load_by_id($wrd->id());
                                log_warning('Word ' . $wrd->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                            }
                            $wrd_lst->add($wrd);
                        }
                    } else {
                        log_err('The phrase list ' . $this->dsp_id() . ' contains ' . $phr->obj()->dsp_id() . ', which is neither a word nor a phrase, but it is a ' . get_class($phr->obj), 'phrase_list->wrd_lst_all');
                    }
                }
            }
        }

        log_debug($wrd_lst->dsp_id());
        return $wrd_lst;
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function name_link(): string
    {
        $result = '';
        $this->sort_by_name();
        foreach ($this->lst() as $phr) {
            if ($result != '' and $phr->name_link() != '') {
                $result .= ', ';
            }
            $result .= $phr->name_link();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the plural of the phrases with the most useful link
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function plural(): string
    {
        return $this->name_link() . 's';
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
     * the old long form to encode
     */
    function id_url_long(): string
    {
        $lib = new library();
        return $lib->ids_to_url($this->id_lst(), "phrase");
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
        foreach ($this->lst() as $phr) {
            if ($phr->is_percent()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * modify
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
                $this->set_lst($new_lst->lst());
            } else {
                // next line would work if array_intersect could handle objects
                // $this->lst = array_intersect($this->lst, $new_lst->lst());
                $found_lst = new phrase_list();
                foreach ($new_lst->lst() as $phr) {
                    $id = $phr->id();
                    $id_lst = $this->id_lst();
                    if (in_array($id, $id_lst)) {
                        $found_lst->add_phrase($phr);
                    }
                }
                $this->set_lst($found_lst->lst());
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
        foreach ($this->lst() as $val) {
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
            $this->set_lst($remain_lst->lst());
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
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }
            $result .= '"';
        }

        return $result;
    }

    /**
     * @return array all phrases that are part of given list and this list
     */
    function common(array $filter_lst): array
    {
        $result = array();
        $lib = new library();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                if (isset($phr)) {
                    if (in_array($phr, $filter_lst)) {
                        $result[] = $phr;
                    }
                }
            }
            $this->set_lst($result);
            $this->id_lst();
        }
        log_debug($lib->dsp_count($this->lst()));
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
    function dsp_graph(phrase_dsp $root_phr, string $back = ''): string
    {
        log_debug();
        $result = '';

        // loop over the link types
        if ($this->lst() == null) {
            $result .= 'Nothing linked to ' . $root_phr->name() . ' until now. Click here to link it.';
        } else {
            $phr_lst = new phrase_list();
            $phr_lst->set_from_json($this->api_json());
            $wrd_lst = $phr_lst->wrd_lst_all();
            $result .= $wrd_lst->tbl($back);
            foreach ($this->lst() as $phr) {
                // show the RDF graph for this verb
                $phr->name();
            }
        }

        return $result;
    }

}
