<?php

/*

    web/group/group_list.php - a list of word and triple groups
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

namespace html\group;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::GROUP . 'group.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::USER . 'user.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED . 'library.php';

use html\phrase\phrase;
use html\phrase\phrase_list;
use html\sandbox\sandbox_list;
use html\user\user;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\library;

class group_list extends sandbox_list
{

    public array $lst;                  // the list of the phrase group objects
    public user $usr;                   // the person for whom the word group list has been created
    public ?array $time_lst = null;     // the list of the time phrase (the add function)
    public ?array $grp_ids = null;      // the list of the phrase group ids

    // search fields
    public ?phrase $phr; //


    /*
     * set and get
     */

    /**
     * add a phrase group if it is not yet part of the list
     */
    function add(group|IdObject|TextIdObject|CombineObject|null $to_add): bool
    {
        log_debug($to_add->id());
        $do_add = false;
        if ($to_add->id() > 0) {
            if ($this->grp_ids == null) {
                $do_add = true;
            } else {
                if (!in_array($to_add->id(), $this->grp_ids)) {
                    $do_add = true;
                }
            }
        }
        if ($do_add) {
            $this->lst[] = $to_add;
            $this->grp_ids[] = $to_add->id();
            $this->time_lst[] = null;
            log_debug($to_add->dsp_id() . ' added to list ' . $this->dsp_id());
        } else {
            log_debug($to_add->dsp_id() . ' skipped, because is already in list ' . $this->dsp_id());
        }
        return $do_add;
    }


    /*
     * get functions
     */

    /**
     * return all phrases that are part of each phrase group of the list
     */
    function common_phrases(): ?phrase_list
    {
        log_debug();
        $lib = new library();
        $result = new phrase_list();
        $pos = 0;
        foreach ($this->lst as $grp) {
            if ($pos == 0) {
                if ($grp->lst() != null) {
                    $result->set_lst($grp->lst());
                }
            } else {
                if ($grp->lst() != null) {
                    //$result = $result->concat_unique($grp->phr_lst);
                    $result->common($grp->lst());
                }
            }
            log_debug($result->dsp_name());
            $pos++;
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /*
     * display
     */

    /**
     * @return string with to identify the phrase group list
     */
    function dsp_id(): string
    {
        global $debug;
        $lib = new library();
        $result = '';
        // check the object setup
        if (count($this->lst) <> count($this->time_lst)) {
            $result .= 'The number of groups (' . $lib->dsp_count($this->lst) . ') are not equal the number of times (' . $lib->dsp_count($this->time_lst) . ') of this phrase group list';
        } else {

            $pos = 0;
            foreach ($this->lst as $phr_lst) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $phr_lst->name();
                    $phr_time = $this->time_lst[$pos];
                    if (!is_null($phr_time)) {
                        $result .= '@' . $phr_time->name();
                    }
                    $pos++;
                }
            }
            if (count($this->lst) > $pos) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst);
            }

        }
        return $result;
    }

}