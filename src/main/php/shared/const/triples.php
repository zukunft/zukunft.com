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
    // the two word colour keys of the editors in config.yaml (see def::FALLBACK_EDITOR_COLOURS)
    const string SHARED_SOFT = 'shared soft';
    const string DANGER_SOFT = 'danger soft';
    const string SUCCESS_SOFT = 'success soft';
    const string PREVIEW_TEXT = 'preview text';
    const string PREVIEW_LABEL = 'preview label';
    const string PREVIEW_MUTED = 'preview muted';
    const string PREVIEW_PANEL = 'preview panel';
    const string PREVIEW_BORDER = 'preview border';
    const string PREVIEW_FIELD = 'preview field';
    const string PREVIEW_RULE = 'preview rule';
    const string PREVIEW_SECTION = 'preview section';
    const string PREVIEW_ASSUMED = 'preview assumed';
    const string PREVIEW_NEGATIVE = 'preview negative';
    // the views linked on the start page (config.yaml system view startup)
    const string PINNED_VIEWS = 'pinned views';
    // the config.yaml keys whose sub keys are names of the data e.g. the url name of a pinned view,
    // which therefore need no word const (see coding_rule_tests::config_yaml_word_triple_tests)
    const array CONFIG_DATA_KEYS = [self::PINNED_VIEWS];
    // the view and the phrase of a pinned view without url, from which the test run creates the url
    const string VIEW_NAME = 'view name';
    const string PHRASE_NAME = 'phrase name';
    // the light or dark mode of the pages (config.yaml user default frontend layout), whose code id
    // makes the settings page offer the selection of the light or the dark colours (see base_phrases.json)
    const string COLOUR_MODE = 'colour mode';
    const string COLOUR_MODE_CODE_ID = 'colour_mode';
    // the file name of the image of the firm that presents a report (config.yaml user default frontend layout)
    const string REPORT_LOGO = 'report logo';
    const string SOURCE_TABLE = 'source table';
    const string START_DELAY = 'start delay';
    const string STORAGE_SIZE = 'storage size';
    const string SYSTEM_CONFIG = 'system configuration';
    const int SYSTEM_CONFIG_ID = 140;
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
    // the own columns and the own default sorting of the table of one phrase, set by the table editor
    const string TABLE_COLUMN = 'table column';
    const string TABLE_COLUMN_CODE_ID = 'system_table_column';
    const string TABLE_SORT = 'table sort';
    const string TABLE_SORT_CODE_ID = 'system_table_sort';
    // the words of the sort conditions that the table editor offers, the name of the word is part of an order triple
    const array TABLE_SORT_CONDITIONS = ['numeric descending', 'numeric ascending'];
    // the keywords of the parameters of a table (editor_views.json), which only the phraseData walk reads (TABLE_KEYWORDS); the column
    // parameters are not children of column (system), because triples_of_types shows those as column tiers;
    // each group is on the to side of the parent, because the phraseData walk follows the from side of a domain word e.g. export
    const string SYSTEM_TABLE_PARAMETER = 'table parameter (system)';
    const string SYSTEM_TABLE_PARAMETER_CODE_ID = 'system_table_parameter';
    const string SYSTEM_TABLE_COLUMN_PARAMETER = 'table column (system)';
    const string SYSTEM_TABLE_COLUMN_PARAMETER_CODE_ID = 'system_table_column_parameter';
    const string SYSTEM_COLUMNS_ALL = 'all table column (system)';
    const string SYSTEM_COLUMNS_ALL_CODE_ID = 'system_columns_all';
    const string SYSTEM_COLUMN_LEVEL = 'level table column (system)';
    const string SYSTEM_COLUMN_LEVEL_CODE_ID = 'system_column_level';
    const string SYSTEM_COLUMN_ORDER = 'order table column (system)';
    const string SYSTEM_COLUMN_ORDER_CODE_ID = 'system_column_order';
    const string SYSTEM_COLUMN_ORDER_RULE = 'order rule table column (system)';
    const string SYSTEM_COLUMN_ORDER_RULE_CODE_ID = 'system_column_order_rule';
    const string SYSTEM_COLUMN_GROUPS = 'groups table column (system)';
    const string SYSTEM_COLUMN_GROUPS_CODE_ID = 'system_column_groups';
    const string SYSTEM_COLUMN_FROZEN = 'frozen table column (system)';
    const string SYSTEM_COLUMN_FROZEN_CODE_ID = 'system_column_frozen';
    const string SYSTEM_COLUMN_WIDTH = 'width table column (system)';
    const string SYSTEM_COLUMN_WIDTH_CODE_ID = 'system_column_width';
    const string SYSTEM_COLUMN_HIDDEN_USER = 'hidden user table column (system)';
    const string SYSTEM_COLUMN_HIDDEN_USER_CODE_ID = 'system_column_hidden_user';
    const string SYSTEM_COLUMN_PIVOT_PHRASE = 'pivot phrase table column (system)';
    const string SYSTEM_COLUMN_PIVOT_PHRASE_CODE_ID = 'system_column_pivot_phrase';
    const string SYSTEM_ROW = 'row (system)';
    const string SYSTEM_ROW_CODE_ID = 'system_row';
    const string SYSTEM_ROW_PHRASE = 'phrase row (system)';
    const string SYSTEM_ROW_PHRASE_CODE_ID = 'system_row_phrase';
    const string SYSTEM_ROW_VERB = 'verb row (system)';
    const string SYSTEM_ROW_VERB_CODE_ID = 'system_row_verb';
    const string SYSTEM_ROW_DIRECTION = 'direction row (system)';
    const string SYSTEM_ROW_DIRECTION_CODE_ID = 'system_row_direction';
    const string SYSTEM_ROW_FILTER = 'filter row (system)';
    const string SYSTEM_ROW_FILTER_CODE_ID = 'system_row_filter';
    const string SYSTEM_ROW_ORDER = 'order row (system)';
    const string SYSTEM_ROW_ORDER_CODE_ID = 'system_row_order';
    const string SYSTEM_ROW_ORDER_RULE = 'order rule row (system)';
    const string SYSTEM_ROW_ORDER_RULE_CODE_ID = 'system_row_order_rule';
    const string SYSTEM_ROW_LIMIT = 'limit row (system)';
    const string SYSTEM_ROW_LIMIT_CODE_ID = 'system_row_limit';
    const string SYSTEM_ROW_PAGE_SIZE = 'page size row (system)';
    const string SYSTEM_ROW_PAGE_SIZE_CODE_ID = 'system_row_page_size';
    const string SYSTEM_ROW_PAGE = 'page row (system)';
    const string SYSTEM_ROW_PAGE_CODE_ID = 'system_row_page';
    const string SYSTEM_ROW_SHOW_ALL = 'show all row (system)';
    const string SYSTEM_ROW_SHOW_ALL_CODE_ID = 'system_row_show_all';
    const string SYSTEM_ROW_FROZEN = 'frozen row (system)';
    const string SYSTEM_ROW_FROZEN_CODE_ID = 'system_row_frozen';
    const string SYSTEM_ROW_PINNED = 'pinned row (system)';
    const string SYSTEM_ROW_PINNED_CODE_ID = 'system_row_pinned';
    const string SYSTEM_ROW_HIDDEN_USER = 'hidden user row (system)';
    const string SYSTEM_ROW_HIDDEN_USER_CODE_ID = 'system_row_hidden_user';
    const string SYSTEM_ROW_EMPTY = 'empty row (system)';
    const string SYSTEM_ROW_EMPTY_CODE_ID = 'system_row_empty';
    const string SYSTEM_HIERARCHY = 'hierarchy (system)';
    const string SYSTEM_HIERARCHY_CODE_ID = 'system_hierarchy';
    const string SYSTEM_HIERARCHY_VERB = 'verb hierarchy (system)';
    const string SYSTEM_HIERARCHY_VERB_CODE_ID = 'system_hierarchy_verb';
    const string SYSTEM_HIERARCHY_EXPAND = 'expand hierarchy (system)';
    const string SYSTEM_HIERARCHY_EXPAND_CODE_ID = 'system_hierarchy_expand';
    const string SYSTEM_HIERARCHY_MAX_DEPTH = 'max depth hierarchy (system)';
    const string SYSTEM_HIERARCHY_MAX_DEPTH_CODE_ID = 'system_hierarchy_max_depth';
    const string SYSTEM_HIERARCHY_INDENT = 'indent hierarchy (system)';
    const string SYSTEM_HIERARCHY_INDENT_CODE_ID = 'system_hierarchy_indent';
    const string SYSTEM_HIERARCHY_PARENT_VALUE = 'parent value hierarchy (system)';
    const string SYSTEM_HIERARCHY_PARENT_VALUE_CODE_ID = 'system_hierarchy_parent_value';
    const string SYSTEM_HIERARCHY_ORPHANS = 'orphans hierarchy (system)';
    const string SYSTEM_HIERARCHY_ORPHANS_CODE_ID = 'system_hierarchy_orphans';
    const string SYSTEM_HIERARCHY_MULTI_PARENT = 'multi parent hierarchy (system)';
    const string SYSTEM_HIERARCHY_MULTI_PARENT_CODE_ID = 'system_hierarchy_multi_parent';
    const string SYSTEM_HIERARCHY_SORT_SCOPE = 'sort scope hierarchy (system)';
    const string SYSTEM_HIERARCHY_SORT_SCOPE_CODE_ID = 'system_hierarchy_sort_scope';
    const string SYSTEM_VALUE = 'value (system)';
    const string SYSTEM_VALUE_CODE_ID = 'system_value';
    const string SYSTEM_VALUE_TYPE = 'type value (system)';
    const string SYSTEM_VALUE_TYPE_CODE_ID = 'system_value_type';
    const string SYSTEM_VALUE_FORMAT = 'format value (system)';
    const string SYSTEM_VALUE_FORMAT_CODE_ID = 'system_value_format';
    const string SYSTEM_VALUE_UNIT = 'unit value (system)';
    const string SYSTEM_VALUE_UNIT_CODE_ID = 'system_value_unit';
    const string SYSTEM_VALUE_SCALE = 'scale value (system)';
    const string SYSTEM_VALUE_SCALE_CODE_ID = 'system_value_scale';
    const string SYSTEM_VALUE_TIME = 'time value (system)';
    const string SYSTEM_VALUE_TIME_CODE_ID = 'system_value_time';
    const string SYSTEM_VALUE_SOURCE_MODE = 'source mode value (system)';
    const string SYSTEM_VALUE_SOURCE_MODE_CODE_ID = 'system_value_source_mode';
    const string SYSTEM_VALUE_SHOW_SOURCE = 'show source value (system)';
    const string SYSTEM_VALUE_SHOW_SOURCE_CODE_ID = 'system_value_show_source';
    const string SYSTEM_VALUE_SHOW_CONFIDENCE = 'show confidence value (system)';
    const string SYSTEM_VALUE_SHOW_CONFIDENCE_CODE_ID = 'system_value_show_confidence';
    const string SYSTEM_VALUE_MISSING = 'missing value (system)';
    const string SYSTEM_VALUE_MISSING_CODE_ID = 'system_value_missing';
    const string SYSTEM_VALUE_ALIGN = 'align value (system)';
    const string SYSTEM_VALUE_ALIGN_CODE_ID = 'system_value_align';
    const string SYSTEM_VALUE_TRUNCATE = 'truncate value (system)';
    const string SYSTEM_VALUE_TRUNCATE_CODE_ID = 'system_value_truncate';
    const string SYSTEM_VALUE_CONDITIONAL_STYLE = 'conditional style value (system)';
    const string SYSTEM_VALUE_CONDITIONAL_STYLE_CODE_ID = 'system_value_conditional_style';
    const string SYSTEM_VALUE_DELTA = 'delta value (system)';
    const string SYSTEM_VALUE_DELTA_CODE_ID = 'system_value_delta';
    const string SYSTEM_VALUE_EDITABLE = 'editable value (system)';
    const string SYSTEM_VALUE_EDITABLE_CODE_ID = 'system_value_editable';
    const string SYSTEM_VALUE_UNMATCHED = 'unmatched values value (system)';
    const string SYSTEM_VALUE_UNMATCHED_CODE_ID = 'system_value_unmatched';
    const string SYSTEM_AGGREGATION = 'aggregation (system)';
    const string SYSTEM_AGGREGATION_CODE_ID = 'system_aggregation';
    const string SYSTEM_TOTALS_ROW = 'totals row aggregation (system)';
    const string SYSTEM_TOTALS_ROW_CODE_ID = 'system_totals_row';
    const string SYSTEM_TOTALS_COLUMN = 'totals column aggregation (system)';
    const string SYSTEM_TOTALS_COLUMN_CODE_ID = 'system_totals_column';
    const string SYSTEM_SUBTOTALS = 'subtotals aggregation (system)';
    const string SYSTEM_SUBTOTALS_CODE_ID = 'system_subtotals';
    const string SYSTEM_AGGREGATION_FUNCTION = 'function aggregation (system)';
    const string SYSTEM_AGGREGATION_FUNCTION_CODE_ID = 'system_aggregation_function';
    const string SYSTEM_AGGREGATION_SCOPE = 'scope aggregation (system)';
    const string SYSTEM_AGGREGATION_SCOPE_CODE_ID = 'system_aggregation_scope';
    const string SYSTEM_HEADER = 'header (system)';
    const string SYSTEM_HEADER_CODE_ID = 'system_header';
    const string SYSTEM_HEADER_LABEL = 'label header (system)';
    const string SYSTEM_HEADER_LABEL_CODE_ID = 'system_header_label';
    const string SYSTEM_HEADER_ROTATE = 'rotate header (system)';
    const string SYSTEM_HEADER_ROTATE_CODE_ID = 'system_header_rotate';
    const string SYSTEM_HEADER_SHOW_UNIT = 'show unit header (system)';
    const string SYSTEM_HEADER_SHOW_UNIT_CODE_ID = 'system_header_show_unit';
    const string SYSTEM_HEADER_TOOLTIP = 'tooltip header (system)';
    const string SYSTEM_HEADER_TOOLTIP_CODE_ID = 'system_header_tooltip';
    const string SYSTEM_ROW_LABEL_MODE = 'row label mode header (system)';
    const string SYSTEM_ROW_LABEL_MODE_CODE_ID = 'system_row_label_mode';
    const string SYSTEM_LAYOUT = 'layout (system)';
    const string SYSTEM_LAYOUT_CODE_ID = 'system_layout';
    const string SYSTEM_TITLE = 'title layout (system)';
    const string SYSTEM_TITLE_CODE_ID = 'system_title';
    const string SYSTEM_CAPTION = 'caption layout (system)';
    const string SYSTEM_CAPTION_CODE_ID = 'system_caption';
    const string SYSTEM_DENSITY = 'density layout (system)';
    const string SYSTEM_DENSITY_CODE_ID = 'system_density';
    const string SYSTEM_STRIPED = 'striped layout (system)';
    const string SYSTEM_STRIPED_CODE_ID = 'system_striped';
    const string SYSTEM_BORDERS = 'borders layout (system)';
    const string SYSTEM_BORDERS_CODE_ID = 'system_borders';
    const string SYSTEM_TRANSPOSE = 'transpose layout (system)';
    const string SYSTEM_TRANSPOSE_CODE_ID = 'system_transpose';
    const string SYSTEM_MAX_WIDTH = 'max width layout (system)';
    const string SYSTEM_MAX_WIDTH_CODE_ID = 'system_max_width';
    const string SYSTEM_RESPONSIVE_MODE = 'responsive mode layout (system)';
    const string SYSTEM_RESPONSIVE_MODE_CODE_ID = 'system_responsive_mode';
    const string SYSTEM_BACKGROUND = 'background layout (system)';
    const string SYSTEM_BACKGROUND_CODE_ID = 'system_background';
    const string SYSTEM_DESIGN = 'design layout (system)';
    const string SYSTEM_DESIGN_CODE_ID = 'system_design';
    const string SYSTEM_INTERACTION = 'interaction (system)';
    const string SYSTEM_INTERACTION_CODE_ID = 'system_interaction';
    const string SYSTEM_SORTABLE = 'sortable interaction (system)';
    const string SYSTEM_SORTABLE_CODE_ID = 'system_sortable';
    const string SYSTEM_FILTERABLE = 'filterable interaction (system)';
    const string SYSTEM_FILTERABLE_CODE_ID = 'system_filterable';
    const string SYSTEM_SEARCHABLE = 'searchable interaction (system)';
    const string SYSTEM_SEARCHABLE_CODE_ID = 'system_searchable';
    const string SYSTEM_SELECTABLE = 'selectable interaction (system)';
    const string SYSTEM_SELECTABLE_CODE_ID = 'system_selectable';
    const string SYSTEM_DRILLDOWN = 'drilldown interaction (system)';
    const string SYSTEM_DRILLDOWN_CODE_ID = 'system_drilldown';
    const string SYSTEM_REORDERABLE = 'reorderable interaction (system)';
    const string SYSTEM_REORDERABLE_CODE_ID = 'system_reorderable';
    const string SYSTEM_RESIZABLE = 'resizable interaction (system)';
    const string SYSTEM_RESIZABLE_CODE_ID = 'system_resizable';
    const string SYSTEM_REFRESH_MODE = 'refresh mode interaction (system)';
    const string SYSTEM_REFRESH_MODE_CODE_ID = 'system_refresh_mode';
    const string SYSTEM_CONTEXT = 'context (system)';
    const string SYSTEM_CONTEXT_CODE_ID = 'system_context';
    const string SYSTEM_VIEW_OWNER = 'view owner context (system)';
    const string SYSTEM_VIEW_OWNER_CODE_ID = 'system_view_owner';
    const string SYSTEM_VIEW_SHARED = 'view shared context (system)';
    const string SYSTEM_VIEW_SHARED_CODE_ID = 'system_view_shared';
    const string SYSTEM_LANGUAGE = 'language context (system)';
    const string SYSTEM_LANGUAGE_CODE_ID = 'system_language';
    const string SYSTEM_PROFILE = 'profile context (system)';
    const string SYSTEM_PROFILE_CODE_ID = 'system_profile';
    const string SYSTEM_TIME_REFERENCE = 'time reference context (system)';
    const string SYSTEM_TIME_REFERENCE_CODE_ID = 'system_time_reference';
    const string SYSTEM_EXPORT = 'export (system)';
    const string SYSTEM_EXPORT_CODE_ID = 'system_export';
    const string SYSTEM_EXPORT_FORMATS = 'formats export (system)';
    const string SYSTEM_EXPORT_FORMATS_CODE_ID = 'system_export_formats';
    const string SYSTEM_EXPORT_SCOPE = 'scope export (system)';
    const string SYSTEM_EXPORT_SCOPE_CODE_ID = 'system_export_scope';
    const string SYSTEM_EXPORT_INCLUDE_SOURCES = 'include sources export (system)';
    const string SYSTEM_EXPORT_INCLUDE_SOURCES_CODE_ID = 'system_export_include_sources';
    const string SYSTEM_STATE = 'state (system)';
    const string SYSTEM_STATE_CODE_ID = 'system_state';
    const string SYSTEM_PERSIST_USER_CHANGES = 'persist user changes state (system)';
    const string SYSTEM_PERSIST_USER_CHANGES_CODE_ID = 'system_persist_user_changes';
    const string SYSTEM_RESET_TO_DEFAULT = 'reset to default state (system)';
    const string SYSTEM_RESET_TO_DEFAULT_CODE_ID = 'system_reset_to_default';
    const string SYSTEM_VIEW_VERSION = 'view version state (system)';
    const string SYSTEM_VIEW_VERSION_CODE_ID = 'system_view_version';
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
        self::CALCULATOR_DECIMALS => self::CALCULATOR_DECIMALS_CODE_ID,
        self::CALCULATOR_RESULT_WIDTH => self::CALCULATOR_RESULT_WIDTH_CODE_ID,
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
    const string CALCULATOR_NUMBER_NOTE = 'calculator number note';
    const string CALCULATOR_NUMBER_NOTE_CODE_ID = 'system_calculator_number_note';
    const string CALCULATOR_SECTION = 'calculator section';
    const string CALCULATOR_SECTION_CODE_ID = 'system_calculator_section';
    const string CALCULATOR_ROW_NUMBER = 'calculator row number';
    const string CALCULATOR_ROW_NUMBER_CODE_ID = 'system_calculator_row_number';
    const string CALCULATOR_PHRASE_COLUMN = 'calculator phrase column';
    const string CALCULATOR_PHRASE_COLUMN_CODE_ID = 'system_calculator_phrase_column';
    const string CALCULATOR_COLUMN_UNIT = 'calculator column unit';
    const string CALCULATOR_COLUMN_UNIT_CODE_ID = 'system_calculator_column_unit';
    const string CALCULATOR_PAGE_INDEPENDENT = 'calculator page independent';
    const string CALCULATOR_PAGE_INDEPENDENT_CODE_ID = 'system_calculator_page_independent';
    // the flags of a detail table and of its columns by their code id
    const array CALCULATOR_DETAIL_FLAGS = [
        self::CALCULATOR_PAGE_INDEPENDENT => self::CALCULATOR_PAGE_INDEPENDENT_CODE_ID,
        self::CALCULATOR_SECTION => self::CALCULATOR_SECTION_CODE_ID,
        self::CALCULATOR_ROW_NUMBER => self::CALCULATOR_ROW_NUMBER_CODE_ID,
        self::CALCULATOR_PHRASE_COLUMN => self::CALCULATOR_PHRASE_COLUMN_CODE_ID,
        self::CALCULATOR_COLUMN_UNIT => self::CALCULATOR_COLUMN_UNIT_CODE_ID,
    ];
    const string CALCULATOR_FILE_IMPORT = 'calculator file import';
    const string CALCULATOR_FILE_IMPORT_CODE_ID = 'system_calculator_file_import';
    const string CALCULATOR_DOWNLOAD = 'calculator download';
    const string CALCULATOR_DOWNLOAD_CODE_ID = 'system_calculator_download';
    const string CALCULATOR_REPORT = 'calculator printable report';
    const string CALCULATOR_REPORT_CODE_ID = 'system_calculator_printable_report';
    const string CALCULATOR_COPY = 'calculator copy';
    const string CALCULATOR_COPY_CODE_ID = 'system_calculator_copy';
    const string CALCULATOR_CASE = 'calculator case';
    const string CALCULATOR_CASE_CODE_ID = 'system_calculator_case';
    // the buttons of a calculator list or table by their code id
    const array CALCULATOR_BUTTONS = [
        self::CALCULATOR_FILE_IMPORT => self::CALCULATOR_FILE_IMPORT_CODE_ID,
        self::CALCULATOR_DOWNLOAD => self::CALCULATOR_DOWNLOAD_CODE_ID,
        self::CALCULATOR_REPORT => self::CALCULATOR_REPORT_CODE_ID,
        self::CALCULATOR_COPY => self::CALCULATOR_COPY_CODE_ID,
    ];
    const string CALCULATOR_NOTE = 'calculator note';
    const string CALCULATOR_NOTE_CODE_ID = 'system_calculator_note';
    const string CALCULATOR_CHECK = 'calculator check';
    const string CALCULATOR_CHECK_CODE_ID = 'system_calculator_check';
    const string CALCULATOR_DIAGRAM = 'calculator diagram';
    const string CALCULATOR_DIAGRAM_CODE_ID = 'system_calculator_diagram';
    // the blocks of a calculator page besides the tables by their code id
    const array CALCULATOR_BLOCKS = [
        self::CALCULATOR_NOTE => self::CALCULATOR_NOTE_CODE_ID,
        self::CALCULATOR_CHECK => self::CALCULATOR_CHECK_CODE_ID,
        self::CALCULATOR_DIAGRAM => self::CALCULATOR_DIAGRAM_CODE_ID,
    ];
    const string CALCULATOR_SLIDER = 'calculator slider';
    const string CALCULATOR_SLIDER_CODE_ID = 'system_calculator_slider';
    const string CALCULATOR_PRESET = 'calculator preset';
    const string CALCULATOR_PRESET_CODE_ID = 'system_calculator_preset';
    const string CALCULATOR_TAG = 'calculator tag';
    const string CALCULATOR_TAG_CODE_ID = 'system_calculator_tag';
    const string CALCULATOR_MORE = 'calculator more';
    const string CALCULATOR_MORE_CODE_ID = 'system_calculator_more';
    const string CALCULATOR_ARGUMENT = 'calculator argument';
    const string CALCULATOR_ARGUMENT_CODE_ID = 'system_calculator_argument';
    const string CALCULATOR_VERDICT = 'calculator verdict';
    const string CALCULATOR_VERDICT_CODE_ID = 'system_calculator_verdict';
    const string CALCULATOR_SCALE = 'calculator scale';
    const string CALCULATOR_SCALE_CODE_ID = 'system_calculator_scale';
    const string CALCULATOR_DIFFERENCE = 'calculator difference';
    const string CALCULATOR_DIFFERENCE_CODE_ID = 'system_calculator_difference';
    const string CALCULATOR_CELL = 'calculator cell';
    const string CALCULATOR_CELL_CODE_ID = 'system_calculator_cell';
    const string CALCULATOR_SENSITIVITY = 'calculator sensitivity';
    const string CALCULATOR_SENSITIVITY_CODE_ID = 'system_calculator_sensitivity';
    // the elements of a calculator page for a decision e.g. a vote by their code id
    const array CALCULATOR_PROPOSED = [
        self::CALCULATOR_SLIDER => self::CALCULATOR_SLIDER_CODE_ID,
        self::CALCULATOR_PRESET => self::CALCULATOR_PRESET_CODE_ID,
        self::CALCULATOR_TAG => self::CALCULATOR_TAG_CODE_ID,
        self::CALCULATOR_MORE => self::CALCULATOR_MORE_CODE_ID,
        self::CALCULATOR_ARGUMENT => self::CALCULATOR_ARGUMENT_CODE_ID,
        self::CALCULATOR_VERDICT => self::CALCULATOR_VERDICT_CODE_ID,
        self::CALCULATOR_SCALE => self::CALCULATOR_SCALE_CODE_ID,
        self::CALCULATOR_DIFFERENCE => self::CALCULATOR_DIFFERENCE_CODE_ID,
        self::CALCULATOR_CELL => self::CALCULATOR_CELL_CODE_ID,
        self::CALCULATOR_SENSITIVITY => self::CALCULATOR_SENSITIVITY_CODE_ID,
    ];
    const string CALCULATOR_COLOUR = 'calculator colour';
    const string CALCULATOR_DECIMALS = 'calculator decimals';
    const string CALCULATOR_DECIMALS_CODE_ID = 'system_calculator_decimals';
    const string CALCULATOR_RESULT_WIDTH = 'calculator result width';
    const string CALCULATOR_RESULT_WIDTH_CODE_ID = 'system_calculator_result_width';
    const string CALCULATOR_WARNING = 'calculator warning';
    const string CALCULATOR_WARNING_CODE_ID = 'system_calculator_warning';
    const string CALCULATOR_READ_ONLY = 'calculator read only';
    const string CALCULATOR_READ_ONLY_CODE_ID = 'system_calculator_read_only';
    const string CALCULATOR_CHECKBOX = 'calculator checkbox';
    const string CALCULATOR_CHECKBOX_CODE_ID = 'system_calculator_checkbox';
    const string CALCULATOR_REPEAT = 'calculator repeat';
    const string CALCULATOR_REPEAT_CODE_ID = 'system_calculator_repeat';
    // the kinds of an input field of a calculator page by their code id
    const array CALCULATOR_FIELD_KINDS = [
        self::CALCULATOR_READ_ONLY => self::CALCULATOR_READ_ONLY_CODE_ID,
        self::CALCULATOR_CHECKBOX => self::CALCULATOR_CHECKBOX_CODE_ID,
        self::CALCULATOR_REPEAT => self::CALCULATOR_REPEAT_CODE_ID,
    ];
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
        self::CALCULATOR_NUMBER_NOTE => self::CALCULATOR_NUMBER_NOTE_CODE_ID,
        self::CALCULATOR_WARNING => self::CALCULATOR_WARNING_CODE_ID,
    ];
    // the keywords of the calculator and of the input fields by their code id, which the data of a
    // phrase (see phrase_data) never walks through, because every page phrase links to them
    const array CALCULATOR_KEYWORDS = [
        ...triples::CALCULATOR_LISTS,
        ...triples::CALCULATOR_PROPERTIES,
        ...triples::FIELD_PROPERTIES,
        ...triples::FIELD_TYPES,
        ...triples::CALCULATOR_FIELD_KINDS,
        ...triples::CALCULATOR_DETAIL_FLAGS,
        ...triples::CALCULATOR_BLOCKS,
        ...triples::CALCULATOR_PROPOSED,
        ...triples::CALCULATOR_BUTTONS,
        self::CALCULATOR_CASE => self::CALCULATOR_CASE_CODE_ID,
        self::CALCULATOR_DECIMALS => self::CALCULATOR_DECIMALS_CODE_ID,
        self::CALCULATOR_RESULT_WIDTH => self::CALCULATOR_RESULT_WIDTH_CODE_ID,
    ];
    // the keywords of the definition of a table by their code id: the own columns, the own sorting and the
    // parameters of editor_views.json; the phraseData walk stops at them like at the calculator keywords,
    // because every table definition links to them, and a phrase with own columns adds the rows of its table
    const array TABLE_KEYWORDS = [
        self::TABLE_COLUMN => self::TABLE_COLUMN_CODE_ID,
        self::TABLE_SORT => self::TABLE_SORT_CODE_ID,
        self::SYSTEM_TABLE_PARAMETER => self::SYSTEM_TABLE_PARAMETER_CODE_ID,
        self::SYSTEM_TABLE_COLUMN_PARAMETER => self::SYSTEM_TABLE_COLUMN_PARAMETER_CODE_ID,
        self::SYSTEM_COLUMNS_ALL => self::SYSTEM_COLUMNS_ALL_CODE_ID,
        self::SYSTEM_COLUMN_LEVEL => self::SYSTEM_COLUMN_LEVEL_CODE_ID,
        self::SYSTEM_COLUMN_ORDER => self::SYSTEM_COLUMN_ORDER_CODE_ID,
        self::SYSTEM_COLUMN_ORDER_RULE => self::SYSTEM_COLUMN_ORDER_RULE_CODE_ID,
        self::SYSTEM_COLUMN_GROUPS => self::SYSTEM_COLUMN_GROUPS_CODE_ID,
        self::SYSTEM_COLUMN_FROZEN => self::SYSTEM_COLUMN_FROZEN_CODE_ID,
        self::SYSTEM_COLUMN_WIDTH => self::SYSTEM_COLUMN_WIDTH_CODE_ID,
        self::SYSTEM_COLUMN_HIDDEN_USER => self::SYSTEM_COLUMN_HIDDEN_USER_CODE_ID,
        self::SYSTEM_COLUMN_PIVOT_PHRASE => self::SYSTEM_COLUMN_PIVOT_PHRASE_CODE_ID,
        self::SYSTEM_ROW => self::SYSTEM_ROW_CODE_ID,
        self::SYSTEM_ROW_PHRASE => self::SYSTEM_ROW_PHRASE_CODE_ID,
        self::SYSTEM_ROW_VERB => self::SYSTEM_ROW_VERB_CODE_ID,
        self::SYSTEM_ROW_DIRECTION => self::SYSTEM_ROW_DIRECTION_CODE_ID,
        self::SYSTEM_ROW_FILTER => self::SYSTEM_ROW_FILTER_CODE_ID,
        self::SYSTEM_ROW_ORDER => self::SYSTEM_ROW_ORDER_CODE_ID,
        self::SYSTEM_ROW_ORDER_RULE => self::SYSTEM_ROW_ORDER_RULE_CODE_ID,
        self::SYSTEM_ROW_LIMIT => self::SYSTEM_ROW_LIMIT_CODE_ID,
        self::SYSTEM_ROW_PAGE_SIZE => self::SYSTEM_ROW_PAGE_SIZE_CODE_ID,
        self::SYSTEM_ROW_PAGE => self::SYSTEM_ROW_PAGE_CODE_ID,
        self::SYSTEM_ROW_SHOW_ALL => self::SYSTEM_ROW_SHOW_ALL_CODE_ID,
        self::SYSTEM_ROW_FROZEN => self::SYSTEM_ROW_FROZEN_CODE_ID,
        self::SYSTEM_ROW_PINNED => self::SYSTEM_ROW_PINNED_CODE_ID,
        self::SYSTEM_ROW_HIDDEN_USER => self::SYSTEM_ROW_HIDDEN_USER_CODE_ID,
        self::SYSTEM_ROW_EMPTY => self::SYSTEM_ROW_EMPTY_CODE_ID,
        self::SYSTEM_HIERARCHY => self::SYSTEM_HIERARCHY_CODE_ID,
        self::SYSTEM_HIERARCHY_VERB => self::SYSTEM_HIERARCHY_VERB_CODE_ID,
        self::SYSTEM_HIERARCHY_EXPAND => self::SYSTEM_HIERARCHY_EXPAND_CODE_ID,
        self::SYSTEM_HIERARCHY_MAX_DEPTH => self::SYSTEM_HIERARCHY_MAX_DEPTH_CODE_ID,
        self::SYSTEM_HIERARCHY_INDENT => self::SYSTEM_HIERARCHY_INDENT_CODE_ID,
        self::SYSTEM_HIERARCHY_PARENT_VALUE => self::SYSTEM_HIERARCHY_PARENT_VALUE_CODE_ID,
        self::SYSTEM_HIERARCHY_ORPHANS => self::SYSTEM_HIERARCHY_ORPHANS_CODE_ID,
        self::SYSTEM_HIERARCHY_MULTI_PARENT => self::SYSTEM_HIERARCHY_MULTI_PARENT_CODE_ID,
        self::SYSTEM_HIERARCHY_SORT_SCOPE => self::SYSTEM_HIERARCHY_SORT_SCOPE_CODE_ID,
        self::SYSTEM_VALUE => self::SYSTEM_VALUE_CODE_ID,
        self::SYSTEM_VALUE_TYPE => self::SYSTEM_VALUE_TYPE_CODE_ID,
        self::SYSTEM_VALUE_FORMAT => self::SYSTEM_VALUE_FORMAT_CODE_ID,
        self::SYSTEM_VALUE_UNIT => self::SYSTEM_VALUE_UNIT_CODE_ID,
        self::SYSTEM_VALUE_SCALE => self::SYSTEM_VALUE_SCALE_CODE_ID,
        self::SYSTEM_VALUE_TIME => self::SYSTEM_VALUE_TIME_CODE_ID,
        self::SYSTEM_VALUE_SOURCE_MODE => self::SYSTEM_VALUE_SOURCE_MODE_CODE_ID,
        self::SYSTEM_VALUE_SHOW_SOURCE => self::SYSTEM_VALUE_SHOW_SOURCE_CODE_ID,
        self::SYSTEM_VALUE_SHOW_CONFIDENCE => self::SYSTEM_VALUE_SHOW_CONFIDENCE_CODE_ID,
        self::SYSTEM_VALUE_MISSING => self::SYSTEM_VALUE_MISSING_CODE_ID,
        self::SYSTEM_VALUE_ALIGN => self::SYSTEM_VALUE_ALIGN_CODE_ID,
        self::SYSTEM_VALUE_TRUNCATE => self::SYSTEM_VALUE_TRUNCATE_CODE_ID,
        self::SYSTEM_VALUE_CONDITIONAL_STYLE => self::SYSTEM_VALUE_CONDITIONAL_STYLE_CODE_ID,
        self::SYSTEM_VALUE_DELTA => self::SYSTEM_VALUE_DELTA_CODE_ID,
        self::SYSTEM_VALUE_EDITABLE => self::SYSTEM_VALUE_EDITABLE_CODE_ID,
        self::SYSTEM_VALUE_UNMATCHED => self::SYSTEM_VALUE_UNMATCHED_CODE_ID,
        self::SYSTEM_AGGREGATION => self::SYSTEM_AGGREGATION_CODE_ID,
        self::SYSTEM_TOTALS_ROW => self::SYSTEM_TOTALS_ROW_CODE_ID,
        self::SYSTEM_TOTALS_COLUMN => self::SYSTEM_TOTALS_COLUMN_CODE_ID,
        self::SYSTEM_SUBTOTALS => self::SYSTEM_SUBTOTALS_CODE_ID,
        self::SYSTEM_AGGREGATION_FUNCTION => self::SYSTEM_AGGREGATION_FUNCTION_CODE_ID,
        self::SYSTEM_AGGREGATION_SCOPE => self::SYSTEM_AGGREGATION_SCOPE_CODE_ID,
        self::SYSTEM_HEADER => self::SYSTEM_HEADER_CODE_ID,
        self::SYSTEM_HEADER_LABEL => self::SYSTEM_HEADER_LABEL_CODE_ID,
        self::SYSTEM_HEADER_ROTATE => self::SYSTEM_HEADER_ROTATE_CODE_ID,
        self::SYSTEM_HEADER_SHOW_UNIT => self::SYSTEM_HEADER_SHOW_UNIT_CODE_ID,
        self::SYSTEM_HEADER_TOOLTIP => self::SYSTEM_HEADER_TOOLTIP_CODE_ID,
        self::SYSTEM_ROW_LABEL_MODE => self::SYSTEM_ROW_LABEL_MODE_CODE_ID,
        self::SYSTEM_LAYOUT => self::SYSTEM_LAYOUT_CODE_ID,
        self::SYSTEM_TITLE => self::SYSTEM_TITLE_CODE_ID,
        self::SYSTEM_CAPTION => self::SYSTEM_CAPTION_CODE_ID,
        self::SYSTEM_DENSITY => self::SYSTEM_DENSITY_CODE_ID,
        self::SYSTEM_STRIPED => self::SYSTEM_STRIPED_CODE_ID,
        self::SYSTEM_BORDERS => self::SYSTEM_BORDERS_CODE_ID,
        self::SYSTEM_TRANSPOSE => self::SYSTEM_TRANSPOSE_CODE_ID,
        self::SYSTEM_MAX_WIDTH => self::SYSTEM_MAX_WIDTH_CODE_ID,
        self::SYSTEM_RESPONSIVE_MODE => self::SYSTEM_RESPONSIVE_MODE_CODE_ID,
        self::SYSTEM_BACKGROUND => self::SYSTEM_BACKGROUND_CODE_ID,
        self::SYSTEM_DESIGN => self::SYSTEM_DESIGN_CODE_ID,
        self::SYSTEM_INTERACTION => self::SYSTEM_INTERACTION_CODE_ID,
        self::SYSTEM_SORTABLE => self::SYSTEM_SORTABLE_CODE_ID,
        self::SYSTEM_FILTERABLE => self::SYSTEM_FILTERABLE_CODE_ID,
        self::SYSTEM_SEARCHABLE => self::SYSTEM_SEARCHABLE_CODE_ID,
        self::SYSTEM_SELECTABLE => self::SYSTEM_SELECTABLE_CODE_ID,
        self::SYSTEM_DRILLDOWN => self::SYSTEM_DRILLDOWN_CODE_ID,
        self::SYSTEM_REORDERABLE => self::SYSTEM_REORDERABLE_CODE_ID,
        self::SYSTEM_RESIZABLE => self::SYSTEM_RESIZABLE_CODE_ID,
        self::SYSTEM_REFRESH_MODE => self::SYSTEM_REFRESH_MODE_CODE_ID,
        self::SYSTEM_CONTEXT => self::SYSTEM_CONTEXT_CODE_ID,
        self::SYSTEM_VIEW_OWNER => self::SYSTEM_VIEW_OWNER_CODE_ID,
        self::SYSTEM_VIEW_SHARED => self::SYSTEM_VIEW_SHARED_CODE_ID,
        self::SYSTEM_LANGUAGE => self::SYSTEM_LANGUAGE_CODE_ID,
        self::SYSTEM_PROFILE => self::SYSTEM_PROFILE_CODE_ID,
        self::SYSTEM_TIME_REFERENCE => self::SYSTEM_TIME_REFERENCE_CODE_ID,
        self::SYSTEM_EXPORT => self::SYSTEM_EXPORT_CODE_ID,
        self::SYSTEM_EXPORT_FORMATS => self::SYSTEM_EXPORT_FORMATS_CODE_ID,
        self::SYSTEM_EXPORT_SCOPE => self::SYSTEM_EXPORT_SCOPE_CODE_ID,
        self::SYSTEM_EXPORT_INCLUDE_SOURCES => self::SYSTEM_EXPORT_INCLUDE_SOURCES_CODE_ID,
        self::SYSTEM_STATE => self::SYSTEM_STATE_CODE_ID,
        self::SYSTEM_PERSIST_USER_CHANGES => self::SYSTEM_PERSIST_USER_CHANGES_CODE_ID,
        self::SYSTEM_RESET_TO_DEFAULT => self::SYSTEM_RESET_TO_DEFAULT_CODE_ID,
        self::SYSTEM_VIEW_VERSION => self::SYSTEM_VIEW_VERSION_CODE_ID,
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
