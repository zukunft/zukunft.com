<?php

/*

    shared/const/triples.php - predefined triples used in the backend and frontend as code id
    ------------------------

    all preserved words must always be owned by an administrator so that the standard cannot be renamed

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'words.php';
include_once paths::TEST_CONST . 'triple_names.php';

use Zukunft\ZukunftCom\test\php\const\triple_names;

class triples
{

    // this list includes all preserved triple names
    // *_COM is the tooltip for the triple; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    // *_WD is the wikidata id

    // triple names used in the config.yaml (sorted alphabetically)
    const string API_USER = 'api user';
    const string AUTOMATIC_CREATE = 'automatic create';
    const string AVERAGE_DELAY = 'average delay';
    const string BLOCK_SIZE = 'block size';
    const string BYTES_PER_SECOND = 'bytes second';
    const string BY_IP_ADDRESS = 'by ip-address';
    const string BY_LOCATION = 'by location';
    const string CHECK_PERIOD = 'check period';
    const string DATABASE_CHANGE = 'database change';
    const string DECREASE_DAYS = 'decrease days';
    const string EMAIL_SERVER = 'email server';
    const string EXPECTED_TIME = 'expected time';
    const string FACTOR_WRONG = 'factor wrong';
    const string FILE_READ = 'file read';
    const string FILE_SIZE = 'file size';
    // the exception of use javascript for the calculator pages (see USE_JAVASCRIPT)
    const string FOR_CALCULATOR = 'for calculator';
    const string FORMULA_LIST = 'formula list';
    const string FUTURE_PERCENT = 'future percent';
    // the start page lists the children of this triple, so the frontend needs its name
    const string GLOBAL_PROBLEM = 'global problem';
    const string INCREASE_LIST = 'increase list';
    const string IP_USER = 'ip user';
    const string LINK_LIST = 'link list';
    const string MAX_CHANGE = 'max change';
    const string MAX_CHANGES = 'max changes';
    const string MAX_COLUMNS = 'max columns';
    const string MAX_DELAY = 'max delay';
    const string MAX_LEVELS = 'max levels';
    const string MAX_LIFETIME = 'max lifetime';
    const string MAX_LOGIN = 'max login';
    const string MAX_PHRASE = 'max phrase';
    const string MAX_TABLES = 'max tables';
    const string MIN_COLUMNS = 'min columns';
    const string MIN_NAMES = 'min names';
    const string MIN_NUMBERS = 'min numbers';
    const string MIN_VALUES = 'min values';
    const string NAME_LIST = 'name list';
    const string NOT_TRUSTED = 'not trusted';
    const string NUMBER_FORMAT = 'number format';
    const string OBJECTS_PER_SECOND = 'objects second';
    const string OBJECT_CREATION = 'object creation';
    const string OBJECT_STORING = 'object storing';
    const string OUTPUT_FORMAT = 'output format';
    const string PERCENT_DECIMAL = 'percent decimal';
    const string PER_DAY = 'per day';
    const string PER_MONTH = 'per month';
    const string PER_VERB = 'per verb';
    const string PER_WEEK = 'per week';
    const string PHRASE_LIST = 'phrase list';
    const string CHANGE_LOG = 'change log';
    const string PER_YEAR = 'per year';
    const string PHRASE_DISTRIBUTION = 'phrase distribution';
    // the value quality of a number taken from a peer reviewed study, shown as "+" behind the number
    const string PEER_REVIEWED = 'peer reviewed by quality journal';
    // the factor that turns the centre of an estimate into a bound of its variance
    const string PROBABILITY_RANGE_FACTOR = 'probability range factor';
    const string RESPONSE_TIME = 'response time';
    const string ROW_LIMIT = 'row limit';
    const string SIDE_WIDTH = 'side width';
    // the views linked on the start page (config.yaml system view startup)
    const string PINNED_VIEWS = 'pinned views';
    // the config.yaml keys whose sub keys are names of the data e.g. the url name of a pinned view,
    // which therefore need no word const (see coding_rule_tests::config_yaml_word_triple_tests)
    const array CONFIG_DATA_KEYS = [self::PINNED_VIEWS];
    const string SOURCE_TABLE = 'source table';
    const string START_DELAY = 'start delay';
    const string STORAGE_SIZE = 'storage size';
    const string SYSTEM_CONFIG = 'system configuration';
    const int SYSTEM_CONFIG_ID = 106;
    // the keys of config.yaml above all settings, which are also phrases of each config value
    const array CONFIG_ROOT = [words::THIS_SYSTEM, self::SYSTEM_CONFIG];
    // the settings of the pod below this path can only be changed by an admin (see value_base::is_pod_setting_denied)
    const array CONFIG_SYSTEM_PATH = [...triples::CONFIG_ROOT, words::SYSTEM];
    // the column priority of a table, defined in solution_prio.json: a mayor column is shown
    // on every screen, a main column only if the screen is not small, a minor column only if
    // it is wide and a marginal column only on the widest screen; a phrase is assigned to a
    // tier with "<phrase> can be <tier>"
    const string SYSTEM_COLUMN = 'column (system)';
    const string SYSTEM_COLUMN_CODE_ID = 'system_column';
    const string SYSTEM_COLUMN_MAYOR = 'mayor column (system)';
    const string SYSTEM_COLUMN_MAYOR_CODE_ID = 'system_column_mayor';
    const string SYSTEM_COLUMN_MAIN = 'main column (system)';
    const string SYSTEM_COLUMN_MAIN_CODE_ID = 'system_column_main';
    const string SYSTEM_COLUMN_MINOR = 'minor column (system)';
    const string SYSTEM_COLUMN_MINOR_CODE_ID = 'system_column_minor';
    const string SYSTEM_COLUMN_MARGINAL = 'marginal column (system)';
    const string SYSTEM_COLUMN_MARGINAL_CODE_ID = 'system_column_marginal';

    // the column tiers in the order they are shown, widest priority first
    const array SYSTEM_COLUMN_TIERS = [
        self::SYSTEM_COLUMN_MAYOR,
        self::SYSTEM_COLUMN_MAIN,
        self::SYSTEM_COLUMN_MINOR,
        self::SYSTEM_COLUMN_MARGINAL,
    ];
    // the charts of a table, defined in solution_prio.json like the column tiers: a chart is a
    // triple "<chart type> of <column>" e.g. "range bars of potential loss", or for two columns
    // "<chart type> of <column> and <column>", and is assigned to a chart type with "<chart> can
    // be <chart type>"; the default charts are shown beside each other if the reader asks to
    // see the table as a chart, the alternative charts can be selected instead
    const string SYSTEM_CHART_TYPE = 'chart type (system)';
    const string SYSTEM_CHART_TYPE_CODE_ID = 'system_chart_type';
    const string SYSTEM_CHART_TYPE_DEFAULT = 'default chart type (system)';
    const string SYSTEM_CHART_TYPE_DEFAULT_CODE_ID = 'system_chart_type_default';
    const string SYSTEM_CHART_TYPE_ALTERNATIVE = 'alternative chart type (system)';
    const string SYSTEM_CHART_TYPE_ALTERNATIVE_CODE_ID = 'system_chart_type_alternative';
    // the charts of a table that shows the mayor columns only, e.g. the initial start page,
    // which are shown instead of the default charts
    const string SYSTEM_CHART_TYPE_MAYOR = 'mayor chart type (system)';
    const string SYSTEM_CHART_TYPE_MAYOR_CODE_ID = 'system_chart_type_mayor';
    // the default sort orders of a table, defined in solution_prio.json like the charts: an
    // order is a triple "<condition> of <column>" e.g. "numeric descending of potential loss",
    // where the condition word carries the table_orders value as its code id, and is assigned
    // to the default tier with "<order> can be <tier>"; the first default order is the prime
    // order of the rows if the url of the page names none, the next ones are the sub orders
    const string SYSTEM_SORT_ORDER = 'sort order (system)';
    const string SYSTEM_SORT_ORDER_CODE_ID = 'system_sort_order';
    const string SYSTEM_SORT_ORDER_DEFAULT = 'default sort order (system)';
    const string SYSTEM_SORT_ORDER_DEFAULT_CODE_ID = 'system_sort_order_default';
    // the number of digits shown of a number, defined in base_phrases.json like the column tiers:
    // a view, a value or a result is assigned to a precision tier with "<phrase> can be <tier>",
    // the default tier is used if none is assigned; the number of digits of each tier is set in
    // config.yaml (number format > digits) with the shared config consts as fallback, except for
    // the full precision tier, which always shows all decimals
    const string SYSTEM_DIGITS = 'digits (system)';
    const string SYSTEM_DIGITS_CODE_ID = 'system_digits';
    const string SYSTEM_DIGITS_DEFAULT = 'default digits (system)';
    const string SYSTEM_DIGITS_DEFAULT_CODE_ID = 'system_digits_default';
    const string SYSTEM_DIGITS_HIGH = 'high precision digits (system)';
    const string SYSTEM_DIGITS_HIGH_CODE_ID = 'system_digits_high';
    const string SYSTEM_DIGITS_VERY_HIGH = 'very high precision digits (system)';
    const string SYSTEM_DIGITS_VERY_HIGH_CODE_ID = 'system_digits_very_high';
    const string SYSTEM_DIGITS_FULL = 'full precision digits (system)';
    const string SYSTEM_DIGITS_FULL_CODE_ID = 'system_digits_full';
    const string SYSTEM_DIGITS_LOW = 'low precision digits (system)';
    const string SYSTEM_DIGITS_LOW_CODE_ID = 'system_digits_low';
    const string SYSTEM_DIGITS_VERY_LOW = 'very low precision digits (system)';
    const string SYSTEM_DIGITS_VERY_LOW_CODE_ID = 'system_digits_very_low';
    // the high and the low bound of the variance of a value, defined in base_phrases.json; a value
    // with one of these phrases is a bound of the value with the same other phrases
    // (see the view-validation of solution_prio.json)
    const string SYSTEM_VARIANCE_HIGH = 'variance high (system)';
    const string SYSTEM_VARIANCE_HIGH_CODE_ID = 'system_variance_high';
    const string SYSTEM_VARIANCE_LOW = 'variance low (system)';
    const string SYSTEM_VARIANCE_LOW_CODE_ID = 'system_variance_low';
    const array VARIANCE_BOUNDS = [self::SYSTEM_VARIANCE_LOW, self::SYSTEM_VARIANCE_HIGH];
    // an input field of the process catch views, defined in process_catch_fields.json like the
    // column tiers: the field of a question phrase is a phrase of a use case file that gets its
    // label, value phrases, unit and type by triples to these properties, and its limits, step and
    // suggestion as values with the property; a field type is linked with "can be" to the field type
    const string SYSTEM_PROCESS_CATCH_FIELD = 'process catch field (system)';
    const string SYSTEM_PROCESS_CATCH_FIELD_CODE_ID = 'system_process_catch_field';
    const string FIELD_LABEL = 'field label';
    const string FIELD_LABEL_CODE_ID = 'system_field_label';
    const string FIELD_VALUE_PHRASES = 'field value phrases';
    const string FIELD_VALUE_PHRASES_CODE_ID = 'system_field_value_phrases';
    const string FIELD_TYPE = 'field type';
    const string FIELD_TYPE_CODE_ID = 'system_field_type';
    const string FIELD_UNIT = 'field unit';
    const string FIELD_UNIT_CODE_ID = 'system_field_unit';
    const string FIELD_LIMIT_UP = 'field limit up';
    const string FIELD_LIMIT_UP_CODE_ID = 'system_field_limit_up';
    const string FIELD_LIMIT_DOWN = 'field limit down';
    const string FIELD_LIMIT_DOWN_CODE_ID = 'system_field_limit_down';
    const string FIELD_STEP = 'field step';
    const string FIELD_STEP_CODE_ID = 'system_field_step';
    const string FIELD_SUGGESTION = 'field suggestion';
    const string FIELD_SUGGESTION_CODE_ID = 'system_field_suggestion';
    const string FIELD_LOCATION_SELECTOR = 'location selector';
    const string FIELD_LOCATION_SELECTOR_CODE_ID = 'system_field_location_selector';
    const string FIELD_NUMBER_WITH_LIMITS = 'number with limits';
    const string FIELD_NUMBER_WITH_LIMITS_CODE_ID = 'system_field_number_with_limits';
    const string FIELD_PRE_SELECTION = 'pre-selection';
    const string FIELD_PRE_SELECTION_CODE_ID = 'system_field_pre_selection';
    const string FIELD_PHRASE_SELECTOR_TYPE = 'phrase selector';
    const string FIELD_PHRASE_SELECTOR_TYPE_CODE_ID = 'system_field_phrase_selector_type';
    const string FIELD_PHRASE_SELECTION_TYPE = 'phrase selection';
    const string FIELD_PHRASE_SELECTION_TYPE_CODE_ID = 'system_field_phrase_selection_type';
    // the phrases that a phrase selector field offers, with its own label, the parent of the
    // phrases and the phrase whose values offer their phrases
    const string FIELD_PHRASE_SELECTOR = 'field phrase selector';
    const string FIELD_PHRASE_SELECTOR_CODE_ID = 'system_field_phrase_selector';
    const string FIELD_PHRASE_SELECTOR_LABEL = 'field phrase selector label';
    const string FIELD_PHRASE_SELECTOR_LABEL_CODE_ID = 'system_field_phrase_selector_label';
    const string FIELD_PHRASE_SELECTOR_PARENT = 'field phrase selector parent';
    const string FIELD_PHRASE_SELECTOR_PARENT_CODE_ID = 'system_field_phrase_selector_parent';
    const string FIELD_PHRASE_SELECTOR_VALUE_BASED = 'field phrase selector value based';
    const string FIELD_PHRASE_SELECTOR_VALUE_BASED_CODE_ID = 'system_field_phrase_selector_value_based';
    // the sorted list of phrases that a phrase selection field shows, with its label and parent
    const string FIELD_SELECTION = 'field selection';
    const string FIELD_SELECTION_CODE_ID = 'system_field_selection';
    const string FIELD_SELECTION_LABEL = 'field selection label';
    const string FIELD_SELECTION_LABEL_CODE_ID = 'system_field_selection_label';
    const string FIELD_SELECTION_PARENT = 'field selection parent';
    const string FIELD_SELECTION_PARENT_CODE_ID = 'system_field_selection_parent';
    // the process catch page that shows the field
    const string FIELD_PAGE = 'field page';
    const string FIELD_PAGE_CODE_ID = 'system_field_page';

    // the properties of a process catch field by their code id
    const array FIELD_PROPERTIES = [
        self::FIELD_LABEL => self::FIELD_LABEL_CODE_ID,
        self::FIELD_VALUE_PHRASES => self::FIELD_VALUE_PHRASES_CODE_ID,
        self::FIELD_TYPE => self::FIELD_TYPE_CODE_ID,
        self::FIELD_UNIT => self::FIELD_UNIT_CODE_ID,
        self::FIELD_LIMIT_UP => self::FIELD_LIMIT_UP_CODE_ID,
        self::FIELD_LIMIT_DOWN => self::FIELD_LIMIT_DOWN_CODE_ID,
        self::FIELD_STEP => self::FIELD_STEP_CODE_ID,
        self::FIELD_SUGGESTION => self::FIELD_SUGGESTION_CODE_ID,
        self::FIELD_PHRASE_SELECTOR => self::FIELD_PHRASE_SELECTOR_CODE_ID,
        self::FIELD_PHRASE_SELECTOR_LABEL => self::FIELD_PHRASE_SELECTOR_LABEL_CODE_ID,
        self::FIELD_PHRASE_SELECTOR_PARENT => self::FIELD_PHRASE_SELECTOR_PARENT_CODE_ID,
        self::FIELD_PHRASE_SELECTOR_VALUE_BASED => self::FIELD_PHRASE_SELECTOR_VALUE_BASED_CODE_ID,
        self::FIELD_SELECTION => self::FIELD_SELECTION_CODE_ID,
        self::FIELD_SELECTION_LABEL => self::FIELD_SELECTION_LABEL_CODE_ID,
        self::FIELD_SELECTION_PARENT => self::FIELD_SELECTION_PARENT_CODE_ID,
        self::FIELD_PAGE => self::FIELD_PAGE_CODE_ID,
    ];
    // the field properties whose number is a value of the field phrase and the keyword e.g. (lifetime, field step) = 1
    const array FIELD_VALUE_PROPERTIES = [
        self::FIELD_LIMIT_UP => self::FIELD_LIMIT_UP_CODE_ID,
        self::FIELD_LIMIT_DOWN => self::FIELD_LIMIT_DOWN_CODE_ID,
        self::FIELD_STEP => self::FIELD_STEP_CODE_ID,
    ];
    // the types of a process catch field by their code id
    const array FIELD_TYPES = [
        self::FIELD_LOCATION_SELECTOR => self::FIELD_LOCATION_SELECTOR_CODE_ID,
        self::FIELD_NUMBER_WITH_LIMITS => self::FIELD_NUMBER_WITH_LIMITS_CODE_ID,
        self::FIELD_PRE_SELECTION => self::FIELD_PRE_SELECTION_CODE_ID,
        self::FIELD_PHRASE_SELECTOR_TYPE => self::FIELD_PHRASE_SELECTOR_TYPE_CODE_ID,
        self::FIELD_PHRASE_SELECTION_TYPE => self::FIELD_PHRASE_SELECTION_TYPE_CODE_ID,
    ];

    // the four pages of a process catch, which the fields and the buttons are assigned to
    const string SYSTEM_PROCESS_CATCH_PAGE = 'process catch page (system)';
    const string SYSTEM_PROCESS_CATCH_PAGE_CODE_ID = 'system_process_catch_page';
    const string PROCESS_CATCH_START_PAGE = 'process catch start page';
    const string PROCESS_CATCH_START_PAGE_CODE_ID = 'system_process_catch_start_page';
    const string PROCESS_CATCH_MAIN_PAGE = 'process catch main page';
    const string PROCESS_CATCH_MAIN_PAGE_CODE_ID = 'system_process_catch_main_page';
    const string PROCESS_CATCH_DETAIL_PAGE = 'process catch detail page';
    const string PROCESS_CATCH_DETAIL_PAGE_CODE_ID = 'system_process_catch_detail_page';
    const string PROCESS_CATCH_RESULT_PAGE = 'process catch result page';
    const string PROCESS_CATCH_RESULT_PAGE_CODE_ID = 'system_process_catch_result_page';
    // the pages of a process catch in the order the user walks through them
    const array PROCESS_CATCH_PAGES = [
        self::PROCESS_CATCH_START_PAGE => self::PROCESS_CATCH_START_PAGE_CODE_ID,
        self::PROCESS_CATCH_MAIN_PAGE => self::PROCESS_CATCH_MAIN_PAGE_CODE_ID,
        self::PROCESS_CATCH_DETAIL_PAGE => self::PROCESS_CATCH_DETAIL_PAGE_CODE_ID,
        self::PROCESS_CATCH_RESULT_PAGE => self::PROCESS_CATCH_RESULT_PAGE_CODE_ID,
    ];

    // a button of a process catch page: the text in the button is defined by the component, the
    // tooltip, the label of the refresh button and the page it opens by the properties of each button
    // type, so a question phrase can give the next and the back button a different tooltip
    const string SYSTEM_PROCESS_CATCH_BUTTON = 'process catch button (system)';
    const string SYSTEM_PROCESS_CATCH_BUTTON_CODE_ID = 'system_process_catch_button';
    const string BUTTON_NEXT = 'next button';
    const string BUTTON_NEXT_CODE_ID = 'system_button_next';
    const string BUTTON_REFRESH = 'refresh button';
    const string BUTTON_REFRESH_CODE_ID = 'system_button_refresh';
    const string BUTTON_BACK = 'back button';
    const string BUTTON_BACK_CODE_ID = 'system_button_back';
    const string BUTTON_NEXT_TOOLTIP = 'button next tooltip';
    const string BUTTON_NEXT_TOOLTIP_CODE_ID = 'system_button_next_tooltip';
    const string BUTTON_NEXT_VIEW = 'button next view';
    const string BUTTON_NEXT_VIEW_CODE_ID = 'system_button_next_view';
    const string BUTTON_REFRESH_TOOLTIP = 'button refresh tooltip';
    const string BUTTON_REFRESH_TOOLTIP_CODE_ID = 'system_button_refresh_tooltip';
    const string BUTTON_REFRESH_LABEL = 'button refresh label';
    const string BUTTON_REFRESH_LABEL_CODE_ID = 'system_button_refresh_label';
    const string BUTTON_REFRESH_VIEW = 'button refresh view';
    const string BUTTON_REFRESH_VIEW_CODE_ID = 'system_button_refresh_view';
    const string BUTTON_BACK_TOOLTIP = 'button back tooltip';
    const string BUTTON_BACK_TOOLTIP_CODE_ID = 'system_button_back_tooltip';
    const string BUTTON_BACK_VIEW = 'button back view';
    const string BUTTON_BACK_VIEW_CODE_ID = 'system_button_back_view';
    // the properties of the process catch buttons by their code id
    const array BUTTON_PROPERTIES = [
        self::BUTTON_NEXT_TOOLTIP => self::BUTTON_NEXT_TOOLTIP_CODE_ID,
        self::BUTTON_NEXT_VIEW => self::BUTTON_NEXT_VIEW_CODE_ID,
        self::BUTTON_REFRESH_TOOLTIP => self::BUTTON_REFRESH_TOOLTIP_CODE_ID,
        self::BUTTON_REFRESH_LABEL => self::BUTTON_REFRESH_LABEL_CODE_ID,
        self::BUTTON_REFRESH_VIEW => self::BUTTON_REFRESH_VIEW_CODE_ID,
        self::BUTTON_BACK_TOOLTIP => self::BUTTON_BACK_TOOLTIP_CODE_ID,
        self::BUTTON_BACK_VIEW => self::BUTTON_BACK_VIEW_CODE_ID,
    ];
    // the types of a process catch button by their code id
    const array BUTTON_TYPES = [
        self::BUTTON_NEXT => self::BUTTON_NEXT_CODE_ID,
        self::BUTTON_REFRESH => self::BUTTON_REFRESH_CODE_ID,
        self::BUTTON_BACK => self::BUTTON_BACK_CODE_ID,
    ];

    // the keywords of the calculator view, defined in calculator_keywords.json like the process
    // catch fields: a list is a group triple from the page phrase to a list keyword whose items
    // are the triples from the group, and a label, a hint or a flag is a triple from an item or
    // from a phrase to the property keyword (docs/llm/new_view_sample_pv_balance.md)
    const string SYSTEM_CALCULATOR = 'calculator (system)';
    const string SYSTEM_CALCULATOR_CODE_ID = 'system_calculator';
    const string CALCULATOR_LABEL = 'calculator label';
    const string CALCULATOR_LABEL_CODE_ID = 'system_calculator_label';
    const string CALCULATOR_HINT = 'calculator hint';
    const string CALCULATOR_HINT_CODE_ID = 'system_calculator_hint';
    const string CALCULATOR_INPUT = 'calculator input';
    const string CALCULATOR_INPUT_CODE_ID = 'system_calculator_input';
    const string CALCULATOR_ASSUMPTION = 'calculator assumption';
    const string CALCULATOR_ASSUMPTION_CODE_ID = 'system_calculator_assumption';
    const string CALCULATOR_RESULT_COLUMN = 'calculator result column';
    const string CALCULATOR_RESULT_COLUMN_CODE_ID = 'system_calculator_result_column';
    const string CALCULATOR_ROW_GROUP = 'calculator row group';
    const string CALCULATOR_ROW_GROUP_CODE_ID = 'system_calculator_row_group';
    const string CALCULATOR_DETAIL_TABLE = 'calculator detail table';
    const string CALCULATOR_DETAIL_TABLE_CODE_ID = 'system_calculator_detail_table';
    const string CALCULATOR_HIGHLIGHT = 'calculator highlight';
    const string CALCULATOR_HIGHLIGHT_CODE_ID = 'system_calculator_highlight';
    const string CALCULATOR_SIGNED = 'calculator signed';
    const string CALCULATOR_SIGNED_CODE_ID = 'system_calculator_signed';
    // the lists of a calculator page by their code id
    const array CALCULATOR_LISTS = [
        self::CALCULATOR_INPUT => self::CALCULATOR_INPUT_CODE_ID,
        self::CALCULATOR_ASSUMPTION => self::CALCULATOR_ASSUMPTION_CODE_ID,
        self::CALCULATOR_RESULT_COLUMN => self::CALCULATOR_RESULT_COLUMN_CODE_ID,
        self::CALCULATOR_ROW_GROUP => self::CALCULATOR_ROW_GROUP_CODE_ID,
        self::CALCULATOR_DETAIL_TABLE => self::CALCULATOR_DETAIL_TABLE_CODE_ID,
    ];
    // the texts and the flags of an item or a phrase of a calculator page by their code id
    const array CALCULATOR_PROPERTIES = [
        self::CALCULATOR_LABEL => self::CALCULATOR_LABEL_CODE_ID,
        self::CALCULATOR_HINT => self::CALCULATOR_HINT_CODE_ID,
        self::CALCULATOR_HIGHLIGHT => self::CALCULATOR_HIGHLIGHT_CODE_ID,
        self::CALCULATOR_SIGNED => self::CALCULATOR_SIGNED_CODE_ID,
    ];
    // the keywords of the calculator and of the input fields by their code id, which the data of a
    // phrase (see phrase_data) never walks through, because every page phrase links to them
    const array CALCULATOR_KEYWORDS = [
        ...triples::CALCULATOR_LISTS,
        ...triples::CALCULATOR_PROPERTIES,
        ...triples::FIELD_PROPERTIES,
        ...triples::FIELD_TYPES,
    ];
    const string SYSTEM_ERRORS = 'system errors';
    const string TABLE_NAME = 'table name';
    const string TIME_PERCENT = 'time percent';
    const string TOP_LEVEL = 'top level';
    const string USER_FRONTEND = 'user frontend';
    // if the html frontend may add javascript to a page; false except for the calculator pages
    const string USE_JAVASCRIPT = 'use javascript';
    const string VALUE_LIST = 'value list';
    const string VALUE_TABLE = 'value table';
    // config keys of the digit tiers (config.yaml number format > digits)
    const string VERY_PRECISE = 'very precise';
    const string VERY_ROUGH = 'very rough';
    const string WEB_MOBILE = 'web mobile';
    const string WHAT_LIMIT = 'what limit';
    const string WORD_CHANGES = 'word changes';

    const array BASE_TRIPLES = [
        [triple_names::MATH_CONST, triple_names::MATH_CONST_ID],
        [triple_names::CANTON_ZURICH, triple_names::CANTON_ZURICH_ID],
        [triple_names::CHF_SYMBOL, triple_names::CHF_SYMBOL_ID],
        [triple_names::CASH_FLOW, triple_names::CASH_FLOW_ID],
        [triple_names::CASH_FLOW_STATEMENT, triple_names::CASH_FLOW_STATEMENT_ID],
        [triple_names::CITY_BE, triple_names::CITY_BE_ID],
        [triple_names::CITY_GE, triple_names::CITY_GE_ID],
        [triple_names::CITY_ZH, triple_names::CITY_ZH_ID],
        [triple_names::COMPANY_ZURICH, triple_names::COMPANY_ZURICH_ID],
        [triple_names::E, triple_names::E_ID],
        [triple_names::GLOBAL_PROBLEM, triple_names::GLOBAL_PROBLEM_ID],
        [triple_names::GLOBAL_WARMING, triple_names::GLOBAL_WARMING_ID],
        [triple_names::GLOBAL_WARMING_PROBLEM, triple_names::GLOBAL_WARMING_PROBLEM_ID],
        [triple_names::GWP, triple_names::GWP_ID],
        [triple_names::HAPPY_TIME_POINTS, triple_names::HAPPY_TIME_POINTS_ID],
        [triple_names::INCOME_TAX, triple_names::INCOME_TAX_ID],
        [triple_names::PI, triple_names::PI_ID],
        [triple_names::PI_SYMBOL, triple_names::PI_SYMBOL_ID],
        [triple_names::POPULISM_PROBLEM, triple_names::POPULISM_PROBLEM_ID],
        [triple_names::POTENTIAL_EDUCATION_PROBLEM, triple_names::POTENTIAL_EDUCATION_PROBLEM_ID],
        [triple_names::POTENTIAL_HEALTH_PROBLEM, triple_names::POTENTIAL_HEALTH_PROBLEM_ID],
        [triple_names::POVERTY_PROBLEM, triple_names::POVERTY_PROBLEM_ID],
        [triple_names::TIME_POINTS, triple_names::TIME_POINTS_ID],
    ];

    // list of predefined triple used for system testing that are expected to be never renamed
    const array RESERVED_NAMES = array(
        self::SYSTEM_CONFIG,
        triple_names::SYSTEM_TEST_ADD,
        triple_names::SYSTEM_TEST_RENAMED,
        triple_names::SYSTEM_TEST_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        triple_names::MATH_CONST
    );

}
