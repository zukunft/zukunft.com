<?php

/*

    test_base.php - for internal code consistency TESTing the BASE functions and definitions
    -------------

    used functions
    ----

    test_exe_time    - show the execution time for the last test and create a warning if it took too long
    test_dsp - simply to display the function test result
    test_show_db_id  - to get a database id because this may differ from instance to instance


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

// TODO move the names and values for testing to the single objects and check that they cannot be used by an user
// TODO add checks that all id (name or link) changing return the correct error message if the new id already exists
// TODO build a cascading test classes and split the classes to sections less than 1000 lines of code

CONST HOST_TESTING = 'http://localhost';

global $debug;
global $root_path;

//const ROOT_PATH = __DIR__;

if ($root_path == '') {
    $root_path = '../';
}

// set the paths of the program code
$path_test = $root_path . 'src/test/php/';     // the test base path
$path_utils = $path_test . 'utils/';           // for the general tests and test setup
$path_unit = $path_test . 'unit/';             // for unit tests
$path_unit_db = $path_test . 'unit_db/';       // for the unit tests with database real only
$path_unit_dsp = $path_test . 'unit_display/'; // for the unit tests that create HTML code
$path_unit_ui = $path_test . 'unit_ui/';       // for the unit tests that create JSON messages for the frontend
$path_unit_save = $path_test . 'unit_save/';   // for the unit tests that save to database (and cleanup the test data after completion)
$path_it = $path_test . 'integration/';        // for integration tests
$path_dev = $path_test . 'dev/';               // for test still in development

include_once $root_path . 'src/main/php/service/config.php';

// load the other test utility modules (beside this base configuration module)
include_once $path_utils . 'test_system.php';
include_once $path_utils . 'test_db_link.php';
include_once $path_utils . 'test_user.php';
include_once $path_utils . 'test_user_sandbox.php';
include_once $path_utils . 'test_cleanup.php';

// load the unit testing modules
include_once $path_unit . 'test_unit.php';
include_once $path_unit . 'test_lib.php';
include_once $path_unit . 'system.php';
include_once $path_unit . 'user_sandbox.php';
include_once $path_unit . 'word.php';
include_once $path_unit . 'word_list.php';
include_once $path_unit . 'triple.php';
include_once $path_unit . 'triple_list.php';
include_once $path_unit . 'phrase.php';
include_once $path_unit . 'phrase_list.php';
include_once $path_unit . 'phrase_group.php';
include_once $path_unit . 'term.php';
include_once $path_unit . 'term_list.php';
include_once $path_unit . 'value.php';
include_once $path_unit . 'value_phrase_link.php';
include_once $path_unit . 'value_list.php';
include_once $path_unit . 'value_list_display.php';
include_once $path_unit . 'formula.php';
include_once $path_unit . 'formula_link.php';
include_once $path_unit . 'formula_value.php';
include_once $path_unit . 'formula_element.php';
include_once $path_unit . 'figure.php';
include_once $path_unit . 'expression.php';
include_once $path_unit . 'view.php';
include_once $path_unit . 'view_component.php';
include_once $path_unit . 'view_component_link.php';
include_once $path_unit . 'verb.php';
include_once $path_unit . 'ref.php';
include_once $path_unit . 'user_log.php';

// load the testing functions for creating HTML code
include_once $path_unit . 'html.php';
include_once $path_unit . 'user_display.php';
include_once $path_unit . 'word_display.php';
include_once $path_unit . 'word_list_display.php';
include_once $path_unit . 'triple_display.php';
include_once $path_unit . 'phrase_list_display.php';
include_once $path_unit_dsp . 'test_display.php';
include_once $path_unit_dsp . 'type_lists.php';


// load the unit testing modules with database read only
include_once $path_unit_db . 'all.php';
include_once $path_unit_db . 'system.php';
include_once $path_unit_db . 'sql_db.php';
include_once $path_unit_db . 'user.php';
include_once $path_unit_db . 'word.php';
include_once $path_unit_db . 'verb.php';
include_once $path_unit_db . 'phrase_group.php';
include_once $path_unit_db . 'term.php';
include_once $path_unit_db . 'value.php';
include_once $path_unit_db . 'formula.php';
include_once $path_unit_db . 'expression.php';
include_once $path_unit_db . 'view.php';
include_once $path_unit_db . 'ref.php';
include_once $path_unit_db . 'share.php';
include_once $path_unit_db . 'protection.php';


// load the testing functions for creating JSON messages for the frontend code
include_once $path_unit_ui . 'test_formula_ui.php';
include_once $path_unit_ui . 'test_word_ui.php';
include_once $path_unit_ui . 'value_test_ui.php';

// load the testing functions that save data to the database
include_once $path_unit_save . 'test_math.php';
include_once $path_unit_save . 'test_word.php';
include_once $path_unit_save . 'test_word_display.php';
include_once $path_unit_save . 'test_word_list.php';
include_once $path_unit_save . 'test_triple.php';
include_once $path_unit_save . 'phrase_test.php';
include_once $path_unit_save . 'phrase_list_test.php';
include_once $path_unit_save . 'phrase_group_test.php';
include_once $path_unit_save . 'phrase_group_list_test.php';
include_once $path_unit_save . 'ref_test.php';
include_once $path_unit_save . 'test_graph.php';
include_once $path_unit_save . 'test_verb.php';
include_once $path_unit_save . 'test_term.php';
include_once $path_unit_save . 'term_list.php';
include_once $path_unit_save . 'value_test.php';
include_once $path_unit_save . 'test_source.php';
include_once $path_unit_save . 'test_expression.php';
include_once $path_unit_save . 'test_formula.php';
include_once $path_unit_save . 'test_formula_link.php';
include_once $path_unit_save . 'test_formula_trigger.php';
include_once $path_unit_save . 'test_formula_value.php';
include_once $path_unit_save . 'test_formula_element.php';
include_once $path_unit_save . 'test_formula_element_group.php';
include_once $path_unit_save . 'test_batch.php';
include_once $path_unit_save . 'test_view.php';
include_once $path_unit_save . 'test_view_component.php';
include_once $path_unit_save . 'test_view_component_link.php';
include_once $path_unit_save . 'test_value.php';

// load the integration test functions
include_once $path_it . 'test_import.php';
include_once $path_it . 'test_export.php';

// load the test functions still in development
include_once $path_dev . 'test_legacy.php';

// the fixed system user used for testing
const TEST_USER_ID = "2";
const TEST_USER_DESCRIPTION = "standard user view for all users";
const TEST_USER_IP = "66.249.64.95"; // used to check the blocking of an IP address

/*
Setting that should be moved to the system config table
*/

