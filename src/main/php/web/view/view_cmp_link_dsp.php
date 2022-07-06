<?php

/*

    view_cmp_link_dsp.php - create HTML code to display a view component links
    ---------------------

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

class view_cmp_link_dsp extends view_cmp_link
{

    /**
     * return the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if (isset($this->fob) and isset($this->tob)) {
            if ($this->fob->name <> '' and $this->tob->name <> '') {
                $result .= '"' . $this->tob->name . '" in "'; // e.g. Company details
                $result .= $this->fob->name . '"';     // e.g. cash flow statement
            }
        } else {
            $result .= 'view component objects not set';
        }
        return $result;
    }

    /**
     * return the html code to display the link name with the hyperlink to the link
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        $this->load_objects();
        if (isset($this->fob)
            and isset($this->tob)) {
            $result = $this->fob->name_linked(NULL, $back) . ' to ' . $this->tob->name_linked($back);
        } else {
            $result .= log_err("The view name or the component name cannot be loaded.", "view_component_link->name");
        }

        return $result;
    }

}
