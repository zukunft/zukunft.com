<?php

/*

    model/system/system_time_list.php - a list of system error objects
    ---------------------------------

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

include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class system_time_list
{

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
        $this->section_lst = [];
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
            if (array_key_exists($key, $this->section_lst)) {
                $this->section_lst[$key] = $this->section_lst[$key] + $duration;
            } else {
                $this->section_lst[$key] = $duration;
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
        return new user_message();
    }
}