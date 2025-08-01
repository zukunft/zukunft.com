<?php

/*

  model/system/sys_log_list.php - a list of system error objects
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

namespace cfg;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_SYSTEM . 'base_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::MODEL_SYSTEM . 'sys_log_type.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';

use cfg\user\user_message;

class system_time_list
{

    /*
     * object vars
     */

    // the protected main var
    private array $lst = [];
    private float $start_time = 0;

    private string $cur_cat = '';
    private string $pre_cat = '';


    /*
     * interface
     */

    /**
     * start the timing of set the user of the error log
     *
     * @param string $typ the category of the following
     * @param bool $do_snap
     * @return void
     */
    function switch(string $typ = '', bool $do_snap = false): void
    {
        if ($typ == '') {
            // stop current time measurement
            $this->stop();
            // continue with previous time measurement
            $this->continue();
        } else {
            if ($typ != $this->cur_cat) {
                // stop current time measurement
                $this->stop();
                // remember current in stack
                $this->pre_cat = $this->cur_cat;
                $this->cur_cat = $typ;
                // start time counting
                $this->start($typ);
            }
        }
    }

    /**
     * @return string description of the execution times by category
     */
    function report(float $expected = 0): string
    {
        $total = 0.0;
        $time_report = '';
        foreach ($this->lst as $cat => $time) {
            if ($time_report != '') {
                $time_report .= ', ';
            }
            $time_report .= $cat . ': ' .  round($time, 4) . ' sec';
            $total = $total + $time;
        }
        $time_report .= ' -> measured ' .  round($total, 4) . ' / unmeasured ' .  round($expected - $total, 4);
        return $time_report;
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
            if (array_key_exists($key, $this->lst)) {
                $this->lst[$key] = $this->lst[$key] + $duration;
            } else {
                $this->lst[$key] = $duration;
            }
        }
    }

    private function continue(): void
    {
        // switch to previous category and continue with time counting
        $this->cur_cat = $this->pre_cat;
        $this->start($this->cur_cat);
    }

    /*
     * save
     */

    private function save(): user_message
    {
        $us_msg = new user_message();
        return $us_msg;
    }
}