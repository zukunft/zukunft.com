<?php

/*

    /web/system/error_log_list.php - the display extension of the system error log api object
    -----------------------------


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

use api\system_error_log_list_api;
use user;

class system_error_log_list_dsp extends system_error_log_list_api
{

    /**
     * display the error that are related to the user, so that he can track when they are closed
     * or display the error that are related to the user, so that he can track when they are closed
     * called also from user_display.php/dsp_errors
     * @param user|null $usr e.g. an admin user to allow updating the system errors
     * @param string $back
     * @return string the html code of the system log
     */
    function get_html(user $usr = null, string $back = ''): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var
        $rows = '';   // the html code of the rows

        if (count($this->system_errors) > 0) {
            // prepare to show the word link
            $log_dsp = $this->system_errors[0];
            if ($log_dsp->time <> '') {
                $row_nbr = 0;
                foreach ($this->system_errors as $log_dsp) {
                    $row_nbr++;
                    if ($row_nbr == 1) {
                        $rows .= $this->headline_html();
                    }
                    $rows .= $log_dsp->get_html($usr, $back);
                }
                $result = $html->tbl($rows);
            }
        }

        return $result;
    }


    function get_html_page(user $usr = null, string $back = ''): string
    {
        return parent::get_html_header('System log') . $this->get_html($usr, $back) . parent::get_html_footer();
    }

    /**
     * @return string the HTML code for the table headline
     * should be corresponding to system_error_log_dsp::get_html
     */
    private function headline_html(): string
    {
        $result = '<tr>';
        $result .= '<th> creation time     </th>';
        $result .= '<th> user              </th>';
        $result .= '<th> issue description </th>';
        $result .= '<th> trace             </th>';
        $result .= '<th> program part      </th>';
        $result .= '<th> owner             </th>';
        $result .= '<th> status            </th>';
        $result .= '</tr>';
        return $result;
    }

}
