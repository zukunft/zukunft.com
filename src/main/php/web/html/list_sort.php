<?php

/*

    web/html/list_sort.php - deprecated placeholder of the fixed start page spreadsheet
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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

/**
 * the empty placeholder of the spreadsheet that the start page has shown before
 *
 * the start page now shows the values of the "global problem" phrase as a table built from the
 * data (ui_list::start_list), so this class no longer hard codes the column headers, the five
 * problem rows and their numbers; the class is kept because the spreadsheet component of the
 * start view is meant to become a changeable sheet with words, numbers and formulas
 */
class list_sort
{

    /**
     * @param phrase $phr the start phrase to select the rows
     * @param user_message $msg to collect the load warnings for the user
     * @param data_object|null $cac the data cache use to reduce the backend traffic
     * @return string always empty, because a spreadsheet is not implemented yet
     * @deprecated the fixed rows are replaced by ui_list::start_list, which shows the values of
     *             the given phrase as a table with the columns defined by the column tiers
     */
    function list_sort(
        phrase       $phr,
        user_message $msg,
        ?data_object $cac = null
    ): string
    {
        return '';
    }

}
