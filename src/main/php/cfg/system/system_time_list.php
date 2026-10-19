<?php

/*

    model/system/system_time_list.php - a list of system error objects
    ---------------------------------

    $sys_tim_lst is the suggested var name

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_SYSTEM . 'system_time.php';
include_once paths::MODEL_SYSTEM . 'system_time_type_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use DateTime;

class system_time_list
{

    // the microtime is in seconds and the system_times table in milliseconds
    const int MILLISECONDS_PER_SECOND = 1000;
    // the sections that are remembered to return to; a section that is switched to and never
    // ended e.g. the explicit sections of a test does not let the stack grow without limit
    const int MAX_SECTION_DEPTH = 64;

    /*
     * object vars
     */

    // the protected main var
    // list of the total execution times
    private array $lst = [];
    // list of the execution times of the last section
    private array $section_lst = [];
    private float $start_time = 0;

    private string $cur_cat = '';
    // the sections around the current section with the outermost first
    private array $pre_cats = [];


    /*
     * interface
     */

    /**
     * start the timing of a section within the current section or, without a type, end the
     * current section and continue with the section around it; also a section of the same type
     * is remembered, so that each end returns to the section that has been counted before its start
     *
     * @param string $typ the category of the following
     * @param bool $do_snap
     * @return void
     */
    function switch(string $typ = '', bool $do_snap = false): void
    {
        // stop current time measurement
        $this->stop();
        if ($typ == '') {
            // continue with previous time measurement
            $this->continue();
        } else {
            // remember current in stack
            $this->pre_cats[] = $this->cur_cat;
            if (count($this->pre_cats) > self::MAX_SECTION_DEPTH) {
                array_shift($this->pre_cats);
            }
            $this->cur_cat = $typ;
            // start time counting
            $this->start($typ);
        }
    }

    /**
     * @return string the category that is counted now or an empty string if none is counted
     */
    function current(): string
    {
        return $this->cur_cat;
    }

    /**
     * @return string description of the execution times by category
     */
    function report(float $expected = 0): string
    {
        $total = 0.0;
        $time_report = '';
        arsort($this->lst);
        foreach ($this->lst as $cat => $time) {
            if ($time_report != '') {
                $time_report .= ', ';
            }
            $time_report .= $cat . ': ' . round($time, 4) . ' sec';
            $total = $total + $time;
        }
        $time_report .= ' -> measured ' . round($total, 4) .
            ' / unmeasured ' . round($expected - $total, 4);
        return $time_report;
    }

    /**
     * @return string description of the execution times by category of the last section
     */
    function section_report(float $expected = 0): string
    {
        $total = 0.0;
        $time_report = '';
        foreach ($this->section_lst as $cat => $time) {
            if ($time_report != '') {
                $time_report .= ', ';
            } else {
                $time_report .= 'section ';
            }
            $time_report .= $cat . ': ' . round($time, 4) . ' sec';
            $total = $total + $time;
        }
        $unmeasured = $expected - $total;
        // show only how much has been measured if this is relevant
        $measured_relevant = false;
        if (abs($total) > 0.1 and abs($unmeasured) > 0.01) {
            $measured_relevant = true;
        }
        // if a relevant amount of time has not been measured, report it
        $unmeasured_relevant = false;
        if ($measured_relevant and abs($unmeasured) > 0.1) {
            $unmeasured_relevant = true;
        }
        if ($measured_relevant) {
            $time_report .= ' -> measured ' . round($total, 4);
        }
        if ($measured_relevant and $unmeasured_relevant) {
            $time_report .= ' / ';
        }
        if ($unmeasured_relevant) {
            $time_report .= 'unmeasured ' . round($expected - $total, 4);
        }
        return $time_report;
    }

    /**
     * start a new measurement section, so that the section report shows only the times of the
     * next section; called whenever the caller restarts its section timer, because a report that
     * still contains the times of the previous sections would show more time than the section took
     *
     * @return void
     */
    function reset_section(): void
    {
        $this->section_lst = [];
    }

    /**
     * the system time rows of one request: a row with the total time if the request has been slow
     * and a row per measured step if it has been very slow, so that the admin sees which step to optimize
     *
     * @param float $start the microtime when the request has been received
     * @param float $end the microtime when the answer has been created
     * @param string $url the request url as received to find the slow call
     * @param float $log_limit seconds above which the total time of the request is written
     * @param float $detail_limit seconds above which also the time of each step is written
     * @param system_time_type_list $sys_tim_typ_lst to get the database id of the request and step areas
     * @return system_time[] the rows to write, empty if the request has not been slower than the log limit
     */
    function slow_request_rows(
        float                 $start,
        float                 $end,
        string                $url,
        float                 $log_limit,
        float                 $detail_limit,
        system_time_type_list $sys_tim_typ_lst
    ): array
    {
        $rows = [];
        $duration = $end - $start;
        if ($duration > $log_limit) {
            $rows[] = $this->time_row(system_time_type::API_REQUEST, $duration, $start, $end, $url, $sys_tim_typ_lst);
        }
        if ($duration > $log_limit and $duration > $detail_limit) {
            foreach ($this->steps($end) as $cat => $step_duration) {
                $rows[] = $this->time_row($cat, $step_duration, $start, $end, $url, $sys_tim_typ_lst);
            }
        }
        return $rows;
    }

    /**
     * @return float seconds above which a request is written to the system times
     *               (config.yaml user > default > frontend > behaviour > response time > log)
     */
    static function log_limit(): float
    {
        global $cfg;
        $names = [triples::RESPONSE_TIME, words::LOG, words::FRONTEND, words::BEHAVIOUR];
        return $cfg?->get_by($names, def::FALLBACK_RESPONSE_TIME_LOG) ?? def::FALLBACK_RESPONSE_TIME_LOG;
    }

    /**
     * the seconds of each step of a slow api request for the json of the answer
     * (see controller::with_time), so that the caller can split its wait for the answer
     *
     * @param float $start the microtime when the request has been received
     * @param float $end the microtime when the answer is sent
     * @param float $limit seconds above which the steps are named
     * @return array the seconds per step with the longest first, empty if the request has not been slower than the limit
     */
    function answer_steps(float $start, float $end, float $limit): array
    {
        $result = [];
        if ($end - $start > $limit) {
            foreach ($this->steps($end) as $cat => $seconds) {
                $result[$cat] = round($seconds, 4);
            }
        }
        return $result;
    }

    /**
     * @return float seconds above which an api answer names the time of each step in its json
     *               (config.yaml user > default > frontend > behaviour > response time > api)
     */
    static function answer_limit(): float
    {
        global $cfg;
        $names = [triples::RESPONSE_TIME, words::API, words::FRONTEND, words::BEHAVIOUR];
        return $cfg?->get_by($names, def::FALLBACK_RESPONSE_TIME_API) ?? def::FALLBACK_RESPONSE_TIME_API;
    }


    /*
     * timing
     */

    private function start(string $typ = ''): void
    {
        $this->start_time = microtime(true);
    }

    private function stop(): void
    {
        if ($this->cur_cat != '') {
            // add time to list
            $key = $this->cur_cat;
            $end_time = microtime(true);
            $duration = $end_time - $this->start_time;
            $this->add($duration, $key);
        }
    }

    private function continue(): void
    {
        // switch to previous category and continue with time counting
        $this->cur_cat = array_pop($this->pre_cats) ?? '';
        $this->start($this->cur_cat);
    }

    /**
     * @param float $end the microtime of the end of the request
     * @return array the seconds per step with the longest first, including the step that is still running
     */
    private function steps(float $end): array
    {
        $steps = $this->lst;
        // the running step is not yet stopped, so its time up to the end is added without stopping it
        if ($this->cur_cat != '') {
            $steps[$this->cur_cat] = ($steps[$this->cur_cat] ?? 0) + $end - $this->start_time;
        }
        arsort($steps);
        return $steps;
    }

    /**
     * @param string $code_id the area of the time e.g. the whole api request or one step
     * @param float $seconds the time used by the area
     * @param float $start the microtime when the request has been received
     * @param float $end the microtime when the answer has been created
     * @param string $url the request url as received
     * @param system_time_type_list $sys_tim_typ_lst to get the database id of the area
     * @return system_time the row to write
     */
    private function time_row(
        string                $code_id,
        float                 $seconds,
        float                 $start,
        float                 $end,
        string                $url,
        system_time_type_list $sys_tim_typ_lst
    ): system_time
    {
        $sys_tim = new system_time();
        $sys_tim->start_time = new DateTime()->setTimestamp((int)$start);
        $sys_tim->end_time = new DateTime()->setTimestamp((int)$end);
        // a step without own area is still written, as not specified; id() has logged the missing area
        $sys_tim->type_id = $sys_tim_typ_lst->id($code_id);
        if ($sys_tim->type_id <= 0) {
            $sys_tim->type_id = $sys_tim_typ_lst->default_id();
        }
        $sys_tim->url = $url;
        $sys_tim->milliseconds = (int)round($seconds * self::MILLISECONDS_PER_SECOND);
        return $sys_tim;
    }


    /*
     * modify
     */

    function add(float $duration, string $key): void
    {
        if (array_key_exists($key, $this->lst)) {
            $this->lst[$key] = $this->lst[$key] + $duration;
        } else {
            $this->lst[$key] = $duration;
        }
        if (array_key_exists($key, $this->section_lst)) {
            $this->section_lst[$key] = $this->section_lst[$key] + $duration;
        } else {
            $this->section_lst[$key] = $duration;
        }
    }

    /**
     * book a part of the time of one category to another category e.g. the api wait that the
     * frontend has measured within the url to html time
     *
     * @param float $duration the seconds to move
     * @param string $from the category that has counted the time
     * @param string $to the category that the time belongs to
     * @return void
     */
    function move(float $duration, string $from, string $to): void
    {
        if ($duration > 0) {
            $this->add(-$duration, $from);
            $this->add($duration, $to);
        }
    }

    /**
     * book the backend steps that the api answers have named out of the api wait,
     * so that the report shows which step of the backend the frontend has waited for
     *
     * @param array $steps the seconds per backend step e.g. from data_object::api_steps_since
     * @return void
     */
    function split_api_wait(array $steps): void
    {
        foreach ($steps as $cat => $seconds) {
            $this->move($seconds, system_time_type::API_WAIT, system_time_type::API_STEP_PREFIX . $cat);
        }
    }


    /*
     * save
     */

    private function save(): user_message
    {
        return new user_message(); // the ok message IS the return value of this not yet implemented save
    }
}