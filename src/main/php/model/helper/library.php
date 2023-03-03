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
        array $in_array,
        string $start = '',
        string $end = '',
        bool $sql_format = false): string
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
     * short forms for the reflection class
     */

    function base_class_name(string $class_name): string
    {
        return $this->str_right_of($class_name, '\\');
    }

}
