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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHEET . 'position_list.php';

use html\helper\data_object;
use html\word\triple;
use html\word\word;
use html\component\sheet\position_list;

class sheet
{


    /**
     * TODO fill it based on the parameters
     * @return string html code to display a spreadsheet
     */
    function calc_sheet(?data_object $data = null, ?position_list $pos_lst = null): string
    {
        // loop over the position list and get the related object

        $result = '<table class="table">
  <thead>
    <tr>
      <th scope="col">Priority</th>
      <th scope="col">Problem</th>
      <th class="text-right" scope="col">Costs in ';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('trillion');
        $result .= $wrd->name_link();
        $result .= ' ';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('USD');
        $result .= $wrd->name_link();
        $result .= '</th>
      <th scope="col">Solution</th>
      <th class="text-right" scope="col">Gain in ';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('billion');
        $result .= $wrd->name_link();
        $result .= ' ';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('htp');
        $result .= $wrd->name_link();
        $result .= '</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>';
        // temp code
        $trp = new triple();
        $trp->load_by_name('global warming');
        $result .= $trp->name_link();
        $result .= '</td>
      <td class="text-right">31.5</td>
      <td>reduce climate gas emissions</td>
      <td class="text-right">35.2</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('populism');
        $result .= $wrd->name_link();
        $result .= '</td>
      <td class="text-right">23.8</td>
      <td>avoid wrong decisions</td>
      <td class="text-right">34.1</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('health');
        $result .= $wrd->name_link();
        $result .= '</td>
      <td class="text-right">20.4</td>
      <td>research</td>
      <td class="text-right">34.1</td>
    </tr>
    <tr>
      <th scope="row">4</th>
      <td>';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('poverty');
        $result .= $wrd->name_link();
        $result .= '</td>
      <td class="text-right">13.6</td>
      <td>taxes</td>
      <td class="text-right">8.8</td>
    </tr>
    <tr>
      <th scope="row">5</th>
      <td>';
        // temp code
        $wrd = new word();
        $wrd->load_by_name('education');
        $result .= $wrd->name_link();
        $result .= '</td>
      <td class="text-right">9.4</td>
      <td>spending</td>
      <td class="text-right">14.3</td>
    </tr>
  </tbody>
</table>';
        return $result;
    }

}
