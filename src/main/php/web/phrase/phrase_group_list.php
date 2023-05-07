<?php

/*

  web/phrase/phrase_group_list.php - html display function for a list of word and triple groups
  --------------------------------

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

namespace html\phrase;

use html\phrase\phrase_group as phrase_group_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use model\library;
use model\phrase;
use model\user;

class phrase_group_list
{

    public array $lst;                  // the list of the phrase group objects
    public user $usr;                   // the person for whom the word group list has been created
    public ?array $time_lst = null;     // the list of the time phrase (the add function)
    public ?array $grp_ids = null;      // the list of the phrase group ids
    public ?array $grp_time_ids = null; // the list of the phrase group and time ids

    public ?array $phr_lst_lst = null;  // list of a list of phrases

    // search fields
    public ?phrase $phr; //

    /*
     * construct and map
     */

    /**
     * always set the user because a phrase group list is always user specific
     */
    function __construct()
    {
        $this->lst = array();
    }


    /*
     * set and get
     */

    /**
     * add a phrase group if it is not yet part of the list
     */
    function add(phrase_group_dsp $grp): void
    {
        log_debug($grp->id());
        $do_add = false;
        if ($grp->id() > 0) {
            if ($this->grp_ids == null) {
                $do_add = true;
            } else {
                if (!in_array($grp->id(), $this->grp_ids)) {
                    $do_add = true;
                }
            }
        }
        if ($do_add) {
            $this->lst[] = $grp;
            $this->grp_ids[] = $grp->id();
            $this->time_lst[] = null;
            log_debug($grp->dsp_id() . ' added to list ' . $this->dsp_id());
        } else {
            log_debug($grp->dsp_id() . ' skipped, because is already in list ' . $this->dsp_id());
        }
    }

    /*
     * get functions
     */

    // return all phrases that are part of each phrase group of the list
    function common_phrases(): ?phrase_list_dsp
    {
        log_debug();
        $lib = new library();
        $result = new phrase_list_dsp();
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