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
include_once test_paths::UTILS . 'headless_browser.php';
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
use Zukunft\ZukunftCom\test\php\utils\headless_browser;
use Zukunft\ZukunftCom\test\php\utils\html_page_diff;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class system_views_read_tests
{

    // the element of pv_lifetime_balance_cases.js with the result of a case as json and its two fields
    const string CASE_RESULT_ID = 'caseResult';
    const string CASE_MISSING = 'missing';
    const string CASE_PAGE = 'page';
    // the field of the case test_files::PV_CASE_MISSING_FIELD that no page has
    const string CASE_UNKNOWN_FIELD = 'Wind turbine size';
    // the missing field of a case that has not ended in the browser e.g. because the page script has failed
    const string CASE_NOT_ENDED = 'the case has not ended in the headless browser';
    // the row of the PV calculator page whose numbers every input case changes
    const string PV_PRODUCTION_ROW = html_page_diff::KIND_ROW . ': Electricity / PV production';

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
        $this->run_cbam_tree_calculator($t, $cfg);
        $this->run_portfolio_split_calculator($t);

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
        $t->assert_view(views::START_CODE, $t->usr1, new triple($t->usr1), triple_names::GLOBAL_PROBLEM_ID, $cfg);
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
        $test_name = 'the start page links the pinned views of config.yaml';
        $start_page = file_get_contents(test_files::START_PAGE);
        $t->assert_file($test_name, $html->start_page($start_page, $pin_lst, new user_message()), test_files::START_PAGE);
        $test_name = 'a pinned view without url opens the view of its view name with its phrase';
        $pin_by_names = [triples::VIEW_NAME => views::CALCULATOR_NAME, triples::PHRASE_NAME => triple_names::EXAMPLE_PV_INSTALLATION];
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $expected = $html->page_url([url_var::MASK => views::CALCULATOR_ID, url_var::ID => $phr->id()]);
        $t->assert($test_name, $this->pinned_url($t, $pin_by_names, $msg), $expected);
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
        if ($this->is_by_names($pin)) {
            $msk = new view($t->usr1);
            $msk->load_by_name($pin[triples::VIEW_NAME], $msg);
            $phr = new phrase($t->usr1);
            $phr->load_by_name($pin[triples::PHRASE_NAME], $msg);
            // the phrase id of a triple is negative, so the view shows the triple
            $url = new html_base()->page_url([url_var::MASK => $msk->id(), url_var::ID => $phr->id()]);
        }
        return $url;
    }

    /**
     * @param array $pin the url or the view name and the phrase name of a pinned view of config.yaml
     * @return bool true if the pinned view has no url but a view name and a phrase name
     */
    private function is_by_names(array $pin): bool
    {
        return ($pin[words::URL] ?? '') == ''
            and ($pin[triples::VIEW_NAME] ?? '') != ''
            and ($pin[triples::PHRASE_NAME] ?? '') != '';
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

        // the table editor placeholder shows only its title and the phrase selector
        $t->assert_view_by_name(views::TABLE_EDITOR_NAME, $t->usr1, new triple($t->usr1), triple_names::PV_IN_SWITZERLAND, $cfg);

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
        $hpd = new html_page_diff();
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES;
        $sample_file = test_files::PV_LIFETIME_BALANCE_SAMPLE . test_files::HTML;
        $page_file = test_files::PV_LIFETIME_BALANCE_NO_JS . test_files::HTML;
        $sample = $hpd->page_items($t->path_file($dir . $sample_file));
        $page = $hpd->page_items($t->path_file($dir . $page_file));

        $test_name = 'the sample of the PV calculator page has items to compare';
        $t->assert_true($test_name, count($sample) > 0);
        // negative: a page compared with itself has no difference, so every finding comes from the pages
        $test_name = '... and a page compared with itself has no difference';
        $self_report = $hpd->report($sample, $sample, $sample_file, $sample_file);
        $t->assert_text_contains($test_name, $self_report, 'Missing in the generated page (0)');
        $t->assert_text_contains($test_name, $self_report, 'Different (0)');
        $test_name = 'the differences of the generated PV calculator page to the sample are reported';
        $t->assert_file($test_name, $hpd->report($sample, $page, $sample_file, $page_file),
            $dir . test_files::PV_LIFETIME_BALANCE_DIFF . test_files::MD, test_files::MD);
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
            $sample = $brw->page_copy($sample_html, test_files::PV_LIFETIME_BALANCE_CASES);
            $page = $brw->page_copy($page_html, test_files::PV_LIFETIME_BALANCE_CASES);
            $std_cells = '';
            foreach (test_files::PV_CASES as $case) {
                $cells = $this->assert_pv_case($t, $brw, $sample, $page, $dir, $case);
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
     * @param string $dir the use case folder of the snapshots, to which the report of the case is written
     * @param string $case the name of the case e.g. test_files::PV_CASE_HIGH
     * @return string the numbers of the PV production row of the generated page after the case
     */
    private function assert_pv_case(
        test_cleanup     $t,
        headless_browser $brw,
        string           $sample,
        string           $page,
        string           $dir,
        string           $case
    ): string
    {
        $hpd = new html_page_diff();
        $sample_res = $this->pv_case($brw, $sample, $case);
        $page_res = $this->pv_case($brw, $page, $case);
        $test_name = 'the ' . $case . ' case types every number into the sample';
        $sample_missing = $sample_res[self::CASE_MISSING];
        $t->assert($test_name, $sample_missing, []);
        $test_name = '... and into the generated page';
        $page_missing = $page_res[self::CASE_MISSING];
        $t->assert($test_name, $page_missing, []);
        $sample_items = $hpd->page_items($sample_res[self::CASE_PAGE]);
        $page_items = $hpd->page_items($page_res[self::CASE_PAGE]);
        $sample_name = test_files::PV_LIFETIME_BALANCE_SAMPLE . test_files::HTML . '#' . $case;
        $page_name = test_files::PV_LIFETIME_BALANCE_JS . test_files::HTML . '#' . $case;
        $test_name = '... and the differences of the two pages are reported';
        $t->assert_file($test_name, $hpd->report($sample_items, $page_items, $sample_name, $page_name),
            $dir . test_files::PV_CASE_DIFF_PREFIX . $case . test_files::MD, test_files::MD);
        $row = $page_items[self::PV_PRODUCTION_ROW] ?? [];
        return $row[html_page_diff::PROP_CELLS] ?? '';
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
     * @param test_cleanup $t the test environment with the test users
     * @param string $view_name the name of the editor view e.g. views::FORMULA_EDITOR_NAME
     * @param string $file_name the name of the snapshot in the use case folder e.g. test_files::FORMULA_EDITOR
     * @return void
     */
    private function assert_editor_view(test_cleanup $t, string $view_name, string $file_name): void
    {
        $t->assert_view_by_name($view_name, $t->usr1, new triple($t->usr1),
            triple_names::EXAMPLE_PV_INSTALLATION, $t->view_context(),
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
