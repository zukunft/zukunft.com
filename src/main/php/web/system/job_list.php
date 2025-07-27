<?php

/*

    web/system/job_list.php - the display extension of the system error log api object
    -----------------------


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

namespace html\system;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::SYSTEM . 'job.php';
include_once html_paths::USER . 'user_message.php';

use html\html_base;
use html\sandbox\list_dsp;
use html\styles;
use html\system\job as job_dsp;
use html\user\user_message;

class job_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        // TODO activate Prio 3
        //$ctrl = new controller();
        //$json_array = $ctrl->check_api_msg($json_array, controller::API_BODY_SYS_LOG);
        return parent::api_mapper_list($json_array, new job());
    }


    /*
     * display
     */

    /**
     * @return string with a table of the batch job entries for users
     */
    function display(): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst() as $job) {
            if ($result == '') {
                $result .= $job->header();
            }
            $result .= $html->tr($job->display());
        }
        return $html->tbl($result);
    }

    /*
     * to review
     */

    /**
     * show all batch_jobs of the list as table row (ex display)
     * @param string $back the back trace url for the undo functionality
     * @return string the html code with all batch_jobs of the list
     */
    function tbl(string $back = ''): string
    {
        $html = new html_base();
        $cols = '';
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst() as $wrd) {
            $lnk = $wrd->dsp_obj()->display_linked($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), styles::STYLE_BORDERLESS);
    }


}
