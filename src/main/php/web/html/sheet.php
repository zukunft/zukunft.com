<?php

/*

    web/html/sheet.php - create the html code to display a spreadsheet
    ------------------


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

use cfg\component\sheet\position_list;
use cfg\data_object;

class sheet
{


    /**
     * TODO fill it based on the parameters
     * @return string html code to display a spreadsheet
     */
    function calc_sheet(?data_object $data = null, ?position_list $pos_lst = null): string
    {
        // loop over the position list and get the related object
        return '<table>
  <tr>
    <th>Priority</th>
    <th>Problem</th>
    <th>Solution</th>
    <th>Gain</th>
    <th></th>
  </tr>
  <tr>
    <td>1</td>
    <td>Climate warming</td>
    <td>reduce climate gas emissions</td>
    <td>2.4</td>
    <td>b htp</td>
  </tr>
  <tr>
    <td>2</td>
    <td>Populism</td>
    <td>avoid wrong decisions</td>
    <td>1.5</td>
    <td>b htp</td>
  </tr>
  <tr>
    <td>3</td>
    <td>Health</td>
    <td>research</td>
    <td>700</td>
    <td>m htp</td>
  </tr>
  <tr>
    <td>4</td>
    <td>Povertiy</td>
    <td>taxes</td>
    <td>300</td>
    <td>m htp</td>
  </tr>
  <tr>
    <td>5</td>
    <td>Education</td>
    <td>spending</td>
    <td>250</td>
    <td>m htp</td>
  </tr>
</table>';
    }

}
