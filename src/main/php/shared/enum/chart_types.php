<?php

/*

    shared/enum/chart_types.php - the chart types that a value table can be shown as
    ---------------------------

    a chart is another rendering of the same table (see web/value/table_chart.php), so the type
    says only how the rows of the table are drawn and never which values are shown


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

enum chart_types: string
{
    // one row per table row with a dot for the number and a bar for its range, e.g. the
    // potential loss of each global problem
    case RANGE_BARS = 'range_bars';
    // one point per table row placed by two numbers of the row, e.g. the potential gain of a
    // solution against its initial effort
    case SCATTER = 'scatter';
    // one labelled point per table row placed by two numbers of the row with the lines of an
    // equal ratio of the two numbers, e.g. the potential gain of a solution against the
    // potential loss of its problem, so that the reader sees which problem has a big lever
    case LEVERAGE = 'leverage_plot';

    /**
     * @return int the number of value columns that the chart type plots
     */
    public function column_count(): int
    {
        return match ($this) {
            chart_types::RANGE_BARS => 1,
            chart_types::SCATTER, chart_types::LEVERAGE => 2,
        };
    }

}
