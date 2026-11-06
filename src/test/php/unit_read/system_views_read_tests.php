<?php

/*

    unit_read/system_view_read_tests.php - testing of the system views with direct data reload from the database
    ------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::EXECUTE . 'calculator_definition_report.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::PHRASE . 'phrase_data.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once test_paths::UTILS . 'headless_browser.php';
include_once test_paths::UTILS . 'csv_table_diff.php';
include_once test_paths::UTILS . 'html_page_diff.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as user_message_cfg;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator_definition_report;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_data as phrase_data_ui;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\csv_table_diff;
use Zukunft\ZukunftCom\test\php\utils\headless_browser;
use Zukunft\ZukunftCom\test\php\utils\html_page_diff;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class system_views_read_tests
{

    // two cells of the sensitivity table of the Swiss NPP vote: the levelised cost and the verdict number of the
    // construction cost and the cost of capital of the inputs (8'826 CHF/kW, 5 %) and of 5'000 CHF/kW at 3 %
    const string NPP_SENSITIVITY_INPUT_CELL = '114 (-0.01)';
    const string NPP_SENSITIVITY_OTHER_CELL = '66 (-0.77)';
    // the element of use_case_cases.js with the result of a case as json and its two fields
    const string CASE_RESULT_ID = 'caseResult';
    const string CASE_MISSING = 'missing';
    const string CASE_PAGE = 'page';
    // the case without an input, to read a page as a browser shows it after its script has run
    const string CASE_UNCHANGED = 'unchanged';
    // the field of the case test_files::PV_CASE_MISSING_FIELD that no page has
    const string CASE_UNKNOWN_FIELD = 'Wind turbine size';
    // the missing field of a case that has not ended in the browser e.g. because the page script has failed
    const string CASE_NOT_ENDED = 'the case has not ended in the headless browser';
    // the row of the PV calculator page whose numbers every input case changes
    const string PV_PRODUCTION_ROW = html_page_diff::KIND_ROW . ': Electricity / PV production';
    // the element of calculator_editor.js that holds the page preview
    const string EDITOR_PREVIEW_ID = 'pane';

    /**
     * render every system view with the data read from the database and compare it with the snapshot
     * TODO Prio 2 move all other HTML frontend tests here
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run(test_cleanup $t): void
    {
        // start the test section (ts)
        $ts = 'db read system view by object ';
        $t->header($ts);

        $cfg = $this->context($t);
        $this->run_start($t);
        $this->run_words($t, $cfg);
        $this->run_verbs($t, $cfg);
        $this->run_triples($t, $cfg);
        $this->run_sources($t, $cfg);
        $this->run_refs($t, $cfg);
        $this->run_values($t, $cfg);
        $this->run_formulas($t, $cfg);
        $this->run_results($t, $cfg);
        $this->run_views($t, $cfg);
        $this->run_components($t, $cfg);
        $this->run_users($t, $cfg);
        $this->run_system_pages($t);
        $this->run_pinned_views($t);
        $this->run_use_cases($t, $cfg);
        // TODO Prio 2 rename it to run_sample_use_cases
        $this->run_pv_lifetime_balance($t);
        $this->run_pv_lifetime_balance_diff($t);
        $this->run_pv_lifetime_balance_cases($t);
        $this->run_calculator_editor($t);
        $this->run_formula_editor($t);
        $this->run_table_editor($t);
        $this->run_cbam_tree_calculator($t, $cfg);
        $this->run_portfolio_split_calculator($t);
        $this->run_portfolio_split_calculator_diff($t);
        $this->run_portfolio_split_calculator_cases($t);
        $this->run_npp_ch_vote($t);
        $this->run_npp_ch_vote_editor($t);
        $this->run_npp_sensitivity($t);
        $this->run_global_warming_solutions_csv($t);
        $this->run_populism_solutions_table($t);
        $this->run_context_values_per_phrase($t);
        $this->run_result_operands($t);
        $this->run_start_page_steps($t);

        // remove test files not used any more e.g. the snapshot of an object id that has changed
        $t->delete_unused_files(test_paths::VIEWS);

        $this->run_web_frontend($t);
    }

    /**
     * create the stable test context that is not based on the database so that the test results rarely change
     * unlike the horizontal system view test for this test the object can be filled with data for special cases
     *
     * @param test_cleanup $t the test environment with the test users
     * @return data_object_ui the frontend type cache with the formulas, views and components of the test factories
     */
    function context(test_cleanup $t): data_object_ui
    {
        $t_frm = new test_formulas($t);
        $t_msk = new test_views($t);
        $t_cmp = new test_components($t);
        $msg = new user_message();

        $ui = new frontend('system_views_read_tests');
        $ui->load_cache($msg);
        $cfg = new data_object_ui();
        $cfg->typ_lst_cache = $ui->dto->typ_lst_cache;
        $cfg->set_formula_list($t_frm->formula_list_ui());
        $cfg->set_view_list($t_msk->view_list_ui());
        $cfg->set_component_list($t_cmp->component_list_ui());
        return $cfg;
    }

    /**
     * the entry view shows the global problems as a simple table with the short list,
     * with the more list that a "... more" click requests with the next list size, and as the
     * full table with every column and the variance that the "..." header links to
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_start(test_cleanup $t): void
    {
        // the variants differ only by the display parameters of the url, so they share one
        // request cache and only the first loads the data of the table
        $cfg = $t->view_context();
        // the first variant loads the data of the table, so its steps and api calls are logged with their seconds
        $debug_before = $t->set_debug(url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID, $cfg);
        $t->set_debug($debug_before);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST]);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
                url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_ALL,
                url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE]);
        // the full table with the short list, and the two switches on their own: the mayor
        // columns with the variance, and the mayor columns explicitly without the variance
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_ALL,
                url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE]);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
                url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_EX_MAIN,
                url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE]);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
                url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_EX_MAIN,
                url_var::DISPLAY_LIST_VARIANCE => url_var::FALSE]);
        // the steps between the mayor columns and the full table, which the "..." clicks
        // request: one tier is added per step, but still one unit per column and no variance
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
                url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_EX_MINOR]);
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID,
            $cfg, [url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
                url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_EX_MARGINAL]);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_words(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), word_names::MATH_ID, $cfg);
        // Zurich and CHF is the example for the page-title symbol-line layout
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), word_names::ZH_ID, $cfg);
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), words::CHF_ID, $cfg);
        // inhabitants is the example for the related formula list (e.g. the "increase" formula)
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), word_names::INHABITANT_ID, $cfg);
        // the values of 15-year have a variance, which is shown only if the url asks for it
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), word_names::FIFTEEN_YEAR_ID, $cfg);
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), word_names::FIFTEEN_YEAR_ID, $cfg,
            [url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE]);
        $t->assert_view(views::WORD_ADD_DETAIL, $t->usr1, new word($t->usr1), word_names::MATH_ID);
        // the one field form that adds a word just by its name
        $t->assert_view(views::WORD_ADD, $t->usr1, new word($t->usr1));
        $t->assert_view(views::WORD_EDIT, $t->usr1, new word($t->usr1), word_names::MATH_ID, $cfg);
        $t->assert_view(views::WORD_DEL, $t->usr1, new word($t->usr1), word_names::MATH_ID, $cfg);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_verbs(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::VERB, $t->usr1, new verb(), 1, $cfg);
        $t->assert_view(views::VERB_ADD, $t->usr1, new verb());
        $t->assert_view(views::VERB_EDIT, $t->usr1, new verb(), 1, $cfg);
        $t->assert_view(views::VERB_DEL, $t->usr1, new verb(), 1, $cfg);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_triples(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), triple_names::MATH_CONST_ID, $cfg);
        $t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), triple_names::PI_ID, $cfg);
        $t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), triple_names::SPEED_OF_LIGHT_ID, $cfg);
        $t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), triple_names::CANTON_ZURICH_ID, $cfg);
        $t->assert_view(views::TRIPLE_ADD_DETAIL, $t->usr1, new triple($t->usr1));
        $t->assert_view(views::TRIPLE_EDIT, $t->usr1, new triple($t->usr1), 1, $cfg);
        $t->assert_view(views::TRIPLE_DEL, $t->usr1, new triple($t->usr1), 1, $cfg);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_sources(test_cleanup $t, data_object_ui $cfg): void
    {
        //$t->assert_view(views::SOURCE, $t->usr1, new source($t->usr1), 1, $cfg);
        $t->assert_view(views::SOURCE_ADD, $t->usr1, new source($t->usr1));
        $t->assert_view(views::SOURCE_EDIT, $t->usr1, new source($t->usr1), 1, $cfg);
        $t->assert_view(views::SOURCE_DEL, $t->usr1, new source($t->usr1), 1, $cfg);
    }

    /**
     * the wikidata reference of the word "Pi" is the only seeded reference with an own url and
     * one of the three with a source, so it is the example that fills every field of the
     * reference page except the last update
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_refs(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::REF, $t->usr1, new ref($t->usr1), refs::PI_ID, $cfg);
        $t->assert_view(views::REF_ADD, $t->usr1, new ref($t->usr1));
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_values(test_cleanup $t, data_object_ui $cfg): void
    {
        // the pi number of units.json is the example for the related-phrase links and the grey value
        // of the "Value title" component and, because it names the "mathematical constant" source,
        // for the "system show field value source" component of the value default view; the pi value
        // keyed by the "π (unit symbol)" triple (values::PI_ID) exists only as a unit test fixture and
        // not in the seeded database, so it would snapshot an empty page
        $t->assert_view(views::VALUE, $t->usr1, new value($t->usr1), values::PI_MATH_ID, $cfg);
        // the e number is the other mathematical constant, so its page is the example for the related
        // values column in the reverse direction (e lists pi like pi lists e) and pins the group id of
        // the value keyed by the "𝑒 (math)" triple (values::E_ID)
        $t->assert_view(views::VALUE, $t->usr1, new value($t->usr1), values::E_ID, $cfg);
        // the target price earning ratio of companies.json is shared personally and protected for
        // users, so it is the example for the share and protection subtitle of the "Value title"
        // component, which shows nothing for a value with the default types like the pi values
        $t->assert_view(views::VALUE, $t->usr1, new value($t->usr1), values::TARGET_PE_RATIO_ID, $cfg);
        // the seeded database has neither a value with more than 16 phrases nor a text or a geo value, so
        // these value pages are rendered from the test factory objects together with the non prime value
        // with 16 phrases; a text and a geo value are shown on the value page, so they share its folder
        $t_val = new test_values($t);
        $t->assert_view_by_factory(views::VALUE, $t->usr1, $t_val->value_16(), test_files::VIEW_CASE_NON_PRIME, $cfg);
        $t->assert_view_by_factory(views::VALUE, $t->usr1, $t_val->value_17_plus(), test_files::VIEW_CASE_BIG, $cfg);
        $t->assert_view_by_factory(views::VALUE, $t->usr1, $t_val->text_value(), test_files::VIEW_CASE_TEXT, $cfg, value::class);
        $t->assert_view_by_factory(views::VALUE, $t->usr1, $t_val->geo_value(), test_files::VIEW_CASE_GEO, $cfg, value::class);
        // the empty form that selects the phrases of a new value step by step
        $t->assert_view(views::VALUE_ADD_NO_JS, $t->usr1, new value($t->usr1));
        // the empty form that adds a value just by its phrases and the number
        $t->assert_view(views::VALUE_ADD, $t->usr1, new value($t->usr1));
        // the form that overwrites just the number of the pi value
        $t->assert_view(views::VALUE_OVERWRITE, $t->usr1, new value($t->usr1), values::PI_MATH_ID, $cfg);
        // the seeded database has no text, time or geo value, so their overwrite forms use the factory values
        $t->assert_view_by_factory(views::VALUE_OVERWRITE, $t->usr1, $t_val->text_value(), test_files::VIEW_CASE_TEXT, $cfg, value::class);
        $t->assert_view_by_factory(views::VALUE_OVERWRITE, $t->usr1, $t_val->time_value(), test_files::VIEW_CASE_TIME, $cfg, value::class);
        $t->assert_view_by_factory(views::VALUE_OVERWRITE, $t->usr1, $t_val->geo_value(), test_files::VIEW_CASE_GEO, $cfg, value::class);
        // GROUP
        //$t->assert_view(views::GROUP_ADD, $t->usr1, new group($t->usr1));
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_formulas(test_cleanup $t, data_object_ui $cfg): void
    {
        // increase is the example for the assigned-phrase subtitle of the "Formula title" component
        $t->assert_view(views::FORMULA, $t->usr1, new formula($t->usr1), formula_names::INCREASE_ID, $cfg);
        $t->assert_view(views::FORMULA, $t->usr1, new formula($t->usr1), formula_names::CITY_POPULATION_ID, $cfg);
        $t->assert_view(views::FORMULA_ADD, $t->usr1, new formula($t->usr1));
        $t->assert_view(views::FORMULA_EDIT, $t->usr1, new formula($t->usr1), 1, $cfg);
        $t->assert_view(views::FORMULA_DEL, $t->usr1, new formula($t->usr1), 1, $cfg);
        // FORMULA TEST
    }

    /**
     * no seeded result id is known, so the result pages are rendered from the factory result;
     * the default page shows beside the number the values, formulas and results that the
     * calculation has used, which the api reads for the phrases of the result (see
     * result::used_phrase_selection), so the page needs the database
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_results(test_cleanup $t, data_object_ui $cfg): void
    {
        $t_res = new test_results($t);
        $t->assert_view_by_factory(views::RESULT, $t->usr1, $t_res->result_simple(), test_files::VIEW_CASE_SIMPLE, $cfg, result::class);
        // the page of a result with used values, formulas and results, so that the three columns
        // are part of a snapshot: the used results are grouped like the values with the unit
        // symbols behind the number (see ui_list::results_used)
        $t->assert_view_by_factory(views::RESULT, $t->usr1, $t_res->result_page_related(), test_files::VIEW_CASE_RELATED, $cfg, result::class);
        // a result that the calculation of the seeded data has created, so that the formula line
        // shows the numbers really used, e.g. the potential loss of populism in trillion EUR
        // the number and the formula are checked, because a set group id alone does not prove that
        // the calculated row has been loaded
        $test_name = 'the potential loss of populism has been calculated';
        $res_pop = $t_res->result_populism_potential_loss();
        $t->assert_true($test_name, $res_pop->number() !== null);
        $test_name = '... by a formula';
        $t->assert_true($test_name, $res_pop->formula_id() != 0);
        $t->assert_view_by_factory(views::RESULT, $t->usr1, $res_pop, test_files::VIEW_CASE_POPULISM_LOSS, $cfg, result::class);
        // the form that overwrites just the number of a result
        $t->assert_view_by_factory(views::RESULT_OVERWRITE, $t->usr1, $t_res->result_simple(), test_files::VIEW_CASE_SIMPLE, $cfg, result::class);
    }

    /**
     * the word default view is the example for the view default page, because it is the view
     * that every word page opens with, so it is the view a user is most likely to look at
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_views(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::VIEW, $t->usr1, new view($t->usr1), views::WORD_ID, $cfg);
        $t->assert_view(views::VIEW_ADD, $t->usr1, new view($t->usr1));
        $t->assert_view(views::VIEW_EDIT, $t->usr1, new view($t->usr1), 1, $cfg);
        $t->assert_view(views::VIEW_DEL, $t->usr1, new view($t->usr1), 1, $cfg);
    }

    /**
     * the solution priority title is the example of a component with a description and a type
     * that is used by a view, so the views list of the page has an entry; the style, the
     * formula and the linked component of this component are not set, so the page shows the
     * three labels without a value
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_components(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view(views::COMPONENT, $t->usr1, new component($t->usr1), components::SOLUTION_PRIO_TITLE_ID, $cfg);
        $t->assert_view(views::COMPONENT_ADD, $t->usr1, new component($t->usr1));
        $t->assert_view(views::COMPONENT_EDIT, $t->usr1, new component($t->usr1), 1, $cfg);
        $t->assert_view(views::COMPONENT_DEL, $t->usr1, new component($t->usr1), 1, $cfg);
        //$t->assert_view(view_shared::DSP_COMPONENT_ADD, $t->usr1, new component($t->usr1), 1, $cfg);
        // TODO Prio 2 add the frontend reaction tests e.g. call the view.php script with the reaction to add a word
    }

    /**
     * the user page lists the sandbox overwrites of the shown user in a fixed column, so the
     * change log of the test context decides what the column shows; the overwrites of the
     * test user are taken from a fixture, because the changes in the database grow with every
     * test run, which would make the snapshot change without a code change
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_users(test_cleanup $t, data_object_ui $cfg): void
    {
        $t_log = new test_log($t);
        $cfg_chg = clone $cfg;
        $cfg_chg->chg_log = $t_log->log_list_user_overwrites_ui();
        // the shown user has done the overwrites of the test context, so the column lists them
        // and names the changed word, triple and formula of each overwrite
        $t->assert_view(views::USER, $t->usr1, new user(), users::SYSTEM_TEST_ID, $cfg_chg);
        // the same test context for a user who has not done any of these overwrites, so the column
        // shows the no-changes message, which also checks that it filters by the shown user
        $t->assert_view(views::USER, $t->usr1, new user(), users::SYSTEM_TEST_NORMAL_ID, $cfg_chg);
        // the configuration, quarantine and job views are part of the system views, but show only the not yet
        // available hint until their data and actions are implemented
        $t->assert_view(views::USER_CONFIG, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::SYSTEM_CONFIG, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::ADMIN_CONFIG, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::USER_QUARANTINE, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::ALL_QUARANTINE, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::USER_JOBS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::ALL_JOBS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
    }

    /**
     * write the static start page and the short url page of each pinned view of config.yaml, which
     * the next run with AUTO_UPDATE_TEST_FILES writes; here, because the url of a pinned view
     * without url is created from the ids of its view name and phrase name, which differ by pod
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_pinned_views(test_cleanup $t): void
    {
        $html = new html_base();
        $msg = new user_message_cfg();
        $pin_lst = [];
        foreach ($t->pinned_views() as $url_name => $pin) {
            $pin[words::URL] = $this->pinned_url($t, $pin, $msg);
            $pin_lst[$url_name] = $pin;
            $test_name = 'the short url ' . $url_name . ' opens ' . $pin[words::URL];
            $t->assert_file($test_name, $html->forward_url_page($pin[words::URL]), test_files::PINNED_PAGE_PATH . $url_name);
        }
        // negative: an inactive pinned view has no target, so its short url page is removed
        foreach (array_keys($t->pinned_views_inactive()) as $url_name) {
            $test_name = 'the inactive pinned view ' . $url_name . ' has no short url page';
            $t->assert_no_file($test_name, test_files::PINNED_PAGE_PATH . $url_name);
        }
        $test_name = 'the start page links the pinned views of config.yaml';
        $start_page = file_get_contents(test_files::START_PAGE);
        $t->assert_file($test_name, $html->start_page($start_page, $pin_lst, new user_message()), test_files::START_PAGE);
        $test_name = 'a pinned view without url opens the view of its view name with its phrase';
        $pin_by_names = [triples::VIEW_NAME => views::CALCULATOR_NAME, triples::PHRASE_NAME => triple_names::EXAMPLE_PV_INSTALLATION];
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $expected = $html->page_url([url_var::MASK => views::CALCULATOR_ID, url_var::ID => $phr->id()]);
        $t->assert($test_name, $this->pinned_url($t, $pin_by_names, $msg), $expected);
        $test_name = '... and only the view if it has no phrase name';
        $pin_view_only = [triples::VIEW_NAME => views::CALCULATOR_NAME];
        $expected = $html->page_url([url_var::MASK => views::CALCULATOR_ID]);
        $t->assert($test_name, $this->pinned_url($t, $pin_view_only, $msg), $expected);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param array $pin the url or the view name and the phrase name of a pinned view of config.yaml
     * @param user_message_cfg $msg to report a view or phrase that cannot be loaded
     * @return string the url of the pinned view or the url of its view name with its phrase if the url is empty
     */
    private function pinned_url(test_cleanup $t, array $pin, user_message_cfg $msg): string
    {
        $url = $pin[words::URL] ?? '';
        if ($url == '') {
            $msk = new view($t->usr1);
            $msk->load_by_name($pin[triples::VIEW_NAME], $msg);
            $url_array = [url_var::MASK => $msk->id()];
            $phr_name = $pin[triples::PHRASE_NAME] ?? '';
            if ($phr_name != '') {
                $phr = new phrase($t->usr1);
                $phr->load_by_name($phr_name, $msg);
                // the phrase id of a triple is negative, so the view shows the triple
                $url_array[url_var::ID] = $phr->id();
            }
            $url = new html_base()->page_url($url_array);
        }
        return $url;
    }

    /**
     * the language, log, import, export, process, find and object list views are skeletons that show
     * only the not yet available hint until their data and actions are implemented
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_system_pages(test_cleanup $t): void
    {
        // LANGUAGE
        $t->assert_view(views::PHRASE_TRANSLATIONS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LANGUAGE_FORMS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::TRANSLATIONS_MISSING, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_LANGUAGES, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // SYS LOG
        $t->assert_view(views::SYS_LOG_DETAIL, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // CHANGE LOG
        $t->assert_view(views::CHANGE_LOG_USER, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::CHANGE_LOG_RECENT, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::OBJECT_HISTORY, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::CHANGE_DETAIL, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // IMPORT
        $t->assert_view(views::IMPORT_PREVIEW, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::IMPORT_RESULT, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::IMPORT_URL, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::IMPORT_WIKIDATA, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // EXPORT
        $t->assert_view(views::EXPORT_SELECTIONS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::EXPORT_OBJECT, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::EXPORT_READY, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // PROCESS
        $t->assert_view(views::JOB_DETAIL, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::CALC_STATUS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // FIND
        $t->assert_view(views::VALUE_FIND, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::REF_FIND, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::FIND_DUPLICATES, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        // OBJECT LISTS
        $t->assert_view(views::LIST_WORDS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_TRIPLES, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_SOURCES, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_REFS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_FORMULAS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_VIEWS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
        $t->assert_view(views::LIST_COMPONENTS, $t->usr1, new user(), users::SYSTEM_TEST_ID);
    }

    /**
     * the views of the use cases and the views appended to the seed views: a use case is user data,
     * so the view and the triple are selected by their names and never by a code id or a database
     * id (docs/llm/json_structure.md "Use case files")
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_use_cases(test_cleanup $t, data_object_ui $cfg): void
    {
        // the comparison table shows the values of a phrase with the defined columns: the use case
        // "PV in Switzerland" is the sample with the avoided emission per reference mix
        $t->assert_view_by_name(views::COMPARISON_TABLE_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);

        // the parked table editor of the server with its title and the phrase selector
        $t->assert_view_by_name(views::TABLE_EDITOR_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg,
            '', [url_var::JAVASCRIPT => url_var::FALSE]);

        // the process catch pages are appended to the seed views, so they are selected by name like the editors
        $t->assert_view_by_name(views::PROCESS_CATCH_START_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);
        $t->assert_view_by_name(views::PROCESS_CATCH_MAIN_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);
        $t->assert_view_by_name(views::PROCESS_CATCH_DETAIL_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);
        $t->assert_view_by_name(views::PROCESS_CATCH_RESULT_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);
    }

    /**
     * the PV lifetime balance use case has no view of its own: the system view Calculator shows
     * the example PV installation by the calculator triples of PV installation, which the view
     * loads with one phraseData call into the request cache before the components render
     * (view_exe::add_calculator_data), so an unchanged page confirms that the answer has
     * everything that the page shows; the page is written to the use
     * case folder next to the hand made sample page (pv_lifetime_balance_sample.html), so that
     * the generated page can be compared with the target layout
     *
     * the page gets a request cache of its own like a live request, so that it shows only the
     * values and results of the database and none of the test objects of the other views
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_pv_lifetime_balance(test_cleanup $t): void
    {
        // sc=0 asks for the page of the server, which shows the page also without javascript
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_PV_INSTALLATION, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::PV_LIFETIME_BALANCE_NO_JS,
            [url_var::JAVASCRIPT => url_var::FALSE]);
        // by default the browser builds the same page from the json data of the page, because
        // config.yaml allows javascript for the calculator
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_PV_INSTALLATION, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::PV_LIFETIME_BALANCE_JS);
    }

    /**
     * the differences of the PV calculator page without javascript (run_pv_lifetime_balance) to the
     * hand made sample page as a markdown report, which can be given to an llm as the prompt to bring
     * the page closer to the sample; the report is checked against its last version like every use
     * case page, so a change of the page shows up as a change of the report
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_pv_lifetime_balance_diff(test_cleanup $t): void
    {
        $this->assert_sample_diff($t, test_files::PV_LIFETIME_BALANCE_SAMPLE,
            test_files::PV_LIFETIME_BALANCE_NO_JS, test_files::PV_LIFETIME_BALANCE_DIFF);
    }

    /**
     * report the differences of a page that the server renders to its hand made sample
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $sample_name the file name of the sample without the extension
     * @param string $page_name the file name of the generated page without the extension
     * @param string $diff_name the file name of the markdown report without the extension
     * @param string $cases_script the script with the CASES of a sample that fills its tables by its script,
     *                             so that the sample is compared as a browser shows it; empty for a sample with its tables in the html
     * @param bool $page_by_script true if also the generated page is built by its script e.g. the table editor,
     *                             so that the page is compared as a browser shows it with the same cases script
     * @return void
     */
    private function assert_sample_diff(
        test_cleanup $t,
        string       $sample_name,
        string       $page_name,
        string       $diff_name,
        string       $cases_script = '',
        bool         $page_by_script = false
    ): void
    {
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
        $sample_file = $sample_name . test_files::HTML;
        $sample_html = $t->path_file($dir . $sample_file);
        $page_html = $t->path_file($dir . $page_name . test_files::HTML);
        if ($cases_script != '') {
            $sample_html = $this->sample_in_browser($sample_html, $cases_script);
            if ($page_by_script) {
                $page_html = $this->sample_in_browser($page_html, $cases_script);
            }
        }
        if ($sample_html === null or $page_html === null) {
            $t->dsp_warning('no headless browser found, so the differences of ' . $sample_file . ' are not reported');
        } else {
            $this->assert_sample_items($t, $sample_html, $page_html, $sample_file, $page_name, $diff_name);
        }
    }

    /**
     * @param string $sample_html the html code of the sample
     * @param string $sample_html the html code of the sample or of a generated page that its script builds
     * @param string $cases_script the script with the CASES of the page
     * @return string|null the html code of the page as a browser shows it after its script has run, null without a browser
     */
    private function sample_in_browser(string $sample_html, string $cases_script): ?string
    {
        $result = null;
        $brw = new headless_browser();
        if ($brw->start() != '') {
            $sample_copy = $brw->page_copy($sample_html, $cases_script, test_files::USE_CASE_CASES);
            $result = $this->pv_case($brw, $sample_copy, self::CASE_UNCHANGED)[self::CASE_PAGE];
            unlink($sample_copy);
        }
        $brw->stop();
        return $result;
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param string $sample_html the html code of the sample to compare
     * @param string $page_html the html code of the generated page to compare
     * @param string $sample_file the file name of the sample
     * @param string $page_name the file name of the generated page without the extension
     * @param string $diff_name the file name of the markdown report without the extension
     * @return void
     */
    private function assert_sample_items(
        test_cleanup $t,
        string       $sample_html,
        string       $page_html,
        string       $sample_file,
        string       $page_name,
        string       $diff_name
    ): void
    {
        $hpd = new html_page_diff();
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
        $page_file = $page_name . test_files::HTML;
        $sample = $hpd->page_items($sample_html);
        $page = $hpd->page_items($page_html);

        // a sub text is a separate part of a cell, also if the html has no white space in front of it
        $test_name = 'the compare reads the parts of a cell the same with and without white space';
        $row_html = '<main><table><tbody><tr><td>row</td><td>10%s<span>shares</span></td></tr></tbody></table></main>';
        $no_space_row = $hpd->page_items(sprintf($row_html, ''));
        $space_row = $hpd->page_items(sprintf($row_html, "\n "));
        $t->assert($test_name, $no_space_row, $space_row);
        // negative: different parts of a cell stay a difference
        $test_name = '... but another part is a difference';
        $other_row = $hpd->page_items(str_replace('shares', 'CHF', sprintf($row_html, '')));
        $t->assert_false($test_name, $no_space_row == $other_row);
        // a number input of a sample cannot hold the thousand separator of the generated page
        $test_name = 'the compare reads a number input like the text of the same number';
        $num_html = '<main><table><tbody><tr><td>row</td><td>%s</td></tr></tbody></table></main>';
        $input_row = $hpd->page_items(sprintf($num_html, '<input type="number" value="30000000.00">'));
        $t->assert($test_name, $input_row, $hpd->page_items(sprintf($num_html, "30'000'000.00")));
        // negative: other decimals stay a difference, also of a number input
        $test_name = '... but other decimals are a difference';
        $t->assert_false($test_name, $input_row == $hpd->page_items(sprintf($num_html, "30'000'000")));

        $test_name = 'the sample ' . $sample_file . ' has items to compare';
        $t->assert_true($test_name, count($sample) > 0);
        // negative: a page compared with itself has no difference, so every finding comes from the pages
        $test_name = '... and a page compared with itself has no difference';
        $self_report = $hpd->report($sample, $sample, $sample_file, $sample_file);
        $t->assert_text_contains($test_name, $self_report, 'Missing in the generated page (0)');
        $t->assert_text_contains($test_name, $self_report, 'Different (0)');
        $test_name = 'the differences of ' . $page_file . ' to the sample are reported';
        $t->assert_file($test_name, $hpd->report($sample, $page, $sample_file, $page_file),
            $dir . $diff_name . test_files::MD, test_files::MD);
    }

    /**
     * the PV calculator page that the browser builds (run_pv_lifetime_balance) and the hand made sample get the
     * same numbers of an input case typed in like a user, so that the differences of the two pages show if the
     * javascript uses the formulas like the sample: standard with the numbers of the sample, high and low with
     * every input and assumption changed; the actions run in a headless browser, so without one they are skipped
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_pv_lifetime_balance_cases(test_cleanup $t): void
    {
        $brw = new headless_browser();
        $browser_name = $brw->start();
        if ($browser_name == '') {
            $t->dsp_warning('no headless browser found, so the PV calculator input cases are skipped');
        } else {
            $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
            $sample_html = $t->path_file($dir . test_files::PV_LIFETIME_BALANCE_SAMPLE . test_files::HTML);
            $page_html = $t->path_file($dir . test_files::PV_LIFETIME_BALANCE_JS . test_files::HTML);
            $sample = $brw->page_copy($sample_html, test_files::PV_LIFETIME_BALANCE_CASES, test_files::USE_CASE_CASES);
            $page = $brw->page_copy($page_html, test_files::PV_LIFETIME_BALANCE_CASES, test_files::USE_CASE_CASES);
            $std_cells = '';
            foreach (test_files::PV_CASES as $case) {
                $page_items = $this->assert_sample_case($t, $brw, $sample, $page, test_files::PV_LIFETIME_BALANCE_SAMPLE,
                    test_files::PV_LIFETIME_BALANCE_JS, test_files::PV_CASE_DIFF_PREFIX, $case, true);
                $cells = ($page_items[self::PV_PRODUCTION_ROW] ?? [])[html_page_diff::PROP_CELLS] ?? '';
                if ($case == test_files::PV_CASE_STANDARD) {
                    $std_cells = $cells;
                } else {
                    $test_name = '... and the ' . $case . ' case changes the PV production of the generated page';
                    $t->assert_not($test_name, $cells, $std_cells);
                }
            }
            // negative: a field of a case that the page does not have is reported, not skipped
            $test_name = 'a case with a field that the page does not have is reported';
            $case_res = $this->pv_case($brw, $page, test_files::PV_CASE_MISSING_FIELD);
            $missing = $case_res[self::CASE_MISSING];
            $t->assert($test_name, $missing, [self::CASE_UNKNOWN_FIELD]);
            unlink($sample);
            unlink($page);
        }
        $brw->stop();
    }

    /**
     * type the numbers of the case into the sample and into the generated page and compare the two pages
     *
     * @param test_cleanup $t the test environment with the test users
     * @param headless_browser $brw the started headless browser
     * @param string $sample the path of the sample copy with the case script
     * @param string $page the path of the generated page copy with the case script
     * @param string $sample_name the file name of the sample without the extension
     * @param string $page_name the file name of the generated page without the extension
     * @param string $diff_prefix the start of the file name of the report, which ends with the name of the case
     * @param string $case the name of the case e.g. test_files::PV_CASE_HIGH
     * @param bool $all_fields true if both pages must have every field of the case, false to only report a missing one
     * @return array the items of the generated page after the case
     */
    private function assert_sample_case(
        test_cleanup     $t,
        headless_browser $brw,
        string           $sample,
        string           $page,
        string           $sample_name,
        string           $page_name,
        string           $diff_prefix,
        string           $case,
        bool             $all_fields
    ): array
    {
        $hpd = new html_page_diff();
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
        $sample_res = $this->pv_case($brw, $sample, $case);
        $page_res = $this->pv_case($brw, $page, $case);
        $sample_missing = $sample_res[self::CASE_MISSING];
        $page_missing = $page_res[self::CASE_MISSING];
        $not_found = '';
        if ($all_fields) {
            $test_name = 'the ' . $case . ' case types every number into the sample';
            $t->assert($test_name, $sample_missing, []);
            $test_name = '... and into the generated page';
            $t->assert($test_name, $page_missing, []);
        } else {
            $not_found = $this->case_fields_not_found($sample_missing, $page_missing);
        }
        $sample_items = $hpd->page_items($sample_res[self::CASE_PAGE]);
        $page_items = $hpd->page_items($page_res[self::CASE_PAGE]);
        $sample_file = $sample_name . test_files::HTML . '#' . $case;
        $page_file = $page_name . test_files::HTML . '#' . $case;
        $test_name = 'the differences of ' . $page_file . ' to the sample are reported';
        $t->assert_file($test_name, $hpd->report($sample_items, $page_items, $sample_file, $page_file) . $not_found,
            $dir . $diff_prefix . $case . test_files::MD, test_files::MD);
        return $page_items;
    }

    /**
     * @param array $sample_missing the labels of the case that the sample does not have
     * @param array $page_missing the labels of the case that the generated page does not have
     * @return string the part of the report that names the fields of the case that were not typed in, so that a
     *                difference of a number can be explained by an input that one page has not got
     */
    private function case_fields_not_found(array $sample_missing, array $page_missing): string
    {
        $result = "\n## Case fields that the sample does not have (" . count($sample_missing) . ")\n\n";
        foreach ($sample_missing as $label) {
            $result .= '- ' . $label . "\n";
        }
        $result .= "\n## Case fields that the generated page does not have (" . count($page_missing) . ")\n\n";
        foreach ($page_missing as $label) {
            $result .= '- ' . $label . "\n";
        }
        return $result;
    }

    /**
     * @param headless_browser $brw the started headless browser
     * @param string $page the path of the page copy with the case script
     * @param string $case the name of the case e.g. test_files::PV_CASE_HIGH
     * @return array the labels of the case that the page does not have and the html of the page after the case,
     *               which for a case that has not ended are CASE_NOT_ENDED and an empty page
     */
    private function pv_case(headless_browser $brw, string $page, string $case): array
    {
        $result = [self::CASE_MISSING => [self::CASE_NOT_ENDED], self::CASE_PAGE => ''];
        $json = $brw->text('file://' . $page . '#' . $case, self::CASE_RESULT_ID);
        if ($json !== null) {
            $result = json_decode($json, true) ?? $result;
        }
        return $result;
    }

    /**
     * the calculator editor of the PV lifetime balance: like the calculator page the editor gets
     * its data from the phraseData api, which the page embeds as the seed of the editor script; the
     * page is written to the use case folder next to the hand made calculator_editor_sample.html
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_calculator_editor(test_cleanup $t): void
    {
        $this->assert_editor_view($t, views::CALCULATOR_EDITOR_NAME, test_files::CALCULATOR_EDITOR);
    }

    /**
     * the formula editor of the PV lifetime balance: like the calculator editor it gets the phrase
     * data of the page phrase with the stored results as the seed of its script; the page is
     * written to the use case folder next to the hand made formula_editor_sample.html
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_formula_editor(test_cleanup $t): void
    {
        $this->assert_editor_view($t, views::FORMULA_EDITOR_NAME, test_files::FORMULA_EDITOR);
    }

    /**
     * the table editor of the imported use case table_editor_sample.json: the script builds the editor and
     * the table from the phrase data of sector of revenue, which the page embeds as the seed; the page is
     * written to the use case folder next to the hand made table_editor_sample.html and the differences of
     * the two are reported as a browser shows them, because the html of both is only the host and the json;
     * the global warming solutions of the start page are the case of a table with own columns, but without
     * table parameters, so that the editor starts with its defaults, and with more rows than one page
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_table_editor(test_cleanup $t): void
    {
        $this->assert_editor_view($t, views::TABLE_EDITOR_NAME, test_files::TABLE_EDITOR, triple_names::SECTOR_OF_REVENUE);
        $this->assert_sample_diff($t, test_files::TABLE_EDITOR_SAMPLE, test_files::TABLE_EDITOR,
            test_files::TABLE_EDITOR_DIFF, test_files::TABLE_EDITOR_CASES, true);
        $this->assert_editor_view($t, views::TABLE_EDITOR_NAME, test_files::TABLE_EDITOR_GLOBAL_WARMING_SOLUTION,
            triple_names::GLOBAL_WARMING_SOLUTION);
    }

    /**
     * @param test_cleanup $t the test environment with the test users
     * @param string $view_name the name of the editor view e.g. views::FORMULA_EDITOR_NAME
     * @param string $file_name the name of the snapshot in the use case folder e.g. test_files::FORMULA_EDITOR
     * @param string $phr_name the name of the page phrase, by default the example PV installation of the calculator editors
     * @return void
     */
    private function assert_editor_view(
        test_cleanup $t,
        string       $view_name,
        string       $file_name,
        string       $phr_name = triple_names::EXAMPLE_PV_INSTALLATION
    ): void
    {
        $t->assert_view_by_name($view_name, $t->usr1, new triple($t->usr1),
            $phr_name, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . $file_name);
    }

    /**
     * the CBAM calculator use case brings its own view as well; the page is written to the use
     * case folder next to the hand made sample page (CBAM_tree_calculator_sample.html)
     *
     * @param test_cleanup $t the test environment with the test users
     * @param data_object_ui $cfg the stable test context of context()
     * @return void
     */
    function run_cbam_tree_calculator(test_cleanup $t, data_object_ui $cfg): void
    {
        $t->assert_view_by_name(views::CBAM_TREE_CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_TUBE_IMPORT, $cfg, test_paths::HTML . test_paths::USE_CASES . cfg_files::CBAM_TREE_CALCULATOR);
    }

    /**
     * the portfolio split use case like the PV lifetime balance: the system view Calculator shows
     * the example portfolio split by the calculator triples of portfolio split; the page is written
     * to the use case folder next to the hand made sample page (portfolio_split_calculator_sample.html),
     * so that the generated page can be compared with the target layout
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_portfolio_split_calculator(test_cleanup $t): void
    {
        // sc=0 asks for the page of the server, which shows the page also without javascript
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_PORTFOLIO_SPLIT, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::PORTFOLIO_SPLIT_CALCULATOR_NO_JS,
            [url_var::JAVASCRIPT => url_var::FALSE]);
        // by default the browser builds the same page from the json data of the page
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_PORTFOLIO_SPLIT, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::PORTFOLIO_SPLIT_CALCULATOR_JS);
    }

    /**
     * the csv export of the table of the global warming solutions through the view of the page, and the
     * differences to the expected csv as a report
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_global_warming_solutions_csv(test_cleanup $t): void
    {
        $ctd = new csv_table_diff();
        $row_col = word_names::SOLUTION;

        $test_name = 'the compare reads a cell by the name of its row and column';
        $csv_txt = "rank,solution,gain\n1,wind,1.5\n";
        $cells = $ctd->table_cells($csv_txt, $row_col);
        $t->assert($test_name, $cells, ['wind' => ['rank' => '1', 'gain' => '1.5']]);
        $test_name = '... and keeps two rows with the same name';
        $same_cells = $ctd->table_cells("solution,gain\nwind,1\nwind,2\n", $row_col);
        $t->assert($test_name, array_keys($same_cells), ['wind', 'wind' . csv_table_diff::SAME_NAME_SEP . '2']);
        $test_name = 'the same number in another format is no difference';
        $format_cells = $ctd->table_cells("solution,gain\nwind,1.78e+03\n", $row_col);
        $number_cells = $ctd->table_cells("solution,gain\nwind,1780\n", $row_col);
        $t->assert_text_contains($test_name, $ctd->report($format_cells, $number_cells, 'a', 'b'), 'Different cells (0)');
        // negative: a rounded number stays a difference
        $test_name = '... but a rounded number is one';
        $full_cells = $ctd->table_cells("solution,gain\nwind,1784.3\n", $row_col);
        $t->assert_text_contains($test_name, $ctd->report($number_cells, $full_cells, 'a', 'b'), 'Different cells (1)');
        $test_name = 'a missing row is reported';
        $none_cells = $ctd->table_cells("solution,gain\n", $row_col);
        $t->assert_text_contains($test_name, $ctd->report($number_cells, $none_cells, 'a', 'b'), 'Rows missing in the export (1)');

        // through the view, so that the url var, the component and the table are tested together
        $cfg = $t->view_context();
        $t->assert_view_by_name(views::TABLE_NAME, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_WARMING_SOLUTION,
            $cfg, '', [url_var::DISPLAY_LIST_CSV => url_var::TRUE]);
        $export_csv = $cfg->table_csv;
        $test_name = 'the table of the global warming solutions is exported as csv';
        $t->assert_text_contains($test_name, $export_csv, word_names::SOLUTION);

        $target = $ctd->table_cells($t->path_file(test_files::GLOBAL_WARMING_SOLUTIONS_CSV), $row_col);
        $export = $ctd->table_cells($export_csv, $row_col);
        // a result names the solution but not the page phrase and is still in the row of the solution
        $test_name = 'the export has the calculated gain of a solution';
        $t->assert($test_name, ($export['solar energy']['potential gain in trillion EUR'] ?? '') != '', true);
        // negative: a solution without a calculated gain would be a row with an empty cell
        $test_name = '... and the calculated reduction potential of a solution without a stated one';
        $t->assert($test_name, ($export['coal phase-out']['reduction potential in gigatonne CO2eq per year'] ?? '') != '', true);
        // the gain per effort uses the calculated gain of this table, the numbers are the calc-validation of the json
        $eur_col = 'gain per effort in billion EUR per person year';
        $htp_col = 'gain per effort in million htp per person year';
        foreach ([
                     ['circular economy', 89.11, 3.56],
                     ['digitalization', 29.7, 1.19],
                     ['coal phase-out', 594.63, 23.75],
                 ] as [$row, $eur, $htp]) {
            $test_name = 'the gain per effort of ' . $row . ' in EUR uses the calculated gain';
            $t->assert($test_name, round((float)($export[$row][$eur_col] ?? 0), 2), $eur);
            $test_name = 'the gain per effort of ' . $row . ' in htp uses the calculated gain';
            $t->assert($test_name, round((float)($export[$row][$htp_col] ?? 0), 2), $htp);
        }
        // problem_global_warming.json states its numbers for "<solution> (climate solution)", so
        // a row of this table has one number per fact and the effort of this table
        $test_name = 'a solution of both files has one potential gain';
        $t->assert($test_name, round((float)($export['circular economy']['potential gain in trillion EUR'] ?? 0), 6), 4.455275);
        $test_name = '... and the initial effort of this table';
        $t->assert($test_name, $export['circular economy']['initial effort in person year'] ?? '', '50');
        // negative: the older gain of the same solution is no second number of the cell
        $test_name = '... and no second number in the cell';
        $t->assert($test_name, str_contains($export['solar energy']['potential gain in trillion EUR'] ?? '', value_list_ui::CSV_NUMBER_SEPARATOR), false);
        // negative: the assumed gain of problem_global_warming.json in the same row is not used
        $test_name = 'the gain per effort does not use the assumed gain of the same solution';
        $t->assert($test_name, round((float)($export['coal phase-out'][$htp_col] ?? 0), 2) != 533.19, true);
        $test_name = 'the differences of the export of the global warming solutions are reported';
        $report = $ctd->report($target, $export, basename(test_files::GLOBAL_WARMING_SOLUTIONS_CSV), triple_names::GLOBAL_WARMING_SOLUTION);
        $t->assert_file($test_name, $report, test_files::GLOBAL_WARMING_SOLUTIONS_DIFF, test_files::MD);
    }

    /**
     * the own columns and the own sorting of the table of the populism solutions, read from its csv export
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_populism_solutions_table(test_cleanup $t): void
    {
        $ctd = new csv_table_diff();
        $eur_col = 'potential gain in trillion EUR';
        $own_cols = [$eur_col, 'potential gain in billion htp', 'initial effort in person year'];

        $cfg = $t->view_context();
        $t->assert_view_by_name(views::TABLE_NAME, $t->usr1, new triple($t->usr1), triple_names::POPULISM_SOLUTION,
            $cfg, '', [url_var::DISPLAY_LIST_CSV => url_var::TRUE]);
        $export_csv = $cfg->table_csv;
        $export = $ctd->table_cells($export_csv, word_names::SOLUTION);
        $first_row = reset($export) ?: [];
        $test_name = 'the table of the populism solutions shows its own columns only';
        $t->assert($test_name, array_keys($first_row), $own_cols);
        // negative: the loss reduction fits no own column, so it is hidden without the url asking for it
        $test_name = '... and not the loss reduction';
        $t->assert_text_not_contains($test_name, $export_csv, triple_names::LOSS_REDUCTION);
        $gains = array_map('floatval', array_column($export, $eur_col));
        $sorted = $gains;
        rsort($sorted);
        $test_name = 'the rows are sorted by the biggest potential gain';
        $t->assert($test_name, $gains, $sorted);
        // negative: a table with one row or without numbers would pass the sort check
        $test_name = '... of every solution';
        $row_count = count($gains);
        $t->assert_greater($test_name, 1, $row_count);

        // the missing operands are loaded with one paged call, which stays complete also if the
        // values of all missing phrases together exceed the row limit of the backend
        $msg = new user_message();
        $effort_lst = new phrase_list_ui();
        $both_lst = new phrase_list_ui();
        foreach ($cfg->res_lst->lst() as $res) {
            $ctx_lst = $res->src_grp?->phr_lst();
            $effort = $ctx_lst?->get_by_name(triple_names::INITIAL_EFFORT, $msg);
            $factor = $ctx_lst?->get_by_name(triple_names::PERSON_YEAR_PER_BILLION_USD, $msg);
            if ($effort != null and $factor != null and $effort_lst->is_empty()) {
                $effort_lst->add_phrase($effort);
                $both_lst->add_phrase($effort);
                $both_lst->add_phrase($factor);
            }
        }
        $test_name = 'the values of the initial effort are loaded with the other missing operands';
        $effort_count = $this->values_with_names($cfg, [triple_names::INITIAL_EFFORT]);
        $t->assert_greater($test_name, 0, $effort_count);
        // negative: no missing phrase gets a call of its own
        $test_name = '... and not with a call of their own';
        $has_own_call = $cfg->has_values_of($effort_lst);
        $t->assert_false($test_name, $has_own_call);

        // with the per phrase switch each missing operand of the table is loaded with a call of its own
        $per_cfg = $t->view_context();
        $tbl_phr = new triple_ui();
        $tbl_phr->load_by_name(triple_names::POPULISM_SOLUTION, $msg);
        $per_typ = new api_type_list([api_types::LOAD_PER_PHRASE]);
        new ui_list()->table_with_related_columns($tbl_phr, $msg, $per_cfg, typ_lst: $per_typ);
        $test_name = 'with the per phrase switch the initial effort is loaded with a call of its own';
        $has_per_call = $per_cfg->has_values_of($effort_lst);
        $t->assert_true($test_name, $has_per_call);
        // negative: the switch never combines two missing phrases
        $test_name = '... and not together with the person year per billion USD';
        $has_both_call = $per_cfg->has_values_of($both_lst);
        $t->assert_false($test_name, $has_both_call);
    }

    /**
     * @param data_object_ui $cfg the request cache of a page
     * @param array $names the phrase names that a counted value has all
     * @return int the number of values in the cache that have all given phrases
     */
    private function values_with_names(data_object_ui $cfg, array $names): int
    {
        $result = 0;
        foreach ($cfg->val_lst->lst() as $val) {
            if (array_diff($names, $val->phr_lst()->names()) == []) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * the assumption fields of a populism solution load the values of the context of each result
     * with one paged call, and with api_types::LOAD_PER_PHRASE with a call per phrase
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_context_values_per_phrase(test_cleanup $t): void
    {
        $msg = new user_message();
        $cfg = $t->view_context();
        $sol = new triple_ui();
        $sol->load_by_name(triple_names::ONE_CLICK_HIDE_POPULISM, $msg);
        new ui_list()->assumption_fields($sol, $msg, $cfg, '', []);
        $effort_lst = new phrase_list_ui();
        $both_lst = new phrase_list_ui();
        foreach ($cfg->res_lst->lst() as $res) {
            $ctx_lst = $res->src_grp?->phr_lst();
            $effort = $ctx_lst?->get_by_name(triple_names::INITIAL_EFFORT, $msg);
            $sol_phr = $ctx_lst?->get_by_name(triple_names::ONE_CLICK_HIDE_POPULISM, $msg);
            if ($effort != null and $sol_phr != null and $effort_lst->is_empty()) {
                $effort_lst->add_phrase($effort);
                $both_lst->add_phrase($effort);
                $both_lst->add_phrase($sol_phr);
            }
        }
        $test_name = 'the context values of a result are loaded with one call';
        $effort_count = $this->values_with_names($cfg, [triple_names::INITIAL_EFFORT, triple_names::ONE_CLICK_HIDE_POPULISM]);
        $t->assert_greater($test_name, 0, $effort_count);
        // negative: no phrase of the context gets a call of its own
        $test_name = '... and not with a call per phrase';
        $has_own_call = $cfg->has_values_of($effort_lst);
        $t->assert_false($test_name, $has_own_call);

        // with the per phrase switch each phrase of the context is loaded with a call of its own
        $per_cfg = $t->view_context();
        $per_typ = new api_type_list([api_types::LOAD_PER_PHRASE]);
        new ui_list()->assumption_fields($sol, $msg, $per_cfg, '', [], [], $per_typ);
        $test_name = 'with the per phrase switch the context values are loaded with a call per phrase';
        $has_per_call = $per_cfg->has_values_of($effort_lst);
        $t->assert_true($test_name, $has_per_call);
        // negative: the switch never combines two phrases of the context
        $test_name = '... and not with one call for the phrases of the context';
        $has_both_call = $per_cfg->has_values_of($both_lst);
        $t->assert_false($test_name, $has_both_call);
    }

    /**
     * the potential gain of a global warming solution is calculated from the results of the avoided
     * emission, which the table data has already, and the social cost of carbon in EUR per tonne,
     * which the table lacks, so the table loads the results of this missing operand for the formula
     * tooltip, although the operand names only a unit
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_result_operands(test_cleanup $t): void
    {
        $msg = new user_message();
        $cfg = $t->view_context();
        $tbl_phr = new triple_ui();
        $tbl_phr->load_by_name(triple_names::GLOBAL_WARMING_SOLUTION, $msg);
        // the steps of the table and each api call are logged with their seconds, so that the
        // load of the results of the missing operands can be followed in the test log
        $t->step('result operands step: create the table of ' . triple_names::GLOBAL_WARMING_SOLUTION);
        $debug_before = $t->set_debug(url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
        $tbl_html = new ui_list()->table_with_related_columns($tbl_phr, $msg, $cfg);
        $t->set_debug($debug_before);
        $t->step('result operands step: table created with ' . $cfg->res_lst->count() . ' results in the cache');
        $operand = new triple_ui();
        $operand->load_by_name(triple_names::EUR_PER_TONNE, $msg);
        $operand_id = $operand->phrase()->id();
        // the formula names its terms in quotes, which the tooltip escapes
        $operand_tip = htmlspecialchars('"' . triple_names::AVOIDED_EMISSION . '"');
        $test_name = 'the results of an operand that is a unit and a result are loaded for the table';
        $has_operand = $cfg->has_results_within($operand_id, []);
        $t->assert_true($test_name, $has_operand);
        $test_name = '... so the potential gain has the formula with its numbers as tooltip';
        $t->assert_text_contains($test_name, $tbl_html, $operand_tip);
        // negative: a unit says how a number is stated, so its results are not loaded
        $test_name = '... but not the results of a unit';
        $has_unit = $cfg->has_results_within(word_names::TRILLION_ID, []);
        $t->assert_false($test_name, $has_unit);
    }

    /**
     * the steps of the start page with the more list, the columns without the minor tiers and the
     * variance, which loads the values and the results of each missing operand with a call per
     * phrase; the pod ends a page without a message above its memory limit, so each step is also
     * checked against that limit and not only for its result
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_start_page_steps(test_cleanup $t): void
    {
        $msg = new user_message();
        $cfg = $t->view_context();
        $url_array = [
            url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST,
            url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_EX_MINOR,
            url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE];
        $tbl_phr = new triple_ui();
        $tbl_phr->load_by_name(triple_names::GLOBAL_PROBLEM, $msg);
        $effort = new triple_ui();
        $effort->load_by_name(triple_names::INITIAL_EFFORT, $msg);
        $effort_id = $effort->phrase()->id();
        $effort_lst = new phrase_list_ui();
        $effort_lst->add_phrase($effort->phrase());

        // the biggest answer of the page and the last one before the page stops
        $test_name = 'the results of the initial effort are loaded';
        $t->step('start page step: load the results of ' . triple_names::INITIAL_EFFORT);
        $start_mem = memory_get_usage();
        memory_reset_peak_usage();
        $res_lst = new result_list_ui();
        $res_lst->load_by_phrase_list($effort_lst, $msg);
        $res_mem = memory_get_peak_usage() - $start_mem;
        $res_count = $res_lst->count();
        $t->step('start page step: ' . $res_count . ' results loaded with ' . $res_mem . ' bytes');
        $t->assert_greater($test_name, 0, $res_count, $t::TIMEOUT_LIMIT_REST);
        $test_name = '... within the memory limit of the pod';
        $t->assert_below($test_name, $t::POD_MEMORY_LIMIT, $res_mem);

        // the table loads the values and then the results of each missing operand one by one
        $test_name = 'the start page table loads the results of the initial effort';
        $t->step('start page step: create the table of ' . triple_names::GLOBAL_PROBLEM);
        $start_mem = memory_get_usage();
        memory_reset_peak_usage();
        // the steps of the table and each api call are logged with their seconds, so that a slow step is visible
        $debug_before = $t->set_debug(url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
        new ui_list()->table_with_related_columns($tbl_phr, $msg, $cfg, false, true, $url_array);
        $t->set_debug($debug_before);
        $tbl_mem = memory_get_peak_usage() - $start_mem;
        $t->step('start page step: table created with ' . $tbl_mem . ' bytes');
        $has_effort = $cfg->has_results_within($effort_id, []);
        $t->assert_true($test_name, $has_effort, $t::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... within the memory limit of the pod';
        $t->assert_below($test_name, $t::POD_MEMORY_LIMIT, $tbl_mem);
        // only the results that the calculation of the table can use, not all results of the phrase
        $test_name = '... but only the results within the context of the table';
        $tbl_effort_count = $this->results_with_phrase($cfg, $effort_id);
        $t->step('start page step: ' . $tbl_effort_count . ' results of ' . triple_names::INITIAL_EFFORT . ' kept');
        $t->assert_below($test_name, $res_count, $tbl_effort_count);
        // negative: not all results of the phrase are loaded
        $test_name = '... and not all results of the initial effort';
        $has_all_effort = $cfg->has_results_of($effort_id);
        $t->assert_false($test_name, $has_all_effort);
        // negative: a unit says how a number is stated, so its results are not loaded
        $test_name = '... but not the results of a unit';
        $has_unit = $cfg->has_results_within(word_names::TRILLION_ID, []);
        $t->assert_false($test_name, $has_unit);
    }

    /**
     * @param data_object_ui $cfg the request cache of a page
     * @param int $phr_id the id of a phrase
     * @return int the number of results in the cache that have the phrase
     */
    private function results_with_phrase(data_object_ui $cfg, int $phr_id): int
    {
        $result = 0;
        foreach ($cfg->res_lst->lst() as $res) {
            if (in_array($phr_id, $res->phr_lst()->ids())) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * the Swiss NPP vote page without javascript and as the browser builds it and the differences of the
     * page without javascript to the hand made sample as a report, which names what the page still misses
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_npp_ch_vote(test_cleanup $t): void
    {
        // sc=0 asks for the page of the server, which shows the page also without javascript
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::NEW_NPP_VOTE, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::NPP_CH_VOTE_NO_JS,
            [url_var::JAVASCRIPT => url_var::FALSE]);
        // by default the browser builds the same page from the json data of the page
        $t->assert_view_by_name(views::CALCULATOR_NAME, $t->usr1, new triple($t->usr1),
            triple_names::NEW_NPP_VOTE, $t->view_context(),
            test_paths::HTML . test_paths::USE_CASES . test_files::NPP_CH_VOTE_JS);
        $this->assert_sample_diff($t, test_files::NPP_CH_VOTE_SAMPLE, test_files::NPP_CH_VOTE_NO_JS,
            test_files::NPP_CH_VOTE_DIFF, test_files::NPP_CH_VOTE_CASES);
    }

    /**
     * the calculator editor of the Swiss NPP vote and the differences of its page preview to the calculator page
     * as the browser builds it (run_npp_ch_vote), because the preview should show the same page; both are read
     * after their script has run in a headless browser, because the html of both holds only the json data, and
     * with the sensitivity table opened, because both calculate its cells only while it is open
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_npp_ch_vote_editor(test_cleanup $t): void
    {
        $this->assert_editor_view($t, views::CALCULATOR_EDITOR_NAME, test_files::NPP_CH_VOTE_EDITOR,
            triple_names::NEW_NPP_VOTE);
        $hpd = new html_page_diff();
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
        $page_file = test_files::NPP_CH_VOTE_JS . test_files::HTML;
        $editor_file = test_files::NPP_CH_VOTE_EDITOR . test_files::HTML;
        $editor_snapshot = $t->path_file($dir . $editor_file);
        $page_html = $this->sample_in_browser($t->path_file($dir . $page_file), test_files::NPP_CH_VOTE_SENSITIVITY_CASES);
        $editor_html = $this->sample_in_browser($editor_snapshot, test_files::NPP_CH_VOTE_SENSITIVITY_CASES);
        $closed_html = $this->sample_in_browser($editor_snapshot, test_files::NPP_CH_VOTE_CASES);
        if ($page_html === null or $editor_html === null or $closed_html === null) {
            $t->dsp_warning('no headless browser found, so the differences of the preview of ' . $editor_file . ' are not reported');
        } else {
            $preview_html = $hpd->element_page($editor_html, self::EDITOR_PREVIEW_ID);
            $preview = $hpd->page_items($preview_html);
            $page = $hpd->page_items($page_html);
            $test_name = 'the page preview of ' . $editor_file . ' has items to compare';
            $t->assert_true($test_name, count($preview) > 0);
            // negative: the page has no preview, so a part that is not found is never compared as the whole page
            $test_name = '... but the page itself has no preview';
            $no_preview = $hpd->element_page($page_html, self::EDITOR_PREVIEW_ID);
            $t->assert($test_name, $no_preview, '');
            $this->assert_verdict_items($t, $hpd);
            // the opened sensitivity table calculates the page again per cell, also with other numbers than the inputs
            $test_name = 'the opened sensitivity table of the preview has the cell of the inputs';
            $t->assert_text_contains($test_name, $preview_html, self::NPP_SENSITIVITY_INPUT_CELL);
            $test_name = '... and a cell of other numbers';
            $t->assert_text_contains($test_name, $preview_html, self::NPP_SENSITIVITY_OTHER_CELL);
            // negative: the closed table calculates nothing
            $test_name = '... but the closed table has no cells';
            $closed_preview = $hpd->element_page($closed_html, self::EDITOR_PREVIEW_ID);
            $t->assert_text_not_contains($test_name, $closed_preview, self::NPP_SENSITIVITY_INPUT_CELL);
            $test_name = 'the differences of the preview of ' . $editor_file . ' to the page are reported';
            $report = $hpd->report($page, $preview, $page_file, $editor_file, html_page_diff::PROMPT_PREVIEW);
            $t->assert_file($test_name, $report, $dir . test_files::NPP_CH_VOTE_EDITOR_DIFF . test_files::MD, test_files::MD);
        }
    }

    /**
     * the verdict is named by its headline, so that the same state with another text is a difference
     *
     * @param test_cleanup $t the test environment with the test users
     * @param html_page_diff $hpd the compare that reads the items of a page
     * @return void
     */
    private function assert_verdict_items(test_cleanup $t, html_page_diff $hpd): void
    {
        $test_name = 'the compare reads a verdict by its headline';
        $verdict_html = '<main><div class="verdict open"><p><span class="headline">Undecided</span></p><p>%s</p></div></main>';
        $verdict = $hpd->page_items(sprintf($verdict_html, 'within 0.25'));
        $verdict_key = html_page_diff::KIND_VERDICT . ': Undecided';
        $t->assert_key_exists($test_name, $verdict_key, $verdict);
        $test_name = '... the same with white space between its paragraphs';
        $spaced_verdict = $hpd->page_items(str_replace('</p><p>', "</p>\n <p>", sprintf($verdict_html, 'within 0.25')));
        $t->assert($test_name, $spaced_verdict, $verdict);
        // negative: another text of the same verdict stays a difference
        $test_name = '... but another text of it is a difference';
        $other_verdict = $hpd->page_items(sprintf($verdict_html, 'within 0.5'));
        $t->assert_false($test_name, $verdict == $other_verdict);
    }

    /**
     * the pdf of the calculator editor fills the cells of the sensitivity table of the Swiss NPP vote page,
     * which the page leaves to its script: per cell the page is calculated again with the numbers of the
     * row and of the column, and afterward each number of the page is set back
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_npp_sensitivity(test_cleanup $t): void
    {
        $msg = new user_message();
        $trp = new triple_ui();
        $trp->load_by_name(triple_names::NEW_NPP_VOTE, $msg);
        $json = new phrase_data_ui()->load_from_walk($trp->phrase(), $msg);
        $def_rpt = new calculator_definition_report();
        $dto = $def_rpt->page_cache($json, $msg);
        $dbo = $dto->phr_lst->get_by_name(triple_names::NEW_NPP_VOTE, $msg)?->obj();
        $page = new system_page();
        $plain_before = $page->calculation_result($dbo, $msg, $dto);
        $filled = $page->calculation_result($dbo, $msg, $dto, true);
        $plain_after = $page->calculation_result($dbo, $msg, $dto);
        $test_name = 'the pdf fills the sensitivity cell of the numbers of the inputs like the main results';
        $t->assert_text_contains($test_name, $filled, self::NPP_SENSITIVITY_INPUT_CELL, $t::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... and a cell of other numbers by calculating the page again';
        $t->assert_text_contains($test_name, $filled, self::NPP_SENSITIVITY_OTHER_CELL);
        // negative: the page leaves the cells to its script, because each cell calculates the page again
        $test_name = '... but the page leaves the cells empty';
        $t->assert_text_not_contains($test_name, $plain_before, self::NPP_SENSITIVITY_INPUT_CELL);
        $test_name = '... and each number of the page is set back after the cells';
        $t->assert($test_name, $plain_after, $plain_before);
    }

    /**
     * the differences of the portfolio split page without javascript to the hand made sample
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_portfolio_split_calculator_diff(test_cleanup $t): void
    {
        $this->assert_sample_diff($t, test_files::PORTFOLIO_SPLIT_CALCULATOR_SAMPLE,
            test_files::PORTFOLIO_SPLIT_CALCULATOR_NO_JS, test_files::PORTFOLIO_SPLIT_CALCULATOR_DIFF,
            test_files::PORTFOLIO_SPLIT_CALCULATOR_CASES);
    }

    /**
     * the portfolio split page that the browser builds and the hand made sample get the same input case typed in;
     * a field that only one page has is listed in the report instead of failing the test, because the two pages
     * do not have the same fields yet; the actions run in a headless browser, so without one they are skipped
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_portfolio_split_calculator_cases(test_cleanup $t): void
    {
        $brw = new headless_browser();
        $browser_name = $brw->start();
        if ($browser_name == '') {
            $t->dsp_warning('no headless browser found, so the portfolio split input cases are skipped');
        } else {
            $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
            $sample_html = $t->path_file($dir . test_files::PORTFOLIO_SPLIT_CALCULATOR_SAMPLE . test_files::HTML);
            $page_html = $t->path_file($dir . test_files::PORTFOLIO_SPLIT_CALCULATOR_JS . test_files::HTML);
            $sample = $brw->page_copy($sample_html, test_files::PORTFOLIO_SPLIT_CALCULATOR_CASES, test_files::USE_CASE_CASES);
            $page = $brw->page_copy($page_html, test_files::PORTFOLIO_SPLIT_CALCULATOR_CASES, test_files::USE_CASE_CASES);
            foreach (test_files::PORTFOLIO_CASES as $case) {
                $this->assert_sample_case($t, $brw, $sample, $page, test_files::PORTFOLIO_SPLIT_CALCULATOR_SAMPLE,
                    test_files::PORTFOLIO_SPLIT_CALCULATOR_JS, test_files::PORTFOLIO_CASE_DIFF_PREFIX, $case, true);
            }
            unlink($sample);
            unlink($page);
        }
        $brw->stop();
    }

    /**
     * the pages of the frontend that are requested via http from the local pod
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_web_frontend(test_cleanup $t): void
    {
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit web frontend ';
        $t->header($ts);

        $html = new html_base();
        $target = htmlspecialchars(trim('<title>Header test - zukunft.com</title>'));
        $result = htmlspecialchars(trim($html->header('Header test', $msg, 'center_form')));
        $t->dsp_contains(", dsp_header", $target, $result);

        // check if the about page contains at least some basic keywords
        // TODO Prio 3 activate: $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
        $target = 'zukunft.com AG';
        if (strpos($result, $target) > 0) {
            $result = $target;
        } else {
            $result = '';
        }
        // about does not return a page for unknown reasons at the moment
        // $t->dsp_contains(', frontend about.php '.$result.' contains at least ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $is_connected = $t->dsp_web_test(
            api::SCRIPT_PATH_NAME . 'privacy_policy.html',
            'Swiss purpose of data protection',
            ', frontend privacy_policy.php contains at least');
        // the error update view is a get action mask; a request without the anti-csrf session token
        // no longer runs the action (see frontend::request_token_valid + http/view.php, the action is
        // gated on the token), so an ip user without a login is shown the 'log in to change' message
        // instead of resolving the error - the change is never applied
        global $mtr;
        $is_connected = $t->dsp_web_test(
            api::SCRIPT_PATH_NAME . 'view.php?' . url_var::MASK . url_var::EQ . views::ERROR_UPDATE_ID
            . url_var::ADD_ID . 1,
            $mtr->txt(msg_id::CHANGE_BLOCKED_FOR_IP_USER),
            ', frontend view.php?m=error_update without a token contains at least', $is_connected);
        // an add view changes data, so a user without login is blocked before the view is created
        // (config.yaml: system configuration > system > pod > permissions > database change > ip user > allowed)
        $is_connected = $t->dsp_web_test(
            api::SCRIPT_PATH_NAME . 'view.php?' . url_var::MASK . url_var::EQ . views::WORD_ADD_DETAIL_ID,
            $mtr->txt(msg_id::CHANGE_BLOCKED_FOR_IP_USER),
            ', frontend view.php?m=word_add contains at least', $is_connected);
        // the former find.php has been replaced by the word find view (m=67)
        // fetching the full word find view over http renders a complete page, so a long timeout is used
        $t->dsp_web_test(
            api::MAIN_SCRIPT_EXT . url_var::PAR . url_var::MASK . url_var::EQ . views::WORD_FIND_ID
            . url_var::ADD . url_var::PATTERN_HUMAN . url_var::EQ . word_names::ABB,
            word_names::ABB,
            ', frontend view.php for the word find view contains at least', $is_connected, $t::TIMEOUT_LIMIT_LONG);
    }

}
