<?php

/*

  link_list.php - a base object for a link list
  -------------
  

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

class link_list
{
    /*
     *  object vars
     */

    public array $lst; // the list of the view component links
    public user $usr;  // the person for whom the list has been created


    function __construct(user $usr)
    {
        $this->lst = [];
        $this->usr = $usr;
    }

    /**
     * @return bool true if the list is already empty
     */
    function empty(): bool
    {
        $result = true;
        if ($this->lst != null) {
            if (count($this->lst) > 0) {
                $result = false;
            }
        }
        return $result;
    }

    /*
     *  display functions
     */

    /**
     * @return string to display the unique id fields
     */
    function dsp_id(): string
    {
        global $debug;
        $result = '';

        if ($this->lst != null) {
            $pos = 0;
            foreach ($this->lst as $lnk) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $lnk->name();
                    $pos++;
                }
            }
            if (count($this->lst) > $pos) {
                $result .= ' ... total ' . dsp_count($this->lst);
            }
        }
        return $result;
    }


}
