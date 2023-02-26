<?php

/*

    test_lib.php - TESTing the general zukunft.com Library functions
    ------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

global $db_con;


class string_unit_tests
{
    function run(testing $t): void
    {

        $t->header('Test the zukunft.com base functions (model/helper/library.php)');

        $t->subheader('strings');

        $lib = new library();

        // test trim (remove also double spaces)
        $text = "  This  text  has  many  spaces  ";
        $target = "This text has many spaces";
        $result = $lib->trim($text);
        $t->assert("trim", $result, $target);

        // test trim all spaces
        $text = "  This Text Has Spaces  ";
        $target = "ThisTextHasSpaces";
        $result = $lib->trim_all_spaces($text);
        $t->assert("trim_all_spaces", $result, $target);

        // test trim line feed and spaces
        $text = "field1, field2 FROM table_name;";
        $target = "field1,field2 FROM table_name;";
        $result = $lib->trim_sql($text);
        $t->assert("trim_all_spaces", $result, $target);

        // test str_left
        $text = "This are the left 4";
        $pos = 4;
        $target = "This";
        $result = $lib->str_left($text, $pos);
        $t->assert(", str_left: What are the left \"" . $pos . "\" chars of \"" . $text . "\"", $result, $target);

        // test str_right
        $text = "This are the right 7";
        $pos = 7;
        $target = "right 7";
        $result = $lib->str_right($text, $pos);
        $t->assert(", str_right: What are the right \"" . $pos . "\" chars of \"" . $text . "\"", $result, $target);

        // test str_left_of
        $text = "This is left of that ";
        $maker = " of that";
        $target = "This is left";
        $result = $lib->str_left_of($text, $maker);
        $t->assert(", str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_left_of
        $text = "This is left of that, but not of that";
        $result = $lib->str_left_of($text, $maker);
        $t->assert(", str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "That is right of this";
        $maker = "That is right ";
        $target = "of this";
        $result = $lib->str_right_of($text, $maker);
        $t->assert(", str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "00000";
        $maker = "0";
        $target = "0000";
        $result = $lib->str_right_of($text, $maker);
        $t->assert(", str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "The formula id of {f23}.";
        $maker = "{f";
        $target = "23}.";
        $result = $lib->str_right_of($text, $maker);
        $t->assert(", str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_between
        $maker_start = "{f";
        $maker_end = "}";
        $target = "23";
        $result = $lib->str_between($text, $maker_start, $maker_end);
        $t->assert(", str_between: " . $text, $result, $target);

        // test str_between
        $text = "The formula id of {f4} / {f5}.";
        $target = "4";
        $result = $lib->str_between($text, $maker_start, $maker_end);
        $t->assert(", str_between: " . $text, $result, $target);

        $t->subheader('arrays and lists');

        // test dsp_array
        $test_array = [1, 2, 3];
        $target = '1,2,3';
        $result = dsp_array($test_array);
        $t->assert(", dsp_array: ", $result, $target);

        $test_array = ["A", "B", "C"];
        $target = 'A,B,C';
        $result = dsp_array($test_array);
        $t->assert(", dsp_array: ", $result, $target);

        $test_array = [];
        $target = 'null';
        $result = dsp_array($test_array);
        $t->assert(", dsp_array: ", $result, $target);

        $test_array = null;
        $result = dsp_array($test_array);
        $t->assert(", dsp_array: ", $result, $target);


        $t->subheader('json');

        // test json_clean
        $json_text = '{
  "remove empty field": "",
  "remove empty array": [],
  "keep array": [
    {
      "remove empty start field": "",
      "keep middle field": "with value",
      "remove empty end field": ""
    }
  ],
  "keep non empty field": "with value"
}';
        $json_target = '{
  "keep array": [
    {
      "keep middle field": "with value"
    }
  ],
  "keep non empty field": "with value"
}';
        $json_check = '{
  "keep array": [
    {
      "remove empty start field": "",
      "keep middle field": "with value"
    }
  ],
  "keep non empty field": "with value"
}';
        $json_needle = '{
  "text field": "text value",
  "number field": 2,
  "array": [
    {
      "id": 1,
      "text field": "text value"
    }
  ],
  "footer field": "footer value"
}';
        $json_needle_without_array = '{
  "text field": "text value",
  "number field": 2,
  "footer field": "footer value"
}';
        $json_haystack = '{
  "text field": "text value",
  "number field": 2,
  "array": [
    {
      "id": 0,
      "text field": "additional ignored text value"
    },
    {
      "id": 1,
      "text field": "text value"
    },
    {
      "id": 2,
      "text field": "additional ignored text value"
    }
  ],
  "footer field": "footer value"
}';
        $json_haystack_with_diff = '{
  "text field": "text value",
  "number field": 2,
  "array": [
    {
      "id": 0,
      "text field": "additional ignored text value"
    },
    {
      "id": 1,
      "text field": "diff text value"
    },
    {
      "id": 2,
      "text field": "additional ignored text value"
    }
  ],
  "footer field": "footer value"
}';
        $json_haystack_without_match = '{
  "text field": "text value",
  "number field": 2,
  "array": [
    {
      "id": 0,
      "text field": "additional ignored text value"
    },
    {
      "id": 2,
      "text field": "additional ignored text value"
    }
  ],
  "footer field": "footer value"
}';
        $json_haystack_without_array = '{
  "text field": "text value",
  "number field": 2,
  "footer field": "footer value"
}';
        $json_array = json_decode($json_text, true);
        $json_clean = json_clean($json_array);
        $result = $json_clean == json_decode($json_target, true);
        $t->assert(", json_clean", $result, true);

        // ... plausibility check
        $result = $json_clean == json_decode($json_check, true);
        $t->assert(", json_clean - false test", $result, false);

        // recursive count
        $result = count_recursive($json_array, 0);
        $t->assert(", count_recursive - count level 0", $result, 0);
        $result = count_recursive($json_array, 1);
        $t->assert(", count_recursive - count level 0", $result, 4);
        $result = count_recursive($json_array, 2);
        $t->assert(", count_recursive - count level 0", $result, 8);
        $result = count_recursive($json_array, 20);
        $t->assert(", count_recursive - count level 0", $result, 8);

        // recursive diff
        $result = json_encode(array_recursive_diff(
            json_decode($json_needle, true),
            json_decode($json_haystack, true)));
        $t->assert(", array_recursive_diff - contains", $result, '[]');
        $result = json_encode(array_recursive_diff(
            json_decode($json_needle_without_array, true),
            json_decode($json_haystack, true)));
        $t->assert(", array_recursive_diff - contains without array", $result, '[]');
        $result = json_encode(array_recursive_diff(
            json_decode($json_needle, true),
            json_decode($json_haystack_with_diff, true)));
        $expected = '{"array":{"text field":"text value"}}';
        $t->assert(", array_recursive_diff - diff expected", $result, $expected);
        $result = json_encode(array_recursive_diff(
            json_decode($json_needle, true),
            json_decode($json_haystack_without_match, true)));
        $expected = '{"array":{"id":1,"text field":"text value","0":{"id":1,"text field":"text value"}}}';
        $t->assert(", array_recursive_diff - without match", $result, $expected);
        $result = json_encode(array_recursive_diff(
            json_decode($json_needle, true),
            json_decode($json_haystack_without_array, true)));
        $expected = '{"array":[{"id":1,"text field":"text value"}]}';
        $t->assert(", array_recursive_diff - without array", $result, $expected);


        $t->subheader('json remove volatile fields');

        // remove timestamp from main json
        $path = 'unit/json/';
        $json_with_timestamp = $t->file($path . 'json_with_timestamp.json');
        $json_without_timestamp = $t->file($path . 'json_without_timestamp.json');
        $result = $t->json_remove_volatile(json_decode($json_with_timestamp, true));
        $target = json_decode($json_without_timestamp, true);
        $t->assert("json remove volatile timestamp", $result, $target);

        // remove timestamp from sub json
        $json_with_timestamp_in_array = $t->file($path . 'json_with_timestamp_in_array.json');
        $json_without_timestamp_in_array = $t->file($path . 'json_without_timestamp_in_array.json');
        $result = $t->json_remove_volatile(json_decode($json_with_timestamp_in_array, true));
        $target = json_decode($json_without_timestamp_in_array, true);
        $t->assert("json remove volatile timestamp in a sub array", $result, $target);

        // remove timestamp from array in sub json
        $json_with_timestamp_in_array = $t->file($path . 'json_with_timestamp_in_array_array.json');
        $json_without_timestamp_in_array = $t->file($path . 'json_without_timestamp_in_array_array.json');
        $result = $t->json_remove_volatile(json_decode($json_with_timestamp_in_array, true));
        $target = json_decode($json_without_timestamp_in_array, true);
        $t->assert("json remove volatile timestamp in a array of a sub array", $result, $target);

        // remove id from json
        $json_with_id_in_array = $t->file($path . 'json_with_id.json');
        $json_without_id_in_array = $t->file($path . 'json_without_id.json');
        $result = $t->json_remove_volatile(json_decode($json_with_id_in_array, true), true);
        $target = json_decode($json_without_id_in_array, true);
        $t->assert("json remove volatile id", $result, $target);

        // remove id from array in sub json
        $json_with_id_in_array = $t->file($path . 'json_with_id_in_array_array.json');
        $json_without_id_in_array = $t->file($path . 'json_without_id_in_array_array.json');
        $result = $t->json_remove_volatile(json_decode($json_with_id_in_array, true), true);
        $target = json_decode($json_without_id_in_array, true);
        $t->assert("json remove volatile id in a array of a sub array", $result, $target);

        // replace username from array in sub json
        $json_with_id_in_array = $t->file($path . 'json_with_username_local_in_array_array.json');
        $json_without_id_in_array = $t->file($path . 'json_with_username_test_in_array_array.json');
        $result = $t->json_remove_volatile(json_decode($json_with_id_in_array, true), true);
        $target = json_decode($json_without_id_in_array, true);
        $t->assert("json remove volatile id in a array of a sub array", $result, $target);

        // replace user id from array in sub json
        $json_with_id_in_array = $t->file($path . 'json_with_user_id_local_in_array_array.json');
        $json_without_id_in_array = $t->file($path . 'json_with_user_id_test_in_array_array.json');
        $result = $t->json_remove_volatile(json_decode($json_with_id_in_array, true), true);
        $target = json_decode($json_without_id_in_array, true);
        $t->assert("json remove volatile id in a array of a sub array", $result, $target);


        $t->subheader('user message tests');

        $msg = new user_message();
        $t->assert("user_message - default ok", $msg->is_ok(), true);

        $msg = new user_message('first message text');
        $t->assert("construct with message", $msg->get_message(), 'first message text');
        $t->assert("if a message text is given, the result is by default NOT ok", $msg->is_ok(), false);

        $msg->add_message('second message text');
        $t->assert("after adding a message the first message stays the same", $msg->get_message(), 'first message text');
        $t->assert("... and the second message can be shown", $msg->get_message(2), 'second message text');
        $t->assert("... which is also the last message", $msg->get_last_message(), 'second message text');
        $t->assert("a too high position simply returns an empty message", $msg->get_message(3), '');

        $msg_2 = new user_message();
        $msg_2->add_message('');
        $t->assert("adding an empty test does not change the status", $msg_2->is_ok(), true);
        $msg_2->add_message('error text');
        $t->assert("but adding an error text does", $msg_2->is_ok(), false);

        $msg->add($msg_2);
        $t->assert("last message of the combined message should be from msg_2", $msg->get_last_message(), 'error text');
    }

}