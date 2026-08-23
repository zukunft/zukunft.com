<?php

/*

    model/view/term_view_list.php - a list of assignments from terms to views
    -----------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_list.php';
include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST_FIELDS . 'view_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\fields\view_fields;

class term_view_list extends sandbox_link_list
{

    /*
     * construct and map
     */

    /**
     * fill the term view list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @param user_message $msg to enrich with problems and suggested solutions
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula link has been added
     */
    protected function rows_mapper(array $db_rows, user_message $msg, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new term_view($this->get_user()), $db_rows, $msg, $load_all);
    }


    /*
     * load sql
     */

    /**
     * set the common part of the SQL query for term views
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(term_view::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(term_view::FLD_NAMES);
        $sc->set_usr_fields(term_view::FLD_NAMES_USR);
        $sc->set_usr_num_fields(term_view::FLD_NAMES_NUM_USR);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of term views by the term view ids
     * @param sql_creator $sc with the target db_type set
     * @param array $ids an array of term view ids which should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        if (count($ids) > 0) {
            $sc->add_where(term_view::FLD_ID, $ids, sql_par_type::INT_LIST);
            // also load the names of both linked objects, so that the link can name them;
            // the term name comes from the terms database view, which unions the term classes
            $sc->set_join_usr_fields(view_db::FLD_NAMES_USR_ALL, view::class, view_fields::FLD_ID, '', true);
            $sc->set_join_usr_fields(term::FLD_NAMES_USR_NAME, term::class, term::FLD_ID, '', true);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load a list of term views by the given term view ids
     * @param array $ids an array of term view ids which should be loaded
     * @return bool true if at least one term view found
     */
    function load_by_ids(array $ids, user_message $msg): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp, $msg);
    }

}