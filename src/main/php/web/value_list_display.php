<?php

/*

  value_list_display.php - to show a list of values
  ----------------------
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class value_list_dsp extends value_list
{

    // creates a table of all values related to a word and a related word and all the subwords of the related word
    // e.g. for "ABB" ($this->phr) list all values for the cash flow statement ($phr_row)
    function dsp_table($phr_row, $back): string
    {
        $result = '';

        // check the parameters
        if (!isset($this->phr)) {
            $result = log_warning('The main phrase is not set.', "value_list_dsp->dsp_table");
        }
        if ($phr_row->id == 0) {
            $result = log_warning('The main phrase is not selected.', "value_list_dsp->dsp_table");
        }
        if (!isset($phr_row)) {
            $result = log_warning('The row type is not set.', "value_list_dsp->dsp_table");
        }
        if (get_class($phr_row) <> 'word_dsp') {
            $result = log_err('The row is of type ' . get_class($phr_row) . ' but should be a phrase.', "value_list_dsp->dsp_table");
        }
        // if (get_class($phr_row) <> 'phrase') { $result = zu_err('The row is of type '.get_class($phr_row).' but should be a phrase.', "value_list_dsp->dsp_table"); }
        if ($phr_row->id == 0) {
            $result = log_warning('The row type is not selected.', "value_list_dsp->dsp_table");
        }

        // if parameters are fine display the table
        if ($result == '') {
            log_debug('value_list_dsp->dsp_table "' . $phr_row->name . '" for "' . $this->phr->name . '" and user "' . $this->usr->name . '"');

            // init the display vars
            $val_main = null; // the "main" value used as a sample for a new value
            $modal_nbr = 1;   // to create a unique id for each modal form; the total number of modal boxes will not get too high, because the user will only see a limited number of values at once

            // create the table headline e.g. cash flow statement
            log_debug('value_list_dsp->dsp_table all pre head: ' . $phr_row->name);
            $result .= $phr_row->dsp_tbl_row();
            log_debug('value_list_dsp->dsp_table all head: ' . $phr_row->name);
            $result .= '<br>';

            // get all values related to the selectiong word, because this is probably strongest selection and to save time reduce the number of records asap
            $val_lst = $this->phr->val_lst();
            log_debug('value_list_dsp->dsp_table all values: ' . count($val_lst->lst));

            //$val_lst->load_phrases();
            /*foreach ($val_lst->lst AS $val) {
              zu_debug('value_list_dsp->dsp_table value: '.$val->number.' (group '.$val->grp_id.' and time '.$val->time_id.')');
            }*/

            // get all words related to the value list to be able to define the column and the row names
            $phr_lst_all = $val_lst->phr_lst();
            log_debug('value_list_dsp->dsp_table all words: ' . $phr_lst_all->name());

            // get the time words for the column heads
            $all_time_lst = $val_lst->time_lst();
            log_debug('value_list_dsp->dsp_table times: ' . $all_time_lst->name());

            // adjust the time words to display
            $time_lst = $all_time_lst->time_useful();
            log_debug('value_list_dsp->dsp_table times sorted: ' . $time_lst->name());

            // filter the value list by the time words used
            $used_value_lst = $val_lst->filter_by_time($time_lst);
            log_debug('value_list_dsp->dsp_table values in the time period: ' . count($used_value_lst->lst));

            // get the word tree for the left side of the table
            $row_wrd_lst = $phr_row->are_and_contains();
            log_debug('value_list_dsp->dsp_table row words: ' . $row_wrd_lst->name());

            // add potential differentiators to the word tree
            $word_incl_differentiator_lst = $row_wrd_lst->differentiators_filtered($phr_lst_all);
            log_debug('value_list_dsp->dsp_table differentiator words: ' . $word_incl_differentiator_lst->name());
            log_debug('value_list_dsp->dsp_table row words after differentiators added: ' . $row_wrd_lst->name());

            // filter the value list by the row words used
            $row_phr_lst_incl = $row_wrd_lst->phrase_lst();
            log_debug('value_list_dsp->dsp_table row phrase list: ' . $row_phr_lst_incl->name());
            $used_value_lst = $used_value_lst->filter_by_phrase_lst($row_phr_lst_incl);
            log_debug('value_list_dsp->dsp_table used values for all rows: ' . count($used_value_lst->lst));

            // get the common words
            $common_lst = $used_value_lst->common_phrases();
            log_debug('value_list_dsp->dsp_table common: ' . $common_lst->name());

            // get all words not yet part of the table rows, columns or common words
            $xtra_phrases = clone $phr_lst_all;
            if (isset($word_incl_differentiator_lst)) {
                $xtra_phrases->not_in($word_incl_differentiator_lst);
            }
            $xtra_phrases->not_in($common_lst);
            $xtra_phrases->not_in($time_lst->phrase_lst());
            log_debug('value_list_dsp->dsp_table xtra phrase, that might need to be added to each table cell: ' . $xtra_phrases->name());

            // display the common words
            // to do: sort the words and use the short form e.g. in mio. CHF instead of in CHF millios
            if (count($common_lst) > 0) {
                $common_text = '(in ';
                foreach ($common_lst->lst as $common_word) {
                    if ($common_word->id <> $this->phr->id) {
                        $common_text .= $common_word->dsp_tbl_row();
                    }
                }
                $common_text .= ')';
                $result .= dsp_line_small($common_text);
            }
            $result .= '<br>';

            // display the table
            $result .= dsp_tbl_start();
            $result .= '   <colgroup>' . "\n";
            //$result .= '<col span="'.sizeof($time_lst)+1.'">';
            $result .= '    <col span="7">' . "\n";
            $result .= '  </colgroup>' . "\n";
            $result .= '  <tbody>' . "\n";

            // display the column heads
            $result .= '  <tr>' . "\n";
            $result .= '    <th></th>' . "\n";
            foreach ($time_lst->lst as $time_word) {
                $result .= dsp_tbl_head_right($time_word->display($back));
            }
            $result .= '  </tr>' . "\n";

            // temp: display the word tree
            $last_words = '';
            $id = 0; // TODO review and rename
            foreach ($row_wrd_lst->lst as $sub_wrd) {
                $wrd_ids = array();
                $wrd_ids[] = $this->phr->id;
                $wrd_ids[] = $sub_wrd->id;
                foreach ($common_lst->ids as $xtra_id) {
                    if (!in_array($xtra_id, $wrd_ids)) {
                        $wrd_ids[] = $xtra_id;
                    }
                }

                // check if row is empty
                $row_has_value = false;
                $grp = new phrase_group;
                $grp->usr = $this->usr;
                $grp->ids = $wrd_ids;
                $grp->load();
                foreach ($time_lst->lst as $time_wrd) {
                    $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                    if ($tbl_value->number <> "") {
                        $row_has_value = true;
                        $val_main = $tbl_value;
                    }
                }

                if (!$row_has_value) {
                    log_debug('value_list_dsp->dsp_table no value found for ' . $grp->name() . ' skip row');
                } else {
                    $result .= '  <tr>' . "\n";
                    $result .= $sub_wrd->dsp_tbl(0);

                    foreach ($time_lst->lst as $time_wrd) {
                        $val_wrd_ids = $wrd_ids;
                        if (!in_array($time_wrd->id, $val_wrd_ids)) {
                            $val_wrd_ids[] = $time_wrd->id;
                        }

                        // get the phrase group for the value row
                        // to be done for the list at once
                        $grp = new phrase_group;
                        $grp->usr = $this->usr;
                        $grp->ids = $val_wrd_ids;
                        $grp->load();
                        log_debug("value_list_dsp->dsp_table val ids " . implode(",", $val_wrd_ids) . " = " . $grp->id . ".");

                        $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                        if ($tbl_value->number == "") {
                            $result .= '      <td class="right_ref">' . "\n";

                            // to review
                            $add_phr_lst = clone $common_lst;
                            $add_phr_ids = $common_lst->ids;
                            $type_ids = array();
                            foreach ($add_phr_lst->ids as $pos) {
                                $type_ids[] = 0;
                            }

                            if ($sub_wrd->id > 0) {
                                $add_phr_lst->add($sub_wrd->phrase());
                                $add_phr_ids[] = $sub_wrd->id;
                                $type_ids[] = $sub_wrd->id; // todo check if it should not be $type_word_id
                            }
                            // if values for just one column are added, the column head word id is already in the commen id list and due to that does not need to be added
                            if (!in_array($time_wrd->id, $add_phr_ids) and $time_wrd->id > 0) {
                                $add_phr_lst->add($time_wrd->phrase());
                                $add_phr_ids[] = $time_wrd->id;
                                $type_ids[] = 0;
                            }

                            //$result .= '      '.btn_add_value_fast ($modal_nbr, $add_phr_lst, $common_lst, $back);
                            $result .= '      ' . btn_add_value_fast($modal_nbr, $add_phr_lst, $this->phr, $common_lst, $back);
                            $modal_nbr++;
                            //$result .= '      '.btn_add_value ($add_phr_lst, $type_ids, $back);
                            $result .= '      </td>' . "\n";
                        } else {
                            $result .= $tbl_value->dsp_tbl($back);
                            // maybe display the extra words of this value
                        }
                    }
                    $result .= '  </tr>' . "\n";
                }

                // display the row differentiators
                $sub_wrd->usr = $this->usr; // to be fixed in the lines before
                log_debug("value_list_dsp->dsp_table ... get differentiator for " . $sub_wrd->id . " and user " . $sub_wrd->usr->name . ".");
                // get all potential differentiator words
                $sub_wrd_lst = $sub_wrd->lst();
                $differentiator_words = $sub_wrd_lst->differentiators_filtered($phr_lst_all);
                $sub_phr_lst = $sub_wrd_lst->phrase_lst();
                $differentiator_phrases = $differentiator_words->phrase_lst();
                log_debug("value_list_dsp->dsp_table ... show differentiator of " . $differentiator_phrases->name() . ".");
                // select only the differentiator words that have a value for the main word
                //$differentiator_phrases = zu_lst_in($differentiator_phrases, $xtra_phrases);
                $differentiator_phrases = $differentiator_phrases->filter($xtra_phrases);

                // find direct differentiator words
                //$differentiator_type = sql_code_link(SQL_LINK_TYPE_DIFFERENTIATOR);
                log_debug("value_list_dsp->dsp_table ... get differentiator type " . $differentiator_phrases->name() . ".");
                $type_phrases = $sub_phr_lst->differentiators();

                // if there is more than one type of differentiator group the differentiators by type
                // and add on each one an "other" line, if the sum is not 100%

                //foreach ($type_word_ids as $type_word_id) {
                foreach ($type_phrases->lst as $type_phr) {
                    if ($type_phr->id <> 1) {
                        $result .= '  <tr>' . "\n";
                        //$result .= '      <td>&nbsp;</td>';
                        $result .= $type_phr->dsp_tbl(0);
                        $result .= '  </tr>' . "\n";
                    }
                    // display the differentiator rows that are matching to the word type (e.g. the country)
                    //foreach (array_keys($differentiator_phrases) as $diff_word_id) {
                    $time_wrd = null;
                    $diff_phrase = null;
                    foreach ($differentiator_phrases->lst as $diff_phrase) {
                        if ($diff_phrase->is_a($type_phr)) {
                            $result .= '  <tr>' . "\n";
                            //$result .= '      <td>&nbsp;</td>';
                            $result .= $sub_wrd->dsp_tbl(0);
                            $wrd_ids = array();
                            $wrd_ids[] = $this->phr->id;
                            if (!in_array($sub_wrd->id, $wrd_ids)) {
                                $wrd_ids[] = $sub_wrd->id;
                            }
                            if (!in_array($diff_phrase->id, $wrd_ids)) {
                                $wrd_ids[] = $diff_phrase->id;
                            }
                            foreach ($common_lst->ids as $xtra_id) {
                                if (!in_array($xtra_id, $wrd_ids)) {
                                    $wrd_ids[] = $xtra_id;
                                }
                            }

                            foreach ($time_lst->lst as $time_wrd) {
                                $val_wrd_ids = $wrd_ids;
                                if (!in_array($time_wrd->id, $val_wrd_ids)) {
                                    $val_wrd_ids[] = $time_wrd->id;
                                }

                                // get the phrase group for the value row
                                // to be done for the list at once
                                $grp = new phrase_group;
                                $grp->usr = $this->usr;
                                $grp->ids = $val_wrd_ids;
                                $grp->load();
                                log_debug("value_list_dsp->dsp_table val ids " . implode(",", $val_wrd_ids) . " = " . $grp->id . ".");

                                $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                                if ($tbl_value->number == "") {
                                    $result .= '      <td class="right_ref">' . "\n";

                                    // to review
                                    $add_phr_lst = $common_lst;
                                    $add_phr_ids = $common_lst->ids;
                                    $type_ids = array();
                                    foreach ($add_phr_lst->ids as $pos) {
                                        $type_ids[] = 0;
                                    }

                                    if ($sub_wrd->id > 0) {
                                        $add_phr_lst->add($sub_wrd->phrase());
                                        $add_phr_ids[] = $sub_wrd->id;
                                        $type_ids[] = $type_phr->id;
                                    }
                                    if ($diff_phrase->id <> 0) {
                                        $add_phr_lst->add($diff_phrase);
                                        $add_phr_ids[] = $diff_phrase->id;
                                        $type_ids[] = 0;
                                    }
                                    // if values for just one column are added, the column head word id is already in the commen id list and due to that does not need to be added
                                    if (!in_array($time_wrd->id, $add_phr_ids) and $time_wrd->id > 0) {
                                        $add_phr_lst->add($time_wrd->phrase());
                                        $add_phr_ids[] = $time_wrd->id;
                                        $type_ids[] = 0;
                                    }

                                    $result .= '      ' . btn_add_value($add_phr_lst, $type_ids, $back);
                                    $result .= '      </td>' . "\n";
                                } else {
                                    $result .= $tbl_value->dsp_tbl($back);
                                    // maybe display the extra words of this value
                                }
                            }
                            $result .= '  </tr>' . "\n";
                        }
                    }
                    // add a new part value for the sub_word
                    if (!empty($differentiator_phrases)) {
                        $result .= '  <tr>' . "\n";
                        $result .= '      <td class="right_ref">' . "\n";

                        // to review
                        $add_phr_ids = $common_lst->ids;
                        $type_ids = array();
                        foreach ($add_phr_ids as $pos) {
                            $type_ids[] = 0;
                        }

                        $add_phr_ids[] = $sub_wrd->id;
                        if ($time_wrd != null) {
                            $add_phr_ids[] = $time_wrd->id;
                        }
                        if ($diff_phrase != null) {
                            $add_phr_ids[] = $diff_phrase->id;
                        }
                        $type_ids[] = $type_phr->id;
                        $type_ids[] = $type_phr->id;
                        $type_ids[] = $type_phr->id;

                        $result .= '      &nbsp;&nbsp;' . btn_add_value($add_phr_ids, $type_ids, $back);
                        $result .= '      </td>' . "\n";
                        $result .= '  </tr>' . "\n";
                    }
                }

            }

            // allow the user to add a completely new value
            if ($last_words == '') {
                $last_words = $id;
            }

            // add an extra row to add new rows
            $result .= '  <tr>' . "\n";
            $result .= '      <td>' . "\n";

            // offer the user to add a new row related word
            $result .= $phr_row->btn_add($back, debug - 1);
            $result .= '&nbsp;&nbsp;';

            // offer the user to add a new value e.g. to add a value for a new year
            // this extra add value button is needed for the case that all values are filled and due to that there is no other plus sign on the table
            if (isset($val_main)) {
                foreach ($time_lst->lst as $time_wrd) {
                    $result .= '      <td class="right_ref">' . "\n";
                    $result .= $val_main->btn_add($back, debug - 1);
                    $result .= '      </td>' . "\n";
                }
            }

            $result .= '      </td>' . "\n";
            $result .= '  </tr>' . "\n";

            $result .= '    </tbody>' . "\n";
            $result .= dsp_tbl_end();

            $result .= '<br><br>';

        }
        log_debug("value_list_dsp->dsp_table ... done");

        return $result;
    }


}

?>
