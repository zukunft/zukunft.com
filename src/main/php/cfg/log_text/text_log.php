<?php

/*

    model/system/text_log.php - object to handle standard io logging
    -------------------------

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

namespace cfg\log_text;

use cfg\const\paths;

include_once paths::MODEL_LOG_TEXT . 'text_log.php';

class text_log
{

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
     * write a header text to the standard io
     * and to the database log table if requested by the db log level
     * @param string $header_text the english header for the system admin
     */
    function header(string $header_text): void
    {
        if ($this->format == text_log_format::TEXT) {
            echo $this->time_stamp() . $header_text . "\n";
        } else {
            echo '<br><br><h2>' . $this->time_stamp() . $header_text . '</h2><br>' . "\n";
        }
        log_info($header_text);
    }

    /**
     * write a subheader text to the standard io
     * and to the database log table if requested by the db log level
     * @param string $header_text the english subheader for the system admin
     */
    function subheader(string $header_text): void
    {
        if ($this->format == text_log_format::TEXT) {
            echo $this->time_stamp() . $header_text . "\n";
        } else {
            echo '<br><h3>' . $this->time_stamp() . $header_text . '</h3><br>' . "\n";
        }
        log_info($header_text);
    }

    /**
     * write a test result text or a log entry text to the standard io
     * and to the database log table if requested by the db log level
     * @param string $text the english text for the system admin
     */
    function echo_log(string $text): void
    {
        if ($this->format == text_log_format::TEXT) {
            echo $this->time_stamp() . $text . "\n";
        } else {
            echo $this->time_stamp() . $text . '</p>' . "\n";
        }
        log_info($text);
    }

    private function time_stamp(): string
    {
        return sprintf('%08.4f', microtime(true) - $this->start_time) . ' ';
    }



}