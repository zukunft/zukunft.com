<?php

/*

    model/log_text/text_log.php - object to handle standard io logging
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\log_text;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_format.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_level.php';
include_once paths::SHARED_ENUM . 'sys_log_levels.php';

use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;

class text_log
{

    /*
     * const
     */

    // fallback log level if not set by the environment settings or overwritten by the system configuration
    const int DSP_LEVEL = sys_log_levels::ERROR_ID;   // starting from this criticality level messages are shown to the user
    const int LOG_LEVEL = sys_log_levels::WARNING_ID; // starting from this criticality level messages are written to the log for debugging
    const int MSG_LEVEL = sys_log_levels::ERROR_ID;   // in case of an error or fatal error
    // additional the message a link to the system log shown
    // so that the user can track when the error is solved

    // cap of the sys_log inserts per time window counted over all requests, so that a flood of
    // requests each logging a distinct error cannot grow the sys_log table unbounded (dos);
    // fixed consts and not config values because the config load itself may log
    const int INSERT_LIMIT = 200;
    const int INSERT_WINDOW_SEC = 600;
    // the json field names of the sys_log throttle state file
    const string THROTTLE_START = 'start';
    const string THROTTLE_COUNT = 'count';


    /*
     * object vars
     */

    private float $start_time; // time when all script have been started
    public text_log_format $format = text_log_format::TEXT;
    public text_log_level $level = text_log_level::TIMEOUT;


    /*
     * construct and map
     */

    function __construct()
    {
        // init the times to be able to detect potential timeouts
        $this->start_time = microtime(true);
    }


    /*
     * set and get
     */

    function start_time(): float
    {
        return $this->start_time;
    }


    /*
     * display
     */

    /**
     * write a test result text or a log entry text to the standard io
     * and to the database log table if requested by the db log level
     * @param string $text the English text for the system admin
     */
    function echo_text_log(string $text): void
    {
        if ($this->format == text_log_format::TEXT) {
            $this->echo_lines($text);
        } else {
            echo $this->time_stamp() . $text . '</p>' . "\n";
        }
        // TODO Prio 3 add a local text log
    }

    /**
     * write a test result text or a log entry text to the standard io
     * and to the database log table if requested by the db log level
     * @param string $text the English text for the system admin
     */
    function echo_log(string $text): void
    {
        if ($this->format == text_log_format::TEXT) {
            $this->echo_lines($text);
        } else {
            echo $this->time_stamp() . $text . '</p>' . "\n";
        }
        log_info($text);
    }

    /**
     * the single place that writes text to the standard io with the runtime timestamp
     * in front of every physical line, so that a multi line message (separated by a line
     * break or a html line break) gets a timestamp on each line and nothing is glued
     * in front of the next log line
     * @param string $text the message that may contain several physical lines
     * @return void
     */
    function echo_lines(string $text): void
    {
        echo $this->time_stamp_lines($text);
    }

    /**
     * put the runtime timestamp in front of every physical line of the given text
     * a html line break is treated as a physical line boundary too and kept as a marker
     * @param string $text the message that may contain several physical lines
     * @return string the timestamped text with a trailing line break for every non-empty line
     */
    function time_stamp_lines(string $text): string
    {
        $result = '';
        $prefix = $this->time_stamp();
        // turn every html line break into a physical line break but keep the marker for the html view
        $with_breaks = str_replace('<br>', '<br>' . "\n", $text);
        $lines = explode("\n", $with_breaks);
        foreach ($lines as $line) {
            if ($line != '') {
                $result .= $prefix . $line . "\n";
            }
        }
        return $result;
    }

    protected function time_stamp(): string
    {
        return sprintf('%08.4f', microtime(true) - $this->start_time) . ' ';
    }



}