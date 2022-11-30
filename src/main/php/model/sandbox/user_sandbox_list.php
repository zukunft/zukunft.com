<?php

/*

    model/sandbox/user_sandbox_list.php - a base object for a list of user sandbox objects
    -----------------------------------


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

class sandbox_list extends base_list
{
    /*
     *  object vars
     */

    private user $usr; // the person for whom the list has been created


    /*
     * construct and map
     */

    /**
     * always set the user because a link list is always user specific
     * @param user $usr the user who requested to see e.g. the formula links
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->set_user($usr);
    }

    /*
     * get and set
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }

    /*
     *  information functions
     */

    /**
     * @return bool true if the list is already empty
     */
    function is_empty(): bool
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
     * debug functions
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
            foreach ($this->lst as $sbx_obj) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $sbx_obj->name();
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
