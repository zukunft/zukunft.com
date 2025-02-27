<?php

/*

    web/html/list_sort.php - create the html code to display a sortable list
    ----------------------


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

include_once WEB_HELPER_PATH . 'data_object.php';
include_once WEB_HTML_PATH . 'table.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';

use html\helper\data_object;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\word\triple;
use html\word\word;

class list_sort
{


    /**
     * TODO review
     * @param phrase $phr the start phrase to select the rows
     * @param data_object|null $dbo the data cache use to reduce the backend traffic
     * @return string html code to display a spreadsheet
     */
    function list_sort(
        phrase      $phr,
        data_object $dbo = null
    ): string
    {
        // create the table
        $tbl = new table();

        // add the main column
        $tbl->add_column($phr);

        // get the phrases for the rows
        // from "global problem" to e.g. "climate change"
        //$phr_lst = $phr->is_or_can_be($dbo?->phrase_list());

        /*
         * outline of the remaining target solution

        // get the most relevant result
        $tbl->add_column($phr_lst->result_most_relevant());

        // fill the space with the most relevant related phrases and numbers
        // if a solution exists and the table has enough space add the solution
        $col_nbr = $tbl->target_columns(); // 1 expected
        $col_phr_lst = $phr_lst->phrases_most_relevant($col_nbr);

        foreach ($col_phr_lst as $col_phr)
        {
            $tbl->add_column($col_phr);

            // get the most relevant result
            $tbl->add_column($col_phr->result_most_relevant());
        }

        // if the list is sorted start it with a ranking column
        $rank_phr = new phrase(phrases::RANKING);
        $tbl->add_first_column($rank_phr);

        // show the table

        */

        // temp hardcoded solution

        $html = new html_base();
        $col_lst = new phrase_list();


        $result = '<thead>
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
        $trp->load_by_name('climate warming');
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
    </tr>';
        $result .= '<tr>';
        $result .= '<th scope="row"></th>';
        $result .= '<td>';
        // temp code
        $result .= $phr->button_add_triple($phr->id());
        $result .= '</td>';
        $result .= '<td class="text-right"></td>';
        $result .= '<td>';
        // temp code
        $result .= $phr->button_add_triple($phr->id());
        $result .= '<td class="text-right"></td>';
        $result .= '</tr>';
        $result .= '</tbody>';
        return $html->tbl($result, styles::TABLE_PUR);
    }

}
