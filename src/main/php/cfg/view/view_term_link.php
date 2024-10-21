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

class view_term_link extends sandbox_link
{

    /*
     * db const
     */

    // the database and JSON object field names used only for formula links
    // *_SQL_TYP is the sql data type used for the field
    const TBL_COMMENT = 'to link view to a word, triple, verb or formula with an n:m relation';
    const FLD_ID = 'view_term_link_id';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
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
    // all database field names, excluding the id, used to identify if there are some user specific changes
    // TODO check if this is used in all relevant objects
    const ALL_SANDBOX_FLD_NAMES = array(
        view_link_type::FLD_ID,
        sandbox_named::FLD_DESCRIPTION,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const FLD_LST_LINK = array(
        [term::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [view::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [view_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, view_link_type::class, self::FLD_TYPE_COM],
    );
    // list of MANDATORY fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [sandbox_named::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [view_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_link_type::class, ''],
        [sandbox_named::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    public ?string $description = null;


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
        $this->set_predicate(view_link_type::DEFAULT);
    }

    function reset(): void
    {
        parent::reset();
        $this->set_predicate_id(null);
        $this->description = null;
    }

    /**
     * map the database fields to the object fields
     * TODO get the related view and term object from the cache if possible
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view component link is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $msk = new view($this->user());
            $msk->set_id($db_row[view::FLD_ID]);
            $this->set_view($msk);
            $trm = new term($this->user());
            $trm->set_id($db_row[term::FLD_ID]);
            $this->set_term($trm);
            $this->set_predicate_id($db_row[view_link_type::FLD_ID]);
            $this->description = $db_row[sandbox_named::FLD_DESCRIPTION];
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the main vars with one function
     * @param int $id the database id of the link
     * @param view $msk the formula that should be linked
     * @param term $trm the phrase to which the formula should be linked
     * @return void
     */
    function set(int $id, view $msk, term $trm): void
    {
        $this->set_id($id);
        $this->set_view($msk);
        $this->set_term($trm);
    }

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
     * interface function to set the connection type from the term to the view
     * @param string $type_code_id the word, triple or formula that should be linked
     * @return void
     */
    function set_predicate(string $type_code_id): void
    {
        global $view_link_types;
        $this->set_predicate_id($view_link_types->id($type_code_id));
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
     * TODO check if the overwrites are correct for all objects
     *      and if a to_id() function is needed
     * @return string with the term name
     */
    function to_value(): string
    {
        if ($this->tob() == null) {
            return '';
        } else {
            return $this->tob()->name();
        }
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

    /**
     * @return array with the all field names that the user can change for this object
     * TODO move to the highest object level
     */
    protected function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function predicate_name(): string
    {
        global $view_link_types;
        return $view_link_types->name($this->predicate_id);
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
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * TODO move inner part to view as "load_remaining"
     * TODO add a bool var "is_loaded" to db_object
     *      to indicate is the object has just been created and might be incomplete
     *      or if loaded from the db and is expected to have all vars in line with the db
     * @return bool true if all the related objects has been loaded
     */
    function load_objects(): bool
    {
        $result = true;

        $msk = $this->view();
        if ($msk->id() == 0) {
            if ($msk->name() != '') {
                $result = $msk->load_by_name($msk->name());
            } else {
                log_warning('Cannot load view because neither id nor name is set');
            }
        } else {
            if ($msk->name() == '') {
                $result = $msk->load_by_id($msk->id());
            }
        }

        $trm = $this->term();
        if ($trm->id() == 0) {
            if ($trm->name() != '') {
                $result = $trm->load_by_name($trm->name());
            } else {
                log_warning('Cannot load term because neither id nor name is set');
            }
        } else {
            if ($trm->name() == '') {
                $result = $trm->load_by_id($trm->id());
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard view term link from the database
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc): sql_par
    {
        // try to get the search values from the objects
        if ($this->id() <= 0) {
            $this->set_id(0);
        }

        $sc->set_class($this::class);
        $qp = new sql_par($this::class);
        if ($this->id() != 0) {
            $qp->name .= 'std_id';
        } else {
            $qp->name .= 'std_link_ids';
        }
        $sc->set_name($qp->name);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)));
        if ($this->id() > 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->view()->id() > 0 and $this->term()->id() > 0) {
            $sc->add_where(view::FLD_ID, $this->view()->id());
            $sc->add_where(term::FLD_ID, $this->term()->id());
        } else {
            log_err('Cannot load default view term link because id is missing');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

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
     * @param sandbox|view_term_link $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|view_term_link $sbx,
        sql_type_list          $sc_par_lst = new sql_type_list([])
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
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sandbox_named::FLD_DESCRIPTION,
                $this->description,
                sandbox_named::FLD_DESCRIPTION_SQL_TYP,
                $sbx->description
            );
        }

        if ($sbx->predicate_id() <> $this->predicate_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view_link_type::FLD_ID,
                    $change_field_list->id($table_id . view_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $phrase_types;
            $lst->add_type_field(
                view_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $phrase_types
            );
        }
        return $lst;
    }

}
