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

namespace Zukunft\ZukunftCom\main\php\cfg\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_SYSTEM . 'list_db_read.php';
//include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_GROUP . 'group.php';
//include_once paths::MODEL_GROUP . 'group_db.php';
//include_once paths::MODEL_GROUP . 'group_id.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
//include_once paths::MODEL_REF . 'ref.php';
//include_once paths::MODEL_REF . 'source.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_db.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VALUE . 'value_base.php';
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_COMPONENT . 'component_link_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_list.php';
include_once paths::MODEL_FORMULA . 'formula_list.php';
// the value change classes of change_value::CHANGE_CLASSES, which load_by_user reads one by one
include_once paths::MODEL_LOG . 'change_value.php';
include_once paths::MODEL_LOG . 'change_values_prime.php';
include_once paths::MODEL_LOG . 'change_values_norm.php';
include_once paths::MODEL_LOG . 'change_values_big.php';
include_once paths::MODEL_LOG . 'change_values_time_prime.php';
include_once paths::MODEL_LOG . 'change_values_time_norm.php';
include_once paths::MODEL_LOG . 'change_values_time_big.php';
include_once paths::MODEL_LOG . 'change_values_text_prime.php';
include_once paths::MODEL_LOG . 'change_values_text_norm.php';
include_once paths::MODEL_LOG . 'change_values_text_big.php';
include_once paths::MODEL_LOG . 'change_values_geo_prime.php';
include_once paths::MODEL_LOG . 'change_values_geo_norm.php';
include_once paths::MODEL_LOG . 'change_values_geo_big.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VIEW . 'term_view_list.php';
include_once paths::MODEL_VIEW . 'view_relation_list.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST_FIELDS . 'group_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\group\group_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\const\fields\group_fields;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_read;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_id;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_base;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\library;

class change_log_list extends list_db_read
{


    // TODO add cast
    // TODO add JSON export test
    // TODO add API test
    // TODO add table view
    // TODO add table view unit test
    // TODO add table view db read test


    /*
     * api
     */

    /**
     * create the api json array with one entry per change so that the frontend
     * can show e.g. the recent changes of a word on the default word page
     *
     * @param api_type_list|array $typ_lst configuration for the api message
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst, user_message $msg, user|null $usr = null): array
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = [];
        foreach ($this->lst() as $chg) {
            $vars[] = $chg->api_json_array($typ_lst, $msg, $usr);
        }
        return $vars;
    }

    /**
     * set the name of the changed object on each change of this list, e.g. the word name of a word
     * change, so that a change log listing the changes of more than one object can name the changed
     * object (see web/log/change_log_named::object_prefix); the names are loaded with one query per
     * object type, because a change log page can contain many changes
     *
     * called by the api controller only for a change log that spans objects (the changes of one
     * user), because an object page already names the object it shows; an object type that is not
     * (yet) included here keeps an empty name and the frontend then shows the change without the
     * object, so a missing type never hides a change
     *
     * @param user $usr the user who has requested the change log, so that the names are the ones
     *                  this user may see
     * @param user_message $msg to collect the problems of loading the changed objects
     * @return void
     */
    function load_row_names(user $usr, user_message $msg): void
    {
        $names = [];
        $ids = $this->row_ids_by_table();
        foreach ($this->name_lists($usr) as $table => $obj_lst) {
            $row_ids = $ids[$table] ?? [];
            if ($row_ids != []) {
                $obj_lst->load_by_ids($row_ids, $msg);
                // a value has no name of its own, so its list loads the phrases of the group
                $obj_lst->load_names_related($msg);
                $names[$table] = $this->names_by_id($obj_lst->lst());
            }
        }
        foreach ($this->lst() as $chg) {
            $chg->row_name = $names[$this->std_table($chg->table())][$chg->row_id] ?? null;
        }
    }

    /**
     * the empty object list per standard table name that can load the names of the changed rows
     * of that table with one query; a table that is missing here simply gets no object name in
     * the change log, so add the list of an object type as soon as its page or its overwrites
     * are shown (see docs/llm/pending.md)
     *
     * @param user $usr the user for whom the object names should be loaded
     * @return array the empty name list by the standard table name
     */
    private function name_lists(user $usr): array
    {
        return [
            change_tables::WORD => new word_list($usr),
            change_tables::TRIPLE => new triple_list($usr),
            change_tables::VALUE => new value_list($usr),
            change_tables::FORMULA => new formula_list($usr),
            change_tables::FORMULA_LINK => new formula_link_list($usr),
            change_tables::VIEW_LINK => new component_link_list($usr),
            change_tables::VIEW_TERM_LINK => new term_view_list($usr),
            change_tables::VIEW_RELATION => new view_relation_list($usr),
        ];
    }

