<?php

/*

    /web/value_list.php - the display extension of the api value list object
    ------------------

    to creat the HTML code to display a list of values


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

namespace html;

use api\phrase_list_api;
use result_list;
use model\phrase;
use model\word_list;

class value_list_dsp extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a value object based on the given json
     * @param array $json_array an api single object json message
     * @return object a value set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new value_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(value_dsp $val): bool
    {
        $result = false;
        if (!in_array($val->id(), $this->id_lst())) {
            $this->lst[] = $val;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * @returns value_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): value_list_dsp
    {
        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $val) {
            if ($val != null) {
                $val_dsp = $val->dsp_obj();
                $lst_dsp[] = $val_dsp;
            }
        }

        return new value_list_dsp($lst_dsp);
    }

    /**
     * @param phrase_list_api|null $context_phr_lst list of phrases that are already known to the user by the context of this table and that does not need to be shown to the user again
     * @param string $back
     * @return string the html code to show the values as a table to the user
     */
    function table(phrase_list_api $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;

        // get the common phrases of the value list e.g. inhabitants, 2019
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        // TODO make the replacement language and user specific
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->dsp_obj()->dsp_link();
        }

        // display the single values
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $val) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($val->name_linked($common_phrases));
            $row .= $html->td($val->value_linked($back));
            $rows .= $html->tr($row);
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF);
    }

    /**
     * return the html code to display all values related to a given word
     * $phr->id is the related word that should not be included in the display
     * $this->user()->id is a parameter, because the viewer must not be the owner of the value
     * TODO move remaining parts to the table() function
     * TODO add back
     */
    function table_old(string $back): string
    {
        $result = '';
        $html = new html_base();

        log_debug();
        $common_phr_ids = array();

        // display the common words
        if (!empty($common_phr_ids)) {
            $common_phr_lst = new word_list_dsp();
            //$common_phr_lst->load_by_ids($common_phr_ids);
            $result .= ' in (' . implode(",", $common_phr_lst->names_linked()) . ')<br>';
        }

        // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
        $result .= $html->dsp_tbl_start();

        // the reused button object
        $btn = new button;

        // to avoid repeating the same words in each line and to offer a useful "add new value"
        $last_phr_lst = array();

        log_debug('add new button');
        foreach ($this->lst as $val) {
            //$this->user()->id()  = $val->usr->id;

            // get the words
            $val->load_phrases();
            if (isset($val->phr_lst)) {
                $val_phr_lst = $val->phr_lst;

                // remove the main word from the list, because it should not be shown on each line
                log_debug('remove main ' . $val->id);
                $dsp_phr_lst = $val_phr_lst->dsp_obj();
                log_debug('cloned ' . $val->id);
                if (isset($this->phr)) {
                    if (isset($this->phr->id)) {
                        $dsp_phr_lst->diff_by_ids(array($this->phr->id));
                    }
                }
                log_debug('removed ' . $this->phr->id);
                $dsp_phr_lst->diff_by_ids($common_phr_ids);
                // remove the words of the previous row, because it should not be shown on each line
                if (isset($last_phr_lst->ids)) {
                    $dsp_phr_lst->diff_by_ids($last_phr_lst->ids);
                }

                //if (isset($val->time_phr)) {
                log_debug('add time ' . $val->id);
                if ($val->time_phr != null) {
                    if ($val->time_phr->id > 0) {
                        $time_phr = new phrase($val->usr);
                        $time_phr->load_by_id($val->time_phr->id());
                        $val->time_phr = $time_phr;
                        $dsp_phr_lst->add($time_phr);
                        log_debug('add time word ' . $val->time_phr->name());
                    }
                }

                $result .= '  <tr>';
                $result .= '    <td>';
                log_debug('words ' . $val->id);
                $result .= '      ' . $dsp_phr_lst->name_linked() . ' <a href="/http/value_edit.php?id=' . $val->id . '&back=' . $this->phr->id . '">' . $val->dsp_obj()->val_formatted() . '</a>';
                log_debug('linked words ' . $val->id . ' done');
                // to review
                // list the related results
                $res_lst = new result_list($this->user());
                $res_lst->load_by_val($val);
                $result .= $res_lst->frm_links_html();
                $result .= '    </td>';
                log_debug('formula results ' . $val->id . ' loaded');

                if ($last_phr_lst != $val_phr_lst) {
                    $last_phr_lst = $val_phr_lst;
                    $result .= '    <td>';
                    $result .= \html\btn_add_value($val_phr_lst, Null, $this->phr->id);

                    $result .= '    </td>';
                }
                $result .= '    <td>';
                $result .= '      ' . $btn->edit_value($val_phr_lst, $val->id, $this->phr->id);
                $result .= '    </td>';
                $result .= '    <td>';
                $result .= '      ' . $btn->del_value($val_phr_lst, $val->id, $this->phr->id);
                $result .= '    </td>';
                $result .= '  </tr>';
            }
        }
        log_debug('add new button done');

        $result .= $html->dsp_tbl_end();

        // allow the user to add a completely new value
        log_debug('new');
        if (empty($common_phr_ids)) {
            $common_phr_lst = new word_list($this->user());
            $common_phr_ids[] = $this->phr->id;
            $common_phr_lst->load_by_ids($common_phr_ids);
        }

        $common_phr_lst = $common_phr_lst->phrase_lst();

        // TODO review probably wrong call from /var/www/default/src/main/php/model/view/view.php(267): view_component_dsp->all(Object(word_dsp), 291, 17
        /*
        if (get_class($this->phr) == word::class or get_class($this->phr) == word_dsp::class) {
            $this->phr = $this->phr->phrase();
        }
        */
        if (isset($common_phr_lst)) {
            if (!empty($common_phr_lst->lst())) {
                $common_phr_lst->add($this->phr);
                $phr_lst_dsp = $common_phr_lst->dsp_obj();
                $result .= $phr_lst_dsp->btn_add_value($back);
            }
        }

        log_debug("done");

        return $result;
    }

}
