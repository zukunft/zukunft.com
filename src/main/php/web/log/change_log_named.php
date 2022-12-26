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
use controller\log\change_log_named_api;
use html\html_base;

class change_log_named_dsp extends change_log_named_api
{

    private bool $condensed = false;
    private bool $with_users = false;



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
