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

namespace model;

use api\combine_object_api;
use controller\controller;
use DateTime;
use Exception;

class library
{

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

    function str_left(string $text, string $pos): string
    {
        return substr($text, 0, $pos);
    }

    function str_right(string $text, string $pos): string
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
                    $result = implode(',', $keys);
                }
            } else {
                $left = array_slice($keys, 0, 3);
                $result = implode(',', $lib->array_flat($left));
                $result .= ',...,' . end($keys);
            }
        }
        return $result;
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

    private static function sort_array_by_class($a, $b): int
    {
        return strcmp($a[combine_object_api::FLD_CLASS], $b[combine_object_api::FLD_CLASS]);
    }

    private static function sort_array_by_id($a, $b): int
    {
        return $a[controller::API_FLD_ID] - $b[controller::API_FLD_ID];
    }

    private static function sort_by_class_and_id(?array $a): ?array
    {
        if ($a != null) {
            if (count($a) > 0) {
                if (array_key_exists(0, $a)) {
                    if (is_array($a[0])) {
                        if (array_key_exists(combine_object_api::FLD_CLASS, $a[0])) {
                            usort($a, array('model\library', 'sort_array_by_class'));
                        }
                        if (array_key_exists(controller::API_FLD_ID, $a[0])) {
                            usort($a, array('model\library', 'sort_array_by_id'));
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
    function array_recursive_diff(array $needle, array $haystack, string $key_name = 'id'): array
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


    /*
     * testing support
     */

    /**
     * highlight the first difference between two string
     * @param string|null $from the expected text
     * @param string|null $to the text to compare
     * @return string the first char that differs or an empty string
     */
    function str_diff(?string $from, ?string $to): string
    {
        $result = '';

        if ($from != null and $to != null) {
            if ($from != $to) {
                $f = str_split($from);
                $t = str_split($to);

                // add message if just one string is shorter
                if (count($f) < count($t)) {
                    $result = 'pos ' . count($t) . ' less: ' . substr($to, count($f), count($t) - count($f));
                } elseif (count($t) < count($f)) {
                    $result = 'pos ' . count($f) . ' additional: ' . substr($from, count($t), count($f) - count($t));
                }

                $i = 0;
                while ($i < count($f) and $i < count($t) and $result == '') {
                    if ($f[$i] != $t[$i]) {
                        $result = 'pos ' . $i . ': ' . $f[$i] . ' (' . ord($f[$i]) . ') != ' . $t[$i] . ' (' . ord($t[$i]) . ')';
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
