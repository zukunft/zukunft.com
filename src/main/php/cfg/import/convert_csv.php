<?php

/*

    model/import/convert_csv.php - convert a csv to a zukunft.com import by the phrases of its format
    ----------------------------

    $fmt_conv is the suggested var name

    the format of the csv is a phrase with triples to the keywords of csv_keywords.json and with value templates
    (docs/json_structure.md "csv format"), so a new csv format is a definition json and no code: this class has no
    name of a column, a kind or a phrase of its own; the definition is the decoded import json of the format, so that
    the declarations of the definition are copied verbatim into the import json of the csv


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

namespace Zukunft\ZukunftCom\main\php\cfg\import;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use DateTime;
use DateTimeInterface;

class convert_csv
{

    // a cell in a description or a csv name: the header text of its column between the two
    const string CELL_START = '{';
    const string CELL_END = '}';
    // the decimals and the significant digits of a number of the json: more than a csv writes for a position, but
    // fewer digits than a float has, because the last digits of a product of two cells are the noise of the float
    // e.g. 451509.3745520001 for 484296.24 times 0.9323, which round() of php 8.4 keeps
    const int DECIMALS = 10;
    const int SIGNIFICANT_DIGITS = 14;
    // the separator of the from, the verb and the to of a triple as a key
    const string KEY_SEP = '|';

    // the first cell of the header row (csv header start)
    private string $header_start = '';
    // the header text of each column by the name of its column item
    private array $headers = [];
    // per column item the description of each of its triples to a csv keyword by the keyword e.g. the fallback column
    private array $flags = [];
    // the kind phrase of each cell text of the kind column
    private array $kinds = [];
    // the column items of the kind, the name and the description of a row, '' if the format has none
    private string $kind_item = '';
    private string $row_name_item = '';
    private string $row_description_item = '';
    // the triples and the values of the definition that are written per row (see triple_templates)
    private array $triple_templates = [];
    private array $value_templates = [];
    // the name of a written triple with its cells by the name of its template (csv name)
    private array $names = [];
    // the declarations of the definition: the words by their name, the triples by their name and by their key
    private array $words = [];
    private array $triples = [];
    private array $keys = [];


    /*
     * set and get
     */

    /**
     * read the format of a csv from its definition
     *
     * @param array $fmt the decoded import json of the definition e.g. bank_position_csv.json
     * @param string $format the name of the phrase of the format e.g. "bank position csv"
     * @param user_message $msg to report a definition without the triple to csv header start or to csv column and a
     *                          value template that has more than one number column
     * @return bool true if the format can be used to convert a csv
     */
    function set_format(array $fmt, string $format, user_message $msg): bool
    {
        $this->set_declarations($fmt);
        $start = $this->keyword_triple($format, triples::CSV_HEADER_START);
        $columns = $this->keyword_triple($format, triples::CSV_COLUMN);
        foreach ([triples::CSV_HEADER_START => $start, triples::CSV_COLUMN => $columns] as $keyword => $trp) {
            if ($trp === null) {
                $this->report_missing_keyword($format, $keyword, $msg);
            }
        }
        $result = ($start !== null and $columns !== null);
        if ($result) {
            $this->header_start = $start[json_fields::DESCRIPTION] ?? '';
            $this->set_columns($this->triple_name($columns));
            $result = $this->set_templates($fmt, $format, $msg);
        }
        return $result;
    }

    /**
     * @param array $fmt the decoded import json of the definition
     * @return void the words and triples of the definition by their name and the triples also by their key
     */
    private function set_declarations(array $fmt): void
    {
        foreach ($fmt[json_fields::WORDS] ?? [] as $wrd) {
            $this->words[$wrd[json_fields::NAME] ?? ''] = $wrd;
        }
        foreach ($fmt[json_fields::TRIPLES] ?? [] as $trp) {
            $this->triples[$this->triple_name($trp)] = $trp;
            $this->keys[$this->triple_key($trp)] = $trp;
        }
    }

    /**
     * @param string $group the name of the group triple of the columns e.g. "bank position csv columns"
     * @return void the header text and the flags of each column, the kinds and the columns of the name, the
     *              description and the kind of a row
     */
    private function set_columns(string $group): void
    {
        foreach ($this->triples as $name => $trp) {
            if (($trp[json_fields::EX_FROM] ?? '') == $group) {
                $this->headers[$name] = $trp[json_fields::DESCRIPTION] ?? '';
            }
        }
        foreach ($this->triples as $name => $trp) {
            $from = $trp[json_fields::EX_FROM] ?? '';
            if ($this->is_column($from) and $this->is_flag($trp)) {
                $this->flags[$from][$trp[json_fields::EX_TO]] = $trp[json_fields::DESCRIPTION] ?? '';
                $this->set_column_role($from, $name, $trp[json_fields::EX_TO]);
            }
        }
    }

    /**
     * @param string $item the name of the column item e.g. "bank position csv instrument"
     * @param string $flag_name the name of the triple of the flag e.g. "bank position csv asset class kind"
     * @param string $keyword the keyword of the flag e.g. triples::CSV_ROW_NAME
     * @return void the column of the name, the description or the kind of a row, and the kind of each cell text
     */
    private function set_column_role(string $item, string $flag_name, string $keyword): void
    {
        if ($keyword == triples::CSV_ROW_NAME) {
            $this->row_name_item = $item;
        } elseif ($keyword == triples::CSV_ROW_DESCRIPTION) {
            $this->row_description_item = $item;
        } elseif ($keyword == triples::CSV_KIND) {
            $this->kind_item = $item;
            foreach ($this->triples as $trp) {
                if (($trp[json_fields::EX_FROM] ?? '') == $flag_name) {
                    $this->kinds[$trp[json_fields::DESCRIPTION] ?? ''] = $trp[json_fields::EX_TO] ?? '';
                }
            }
        }
    }

    /**
     * @param array $fmt the decoded import json of the definition
     * @param string $format the name of the phrase of the format, which a message names
     * @param user_message $msg to report a value template with more than one number column
     * @return bool true if every value template has at most one number column; sets the triple and the value
     *              templates and the csv names of the triple templates
     */
    private function set_templates(array $fmt, string $format, user_message $msg): bool
    {
        $result = true;
        foreach ($this->triples as $name => $trp) {
            if ($this->is_triple_template($trp)) {
                $this->triple_templates[$name] = $trp;
            } elseif (($trp[json_fields::EX_TO] ?? '') == triples::CSV_NAME) {
                $this->names[$trp[json_fields::EX_FROM] ?? ''] = $trp[json_fields::DESCRIPTION] ?? '';
            }
        }
        foreach ($fmt[json_fields::VALUES] ?? [] as $val) {
            $phr_names = $val[json_fields::WORDS] ?? [];
            if (count($this->number_items($phr_names)) > 1) {
                $msg->add(msg_id::IMPORT_CSV_TEMPLATE_NUMBERS, [
                    msg_id::VAR_NAME => $format,
                    msg_id::VAR_VALUE => implode(', ', $phr_names),
                ]);
                $result = false;
            } else {
                $this->value_templates[] = $val;
            }
        }
        return $result;
    }


    /*
     * convert
     */

    /**
     * convert the rows of a csv to the import json by the format of set_format
     *
     * @param string $csv the text of the csv
     * @param array $context the names of the phrases of the conversion e.g. "example portfolio split"; the first one
     *                       is the phrase that csv context stands for
     * @param string $source the name of the source of every value e.g. the bank statement, which a message names as
     *                       the csv
     * @param user_message $msg to report a csv without the header row and each row that is left out with its line and
     *                          the reason
     * @param string $timestamp the time of the json, now if empty
     * @return string the zukunft.com import json of the rows by the format of set_format
     */
    function convert(string $csv, array $context, string $source, user_message $msg, string $timestamp = ''): string
    {
        $triples = [];
        $values = [];
        $row_words = [];
        $once = [];
        foreach ($this->rows($csv, $source, $msg) as $line_nbr => $cells) {
            if ($this->row_is_valid($cells, $line_nbr, $source, $msg)) {
                $row_name = $this->cell($cells, $this->row_name_item);
                $row_words[$row_name] = $this->row_description($cells);
                $written = $this->row_triples($cells, $context, $triples);
                $this->row_values($cells, $written, $source, $values, $once);
            }
        }
        $json = $this->header($timestamp);
        $json[json_fields::SELECTION] = $context;
        $used = $this->used_declarations($context, $triples, $values);
        $written_names = array_map(fn(array $trp) => $this->triple_name($trp), array_values($triples));
        $json[json_fields::WORDS] = $this->result_words($used, $row_words, $written_names);
        $json[json_fields::TRIPLES] = $this->unique_triples(array_merge($this->used_triples($used), array_values($triples)));
        $json[json_fields::SOURCES] = [[json_fields::NAME => $source]];
        $json[json_fields::VALUES] = $values;
        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $cells the cells of a valid row by the header text of their column
     * @return string|null the description of the word of the row, null if the format has no row description column
     */
    private function row_description(array $cells): ?string
    {
        return $this->row_description_item == '' ? null : $this->cell($cells, $this->row_description_item);
    }

    /**
     * @param array $cells the cells of a valid row by the header text of their column
     * @param array $context the names of the phrases of the conversion
     * @param array $triples (in/out) the written triples by their key, to which the triples of the row are added
     * @return array per triple template the name of the triple that it has written for the row, null if none
     */
    private function row_triples(array $cells, array $context, array &$triples): array
    {
        $result = [];
        $kind = $this->row_kind($cells);
        $row_name = $this->cell($cells, $this->row_name_item);
        if ($kind != '' and $row_name != '') {
            $kind_trp = [
                json_fields::EX_FROM => $row_name,
                json_fields::EX_VERB => verbs::IS_NAME,
                json_fields::EX_TO => $kind,
            ];
            $triples[$this->triple_key($kind_trp)] = $this->declared_or($kind_trp);
        }
        foreach ($this->triple_templates as $name => $tpl) {
            $trp = $this->written_triple($name, $tpl, $cells, $context);
            $result[$name] = $trp === null ? null : $this->triple_name($trp);
            if ($trp !== null) {
                $triples[$this->triple_key($trp)] = $trp;
            }
        }
        return $result;
    }

    /**
     * @param string $name the name of the triple template e.g. "bank position csv currency pair"
     * @param array $tpl the triple template of the definition
     * @param array $cells the cells of the row by the header text of their column
     * @param array $context the names of the phrases of the conversion
     * @return array|null the triple of the row, verbatim if the definition declares it, null if a cell or the context
     *                    is empty or if its from and its to are the same phrase
     */
    private function written_triple(string $name, array $tpl, array $cells, array $context): ?array
    {
        $result = null;
        $from = $this->row_phrase($tpl[json_fields::EX_FROM] ?? '', $cells, $context);
        $to = $this->row_phrase($tpl[json_fields::EX_TO] ?? '', $cells, $context);
        if ($from != '' and $to != '' and $from != $to) {
            $trp = $tpl;
            unset($trp[json_fields::NAME]);
            $trp[json_fields::EX_FROM] = $from;
            $trp[json_fields::EX_TO] = $to;
            if (array_key_exists(json_fields::DESCRIPTION, $tpl)) {
                $trp[json_fields::DESCRIPTION] = $this->with_cells($tpl[json_fields::DESCRIPTION], $cells);
            }
            if (array_key_exists($name, $this->names)) {
                $trp = [json_fields::NAME => $this->with_cells($this->names[$name], $cells)] + $trp;
            }
            $result = $this->declared_or($trp);
        }
        return $result;
    }

    /**
     * @param array $cells the cells of a valid row by the header text of their column
     * @param array $written per triple template the name of the triple that it has written for the row (row_triples)
     * @param string $source the name of the source of every value that is no assumption
     * @param array $values (in/out) the written values, to which the values of the row are added
     * @param array $once (in/out) the phrases of the written values of the templates without the row name column
     * @return void
     */
    private function row_values(array $cells, array $written, string $source, array &$values, array &$once): void
    {
        foreach ($this->value_templates as $tpl) {
            $phr_names = $tpl[json_fields::WORDS] ?? [];
            $val = $this->written_value($tpl, $cells, $written, $source);
            if ($val !== null and in_array($this->row_name_item, $phr_names)) {
                $values[] = $val;
            } elseif ($val !== null) {
                $key = implode(self::KEY_SEP, $val[json_fields::WORDS]);
                if (!in_array($key, $once)) {
                    $once[] = $key;
                    $values[] = $val;
                }
            }
        }
    }

    /**
     * @param array $tpl the value template of the definition
     * @param array $cells the cells of the row by the header text of their column
     * @param array $written per triple template the name of the triple that it has written for the row
     * @param string $source the name of the source, which a template with the word assumed does not get
     * @return array|null the value of the row, null if the template is for another kind, if a cell that it uses is
     *                    empty or if a triple template that it uses has written nothing for the row
     */
    private function written_value(array $tpl, array $cells, array $written, string $source): ?array
    {
        $result = null;
        $phr_names = $tpl[json_fields::WORDS] ?? [];
        $number = $this->template_number($tpl, $cells);
        if ($number !== null and $this->is_for_kind($phr_names, $this->row_kind($cells))) {
            $words = $this->value_words($phr_names, $cells, $written);
            if ($words !== null) {
                $result = [json_fields::WORDS => $words, json_fields::NUMBER => $this->number_text($number)];
                if (!in_array(words::ASSUMED, $phr_names)) {
                    $result[json_fields::SOURCE_NAME] = $source;
                }
            }
        }
        return $result;
    }

    /**
     * the number of a value as the text of the import json, independent of the php setting precision
     *
     * @param float $number the number of the template times the cell of its number column
     * @return string the number with at most SIGNIFICANT_DIGITS digits and at most DECIMALS decimals, without the
     *                zeros at the end and without an exponent e.g. "451509.374552"
     */
    private function number_text(float $number): string
    {
        $rounded = round($number, self::DECIMALS);
        $result = '0';
        if ($rounded != 0) {
            $int_digits = max(1, (int)floor(log10(abs($rounded))) + 1);
            $decimals = max(0, min(self::DECIMALS, self::SIGNIFICANT_DIGITS - $int_digits));
            // sprintf and not number_format, because number_format rounds a half digit of the float differently
            $result = sprintf('%.' . $decimals . 'F', $rounded);
        }
        if (str_contains($result, '.')) {
            $result = rtrim(rtrim($result, '0'), '.');
        }
        return $result;
    }

    /**
     * @param array $tpl the value template of the definition
     * @param array $cells the cells of the row by the header text of their column
     * @return float|null the number of the template times the cell of its number column, the number of the template
     *                    without a number column, null for an empty number cell or for zero with csv optional
     */
    private function template_number(array $tpl, array $cells): ?float
    {
        $result = (float)($tpl[json_fields::NUMBER] ?? 0);
        $phr_names = $tpl[json_fields::WORDS] ?? [];
        foreach ($this->number_items($phr_names) as $item) {
            $cell = $this->cell($cells, $item);
            $skip_zero = (in_array(triples::CSV_OPTIONAL, $phr_names) and (float)$cell == 0);
            if ($cell == '' or $skip_zero) {
                $result = null;
            } else {
                $result = $result * (float)$cell;
            }
        }
        return $result;
    }

    /**
     * @param array $phr_names the phrase names of a value template
     * @param array $cells the cells of the row by the header text of their column
     * @param array $written per triple template the name of the triple that it has written for the row
     * @return array|null the phrase names of the value of the row: without the kind, the number column and csv
     *                    optional, a column item as its cell and a triple template as its written triple; null if a
     *                    cell is empty or a triple template has written nothing
     */
    private function value_words(array $phr_names, array $cells, array $written): ?array
    {
        $result = [];
        foreach ($phr_names as $phr_name) {
            if (array_key_exists($phr_name, $written)) {
                $result[] = $written[$phr_name];
            } elseif ($this->is_column($phr_name) and !$this->has_flag($phr_name, triples::CSV_NUMBER)) {
                $result[] = $this->cell($cells, $phr_name);
            } elseif (!$this->is_column($phr_name) and !$this->is_kind($phr_name) and $phr_name != triples::CSV_OPTIONAL) {
                $result[] = $phr_name;
            }
        }
        $is_incomplete = (in_array(null, $result, true) or in_array('', $result, true));
        return $is_incomplete ? null : $result;
    }


    /*
     * rows
     */

    /**
     * @param string $csv the text of the csv
     * @param string $source the name of the csv, which a message names as the place
     * @param user_message $msg to report a csv without the header row and a line that is left out, because it has
     *                          another number of cells than the header row e.g. by a comma in a name without quotes
     * @return array per line number of the csv (from 1) of a row below the header row its cells by the header text of
     *               their column, without the spaces around a cell; empty if the csv has no header row
     */
    private function rows(string $csv, string $source, user_message $msg): array
    {
        $result = [];
        $header = null;
        foreach (preg_split('/\r\n|\n|\r/', $csv) as $i => $line) {
            $cells = array_map('trim', str_getcsv($line, ',', '"', '\\'));
            if ($header === null) {
                if ($this->header_start != '' and ($cells[0] ?? '') == $this->header_start) {
                    $header = $cells;
                }
            } elseif (implode('', $cells) != '') {
                $line_nbr = $i + 1;
                if (count($cells) != count($header)) {
                    $msg->add(msg_id::IMPORT_CSV_LINE_CELLS, [
                        msg_id::VAR_ID => (string)$line_nbr,
                        msg_id::VAR_SOURCE_NAME => $source,
                        msg_id::VAR_VALUE_COUNT => (string)count($cells),
                        msg_id::VAR_VALUE_COUNT_CHK => (string)count($header),
                    ]);
                } else {
                    $result[$line_nbr] = $this->cells_by_column($header, $cells);
                }
            }
        }
        if ($header === null) {
            $msg->add(msg_id::IMPORT_CSV_NO_HEADER, [
                msg_id::VAR_SOURCE_NAME => $source,
                msg_id::VAR_FIELD_NAME => $this->header_start,
            ]);
        }
        return $result;
    }

    /**
     * @param array $header the cells of the header row
     * @param array $cells the cells of a line with as many cells as the header row
     * @return array the cells by the header text of their column; of a text that is used twice e.g. "Exchange Rate"
     *               the first one
     */
    private function cells_by_column(array $header, array $cells): array
    {
        $result = [];
        foreach ($header as $i => $col) {
            if (!array_key_exists($col, $result)) {
                $result[$col] = $cells[$i];
            }
        }
        return $result;
    }

    /**
     * @param array $cells one row of the csv (see rows)
     * @param int $line_nbr the line of the row in the csv, which a message names
     * @param string $source the name of the csv, which a message names
     * @param user_message $msg to report each reason why the row is left out
     * @return bool true if the kind cell has a kind, every required cell is filled, each number cell is a number and
     *              each positive cell is above zero
     */
    private function row_is_valid(array $cells, int $line_nbr, string $source, user_message $msg): bool
    {
        $result = true;
        $vars = [
            msg_id::VAR_NAME => $this->cell($cells, $this->row_name_item),
            msg_id::VAR_ID => (string)$line_nbr,
            msg_id::VAR_SOURCE_NAME => $source,
        ];
        if ($this->kind_item != '' and $this->row_kind($cells) == '') {
            $msg->add(msg_id::IMPORT_CSV_UNKNOWN_KIND, $vars + [msg_id::VAR_TYPE => $this->cell($cells, $this->kind_item)]);
            $result = false;
        }
        foreach (array_keys($this->flags) as $item) {
            $cell = $this->cell($cells, $item);
            $cell_vars = $vars + [msg_id::VAR_FIELD_NAME => $this->headers[$item] ?? '', msg_id::VAR_VALUE => $cell];
            if ($cell == '' and $this->has_flag($item, triples::CSV_REQUIRED)) {
                $msg->add(msg_id::IMPORT_CSV_CELL_EMPTY, $cell_vars);
                $result = false;
            } elseif ($cell != '' and $this->has_flag($item, triples::CSV_NUMBER) and !is_numeric($cell)) {
                $msg->add(msg_id::IMPORT_CSV_CELL_NOT_NUMBER, $cell_vars);
                $result = false;
            } elseif ($cell != '' and $this->has_flag($item, triples::CSV_POSITIVE) and (float)$cell <= 0) {
                $msg->add(msg_id::IMPORT_CSV_CELL_NOT_POSITIVE, $cell_vars);
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param array $cells the cells of a row by the header text of their column
     * @param string $item the name of a column item, '' for none
     * @return string the cell of the column, else the cell of its fallback column while it is empty, '' for no column
     */
    private function cell(array $cells, string $item): string
    {
        $result = $cells[$this->headers[$item] ?? ''] ?? '';
        if ($result == '' and $this->has_flag($item, triples::CSV_FALLBACK)) {
            $result = $cells[$this->flags[$item][triples::CSV_FALLBACK]] ?? '';
        }
        return $result;
    }

    /**
     * @param array $cells the cells of a row by the header text of their column
     * @return string the kind phrase of the row by its kind cell, '' if the format has no kind or the cell no kind
     */
    private function row_kind(array $cells): string
    {
        return $this->kinds[$this->cell($cells, $this->kind_item)] ?? '';
    }

    /**
     * @param string $phr_name the name of a phrase of a template
     * @param array $cells the cells of the row by the header text of their column
     * @param array $context the names of the phrases of the conversion
     * @return string the cell for a column item, the first context phrase for csv context, else the phrase itself
     */
    private function row_phrase(string $phr_name, array $cells, array $context): string
    {
        $result = $phr_name;
        if ($this->is_column($phr_name)) {
            $result = $this->cell($cells, $phr_name);
        } elseif ($phr_name == triples::CSV_CONTEXT) {
            $result = $context[0] ?? '';
        }
        return $result;
    }

    /**
     * @param string $text a description or a csv name of a template e.g. "{Instrument} hint"
     * @param array $cells the cells of the row by the header text of their column
     * @return string the text with each {<header text>} replaced by the cell of that column
     */
    private function with_cells(string $text, array $cells): string
    {
        $result = $text;
        foreach ($cells as $col => $cell) {
            $result = str_replace(self::CELL_START . $col . self::CELL_END, $cell, $result);
        }
        return $result;
    }


    /*
     * result
     */

    /**
     * @param array $context the names of the phrases of the conversion
     * @param array $triples the written triples
     * @param array $values the written values
     * @return array the names of the phrases of the result: the context, the phrases of the written triples and
     *               values, the phrases of their declarations and of every declared triple between two of them
     */
    private function used_declarations(array $context, array $triples, array $values): array
    {
        $result = $context;
        foreach ($triples as $trp) {
            $result[] = $trp[json_fields::EX_FROM];
            $result[] = $trp[json_fields::EX_TO];
        }
        foreach ($values as $val) {
            $result = array_merge($result, $val[json_fields::WORDS]);
        }
        $count = 0;
        while ($count != count($result)) {
            $count = count($result);
            $result = $this->with_linked($result);
        }
        return $result;
    }

    /**
     * @param array $names the names of the phrases of the result so far
     * @return array the names with the from and the to of each declared triple of them and each declared triple
     *               between two of them, each once
     */
    private function with_linked(array $names): array
    {
        $result = array_values(array_unique($names));
        foreach ($this->triples as $name => $trp) {
            $from = $trp[json_fields::EX_FROM] ?? '';
            $to = $trp[json_fields::EX_TO] ?? '';
            $is_used = in_array($name, $result);
            $links_used = (in_array($from, $result) and in_array($to, $result));
            if ($is_used or $links_used) {
                $result = array_values(array_unique(array_merge($result, [$name, $from, $to])));
            }
        }
        return $result;
    }

    /**
     * @param array $used the names of the phrases of the result (see used_declarations)
     * @param array $row_words the description of the word of each row by its name, null without a description column
     * @param array $written_names the names of the triples that the templates have written
     * @return array the words of the result: the row words with their description, the declared words verbatim and
     *               the other names that are no triple with the name only
     */
    private function result_words(array $used, array $row_words, array $written_names): array
    {
        $result = [];
        foreach ($used as $name) {
            $is_triple = (array_key_exists($name, $this->triples) or in_array($name, $written_names));
            if (array_key_exists($name, $row_words)) {
                $result[] = $this->row_word($name, $row_words[$name]);
            } elseif (array_key_exists($name, $this->words)) {
                $result[] = $this->words[$name];
            } elseif (!$is_triple) {
                $result[] = [json_fields::NAME => $name];
            }
        }
        return $result;
    }

    /**
     * @param string $name the cell of the row name column
     * @param string|null $description the cell of the row description column, null if the format has none
     * @return array the word of the row
     */
    private function row_word(string $name, ?string $description): array
    {
        $result = [json_fields::NAME => $name];
        if ($description !== null) {
            $result[json_fields::DESCRIPTION] = $description;
        }
        return $result;
    }

    /**
     * @param array $triples the declared triples of the result and the written triples
     * @return array the triples, each from, verb and to once, the first one kept, so that a declared triple that a
     *               template writes again stays at the place of the declaration
     */
    private function unique_triples(array $triples): array
    {
        $result = [];
        foreach ($triples as $trp) {
            $key = $this->triple_key($trp);
            if (!array_key_exists($key, $result)) {
                $result[$key] = $trp;
            }
        }
        return array_values($result);
    }

    /**
     * @param array $used the names of the phrases of the result (see used_declarations)
     * @return array the declared triples of the result verbatim in the order of the definition
     */
    private function used_triples(array $used): array
    {
        $result = [];
        foreach ($this->triples as $name => $trp) {
            if (in_array($name, $used)) {
                $result[] = $trp;
            }
        }
        return $result;
    }

    /**
     * @param string $timestamp the time of the json, now if empty
     * @return array the head of the import json like convert_portfolio_csv::header, which change 3 removes
     */
    private function header(string $timestamp): array
    {
        return [
            json_fields::POD => POD_NAME,
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::DATA_VERSION => def::DATA_VERSION_INIT,
            json_fields::TIME => $timestamp == '' ? new DateTime()->format(DateTimeInterface::ATOM) : $timestamp,
        ];
    }


    /*
     * the definition
     */

    /**
     * @param string $format the name of the phrase of the format
     * @param string $keyword the name of the keyword e.g. triples::CSV_COLUMN
     * @return array|null the triple from the format to the keyword, null if the definition has none
     */
    private function keyword_triple(string $format, string $keyword): ?array
    {
        $result = null;
        foreach ($this->triples as $trp) {
            if ($result === null and ($trp[json_fields::EX_FROM] ?? '') == $format and ($trp[json_fields::EX_TO] ?? '') == $keyword) {
                $result = $trp;
            }
        }
        return $result;
    }

    /**
     * @param string $format the name of the phrase of the format, which the message names
     * @param string $keyword the name of the keyword that the format misses
     * @param user_message $msg to which the problem is added
     * @return void
     */
    private function report_missing_keyword(string $format, string $keyword, user_message $msg): void
    {
        $msg->add(msg_id::IMPORT_CSV_FORMAT_KEYWORD_MISSING, [
            msg_id::VAR_NAME => $format,
            msg_id::VAR_FIELD_NAME => $keyword,
        ]);
    }

    /**
     * @param array $trp a triple of the definition
     * @return bool true if the triple is from a column item to a keyword that is a property of the column; csv
     *              context is no property but stands for the first context phrase in a template
     */
    private function is_flag(array $trp): bool
    {
        $to = $trp[json_fields::EX_TO] ?? '';
        return array_key_exists($to, triples::CSV_KEYWORDS) and $to != triples::CSV_CONTEXT;
    }

    /**
     * @param array $trp a triple of the definition
     * @return bool true if the triple is from or to a column item and no property of the column
     */
    private function is_triple_template(array $trp): bool
    {
        $from = $trp[json_fields::EX_FROM] ?? '';
        $to = $trp[json_fields::EX_TO] ?? '';
        $uses_column = ($this->is_column($from) or $this->is_column($to));
        return $uses_column and !$this->is_flag($trp);
    }

    /**
     * @param string $phr_name the name of a phrase of the definition
     * @return bool true if it is a column item e.g. "bank position csv quantity"
     */
    private function is_column(string $phr_name): bool
    {
        return array_key_exists($phr_name, $this->headers);
    }

    /**
     * @param string $item the name of a column item
     * @param string $keyword the name of the keyword e.g. triples::CSV_REQUIRED
     * @return bool true if the definition has the triple from the column item to the keyword
     */
    private function has_flag(string $item, string $keyword): bool
    {
        return array_key_exists($keyword, $this->flags[$item] ?? []);
    }

    /**
     * @param string $phr_name the name of a phrase of a value template
     * @return bool true if it is the kind phrase of a cell of the kind column e.g. stock
     */
    private function is_kind(string $phr_name): bool
    {
        return in_array($phr_name, $this->kinds);
    }

    /**
     * @param array $phr_names the phrase names of a value template
     * @return array the column items of the template that are numbers e.g. "bank position csv quantity"
     */
    private function number_items(array $phr_names): array
    {
        return array_values(array_filter($phr_names, fn(string $phr_name) => $this->has_flag($phr_name, triples::CSV_NUMBER)));
    }

    /**
     * @param array $phr_names the phrase names of a value template
     * @param string $kind the kind phrase of the row, '' if it has none
     * @return bool true if the template has no kind phrase or one that is the kind of the row or that the kind is a
     */
    private function is_for_kind(array $phr_names, string $kind): bool
    {
        $result = true;
        foreach ($phr_names as $phr_name) {
            if ($this->is_kind($phr_name) and !$this->is_a($kind, $phr_name, [])) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param string $kind the kind phrase of a row e.g. alternative investment
     * @param string $target the kind phrase of a template e.g. stock
     * @param array $visited the phrases checked already, so that a loop of is a triples ends
     * @return bool true if the kind is the target or the definition declares that the kind is a target, also over
     *              several is a triples
     */
    private function is_a(string $kind, string $target, array $visited): bool
    {
        $result = ($kind == $target);
        foreach ($this->triples as $trp) {
            $from = $trp[json_fields::EX_FROM] ?? '';
            $next = $trp[json_fields::EX_TO] ?? '';
            $is_parent = ($from == $kind and ($trp[json_fields::EX_VERB] ?? '') == verbs::IS_NAME);
            if (!$result and $is_parent and !in_array($next, $visited)) {
                $result = $this->is_a($next, $target, array_merge($visited, [$kind]));
            }
        }
        return $result;
    }

    /**
     * @param array $trp a triple that a template writes
     * @return array the declaration of the definition with the same from, verb and to, else the triple itself
     */
    private function declared_or(array $trp): array
    {
        return $this->keys[$this->triple_key($trp)] ?? $trp;
    }

    /**
     * @param array $trp a triple of the import json
     * @return string the from, the verb and the to of the triple, which identify it like the import does
     */
    private function triple_key(array $trp): string
    {
        $parts = [$trp[json_fields::EX_FROM] ?? '', $trp[json_fields::EX_VERB] ?? '', $trp[json_fields::EX_TO] ?? ''];
        return implode(self::KEY_SEP, $parts);
    }

    /**
     * @param array $trp a triple of the import json
     * @return string its name, else the name that the import generates like triple::generate_name: "from (to)" for
     *                is a and "from verb to" for the other verbs
     */
    private function triple_name(array $trp): string
    {
        $from = $trp[json_fields::EX_FROM] ?? '';
        $verb = $trp[json_fields::EX_VERB] ?? '';
        $to = $trp[json_fields::EX_TO] ?? '';
        $generated = $verb == verbs::IS_NAME ? $from . ' (' . $to . ')' : $from . ' ' . $verb . ' ' . $to;
        return $trp[json_fields::NAME] ?? $generated;
    }

}
