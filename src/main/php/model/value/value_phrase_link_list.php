<?php

/*

    model/value/value_phrase_link_list.php - a list of value phrase links
    --------------------------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace model;

class value_phrase_link_list extends sandbox_list
{

    /**
     * create an SQL statement to retrieve a list of value phrase links from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase|null $phr if set to get all values for this phrase
     * @param value|null $val if set to get all phrase for this value
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, ?phrase $phr = null, ?value $val = null): sql_par
    {
        $db_con->set_type(sql_db::TBL_VALUE_PHRASE_LINK);
        $qp = new sql_par(self::class);
        $sql_by = '';

        if ($val != null) {
            if ($val->id() > 0) {
                $sql_by = value::FLD_ID;
            }
        } elseif ($phr != null) {
            if ($phr->id() <> 0) {
                $sql_by = phrase::FLD_ID;
            }
        }
        if ($sql_by == '') {
            log_err('Either the value id or phrase id and the user must be set ' .
                      'to load a ' . self::class, self::class . '->load_sql');
            $qp->name = '';
        } else {
            $qp->name .= $sql_by;
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(value_phrase_link::FLD_NAMES);
            if ($val != null) {
                $db_con->set_join_fields(array(value::FLD_ID), sql_db::TBL_VALUE);
            } else {
                $db_con->set_join_fields(array(phrase::FLD_ID), sql_db::TBL_PHRASE);
            }
            if ($val != null) {
                if ($val->id() > 0) {
                    $db_con->add_par(sql_db::PAR_INT, $val->id());
                    $qp->sql = $db_con->select_by_field_list(array(value::FLD_ID));
                }
            } elseif ($phr != null) {
                if ($phr->id() <> 0) {
                    $db_con->add_par(sql_db::PAR_INT, $phr->id());
                    $qp->sql = $db_con->select_by_field_list(array(phrase::FLD_ID));
                }
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
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
        if ($usr->id() <= 0) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } else {
            $this->set_user($usr);
            $qp = $this->load_sql($db_con, $phr, $val);
            if ($qp->name == '') {
                log_err('A value or phrase must be set to load ' . self::class, self::class . '->load');
            } else {

                if ($val != null) {
                    $id = $val->id();
                } else {
                    $id = $phr->id();
                }

                // if $sql is an empty string, the prepared statement should be used
                $db_rows = $db_con->get_old($qp->sql, $qp->name, array($id));
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
            foreach ($this->lst as $val_phr_lmk) {
                $result->add($val_phr_lmk->del());
            }
        }
        return new user_message();
    }

    /*
     * extract function
     */

    /**
     * @return array with all phrase ids
     */
    function phr_ids(): array
    {
        $result = array();
        foreach ($this->lst as $lnk) {
            if ($lnk->phr->id() <> 0) {
                if (in_array($lnk->phr->id(), $result)) {
                    $result[] = $lnk->phr->id();
                }
            }
        }
        return $result;
    }

    /**
     * @return array with all phrase ids
     */
    function val_ids(): array
    {
        $result = array();
        foreach ($this->lst as $lnk) {
            if ($lnk->val->id <> 0) {
                if (in_array($lnk->val->id, $result)) {
                    $result[] = $lnk->val->id;
                }
            }
        }
        return $result;
    }

 }