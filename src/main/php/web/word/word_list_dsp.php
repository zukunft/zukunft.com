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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class word_list_dsp extends word_list
{

    /**
     * @return string one string with all names of the list with the link
     */
    function name_linked(): string
    {
        return dsp_array($this->names_linked());
    }

    /**
     * @return array with a list of the word names with html links
     */
    function names_linked(): array
    {
        log_debug('word_list->names_linked (' . dsp_count($this->lst) . ')');
        $result = array();
        foreach ($this->lst as $wrd) {
            $result[] = $wrd->display();
        }
        log_debug('word_list->names_linked (' . dsp_array($result) . ')');
        return $result;
    }

    /**
     * like names_linked, but without measure and time words
     * because measure words are usually shown after the number
     * @return array with the names of the list with the link
     */
    function names_linked_ex_measure_and_time(): array
    {
        log_debug('word_list->names_linked_ex_measure_and_time (' . dsp_count($this->lst) . ')');
        $wrd_lst_ex = clone $this;
        $wrd_lst_ex->ex_time();
        $wrd_lst_ex->ex_measure();
        $wrd_lst_ex->ex_scaling();
        $wrd_lst_ex->ex_percent(); // the percent sign is normally added to the value
        $result = $wrd_lst_ex->names_linked();
        log_debug('word_list->names_linked_ex_measure_and_time (' . dsp_array($result) . ')');
        return $result;
    }

    // like names_linked, but only the measure words
    // because measure words are usually shown after the number
    function names_linked_measure(): array
    {
        log_debug('word_list->names_linked_measure (' . dsp_count($this->lst) . ')');
        $wrd_lst_scale = $this->scaling_lst();
        $wrd_lst_measure = $this->measure_lst();
        $wrd_lst_measure->merge($wrd_lst_scale);
        $result = $wrd_lst_measure->names_linked();
        log_debug('word_list->names_linked_measure (' . dsp_array($result) . ')');
        return $result;
    }

    // like names_linked, but only the time words
    function names_linked_time(): array
    {
        log_debug('word_list->names_linked_time (' . dsp_count($this->lst) . ')');
        $wrd_lst_time = $this->time_lst();
        $result = $wrd_lst_time->names_linked();
        log_debug('word_list->names_linked_time (' . dsp_array($result) . ')');
        return $result;
    }

    // similar to zuh_selector but using a list not a query
    function dsp_selector($name, $form, $selected): string
    {
        log_debug('word_list->dsp_selector(' . $name . ',' . $form . ',s' . $selected . ')');

        $result = '<select name="' . $name . '" form="' . $form . '">';

        foreach ($this->lst as $wrd) {
            if ($wrd->id == $selected) {
                log_debug('word_list->dsp_selector ... selected ' . $wrd->id);
                $result .= '      <option value="' . $wrd->id . '" selected>' . $wrd->name . '</option>';
            } else {
                $result .= '      <option value="' . $wrd->id . '">' . $wrd->name . '</option>';
            }
        }

        $result .= '</select>';

        log_debug('word_list->dsp_selector ... done');
        return $result;
    }

    // TODO: use word_link_list->display instead
    // list of related words filtered by a link type
    // returns the html code
    // database link must be open
    function name_table($word_id, $verb_id, $direction, $user_id, $back): string
    {
        log_debug('word_list->name_table (t' . $word_id . ',v' . $verb_id . ',' . $direction . ',u' . $user_id . ')');
        $result = '';

        // this is how it should be replaced in the calling function
        $wrd = new word_dsp($this->usr);
        $wrd->id = $word_id;
        $wrd->load();
        $vrb = new verb;
        $vrb->id = $verb_id;
        $vrb->load();
        $lnk_lst = new word_link_list($this->usr);
        $lnk_lst->wrd = $wrd;
        $lnk_lst->vrb = $vrb;
        $lnk_lst->direction = $direction;
        $lnk_lst->load_old();
        $result .= $lnk_lst->display($back);

        /*
        foreach ($this->lst AS $wrd) {
          if ($direction == word_select_direction::UP) {
            $directional_verb_id = $wrd->verb_id;
          } else {
            $directional_verb_id = $wrd->verb_id * -1;
          }

          // display the link type
          $num_rows = mysqli_num_rows($sql_result);
          if ($num_rows > 1) {
            $result .= zut_plural ($word_id, $user_id);
            if ($direction == word_select_direction::UP) {
              $result .= " " . zul_plural_reverse($verb_id);
            } else {
              $result .= " " . zul_plural($verb_id);
            }
          } else {
            $result .= zut_name ($word_id, $user_id);
            if ($direction == word_select_direction::UP) {
              $result .= " " . zul_reverse($verb_id);
            } else {
              $result .= " " . zul_name($verb_id);
            }
          }

          zu_debug('zum_word_list -> table');

          // display the words
          $result .= dsp_tbl_start_half();
          while ($word_entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
            $result .= '  <tr>'."\n";
            $result .= zut_html_tbl($word_entry[0], $word_entry[1]);
            $result .= zutl_btn_edit ($word_entry[3], $word_id);
            $result .= zut_unlink_html ($word_entry[3], $word_id);
            $result .= '  </tr>'."\n";
            // use the last word as a sample for the new word type
            $word_type_id = $word_entry[2];
          }
        }

        // give the user the possibility to add a similar word
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      '.zuh_btn_add ("Add similar word", '/http/word_add.php?link='.$directional_verb_id.'&word='.$word_id.'&type='.$word_type_id.'&back='.$word_id);
        $result .= '    </td>';
        $result .= '  </tr>';

        $result .= dsp_tbl_end ();
        $result .= '<br>';
        */

        return $result;
    }

    // display a list of words that match to the given pattern
    function dsp_like($word_pattern, $user_id): string
    {
        log_debug('word_dsp->dsp_like (' . $word_pattern . ',u' . $user_id . ')');

        global $db_con;
        $result = '';

        $back = 1;
        // get the link types related to the word
        $sql = " ( SELECT t.word_id AS id, t.word_name AS name, 'word' AS type
                 FROM words t 
                WHERE t.word_name like '" . $word_pattern . "%' 
                  AND t.word_type_id <> " . cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK) . ")
       UNION ( SELECT f.formula_id AS id, f.formula_name AS name, 'formula' AS type
                 FROM formulas f 
                WHERE f.formula_name like '" . $word_pattern . "%' )
             ORDER BY name
                LIMIT 200;";
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get_old($sql);

        // loop over the words and display it with the link
        foreach ($db_lst as $db_row) {
            //while ($entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
            if ($db_row['type'] == "word") {
                $wrd = new word_dsp($this->usr);
                $wrd->id = $db_row['id'];
                $wrd->name = $db_row['name'];
                $result .= $wrd->dsp_tbl_row();
            }
            if ($db_row['type'] == "formula") {
                $frm = new formula($this->usr);
                $frm->id = $db_row['id'];
                $frm->name = $db_row['name'];
                $result .= $frm->name_linked($back);
            }
        }

        return $result;
    }

    // return an url with the word ids
    function id_url_long(): string
    {
        return zu_ids_to_url($this->ids(), "word");
    }

}
