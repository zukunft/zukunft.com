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

include_once SHARED_TYPES_PATH . 'phrase_type.php';

use api\verb\verb as verb_api;
use cfg\export\export;
use cfg\phrase_list;
use cfg\phrase_type;
use cfg\user;
use DateTime;
use DateTimeInterface;
use shared\library;
use shared\types\phrase_type AS phrase_type_shared;

class convert_wikipedia_table
{

    const KEY_TABLE_NAME = 'table_name';

    const TABLE_START = '{| class=';
    const TABLE_END = '|}';
    const CLASS_NAME = '"wikitable';

    const ROW_END = "\n";
    const ROW_MAKER = '|-';
    const COL_MAKER_HEADER = '!';
    const COL_MAKER = '|';
    const COL_MAKER_INNER = '||';
    const NUMBER_MAKER = "'''";
    const LINK_MAKER = "{{";
    const LINK_FLAGDECO = "flagdeco";
    const LINK_MONO = "mono";
    const LINK_SORT = "sort";
    const LINK_STYLE = "style=";
    const LINK_MAKER_END = "}}";
    const LINK_LONG_MAKER = "[[";
    const LINK_LONG_MAKER_END = "]]";
    const ITEM_IGNORE_MAKER = "rowspan";
    const SORT_MAKER = "sort";

