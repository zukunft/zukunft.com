<?php

/*

    web/component/execute/ui_log.php - html user interface components for change log
    ---------------------------------

    $log is the suggested var name

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

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class ui_log
{

    /*
     * display
     */

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

    /**
     * show the most relevant open system errors related to the user
     * so that the user can track when they are solved
     *
     * @param sys_log_list $err_lst the open system errors related to the user as loaded from the backend
     * @param msg_id|null $ui_msg_code_id the message id of the headline shown in the user language
     * @return string the html code with the error list or the no-error message
     */
    function user_system_errors(sys_log_list $err_lst, ?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        if ($err_lst->is_empty()) {
            $result .= $mtr->txt(msg_id::USER_SYSTEM_ERRORS_NONE);
        } else {
            if ($ui_sys?->cfg !== null) {
                $limit = (int)$ui_sys->cfg->get_by(
                    [triples::SYSTEM_ERRORS, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                    def::FALLBACK_USER_ERRORS
                );
            } else {
                $limit = def::FALLBACK_USER_ERRORS;
            }
            $result .= $err_lst->head($limit)->get_html();
        }
        return $result;
    }

}
