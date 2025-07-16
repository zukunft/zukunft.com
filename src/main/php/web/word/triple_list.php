<?php

/*

    web/word/triple_list_dsp.php - a list function to create the HTML code to display a triple list
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

namespace html\word;

include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'triple_list.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';

use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use html\sandbox\list_dsp;
use html\styles;
use html\user\user_message;
use html\word\triple as triple_dsp;
use html\word\triple_list as triple_list_dsp;
use shared\enum\foaf_direction;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;

class triple_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the triples based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new triple_dsp());
    }


    /*
     * display
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the triple names with html links
     * ex. names_linked
     */
    function display(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the triple names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst() as $wrd) {
            if (!$wrd->is_hidden()) {
                $result[] = $wrd->name_linked($back);
            }
        }
        return $result;
    }

    /**
     * show all triples of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @param bool $add_btn set to true for eas allow of similar triples
     * @return string the html code with all triples of the list
     */
    function tbl(string $back = '', bool $add_btn = false): string
    {
        $html = new html_base();
        $cols = '';
        $last_trp = null;
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst() as $trp) {
            $lnk = $trp->name_link($back);
            $cols .= $html->td($lnk);
            $last_trp = $trp;
        }
        if ($add_btn) {
            $add_trp = $this->suggested();
            $add_url = $add_trp->btn_add($back);
            $cols .= $html->td($add_url);
        }
        return $html->tbl($html->tr($cols), styles::STYLE_BORDERLESS);
    }

    /**
     * TODO move the relevant parts to other functions
     * shows all words the link to the given word
     * returns the html code to select a word that can be edited
     */
    function graph(string $back = ''): string
    {
        global $vrb_cac;

        $html = new html_base();
        $result = '';

        // check the all minimal input parameters
        if (isset($this->wrd)) {
            log_debug('graph->display for ' . $this->wrd->name() . ' called from ' . $back);
        }
        $prev_verb_id = 0;

        // loop over the graph elements
        foreach (array_keys($this->lst()) as $lnk_id) {
            // reset the vars
            $directional_link_type_id = 0;

            $lnk = $this->get($lnk_id);
            // get the next link to detect if there is more than one word linked with the same link type
            // TODO check with a unit test if last element is used
            if ($this->count() - 1 > $lnk_id) {
                $next_lnk = $this->get($lnk_id + 1);
            } else {
                $next_lnk = $lnk;
            }

            // display type header
            if (!$lnk->has_verb()) {
                log_warning('graph->display type is missing');
            } else {
                if ($lnk->verb_id() <> $prev_verb_id) {
                    log_debug('graph->display type "' . $lnk->verb()->name() . '"');

                    // select the same side of the verb
                    if ($this->direction == foaf_direction::DOWN) {
                        $directional_link_type_id = $lnk->verb()->id();
                    } else {
                        $directional_link_type_id = $lnk->verb()->id() * -1;
                    }

                    // display the link type
                    if ($lnk->verb()->id() == $next_lnk->verb()->id()) {
                        if ($this->wrd != null) {
                            $result .= $this->wrd->plural();
                        }
                        if ($this->direction == foaf_direction::DOWN) {
                            $result .= " " . $lnk->verb()->rev_plural;
                        } else {
                            $result .= " " . $lnk->verb()->plural();
                        }
                    } else {
                        $result .= $this->wrd->name();
                        if ($this->direction == foaf_direction::DOWN) {
                            $result .= " " . $lnk->verb()->reverse();
                        } else {
                            $result .= " " . $lnk->verb()->name;
                        }
                    }
                }
                $result .= $html->dsp_tbl_start_half();
                $prev_verb_id = $lnk->verb()->id();

                // display the word
                if ($lnk->fob() == null) {
                    log_warning('graph->display from is missing');
                } else {
                    log_debug('word->dsp_graph display word ' . $lnk->from_name());
                    $result .= '  <tr>' . "\n";
                    if ($lnk->tob() != null) {
                        $dsp_obj = $lnk->tob()->get_dsp_obj();
                        $result .= $dsp_obj->dsp_tbl_cell(0);
                    }
                    $lnk_dsp = new triple_dsp($lnk->api_json());
                    $result .= $lnk_dsp->btn_edit($lnk->fob()->dsp_obj());
                    if ($lnk->fob() != null) {
                        $dsp_obj = $lnk->fob()->get_dsp_obj();
                        $result .= $dsp_obj->dsp_unlink($lnk->id());
                    }
                    $result .= '  </tr>' . "\n";
                }

                // use the last word as a sample for the new word type
                $last_linked_word_id = 0;
                if ($lnk->verb()->id() == $vrb_cac->id(verbs::FOLLOW)) {
                    $last_linked_word_id = $lnk->to()->id();
                }

                // in case of the verb "following" continue the series after the last element
                $start_id = 0;
                if ($lnk->verb()->id() == $vrb_cac->id(verbs::FOLLOW)) {
                    $start_id = $last_linked_word_id;
                    // and link with the same direction (looks like not needed!)
                    /* if ($directional_link_type_id > 0) {
                      $directional_link_type_id = $directional_link_type_id * -1;
                    } */
                } else {
                    if ($lnk->fob() == null) {
                        log_warning('graph->display from is missing');
                    } else {
                        $start_id = $lnk->fob()->id(); // to select a similar word for the verb following
                    }
                }

                if ($lnk->verb()->id() <> $next_lnk->verb()->id()) {
                    if ($lnk->fob() == null) {
                        log_warning('graph->display from is missing');
                    } else {
                        $start_id = $lnk->fob()->id();
                    }
                    // give the user the possibility to add a similar word
                    $result .= '  <tr>';
                    $result .= '    <td>';
                    $result .= '      ' . \html\btn_add("Add similar word", '/http/word_add.php?verb=' .
                            $directional_link_type_id . '&word=' . $start_id . '&type=' . $lnk->tob()->type_id . '&back=' . $start_id);
                    $result .= '    </td>';
                    $result .= '  </tr>';

                    $result .= $html->dsp_tbl_end();
                    $result .= '<br>';
                }
            }
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param triple_list_dsp $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(triple_list_dsp $del_lst): void
    {
        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst() as $wrd) {
                if (!in_array($wrd->id(), $lst_ids)) {
                    $result[] = $wrd;
                }
            }
            $this->set_lst($result);
        }
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @return triple_list_dsp with the all triples of the give type
     */
    private function filter(string $type): triple_list_dsp
    {
        $result = new triple_list_dsp();
        foreach ($this->lst() as $wrd) {
            if ($wrd->is_type($type)) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all time triples from this list of triples
     */
    function time_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type_shared::TIME);
    }

    /**
     * get all measure triples from this list of triples
     */
    function measure_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type_shared::MEASURE);
    }

    /**
     * get all scaling triples from this list of triples
     */
    function scaling_lst(): triple_list_dsp
    {
        $result = new triple_list_dsp();
        foreach ($this->lst() as $wrd) {
            if ($wrd->is_scaling()) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all measure and scaling triples from this list of triples
     * @returns triple_list_dsp triples that are usually shown after a number
     */
    function measure_scale_lst(): triple_list_dsp
    {
        $scale_lst = $this->scaling_lst();
        $measure_lst = $this->measure_lst();
        $measure_lst->merge($scale_lst);
        return $measure_lst;
    }

    /**
     * get all measure triples from this list of triples
     */
    function percent_lst(): triple_list_dsp
    {
        return $this->filter(phrase_type_shared::PERCENT);
    }

    /**
     * like names_linked, but without measure and time triples
     * because measure triples are usually shown after the number
     * TODO call this from the display object t o avoid casting again
     * @returns triple_list_dsp a triple
     */
    function ex_measure_and_time_lst(): triple_list_dsp
    {
        $wrd_lst_ex = clone $this;
        $wrd_lst_ex->ex_time();
        $wrd_lst_ex->ex_measure();
        $wrd_lst_ex->ex_scaling();
        $wrd_lst_ex->ex_percent(); // the percent sign is normally added to the value
        return $wrd_lst_ex;
    }

    /**
     * Exclude all time triples from this triple list
     */
    function ex_time(): void
    {
        $this->diff($this->time_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_measure(): void
    {
        $this->diff($this->measure_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_scaling(): void
    {
        $this->diff($this->scaling_lst());
    }

    /**
     * Exclude all measure triples from this triple list
     */
    function ex_percent(): void
    {
        $this->diff($this->percent_lst());
    }

    /**
     * @return phrase_list_dsp with all from phrases
     */
    function from_phrase_list(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        foreach ($this->lst() as $trp) {
            $lst->add($trp->from);
        }
        return $lst;
    }

    /**
     * @return phrase_list_dsp with all from phrases
     */
    function to_phrase_list(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        foreach ($this->lst() as $trp) {
            $lst->add($trp->to);
        }
        return $lst;
    }

    function suggested(): triple_dsp
    {
        $trp = new triple_dsp();
        $from_lst = $this->from_phrase_list();
        $from_phr = $from_lst->mainly();
        if ($from_phr != null) {
            $trp->set_from($from_phr);
        }
        // TODO preset verb
        $to_lst = $this->to_phrase_list();
        $to_phr = $to_lst->mainly();
        if ($to_phr != null) {
            $trp->set_to($to_phr);
        }
        return $trp;
    }

    /**
     * @return array with all triple names in alphabetic order
     * this function is called from dsp_id, so no call of another function is allowed
     * TODO move to a parent object for triple list and term list
     */
    function names(): array
    {
        $name_lst = array();
        foreach ($this->lst() as $phr) {
            if ($phr != null) {
                $name_lst[] = $phr->name();
            }
        }
        // TODO allow to fix the order
        asort($name_lst);
        return $name_lst;
    }

}
