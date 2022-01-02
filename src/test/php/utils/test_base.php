<?php

/*

    test_base.php - for internal code consistency TESTing the BASE functions and definitions
    -------------

    used functions
    ----

    test_exe_time    - show the execution time for the last test and create a warning if it took too long
    test_dsp - simply to display the function test result
    test_show_db_id  - to get a database id because this may differ from instance to instance


    zukunft.com - calc with words

    copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// TODO move the names and values for testing to the single objects and check that they cannot be used by an user
// TODO add checks that all id (name or link) changing return the correct error message if the new id already exists

global $debug;
global $root_path;

//const ROOT_PATH = __DIR__;

if ($root_path == '') {
    $root_path = '../';
}

// set the paths of the program code
$path_test = $root_path . 'src/test/php/utils/';            // for the general tests and test setup
$path_unit = $root_path . 'src/test/php/unit/';             // for unit tests
$path_unit_db = $root_path . 'src/test/php/unit_db/';       // for the unit tests with database real only
$path_unit_dsp = $root_path . 'src/test/php/unit_display/'; // for the unit tests that create HTML code
$path_unit_ui = $root_path . 'src/test/php/unit_ui/';       // for the unit tests that create JSON messages for the frontend
$path_unit_save = $root_path . 'src/test/php/unit_save/';   // for the unit tests that save to database (and cleanup the test data after completion)
$path_it = $root_path . 'src/test/php/integration/';        // for integration tests
$path_dev = $root_path . 'src/test/php/dev/';               // for test still in development

include_once $root_path . 'src/main/php/service/config.php';

// load the other test utility modules (beside this base configuration module)
include_once $path_test . 'test_system.php';
include_once $path_test . 'test_db_link.php';
include_once $path_test . 'test_user.php';
include_once $path_test . 'test_user_sandbox.php';

// load the unit testing modules
include_once $path_unit . 'all.php';
include_once $path_unit . 'test_lib.php';
include_once $path_unit . 'system.php';
include_once $path_unit . 'user_sandbox.php';
include_once $path_unit . 'word.php';
include_once $path_unit . 'word_list.php';
include_once $path_unit . 'word_link.php';
include_once $path_unit . 'word_link_list.php';
include_once $path_unit . 'phrase_list.php';
include_once $path_unit . 'phrase_group.php';
include_once $path_unit . 'value.php';
include_once $path_unit . 'value_phrase_link.php';
include_once $path_unit . 'value_list.php';
include_once $path_unit . 'formula.php';
include_once $path_unit . 'formula_link.php';
include_once $path_unit . 'formula_value.php';
include_once $path_unit . 'figure.php';
include_once $path_unit . 'view.php';
include_once $path_unit . 'view_component.php';
include_once $path_unit . 'view_component_link.php';
include_once $path_unit . 'ref.php';
include_once $path_unit . 'user_log.php';

// load the unit testing modules with database real only
include_once $path_unit_db . 'all.php';
include_once $path_unit_db . 'system.php';
include_once $path_unit_db . 'sql_db.php';
include_once $path_unit_db . 'user.php';
include_once $path_unit_db . 'word.php';
include_once $path_unit_db . 'verb.php';
include_once $path_unit_db . 'formula.php';
include_once $path_unit_db . 'view.php';
include_once $path_unit_db . 'ref.php';
include_once $path_unit_db . 'share.php';
include_once $path_unit_db . 'protection.php';

// load the testing functions for creating HTML code
include_once $path_unit_dsp . 'test_display.php';

// load the testing functions for creating JSON messages for the frontend code
include_once $path_unit_ui . 'test_formula_ui.php';
include_once $path_unit_ui . 'test_word_ui.php';
include_once $path_unit_ui . 'value_test_ui.php';

// load the testing functions that save data to the database
include_once $path_unit_save . 'test_math.php';
include_once $path_unit_save . 'test_word.php';
include_once $path_unit_save . 'test_word_display.php';
include_once $path_unit_save . 'test_word_list.php';
include_once $path_unit_save . 'test_word_link.php';
include_once $path_unit_save . 'phrase_test.php';
include_once $path_unit_save . 'phrase_list_test.php';
include_once $path_unit_save . 'phrase_group_test.php';
include_once $path_unit_save . 'phrase_group_list_test.php';
include_once $path_unit_save . 'ref_test.php';
include_once $path_unit_save . 'test_graph.php';
include_once $path_unit_save . 'test_verb.php';
include_once $path_unit_save . 'test_term.php';
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
include_once $path_unit_save . 'test_cleanup.php';

// load the integration test functions
include_once $path_it . 'test_import.php';
include_once $path_it . 'test_export.php';

// libraries that can be dismissed, but still used to compare the result with the result of the legacy function
include_once $root_path . 'src/main/php/service/test/zu_lib_word_dsp.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_sql.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_link.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_sql_naming.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_value.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_word.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_word_db.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_calc.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_value_dsp.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_user.php';
include_once $root_path . 'src/main/php/service/test/zu_lib_html.php';

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

    // the fixed system user used for testing
    const USER_NAME = "zukunft.com system test";
    const USER_PARTNER_NAME = "zukunft.com system test partner";

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

    function __construct()
    {
        // init the times to be able to detect potential timeouts
        $this->start_time = microtime(true);
        $this->exe_start_time = $this->start_time;

        // reset the error counters
        $this->error_counter = 0;
        $this->timeout_counter = 0;
        $this->total_tests = 0;

        $this->name = '';
        $this->resource_path = '';
    }

    function set_users()
    {

        // create the system test user to simulate the user sandbox
        // e.g. a value owned by the first user cannot be adjusted by the second user instead a user specific value is created
        // instead a user specific value is created
        // for testing $usr is the user who has started the test ans $usr1 and $usr2 are the users used for simulation
        $this->usr1 = new user_dsp;
        $this->usr1->name = self::USER_NAME;
        $this->usr1->load_test_user();

        $this->usr2 = new user_dsp;
        $this->usr2->name = self::USER_PARTNER_NAME;
        $this->usr2->load_test_user();

    }


    /*
     * object adding, loading and testing functions
     *
     *   add_* to create an object and save it in the database to prepare the testing (not used for all classes)
     *   load_* just load the object, but does not create the object
     *   test_* additional creates the object if needed and checks if it has been persistent
     *
     *   * is for the name of the class, so the long name e.g. word not wrd
     *
     */

    function load_word(string $wrd_name, ?user $test_usr = null): word
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $wrd = new word($test_usr);
        $wrd->name = $wrd_name;
        $wrd->load();
        return $wrd;
    }

    function add_word(string $wrd_name, string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id == 0) {
            $wrd->name = $wrd_name;
            $wrd->save();
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = cl(db_cl::WORD_TYPE, $wrd_type_code_id);
            $wrd->save();
        }
        return $wrd;
    }

    function test_word(string $wrd_name, $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->add_word($wrd_name, $wrd_type_code_id, $test_usr);
        $target = $wrd_name;
        $this->dsp('testing->add_word', $target, $wrd->name);
        return $wrd;
    }

    function load_ref(string $wrd_name, string $type_name): ref
    {
        global $usr;

        $wrd = $this->load_word($wrd_name);
        $phr = $wrd->phrase();

        $ref = new ref($usr);
        $ref->phr = $phr;
        $ref->ref_type = get_ref_type($type_name);
        if ($phr->id != 0) {
            $ref->load();
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name, $type_name);
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

    function load_formula(string $frm_name): formula
    {
        global $usr;
        $frm = new formula_dsp($usr);
        $frm->name = $frm_name;
        $frm->load();
        return $frm;
    }

    /**
     * get or create a formula
     */
    function add_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->load_formula($frm_name);
        if ($frm->id == 0) {
            $frm->name = $frm_name;
            $frm->usr_text = $frm_text;
            $frm->set_ref_text();
            $frm->save();
        }
        return $frm;
    }

    function test_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->add_formula($frm_name, $frm_text);
        $this->dsp('formula', $frm_name, $frm->name);
        return $frm;
    }

    function load_phrase(string $phr_name): phrase
    {
        global $usr;
        $phr = new phrase($usr);
        $phr->name = $phr_name;
        $phr->load();
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
        $this->dsp('phrase', $phr_name, $phr->name);
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list($array_of_word_str): word_list
    {
        global $usr;
        $wrd_lst = new word_list($usr);
        foreach ($array_of_word_str as $word_str) {
            $wrd_lst->add_name($word_str);
        }
        $wrd_lst->load();
        return $wrd_lst;
    }

    function test_word_list($array_of_word_str): word_list
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
    function load_phrase_list($array_of_word_str): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names($array_of_word_str);
        return $phr_lst;
    }

    function test_phrase_list($array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->dsp(', phrase list', $target, $result);
        return $phr_lst;
    }

    function load_phrase_group($array_of_phrase_str): phrase_group
    {
        return $this->load_phrase_list($array_of_phrase_str)->get_grp();
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $val = new value($usr);
        $val->id = $id;
        $val->load();
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
            $val->load();
        }
        return $val;
    }

    function add_value($array_of_word_str, $target): value
    {
        global $usr;
        $val = $this->load_value($array_of_word_str);
        if ($val->id == 0) {
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
            $val->number = $target;
            $val->save();
        }

        return $val;
    }

    function test_value($array_of_word_str, $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->number;
        $this->dsp(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function load_source(string $src_name): source
    {
        global $usr;
        $src = new source($usr);
        $src->name = $src_name;
        $src->load();
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id == 0) {
            $src->name = $src_name;
            $src->save();
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->dsp('source', $src_name, $src->name);
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

        $dsp = new view_dsp($test_usr);
        $dsp->name = $dsp_name;
        $dsp->load();
        return $dsp;
    }

    function add_view(string $dsp_name, ?user $test_usr = null): view
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $dsp = $this->load_view($dsp_name, $test_usr);
        if ($dsp->id == 0) {
            $dsp->usr = $test_usr;
            $dsp->name = $dsp_name;
            $dsp->save();
        }
        return $dsp;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        $dsp = $this->add_view($dsp_name, $test_usr);
        $this->dsp('view', $dsp_name, $dsp->name);
        return $dsp;
    }


    function load_view_component(string $cmp_name, ?user $test_usr = null): view_cmp
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = new view_cmp($test_usr);
        $cmp->name = $cmp_name;
        $cmp->load();
        return $cmp;
    }

    function add_view_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): view_cmp
    {
        global $usr;

        if ($test_usr == null) {
            $test_usr = $usr;
        }

        $cmp = $this->load_view_component($cmp_name, $test_usr);
        if ($cmp->id == 0 or $cmp->id == Null) {
            $cmp->usr = $test_usr;
            $cmp->name = $cmp_name;
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
        $this->dsp('view component', $cmp_name, $cmp->name);
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
            if ($dsp->id > 0 and $cmp->id > 0) {
                $result = $cmp->unlink($dsp);
            }
        }
        return $result;
    }

    function load_word_link(string $from_name,
                            string $verb_code_id,
                            string $to_name): word_link
    {
        global $usr;
        global $verbs;

        $wrd_from = $this->load_word($from_name);
        $wrd_to = $this->load_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new word_link($usr);
        if ($from->id > 0 or $to->id > 0) {
            // check if the forward link exists
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load();
        }
        return $lnk_test;
    }

    /**
     * check if a word link exists and if not and requested create it
     * $phrase_name should be set if the standard name for the link should not be used
     */
    function test_word_link(string $from_name,
                            string $verb_code_id,
                            string $to_name,
                            string $target = '',
                            string $phrase_name = '',
                            bool   $autocreate = true)
    {
        global $usr;
        global $verbs;

        $result = '';

        // create the words if needed
        $wrd_from = $this->load_word($from_name);
        if ($wrd_from->id <= 0 and $autocreate) {
            $wrd_from->name = $from_name;
            $wrd_from->save();
            $wrd_from->load();
        }
        $wrd_to = $this->load_word($to_name);
        if ($wrd_to->id <= 0 and $autocreate) {
            $wrd_to->name = $to_name;
            $wrd_to->save();
            $wrd_to->load();
        }
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

        $lnk_test = new word_link($usr);
        if ($from->id == 0 or $to->id == 0) {
            log_err("Words " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            // check if the forward link exists
            $lnk_test->from = $from;
            $lnk_test->verb = $vrb;
            $lnk_test->to = $to;
            $lnk_test->load();
            if ($lnk_test->id > 0) {
                // refresh the given name if needed
                if ($phrase_name <> '' and $lnk_test->description() <> $phrase_name) {
                    $lnk_test->description = $phrase_name;
                    $lnk_test->save();
                    $lnk_test->load();
                }
                $result = $lnk_test;
            } else {
                // check if the backward link exists
                $lnk_test->from = $to;
                $lnk_test->verb = $vrb;
                $lnk_test->to = $from;
                $lnk_test->usr = $usr;
                $lnk_test->load();
                $result = $lnk_test;
                // create the link if requested
                if ($lnk_test->id <= 0 and $autocreate) {
                    $lnk_test->from = $from;
                    $lnk_test->verb = $vrb;
                    $lnk_test->to = $to;
                    $lnk_test->save();
                    $lnk_test->load();
                    // refresh the given name if needed
                    if ($lnk_test->id <> 0 and $phrase_name <> '' and $lnk_test->description() <> $phrase_name) {
                        $lnk_test->description = $phrase_name;
                        $lnk_test->save();
                        $lnk_test->load();
                    }
                    $result = $lnk_test;
                }
            }
        }
        // fallback setting of target f
        $result_text = '';
        if ($lnk_test->id > 0) {
            $result_text = $lnk_test->description();
            if ($target == '') {
                $target = $lnk_test->name();
            }
        }
        $this->dsp('word link', $target, $result_text, TIMEOUT_LIMIT_DB);
        return $result;
    }

    function del_word_link(string $from_name,
                            string $verb_code_id,
                            string $to_name): bool
    {
        $trp = $this->load_word_link($from_name, $verb_code_id, $to_name);
        if ($trp->id <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }

    function test_formula_link(string $formula_name, string $word_name, bool $autocreate = true): string
    {
        global $usr;

        $result = '';

        $frm = new formula($usr);
        $frm->name = $formula_name;
        $frm->load();
        $phr = new word($usr);
        $phr->name = $word_name;
        $phr->load();
        if ($frm->id > 0 and $phr->id <> 0) {
            $frm_lnk = new formula_link($usr);
            $frm_lnk->fob = $frm;
            $frm_lnk->tob = $phr;
            $frm_lnk->load();
            if ($frm_lnk->id > 0) {
                $result = $frm_lnk->fob->name . ' is linked to ' . $frm_lnk->tob->name;
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
     * Format functions
     */

    /**
     * @return string text with just single spaces
     */
    function trim(string $string_with_multiple_spaces): string
    {
        return trim(preg_replace('!\s+!', ' ', $string_with_multiple_spaces));
    }

    /**
     * @return string text with just single spaces and without line feeds
     */
    function trim_lines(string $string_with_new_lines): string
    {
        return $this->trim(preg_replace('/[\n\r]/', '', $string_with_new_lines));
    }

    /**
     * @return string text with just single spaces and all spaces removed not needed in a JSON
     */
    function trim_json(string $json_string): string
    {
        $result = $this->trim_lines($json_string);
        $result = preg_replace('/\[ {/', '[{', $result);
        $result = preg_replace('/} ]/', '}]', $result);
        $result = preg_replace('/" }/', '"}', $result);
        $result = preg_replace('/": /', '":', $result);
        $result = preg_replace('/, "/', ',"', $result);
        $result = preg_replace('/{ "/', '{"', $result);
        return preg_replace('/}, {/', '},{', $result);
    }

    /**
     * @return string text with just single spaces and all spaces removed not needed for HTML
     */
    function trim_html(string $html_string): string
    {
        $result = $this->trim_lines($html_string);
        $result = preg_replace('/ <td>/', '<td>', $result);
        $result = preg_replace('/ <\/td>/', '</td>', $result);
        $result = preg_replace('/ <th>/', '<th>', $result);
        $result = preg_replace('/ <\/th>/', '</th>', $result);
        $result = preg_replace('/ <tr>/', '<tr>', $result);
        $result = preg_replace('/ <\/tr>/', '</tr>', $result);
        $result = preg_replace('/> <div/', '><div', $result);
        $result = preg_replace('/> <\/div/', '></div', $result);
        $result = preg_replace('/> <table/', '><table', $result);
        $result = preg_replace('/> <\/table/', '></table', $result);
        $result = preg_replace('/> <footer/', '><footer', $result);
        $result = preg_replace('/> <\/footer/', '></footer', $result);
        $result = preg_replace('/" \/>/', '"/>', $result);
        return preg_replace('/"> </', '"><', $result);
    }

    /*
     * Display functions
     */

    /**
     * the HTML code to display the header text
     */
    function header($header_text)
    {
        echo '<br><br><h2>' . $header_text . '</h2><br>';
    }

    /**
     * the HTML code to display the subheader text
     */
    function subheader($header_text)
    {
        echo '<br><h3>' . $header_text . '</h3><br>';
    }

    /**
     * @return string the content of the test resource file
     */
    function file(string $test_resource_path): string
    {
        return file_get_contents(PATH_TEST_IMPORT_FILES . $test_resource_path);
    }

    /**
     * check if the test result is as expected and display the test result to an admin user
     * TODO replace all dsp calls with this but the
     *
     * @param string $msg (unique) description of the test
     * @param $result
     * @param $target
     * @param $exe_max_time
     * @param $comment
     * @param $test_type
     * @return bool
     */
    function assert(string $msg, $result, $target, $exe_max_time = TIMEOUT_LIMIT, $comment = '', $test_type = ''): bool
    {
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    function assert_json(object $usr_obj, string $json_file_name): bool
    {
        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . $json_file_name), true);
        $usr_obj->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($usr_obj->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        return $this->assert($this->name . 'import check name', $result, true);
    }

    /**
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql(sql_db $db_con, object $usr_obj): bool
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql($db_con, get_class($usr_obj));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql($db_con, get_class($usr_obj));
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
     * test the SQL statement creation for a value
     *
     * @param sql_par $qp the query parameters that should be tested
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return void
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
        $result = $this->assert(
            $this->name . $qp->name . '_' . $dialect,
            $this->trim($qp->sql),
            $this->trim($expected_sql)
        );

        // check if the prepared sql name is unique always based on the  PostgreSQL query parameter creation
        if ($dialect == sql_db::POSTGRES) {
            $result = $this->assert_sql_name_unique($qp->name);
        }

        return $result;
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
    function dsp($msg, $target, $result, $exe_max_time = TIMEOUT_LIMIT, $comment = '', $test_type = ''): bool
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
            foreach ($result as $key => $value) {
                if ($value != $target[$key]) {
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
                        $target = $result;
                        log_err('Unexpected diff ' . $diff);
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
                $txt .= ", but does not contain ";
            } else {
                $txt .= ", but it is ";
            }
        }
        if (is_array($result)) {
            if ($result != null) {
                if (is_array($result[0])) {
                    $txt .= "\"";
                    foreach ($result as $result_item) {
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
    function dsp_contains($test_text, $target, $result, $exe_max_time = TIMEOUT_LIMIT, $comment = ''): bool
    {
        if (strpos($result, $target) === false) {
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
    function dsp_warning(string $msg)
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
    function dsp_result_html()
    {
        echo '<br>';
        echo '<h2>';
        echo $this->total_tests . ' test cases<br>';
        echo $this->timeout_counter . ' timeouts<br>';
        echo $this->error_counter . ' errors<br>';
        echo "<br>";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com</h2>';
        echo '<br>';
        echo '<br>';
    }

    /**
     * display the test results in pure test format
     */
    function dsp_result()
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

}


// -----------------------------------------------
// testing functions to create the main time value
// -----------------------------------------------

function zu_test_time_setup(testing $t): string
{
    global $usr, $db_con;

    $result = '';
    $this_year = date('Y');
    $prev_year = '';
    $test_years = cfg_get('test_years', $db_con);
    if ($test_years == '') {
        log_warning('Configuration of test years is missing', 'test_base->zu_test_time_setup');
    } else {
        $start_year = $this_year - $test_years;
        $end_year = $this_year + $test_years;
        for ($year = $start_year; $year <= $end_year; $year++) {
            $this_year = $year;
            $t->test_word(strval($this_year));
            $wrd_lnk = $t->test_word_link(TW_YEAR, verb::IS_A, $this_year);
            $result = $wrd_lnk->name;
            if ($prev_year <> '') {
                $t->test_word_link($prev_year, verb::DBL_FOLLOW, $this_year);
            }
            $prev_year = $this_year;
        }
    }

    return $result;
}
