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
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_read;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;

class change_log_link_list extends list_db_read
{

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
    function load_by_obj(string $class, int|string $id, user $usr, int $size = sql_db::ROW_LIMIT): bool
    {
        global $db_con;

        $lib = new library();
        // a component/view_cmp link change is shown by its from side, all others by their to side
        $name = $lib->class_to_name($class);
        $use_from = ($name == 'view_cmp' or $name == 'component');
        $sql = $this->load_sql_by_obj($db_con, $class, $id, $usr, $size);
        return $this->load($sql, $usr, $use_from);
    }

    /**
     * create the SQL statement to load the link changes related to one object from the change_links table
     * the column list matches change_link::FLD_NAMES so change_link::row_mapper can map each row
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the class name of the object whose link changes should be loaded
     * @param int|string $id the database id of the object
     * @param user $usr the user who wants to see the changes
     * @param int $size the max number of changes to load
     * @return string the SQL statement to load the link changes
     */
    function load_sql_by_obj(sql_db $db_con, string $class, int|string $id, user $usr, int $size = sql_db::ROW_LIMIT): string
    {
        global $sys;

        $lib = new library();
        $name = $lib->class_to_name($class);
        $cng_tbl = $sys->typ_lst->cng_tbl;

        // select the change table ids and the row filter to use per object class
        $table_ids = [];
        $sql_row = '';
        if ($name == 'user') {
            $table_ids = [$cng_tbl->id(change_tables::USER)];
            $sql_row = 'c.user_id = ' . $usr->id . ' AND ';
        } elseif ($name == 'word') {
            $table_ids = [
                $cng_tbl->id(change_tables::WORD), $cng_tbl->id(change_tables::WORD_USR),
                $cng_tbl->id(change_tables::TRIPLE), $cng_tbl->id(change_tables::TRIPLE_USR)];
            $sql_row = '(c.old_from_id = ' . $id . ' OR c.old_to_id = ' . $id . ' OR '
                . 'c.new_from_id = ' . $id . ' OR c.new_to_id = ' . $id . ') AND ';
        } elseif ($name == 'value') {
            $table_ids = [
                $cng_tbl->id(change_tables::VALUE), $cng_tbl->id(change_tables::VALUE_USR),
                $cng_tbl->id(change_tables::VALUE_LINK)];
            $sql_row = '(c.old_from_id = ' . $id . ' OR c.new_from_id = ' . $id . ') AND ';
        } elseif ($name == 'formula') {
            $table_ids = [
                $cng_tbl->id(change_tables::FORMULA), $cng_tbl->id(change_tables::FORMULA_USR),
                $cng_tbl->id(change_tables::FORMULA_LINK), $cng_tbl->id(change_tables::FORMULA_LINK_USR)];
            $sql_row = '(c.old_from_id = ' . $id . ' OR c.new_from_id = ' . $id . ') AND ';
        } elseif ($name == 'view') {
            $table_ids = [
                $cng_tbl->id(change_tables::VIEW), $cng_tbl->id(change_tables::VIEW_USR),
                $cng_tbl->id(change_tables::VIEW_LINK), $cng_tbl->id(change_tables::VIEW_LINK_USR)];
            $sql_row = '(c.old_from_id = ' . $id . ' OR c.new_from_id = ' . $id . ') AND ';
        } elseif ($name == 'view_cmp' or $name == 'component') {
            $table_ids = [
                $cng_tbl->id(change_tables::VIEW_COMPONENT), $cng_tbl->id(change_tables::VIEW_COMPONENT_USR),
                $cng_tbl->id(change_tables::VIEW_LINK), $cng_tbl->id(change_tables::VIEW_LINK_USR)];
            $sql_row = '(c.old_to_id = ' . $id . ' OR c.new_to_id = ' . $id . ') AND ';
        } else {
            log_warning('object "' . $class . '" is not defined for showing the link changes',
                self::class . '->load_sql_by_obj');
            return '';
        }

        $sql_table = 'c.change_table_id IN (' . implode(',', $table_ids) . ') AND ';

        // select the full change_link column set so change_link::row_mapper can map each row
        return 'SELECT c.' . change_link::FLD_ID . ',
                       u.' . user_db::FLD_ID . ',
                       u.' . user_db::FLD_NAME . ',
                       c.' . change_link::FLD_TABLE_ID . ',
                       c.' . change_link::FLD_TIME . ',
                       c.' . change_link::FLD_OLD_FROM_TEXT . ',
                       c.' . change_link::FLD_OLD_FROM_ID . ',
                       c.' . change_link::FLD_OLD_LINK_TEXT . ',
                       c.' . change_link::FLD_OLD_LINK_ID . ',
                       c.' . change_link::FLD_OLD_TO_TEXT . ',
                       c.' . change_link::FLD_OLD_TO_ID . ',
                       c.' . change_link::FLD_NEW_FROM_TEXT . ',
                       c.' . change_link::FLD_NEW_FROM_ID . ',
                       c.' . change_link::FLD_NEW_LINK_TEXT . ',
                       c.' . change_link::FLD_NEW_LINK_ID . ',
                       c.' . change_link::FLD_NEW_TO_TEXT . ',
                       c.' . change_link::FLD_NEW_TO_ID . ',
                       c.' . change_link::FLD_ROW_ID . '
                  FROM change_links c,
                       users u
                 WHERE ' . $sql_table . '
                       ' . $sql_row . '
                       c.user_id = u.user_id
              ORDER BY c.' . change_link::FLD_TIME . ' DESC
                 LIMIT ' . $size . ';';
    }

    /**
     * map the loaded change_links rows to a list of change_link objects
     *
     * @param string $sql the SQL statement to load the link changes
     * @param user $usr the user who wants to see the changes
     * @param bool $use_from true to show the from side of the link instead of the to side (e.g. for components)
     * @return bool true if at least one link change has been loaded
     */
    private function load(string $sql, user $usr, bool $use_from = false): bool
    {
        global $db_con;
        $result = false;

        if ($sql == '') {
            log_err('The query cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_con->usr_id = $usr->id;
            $db_rows = $db_con->get_old($sql);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $chg = new change_link($usr);
                    $chg->row_mapper($db_row);
                    // normalise the display text so the api always shows the relevant side as old/new text
                    if ($use_from) {
                        $chg->old_text_to = $chg->old_text_from;
                        $chg->new_text_to = $chg->new_text_from;
                    }
                    $this->add_obj($chg);
                    $result = true;
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