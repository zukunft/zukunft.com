<?php

/*

  display_list.php - to display a list that can be sorted
  ----------------
  
  this should be as easy as possible that's why it got its own class
  e.g. the list of display components related to a display view
  
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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED . 'library.php';

use shared\library;

class display_list extends html_base
{

    // the parameters
    public ?array $lst = null; // a array of objects that must have id and name
    public ?string $id_field = null; //
    public string $script_name = ''; // name of the code that handles the list
    public string $class_edit = ''; // the class name
    public ?string $script_parameter = null; //

    // display a list that can be sorted using the fixed field "order_nbr"
    function display(string $back = ''): string
    {
        $result = '';

        $lib = new library();

        // set the default values
        $row_nbr = 0;
        $num_rows = count($this->lst);
        foreach ($this->lst as $entry) {
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // list of all possible view components
            $row_nbr = $row_nbr + 1;
            $edit_script = $this->edit_url($this->class_edit);
            $result .= '<a href="/http/' . $edit_script . '?id=' . $entry->id . '&back=' . $this->script_parameter . '">' . $entry->name . '</a> ';
            if ($row_nbr > 1) {
                $result .= '<a href="/http/' . $this->script_name . '?id=' . $this->script_parameter . '&move_up=' . $entry->id . '">up</a>';
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            if ($row_nbr < $num_rows) {
                $result .= '<a href="/http/' . $this->script_name . '?id=' . $this->script_parameter . '&move_down=' . $entry->id . '">down</a>';
            }
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '</td><td>';
            }
            $result .= ' ';
            $result .= \html\btn_del('Delete component', $this->script_name . '?id=' . $this->script_parameter . '&del=' . $entry->id);
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '</td></tr>';
            }
            $result .= '<br>';
        }

        return $result;
    }

}
