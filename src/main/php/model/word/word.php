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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class word extends user_sandbox_description
{
    // object specific database and JSON object field names
    // means: database fields only used for words
    const FLD_ID = 'word_id';
    const FLD_NAME = 'word_name';
    const FLD_PLURAL = 'plural';
    const FLD_TYPE = 'word_type_id';
    const FLD_VIEW = 'view_id';
    const FLD_VALUES = 'values';

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
        sql_db::FLD_SHARE,
        sql_db::FLD_PROTECT
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

    // persevered word names for system settings
    const DB_SETTINGS = 'System database settings';

    // persevered word names for unit and integration tests
    const TN_READ = 'Mathematical constant';
    const TN_ADD = 'System Test Word';
    const TN_RENAMED = 'System Test Word Renamed';
    const TN_PARENT = 'System Test Word Parent';
    const TN_CH = 'System Test Word Parent e.g. Switzerland';
    const TN_ZH = 'System Test Word Member e.g. Zurich';
    const TN_COUNTRY = 'System Test Word Parent e.g. Country';
    const TN_CANTON = 'System Test Word Category e.g. Canton';
    const TN_CITY = 'System Test Word Another Category e.g. City';
    const TN_COMPANY = 'System Test Word Group e.g. Company';
    const TN_PARENT_NON_INHERITANCE = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TN_CHILD_NON_INHERITANCE = 'System Test Word Child without Inheritance e.g. Income Taxes';
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
        self::TN_PARENT_NON_INHERITANCE,
        self::TN_CHILD_NON_INHERITANCE,
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
        self::TN_INHABITANT
    );
    const TEST_WORDS_STANDARD = array(
        self::TN_PARENT,
        self::TN_CH,
        self::TN_ZH,
        self::TN_COUNTRY,
        self::TN_CANTON,
        self::TN_CITY,
        self::TN_COMPANY,
        self::TN_PARENT_NON_INHERITANCE,
        self::TN_CHILD_NON_INHERITANCE,
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

    // database fields additional to the user sandbox fields
    public ?string $plural = null;    // the english plural name as a kind of shortcut; if plural is NULL the database value should not be updated
    public ?int $view_id = null;      // defines the default view for this word
    public ?int $values = null;       // the total number of values linked to this word as an indication how common the word is and to sort the words

    // in memory only fields
    public ?string $is_wrd = null;    // the main type object e.g. for "ABB" it is the word object for "Company"
    public ?int $is_wrd_id = null;    // the id for the parent (verb "is") object
    public ?int $dsp_pos = null;      // position of the word on the screen
    public ?int $dsp_lnk_id = null;   // position or link id based on which to item is displayed on the screen
    public ?int $link_type_id = null; // used in the word list to know based on which relation the word was added to the list


    // only used for the export object
    private ?view $view = null; // name of the default view for this word
    private ?array $ref_lst = [];

    /**
     * define the settings for this word object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_name = DB_TYPE_WORD;

        $this->rename_can_switch = UI_CAN_CHANGE_WORD_NAME;
    }

    function reset()
    {
        parent::reset();
        $this->plural = null;
        $this->type_id = null;
        $this->view_id = null;
        $this->values = null;

        $this->is_wrd = null;
        $this->is_wrd_id = null;
        $this->dsp_pos = null;
        $this->dsp_lnk_id = null;
        $this->link_type_id = null;

        $this->share_id = null;
        $this->protection_id = null;

        $this->view = null;
        $this->ref_lst = null;
    }

    /**
     * @return word_dsp the word object with the display interface functions
     */
    function dsp_obj(): object
    {
        $dsp_obj = new word_dsp($this->usr);

        $dsp_obj = parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->plural = $this->plural;
        $dsp_obj->type_id = $this->type_id;
        $dsp_obj->view_id = $this->view_id;
        $dsp_obj->values = $this->values;

        $dsp_obj->is_wrd = $this->is_wrd;
        $dsp_obj->is_wrd_id = $this->plural;
        $dsp_obj->dsp_pos = $this->dsp_pos;
        $dsp_obj->dsp_lnk_id = $this->dsp_lnk_id;
        $dsp_obj->link_type_id = $this->link_type_id;

        $dsp_obj->share_id = $this->share_id;
        $dsp_obj->protection_id = $this->protection_id;

        $dsp_obj->view = $this->view;
        $dsp_obj->ref_lst = $this->ref_lst;

        return $dsp_obj;
    }

    function row_mapper(array $db_row, bool $map_usr_fields = false)
    {
        if ($db_row != null) {
            // TODO excluded words should not be loaded, but it should be possible to restore them
            //if ($db_row[self::FLD_EXCLUDED] != 1) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->name = $db_row[self::FLD_NAME];
                $this->owner_id = $db_row[self::FLD_USER];
                $this->plural = $db_row[self::FLD_PLURAL];
                $this->description = $db_row[sql_db::FLD_DESCRIPTION];
                $this->type_id = $db_row[self::FLD_TYPE];
                $this->view_id = $db_row[self::FLD_VIEW];
                $this->excluded = $db_row[self::FLD_EXCLUDED];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row[$this->fld_usr_id()];
                    $this->share_id = $db_row[sql_db::FLD_SHARE];
                    $this->protection_id = $db_row[sql_db::FLD_PROTECT];
                } else {
                    $this->share_id = cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC);
                    $this->protection_id = cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO);
                }
            } else {
                $this->id = 0;
            }
            //} else {
            //    $this->id = 0;
            //}
        } else {
            $this->id = 0;
        }
    }

    /**
     * load the word parameters for all users
     */
    function load_standard(): bool
    {
        global $db_con;
        $result = '';

        $db_con->set_type(DB_TYPE_WORD);
        $db_con->set_fields(array(sql_db::FLD_USER_ID, self::FLD_PLURAL, sql_db::FLD_DESCRIPTION, self::FLD_TYPE, self::FLD_VIEW, self::FLD_EXCLUDED));
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_wrd = $db_con->get1_old($sql);
            $this->row_mapper($db_wrd);
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a word from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = self::class . '_by_';
        if ($this->id != 0) {
            $sql_name .= 'id';
        } elseif ($this->name != '') {
            $sql_name .= 'name';
        } else {
            log_err("Either the database ID (" . $this->id . ") or the word name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to load a word.", "word->load");
        }

        $db_con->set_type(DB_TYPE_WORD);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
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

            $sql = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
                $db_wrd = $db_con->get1_old($sql);
                $this->row_mapper($db_wrd, true);
                if ($this->id <> 0) {
                    if (is_null($db_wrd[self::FLD_EXCLUDED]) or $db_wrd[self::FLD_EXCLUDED] == 0) {
                        // additional user sandbox fields
                        $this->type_name();
                    }
                    log_debug('word->loaded ' . $this->dsp_id());
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
            log_debug('word->main_wrd_from_txt from "' . $id_txt . '"');
            $wrd_ids = explode(",", $id_txt);
            log_debug('word->main_wrd_from_txt check if "' . $wrd_ids[0] . '" is a number');
            if (is_numeric($wrd_ids[0])) {
                $this->id = $wrd_ids[0];
                log_debug('word->main_wrd_from_txt from "' . $id_txt . '" got id ' . $this->id);
            } else {
                $this->name = $wrd_ids[0];
                log_debug('word->main_wrd_from_txt from "' . $id_txt . '" got name ' . $this->name);
            }
            $this->load();
        }
    }

    /*
    data retrieval functions
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
                log_debug('word->view got id ' . $this->view_id);
                $result = new view($this->usr);
                $result->id = $this->view_id;
                if ($result->load()) {
                    $this->view = $result;
                    log_debug('word->view for ' . $this->dsp_id() . ' is ' . $result->dsp_id());
                }
            }
        }

        return $result;
    }

    // TODO review, because is it needed? get the view used by most users for this word

    /**
     * get the suggested view
     * @return int|mixed
     */
    function view_id()
    {
        log_debug('word->view_id for ' . $this->dsp_id());

        global $db_con;

        $view_id = 0;
        $sql = "SELECT view_id
              FROM ( SELECT u.view_id, count(u.user_id) AS users
                       FROM words w 
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                      WHERE w.word_id = " . $this->id . "
                   GROUP BY u.view_id ) as v
          ORDER BY users DESC;";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1_old($sql);
        if (isset($db_row)) {
            $view_id = $db_row[self::FLD_VIEW];
        }

        log_debug('word->view_id for ' . $this->dsp_id() . ' got ' . $view_id);
        return $view_id;
    }

    /**
     * get a list of all values related to this word
     */
    function val_lst(): value_list
    {
        log_debug('word->val_lst for ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');
        $val_lst = new value_list($this->usr);
        $val_lst->phr = $this->phrase();
        $val_lst->page_size = SQL_ROW_MAX;
        $val_lst->load();
        log_debug('word->val_lst -> got ' . dsp_count($val_lst->lst));
        return $val_lst;
    }

    /**
     * if there is just one formula linked to the word, get it
     * TODO allow also to retrieve a list of formulas
     * TODO get the user specific list of formulas
     */
    function formula(): formula
    {
        log_debug('word->formula for ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');

        global $db_con;

        $db_con->set_type(DB_TYPE_FORMULA_LINK);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link(null, null, $this->id);
        $sql = $db_con->select();
        $db_row = $db_con->get1_old($sql);
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
     * @return string an empty string if the import has been successfully saved to the database
     *                or the message that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): string
    {
        global $word_types;
        global $share_types;
        global $protection_types;

        log_debug('word->import_obj');
        $result = '';

        // reset all parameters for the word object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->usr = $usr;
        foreach ($json_obj as $key => $value) {
            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'type') {
                $this->type_id = $word_types->id($value);
            }
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            if ($key == 'description') {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
            if ($key == 'share') {
                $this->share_id = $share_types->id($value);
            }
            if ($key == 'protection') {
                $this->protection_id = $protection_types->id($value);
            }
            if ($key == 'view') {
                $wrd_view = new view($this->usr);
                $wrd_view->name = $value;
                if ($do_save) {
                    $wrd_view->load();
                    if ($wrd_view->id == 0) {
                        log_err('Cannot find view "' . $value . '" when importing ' . $this->dsp_id(), 'word->import_obj');
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
            $result = $this->save();
        }

        // add related parameters to the word object
        if ($result or !$do_save) {
            log_debug('word->import_obj -> saved ' . $this->dsp_id());

            if ($this->id <= 0 and $do_save) {
                log_err('Word ' . $this->dsp_id() . ' cannot be saved', 'word->import_obj');
            } else {
                foreach ($json_obj as $key => $value) {
                    if ($result or !$do_save) {
                        if ($key == 'refs') {
                            foreach ($value as $ref_data) {
                                $ref_obj = new ref($this->usr);
                                $ref_obj->phr = $this->phrase();
                                $result = $ref_obj->import_obj($ref_data, $do_save);
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
     * @return word_exp a reduced word object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): word_exp
    {
        global $word_types;

        log_debug('word->export_obj');
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
        if ($this->share_id > 0 and $this->share_id <> cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO)) {
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


        log_debug('word->export_obj -> ' . json_encode($result));
        return $result;
    }


    /*
    display functions
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

    // return the html code to display a word
    function display(string $back = ''): string
    {
        if ($back != '') {
            $result = '<a href="/http/view.php?words=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
        } else {
            $result = '<a href="/http/view.php?words=' . $this->id . '">' . $this->name . '</a>';
        }
        return $result;
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
        return btn_add($wrd_add_title, $wrd_add_call);
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
     * return true if the word has the given type
     */
    function is_type($type): bool
    {
        global $word_types;

        log_debug('word->is_type (' . $this->dsp_id() . ' is ' . $type . ')');

        $result = false;
        if ($this->type_id == $word_types->id($type)) {
            $result = true;
            log_debug('word->is_type (' . $this->dsp_id() . ' is ' . $type . ')');
        }
        return $result;
    }

    /**
     * return true if the word has the type "time"
     */
    function is_time(): bool
    {
        return $this->is_type(word_type_list::DBL_TIME);
    }

    /**
     * return true if the word is just to define the default period
     */
    function is_time_jump(): bool
    {
        return $this->is_type(word_type_list::DBL_TIME_JUMP);
    }

    /**
     * return true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(word_type_list::DBL_MEASURE);
    }

    /**
     * return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(word_type_list::DBL_SCALING)
            or $this->is_type(word_type_list::DBL_SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * return true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(word_type_list::DBL_PERCENT);
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
    private function lst(): phrase_list
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
        log_debug('word->parents for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
        $phr_lst = $this->lst();
        $parent_phr_lst = $phr_lst->foaf_parents(cl(db_cl::VERB, verb::IS_A));
        log_debug('word->parents are ' . $parent_phr_lst->name() . ' for ' . $this->dsp_id());
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
        $phr_lst->add($this);
        log_debug('word->is -> ' . $this->dsp_id() . ' is a ' . $phr_lst->name());
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
        log_debug('word->is_mainly -> (' . $this->dsp_id() . ' is a ' . $result->name . ')');
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
        log_debug('word->children for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
        $phr_lst = $this->lst();
        $child_phr_lst = $phr_lst->foaf_children(cl(db_cl::VERB, verb::IS_A));
        log_debug('word->children are ' . $child_phr_lst->name() . ' for ' . $this->dsp_id());
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
        $wrd_lst->add($this);
        return $wrd_lst;
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @return phrase_list all phrases linked with are and contains
     */
    function are_and_contains(): phrase_list
    {
        log_debug('word->are_and_contains for ' . $this->dsp_id());

        // this first time get all related items
        $phr_lst = $this->lst();
        $phr_lst = $phr_lst->are();
        $phr_lst = $phr_lst->contains();
        $added_lst = $phr_lst->diff($this->lst());
        // ... and after that get only for the new
        if (count($added_lst->lst) > 0) {
            $loops = 0;
            log_debug('word->are_and_contains -> added ' . $added_lst->name() . ' to ' . $phr_lst->name());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $next_lst = $next_lst->contains();
                $added_lst = $next_lst->diff($phr_lst);
                if (count($added_lst->lst) > 0) {
                    log_debug('word->are_and_contains -> add ' . $added_lst->name() . ' to ' . $phr_lst->name());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while (count($added_lst->lst) > 0 and $loops < MAX_LOOP);
        }
        log_debug('word->are_and_contains -> ' . $this->dsp_id() . ' are_and_contains ' . $phr_lst->name());
        return $phr_lst;
    }

    /**
     * return the follow word id based on the predefined verb following
     */
    function next(): word_dsp
    {
        log_debug('word->next ' . $this->dsp_id() . ' and user ' . $this->usr->name);

        global $db_con;
        $result = new word_dsp($this->usr);

        $link_id = cl(db_cl::VERB, verb::DBL_FOLLOW);
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
    function prior(): word_dsp
    {
        log_debug('word->prior(' . $this->dsp_id() . ',u' . $this->usr->id . ')');

        global $db_con;
        $result = new word_dsp($this->usr);

        $link_id = cl(db_cl::VERB, verb::DBL_FOLLOW);
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
        log_debug('word->is(' . $this->dsp_id() . ', user ' . $this->usr->id . ')');
        $phr_lst = $this->lst();
        $is_phr_lst = $phr_lst->foaf_parents(cl(db_cl::VERB, verb::IS_PART_OF));

        log_debug('word->is -> (' . $this->dsp_id() . ' is a ' . $is_phr_lst->name() . ')');
        return $is_phr_lst;
    }

    /**
     * returns a list of the link types related to this word e.g. for "Company" the link "are" will be returned, because "ABB" "is a" "Company"
     */
    function link_types($direction): verb_list
    {
        log_debug('word->link_types ' . $this->dsp_id() . ' and user ' . $this->usr->id);

        global $db_con;

        $vrb_lst = new verb_list;
        $vrb_lst->wrd = clone $this;
        $vrb_lst->usr = $this->usr;
        $vrb_lst->direction = $direction;
        $vrb_lst->load_work_links($db_con);
        return $vrb_lst;
    }

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

    /*
    convert functions
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
        log_debug('word->phrase of ' . $this->dsp_id());
        return $phr;
    }

    /*
    save functions
    */

    function not_used(): bool
    {
        log_debug('word->not_used (' . $this->id . ')');

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
     * true if no other user has modified the word
     * assuming that in this case not confirmation from the other users for a word rename is needed
     */
    function not_changed(): bool
    {
        log_debug('word->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_words 
               WHERE word_id = " . $this->id . "
                 AND user_id <> " . $this->owner_id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_words 
               WHERE word_id = " . $this->id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        }
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1_old($sql);
        $change_user_id = $db_row[self::FLD_USER];
        if ($change_user_id > 0) {
            $result = false;
        }
        log_debug('word->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * to be dismissed!
     * if the value has been changed by someone else than the owner the user id is returned
     * but only return the user id if the user has not also excluded it
     */
    function changer(): int
    {
        log_debug('word->changer (' . $this->id . ')');

        global $db_con;
        $user_id = 0;

        $sql = "SELECT user_id 
              FROM user_words 
             WHERE word_id = " . $this->id . "
               AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1_old($sql);
        if ($db_row != null) {
            $user_id = $db_row[self::FLD_USER];
        }
        return $user_id;
    }

    /**
     * true if the user is the owner and no one else has changed the word
     * because if another user has changed the word and the original value is changed, maybe the user word also needs to be updated
     */
    function can_change(): bool
    {
        log_debug('word->can_change (' . $this->id . ',u' . $this->usr->id . ')');
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $wrd_user = $this->changer();
            if ($wrd_user == $this->usr->id or $wrd_user <= 0) {
                $can_change = true;
            }
        }

        log_debug('word->can_change -> (' . zu_dsp_bool($can_change) . ')');
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
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::ALL_FLD_NAMES);
        $db_con->set_where($this->id);
        $sql = $db_con->select();
        $usr_wrd_cfg = $db_con->get1_old($sql);
        if ($usr_wrd_cfg != null) {
            log_debug('word->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
            if ($usr_wrd_cfg[self::FLD_ID] > 0) {
                if ($this->no_usr_fld_used($usr_wrd_cfg)) {
                    // delete the entry in the user sandbox
                    log_debug('word->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
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
        log_debug('word->log_upd ' . $this->dsp_id() . ' for user ' . $this->usr->name);
        $dsp_new = new view_dsp;
        $dsp_new->id = $view_id;
        $dsp_new->usr = $this->usr;
        $dsp_new->load();

        $log = new user_log_named;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
        $log->table = 'words';
        $log->field = self::FLD_VIEW;
        if ($this->view_id > 0) {
            $dsp_old = new view_dsp;
            $dsp_old->id = $this->view_id;
            $dsp_old->usr = $this->usr;
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
            log_debug('word->save_view ' . $view_id . ' for ' . $this->dsp_id() . ' and user ' . $this->usr->id);
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
    private function save_field_plural($db_con, $db_rec, $std_rec): string
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
    function save_fields($db_con, $db_rec, $std_rec): string
    {
        log_debug('word->save_fields');
        $result = $this->save_field_plural($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_view($db_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('word->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
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
     * delete the phrase groups which where this word is used
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all phrase groups where this word is used
        $grp_lst = new phrase_group_list();
        $grp_lst->usr = $this->usr;
        $grp_lst->phr = $this->phrase();
        $grp_lst->load();

        // collect all values related to word triple
        $val_lst = new value_list($this->usr);
        $val_lst->phr = $this->phrase();
        $val_lst->load();

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $result->add($val_lst->del());
        }

        // delete the phrase groups
        $result->add($grp_lst->del());

        return $result;
    }

}
