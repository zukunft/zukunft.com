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


use cfg\export\export;
use cfg\library;
use cfg\user;
use DateTime;
use DateTimeInterface;

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
     * @param string $wiki_tbl wth the wikipedia table
     * @param user $usr the user how has initiated the convertion
     * @param array $context a list of phrases that describes the context of the table
     * @return string
     */
    function convert(
        string $wiki_tbl,
        user   $usr,
        string $timestamp,
        array  $context,
        string $row_name_in,
        int    $col_of_row_name,
        string $col_name_in,
        string $col_type,
        int    $col_start
    ): string
    {
        $table = $this->wikipedia_table_to_array($wiki_tbl);
        $col_names = $table[0];
        $rows = $table[1];

        // map the table to a json
        $json = $this->header($usr, $timestamp);
        $json[export::SELECTION] = $context;
        $words = [];
        $word = [];
        $word[export::NAME] = $row_name_in;
        $words[] = $word;
        $word[export::NAME] = $col_name_in;
        $words[] = $word;
        foreach ($rows as $row) {
            $word[export::NAME] = $row[$col_of_row_name];
            $words[] = $word;
        }
        $i = 0;
        foreach ($col_names as $col_name) {
            if ($i > $col_start) {
                $word[export::NAME] = $col_name;
                $word[export::TYPE] = $col_type;
                $words[] = $word;
            }
            $i++;
        }
        $json[export::WORDS] = $words;
        $val_list = [];
        foreach ($rows as $row_in) {
            $val_row = [];
            $context_row = $context;
            $context_row[] = $row_in[1];
            $val_row[export::CONTEXT] = $context_row;
            $i = 0;
            $val_row_items = [];
            foreach ($row_in as $item) {
                if ($i > $col_start) {
                    $val_row_items[$col_names[$i]] = $item;
                }
                $i++;
            }
            $val_row[export::VALUES] = $val_row_items;
            $val_list[] = $val_row;
        }
        $json[export::VALUE_LIST] = $val_list;
        return json_encode($json);
    }

    /**
     * convert a wikipedia table into an array
     *
     * @param string $wiki_tbl the wikipedia table as a string
     * @return array the array where the first entry is the column names and the second an array of the table rows
     */
    private function wikipedia_table_to_array(string $wiki_tbl): array
    {
        $lib = new library();
        $col_names = [];
        $rows = [];
        $row_in = [];
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
                        if (count($row_in) > 0) {
                            $rows[] = $row_in;
                            $row_in = [];
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
                            $row_in[] = $row_entry;
                        }
                        $tbl_str = $lib->str_right_of($tbl_str, self::ROW_END);
                    }
                }
                // last row
                if (count($row_in) > 0) {
                    $rows[] = $row_in;
                }
            }
        }
        $table = [];
        $table[] = $col_names;
        $table[] = $rows;

        return $table;
    }

    private function header(user $usr, string $timestamp = ''): array
    {
        $header = [];
        $header[export::POD] = POD_NAME;
        $header[export::VERSION] = PRG_VERSION;
        if ($timestamp == '') {
            $header[export::TIME] = (new DateTime())->format(DateTimeInterface::ATOM);
        } else {
            $header[export::TIME] = $timestamp;
        }
        return $header;
    }

}