// switch for the email testing
const TEST_EMAIL = FALSE; // if set to true an email will be sent in case of errors and once a day an "everything fine" email is send

// TODO move the test names to the single objects and check for reserved names to avoid conflicts
// the basic test record for doing the pre check
// the word "Company" is assumed to have the ID 1
const TEST_WORD = "Company";

// some test words used for testing
const TW_ABB = "ABB";
const TW_VESTAS = "Vestas";
const TW_SALES = "Sales";
const TW_CHF = "CHF";
const TW_YEAR = "Year";
const TW_2013 = "2013";
const TW_2014 = "2014";
const TW_2017 = "2017";
const TW_MIO = "million";
const TW_CF = "cash flow statement";
const TW_TAX = "Income taxes";

// some test phrases used for testing
const TP_ABB = "ABB (Company)";
const TP_FOLLOW = "2014 is follower of 2013";
const TP_TAXES = "Income taxes is part of cash flow statement";

// some formula parameter used for testing
const TF_SECTOR = "sectorweight";

// some numbers used to test the program
const TV_TEST_SALES_2016 = 1234;
const TV_TEST_SALES_2017 = 2345;
const TV_ABB_SALES_2013 = 45548;
const TV_ABB_SALES_2014 = 46000;
const TV_ABB_PRICE_20200515 = 17.08;
const TV_NESN_SALES_2016 = 89469;
const TV_ABB_SALES_AUTO_2013 = 9915;
const TV_DAN_SALES_USA_2016 = '11%';

const TV_TEST_SALES_INCREASE_2017_FORMATTED = '90.03 %';
const TV_NESN_SALES_2016_FORMATTED = '89\'469';

// some source used to test the program
const TS_IPCC_AR6_SYNTHESIS = 'IPCC AR6 Synthesis Report: Climate Change 2022';
const TS_IPCC_AR6_SYNTHESIS_URL = 'https://www.ipcc.ch/report/sixth-assessment-report-cycle/';
const TS_NESN_2016_NAME = 'Nestlé Financial Statement 2016';


// max time expected for each function execution
const TIMEOUT_LIMIT = 0.03; // time limit for normal functions
const TIMEOUT_LIMIT_PAGE = 0.1;  // time limit for complete webpage
const TIMEOUT_LIMIT_PAGE_SEMI = 0.6;  // time limit for complete webpage
const TIMEOUT_LIMIT_PAGE_LONG = 1.2;  // time limit for complete webpage
const TIMEOUT_LIMIT_DB = 0.2;  // time limit for database modification functions
const TIMEOUT_LIMIT_DB_MULTI = 0.9;  // time limit for many database modifications
const TIMEOUT_LIMIT_LONG = 3;    // time limit for complex functions
const TIMEOUT_LIMIT_IMPORT = 12;    // time limit for complex import tests in seconds


// ---------------------------
// function to support testing
// ---------------------------


/**
 * highlight the first difference between two string
 * @param string|null $from the expected text
 * @param string|null $to the text to compare
 * @return string the first char that differs or an empty string
 */
function str_diff(?string $from, ?string $to): string
{
    $result = '';

    if ($from != null and $to != null) {
        if ($from != $to) {
            $f = str_split($from);
            $t = str_split($to);

            // add message if just one string is shorter
            if (count($f) < count($t)) {
                $result = 'pos ' . count($t) . ' less: ' . substr($to, count($f), count($t) - count($f));
            } elseif (count($t) < count($f)) {
                $result = 'pos ' . count($f) . ' additional: ' . substr($from, count($t), count($f) - count($t));
            }

            $i = 0;
            while ($i < count($f) and $i < count($t) and $result == '') {
                if ($f[$i] != $t[$i]) {
                    $result = 'pos ' . $i . ': ' . $f[$i] . ' (' . ord($f[$i]) . ') != ' . $t[$i] . ' (' . ord($t[$i]) . ')';
                    $result .= ', near ' . substr($from, $i - 10, 20);
                }
                $i++;
            }
        }
    } elseif ($from == null and $to != null) {
        $result = 'less: ' . $to;
    } elseif ($from != null and $to == null) {
        $result = 'additional: ' . $from;
    }


    return $result;
}

/*
 *   testing class - to check the words, values and formulas that should always be in the system
 *   -------------
*/

class test_base
{
    // the url which should be used for testing (maybe later https://test.zukunft.com/)
    const URL = 'https://zukunft.com/';

    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public user $usr1; // the main user for testing
    public user $usr2; // a second testing user e.g. to test the user sandbox

    private float $start_time; // time when all tests have started
    private float $exe_start_time; // time when the single test has started (end the end time of all tests)

    // the counter of the error for the summery
    private int $error_counter;
    private int $timeout_counter;
    private int $total_tests;

    public string $name;
    public string $resource_path;

    private int $seq_nbr;

