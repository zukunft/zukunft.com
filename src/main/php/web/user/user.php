<?php

/*

    user_dsp.php - functions to create the HTML code to display a the user setup and log information
    ------------

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

use api\user_api;

class user_dsp extends user_api
{

    const FORM_EDIT = 'user_edit';

    /**
     * display a form with the user parameters such as name or email
     */
    function form_edit($back): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var

        if ($this->id > 0) {
            // display the user fields using a table and not using px in css to be independent of any screen solution
            $header = $html->text_h2('User "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            $hidden_fields .= $html->form_hidden("back", $back);
            $detail_fields = $html->form_text("username", $this->name);
            $detail_fields .= $html->form_text("email", $this->email);
            $detail_fields .= $html->form_text("first name", $this->first_name);
            $detail_fields .= $html->form_text("last name", $this->last_name);
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(self::FORM_EDIT, $hidden_fields . $detail_row)
                . '<br>';
        }

        return $result;
    }


}
