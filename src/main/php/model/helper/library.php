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
     * format functions
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
     * short forms for the reflection class
     */

    function base_class_name(string $class_name): string
    {
        return $this->str_right_of($class_name,'\\');
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

}
