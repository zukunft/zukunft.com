<?php

/*

    test/unit/sys_log_tests.php - unit testing of the user log functions
    ---------------------------
  

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

include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_SYSTEM . 'system_time.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_slow_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time_list;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time_slow_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;
use DateTimeInterface;

class sys_log_tests
{

    // const used for system testing
    CONST string TV_TIME = '2023-01-03T20:59:59+0100'; // time for unit tests
    CONST string TV_TIME_TWO = '2023-01-03T21:58:58+0100'; // a second time for unit tests
    CONST string TV_TIME_ASSIGNED = '2023-01-04T09:12:34+0100'; // a third time for unit tests
    CONST string TV_TIME_CLOSED = '2023-01-05T10:23:45+0100'; // a fourth time for unit tests
    CONST string TV_LOG_TEXT = 'the log text that describes the problem for the user or system admin';
    CONST string TV_LOG_TRACE = 'the technical trace back description for debugging';
    CONST string TV_FUNC_NAME = 'name of the function that has caused the exception';
    CONST string TV_SOLVE_ID = 'code id of the suggested solver of the problem';
    CONST string TV_DESCRIPTION = 'the error has been replicated and the fix will be deployed';
    CONST string T2_TIME = '2023-01-03T21:45:01+0100'; // time for unit tests
    CONST string T2_LOG_TEXT = 'the log 2 text that describes the problem for the user or system admin';
    CONST string T2_LOG_TRACE = 'the technical trace 2 back description for debugging';
    CONST string T2_FUNC_NAME = 'name 2 of the function that has caused the exception';
    CONST string THROTTLE_FILE_PREFIX = 'zukunft_test_sys_log_throttle_';
    CONST string T2_SOLVE_ID = 'code id 2 of the suggested solver of the problem';
    // a pause much longer than two following statements, so the step time test never depends on the machine speed
    // in micro seconds, because usleep() takes them
    CONST int STEP_PAUSE_USEC = 100000;
    CONST int USEC_PER_SEC = 1000000;
    // derived, so the compared pause never differs from the slept one
    CONST float STEP_PAUSE_SEC = self::STEP_PAUSE_USEC / self::USEC_PER_SEC;
    // a request url and request times below, between and above the fallback log and detail limits
    CONST string TV_URL = '/api/sysLogList/index.php';
    CONST float TV_FAST_SEC = 1.0;
    CONST int TV_SLOW_MS = 1600;
    CONST float TV_SLOW_SEC = self::TV_SLOW_MS / system_time_list::MILLISECONDS_PER_SECOND;
    CONST float TV_VERY_SLOW_SEC = 2.5;
    // the measured steps of the very slow request
    CONST int TV_STEP_DB_READ_MS = 1500;
    CONST float TV_STEP_DB_READ_SEC = self::TV_STEP_DB_READ_MS / system_time_list::MILLISECONDS_PER_SECOND;
    CONST float TV_STEP_LOAD_TYPES_SEC = 0.4;
    CONST string TV_STEP_UNKNOWN = 'step without system time type';
    // the start time of a slow request as read from the database
    CONST string TV_TIME_DB = '2023-01-03 20:59:59';

    function run(test_cleanup $t): void
    {

        global $sys;

        // init
        $sc = new sql_creator();
        $t_sys = new test_sys_log($t);
        $msg = new user_message($t->usr1);
        $t->name = 'sys_log->';
        $t->resource_path = 'db/sys_log/';

        // start the test section (ts)
        $ts = 'unit log ';
        $t->header($ts);

        $t->subheader($ts . 'critical error output escaping');

        // a critical error echoes the (possibly request-derived) message to the user, so it must be
        // html-escaped or a crafted value (e.g. a url mask reaching log_err) could inject script into
        // the error response (reflected xss); see text_log_functions.php::critical_error_html
        $xss_payload = '<script>alert(1)</script>';
        $err_html = \critical_error_html($xss_payload);
        $test_name = 'the critical error html escapes an injected script tag';
        $t->assert_text_contains($test_name, $err_html, htmlspecialchars($xss_payload, ENT_QUOTES));
        $test_name = 'the critical error html does not echo the raw script tag';
        $t->assert_text_not_contains($test_name, $err_html, $xss_payload);
        // the internal function / file name is not disclosed to the user (information disclosure);
        // it stays in the sys_log for the admin, so the user-facing html has no 'by <function>' part
        $test_name = 'the critical error html does not disclose an internal function or file name';
        $t->assert_text_not_contains($test_name, $err_html, ' by ');

        $t->subheader($ts . 'debug step time');

        // a debug line shows the duration of its step, because its timestamp shows the runtime already
        $log_txt = new text_log();
        usleep(self::STEP_PAUSE_USEC);
        $test_name = 'the step time includes the pause since the last line';
        $t->assert_true($test_name, (float)$log_txt->since_last() >= self::STEP_PAUSE_SEC);
        $log_txt->time_stamp_lines(self::TV_LOG_TEXT);
        // negative: after a new line the step time starts again and is not the runtime
        $test_name = '... and starts again with the next line';
        $t->assert_true($test_name, (float)$log_txt->since_last() < self::STEP_PAUSE_SEC);
        // negative: an empty text gets no time stamped line
        $test_name = 'an empty text has no time stamp line';
        $stamped_empty = $log_txt->time_stamp_lines('');
        $t->assert($test_name, $stamped_empty, '');

        $t->subheader($ts . 'sys_log insert throttle');

        // the cross-request throttle caps the sys_log inserts per time window so that a flood of
        // requests each logging a distinct error cannot grow the sys_log table unbounded (dos);
        // see text_log_functions.php::sys_log_insert_allowed
        $state_file = tempnam(sys_get_temp_dir(), self::THROTTLE_FILE_PREFIX);
        $note_file = tempnam(sys_get_temp_dir(), self::THROTTLE_FILE_PREFIX);
        $now = strtotime(self::TV_TIME);
        $allowed = true;
        for ($i = 0; $i < text_log::INSERT_LIMIT; $i++) {
            $allowed = $allowed && \sys_log_insert_allowed($now, $state_file, $note_file);
        }
        $test_name = 'all inserts up to the limit are allowed';
        $t->assert_true($test_name, $allowed);
        $test_name = 'the insert above the limit is rejected';
        $t->assert_false($test_name, \sys_log_insert_allowed($now, $state_file, $note_file));
        $test_name = 'the start of the suppression is noted in the text error log';
        $t->assert_text_contains($test_name, file_get_contents($note_file), 'suppressed');
        $test_name = 'a new time window allows inserts again';
        $t->assert_true($test_name,
            \sys_log_insert_allowed($now + text_log::INSERT_WINDOW_SEC, $state_file, $note_file));
        $test_name = 'a broken state file allows the insert to not lose a log entry';
        file_put_contents($state_file, 'not a json');
        $t->assert_true($test_name, \sys_log_insert_allowed($now, $state_file, $note_file));
        unlink($state_file);
        unlink($note_file);

        $t->subheader($ts . 'system sql setup');
        $log = new sys_log();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);

        // sql to load one error by id
        $err = new sys_log();
        $t->assert_sql_by_id($sc, $err);

        $t->subheader($ts . 'system log SQL write');
        $log = $t_sys->sys_log();
        $t->assert_sql_insert($sc, $log);
        $log_db = $t_sys->sys_log_two();
        $log = $t_sys->sys_log_filled();
        $t->assert_sql_update($sc, $log, $log_db, [sql_type::LOG]);
        $log_db = $t_sys->sys_log_filled();
        $log = $t_sys->sys_log_closed();
        $t->assert_sql_update($sc, $log, $log_db, [sql_type::LOG]);


        $t->subheader($ts . 'api');

        $t_sys = new test_sys_log($t);
        $log_lst = $t_sys->sys_log_list();
        $t->assert_api($log_lst, '', [api_types::HEADER, api_types::INCL_COMPONENTS]);

        $t->subheader($ts . 'api json array');

        // the description of a log entry is sent if it is set
        $test_name = 'description of a filled entry';
        $log = $t_sys->sys_log_filled();
        $vars = $log->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::DESCRIPTION] ?? '', self::TV_DESCRIPTION);
        $msg->reset();

        // negative: an empty description is filtered out
        $test_name = 'no description of an open entry';
        $log = $t_sys->sys_log();
        $vars = $log->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::DESCRIPTION, $vars);
        $msg->reset();

        // the solver of an assigned log entry
        $test_name = 'solver of an assigned entry';
        $log = $t_sys->sys_log_two();
        $vars = $log->api_json_array([], $msg);
        $solver_id = $t->usr_admin->id();
        $t->assert($test_name, $vars[json_fields::SOLVER], $solver_id);
        $msg->reset();

        // negative: an open log entry without solver sends no solver
        $test_name = 'no solver of an open entry';
        $log = $t_sys->sys_log();
        $vars = $log->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::SOLVER, $vars);
        $msg->reset();


        $t->subheader($ts . 'list add');

        // a log entry is added to the list
        $test_name = 'add a log entry';
        $log_lst = new sys_log_list();
        $log_lst->add($t_sys->sys_log());
        $cnt = $log_lst->count();
        $t->assert($test_name, $cnt, 1);

        // negative: the same log entry again is not added a second time
        $test_name = 'add a log entry twice';
        $log_lst = new sys_log_list();
        $log_lst->add($t_sys->sys_log());
        $log_lst->add($t_sys->sys_log());
        $cnt = $log_lst->count();
        $t->assert($test_name, $cnt, 1);


        $t->subheader($ts . 'system time add');

        // a new step is added with its time
        $test_name = 'add a step';
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add(self::TV_STEP_DB_READ_SEC, system_time_type::DB_READ);
        $report = $sys_tim_lst->report();
        $t->assert_text_contains($test_name, $report,
            system_time_type::DB_READ . ': ' . round(self::TV_STEP_DB_READ_SEC, 4) . ' sec');

        // negative: a step that is already in the list is not added again, but its time is summed
        $test_name = 'add a known step';
        $sys_tim_lst = $t_sys->system_time_steps();
        $sys_tim_lst->add(self::TV_STEP_DB_READ_SEC, system_time_type::DB_READ);
        $report = $sys_tim_lst->report();
        $t->assert_text_contains($test_name, $report,
            system_time_type::DB_READ . ': ' . round(2 * self::TV_STEP_DB_READ_SEC, 4) . ' sec');


        $t->subheader($ts . 'system time type sql setup');
        $sys_exe_typ = new system_time_type('');
        $t->assert_sql_table_create($sys_exe_typ);
        $t->assert_sql_index_create($sys_exe_typ);

        $t->subheader($ts . 'system time sql setup');
        $sys_exe = new system_time();
        $t->assert_sql_table_create($sys_exe);
        $t->assert_sql_index_create($sys_exe);
        $t->assert_sql_foreign_key_create($sys_exe);

        $t->subheader($ts . 'system time sections');
        // a section within a section within a section e.g. a db read of a related load of the api json
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->switch(system_time_type::MAP_JSON);
        $sys_tim_lst->switch(system_time_type::LOAD_RELATED);
        $sys_tim_lst->switch(system_time_type::DB_READ);
        $test_name = 'the innermost section is counted';
        $t->assert($test_name, $sys_tim_lst->current(), system_time_type::DB_READ);
        $sys_tim_lst->switch();
        $test_name = '... and the end of it returns to the section around it';
        $t->assert($test_name, $sys_tim_lst->current(), system_time_type::LOAD_RELATED);
        $sys_tim_lst->switch();
        $test_name = '... and the end of that to the outermost section';
        $t->assert($test_name, $sys_tim_lst->current(), system_time_type::MAP_JSON);
        // a section that starts the section that is already counted e.g. a db read within a db read
        $sys_tim_lst->switch(system_time_type::MAP_JSON);
        $sys_tim_lst->switch();
        $test_name = 'the end of a repeated section returns to the same section';
        $t->assert($test_name, $sys_tim_lst->current(), system_time_type::MAP_JSON);
        $sys_tim_lst->switch();
        $test_name = '... and the end of the outermost section stops the counting';
        $t->assert($test_name, $sys_tim_lst->current(), '');

        // each test header checks that the tests before have ended their sections, because a
        // section left open counts the time of all following tests e.g. as map_json
        $t->subheader($ts . 'open time section');
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->switch(system_time_type::DEFAULT);
        $test_name = 'the base section of the test run is no problem';
        $t->assert($test_name, test_base::time_section_problem($sys_tim_lst), '');
        $sys_tim_lst->switch(system_time_type::MAP_JSON);
        $test_name = 'a section left open is reported with its name';
        $t->assert_text_contains($test_name, test_base::time_section_problem($sys_tim_lst), system_time_type::MAP_JSON);
        $sys_tim_lst->switch();
        $sys_tim_lst->switch();
        $test_name = '... and also a section ended once too often';
        $t->assert_true($test_name, test_base::time_section_problem($sys_tim_lst) != '');
        // the api wait that the frontend has measured is moved out of the url to html time
        $sys_tim_lst = $t_sys->system_time_steps();
        $sys_tim_lst->move(self::TV_STEP_LOAD_TYPES_SEC, system_time_type::DB_READ, system_time_type::API_WAIT);
        $test_name = 'a moved time is shown in the new category';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::API_WAIT . ': ' . round(self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec');
        $test_name = '... and no longer in the category that has counted it';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::DB_READ . ': ' . round(self::TV_STEP_DB_READ_SEC - self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec');
        // negative: a time of zero is not moved, so the new category is not created
        $test_name = 'a time of zero is not moved';
        $sys_tim_lst = $t_sys->system_time_steps();
        $sys_tim_lst->move(0.0, system_time_type::DB_READ, system_time_type::API_WAIT);
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), system_time_type::API_WAIT . ': ');
        // the steps that the backend has named in the json of its answer split the api wait
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add(self::TV_SLOW_SEC, system_time_type::API_WAIT);
        $sys_tim_lst->split(system_time_type::API_WAIT, [system_time_type::DB_READ => self::TV_STEP_DB_READ_SEC]);
        $test_name = 'the api wait of the report keeps the time of its backend steps';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::API_WAIT . ': ' . round(self::TV_SLOW_SEC, 4) . ' sec' . system_time_list::REPORT_LINE);
        $test_name = '... and shows the backend steps indented below it with the longest first';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::API_WAIT . ': ' . round(self::TV_SLOW_SEC, 4) . ' sec'
            . system_time_list::REPORT_LINE . system_time_list::REPORT_INDENT
            . system_time_type::DB_READ . ': ' . round(self::TV_STEP_DB_READ_SEC, 4) . ' sec');
        // the rest of 0.1 sec is below the limit, so it is only part of the sum of the small steps
        $test_name = '... and the steps below the limit summed in one line';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_list::REPORT_INDENT . system_time_list::small_steps_name() . ': '
            . round(self::TV_SLOW_SEC - self::TV_STEP_DB_READ_SEC, 4) . ' sec');
        // negative: a step below the limit is not shown on its own
        $test_name = '... but not on their own';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(),
            system_time_list::REPORT_INDENT . system_time_list::GROUP_REST . ': ');
        $test_name = '... but in the report of the section, which shows every step with the longest first';
        $t->assert_text_contains($test_name, $sys_tim_lst->section_report(),
            system_time_type::API_WAIT . ': ' . round(self::TV_SLOW_SEC, 4) . ' sec ['
            . system_time_type::DB_READ . ': ' . round(self::TV_STEP_DB_READ_SEC, 4) . ' sec, '
            . system_time_list::GROUP_REST . ': ' . round(self::TV_SLOW_SEC - self::TV_STEP_DB_READ_SEC, 4) . ' sec]');
        // negative: an answer without backend steps leaves the api wait as it is
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add(self::TV_SLOW_SEC, system_time_type::API_WAIT);
        $sys_tim_lst->split(system_time_type::API_WAIT, []);
        $test_name = 'without backend steps the api wait is not split';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), system_time_list::GROUP_REST);
        $test_name = '... also not in the report of the section';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->section_report(), system_time_list::GROUP_REST);
        $test_name = '... and has no line of small steps';
        $small_name = system_time_list::small_steps_name();
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), $small_name);
        // the render time of each component type splits the url to html time in the same way
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add(self::TV_SLOW_SEC, system_time_type::URL_TO_HTML);
        $sys_tim_lst->split(system_time_type::URL_TO_HTML, [component_types::TEXT => self::TV_STEP_DB_READ_SEC]);
        $test_name = 'the url to html time of the report shows the components behind it';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::URL_TO_HTML . ': ' . round(self::TV_SLOW_SEC, 4) . ' sec'
            . system_time_list::REPORT_LINE . system_time_list::REPORT_INDENT
            . component_types::TEXT . ': ' . round(self::TV_STEP_DB_READ_SEC, 4) . ' sec');
        // negative: a page without timed components leaves the url to html time as it is
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add(self::TV_SLOW_SEC, system_time_type::URL_TO_HTML);
        $sys_tim_lst->split(system_time_type::URL_TO_HTML, []);
        $test_name = 'without timed components the url to html time is not split';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), system_time_list::GROUP_REST);
        // the times of the frontend are booked out of the category counted when they have been measured
        // e.g. a test that renders without url to html
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->switch(system_time_type::DEFAULT);
        // the test takes longer than its api wait and html creation together, so that a rest remains
        $sys_tim_lst->add(self::TV_VERY_SLOW_SEC, system_time_type::DEFAULT);
        $sys_tim_lst->book_frontend(self::TV_STEP_DB_READ_SEC, [system_time_type::DB_READ => self::TV_STEP_LOAD_TYPES_SEC],
            [component_types::TEXT => self::TV_STEP_LOAD_TYPES_SEC]);
        $test_name = 'the frontend wait is booked as api wait with its backend steps';
        $t->assert_text_contains($test_name, $sys_tim_lst->section_report(),
            system_time_type::API_WAIT . ': ' . round(self::TV_STEP_DB_READ_SEC, 4) . ' sec ['
            . system_time_list::GROUP_REST . ': ' . round(self::TV_STEP_DB_READ_SEC - self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec, '
            . system_time_type::DB_READ . ': ' . round(self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec]');
        $test_name = '... and the html creation as a step of the category of the test';
        $t->assert_text_contains($test_name, $sys_tim_lst->section_report(),
            system_time_type::DEFAULT . ': ' . round(self::TV_VERY_SLOW_SEC - self::TV_STEP_DB_READ_SEC, 4) . ' sec ['
            . system_time_list::GROUP_REST . ': '
            . round(self::TV_VERY_SLOW_SEC - self::TV_STEP_DB_READ_SEC - self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec, '
            . component_types::TEXT . ': ' . round(self::TV_STEP_LOAD_TYPES_SEC, 4) . ' sec]');
        // negative: without a counted category the times have not been counted, so nothing is booked
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->book_frontend(self::TV_STEP_DB_READ_SEC, [], []);
        $test_name = 'without a counted category no frontend time is booked';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), system_time_type::API_WAIT);
        // the includes of an api script are done before the timing starts, so they are added afterwards
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add_script_loading(self::TV_FAST_SEC, self::TV_SLOW_SEC);
        $test_name = 'the time from the request until the start of the timing is the script loading';
        $t->assert_text_contains($test_name, $sys_tim_lst->report(),
            system_time_type::SCRIPT_LOADING . ': ' . round(self::TV_SLOW_SEC - self::TV_FAST_SEC, 4) . ' sec');
        // negative: without the request time e.g. in a script started from the command line nothing is added
        $sys_tim_lst = new system_time_list();
        $sys_tim_lst->add_script_loading(0, self::TV_SLOW_SEC);
        $test_name = '... but not without the time of the request';
        $t->assert_text_not_contains($test_name, $sys_tim_lst->report(), system_time_type::SCRIPT_LOADING);

        $t->subheader($ts . 'system time of a slow request');
        $sys_tim_typ_lst = $t_sys->system_time_types();
        $start = (float)strtotime(self::TV_TIME);
        $log_limit = def::FALLBACK_RESPONSE_TIME_LOG;
        $detail_limit = def::FALLBACK_RESPONSE_TIME_DETAIL;

        $test_name = 'a request faster than the log limit writes no system time';
        $end = $start + self::TV_FAST_SEC;
        $sys_tim_lst = $t_sys->system_time_steps();
        $rows = $sys_tim_lst->slow_request_rows($start, $end, self::TV_URL, $log_limit, $detail_limit, $sys_tim_typ_lst);
        $t->assert($test_name, count($rows), 0);

        $test_name = 'a slow request writes one row with the total time';
        $end = $start + self::TV_SLOW_SEC;
        $sys_tim_lst = $t_sys->system_time_steps();
        $rows = $sys_tim_lst->slow_request_rows($start, $end, self::TV_URL, $log_limit, $detail_limit, $sys_tim_typ_lst);
        $t->assert($test_name, count($rows), 1);
        $test_name = '... as api request';
        $t->assert($test_name, $rows[0]->type_id, $sys_tim_typ_lst->id(system_time_type::API_REQUEST));
        $test_name = '... with the url';
        $t->assert($test_name, $rows[0]->url, self::TV_URL);
        $test_name = '... and the milliseconds of the request';
        $t->assert($test_name, $rows[0]->milliseconds, self::TV_SLOW_MS);

        $test_name = 'a request slower than the detail limit writes also each step';
        $end = $start + self::TV_VERY_SLOW_SEC;
        $sys_tim_lst = $t_sys->system_time_steps();
        $rows = $sys_tim_lst->slow_request_rows($start, $end, self::TV_URL, $log_limit, $detail_limit, $sys_tim_typ_lst);
        $t->assert($test_name, count($rows), 3);
        $test_name = '... with the longest step first';
        $t->assert($test_name, $rows[1]->type_id, $sys_tim_typ_lst->id(system_time_type::DB_READ));
        $test_name = '... and the milliseconds of the step';
        $t->assert($test_name, $rows[1]->milliseconds, self::TV_STEP_DB_READ_MS);

        $test_name = 'a step without system time type is written as not specified';
        $end = $start + self::TV_VERY_SLOW_SEC;
        $sys_tim_lst = $t_sys->system_time_steps_unknown();
        $rows = $sys_tim_lst->slow_request_rows($start, $end, self::TV_URL, $log_limit, $detail_limit, $sys_tim_typ_lst);
        $t->assert($test_name, $rows[1]->type_id, $sys_tim_typ_lst->default_id());

        $t->subheader($ts . 'time of a slow answer');
        $answer_limit = def::FALLBACK_RESPONSE_TIME_API;
        $steps = [system_time_type::DB_READ => self::TV_STEP_DB_READ_SEC, system_time_type::LOAD_TYPES => self::TV_STEP_LOAD_TYPES_SEC];

        $test_name = 'a slow answer names the seconds of each step with the longest first';
        $end = $start + self::TV_SLOW_SEC;
        $sys_tim_lst = $t_sys->system_time_steps();
        $t->assert($test_name, $sys_tim_lst->answer_steps($start, $end, $answer_limit), $steps);
        // negative: a fast answer has nothing to split
        $test_name = '... but a fast answer names no step';
        $end = $start + $answer_limit / 2;
        $sys_tim_lst = $t_sys->system_time_steps();
        $t->assert($test_name, $sys_tim_lst->answer_steps($start, $end, $answer_limit), []);

        $t->subheader($ts . 'system time SQL write');
        $sys_tim = $t_sys->system_time();
        $t->assert_sql_insert($sc, $sys_tim);

        $t->subheader($ts . 'slow requests SQL read');
        // only the total time of a request of the last days that is still slower than the log limit
        $slw_lst = new system_time_slow_list();
        $type_id = $t_sys->system_time_types()->id(system_time_type::API_REQUEST);
        $since = new DateTime(self::TV_TIME);
        $sc->reset(sql_db::POSTGRES);
        $qp = $slw_lst->load_sql($sc, $type_id, $since, self::TV_SLOW_MS);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $slw_lst->load_sql($sc, $type_id, $since, self::TV_SLOW_MS);
        $t->assert_qp($qp, $sc->db_type);

        $t->subheader($ts . 'slow requests period');
        // the config.yaml period is the same as the fallback
        $test_name = 'the period starts the configured days before now';
        $now = strtotime(self::TV_TIME);
        $since = system_time_slow_list::since($now);
        $days_sec = def::FALLBACK_SLOW_REQUEST_DAYS * system_time_slow_list::SECONDS_PER_DAY;
        $t->assert($test_name, $since->getTimestamp(), $now - $days_sec);

        $t->subheader($ts . 'slow request api');
        $test_name = 'a slow request is sent with its milliseconds';
        $sys_tim = new system_time();
        $sys_tim->row_mapper($t_sys->system_time_row(), $msg);
        $json = $sys_tim->api_json_array([], $msg);
        $t->assert($test_name, $json[json_fields::MILLISECONDS], self::TV_SLOW_MS);
        $test_name = '... its url';
        $t->assert($test_name, $json[json_fields::URL], self::TV_URL);
        $test_name = '... and its start time';
        $start = new DateTime(self::TV_TIME_DB)->format(DateTimeInterface::ATOM);
        $t->assert($test_name, $json[json_fields::TIME_START], $start);
        $test_name = 'a request without end time has no end time instead of now';
        $t->assert_null($test_name, $sys_tim->end_time);
        $test_name = 'a missing row maps no url';
        $sys_tim = new system_time();
        $sys_tim->row_mapper(null, $msg);
        $t->assert_null($test_name, $sys_tim->url);
        $msg->reset();

        // negative: a system time that has not been started sends no start time
        $test_name = 'no start time if not started';
        $sys_tim = new system_time();
        $vars = $sys_tim->api_json_array([], $msg);
        $t->assert_null($test_name, $vars[json_fields::TIME_START]);
        $msg->reset();


        $t->subheader($ts . 'debug id');

        // the debug id of an updated log entry shows the update time
        $test_name = 'updated log with update time';
        $slg = $t_sys->sys_log_filled();
        $dsp = $slg->dsp_id();
        $upd = new DateTime(self::TV_TIME_ASSIGNED)->format(DateTimeInterface::ATOM);
        $t->assert_text_contains($test_name, $dsp, $upd);

        // negative: a log entry that has never been updated shows no update time
        $test_name = 'new log without update time';
        $slg = $t_sys->sys_log();
        $dsp = $slg->dsp_id();
        $upd = new DateTime(self::TV_TIME_ASSIGNED)->format(DateTimeInterface::ATOM);
        $t->assert_text_not_contains($test_name, $dsp, $upd);

    }

}
