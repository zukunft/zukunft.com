<?php

/*

    word_list_dsp.php - a list function to create the HTML code to display a word list
    -----------------

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

include_once WEB_SANDBOX_PATH . 'list_named.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_PHRASE_PATH . 'term_list.php';
//include_once WEB_VALUE_PATH . 'value_list.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'library.php';

use html\phrase\phrase;
use html\phrase\phrase_list;
use html\phrase\term_list;
use html\sandbox\list_named;
use html\styles;
use html\user\user_message;
use html\value\value_list;
use html\html_base;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;

class word_list extends list_named
{

    /*
     * set and get
     */

    /**
     * set the vars of a word object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new word());
    }


    /*
     * cast
     */

    /**
     * convert the word list object into a phrase list object
     * @return phrase_list with all words of this list
     */
    function phrase_lst(): phrase_list
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $phr_lst = new phrase_list();
        foreach ($this->lst() as $phr) {
            if (get_class($phr) == word::class) {
                $phr_lst->add($phr->phrase());
            } elseif (get_class($phr) == phrase::class) {
                $phr_lst->add($phr);
            } else {
                log_err('unexpected object type ' . get_class($phr));
            }
        }
        $phr_lst->id_lst();
        log_debug('done ' . $lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }


    /*
     * table
     */

    /**
     * show all words of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function tbl(string $back = ''): string
    {
        $html = new html_base();
        $cols = '';
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst() as $wrd) {
            $lnk = $wrd->name_link($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), styles::STYLE_BORDERLESS);
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
     * @param word_list $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(word_list $del_lst): void
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
     * @return word_list with the all words of the give type
     */
    private function filter(string $type): word_list
    {
        $result = new word_list();
        foreach ($this->lst() as $wrd) {
            if ($wrd->is_type($type)) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get the most useful time for the given list
     * so either the last time from the word list
     * or the time of the last "real" (reported) value for the word list
     *
     * always returns a phrase to avoid converting in the calling function
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return phrase|null a time phrase
     */
    function assume_time(?term_list $trm_lst = null): ?phrase
    {
        log_debug('for ' . $this->dsp_id());
        $result = null;
        $phr = null;

        if ($this->has_time()) {
            // get the last time from the word list
            $time_phr_lst = $this->time_lst();
            // shortcut, replace with a most_useful function
            foreach ($time_phr_lst->lst() as $time_wrd) {
                if (is_null($phr)) {
                    $phr = $time_wrd;
                    $phr->set_user();
                } else {
                    log_warning("The word list contains more time word than supported by the program.", "word_list->assume_time");
                }
            }
            log_debug('time ' . $phr->name() . ' assumed for ' . $this->name_tip());
        } else {
            // get the time of the last "real" (reported) value for the word list
            $wrd_max_time = $this->max_val_time($trm_lst);
            $phr = $wrd_max_time?->phrase();
        }

        if ($phr != null) {
            log_debug('time used "' . $phr->name() . '" (' . $phr->id() . ')');
            if (get_class($phr) == word::class or get_class($phr) == word::class) {
                $result = $phr->phrase();
            } else {
                $result = $phr;
            }
        } else {
            log_debug('no time found');
        }
        return $result;
    }

    /**
     * @return bool true if a word lst contains a time word
     */
    function has_time(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            if (!$result) {
                if ($wrd->is_time()) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * get the time of the last value related to a word and assigned to a phrase list
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return word|null a time word (or phrase?)
     */
    function max_val_time(?term_list $trm_lst = null): ?word
    {
        $lib = new library();
        $wrd = null;

        if ($trm_lst == null) {
            // load the list of all value related to the word list
            $val_lst = new value_list();
            $val_lst->load_by_phr_lst($this->phrase_lst());
            log_debug($lib->dsp_count($val_lst->lst()) . ' values for ' . $this->dsp_id());

            $time_ids = array();
            foreach ($val_lst->lst() as $val) {
                $val->load_phrases();
                if (isset($val->time_phr)) {
                    log_debug('value (' . $val->number() . ' @ ' . $val->time_phr->name() . ')');
                    if ($val->time_phr->id() > 0) {
                        if (!in_array($val->time_phr->id(), $time_ids)) {
                            $time_ids[] = $val->time_phr->id();
                            log_debug('add word id (' . $val->time_phr->id() . ')');
                        }
                    }
                }
            }

            $time_lst = new word_list();
            if (count($time_ids) > 0) {
                $time_lst->load_by_ids($time_ids);
                $wrd = $time_lst->max_time();
            }
        } else {
            $time_lst = new word_list();
            foreach ($trm_lst->lst() as $trm) {
                if ($trm->is_time()) {
                    $time_lst->add($trm->word());
                }
            }
            $wrd = $time_lst->max_time();
        }

        /*
        // get all values related to the selecting word, because this is probably strongest selection and to save time reduce the number of records asap
        $val = New value;
        $val->wrd_lst = $this;
        $val->usr = $this->user();
        $val->load_by_wrd_lst();
        $value_lst = array();
        $value_lst[$val->id] = $val->number();
        zu_debug('word_list->max_val_time -> ('.implode(",",$value_lst).')');

        if (sizeof($value_lst) > 0) {

          // get all words related to the value list
          $all_word_lst = zu_sql_value_lst_words($value_lst, $this->user()->id());

          // get the time words
          $time_lst = zut_time_lst($all_word_lst);

          // get the most useful (last) time words (replace by a "followed by" sorted list
          ar sort($time_lst);
          $time_keys = array_keys($time_lst);
          $wrd_id = $time_keys[0];
          $wrd = New word;
          if ($wrd_id > 0) {
            $wrd->id = $wrd_id;
            $wrd->usr = $this->user();
            $wrd->load();
          }
        }
        */
        if ($wrd != null) {
            log_debug('done (' . $wrd->name() . ')');
        }
        return $wrd;
    }

    /**
     * @return word the last time word of the word list
     */
    function max_time(): word
    {
        $max_wrd = new word();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $wrd) {
                // TODO replaced by "is following"
                if ($wrd->name() > $max_wrd->name()) {
                    $max_wrd = clone $wrd;
                }
            }
        }
        return $max_wrd;
    }

    /**
     * get all time words from this list of words
     */
    function time_lst(): word_list
    {
        return $this->filter(phrase_type_shared::TIME);
    }

    /**
     * get all measure words from this list of words
     */
    function measure_lst(): word_list
    {
        return $this->filter(phrase_type_shared::MEASURE);
    }

    /**
     * get all scaling words from this list of words
     */
    function scaling_lst(): word_list
    {
        $result = new word_list();
        foreach ($this->lst() as $wrd) {
            if ($wrd->is_scaling()) {
                $result->add($wrd);
            }
        }
        return $result;
    }

    /**
     * get all measure and scaling words from this list of words
     * @returns word_list words that are usually shown after a number
     */
    function measure_scale_lst(): word_list
    {
        $scale_lst = $this->scaling_lst();
        $measure_lst = $this->measure_lst();
        $measure_lst->merge($scale_lst);
        return $measure_lst;
    }

    /**
     * get all measure words from this list of words
     */
    function percent_lst(): word_list
    {
        return $this->filter(phrase_type_shared::PERCENT);
    }

    /**
     * like names_linked, but without measure and time words
     * because measure words are usually shown after the number
     * TODO call this from the display object t o avoid casting again
     * @returns word_list a word
     */
    function ex_measure_and_time_lst(): word_list
    {
        $wrd_lst_ex = clone $this;
        $wrd_lst_ex->ex_time();
        $wrd_lst_ex->ex_measure();
        $wrd_lst_ex->ex_scaling();
        $wrd_lst_ex->ex_percent(); // the percent sign is normally added to the value
        return $wrd_lst_ex;
    }

    /**
     * Exclude all time words from this word list
     */
    function ex_time(): void
    {
        $this->diff($this->time_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_measure(): void
    {
        $this->diff($this->measure_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_scaling(): void
    {
        $this->diff($this->scaling_lst());
    }

    /**
     * Exclude all measure words from this word list
     */
    function ex_percent(): void
    {
        $this->diff($this->percent_lst());
    }

}
