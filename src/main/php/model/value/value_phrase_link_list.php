<?php

/*

    value_phrase_link_list.php - a list of value phrase links
    --------------------------

    These links are mainly used for using the database for index based selections
    the links itself are a replication of the phrase group links per value

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

class value_phrase_link_list
{

    public array $lst; // the list of the value phrase links
    public user $usr;  // the person for whom the list has been created

    function __construct(user $usr)
    {
        $this->lst = [];
        $this->usr = $usr;
    }

    /**
     * create an SQL statement to retrieve a list of value phrase links from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_sql(sql_db $db_con, ?phrase $phr = null, ?value $val = null, bool $get_name = false): string
    {
        $sql = '';
        $sql_where = '';
        $sql_name = self::class . '_by_';
        $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
        if ($val != null) {
            if ($val->id > 0) {
                $sql_name .= value::FLD_ID;
                $db_con->add_par(sql_db::PAR_INT);
                $sql_where = 'l.' . value::FLD_ID . ' = ' . $db_con->par_name();
            }
        } elseif ($phr != null) {
            if ($phr->id <> 0) {
                $sql_name .= phrase::FLD_ID;
                $db_con->add_par(sql_db::PAR_INT);
                $sql_where = 'l.' . phrase::FLD_ID . ' = ' . $db_con->par_name();
            }
        }
        if ($sql_where == '') {
            log_err("The phrase and the user must be set to load a phrase group list.", self::class . '->load_sql');
            $sql_name = '';
        } else {

            $db_con->set_name($sql_name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(value_phrase_link::FLD_NAMES);
            if ($val != null) {
                $db_con->set_join_fields(array(value::FLD_ID), DB_TYPE_VALUE);
            } else {
                $db_con->set_join_fields(array(phrase::FLD_ID), DB_TYPE_PHRASE);
            }
            $db_con->set_where_text($sql_where);
            $sql = $db_con->select();
        }

        if ($get_name) {
            return  $sql_name;
        } else {
            return  $sql;
        }
    }

    /**
     * load all phrases linked to a given value
     *
     * @param user $usr the user for whom the links should be loaded
     * @param ?phrase $phr the phrase to which values should be loaded
     * @param ?value $val the value which phrases should be loaded
     * @return bool true if value or phrases are found
     */
    private function load(user $usr, ?phrase $phr = null, ?value $val = null): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if ($usr->id <= 0) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } else {
            $this->usr = $usr;
            $sql_name = $this->load_sql($db_con, $phr, $val, true);
            if ($sql_name == '') {
                log_err('A value or phrase must be set to load ' . self::class, self::class . '->load');
            } else {

                $sql = '';
                if (!$db_con->has_query($sql_name)) {
                    $sql = $this->load_sql($db_con, $phr, $val);
                }
                if ($val != null) {
                    $id = $val->id;
                } else {
                    $id = $phr->id;
                }

                // if $sql is an empty string, the prepared statement should be used
                $db_rows = $db_con->get($sql, $sql_name, array($id));
                if ($db_rows != null) {
                    foreach ($db_rows as $db_row) {
                        $val_phr_lnk = new value_phrase_link($usr);
                        $val_phr_lnk->row_mapper($db_row);
                        $this->lst[] = $val_phr_lnk;
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * interface function to load all phrases linked to a given value
     *
     * @param user $usr the user for whom the links should be loaded
     * @param value $val the value which phrases should be loaded
     * @return bool true if phrases are found
     */
    function load_by_value(user $usr, value $val): bool
    {
        return $this->load($usr, null, $val);
    }

    /**
     * interface function to load all values linked to a given phrase
     *
     * @param user $usr the user for whom the links should be loaded
     * @param phrase $phr the phrase to which values should be loaded
     * @return bool true if phrases are found
     */
    function load_by_phrase(user $usr, phrase $phr): bool
    {
        return $this->load($usr, $phr);
    }

    /**
     * delete all loaded value phrase links e.g. to delete al the "value phrase links" linked to a phrase
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        if ($this->lst != null) {
            foreach ($this->lst as $phr_grp) {
                $result->add($phr_grp->del());
            }
        }
        return new user_message();
    }


    /*
      display functions
      -----------------
    */

    // display the unique id fields
    function dsp_id(): string
    {
        global $debug;
        $result = '';

        if ($this->lst != null) {
            $pos = 0;
            foreach ($this->lst as $phr_lst) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $phr_lst->name();
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