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

include_once HTML_PATH . 'html_selector.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';

use cfg\sandbox\sandbox_value;

class user_sandbox_display extends sandbox_value
{

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