    /**
     * the row ids of this list grouped by the standard table name, so that the names of the changed
     * objects can be loaded with one query per object type; a user sandbox (overlay) change is
     * grouped with the change of the standard object, because both name the same object
     *
     * @return array a list of the changed row ids by the standard table name
     */
    private function row_ids_by_table(): array
    {
        $result = [];
        foreach ($this->lst() as $chg) {
            $table = $this->std_table($chg->table());
            // the id of a value is its group id, which is a text for a group of more than four
            // phrases, so only a numeric id is used as an int
            $row_id = is_numeric($chg->row_id) ? (int)$chg->row_id : $chg->row_id;
            // the same object is usually changed more than once, so load each id only once
            if ($row_id != null and $row_id != 0
                and !in_array($row_id, $result[$table] ?? [], true)) {
                $result[$table][] = $row_id;
            }
        }
        return $result;
    }

    /**
     * @param string $table the change log table name e.g. 'user_words'
     * @return string the name of the table with the standard objects e.g. 'words'
     */
    private function std_table(string $table): string
    {
        $result = $table;
        if (str_starts_with($table, change_tables::USER_PREFIX)) {
            $result = substr($table, strlen(change_tables::USER_PREFIX));
        }
        return $result;
    }

    /**
     * @param array $lst a list of named objects e.g. the loaded words
     * @return array the object name by the object id
     */
    private function names_by_id(array $lst): array
    {
        $result = [];
        foreach ($lst as $obj) {
            $result[$obj->id()] = $obj->name();
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * load the changes of one user including the value changes, which are logged in one table per
     * value type and group id type and can therefore not be read with the query of the named
     * objects; the lists are merged, sorted by time and cut to the page limit, because each query
     * returns its own newest rows (same reason as in value_list::load_by_ids)
     *
     * @param user $usr the user sandbox object
     * @return bool true if at least one change found
     */
    function load_by_user(user $usr, user_message $msg): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_user($sc, $usr);
        $result = $this->load($qp, $usr, $msg);
        foreach (change_value::CHANGE_CLASSES as $class) {
            $sc = $db_con->sql_creator();
            $qp = $this->load_sql_by_user_value($sc, $usr, new $class($usr));
            if ($this->load($qp, $usr, $msg)) {
                $result = true;
            }
        }
        $this->sort_by_time_and_cut();
        return $result;
    }

    /**
     * sort the merged changes of load_by_user by the change time, newest first, and keep only the
     * page limit, because every single query has selected its own newest rows
     * @return void
     */
    private function sort_by_time_and_cut(): void
    {
        $lst = $this->lst();
        usort($lst, fn(change $a, change $b) => [$b->time(), $b->id()] <=> [$a->time(), $a->id()]);
        if ($this->limit > 0) {
            $lst = array_slice($lst, 0, $this->limit);
        }
        $this->set_lst($lst);
    }

    /**
     * load the latest changes of one object
     * @param sandbox|sandbox_multi $sbx e.g. the word with id set or the value with the group id set
     * @param user $usr who has requested to see the changed
     * @param user_message $msg to collect any problem while loading the changes
     * @return bool true if at least one change found
     */
    function load_obj_last(sandbox|sandbox_multi $sbx, user $usr, user_message $msg): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_obj_last($sc, $sbx::class, $sbx->id(), $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load the latest changes of one object
     * @param sandbox $sbx e.g. the word with id set
     * @param user $usr who has requested to see the changed
     * @param user_message $msg to collect any problem while loading the changes
     * @param string $fld the field name to filter the changes for
     * @return bool true if at least one change found
     */
    function load_obj_field_last(sandbox $sbx, user $usr, user_message $msg, string $fld): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_obj_fld($sc, $sbx::class, $fld, $sbx->id(), $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of sandbox object changes
     * e.g. the change of a value
     * @param string $class the name of the class
     * @param int|string|null $id the unique database id of the sandbox object to filter the changes
     * @param user|null $usr if set load only the changes of the given user
     * @param string|null $field_name the field that has been change e.g. 'view_id'
     *                                if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_obj_fld(
        string          $class,
        user_message $msg, int|string|null $id = null,
        user|null       $usr = null,
        string|null     $field_name = ''
    ): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            $class,
            $field_name,
            $id,
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a word
     * @param word $wrd the word to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_wrd(word $wrd, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            word::class,
            $field_name,
            $wrd->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a verb
     * @param verb $trp the verb to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'verb_name'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_vrb(verb $trp, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            verb::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a triple
     * @param triple $trp the triple to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_trp(triple $trp, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            triple::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a value
     * @param value_base $val the value to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'numeric_value'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_val(value_base $val, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;

        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            value::class,
            $field_name,
            $val->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a formula
     * @param formula $trp the formula to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_frm(formula $trp, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            formula::class,
            $field_name,
            $trp->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a source
     * @param source $src the source to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_src(source $src, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            source::class,
            $field_name,
            $src->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a view
     * @param view $msk the view to which the view changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_ui(view $msk, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            view::class,
            $field_name,
            $msk->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }

    /**
     * load a list of the view changes of a view component
     * @param component $cmp the view to which the view component changes should be loaded
     * @param string $field_name the field that has been change e.g. 'view_id'
     *                           if not set, all changes are returned
     * @return bool true if at least one change found
     */
    function load_by_fld_of_cmp(component $cmp, user $usr, user_message $msg, string $field_name = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_obj_fld(
            $db_con->sql_creator(),
            component::class,
            $field_name,
            $cmp->id(),
            $usr);
        return $this->load($qp, $usr, $msg);
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve the overwrites done by the given user
     *
     * only the changes of the user sandbox (overlay) tables are selected, because this is what the
     * only consumer shows (the all user overwrites column of the user page, see
     * web/component/execute/ui_log::all_user_overwrites) and because only then the row limit is
     * correct: with all changes selected, the limit would cut off the overwrites of a user who has
     * also changed many standard objects (over 15'000 for the system user), so that the column
     * would show none of the overwrites
     *
     * @param sql_creator $sc with the target db_type set
     * @param user $usr the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_user(sql_creator $sc, user $usr): sql_par
    {
        $qp = $this->load_sql($sc, 'user_last', self::class);

        $sc->add_where(user_db::FLD_ID, $usr->id);
        // TODO replace 'l2' with a var or const (like load_sql_by_obj_fld)
        $sc->add_where(change_field::FLD_TABLE, $this->user_table_ids(), sql_par_type::INT_LIST, 'l2');
        // the page limit set by the caller, so that a user page never reads the complete change
        // log of the user just to show the newest rows
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the value overwrites done by the given user from one of
     * the value change tables; the same selection as load_sql_by_user, but built from the given
     * value change class, because a value change is logged per value type and group id type
     *
     * @param sql_creator $sc with the target db_type set
     * @param user $usr the user sandbox object
     * @param change_value $log_val an empty log object of the value change class to read
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_user_value(sql_creator $sc, user $usr, change_value $log_val): sql_par
    {
        $lib = new library();
        $qp = $log_val->load_sql($sc, 'user_last');
        // the three numeric value change classes share the query base name of change_value,
        // so the name is set from the class to keep one prepared statement name per table
        $qp->name = $lib->class_to_name($log_val::class) . sql::NAME_SEP . 'user_last';
        $sc->set_name($qp->name);

        $sc->add_where(user_db::FLD_ID, $usr->id);
        // TODO replace 'l2' with a var or const (like load_sql_by_obj_fld)
        $sc->add_where(change_field::FLD_TABLE, $this->user_table_ids(), sql_par_type::INT_LIST, 'l2');
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * @return array the database ids of the user sandbox (overlay) tables e.g. of user_words,
     *               used to select only the changes that a user has done on an own overlay row
     */
    private function user_table_ids(): array
    {
        global $sys;
        $result = [];
        foreach (change_tables::USER_TABLES as $table_name) {
            // checked without auto-adding the table, because a table that is not used yet
            // simply has no changes to select
            $table_id = $sys->typ_lst->cng_tbl->id($table_name, false);
            if ($table_id > 0) {
                $result[] = $table_id;
            }
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of the change log
     * TODO use class name instead of TBL_CHANGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par($this::class);
        $sc->set_class(change::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_fields(change::FLD_NAMES);
        $sc->set_join_fields(array(user_db::FLD_NAME), user::class);
        $sc->set_join_fields(array(change_fields::FLD_TABLE), change_field::class);
        $sc->set_order(change_log::FLD_TIME, sql::ORDER_DESC);

        return $qp;
    }


    /*
     * load internals
     */

    private function table_field_to_query_name(string $class, string $field_name): string
    {
        $result = '';
        if ($class == word::class) {
            if ($field_name == change_fields::FLD_WORD_VIEW) {
                $result = 'dsp_of_wrd';
            } else {
                if ($field_name != '') {
                    $result = $field_name . '_of_wrd';
                } else {
                    $result = 'wrd';
                }
                log_info('field name ' . $field_name . ' not expected for table ' . $class);
            }
        } elseif ($class == triple::class) {
            if ($field_name == change_fields::FLD_TRIPLE_VIEW) {
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
            $result = $field_name . '_of_msk';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == component::class) {
            $result = $field_name . '_of_cmp';
            log_info('field name ' . $field_name . ' not expected for table ' . $class);
        } elseif ($class == ref::class) {
            if ($field_name != '') {
                $result = $field_name . '_of_ref';
                log_info('field name ' . $field_name . ' not expected for table ' . $class);
            } else {
                $result = 'ref';
            }
        } elseif (is_subclass_of($class, type_object::class)) {
            // a type row e.g. a sys log function logs to the changes table like the named objects
            // (used by the test cleanup to remove the change log of a type test row); type changes
            // are rare, so the query name is simply based on the class name
            $lib = new library();
            if ($field_name != '') {
                $result = $field_name . '_of_' . $lib->class_to_name($class);
            } else {
                $result = $lib->class_to_name($class);
            }
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
        global $sys;

        // prepare sql to get the view changes of a user sandbox object e.g. word
        $lib = new library();
        $table_name = $lib->class_to_table($class);
        $table_id = $sys->typ_lst->cng_tbl->id($table_name);
        if ($field_name != '') {
            $table_field_name = $table_id . $field_name;
            $table_field_id = $sys->typ_lst->cng_fld->id($table_field_name);
        } else {
            $table_field_id = $table_id;
        }
        // a change of a user sandbox row is logged to the user overlay table (e.g. user_words),
        // so the all-changes query must select the changes of both tables,
        // so that the test cleanup can remove the complete change log of a test row
        // before deleting the row (see test_base::delete_change_log_of_obj); checked without auto-adding the table
        $usr_table_id = $sys->typ_lst->cng_tbl->id(sql_db::TBL_USER_PREFIX . $table_name, false);
        $log_named = new change($usr);
        $query_ext = $this->table_field_to_query_name($class, $field_name);
        if ($field_name == '' and $usr_table_id > 0) {
            // an own prepared query name, because the table filter parameter is a list here
            // and a shared name with different parameter types is rejected by the database
            $query_ext .= sql::NAME_SEP . 'with_usr';
        }
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
        if ($field_name != '') {
            $sc->add_where(change::FLD_FIELD_ID, $table_field_id);
        } else {
            // TODO replace 'l2' with a var or const
            if ($usr_table_id > 0) {
                $sc->add_where(change_field::FLD_TABLE, [$table_field_id, $usr_table_id], sql_par_type::INT_LIST, 'l2');
            } else {
                $sc->add_where(change_field::FLD_TABLE, $table_field_id, null, 'l2');
            }
        }
        if ($class == value::class) {
            $sc->add_where(group_fields::FLD_ID, $id);
        } else {
            $sc->add_where(change_log::FLD_ROW_ID, $id);
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
        // add 'last' to the query name because this query selects by the row id only (3 parameters),
        // whereas load_sql_obj_fld without a field name shares the base name but filters additionally
        // by the change table (4 parameters); a shared prepared name with different parameters makes
        // the database reject the second bind
        $query_ext .= sql::NAME_SEP . 'last';
        $qp = $log_named->load_sql($sc, $query_ext);
        if ($class == value::class) {
            $sc->add_where(group_fields::FLD_ID, $id);
        } else {
            $sc->add_where(change_log::FLD_ROW_ID, $id);
        }
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load this list of changes
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param user $usr the user who wants to see the changes e.g. to check the permission
     * @return bool true if at least one change found
     */
    private function load(sql_par $qp, user $usr, user_message $msg): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp, $msg, 'change log list');
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change($usr);
                    $chg->row_mapper($db_row, $msg, '', $usr);
                    // allow duplicates, because the change id is unique per change table only:
                    // a list that merges the changes of the named objects with the changes of
                    // the value tables (see load_by_user) always has repeated ids
                    $this->add_obj($chg, true);
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
            $msg = $first->dsp();
        }
        return $msg;
    }


}