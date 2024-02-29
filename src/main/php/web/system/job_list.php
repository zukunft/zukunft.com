<?php

/*

    /web/system/job_list.php - the display extension of the system error log api object
    ------------------------


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

include_once WEB_SANDBOX_PATH . 'list.php';
include_once WEB_SYSTEM_PATH . 'job.php';

use controller\controller;
use html\html_base;
use html\list_dsp;
use html\system\job as job_dsp;

class job_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        // TODO activate Prio 3
        //$ctrl = new controller();
        //$json_array = $ctrl->check_api_msg($json_array, controller::API_BODY_SYS_LOG);
        foreach ($json_array as $value) {
            $this->add_obj($this->set_obj_from_json_array($value));
        }
    }

    /**
     * set the vars of a batch job object based on the given json
     * @param array $json_array an api single object json message
     * @return object a batch job set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $job = new job_dsp();
        $job->set_from_json_array($json_array);
        return $job;
    }


    /*
     * modify
     */

    /**
     * add a batch_job to the list
     * @returns bool true if the batch_job has been added
     */
    function add(job_dsp $job): bool
    {
        return parent::add_obj($job);
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
        foreach ($this->lst as $job) {
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
        foreach ($this->lst as $wrd) {
            $lnk = $wrd->dsp_obj()->display_linked($back);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), html_base::STYLE_BORDERLESS);
    }


}
