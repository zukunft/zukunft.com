<?php

/*

    model/verb/verb.php - predicate object to link two words
    -------------------

    TODO maybe move the reverse to a linked predicate

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

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_LOG_PATH . 'change_log_named.php';
include_once API_VERB_PATH . 'verb.php';
include_once SERVICE_EXPORT_PATH . 'verb_exp.php';
include_once SERVICE_EXPORT_PATH . 'sandbox_exp_named.php';

use api\verb_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use model\export\exp_obj;
use model\export\sandbox_exp_named;
use html\html_base;
use html\html_selector;
use html\verb\verb as verb_dsp;
use verb_exp;

class verb extends db_object
{

    /*
     * code links
     */

    // the unique id of predicates or verbs
    // to link a db row to predefined program code
    const NOT_SET = "not_set";
    const IS = "is";
    const IS_PART_OF = "contains";
    const IS_WITH = "with";
    const FOLLOW = "follow";
    const CAN_CONTAIN = "can_contain";
    const CAN_CONTAIN_NAME = "differentiator";
    const CAN_CONTAIN_NAME_REVERSE = "of";
    const CAN_BE = "can_be";
    const CAN_USE = "can_use";
    const SELECTOR = "selector"; // the from_phrase of a selector can be used more than once so the description of the to_phrase should be shown to the user

    // directional forms of verbs (maybe move to verb_api or test if only used for testing)
    const FOLLOWED_BY = "is followed by";

    // search directions to get related words (phrases)
    const DIRECTION_NO = '';
    const DIRECTION_DOWN = 'down';    // or forward  to get a list of 'to' phrases
    const DIRECTION_UP = 'up';        // or backward to get a list of 'from' phrases based on a given to phrase


    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'verb_id';
    const FLD_NAME = 'verb_name';
    const FLD_PLURAL = 'name_plural';
    const FLD_REVERSE = 'name_reverse';
    const FLD_PLURAL_REVERSE = 'name_plural_reverse';
    const FLD_FORMULA = 'formula_name';
    const FLD_WORDS = 'words';

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_PLURAL,
        self::FLD_REVERSE,
        self::FLD_PLURAL_REVERSE,
        self::FLD_FORMULA,
        self::FLD_WORDS
    );


    /*
     * object vars
     */

    private ?user $usr = null;         // used only to allow adding the code id on import
    //                                    but there should not be any user specific verbs
    //                                    otherwise if id is 0 (not NULL) the standard word link type,
    //                                    otherwise the user specific verb
    public ?string $code_id = '';      // the main id to detect verbs that have a special behavior
    private ?string $name = '';        // the verb name to build the "sentence" for the user, which cannot be empty
    public ?string $plural = '';       // name used if more than one word is shown
    //                                    e.g. instead of "ABB" "is a" "company"
    //                                         use "ABB", Nestlé" "are" "companies"
    public ?string $reverse = '';      // name used if displayed the other way round
    //                                    e.g. for "Country" "has a" "Human Development Index"
    //                                         the reverse would be "Human Development Index" "is used for" "Country"
    public ?string $rev_plural = '';   // the reverse name for many words
    public ?string $frm_name = '';     // short name of the verb for the use in formulas
    //                                    because there both sides are combined
    public ?string $description = '';  // for the mouse over explain
    public int $usage = 0;             // how often this current used has used the verb
    //                                    (until now just the usage of all users)


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', string $code_id = '')
    {
        parent::__construct();
        if ($id > 0) {
            $this->set_id($id);
        }
        if ($name != '') {
            $this->set_name($name);
        }
        if ($code_id != '') {
            $this->code_id = $code_id;
        }
    }

    function reset(): void
    {
        $this->id = 0;
        $this->set_user(null);
        $this->code_id = null;
        $this->name = null;
        $this->plural = null;
        $this->reverse = null;
        $this->rev_plural = null;
        $this->frm_name = null;
        $this->description = null;
        $this->usage = 0;
    }

    /**
     * set the class vars based on a database record
     *
     * @param array|null $db_row is an array with the database values
     * @param string $id_fld the name of the id field
     * @param string $name_fld the name of the name field
     * @return bool true if the verb is loaded and valid
     */
    function row_mapper_verb(
        ?array $db_row,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME): bool
    {
        $result = parent::row_mapper($db_row, $id_fld);
        if ($result) {
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql_db::FLD_CODE_ID];
            }
            $this->set_name($db_row[$name_fld]);
            if (array_key_exists(self::FLD_PLURAL, $db_row)) {
                $this->plural = $db_row[self::FLD_PLURAL];
            }
            if (array_key_exists(self::FLD_REVERSE, $db_row)) {
                $this->reverse = $db_row[self::FLD_REVERSE];
            }
            if (array_key_exists(self::FLD_PLURAL_REVERSE, $db_row)) {
                $this->rev_plural = $db_row[self::FLD_PLURAL_REVERSE];
            }
            if (array_key_exists(self::FLD_FORMULA, $db_row)) {
                $this->frm_name = $db_row[self::FLD_FORMULA];
            }
            if (array_key_exists(sandbox_named::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sandbox_named::FLD_DESCRIPTION];
            }
            if (array_key_exists(self::FLD_WORDS, $db_row)) {
                if ($db_row[self::FLD_WORDS] == null) {
                    $this->usage = 0;
                } else {
                    $this->usage = $db_row[self::FLD_WORDS];
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the verb
     * @param string $name mainly for test creation the name of the verb
     */
    function set(int $id = 0, string $name = ''): void
    {
        $this->set_id($id);
        $this->set_name($name);
    }

    /**
     * @param int $id the database id of the verb
     */
    function set_id(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string|null $name the unique name of the verb
     */
    function set_name(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the user of the verb
     *
     * @param user|null $usr the person who wants to access the verb
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * set the value to rank the verbs by usage
     *
     * @param int $usage a higher value moves the verb to the top of the selection list
     * @return void
     */
    function set_usage(int $usage): void
    {
        //$this->values = $usage;
    }

    /**
     * @return int the database id which is not 0 if the object has been saved
     */
    function id(): int
    {
        return $this->id;
    }

    /**
     * @return string a unique name for the verb that is also used in the code
     */
    function code_id(): string
    {
        if ($this->code_id == null) {
            return '';
        } else {
            return $this->code_id;
        }
    }

    /**
     * @return string the description of the verb
     */
    function comment(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    /**
     * @return user|null the person who wants to see this verb
     */
    function user(): ?user
    {
        return $this->usr;
    }

    /**
     * @return int a higher number indicates a higher usage
     */
    function usage(): int
    {
        return 0;
    }


    /*
     * cast
     */

    /**
     * @return verb_api the verb frontend api object
     */
    function api_obj(): verb_api
    {
        $api_obj = new verb_api();
        $api_obj->set_id($this->id());
        $api_obj->set_name($this->name());
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return verb_dsp the verb frontend api object
     */
    function dsp_obj(): verb_dsp
    {
        $dsp_obj = new verb_dsp();
        $dsp_obj->set_id($this->id());
        $dsp_obj->set_name($this->name());
        return $dsp_obj;
    }

    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a verb from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_type(self::class);
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a verb by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * create an SQL statement to retrieve a verb by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, self::class);
        $sc->add_where(self::FLD_NAME, $name, sql_par_type::TEXT_OR);
        $sc->add_where(self::FLD_FORMULA, $name, sql_par_type::TEXT_OR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a verb by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id): sql_par
    {
        $qp = $this->load_sql($sc, 'code_id', self::class);
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a verb from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_verb($db_row);
        return $this->id();
    }

    /**
     * load a verb by database id
     * @param int $id the id of the verb
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * load a verb by the verb name
     * @param string $name the name of the verb
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * load a verb by the verb name
     * @param string $code_id the code id of the verb
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_code_id(string $code_id): int
    {
        global $db_con;

        log_debug($code_id);
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id);
        return $this->load($qp);
    }


    /*
     * im- and export
     */

    /**
     * add a verb in the database from an imported json object of external database from
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        global $verbs;

        log_debug();
        $result = parent::import_db_obj($this, $test_obj);

        // reset all parameters of this verb object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->set_user($usr);
        foreach ($json_obj as $key => $value) {
            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == exp_obj::FLD_CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->code_id = $value;
                    }
                }
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == self::FLD_REVERSE) {
                $this->reverse = $value;
            }
            if ($key == self::FLD_PLURAL) {
                $this->plural = $value;
            }
            if ($key == self::FLD_FORMULA) {
                $this->frm_name = $value;
            }
            if ($key == self::FLD_PLURAL_REVERSE) {
                $this->rev_plural = $value;
            }
        }

        // save the verb in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $result->add_message($this->save());
            }
        }

        return $result;
    }

    /**
     * create a verb object for the export
     * @return verb_exp a reduced word object that can be used to create a JSON message
     */
    function export_obj(): verb_exp
    {
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new verb_exp();

        if ($this->name <> '') {
            $result->name = $this->name();
        }
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->plural <> '') {
            $result->name_plural = $this->plural;
        }
        if ($this->reverse <> '') {
            $result->name_reverse = $this->reverse;
        }
        if ($this->rev_plural <> '') {
            $result->name_plural_reverse = $this->rev_plural;
        }

        // TODO add the protection type
        /*
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }
        */

        log_debug(json_encode($result));
        return $result;
    }


    /*
    display functions
    */

    // display the unique id fields (used also for debugging)
    function dsp_id(): string
    {
        $result = '';

        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if ($this->user() != null) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    function name(): string
    {
        return $this->name;
    }

    // create the HTML code to display the formula name with the HTML link
    function display(?string $back = ''): string
    {
        return '<a href="/http/verb_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    // returns the html code to select a verb link type
    // database link must be open
    function dsp_selector($side, $form, $class, $back): string
    {
        global $verbs;

        log_debug('for verb id ' . $this->id);
        $result = '';

        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->label = "Verb:";
        $sel->bs_class = $class;
        $db_lst = $verbs->selector_list($side);
        $sel_lst = array();
        foreach ($db_lst as $db_entry) {
            $sel_lst[$db_entry['id']] = $db_entry['name'];
        }
        $sel->lst = $sel_lst;
        $sel->selected = $this->id;
        $sel->dummy_text = '';
        $result .= $sel->display_old();

        log_debug('admin id ' . $this->id);
        if ($this->user() != null) {
            if ($this->user()->is_admin()) {
                // admin users should always have the possibility to create a new verb / link type
                $result .= \html\btn_add('add new verb', '/http/verb_add.php?back=' . $back);
            }
        }

        log_debug('done verb id ' . $this->id);
        return $result;
    }

    // show the html form to add or edit a new verb
    function dsp_edit(string $back = ''): string
    {
        $html = new html_base();
        log_debug('verb->dsp_edit ' . $this->dsp_id());
        $result = '';

        if ($this->id <= 0) {
            $script = "verb_add";
            $result .= $html->dsp_text_h2('Add verb (word link type)');
        } else {
            $script = "verb_edit";
            $result .= $html->dsp_text_h2('Change verb (word link type)');
        }
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_tbl_start_half();
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb name:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="name" value="' . $this->name . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb plural:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="plural" value="' . $this->plural . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="reverse" value="' . $this->reverse . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      plural_reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="text" name="plural_reverse" value="' . $this->rev_plural . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <input type="hidden" name="back" value="' . $back . '">';
        $result .= '  <input type="hidden" name="confirm" value="1">';
        $result .= $html->dsp_tbl_end();
        $result .= $html->dsp_form_end('', $back);

        log_debug('verb->dsp_edit ... done');
        return $result;
    }

    /*
     * convert functions
     */

    /**
     * get the term corresponding to this verb name
     * so in this case, if a word or formula with the same name already exists, get it
     */
    private function get_term(): term
    {
        $trm = new term($this->usr);
        $trm->load_by_name($this->name);
        return $trm;
    }

    /**
     * @returns term the formula object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->usr);
        $trm->set_id_from_obj($this->id, self::class);
        $trm->set_name($this->name);
        $trm->set_obj($this);
        return $trm;
    }

    /*
    save functions
    */

    // TODO to review: additional check the database foreign keys
    function not_used_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(verb::class);

        $qp->name .= 'usage';
        $db_con->set_type(word::class);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_where_std($this->id);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * @returns bool true if no one has used this verb
     */
    private function not_used(): bool
    {
        log_debug('verb->not_used (' . $this->id . ')');

        global $db_con;
        $result = true;

        // to review: additional check the database foreign keys
        $qp = $this->not_used_sql($db_con);
        $db_row = $db_con->get1($qp);
        $used_by_words = $db_row[self::FLD_WORDS];
        if ($used_by_words > 0) {
            $result = false;
        }

        return $result;
    }

    // true if no other user has modified the verb
    private function not_changed(): bool
    {
        log_debug('verb->not_changed (' . $this->id . ') by someone else than the owner (' . $this->user()->id() . ')');

        global $db_con;
        $result = true;

        /*
        $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_verbs
                 WHERE verb_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        }
        */

        log_debug('verb->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if no one else has used the verb
    function can_change(): bool
    {
        log_debug('verb->can_change ' . $this->id);
        $can_change = false;
        if ($this->usage == 0) {
            $can_change = true;
        }

        log_debug(zu_dsp_bool($can_change));
        return $can_change;
    }

    // set the log entry parameter for a new verb
    private function log_add(): change_log_named
    {
        log_debug('verb->log_add ' . $this->dsp_id());
        $log = new change_log_named($this->usr);
        $log->action = change_log_action::ADD;
        $log->set_table(change_log_table::VERB);
        $log->set_field(self::FLD_NAME);
        $log->old_value = '';
        $log->new_value = $this->name;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one verb field
    private function log_upd(): change_log_named
    {
        log_debug('verb->log_upd ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $log = new change_log_named($this->usr);
        $log->action = change_log_action::UPDATE;
        $log->set_table(change_log_table::VERB);

        return $log;
    }

    // set the log entry parameter to delete a verb
    private function log_del(): change_log_named
    {
        log_debug('verb->log_del ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $log = new change_log_named($this->usr);
        $log->action = change_log_action::DELETE;
        $log->set_table(change_log_table::VERB);
        $log->set_field(self::FLD_NAME);
        $log->old_value = $this->name;
        $log->new_value = '';
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // actually update a formula field in the main database record or the user sandbox
    private function save_field_do(sql_db $db_con, $log): string
    {
        $result = '';
        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        if ($log->add()) {
            if ($this->can_change()) {
                $db_con->set_type(sql_db::TBL_VERB);
                if (!$db_con->update($this->id, $log->field(), $new_value)) {
                    $result .= 'updating ' . $log->field() . ' to ' . $new_value . ' for verb ' . $this->dsp_id() . ' failed';
                }

            } else {
                // TODO: create a new verb and request to delete the old
                log_warning('verb->save_field_do creating of a new verb not yet coded');
            }
        }
        return $result;
    }

    private function save_field_code_id(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->name <> $this->code_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->code_id;
            $log->new_value = $this->code_id;
            $log->std_value = $db_rec->code_id;
            $log->row_id = $this->id;
            $log->set_field(sql_db::FLD_CODE_ID);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }


    // set the update parameters for the verb name
    private function save_field_name(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->name <> $this->name) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $db_rec->name;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_NAME);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb plural
    private function save_field_plural(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->plural <> $this->plural) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->plural;
            $log->new_value = $this->plural;
            $log->std_value = $db_rec->plural;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_PLURAL);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb reverse
    private function save_field_reverse(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->reverse <> $this->reverse) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->reverse;
            $log->new_value = $this->reverse;
            $log->std_value = $db_rec->reverse;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_REVERSE);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb rev_plural
    private function save_field_rev_plural(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->rev_plural <> $this->rev_plural) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->rev_plural;
            $log->new_value = $this->rev_plural;
            $log->std_value = $db_rec->rev_plural;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_PLURAL_REVERSE);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb description
    private function save_field_description(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $db_rec->description;
            $log->row_id = $this->id;
            $log->set_field(sandbox_named::FLD_DESCRIPTION);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the verb description
    private function save_field_formula_name(sql_db $db_con, $db_rec): string
    {
        $result = '';
        if ($db_rec->description <> $this->frm_name) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->frm_name;
            $log->new_value = $this->frm_name;
            $log->std_value = $db_rec->frm_name;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_FORMULA);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated verb fields excluding the name, because already done when adding a verb
    private function save_fields(sql_db $db_con, $db_rec): string
    {
        $result = $this->save_field_code_id($db_con, $db_rec);
        $result .= $this->save_field_plural($db_con, $db_rec);
        $result .= $this->save_field_reverse($db_con, $db_rec);
        $result .= $this->save_field_rev_plural($db_con, $db_rec);
        $result .= $this->save_field_description($db_con, $db_rec);
        $result .= $this->save_field_formula_name($db_con, $db_rec);
        log_debug('verb->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    // check if the id parameters are supposed to be changed
    private function save_id_if_updated(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        /*
            TODO:
            if ($db_rec->name <> $this->name) {
              // check if target link already exists
              zu_debug('verb->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")');
              $db_chk = clone $this;
              $db_chk->id = 0; // to force the load by the id fields
              $db_chk->load_standard();
              if ($db_chk->id > 0) {
                if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
                  // ... if yes request to delete or exclude the record with the id parameters before the change
                  $to_del = clone $db_rec;
                  $result .= $to_del->del();
                  // .. and use it for the update
                  $this->id = $db_chk->id;
                  $this->owner_id = $db_chk->owner_id;
                  // force the include again
                  $this->excluded = null;
                  $db_rec->excluded = '1';
                  $this->save_field_excluded ($db_con, $db_rec, $std_rec);
                  zu_debug('verb->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id());
                } else {
                  $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
                }
              } else {
                if ($this->can_change() AND $this->not_used()) {
                  // in this case change is allowed and done
                  zu_debug('verb->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")');
                  //$this->load_objects();
                  $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                } else {
                  // if the target link has not yet been created
                  // ... request to delete the old
                  $to_del = clone $db_rec;
                  $result .= $to_del->del();
                  // .. and create a deletion request for all users ???

                  // ... and create a new display component link
                  $this->id = 0;
                  $this->owner_id = $this->user()->id();
                  $result .= $this->add($db_con);
                  zu_debug('verb->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")');
                }
              }
            }
        */
        log_debug('verb->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * create a new verb
     */
    private function add(sql_db $db_con): string
    {
        log_debug('verb->add the verb ' . $this->dsp_id());
        $result = '';

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the new verb
            $db_con->set_type(sql_db::TBL_VERB);
            $this->id = $db_con->insert(self::FLD_NAME, $this->name);
            if ($this->id > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    $result .= 'Updating the reference in the log failed';
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new verb;
                    $db_rec->name = $this->name;
                    $db_rec->usr = $this->usr;
                    // save the verb fields
                    $result .= $this->save_fields($db_con, $db_rec);
                }

            } else {
                $result .= "Adding verb " . $this->name . " failed.";
            }
        }

        return $result;
    }

    /**
     * check if this object uses any preserved names and if return a message to the user
     *
     * @return string
     */
    protected function check_preserved(): string
    {
        global $usr;

        $result = '';
        if (!$usr->is_system()) {
            if (in_array($this->name, verb_api::RESERVED_WORDS)) {
                // the admin user needs to add the read test word during initial load
                if (!$usr->is_admin()) {
                    $result = '"' . $this->name() . '" is a reserved name for system testing. Please use another name';
                }
            }
        }
        return $result;
    }

    /**
     * TODO return a user message object, so that messages to the user like "use another name" does not case a error log entry
     * add or update a verb in the database (or create a user verb if the program settings allow this)
     *
     */
    function save(): string
    {
        log_debug($this->dsp_id());

        global $db_con;
        $result = '';

        // check the preserved names
        $result = $this->check_preserved();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_type(sql_db::TBL_VERB);

        // check if a new verb is supposed to be added
        if ($this->id <= 0) {
            // check if a word, triple or formula with the same name is already in the database
            $trm = $this->get_term();
            if ($trm->id_obj() > 0 and $trm->type() <> verb::class) {
                $result .= $trm->id_used_msg($this);
            } else {
                $this->id = $trm->id_obj();
                log_debug('verb->save adding verb name ' . $this->dsp_id() . ' is OK');
            }
        }

        // create a new verb or update an existing
        if ($result == '') {
            if ($this->id <= 0) {
                $result .= $this->add($db_con);
            } else {
                log_debug('update "' . $this->id . '"');
                // read the database values to be able to check if something has been changed; done first,
                // because it needs to be done for user and general formulas
                $db_rec = new verb;
                $db_rec->usr = $this->usr;
                $db_rec->load_by_id($this->id);
                log_debug("database verb loaded (" . $db_rec->name . ")");

                // if the name has changed, check if verb, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
                if ($db_rec->name <> $this->name) {
                    // check if a verb, formula or verb with the same name is already in the database
                    $trm = $this->get_term();
                    if ($trm->id_obj() > 0 and $trm->type() <> verb::class) {
                        $result .= $trm->id_used_msg($this);
                    } else {
                        if ($this->can_change()) {
                            $result .= $this->save_field_name($db_con, $db_rec);
                        } else {
                            // TODO: create a new verb and request to delete the old
                            log_err('Creating a new verb is not yet possible');
                        }
                    }
                }

                if ($db_rec->code_id <> $this->code_id) {
                    $result .= $this->save_field_code_id($db_con, $db_rec);
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($result == '') {
                    $result = $this->save_fields($db_con, $db_rec);
                }
            }
        }

        // TODO log internal errors as errors but user warnings as info
        if ($result != '') {
            log_info($result);
        }

        return $result;
    }

    /**
     * exclude or delete a verb
     * @returns string the message that should be shown to the user if something went wrong or an empty string if everything is fine
     */
    function del(): string
    {
        log_debug('verb->del');

        global $db_con;
        $result = '';

        // reload only if needed
        if ($this->name == '') {
            if ($this->id > 0) {
                $this->load_by_id($this->id);
            } else {
                log_err('Cannot delete verb, because neither the id or name is given');
            }
        } else {
            if ($this->id == 0) {
                $this->load_by_name($this->name);
            }
        }

        if ($this->id > 0) {
            log_debug('verb->del ' . $this->dsp_id());
            if ($this->can_change()) {
                $log = $this->log_del();
                if ($log->id() > 0) {
                    $db_con->usr_id = $this->user()->id();
                    $db_con->set_type(sql_db::TBL_VERB);
                    $result = $db_con->delete(self::FLD_ID, $this->id);
                }
            } else {
                // TODO: create a new verb and request to delete the old
                log_err('Creating a new verb is not yet possible');
            }
        }

        return $result;
    }

}
