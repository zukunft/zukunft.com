<?php

/*

    model/view/view_term_link.php - to define the standard view for a word, triple, verb or formula
    -----------------------------

    The main sections of this object are
    - db const:          const for the database link
    - set and get:       to capsule the vars from unexpected changes
    - fields:            the field names of this object as overwrite functions
    - load:              database access object (DAO) functions
    - sql write fields:  field list for writing to the database

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

use api\view\view as view_api;
use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type_list;
use cfg\export\sandbox_exp;
use cfg\export\view_exp;
use cfg\log\change;

class view_term_link extends sandbox_link_with_type
{

    /*
     * db const
     */

    // the database and JSON object field names used only for formula links
    // *_SQLTYP is the sql data type used for the field
    const TBL_COMMENT = 'to link view to a word, triple, verb or formula with an n:m relation';
    const FLD_ID = 'view_term_link_id';
    const FLD_DESCRIPTION_SQLTYP = sql_field_type::TEXT;
    const FLD_TYPE_COM = '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups';

    // all database field names excluding the id
    const FLD_NAMES = array(
        term::FLD_ID,
        view_link_type::FLD_ID,
        view::FLD_ID
    );
    //
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION
    );
    // list of fields that select the objects that should be linked
    const FLD_LST_LINK = array(
        [term::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [view::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [view_link_type::FLD_ID, type_object::FLD_ID_SQLTYP, sql_field_default::ONE, sql::INDEX, view_link_type::class, self::FLD_TYPE_COM],
    );
    // list of MANDATORY fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [sandbox_named::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQLTYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [view_link_type::FLD_ID, type_object::FLD_ID_SQLTYP, sql_field_default::NULL, sql::INDEX, view_link_type::class, ''],
        [sandbox_named::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQLTYP, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    public ?string $description = null;


    /*
     * construct and map
     */

    function reset(): void
    {
        parent::reset();
        $this->description = null;
    }


    /*
     * set and get
     */

    /**
     * interface function to set the view always to the from object
     * @param view $msk the view that should be linked
     * @return void
     */
    function set_view(view $msk): void
    {
        $this->set_fob($msk);
    }

    /**
     * interface function to set the term always to the to object
     * @param term $trm the word, triple or formula that should be linked
     * @return void
     */
    function set_term(term $trm): void
    {
        $this->set_tob($trm);
    }

    /**
     * interface function to set the term always to the to object
     * @param string $type_code_id the word, triple or formula that should be linked
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $view_link_types;
        $this->set_type_id($view_link_types->id($type_code_id));
    }

    /**
     * interface function to get the view
     * @return object but actually the view object
     */
    function view(): object
    {
        return $this->fob();
    }

    /**
     * interface function to get the term
     * @return object but actually the term object
     */
    function term(): object
    {
        return $this->tob();
    }


    /*
     * fields
     */

    /**
     * @return string with the field name for the view as an overwrite function
     */
    function from_field(): string
    {
        return view::FLD_ID;
    }

    /**
     * @return string with the field name for the term as an overwrite function
     */
    function to_field(): string
    {
        return term::FLD_ID;
    }

    /**
     * @return string with the field name for the link type as an overwrite function
     */
    function type_field(): string
    {
        return view_link_type::FLD_ID;
    }

    /**
     * the view_term_link does not really have a name, only a description
     * @return string
     */
    function name_field(): string
    {
        return '';
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $view_link_types;
        return $view_link_types->name($this->type_id);
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a view term link from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * add the type fields to the list of all database fields that might be changed
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                sandbox_named::FLD_DESCRIPTION,
                view_link_type::FLD_ID,
            ]
        );
    }

    /**
     * add the type field to the list of changed database fields with name, value and type
     *
     * @param sandbox|word $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|word $sbx,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $change_field_list;

        $sc = new sql();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);

        if ($sbx->description <> $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sandbox_named::FLD_DESCRIPTION,
                    $change_field_list->id($table_id . sandbox_named::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                sandbox_named::FLD_DESCRIPTION,
                $this->description,
                sandbox_named::FLD_DESCRIPTION_SQLTYP,
                $sbx->description
            );
        }

        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view_link_type::FLD_ID,
                    $change_field_list->id($table_id . view_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            global $phrase_types;
            $lst->add_type_field(
                view_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->type_id(),
                $sbx->type_id(),
                $phrase_types
            );
        }
        return $lst;
    }

}
