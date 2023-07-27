<?php

/*

    model/user/user_list.php - a list of users
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

namespace cfg;

use cfg\db\sql_creator;
use cfg\db\sql_par_type;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';

global $system_users;

class user_list
{

    public ?array $lst = null;  // the list of users
    public array $code_id_hash = [];
    private user $usr;          // the person who wants to see the user list e.g. an admin user


    /*
     * construct and map
     */

    /**
     * always set the user because a link list is always user specific
     * @param user $usr the user who requested to see e.g. the formula links
     */
    function __construct(user $usr)
    {
        $this->set_user($usr);
    }


    /*
     * load
     */

    /**
     * set the SQL query parameters to load a list of figure objects
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_type(sql_db::TBL_USER);
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(user::FLD_NAMES);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user list by the id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids list of user ids that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(user::FLD_ID, $ids);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user the code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id all users with this code id should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id): sql_par
    {
        $qp = $this->load_sql($sc, 'code_id');
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve all users that
     * have the given profile level or higher
     * e.g. loading the admin includes the system user
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $profile_id list of user that have at least this profile level
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_profile_and_higher(sql_creator $sc, int $profile_id): sql_par
    {
        $qp = $this->load_sql($sc, 'profiles');
        $sc->set_join_fields(
            array(user_profile::FLD_LEVEL),
            sql_db::TBL_USER_PROFILE,
            user::FLD_USER_PROFILE,
            user_profile::FLD_ID);
        $sc->add_where(sql_db::LNK_TBL . '.' . user_profile::FLD_LEVEL, $profile_id, sql_par_type::INT_HIGHER);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load this list of user
     * @param sql_db $db_con the database link as a parameter to load the system users at program start
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one user has been loaded
     */
    private function load(sql_db $db_con, sql_par $qp): bool
    {
        $result = false;

        $db_rows = $db_con->get($qp);
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $usr = new user();
                $usr->row_mapper($db_row);
                $this->lst[] = $usr;
                $result = true;
            }
            $this->set_hash();
        }

        return $result;
    }

    /**
     * load all users that have the given profile level or higher
     * e.g. loading the admin includes the system user
     *
     * @param sql_db $db_con the database link as a parameter to load the system users at program start
     * @param int $profile_id list of user that have at least this profile level
     * @return bool true if at least one user found
     */
    function load_by_profile_and_higher(sql_db $db_con, int $profile_id): bool
    {
        $qp = $this->load_sql_by_profile_and_higher($db_con->sql_creator(), $profile_id);
        return $this->load($db_con, $qp);
    }

    /**
     * load all system users that have a code id
     */
    function load_system(sql_db $db_con): void
    {
        global $system_users;

        $this->load_by_profile_and_higher($db_con, user::RIGHT_LEVEL_SYSTEM_TEST);
        $system_users = clone $this;
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the user list
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the user list
     */
    function user(): user
    {
        return $this->usr;
    }


    // fill the user objects of the list based on a sql
    // TODO review
    private function load_sql_old($sql, sql_db $db_con): void
    {

        $db_usr_lst = $db_con->get_old($sql);

        if ($db_usr_lst != null) {
            foreach ($db_usr_lst as $db_usr) {
                $usr = new user;
                $usr->set_id($db_usr[user::FLD_ID]);
                $usr->name = $db_usr[user::FLD_NAME];
                $usr->code_id = $db_usr[sql_db::FLD_CODE_ID];
                $this->lst[] = $usr;
            }
        }
    }

    // return a list of all users that have done at least one modification compared to the standard
    function load_active(): array
    {
        log_debug('user_list->load_active');
        $lib = new library();

        global $db_con;

        // add a dummy user to calculate the standard results within the same loop
        $usr = new user;
        $usr->dummy_all();
        $this->lst[] = $usr;

        $sql = "SELECT u.user_id, u.user_name, u.code_id 
              FROM users u,
                  ( SELECT user_id 
                      FROM ( SELECT user_id 
                               FROM user_formulas
                           GROUP BY user_id 
                       UNION SELECT user_id 
                               FROM user_words
                           GROUP BY user_id 
                       UNION SELECT user_id 
                               FROM user_values
                           GROUP BY user_id ) AS cp
                  GROUP BY user_id ) AS c
            WHERE u.user_id = c.user_id
         ORDER BY u.user_id;";
        // TODO check if the user needs to be set to the original value again
        $db_con->usr_id = $usr->id();
        $this->load_sql_old($sql, $db_con);

        log_debug($lib->dsp_count($this->lst));
        return $this->lst;
    }

    /**
     * add an usr with just the id for later mass load
     */
    function add_by_id($usr_id): void
    {
        $usr = new user;
        $usr->set_id($usr_id);
        $this->lst[] = $usr;
    }

    // fill the user objects of the list based on the id
    function load_by_id(): void
    {

        global $db_con;
        $lib = new library();

        $sql_in = $lib->sql_array($this->lst, 'user_id IN(', ')');
        $sql = "SELECT user_id, user_name, code_id 
              FROM users
            WHERE " . $sql_in . "
         ORDER BY user_id;";
        $this->load_sql_old($sql, $db_con);
    }

    function name_lst(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    function names(): array
    {
        $result = array();
        foreach ($this->lst as $usr) {
            $result[] = $usr->name;
        }
        return $result;
    }

    /**
     * fill the hash based on the code id
     */
    function set_hash(): void
    {
        $this->code_id_hash = [];
        if ($this->lst != null) {
            foreach ($this->lst as $key => $usr) {
                $this->code_id_hash[$usr->code_id] = $key;
            }
        }
    }

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int
     */
    function id(string $code_id): int
    {
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->code_id_hash)) {
                $result = $this->code_id_hash[$code_id];
            } else {
                $lib = new library();
                log_err('User id not found for ' . $code_id . ' in ' . $lib->dsp_array($this->code_id_hash));
            }
        } else {
            log_debug('Type code id not not set');
        }
        return $result;
    }

    /**
     * create dummy system user list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->lst = array();
        $this->code_id_hash = array();
        $type = new user();
        $type->name = user::SYSTEM_NAME;
        $type->code_id = user::SYSTEM_CODE_ID;
        $this->lst[1] = $type;
        $this->code_id_hash[user::SYSTEM_NAME] = 1;
        $type = new user();
        $type->name = user::SYSTEM_TEST_NAME;
        $type->code_id = user::SYSTEM_TEST_PROFILE_CODE_ID;
        $this->lst[2] = $type;
        $this->code_id_hash[user::SYSTEM_TEST_PROFILE_CODE_ID] = 2;

    }

}