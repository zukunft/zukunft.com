<?php

/*

  display_selector.php - to select a word (or formula or verb)
  --------------------
  
  this should be as easy as possible that's why it got its own class
  
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

class selector
{

    // the parameters
    public ?user $usr = null; // if 0 (not NULL) for standard values, otherwise for a user specific values
    public string $name = '';   // the HTML form field name
    public string $form = '';   // the name of the HTML form
    public string $label = '';   // the label of the HTML form
    public string $bs_class = '';   // to add addition class information for the bootstrap version
    public string $attribute = '';   // to add addition attribute information for the bootstrap version e.g. display an disabled selector
    public string $sql = '';   // query to select the items
    public $lst = null; // list of objects from which the user can select
    public ?int $selected = null; // id of the selected object
    public string $dummy_text = '';   // text for the NULL result if allowed

    function display(): string
    {
        log_debug('selector->display (' . $this->name . ',' . $this->form . ',' . $this->sql . ',s' . $this->selected . ',' . $this->dummy_text . ')');

        global $db_con;

        $result = dsp_form_fld_select($this->form, $this->name, $this->label, $this->bs_class, $this->attribute);

        /*
        if ($this->dummy_text == '') {
            $this->dummy_text == 'please select ...';
        }
        */

        if ($this->selected == 0) {
            $result .= '<option value="0" selected>' . $this->dummy_text . '</option>';
        }

        // check if list needs to be reloaded
        $db_lst = $this->lst;
        if ($this->sql != '') {
            $db_con->usr_id = $this->usr->id;
            $db_lst = $db_con->get($this->sql);
        }
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $row_option = '';
                if ($db_entry['id'] == $this->selected and $this->selected <> 0) {
                    log_debug('selector->display ... selected ' . $db_entry['id']);
                    $row_option = ' selected';
                }
                $result .= '<option value="' . $db_entry['id'] . '" ' . $row_option . ' >' . $db_entry['name'] . '</option>';
            }
        }

        $result .= dsp_form_fld_select_end();

        log_debug('selector->display ... done');
        return $result;
    }

}
