<?php

/*

    web/value/value.php - create the html code to show a value to the user
    -------------------

    to create the HTML code to show a value to the user
    and allow changing the value


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

namespace html\value;

include_once WEB_SANDBOX_PATH . 'sandbox_value.php';
include_once DB_PATH . 'sql_db.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_FIGURE_PATH . 'figure.php';
include_once WEB_HELPER_PATH . 'config.php';
include_once WEB_LOG_PATH . 'user_log_display.php';
include_once WEB_GROUP_PATH . 'group.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_REF_PATH . 'source.php';
include_once WEB_SANDBOX_PATH . 'sandbox_value.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use html\group\group;
use html\figure\figure;
use html\helper\config;
use html\html_base;
use html\log\user_log_display;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\ref\source;
use html\rest_ctrl as api_dsp;
use html\sandbox\sandbox_value;
use html\styles;
use html\user\user_message;
use html\word\word;
use shared\const\views;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;

class value extends sandbox_value
{

    /*
     * set and get
     */

    /**
     * @param group $grp
     * @return float
     */
    function get(group $grp): float
    {
        /*
         * $result = null;
         * check if the value can be returned from frontend cache
         *  // if the group contains a table phrase of the prime pod for the frontend,
         *  if ($grp->has_table_pod_phrase()) {
         *      $tbl_phr = $grp->get_pod_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group contains a table phrase which is cloned in the prime pod for the frontend,
         *  } elseif ($grp->has_table_clone_phrase()) {
         *      $tbl_phr = $grp->get_clone_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group up to 4 phrases and all phrases are prime phrases
         *  } elseif ($grp->has_max_4_prime_phrases()) {
         *      if (!$grp->prime_is_cached()) {
         *          $grp_prime_cache->check_size_and_remove_unneeded();
         *          $grp_prime_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_prime_cache->get_number($grp);
         *  // if the grp up to 16 phrases
         *  } elseif ($grp->has_max_16_phrases()) {
         *      if (!$grp->is_cached()) {
         *          $grp_cache->check_size_and_remove_unneeded();
         *          $grp_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_cache->get_number($grp);
         *  // if the group contains more than 16 phrases
         *  } else {
         *      if (!$grp->is_cached()) {
         *          $grp_big_cache->check_size_and_remove_unneeded();
         *          $grp_big_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_big_cache->get_number($grp);
         *  }
         * if the frontend cache is not yet fully used include
         *
         *
         */
        return 0;
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::PHRASES] = $this->grp()->phr_lst()->api_array();
        $vars[json_fields::NUMBER] = $this->number();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * select
     */

    /**
     * to select the value if it matches all given phrase names
     * @param array $names the phrase names for the selection
     * @return bool true if this values is related to all phrase names
     */
    function match_all(array $names): bool
    {
        $result = true;
        $phr_names = $this->phr_lst()->names();
        foreach ($names as $name) {
            if ($result) {
                if (!in_array($name, $phr_names)) {
                    $result = false;
                }
            }
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @returns figure the figure display object base on this value object
     */
    function figure(): figure
    {
        $fig = new figure();
        $fig->set_obj($this);
        return $fig;
    }


    /*
     * base
     */

    /**
     * create the html code to show only the value in default format to the user
     * this is the opposite of the convert function
     * @return string the html code to show only the value
     */
    function value(): string
    {
        $html = new html_base();
        if ($this->number() != null) {
            $result = $this->val_formatted();
            if (!$this->is_std()) {
                $result = $html->span($result, styles::STYLE_USER);
            }
        } elseif ($this->text_value() != null) {
            $result = $this->text_value();
        } elseif ($this->time_value() != null) {
            $result = $this->time_value();
        } else {
            $result = '';
        }
        return $result;
    }

    /**
     * create the html code to show only the value formatted based on the user settings
     * with a link to see more related information of the value
     * @return string the formatted value with a link for more details
     */
    function value_link(string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(api_dsp::VALUE, $this->id(), '', $back);
        $txt = $this->value();
        return $html->ref($url, $txt);
    }

    /**
     * create the html code to show only the value formatted based on the user settings
     * with a link to change the value itself or the value parameters
     * @return string the formatted value with a link to change this value
     */
    function value_edit(string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(api_dsp::VALUE_EDIT, $this->id(), '', $back);
        $txt = $this->value();
        return $html->ref($url, $txt);
    }

    /**
     * create the HTML code to show to the user
     * the value with the name and the formatted value
     * with a tooltip
     *
     * @param phrase_list|null $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @param string $sep the separator string between the name and the value
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_tip(phrase_list|null $phr_lst_exclude = null, string $sep = ' '): string
    {
        return $this->grp()->name_tip($phr_lst_exclude) . $sep . $this->value();
    }

    /**
     * create the HTML code to show the value name and the formatted value to the user
     *
     * @param phrase_list|null $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @param string $sep the separator string between the name and the value
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_link(phrase_list|null $phr_lst_exclude = null, string $sep = ' '): string
    {
        return $this->grp()->name_link_list($phr_lst_exclude) . $sep . $this->value_edit('');
    }

    /**
     * depending on the word list format the numeric value
     * format the value for on screen display
     * similar to the corresponding function in the "result" class
     * @returns string the HTML code to display this value
     */
    function val_formatted(): string
    {
        $result = '';

        $cfg = new config();

        if (!is_null($this->number())) {
            // load the list of phrases if needed
            if (!$this->grp()->phr_lst()->is_empty()) {
                if ($this->grp()->phr_lst()->has_percent()) {
                    $result = round($this->number() * 100, $cfg->percent_decimals()) . "%";
                } else {
                    if ($this->number() >= 1000 or $this->number() <= -1000) {
                        $result .= number_format($this->number(), 0, $cfg->dec_point(), $cfg->thousand_sep());
                    } else {
                        $result = round($this->number(), 2);
                    }
                }
            } else {
                // use default settings
                $result = round($this->number(), 2);
            }
        }
        return $result;
    }


    /*
     * buttons
     */

    /**
     * offer the user to add a new value similar to this value
     *
     * possible future parameters:
     * $fixed_words - words that the user is not suggested to change this time
     * $select_word - suggested words which the user can change
     * $type_word   - word to preselect the suggested words e.g. "Country" to list all their countries first for the suggested word
     *
     * @param string $back the id of the word from which the page has been called (TODO to be replace with the back trace object)
     * @returns string the HTML code for a button to add a value related to this value
     */
    function btn_add(string $back = ''): string
    {
        $msg_code_id = msg_id::VALUE_ADD;
        $explain = '';

        if ($this->grp()->phr_lst()->is_empty()) {
            if (!empty($this->grp()->phr_lst()->lst())) {
                $explain = htmlentities($this->grp()->phr_lst()->dsp_name());
                $msg_code_id = msg_id::VALUE_ADD_SIMILAR;
            }
        }

        return parent::btn_add_sbx(
            views::VALUE_ADD,
            $msg_code_id,
            $back, $explain);
    }

    /**
     * @return string the html code for a bottom
     * to change a value e.g. the name or the type
     */
    function btn_edit(string $back = ''): string
    {
        return parent::btn_edit_sbx(
            views::VALUE_EDIT,
            msg_id::VALUE_EDIT,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to exclude the value for the current user
     * or if no one uses the value delete the complete value
     */
    function btn_del(string $back = ''): string
    {
        return parent::btn_del_sbx(
            views::VALUE_DEL,
            msg_id::VALUE_DEL,
            $back);
    }


    /*
     * backend requests
     */

    /**
     * @return user_message
     */
    private function reload(): user_message
    {
        $usr_msg = new user_message();
        if ($this->is_id_set()) {
            $this->load_by_id($this->id());
        }
        return $usr_msg;
    }

    /**
     * reload the value object from the database, but only if some related objects
     * e,g, the phrase list is probably missing
     * @return user_message
     */
    private function reload_if_needed(): user_message
    {
        $usr_msg = new user_message();
        if (!$this->is_loaded()) {
            $usr_msg = $this->reload();
        }
        return $usr_msg;
    }

    /**
     * @return bool true if all related objects are loaded
     */
    private function is_loaded(): bool
    {
        $result = true;
        if ($this->grp()->phr_lst()->is_empty()) {
            $result = false;
        }
        return $result;
    }

    function is_id_set(): bool
    {
        return $this->grp()->is_id_set();
    }

    /*
     * to review
     */

    // the same as \html\btn_del_value, but with another icon
    function btn_undo_add_value($back): string
    {
        return \html\btn_undo('delete this value', '/http/value_del.php?id=' . $this->id() . '&back=' . $back . '');
    }

    // display a value, means create the HTML code that allows to edit the value
    function dsp_tbl_std($back): string
    {
        log_debug('value->dsp_tbl_std ');
        $result = '    <td>' . "\n";
        $result .= '      <div class="' . styles::STYLE_RIGHT . '"><a href="/http/value_edit.php?id=' . $this->id() . '&back=' . $back . '">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    // same as dsp_tbl_std, but in the user specific color
    function dsp_tbl_usr($back): string
    {
        log_debug('value->dsp_tbl_usr');
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="' . styles::STYLE_RIGHT . '"><a href="/http/value_edit.php?id=' . $this->id() . '&back=' . $back . '" class="' . styles::STYLE_USER . '">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl($back): string
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
    function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("value->dsp_hist for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->obj = $this;
        $log_dsp->type = \cfg\value\value::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_old();

        log_debug("done");
        return $result;
    }

    // display the history of a value
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug($this->id() . ",size" . $size . ",b" . $size);
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->type = value::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug("done");
        return $result;
    }

    // lists all words related to a given value except the given word
    // and offer to add a formula to the value as an alternative
    // $wrd_add is only optional to display the last added word at the end
    // TODO: take user unlink of words into account
    // save data to the database only if "save" is pressed add and remove the word links "on the fly", which means that after the first call the edit view is more or less the same as the add view

    // display some value samples related to the wrd_id
    // with a preference of the start_word_ids
    /*
     * TODO recreate based on the group
    function dsp_samples($wrd_id, $start_wrd_ids, $size, $back): string
    {
        log_debug("value->dsp_samples (" . $wrd_id . ",rt" . implode(",", $start_wrd_ids) . ",size" . $size . ")");

        global $db_con;
        $result = ''; // reset the html code var

        $html = new html_base();

        // get value changes by the user that are not standard
        $sql = "SELECT v.group_id,
                    " . $db_con->get_usr_field(value::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                   t.word_id,
                   t.word_name
              FROM groups g,
                   groups gt,
                   words t,
                   " . $db_con->get_table_name_esc(value::class) . " v
         LEFT JOIN user_values u ON v.group_id = u.group_id AND u.user_id = " . $this->user()->id() . "
             WHERE l.phrase_id = " . $wrd_id . "
               AND l.group_id = v.group_id
               AND v.group_id = lt.group_id
               AND lt.phrase_id <> " . $wrd_id . "
               AND lt.phrase_id = t.word_id
               AND (u.excluded IS NULL OR u.excluded = 0)
             LIMIT " . $size . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        $db_lst = $db_con->get_old($sql);

        // prepare to show where the user uses different value than a normal viewer
        $row_nbr = 0;
        $group_id = 0;
        $word_names = "";
        $result .= $html->dsp_tbl_start_hist();
        foreach ($db_lst as $db_row) {
            // display the headline first if there is at least on entry
            if ($row_nbr == 0) {
                $result .= '<tr>';
                $result .= '<th>samples</th>';
                $result .= '<th>for</th>';
                $result .= '</tr>';
                $row_nbr++;
            }

            $new_group_id = $db_row["group_id"];
            $wrd = new word_dsp();
            $wrd->set_id($db_row["word_id"]);
            $wrd->set_name($db_row["word_name"]);
            if ($group_id <> $new_group_id) {
                if ($word_names <> "") {
                    // display a row if the value has changed and
                    $result .= '<tr>';
                    $result .= '<td><a href="/http/value_edit.php?id=' . $group_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
                    $result .= '<td>' . $word_names . '</td>';
                    $result .= '</tr>';
                    $row_nbr++;
                }
                // prepare a new value display
                $row_value = $db_row["numeric_value"];
                $word_names = $wrd->name_linked(styles::STYLE_GREY);
                $group_id = $new_group_id;
            } else {
                $word_names .= ", " . $wrd->name_linked(styles::STYLE_GREY);
            }
        }
        // display the last row if there has been at least one word
        if ($word_names <> "") {
            $result .= '<tr>';
            $result .= '<td><a href="/http/value_edit.php?id=' . $group_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
            $result .= '<td>' . $word_names . '</td>';
            $result .= '</tr>';
        }
        $result .= $html->dsp_tbl_end();

        log_debug("done.");
        return $result;
    }
    */

    // simple modal box to add a value
    function dsp_add_fast($back): string
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
    // TODO: take user unlink of phrases into account
    // save data to the database only if "save" is pressed add and remove the phrase links "on the fly", which means that after the first call the edit view is more or less the same as the add view
    function dsp_edit($type_ids, $back): string
    {
        $result = ''; // reset the html code var
        $lib = new library();
        $html = new html_base();

        // set main display parameters for the add or edit view
        if ($this->id() <= 0) {
            $script = "value_add";
            $result .= $html->dsp_form_start($script);
            $result .= $html->dsp_text_h3("Add value for");
            log_debug("value->dsp_edit new for " . $this->dsp_id());
        } else {
            $script = "value_edit";
            $result .= $html->dsp_form_start($script);
            $result .= $html->dsp_text_h3("Change value for");
            if (count($this->ids()) <= 0) {
                $this->load_phrases();
                log_debug('value->dsp_edit ' . $this->dsp_id());
            }
        }
        $this_url = '/http/' . $script . '.php?id=' . $this->id() . '&back=' . $back; // url to call this display again to display the user changes

        // display the words and triples
        $result .= $html->dsp_tbl_start_select();
        if (count($this->ids()) > 0) {
            $url_pos = 1; // the phrase position (combined number for fixed, type and free phrases)
            // if the form is confirmed, save the value or the other way round: if with the plus sign only a new phrase is added, do not yet save the value
            $result .= $html->input(\shared\api::URL_VAR_ID, $this->id(), html_base::INPUT_HIDDEN);
            $result .= $html->input('confirm', '1', html_base::INPUT_HIDDEN);

            // reset the phrase sample settings
            $main_wrd = null;
            log_debug("value->dsp_edit main wrd");

            /*
      // load the phrase list
      $phr_lst = New phrase_list;
      $phr_lst->ids = $this->ids;
      $phr_lst->usr = $this->user();
      $phr_lst->load();

      // separate the time if needed
      if ($this->time_id <= 0) {
        $this->time_phr = $phr_lst->time_useful();
        $phr_lst->del($this->time_phr);
        $this->time_id = $this->time_phr->id(); // not really needed ...
      }
      */

            // assign the type to the phrases
            $phr_lst = clone $this->grp()->phrase_list();
            foreach ($phr_lst->lst() as $phr) {
                $phr->set_user($this->user());
                foreach (array_keys($this->ids()) as $pos) {
                    if ($phr->id == $this->ids()[$pos]) {
                        $phr->is_wrd_id = $type_ids[$pos];
                        $is_wrd = new word();
                        $is_wrd->set_id($phr->is_wrd_id);
                        $phr->is_wrd = $is_wrd;
                        $phr->dsp_pos = $pos;
                    }
                }
                // guess the missing phrase types
                if ($phr->is_wrd_id == 0) {
                    log_debug('guess type for "' . $phr->name() . '"');
                    $phr->is_wrd = $phr->is_mainly();
                    if ($phr->is_wrd->id() > 0) {
                        $phr->is_wrd_id = $phr->is_wrd->id();
                        log_debug('guessed type for ' . $phr->name() . ': ' . $phr->is_wrd->name);
                    }
                }
            }

            // show first the phrases, that are not supposed to be changed
            //foreach (array_keys($this->ids) AS $pos) {
            log_debug('show fixed phrases');
            foreach ($phr_lst->lst() as $phr) {
                //if ($type_ids[$pos] < 0) {
                if ($phr->is_wrd_id < 0) {
                    log_debug('show fixed phrase "' . $phr->name() . '"');
                    // allow the user to change also the fixed phrases
                    $type_ids_adj = $type_ids;
                    $type_ids_adj[$phr->dsp_pos] = 0;
                    $used_url = $this_url . $lib->ids_to_url($this->ids(), "phrase") .
                        $lib->ids_to_url($type_ids_adj, "type");
                    $result .= $phr->dsp_name_del($used_url);
                    $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="phrase' . $url_pos . '" value="' . $phr->id . '">';
                    $url_pos++;
                }
            }

            // show the phrases that the user can change:
            // first the non-specific ones, that the phrases of a selective type
            // and new phrases at the end
            log_debug('show phrases');
            for ($dsp_type = 0; $dsp_type <= 1; $dsp_type++) {
                foreach ($phr_lst->lst() as $phr) {
                    /*
          // build a list of suggested phrases
          $phr_lst_sel_old = array();
          if ($phr->is_wrd_id > 0) {
            // prepare the selector for the type phrase
            $phr->is_wrd->usr = $this->user();
            $phr_lst_sel = $phr->is_wrd->children();
            zu_debug("value->dsp_edit -> suggested phrases for ".$phr->name().": ".$phr_lst_sel->name().".");
          } else {
            // if no phrase group is found, use the phrase type time if the phrase is a time phrase
            if ($phr->is_time()) {
              $phr_lst_sel = New phrase_list;
              $phr_lst_sel->usr = $this->user();
              $phr_lst_sel->phrase_type_id = cl(SQL_WORD_TYPE_TIME);
              $phr_lst_sel->load();
            }
          } */

                    // build the url for the case that this phrase should be removed
                    log_debug('build url');
                    $phr_ids_adj = $this->ids();
                    $type_ids_adj = $type_ids;
                    array_splice($phr_ids_adj, $phr->dsp_pos, 1);
                    array_splice($type_ids_adj, $phr->dsp_pos, 1);
                    $used_url = $this_url . $lib->ids_to_url($phr_ids_adj, "phrase") .
                        $lib->ids_to_url($type_ids_adj, "type") .
                        '&confirm=1';
                    // url for the case that this phrase should be renamed
                    if ($phr->id() > 0) {
                        $phrase_url = '/http/word_edit.php?id=' . $phr->id . '&back=' . $back;
                    } else {
                        $lnk_id = $phr->id * -1;
                        $phrase_url = '/http/view.php?m=' . views::TRIPLE_EDIT . '&id=' . $lnk_id . '&back=' . $back;
                    }

                    // show the phrase selector
                    $result .= '  <tr>';

                    // show the phrases that have a type
                    if ($dsp_type == 0) {
                        if ($phr->is_wrd->id() > 0) {
                            log_debug('id ' . $phr->id . ' has a type');
                            $result .= '    <td>';
                            $result .= $phr->is_wrd->name . ':';
                            $result .= '    </td>';
                            //$result .= '    <input type="' . html_base::INPUT_HIDDEN . '" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td>';
                            /*if (!empty($phr_lst_sel->lst)) {
                $result .= '      '.$phr_lst_sel->dsp_selector("phrase".$url_pos, $script, $phr->id);
              } else {  */
                            $result .= '      ' . $phr->dsp_selector($phr->is_wrd, $script, $url_pos, '', $back);
                            //}
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . \html\btn_del("Remove " . $phr->name(), $used_url) . '</td>';
                            $result .= '    <td>' . \html\btn_edit("Rename " . $phr->name(), $phrase_url) . '</td>';
                        }
                    }

                    // show the phrases that don't have a type
                    if ($dsp_type == 1) {
                        if ($phr->is_wrd->id == 0 and $phr->id() > 0) {
                            log_debug('id ' . $phr->id . ' has no type');
                            if (!isset($main_wrd)) {
                                $main_wrd = $phr;
                            }
                            //$result .= '    <input type="' . html_base::INPUT_HIDDEN . '" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td colspan="2">';
                            $result .= '      ' . $phr->dsp_selector(0, $script, $url_pos, '', $back);
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . \html\btn_del("Remove " . $phr->name(), $used_url) . '</td>';
                            $result .= '    <td>' . \html\btn_edit("Rename " . $phr->name(), $phrase_url) . '</td>';
                        }
                    }


                    $result .= '  </tr>';
                }
            }

            // show the time phrase
            log_debug('show time');
            $time_lst = $this->grp()->phr_lst()->time_word_list();
            $has_time = false;
            foreach ($time_lst->lst() as $time_phr) {
                $result .= '  <tr>';
                if ($time_phr->id() <> 0) {
                    $has_time = true;
                    $result .= '    <td colspan="2">';

                    log_debug('show time selector');
                    $result .= $time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . \html\btn_del("Remove " . $time_phr->name(), $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
            // show an empty time selector
            if (!$has_time) {
                $time_phr = new phrase($this->user());
                $result .= '  <tr>';
                $result .= '    <td colspan="2">';

                log_debug('show time selector');
                $result .= $time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                $url_pos++;

                $result .= '    </td>';
                $result .= '    <td>' . \html\btn_del("Remove " . $time_phr->name(), $used_url) . '</td>';
                $result .= '  </tr>';
            }

            // show the new phrases
            log_debug('show new phrases');
            foreach ($this->ids() as $phr_id) {
                $result .= '  <tr>';
                if ($phr_id == 0) {
                    $result .= '    <td colspan="2">';

                    $phr_new = new phrase();
                    $result .= $phr_new->dsp_selector(null, $script, $url_pos, '', $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . \html\btn_del("Remove new", $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
        }

        $result .= $html->dsp_tbl_end();

        log_debug('table ended');
        $phr_ids_new = $this->ids();
        //$phr_ids_new[]  = $new_phrase_default;
        $phr_ids_new[] = 0;
        $type_ids_new = $type_ids;
        $type_ids_new[] = 0;
        $used_url = $this_url . $lib->ids_to_url($phr_ids_new, "phrase") .
            $lib->ids_to_url($type_ids_new, "type");
        $result .= '  ' . \html\btn_add("Add another phrase", $used_url);
        $result .= '  <br><br>';
        $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="back" value="' . $back . '">';
        if ($this->id() > 0) {
            $result .= '  to <input type="' . html_base::INPUT_TEXT . '" name="value" value="' . $this->number() . '">';
        } else {
            $result .= '  is <input type="' . html_base::INPUT_TEXT . '" name="value">';
        }
        $result .= $html->dsp_form_end("Save", $back);
        $result .= '<br><br>';
        log_debug('load source');
        $src = $this->load_source();
        if (isset($src)) {
            $scr_dsp = new source($src->api_json());
            $result .= $scr_dsp->dsp_select($script, $back);
            $result .= '<br><br>';
        }

        // display the share type
        $result .= $this->dsp_share($script, $back);

        // display the protection type
        $result .= $this->dsp_protection($script, $back);

        $result .= '<br>';
        $result .= \html\btn_back($back);

        // display the user changes
        log_debug('user changes');
        if ($this->id() > 0) {
            $changes = $this->dsp_hist(0, 0, '', $back);
            if (trim($changes) <> "") {
                $result .= $html->dsp_text_h3("Latest changes related to this value", "change_hist");
                $result .= $changes;
            }
            $changes = $this->dsp_hist_links(0, 0, '', $back);
            if (trim($changes) <> "") {
                $result .= $html->dsp_text_h3("Latest link changes related to this value", "change_hist");
                $result .= $changes;
            }
        } else {
            // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
            if (isset($main_wrd)) {
                $main_wrd->load();
                // TODO activate based on a group load
                /*
                $samples = $this->dsp_samples($main_wrd->id, $this->ids(), 10, $back);
                log_debug("value->dsp_edit samples.");
                if (trim($samples) <> "") {
                    $result .= $html->dsp_text_h3('Please have a look at these other "' . $main_wrd->dsp_obj()->name_linked(styles::STYLE_GREY) . '" values as an indication', 'change_hist');
                    $result .= $samples;
                }
                */
            }
        }

        log_debug("done");
        return $result;
    }

}
