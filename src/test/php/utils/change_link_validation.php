<?php

/*

    test/php/utils/change_link_validation.php - check that the renumbered change tables and fields hold the same rows
    -----------------------------------------

    compares the unsorted baseline (unit/change_table/list_unsorted.csv and unit/change_field/list_unsorted.csv)
    with the code link csv files db_code_links/change_tables.csv and db_code_links/change_fields.csv:
    no table or field may be missing or added and every field must still point to the same table

    because the ids have been renumbered, a field is compared by the name of its table and never by the table id,
    and both sides are written with that text reference to unit/change_field/list_*_with_text_ref.csv for review

    $lnk_chk is the suggested var name


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class change_link_validation
{

    // the columns that describe a change table independent of its id
    const array TABLE_COLUMNS = [change_table::FLD_NAME, fields::FLD_CODE_ID, fields::FLD_DESCRIPTION];

    // the columns of a change field row that names its table instead of the table id
    const array TEXT_REF_COLUMNS = [change_table::FLD_NAME, change_field::FLD_NAME, fields::FLD_CODE_ID, fields::FLD_DESCRIPTION];

    /**
     * compare the unsorted baseline with the code link csv files and write the fields of both with the table name
     *
     * @return array one text per table or field row that is missing, added, unreadable or points to an unknown table
     */
    function check(): array
    {
        $lib = new library();
        $findings = [];
        $old_tbl = $this->read_csv($lib->class_csv_file_path(change_table::class, test_files::FIXED_DB_UNSORTED_CSV), $findings);
        $old_fld = $this->read_csv($lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_UNSORTED_CSV), $findings);
        $new_tbl = $this->read_csv($this->code_link_file(change_table::class), $findings);
        $new_fld = $this->read_csv($this->code_link_file(change_field::class), $findings);
        $old_ref = $this->text_ref_lines($old_fld, $old_tbl, $findings);
        $new_ref = $this->text_ref_lines($new_fld, $new_tbl, $findings);
        $this->write_text_ref($lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_UNSORTED_TEXT_REF_CSV), $old_ref);
        $this->write_text_ref($lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_SORTED_TEXT_REF_CSV), $new_ref);
        $old_tbl_lines = $this->lines($old_tbl, self::TABLE_COLUMNS);
        $new_tbl_lines = $this->lines($new_tbl, self::TABLE_COLUMNS);
        $findings = array_merge($findings, $this->diff(change_table::class, $old_tbl_lines, $new_tbl_lines));
        return array_merge($findings, $this->diff(change_field::class, $old_ref, $new_ref));
    }

    /**
     * read a csv file whose first line names the columns
     *
     * @param string $file the path of the csv file
     * @param array $findings to report a missing file or a line that does not match the header
     * @return array the rows as arrays with the trimmed column name as key and the trimmed value
     */
    function read_csv(string $file, array &$findings): array
    {
        $lib = new library();
        $rows = [];
        $lines = is_readable($file) ? file($file, FILE_IGNORE_NEW_LINES) : false;
        if ($lines === false) {
            $findings[] = 'cannot read ' . $file;
        } else {
            $header = array_map('trim', $lib->csv_line_to_array((string)array_shift($lines)));
            // a line without any char does not contain a row
            $lines = array_filter($lines, fn(string $line) => trim($line) != '');
            foreach ($lines as $pos => $line) {
                $values = array_map('trim', $lib->csv_line_to_array($line));
                if (count($values) == count($header)) {
                    $rows[] = array_combine($header, $values);
                } else {
                    $findings[] = 'line ' . ($pos + 2) . ' of ' . $file . ' does not match the header: ' . $line;
                }
            }
        }
        return $rows;
    }

    /**
     * @param array $fld_rows the change field rows as read by read_csv
     * @param array $tbl_rows the change table rows that the table id of the fields refers to
     * @param array $findings to report a field whose table id is not in the table rows
     * @return array one csv line per field with the table name instead of the table id in the order of the fields
     */
    function text_ref_lines(array $fld_rows, array $tbl_rows, array &$findings): array
    {
        $tbl_names = array_column($tbl_rows, change_table::FLD_NAME, change_table::FLD_ID);
        $lines = [];
        foreach ($fld_rows as $row) {
            $tbl_id = $row[change_field::FLD_TABLE] ?? '';
            if (!array_key_exists($tbl_id, $tbl_names)) {
                $findings[] = 'unknown table id "' . $tbl_id . '" of change field ' . $this->line($row, [change_field::FLD_ID, change_field::FLD_NAME]);
            }
            $row[change_table::FLD_NAME] = $tbl_names[$tbl_id] ?? '';
            $lines[] = $this->line($row, self::TEXT_REF_COLUMNS);
        }
        return $lines;
    }

    /**
     * @param string $class the class of the compared rows used to name the findings
     * @param array $old_lines the lines of the unsorted baseline
     * @param array $new_lines the lines of the code link file
     * @return array one finding per line that is missing in or added to the new lines, a repeated line counted as often as it is repeated
     */
    function diff(string $class, array $old_lines, array $new_lines): array
    {
        $lib = new library();
        $name = $lib->class_to_name($class);
        $findings = [];
        $old_cnt = array_count_values($old_lines);
        $new_cnt = array_count_values($new_lines);
        foreach ($old_cnt as $line => $cnt) {
            if ($cnt > ($new_cnt[$line] ?? 0)) {
                $findings[] = $name . ' missing: ' . $line;
            }
        }
        foreach ($new_cnt as $line => $cnt) {
            if ($cnt > ($old_cnt[$line] ?? 0)) {
                $findings[] = $name . ' added: ' . $line;
            }
        }
        return $findings;
    }

    /**
     * @param string $class the class whose rows are defined in the code link file e.g. change_field
     * @return string the path of the code link csv file e.g. db_code_links/change_fields.csv
     */
    private function code_link_file(string $class): string
    {
        $lib = new library();
        return cfg_files::CODE_LINK_PATH . $lib->class_to_table($class) . cfg_files::CODE_LINK_TYPE;
    }

    /**
     * @param array $rows the rows as read by read_csv
     * @param array $columns the names of the columns that are compared
     * @return array one csv line per row
     */
    private function lines(array $rows, array $columns): array
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = $this->line($row, $columns);
        }
        return $lines;
    }

    /**
     * @param array $row one row as read by read_csv
     * @param array $columns the names of the columns in the order of the line
     * @return string the csv line with an empty value for a column that the file does not have e.g. the code_id of change_fields.csv
     */
    private function line(array $row, array $columns): string
    {
        $values = [];
        foreach ($columns as $col) {
            $values[] = $row[$col] ?? '';
        }
        $lib = new library();
        return $lib->csv_line($values);
    }

    /**
     * @param string $file the path of the text ref csv file that is created or rewritten if the content differs
     * @param array $lines the csv lines of the fields without the header
     */
    private function write_text_ref(string $file, array $lines): void
    {
        $lib = new library();
        $header = $lib->csv_line(self::TEXT_REF_COLUMNS);
        $txt = implode("\n", array_merge([$header], $lines)) . "\n";
        // an unchanged file is not rewritten, so that the file time stays the time of the last real change
        if (!is_readable($file) or file_get_contents($file) !== $txt) {
            file_put_contents($file, $txt);
        }
    }

}
