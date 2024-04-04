<?php

/*

    cfg/import/convert_wikipedia_table.php - convert a wikipedia table to a
    --------------------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\import;


use cfg\library;

class convert_wikipedia_table
{

    const TABLE_START = '{| class=';
    const TABLE_END = '|}';
    const CLASS_NAME = '"wikitable sortable mw-datatable"';

    const ROW_END = "\n";
    const ROW_MAKER = '|-';
    const COL_MAKER_HEADER = '!';
    const COL_MAKER = '|';
    const NUMBER_MAKER = "'''";
    const LINK_MAKER = "{{";
    const LINK_MAKER_END = "}}";
    const ITEM_IGNORE_MAKER = "rowspan";
    const SORT_MAKER = "sort";

    /**
     * convert a wikipedia table to a zukunft.com json string
     * @param string $wiki_tbl
     * @return string
     */
    function convert(string $wiki_tbl): string
    {
        $lib = new library();
        $result = '';
        $col_names = [];
        $rows = [];
        $row = [];
        $tbl_str = $lib->str_right_of($wiki_tbl, self::TABLE_START . self::CLASS_NAME);
        if ($tbl_str != '') {
            $tbl_str = $lib->str_left_of($tbl_str, self::TABLE_END);
            if ($tbl_str != '') {

                // jump over the style to the first header row
                while (substr($tbl_str, 0, 1) != self::COL_MAKER_HEADER) {
                    $tbl_str = $lib->str_right_of($tbl_str, self::ROW_END);
                }

                // get the column names
                while (substr($tbl_str, 0, 1) == self::COL_MAKER_HEADER) {
                    $col_name = $lib->str_right_of($tbl_str, self::COL_MAKER_HEADER);
                    $col_name = $lib->str_left_of($col_name, self::ROW_END);
                    $col_names[] = $col_name;
                    $tbl_str = $lib->str_right_of($tbl_str, self::ROW_END);
                }

                // get the data rows
                while ($tbl_str != '') {
                    $data_row = $lib->str_left_of($tbl_str, self::ROW_END);
                    if ($data_row == self::ROW_MAKER) {
                        // new row
                        if (count($row) > 0) {
                            $rows[] = $row;
                            $row = [];
                        }
                        $tbl_str = $lib->str_right_of($tbl_str, self::ROW_END);
                    }
                    while (str_starts_with($tbl_str, self::COL_MAKER)
                        and !str_starts_with($tbl_str, self::ROW_MAKER)) {
                        $row_entry = $lib->str_right_of($tbl_str, self::COL_MAKER);
                        $row_entry = $lib->str_left_of($row_entry, self::ROW_END);
                        if (strpos($row_entry, self::ITEM_IGNORE_MAKER) > 0) {
                            $row_entry = '';
                        } else {
                            // remove style
                            if (strpos($row_entry, self::COL_MAKER) > 1) {
                                $row_entry = $lib->str_right_of($row_entry, self::COL_MAKER);
                                if (str_starts_with($row_entry, self::NUMBER_MAKER)) {
                                    $row_entry = $lib->str_between($row_entry, self::NUMBER_MAKER, self::NUMBER_MAKER);
                                }
                                if (str_starts_with($row_entry, self::LINK_MAKER)) {
                                    $row_entry = $lib->str_between($row_entry, self::LINK_MAKER, self::LINK_MAKER_END);
                                }
                                if (str_starts_with($row_entry, self::SORT_MAKER)) {
                                    // TODO get sort name
                                    $row_entry = $lib->str_right_of($row_entry, self::LINK_MAKER, self::LINK_MAKER_END);
                                }
                                if (str_starts_with($row_entry, self::LINK_MAKER)) {
                                    $row_entry = $lib->str_between($row_entry, self::LINK_MAKER, self::LINK_MAKER_END);
                                }
                            }
                        }
                        if ($row_entry != '') {
                            $row[] = $row_entry;
                        }
                        $tbl_str = $lib->str_right_of($tbl_str, self::ROW_END);
                    }
                }
                // last row
                if (count($row) > 0) {
                    $rows[] = $row;
                }
            }
        }
        $result .= '{"version": "0.0.3", "time": "2022-02-12 18:09:36", "user": "timon",';
        $result .= '"selection": ["Democracy Index"],';
        $result .= '"words": [';
        $result .= '{"name": "Country"},';
        foreach ($rows as $row) {
            $result .= '{"name": "' . $row[1] . '"},';
        }
        $result .= '{"name": "Year"},';
        $i = 0;
        $year_item = '';
        foreach ($col_names as $col_name) {
            if ($i > 3) {
                if ($year_item != '') {
                    $year_item .= ',';
                }
                $year_item .= '{ "name": "' . $col_name . '", "type": "time"}';
            }
            $i++;
        }
        $result .= $year_item;
        $result .= '],';
        $result .= '"value-list": [';
        $val_list = '';
        foreach ($rows as $row) {
            if ($val_list != '') {
                $val_list .= ',';
            }
            $val_list .= '{';
            $val_list .= '"context": ["Democracy Index","' . $row[1] . '"],';
            $val_list .= '"values": [';
            $i = 0;
            $val_item = '';
            foreach ($row as $item) {
                if ($val_item != '') {
                    $val_item .= ',';
                }
                if ($i > 3) {
                    $val_item .= '{"' . $col_names[$i] . '": ' . $item . '}';
                }
                $i++;
            }
            $val_list .= $val_item;
            $val_list .= ']';
            $val_list .= '}';
        }
        $result .= $val_list;
        $result .= ']';
        $result .= '}';
        return $result;
    }
}
