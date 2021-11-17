<?php

/*

    log.php - the simple log interface object
    -------

    for the internal handling of the system error the system_error_log class is used

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


class log
{

    CONST MSG_ERR = ' failed due to: ';
    CONST MSG_ERR_USING = ' failed due using "';
    CONST MSG_ERR_BECAUSE = '" because: ';
    CONST MSG_ERR_INTERNAL = ' failed due to an internal error. The error has been logged and the fixing of this error can be traced with this link: ';

}