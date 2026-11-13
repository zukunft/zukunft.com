<?php

/*

    test/php/utils/csv_table_diff.php - compare an exported table with an expected csv table
    ---------------------------------

    $ctd is the suggested var name

    a text diff of the two files would report every later row after one moved row, so both tables are
    read as cells by the name of the row and the name of the column; the report lists the columns and
    rows that the export misses or adds, the cells that differ and the rows in another order, as a
    markdown text that can be given to an llm as the prompt to bring the export closer to the target


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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\shared\library;

class csv_table_diff
{

    // the separator of the row name and its position if two rows have the same name, so that no row is lost
    const string SAME_NAME_SEP = ' #';

    /**
     * @param string $csv the csv text with the header in the first line
     * @param string $row_col the name of the column that names a row; the first column if the table has no such column
     * @return array per row name the texts of the cells by the column name, without the column that names the row
     */
    function table_cells(string $csv, string $row_col): array
    {
        $lines = $this->csv_lines($csv);
        $header = array_shift($lines) ?? [];
        $key_pos = array_search($row_col, $header);
        $key_pos = $key_pos === false ? 0 : $key_pos;
        $result = [];
        foreach ($lines as $line) {
            $key = $this->unique_key($line[$key_pos] ?? '', $result);
            foreach ($header as $pos => $col_name) {
                if ($pos != $key_pos) {
                    $result[$key][$col_name] = $line[$pos] ?? '';
                }
            }
        }
        return $result;
    }

    /**
     * @param string $csv the csv text
     * @return array per line of the text with a content the texts of the cells
     */
    private function csv_lines(string $csv): array
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);
        $result = [];
        while (($line = fgetcsv($stream, null, library::CSV_DELIMITER, library::CSV_ENCLOSURE, '')) !== false) {
            if ($line != [null]) {
                $result[] = $line;
            }
        }
        fclose($stream);
        return $result;
    }

    /**
     * @param string $name the name of the row
     * @param array $known the rows found so far by their key
     * @return string the name of the row, followed by its position if a row with the same name exists
     */
    private function unique_key(string $name, array $known): string
    {
        $result = $name;
        $pos = 1;
        while (array_key_exists($result, $known)) {
            $pos++;
            $result = $name . self::SAME_NAME_SEP . $pos;
        }
        return $result;
    }

    /**
     * @param array $target the cells of the expected table of table_cells
     * @param array $export the cells of the exported table of table_cells
     * @param string $target_name the name of the expected file for the report
     * @param string $export_name the name of the export for the report
     * @return string the markdown report of the differences
     */
    function report(array $target, array $export, string $target_name, string $export_name): string
    {
        $target_cols = $this->column_names($target);
        $export_cols = $this->column_names($export);
        $title = 'Differences of ' . $export_name . ' to ' . $target_name;
        $prompt = 'Change the data or the table so that the export ' . $export_name . ' has what the target '
            . $target_name . ' has: add what is missing, correct what differs and remove what only the export'
            . ' has, unless the target lacks it by mistake. The export has the complete numbers, so a number of'
            . ' the target that is rounded is reported as different; check the value before changing it.';
        $hpd = new html_page_diff();
        return '# ' . $title . "\n\n" . $prompt . "\n"
            . $hpd->report_section('Columns missing in the export', array_values(array_diff($target_cols, $export_cols)))
            . $hpd->report_section('Columns only in the export', array_values(array_diff($export_cols, $target_cols)))
            . $hpd->report_section('Rows missing in the export', array_keys(array_diff_key($target, $export)))
            . $hpd->report_section('Rows only in the export', array_keys(array_diff_key($export, $target)))
            . $hpd->report_section('Different cells', $this->different_lines($target, $export))
            . $hpd->report_section('Different order', $this->order_lines($target, $export));
    }

    /**
     * @param array $cells the cells of table_cells
     * @return array the names of the columns of the table
     */
    private function column_names(array $cells): array
    {
        return array_keys($cells[array_key_first($cells)] ?? []);
    }

    /**
     * @param array $target the cells of the expected table
     * @param array $export the cells of the exported table
     * @return array per cell with a different content that both tables have, one line with both texts
     */
    private function different_lines(array $target, array $export): array
    {
        $result = [];
        foreach (array_intersect_key($target, $export) as $row => $cols) {
            foreach (array_intersect_key($cols, $export[$row]) as $col => $txt) {
                if (!$this->is_same_cell($txt, $export[$row][$col])) {
                    $result[] = $row . ', ' . $col . ': the target has "' . $txt
                        . '" and the export "' . $export[$row][$col] . '"';
                }
            }
        }
        return $result;
    }

    /**
     * @param string $target_txt the cell of the expected table
     * @param string $export_txt the cell of the exported table
     * @return bool true if the texts are the same or both state the complete same number e.g. 1.78e+03 and 1780
     */
    private function is_same_cell(string $target_txt, string $export_txt): bool
    {
        $is_number = (is_numeric($target_txt) and is_numeric($export_txt));
        return $is_number ? (float)$target_txt == (float)$export_txt : $target_txt == $export_txt;
    }

    /**
     * @param array $target the cells of the expected table
     * @param array $export the cells of the exported table
     * @return array per position with another row of the rows that both tables have, one line with both names
     */
    private function order_lines(array $target, array $export): array
    {
        $target_rows = array_keys(array_intersect_key($target, $export));
        $export_rows = array_keys(array_intersect_key($export, $target));
        $result = [];
        foreach ($target_rows as $pos => $row) {
            if ($row != $export_rows[$pos]) {
                $result[] = 'position ' . ($pos + 1) . ': the target has "' . $row . '" and the export "' . $export_rows[$pos] . '"';
            }
        }
        return $result;
    }

}
