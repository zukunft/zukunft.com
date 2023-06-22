<?php

/*

    model/view/view_term_link.php - to define the standard view for a word, triple, verb or formula
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

use api\view_api;
use cfg\export\exp_obj;
use cfg\export\view_exp;
use cfg\type_list;
use model\component;
use model\component_dsp_old;
use model\component_link;
use model\component_link_list;
use model\component_list;
use model\formula;
use model\library;
use model\phrase;
use model\sandbox;
use model\sandbox_link_typed;
use model\sandbox_typed;
use model\sql_db;
use model\sql_par;
use model\term;
use model\user;
use model\user_message;
use model\view;

class view_term_link extends sandbox_link_typed
{

    /*
     * database link
     */

    // the database and JSON object field names used only for formula links
    const FLD_ID = 'view_term_link_id';
    const FLD_TYPE = 'type_id';
    const FLD_LINK_TYPE = 'link_type_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        term::FLD_ID,
        self::FLD_TYPE,
        self::FLD_LINK_TYPE,
        view::FLD_ID
    );


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a view term link from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_VIEW_TERM_LINK);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

}
