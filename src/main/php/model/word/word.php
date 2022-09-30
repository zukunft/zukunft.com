<?php

/*

    word.php - the main word object
    --------

    TODO move plural to a linked word?

    TODO check if all objects follow these rules
        - database fields are defined within the object wit a const staring with FLD_
        - the object is as small as possible, means there are no redundant fields
        - for each selection and database reading function a separate load function with the search field is defined e.g. load_by_name(string name)
        - for each load function a separate load_sql function exists, which is unit tested
        - the row_mapper function is always used map the database field to the object fields
        - a minimal object exists with for display only for one user only e.g. for a word object, just the id and the name
        - a ex- and import object exists, that does not include any internal database ids

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

use api\word_api;
use cfg\phrase_type;
use cfg\protection_type;
use cfg\share_type;
use export\exp_obj;
use export\user_sandbox_exp_named;
use export\word_exp;
use html\api;
use html\button;
use html\html_selector;
use html\word_dsp;

class word extends user_sandbox_description
{
    /*
     * database link
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    const FLD_ID = 'word_id';
    const FLD_NAME = 'word_name';
    const FLD_PLURAL = 'plural';
    const FLD_TYPE = 'word_type_id';
    const FLD_VIEW = 'view_id';
    const FLD_VALUES = 'values';
    // the field names used for the im- and export in the json or yaml format
    const FLD_REFS = 'refs';

    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        self::FLD_VALUES
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_PLURAL,
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_VIEW,
        self::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_PLURAL,
        sql_db::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_VIEW,
        self::FLD_EXCLUDED
    );


    /*
     * for system testing
     */

    // persevered word names for system settings
    const DB_SETTINGS = 'System database settings';

    // persevered word names for unit and integration tests based on the database
    // for stand-alone unit test words see api/word/word_min.php
    const TN_READ = 'Mathematical constant';
    const TN_READ_SCALE = 'million';
    const TN_READ_PERCENT = 'percent';
    const TN_ADD = 'System Test Word';
    const TN_RENAMED = 'System Test Word Renamed';
    const TN_PARENT = 'System Test Word Parent';
    const TN_CH = 'System Test Word Parent e.g. Switzerland';
    const TN_ZH = 'System Test Word Member e.g. Zurich';
    const TN_COUNTRY = 'System Test Word Parent e.g. Country';
    const TN_CANTON = 'System Test Word Category e.g. Canton';
    const TN_CITY = 'System Test Word Another Category e.g. City';
    const TN_COMPANY = 'System Test Word Group e.g. Company';
    const TN_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const TN_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TN_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const TN_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const TN_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const TN_SECTOR = 'System Test Word with differentiator e.g. Sector';
    const TN_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const TN_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const TN_CASH = 'System Test Word multi levels e.g. Cash';
    const TN_YEAR = 'System Test Time Word Category e.g. Year';
    const TN_2019 = 'System Test Another Time Word e.g. 2019';
    const TN_2020 = 'System Test Another Time Word e.g. 2020';
    const TN_2021 = 'System Test Time Word e.g. 2021';
    const TN_2022 = 'System Test Another Time Word e.g. 2022';
    const TN_CHF = 'System Test Measure Word e.g. CHF';
    const TN_SHARE = 'System Test Word Share';
    const TN_PRICE = 'System Test Word Share Price';
    const TN_EARNING = 'System Test Word Earnings';
    const TN_PE = 'System Test Word PE Ratio';
    const TN_ONE = 'System Test Scaling Word e.g. one';
    const TN_IN_K = 'System Test Scaling Word e.g. thousands';
    const TN_MIO = 'System Test Scaling Word e.g. millions';
    const TN_BIL = 'System Test Scaling Word e.g. billions';
    const TN_PCT = 'System Test Percent Word';
    const TN_TOTAL = 'System Test Word Total';
    const TN_INCREASE = 'System Test Word Increase';
    const TN_THIS = 'System Test Word This';
    const TN_PRIOR = 'System Test Word Prior';
    const TN_INHABITANT = 'System Test Word Unit e.g. inhabitant';
    const TN_CONST = 'System Test Word Math Const e.g. Pi';
    const TN_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const TN_LATEST = 'System Test Word Latest';
    const TN_SCALING_PCT = 'System Test Word Scaling Percent';
    const TN_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const TN_CALC = 'System Test Word Calc';
    const TN_LAYER = 'System Test Word Layer';

    // word groups for creating the test words and remove them after the test
    const RESERVED_WORDS = array(
        self::DB_SETTINGS,
        self::TN_READ,
        self::TN_ADD,
        self::TN_RENAMED,
        self::TN_PARENT,
        self::TN_CH,
        self::TN_ZH,
        self::TN_COUNTRY,
        self::TN_CANTON,
        self::TN_CITY,
        self::TN_COMPANY,
        self::TN_FIN_REPORT,
        self::TN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_YEAR,
        self::TN_2019,
        self::TN_2020,
        self::TN_2021,
        self::TN_2022,
        self::TN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_ONE,
        self::TN_IN_K,
        self::TN_MIO,
        self::TN_BIL,
        self::TN_PCT,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_INHABITANT,
        self::TN_CONST,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER
    );
    const TEST_WORDS_STANDARD = array(
        self::TN_PARENT,
        self::TN_CH,
        self::TN_ZH,
        self::TN_COUNTRY,
        self::TN_CANTON,
        self::TN_CITY,
        self::TN_COMPANY,
        self::TN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_INHABITANT,
        self::TN_INCREASE,
        self::TN_YEAR,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_TOTAL
    );
    const TEST_WORDS_MEASURE = array(self::TN_CHF);
    const TEST_WORDS_SCALING_HIDDEN = array(self::TN_ONE);
    const TEST_WORDS_SCALING = array(self::TN_IN_K, self::TN_MIO, self::TN_BIL);
    const TEST_WORDS_PERCENT = array(self::TN_PCT);
    // the time words must be in correct order because the following is set during creation
    const TEST_WORDS_TIME = array(self::TN_2019, self::TN_2020, self::TN_2021, self::TN_2022);

    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    public ?string $plural;    // the english plural name as a kind of shortcut; if plural is NULL the database value should not be updated
    public ?int $view_id;      // defines the default view for this word
    public ?int $values;       // the total number of values linked to this word as an indication how common the word is and to sort the words

    // in memory only fields
    public ?int $link_type_id; // used in the word list to know based on which relation the word was added to the list

    // only used for the export object
    private ?view $view; // name of the default view for this word
    private ?array $ref_lst = [];

    /*
     * construct and map
     */

    /**
     * define the settings for this word object
     * @param user $usr the user who requested to see this word
     */
    function __construct(user $usr, string $name = '')
    {
        parent::__construct($usr);
        $this->reset();
        $this->obj_name = DB_TYPE_WORD;

        $this->rename_can_switch = UI_CAN_CHANGE_WORD_NAME;

        $this->name = $name;
    }

    /**
     * clear the object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();
        $this->plural = null;
        $this->type_id = null;
        $this->view_id = null;
        $this->values = null;

        $this->link_type_id = null;

        $this->share_id = null;
        $this->protection_id = null;

        $this->view = null;
        $this->ref_lst = [];
    }

    /**
     * map the database fields to the object fields
     *
     * TODO check if "if (is_null($db_wrd[user_sandbox::FLD_EXCLUDED]) or $db_wrd[user_sandbox::FLD_EXCLUDED] == 0) {" should be added
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default word used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the word is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->plural = $db_row[self::FLD_PLURAL];
            $this->description = $db_row[sql_db::FLD_DESCRIPTION];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->view_id = $db_row[self::FLD_VIEW];
        }
        return $result;
    }

    /*
     * casting objects
     */

    /**
     * @return word_api the word frontend api object
     */
    function api_obj(): word_api
    {
        $api_obj = new word_api();
        if (!$this->excluded) {
            parent::fill_api_obj($api_obj);
        }
        return $api_obj;
    }

    /**
     * @return word_dsp the word object with the display interface functions
     */
    function dsp_obj(): word_dsp
    {
        $dsp_obj = new word_dsp();

        if (!$this->excluded) {
            $dsp_obj = parent::fill_dsp_obj($dsp_obj);

            $dsp_obj->plural = $this->plural;
            $dsp_obj->type_id = $this->type_id;
            $dsp_obj->view_id = $this->view_id;
            $dsp_obj->values = $this->values;

            $dsp_obj->link_type_id = $this->link_type_id;

            $dsp_obj->share_id = $this->share_id;
            $dsp_obj->protection_id = $this->protection_id;

            $dsp_obj->view = $this->view;
            $dsp_obj->ref_lst = $this->ref_lst;
        }

        return $dsp_obj;
    }

    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the SQL to load the default word always by the id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(DB_TYPE_WORD);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)
        ));

        return parent::load_standard_sql($db_con, self::class);
    }

    /**
     * load the word parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard word has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con);
        $result = parent::load_standard($qp, self::class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a word from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($db_con, self::class);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err("Either the database ID (" . $this->id . ") or the word name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to load a word.", "word->load");
        }

        $db_con->set_type(DB_TYPE_WORD);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $db_con->set_where_std($this->id, $this->name);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the missing word parameters from the database
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            // don't use too specific error text, because for each unique error text a new message is created
            //log_err('The user id must be set to load word '.$this->dsp_id().'.', "word->load");
            log_err('The user id must be set to load word.', "word->load");
        } elseif ($this->id <= 0 and $this->name == '') {
            log_err("Either the database ID (" . $this->id . ") or the word name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to load a word.", "word->load");
        } else {

            $qp = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
                $db_wrd = $db_con->get1($qp);
                $this->row_mapper($db_wrd);
                if ($this->id <> 0) {
                    if (is_null($db_wrd[self::FLD_EXCLUDED]) or $db_wrd[self::FLD_EXCLUDED] == 0) {
                        // additional user sandbox fields
                        $this->type_name();
                    }
                    log_debug($this->dsp_id());
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * return the main word object based on an id text e.g. used in view.php to get the word to display
     * TODO: check if needed and review
     */
    function main_wrd_from_txt($id_txt)
    {
        if ($id_txt <> '') {
            log_debug('from "' . $id_txt . '"');
            $wrd_ids = explode(",", $id_txt);
            log_debug('check if "' . $wrd_ids[0] . '" is a number');
            if (is_numeric($wrd_ids[0])) {
                $this->id = $wrd_ids[0];
                log_debug('from "' . $id_txt . '" got id ' . $this->id);
            } else {
                $this->name = $wrd_ids[0];
                log_debug('from "' . $id_txt . '" got name ' . $this->name);
            }
            $this->load();
        }
    }

    /*
     * data retrieval functions
     */

    /**
     * get a list of values related to this word
     * @param int $limit
     * @return value_list a list object with the most relevant values related to this word
     */
    function value_list(int $limit = SQL_ROW_LIMIT): value_list
    {
        $val_lst = new value_list($this->usr);
        $val_lst->load();
        return $val_lst;
    }

    /**
     * get the view object for this word
     */
    function load_view(): ?view
    {
        $result = null;

        $this->load();

        if ($this->view != null) {
            $result = $this->view;
        } else {
            if ($this->view_id > 0) {
                log_debug('got id ' . $this->view_id);
                $result = new view($this->usr);
                $result->id = $this->view_id;
                if ($result->load()) {
                    $this->view = $result;
                    log_debug('for ' . $this->dsp_id() . ' is ' . $result->dsp_id());
                }
            }
        }

        return $result;
    }

    // TODO review, because is it needed? get the view used by most users for this word

    function view_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(DB_TYPE_WORD);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array(self::FLD_VIEW));
        $db_con->set_join_usr_count_fields(array(sql_db::FLD_USER_ID), DB_TYPE_WORD);
        $qp = new sql_par(self::class);
        $qp->name = 'word_view_most_used';
        $db_con->set_name($qp->name);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * get the suggested view
     * @return int the view of the most often used view
     */
    function view_id(): int
    {
        log_debug('for ' . $this->dsp_id());

        global $db_con;

        $view_id = 0;
        $qp = $this->view_sql($db_con);
        $db_row = $db_con->get1($qp);
        if (isset($db_row)) {
            $view_id = $db_row[self::FLD_VIEW];
        }

        log_debug('for ' . $this->dsp_id() . ' got ' . $view_id);
        return $view_id;
    }

    /**
     * get the view used by most other users
     * @return view the view of the most often used view
     */
    function suggested_view(): view
    {
        $dsp = new view($this->usr);
        $dsp->load_by_phrase($this->phrase());
        return $dsp;
    }

    /**
     * get a list of all values related to this word
     */
    function val_lst(): value_list
    {
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');
        $val_lst = new value_list($this->usr);
        $val_lst->phr = $this->phrase();
        $val_lst->page_size = SQL_ROW_MAX;
        $val_lst->load();
        log_debug('got ' . dsp_count($val_lst->lst));
        return $val_lst;
    }

    /**
     * if there is just one formula linked to the word, get it
     * TODO separate the query parameter creation and add a unit test
     * TODO allow also to retrieve a list of formulas
     * TODO get the user specific list of formulas
     */
    function formula(): formula
    {
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');

        global $db_con;

        $db_con->set_type(DB_TYPE_FORMULA_LINK);
        $qp = new sql_par(self::class);
        $qp->name = 'word_formula_by_id';
        $db_con->set_name($qp->name);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link(null, null, $this->id);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();
        $db_row = $db_con->get1($qp);
        $frm = new formula($this->usr);
        if ($db_row !== false) {
            if ($db_row[formula::FLD_ID] > 0) {
                $frm->id = $db_row[formula::FLD_ID];
                $frm->load();
            }
        }

        return $frm;
    }

    /**
     * import a word from a json data word object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        global $word_types;
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new user_message();

        // reset all parameters for the word object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->usr = $usr;
        foreach ($json_obj as $key => $value) {
            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == exp_obj::FLD_TYPE) {
                $this->type_id = $word_types->id($value);
            }
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protection_type::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
            }
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->usr);
                $wrd_view->name = $value;
                if ($do_save) {
                    $wrd_view->load();
                    if ($wrd_view->id == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id;
                    }
                }
                $this->view = $wrd_view;
            }
        }

        // set the default type if no type is specified
        if ($this->type_id == 0) {
            $this->type_id = $word_types->default_id();
        }
        // save the word in the database
        if ($do_save) {
            // TODO should save not return the error reason that should be shown to the user if it fails?
            $result->add_message($this->save());
        }

        // add related parameters to the word object
        if ($result->is_ok()) {
            log_debug('saved ' . $this->dsp_id());

            if ($this->id <= 0 and $do_save) {
                $result->add_message('Word ' . $this->dsp_id() . ' cannot be saved');
            } else {
                foreach ($json_obj as $key => $value) {
                    if ($result->is_ok()) {
                        if ($key == self::FLD_REFS) {
                            foreach ($value as $ref_data) {
                                $ref_obj = new ref($this->usr);
                                $ref_obj->phr = $this->phrase();
                                $result->add($ref_obj->import_obj($ref_data, $do_save));
                                $this->ref_lst[] = $ref_obj;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * create a word object for the export
     * @param bool $do_load can be set to false for unit testing
     * @return user_sandbox_exp_named a reduced word object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): user_sandbox_exp_named
    {
        global $word_types;

        log_debug();
        $result = new word_exp();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if ($this->plural <> '') {
            $result->plural = $this->plural;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if (isset($this->type_id)) {
            if ($this->type_id <> $word_types->default_id()) {
                $result->type = $this->type_code_id();
            }
        }

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> cl(db_cl::SHARE_TYPE, share_type::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> cl(db_cl::PROTECTION_TYPE, protection_type::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }

        if ($this->view_id > 0) {
            if ($do_load) {
                $this->view = $this->load_view();
            }
        }
        if (isset($this->view)) {
            $result->view = $this->view->name;
        }
        if (isset($this->ref_lst)) {
            foreach ($this->ref_lst as $ref) {
                $result->refs[] = $ref->export_obj();
            }
        }


        log_debug(json_encode($result));
        return $result;
    }


    /*
     * display functions
     */

    /**
     * return the name (just because all objects should have a name function)
     */
    function name(): string
    {
        if ($this->excluded) {
            return '';
        } else {
            return $this->name;
        }
    }

    /*
     * TODO display functions to review
     */

    /**
     * list of related words and values filtered by a link type
     */
    function dsp_val_list(word $col_wrd, phrase $is_part_of, string $back): string
    {
        log_debug('word_dsp->dsp_val_list for ' . $this->dsp_id() . ' with "' . $col_wrd->name . '"');

        $is_part_of_dsp = $is_part_of->get_dsp_obj();
        $result = $this->dsp_obj()->header($is_part_of_dsp);

        //$result .= $this->name."<br>";
        //$result .= $col_wrd->name."<br>";

        $row_lst = $this->children();    // not $this->are(), because e.g. for "Company" the word "Company" itself should not be included in the list
        $col_lst = $col_wrd->children();
        log_debug('word_dsp->dsp_val_list -> columns ' . $col_lst->dsp_id());

        $row_lst->name_sort();
        $col_lst->name_sort();

        // TODO use this for fast loading
        $val_matrix = $row_lst->val_matrix($col_lst);
        $row_lst_dsp = $row_lst->dsp_obj();
        $result .= $row_lst_dsp->dsp_val_matrix($val_matrix);

        log_debug('word_dsp->dsp_val_list -> table');

        // display the words
        $row_nbr = 0;
        $result .= dsp_tbl_start();
        foreach ($row_lst->lst as $row_phr) {
            // display the column headers
            // not needed any more if wrd lst is created based on word_display elements
            // to review
            $row_phr_dsp = new word($this->usr);
            $row_phr_dsp->id = $row_phr->id;
            $row_phr_dsp->load();
            if ($row_nbr == 0) {
                $result .= '  <tr>' . "\n";
                $result .= '    <th>' . "\n";
                $result .= '    </th>' . "\n";
                foreach ($col_lst->lst as $col_lst_wrd) {
                    log_debug('word_dsp->dsp_val_list -> column ' . $col_lst_wrd->name);
                    $result .= $col_lst_wrd->dsp_obj()->dsp_th($back, api::STYLE_RIGHT);
                }
                $result .= '  </tr>' . "\n";
            }

            // display the rows
            log_debug('word_dsp->dsp_val_list -> row');
            $result .= '  <tr>' . "\n";
            $result .= '      ' . $row_phr_dsp->dsp_obj()->td($back);
            foreach ($col_lst->lst as $col_lst_wrd) {
                $result .= '    <td>' . "\n";
                $val_wrd_ids = array();
                $val_wrd_ids[] = $row_phr->id;
                $val_wrd_ids[] = $col_lst_wrd->id;
                asort($val_wrd_ids);
                $val_wrd_lst = new word_list($this->usr);
                $val_wrd_lst->load_by_ids($val_wrd_ids);
                log_debug('word_dsp->dsp_val_list -> get group ' . dsp_array($val_wrd_ids));
                $wrd_grp = $val_wrd_lst->get_grp();
                if ($wrd_grp->id > 0) {
                    log_debug('word_dsp->dsp_val_list -> got group ' . $wrd_grp->id);
                    $in_value = $wrd_grp->result(0);
                    $fv_text = '';
                    // temp solution to be reviewed
                    if ($in_value['id'] > 0) {
                        $fv = new formula_value($this->usr);
                        $fv->load_by_id($in_value['id']);
                        if ($fv->value <> 0) {
                            $fv_text = $fv->val_formatted();
                        } else {
                            $fv_text = '';
                        }
                    }
                    if ($fv_text <> '') {
                        //$back = $row_phr->id;
                        if (!isset($back)) {
                            $back = $this->id;
                        }
                        if ($in_value['usr'] > 0) {
                            $result .= '      <p class="right_ref"><a href="/http/formula_result.php?id=' . $in_value['id'] . '&phrase=' . $row_phr->id . '&group=' . $wrd_grp->id . '&back=' . $back . '" class="user_specific">' . $fv_text . '</a></p>' . "\n";
                        } else {
                            $result .= '      <p class="right_ref"><a href="/http/formula_result.php?id=' . $in_value['id'] . '&phrase=' . $row_phr->id . '&group=' . $wrd_grp->id . '&back=' . $back . '">' . $fv_text . '</a></p>' . "\n";
                        }
                    }
                }
                $result .= '    </td>' . "\n";
            }
            $result .= '  </tr>' . "\n";
            $row_nbr++;
        }

        // display an add button to offer the user to add one row
        $result .= '<tr><td>' . $this->btn_add($back) . '</td></tr>';

        $result .= dsp_tbl_end();

        return $result;
    }

    /**
     * returns the html code to select a word link type
     * database link must be open
     * TODO: similar to verb->dsp_selector maybe combine???
     */
    function selector_link($id, $form, $back): string
    {
        log_debug('word_dsp->selector_link ... verb id ' . $id);
        global $db_con;

        $result = '';

        $sql_name = "";
        if ($db_con->get_type() == sql_db::POSTGRES) {
            $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        } elseif ($db_con->get_type() == sql_db::MYSQL) {
            $sql_name = "IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name";
        } else {
            log_err('Unknown db type ' . $db_con->get_type());
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->sql = $sql;
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        if ($this->usr->is_admin()) {
            // admin users should always have the possibility to create a new link type
            $result .= \html\btn_add('add new link type', '/http/verb_add.php?back=' . $back);
        }

        return $result;
    }

    // to select an existing word to be added
    private function selector_add($id, $form, $bs_class): string
    {
        log_debug('word_dsp->selector_add ... word id ' . $id);
        $result = '';
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'add';
        $sel->label = "Word:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst_usr("word", $this->usr);
        $sel->selected = $id;
        $sel->dummy_text = '... or select an existing word to link it';
        $result .= $sel->display();

        return $result;
    }

    // returns the html code to select a word
    // database link must be open
    function selector_word($id, $pos, $form_name): string
    {
        log_debug('word_dsp->selector_word ... word id ' . $id);
        $result = '';

        if ($pos > 0) {
            $field_id = "word" . $pos;
        } else {
            $field_id = "word";
        }
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_id;
        $sel->sql = sql_lst_usr("word", $this->usr);
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        log_debug('word_dsp->selector_word ... done ' . $id);
        return $result;
    }

    //
    private function type_selector($script, $bs_class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->name = 'type';
        $sel->label = "Word type:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst("word_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = '';
        $result .= $sel->display();
        return $result;
    }

    // HTML code to edit all word fields
    function dsp_add($wrd_id, $wrd_to, $vrb_id, $back): string
    {
        log_debug('word_dsp->dsp_add ' . $this->dsp_id() . ' or link the existing word with id ' . $wrd_id . ' to ' . $wrd_to . ' by verb ' . $vrb_id . ' for user ' . $this->usr->name . ' (called by ' . $back . ')');
        $result = '';

        $form = "word_add";
        $result .= dsp_text_h2('Add a new word');
        $result .= dsp_form_start($form);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        $result .= dsp_form_text("word_name", $this->name, "Name:", "col-sm-4");
        $result .= $this->dsp_type_selector($form, "col-sm-4");
        $result .= $this->selector_add($wrd_id, $form, "form-row") . ' ';
        $result .= '</div>';
        $result .= 'which ';
        $result .= '<div class="form-row">';
        $result .= $this->selector_link($vrb_id, $form, $back);
        $result .= $this->selector_word($wrd_to, 0, $form);
        $result .= '</div>';
        $result .= dsp_form_end('', $back);

        log_debug('word_dsp->dsp_add ... done');
        return $result;
    }

    function dsp_formula(string $back = ''): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::WORD_TYPE, phrase_type::FORMULA_LINK)) {
            $result .= dsp_form_hidden("name", $this->name);
            $result .= '  to change the name of "' . $this->name . '" rename the ';
            $frm = $this->formula();
            $result .= $frm->dsp_obj()->name_linked($back);
            $result .= '.<br> ';
        } else {
            $result .= dsp_form_text("name", $this->name, "Name:", "col-sm-4");
        }
        return $result;
    }

    function dsp_type_selector(string $back = ''): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::WORD_TYPE, phrase_type::FORMULA_LINK)) {
            $result .= ' type: ' . $this->type_name();
        } else {
            $result .= $this->type_selector('word_edit', "col-sm-4");
        }
        return $result;
    }

    function dsp_graph(string $direction, verb_list $link_types, string $back = ''): string
    {
        return $this->phrase()->dsp_graph($direction, $link_types, $back);
    }


    /**
     * HTML code to edit all word fields
     */
    function dsp_edit(string $back = ''): string
    {
        $phr_lst_up = $this->parents();
        $phr_lst_down = $this->children();
        $phr_lst_up_dsp = $phr_lst_up->dsp_obj();
        $phr_lst_down_dsp = $phr_lst_down->dsp_obj();
        $dsp_graph = $phr_lst_up_dsp->dsp_graph($this->phrase(), $back);
        $dsp_graph .= $phr_lst_down_dsp->dsp_graph($this->phrase(), $back);
        $wrd_dsp = $this->dsp_obj();
        // collect the display code for the user changes
        $dsp_log = '';
        $changes = $this->dsp_hist(1, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= dsp_text_h3("Latest changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= dsp_text_h3("Latest link changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        return $wrd_dsp->dsp_edit(
            $dsp_graph,
            $dsp_log,
            $this->dsp_formula($back),
            $this->dsp_type_selector($back),
            $back);
    }

    function view(): ?view
    {
        return $this->load_view();
    }

    /*
    // offer the user to export the word and the relations as a xml file
    function config_json_export(string $back = ''): string
    {
        return 'Export as <a href="/http/get_json.php?words=' . $this->name . '&back=' . $back . '">JSON</a>';
    }

    // offer the user to export the word and the relations as a xml file
    function config_xml_export($back)
    {
        $result = '';
        $result .= 'Export as <a href="/http/get_xml.php?words=' . $this->name . '&back=' . $back . '">XML</a>';
        return $result;
    }

    // offer the user to export the word and the relations as a xml file
    function config_csv_export($back)
    {
        $result = '<a href="/http/get_csv.php?words=' . $this->name . '&back=' . $back . '">CSV</a>';
        return $result;
    }
    */

    /**
     * to add a word linked to this word
     * e.g. if this word is "Company" to add another company
     */
    function btn_add(string $back = ''): string
    {
        global $word_types;
        $vrb_is = cl(db_cl::VERB, verb::IS_A);
        $wrd_type = $word_types->default_id(); // maybe base it on the other linked words
        $wrd_add_title = "add a new " . $this->name;
        $wrd_add_call = "/http/word_add.php?verb=" . $vrb_is . "&word=" . $this->id . "&type=" . $wrd_type . "&back=" . $back . "";
        return (new button($wrd_add_title, $wrd_add_call))->add();
    }

    /**
     * get the database id of the word type
     * also to fix a problem if a phrase list contains a word
     * @return int the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * get the name of the word type
     * @return string the name of the word type
     */
    function type_name(): string
    {
        global $word_types;
        return $word_types->name($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $word_types;
        return $word_types->code_id($this->type_id);
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     */
    function is_type(string $type): bool
    {
        global $word_types;

        log_debug($this->dsp_id() . ' is ' . $type);

        $result = false;
        if ($this->type_id == $word_types->id($type)) {
            $result = true;
            log_debug($this->dsp_id() . ' is ' . $type);
        }
        return $result;
    }

    /**
     * @returns bool true if the word has the type "time"
     */
    function is_time(): bool
    {
        return $this->is_type(phrase_type::TIME);
    }

    /**
     * @return bool true if the word is just to define the default period
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type::TIME_JUMP);
    }

    /**
     * @returns bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type::MEASURE);
    }

    /**
     * @returns bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING)
            or $this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

    /**
     * tree building function
     * ----------------------
     *
     * Overview for words, triples and phrases and it's lists
     *
     * children and            parents return the direct parents and children   without the original phrase(s)
     * foaf_children and       foaf_parents return the    all parents and children   without the original phrase(s)
     * are and                 is return the    all parents and children including the original phrase(s) for the specific verb "is a"
     * contains                        return the    all             children including the original phrase(s) for the specific verb "contains"
     * is part of return the    all parents                without the original phrase(s) for the specific verb "contains"
     * next and              prior return the direct parents and children   without the original phrase(s) for the specific verb "follows"
     * followed_by and        follower_of return the    all parents and children   without the original phrase(s) for the specific verb "follows"
     * differentiated_by and differentiator_for return the    all parents and children   without the original phrase(s) for the specific verb "can_contain"
     *
     * Samples
     *
     * the        parents of  "ABB" can be "public limited company"
     * the   foaf_parents of  "ABB" can be "public limited company" and "company"
     * "is" of  "ABB" can be "public limited company" and "company" and "ABB" (used to get all related values)
     * the       children for "company" can include "public limited company"
     * the  foaf_children for "company" can include "public limited company" and "ABB"
     * "are" for "company" can include "public limited company" and "ABB" and "company" (used to get all related values)
     *
     * "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
     * "is part of" for "assets" is "balance sheet" but not "assets"
     *
     * "next" for "2016" is "2017"
     * "prior" for "2017" is "2016"
     * "is followed by" for "2016" is "2017" and "2018"
     * "is follower of" for "2016" is "2015" and "2014"
     *
     * "wind energy" and "energy" "can be differentiator for" "sector"
     * "sector" "can be differentiated_by"  "wind energy" and "energy"
     *
     * if "wind energy" "is part of" "energy"
     */

    /**
     * helper function that returns a phrase list object just with the word object
     */
    function lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_lst->add($this->phrase());
        return $phr_lst;
    }

    /**
     * returns a list of words (actually phrases) that are related to this word
     * e.g. for "Zurich" it will return "Canton", "City" and "Company", but not "Zurich" itself
     */
    function parents(): phrase_list
    {
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
        $phr_lst = $this->lst();
        $parent_phr_lst = $phr_lst->foaf_parents(cl(db_cl::VERB, verb::IS_A));
        log_debug('are ' . $parent_phr_lst->dsp_name() . ' for ' . $this->dsp_id());
        return $parent_phr_lst;
    }

    /**
     * TODO maybe collect the single words or this is a third case
     * returns a list of words that are related to this word
     * e.g. for "Zurich" it will return "Canton", "City" and "Company" and "Zurich" itself
     *      to be able to collect all relations to the given word e.g. Zurich
     */
    function is(): phrase_list
    {
        $phr_lst = $this->parents();
        $phr_lst->add($this->phrase());
        log_debug($this->dsp_id() . ' is a ' . $phr_lst->dsp_name());
        return $phr_lst;
    }

    /**
     * returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
     */
    function is_mainly(): phrase
    {
        $result = null;
        $is_phr_lst = $this->is();
        if (count($is_phr_lst->lst) >= 1) {
            $result = $is_phr_lst->lst[0];
        }
        log_debug($this->dsp_id() . ' is a ' . $result->name);
        return $result;
    }

    /**
     * add a child word to this word
     * e.g. Zurich (child) is a Canton (Parent)
     * @param word $child the word that should be added as a child
     * @return bool
     */
    function add_child(word $child): bool
    {
        global $verbs;

        $result = false;
        $wrd_lst = $this->children();
        if (!$wrd_lst->does_contain($child)) {
            $wrd_lnk = new word_link($this->usr);
            $wrd_lnk->from = $child->phrase();
            $wrd_lnk->verb = $verbs->get_verb(verb::IS_A);
            $wrd_lnk->to = $this->phrase();
            if ($wrd_lnk->save() == '') {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return phrase_list a list of words that are related to this word
     * e.g. for "Canton" it will return "Zurich (Canton)" and others, but not "Canton" itself
     */
    function children(): phrase_list
    {
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
        $phr_lst = $this->lst();
        $child_phr_lst = $phr_lst->foaf_children(cl(db_cl::VERB, verb::IS_A));
        log_debug('are ' . $child_phr_lst->name() . ' for ' . $this->dsp_id());
        return $child_phr_lst;
    }

    /**
     * @return phrase_list a list of words that are related to the given word
     * e.g. for "Canton" it will return "Zurich (Canton)" and "Canton", but not "Zurich (City)"
     * used to collect e.g. all formulas used for Canton
     */
    function are(): phrase_list
    {
        $wrd_lst = $this->children();
        $wrd_lst->add($this->phrase());
        return $wrd_lst;
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @return phrase_list all phrases linked with are and contains
     */
    function are_and_contains(): phrase_list
    {
        log_debug('for ' . $this->dsp_id());

        // this first time get all related items
        $phr_lst = $this->lst();
        $phr_lst = $phr_lst->are();
        $added_lst = $phr_lst->contains();
        $added_lst->diff($this->lst());
        // ... and after that get only for the new
        if ($added_lst->count() > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $added_lst = $next_lst->contains();
                $added_lst->diff($phr_lst);
                if (count($added_lst->lst) > 0) {
                    log_debug('add ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while (count($added_lst->lst) > 0 and $loops < MAX_LOOP);
        }
        log_debug($this->dsp_id() . ' are_and_contains ' . $phr_lst->dsp_id());
        return $phr_lst;
    }

    /**
     * return the follow word id based on the predefined verb following
     */
    function next(): word
    {
        log_debug($this->dsp_id() . ' and user ' . $this->usr->name);

        global $db_con;
        $result = new word($this->usr);

        $link_id = cl(db_cl::VERB, verb::FOLLOW);
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_TRIPLE);
        $key_result = $db_con->get_value_2key('from_phrase_id', 'to_phrase_id', $this->id, verb::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $result->id = intval($key_result);
        }
        if ($result->id > 0) {
            $result->load();
        }
        return $result;
    }

    /**
     * return the follow word id based on the predefined verb following
     */
    function prior(): word
    {
        log_debug($this->dsp_id() . ',u' . $this->usr->id);

        global $db_con;
        $result = new word($this->usr);

        $link_id = cl(db_cl::VERB, verb::FOLLOW);
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_TRIPLE);
        $key_result = $db_con->get_value_2key('to_phrase_id', 'from_phrase_id', $this->id, verb::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $result->id = intval($key_result);
        }
        if ($result->id > 0) {
            $result->load();
        }
        return $result;
    }

    /**
     * calculates how many times a word is used, because this can be helpful for sorting
     */
    function calc_usage(): bool
    {
        global $db_con;

        $sql = 'UPDATE words t
             SET ' . $db_con->sf("values") . ' = ( 
          SELECT COUNT(value_id) 
            FROM value_phrase_links l
           WHERE l.phrase_id = t.word_id);';
        $db_con->exe_try('Calculate word usage', $sql);
        return true;
    }

    /**
     * returns the more general word as defined by "is part of"
     * e.g. for "Meilen (District)" it will return "Zürich (Canton)"
     * for the value selection this should be tested level by level
     * to use by default the most specific value
     */
    function is_part(): phrase_list
    {
        log_debug($this->dsp_id() . ', user ' . $this->usr->id);
        $phr_lst = $this->lst();
        $is_phr_lst = $phr_lst->foaf_parents(cl(db_cl::VERB, verb::IS_PART_OF));

        log_debug($this->dsp_id() . ' is a ' . $is_phr_lst->dsp_name());
        return $is_phr_lst;
    }


    /*
     * functions that create and fill related objects
     */

    /**
     * returns a list of the link types related to this word e.g. for "Company" the link "are" will be returned, because "ABB" "is a" "Company"
     */
    function link_types(string $direction): verb_list
    {
        log_debug($this->dsp_id() . ' and user ' . $this->usr->id);

        global $db_con;

        $vrb_lst = new verb_list($this->usr);
        $wrd = clone $this;
        $phr = $wrd->phrase();
        $vrb_lst->load_by_linked_phrases($db_con, $phr, $direction);
        return $vrb_lst;
    }

    /**
     * return a list of upward related verbs e.g. 'is a' for Zurich because Zurich is a City
     */
    private function verb_list_up(): verb_list
    {
        return $this->link_types(word_select_direction::UP);
    }

    /**
     * return a list of downward related verbs e.g. 'contains' for Mathematical constant because Mathematical constant contains Pi
     */
    private function verb_list_down(): verb_list
    {
        return $this->link_types(word_select_direction::DOWN);
    }

    private function phrase_list_up(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        return $phr_lst->parents();
    }

    private function phrase_list_down(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        return $phr_lst->children();
    }

    /*
     * display functions
     */

    /**
     * display the history of a word
     * maybe move this to a new object user_log_display
     * because this is very similar to a value linked function
     */
    public function dsp_hist(int $page = 1, int $size = 20, string $call = '', string $back = ''): string
    {
        log_debug("word_dsp->dsp_hist for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->usr);
        $log_dsp->id = $this->id;
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug('word_dsp->dsp_hist -> done');
        return $result;
    }

    /**
     * display the history of a word
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug("word_dsp->dsp_hist_links (" . $this->id . ",size" . $size . ",b" . $size . ")");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->usr);
        $log_dsp->id = $this->id;
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug('word_dsp->dsp_hist_links -> done');
        return $result;
    }

    /*
     * convert functions
     */

    /**
     * convert the word object into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->usr);
        $phr->id = $this->id;
        $phr->name = $this->name;
        $phr->obj = $this;
        log_debug($this->dsp_id());
        return $phr;
    }

    /*
     * save / database transfer object functions
     */

    /**
     * true if the word has any none default settings such as a special type
     */
    function has_cfg(): bool
    {
        global $word_types;

        $has_cfg = false;
        if (isset($this->plural)) {
            if ($this->plural <> '') {
                $has_cfg = true;
            }
        }
        if (isset($this->description)) {
            if ($this->description <> '') {
                $has_cfg = true;
            }
        }
        if (isset($this->type_id)) {
            if ($this->type_id <> $word_types->default_id()) {
                $has_cfg = true;
            }
        }
        if (isset($this->view_id)) {
            if ($this->view_id > 0) {
                $has_cfg = true;
            }
        }
        return $has_cfg;
    }

    function not_used(): bool
    {
        log_debug($this->id);

        if (parent::not_used()) {
            $result = true;
            // check if no value is related to the word
            // check if no phrase group is linked to the word
            // TODO if a value or formula is linked to the word the user should see a warning message, which he can confirm
            return $result;
        } else {
            return false;
        }

        /*    $change_user_id = 0;
            $sql = "SELECT user_id
                      FROM user_words
                     WHERE word_id = ".$this->id."
                       AND user_id <> ".$this->owner_id."
                       AND (excluded <> 1 OR excluded is NULL)";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $change_user_id = $db_con->get1($sql);
            if ($change_user_id > 0) {
              $result = false;
            } */
        //return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the word has been changed
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(DB_TYPE_WORD);
        return $db_con->not_changed_sql($this->id, $this->owner_id);
    }

    /**
     * true if no other user has modified the word
     * assuming that in this case not confirmation from the other users for a word rename is needed
     */
    function not_changed(): bool
    {
        log_debug($this->id . ' by someone else than the owner (' . $this->owner_id);

        global $db_con;
        $result = true;

        if ($this->id == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[self::FLD_USER] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * true if the user is the owner and no one else has changed the word
     * because if another user has changed the word and the original value is changed, maybe the user word also needs to be updated
     */
    function can_change(): bool
    {
        log_debug($this->id . ',u' . $this->usr->id);
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $wrd_user = $this->changer();
            if ($wrd_user == $this->usr->id or $wrd_user <= 0) {
                $can_change = true;
            }
        }

        log_debug(zu_dsp_bool($can_change));
        return $can_change;
    }

    /**
     * true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    private function no_usr_fld_used($db_row): bool
    {
        $result = true;
        foreach (self::ALL_FLD_NAMES as $field_name) {
            if ($db_row[$field_name] != '') {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * check if the database record for the user specific settings can be removed
     * TODO separate the query parameter creation and add a unit test
     * @return bool true if the checking and the potential removing has been successful, which does not mean, that the user sandbox database row has actually been removed
     */
    function del_usr_cfg_if_not_needed(): bool
    {

        global $db_con;
        $result = true;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        // TODO add user id to where
        $db_con->set_type(DB_TYPE_WORD);
        $qp = new sql_par(self::class);
        $qp->name = 'word_del_usr_cfg_if';
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::ALL_FLD_NAMES);
        $db_con->set_where_std($this->id);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();
        $usr_wrd_cfg = $db_con->get1($qp);
        if ($usr_wrd_cfg != null) {
            log_debug('for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $qp->sql . ')');
            if ($usr_wrd_cfg[self::FLD_ID] > 0) {
                if ($this->no_usr_fld_used($usr_wrd_cfg)) {
                    // delete the entry in the user sandbox
                    log_debug('any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        //}
        return $result;
    }

    /**
     * set the log entry parameters for a value update
     */
    private
    function log_upd_view($view_id): user_log_named
    {
        log_debug($this->dsp_id() . ' for user ' . $this->usr->name);
        $dsp_new = new view_dsp_old($this->usr);
        $dsp_new->id = $view_id;
        $dsp_new->load();

        $log = new user_log_named;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
        $log->table = 'words';
        $log->field = self::FLD_VIEW;
        if ($this->view_id > 0) {
            $dsp_old = new view_dsp_old($this->usr);
            $dsp_old->id = $this->view_id;
            $dsp_old->load();
            $log->old_value = $dsp_old->name;
            $log->old_id = $dsp_old->id;
        } else {
            $log->old_value = '';
            $log->old_id = 0;
        }
        $log->new_value = $dsp_new->name;
        $log->new_id = $dsp_new->id;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * remember the word view, which means to save the view id for this word
     * each user can define set the view individually, so this is user specific
     */
    function save_view($view_id): string
    {

        global $db_con;
        $result = '';

        if ($this->id > 0 and $view_id > 0 and $view_id <> $this->view_id) {
            log_debug($view_id . ' for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
            if ($this->log_upd_view($view_id) > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->usr->id;
                if ($this->can_change()) {
                    $db_con->set_type(DB_TYPE_WORD);
                    if (!$db_con->update($this->id, "view_id", $view_id)) {
                        $result = 'setting of view failed';
                    }
                } else {
                    if (!$this->has_usr_cfg()) {
                        if (!$this->add_usr_cfg()) {
                            $result = 'adding of user configuration failed';
                        }
                    }
                    if ($result == '') {
                        $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_WORD);
                        if (!$db_con->update($this->id, "view_id", $view_id)) {
                            $result = 'setting of view for user failed';
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word plural
     */
    private function save_field_plural(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        // if the plural is not set, don't overwrite any db entry
        if ($this->plural <> Null) {
            if ($this->plural <> $db_rec->plural) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->plural;
                $log->new_value = $this->plural;
                $log->std_value = $std_rec->plural;
                $log->row_id = $this->id;
                $log->field = self::FLD_PLURAL;
                $result = $this->save_field_do($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word view_id
     */
    private function save_field_view($db_rec): string
    {
        $result = '';
        if ($db_rec->view_id <> $this->view_id) {
            $result = $this->save_view($this->view_id);
        }
        return $result;
    }

    /**
     * save all updated word fields
     */
    function save_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        log_debug();
        $result = $this->save_field_plural($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_view($db_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    function get_obj_with_same_id_fields(): user_sandbox
    {
        $db_chk = parent::get_obj_with_same_id_fields();
        if ($db_chk->id > 0) {
            if ($this->obj_name == word::class or $this->obj_name == word_dsp::class) {
                // TODO check if this is always correct
                $db_chk->id = 0;
            }
        }

        return $db_chk;
    }

    /**
     * delete the references to this word which includes the phrase groups, the triples and values
     *
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all phrase groups where this word is used
        $grp_lst = new phrase_group_list($this->usr);
        $grp_lst->phr = $this->phrase();
        $grp_lst->load();

        // collect all triples where this word is used
        $trp_lst = new word_link_list($this->usr);
        $trp_lst->load_by_phr($this->phrase());

        // collect all values related to word triple
        $val_lst = new value_list($this->usr);
        $val_lst->phr = $this->phrase();
        $val_lst->load();

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $result->add($val_lst->del());
        }

        // if there are still triples, ask if they really should be deleted
        if ($trp_lst->has_values()) {
            $result->add($trp_lst->del());
        }

        // delete the phrase groups
        $result->add($grp_lst->del());

        return $result;
    }

}
