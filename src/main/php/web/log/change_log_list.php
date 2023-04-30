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

namespace html\log;

use api\change_log_list_api;
use html\html_base;
use html\system\back_trace;

class change_log_list extends change_log_list_api
{

    /**
     * show all changes of a named user sandbox object e.g. a word as table
     * @param back_trace|null $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function tbl(back_trace $back = null, bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $html_text = $this->th($condensed, $with_users);
        foreach ($this->lst as $chg) {
            $html_text .= $html->td($chg->tr($back, $condensed, $with_users));
        }
        return $html->tbl($html->tr($html_text), html_base::STYLE_BORDERLESS);
    }

    /**
     * @return string with the html table header to show the changes of sandbox objects e.g. a words
     */
    private function th(bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $head_text = $html->th('time');
        if ($condensed) {
            $head_text .= $html->th('changed to');
        } else {
            if ($with_users) {
                $head_text .= $html->th('user');
            }
            $head_text .= $html->th_row(array('field','from','to'));
            $head_text .= $html->th('');  // extra column for the undo icon
        }
        return $head_text;
    }

}
