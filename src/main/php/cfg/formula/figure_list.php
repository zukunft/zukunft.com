<?php

/*

    model/formula/figure_list.php - a list of figures, so either a value of a formula result object
    -----------------------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object


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

namespace cfg\formula;

use cfg\const\paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\phrase\term_list;
use cfg\result\result;
use cfg\sandbox\sandbox_list;
use cfg\user\user_message;
use cfg\value\value;
use shared\library;

class figure_list extends sandbox_list
{

    /*
     * object vars
     */

    // array $lst is the list of figures
    public ?bool $fig_missing = false; // true if at least one of the results is not set which means is NULL (but zero is a value)


    /*
     * construct and map
     */

    // the rows_mapper is not needed, because the figures are not saved in the database

    /**
     * map a figure list api json to this model figure list object
     * @param array $api_json the api array with the figures that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = new user_message();

        foreach ($api_json as $json_phr) {
            $fig = new figure($this->user());
            $usr_msg->add($fig->api_mapper($json_phr));
            if ($usr_msg->is_ok()) {
                $this->add($fig);
            }
        }

        return $usr_msg;
    }


    /*
     * load
     */

    /**
     * load the figures including the related value or result object by the given id list from the database
     *
     * @param fig_ids $ids figure ids that should be loaded
     * @return bool true if at least one phrase has been loaded
     */
    function load_by_ids(fig_ids $ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp);
    }

    /**
     * load this list of figures
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded figures e.g. for admins
     * @return bool true if at least one phrase has been loaded
     */
    function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class);
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    if ($db_row[figure::FLD_ID] > 0) {
                        $val = new value($this->user());
                        $fig = new figure($val);
                    } else {
                        $res = new result($this->user());
                        $fig = new figure($res);
                    }
                    $fig->row_mapper($db_row, $qp->ext);
                    $this->add_obj($fig);
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve a list of phrase objects by the id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param fig_ids $ids figure ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        fig_ids     $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(figure::FLD_ID, $ids->lst);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of figure objects
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(figure::class);
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(figure::FLD_NAMES);
        //$db_con->set_usr_fields(figure::FLD_NAMES_USR_NO_NAME);
        //$db_con->set_usr_num_fields(figure::FLD_NAMES_NUM_USR);
        //$db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(figure::FLD_VALUES) . ' DESC, ' . figure::FLD_NAME);
        return $qp;
    }

    // TODO use cache to improve speed
    function load_phrases(): void
    {
        foreach ($this->lst() as $fig) {
            $fig->obj()->grp()->load_phrases();
        }
    }


    /*
     * modify
     */

    /**
     * add one figure to the figure list, but only if it is not yet part of the figure list
     * @param figure|null $fig_to_add the figure that should be added to this list (if it does not yet exist)
     * @returns bool true the term has been added
     */
    function add(?figure $fig_to_add): bool
    {
        $result = false;
        // check parameters
        if ($fig_to_add != null) {
            log_debug($fig_to_add->dsp_id());
            if ($fig_to_add->id() <> 0 or $fig_to_add->name() != '') {
                $result = parent::add_obj($fig_to_add)->is_ok();
            }
        }
        return $result;
    }


    /*
     * display
     */

    /*
     * TODO review
     */
    function get_first_id(): int
    {
        $result = 0;
        if ($this != null) {
            if (count($this->lst()) > 0) {
                $fig = $this->get(0);
                if ($fig != null) {
                    $result = $fig->id();
                }
            }
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * create a text that describes the figures for unique identification
     *
     * @param term_list|null $trm_lst a cached list of terms
     * @return string to display the unique id fields
     */
    function dsp_id(?term_list $trm_lst = null): string
    {
        $id = $this->ids_txt();
        $name = $this->name();
        if ($name <> '""') {
            $result = $name . ' (' . $id . ')';
        } else {
            $result = $id;
        }

        return $result;
    }

    function name(int $limit = null): string
    {
        $result = '';

        foreach ($this->lst() as $fig) {
            $result .= $fig->name() . ' ';
        }

        return $result;
    }

    /**
     * return a list of the figure list ids as sql compatible text
     */
    function ids_txt(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->ids());
    }

}