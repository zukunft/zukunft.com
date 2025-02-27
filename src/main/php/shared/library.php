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

namespace shared;

include_once SERVICE_PATH . 'config.php';

use cfg\component\view_style;
use cfg\log\change_values_geo_big;
use cfg\log\change_values_geo_norm;
use cfg\log\change_values_geo_prime;
use cfg\log\change_values_text_big;
use cfg\log\change_values_text_norm;
use cfg\log\change_values_text_prime;
use cfg\log\change_values_time_big;
use cfg\log\change_values_time_norm;
use cfg\log\change_values_time_prime;
use cfg\ref\source_type;
use cfg\sandbox\sandbox_value;
use cfg\system\session;
use cfg\system\sys_log_status;
use cfg\system\sys_log_status_list;
use cfg\system\sys_log_type;
use cfg\system\system_time;
use cfg\user\user_official_type;
use cfg\value\value;
use cfg\view\view_link_type;
use cfg\word\word_db;
use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_type;
use cfg\component\component_type;
use cfg\component\position_type;
use cfg\config;
use cfg\db\sql_db;
use cfg\db\sql_par_field_list;
use cfg\element\element;
use cfg\element\element_type;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_link_type;
use cfg\formula\formula_type;
use cfg\system\ip_range;
use cfg\system\job;
use cfg\system\job_time;
use cfg\system\job_type;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_values_big;
use cfg\log\change_field;
use cfg\log\change_link;
use cfg\log\change_values_norm;
use cfg\log\change_values_prime;
use cfg\log\change_table;
use cfg\log\change_table_field;
use cfg\log\changes_big;
use cfg\log\changes_norm;
use cfg\phrase\phrase;
use cfg\phrase\phrase_table;
use cfg\phrase\phrase_table_status;
use cfg\phrase\phrase_type;
use cfg\phrase\phrase_types;
use cfg\system\pod;
use cfg\system\pod_status;
use cfg\system\pod_type;
use cfg\ref\ref;
use cfg\ref\ref_type;
use cfg\sandbox\sandbox_named;
use cfg\ref\source;
use cfg\system\sys_log;
use cfg\system\sys_log_function;
use cfg\system\system_time_type;
use cfg\user\user;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\value\value_base;
use cfg\value\value_ts_data;
use cfg\view\view;
use cfg\view\view_term_link;
use cfg\word\word;
use DateTime;
use Exception;
use shared\types\protection_type;
use shared\types\share_type;
use shared\types\view_type;

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
     * @param string|null $datetime_text the datetime as received from the database
     * @return DateTime the converted DateTime value or now()
     */
    function get_datetime(?string $datetime_text, string $obj_name = '', string $process = ''): DateTime
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
        $result = preg_replace('/\) ;/', ');', $result);
        $result = preg_replace("/\) ';/", ")';", $result);
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
        // special case: replace system test winter time with daylight saving time
        $result = str_replace('2023-01-03T20:59:59+00:00', '2023-01-03T20:59:59+01:00', $result);
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
     * @param string $text the text from which the maker several times e.g. "cfg\formula\formula"
     * @param string $maker e.g. "\formula"
     * @return string the text without the last maker e.g. "cfg\formula"
     */
    function str_left_of_last(string $text, string $maker): string
    {
        $result = "";
        $pos = strrpos($text, $maker);
        if ($pos > 0) {
            $result = substr($text, 0, strrpos($text, $maker));
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
    function str_left_of_or_all(?string $text, ?string $maker): string
    {
        if ($text == null) {
            $text = "";
        }
        $result = $text;
        if ($maker == null) {
            $maker = "";
        }
        if ($result !== $maker) {
            while (str_contains($result, $maker)) {
                if (substr($result, strpos($result, $maker), strlen($maker)) === $maker) {
                    $result = substr($text, 0, strpos($text, $maker));
                }
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
            while (str_contains($result, $maker)) {
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

    function array_keys_r($array): array
    {
        $keys = array_keys($array);

        foreach ($array as $sub_array)
            if (is_array($sub_array))
                $keys = array_merge($keys, $this->array_keys_r($sub_array));

        return $keys;
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
     * @return array the value comma separated or "null" if the array is empty
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
     * @return string the values comma separated or "" if the array is empty
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

    function not_msg(
        string|array|null $result,
        string|array|null $target
    ): string
    {
        $msg = '';
        if ($result == $target) {
            $msg = $result . ' should not be' . $target;
        }
        return $msg;
    }


    /*
     * diff
     */

    /**
     * explains the difference between two strings or arrays
     * in a useful human-readable format
     *
     * @param string|array|null $result the actual value that should be checked
     * @param string|array|null $target the expected value to compare
     * @param bool $ignore_order true if the array can be resorted to find the matches
     * @return string an empty string if the actual value matches the expected
     */
    function diff_msg(
        string|array|null $result,
        string|array|null $target,
        bool              $ignore_order = true): string
    {
        if (is_string($target) and is_string($result)) {
            $msg = $this->str_diff_msg($result, $target);
        } elseif (is_array($target) and is_array($result)) {
            $msg = $this->array_explain_missing($result, $target);
            $msg .= $this->array_explain_missing($target, $result, self::STR_DIFF_DEL_START, self::STR_DIFF_DEL_END);
            if ($msg == '') {
                $msg = $this->array_diff_msg($result, $target, $ignore_order);
            }
        } elseif ($target == null and $result == null) {
            $msg = 'Both are null';
        } else {
            $msg = 'The type combination of ' . gettype($target) . ' and ' . gettype($result) . ' are not expected.';
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
                    // check if the diff is only the order of the items
                    $diff = $this->diff_msg($result[$key], $value);
                    if ($diff != '') {
                        $msg .= $this->str_sep($msg);
                        $msg .= 'pos  ' . $key . ': ' . $diff;
                    }
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
                if (!$result[$key] == $value) {
                    if (is_array($result[$key]) and is_array($value)) {
                        $msg .= $this->array_explain_missing($result[$key], $value);
                    } else {
                        if ($msg != '') {
                            $msg .= ', ';
                        }
                        $msg .= $value . ' is not ' . $result[$key];
                    }
                }
            }
        }
        if ($more > 0) {
            $msg .= ' ... and ' . $more . ' more';
        }
        return $msg;
    }

    /*
     * array
     */

    function key_num_sort(array $in_array): array
    {
        ksort($in_array);
        $num_array = [];
        $str_array = [];
        foreach ($in_array as $key => $item) {
            if (is_numeric($key)) {
                $num_array[$key] = $item;
            } else {
                $str_array[$key] = $item;
            }
        }
        return array_merge($num_array, $str_array);
    }

    function implode_recursive(string $sep, array $array): string
    {
        $ret = '';
        foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= $this->implode_recursive($sep, $item) . $sep;
            } else {
                $ret .= $item . $sep;
            }
        }
        return substr($ret, 0, 0 - strlen($sep));
    }

    /*
     * file
     */

    /**
     * get all file names of a folder and its subfolders as an array
     * @param string $dir the initial path of the root folder for the search
     * @return array with the folders and file names
     */
    function dir_to_array(string $dir): array
    {
        $result = [];
        $files = scandir($dir);
        foreach ($files as $file) {
            if (!in_array($file, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $result[$file] = $this->dir_to_array($dir . DIRECTORY_SEPARATOR . $file);
                } else {
                    $result[] = $file;
                }
            }
        }
        return $result;
    }

    /**
     * flat the folder and file name array
     * @param array $files with the folders and file names
     * @param string $path the path of the files
     * @return array flat array with the path of the files
     */
    function array_to_path(array $files, string $path = ''): array
    {

        $result = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $sub_path = $path . DIRECTORY_SEPARATOR . $key;
                $result = array_merge($result, $this->array_to_path($file, $sub_path));
            } else {
                $result[] = $path . DIRECTORY_SEPARATOR . $file;
            }
        }
        return $result;
    }

    function php_code_use(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'use')) {
                $class_with_path = trim($this->str_between($line, 'use', ';'));
                $class = $this->str_right_of_or_all($class_with_path, '\\');
                $path = $this->str_left_of_last($class_with_path, '\\' . $class);
                $class = $this->str_left_of_or_all($class, ' as ');
                $use = [];
                $use[] = $class;
                $use[] = $path;
                if ($class != '') {
                    $result[] = $use;
                }
            }
        }
        return $result;
    }

    /**
     * get a list of functions used in a class with the corresponding class section
     * @param array $lines
     * @return array
     */
    function php_code_function(array $lines): array
    {
        $result = [];
        $in_comment_part = false;
        $section_name = '';
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '/*') and !str_starts_with(trim($line), '/**')) {
                $in_comment_part = true;
            }
            if (str_starts_with(trim($line), '*/')) {
                $in_comment_part = false;
            }
            if ($in_comment_part) {
                if (str_starts_with(trim($line), '*')) {
                    $section_name = trim($this->str_right_of($line, '* '));
                    $in_comment_part = false;
                }
            }
            if (str_starts_with(trim($line), 'function ')) {
                $function_name = trim($this->str_between($line, 'function ', '('));
                $use = [];
                $use[] = $function_name;
                $use[] = $section_name;
                if ($function_name != '') {
                    $result[] = $use;
                }
            }
        }
        return $result;
    }

    function php_code_use_sorted(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'use')) {
                $result[] = $line;
            }
        }
        asort($result);
        return $result;
    }

    function php_code_use_converted(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'use')) {
                $class_with_path = trim($this->str_between($line, 'use', ';'));
                $class = $this->str_right_of_or_all($class_with_path, '\\');
                $path = $this->str_left_of_last($class_with_path, '\\' . $class);
                $class = $this->str_left_of_or_all($class, ' as ');
                $path_conv = $this->php_path_convert($path);
                $result[] = 'include_once ' . $path_conv . " . '" . $class . ".php';";
            }
        }
        asort($result);
        return $result;
    }

    function php_code_include(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'include_once') or str_starts_with($line, '//include_once')) {
                $class_with_path = trim($this->str_between($line, 'include_once', ';'));
                $class = $this->str_left_of_or_all($class_with_path, '.php');
                $path = trim($this->str_left_of_or_all($class, '.'));
                $class = $this->str_right_of_or_all($class, " . '");
                $include = [];
                $include[] = $class;
                $include[] = $path;
                if ($class != '') {
                    $result[] = $include;
                }
            }
        }
        return $result;
    }


    /*
     * php code
     */

    /**
     * returns the const name for the include path
     * for standard PHP libraries 'PHP' is returned
     * @param string $use_path the namespace prefix of the class
     * @return string the const name
     */
    function php_path_convert(string $use_path): string
    {
        return match ($use_path) {
            'api\result' => 'API_RESULT_PATH',
            'api\word' => 'API_WORD_PATH',
            'api\phrase' => 'API_PHRASE_PATH',
            'api\value' => 'API_VALUE_PATH',
            'api\ref' => 'API_REF_PATH',
            'api\user' => 'API_USER_PATH',
            'api\sandbox' => 'API_SANDBOX_PATH',
            'api\formula' => 'API_FORMULA_PATH',
            'api\component' => 'API_COMPONENT_PATH',
            'api\verb' => 'API_VERB_PATH',
            'api\view' => 'API_VIEW_PATH',
            'api\log' => 'API_LOG_PATH',
            'controller', 'api' => 'API_OBJECT_PATH',
            'controller\system', 'api\system' => 'API_SYSTEM_PATH',
            'cfg' => 'SERVICE_PATH',
            'cfg\db' => 'DB_PATH',
            'cfg\log' => 'MODEL_LOG_PATH',
            'cfg\const' => 'MODEL_CONST_PATH',
            'cfg\system' => 'MODEL_SYSTEM_PATH',
            'cfg\formula' => 'MODEL_FORMULA_PATH',
            'cfg\element' => 'MODEL_ELEMENT_PATH',
            'cfg\result' => 'MODEL_RESULT_PATH',
            'cfg\phrase' => 'MODEL_PHRASE_PATH',
            'cfg\sandbox' => 'MODEL_SANDBOX_PATH',
            'cfg\helper' => 'MODEL_HELPER_PATH',
            'cfg\group' => 'MODEL_GROUP_PATH',
            'cfg\user' => 'MODEL_USER_PATH',
            'cfg\word' => 'MODEL_WORD_PATH',
            'cfg\ref' => 'MODEL_REF_PATH',
            'cfg\view' => 'MODEL_VIEW_PATH',
            'cfg\value' => 'MODEL_VALUE_PATH',
            'cfg\import' => 'MODEL_IMPORT_PATH',
            'cfg\language' => 'MODEL_LANGUAGE_PATH',
            'cfg\verb' => 'MODEL_VERB_PATH',
            'cfg\component' => 'MODEL_COMPONENT_PATH',
            'cfg\component\sheet' => 'MODEL_SHEET_PATH',
            'cfg\export' => 'EXPORT_PATH',
            'html' => 'WEB_HTML_PATH',
            'html\log' => 'WEB_LOG_PATH',
            'html\user' => 'WEB_USER_PATH',
            'html\element' => 'WEB_ELEMENT_PATH',
            'html\formula' => 'WEB_FORMULA_PATH',
            'html\result' => 'WEB_RESULT_PATH',
            'html\word' => 'WEB_WORD_PATH',
            'html\figure' => 'WEB_FIGURE_PATH',
            'html\group' => 'WEB_GROUP_PATH',
            'html\phrase' => 'WEB_PHRASE_PATH',
            'html\verb' => 'WEB_VERB_PATH',
            'html\value' => 'WEB_VALUE_PATH',
            'html\ref' => 'WEB_REF_PATH',
            'html\system' => 'WEB_SYSTEM_PATH',
            'html\types' => 'WEB_TYPES_PATH',
            'html\helper' => 'WEB_HELPER_PATH',
            'html\sandbox' => 'WEB_SANDBOX_PATH',
            'html\view' => 'WEB_VIEW_PATH',
            'html\component' => 'WEB_COMPONENT_PATH',
            'html\component\sheet' => 'WEB_SHEET_PATH',
            'html\component\form' => 'WEB_FORM_PATH',
            'shared' => 'SHARED_PATH',
            'shared\calc' => 'SHARED_CALC_PATH',
            'shared\const' => 'SHARED_CONST_PATH',
            'shared\enum' => 'SHARED_ENUM_PATH',
            'shared\helper' => 'SHARED_HELPER_PATH',
            'shared\types' => 'SHARED_TYPES_PATH',
            default => 'missing path for ' . $use_path,
        };
    }

    /**
     * get the expected class section name for a function
     * @param string $fnc_name the name of the function
     * @return string name of the expected class section
     */
    function php_expected_function_section(string $fnc_name): string
    {
        $result = match ($fnc_name) {
            '__construct', 'reset', 'row_mapper_sandbox' => 'construct and map',
            'load_standard' => 'load',
            'api_json_array', 'set_by_api_json' => 'api',
            'import_obj', 'export_json' => 'im- and export',
            'db_fields_all', 'db_fields_changed' => 'sql write fields',
            'dsp_id' => 'debug',
            default => '',
        };

        if ($result == '') {
            if (str_starts_with($fnc_name, 'set_')) {
                $result = 'set and get';
            } elseif (str_starts_with($fnc_name, 'load_by_')) {
                $result = 'load';
            } elseif (str_starts_with($fnc_name, 'load_sql')
                and !(str_starts_with($fnc_name, 'load_sql_user_changes'))) {
                $result = 'load sql';
            } elseif (str_starts_with($fnc_name, 'log_')) {
                $result = 'log';
            } elseif (str_starts_with($fnc_name, 'save_')) {
                $result = 'save';
            } elseif (str_starts_with($fnc_name, 'del_')) {
                $result = 'del';
            }
        }
        return $result;
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
     * @return string the value comma separated or "null" if the array is empty
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
        return strcmp($a[json_fields::OBJECT_CLASS], $b[json_fields::OBJECT_CLASS]);
    }

    private
    static function sort_array_by_id($a, $b): int
    {
        return $a[json_fields::ID] - $b[json_fields::ID];
    }

    private
    static function sort_by_class_and_id(?array $a): ?array
    {
        if ($a != null) {
            if (count($a) > 0) {
                if (array_key_exists(0, $a)) {
                    if (is_array($a[0])) {
                        if (array_key_exists(json_fields::OBJECT_CLASS, $a[0])) {
                            usort($a, array('shared\library', 'sort_array_by_class'));
                        }
                        if (array_key_exists(json_fields::ID, $a[0])) {
                            usort($a, array('shared\library', 'sort_array_by_id'));
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
     * @param array $haystack the bigger array that is expected to contain all items from the needle
     * @param array $needle the smaller array that is expected to be part of the haystack array
     * @param string $key_name the key name to find the matching item in the haystack
     * @return array an empty array if all item and sub items from the needle are in the haystack
     */
    function array_recursive_diff(array $haystack, array $needle, string $key_name = sql_db::FLD_ID): array
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
                            $inner_haystack = $this->array_recursive_diff($haystack[$key][$haystack_key], $inner_value);
                            if (count($inner_haystack)) {
                                $result[$key] = $inner_haystack;
                            }
                        }
                    }
                    if ($haystack_key < 0) {
                        $inner_haystack = $this->array_recursive_diff($haystack[$key], $value);
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
        $diff = $this->array_recursive_diff($json_haystack_clean, $json_needle_clean);
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
                    if (is_array($diff_val[$i])) {
                        $msg .= $this->implode_recursive(',', $diff_val[$i]);
                    } else {
                        $msg .= $diff_val[$i];
                    }
                }
                if ($type != self::STR_DIFF_DEL) {
                    $diff_val_used = $diff_val[$i];
                    if (is_array($diff_val_used)) {
                        $diff_val_used = $this->dsp_array($diff_val_used);
                    }
                    $pos = $pos + strlen($diff_val_used);
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

        // get the array keys e.g. if the keys are not a number
        $from_keys = array_keys($from);
        $to_keys = array_keys($to);
        $from_pos = 0;
        $to_pos = 0;
        $to_last_match_pos = 0;

        // if to is empty from is always a diff
        if (count($to_keys) == 0) {
            if ($from_pos < count($from)) {
                $diff_part[] = $from_sep[$from_keys[$from_pos]];
                $diff_type[] = self::STR_DIFF_DEL;
                $from_pos = $from;
            }
        }

        // check if the from parts are also part of to
        while ($from_pos < count($from)) {
            if (!array_key_exists($to_pos, $to_keys)) {
                $to_pos = $to_last_match_pos;
            }
            if (!array_key_exists($from_pos, $from_keys)) {
                log_err($from_pos . ' does not exist in ' . $this->dsp_array($from_keys));
            } else {
                if (!array_key_exists($from_keys[$from_pos], $from)) {
                    log_err($from_keys[$from_pos] . ' does not exist in ' . $this->dsp_array($from));
                }
            }
            if (!array_key_exists($to_pos, $to_keys)) {
                log_err($to_pos . ' does not exist in ' . $this->dsp_array($to_keys));
            } else {
                if (!array_key_exists($to_keys[$to_pos], $to)) {
                    log_err($to_keys[$to_pos] . ' does not exist in ' . $this->dsp_array($to));
                }
            }
            if ($from[$from_keys[$from_pos]] == $to[$to_keys[$to_pos]]) {
                $diff_part[] = $to_sep[$to_keys[$to_pos]];
                $diff_type[] = self::STR_DIFF_UNCHANGED;
                if ($to_pos < count($to)) {
                    $to_last_match_pos = $to_pos;
                    $to_pos++;
                }
                $from_pos++;
            } else {
                if (is_array($from[$from_keys[$from_pos]])) {
                    $from_key = $from_keys[$from_pos];
                    $to_key = $to_keys[$to_pos];
                    $from_part = $from[$from_key];
                    $to_part = $to[$to_key];
                    $from_sep_part = $from_sep[$from_key];
                    $to_sep_part = $to_sep[$to_key];
                    if (!is_array($from_part)) {
                        $from_part = [$from_part];
                    }
                    if (!is_array($to_part)) {
                        $to_part = [$to_part];
                    }
                    if (!is_array($from_sep_part)) {
                        $from_sep_part = [$from_sep_part];
                    }
                    if (!is_array($to_sep_part)) {
                        $to_sep_part = [$to_sep_part];
                    }
                    $sub_diff = $this->str_diff_list(
                        $from_part,
                        $to_part,
                        $from_sep_part,
                        $to_sep_part,
                        $str_type
                    );
                    array_walk_recursive($sub_diff[self::STR_DIFF_VAL], function ($diff, $key) use (&$diff_part) {
                        $diff_part[$key] = $diff;
                    });
                    array_walk_recursive($sub_diff[self::STR_DIFF_TYP], function ($type, $key) use (&$diff_type) {
                        $diff_type[$key] = $type;
                    });
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
                        $match_pos = $this->str_diff_list_next_match($from[$from_keys[$from_pos]], $to, $to_keys, $to_pos);
                    }
                    if ($match_pos > $to_pos) {
                        for ($add_pos = $to_pos; $add_pos < $match_pos; $add_pos++) {
                            $diff_part[] = $to_sep[$to_keys[$add_pos]];
                            $diff_type[] = self::STR_DIFF_ADD;
                        }
                        $to_pos = $add_pos;
                    } elseif ($match_pos == -1) {
                        $diff_part[] = $from_sep[$from_keys[$from_pos]];
                        $diff_type[] = self::STR_DIFF_DEL;
                        $from_pos++;
                    }
                }
            }
        }

        // add the parts extra in to at the end
        while ($to_pos < count($to)) {
            $diff_part[] = $to_sep[$to_keys[$to_pos]];
            $diff_type[] = self::STR_DIFF_ADD;
            $to_pos++;
        }


        return array(
            self::STR_DIFF_VAL => $diff_part,
            self::STR_DIFF_TYP => $diff_type);
    }

    /**
     * @param array $from the part of the target string that should be found in the $to string array
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
                if ($check_pos + $compare_pos >= count($to)) {
                    $found = false;
                } else {
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
     * @param string|null $from the part of the target string that should be found in the $to string array
     * @param array $to the result string converted to an array
     * @param array $to_keys the array keys to the to array for the case they are not integer
     * @param int $start_pos the staring position in the to string array
     * @return int the position of the next match in the to array or -1 if nothing found
     */
    private
    function str_diff_list_next_match(string|null $from, array $to, array $to_keys, int $start_pos): int
    {
        $check_pos = $start_pos;
        $found_pos = -1;
        if ($from != null) {
            while ($check_pos < count($to) and $found_pos == -1) {
                if ($from == $to[$to_keys[$check_pos]]) {
                    $found_pos = $check_pos;
                }
                $check_pos++;
            }
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
                // avoid handling a simple string with high-quotes as a json
            } elseif (!(str_contains($text, '[')
                or str_contains($text, '{'))) {
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
     * same as class_to_name but without backend exceptions
     * TODO make it static
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_name_pur(string $class): string
    {
        return $this->str_right_of_or_all($class, '\\');
    }

    /**
     * remove the namespace from the class name
     * TODO avoid these exception
     *
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_name(string $class): string
    {
        $result = $this->str_right_of_or_all($class, '\\');
        // for some lists and exceptions
        switch ($class) {
            case phrase_types::class;
                $result = str_replace('_types', '_type', $result);
                break;
            case sys_log_status_list::class;
                $result = str_replace('_list', '', $result);
                break;
        }
        return $result;
    }

    /**
     * get the fixed api name of an object class
     * to allow changing the internal object name without changing the api
     *
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_api_name(string $class): string
    {
        // activate to add api exceptions
        /*
        switch ($class) {
            case phrase_types::class;
                $class = json_fields::CLASS_PHRASE_TYPE;
                break;
            case sys_log_status_list::class;
                $class = json_fields::CLASS_LOG_STATUS;
                break;
        }
        */
        return $this->class_to_name_pur($class);
    }

    /**
     * get the object class name from the fixed api name
     * to allow changing the internal object name without changing the api
     *
     * @param string $class_name the fixed class name without the namespace as used in the api
     * @return string class name without the namespace
     */
    function api_name_to_class(string $class_name): string
    {
        $result = 'api class name match missing';
        $i = 0;
        $found = false;
        while ($i < count(API_CLASSES) and !$found) {
            $class = API_CLASSES[$i];
            $api_name = $this->class_to_api_name($class);
            if ($api_name == $class_name) {
                $result = $class;
                $found = true;
            }
            $i++;
        }
        return $result;
    }

    /**
     * remove the namespace from the class name and adds the name extension for the table
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_table(string $class): string
    {
        $result = $this->class_to_name($class);
        // for some lists and exceptions
        if ($class != changes_norm::class
            and $class != changes_big::class
            and $class != change_values_prime::class
            and $class != change_values_norm::class
            and $class != change_values_big::class
            and $class != sys_log_status::class) {
            $result .= sql_db::TABLE_EXTENSION;
        }
        // TODO remove these exception
        if ($result == 'values_primes') {
            $result = 'values_prime';
        }
        if ($result == 'values_bigs') {
            $result = 'values_big';
        }
        if ($result == 'values_norms') {
            $result = 'values_norm';
        }
        if ($result == 'value_times') {
            $result = 'values_time';
        }
        if ($result == 'value_texts') {
            $result = 'values_text';
        }
        if ($result == 'value_geos') {
            $result = 'values_geo';
        }
        return $result;
    }

    /**
     * the folder where a class can be found
     * @param string $class including the namespace
     * @return string class name without the namespace
     */
    function class_to_path(string $class): string
    {
        $result = $this->class_to_name($class);
        switch ($result) {
            case $this->class_to_name(config::class):
            case $this->class_to_name(ip_range::class):
            case $this->class_to_name(session::class):
                $result = 'system';
                break;
            case $this->class_to_name(system_time_type::class):
            case $this->class_to_name(system_time::class):
                $result = $this->class_to_name(sys_log::class);
                break;
            case $this->class_to_name(job_time::class):
                $result = $this->class_to_name(job::class);
                break;
            case $this->class_to_name(change_action::class):
            case $this->class_to_name(change_table::class):
            case $this->class_to_name(change_field::class):
            case $this->class_to_name(change::class):
            case $this->class_to_name(changes_norm::class):
            case $this->class_to_name(changes_big::class):
            case $this->class_to_name(change_values_prime::class):
            case $this->class_to_name(change_values_norm::class):
            case $this->class_to_name(change_values_big::class):
            case $this->class_to_name(change_values_time_prime::class):
            case $this->class_to_name(change_values_time_norm::class):
            case $this->class_to_name(change_values_time_big::class):
            case $this->class_to_name(change_values_text_prime::class):
            case $this->class_to_name(change_values_text_norm::class):
            case $this->class_to_name(change_values_text_big::class):
            case $this->class_to_name(change_values_geo_prime::class):
            case $this->class_to_name(change_values_geo_norm::class):
            case $this->class_to_name(change_values_geo_big::class):
            case $this->class_to_name(change_link::class):
            case $this->class_to_name(change_table_field::class):
                $result = 'log';
                break;
            case $this->class_to_name(pod_type::class):
            case $this->class_to_name(pod_status::class):
                $result = $this->class_to_name(pod::class);
                break;
            case $this->class_to_name(phrase_table_status::class):
            case $this->class_to_name(phrase_table::class):
                $result = $this->class_to_name(phrase::class);
                break;
            case $this->class_to_name(language_form::class):
                $result = $this->class_to_name(language::class);
                break;
            case $this->class_to_name(value_ts_data::class):
                $result = $this->class_to_name(value::class);
                break;
            case $this->class_to_name(source::class):
                $result = $this->class_to_name(ref::class);
                break;
            case $this->class_to_name(element_type::class):
                $result = $this->class_to_name(element::class);
                break;
            case $this->class_to_name(formula_link_type::class):
            case $this->class_to_name(formula_link::class):
                $result = $this->class_to_name(formula::class);
                break;
            case $this->class_to_name(view_term_link::class):
                $result = $this->class_to_name(view::class);
                break;
            case $this->class_to_name(component_link_type::class):
            case $this->class_to_name(position_type::class):
            case $this->class_to_name(component_type::class):
            case $this->class_to_name(component_link::class):
                $result = $this->class_to_name(component::class);
                break;
            case $this->class_to_name(sys_log_type::class):
            case $this->class_to_name(sys_log_status::class):
            case $this->class_to_name(sys_log_function::class):
            case $this->class_to_name(job_type::class):
            case $this->class_to_name(user_type::class):
            case $this->class_to_name(user_profile::class):
            case $this->class_to_name(user_official_type::class):
            case $this->class_to_name(protection_type::class):
            case $this->class_to_name(share_type::class):
            case $this->class_to_name(phrase_type::class):
            case $this->class_to_name(source_type::class):
            case $this->class_to_name(ref_type::class):
            case $this->class_to_name(formula_type::class):
            case $this->class_to_name(view_type::class):
            case $this->class_to_name(view_style::class):
            case $this->class_to_name(view_link_type::class):
                $result = 'type';
                break;
        }
        return $result;
    }

    /**
     * create a short string that indicates, which fields of all fields have been changed
     * TODO combine with the sql_name_shorten function
     *
     * @param sql_par_field_list $fvt_lst list of fields that have been changed
     * @param array $fld_lst_all list of all fields of the given object
     * @return string the query name extension to make the query name
     */
    function sql_field_ext(sql_par_field_list $fvt_lst, array $fld_lst_all): string
    {
        $result = '';
        foreach ($fld_lst_all as $fld) {
            if (in_array($fld, $fvt_lst->names())) {
                $fvt = $fvt_lst->get($fld);
                if ($fvt->id == null) {
                    if ($fvt->old_id == null) {
                        if ($fvt->old == null) {
                            $result .= '1';
                        } else {
                            $result .= '2';
                        }
                    } else {
                        if ($fvt->old == null) {
                            $result .= '3';
                        } else {
                            $result .= '4';
                        }
                    }
                } else {
                    if ($fvt->old_id == null) {
                        if ($fvt->old == null) {
                            $result .= '5';
                        } else {
                            $result .= '6';
                        }
                    } else {
                        if ($fvt->old == null) {
                            $result .= '7';
                        } else {
                            $result .= '8';
                        }
                    }
                }
            } else {
                $result .= '0';
            }
        }
        return $result;
    }

    /*
     * shorten a list of fields for sql query naming
     */

    /**
     * shorten the sql names e.g. because the name length of queries is limited
     * @param array $sql_names with long sql names
     * @return array with short sql names
     */
    function sql_name_shorten(array $sql_names): array
    {
        $result = [];
        foreach ($sql_names as $name) {
            $result[] = match ($name) {
                word_db::FLD_NAME => 'wrd',
                sandbox_named::FLD_DESCRIPTION => 'des',
                phrase::FLD_TYPE => 'pty',
                value_base::FLD_ID => 'grp',
                user::FLD_ID => 'usr',
                source::FLD_ID => 'src',
                sandbox_value::FLD_VALUE => 'val',
                sandbox_value::FLD_LAST_UPDATE => 'upd',
                phrase::FLD_ID . '_1' => '',
                phrase::FLD_ID . '_2' => '',
                phrase::FLD_ID . '_3' => '',
                phrase::FLD_ID . '_4' => '',
                default => $name
            };
        }
        return $result;
    }

}
