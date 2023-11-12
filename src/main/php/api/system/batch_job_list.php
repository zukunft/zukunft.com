<?php

/*

    api/system/batch_job_list.php - a list object of minimal/api batch_job objects
    -----------------------------


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

namespace api\system;

use api\sandbox\list_api;
use JsonSerializable;

class batch_job_list_api extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a batch job to the list
     * @returns bool true if the batch job has been added
     */
    function add(batch_job_api $job): bool
    {
        return parent::add_obj($job);
    }


    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $job) {
            $vars[] = json_decode(json_encode($job));
        }
        return $vars;
    }


    /*
     * selection functions
     */

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param batch_job_list_api $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(batch_job_list_api $del_lst): void
    {
        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst() as $job) {
                if (!in_array($job->id(), $lst_ids)) {
                    $result[] = $job;
                }
            }
            $this->set_lst($result);
        }
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @param batch_job_list_api $new_wrd_lst with the batch_jobs that should be added
     */
    function merge(batch_job_list_api $new_wrd_lst)
    {
        foreach ($new_wrd_lst->lst() as $new_wrd) {
            $this->add($new_wrd);
        }
    }

}
