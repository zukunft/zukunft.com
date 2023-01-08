<?php

/*

  application.php - the application settings
  ---------------

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

  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>

  http://zukunft.com

*/

const POD_NAME = "zukunft.com"; // the default pod name if not defined
const PRG_VERSION = "0.0.3"; // to detect the correct update script and to mark the data export
const NEXT_VERSION = "0.0.4"; // to prevent importing incompatible data
const FIRST_VERSION = "0.0.2"; // the last program version which has not a basic upgrade process

// log level
const DSP_LEVEL = sys_log_level::ERROR;   // starting from this criticality level messages are shown to the user
const LOG_LEVEL = sys_log_level::WARNING; // starting from this criticality level messages are written to the log for debugging
const MSG_LEVEL = sys_log_level::ERROR;   // in case of an error or fatal error
// additional the message a link to the system log shown
// so that the user can track when the error is solved


