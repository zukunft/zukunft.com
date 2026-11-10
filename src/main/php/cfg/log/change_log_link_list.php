<?php

/*

    model/log/change_log_link_list.php - a list of the link changes done by the users e.g. to show the link history of a word
    ----------------------------------

    loads the link change history (the change_links table) for one object e.g. a word, formula or view
    so that the frontend can show it via the API without a direct database access


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'list_db_read.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED . 'library.php';
// only used by ::class, which does not load the class, like in change_log_list.php
//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_WORD . 'word.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_read;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;

class change_log_link_list extends list_db_read
{

    // the change tables of the link changes shown for an object, by the class of the object
    const array OBJ_CHANGE_TABLES = [
        user::class => [change_tables::USER],
        word::class => [
            change_tables::WORD, change_tables::WORD_USR,
            change_tables::TRIPLE, change_tables::TRIPLE_USR],
        value::class => [
            change_tables::VALUE, change_tables::VALUE_USR,
            change_tables::VALUE_LINK, change_tables::VALUE_LINK_USR],
        formula::class => [
            change_tables::FORMULA, change_tables::FORMULA_USR,
            change_tables::FORMULA_LINK, change_tables::FORMULA_LINK_USR],
        view::class => [
            change_tables::VIEW, change_tables::VIEW_USR,
            change_tables::VIEW_LINK, change_tables::VIEW_LINK_USR],
        component::class => [
            change_tables::VIEW_COMPONENT, change_tables::VIEW_COMPONENT_USR,
            change_tables::VIEW_LINK, change_tables::VIEW_LINK_USR],
    ];

    // the id fields of a link change that select the changes of an object, any of them may match
    const array OBJ_ID_FIELDS = [
        user::class => [user_db::FLD_ID],
        word::class => [
            change_link::FLD_OLD_FROM_ID, change_link::FLD_OLD_TO_ID,
            change_link::FLD_NEW_FROM_ID, change_link::FLD_NEW_TO_ID],
        value::class => [change_link::FLD_OLD_FROM_ID, change_link::FLD_NEW_FROM_ID],
        formula::class => [change_link::FLD_OLD_FROM_ID, change_link::FLD_NEW_FROM_ID],
        view::class => [change_link::FLD_OLD_FROM_ID, change_link::FLD_NEW_FROM_ID],
        component::class => [change_link::FLD_OLD_TO_ID, change_link::FLD_NEW_TO_ID],
    ];


    /*
     * load
     */

    /**
     * load the link changes related to one object e.g. all links added to or removed from a word
     *
     * @param string $class the class name of the object whose link changes should be loaded
     * @param int|string $id the database id of the object
     * @param user $usr the user who wants to see the changes
     * @param int $size the max number of changes to load
     * @return bool true if at least one link change has been loaded
     */
    function load_by_obj(string $class, int|string $id, user $usr, user_message $msg, int $size = sql_db::ROW_LIMIT): bool
    {
        global $db_con;

        // a component link change is shown by its from side, all others by their to side
        $use_from = ($class == component::class);
        $qp = $this->load_sql_by_obj($db_con->sql_creator(), $class, $id, $usr, $size);
        return $this->load($qp, $usr, $msg, $use_from);
    }

    /**
     * create the sql statement to load the link changes related to one object from the change_links table
     * the column list matches change_link::FLD_NAMES so change_link::row_mapper can map each row
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the class name of the object whose link changes should be loaded
     * @param int|string $id the database id of the object; not used for a user, whose own changes are selected
     * @param user $usr the user who wants to see the changes
     * @param int $size the max number of changes to load
     * @return sql_par the sql statement, its unique name and the parameters; without name for an unknown class
     */
    function load_sql_by_obj(sql_creator $sc, string $class, int|string $id, user $usr, int $size = sql_db::ROW_LIMIT): sql_par
    {
        $lib = new library();
        $qp = new sql_par(change_link::class);
        if (!array_key_exists($class, self::OBJ_CHANGE_TABLES)) {
            log_warning('object "' . $class . '" is not defined for showing the link changes',
                self::class . '->load_sql_by_obj');
            $qp->name = '';
        } else {
            $qp->name .= $lib->class_to_name($class);
            $sc->set_class(change_link::class);
            $sc->set_name($qp->name);
            $sc->set_usr($usr->id);
            $sc->set_fields(array_merge(change_link::FLD_NAMES, [change_link::FLD_ROW_ID]));
            $sc->set_join_fields(array(user_db::FLD_NAME), user::class);
            // the or list of the id fields first, so that its brackets do not include the change tables
            $this->add_where_obj($sc, $class, $id, $usr);
            $sc->add_where(change_link::FLD_TABLE_ID, $this->change_table_ids($class), sql_par_type::INT_LIST);
            $sc->set_order(change_link::FLD_TIME, sql::ORDER_DESC);
            $sc->set_page($size);
            $qp->sql = $sc->sql();
            $qp->par = $sc->get_par();
        }
        return $qp;
    }

    /**
     * add the selection of the link changes of one object, any of its id fields may match
     *
     * @param sql_creator $sc the sql creator of the link change list query
     * @param string $class the class of the object, a key of OBJ_ID_FIELDS
     * @param int|string $id the database id of the object
     * @param user $usr the user whose own changes are selected for the class user
     * @return void
     */
    private function add_where_obj(sql_creator $sc, string $class, int|string $id, user $usr): void
    {
        $fields = self::OBJ_ID_FIELDS[$class];
        $sel_id = $id;
        if ($class == user::class) {
            $sel_id = $usr->id;
        }
        $spt = null;
        if (count($fields) > 1) {
            $spt = sql_par_type::INT_OR;
        }
        foreach ($fields as $fld) {
            $sc->add_where($fld, $sel_id, $spt);
        }
    }

    /**
     * @param string $class the class of the object, a key of OBJ_CHANGE_TABLES
     * @return array the database ids of the change tables of the link changes of the object
     */
    private function change_table_ids(string $class): array
    {
        global $sys;
        $result = [];
        foreach (self::OBJ_CHANGE_TABLES[$class] as $tbl_name) {
            $result[] = $sys->typ_lst->cng_tbl->id($tbl_name);
        }
        return $result;
    }

    /**
     * map the loaded change_links rows to a list of change_link objects
     *
     * @param sql_par $qp the sql statement to load the link changes
     * @param user $usr the user who wants to see the changes
     * @param bool $use_from true to show the from side of the link instead of the to side (e.g. for components)
     * @return bool true if at least one link change has been loaded
     */
    private function load(sql_par $qp, user $usr, user_message $msg, bool $use_from = false): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err_msg('The query cannot be created to load a ' . self::class, $msg);
        } else {
            $db_con->usr_id = $usr->id;
            $db_rows = $db_con->get($qp, $msg, 'link change list');
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change_link($usr);
                    $row_msg = new user_message($msg->usr); // a buffer per row, merged below, see docs/llm/dependent-errors.md
                    if ($chg->row_mapper($db_row, $row_msg)) {
                        // normalise the display text so the api always shows the relevant side as old/new text
                        if ($use_from) {
                            $chg->old_text_to = $chg->old_text_from;
                            $chg->new_text_to = $chg->new_text_from;
                        }
                        $this->add_obj($chg);
                        $result = true;
                    }
                    $msg->merge($row_msg);
                }
            }
        }

        return $result;
    }

    /**
     * add one link change to the list
     * @param change_link|null $chg_to_add the link change that should be added to the list
     * @returns bool true the link change has been added
     */
    function add(?change_link $chg_to_add): bool
    {
        $result = false;
        if ($chg_to_add != null) {
            parent::add_obj($chg_to_add);
            $result = true;
        }
        return $result;
    }

}