    /**
     * convert a wikipedia table to a zukunft.com json string
     * TODO review and move the parameters to a more general context
     *
     * @param string $wiki_tbl wth the wikipedia table
     * @param user $usr the user how has initiated the convertion
     * @param string $timestamp
     * @param array $context a list of phrases that describes the context of the table
     * @param string $row_name_in
     * @param int $col_of_row_name
     * @param string $col_name_in
     * @param string $col_type
     * @param int $col_start
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

        // init json vars
        $word_list = []; // list of simple words where only the name is added / checked
        $words = []; // list of more complex words where more than the name should be imported

        $col_names = $table[0];
        $rows = $table[1];

        // map the table to a json
        $word_list[] = $row_name_in;
        $word_list[] = $col_name_in;
        foreach ($rows as $row) {
            $word_list[] = $row[$col_of_row_name];
        }
        $i = 0;
        foreach ($col_names as $col_name) {
            if ($i > $col_start) {
                $word = [];
                $word[export::NAME] = $col_name;
                $word[export::TYPE] = $col_type;
                $words[] = $word;
            }
            $i++;
        }
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

        // build the json
        $json = $this->header($usr, $timestamp);
        $json[export::SELECTION] = $context;
        $json[export::WORD_LIST] = $word_list;
        $json[export::WORDS] = $words;
        $json[export::VALUE_LIST] = $val_list;

        return json_encode($json);
    }

    /**
     * convert a wikitable2json to a zukunft.com json
     * based on wikitable2json
     * TODO use it to compare wikipedia table with wikidata and report the differences
     * TODO base all assumptions on the given context
     * TODO add a word splitter to seperate e.g. "Growth rate (2019–2022)" to "Growth rate", "2019" and "2022"
     *
     * @param string $wiki_json wth the wikipedia table
     * @param user $usr the user how has initiated the convertion
     * @param string $timestamp the timestamp of the import
     * @param int $table_nbr position of the tbale that should be converted
     * @param string $context a json string with a phrase list for the import context
     * @param array $context_array a list of phrases that describes the context of the table
     * @param string $row_name_wiki column name used to select the row name
     * @param string $row_name_out name for the row entry in the target json
     * @param string $col_name_wiki column name used to select the row name
     * @param string $col_name_out
     * @return string
     */
    function convert_wiki_json(
        string $wiki_json,
        user   $usr,
        string $timestamp,
        string $context = '',
        array  $context_array = [],
        array  $exclude_col_names_in = [],
        int    $table_nbr = 0,
        string $row_name_wiki = '',
        string $row_name_out = '',
        string $col_name_wiki = '',
        string $col_name_out = ''
    ): string
    {
        global $vrb_cac;

        // create context for assumptions
        $list_of_symbols = []; // if a row contains a symbol and a name they are usually linked
        $rank_names = []; // list of phrase names that indicate a columns that contains a rank with should not be included in the result
        $ignore_names = []; // list of phrase names that indicate a columns should not be included in the result
        $phr_lst = new phrase_list($usr);
        if ($context != '') {
            $phr_lst->import_context(json_decode($context, true));
            $list_of_symbols = $phr_lst->get_names_by_type(phrase_type_shared::SYMBOL);
            $rank_names = $phr_lst->get_names_by_type(phrase_type_shared::RANK);
            $ignore_names = $phr_lst->get_names_by_type(phrase_type_shared::IGNORE);
        }
        $exclude_col_names = array_merge($rank_names, $ignore_names);


        $wiki_json = json_decode($wiki_json, true);

        // prepare the result
        $json = $this->header($usr, $timestamp);
        $word_lst = [];
        $triples = [];
        $values = [];

        // usually the first column of a table contains the "key"
        $id_column = null;

        // add the context to the result
        foreach ($context_array as $context_name) {
            $word_lst[] = $context_name;
        }

        // TODO remove: special cases to be deprecated
        if ($col_name_out == '') {
            $col_name_out = $col_name_wiki;
        }
        if ($col_name_out != '') {
            $word_lst[] = $context_array[1] . ' ' . $col_name_out;
        }
        if ($row_name_out != '') {
            $word_lst[] = $row_name_out;
        }

        // select the target table
        $wiki_table = $wiki_json[$table_nbr];

        // get the header row
        $header_row = array_shift($wiki_table);

        // select the columns to convert
        $col_names = [];
        $col_positions = [];
        $i = 0;
        foreach ($header_row as $col_name) {
            if (!in_array($col_name, $exclude_col_names)) {
                $word_lst[] = $col_name;
                $col_names[$i] = $col_name;
                $col_positions[] = $i;
                if ($id_column == null) {
                    $id_column = $i;
                }
            }
            $i++;
        }

        $pos_row = array_search($row_name_wiki, $header_row);
        $pos_col = array_search($col_name_wiki, $header_row);

        // write the words from the rows
        foreach ($wiki_table as $wiki_row) {
            $row_key = '';
            for ($i = 0; $i < count($wiki_row); $i++) {
                if ($i == $id_column) {
                    $row_key = $wiki_row[$i];
                }
            }
            for ($i = 0; $i < count($wiki_row); $i++) {
                if (in_array($i, $col_positions)) {
                    if ($this->is_value($wiki_row[$i])) {
                        $value = [];
                        $val_words = [];
                        $val_words[] = $row_key;
                        $val_words[] = $col_names[$i];
                        if ($this->get_value_words($wiki_row[$i]) != null) {
                            $val_words[] = $this->get_value_words($wiki_row[$i]);
                        }
                        $value[export::WORDS] = $val_words;
                        $value[export::VALUE_NUMBER] = $this->get_value($wiki_row[$i]);
                        $values[] = $value;
                    } else {
                        $word_entry = $wiki_row[$i];
                        $word_lst[] = $word_entry;
                    }
                }
            }
        }

        // get the triples
        foreach ($wiki_table as $wiki_row) {

            // remember the row key
            $row_key = '';
            for ($i = 0; $i < count($wiki_row); $i++) {
                if ($i == $id_column) {
                    $row_key = $wiki_row[$i];
                    if (is_array($row_key)) {
                        $row_key = $row_key[0];
                    }
                }
            }

            if ($col_name_out == '') {
                for ($i = 0; $i < count($wiki_row); $i++) {

                    // create a triple only for the selected columns
                    if (in_array($i, $col_positions)) {
                        $phr_name = $wiki_row[$i];
                        if (is_array($phr_name)) {
                            $phr_name = $phr_name[0];
                        }
                        if (!$this->is_value($phr_name)) {
                            // assume that the row name has an "is a" relation to the column name
                            $trp = [];
                            $trp[export::FROM] = $phr_name;
                            $trp[export::VERB] = verb_api::TN_IS;
                            if ($row_name_out != '') {
                                $trp[export::TO] = $row_name_out;
                            } else {
                                $trp[export::TO] = $col_names[$i];
                            }
                            $triples[] = $trp;

                            // create other assumend relations
                            if (in_array($col_names[$i], $list_of_symbols)) {
                                $trp = [];
                                $trp[export::FROM] = $phr_name;
                                $trp[export::VERB] = verb_api::TN_SYMBOL;
                                $trp[export::TO] = $row_key;
                                $triples[] = $trp;
                            }
                        }
                    }
                }
            }

            // TODO remove: special cases to be deprecated
            if ($col_name_out != '') {
                $trp = [];
                $row_key = $wiki_row[$pos_row];
                if (is_array($row_key)) {
                    $row_key = $row_key[0];
                }
                $trp[export::FROM] = $row_key;
                $trp[export::VERB] = verb_api::TN_IS;
                $trp[export::TO] = $row_name_out;
                $triples[] = $trp;

                $trp = [];
                $trp[export::FROM] = $wiki_row[$pos_col];
                $trp[export::VERB] = verb_api::TN_IS;
                $trp[export::TO] = $context_array[1] . ' ' . $col_name_out;
                $triples[] = $trp;

                $trp = [];
                $trp[export::FROM] = $wiki_row[$pos_col];
                $trp[export::VERB] = verb_api::TN_SYMBOL;
                $trp[export::TO] = $row_key;
                $triples[] = $trp;
            }

        }

        // create the json based on the word list
        $words = $this->word_names_to_array($word_lst);

        if (count($words) > 0) {
            $json[export::WORDS] = $words;
        }
        if (count($triples) > 0) {
            $json[export::TRIPLES] = $triples;
        }
        if (count($values) > 0) {
            $json[export::VALUES] = $values;
        }
        return json_encode($json);
    }

