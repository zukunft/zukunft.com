<?php

/*

    web/component/execute/ui_log.php - html user interface components for change log
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;

class ui_log
{

    /**
     * @return string with the html code that shows the recent changes of this object
     */
    function system_change_log(db_object $dbo, change_log_list $log_lst): string
    {
        // TODO review
        // if the given change og is empty use the global cache
        if ($log_lst->is_empty()) {
            global $ui_sys;
            if ($ui_sys == null) {
                log_warning('ui cache is empty');
                $log_lst = new change_log_list();
            } else {
                $log_lst = $ui_sys->chg_log;
            }
        }
        // filter the change log based on the given object
        $log_lst = $log_lst->filter($dbo);
        return $log_lst->dsp();
    }

}
