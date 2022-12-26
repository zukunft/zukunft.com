<?php

/*

    api/log/change_log_list.php - a list function to create the HTML code to display a list of user changes
    ---------------------------

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

namespace api;

use back_trace;
use html\html_base;

class change_log_list_dsp extends change_log_list_api
{

    private bool $condensed = false;
    private bool $with_users = false;

    public function hist_named(): string
    {
        $result = ''; // reset the html code var

        $html = new html_base();
        $result .= dsp_tbl_start_hist();
        $row_nbr = 1;
        foreach ($this->lst() as $db_row) {
            // display the row only if the field is not an "admin only" field
            //if ($db_row["code_id"] <> formula::FLD_REF_TEXT) {
            $row_nbr++;
            $result .= '<tr>';
            if ($row_nbr == 1) {
                if ($this->condensed) {
                    $result .= '<th>time</th>';
                    $result .= '<th>changed to</th>';
                } else {
                    $result .= '<th>time</th>';
                    if ($this->type <> 'user') {
                        $result .= '<th>user</th>';
                    }
                    $result .= '<th>field</th>';
                    $result .= '<th>from</th>';
                    $result .= '<th>to</th>';
                    $result .= '<th></th>'; // extra column for the undo icon
                }
            }
            $result .= '</tr><tr>';

            // pick the useful field name
            $txt_fld = '';
            if ($db_row[sql_db::FLD_CODE_ID] == "value") {
                $txt_fld .= $db_row['type'] . ' value';
                /* because changing the words creates a new value there is no need to display the words here
                if ($db_row['row_id'] > 0) {
                  $val = New value;
                  $val->id = $db_row['row_id'];
                  $val->usr = $this;
                  $val->load();
                  $val->load_phrases();
                  $txt_fld .= '<td>';
                  if (isset($val->wrd_lst)) {
                    $txt_fld .= implode(",",$val->wrd_lst->names_linked());
                  }
                  $txt_fld .= '</td>';
                } else {
                  $txt_fld .= '<td>'.$db_row['type'].' value</td>';
                }
                */
            } elseif ($this->type <> 'user') {
                $txt_fld .= $db_row['type_field'];
                // probably not needed to display the action, because this can be seen by the change itself
                // $result .= $db_row['type'].' '.$db_row['type_field'];
            } else {
                $txt_fld .= $db_row['type_table'] . ' ' . $db_row['type_field'];
            }

            // create the description for the old and new field value for the user
            $txt_old = $db_row["old"];
            $txt_new = $db_row["new"];
            // encode of text
            if ($db_row["code_id"] == formula::FLD_ALL_NEEDED) {
                if ($txt_old == "1") {
                    $txt_old = "all values needed for calculation";
                } else {
                    $txt_old = "calculate if one value is set";
                }
                if ($txt_new == "1") {
                    $txt_new = "all values needed for calculation";
                } else {
                    $txt_new = "calculate if one value is set";
                }
            }
            /* no encoding needed for this field at the moment
            if ($db_row["code_id"] == DBL_FLD_FORMULA_TYPE) {
              if ($txt_old <> "") { $txt_old = 'type '.$txt_old; }
              if ($txt_new <> "") { $txt_new = 'type '.$txt_new; }
            }
            */

            if ($this->condensed) {
                $result .= '<td>' . $db_row["time"];
                if ($this->type <> 'user') {
                    $result .= ' by ' . $db_row["user_name"];
                }
                $result .= '</td>';
                $result .= '<td>' . $txt_fld . ': ' . $txt_new . '</td>';
            } else {
                $result .= '<td>' . $db_row["time"] . '</td>';
                if ($this->type <> 'user') {
                    $result .= '<td>' . $db_row["user"] . '</td>';
                }


                // display the change
                $result .= '<td>' . $txt_fld . '</td>';
                $result .= '<td>' . $txt_old . '</td>';
                $result .= '<td>' . $txt_new . '</td>';
                // switched of because "less seems to be more"
                //if ($txt_old == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_old.'</td>'; }
                //if ($txt_new == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_new.'</td>'; }
            }

            // encode the undo action
            $undo_text = '';
            $undo_call = '';
            $undo_btn = '';
            if ($this->type == 'word') {
                if ($db_row['type'] == 'add') {
                    $undo_call = $html->url('value' . api::REMOVE, $this->id, $this->back);
                    $undo_btn = (new button('delete this value', $undo_call))->undo();
                }
            } elseif ($this->type == 'value') {
                if ($db_row['type'] == 'add') {
                    $undo_btn = $this->obj->btn_undo_add_value($this->back);
                }
            } elseif ($this->type == 'formula') {
                if ($db_row['type'] == 'update') {
                    $undo_call = $html->url(formula::class . api::UPDATE, $db_row["row_id"], $this->back . '&undo_change=' . $db_row["change_id"]);
                    $undo_btn = (new button('revert this change', $undo_call))->undo();
                }
            }
            // display the undo button
            if ($this->condensed) {
                if ($undo_call <> '') {
                    $result .= ' ' . $undo_btn;
                } else {
                    $result .= '';
                }
            } else {
                if ($undo_call <> '') {
                    $result .= '<td>' . $undo_btn . '</td>';
                } else {
                    $result .= '<td></td>';
                }
            }

            $result .= '</tr>';
            //}
        }
        $result .= dsp_tbl_end();

        return $result;
    }

    /**
     * show all changes of a named user sandbox object e.g. a word as table
     * @param back_trace $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    private function tbl(back_trace $back): string
    {
        $html = new html_base();
        $html_text = $this->th();
        foreach ($this->lst as $wrd) {
            $lnk = $wrd->dsp_obj()->dsp_link($back);
            $html_text .= $html->td($lnk);
        }
        return $html->tbl($html->tr($html_text), html_base::STYLE_BORDERLESS);
    }

    /**
     * @return string with the html table header to show the changes of sandbox objects e.g. a words
     */
    private function th(): string
    {
        $html = new html_base();
        $head_text = $html->th('time');
        if ($this->condensed) {
            $head_text .= $html->th('changed to');
        } else {
            if ($this->with_users) {
                $head_text .= $html->th('user');
            }
            $head_text .= $html->th_row(array('field','from','to'));
            $head_text .= $html->th('');  // extra column for the undo icon
        }
        return $head_text;
    }

    /**
     * @return string with the html code to show one row of the changes of sandbox objects e.g. a words
     */
    private function tr(): string
    {
        $html = new html_base();
        $head_text = $html->th('time');
        if ($this->condensed) {
            $head_text .= $html->th('changed to');
        } else {
            if ($this->with_users) {
                $head_text .= $html->th('user');
            }
            $head_text .= $html->th_row(array('field','from','to'));
            $head_text .= $html->th('');  // extra column for the undo icon
        }
        return $head_text;
    }

}