    /**
     * create array for the json based on the word list
     * @param array $word_lst
     * @return array
     */
    private function word_names_to_array(array $word_lst): array
    {
        $words = [];
        $words_in_list = [];

        foreach ($word_lst as $word_entry) {
            $word_name = '';
            $word = [];
            if (is_array($word_entry)) {
                foreach ($word_entry as $word_part) {
                    if (is_array($word_part)) {
                        foreach ($word_part as $key => $word_part_par) {
                            $word[$key] = $word_part_par;
                        }
                    } else {
                        // TODO base this on the ontologie / context
                        $word_name = str_replace('[lower-alpha 2]', '', $word_part);
                        $word[export::NAME] = $word_name;
                    }
                }
            } else {
                if ($word_entry != '') {
                    if (!in_array($word_entry, $words_in_list)) {
                        // TODO base this on the ontologie / context
                        $word_name = str_replace('[lower-alpha 2]', '', $word_entry);
                        $word[export::NAME] = $word_name;
                    }
                }
            }
            if ($word_name != '') {
                if (!in_array($word_name, $words_in_list)) {
                    $words[] = $word;
                    $words_in_list[] = $word_name;
                }
            }
        }
        return $words;
    }

    /**
     * @param string|array $cell_text the text of a table cell
     * @return bool
     */
    private function is_value(string|array $cell_text): bool
    {
        if (is_array($cell_text)) {
            return false;
        } else {
            // remove percent symbol
            // TODO base this on the ontologie / context
            $cell_text = str_replace('%', '', $cell_text);
            if (is_numeric($cell_text)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param string|array $cell_text the text of a table cell
     * @return array|null
     */
    private function get_value_words(string|array $cell_text): array|null
    {
        if (is_array($cell_text)) {
            return null;
        } else {
            // remove percent symbol
            // TODO base this on the ontologie / context
            if ($cell_text == '%') {
                $word = [];
                $word[export::NAME] = '%';
                return $word;
            } else {
                return null;
            }
        }
    }

    /**
     * @param string|array $cell_text the text of a table cell
     * @return float|null
     */
    private function get_value(string|array $cell_text): ?float
    {
        if (is_array($cell_text)) {
            return null;
        } else {
            // remove percent symbol
            // TODO base this on the ontologie / context
            $cell_text = str_replace('%', '', $cell_text);
            if (is_numeric($cell_text)) {
                return floatval($cell_text);
            } else {
                return null;
            }
        }
    }

    /**
     * convert a wikipedia table into an array
     * - the first array are the column headlines
     * - and the following arrays are the rows
     *   - each cell can be a string with the written value
     *   - or an array where the written value is the first entry
     *     and the
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
                    $col_name = $this->wikipedia_cell_to_string_or_array($col_name);
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
                        $row_entry = $lib->str_left_of($tbl_str, self::ROW_END);
                        $row_entry = $lib->str_right_of($row_entry, self::COL_MAKER);
                        if (str_contains($row_entry, self::COL_MAKER_INNER)) {
                            $row_remaining = $row_entry;
                            while (str_contains($row_remaining, self::COL_MAKER_INNER)) {
                                $row_entry = $lib->str_left_of($row_entry, self::COL_MAKER_INNER);
                                if (strpos($row_entry, self::ITEM_IGNORE_MAKER) > 0) {
                                    $row_entry = '';
                                } else {
                                    // get the cell text
                                    $row_entry = $this->wikipedia_cell_to_string_or_array($row_entry);
                                }
                                if ($row_entry != '') {
                                    $row_in[] = $row_entry;
                                }
                                $row_remaining = $lib->str_right_of($row_remaining, self::COL_MAKER_INNER);
                            }
                        } else {
                            if (strpos($row_entry, self::ITEM_IGNORE_MAKER) > 0) {
                                $row_entry = '';
                            } else {
                                // get the cell text
                                $row_entry = $this->wikipedia_cell_to_string_or_array($row_entry);
                            }
                            if ($row_entry != '') {
                                $row_in[] = $row_entry;
                            }
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

    /**
     * get the display text from a wikipedia cell
     * @param string $in_text the text of the cell
     * @return string|array the text shown to the user or an array with triples
     */
    private function wikipedia_cell_to_string_or_array(string $in_text): string|array
    {
        $lib = new library();
        $result = '';
        $remaining = $in_text;

        // ignore styles
        if (str_contains($remaining, self::COL_MAKER)
            and str_contains($remaining, self::LINK_STYLE)) {
            $remaining = $lib->str_right_of($remaining, self::LINK_STYLE);
            $remaining = $lib->str_right_of($remaining, self::COL_MAKER);
        }

        // ignore sort
        if (str_contains($remaining, self::COL_MAKER)
            and str_contains($remaining, self::SORT_MAKER)) {
            // TODO get sort name
            $remaining = $lib->str_right_of($remaining, self::SORT_MAKER);
            $remaining = $lib->str_right_of($remaining, self::COL_MAKER);
            $sort_name = $lib->str_left_of($remaining, self::COL_MAKER);
            $remaining = $lib->str_right_of($remaining, self::COL_MAKER);
        }

        // get the number
        if ($lib->str_between($remaining, self::NUMBER_MAKER, self::NUMBER_MAKER) != '') {
            $number_text = $lib->str_between($remaining, self::NUMBER_MAKER, self::NUMBER_MAKER);
            $result .= $number_text;
            $remaining = $lib->str_right_of($remaining, self::NUMBER_MAKER);
            $remaining = $lib->str_right_of($remaining, self::NUMBER_MAKER);
        }

        // get the text from links
        if (str_contains($remaining, self::LINK_LONG_MAKER)
            and str_contains($remaining, self::LINK_LONG_MAKER_END)) {
            $result .= $lib->str_left_of($remaining, self::LINK_LONG_MAKER);
            $link_text = $lib->str_between($remaining, self::LINK_LONG_MAKER, self::LINK_LONG_MAKER_END);
            if (str_contains($link_text, self::COL_MAKER)) {
                $link_text = $lib->str_right_of($link_text, self::COL_MAKER);
            }
            $result .= $link_text;
            $remaining = $lib->str_right_of($remaining, self::LINK_LONG_MAKER_END);
        }
        if (str_contains($remaining, self::COL_MAKER)) {
            if (str_starts_with($remaining, self::SORT_MAKER)) {
                // TODO get sort name
                $remaining = $lib->str_right_of($remaining, self::LINK_MAKER);
            }
        }
        if (str_contains($remaining, self::LINK_MAKER)
            and str_contains($remaining, self::LINK_MAKER_END)) {
            $result = $lib->str_left_of($remaining, self::LINK_MAKER);
            $link_text = $lib->str_between($remaining, self::LINK_MAKER, self::LINK_MAKER_END);
            if (str_contains($link_text, self::COL_MAKER)) {
                $link_type = $lib->str_left_of($link_text, self::COL_MAKER);
                if ($link_type != self::LINK_FLAGDECO) {
                    $link_text = $lib->str_right_of($link_text, self::COL_MAKER);
                }
            }
            $result .= $link_text;
            $remaining = $lib->str_right_of($remaining, self::LINK_LONG_MAKER_END);
        }
        $result .= $remaining;
        return trim($result);
    }

    private function wikipedia_text_to_json_array(string $wiki_text, array $result): array
    {
        $lib = new library();
        if (str_contains($wiki_text, self::LINK_MAKER)) {
            $link_text = $lib->str_right_of($wiki_text, self::LINK_MAKER);
            if (str_contains($link_text, self::LINK_MAKER_END)) {
                $link_text = $lib->str_left_of($link_text, self::LINK_MAKER_END);
                $link_type = $lib->str_left_of($link_text, self::COL_MAKER);
                $link_text = $lib->str_right_of($link_text, self::COL_MAKER);
                if ($link_type == self::LINK_FLAGDECO) {
                    $link_result = [];
                    $link_result[self::LINK_FLAGDECO] = $link_text;
                    $result[] = $link_result;
                }
            }
        }
        return $result;
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
