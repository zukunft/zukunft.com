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

namespace cfg\user;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_HELPER_PATH . 'db_object_multi.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_REF_PATH . 'ref.php';
//include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_profile.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_time.php';
//include_once MODEL_VALUE_PATH . 'value_text.php';
//include_once MODEL_VALUE_PATH . 'value_geo.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_WORD_PATH . 'triple.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'users.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\formula\formula;
use cfg\helper\db_object;
use cfg\helper\db_object_multi;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\value\value;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use shared\const\users;
use shared\enum\messages as msg_id;
use shared\enum\user_profiles;
use shared\library;
use shared\types\api_type_list;

global $system_users;

// TODO base it on the base_list object
class user_list
{
    // internal db field name to count the changes by on user
    const FLD_CHANGES = 'changes';

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
     * set and get
     */

    function lst(): ?array
    {
        return $this->lst;
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

        $sc->set_class(user::class);
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(user_db::FLD_NAMES_LIST);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user list by the id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids list of user ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
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
        $sc->add_where(sql::FLD_CODE_ID, $code_id);
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
            user_profile::class,
            user_db::FLD_PROFILE,
            user_profile::FLD_ID);
        $sc->add_where(user_profile::FLD_LEVEL, $profile_id, sql_par_type::INT_HIGHER, sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    private function load_sql_count_changes_dbo(db_object|db_object_multi $dbo): string
    {
        $lib = new library();
        $class = $lib->class_to_name($dbo::class);
        $sql = 'SELECT ' . user::FLD_ID . ',';
        $id_fields = $dbo->id_field();
        if (is_array($id_fields)) {
            $sql .= ' COUNT (*) AS ' . self::FLD_CHANGES;
        } else {
            $sql .= ' COUNT (' . $dbo->id_field() . ') AS ' . self::FLD_CHANGES;
        }
        $sql .= ' FROM ' . sql_db::TBL_USER_PREFIX . $class . sql_db::TABLE_EXTENSION;
        $sql .= ' GROUP BY ' . user::FLD_ID;
        return $sql;
    }

    /**
     * TODO add the time, text and geo and prime and big value tables
     * @return string
     */
    private function load_sql_count_all_changes(): string
    {
        $sql = $this->load_sql_count_changes_dbo(new word($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new triple($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new value($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new formula($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new ref($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new source($this->user()));
        $sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new view($this->user()));
        //$sql .= ' ' . sql::UNION . ' ' . $this->load_sql_count_changes_dbo(new component($this->user()));
        /* TODO activate if a class name can be used to create a class instance
        foreach (sql_db::CLASSES_WITH_USER_CHANGES as $class) {
            $sql_count .= $this->load_sql_count_changes($class);
        }
        */
        return $sql;
    }

    private function load_sql_count_sum_changes(): string
    {
        $sql = 'SELECT ' . sql_db::GRP_TBL . '.' . user::FLD_ID . ',';
        $sql .= ' SUM (' . sql_db::GRP_TBL . '.' . self::FLD_CHANGES . ') AS ' . self::FLD_CHANGES;
        $sql .= ' FROM ( ' . $this->load_sql_count_all_changes() . ') ' . sql_db::GRP_TBL;
        $sql .= ' GROUP BY ' . user::FLD_ID;
        return $sql;
    }

    /**
     * create an SQL statement to retrieve users that have changed something
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_count_changes(sql_creator $sc): sql_par
    {
        $sub_sql = '(' . $this->load_sql_count_sum_changes() . ')';
        $qp = $this->load_sql($sc, 'count_changes');
        $sc->set_join_sql($sub_sql, array(self::FLD_CHANGES), user::FLD_ID);
        $sc->add_where(self::FLD_CHANGES, '', sql_par_type::NOT_NULL, sql_db::LNK_TBL);
        $sc->set_order(self::FLD_CHANGES, sql::ORDER_DESC, sql_db::LNK_TBL);
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
     * load a list of users by the id
     *
     * @param sql_db $db_con the database link as a parameter to load the system users at program start
     * @param array $ids list of user ids that should be loaded
     * @return bool true if at least one user found
     */
    function load_by_ids(sql_db $db_con, array $ids): bool
    {
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($db_con, $qp);
    }

    /**
     * load a list of users by the id
     *
     * @param sql_db $db_con the database link as a parameter to load the system users at program start
     * @param string $code_id list of user ids that should be loaded
     * @return bool true if at least one user found
     */
    function load_by_code_id(sql_db $db_con, string $code_id): bool
    {
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id);
        return $this->load($db_con, $qp);
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
        $this->load_by_profile_and_higher($db_con, users::RIGHT_LEVEL_SYSTEM_TEST);
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
                $usr->name = $db_usr[user_db::FLD_NAME];
                $usr->code_id = $db_usr[sql::FLD_CODE_ID];
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

        $qp = $this->load_sql_count_changes($db_con->sql_creator());
        $this->load($db_con, $qp);

        log_debug($lib->dsp_count($this->lst));
        return $this->lst;
    }

    function name_lst(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    function id_lst(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $usr) {
                $result[] = $usr->id();
            }
        }
        return $result;
    }

    function names(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $usr) {
                $result[] = $usr->name;
            }
        }
        return $result;
    }

    function emails(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $usr) {
                $result[] = $usr->email;
            }
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
     * create a dummy system user list for the unit tests without database connection that matches the core system user list
     */
    function load_dummy(): void
    {
        global $usr_pro_cac;

        $this->lst = array();
        $this->code_id_hash = array();

        $usr = new user(users::SYSTEM_NAME, users::SYSTEM_EMAIL);
        $usr->code_id = users::SYSTEM_CODE_ID;
        $usr->profile_id = $usr_pro_cac->id(user_profiles::SYSTEM);
        $this->lst[users::SYSTEM_ID] = $usr;
        $this->code_id_hash[users::SYSTEM_CODE_ID] = users::SYSTEM_ID;

        $usr = new user(users::SYSTEM_ADMIN_NAME, users::SYSTEM_ADMIN_EMAIL);
        $usr->code_id = users::SYSTEM_ADMIN_CODE_ID;
        $usr->profile_id = $usr_pro_cac->id(user_profiles::ADMIN);
        $this->lst[users::SYSTEM_ADMIN_ID] = $usr;
        $this->code_id_hash[users::SYSTEM_ADMIN_CODE_ID] = users::SYSTEM_ADMIN_ID;

        $usr = new user(users::SYSTEM_TEST_NAME, users::SYSTEM_TEST_EMAIL);
        $usr->code_id = users::SYSTEM_TEST_CODE_ID;
        $usr->profile_id = $usr_pro_cac->id(user_profiles::TEST);
        $this->lst[users::SYSTEM_TEST_ID] = $usr;
        $this->code_id_hash[users::SYSTEM_TEST_CODE_ID] = users::SYSTEM_TEST_ID;

        $usr = new user(users::SYSTEM_TEST_PARTNER_NAME, users::SYSTEM_TEST_PARTNER_EMAIL);
        $usr->code_id = users::SYSTEM_TEST_PARTNER_CODE_ID;
        $usr->profile_id = $usr_pro_cac->id(user_profiles::TEST);
        $this->lst[users::SYSTEM_TEST_PARTNER_ID] = $usr;
        $this->code_id_hash[users::SYSTEM_TEST_PARTNER_CODE_ID] = users::SYSTEM_TEST_PARTNER_ID;

        $usr = new user(users::SYSTEM_TEST_NORMAL_NAME, users::SYSTEM_TEST_NORMAL_EMAIL);
        $usr->code_id = users::SYSTEM_TEST_NORMAL_CODE_ID;
        $usr->profile_id = $usr_pro_cac->id(user_profiles::NORMAL);
        $this->lst[users::SYSTEM_TEST_NORMAL_ID] = $usr;
        $this->code_id_hash[users::SYSTEM_TEST_NORMAL_CODE_ID] = users::SYSTEM_TEST_NORMAL_ID;

    }


    /*
     * info
     */

    /**
     * @returns int the number of elements in the list
     */
    function count(): int
    {
        if ($this->lst() == null) {
            return 0;
        } else {
            return count($this->lst);
        }
    }

    /**
     * @return bool true if the list is already empty
     */
    function is_empty(): bool
    {
        $result = true;
        if ($this->lst() != null) {
            if (count($this->lst) > 0) {
                $result = false;
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a user to the list
     *
     * @param user $usr_to_add an object with a unique database id that should be added to the list
     * @param bool $allow_duplicates set it to true if duplicate db id should be allowed
     * @returns user_message if adding failed or something is strange the messages for the user with the suggested solutions
     */
    function add(
        user $usr_to_add,
        bool $allow_duplicates = false
    ): user_message
    {
        $usr_msg = new user_message();

        // check boolean first because in_array might take longer
        if ($allow_duplicates) {
            $this->add_direct($usr_to_add);
        } else {
            if ($usr_to_add->id() != 0) {
                if (!array_key_exists($usr_to_add->id(), $this->id_lst())) {
                    if (!array_key_exists($usr_to_add->name(), $this->names())) {
                        $this->add_direct($usr_to_add);
                    } else {
                        $usr_msg->add_id(msg_id::LIST_DOUBLE_ENTRY);
                    }
                } else {
                    $usr_msg->add_id(msg_id::LIST_DOUBLE_ENTRY);
                }
            } elseif ($usr_to_add->name() != '') {
                if (!array_key_exists($usr_to_add->name(), $this->names())) {
                    $this->add_direct($usr_to_add);
                } else {
                    $usr_msg->add_id(msg_id::LIST_DOUBLE_ENTRY);
                }
            } elseif ($usr_to_add->email() != '') {
                if (!array_key_exists($usr_to_add->email(), $this->emails())) {
                    $this->add_direct($usr_to_add);
                } else {
                    $usr_msg->add_id(msg_id::LIST_DOUBLE_ENTRY);
                }
            } else {
                $usr_msg->add_id_with_vars(msg_id::LIST_USER_INVALID,
                    [
                        msg_id::VAR_USER_NAME => $usr_to_add->dsp_id(),
                        msg_id::VAR_USER_LIST_NAME => $this->names(),
                    ]);
            }
        }
        return $usr_msg;
    }

    /**
     * add the user to the list without duplicate check
     * and later add the id to the id hash
     *
     * @param user $usr_to_add
     * @return void
     */
    protected function add_direct(user $usr_to_add): void
    {
        $this->lst[] = $usr_to_add;
    }


    /*
     * api
     */

    /**
     * create an array for the json api message
     *
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr_req the user for whom the api message should be created which can differ from the session user
     * @returns array with the json fields to create an api message
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr_req = null): array
    {
        $lst = [];
        foreach ($this->lst() as $usr) {
            $vars = $usr->api_json_array($typ_lst, $usr_req);
            $lst[] = array_filter($vars, fn($value) => !is_null($value) && $value !== '');
        }
        return $lst;
    }


    /*
     * save
     */

    /**
     * simple loop to save all users of the list
     * because there are probably not many users to save at once
     *
     * @return user_message in case of an issue the problem description what has failed and a suggested solution
     */
    function save(): user_message
    {
        $usr_msg = new user_message();

        if ($this->is_empty()) {
            $usr_msg->add_info_text('no users to save');
        } else {
            foreach ($this->lst() as $usr) {
                $usr_msg->add($usr->save());
            }
        }

        return $usr_msg;
    }

}