<?php

/*

  user_sandbox_display.php - extends the user sandbox superclass for common display functions
  ------------------------
  
  
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

namespace html\sandbox;

use html\html_selector;
use model\sandbox_value;

class user_sandbox_display extends sandbox_value
{

    // create the HTML code to display the protection setting (but only if allowed)
    function dsp_share($form_name, $back): string
    {
        log_debug($this->dsp_id());
        $result = ''; // reset the html code var

        // only the owner can change the share type (TODO or an admin)
        if ($this->user()->id() == $this->owner_id) {
            $sel = new html_selector;
            $sel->form = $form_name;
            $sel->name = "share";
            $sel->sql = sql_lst("share_type");
            $sel->selected = $this->share_id;
            $sel->dummy_text = 'please define the share level';
            $result .= 'share type ' . $sel->display() . ' ';
        }

        log_debug($this->dsp_id() . ' done');
        return $result;
    }

    // create the HTML code to display the protection setting (but only if allowed)
    function dsp_protection($form_name, $back): string
    {
        log_debug($this->dsp_id());
        $result = ''; // reset the html code var

        // only the owner can change the protection level (TODO or an admin)
        if ($this->user()->id() == $this->owner_id) {
            $sel = new html_selector;
            $sel->form = $form_name;
            $sel->name = "protection";
            $sel->sql = sql_lst("protection_type");
            $sel->selected = $this->protection_id;
            log_debug($this->dsp_id() . ' id ' . $this->protection_id);
            $sel->dummy_text = 'please define the protection level';
            $result .= 'protection ' . $sel->display() . ' ';
        }

        log_debug($this->dsp_id() . ' done');
        return $result;
    }

    /**
     * display word changes by the user which are not (yet) standard
     */
    function dsp_sandbox_wrd($user_id, $back_link)
    {
        global $db_con;

        log_debug('zuu_dsp_sandbox_wrd(u' . $user_id . ')');
        $result = ''; // reset the html code var

        // get word changes by the user that are not standard
        $sql = "SELECT u.word_name AS usr_word_name, 
                 t.word_name, 
                 t.word_id 
            FROM user_words u,
                 words t
           WHERE u.user_id = " . $user_id . "
             AND u.word_id = t.word_id;";
        $sql_result = $db_con->get_old($sql);

        // prepare to show the word link
        $row_nbr = 0;
        $result .= '<table>';
        foreach ($sql_result as $wrd_row) {
            $row_nbr++;
            $result .= '<tr>';
            if ($row_nbr == 1) {
                $result .= '<th>Your name vs. </th><th>common name</th></tr><tr>';
            }
            $result .= '<td>' . $wrd_row[0] . '</td><td>' . $wrd_row[1] . '</td>';
            //$result .= '<td><a href="/http/user.php?id='.$user_id.'&undo_word='.$wrd_row[2].'&back='.$id.'"><img src="/src/main/resources/images/button_del_small.jpg" alt="undo change"></a></td>';
            $url = "/http/user.php?id='.$user_id.'&undo_word='.$wrd_row[2].'&back='.$back_link.'";
            $result .= '<td>' . \html\btn_del("Undo your change and use the standard word " . $wrd_row[1], $url) . '</td>';
            $result .= '</tr>';
        }
        $result .= '</table>';

        log_debug('done');
        return $result;
    }

    /**
     * display formula changes by the user which are not (yet) standard
     */
    function dsp_sandbox_frm($user_id, $back_link)
    {
        global $db_con;
        log_debug('dsp_sandbox_frm(u' . $user_id . ')');
        $result = ''; // reset the html code var

        // get word changes by the user that are not standard
        $sql = "SELECT u.formula_name, 
                 u.resolved_text AS usr_formula_text, 
                 f.resolved_text AS formula_text, 
                 f.formula_id 
            FROM user_formulas u,
                 formulas f
           WHERE u.user_id = " . $user_id . "
             AND u.formula_id = f.formula_id;";
        $sql_result = $db_con->get_old($sql);

        // prepare to show the word link
        $row_nbr = 0;
        $result .= '<table>';
        foreach ($sql_result as $wrd_row) {
            $row_nbr++;
            $result .= '<tr>';
            if ($row_nbr == 1) {
                $result .= '<th>Formula name </th>';
                $result .= '<th>Your formula vs. </th>';
                $result .= '<th>common formula</th>';
                $result .= '</tr><tr>';
            }
            $result .= '<td>' . $wrd_row[0] . '</td>';
            $result .= '<td>' . $wrd_row[1] . '</td>';
            $result .= '<td>' . $wrd_row[2] . '</td>';
            //$result .= '<td><a href="/http/user.php?id='.$user_id.'&undo_formula='.$wrd_row[3].'&back='.$id.'"><img src="/src/main/resources/images/button_del_small.jpg" alt="undo change"></a></td>';
            $url = "/http/user.php?id='.$user_id.'&undo_formula='.$wrd_row[3].'&back='.$back_link.'";
            $result .= '<td>' . \html\btn_del("Undo your change and use the standard formula " . $wrd_row[2], $url) . '</td>';
            $result .= '</tr>';
        }
        $result .= '</table>';

        log_debug('done');
        return $result;
    }
}