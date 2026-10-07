<?php

/*

    web/value/table_model.php - the rows and columns of a value table before they are rendered
    -------------------------

    built once by value_list::table_model from the values and the column definitions, so that
    the html table (value_list::table_by_related_columns) and the svg chart
    (value_list::table_to_svg) show the same rows, columns, units and ranges


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

namespace Zukunft\ZukunftCom\main\php\web\value;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'sandbox_value.php';
include_once html_paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\shared\const\words;

class table_model
{

    // the phrases that every value carries, which the table header names once
    public phrase_list $tbl_phr;
    // per row key the html link list of the phrases that name the row
    public array $row_label = [];
    // per row key and column id the values of the cell
    public array $cells = [];
    // per row key and phrase column id the phrase of the row shown in that column
    public array $phr_cells = [];
    // the ids of the columns to show, the leftmost column first
    public array $col_ids = [];
    // the columns that hold a value, keyed by column id
    public array $col_phr = [];
    // per value column id the phrase shown in its header, e.g. "loss" for the column of
    // "potential loss" if the table names "potential" for every value (see value_list::column_head)
    public array $col_head = [];
    // the columns that name a phrase of the row, keyed by column id
    public array $phr_col = [];
    // per value column id the unit phrases of the column, the scaling first
    public array $col_unit = [];
    // per value column id the names that select the column e.g. "potential loss" and "loss"
    public array $col_names = [];
    // the keys of the rows shown, in the order of the impact
    public array $shown_keys = [];
    // the number of rows behind the shown rows
    public int $rows_behind = 0;
    // the position of the first shown row and the number of rows shown per page
    public int $first_row = 0;
    public int $row_limit = 0;

    function __construct()
    {
        $this->tbl_phr = new phrase_list();
    }

    /**
     * @return array the ids of the shown columns that hold a value, the leftmost first
     */
    function value_col_ids(): array
    {
        return array_values(array_filter($this->col_ids,
            fn($col_id) => array_key_exists($col_id, $this->col_phr)));
    }

    /**
     * @return array the ids of the shown columns that name a phrase of the row, the leftmost first
     */
    function phrase_col_ids(): array
    {
        return array_values(array_filter($this->col_ids,
            fn($col_id) => array_key_exists($col_id, $this->phr_col)));
    }

    /**
     * @param string $name a name that selects a value column e.g. "loss" or "potential loss"
     * @return int|string|null the id of the first shown value column with that name or null if none has it
     */
    function col_id_by_name(string $name): int|string|null
    {
        $result = null;
        foreach ($this->value_col_ids() as $col_id) {
            if ($result === null and in_array($name, $this->col_names[$col_id] ?? [])) {
                $result = $col_id;
            }
        }
        return $result;
    }

    /**
     * the numbers of one cell sorted by their role, like value_list::cell shows them
     *
     * @param string $row_key the key of the row
     * @param int|string $col_id the id of the value column
     * @return array the centre value, the low bound, the high bound and the confidence of the
     *               cell, each null if the cell has none
     */
    function cell_numbers(string $row_key, int|string $col_id): array
    {
        $centre = null;
        $low = null;
        $high = null;
        $conf = null;
        foreach ($this->cells[$row_key][$col_id] ?? [] as $val) {
            $names = $val->grp->phr_lst()->names();
            if (in_array(words::CONFIDENCE, $names)) {
                $conf = $val;
            } elseif (in_array(words::LOW, $names)) {
                $low = $val;
            } elseif (in_array(words::HIGH, $names)) {
                $high = $val;
            } elseif ($centre === null) {
                $centre = $val;
            }
        }
        return [$centre, $low, $high, $conf];
    }

}