    function __construct()
    {
        // init the times to be able to detect potential timeouts
        $this->start_time = microtime(true);
        $this->exe_start_time = $this->start_time;

        // reset the error counters
        $this->error_counter = 0;
        $this->timeout_counter = 0;
        $this->total_tests = 0;

        $this->seq_nbr = 0;

        $this->name = '';
        $this->resource_path = '';
    }

    function set_users(): void
    {

        // create the system test user to simulate the user sandbox
        // e.g. a value owned by the first user cannot be adjusted by the second user instead a user specific value is created
        // instead a user specific value is created
        // for testing $usr is the user who has started the test ans $usr1 and $usr2 are the users used for simulation
        $this->usr1 = new user_dsp_old;
        $this->usr1->name = user::NAME_SYSTEM_TEST;
        $this->usr1->load_test_user();

        $this->usr2 = new user_dsp_old;
        $this->usr2->name = user::NAME_SYSTEM_TEST_PARTNER;
        $this->usr2->load_test_user();

    }


    /*
     * object adding, loading and testing functions
     *
     *   create_* to create an object mainly used to shorten the code in unit tests
     *   add_* to create an object and save it in the database to prepare the testing (not used for all classes)
     *   load_* just load the object, but does not create the object
     *   test_* additional creates the object if needed and checks if it has been persistent
     *
     *   * is for the name of the class, so the long name e.g. word not wrd
     *
     */

