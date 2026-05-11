<?php

/*

    web/html/display_list.php - to display a list that can be sorted
    -------------------------

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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class display_list extends html_base
{

    // the parameters
    public ?array $lst = null; // a array of objects that must have id and name
    public ?string $id_field = null; //
    public string $script_name = ''; // name of the code that handles the list
    public ?string $script_parameter = null; //

    /**
     * TODO Prio 1 review
     * create the html code for a list that can be sorted using the fixed field "order_nbr"
     * @param string $class the class of the objects in the lis
     */
    function display(string $class, int $id, string $back = ''): string
    {
        global $mtr;
        $result = '';

        $msk_c = new views();
        $msk_id = $msk_c->class_to_edit($class);

        // set the default values
        $row_nbr = 0;
        $num_rows = count($this->lst);
        foreach ($this->lst as $entry) {
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // list of all possible view components
            $row_nbr = $row_nbr + 1;
            $result .= $this->ref_view($msk_id, $entry->id, $entry->name) . ' ';
            // add a link to move this item up if not the first item
            if ($row_nbr > 1) {
                $result = $this->ref_view($msk_id, $entry->id, $mtr->txt(msg_id::UP), url_var::UP) . ' ';
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            // add a link to move this item down if not the last item
            if ($row_nbr < $num_rows) {
                $result = $this->ref_view($msk_id, $entry->id, $mtr->txt(msg_id::DOWN), url_var::DOWN) . ' ';
            }
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '</td><td>';
            }
            $result .= ' ';
            $result .= \Zukunft\ZukunftCom\main\php\web\btn_del('Delete component', $this->script_name . '?id=' . $this->script_parameter . '&del=' . $entry->id);
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '</td></tr>';
            }
            $result .= '<br>';
        }

        return $result;
    }

}
