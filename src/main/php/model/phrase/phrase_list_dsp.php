<?php

/*

    phrase_list_dsp.php - based s a phrase list create HTML code to display it
    -------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class phrase_list_dsp extends phrase_list
{

    public array $lst = array();   // arr
    /*
      display functions
      -----------------

      the functions dsp_id and name should exist for all objects
      these function should never call any other function especially not debug functions,
      because only these two functions can be called from debug statements

    */


    // return a list of the phrase names
    function names(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $phr) {
                $result[] = $phr->name;
                if (!isset($phr->usr)) {
                    log_err('The user of a phrase list element differs from the list user.', 'phrase_list->names', 'The user of "' . $phr->name . '" is missing, but the list user is "' . $this->usr->name . '".', (new Exception)->getTraceAsString(), $this->usr);
                } elseif ($phr->usr <> $this->usr) {
                    log_err('The user of a phrase list element differs from the list user.', 'phrase_list->names', 'The user "' . $phr->usr->name . '" of "' . $phr->name . '" does not match the list user "' . $this->usr->name . '".', (new Exception)->getTraceAsString(), $this->usr);
                }
            }
        }
        log_debug('phrase_list->names (' . implode(",", $result) . ')');
        return $result;
    }

    // return a list of the phrase names with html links
    function names_linked(): array
    {
        log_debug('phrase_list->names_linked (' . dsp_count($this->lst) . ')');
        $result = array();
        foreach ($this->lst as $phr) {
            $result[] = $phr->display();
        }
        log_debug('phrase_list->names_linked (' . implode(",", $result) . ')');
        return $result;
    }

    // return a list of the phrase ids as a sql compatible text
    function ids_txt()
    {
        $result = dsp_array($this->id_lst());
        return $result;
    }

    // return one string with all names of the list without high quotes for the user, but not necessary as a unique text
    // e.g. >Company Zurich< can be either >"Company Zurich"< or >"Company" "Zurich"<, means either a triple or two words
    //      but this "short" form probably confuses the user less and
    //      if the user cannot change the tags anyway the saving of a related value is possible
    function name_dsp()
    {
        $result = implode(' ', $this->names());
        return $result;
    }

    // return one string with all names of the list with the link
    function name_linked()
    {
        $result = dsp_array($this->names_linked());
        return $result;
    }

    // offer the user to add a new value for these phrases
    // similar to value.php/btn_add
    function btn_add_value($back)
    {
        $result = btn_add_value($this, Null, $back);
        /*
        zu_debug('phrase_list->btn_add_value');
        $val_btn_title = '';
        $url_phr = '';
        if (!empty($this->lst)) {
          $val_btn_title = "add new value similar to ".htmlentities($this->name());
        } else {
          $val_btn_title = "add new value";
        }
        $url_phr = $this->id_url_long();

        $val_btn_call  = '/http/value_add.php?back='.$back.$url_phr;
        $result .= btn_add ($val_btn_title, $val_btn_call);
        zu_debug('phrase_list->btn_add_value -> done');
        */
        return $result;
    }

    function dsp_val_matrix($val_matrix): string
    {
        if ($val_matrix != null) {
            log_debug('word_list->dsp_val_matrix for ' . $val_matrix->dsp_id());
        }
        return '';
    }

}
