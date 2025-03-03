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
include_once WEB_HTML_PATH . 'scopes.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'words.php';

use html\helper\data_object;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\word\triple;
use html\word\word;
use shared\const\words;

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
        $phr_lst = $phr->is_or_can_be($dbo?->phrase_list());

        // TODO remove temp hardcoded solution
        if ($phr_lst->is_empty()) {
            $phr_lst = new phrase_list();
            $trp = new triple();
            $trp->load_by_name('climate warming');
            $phr_lst->add($trp->phrase());
            $wrd = new word();
            $wrd->load_by_name('populism');
            $phr_lst->add($wrd->phrase());
            $wrd = new word();
            $wrd->load_by_name('health');
            $phr_lst->add($wrd->phrase());
            $wrd = new word();
            $wrd->load_by_name('poverty');
            $phr_lst->add($wrd->phrase());
            $wrd = new word();
            $wrd->load_by_name('education');
            $phr_lst->add($wrd->phrase());
            $trillion = new word();

            $trillion->load_by_name('trillion');
            $billion = new word();
            $billion->load_by_name('billion');
            $usd = new word();
            $usd->load_by_name('USD');
            $htp = new word();
            $htp->load_by_name('htp');
        } else {
            $trillion = $phr_lst->get_by_name(words::TRILLION);
            $billion = $phr_lst->get_by_name(words::BILLION);
            $usd = $phr_lst->get_by_name(words::USD);
            $htp = $phr_lst->get_by_name(words::HTP);
        }

        // get the most relevant result
        //$tbl->add_column($phr_lst->result_phrases_most_relevant());

        /*
         * outline of the remaining target solution

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

        $html = new html_base();
        $col_lst = new phrase_list();
        // add phrase_views class: a phrase_list with a selected component and component parameters



        $th = $html->th('Priority', scopes::COL);
        $th .= $html->th('Problem', scopes::COL);
        $th .= $html->th('Costs in ' . $trillion->name_link() . ' ' . $usd->name_link(),
            scopes::COL, styles::TEXT_RIGHT);
        $th .= $html->th('Solution', scopes::COL);
        $th .= $html->th('Gain in ' . $billion->name_link() . ' ' . $htp->name_link(),
            scopes::COL, styles::TEXT_RIGHT);
        $tr = $html->tr($th);
        $thead = $html->thead($tr);
        $result = $thead;
        $tr = '';
        $row = 1;
        foreach ($phr_lst->lst() as $row_phr) {
            $td = $html->th($row, scopes::ROW);
            $td .= $html->td($row_phr->name_link());
            // TODO remove temp hardcoded solution
            if ($row == 1) {
                $td .= $html->td("31.5", styles::TEXT_RIGHT);
                $td .= $html->td("reduce climate gas emissions");
                $td .= $html->td("35.2", styles::TEXT_RIGHT);
            }
            if ($row == 2) {
                $td .= $html->td("23.8", styles::TEXT_RIGHT);
                $td .= $html->td("avoid wrong decisions");
                $td .= $html->td("34.1", styles::TEXT_RIGHT);
            }
            if ($row == 3) {
                $td .= $html->td("20.4", styles::TEXT_RIGHT);
                $td .= $html->td("research");
                $td .= $html->td("34.1", styles::TEXT_RIGHT);
            }
            if ($row == 4) {
                $td .= $html->td("13.6", styles::TEXT_RIGHT);
                $td .= $html->td("taxes");
                $td .= $html->td("8.8", styles::TEXT_RIGHT);
            }
            if ($row == 5) {
                $td .= $html->td("9.4", styles::TEXT_RIGHT);
                $td .= $html->td("spending");
                $td .= $html->td("14.3", styles::TEXT_RIGHT);
            }
            $tr .= $html->tr($td);
            $row++;
        }

        // footer row to extend the table
        $td = $html->th('', scopes::ROW);
        $td .= $html->td($phr->button_add_triple($phr->id()));
        $td .= $html->td("", styles::TEXT_RIGHT);
        $td .= $html->td($phr->button_add_triple($phr->id()));
        $td .= $html->td("", styles::TEXT_RIGHT);
        $tr .= $html->tr($td);
        $tbody = $html->tbody($tr);

        return $html->tbl($thead . $tbody, styles::TABLE_PUR);
    }

}
