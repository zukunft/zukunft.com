<?php

/*

    shared/enum/table_forms.php - the forms a value table can be shown in
    ---------------------------

    selected by the "as" entries of the "..." menu of a table (url_var::DISPLAY_LIST_AS): the
    rows as a html table, the default charts of the table beside each other, or both


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum table_forms: string
{
    // the rows and columns as a html table, the default
    case TABLE = 'table';
    // the default charts of the table beside each other (triples::SYSTEM_CHART_TYPE_DEFAULT)
    case CHART = 'chart';
    // the table followed by its default charts
    case TABLE_AND_CHART = 'table_chart';

    /**
     * @return messages the message id of the name shown in the "as" entries of the table menu
     */
    public function msg_id(): messages
    {
        return match ($this) {
            table_forms::TABLE => messages::TABLE_AS_TABLE,
            table_forms::CHART => messages::TABLE_AS_CHART,
            table_forms::TABLE_AND_CHART => messages::TABLE_AS_TABLE_CHART,
        };
    }

    /**
     * @return bool true if the html table is shown
     */
    public function with_table(): bool
    {
        return $this != table_forms::CHART;
    }

    /**
     * @return bool true if the default charts of the table are shown
     */
    public function with_chart(): bool
    {
        return $this != table_forms::TABLE;
    }

}
