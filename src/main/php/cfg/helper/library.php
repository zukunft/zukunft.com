<?php

/*

    model/helper/library.php - some useful function e.g. for string handling
    ------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

use api\api;
use api\combine_object_api;
use controller\controller;
use DateTime;
use DOMDocument;
use Exception;

class library
{

    const DIFF_NUM_PRECISION = 7;

    // to separate two string for the human-readable format
    const SEPARATOR = ',';

    /*
     * internal const
     */

    const STR_TYPE_AUTO = -1; // try to detect the type
    const STR_TYPE_CODE = 0; // the string should be checked byte by byte
    const STR_TYPE_PROSA = 1; // words are the critical parts
    const STR_TYPE_JSON = 2; // check for json elements
    const STR_TYPE_HTML = 3;

    private const STR_DIFF_VAL = 'values';
    private const STR_DIFF_TYP = 'type';
    private const STR_DIFF_UNCHANGED = 0;
    private const STR_DIFF_ADD = 1;
    private const STR_DIFF_DEL = -1;
    private const STR_DIFF_ADD_START = '//+';
    private const STR_DIFF_ADD_END = '//';
    private const STR_DIFF_DEL_START = '//-';
    private const STR_DIFF_DEL_END = '//';
    private const STR_DIFF_MSG_LEN = 100; // the max target length of the difference message to keep it human-readable
    private const STR_DIFF_MATCH_LEN = 8; // the min length of a matching pattern to keep the diff human-readable

    // the expected minimal length of 80% of the words
    private const STR_WORD_MIN_LEN = 2;
    // the expected maximal length of 80% of the words
    private const STR_WORD_MAX_LEN = 20;
    private const STR_WORD_MIN_LEN_NORMAL_IN_PCT = 0.9; // if 90% of the words have a "normal" length the text is supposed to be a text for humans


    /*
     * convert
     */

    /**
     * convert a database datetime string to a php DateTime object
     *
     * @param string $datetime_text the datetime as received from the database
     * @return DateTime the converted DateTime value or now()
     */
    function get_datetime(string $datetime_text, string $obj_name = '', string $process = ''): DateTime
    {
        $result = new DateTime();
        try {
            $result = new DateTime($datetime_text);
        } catch (Exception $e) {
            $msg = 'Failed to convert the database DateTime value ' . $datetime_text;
            if ($obj_name != '') {
                $msg .= ' for ' . $obj_name;
            }
            if ($process != '') {
                $msg .= ' during ' . $process;
            }
            $msg .= ', because ' . $e;
            $msg .= ' reset to now';
            log_err($msg);
        }
        return $result;
    }

    /**
     * convert a database boolean tiny int value to a php boolean object
     *
     * @param int|null $bool_value the value as received from the database
     * @return bool true if the database value is 1
     */
    function get_bool(?int $bool_value): bool
    {
        if ($bool_value == 1) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * format
     */

    /**
     * @param string $string_with_multiple_spaces
     * @return string text with just single spaces
     */
    function trim(string $string_with_multiple_spaces): string
    {
        return trim(preg_replace('!\s+!', ' ', $string_with_multiple_spaces));
    }

    /**
     * @param string $string_with_multiple_spaces
     * @return string text without any single spaces
     */
    function trim_all_spaces(string $string_with_multiple_spaces): string
    {
        return trim(preg_replace('/\s+/', '', $string_with_multiple_spaces));
    }

    /**
     * @param string $string_with_new_lines
     * @return string text with just single spaces and without line feeds
     */
    function trim_lines(string $string_with_new_lines): string
    {
        return $this->trim(preg_replace('/[\n\r]/', ' ', $string_with_new_lines));
    }

    /**
     * @param string $sql_string
     * @return string text with just single spaces and all spaces removed not needed in a SQL
     */
    function trim_sql(string $sql_string): string
    {
        $result = $this->trim_lines($sql_string);
        $result = preg_replace('/\( /', '(', $result);
        $result = preg_replace('/ \)/', ')', $result);
        return preg_replace('/, /', ',', $result);
    }

    /**
     * @param string $json_string
     * @return string text with just single spaces and all spaces removed not needed in a JSON
     */
    function trim_json(string $json_string): string
    {
        $result = $this->trim_lines($json_string);
        $result = preg_replace('/\[ {/', '[{', $result);
        $result = preg_replace('/] }/', ']}', $result);
        $result = preg_replace('/{ \[/', '{[', $result);
        $result = preg_replace('/} ]/', '}]', $result);
        $result = preg_replace('/" }/', '"}', $result);
        $result = preg_replace('/": /', '":', $result);
        $result = preg_replace('/, "/', ',"', $result);
        $result = preg_replace('/{ "/', '{"', $result);
        return preg_replace('/}, {/', '},{', $result);
    }

    /**
     * @param string $html_string
     * @return string text with just single spaces and all spaces removed not needed for HTML
     */
    function trim_html(string $html_string): string
    {
        $result = $this->trim_lines($html_string);
        $result = preg_replace('/ <td>/', '<td>', $result);
        $result = preg_replace('/ <\/td>/', '</td>', $result);
        $result = preg_replace('/ <th>/', '<th>', $result);
        $result = preg_replace('/ <\/th>/', '</th>', $result);
        $result = preg_replace('/ <tr>/', '<tr>', $result);
        $result = preg_replace('/ <\/tr>/', '</tr>', $result);
        $result = preg_replace('/> <div/', '><div', $result);
        $result = preg_replace('/> <\/div/', '></div', $result);
        $result = preg_replace('/> <table/', '><table', $result);
        $result = preg_replace('/> <\/table/', '></table', $result);
        $result = preg_replace('/> <footer/', '><footer', $result);
        $result = preg_replace('/> <\/footer/', '></footer', $result);
        $result = preg_replace('/" \/>/', '"/>', $result);
        $result = preg_replace('/" </', '"<', $result);
        $result = preg_replace('/ >/', '>', $result);
        $result = preg_replace('/ </', '<', $result);
        return preg_replace('/> </', '><', $result);
    }


    /*
     * select string part functions
     */

    /**
     * @param string $text the text from which a part should be taken e.g. "select" of "ignore start<select>end ignore"
     * @param string $maker_start e.g. "start<"
     * @param string $maker_end e.g. ">end"
     * @return string the selected text e.g. "select"
     */
    function str_between(string $text, string $maker_start, string $maker_end): string
    {
        $result = $this->str_right_of($text, $maker_start);
        return $this->str_left_of($result, $maker_end);
    }

    /**
     * @param string $text the text from which the left part should be taken e.g. "select" of "select>end ignore"
     * @param string $maker e.g. ">end"
     * @return string the selected text e.g. "select"
     */
    function str_left_of(string $text, string $maker): string
    {
        $result = "";
        $pos = strpos($text, $maker);
        if ($pos > 0) {
            $result = substr($text, 0, strpos($text, $maker));
        }
        return $result;
    }

    /**
     * @param string|null $text the text from which the right part should be taken e.g. "select" of "ignore start<select"
     * @param string|null $maker e.g. "start<"
     * @return string the selected text e.g. "select"
     */
    function str_right_of(?string $text, ?string $maker): string
    {
        $result = "";
        if ($text == null) {
            $text = "";
        }
        if ($maker == null) {
            $maker = "";
        }
        if ($text !== $maker) {
            if (substr($text, strpos($text, $maker), strlen($maker)) === $maker) {
                $result = substr($text, strpos($text, $maker) + strlen($maker));
            }
        }
        return $result;
    }

    /**
     * @param string|null $text the text from which the right part should be taken e.g. "select" of "ignore start<select"
     *                          or the complete text if maker not found
     * @param string|null $maker e.g. "start<"
     * @return string the selected text e.g. "select"
     */
    function str_right_of_or_all(?string $text, ?string $maker): string
    {
        if ($text == null) {
            $text = "";
        }
        $result = $text;
        if ($maker == null) {
            $maker = "";
        }
        if ($result !== $maker) {
            while (strpos($result, $maker) > 0) {
                if (substr($result, strpos($result, $maker), strlen($maker)) === $maker) {
                    $result = substr($result, strpos($result, $maker) + strlen($maker));
                }
            }
        }
        return $result;
    }

    /*
     * string functions (to be dismissed)
     * some small string related functions to shorten code and make the code clearer
     */

    function str_left(string $text, int $pos): string
    {
        return substr($text, 0, $pos);
    }

    function str_right(string $text, int $pos): string
    {
        return substr($text, $pos * -1);
    }

    function camelize(string $input, string $separator = '_'): string
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    function camelize_ex_1(string $input, string $separator = '_'): string
    {
        return str_replace($separator, '', lcfirst(ucwords($input, $separator)));
    }


    /*
     * list functions (to be replaced by standard functions if possible)
     */

    function array_flat(array $array): array
    {
        $return = array();
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

    /**
     * recursive count of the number of elements in an array but limited to a given level
     * @param array $json_array the array that should be analysed
     * @param int $levels the number of levels that should be taken into account (-1 or empty for unlimited levels)
     * @param int $level used for the recursive
     * @return int the number of elements
     */
    function count_recursive(array $json_array, int $levels = -1, int $level = 1): int
    {
        $result = 0;
        if ($json_array != null) {
            if ($level <= $levels or $levels == -1) {
                foreach ($json_array as $sub_array) {
                    $result++;
                    if (is_array($sub_array)) {
                        $result = $result + $this->count_recursive($sub_array, $levels, ++$level);
                    }
                }
            }
        }
        return $result;
    }

    function dsp_count(?array $in_array): int
    {
        $result = 0;
        if ($in_array != null) {
            $result = count($in_array);
        }
        return $result;
    }

    /**
     * remove all empty string entries from an array
     * @param array|null $in_array the array with empty strings or string with leading spaces
     * @return array the value comma seperated or "null" if the array is empty
     */
    function array_trim(?array $in_array): array
    {
        $result = array();
        if ($in_array != null) {
            foreach ($in_array as $item) {
                if (trim($item) <> '') {
                    $result[] = trim($item);
                }
            }
        }
        return $result;
    }

    /**
     * prepare an array for an SQL statement
     * maybe needs esc of values and check of SQL injection
     *
     * @param array $in_array the array that should be formatted
     * @param string $start the start text of the SQL statement
     * @param string $end the end text of the SQL statement
     * @return string the values comma seperated or "" if the array is empty
     */
    function sql_array(
        array  $in_array,
        string $start = '',
        string $end = '',
        bool   $sql_format = false): string
    {
        global $db_con;
        $result = '';
        if ($in_array != null) {
            if (count($in_array) > 0) {
                if ($sql_format) {
                    $result = $start . $db_con->sf(implode(',', $in_array)) . $end;
                } else {
                    $result = $start . implode(',', $in_array) . $end;
                }
            }
        }
        return $result;
    }

    /**
     * @return array with all entries of the list that are not in the second list
     */
    function lst_not_in(array $in_lst, array $exclude_lst): array
    {
        $lib = new library();
        $result = array();
        foreach ($in_lst as $lst_entry) {
            if (!in_array($lst_entry, $exclude_lst)) {
                $result[] = $lst_entry;
            }
        }
        return $result;
    }

    /**
     * create an url parameter text out of an id array
     * @param array $ids
     * @param string $par_name
     * @return string
     */
    function ids_to_url(array $ids, string $par_name): string
    {
        $result = "";
        foreach (array_keys($ids) as $pos) {
            $nbr = $pos + 1;
            if ($ids[$pos] <> "" or $ids[$pos] === 0) {
                $result .= "&" . $par_name . $nbr . "=" . $ids[$pos];
            }
        }
        return $result;
    }

    /**
     * @param array $ids that can contain o and null values
     * @return array with positive or negative numbers
     */
    function ids_not_empty(array $ids): array
    {
        $result = array();
        foreach ($ids as $id) {
            if ($id != null) {
                if ($id > 0) {
                    $result[] = $id;
                }
            }
        }
        return $result;
    }


    /*
     * diff
     */

    /**
     * explains the difference between two strings or arrays
     * in a useful human-readable format
     *
     * @param string|array $result the actual value that should be checked
     * @param string|array $target the expected value to compare
     * @param bool $ignore_order true if the array can be resorted to find the matches
     * @return string an empty string if the actual value matches the expected
     */
    function diff_msg(
        string|array $result,
        string|array $target,
        bool         $ignore_order = true): string
    {
        if (is_string($target) and is_string($result)) {
            $msg = $this->str_diff_msg($result, $target);
        } elseif (is_array($target) and is_array($result)) {
            $msg = $this->array_explain_missing($result, $target);
            $msg .= $this->array_explain_missing($target, $result, self::STR_DIFF_DEL_START, self::STR_DIFF_DEL_END);
            if ($msg == '') {
                $msg = $this->array_diff_msg($result, $target, $ignore_order);
            }
        } else {
            $msg = 'The type combination of ' . gettype($target) . ' and ' . gettype($target) . ' are not expected.';
        }
        return $msg;
    }

    /**
     * explains the difference between two strings
     * in a useful human-readable format
     *
     * @param string $result the actual value that should be checked
     * @param string $target the expected value to compare
     * @return string an empty string if the actual value matches the expected
     */
    private function str_diff_msg(string $result, string $target): string
    {
        $msg = '';
        if ($result != $target) {
            if (is_numeric($result) && is_numeric($target)) {
                $result = round($result, self::DIFF_NUM_PRECISION);
                $target = round($target, self::DIFF_NUM_PRECISION);
                if ($result != $target) {
                    $msg .= 'number ' . $result . ' != ' . $target;
                    // TODO add this message on level info
                    // } else {
                    //    $msg = 'number ' . $result . ' ≈ ' . $target;
                }
            } else {
                if ($target == '') {
                    $msg .= 'Target is not expected to be empty ' . $result;
                } else {
                    $diff = $this->str_diff($target, $result);
                    if ($diff != '') {
                        $msg .= $diff;
                    }
                }
            }
        }
        return $msg;
    }

    /**
     * explains the difference between two arrays
     * in a useful human-readable format
     *
     * @param array $result the actual value that should be checked
     * @param array $target the expected value to compare
     * @return string an empty string if the actual value matches the expected
     */
    private function array_diff_msg(
        array $result,
        array $target,
        bool  $ignore_order = true): string
    {
        $msg = '';
        if ($ignore_order) {
            // sort only if needed
            $do_sort = false;
            $pos = 0;
            $result_keys = array_keys($result);
            $result_value = array_values($result);
            foreach ($target as $key => $value) {
                // sort is only needed
                // if the key on the same position does not match matches
                if ($result_keys[$pos] != $key) {
                    $do_sort = true;
                }
                // or the value does not match
                if ($result_value[$pos] != $value) {
                    $do_sort = true;
                }
                $pos++;
            }
            if ($do_sort) {
                sort($target);
                sort($result);
            }
        }
        // in an array each value needs to be the same
        foreach ($target as $key => $value) {
            if (array_key_exists($key, $result)) {
                if ($value != $result[$key]) {
                    $msg .= $this->str_sep($msg);
                    $msg .= 'pos  ' . $key . ': ' . $this->diff_msg($result[$key], $value);
                }
            }
        }
        return $msg;
    }

    /**
     * explains which parts of the target are missing
     * in the result in a useful human-readable format
     *
     * @param string|array $result the actual result that can contain more than the target
     * @param string|array $target the part that must at least be part of the result
     * @return string an empty string if all target entries are part of the result
     */
    function explain_missing(string|array $result, string|array $target): string
    {
        if (is_array($target) and is_array($result)) {
            if ($result !== $target) {
                $msg = $this->array_explain_missing($result, $target);
            } else {
                $msg = '';
            }
        } elseif (is_string($target) and is_string($result)) {
            $msg = $this->str_diff_msg($result, $target);
        } else {
            $msg = 'The type combination of ' . gettype($target) . ' and ' . gettype($target) . ' are not expected.';
        }
        return $msg;
    }

    /**
     * explains which array parts of the target are missing
     * in the result in a useful human-readable format
     *
     * @param array $result the actual result that can contain more than the target
     * @param array $target the part that must at least be part of the result
     * @param string $start_maker to mark the start of the missing part
     * @param string $end_maker to mark the end of the missing part
     * @return string an empty string if all target entries are part of the result
     */
    private function array_explain_missing(
        array  $result,
        array  $target,
        string $start_maker = self::STR_DIFF_ADD_START,
        string $end_maker = self::STR_DIFF_ADD_END): string
    {
        $msg = '';
        $more = 0;
        // in an array each value needs to be the same
        foreach ($target as $key => $value) {
            if (!array_key_exists($key, $result)) {
                if (strlen($msg) < self::STR_DIFF_MSG_LEN) {
                    $msg .= $this->str_sep($msg);
                    $msg .= $key . $start_maker;
                    if (is_array($value)) {
                        $msg .= json_encode($value);
                    } else {
                        $msg .= $value;
                    }
                    $msg .= $end_maker;
                } else {
                    $more++;
                }
            } else {
                if (is_array($value)) {
                    $msg .= $this->array_explain_missing($result[$key], $value);
                }
            }
        }
        if ($more > 0) {
            $msg .= ' ... and ' . $more . ' more';
        }
        return $msg;
    }


    /*
     * display
     * to format objects as a string
     */

    /**
     * @param array|string|int|null $var_to_format
     * @return string best guess formatting of an array, string or int value for debug lines
     */
    function dsp_var(array|string|int|null $var_to_format): string
    {
        $result = '';
        $lib = new library();
        if ($var_to_format != null) {
            if (is_array($var_to_format)) {
                $result = $lib->dsp_array($var_to_format);
            } else {
                $result = $var_to_format;
            }
        }
        return $result;
    }

    /**
     * create a human-readable string from an array
     * @param array|null $in_array the array that should be formatted
     * @return string the value comma seperated or "null" if the array is empty
     */
    function dsp_array(?array $in_array, bool $with_keys = false): string
    {
        global $debug;

        $lib = new library();
        $result = 'null';
        if ($in_array != null) {
            if ($debug > 10 or count($in_array) < 7) {
                if (count($in_array) > 0) {
                    $result = implode(',', $lib->array_flat($in_array));
                }
                if ($with_keys) {
                    $result .= ' (keys ' . $this->dsp_array_keys($in_array) . ')';
                }
            } else {
                $left = array_slice($in_array, 0, 3);
                $result = implode(',', $lib->array_flat($left));
                $result .= ',...,' . end($in_array);
            }
        }
        return $result;
    }

    function dsp_array_keys(?array $in_array): string
    {
        global $debug;

        $lib = new library();
        $result = 'null';
        if ($in_array != null) {
            $keys = array_keys($in_array);
            if ($debug > 10 or count($keys) < 7) {
                if (count($keys) > 0) {
                    $result = implode(self::SEPARATOR, $keys);
                }
            } else {
                $left = array_slice($keys, 0, 3);
                $result = implode(self::SEPARATOR, $lib->array_flat($left));
                $result .= ',...,' . end($keys);
            }
        }
        return $result;
    }

    /**
     * if useful add a comma as a separator to make a contacted text  easier to read for humans
     * @param string $msg the text as use until now
     * @return string the text with a comma if this helps to make the text easier to read
     */
    private
    function str_sep(string $msg): string
    {
        if ($msg != '') {
            return self::SEPARATOR . ' ';
        } else {
            return '';
        }

    }


    /*
     * json utils
     */

    /**
     * remove all empty field from a json
     */
    function json_clean(array $in_json): array
    {
        foreach ($in_json as &$value) {
            if (is_array($value)) {
                $value = $this->json_clean($value);
            }
        }

        return array_filter($in_json);

    }

    private
    static function sort_array_by_class($a, $b): int
    {
        return strcmp($a[combine_object_api::FLD_CLASS], $b[combine_object_api::FLD_CLASS]);
    }

    private
    static function sort_array_by_id($a, $b): int
    {
        return $a[api::FLD_ID] - $b[api::FLD_ID];
    }

    private
    static function sort_by_class_and_id(?array $a): ?array
    {
        if ($a != null) {
            if (count($a) > 0) {
                if (array_key_exists(0, $a)) {
                    if (is_array($a[0])) {
                        if (array_key_exists(combine_object_api::FLD_CLASS, $a[0])) {
                            usort($a, array('cfg\library', 'sort_array_by_class'));
                        }
                        if (array_key_exists(api::FLD_ID, $a[0])) {
                            usort($a, array('cfg\library', 'sort_array_by_id'));
                        }
                    }
                }
            }
        }
        return $a;
    }

    /**
     * check if the import JSON array matches the export JSON array
     * @param array|null $json_in a JSON array that is can contain empty field
     * @param array|null $json_ex a JSON that can have other empty field than $json_in and in a different order
     * @return bool true if the JSON have the same meaning
     */
    function json_is_similar(?array $json_in, ?array $json_ex): bool
    {
        // sort multidimensional arrays by class and id if useful
        $json_in = $this->sort_by_class_and_id($json_in);
        $json_ex = $this->sort_by_class_and_id($json_ex);

        // this is for compare, so a null value is considered to be the same as an empty array
        if ($json_in == null) {
            $json_in = [];
        }
        if ($json_ex == null) {
            $json_ex = [];
        }
        // remove empty JSON fields
        $json_in_clean = json_encode($this->json_clean($json_in));
        $json_ex_clean = json_encode($this->json_clean($json_ex));
        // compare the JSON object not the array to ignore the order
        return json_decode($json_in_clean) == json_decode($json_ex_clean);

    }

    /**
     * get the diff of a multidimensional array where the sub item can ba matched by a key
     *
     * @param array $needle the smaller array that is expected to be part of the haystack array
     * @param array $haystack the bigger array that is expected to contain all items from the needle
     * @param string $key_name the key name to find the matching item in the haystack
     * @return array an empty array if all item and sub items from the needle are in the haystack
     */
    function array_recursive_diff(array $needle, array $haystack, string $key_name = sql_db::FLD_ID): array
    {
        $result = array();

        //
        foreach ($needle as $key => $value) {
            if (array_key_exists($key, $haystack)) {
                if (is_array($value)) {
                    // find the matching haystack entry if a key name is set
                    $key_value = '';
                    $haystack_key = -1;
                    // loop over the inner needle items
                    foreach ($value as $inner_key => $inner_value) {
                        if (is_array($inner_value)) {
                            if ($key_name != '') {
                                $key_value = $inner_value[$key_name];
                            }
                        } else {
                            if ($inner_value != $haystack[$key][$inner_key]) {
                                $result[$inner_key] = $inner_value;
                            }
                        }
                        // find the entry in the haystack that matches the key value
                        if ($key_value != '') {
                            foreach ($haystack[$key] as $search_key => $inner_haystack) {
                                if ($inner_haystack[$key_name] == $key_value) {
                                    $haystack_key = $search_key;
                                }
                            }
                        }
                        if ($haystack_key >= 0) {
                            $inner_haystack = $this->array_recursive_diff($inner_value, $haystack[$key][$haystack_key]);
                            if (count($inner_haystack)) {
                                $result[$key] = $inner_haystack;
                            }
                        }
                    }
                    if ($haystack_key < 0) {
                        $inner_haystack = $this->array_recursive_diff($value, $haystack[$key]);
                        if (count($inner_haystack)) {
                            $result[$key] = $inner_haystack;
                        }
                    }
                } else {
                    if ($value != $haystack[$key]) {
                        $result[$key] = $value;
                    }
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * check if the import JSON array matches the export JSON array
     * @param array|null $json_needle a JSON array that is can contain empty field
     * @param array|null $json_haystack a JSON that can have additional fields than $json_needle and in a different order
     * @return bool true if the JSON have the same meaning
     */
    function json_contains(?array $json_needle, ?array $json_haystack): bool
    {
        // this is for compare, so a null value is considered to be the same as an empty array
        if ($json_needle == null) {
            $json_needle = [];
        }
        if ($json_haystack == null) {
            $json_haystack = [];
        }
        // remove empty JSON fields
        $json_needle_clean = $this->json_clean($json_needle);
        $json_haystack_clean = $this->json_clean($json_haystack);
        // compare the JSON object not the array to ignore the order
        $diff = $this->array_recursive_diff($json_needle_clean, $json_haystack_clean);
        if (count($diff) == 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * add a json at a defined node to a given json
     * @param array $target_json the json array that should be extended
     * @param array $json_to_merge the json that should be added to the target
     * @param string $node_name the name of the node where the json should be added
     * @return array the json array that contains both json
     */
    function json_merge(array $target_json, array $json_to_merge, string $node_name): array
    {
        $result = $target_json;
        $result[$node_name] = $json_to_merge;
        return $result;
    }

    /**
     * same as json_merge, but for json strings
     * @param string $target_json the json text that should be extended
     * @param string $json_to_merge the json that should be added to the target
     * @param string $node_name the name of the node where the json should be added
     * @return string the json text that contains both json
     */
    function json_merge_str(string $target_json, string $json_to_merge, string $node_name): string
    {
        return json_encode($this->json_merge(
            json_decode($target_json, true),
            json_decode($json_to_merge, true),
            $node_name
        ));
    }


    /*
     * testing support
     */

    /**
     * highlight the first difference between two string
     * @param string|null $from the expected text
     * @param string|null $to the text to compare
     * @return string the first char that differs or an empty string
     */
    function str_diff_old(?string $from, ?string $to): string
    {
        $result = '';

        if ($from != null and $to != null) {
            if ($from != $to) {
                $f = str_split($from);
                $t = str_split($to);

                // add message if just one string is shorter
                if (count($f) < count($t)) {
                    $result = '@' . count($t) . ' less: ' . substr($to, count($f), count($t) - count($f));
                } elseif (count($t) < count($f)) {
                    $result = '@' . count($t) . ' additional: ' . substr($from, count($t), count($f) - count($t));
                }

                // find the first diff
                $i = 0;
                while ($i < count($f) and $i < count($t) and $result == '') {
                    if ($f[$i] != $t[$i]) {
                        $result = '@' . $i . ': ' . $f[$i] . ' (' . ord($f[$i]) . ') != ' . $t[$i] . ' (' . ord($t[$i]) . ')';
                        $result .= ', near ' . substr($from, $i - 10, 20);
                    }
                    $i++;
                }
            }
        } elseif ($from == null and $to != null) {
            $result = 'less: ' . $to;
        } elseif ($from != null and $to == null) {
            $result = 'additional: ' . $from;
        }


        return $result;
    }

    /**
     * highlight the first differences between two string
     * @param string|null $from the text to compare
     * @param string|null $to the expected text
     * @return string the first char that differs or an empty string
     */
    function str_diff(?string $from, ?string $to): string
    {
        $str_type = $this->str_type($to);
        $from_array = $this->str_split_for_humans($from, $str_type, false);
        $from_sep = $this->str_split_for_humans($from, $str_type);
        $to_array = $this->str_split_for_humans($to, $str_type, false);
        $to_sep = $this->str_split_for_humans($to, $str_type);
        $diff = $this->str_diff_list($from_array, $to_array, $from_sep, $to_sep, $str_type);
        $diff_val = $diff[self::STR_DIFF_VAL];
        $diff_typ = $diff[self::STR_DIFF_TYP];

        // if the result is expected to be long only show the differences
        $long_text = false;
        if (strlen($from) > self::STR_DIFF_MSG_LEN
            or strlen($to) > self::STR_DIFF_MSG_LEN) {
            $long_text = true;
        }

        // create the user message
        $prev_type = 0;
        $msg = '';
        $pos = 1;
        $prev_pos = 1;
        $i = 0;
        while (($i < count($diff_val) and strlen($msg) < self::STR_DIFF_MSG_LEN)) {
            $type = $diff_typ[$i];
            if ($type != $prev_type) {
                switch ($prev_type) {
                    case self::STR_DIFF_DEL:
                        $msg .= self::STR_DIFF_DEL_END;
                        break;
                    case self::STR_DIFF_ADD:
                        $msg .= self::STR_DIFF_ADD_END;
                        break;
                }
                switch ($type) {
                    case self::STR_DIFF_DEL:
                        if ($long_text and $pos != $prev_pos) {
                            $msg .= $pos;
                            $prev_pos = $pos;
                        }
                        $msg .= self::STR_DIFF_DEL_START;
                        break;
                    case self::STR_DIFF_ADD:
                        if ($long_text and $pos != $prev_pos) {
                            $msg .= $pos;
                            $prev_pos = $pos;
                        }
                        $msg .= self::STR_DIFF_ADD_START;
                        break;
                }
            }
            if ($long_text) {
                if ($type != self::STR_DIFF_UNCHANGED) {
                    $msg .= $diff_val[$i];
                }
                if ($type != self::STR_DIFF_DEL) {
                    $pos = $pos + strlen($diff_val[$i]);
                }
            } else {
                $msg .= $diff_val[$i];
            }

            $prev_type = $type;
            $i++;
        }
        switch ($prev_type) {
            case self::STR_DIFF_DEL:
                $msg .= self::STR_DIFF_DEL_END;
                break;
            case self::STR_DIFF_ADD:
                $msg .= self::STR_DIFF_ADD_END;
                break;
        }

        if ($i < count($diff_val)) {
            $additional_diff = count($diff_val) - $i;
            $msg .= ' and ' . $additional_diff . ' more';
        }

        return $msg;
    }

    /**
     * array with the differences of two strings converted into arrays with "useful" parts
     *
     * @param array $from the target string converted to an array
     * @param array $to the result string converted to an array
     * @return array of array of
     *         values: a list of elements as they appear in the diff
     *           type: contains numbers. 0: unchanged, -1: removed, 1: added
     */
    private
    function str_diff_list(
        array  $from,
        array  $to,
        ?array $from_sep = null,
        ?array $to_sep = null,
        int    $str_type): array
    {
        if ($from_sep == null
            or count($from_sep) != count($from)
            or array_keys($from_sep) != array_keys($from)) {
            $from_sep = array_values($from);
        }
        if ($to_sep == null
            or count($to_sep) != count($to)
            or array_keys($to_sep) != array_keys($to)) {
            $to_sep = array_values($to);
        }

        $diff_part = array(); // list with the differences
        $diff_type = array(); // list with the difference type: same, add or del

        $from_pos = 0;
        $to_pos = 0;

        // check if the from parts are also part of to
        while ($from_pos < count($from)) {
            if (!array_key_exists($to_pos, $to)) {
                log_err('To pos ' . $to_pos . ' not found in ' . implode(",",$to) . ' when comparing to ' . implode(",",$from));
            }
            if ($from[$from_pos] == $to[$to_pos]) {
                $diff_part[] = $to_sep[$to_pos];
                $diff_type[] = self::STR_DIFF_UNCHANGED;
                if ($to_pos < count($to)) {
                    $to_pos++;
                }
                $from_pos++;
            } else {
                if ($str_type == self::STR_TYPE_CODE) {
                    $from_len = min(self::STR_DIFF_MATCH_LEN, count($from) - $from_pos);
                    $search = array_slice($from, $from_pos, $from_len);
                    $match_pos = $this->str_diff_list_next_array_match($search, $to, $to_pos);
                } else {
                    $match_pos = $this->str_diff_list_next_match($from[$from_pos], $to, $to_pos);
                }
                if ($match_pos > $to_pos) {
                    for ($add_pos = $to_pos; $add_pos < $match_pos; $add_pos++) {
                        $diff_part[] = $to_sep[$add_pos];
                        $diff_type[] = self::STR_DIFF_ADD;
                    }
                    $to_pos = $add_pos;
                } elseif ($match_pos == -1) {
                    $diff_part[] = $from_sep[$from_pos];
                    $diff_type[] = self::STR_DIFF_DEL;
                    $from_pos++;
                }
            }
        }

        // add the parts extra in to at the end
        while ($to_pos < count($to)) {
            $diff_part[] = $to_sep[$to_pos];
            $diff_type[] = self::STR_DIFF_ADD;
            $to_pos++;
        }


        return array(
            self::STR_DIFF_VAL => $diff_part,
            self::STR_DIFF_TYP => $diff_type);
    }

    /**
     * @param string $from the part of the target string that should be found in the $to string array
     * @param array $to the result string converted to an array
     * @param int $start_pos the staring position in the to string array
     * @return int the position of the next match in the to array or -1 if nothing found
     */
    private
    function str_diff_list_next_array_match(array $from, array $to, int $start_pos): int
    {
        $check_pos = $start_pos;
        $found_pos = -1;
        while ($check_pos < count($to) and $found_pos == -1) {
            $compare_pos = 0;
            $found = true;
            while ($compare_pos < count($from) and $found) {
                if ($check_pos + $compare_pos > count($to)) {
                    $found = false;
                } else {
                    $from_char = $from[$compare_pos];
                    $to_char = $to[$check_pos + $compare_pos];
                    if ($from[$compare_pos] != $to[$check_pos + $compare_pos]) {
                        $found = false;
                    }
                }
                $compare_pos++;
            }
            if ($found) {
                $found_pos = $check_pos;
            }
            $check_pos++;
        }
        return $found_pos;
    }

    /**
     * @param string $from the part of the target string that should be found in the $to string array
     * @param array $to the result string converted to an array
     * @param int $start_pos the staring position in the to string array
     * @return int the position of the next match in the to array or -1 if nothing found
     */
    private
    function str_diff_list_next_match(string $from, array $to, int $start_pos): int
    {
        $check_pos = $start_pos;
        $found_pos = -1;
        while ($check_pos < count($to) and $found_pos == -1) {
            if ($from == $to[$check_pos]) {
                $found_pos = $check_pos;
            }
            $check_pos++;
        }
        return $found_pos;
    }

    /**
     * array with the differences of two strings converted into arrays with "useful" parts
     *
     * @param array $from the target string converted to an array
     * @param array $to the result string converted to an array
     * @return array of array of
     *         values: a list of elements as they appear in the diff
     *           type: contains numbers. 0: unchanged, -1: removed, 1: added
     */
    private
    function str_diff_list_lcs(array $from, array $to): array
    {
        $diff_part = array(); // list with the differences
        $diff_type = array(); //

        $diffs = array();
        $len_f = count($from);
        $len_t = count($to);
        $len_min = min($len_f, $len_t);

        // Extract unchanged head and remove unchanged elements from both arrays.
        $head_part = array();
        $head_type = array();
        $first_changed = $len_min;
        for ($i = 0; $i < $len_min; $i++) {
            if ($from[$i] != $to[$i]) {
                $first_changed = $i;
                break;
            }
            $head_part[] = $from[$i];
            $head_type[] = 0;
        }
        $from = array_slice($from, $first_changed);
        $to = array_slice($to, $first_changed);
        $len_f -= $first_changed;
        $len_t -= $first_changed;
        $len_min -= $first_changed;

        // Extract unchanged tail and remove unchanged elements from both arrays.
        $tail_part = array();
        $tail_type = array();
        $first_changed = $len_min;
        for ($i = 1; $i <= $len_min; $i++) {
            if ($from[$len_f - $i] != $to[$len_t - $i]) {
                $first_changed = $i - 1;
                break;
            }
            $tail_part[] = $from[$len_f - $i];
            $tail_type[] = 0;
        }
        $tail_part = array_reverse($tail_part);

        $from = array_slice($from, 0, $len_f - $first_changed);
        $to = array_slice($to, 0, $len_t - $first_changed);
        $len_f -= $first_changed;
        $len_t -= $first_changed;
        $len_min -= $first_changed;

        // fill the LCS matrix with the no change default
        // create the column
        for ($pos_t = -1; $pos_t < $len_t; $pos_t++) $diffs[-1][$pos_t] = 0;
        // create the row
        for ($pos_f = -1; $pos_f < $len_f; $pos_f++) $diffs[$pos_f][-1] = 0;
        ksort($diffs[-1]);
        ksort($diffs);
        for ($pos_f = 0; $pos_f < $len_f; $pos_f++) {
            for ($pos_t = 0; $pos_t < $len_t; $pos_t++) {
                if ($from[$pos_f] == $to[$pos_t]) {
                    $ad = $diffs[$pos_f - 1][$pos_t - 1];
                    $diffs[$pos_f][$pos_t] = $ad + 1;
                } else {
                    $a1 = $diffs[$pos_f - 1][$pos_t];
                    $a2 = $diffs[$pos_f][$pos_t - 1];
                    $diffs[$pos_f][$pos_t] = max($a1, $a2);
                }
            }
        }

        // Traverse the diff matrix.
        $pos_f = $len_f - 1;
        $pos_t = $len_t - 1;
        while (($pos_f > -1) || ($pos_t > -1)) {
            if ($pos_t > -1) {
                if ($diffs[$pos_f][$pos_t - 1] == $diffs[$pos_f][$pos_t]) {
                    $diff_part[] = $to[$pos_t];
                    $diff_type[] = self::STR_DIFF_ADD;
                    $pos_t--;
                    continue;
                }
            }
            if ($pos_f > -1) {
                if ($diffs[$pos_f - 1][$pos_t] == $diffs[$pos_f][$pos_t]) {
                    $diff_part[] = $from[$pos_f];
                    $diff_type[] = self::STR_DIFF_DEL;
                    $pos_f--;
                    continue;
                }
            }
            {
                $diff_part[] = $from[$pos_f];
                $diff_type[] = self::STR_DIFF_UNCHANGED;
                $pos_f--;
                $pos_t--;
            }
        }

        $diff_part = array_reverse($diff_part);
        $diff_type = array_reverse($diff_type);

        $diff_part = array_merge($head_part, $diff_part, $tail_part);
        $diff_type = array_merge($head_type, $diff_type, $tail_type);

        return array(
            self::STR_DIFF_VAL => $diff_part,
            self::STR_DIFF_TYP => $diff_type);
    }

    /**
     * split a text string into best human-readable parts
     * works only for English at the moment
     *
     * @param string $text any string that could be a human-readable text or a technical list of chars
     * @param int $type to force to use a predefined split type
     * @param bool $with_sep false if the separators should not be included and ignored in the compare
     * @return array with the "useful" parts of the string
     */
    function str_split_for_humans(string $text, int $type = self::STR_TYPE_AUTO, bool $with_sep = true): array
    {
        if ($type == self::STR_TYPE_AUTO) {
            $type = $this->str_type($text);
        }
        if ($type == self::STR_TYPE_CODE) {
            $result = str_split($text);
        } elseif ($type == self::STR_TYPE_PROSA) {
            $result = preg_split("/[\s,]+/", $text);
            $result = $this->str_split_add_sep($text, $result, $with_sep);
        } elseif ($type == self::STR_TYPE_JSON) {
            $result = json_decode($text, true);
        } elseif ($type == self::STR_TYPE_HTML) {
            $result = preg_split("/[\s,<>]+/", $text);
            $result = $this->str_split_add_sep($text, $result, $with_sep);
            /*
            $dom = new DOMDocument;
            $dom->loadHTML($text);
            $result = array();
            ...
            */
        } else {
            $result = str_split($text);
        }
        return $result;
    }

    /**
     * if requested add the separators to a split string as array
     *
     * @param string $text any string that could be a human-readable text or a technical list of chars
     * @param bool $with_sep false if the separators should not be included and ignored in the compare
     * @return array with the "useful" parts of the string
     */
    private
    function str_split_add_sep(string $text, array $result, bool $with_sep = true): array
    {
        $pos = 0;
        foreach ($result as $key => $word) {
            $start = $pos;
            $end = strpos($text, $word, $pos);
            if ($start > 0 and $end > $start) {
                if ($with_sep) {
                    $sep = substr($text, $start, $end - $start);
                } else {
                    $sep = '';
                }
                $result[$key] = $sep . $word;
            }
            $pos = $end + strlen($word);
        }
        return $result;
    }

    /**
     * classify the string text
     *
     * @param string $text any text
     * @return int the enum type of text
     */
    function str_type(string $text): int
    {
        $type = self::STR_TYPE_CODE;
        if ($this->str_is_json($text)) {
            $type = self::STR_TYPE_JSON;
        } elseif ($this->str_is_html($text)) {
            $type = self::STR_TYPE_HTML;
        } else {
            $result = preg_split("/[\s,]+/", $text);
            $normal_len_in_pct = $this->str_word_len_normal(
                $result,
                self::STR_WORD_MIN_LEN,
                self::STR_WORD_MAX_LEN);
            if ($normal_len_in_pct > self::STR_WORD_MIN_LEN_NORMAL_IN_PCT) {
                $type = self::STR_TYPE_PROSA;
            }
        }
        return $type;
    }


    /**
     * @param string $text any text that is potential a json string
     * @return bool true if the string is a valid json
     */
    function str_is_json(string $text): bool
    {
        $json = json_decode($text);
        $result = json_last_error() === JSON_ERROR_NONE;
        if ($result) {
            if ($json == '') {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param string $text any text that is potential a html string
     * @return bool true if the string is a valid html
     */
    function str_is_html(string $text): bool
    {
        /*
        $dom = new DOMDocument;
        $dom->loadHTML($text);
        if ($dom->validate()) {
            return true;
        } else {
            return false;
        }
        */
        $start_maker = '<!DOCTYPE html>';
        if ($this->str_left($text, strlen($start_maker)) == $start_maker) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return the percentage of words that are within a expected length range
     *
     * @param array $word_list list of words (or any text)
     * @param int $min_len the expect minimal length of a "word"
     * @param int $max_len the expect maximal length of a "word"
     * @return float the percentage of "words" within the expected length
     */
    private
    function str_word_len_normal(
        array $word_list,
        int   $min_len,
        int   $max_len
    ): float
    {
        $short_words = 0;
        $long_words = 0;
        foreach ($word_list as $word) {
            if (strlen($word) < $min_len) {
                $short_words++;
            }
            if (strlen($word) > $max_len) {
                $long_words++;
            }
        }
        return 1 - (($short_words + $long_words) / count($word_list));
    }


    /*
     * short forms for the reflection class
     */

    /**
     * remove the namespace from the class name
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_name(string $class): string
    {
        return $this->str_right_of_or_all($class, '\\');
    }

}
