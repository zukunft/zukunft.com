<?php

/*

    web/log_text/text_log.php - frontend display of the standard io logging
    -------------------------

    the html display functions (header and subheader) of the backend text_log,
    moved to the frontend so that the backend text_log does not depend on html_base


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

namespace Zukunft\ZukunftCom\main\php\web\log_text;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_format.php';
include_once html_paths::HTML . 'html_base.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log as text_log_base;
use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\web\html\html_base;

class text_log extends text_log_base
{

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
            $html = new html_base();
            echo '<br><br>' . $html->h2($this->time_stamp() . $header_text) . '<br>' . "\n";
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
            $html = new html_base();
            echo '<br>' . $html->h3($this->time_stamp() . $header_text) . '<br>' . "\n";
        }
        log_info($header_text);
    }

}