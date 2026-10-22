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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_par_field_list.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::REF . 'ref.php';
include_once test_paths::CONST . 'files.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use DateTime;
use DateTimeInterface;
use Zukunft\ZukunftCom\main\php\shared\const\def as def_shared;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\group_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_api;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

global $db_con;

class lib_tests
{
    function run(all_tests $t): void
    {
        global $debug;
        global $sys;
        $lib = new library();

        // start the test section (ts)
        $ts = 'unit lib ';
        $t->header($ts);

        $t->subheader($ts . 'convert');

        // db date text to php datetime object
        $test_name = 'get_datetime of a db date text';
        $date_text = "2023-03-03 09:32:50.980518";
        $target_summer = '2023-03-03T09:32:50+01:00';
        $target_winter = '2023-03-03T09:32:50+00:00';
        $dt = $lib->get_datetime($date_text);
        $result = $dt->format(DateTimeInterface::ATOM);
        $t->assert_contains($test_name, array($target_summer, $target_winter), $result);

        // negative: an empty db date text falls back to now
        $test_name = 'get_datetime of an empty text is now';
        $before = time() - 1;
        $dt = $lib->get_datetime('');
        $result = $dt->getTimestamp();
        $t->assert_greater($test_name, $before, $result);

        // the db tiny int 1 is true
        $test_name = 'get_bool of 1';
        $result = $lib->get_bool(1);
        $t->assert_true($test_name, $result);

        // negative: a potential db bool value null is false
        $test_name = 'get_bool of null';
        $result = $lib->get_bool(null);
        $t->assert_false($test_name, $result);

        // a time in the url has no raw colon or plus
        $test_name = 'time_to_url encodes the offset';
        $dt = new DateTime('2023-03-03T09:32:50+01:00');
        $result = $lib->time_to_url($dt);
        $t->assert($test_name, $result, '2023-03-03T09%3A32%3A50%2B01%3A00');

        // negative: the colon of the time is never part of the url text
        $test_name = 'time_to_url has no colon';
        $dt = new DateTime('2023-03-03T09:32:50+00:00');
        $result = $lib->time_to_url($dt);
        $t->assert_text_not_contains($test_name, $result, ':');

        // a word id is odd and positive in the term id
        $test_name = 'term_id of a word';
        $result = $lib->term_id(2, word::class);
        $t->assert($test_name, $result, 3);

        // negative: a triple id is odd and negative in the term id
        $test_name = 'term_id of a triple';
        $result = $lib->term_id(2, triple::class);
        $t->assert($test_name, $result, -3);

        // a formula id is even and positive in the term id
        $test_name = 'term_id of a formula';
        $result = $lib->term_id(2, formula::class);
        $t->assert($test_name, $result, 4);

        // negative: a verb id is even and negative in the term id
        $test_name = 'term_id of a verb';
        $result = $lib->term_id(2, verb::class);
        $t->assert($test_name, $result, -4);

        // a value class is a value
        $test_name = 'is_value of a value';
        $result = $lib->is_value(value::class);
        $t->assert_true($test_name, $result);

        // negative: a word class is not a value
        $test_name = 'is_value of a word';
        $result = $lib->is_value(word::class);
        $t->assert_false($test_name, $result);

        // a higher minor version is newer
        $test_name = 'prg_version_is_newer of a higher minor version';
        $result = $lib->prg_version_is_newer('0.1.0', '0.0.9');
        $t->assert_true($test_name, $result);

        // negative: a missing build number is the same version as build number zero
        $test_name = 'prg_version_is_newer of the same version';
        $result = $lib->prg_version_is_newer('0.0.3.0', '0.0.3');
        $t->assert_false($test_name, $result);

        // the ids are numbered in the url by their position
        $test_name = 'ids_to_url';
        $result = $lib->ids_to_url([5, 6], 'phr');
        $t->assert($test_name, $result, '&phr1=5&phr2=6');

        // negative: an empty id is skipped but keeps its position number
        $test_name = 'ids_to_url skips empty ids';
        $result = $lib->ids_to_url([null, '', 7], 'phr');
        $t->assert($test_name, $result, '&phr3=7');

        // the positive ids are kept
        $test_name = 'ids_not_empty keeps the positive ids';
        $result = $lib->ids_not_empty([3, 4]);
        $t->assert($test_name, $result, [3, 4]);

        // negative: a zero, null or negative id is removed
        $test_name = 'ids_not_empty removes zero, null and negative ids';
        $result = $lib->ids_not_empty([0, null, -2]);
        $t->assert($test_name, $result, []);

        // the array entries are comma separated within the start and end text
        $test_name = 'sql_array with start and end';
        $result = $lib->sql_array(['a', 'b'], '(', ')');
        $t->assert($test_name, $result, '(a,b)');

        // negative: an empty array returns an empty text without start and end
        $test_name = 'sql_array of an empty array';
        $result = $lib->sql_array([], '(', ')');
        $t->assert($test_name, $result, '');

        // a table listed in library::SINGULAR_TABLES keeps the singular class name
        $test_name = 'the user log table keeps the singular name';
        $t->assert($test_name, $lib->singular_table('user_logs'), 'user_log');
        $test_name = 'the class of the user log has the singular table name';
        $t->assert($test_name, $lib->class_to_table(user_log::class), 'user_log');
        $test_name = 'a table not listed keeps the plural name';
        $t->assert($test_name, $lib->singular_table('words'), 'words');
        $test_name = 'a listed name without the plural extension is not changed';
        $t->assert($test_name, $lib->singular_table('user_log'), 'user_log');


        $t->subheader($ts . 'strings');

        // test trim (remove also double spaces)
        $text = "  This  text  has  many  spaces  ";
        $target = "This text has many spaces";
        $result = $lib->trim($text);
        $t->assert("trim", $result, $target);

        // test trim all spaces
        $test_name = 'trim_all_spaces';
        $text = "  This Text Has Spaces  ";
        $target = "ThisTextHasSpaces";
        $result = $lib->trim_all_spaces($text);
        $t->assert($test_name, $result, $target);

        // negative: a text of only spaces and line feeds becomes empty
        $test_name = 'trim_all_spaces of only spaces';
        $result = $lib->trim_all_spaces(" \n  \t ");
        $t->assert($test_name, $result, '');

        // a line feed becomes a single space
        $test_name = 'trim_lines';
        $result = $lib->trim_lines("line one\n\rline  two\n");
        $t->assert($test_name, $result, 'line one line two');

        // negative: a text of only line feeds becomes empty
        $test_name = 'trim_lines of only line feeds';
        $result = $lib->trim_lines("\r\n\n");
        $t->assert($test_name, $result, '');

        // the char to escape gets the escape char in front
        $test_name = 'escape';
        $result = $lib->escape('say "hi"', '"', '\\');
        $t->assert($test_name, $result, 'say \"hi\"');

        // negative: a text without the char to escape is not changed
        $test_name = 'escape without the char';
        $result = $lib->escape('say hi', '"', '\\');
        $t->assert($test_name, $result, 'say hi');

        // the quotes around a text are removed
        $test_name = 'unquote';
        $result = $lib->unquote('"hi"');
        $t->assert($test_name, $result, 'hi');

        // negative: a quote inside the text is kept
        $test_name = 'unquote keeps the inner quote';
        $result = $lib->unquote("it's");
        $t->assert($test_name, $result, "it's");

        // a json object is a json
        $test_name = 'str_is_json of an object';
        $result = $lib->str_is_json('{"name": "math"}');
        $t->assert_true($test_name, $result);

        // negative: a quoted text is a valid json, but not treated as one
        $test_name = 'str_is_json of a quoted text';
        $result = $lib->str_is_json('"math"');
        $t->assert_false($test_name, $result);

        // a html page starts with the doctype
        $test_name = 'str_is_html of a page';
        $result = $lib->str_is_html('<!DOCTYPE html><html lang="en"></html>');
        $t->assert_true($test_name, $result);

        // negative: a html fragment without doctype is not a html page
        $test_name = 'str_is_html of a fragment';
        $result = $lib->str_is_html('<html lang="en"></html>');
        $t->assert_false($test_name, $result);

        // a json text is detected as json
        $test_name = 'str_type of a json';
        $result = $lib->str_type('{"name": "math"}');
        $t->assert($test_name, $result, library::STR_TYPE_JSON);

        // a html page is detected as html
        $test_name = 'str_type of a html page';
        $result = $lib->str_type('<!DOCTYPE html><html lang="en"></html>');
        $t->assert($test_name, $result, library::STR_TYPE_HTML);

        // a text of normal word length is detected as human-readable text
        $test_name = 'str_type of a sentence';
        $result = $lib->str_type('the user reads this short text');
        $t->assert($test_name, $result, library::STR_TYPE_PROSA);

        // negative: a list of single chars is code
        $test_name = 'str_type of single chars';
        $result = $lib->str_type('a, b, c');
        $t->assert($test_name, $result, library::STR_TYPE_CODE);

        // a long sql field name is shortened for the query name
        $test_name = 'sql_name_shorten';
        $result = $lib->sql_name_shorten([word_fields::FLD_NAME, fields::FLD_DESCRIPTION]);
        $t->assert($test_name, $result, ['wrd', 'des']);

        // negative: a field name without short form is kept
        $test_name = 'sql_name_shorten keeps an unknown name';
        $result = $lib->sql_name_shorten(['not_shortened']);
        $t->assert($test_name, $result, ['not_shortened']);

        // each field gets the char of its change type and 0 if it is not changed
        $test_name = 'sql_field_ext';
        $msg = new user_message();
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->add_field(word_fields::FLD_NAME, 'math');
        $fvt_lst->add_field(fields::FLD_DESCRIPTION, 'new', null, 'old');
        $fld_lst = [word_fields::FLD_NAME, fields::FLD_DESCRIPTION, fields::FLD_LAST_UPDATE];
        $result = $lib->sql_field_ext($fvt_lst, $fld_lst, $msg);
        $t->assert($test_name, $result, '120');

        // negative: without any changed field every field is 0
        $test_name = 'sql_field_ext without changes';
        $msg = new user_message();
        $fvt_lst = new sql_par_field_list();
        $result = $lib->sql_field_ext($fvt_lst, [word_fields::FLD_NAME], $msg);
        $t->assert($test_name, $result, '0');

        // the var of a message is replaced by its value
        $test_name = 'msg_var_text';
        $msg_txt = 'the word ' . msg_id::VAR_START . msg_id::VAR_NAME . msg_id::VAR_END;
        $result = $lib->msg_var_text([[$msg_txt, [msg_id::VAR_NAME => 'math']]]);
        $t->assert($test_name, $result, 'the word math');

        // negative: an escaped var is not replaced, but shown as a var
        $test_name = 'msg_var_text keeps an escaped var';
        $msg_txt = msg_id::VAR_ESC_START . msg_id::VAR_NAME . msg_id::VAR_ESC_END;
        $target = msg_id::VAR_START . msg_id::VAR_NAME . msg_id::VAR_END;
        $result = $lib->msg_var_text([[$msg_txt, [msg_id::VAR_NAME => 'math']]]);
        $t->assert($test_name, $result, $target);

        // test trim of an SQL statement to the relevant part
        // to make two SQL statements more comparable
        $text = "field1, field2 FROM table_name;";
        $target = "field1,field2 FROM table_name;";
        $result = $lib->trim_sql($text);
        $t->assert("trim_sql", $result, $target);

        // sql_format recreates the hand formatted layout of an update log function
        // from the generated single line sql statement
        $test_name = 'sql_format';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST, $t);
        $test_name = 'sql_format MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_MYSQL, $t);
        $test_name = 'sql_format insert';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_INSERT, $t);
        $test_name = 'sql_format insert MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_INSERT_MYSQL, $t);
        $test_name = 'sql_format prepared insert';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_INSERT_PREPARED, $t);
        $test_name = 'sql_format prepared insert MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_INSERT_PREPARED_MYSQL, $t);
        $test_name = 'sql_format update';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_UPDATE, $t);
        $test_name = 'sql_format update MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_UPDATE_MYSQL, $t);
        $test_name = 'sql_format select';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT, $t);
        $test_name = 'sql_format select MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT_MYSQL, $t);
        $test_name = 'sql_format select of a joined object';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT_JOINED, $t);
        $test_name = 'sql_format select of a joined object MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT_JOINED_MYSQL, $t);
        $test_name = 'sql_format select with two joins, sort and paging';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT_SORTED, $t);
        $test_name = 'sql_format select with two joins, sort and paging MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_SELECT_SORTED_MYSQL, $t);
        $test_name = 'sql_format select union';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_UNION, $t);
        $test_name = 'sql_format select union MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_UNION_MYSQL, $t);
        $test_name = 'sql_format select with bracketed or groups';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_OR_GROUP, $t);
        $test_name = 'sql_format delete log';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_DELETE_LOG, $t);
        $test_name = 'sql_format delete log MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_DELETE_LOG_MYSQL, $t);
        $test_name = 'sql_format select count';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_COUNT, $t);
        $test_name = 'sql_format select count MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_COUNT_MYSQL, $t);
        $test_name = 'sql_format select count by user';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_COUNT_USER, $t);
        $test_name = 'sql_format select count by user MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_COUNT_USER_MYSQL, $t);
        // a joined sub query without a union e.g. the summed user log sessions with paging
        $test_name = 'sql_format select sum with paging';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_SUM, $t);
        $test_name = 'sql_format select sum with paging MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_USER_FORMAT . test_files::SQL_FORMAT_TEST_SUM_MYSQL, $t);
        $test_name = 'sql_format create table';
        $this->assert_sql_format($test_name, test_paths::DB_CACHE . test_files::SQL_FORMAT_TEST_CREATE, $t);
        $test_name = 'sql_format create table MariaSQL';
        $this->assert_sql_format($test_name, test_paths::DB_CACHE . test_files::SQL_FORMAT_TEST_CREATE_MYSQL, $t);
        $test_name = 'sql_format create table with user table and comments';
        $this->assert_sql_format($test_name, test_paths::DB_FORMAT_TEST . test_files::SQL_FORMAT_TEST_CREATE_USER, $t);

        // a malformed insert (e.g. the extra comma of the change log generator, see
        // docs/llm/pending_prio_2.md) has more columns than values, so it cannot be placed on the
        // column grid; it is kept on one line instead of reading a value that does not exist
        $body = 'CREATE OR REPLACE FUNCTION t (_a bigint) RETURNS bigint AS $$ BEGIN '
            . 'INSERT INTO changes (user_id, new_value,, row_id) SELECT _a,_a,_a ; '
            . 'END $$ LANGUAGE plpgsql; PREPARE t_call (bigint) AS SELECT t ($1); SELECT t (1::bigint);';
        $test_name = 'sql_format keeps an insert with a missing value on one line';
        $t->assert_text_contains($test_name, $lib->sql_format($body),
            'INSERT INTO changes (user_id, new_value,, row_id) SELECT _a,_a,_a;');
        // the column names of a matching insert are indented by the sigil of their value, so
        // that each name starts above the name part of the value below it
        $test_name = 'sql_format aligns the same insert once the columns and values match';
        $t->assert_text_contains($test_name, $lib->sql_format(str_replace(',,', ',', $body)),
            'INSERT INTO changes ( user_id, new_value, row_id)');

        // json_compact_format recreates the compact layout of the import files (e.g.
        // travel_scoring_value_list.json) from the standard php json pretty print
        $test_name = 'json_compact_format short objects';
        $this->assert_json_compact_format($test_name,
            test_paths::IMPORT_FORMAT_TEST . test_files::JSON_FORMAT_TEST_WORDS, $t);
        $test_name = 'json_compact_format nested value list';
        $this->assert_json_compact_format($test_name,
            test_paths::IMPORT_FORMAT_TEST . test_files::JSON_FORMAT_TEST_VALUE_LIST, $t);
        $test_name = 'json_compact_format long line';
        $this->assert_json_compact_format($test_name,
            test_paths::IMPORT_FORMAT_TEST . test_files::JSON_FORMAT_TEST_LONG_LINE, $t);

        // the negative cases: an input that is not a json is returned unchanged like sql_format
        $test_name = 'json_compact_format keeps a text that is not a json';
        $t->assert($test_name, $lib->json_compact_format('not a json'), 'not a json');
        $test_name = 'json_compact_format keeps an empty text';
        $t->assert($test_name, $lib->json_compact_format(''), '');
        // an empty object or array has no child to place on its own line
        $test_name = 'json_compact_format of an empty object';
        $t->assert($test_name, $lib->json_compact_format('{}'), '{}');
        $test_name = 'json_compact_format of an empty array';
        $t->assert($test_name, $lib->json_compact_format('[]'), '[]');

        // test trim of an JSON string to the relevant part
        // to make two JSON strings more comparable
        $text = ' { "field" :  "value", "array": [ "item" ] } ';
        $target = '{"field" : "value","array":[ "item" ]}';
        $result = $lib->trim_json($text);
        $t->assert("trim_json", $result, $target);

        // test trim of an HTML string to the relevant part
        // to make two HTML strings more comparable
        $text = ' <html lang="en">  <table >  <tr>  <th>header</th>   </tr>  <tr>  <td>data</td>   </tr>  </table>   </html> ';
        $target = '<html lang="en"><table><tr><th>header</th></tr><tr><td>data</td></tr></table></html>';
        $result = $lib->trim_html($text);
        $t->assert("trim_html", $result, $target);

        // a space directly after a tag is ignored e.g. '<td> 5</td>' is the same as '<td>5</td>'
        $text = '<td> 5</td>';
        $target = $lib->trim_html('<td>5</td>');
        $result = $lib->trim_html($text);
        $t->assert("trim_html space after tag", $result, $target);

        // the html snapshots are stored formatted, so a formatted html must trim to the same text
        // as the compact html it has been created from, links included
        $test_name = 'trim_html of a formatted html';
        $text = '<li><a href="/http/view.php">word</a></li>';
        $target = $lib->trim_html($text);
        $result = $lib->trim_html($lib->format_html($text));
        $t->assert($test_name, $result, $target);
        // negative: a text without a tag has nothing to indent, so it is kept as it is
        $test_name = 'format_html keeps a text without a tag';
        $t->assert($test_name, $lib->format_html('two words'), 'two words');

        // the sql fixtures of a class are in the folder of the class, a link class shares the folder of its main class
        $test_name = 'the resource path of a class names the class';
        $cmp_path = $lib->class_to_resource_path(component::class);
        $t->assert_text_contains($test_name, $cmp_path, $lib->class_to_name(component::class));
        $test_name = '... and a formula link has the path of the formula, not of the component';
        $t->assert_false($test_name, $lib->class_to_resource_path(formula_link::class) == $cmp_path);

        // a cleared link object exports only the json array, a named object only its name
        $test_name = 'a cleared formula link exports an empty array';
        $t->assert($test_name, $lib->class_to_empty_json(formula_link::class), test_api::JSON_ARRAY_ONLY);
        $test_name = '... but a cleared component its name';
        $t->assert($test_name, $lib->class_to_empty_json(component::class), test_api::JSON_NAME_ONLY);

        // only the spaces between the tags are removed, a space within a text is part of the text
        $test_name = 'trim_html keeps a space within a text';
        $target = $lib->trim_html('<td>two words</td>');
        $result = $lib->trim_html('<td>twowords</td>');
        $t->assert_not($test_name, $result, $target);

        // convert an HTML page title to the text the user would see, keeping the
        // font awesome edit icon as a readable '<fas fa-edit>' placeholder
        $test_name = 'html_to_text';
        $text = '<div class="heading-line"><h4 class="heading-inline">Zurich</h4>'
            . '<a href="#"><i class="fas fa-edit"></i></a></div>'
            . '<div class="subtitle">(is a city, canton, ...) / measure</div>'
            . '<div class="subtitle">(personal, admin protection)</div>';
        $target = 'Zurich <fas fa-edit> (is a city, canton, ...) / measure (personal, admin protection)';
        $result = $lib->html_to_text($text);
        $t->assert($test_name, $result, $target);

        // text without any tags is returned unchanged apart from collapsed spaces
        $test_name = 'html_to_text without tags';
        $text = '  no  tags  here  ';
        $target = 'no tags here';
        $result = $lib->html_to_text($text);
        $t->assert($test_name, $result, $target);

        // convert a realistic view fragment (a headline with an edit icon, two subtitles,
        // related phrases and a values table) to markdown and compare it to the expected
        // ".md" screenshot; the icon is kept as a readable '<fas fa-edit>' placeholder
        $test_name = 'html_to_markdown';
        $res_stem = test_paths::HTML . 'markdown/word_title_and_values';
        $text = $t->file($res_stem . test_files::HTML);
        $target = $t->file($res_stem . test_files::MD);
        $result = $lib->html_to_markdown($text);
        $t->assert($test_name, $result, $target);

        // text without any tags is returned unchanged apart from collapsed spaces
        $test_name = 'html_to_markdown without tags';
        $text = '  no  tags  here  ';
        $target = 'no tags here';
        $result = $lib->html_to_markdown($text);
        $t->assert($test_name, $result, $target);

        // replace volatile CSRF token with a fixed dummy value for snapshot tests
        $token = test_const::DUMMY_SESSION_TOKEN;
        $live_token = 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2';
        $text = '<input type="hidden" name="token" value="' . $live_token . '">';
        $target = '<input type="hidden" name="token" value="' . $token . '">';
        $result = $lib->fix_volatile_in_html($text, $token);
        $t->assert("fix_volatile_in_html: token replaced", $result, $target);

        // token with name after value attribute
        $text = '<input type="hidden" value="' . $live_token . '" name="token">';
        $target = '<input type="hidden" value="' . $token . '" name="token">';
        $result = $lib->fix_volatile_in_html($text, $token);
        $t->assert("fix_volatile_in_html: token replaced (value before name)", $result, $target);

        // non-token hidden field must not be changed
        $text = '<input type="hidden" name="mask" value="61">';
        $target = '<input type="hidden" name="mask" value="61">';
        $result = $lib->fix_volatile_in_html($text, $token);
        $t->assert("fix_volatile_in_html: non-token field unchanged", $result, $target);

        // html without any token is returned unchanged
        $text = '<form action="view.php"><input type="text" name="user"></form>';
        $result = $lib->fix_volatile_in_html($text, $token);
        $t->assert("fix_volatile_in_html: no token unchanged", $result, $text);


        $t->subheader($ts . 'string parts');

        // test str_between
        $test_name = 'str_between';
        $text = "The formula id of {f23}.";
        $maker_start = "{f";
        $maker_end = "}";
        $target = "23";
        $result = $lib->str_between($text, $maker_start, $maker_end);
        $t->assert($test_name, $result, $target);

        // only the first part between the makers is selected
        $test_name = 'str_between of two parts';
        $text = "The formula id of {f4} / {f5}.";
        $target = "4";
        $result = $lib->str_between($text, $maker_start, $maker_end);
        $t->assert($test_name, $result, $target);

        // negative: a text without the start maker has nothing between
        $test_name = 'str_between without start maker';
        $result = $lib->str_between('The formula id of 23}.', $maker_start, $maker_end);
        $t->assert($test_name, $result, '');

        // test str_left_of
        $text = "This is left of that ";
        $maker = " of that";
        $target = "This is left";
        $result = $lib->str_left_of($text, $maker);
        $t->assert("str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_left_of
        $text = "This is left of that, but not of that";
        $result = $lib->str_left_of($text, $maker);
        $t->assert("str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "That is right of this";
        $maker = "That is right ";
        $target = "of this";
        $result = $lib->str_right_of($text, $maker);
        $t->assert("str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "00000";
        $maker = "0";
        $target = "0000";
        $result = $lib->str_right_of($text, $maker);
        $t->assert("str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_right_of
        $text = "The formula id of {f23}.";
        $maker = "{f";
        $target = "23}.";
        $result = $lib->str_right_of($text, $maker);
        $t->assert("str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $result, $target);

        // test str_left
        $test_name = 'str_left';
        $text = "This are the left 4";
        $pos = 4;
        $target = "This";
        $result = $lib->str_left($text, $pos);
        $t->assert($test_name, $result, $target);

        // negative: zero chars from the left is an empty text
        $test_name = 'str_left of zero chars';
        $result = $lib->str_left($text, 0);
        $t->assert($test_name, $result, '');

        // test str_right
        $test_name = 'str_right';
        $text = "This are the right 7";
        $pos = 7;
        $target = "right 7";
        $result = $lib->str_right($text, $pos);
        $t->assert($test_name, $result, $target);

        // negative: more chars than the text has return the complete text
        $test_name = 'str_right of more chars than the text';
        $result = $lib->str_right('right', 9);
        $t->assert($test_name, $result, 'right');

        $text = "ignore start<select";
        $maker = 'start<';
        $target = "select";
        $result = $lib->str_right_of($text, $maker);
        $t->assert("str_right: right of \"" . $maker . "\" is \"" . $target . "\"", $result, $target);

        // the part right of the maker
        $test_name = 'str_right_of_or_all';
        $text = "9code_id";
        $maker = '9';
        $target = "code_id";
        $result = $lib->str_right_of_or_all($text, $maker);
        $t->assert($test_name, $result, $target);

        // negative: the complete text, because the maker is not part of the given string
        $test_name = 'str_right_of_or_all without maker';
        $text = "ignore start<select";
        $maker = 'no start<';
        $target = "ignore start<select";
        $result = $lib->str_right_of_or_all($text, $maker);
        $t->assert($test_name, $result, $target);

        // the part left of the last maker e.g. the namespace of a class
        $test_name = 'str_left_of_last';
        $result = $lib->str_left_of_last('cfg\formula\formula', '\formula');
        $t->assert($test_name, $result, 'cfg\formula');

        // negative: a text without the maker has no left part
        $test_name = 'str_left_of_last without maker';
        $result = $lib->str_left_of_last('cfg\word', '\formula');
        $t->assert($test_name, $result, '');

        // the part left of the maker
        $test_name = 'str_left_of_or_all';
        $result = $lib->str_left_of_or_all('select>end ignore', '>end');
        $t->assert($test_name, $result, 'select');

        // negative: the complete text, because the maker is not part of the given string
        $test_name = 'str_left_of_or_all without maker';
        $result = $lib->str_left_of_or_all('select', '>end');
        $t->assert($test_name, $result, 'select');

        // the script name of a log entry is written to the sys_log_functions name field, so it
        // must stay short also if the trace does not contain the project path e.g. if the pod is
        // deployed to /var/www/html; a name with the complete trace would not fit into the field
        // and the failing insert would log again and never end (see log_msg)
        $trace_dev = '#0 ' . def_shared::PROJECT_PATH
            . 'src/main/php/shared/helper/Translator.php(184): log_err()';
        $test_name = 'the script name of a trace within the project path';
        $t->assert($test_name, library::php_function_from_exception($trace_dev),
            'src/main/php/shared/helper/Translator');

        $trace_deployed = '#0 /var/www/html/src/main/php/shared/helper/Translator.php(184): log_err()'
            . "\n" . '#1 /var/www/html/src/main/php/web/log/change_log_named.php(356): field_name()';
        $test_name = 'the script name of a trace outside the project path';
        $t->assert($test_name, library::php_function_from_exception($trace_deployed), 'Translator');

        $test_name = 'the script name of a trace outside the project path fits into the db field';
        $t->assert_true($test_name,
            strlen(library::php_function_from_exception($trace_deployed)) <= sql_field_type::NAME_MAX_LEN);

        $test_name = 'a trace without any script name does not create a name with the trace';
        $t->assert($test_name, library::php_function_from_exception(''), library::FUNCTION_UNKNOWN);

        // test base_class_name
        $class = 'cfg\language';
        $target = 'language';
        $result = $lib->class_to_name($class);
        $t->assert("base_class_name", $result, $target);

        // test camelize
        $test_name = 'camelize';
        $result = $lib->camelize("function_name");
        $t->assert($test_name, $result, "FunctionName");

        // negative: a text without separator only gets the first char in upper case
        $test_name = 'camelize without separator';
        $result = $lib->camelize("function");
        $t->assert($test_name, $result, "Function");

        // test camelize_ex_1
        $test_name = 'camelize_ex_1';
        $result = $lib->camelize_ex_1("function_name");
        $t->assert($test_name, $result, "functionName");

        // negative: a text without separator is not changed
        $test_name = 'camelize_ex_1 without separator';
        $result = $lib->camelize_ex_1("function");
        $t->assert($test_name, $result, "function");

        // test class_to_api_route: the reference api folder is named by the full word
        $test_name = 'api route of the frontend reference class';
        $t->assert($test_name, $lib->class_to_api_route(ref_ui::class), url_var::REF_API);
        $test_name = 'api route of any other class is the camelized class name';
        $t->assert($test_name, $lib->class_to_api_route(formula_link::class), 'formulaLink');

        // test class_csv_file_path: the rows expected after a database reset are in the folder of
        // the class and the types of the class in the types csv of the same folder; the files
        // themselves are written by the database reset (see test_db_load::csv_recreate)
        $test_name = 'the expected rows of a class are in the list csv of the class folder';
        $t->assert($test_name, $lib->class_csv_file_path(component::class),
            test_paths::UNIT_RES . 'component' . DIRECTORY_SEPARATOR . test_files::FIXED_DB_CSV);
        $test_name = 'and the expected types of the same class in the types csv';
        $t->assert($test_name, $lib->class_csv_file_path(component::class, test_files::FIXED_DB_TYPES_CSV),
            test_paths::UNIT_RES . 'component' . DIRECTORY_SEPARATOR . test_files::FIXED_DB_TYPES_CSV);

        // test csv_line: a value that contains the delimiter or a quote is enclosed
        $test_name = 'a value with a comma is enclosed in double quotes';
        $t->assert($test_name, $lib->csv_line(['7', 'word list up', 'a list, of words']),
            '7,word list up,"a list, of words"');
        $test_name = 'a quote within a value is doubled';
        $t->assert($test_name, $lib->csv_line(['e.g. Verb "is a"']), '"e.g. Verb ""is a"""');

        // test csv_line_to_array: an enclosed value is read as one value, whichever quote is used
        $test_name = 'a double quoted value with a comma is read as one value';
        $t->assert($test_name, implode('|', $lib->csv_line_to_array('7,word list up,"a list, of words"')),
            '7|word list up|a list, of words');
        $test_name = 'the same for the single quotes of the code link files';
        $t->assert($test_name, implode('|', $lib->csv_line_to_array("7, 'word list up', 'a list, of words'")),
            '7|word list up|a list, of words');
        $test_name = 'a doubled quote within an enclosed value is one quote';
        $t->assert($test_name, implode('|', $lib->csv_line_to_array('1,"""one"" is needed, but not shown"')),
            '1|"one" is needed, but not shown');
        $test_name = 'so a value with a comma and a quote survives the round trip';
        $row = ['a', 'b, c', 'd"e', ''];
        $t->assert($test_name, implode('|', $lib->csv_line_to_array($lib->csv_line($row))), implode('|', $row));

        // the negative cases: without an enclosure the delimiter separates two values
        // and a quote within a value is just a char of the value
        $test_name = 'a comma without an enclosure separates two values';
        $t->assert($test_name, implode('|', $lib->csv_line_to_array('1,a list, of words')),
            '1|a list| of words');
        $test_name = 'an apostrophe within a value does not enclose';
        $t->assert($test_name, implode('|', $lib->csv_line_to_array("1,it doesn't split")),
            "1|it doesn't split");


        $t->subheader($ts . 'arrays and lists');

        // the inner array values are added in place of the inner array
        $test_name = 'array_flat';
        $inner_array = ["a", "b", "c"];
        $test_array = [1, 2, $inner_array, 3];
        $result = $lib->array_flat($test_array);
        $t->assert($test_name, $result, [1, 2, 'a', 'b', 'c', 3]);

        // negative: an empty array stays empty
        $test_name = 'array_flat of empty';
        $result = $lib->array_flat([]);
        $t->assert($test_name, $result, []);

        // all elements of all levels are counted
        $test_name = 'count_recursive';
        $level2_array = [4, 5, 6];
        $level1_array = ["a", "b", "c", $level2_array];
        $test_array = [1, 2, $level1_array, 3];
        $result = $lib->count_recursive($test_array);
        $t->assert($test_name, $result, 11);

        // only the elements of the first two levels are counted
        $test_name = 'count_recursive of two levels';
        $level2_array = [4, 5, 6];
        $level1_array = ["a", "b", "c", $level2_array];
        $test_array = [1, 2, $level1_array, 3];
        $result = $lib->count_recursive($test_array, 2);
        $t->assert($test_name, $result, 8);

        // negative: an empty array has no elements
        $test_name = 'count_recursive of empty';
        $result = $lib->count_recursive([]);
        $t->assert($test_name, $result, 0);

        // the empty strings are removed
        $test_name = 'array_trim';
        $test_array = ["a", "", "c"];
        $result = $lib->array_trim($test_array);
        $t->assert($test_name, $result, ['a', 'c']);

        // negative: null results in an empty array
        $test_name = 'array_trim of null';
        $result = $lib->array_trim(null);
        $t->assert($test_name, $result, []);

        $test_array = [1, 2, 3];
        $target = " AND field IN (1,2,3)";
        $result = $lib->sql_array($test_array, ' AND field IN (', ')');
        $t->assert("sql_array", $result, $target);

        $target = " AND field IN ('1,2,3')";
        $result = $lib->sql_array($test_array, ' AND field IN (', ')', true);
        $t->assert("sql_array", $result, $target);

        $test_array = [];
        $target = '';
        $result = $lib->sql_array($test_array, ' AND field IN (', ')');
        $t->assert("sql_array empty", $result, $target);

        // the excluded entries are removed
        $test_name = 'lst_not_in';
        $test_array = [1, 2, 3, 4];
        $del_array = [2, 3];
        $result = $lib->lst_not_in($test_array, $del_array);
        $t->assert($test_name, $result, [1, 4]);

        // negative: if all entries are excluded the list is empty
        $test_name = 'lst_not_in all excluded';
        $test_array = [1, 2];
        $del_array = [2, 1];
        $result = $lib->lst_not_in($test_array, $del_array);
        $t->assert($test_name, $result, []);

        $test_array = [5, 6, 7, 8];
        $par_name = 'phr';
        $target = '&phr1=5&phr2=6&phr3=7&phr4=8';
        $result = $lib->ids_to_url($test_array, $par_name);
        $t->assert("ids_to_url", $result, $target);

        $test_array = [1, 0, null, 4];
        $target = [1, 4];
        $result = $lib->ids_not_empty($test_array);
        $t->assert("ids_not_empty", $result, $target);

        // an entry added to the order list keeps the order
        $test_name = 'same function order but add to all list';
        $result = $lib->arrayCompareOrder([1, 2, 3], [1, 2]);
        $t->assert_true($test_name, $result);

        // an entry missing at the end keeps the order
        $test_name = 'function missing at end but same order in all list';
        $result = $lib->arrayCompareOrder([1, 2], [1, 2, 3]);
        $t->assert_true($test_name, $result);

        // an entry missing in the middle keeps the order
        $test_name = 'function missing in the middle but same order in all list';
        $result = $lib->arrayCompareOrder([1, 3], [1, 2, 3]);
        $t->assert_true($test_name, $result);

        // an empty order list keeps the order
        $test_name = 'no function but same order in all list';
        $result = $lib->arrayCompareOrder([], [1, 2, 3]);
        $t->assert_true($test_name, $result);

        // a different last entry keeps the order
        $test_name = 'last function differs the order is still fine';
        $result = $lib->arrayCompareOrder([1, 2, 3, 4, 5], [1, 2, 3, 4, 6]);
        $t->assert_true($test_name, $result);

        // an empty compare list keeps the order
        $test_name = 'same function order because all list is empty';
        $result = $lib->arrayCompareOrder([1, 2, 3], []);
        $t->assert_true($test_name, $result);

        // negative: a swapped pair breaks the order
        $test_name = 'function order differs from all function list';
        $result = $lib->arrayCompareOrder([3, 1], [1, 2, 3]);
        $t->assert_false($test_name, $result);

        // the first entry in the wrong order is named
        $test_name = 'arrayOrderDiff';
        $result = $lib->arrayOrderDiff([3, 1], [1, 2, 3]);
        $t->assert($test_name, $result, '1 should be before 3');

        // negative: the same order has no diff
        $test_name = 'arrayOrderDiff same order';
        $result = $lib->arrayOrderDiff([1, 3], [1, 2, 3]);
        $t->assert($test_name, $result, '');

        // the values for which the callback is true are removed on all levels
        $test_name = 'array_filter_r';
        $test_array = ['a' => 1, 'b' => ['c' => 2, 'd' => 3]];
        $result = $lib->array_filter_r($test_array, fn($val) => $val == 2);
        $t->assert($test_name, $result, ['a' => 1, 'b' => ['d' => 3]]);

        // negative: if the callback is never true nothing is removed
        $test_name = 'array_filter_r no match';
        $test_array = ['a' => 1, 'b' => ['c' => 2]];
        $result = $lib->array_filter_r($test_array, fn($val) => $val == 9);
        $t->assert($test_name, $result, ['a' => 1, 'b' => ['c' => 2]]);

        // the first column is the key and the second the value
        $test_name = 'array_get_first_two_col';
        $test_array = [['k1', 'v1', 'default'], ['k2', 'v2']];
        $result = $lib->array_get_first_two_col($test_array);
        $t->assert($test_name, $result, ['k1' => 'v1', 'k2' => 'v2']);

        // negative: an empty array results in an empty array
        $test_name = 'array_get_first_two_col of empty';
        $result = $lib->array_get_first_two_col([]);
        $t->assert($test_name, $result, []);

        // the keys of all levels are listed
        $test_name = 'array_keys_r';
        $test_array = ['a' => 1, 'b' => ['c' => 2]];
        $result = $lib->array_keys_r($test_array);
        $t->assert($test_name, $result, ['a', 'b', 'c']);

        // negative: an empty array has no keys
        $test_name = 'array_keys_r of empty';
        $result = $lib->array_keys_r([]);
        $t->assert($test_name, $result, []);

        // the entries of the second array are added
        $test_name = 'array_merge_by_key';
        $result = $lib->array_merge_by_key(['a' => 1], ['b' => 2]);
        $t->assert($test_name, $result, ['a' => 1, 'b' => 2]);

        // negative: a key of the second array that is already set is ignored
        $test_name = 'array_merge_by_key same key';
        $result = $lib->array_merge_by_key(['a' => 1], ['a' => 2]);
        $t->assert($test_name, $result, ['a' => 1]);

        // a needle value that differs from the haystack is returned
        $test_name = 'array_recursive_diff';
        $haystack = ['id' => 1, 'name' => 'a', 'type' => 'x'];
        $needle = ['id' => 1, 'name' => 'b'];
        $result = $lib->array_recursive_diff($haystack, $needle);
        $t->assert($test_name, $result, ['name' => 'b']);

        // negative: a needle that is part of the haystack has no diff
        $test_name = 'array_recursive_diff contained';
        $haystack = ['id' => 1, 'name' => 'a', 'type' => 'x'];
        $needle = ['id' => 1, 'name' => 'a'];
        $result = $lib->array_recursive_diff($haystack, $needle);
        $t->assert($test_name, $result, []);

        // the values of all levels are combined with the separator
        $test_name = 'implode_recursive';
        $result = $lib->implode_recursive(',', [1, [2, 3], 4]);
        $t->assert($test_name, $result, '1,2,3,4');

        // negative: an empty array results in an empty text
        $test_name = 'implode_recursive of empty';
        $result = $lib->implode_recursive(',', []);
        $t->assert($test_name, $result, '');

        // the numeric keys are sorted before the text keys
        $test_name = 'key_num_sort';
        $test_array = ['b' => 'B', 1 => 'one', 'a' => 'A', 0 => 'zero'];
        $result = $lib->key_num_sort($test_array);
        $key_order = implode(',', array_keys($result));
        $t->assert($test_name, $key_order, '0,1,a,b');

        // negative: an empty array stays empty
        $test_name = 'key_num_sort of empty';
        $result = $lib->key_num_sort([]);
        $t->assert($test_name, $result, []);

        $test_name = 'add string to array after a given string';
        $lst = ['first', 'middle', 'last'];
        $result = $lib->arrayAddAfter($lst, 'before middle', 'first');
        $target = ['first', 'before middle', 'middle', 'last'];
        $t->assert($test_name . ' before middle', $result, $target);
        $result = $lib->arrayAddAfter($lst, 'after middle', 'middle');
        $target = ['first', 'middle', 'after middle', 'last'];
        $t->assert($test_name . ' after middle', $result, $target);
        $result = $lib->arrayAddAfter($lst, 'at the end', 'not in list');
        $target = ['first', 'middle', 'last', 'at the end'];
        $t->assert($test_name . ' at the end', $result, $target);

        $test_name = 'add array to array before a given array value';
        $result = $lib->arrayAddArrayBefore($lst, ['after middle', 'before last'], 'last');
        $target = ['first', 'middle', 'after middle', 'before last', 'last'];
        $t->assert($test_name . ' before last', $result, $target);
        $result = $lib->arrayAddArrayBefore($lst, ['after last', 'after after last'], 'no match');
        $target = ['first', 'middle', 'last', 'after last', 'after after last'];
        $t->assert($test_name . ' after last', $result, $target);
        $result = $lib->arrayAddArrayBefore($lst, ['even before first', 'before first'], 'first');
        $target = ['even before first', 'before first', 'first', 'middle', 'last'];
        $t->assert($test_name . ' before first', $result, $target);
        $test_name = 'add array to array before a the last';
        $result = $lib->arrayAddArrayBefore($lst, ['before last'], 'last');
        $target = ['first', 'middle', 'before last', 'last'];
        $t->assert($test_name, $result, $target);
        $test_name = 'add array to array before a the last and add after the previous insert';
        $result = $lib->arrayAddArrayBefore($result, ['after before last'], 'last');
        $target = ['first', 'middle', 'before last', 'after before last', 'last'];
        $t->assert($test_name, $result, $target);

        $test_name = 'function tree md page tests';
        $t_code = new coding_rule_tests();
        $fnc_lst = [
            'main backend errors' => [
                'component link' => [
                    'get_predicate_name' => 'description'],
                'formula' => [
                    'special result' => 'description']],
            'main backend' => [
                'construct and map' => [
                    '__construct' => 'description'],
                'reset' => [
                    'component' => 'description']]];
        $result = $t_code->php_function_list_to_md_row_start($fnc_lst);
        $target = '### main backend errors

```
\-- component link
    \-- get_predicate_name - description
\-- formula
    \-- special result - description
```

### main backend

```
\-- construct and map
    \-- __construct - description
\-- reset
    \-- component - description
```
';
        $t->assert($test_name, $result, $target);


        $t->subheader($ts . 'csv and files');

        // the text keys are the header and the numeric keys are ignored
        $test_name = 'csv_form_db_lst';
        $db_lst = [['id' => 1, 0 => 1, 'name' => 'a,b']];
        $result = $lib->csv_form_db_lst($db_lst, component::class);
        $t->assert($test_name, $result, ["id,name\n", "1,\"a,b\"\n"]);

        // negative: an empty database list results in an empty csv
        $test_name = 'csv_form_db_lst of empty';
        $result = $lib->csv_form_db_lst([], component::class);
        $t->assert($test_name, $result, []);

        // the values of the named column are cleared
        $test_name = 'csv_clear_col';
        $csv = ["id,password\n", "1,secret\n"];
        $result = $lib->csv_clear_col($csv, 'password');
        $t->assert($test_name, $result, ["id,password\n", "1,\n"]);

        // negative: a column that is not in the header changes nothing
        $test_name = 'csv_clear_col unknown column';
        $csv = ["id,password\n", "1,secret\n"];
        $result = $lib->csv_clear_col($csv, 'email');
        $t->assert($test_name, $result, ["id,password\n", "1,secret\n"]);

        // a value with the delimiter is enclosed and the quote is doubled
        $test_name = 'csv_field';
        $result = $lib->csv_field('a,"b"');
        $t->assert($test_name, $result, '"a,""b"""');

        // negative: null results in an empty field
        $test_name = 'csv_field of null';
        $result = $lib->csv_field(null);
        $t->assert($test_name, $result, '');

        // dots and slashes are removed and commas replaced
        $test_name = 'str_to_file';
        $result = $lib->str_to_file('a.b/c,d');
        $t->assert($test_name, $result, 'abc_d');

        // negative: a long text is cut after 32 chars
        $test_name = 'str_to_file too long';
        $result = $lib->str_to_file('0123456789abcdefghij0123456789abcdefghij');
        $t->assert($test_name, $result, '0123456789abcdefghij0123456789ab');

        // the files of the folder tree are listed with the path
        $test_name = 'array_to_path';
        $files = ['a.txt', 'sub' => ['b.txt']];
        $result = $lib->array_to_path($files, 'root');
        $target = ['root' . DIRECTORY_SEPARATOR . 'a.txt', 'root' . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'b.txt'];
        $t->assert($test_name, $result, $target);

        // negative: an empty folder tree has no path
        $test_name = 'array_to_path of empty';
        $result = $lib->array_to_path([], 'root');
        $t->assert($test_name, $result, []);

        // a subfolder is listed with its name as key
        $test_name = 'dir_to_array';
        $result = $lib->dir_to_array(test_paths::API_RES);
        $t->assert_key_exists($test_name, 'system', $result);

        // negative: a file is listed as value not as key
        $test_name = 'dir_to_array file is no key';
        $result = $lib->dir_to_array(test_paths::API_SYSTEM_RES);
        $t->assert_key_not_exists($test_name, 'sys_log.json', $result);

        // the files are listed with the full path
        $test_name = 'dir_files';
        $result = $lib->dir_files(test_paths::API_SYSTEM_RES);
        $t->assert_contains($test_name, $result, test_files::SYS_LOG);

        // negative: the dot entries of the folder are not listed
        $test_name = 'dir_files without dot entries';
        $dot_entry = test_paths::API_SYSTEM_RES . '.';
        $result = $lib->dir_files(test_paths::API_SYSTEM_RES);
        $t->assert_contains_not($test_name, $result, $dot_entry);

        // negative: a missing folder counts as removed
        $test_name = 'dir_remove of missing folder';
        $missing_dir = test_paths::API_SYSTEM_RES . 'missing_folder';
        $result = $lib->dir_remove($missing_dir);
        $t->assert_true($test_name, $result);

        $t->subheader($ts . 'display');

        // an array is shown comma separated
        $test_name = 'dsp_var array';
        $test_var = [1, 2, 3];
        $target = '1,2,3';
        $result = $lib->dsp_var($test_var);
        $t->assert($test_name, $result, $target);

        // a number is shown as it is
        $test_name = 'dsp_var number';
        $test_var = 1;
        $target = '1';
        $result = $lib->dsp_var($test_var);
        $t->assert($test_name, $result, $target);

        // negative: null is shown as an empty text
        $test_name = 'dsp_var null';
        $result = $lib->dsp_var(null);
        $t->assert($test_name, $result, '');

        // numbers are shown comma separated
        $test_name = 'dsp_array numbers';
        $test_array = [1, 2, 3];
        $target = '1,2,3';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // texts are shown comma separated
        $test_name = 'dsp_array string';
        $test_array = ["A", "B", "C"];
        $target = 'A,B,C';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // negative: an empty array is shown as an empty text
        $test_name = 'dsp_array empty';
        $test_array = [];
        $target = '';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // negative: a missing array is shown as null
        $test_name = 'dsp_array null';
        $test_array = null;
        $target = 'null';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // a long array is cut to the first and the last entries without debug
        $test_name = 'dsp_array many numbers';
        $mem_debug = $debug;
        $debug = 1;
        $test_array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $target = '1,2,3,...,10';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // a debug level above 10 shows all entries
        $test_name = 'dsp_array many number details';
        $debug = 11;
        $test_array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $target = '1,2,3,4,5,6,7,8,9,10';
        $result = $lib->dsp_array($test_array);
        $t->assert($test_name, $result, $target);

        // the keys of a long array are cut to the first and the last keys without debug
        $test_name = 'dsp_array_keys many numbers';
        $debug = 1;
        $test_array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $target = '0,1,2,...,9';
        $result = $lib->dsp_array_keys($test_array);
        $t->assert($test_name, $result, $target);

        // a debug level above 10 shows all keys
        $test_name = 'dsp_array_keys many number details';
        $debug = 11;
        $test_array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $target = '0,1,2,3,4,5,6,7,8,9';
        $result = $lib->dsp_array_keys($test_array);
        $t->assert($test_name, $result, $target);

        // negative: a missing array has no keys and is shown as null
        $test_name = 'dsp_array_keys null';
        $result = $lib->dsp_array_keys(null);
        $t->assert($test_name, $result, 'null');

        // with keys each entry is shown as key=value
        $test_name = 'dsp_array with keys';
        $test_array = ['key1' => 'val1', 'key2' => 'val2'];
        $target = 'key1=val1,key2=val2';
        $result = $lib->dsp_array($test_array, true);
        $t->assert($test_name, $result, $target);

        // recursive: nested array is shown as [key=val,...] inline
        $test_name = 'dsp_array recursive';
        $test_array = ['key1' => ['m' => '3', 'id' => '123'], 'key2' => 'val2'];
        $target = 'key1=[m=3,id=123],key2=val2';
        $result = $lib->dsp_array($test_array, true);
        $t->assert($test_name, $result, $target);

        // many entries with keys are cut without debug
        $test_name = 'dsp_array with keys truncated';
        $debug = 1;
        $test_array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8];
        $target = 'a=1,b=2,c=3,...,h=8';
        $result = $lib->dsp_array($test_array, true);
        $t->assert($test_name, $result, $target);

        // many entries with keys are shown completely with a debug level above 10
        $test_name = 'dsp_array with keys debug>10 shows all';
        $debug = 11;
        $test_array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8];
        $target = 'a=1,b=2,c=3,d=4,e=5,f=6,g=7,h=8';
        $result = $lib->dsp_array($test_array, true);
        $t->assert($test_name, $result, $target);

        $debug = $mem_debug;

        // a true bool is shown as text
        $test_name = 'dsp_bool true';
        $result = $lib->dsp_bool(true);
        $t->assert($test_name, $result, 'true');

        // negative: a false bool is shown as text and not as an empty string
        $test_name = 'dsp_bool false';
        $result = $lib->dsp_bool(false);
        $t->assert($test_name, $result, 'false');

        // the number of entries of an array
        $test_name = 'dsp_count';
        $test_array = [1, 2, 3];
        $result = $lib->dsp_count($test_array);
        $t->assert($test_name, $result, 3);

        // negative: a missing array has no entries
        $test_name = 'dsp_count of null';
        $result = $lib->dsp_count(null);
        $t->assert($test_name, $result, 0);


        $t->subheader($ts . 'diff');

        // test the diff supporting functions:
        // ... useful text split
        $test_name = 'str_split_for_humans, words';
        $test_text = 'this text is expected to be split into words';
        $result = $lib->str_split_for_humans($test_text);
        $target = ['this', ' text', ' is', ' expected', ' to', ' be', ' split', ' into', ' words'];
        $t->assert($test_name, $result, $target);
        // the char branch: a long text without a space is no prose, so it is split into chars
        $test_name = 'str_split_for_humans, chars';
        $test_text = 'SplitToCharBecauseNoWordIsExpectedSoLong';
        $result = $lib->str_split_for_humans($test_text);
        $target = ['S', 'p', 'l', 'i', 't', 'T', 'o', 'C', 'h', 'a', 'r', 'B', 'e', 'c', 'a', 'u', 's', 'e', 'N', 'o', 'W', 'o', 'r', 'd', 'I', 's', 'E', 'x', 'p', 'e', 'c', 't', 'e', 'd', 'S', 'o', 'L', 'o', 'n', 'g'];
        $t->assert($test_name, $result, $target);
        $test_text = '{ "json_tag": "text" }';
        $result = $lib->str_split_for_humans($test_text);
        $target = ['json_tag' => 'text'];
        $t->assert("str_split_for_humans, json", $result, $target);
        // a quoted text is a json scalar, which decodes to a string, so it is kept as one part
        $test_text = '"sys_log"';
        $result = $lib->str_split_for_humans($test_text);
        $t->assert("str_split_for_humans, json scalar", $result, [$test_text]);
        $test_text = 'the expected json key "sys_log" is missing';
        $test_name = "... and a diff with a json scalar is a message and no fatal type error";
        $t->assert_true($test_name, $lib->diff_msg($test_text, '"sys_log"') != '');
        /* TODO Prio 2 activate
        $test_text = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Title</title></head><body></body></html>';
        $result = $lib->str_split_for_humans($test_text);
        $target = ['json_tag' => 'text'];
        $t->assert("str_split_for_humans, json", $result, $target);
        */

        // start the test block (tb)
        $tb = 'diff_msg ';

        $test_name = $tb . 'identical string -> no diff';
        $test_result = 'Text';
        $test_target = 'Text';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'empty result -> no diff';
        $test_result = "";
        $test_target = "1";
        $result = $lib->diff_msg($test_result, $test_target);
        // TODO Prio 2 check why this differs in php 8.2
        if ($result == '//-1////+//') {
            $target = '//-1////+//';
        } else {
            $target = '//-1//';
        }
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'null result -> no diff';
        $test_result = null;
        $result = $lib->diff_msg($test_result, $test_target);
        $target = 'The type combination of string and NULL are not expected.';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'empty result array -> no diff';
        $test_result = [];
        $test_target = ['1'];
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '0//+1//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'json result to bool';
        $test_result = $lib->json_is_similar([1, 2], [1]);
        $result = $lib->diff_msg($test_result, true);
        if ($result == '//-1////+//') {
            $target = '//-1////+//';
        } else {
            $target = '//-1//';
        }
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'code text with other beginning';
        $test_result = 'codeStartingWithMoreCharsText';
        $test_target = 'Text';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '//+codeStartingWithMoreChars//Text';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'string with more at the end';
        $test_result = 'Text with more';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = 'Text//+ with more//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'string with other beginning';
        $test_result = 'more begin Text';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '//+more begin// Text';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'string with different middle part';
        $test_result = 'text add end';
        $test_target = 'text less end';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = 'text//- less////+ add// end';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'string with almost empty result';
        $test_result = '""';
        $test_target = '"System Test Word Share"';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '//-"System Test Word Share"////+""//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'string that has caused an error in an earlier version';
        $test_result = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked System Test View Renamed from System Test View Component';
        $test_target = users::SYSTEM_TEST_PARTNER_NAME . ' ';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = 'zukunft.com system test partner//-////+ unlinked System Test View Renamed from System Test View Component//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'identical array';
        $test_result = [1, 2, 3];
        $test_target = [1, 2, 3];
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'position in long html files';
        $test_result = $t->file('/web/system/result.html');
        $test_target = $t->file('/web/system/target.html');
        $result = $lib->diff_msg($test_result, $test_target);
        // the view id in the expected diff is the text of the static result.html fixture, not an id
        // that the code creates, so it stays a literal and does not follow the view id consts
        $target = '381//- href="Test" title=""////+ href="/http/view.php?m=2" title="add new word"//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'json in short json files';
        $test_result = $t->file('/web/system/result_short.json');
        $test_target = $t->file('/web/system/target_short.json');
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '95//+//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'json in json files';
        $test_result = json_decode($t->file('/web/system/result.json'), true);
        $test_target = json_decode($t->file('/web/system/target.json'), true);
        $result = $lib->diff_msg($test_result, $test_target);
        $target = 'pos  5: pos  20: 64//-{"id":65,"code_id":"18excluded","name":"18excluded","comment":""}//, 65//-{"id":66,"code_id":"14excluded","name":"14excluded","comment":""}// ... and 1 more';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'sql files';
        $test_result = $t->file('/web/system/result.sql');
        $test_target = $t->file('/web/system/target.sql');
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '165//-,s.phrase_type_id,l.verb_id////+,
            s.from_phrase_id,
            s.verb_id//931//- LEFT// and 147 more';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'long sql files';
        $test_result = $t->file('/web/system/result_long.sql');
        $test_target = $t->file('/web/system/target_long.sql');
        $result = $lib->diff_msg($test_result, $test_target);
        $target = "6185//- values';////+ values numeric value';//8843//+ phrases numeric value'; COMMENT ON COLUMN user_values_big.user_id// and 8038 more";
        // TODO Prio 2 speed up
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_FILE);

        $test_name = $tb . 'with position in long html string';
        $test_result = '<a href="/http/result_edit.php?id=12&back=1" title="1.55">1.55</a>';
        $test_target = '<a href="/http/value_edit.php?id=12&back=1" title="1.55">1.55</a>';
        $result = $lib->str_diff($test_result, $test_target);
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '<a href="/http///-value////+result//_edit.php?id=12&back=1" title="1.55">1.55</a>';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'with position in short json string';
        $test_result = '{"user_id":2,"sys_log":[{"id":1,"user":"zukunft.com system test"},{"id":2,"user":"zukunft.com system test"}]}';
        $test_target = '{"user_id":3,"sys_log":[{"id":1,"user":"zukunft.com system test"},{"id":2,"user":"zukunft.com system test"}]}';
        $result = $lib->str_diff($test_result, $test_target);
        $target = '//-2zukunft.com system test////+31,zukunft.com system test,2,zukunft.com system test//';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'with position in long json string';
        $test_result = '{"id":1,"time":"2023-01-03T20:59:59+00:00","user_id":0,"text":"the log text that describes the problem for the user or system admin","status":2,"trace":"the technical trace back description for debugging","prg_part":"name of the function that has caused the exception","owner":0}';
        $test_target = '{"id":1,"time":"2023-01-03 20:59:59","user":"zukunft.com system test","text":"the log text that describes the problem for the user or system admin","description":null,"trace":"the technical trace back description for debugging","prg_part":"name of the function that has caused the exception","owner":"","status":"2"}';
        $result = $lib->diff_msg($test_result, $test_target);
        $target = '2//-2023-01-03 20:59:59zukunft.com system test////+2023-01-03T20:59:59+00:000//96//-////+2//197//-2////+0//';
        $t->assert($test_name, $result, $target);

        // a target entry that the result does not have is reported
        $test_name = 'explain_missing entry';
        $test_result = ['a' => '1'];
        $test_target = ['a' => '1', 'b' => '2'];
        $result = $lib->explain_missing($test_result, $test_target);
        $t->assert($test_name, $result, 'b//+2//');

        // negative: a result with more than the target misses nothing
        $test_name = 'explain_missing nothing';
        $test_result = ['a' => '1', 'b' => '2'];
        $test_target = ['a' => '1'];
        $result = $lib->explain_missing($test_result, $test_target);
        $t->assert($test_name, $result, '');

        // a result that is the same as the not expected value is reported
        $test_name = 'not_msg same';
        $result = $lib->not_msg('a', 'a');
        $t->assert($test_name, $result, 'a should not be a');

        // negative: a different result is no message
        $test_name = 'not_msg different';
        $result = $lib->not_msg('a', 'b');
        $t->assert($test_name, $result, '');

        $t->subheader($ts . 'class names');

        // the namespace is removed from the class name
        $test_name = 'class_to_name_pur';
        $result = $lib->class_to_name_pur(component::class);
        $t->assert($test_name, $result, 'component');

        // negative: a name without namespace is not changed
        $test_name = 'class_to_name_pur without namespace';
        $result = $lib->class_to_name_pur('component');
        $t->assert($test_name, $result, 'component');

        // the api name is the class name without namespace
        $test_name = 'class_to_api_name';
        $result = $lib->class_to_api_name(component::class);
        $t->assert($test_name, $result, 'component');

        // negative: a name without namespace is not changed
        $test_name = 'class_to_api_name without namespace';
        $result = $lib->class_to_api_name('component');
        $t->assert($test_name, $result, 'component');

        // the api name of an api class is mapped back to the class
        $test_name = 'api_name_to_class';
        $result = $lib->api_name_to_class('component');
        $t->assert($test_name, $result, component::class);

        // negative: a class that is not used in the api is not found
        $test_name = 'api_name_to_class not in api';
        $result = $lib->api_name_to_class('user_log');
        $t->assert($test_name, $result, 'api class name match missing');

        // a component has a user sandbox
        $test_name = 'class_is_sandbox';
        $result = $lib->class_is_sandbox(component::class);
        $t->assert_true($test_name, $result);

        // negative: the user log has no user sandbox
        $test_name = 'class_is_sandbox user log';
        $result = $lib->class_is_sandbox(user_log::class);
        $t->assert_false($test_name, $result);

        // the id field is the class name with the id extension
        $test_name = 'class_to_id_field';
        $result = $lib->class_to_id_field(component::class);
        $t->assert($test_name, $result, 'component_id');

        // negative: a value is selected by the group id and not by a value id
        $test_name = 'class_to_id_field of a value';
        $result = $lib->class_to_id_field(value::class);
        $t->assert($test_name, $result, group_fields::FLD_ID);

        // the predefined word of a component
        $test_name = 'class_to_word';
        $result = $lib->class_to_word(component::class);
        $t->assert($test_name, $result, words::COMPONENTS);

        // negative: the user log has no predefined word
        $test_name = 'class_to_word user log';
        $result = $lib->class_to_word(user_log::class);
        $t->assert($test_name, $result, '');

        // a verb has its own add message
        $test_name = 'class_to_add_msg_id verb';
        $result = $lib->class_to_add_msg_id(verb_ui::class);
        $msg_code = $result->value;
        $t->assert($test_name, $msg_code, msg_id::VERB_ADD->value);

        // negative: a class without its own add message gets the general one
        $test_name = 'class_to_add_msg_id default';
        $result = $lib->class_to_add_msg_id(component::class);
        $msg_code = $result->value;
        $t->assert($test_name, $msg_code, msg_id::ADD->value);

        // the table name is the class name in the plural
        $test_name = 'class_to_table';
        $result = $lib->class_to_table(component::class);
        $t->assert($test_name, $result, 'components');

        // negative: the user log table keeps the singular name
        $test_name = 'class_to_table user log';
        $result = $lib->class_to_table(user_log::class);
        $t->assert($test_name, $result, 'user_log');

        // the class name is translated via its table
        $test_name = 'class_to_name_translated';
        $lan = language_codes::EN;
        $result = $lib->class_to_name_translated(component::class, $lan);
        $t->assert($test_name, $result, 'Components');

        // negative: a class without table falls back to the plain class name
        $test_name = 'class_to_name_translated without table';
        $result = $lib->class_to_name_translated(user_message::class);
        $t->assert($test_name, $result, 'user_message');

        // the changes of a ref are in the ref table and the user ref table
        $test_name = 'ui_class_to_table_id_list';
        $cng_tbl = $sys->typ_lst->cng_tbl;
        $tbl_id = $cng_tbl->id(change_tables::REF);
        $usr_tbl_id = $cng_tbl->id(change_tables::REF_USR);
        $result = $lib->ui_class_to_table_id_list(ref_ui::class);
        $t->assert($test_name, $result, [$tbl_id, $usr_tbl_id]);

        // negative: a verb has no user table, so only the verb table is listed
        $test_name = 'ui_class_to_table_id_list without user table';
        $tbl_id = $cng_tbl->id(change_tables::VERB);
        $result = $lib->ui_class_to_table_id_list(verb_ui::class);
        $t->assert($test_name, $result, [$tbl_id]);

        $t->subheader($ts . 'var filter');

        $test_name = $tb . 'test valid local paths that should be allowed';
        $result = $lib->filter_var('/home');
        $target = '/home';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'test deep path with query parameters';
        $result = $lib->filter_var('/products/view?id=123');
        $target = '/products/view?id=123';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'block protocol-relative urls (double slash)';
        $result = $lib->filter_var('//malicious-site.com');
        $target = '';
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'block full external urls with protocol';
        $result = $lib->filter_var('https://malicious-site.com/login');
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'block strings not starting with a slash';
        $result = $lib->filter_var('dashboard');
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'block single slash (root) if regex requires following char';
        $result = $lib->filter_var('/');
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'handle null input by returning default';
        $result = $lib->filter_var(null);
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'handle empty string input';
        $result = $lib->filter_var('');
        $t->assert($test_name, $result, $target);

        $test_name = $tb . 'ensure illegal url characters are stripped';
        $result = $lib->filter_var('/path with spaces');
        $target = '/pathwithspaces';
        $t->assert($test_name, $result, $target);


        $t->subheader($ts . 'json');

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
        // empty fields and empty arrays are removed on every level
        $test_name = 'json_clean removes the empty fields';
        $json_array = json_decode($json_text, true);
        $result = $lib->json_clean($json_array);
        $target = json_decode($json_target, true);
        $t->assert($test_name, $result, $target);

        // plausibility check: the empty start field in the sub array is removed as well
        $test_name = 'json_clean removes the empty field of a sub array';
        $result = $lib->json_clean($json_array);
        $check = json_decode($json_check, true);
        $t->assert_not($test_name, $result, $check);

        // negative: a json without empty fields is returned unchanged
        $test_name = 'json_clean keeps a json without empty fields';
        $json_full = json_decode($json_target, true);
        $result = $lib->json_clean($json_full);
        $t->assert($test_name, $result, $json_full);

        // recursive count
        $result = $lib->count_recursive($json_array, 0);
        $t->assert("count_recursive - count level 0", $result, 0);
        $result = $lib->count_recursive($json_array, 1);
        $t->assert("count_recursive - count level 0", $result, 4);
        $result = $lib->count_recursive($json_array, 3);
        $t->assert("count_recursive - count level 0", $result, 5);
        $result = $lib->count_recursive($json_array, 20);
        $t->assert("count_recursive - count level 0", $result, 8);

        $json_text = file_get_contents(test_files::IMPORT_WIKI_DEMOCRACY);
        $json_array = json_decode($json_text, true);
        $result = $lib->count_recursive($json_array, 3);
        $t->assert("count_recursive - count level 0", $result, 179);

        // recursive diff
        $result = json_encode($lib->array_recursive_diff(
            json_decode($json_haystack, true),
            json_decode($json_needle, true)));
        $t->assert("array_recursive_diff - contains", $result, '[]');
        $result = json_encode($lib->array_recursive_diff(
            json_decode($json_haystack, true),
            json_decode($json_needle_without_array, true)));
        $t->assert("array_recursive_diff - contains without array", $result, '[]');
        $result = json_encode($lib->array_recursive_diff(
            json_decode($json_haystack_with_diff, true),
            json_decode($json_needle, true)));
        $expected = '{"array":{"text field":"text value"}}';
        $t->assert("array_recursive_diff - diff expected", $result, $expected);
        $result = json_encode($lib->array_recursive_diff(
            json_decode($json_haystack_without_match, true),
            json_decode($json_needle, true)));
        $expected = '{"array":{"id":1,"text field":"text value","0":{"id":1,"text field":"text value"}}}';
        $t->assert("array_recursive_diff - without match", $result, $expected);
        $result = json_encode($lib->array_recursive_diff(
            json_decode($json_haystack_without_array, true),
            json_decode($json_needle, true)));
        $expected = '{"array":[{"id":1,"text field":"text value"}]}';
        $t->assert("array_recursive_diff - without array", $result, $expected);

        // the haystack may have additional fields and the needle empty ones
        $test_name = 'json_contains the needle';
        $needle = ['a' => 'x', 'empty' => ''];
        $haystack = ['b' => 'y', 'a' => 'x'];
        $result = $lib->json_contains($needle, $haystack);
        $t->assert_true($test_name, $result);

        // negative: a changed field value is not contained
        $test_name = 'json_contains not with a diff';
        $haystack_diff = ['a' => 'z', 'b' => 'y'];
        $result = $lib->json_contains($needle, $haystack_diff);
        $t->assert_false($test_name, $result);

        // a null needle is the same as an empty json and contained in any json
        $test_name = 'json_contains a null needle';
        $result = $lib->json_contains(null, $haystack);
        $t->assert_true($test_name, $result);

        // the developer format escapes the slash
        $test_name = 'json_for_dev pretty prints';
        $json_dev = ['url' => 'a/b'];
        $result = $lib->json_for_dev($json_dev);
        $t->assert($test_name, $result, "{\n    \"url\": \"a\\/b\"\n}");

        // negative: an empty array is an empty json array
        $test_name = 'json_for_dev of an empty array';
        $result = $lib->json_for_dev([]);
        $t->assert($test_name, $result, '[]');

        // the human format keeps the slash and the umlaut
        $test_name = 'json_for_humans keeps slash and umlaut';
        $json_human = ['url' => 'ä/b'];
        $result = library::json_for_humans($json_human);
        $t->assert($test_name, $result, "{\n    \"url\": \"ä/b\"\n}");

        // negative: an empty array is an empty json array
        $test_name = 'json_for_humans of an empty array';
        $result = library::json_for_humans([]);
        $t->assert($test_name, $result, '[]');

        // the json to merge is added as a new node
        $test_name = 'json_merge adds the node';
        $json_base = ['a' => 1];
        $json_add = ['b' => 2];
        $target = ['a' => 1, 'n' => ['b' => 2]];
        $result = $lib->json_merge($json_base, $json_add, 'n');
        $t->assert($test_name, $result, $target);

        // negative: an existing node is replaced, not extended
        $test_name = 'json_merge replaces an existing node';
        $json_base = ['n' => ['a' => 1]];
        $target = ['n' => ['b' => 2]];
        $result = $lib->json_merge($json_base, $json_add, 'n');
        $t->assert($test_name, $result, $target);

        // the json text to merge is added as a new node
        $test_name = 'json_merge_str adds the node';
        $result = $lib->json_merge_str('{"a":1}', '{"b":2}', 'n');
        $t->assert($test_name, $result, '{"a":1,"n":{"b":2}}');

        // negative: an empty json to merge adds an empty node
        $test_name = 'json_merge_str with an empty json';
        $result = $lib->json_merge_str('{"a":1}', '[]', 'n');
        $t->assert($test_name, $result, '{"a":1,"n":[]}');

        // line feeds and the spaces between the json tokens are removed
        $test_name = 'trim_json removes the spaces';
        $json_spaced = '[
  { "a": "x" },
  { "b": "y" }
]';
        $result = $lib->trim_json($json_spaced);
        $t->assert($test_name, $result, '[{"a":"x"},{"b":"y"}]');

        // negative: a compact json is unchanged and the space within a value is kept
        $test_name = 'trim_json keeps a compact json';
        $json_compact = '{"a":"x y"}';
        $result = $lib->trim_json($json_compact);
        $t->assert($test_name, $result, $json_compact);


        $t->subheader($ts . 'json remove volatile fields');

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

        // the log function list grows with every script and error location that logs,
        // so it can never match a stored fixture and is dropped from the type list compare
        $json_with_log_fnc = [json_fields::LIST_SYS_LOG_FUNCTIONS => [['id' => 1]], 'a' => 1];
        $result = $t->json_remove_volatile($json_with_log_fnc);
        $t->assert("json remove volatile log function list", $result, ['a' => 1]);

        // the tag and attribute names of the token field are matched case-insensitive
        $test_name = 'fix_volatile_in_html of an upper case token tag';
        $token = test_const::DUMMY_SESSION_TOKEN;
        $html = '<INPUT NAME="token" VALUE="abc">';
        $target = '<INPUT NAME="token" VALUE="' . $token . '">';
        $result = $lib->fix_volatile_in_html($html, $token);
        $t->assert($test_name, $result, $target);

        // negative: a field whose name only starts with token is not changed
        $test_name = 'fix_volatile_in_html keeps a token_old field';
        $html = '<input name="token_old" value="abc">';
        $result = $lib->fix_volatile_in_html($html, $token);
        $t->assert($test_name, $result, $html);

        // the creation time of a user changes with every test run
        $test_name = 'is_volatile_db_field user created';
        $usr_class = user::class;
        $created = user_db::FLD_CREATED;
        $result = $lib->is_volatile_db_field($usr_class, $created);
        $t->assert_true($test_name, $result);

        // negative: the same field of a class not listed is not volatile
        $test_name = 'is_volatile_db_field component created';
        $result = $lib->is_volatile_db_field(component::class, $created);
        $t->assert_false($test_name, $result);


        $t->subheader($ts . 'user message');

        $msg = new user_message();
        $t->assert("user_message - default ok", $msg->is_ok(), true);

        $msg = new user_message();
        $msg->add_message_text('first message text');
        $t->assert("construct with message", $msg->get_message(), 'first message text');
        $t->assert("if a message text is given, the result is by default NOT ok", $msg->is_ok(), false);

        $msg->add_message_text('second message text');
        $t->assert("after adding a message the first message stays the same", $msg->get_message(), 'first message text');
        $t->assert("... and the second message can be shown", $msg->get_message(2), 'second message text');
        $t->assert("... which is also the last message", $msg->get_last_message(), 'second message text');
        // TODO Prio 1 activate
        //$t->assert("a too high position simply returns an empty message", $usr_msg->get_message(3), 'user message position 2 not found');
        $t->assert("a too high position simply returns an empty message", $msg->get_message(3), '');

        $msg_2 = new user_message();
        $msg_2->add_message_text('');
        $t->assert("adding an empty test does not change the status", $msg_2->is_ok(), true);
        $msg_2->add_message_text('error text');
        $t->assert("but adding an error text does", $msg_2->is_ok(), false);

        $msg->merge($msg_2);
        $t->assert("last message of the combined message should be from msg_2", $msg->get_last_message(), 'error text');


        $t->subheader($ts . 'php code');

        // a use line is split into the class and the namespace without the alias
        $test_name = 'php_code_use with alias';
        $code = ['use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;'];
        $target = [['paths', 'Zukunft\ZukunftCom\main\php\web\const']];
        $result = $lib->php_code_use($code);
        $t->assert($test_name, $result, $target);

        // negative: an include line is no use line
        $test_name = 'php_code_use without use line';
        $code_inc = ["include_once paths::SHARED . 'library.php';"];
        $result = $lib->php_code_use($code_inc);
        $t->assert($test_name, $result, []);

        // only the use lines are returned in sorted order, which the callers implode
        $test_name = 'php_code_use_sorted';
        $code = ['use b\y;', 'class x', 'use a\z;'];
        $target = ['use a\z;', 'use b\y;'];
        $result = $lib->php_code_use_sorted($code);
        $sorted = array_values($result);
        $t->assert($test_name, $sorted, $target);

        // negative: code without use lines
        $test_name = 'php_code_use_sorted without use line';
        $result = $lib->php_code_use_sorted($code_inc);
        $t->assert($test_name, $result, []);

        // a use line is converted to the matching include line
        $test_name = 'php_code_use_converted';
        $code = ['use Zukunft\ZukunftCom\main\php\shared\library;'];
        $target = ["include_once paths::SHARED . 'library.php';"];
        $result = $lib->php_code_use_converted($code);
        $t->assert($test_name, $result, $target);

        // negative: a namespace without path const is named in the include line
        $test_name = 'php_code_use_converted of an unknown path';
        $code = ['use Foo\Bar;'];
        $target = ["include_once missing path for Foo . 'Bar.php';"];
        $result = $lib->php_code_use_converted($code);
        $t->assert($test_name, $result, $target);

        // an include line is split into the class and the path const
        $test_name = 'php_code_include';
        $target = [['library', 'paths::SHARED']];
        $result = $lib->php_code_include($code_inc);
        $t->assert($test_name, $result, $target);

        // a commented include line is listed too
        $test_name = 'php_code_include commented';
        $code = ["//include_once paths::SHARED . 'library.php';"];
        $result = $lib->php_code_include($code);
        $t->assert($test_name, $result, $target);

        // negative: a use line is no include line
        $test_name = 'php_code_include without include line';
        $code = ['use Foo\Bar;'];
        $result = $lib->php_code_include($code);
        $t->assert($test_name, $result, []);

        // the function is listed with the section and the first docblock line
        $test_name = 'php_code_function';
        $code = [
            '    /*',
            '     * load',
            '     */',
            '    /**',
            '     * load a sample',
            '     */',
            '    function load_sample(): bool',
        ];
        $fnc = ['name' => 'load_sample', 'section' => 'load', 'description' => 'load a sample'];
        $target = ['load_sample' => $fnc];
        $result = $lib->php_code_function($code);
        $t->assert($test_name, $result, $target);

        // negative: code without a function
        $test_name = 'php_code_function without function';
        $result = $lib->php_code_function($code_inc);
        $t->assert($test_name, $result, []);

        // the class is listed with the parent, the header path and the var name with the description
        $test_name = 'php_code_parent';
        $code = [
            '    word.php - the main word object',
            '    $wrd is the suggested var name',
            'use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_typed;',
            'class word extends sandbox_typed',
        ];
        $target = ['word' => ['sandbox_typed', 'word', '$wrd - the main word object']];
        $result = $lib->php_code_parent($code, '', 'src/main/php/cfg/word/word.php');
        $t->assert($test_name, $result, $target);

        // negative: code without a class
        $test_name = 'php_code_parent without class';
        $result = $lib->php_code_parent($code_inc, '', 'src/main/php/cfg/word/word.php');
        $t->assert($test_name, $result, []);

        // the class name is the part before extends
        $test_name = 'php_class_from_code';
        $code = ['namespace Zukunft\ZukunftCom\main\php\cfg\word;', 'class word extends sandbox_typed'];
        $result = $lib->php_class_from_code($code);
        $t->assert($test_name, $result, 'word');

        // negative: code without a class
        $test_name = 'php_class_from_code without class';
        $result = $lib->php_class_from_code($code_inc);
        $t->assert($test_name, $result, '');

        // the namespace is the part between namespace and the semicolon
        $test_name = 'php_namespace_from_code';
        $result = $lib->php_namespace_from_code($code);
        $t->assert($test_name, $result, 'Zukunft\ZukunftCom\main\php\cfg\word');

        // negative: code without a namespace
        $test_name = 'php_namespace_from_code without namespace';
        $result = $lib->php_namespace_from_code($code_inc);
        $t->assert($test_name, $result, '');

        // a known namespace is converted to the path const
        $test_name = 'php_path_convert';
        $result = $lib->php_path_convert('Zukunft\ZukunftCom\main\php\shared');
        $t->assert($test_name, $result, 'paths::SHARED');

        // negative: an unknown namespace is reported
        $test_name = 'php_path_convert of an unknown path';
        $result = $lib->php_path_convert('Foo');
        $t->assert($test_name, $result, 'missing path for Foo');

        // a listed function name has its fixed section
        $test_name = 'php_expected_function_section listed';
        $result = $lib->php_expected_function_section('api_json_array');
        $t->assert($test_name, $result, 'api');

        // a function name that is not listed gets the section of its prefix
        $test_name = 'php_expected_function_section by prefix';
        $result = $lib->php_expected_function_section('load_by_name');
        $t->assert($test_name, $result, 'load');

        // negative: a name that is neither listed nor has a known prefix has no section
        $test_name = 'php_expected_function_section unknown';
        $result = $lib->php_expected_function_section('convert');
        $t->assert($test_name, $result, '');


        $t->subheader($ts . 'cache age');

        // a time far in the past is older than any max cache age
        $test_name = 'is_older_than_cache_max_age of an old time';
        $old_time = $lib->get_datetime('2000-01-01 00:00:00');
        $result = $lib->is_older_than_cache_max_age($old_time);
        $t->assert_true($test_name, $result);

        // negative: a time in the future is never older than the max cache age
        $test_name = 'is_older_than_cache_max_age of a future time';
        $future_time = $lib->get_datetime('2999-01-01 00:00:00');
        $result = $lib->is_older_than_cache_max_age($future_time);
        $t->assert_false($test_name, $result);
    }

    private function assert_sql_format(string $test_name, string $file_name, all_tests $t): void
    {
        $lib = new library();
        $target = $t->file($file_name);
        $result = $lib->sql_format($lib->trim($target));
        $t->assert($test_name . ' recreates the formatted update log function', $result, $target);
        $t->assert($test_name . ' is idempotent', $lib->sql_format($target), $target);
    }

    /**
     * check that the compact json format of the given resource file can be recreated from the
     * standard php json format, that reformatting does not change it again and that the data
     * itself is not changed by the formatting
     *
     * @param string $test_name the description of the test
     * @param string $file_name the resource file with the expected compact format
     * @param all_tests $t the test environment
     * @return void
     */
    private function assert_json_compact_format(string $test_name, string $file_name, all_tests $t): void
    {
        $lib = new library();
        $target = $t->file($file_name);
        // the standard php pretty print is the input from which the compact format is created
        $std = json_encode(json_decode($target, true),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $t->assert($test_name . ' recreates the compact format',
            $lib->json_compact_format($std), $target);
        $t->assert($test_name . ' is idempotent',
            $lib->json_compact_format($target), $target);
        $t->assert($test_name . ' does not change the data',
            json_encode(json_decode($lib->json_compact_format($std), true)),
            json_encode(json_decode($target, true)));
    }

}