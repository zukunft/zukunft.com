<?php

/*

    cfg/component/component.php - a single display object like a headline or a table
    ---------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this component object
    - construct and map: including the mapping of the db row to this component object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql and other load helper functions
    - retrieval:         get related objects assigned to this component
    - cast:              create an api object and set the vars from an api json
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - log:               write the changes to the log
    - link:              link and release the component to and from a view
    - save:              manage to update the database
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging


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

namespace cfg\component;

include_once API_COMPONENT_PATH . 'component.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_COMPONENT_PATH . 'view_style.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_link.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';

use api\component\component as component_api;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\formula\formula;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use cfg\phrase\phrase;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_typed;
use cfg\helper\type_object;
use cfg\user\user;
use cfg\user\user_message;
use cfg\word\word;
use shared\json_fields;
use shared\types\api_type_list;

class component extends sandbox_typed
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the single components of a view';

    // the database and JSON object field names used only for view components links
    // *_COM: the description of the field
    // *_SQL_TYP: the sql field type used for this field
    const FLD_ID = 'component_id';
    const FLD_NAME_COM = 'the unique name used to select a component by the user';
    const FLD_NAME = 'component_name';
    const FLD_DESCRIPTION_COM = 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to select the predefined functionality';
    const FLD_TYPE = 'component_type_id';
    const FLD_STYLE_COM = 'the default display style for this component';
    const FLD_STYLE = 'view_style_id';
    const FLD_CODE_ID_COM = 'used for system components to select the component by the program code';
    const FLD_UI_MSG_ID_COM = 'used for system components the id to select the language specific user interface message e.g. "add word"';
    const FLD_UI_MSG_ID = 'ui_msg_code_id';
    const FLD_UI_MSG_ID_SQL_TYP = sql_field_type::CODE_ID;
    // TODO move the lined phrases to a component phrase link table for n:m relation with a type for each link
    const FLD_ROW_PHRASE_COM = 'for a tree the related value the start node';
    const FLD_ROW_PHRASE = 'word_id_row';
    const FLD_COL_PHRASE_COM = 'to define the type for the table columns';
    const FLD_COL_PHRASE = 'word_id_col';
    const FLD_COL2_PHRASE_COM = 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
    const FLD_COL2_PHRASE = 'word_id_col2';
    const FLD_FORMULA_COM = 'used for type 6';
    const FLD_LINK_COMP_COM = 'to link this component to another component';
    const FLD_LINK_COMP = 'linked_component_id';
    const FLD_LINK_COMP_TYPE_COM = 'to define how this entry links to the other entry';
    const FLD_LINK_COMP_TYPE = 'component_link_type_id';
    const FLD_LINK_TYPE_COM = 'e.g. for type 4 to select possible terms';
    const FLD_LINK_TYPE = 'link_type_id';
    const FLD_LINK_TYPE_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_POSITION = 'position'; // TODO move to component_link

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, component_type::class, self::FLD_TYPE_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
        // TODO link with a foreign key to phrases (or terms?) if link to a view is allowed
        [self::FLD_ROW_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_ROW_PHRASE_COM],
        [formula::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, formula::class, self::FLD_FORMULA_COM],
        [self::FLD_COL_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_COL_PHRASE_COM],
        [self::FLD_COL2_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_COL2_PHRASE_COM],
        [self::FLD_LINK_COMP, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_COMP_COM],
        [self::FLD_LINK_COMP_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_COMP_TYPE_COM],
        [self::FLD_LINK_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_TYPE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [self::FLD_UI_MSG_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_UI_MSG_ID_COM],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql::FLD_CODE_ID,
        self::FLD_UI_MSG_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_STYLE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_STYLE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    public ?int $order_nbr = null;          // the position in the linked view // TODO dismiss and use link order number instead
    public ?int $link_type_id = null;       // the word link type used to build the word tree started with the $start_word_id
    public ?int $formula_id = null;         // to select a formula (no used case at the moment)
    public ?int $word_id_col2 = null;       // for a table to defined second columns layer or the second axis in case of a chart
    //                                         e.g. for a "company cash flow statement" the "col word" could be "Year"
    //                                              "col2 word" could be "Quarter" to show the Quarters between the year upon request
    public ?string $code_id = null;         // to select a specific system component by the program code
    //                                         the code id cannot be changed by the user
    //                                         so this field is not part of the table user_components
    public ?string $ui_msg_code_id = null;  // to select a user interface language specific message
    //                                         e.g. "add word" or "Wort zufügen"
    //                                         the code id cannot be changed by the user
    //                                         so this field is not part of the table user_components

    // database fields repeated from the component link for a easy to use in memory view object
    // TODO create a component_phrase_link table with a type fields where the type can be at least row, row_right, col and sub_col
    // TODO easy use the position type object similar to the style
    public ?int $pos_type_id = null;           // the position type in the linked view

    // linked fields
    public ?object $obj = null;             // the object that should be shown to the user
    public ?phrase $row_phrase = null;           // if the view component uses a related word tree this is the start node e.g. for "company" the start node could be "cash flow statement" to show the cash flow for any company
    public ?phrase $col_phrase = null;           // for a table to defined which columns should be used (if not defined by the calling word)
    public ?phrase $col_sub_phrase = null;          // the word object for $word_id_col2
    public ?formula $frm = null;            // the formula object for $formula_id
    private ?type_object $style = null; // the default display style for this component which can be overwritten by the link


    /*
     * construct and map
     */

    /**
     * define the settings for this view component object
     * @param user $usr the user who requested to see this view
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_NAME;
    }

    /**
     * clear the view component object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();

        $this->order_nbr = null;
        $this->type_id = null;
        $this->style = null;
        $this->link_type_id = null;
        $this->formula_id = null;
        $this->word_id_col2 = null;
        $this->row_phrase = null;
        $this->col_phrase = null;
        $this->col_sub_phrase = null;
        $this->frm = null;
        $this->code_id = null;
        $this->ui_msg_code_id = null;
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @return bool true if the view component is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME
    ): bool
    {
        global $msk_sty_cac;
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(sql::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql::FLD_CODE_ID];
            }
            if (array_key_exists(self::FLD_UI_MSG_ID, $db_row)) {
                $this->ui_msg_code_id = $db_row[self::FLD_UI_MSG_ID];
            }
            // TODO easy use set_type_by_id function
            if (array_key_exists(self::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[self::FLD_TYPE];
            }
            if (array_key_exists(self::FLD_STYLE, $db_row)) {
                $this->set_style_by_id($db_row[self::FLD_STYLE]);
            }
            if (array_key_exists(self::FLD_ROW_PHRASE, $db_row)) {
                $this->load_row_phrase($db_row[self::FLD_ROW_PHRASE]);
            }
            if (array_key_exists(self::FLD_LINK_TYPE, $db_row)) {
                $this->link_type_id = $db_row[self::FLD_LINK_TYPE];
            }
            if (array_key_exists(formula::FLD_ID, $db_row)) {
                $this->formula_id = $db_row[formula::FLD_ID];
            }
            if (array_key_exists(self::FLD_COL_PHRASE, $db_row)) {
                $this->load_col_phrase($db_row[self::FLD_COL_PHRASE]);
            }
            if (array_key_exists(self::FLD_COL2_PHRASE, $db_row)) {
                $this->word_id_col2 = $db_row[self::FLD_COL2_PHRASE];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used view component vars with one set statement
     * @param int $id mainly for test creation the database id of the view component
     * @param string $name mainly for test creation the name of the view component
     * @param string $type_code_id the code id of the predefined view component type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the view component type
     *
     * @param string $type_code_id the code id that should be added to this view component
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $cmp_typ_cac;
        $this->type_id = $cmp_typ_cac->id($type_code_id);
    }

    /**
     * set the default style for this component by the code id
     *
     * @param string|null $code_id the code id of the display style use for im and export
     * @return void
     */
    function set_style(?string $code_id): void
    {
        global $msk_sty_cac;
        if ($code_id == null) {
            $this->style = null;
        } else {
            $this->style = $msk_sty_cac->get_by_code_id($code_id);
        }
    }

    /**
     * set the default style for this component by the database id
     *
     * @param int|null $style_id the database id of the display style
     * @return void
     */
    function set_style_by_id(?int $style_id): void
    {
        global $msk_sty_cac;
        if ($style_id == null) {
            $this->style = null;
        } else {
            $this->style = $msk_sty_cac->get($style_id);
        }
    }

    /**
     * @return view_style|type_object|null the view style for this component or null if the parent style should be used
     */
    function style(): view_style|type_object|null
    {
        return $this->style;
    }

    /**
     * @return int|null the database id of the view style or null
     */
    function style_id(): ?int
    {
        return $this->style?->id();
    }

    /**
     * define or remove the phrase that is used to select the table rows
     * @param phrase|null $phr e.g. if "year" each table row is one year
     * @return void
     */
    function set_row_phrase(?phrase $phr): void
    {
        $this->row_phrase = $phr;
    }

    function row_phrase_id(): int
    {
        if ($this->row_phrase != null) {
            return $this->row_phrase->id();
        } else {
            return 0;
        }
    }

    function row_phrase_name(): string
    {
        if ($this->row_phrase != null) {
            return $this->row_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * define or remove the phrase that is used to select the table columns
     * @param phrase|null $phr e.g. if "canton" the canton names are used for the table columns
     * @return void
     */
    function set_col_phrase(?phrase $phr): void
    {
        $this->col_phrase = $phr;
    }

    function col_phrase_id(): int
    {
        if ($this->col_phrase != null) {
            return $this->col_phrase->id();
        } else {
            return 0;
        }
    }

    function col_phrase_name(): string
    {
        if ($this->col_phrase != null) {
            return $this->col_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * define or remove the phrase that is used as the second selection for table columns
     * @param phrase|null $phr e.g. if "city" and "canton" is the col_phrase the cities of each canton are used
     * @return user_message if the sub phrase has no relation to the column phrase a suggestion of the possible sub phrases
     */
    function set_col_sub_phrase(?phrase $phr): user_message
    {
        $this->col_sub_phrase = $phr;
        return new user_message();
    }

    function col_sub_phrase_id(): int
    {
        if ($this->col_sub_phrase != null) {
            return $this->col_sub_phrase->id();
        } else {
            return 0;
        }
    }

    function col_sub_phrase_name(): string
    {
        if ($this->col_sub_phrase != null) {
            return $this->col_sub_phrase->name();
        } else {
            return 0;
        }
    }

    /**
     * set the formula used for the component
     * @param formula $frm
     * @return user_message if setting the formula does not make sense with a suggested solution
     */
    function set_formula(formula $frm): user_message
    {
        $this->frm = $frm;
        $this->formula_id = $frm->id();
        return new user_message();
    }

    function formula_id(): int
    {
        if ($this->formula_id != null) {
            return $this->formula_id;
        } else {
            return 0;
        }
    }

    /**
     * set the type of linked components
     *
     * @param string $type_code_id the code id that should be added to this view component
     * @return void
     */
    function set_link_type(string $type_code_id): void
    {
        global $cmp_lnk_typ_cac;
        $this->link_type_id = $cmp_lnk_typ_cac->id($type_code_id);
    }

    /**
     * TODO use a set_join function for all not simple sql joins
     * @param sql_creator $sc the sql creator without component joins
     * @return sql_creator the sql creator with the components join set
     */
    function set_join(sql_creator $sc): sql_creator
    {
        $sc->set_join_fields(component::FLD_NAMES, component::class);
        $sc->set_join_usr_fields(component::FLD_NAMES_USR, component::class);
        $sc->set_join_usr_num_fields(component::FLD_NAMES_NUM_USR, component::class);
        return $sc;
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the component type
     */
    function type_name(): string
    {
        global $cmp_typ_cac;
        return $cmp_typ_cac->name($this->type_id);
    }

    /**
     * get the name of the component type or null if no type is set
     * @return string|null the name of the component type
     */
    function type_name_or_null(): ?string
    {
        global $cmp_typ_cac;
        return $cmp_typ_cac->name_or_null($this->type_id);
    }

    /**
     * get the view component type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $cmp_typ_cac;
        return $cmp_typ_cac->id($code_id);
    }


    /*
     * load
     */

    /**
     * just set the class name for the user sandbox function
     * load a view component object by name
     * @param string $name the name view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        $id = parent::load_by_name($name);
        if ($this->id() > 0) {
            $this->load_phrases();
        }
        return $id;
    }

    /**
     * just set the class name for the user sandbox function
     * load a view component object by database id
     * @param int $id the id of the view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        $id = parent::load_by_id($id);
        if ($this->id() > 0) {
            $this->load_phrases();
        }
        return $id;
    }

    /**
     * load the view component parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard view component has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp);

        if ($result) {
            $result = $this->load_owner();
        }
        if ($result) {
            $result = $this->load_phrases();
        }
        return $result;
    }

    /**
     * create the SQL to load the default view always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view component from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * retrieval
     */

    /**
     * load the related word and formula objects
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_phrases(): bool
    {
        $result = true;
        $this->load_row_phrase();
        $this->load_col_phrase();
        $this->load_wrd_col2();
        $this->load_formula();
        log_debug('done for ' . $this->dsp_id());
        return $result;
    }

    /**
     * load the phrase that should be used for the rows of a table
     * or the left Y-axis of a chart
     *
     * @param int|null $id the id of suggested the row phrase
     * @return int the id of the loaded phrase or 0 if no phrase has been loaded
     */
    function load_row_phrase(?int $id = null): int
    {
        $result = 0;
        $row_phr = $this->load_phrase($id);
        if ($row_phr != null) {
            $this->row_phrase = $row_phr;
            $result = $id;
        }
        return $result;
    }

    /**
     * load the phrase that should be used for the columns of a table
     *  load the word object that defines the column names
     *  e.g. "year" to display the yearly values
     *       or the left X-axis of a chart
     *
     * @param int|null $id the id of suggested the col phrase
     * @return int the id of the loaded phrase or 0 if no phrase has been loaded
     */
    function load_col_phrase(?int $id = null): int
    {
        $result = 0;
        $col_phr = $this->load_phrase($id);
        if ($col_phr != null) {
            $this->col_phrase = $col_phr;
            $result = $id;
        }
        return $result;
    }

    /**
     * load a phrase if the id is valid
     *
     * @param int|null $id the id of suggested the phrase
     * @return phrase|null the loaded phrase
     */
    private function load_phrase(?int $id = null): ?phrase
    {
        $result = null;
        if ($id != null) {
            if ($id != 0) {
                $phr = new phrase($this->user());
                if ($phr->load_by_id($id) != 0) {
                    $result = $phr;
                }
            }
        }
        return $result;
    }

    //
    function load_wrd_col2(): string
    {
        $result = '';
        if ($this->word_id_col2 > 0) {
            $wrd_col2 = new word($this->user());
            $wrd_col2->load_by_id($this->word_id_col2);
            $this->col_sub_phrase = $wrd_col2->phrase();
            $result = $wrd_col2->name();
        }
        return $result;
    }

    // load the related formula and returns the name of the formula
    function load_formula(): string
    {
        $result = '';
        if ($this->formula_id > 0) {
            $frm = new formula($this->user());
            $frm->load_by_id($this->formula_id);
            $this->frm = $frm;
            $result = $frm->name();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current view component
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }


    /*
     * cast
     */

    /**
     * @return component_api the view component frontend api object
     */
    function api_obj(): component_api
    {
        $api_obj = new component_api();
        if ($this->is_excluded()) {
            $api_obj->set_id($this->id());
            $api_obj->excluded = true;
        } else {
            parent::fill_api_obj($api_obj);
            $api_obj->code_id = $this->code_id;
            $api_obj->ui_msg_code_id = $this->ui_msg_code_id;
        }
        return $api_obj;
    }

    /**
     * map a component api json to this model component object
     * @param array $api_json the api array with the values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function set_by_api_json(array $api_json): user_message
    {
        $msg = parent::set_by_api_json($api_json);

        foreach ($api_json as $key => $value) {
            // TODO the code id might be not be mapped because this can never be changed by the user
            if ($key == json_fields::CODE_ID) {
                $this->code_id = $value;
            }
            if ($key == json_fields::UI_MSG_CODE_ID) {
                $this->ui_msg_code_id = $value;
            }
        }

        return $msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst): array
    {
        if ($this->is_excluded()) {
            $vars = [];
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        } else {
            $vars = parent::api_json_array($typ_lst);
            if ($this->code_id != null) {
                $vars[json_fields::CODE_ID] = $this->code_id;
            }
            if ($this->ui_msg_code_id != null) {
                $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id;
            }
        }

        return $vars;
    }


    /*
     * im- and export
     */

    /**
     *  */
    /**
     * import a view component from a JSON object
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        $usr_msg = parent::import_obj($in_ex_json, $test_obj);

        foreach ($in_ex_json as $key => $value) {

            if ($key == self::FLD_POSITION) {
                $this->order_nbr = $value;
            }
            if ($key == json_fields::TYPE_NAME) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->type_id = $this->type_id_by_code_id($value);
                    }
                }
            }
            if ($key == json_fields::STYLE) {
                if ($value != '') {
                    $this->set_style($value);
                }
            }
            if ($key == json_fields::CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->code_id = $value;
                    }
                }
            }
            if ($key == json_fields::UI_MSG_CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->ui_msg_code_id = $value;
                    }
                }
            }
        }

        if (!$test_obj) {
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save());
            } else {
                log_debug('not saved because ' . $usr_msg->get_last_message());
            }
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        if ($this->order_nbr >= 0) {
            $vars[json_fields::POSITION] = $this->order_nbr;
        }
        if ($this->code_id != null) {
            $vars[json_fields::CODE_ID] = $this->code_id;
        }
        if ($this->ui_msg_code_id != null) {
            $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id;
        }

        // add the phrases used
        if ($do_load) {
            $this->load_phrases();
        }
        if ($this->row_phrase != null) {
            if ($this->row_phrase->name() != '') {
                $vars[json_fields::ROW] = $this->row_phrase->name();
            }
        }
        if ($this->col_phrase != null) {
            if ($this->col_phrase->name() != '') {
                $vars[json_fields::COLUMN] = $this->col_phrase->name();
            }
        }
        if ($this->col_sub_phrase != null) {
            if ($this->col_sub_phrase->name() != '') {
                $vars[json_fields::COLUMN2] = $this->col_sub_phrase->name();
            }
        }

        return $vars;
    }


    /*
     * information
     */

    /**
     * returns the next free order number for a new view component
     */
    function next_nbr(int $view_id): int
    {
        log_debug('component->next_nbr for view "' . $view_id . '"');

        global $db_con;

        $result = 1;
        if ($view_id == '' or $view_id == Null or $view_id == 0) {
            log_err('Cannot get the next position, because the view_id is not set', 'component->next_nbr');
        } else {
            $vcl = new component_link($this->user());
            $result = $vcl->max_pos_by_view($view_id);

            // if nothing is found, assume one as the next free number
            if ($result <= 0) {
                $result = 1;
            } else {
                $result++;
            }
        }

        log_debug($result);
        return $result;
    }


    /*
     * log
     */

    // set the log entry parameters for a value update
    function log_link($dsp): bool
    {
        log_debug('component->log_link ' . $this->dsp_id() . ' to "' . $dsp->name . '"  for user ' . $this->user()->id());
        $log = new change_link($this->user());
        $log->set_action(change_action::ADD);
        $log->set_class(component_link::class);
        $log->new_from = clone $this;
        $log->new_to = clone $dsp;
        $log->row_id = $this->id();
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }

    // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
    function log_unlink($dsp): bool
    {
        log_debug($this->dsp_id() . ' from "' . $dsp->name . '" for user ' . $this->user()->id());
        $log = new change_link($this->user());
        $log->set_action(change_action::DELETE);
        $log->set_class(component_link::class);
        $log->old_from = clone $this;
        $log->old_to = clone $dsp;
        $log->row_id = $this->id();
        $result = $log->add_link_ref();

        log_debug('logged ' . $log->id());
        return $result;
    }


    /*
     * link
     */

    // link a view component to a view
    function link($dsp, $order_nbr): string
    {
        global $pos_typ_cac;

        log_debug($this->dsp_id() . ' to ' . $dsp->dsp_id() . ' at pos ' . $order_nbr);

        $dsp_lnk = new component_link($this->user());
        $dsp_lnk->reset();
        $dsp_lnk->set_view($dsp);
        $dsp_lnk->set_component($this);
        $dsp_lnk->order_nbr = $order_nbr;
        $dsp_lnk->set_pos_type(position_type::BELOW);
        return $dsp_lnk->save()->get_last_message();
    }

    // remove a view component from a view
    // TODO check if the view component is not linked anywhere else
    // and if yes, delete the view component after confirmation
    function unlink($dsp): string
    {
        $result = '';

        if (isset($dsp) and $this->user() != null) {
            log_debug($this->dsp_id() . ' from "' . $dsp->name() . '" (' . $dsp->id() . ')');
            $dsp_lnk = new component_link($this->user());
            $dsp_lnk->load_by_link($dsp, $this);
            $dsp_lnk->load_objects();
            $msg = $dsp_lnk->del();
            $result .= $msg->get_last_message();
        } else {
            $result .= log_err("Cannot unlink view component, because view is not set.", "component.php");
        }

        return $result;
    }


    /*
     * save
     */

    /**
     * set the update parameters for the component code id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_code_id(sql_db $db_con, component $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($this->code_id <> $db_rec->code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->code_id;
            $log->new_value = $this->code_id;
            $log->row_id = $this->id();
            $log->set_field(sql::FLD_CODE_ID);
            $usr_msg = $this->save_field($db_con, $log);
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the component user interface message id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_ui_msg_id(sql_db $db_con, component $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($this->ui_msg_code_id <> $db_rec->ui_msg_code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->ui_msg_code_id;
            $log->new_value = $this->ui_msg_code_id;
            $log->row_id = $this->id();
            $log->set_field(self::FLD_UI_MSG_ID);
            $usr_msg = $this->save_field($db_con, $log);
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word row
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_wrd_row(sql_db $db_con, component $db_rec, component $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->row_phrase_id() <> $this->row_phrase_id()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->row_phrase_name();
            $log->old_id = $db_rec->row_phrase_id();
            $log->new_value = $this->row_phrase_name();
            $log->new_id = $this->row_phrase_id();
            $log->std_value = $std_rec->row_phrase_name();
            $log->std_id = $std_rec->row_phrase_id();
            $log->row_id = $this->id();
            $log->set_field(self::FLD_ROW_PHRASE);
            $usr_msg->add($this->save_field_user($db_con, $log));
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word col
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_wrd_col(sql_db $db_con, component $db_rec, component $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->col_phrase_id() <> $this->col_phrase_id()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->col_phrase_name();
            $log->old_id = $db_rec->col_phrase_id();
            $log->new_value = $this->col_phrase_name();
            $log->new_id = $this->col_phrase_id();
            $log->std_value = $std_rec->col_phrase_name();
            $log->std_id = $std_rec->col_phrase_id();
            $log->row_id = $this->id();
            $log->set_field(self::FLD_COL_PHRASE);
            $usr_msg = $this->save_field_user($db_con, $log);
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word col2
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_wrd_col2(sql_db $db_con, component $db_rec, component $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->word_id_col2 <> $this->word_id_col2) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_wrd_col2();
            $log->old_id = $db_rec->word_id_col2;
            $log->new_value = $this->load_wrd_col2();
            $log->new_id = $this->word_id_col2;
            $log->std_value = $std_rec->load_wrd_col2();
            $log->std_id = $std_rec->word_id_col2;
            $log->row_id = $this->id();
            $log->set_field(self::FLD_COL2_PHRASE);
            $usr_msg = $this->save_field_user($db_con, $log);
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the formula
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component $db_rec the view component as saved in the database before the update
     * @param component $std_rec the default parameter used for this view component
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_formula(sql_db $db_con, component $db_rec, component $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->formula_id <> $this->formula_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->load_formula();
            $log->old_id = $db_rec->formula_id;
            $log->new_value = $this->load_formula();
            $log->new_id = $this->formula_id;
            $log->std_value = $std_rec->load_formula();
            $log->std_id = $std_rec->formula_id;
            $log->row_id = $this->id();
            $log->set_field(formula::FLD_ID);
            $usr_msg = $this->save_field_user($db_con, $log);
        }
        return $usr_msg;
    }

    /**
     * save all updated component fields excluding the name, because already done when adding a component
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component|sandbox $db_obj the view component as saved in the database before the update
     * @param component|sandbox $norm_obj the default parameter used for this view component
     * @returns string any message that should be shown to the user or an empty string if everything is fine
     */
    function save_all_fields(sql_db $db_con, component|sandbox $db_obj, component|sandbox $norm_obj): user_message
    {
        $result = parent::save_fields_typed($db_con, $db_obj, $norm_obj);
        $result->add($this->save_field_code_id($db_con, $db_obj));
        $result->add($this->save_field_ui_msg_id($db_con, $db_obj));
        $result->add($this->save_field_wrd_row($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_wrd_col($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_wrd_col2($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_formula($db_con, $db_obj, $norm_obj));
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved component names
     */
    protected function reserved_names(): array
    {
        return component_api::RESERVED_COMPONENTS;
    }

    /**
     * @return array with the fixed component names for db read testing
     */
    protected function fixed_names(): array
    {
        return component_api::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * delete the view component links of linked to this view component
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $usr_msg = new user_message();

        // collect all component links where this component is used
        $lnk_lst = new component_link_list($this->user());
        $lnk_lst->load_by_component($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $usr_msg->add($lnk_lst->del());
        }

        return $usr_msg;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                self::FLD_TYPE,
                self::FLD_STYLE,
                sql::FLD_CODE_ID,
                self::FLD_UI_MSG_ID,
                self::FLD_ROW_PHRASE,
                self::FLD_COL_PHRASE,
                self::FLD_COL2_PHRASE,
                formula::FLD_ID,
                //self::FLD_LINK_COMP,
                //self::FLD_LINK_COMP_TYPE,
                self::FLD_LINK_TYPE,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|component $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|component $sbx,
        sql_type_list     $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_TYPE,
                    $cng_fld_cac->id($table_id . self::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $cmp_typ_cac;
            if ($this->type_id() < 0) {
                log_err('component type for ' . $this->dsp_id() . ' not found');
            }
            $lst->add_type_field(
                self::FLD_TYPE,
                type_object::FLD_NAME,
                $this->type_id(),
                $sbx->type_id(),
                $cmp_typ_cac
            );
        }
        if ($sbx->style_id() <> $this->style_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_STYLE,
                    $cng_fld_cac->id($table_id . self::FLD_STYLE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $msk_sty_cac;
            // TODO easy move to id function of type list
            if ($this->style_id() < 0) {
                log_err('component style for ' . $this->dsp_id() . ' not found');
            }
            $lst->add_type_field(
                self::FLD_STYLE,
                view_style::FLD_NAME,
                $this->style_id(),
                $sbx->style_id(),
                $msk_sty_cac
            );
        }
        if ($sbx->code_id <> $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql::FLD_CODE_ID,
                    $cng_fld_cac->id($table_id . sql::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql::FLD_CODE_ID,
                $this->code_id,
                sql::FLD_CODE_ID_SQL_TYP,
                $sbx->code_id
            );
        }
        if ($sbx->ui_msg_code_id <> $this->ui_msg_code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_UI_MSG_ID,
                    $cng_fld_cac->id($table_id . self::FLD_UI_MSG_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_UI_MSG_ID,
                $this->ui_msg_code_id,
                self::FLD_UI_MSG_ID_SQL_TYP,
                $sbx->ui_msg_code_id
            );
        }
        if ($sbx->row_phrase_id() <> $this->row_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ROW_PHRASE,
                    $cng_fld_cac->id($table_id . self::FLD_ROW_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $sbx->row_phrase_id();
            if ($sbx->row_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                self::FLD_ROW_PHRASE,
                $this->row_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($sbx->col_phrase_id() <> $this->col_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_COL_PHRASE,
                    $cng_fld_cac->id($table_id . self::FLD_COL_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $sbx->col_phrase_id();
            if ($sbx->col_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                self::FLD_COL_PHRASE,
                $this->col_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($sbx->col_sub_phrase_id() <> $this->col_sub_phrase_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_COL2_PHRASE,
                    $cng_fld_cac->id($table_id . self::FLD_COL2_PHRASE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $sbx->col_sub_phrase_id();
            if ($sbx->col_sub_phrase == null) {
                $old_val = null;
            }
            $lst->add_field(
                self::FLD_COL2_PHRASE,
                $this->col_sub_phrase_id(),
                phrase::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        if ($sbx->formula_id() <> $this->formula_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula::FLD_ID,
                    $cng_fld_cac->id($table_id . formula::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $old_val = $sbx->formula_id();
            if ($sbx->formula_id == null) {
                $old_val = null;
            }
            $lst->add_field(
                formula::FLD_ID,
                $this->formula_id(),
                formula::FLD_ID_SQL_TYP,
                $old_val
            );
        }
        // TODO add FLD_LINK_COMP and FLD_LINK_COMP_TYPE
        if ($sbx->link_type_id <> $this->link_type_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_LINK_TYPE,
                    $cng_fld_cac->id($table_id . self::FLD_LINK_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_LINK_TYPE,
                $this->link_type_id,
                self::FLD_LINK_TYPE_SQL_TYP,
                $sbx->link_type_id
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }


    /*
     * debug
     */

    function name(): string
    {
        return $this->name;
    }

    // not used at the moment
    /*  private function link_type_name() {
        if ($this->type_id > 0) {
          $sql = "SELECT type_name
                    FROM component_types
                   WHERE component_type_id = ".$this->type_id.";";
          $db_con = new mysql;
          $db_con->usr_id = $this->user()->id();
          $db_type = $db_con->get1($sql);
          $this->type_name = $db_type[sql::FLD_TYPE_NAME];
        }
        return $this->type_name;
      } */

    /*
      to link and unlink a component
    */

    /**
     * @return array with all view ids that are directly assigned to this view component
     */
    function assigned_msk_ids(): array
    {
        $result = array();

        if ($this->id() > 0 and $this->user() != null) {
            $lst = new component_link_list($this->user());
            $lst->load_by_component($this);
            $result = $lst->view_ids();
        } else {
            log_err("The user id must be set to list the component links.", "component->assign_dsp_ids");
        }

        return $result;
    }

}

