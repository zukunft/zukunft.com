<?php

/*

  value_dsp.php - create the UI JSON messsage or HTML code to display a value
  -------------
  

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

class value_dsp extends value
{


    function __construct()
    {
        parent::__construct();
    }

    function reset()
    {
    }

    /*
    display functions
    -------
  */

    // return the html code to display a value
    // this is the opposite of the convert function
    function display(string $back = ''): string
    {
        $result = '';
        if (!is_null($this->number)) {
            $this->load_phrases();
            $num_text = $this->val_formatted();
            if (!$this->is_std()) {
                $result = '<font class="user_specific">' . $num_text . '</font>';
                //$result = $num_text;
            } else {
                $result = $num_text;
            }
        }
        return $result;
    }

    // html code to show the value with the possibility to click for the result explanation
    function display_linked($back)
    {
        $result = '';

        log_debug('value->display_linked (' . $this->id . ',u' . $this->usr->id . ')');
        if (!is_null($this->number)) {
            $num_text = $this->val_formatted();
            $link_format = '';
            if (isset($this->usr)) {
                if (!$this->is_std()) {
                    $link_format = ' class="user_specific"';
                }
            }
            // to review
            $result .= '<a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '" ' . $link_format . ' >' . $num_text . '</a>';
        }
        log_debug('value->display_linked -> done');
        return $result;
    }

    // offer the user to add a new value similar to this value
    function btn_add($back)
    {
        $result = '';

        $val_btn_title = '';
        $url_phr = '';
        $this->load_phrases();
        if (isset($this->phr_lst)) {
            if (!empty($this->phr_lst->lst)) {
                $val_btn_title = "add new value similar to " . htmlentities($this->phr_lst->name());
            } else {
                $val_btn_title = "add new value";
            }
            $url_phr = $this->phr_lst->id_url_long();
        }

        $val_btn_call = '/http/value_add.php?back=' . $back . $url_phr;
        $result .= btn_add($val_btn_title, $val_btn_call);

        return $result;
    }

    // depending on the word list format the numeric value
    // format the value for on screen display
    // similar to the corresponding function in the "formula_value" class
    function val_formatted()
    {
        $result = '';

        $this->load_phrases();

        if (!is_null($this->number)) {
            if (is_null($this->wrd_lst)) {
                $this->load();
            }
            if ($this->wrd_lst->has_percent()) {
                $result = round($this->number * 100, 2) . "%";
            } else {
                if ($this->number >= 1000 or $this->number <= -1000) {
                    $result .= number_format($this->number, 0, $this->usr->dec_point, $this->usr->thousand_sep);
                } else {
                    $result = round($this->number, 2);
                }
            }
        }
        return $result;
    }

    // the same as btn_del_value, but with another icon
    function btn_undo_add_value($back)
    {
        $result = btn_undo('delete this value', '/http/value_del.php?id=' . $this->id . '&back=' . $back . '');
        return $result;
    }

    // display a value, means create the HTML code that allows to edit the value
    function dsp_tbl_std($back)
    {
        log_debug('value->dsp_tbl_std ');
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="right_ref"><a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    // same as dsp_tbl_std, but in the user specific color
    function dsp_tbl_usr($back)
    {
        log_debug('value->dsp_tbl_usr');
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="right_ref"><a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '" class="user_specific">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl($back)
    {
        log_debug('value->dsp_tbl_std ');
        $result = '';

        if ($this->is_std()) {
            $result .= $this->dsp_tbl_std($back);
        } else {
            $result .= $this->dsp_tbl_usr($back);
        }
        return $result;
    }

    // display the history of a value
    function dsp_hist($page, $size, $call, $back)
    {
        log_debug("value->dsp_hist for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->obj = $this;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'value';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug("value->dsp_hist -> done");
        return $result;
    }

    // display the history of a value
    function dsp_hist_links($page, $size, $call, $back)
    {
        log_debug("value->dsp_hist_links (" . $this->id . ",size" . $size . ",b" . $size . ")");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'value';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug("value->dsp_hist_links -> done");
        return $result;
    }

    // display some value samples related to the wrd_id
    // with a preference of the start_word_ids
    function dsp_samples($wrd_id, $start_wrd_ids, $size, $back)
    {
        log_debug("value->dsp_samples (" . $wrd_id . ",rt" . implode(",", $start_wrd_ids) . ",size" . $size . ")");

        global $db_con;
        $result = ''; // reset the html code var

        // get value changes by the user that are not standard
        $sql = "SELECT v.value_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                   t.word_id,
                   t.word_name
              FROM value_phrase_links l,
                   value_phrase_links lt,
                   words t,
                   " . $db_con->get_table_name(DB_TYPE_VALUE) . " v
         LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = " . $this->usr->id . " 
             WHERE l.phrase_id = " . $wrd_id . "
               AND l.value_id = v.value_id
               AND v.value_id = lt.value_id
               AND lt.phrase_id <> " . $wrd_id . "
               AND lt.phrase_id = t.word_id
               AND (u.excluded IS NULL OR u.excluded = 0) 
             LIMIT " . $size . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get($sql);

        // prepare to show where the user uses different value than a normal viewer
        $row_nbr = 0;
        $value_id = 0;
        $word_names = "";
        $result .= dsp_tbl_start_hist();
        foreach ($db_lst as $db_row) {
            // display the headline first if there is at least on entry
            if ($row_nbr == 0) {
                $result .= '<tr>';
                $result .= '<th>samples</th>';
                $result .= '<th>for</th>';
                $result .= '</tr>';
                $row_nbr++;
            }

            $new_value_id = $db_row["value_id"];
            $wrd = new word_dsp;
            $wrd->usr = $this->usr;
            $wrd->id = $db_row["word_id"];
            $wrd->name = $db_row["word_name"];
            if ($value_id <> $new_value_id) {
                if ($word_names <> "") {
                    // display a row if the value has changed and
                    $result .= '<tr>';
                    $result .= '<td><a href="/http/value_edit.php?id=' . $value_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
                    $result .= '<td>' . $word_names . '</td>';
                    $result .= '</tr>';
                    $row_nbr++;
                }
                // prepare a new value display
                $row_value = $db_row["word_value"];
                $word_names = $wrd->dsp_link_style("grey");
                $value_id = $new_value_id;
            } else {
                $word_names .= ", " . $wrd->dsp_link_style("grey");
            }
        }
        // display the last row if there has been at least one word
        if ($word_names <> "") {
            $result .= '<tr>';
            $result .= '<td><a href="/http/value_edit.php?id=' . $value_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
            $result .= '<td>' . $word_names . '</td>';
            $result .= '</tr>';
        }
        $result .= dsp_tbl_end();

        log_debug("value->dsp_samples -> done.");
        return $result;
    }

    // simple modal box to add a value
    function dsp_add_fast($back)
    {
        $result = '';

        $result .= '  <h2>Modal Example</h2>';
        $result .= '  <!-- Button to Open the Modal -->';
        //$result .= '  <a href="/http/value_add.php?back=2" title="add"><img src="'.$icon.'" alt="'.$this->title.'"></a>';
        $result .= '';

        return $result;
    }

    // lists all phrases related to a given value except the given phrase
    // and offer to add a formula to the value as an alternative
    // $wrd_add is only optional to display the last added phrase at the end
    // todo: take user unlink of phrases into account
    // save data to the database only if "save" is pressed add and remove the phrase links "on the fly", which means that after the first call the edit view is more or less the same as the add view
    function dsp_edit($type_ids, $back): string
    {
        $result = ''; // reset the html code var

        // set main display parameters for the add or edit view
        if ($this->id <= 0) {
            $script = "value_add";
            $result .= dsp_form_start($script);
            $result .= dsp_text_h3("Add value for");
            log_debug("value->dsp_edit new for phrase ids " . implode(",", $this->ids) . " and user " . $this->usr->id . ".");
        } else {
            $script = "value_edit";
            $result .= dsp_form_start($script);
            $result .= dsp_text_h3("Change value for");
            if (count($this->ids) <= 0) {
                $this->load_phrases();
                log_debug('value->dsp_edit id ' . $this->id . ' with "' . $this->grp->name() . '"@"' . $this->time_phr->name . '"and user ' . $this->usr->id);
            } else {
                $this->load_time_phrase();
                log_debug('value->dsp_edit id ' . $this->id . ' with phrase ids ' . dsp_array($this->ids) . ' and user ' . $this->usr->id);
            }
        }
        $this_url = '/http/' . $script . '.php?id=' . $this->id . '&back=' . $back; // url to call this display again to display the user changes

        // display the words and triples
        $result .= dsp_tbl_start_select();
        if (count($this->ids) > 0) {
            $url_pos = 1; // the phrase position (combined number for fixed, type and free phrases)
            // if the form is confirmed, save the value or the other way round: if with the plus sign only a new phrase is added, do not yet save the value
            $result .= '  <input type="hidden" name="id" value="' . $this->id . '">';
            $result .= '  <input type="hidden" name="confirm" value="1">';

            // reset the phrase sample settings
            $main_wrd = null;
            log_debug("value->dsp_edit main wrd");

            // rebuild the value ids if needed
            // 1. load the phrases parameters based on the ids
            $result .= $this->set_phr_lst_by_ids();
            // 2. extract the time from the phrase list
            $result .= $this->set_time_by_phr_lst();
            log_debug("value->dsp_edit phrase list incl. time " . $this->phr_lst->name());
            $result .= $this->set_phr_lst_ex_time();
            log_debug("value->dsp_edit phrase list excl. time " . $this->phr_lst->name());
            $phr_lst = $this->phr_lst;

            /*
      // load the phrase list
      $phr_lst = New phrase_list;
      $phr_lst->ids = $this->ids;
      $phr_lst->usr = $this->usr;
      $phr_lst->load();

      // separate the time if needed
      if ($this->time_id <= 0) {
        $this->time_phr = $phr_lst->time_useful();
        $phr_lst->del($this->time_phr);
        $this->time_id = $this->time_phr->id; // not really needed ...
      }
      */

            // assign the type to the phrases
            foreach ($phr_lst->lst as $phr) {
                $phr->usr = $this->usr;
                foreach (array_keys($this->ids) as $pos) {
                    if ($phr->id == $this->ids[$pos]) {
                        $phr->is_wrd_id = $type_ids[$pos];
                        $is_wrd = new word_dsp;
                        $is_wrd->id = $phr->is_wrd_id;
                        $is_wrd->usr = $this->usr;
                        $phr->is_wrd = $is_wrd;
                        $phr->dsp_pos = $pos;
                    }
                }
                // guess the missing phrase types
                if ($phr->is_wrd_id == 0) {
                    log_debug('value->dsp_edit -> guess type for "' . $phr->name . '"');
                    $phr->is_wrd = $phr->is_mainly();
                    if ($phr->is_wrd->id > 0) {
                        $phr->is_wrd_id = $phr->is_wrd->id;
                        log_debug('value->dsp_edit -> guessed type for ' . $phr->name . ': ' . $phr->is_wrd->name);
                    }
                }
            }

            // show first the phrases, that are not supposed to be changed
            //foreach (array_keys($this->ids) AS $pos) {
            log_debug('value->dsp_edit -> show fixed phrases');
            foreach ($phr_lst->lst as $phr) {
                //if ($type_ids[$pos] < 0) {
                if ($phr->is_wrd_id < 0) {
                    log_debug('value->dsp_edit -> show fixed phrase "' . $phr->name . '"');
                    // allow the user to change also the fixed phrases
                    $type_ids_adj = $type_ids;
                    $type_ids_adj[$phr->dsp_pos] = 0;
                    $used_url = $this_url . zu_ids_to_url($this->ids, "phrase") .
                        zu_ids_to_url($type_ids_adj, "type");
                    $result .= $phr->dsp_name_del($used_url);
                    $result .= '  <input type="hidden" name="phrase' . $url_pos . '" value="' . $phr->id . '">';
                    $url_pos++;
                }
            }

            // show the phrases that the user can change: first the non specific ones, that the phrases of a selective type and new phrases at the end
            log_debug('value->dsp_edit -> show phrases');
            for ($dsp_type = 0; $dsp_type <= 1; $dsp_type++) {
                foreach ($phr_lst->lst as $phr) {
                    /*
          // build a list of suggested phrases
          $phr_lst_sel_old = array();
          if ($phr->is_wrd_id > 0) {
            // prepare the selector for the type phrase
            $phr->is_wrd->usr = $this->usr;
            $phr_lst_sel = $phr->is_wrd->children();
            zu_debug("value->dsp_edit -> suggested phrases for ".$phr->name.": ".$phr_lst_sel->name().".");
          } else {
            // if no phrase group is found, use the phrase type time if the phrase is a time phrase
            if ($phr->is_time()) {
              $phr_lst_sel = New phrase_list;
              $phr_lst_sel->usr = $this->usr;
              $phr_lst_sel->phrase_type_id = cl(SQL_WORD_TYPE_TIME);
              $phr_lst_sel->load();
            }
          } */

                    // build the url for the case that this phrase should be removed
                    log_debug('value->dsp_edit -> build url');
                    $phr_ids_adj = $this->ids;
                    $type_ids_adj = $type_ids;
                    array_splice($phr_ids_adj, $phr->dsp_pos, 1);
                    array_splice($type_ids_adj, $phr->dsp_pos, 1);
                    $used_url = $this_url . zu_ids_to_url($phr_ids_adj, "phrase") .
                        zu_ids_to_url($type_ids_adj, "type") .
                        '&confirm=1';
                    // url for the case that this phrase should be renamed
                    if ($phr->id > 0) {
                        $phrase_url = '/http/word_edit.php?id=' . $phr->id . '&back=' . $back;
                    } else {
                        $lnk_id = $phr->id * -1;
                        $phrase_url = '/http/link_edit.php?id=' . $lnk_id . '&back=' . $back;
                    }

                    // show the phrase selector
                    $result .= '  <tr>';

                    // show the phrases that have a type
                    if ($dsp_type == 0) {
                        if ($phr->is_wrd->id > 0) {
                            log_debug('value->dsp_edit -> id ' . $phr->id . ' has a type');
                            $result .= '    <td>';
                            $result .= $phr->is_wrd->name . ':';
                            $result .= '    </td>';
                            //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td>';
                            /*if (!empty($phr_lst_sel->lst)) {
                $result .= '      '.$phr_lst_sel->dsp_selector("phrase".$url_pos, $script, $phr->id);
              } else {  */
                            $result .= '      ' . $phr->dsp_selector($phr->is_wrd, $script, $url_pos, '', $back);
                            //}
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . btn_del("Remove " . $phr->name, $used_url) . '</td>';
                            $result .= '    <td>' . btn_edit("Rename " . $phr->name, $phrase_url) . '</td>';
                        }
                    }

                    // show the phrases that don't have a type
                    if ($dsp_type == 1) {
                        if ($phr->is_wrd->id == 0 and $phr->id > 0) {
                            log_debug('value->dsp_edit -> id ' . $phr->id . ' has no type');
                            if (!isset($main_wrd)) {
                                $main_wrd = $phr;
                            }
                            //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td colspan="2">';
                            $result .= '      ' . $phr->dsp_selector(0, $script, $url_pos, '', $back);
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . btn_del("Remove " . $phr->name, $used_url) . '</td>';
                            $result .= '    <td>' . btn_edit("Rename " . $phr->name, $phrase_url) . '</td>';
                        }
                    }


                    $result .= '  </tr>';
                }
            }

            // show the time word
            log_debug('value->dsp_edit -> show time');
            if ($this->time_id > 0) {
                if (isset($this->time_phr)) {
                    $result .= '  <tr>';
                    if ($this->time_phr->id == 0) {
                        $result .= '    <td colspan="2">';

                        log_debug('value->dsp_edit -> show time selector');
                        $result .= $this->time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                        $url_pos++;

                        $result .= '    </td>';
                        $result .= '    <td>' . btn_del("Remove " . $this->time_phr->name, $used_url) . '</td>';
                    }
                    $result .= '  </tr>';
                }
            }

            // show the new phrases
            log_debug('value->dsp_edit -> show new phrases');
            foreach ($this->ids as $phr_id) {
                $result .= '  <tr>';
                if ($phr_id == 0) {
                    $result .= '    <td colspan="2">';

                    $phr_new = new phrase;
                    $phr_new->usr = $this->usr;
                    $result .= $phr_new->dsp_selector(0, $script, $url_pos, '', $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . btn_del("Remove new", $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
        }

        $result .= dsp_tbl_end();

        log_debug('value->dsp_edit -> table ended');
        $phr_ids_new = $this->ids;
        //$phr_ids_new[]  = $new_phrase_default;
        $phr_ids_new[] = 0;
        $type_ids_new = $type_ids;
        $type_ids_new[] = 0;
        $used_url = $this_url . zu_ids_to_url($phr_ids_new, "phrase") .
            zu_ids_to_url($type_ids_new, "type");
        $result .= '  ' . btn_add("Add another phrase", $used_url);
        $result .= '  <br><br>';
        $result .= '  <input type="hidden" name="back" value="' . $back . '">';
        if ($this->id > 0) {
            $result .= '  to <input type="text" name="value" value="' . $this->number . '">';
        } else {
            $result .= '  is <input type="text" name="value">';
        }
        $result .= dsp_form_end("Save", $back);
        $result .= '<br><br>';
        log_debug('value->dsp_edit -> load source');
        $src = $this->load_source();
        if (isset($src)) {
            $result .= $src->dsp_select($script, $back);
            $result .= '<br><br>';
        }

        // display the share type
        $result .= $this->dsp_share($script, $back);

        // display the protection type
        $result .= $this->dsp_protection($script, $back);

        $result .= '<br>';
        $result .= btn_back($back);

        // display the user changes
        log_debug('value->dsp_edit -> user changes');
        if ($this->id > 0) {
            $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $result .= dsp_text_h3("Latest changes related to this value", "change_hist");
                $result .= $changes;
            }
            $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $result .= dsp_text_h3("Latest link changes related to this value", "change_hist");
                $result .= $changes;
            }
        } else {
            // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
            if (isset($main_wrd)) {
                $main_wrd->load();
                $samples = $this->dsp_samples($main_wrd->id, $this->ids, 10, $back);
                log_debug("value->dsp_edit samples.");
                if (trim($samples) <> "") {
                    $result .= dsp_text_h3('Please have a look at these other "' . $main_wrd->dsp_link_style("grey") . '" values as an indication', 'change_hist');
                    $result .= $samples;
                }
            }
        }

        log_debug("value->dsp_edit -> done");
        return $result;
    }

}