    /*
     * word test creation
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the name of the word that should be created
     * @param int|null $id to force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the created word object
     */
    function new_word(string $wrd_name, ?int $id = null, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        global $usr;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $wrd = new word($test_usr);
        $wrd->set_id($id);
        $wrd->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $wrd->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
        }
        return $wrd;
    }

    /**
     * load a word from the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word loaded from the database by name
     */
    function load_word(string $wrd_name, ?user $test_usr = null): word
    {
        global $usr;
        if ($test_usr == null) {
            $test_usr = $usr;
        }
        $wrd = new word($test_usr);
        $wrd->set_name($wrd_name);
        $wrd->load_obj_vars();
        return $wrd;
    }

    /**
     * save the just created word object in the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function add_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $wrd->save();
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
            $wrd->save();
        }
        return $wrd;
    }

    /**
     * check if a word object could have been added to the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function test_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->add_word($wrd_name, $wrd_type_code_id, $test_usr);
        $target = $wrd_name;
        $this->dsp('testing->add_word', $target, $wrd->name());
        return $wrd;
    }

    /*
     * triple test creation
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the given name of the triple that should be created
     * @param string $from_name the name of the child word e.g. zurich
     * @param string $verb_code_id the code id of the child to parent relation e.g. is a
     * @param string $to_name the name of the parent word e.g. city
     * @param int|null $id t force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return triple the created triple object
     */
    function new_triple(string  $wrd_name,
                        string  $from_name,
                        string  $verb_code_id,
                        string  $to_name,
                        ?int $id = null,
                        ?string $wrd_type_code_id = null,
                        ?user   $test_usr = null): triple
    {
        global $usr;
        global $verbs;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $trp = new triple($test_usr);
        $trp->set_id($id);
        $trp->from = $this->new_word($from_name)->phrase();
        $trp->verb = $verbs->get_verb($verb_code_id);
        $trp->to = $this->new_word($to_name)->phrase();
        $trp->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $trp->type_id = cl(db_cl::PHRASE_TYPE, $wrd_type_code_id);
        }
        return $trp;
    }

    function load_triple(string $from_name,
                            string $verb_code_id,
                            string $to_name): triple
    {
        global $usr;
        global $verbs;

        $wrd_from = $this->load_word($from_name);
        $wrd_to = $this->load_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new triple($usr);
        if ($from->id() > 0 or $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load_obj_vars();
        }
        return $lnk_test;
    }

    /**
     * check if a word link exists and if not and requested create it
     * $phrase_name should be set if the standard name for the link should not be used
     */
    function test_triple(string $from_name,
                            string $verb_code_id,
                            string $to_name,
                            string $target = '',
                            string $phrase_name = '',
                            bool   $autocreate = true): triple
    {
        global $usr;
        global $verbs;

        $result = '';

        // create the words if needed
        $wrd_from = $this->load_word($from_name);
        if ($wrd_from->id() <= 0 and $autocreate) {
            $wrd_from->set_name($from_name);
            $wrd_from->save();
            $wrd_from->load_obj_vars();
        }
        $wrd_to = $this->load_word($to_name);
        if ($wrd_to->id() <= 0 and $autocreate) {
            $wrd_to->set_name($to_name);
            $wrd_to->save();
            $wrd_to->load_obj_vars();
        }
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new triple($usr);
        if ($from->id() == 0 or $to->id() == 0) {
            log_err("Words " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            // check if the forward link exists
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load_obj_vars();
            if ($lnk_test->id() > 0) {
                // refresh the given name if needed
                if ($phrase_name <> '' and $lnk_test->name_given() <> $phrase_name) {
                    $lnk_test->set_name_given($phrase_name);
                    $lnk_test->save();
                    $lnk_test->load_obj_vars();
                }
                $result = $lnk_test;
            } else {
                // check if the backward link exists
                $lnk_test->from = $to;
                $lnk_test->verb = $vrb;
                $lnk_test->to = $from;
                $lnk_test->set_user($usr);
                $lnk_test->load_obj_vars();
                $result = $lnk_test;
                // create the link if requested
                if ($lnk_test->id() <= 0 and $autocreate) {
                    $lnk_test->from = $from;
                    $lnk_test->verb = $vrb;
                    $lnk_test->to = $to;
                    if ($lnk_test->name_given() <> $phrase_name) {
                        $lnk_test->set_name_given($phrase_name);
                    }
                    $lnk_test->save();
                    $lnk_test->load_obj_vars();
                }
            }
        }
        // fallback setting of target f
        $result_text = '';
        if ($lnk_test->id() > 0) {
            $result_text = $lnk_test->name();
            if ($target == '') {
                $target = $lnk_test->name();
            }
        }
        $this->dsp('word link', $target, $result_text, TIMEOUT_LIMIT_DB);
        return $result;
    }

    function del_triple(string $from_name,
                           string $verb_code_id,
                           string $to_name): bool
    {
        $trp = $this->load_triple($from_name, $verb_code_id, $to_name);
        if ($trp->id() <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }

    /*
     * formula test creation
     */

    /**
     * create a new formula e.g. for unit testing with a given type
     *
     * @param string $frm_name the name of the formula that should be created
     * @param int|null $id to force setting the id for unit testing
     * @param string|null $frm_type_code_id the id of the predefined formula type which the new formula should have
     * @param user|null $test_usr if not null the user for whom the formula should be created to test the user sandbox
     * @return formula the created formula object
     */
    function new_formula(string $frm_name, ?int $id = null, ?string $frm_type_code_id = null, ?user $test_usr = null): formula
    {
        global $usr;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $frm = new formula($test_usr);
        $frm->set_id($id);
        $frm->set_name($frm_name);

        if ($frm_type_code_id != null) {
            $frm->type_id = cl(db_cl::FORMULA_TYPE, $frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        global $usr;
        $frm = new formula_dsp_old($usr);
        $frm->load_by_name($frm_name, formula::class);
        return $frm;
    }

    /**
     * get or create a formula
     */
    function add_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->load_formula($frm_name);
        if ($frm->id() == 0) {
            $frm->set_name($frm_name);
            $frm->usr_text = $frm_text;
            $frm->set_ref_text();
            $frm->save();
        }
        return $frm;
    }

    function test_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->add_formula($frm_name, $frm_text);
        $this->dsp('formula', $frm_name, $frm->name());
        return $frm;
    }

    /*
     * reference test creation
     */

    function load_ref(string $wrd_name, string $type_name): ref
    {
        global $usr;

        $wrd = $this->load_word($wrd_name);
        $phr = $wrd->phrase();

        $ref = new ref($usr);
        $ref->phr = $phr;
        $ref->ref_type = get_ref_type($type_name);
        if ($phr->id() != 0) {
            $ref->load_obj_vars();
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id == 0) {
            $ref->phr = $phr;
            $ref->ref_type = get_ref_type($type_name);
            $ref->external_key = $external_key;
            $ref->save();
        }
        $target = $external_key;
        $this->dsp('ref', $target, $ref->external_key);
        return $ref;
    }

    function load_phrase(string $phr_name): phrase
    {
        global $usr;
        $phr = new phrase($usr);
        $phr->load_by_name($phr_name);
        $phr->load_obj();
        return $phr;
    }

    /**
     * test if a phrase with the given name exists, but does not create it, if it has not yet been created
     * @param string $phr_name name of the phrase to test
     * @return phrase the loaded phrase object
     */
    function test_phrase(string $phr_name): phrase
    {
        $phr = $this->load_phrase($phr_name);
        $this->dsp('phrase', $phr_name, $phr->name());
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list(array $array_of_word_str): word_list
    {
        global $usr;
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names($array_of_word_str);
        return $wrd_lst;
    }

    function test_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = $this->load_word_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $wrd_lst->name();
        $this->dsp(', word list', $target, $result);
        return $wrd_lst;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_phrase_list(array $array_of_word_str): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names($array_of_word_str);
        return $phr_lst;
    }

    function test_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->dsp(', phrase list', $target, $result);
        return $phr_lst;
    }

    /**
     * load a phrase group by the list of phrase names
     * @param array $array_of_phrase_str with the names of the words or triples
     * @return phrase_group
     */
    function load_phrase_group(array $array_of_phrase_str): phrase_group
    {
        return $this->load_phrase_list($array_of_phrase_str)->get_grp();
    }

    /**
     * load a phrase group by the name
     * which can be either the name set by the users
     * or the automatically created name based on the phrases
     * @param string $phrase_group_name
     * @return phrase_group
     */
    function load_phrase_group_by_name(string $phrase_group_name): phrase_group
    {
        global $usr;
        $phr_grp = new phrase_group($usr);
        $phr_grp->grp_name = $phrase_group_name;
        $phr_grp->load();
        return $phr_grp;
    }

    /**
     * add a phrase group to the database
     * @param array $array_of_phrase_str the phrase names
     * @param string $phrase_group_name the name that should be shown to the user
     * @return phrase_group the phrase group object including the database is
     */
    function add_phrase_group(array $array_of_phrase_str, string $phrase_group_name): phrase_group
    {
        global $usr;
        $phr_grp = new phrase_group($usr);
        $phr_grp->phr_lst = $this->load_phrase_list($array_of_phrase_str);
        $phr_grp->grp_name = $phrase_group_name;
        $phr_grp->get();
        return $phr_grp;
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $val = new value($usr);
        $val->load_by_id($id, value::class);
        return $val;
    }

    function load_value(array $array_of_word_str): value
    {
        global $usr;

        // the time separation is done here until there is a phrase series value table that can be used also to time phrases
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $phr_grp = $phr_lst->get_grp();

        $val = new value($usr);
        if ($phr_grp == null) {
            log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->grp = $phr_grp;
            $val->time_phr = $time_phr;
            $val->load_obj_vars();
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        global $usr;
        $val = $this->load_value($array_of_word_str);
        if ($val->id() == 0) {
            // the time separation is done here until there is a phrase series value table that can be used also to time phrases
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $time_phr = $phr_lst->time_useful();
            $phr_lst->ex_time();
            $phr_grp = $phr_lst->get_grp();

            $val = new value($usr);
            if ($phr_grp == null) {
                log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
            } else {
                $val->grp = $phr_grp;
            }
            $val->time_phr = $time_phr;
            $val->set_number($target);
            $val->save();
        }

        return $val;
    }

    function test_value(array $array_of_word_str, float $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->number();
        $this->dsp(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function load_value_by_phr_grp(phrase_group $phr_grp): value
    {
        global $usr;

        $val = new value($usr);
        $val->grp = $phr_grp;
        $val->load_obj_vars();
        return $val;
    }

    function add_value_by_phr_grp(phrase_group $phr_grp, float $target): value
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if ($val->id() == 0) {
            $val->grp = $phr_grp;
            $val->set_number($target);
            $val->save();
        }

        return $val;
    }

    function test_value_by_phr_grp(phrase_group $phr_grp, float $target): value
    {
        $val = $this->add_value_by_phr_grp($phr_grp, $target);
        $result = $val->number();
        $this->dsp(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    /**
     * create a new verb e.g. for unit testing with a given type
     *
     * @param string $vrb_name the name of the verb that should be created
     * @param int|null $id to force setting the id for unit testing
     * @return verb the created verb object
     */
    function new_verb(string $vrb_name, ?int $id = null): verb
    {
        global $usr;
        if ($id == null) {
            $id = $this->next_seq_nbr();
        }

        $vrb = new verb();
        $vrb->set_id($id);
        $vrb->set_name($vrb_name);
        $vrb->set_user($usr);

        return $vrb;
    }

    function load_source(string $src_name): source
    {
        global $usr;
        $src = new source($usr);
        $src->set_name($src_name);
        $src->load_obj_vars();
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id() == 0) {
            $src->set_name($src_name);
            $src->save();
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->dsp('source', $src_name, $src->name());
        return $src;
    }

    /**
     * load a view and if the test user is set for a specific user
     */
    function load_view(string $dsp_name, ?user $test_usr = null): view
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $dsp = new view_dsp_old($test_usr);
        $dsp->load_by_name($dsp_name, view::class);
        return $dsp;
    }

    function add_view(string $dsp_name, ?user $test_usr = null): view
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $dsp = $this->load_view($dsp_name, $test_usr);
        if ($dsp->id() == 0) {
            $dsp->set_user($test_usr);
            $dsp->set_name($dsp_name);
            $dsp->save();
        }
        return $dsp;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        $dsp = $this->add_view($dsp_name, $test_usr);
        $this->dsp('view', $dsp_name, $dsp->name());
        return $dsp;
    }


    function load_view_component(string $cmp_name, ?user $test_usr = null): view_cmp
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = new view_cmp($test_usr);
        $cmp->load_by_name($cmp_name, view_cmp::class);
        return $cmp;
    }

    function add_view_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): view_cmp
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = $this->load_view_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, $type_code_id);
            }
            $cmp->save();
        }
        return $cmp;
    }

    function test_view_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): view_cmp
    {
        $cmp = $this->add_view_component($cmp_name, $type_code_id, $test_usr);
        $this->dsp('view component', $cmp_name, $cmp->name());
        return $cmp;
    }

    function test_view_cmp_lnk(string $dsp_name, string $cmp_name, int $pos): view_cmp_link
    {
        global $usr;
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_view_component($cmp_name);
        $lnk = new view_cmp_link($usr);
        $lnk->fob = $dsp;
        $lnk->tob = $cmp;
        $lnk->order_nbr = $pos;
        $result = $lnk->save();
        $target = '';
        $this->dsp('view component link', $target, $result);
        return $lnk;
    }

    function test_view_cmp_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_view_component($cmp_name);
        if ($dsp != null and $cmp != null) {
            if ($dsp->id() > 0 and $cmp->id() > 0) {
                $result = $cmp->unlink($dsp);
            }
        }
        return $result;
    }

    function test_formula_link(string $formula_name, string $word_name, bool $autocreate = true): string
    {
        global $usr;

        $result = '';

        $frm = new formula($usr);
        $frm->load_by_name($formula_name, formula::class);
        $phr = new word($usr);
        $phr->load_by_name($word_name, word::class);
        if ($frm->id() > 0 and $phr->id() <> 0) {
            $frm_lnk = new formula_link($usr);
            $frm_lnk->fob = $frm;
            $frm_lnk->tob = $phr;
            $frm_lnk->load_obj_vars();
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->fob->name() . ' is linked to ' . $frm_lnk->tob->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                $this->dsp('formula_link', $target, $result);
            } else {
                if ($autocreate) {
                    $frm_lnk->save();
                }
            }
        }
        return $result;
    }


    /*
     * do it
     */


    /**
     * execute the API test using localhost
     *
     * @param testing $t
     * @return void
     */
    function run_api_test(): void
    {

        $this->assert_api_get(word::class);
        $this->assert_api_get(verb::class);
        $this->assert_api_get(triple::class);
        $this->assert_api_get(value::class);
        $this->assert_api_get(formula::class);
        $this->assert_api_get(view::class);
        $this->assert_api_get(view_cmp::class);

        $this->assert_api_get_list(phrase_list::class);
        $this->assert_api_get_list(term_list::class, [1,-1]);
        // $this->assert_rest(new word($usr, word::TN_READ));

    }


    /*
     * Display functions
     */

    /**
     * the HTML code to display the header text
     */
    function header(string $header_text): void
    {
        echo '<br><br><h2>' . $header_text . '</h2><br>';
    }

    /**
     * the HTML code to display the subheader text
     */
    function subheader(string $header_text): void
    {
        echo '<br><h3>' . $header_text . '</h3><br>';
    }

    /**
     * @return string the content of the test resource file
     */
    function file(string $test_resource_path): string
    {
        return file_get_contents(PATH_TEST_FILES . $test_resource_path);
    }

    /**
     * check if the test result is as expected and display the test result to an admin user
     * TODO replace all dsp calls with this but the
     *
     * @param string $msg (unique) description of the test
     * @param string|array $result the actual result
     * @param string|array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert(
        string $msg,
        string|array $result,
        string|array $target,
        float $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     *
     * @param string $msg (unique) description of the test
     * @param array $result the actual result
     * @param array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains(
        string $msg,
        array $result,
        array $target,
        float $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        $result = array_intersect($result, $target);
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     *
     * @param string $msg (unique) description of the test
     * @param array $result the actual result
     * @param array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains_not(
        string $msg,
        array $result,
        array $target,
        float $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        $result = array_diff($target, $result);
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the frontend API object can be created
     * and if the export based recreation of the backend object result to the similar object
     *
     * @param object $usr_obj the object which frontend API functions should be tested
     * @return bool true if the reloaded backend object has no relevant differences
     */
    function assert_api_exp(object $usr_obj): bool
    {
        $original_json = json_decode(json_encode($usr_obj->export_obj(false)), true);
        $recreated_json = '';
        $api_obj = $usr_obj->api_obj();
        if ($api_obj->id() == $usr_obj->id()) {
            $db_obj = $api_obj->db_obj($usr_obj->user(), get_class($api_obj));
            $recreated_json = json_decode(json_encode($db_obj->export_obj(false)), true);
        }
        $result = json_is_similar($original_json, $recreated_json);
        return $this->assert($this->name . 'API check', $result, true);
    }

    /**
     * get the expected api json message of a user sandbox object
     *
     * @param string $class the class name of the object to test
     * @return string with the expected json message
     */
    private function api_json_expected(string $class): string
    {
        return $this->file('api/' . $class . '/' . $class . '.json');
    }

    function assert_api(object $usr_obj): bool
    {
        $api_obj = $usr_obj->api_obj();
        $actual = json_decode(json_encode($api_obj), true);
        $expected = json_decode($this->api_json_expected($usr_obj::class), true);
        return $this->assert($usr_obj::class . ' API object', json_is_similar($actual, $expected), true);
    }

    /**
     * check if the REST GET call returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param int $id the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get(string $class, int $id = 1): bool
    {
        $url = HOST_TESTING . '/api/' . $class;
        $data = array("id" => $id);
        // TODO check why for formula a double call is needed
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        $expected = json_decode($this->api_json_expected($class), true);
        return $this->assert($class . ' API GET', json_is_similar($actual, $expected), true);
    }

    /**
     * check if the REST GET call returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $ids the database ids of the db rows that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_list(string $class, array $ids = [1,2]): bool
    {
        $url = HOST_TESTING . '/api/' . camelize($class);
        $data = array("ids" => implode(",", $ids));
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        $expected = json_decode($this->api_json_expected($class), true);
        return $this->assert($class . ' API GET', json_is_similar($actual, $expected), true);
    }

    /**
     * check if the REST curl calls are possible
     *
     * @param object $usr_obj the object to enrich which REST curl calls should be tested
     * @return bool true if the reloaded backend object has no relevant differences
     */
    function assert_rest(object $usr_obj): bool
    {
        $obj_name = get_class($usr_obj);
        $url_read = 'api/' . $obj_name . '/index.php';
        $original_json = json_decode(json_encode($usr_obj->$usr_obj()), true);
        $recreated_json = '';
        $api_obj = $usr_obj->api_obj();
        if ($api_obj->id == $usr_obj->id) {
            $db_obj = $api_obj->db_obj($usr_obj->usr, get_class($api_obj));
            $recreated_json = json_decode(json_encode($db_obj->export_obj(false)), true);
        }
        $result = json_is_similar($original_json, $recreated_json);
        return $this->assert($this->name . 'REST check', $result, true);
    }

    /**
     * check if an object json file can be recreated by importing the object and recreating the json with the export function
     *
     * @param object $usr_obj the object which json im- and export functions should be tested
     * @param string $json_file_name the resource path name to the json sample file
     * @return bool true if the json has no relevant differences
     */
    function assert_json(object $usr_obj, string $json_file_name): bool
    {
        $json_in = json_decode(file_get_contents(PATH_TEST_FILES . $json_file_name), true);
        $usr_obj->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($usr_obj->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        return $this->assert($this->name . 'import check name', $result, true);
    }

    /**
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $db_type to define the database type if it does not match the class
     * @return bool true if all tests are fine
     */
    function assert_load_sql(sql_db $db_con, object $usr_obj, string $db_type = ''): bool
    {
        if ($db_type == '') {
            $db_type = get_class($usr_obj);
        }

        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_obj_vars($db_con, $db_type);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_obj_vars($db_con, $db_type);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for an id
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_id(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_id($db_con, 1, $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_id($db_con, 1, $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for an id list
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_ids(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_ids($db_con, new trm_ids(array()));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_ids($db_con, new trm_ids(array()));
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the name
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_name(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_name($db_con, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_name($db_con, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the code id
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_code_id(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_code_id($db_con, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_code_id($db_con, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the linked components
     * check the SQL statements for user object load by linked objects for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_link(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_link($db_con, 1,0,3, $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_link($db_con, 1,0,3, $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for a name pattern
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_like(sql_db $db_con, object $usr_obj,): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_like($db_con, '');
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_like($db_con, '');
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements for loading a list of objects in all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $lst_obj the list object e.g. a formula value list
     * @param object $select_obj the named user sandbox or phrase group object used for the selection e.g. a formula
     * @param object|null $select_obj2 a second named object used for selection e.g. a time phrase
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if all tests are fine
     */
    function assert_load_list_sql(sql_db $db_con, object $lst_obj, object $select_obj, object $select_obj2 = null, bool $by_source = false): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst_obj->load_sql($db_con, $select_obj, $select_obj2, $by_source);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst_obj->load_sql($db_con, $select_obj, $select_obj2, $by_source);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the object load SQL statements to get the default object value for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_standard_sql(sql_db $db_con, user_sandbox $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_standard_sql($db_con, get_class($usr_obj));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_standard_sql($db_con, get_class($usr_obj));
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the object loading by id and name
     *
     * @param user_sandbox $usr_obj the user sandbox object e.g. a word
     * @param string $name the name
     * @return bool true if all tests are fine
     */
    function assert_load(db_object $usr_obj, string $name): bool
    {
        // check the loading via id and check the name
        $usr_obj->load_by_id(1, $usr_obj::class);
        $result = $this->assert($usr_obj::class . '->load', $usr_obj->name(), $name);

        // ... and check the loading via name and check the id
        if ($result) {
            $usr_obj->reset();
            $usr_obj->load_by_name($name, $usr_obj::class);
            $result = $this->assert($usr_obj::class . '->load', $usr_obj->id(), 1);
        }
        return $result;
    }

    /**
     * check the not changed SQL statements of a user sandbox object e.g. word, triple, value or formulas
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_not_changed_sql(sql_db $db_con, user_sandbox $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $usr_obj->owner_id = 0;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->not_changed_sql($db_con);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check with owner
        if ($result) {
            $usr_obj->owner_id = 1;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $usr_obj->owner_id = 0;
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        // ... and check with owner
        if ($result) {
            $usr_obj->owner_id = 1;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        return $result;
    }

    /**
     * check the SQL statements to get the user sandbox changes
     * e.g. the value a user has changed of word, triple, value or formulas
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_user_config_sql(sql_db $db_con, user_sandbox $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->usr_cfg_sql($db_con);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->usr_cfg_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        return $result;
    }

    /**
     * test the SQL statement creation for a value
     *
     * @param sql_par $qp the query parameters that should be tested
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return bool true if the test is fine
     */
    function assert_qp(sql_par $qp, string $dialect = ''): bool
    {
        if ($dialect == sql_db::POSTGRES) {
            $file_name_ext = '';
        } elseif ($dialect == sql_db::MYSQL) {
            $file_name_ext = self::FILE_MYSQL;
        } else {
            $file_name_ext = $dialect;
        }
        $file_name = $this->resource_path . $qp->name . $file_name_ext . self::FILE_EXT;
        $expected_sql = $this->file($file_name);
        if ($expected_sql == '') {
            $expected_sql = 'File ' . $file_name . ' with the expected SQL statement is missing.';
        }
        $result = $this->assert_sql(
            $this->name . $qp->name . '_' . $dialect,
            $qp->sql,
            $expected_sql
        );

        // check if the prepared sql name is unique always based on the  PostgreSQL query parameter creation
        if ($dialect == sql_db::POSTGRES) {
            $result = $this->assert_sql_name_unique($qp->name);
        }

        return $result;
    }

    /**
     * test am SQL statement
     *
     * @param string $created the created SQL statement that should be checked
     * @param string $expected the fixed SQL statement that is supposed to be correct
     * @return bool true if the created SQL statement matches the expected SQL statement if the formatting is removed
     */
    function assert_sql(string $name, string $created, string $expected): bool
    {
        $lib = new library();
        return $this->assert($name, $lib->trim_sql($created), $lib->trim_sql($expected));
    }

    /**
     * test am SQL statement
     *
     * @param int $received an integer value that is expected to be greater zero
     * @return bool true if the value is actually greater zero
     */
    function assert_greater_zero(string $name, int $received): bool
    {
        $expected = 0;
        if ($received > 0) {
            $expected = $received;
        }
        return $this->assert($name, $received, $expected);
    }

    /**
     * check if the SQL query name is unique
     * should be called once per query, but not for each SQL dialect
     *
     * @param string $sql_name the SQL query name that is supposed to be unique
     * @return bool true if the name has not been tested before and is therefore expected to be unique
     */
    function assert_sql_name_unique(string $sql_name): bool
    {
        global $sql_names;

        $result = false;
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        return $this->assert('is SQL name ' . $sql_name . ' unique', $result, true);
    }

    /**
     * display the result of one test e.g. if adding a value has been successful
     *
     * @return bool true if the test result is fine
     */
    function dsp(
        string $msg,
        string|array $target,
        string|array $result,
        float $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {

        // init the test result vars
        $test_result = false;
        $txt = '';
        $test_diff = '';
        $new_start_time = microtime(true);
        $since_start = $new_start_time - $this->exe_start_time;

        // do the compare depending on the type
        if (is_array($target) and is_array($result)) {
            sort($target);
            sort($result);
            // in an array each value needs to be the same
            $test_result = true;
            foreach ($target as $key => $value) {
                if ($value != $result[$key]) {
                    $test_result = false;
                }
            }
        } elseif (is_numeric($result) && is_numeric($target)) {
            $result = round($result, 7);
            $target = round($target, 7);
            if ($result == $target) {
                $test_result = true;
            }
        } else {
            if ($result != null) {
                $result = $this->test_remove_color($result);
            }
            if ($result == $target) {
                $test_result = true;
            } else {
                if ($target == '') {
                    log_err('Target is not expected to be empty ' . $result);
                } else {
                    $diff = str_diff($result, $target);
                    if ($diff == '') {
                        log_err('Unexpected diff ' . $diff);
                        $target = $result;
                    }
                }
            }
        }

        // display the result
        if ($test_result) {
            // check if executed in a reasonable time and if the result is fine
            if ($since_start > $exe_max_time) {
                $txt .= '<p style="color:orange">TIMEOUT' . $msg;
                $this->timeout_counter++;
            } else {
                $txt .= '<p style="color:green">OK' . $msg;
                $test_result = true;
            }
        } else {
            $txt .= '<p style="color:red">Error' . $msg;
            $this->error_counter++;
            // TODO: create a ticket
        }

        // explain the check
        if (is_array($target)) {
            if ($test_type == 'contains') {
                $txt .= " should contain \"" . dsp_array($target) . "\"";
            } else {
                $txt .= " should be \"" . dsp_array($target) . "\"";
            }
        } else {
            if ($test_type == 'contains') {
                $txt .= " should contain \"" . $target . "\"";
            } else {
                $txt .= " should be \"" . $target . "\"";
            }
        }
        if ($result == $target) {
            if ($test_type == 'contains') {
                $txt .= " and it contains ";
            } else {
                $txt .= " and it is ";
            }
        } else {
            if ($test_type == 'contains') {
                $txt .= ", but ";
            } else {
                $txt .= ", but it is ";
            }
        }
        if (is_array($result)) {
            if ($result != null) {
                if (is_array($result[0])) {
                    $txt .= "\"";
                    foreach ($result[0] as $result_item) {
                        if ($result_item <> $result[0]) {
                            $txt .= ",";
                        }
                        $txt .= implode(":", $result_item);
                    }
                    $txt .= "\"";
                } else {
                    $txt .= "\"" . dsp_array($result) . "\"";
                }
            }
        } else {
            $txt .= "\"" . $result . "\"";
            if ($test_diff != '') {
                $txt .= ' ' . $test_diff;
            }
        }
        if ($comment <> '') {
            $txt .= ' (' . $comment . ')';
        }

// show the execution time
        $txt .= ', took ';
        $txt .= round($since_start, 4) . ' seconds';

// --- and finally display the test result
        $txt .= '</p>';
        echo $txt;
        echo "\n";
        flush();

        $this->total_tests++;
        $this->exe_start_time = $new_start_time;

        return $test_result;
    }

    /**
     * similar to test_show_result, but the target only needs to be part of the result
     * e.g. "Zurich" is part of the canton word list
     */
    function dsp_contains(
        string $test_text,
        string $target,
        string $result,
        float $exe_max_time = TIMEOUT_LIMIT,
        string $comment = ''): bool
    {
        if (!str_contains($result, $target) and $result != '' and $target != '') {
            $result = $target . ' not found in ' . $result;
        } else {
            $result = $target;
        }
        return $this->dsp($test_text, $target, $result, $exe_max_time, $comment, 'contains');
    }


    function dsp_web_test(string $url_path, string $must_contain, string $msg, bool $is_connected = true): bool
    {
        $msg_net_off = 'Cannot gat the policy, probably not connected to the internet';
        if ($is_connected) {
            $result = file_get_contents(self::URL . $url_path);
            if ($result === false) {
                $this->dsp_warning($msg_net_off);
                $is_connected = false;
            } else {
                $this->dsp_contains($msg, $must_contain, $result, TIMEOUT_LIMIT_PAGE_SEMI);
            }
        }
        return $is_connected;
    }

    /**
     * @param string $msg the message to display to the person who executes the system
     */
    function dsp_warning(string $msg): void
    {
        echo $msg;
        echo '<br>';
        echo '\n';
    }

    /**
     * remove color setting from the result to reduce confusion by misleading colors
     */
    function test_remove_color(string $result): string
    {
        $result = str_replace('<p style="color:red">', '', $result);
        $result = str_replace('<p class="user_specific">', '', $result);
        return str_replace('</p>', '', $result);
    }

    /**
     * display the test results in HTML format
     */
    function dsp_result_html(): void
    {
        echo '<br>';
        echo '<h2>';
        echo $this->total_tests . ' test cases<br>';
        echo $this->timeout_counter . ' timeouts<br>';
        if ($this->error_counter == 1) {
            echo $this->error_counter . ' error<br>';
        } else {
            echo $this->error_counter . ' errors<br>';
        }
        echo "<br>";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com</h2>';
        echo '<br>';
        echo '<br>';
    }

    /**
     * display the test results in pure test format
     */
    function dsp_result(): void
    {

        echo "\n";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com';
        echo "\n";
        echo $this->total_tests . ' test cases';
        echo "\n";
        echo $this->timeout_counter . ' timeouts';
        echo "\n";
        echo $this->error_counter . ' errors';
    }

    /**
     * @return int the next sequence number to simulate database auto increase for unit testing
     */
    private function next_seq_nbr(): int
    {
        $this->seq_nbr++;
        return $this->seq_nbr;
    }

    function api_call($method, $url, $data = false): string
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}


// -----------------------------------------------
// testing functions to create the main time value
// -----------------------------------------------

function zu_test_time_setup(testing $t): string
{
    global $db_con;

    $result = '';
    $this_year = intval(date('Y'));
    $prev_year = '';
    $test_years = intval(cfg_get(config::TEST_YEARS, $db_con));
    if ($test_years == '') {
        log_warning('Configuration of test years is missing', 'test_base->zu_test_time_setup');
    } else {
        $start_year = $this_year - $test_years;
        $end_year = $this_year + $test_years;
        for ($year = $start_year; $year <= $end_year; $year++) {
            $this_year = $year;
            $t->test_word(strval($this_year));
            $wrd_lnk = $t->test_triple(TW_YEAR, verb::IS_A, $this_year);
            $result = $wrd_lnk->name();
            if ($prev_year <> '') {
                $t->test_triple($prev_year, verb::FOLLOW, $this_year);
            }
            $prev_year = $this_year;
        }
    }

    return $result;
}
