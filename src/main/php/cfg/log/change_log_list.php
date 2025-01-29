<?php

/*

    model/log/change_log_list.php - read the changes from the database and forward them to the API
    -----------------------------

    for writing the user change to the database the classes model/user/user_log* are used

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

namespace cfg\log;

include_once MODEL_SYSTEM_PATH . 'base_list.php';
//include_once MODEL_COMPONENT_PATH . 'component.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_type.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_GROUP_PATH . 'group.php';
//include_once MODEL_GROUP_PATH . 'group_id.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_USER_PATH . 'user.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VERB_PATH . 'verb.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_WORD_PATH . 'word.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once WEB_LOG_PATH . 'change_log_list.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'library.php';

use cfg\system\base_list;
use cfg\component\component;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\formula\formula;
use cfg\group\group;
use cfg\group\group_id;
use cfg\sandbox\sandbox;
use cfg\ref\source;
use cfg\user\user;
use cfg\value\value;
use cfg\value\value_base;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\word;
use cfg\word\triple;
use shared\library;

class change_log_list extends base_list
{


    // TODO add cast
    // TODO add JSON export test
    // TODO add API controller
    // TODO add API test
    // TODO add table view
    // TODO add table view unit test
    // TODO add table view db read test


    /*
     * load interface
     */

    /**
     * load the changes of one user
     * @param user $usr the user sandbox object
     * @return bool true if at least one change found
     */
    function load_by_user(user $usr): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_user($sc, $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load the latest changes of one object
     * @param sandbox $sbx e.g. the word with id set
     * @param user $usr who has requested to see the changed
     * @return bool true if at least one change found
     */
    function load_obj_last(sandbox $sbx, user $usr): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_obj_last($sc, $sbx::class, $sbx->id(), $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load the latest changes of one object
     * @param sandbox $sbx e.g. the word with id set
     * @param user $usr who has requested to see the changed
     * @return bool true if at least one change found
     */
    function load_obj_field_last(sandbox $sbx, user $usr, string $fld): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_obj_fld($sc, $sbx::class, $fld, $sbx->id(), $usr);
        return $this->load($qp, $usr);
    }

    /**
     * create an SQL statement to retrieve the changes done by the given user
     *
     * @param sql_creator $sc with the target db_type set
     * @param user $usr the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_user(sql_creator $sc, user $usr): sql_par
    {
        $qp = $this->load_sql($sc, 'user_last', self::class);

        $sc->add_where(user::FLD_ID, $usr->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of the change log
     * TODO use class name instead of TBL_CHANGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par($this::class);
        $sc->set_class(change::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_fields(change::FLD_NAMES);
        $sc->set_join_fields(array(user::FLD_NAME), user::class);
        $sc->set_join_fields(array(change_field_list::FLD_TABLE), change_field::class);
        $sc->set_order(change_log::FLD_TIME, sql::ORDER_DESC);

        return $qp;
    }

    /**
     * load a list of the view changes of a word
     * @param word $wrd the word to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_wrd(word $wrd, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            word::class,
            $field_name,
            $wrd->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a verb
     * @param verb $trp the verb to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'verb_name'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_vrb(verb $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            verb::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a triple
     * @param triple $trp the triple to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_trp(triple $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            triple::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a value
     * @param value_base $val the value to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'numeric_value'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_val(value_base $val, user $usr, string $field_name = ''): bool
    {
        global $db_con;

        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            value::class,
            $field_name,
            $val->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a formula
     * @param formula $trp the formula to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_frm(formula $trp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            formula::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a source
     * @param source $src the source to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_src(source $src, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            source::class,
            $field_name,
            $src->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a view
     * @param view $msk the view to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_dsp(view $msk, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            view::class,
            $field_name,
            $msk->id(),
            $usr);
        return $this->load($qp, $usr);
    }

    /**
     * load a list of the view changes of a view component
     * @param component $cmp the view to which the view component changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_cmp(component $cmp, user $usr, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            component::class,
            $field_name,
            $cmp->id(),
            $usr);
        return $this->load($qp, $usr);
    }


    /*
     * load internals
     */

    private function table_field_to_query_name(string $class, string $field_name): string
    {
        $result = '';
        if ($class == word::class) {
            if ($field_name == change_field_list::FLD_WORD_VIEW) {
                $result = 'dsp_of_wrd';
            } else {
                $result = $field_name . '_of_wrd';
                log_info('field name ' . $field_name . ' not expected for table ' . $class);
            }
        } elseif ($class == triple::class) {
            if ($field_name == change_field_list::FLD_TRIPLE_VIEW) {
                $result = 'dsp_of_trp';
            } else {
                $result = $field_name . '_of_trp';
                log_info('field name ' . $field_name . ' not expected for table ' . $class);
            }
        } elseif ($class == verb::class) {
            $result = $field_name . '_of_vrb';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == group::class) {
            $result = $field_name . '_of_grp';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == value::class) {
            $result = $field_name . '_of_val';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == formula::class) {
            $result = $field_name . '_of_frm';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == source::class) {
            $result = $field_name . '_of_src';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == view::class) {
            $result = $field_name . '_of_dsp';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == component::class) {
            $result = $field_name . '_of_cmp';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } else {
            log_err('table name ' . $class . ' not expected');
        }
        return $result;
    }

    /**
     * prepare sql to get the changes of one field of one user sandbox object
     * e.g. the when and how a user has changed the way a word should be shown in the user interface
     * only public for SQL unit testing
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the class name of the user sandbox object to select the table e.g. 'word'
     * @param string $field_name the field that has been change e.g. 'view'
     * @param string|int $id the database id of the user sandbox object that has been changed
     * @param user $usr
     * @return sql_par
     */
    function load_sql_obj_fld(
        sql_creator $sc,
        string      $class,
        string      $field_name,
        string|int  $id,
        user        $usr): sql_par
    {
        global $cng_tbl_cac;
        global $cng_fld_cac;

        // prepare sql to get the view changes of a user sandbox object e.g. word
        $lib = new library();
        $table_name = $lib->class_to_table($class);
        $table_id = $cng_tbl_cac->id($table_name);
        $table_field_name = $table_id . $field_name;
        $field_id = $cng_fld_cac->id($table_field_name);
        $log_named = new change($usr);
        $query_ext = $this->table_field_to_query_name($class, $field_name);
        if ($class == value::class) {
            $grp_id = new group_id();
            $typ = $grp_id->table_type($id);
            if ($typ == sql_type::PRIME) {
                $log_named = new change_values_prime($usr);
                $query_ext .= sql::NAME_SEP . sql_type::PRIME->value;
            } elseif ($typ == sql_type::BIG) {
                $log_named = new change_values_big($usr);
                $query_ext .= sql::NAME_SEP . sql_type::BIG->value;
            } else {
                $log_named = new change_values_norm($usr);
                $query_ext .= sql::NAME_SEP . sql_type::NORM->value;
            }
        } elseif ($class == group::class) {
            $grp_id = new group_id();
            $typ = $grp_id->table_type($id);
            if ($typ == sql_type::PRIME) {
                $log_named = new change($usr);
                $query_ext .= sql::NAME_SEP . sql_type::PRIME->value;
            } elseif ($typ == sql_type::BIG) {
                $log_named = new changes_big($usr);
                $query_ext .= sql::NAME_SEP . sql_type::BIG->value;
            } else {
                $log_named = new changes_norm($usr);
                $query_ext .= sql::NAME_SEP . sql_type::NORM->value;
            }
        }
        $qp = $log_named->load_sql($sc, $query_ext);
        $sc->add_where(change::FLD_FIELD_ID, $field_id);
        if ($class == value::class) {
            $sc->add_where(group::FLD_ID, $id);
        } else {
            $sc->add_where(change::FLD_ROW_ID, $id);
        }
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * prepare sql to get the last changes of a user sandbox object
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the class name of the user sandbox object to select the table e.g. 'word'
     * @param string|int $id the database id of the user sandbox object that has been changed
     * @param user $usr the user who has requested the change
     * @return sql_par the sql statement to get the latest changed
     */
    function load_sql_obj_last(
        sql_creator $sc,
        string      $class,
        string|int  $id,
        user        $usr): sql_par
    {
        global $cng_tbl_cac;
        global $cng_fld_cac;

        // prepare sql to get the view changes of a user sandbox object e.g. word
        $log_named = new change($usr);
        $query_ext = $this->table_field_to_query_name($class, '');
        if ($class == value::class) {
            $grp_id = new group_id();
            $typ = $grp_id->table_type($id);
            if ($typ == sql_type::PRIME) {
                $log_named = new change_values_prime($usr);
                $query_ext .= sql::NAME_SEP . sql_type::PRIME->value;
            } elseif ($typ == sql_type::BIG) {
                $log_named = new change_values_big($usr);
                $query_ext .= sql::NAME_SEP . sql_type::BIG->value;
            } else {
                $log_named = new change_values_norm($usr);
                $query_ext .= sql::NAME_SEP . sql_type::NORM->value;
            }
        } elseif ($class == group::class) {
            $grp_id = new group_id();
            $typ = $grp_id->table_type($id);
            if ($typ == sql_type::PRIME) {
                $log_named = new change($usr);
                $query_ext .= sql::NAME_SEP . sql_type::PRIME->value;
            } elseif ($typ == sql_type::BIG) {
                $log_named = new changes_big($usr);
                $query_ext .= sql::NAME_SEP . sql_type::BIG->value;
            } else {
                $log_named = new changes_norm($usr);
                $query_ext .= sql::NAME_SEP . sql_type::NORM->value;
            }
        }
        $qp = $log_named->load_sql($sc, $query_ext);
        if ($class == value::class) {
            $sc->add_where(group::FLD_ID, $id);
        } else {
            $sc->add_where(change::FLD_ROW_ID, $id);
        }
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load this list of changes
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one change found
     */
    private function load(sql_par $qp, user $usr): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change($usr);
                    $chg->row_mapper($db_row);
                    $this->add_obj($chg);
                    $result = true;
                }
            }
        }

        return $result;
    }


    /*
     * modify
     */

    /**
     * add one change log entry to the change list
     * @param change|null $chg_to_add the change that should be added to the list
     * @returns bool true the log entry has been added
     */
    function add(?change $chg_to_add): bool
    {
        $result = false;
        if ($chg_to_add != null) {
            parent::add_obj($chg_to_add);
            $result = true;
        }
        return $result;
    }

    /*
     * info
     */

    /**
     * @return string with the first change description of this list
     */
    function first_msg(): string
    {
        $msg = '';
        if (!$this->is_empty()) {
            $lst = $this->lst();
            $first = $lst[array_key_first($lst)];
            $msg = $first->dsp_last();
        }
        return $msg;
    }


}