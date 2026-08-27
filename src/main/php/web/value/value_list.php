<?php

/*

    web/value/value_list.php - the display extension of the api value list object
    ------------------------

    to create the HTML code to display a list of values


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

namespace Zukunft\ZukunftCom\main\php\web\value;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
// TODO move phr_ids to shared objects
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::GROUP . 'group.php';
include_once html_paths::GROUP . 'group_list.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::REF . 'source.php';
//include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VALUE . 'value.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'word_list.php';
include_once html_paths::MODEL_PHRASE . 'phr_ids.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_HELPER . 'CombineObject.php';
include_once html_paths::SHARED_HELPER . 'IdObject.php';
include_once html_paths::SHARED_HELPER . 'TextIdObject.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'position_types.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\group\group;
use Zukunft\ZukunftCom\main\php\web\group\group_list;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\library;

class value_list extends ListBase
{

    // show every row of a table instead of the configured number (like type_list::LIMIT_ALL)
    const int LIMIT_ALL = 0;
    // the probability range of a value is shown behind its centre value e.g. "2.2 (0.88 – 5.5)"
    const string RANGE_START = ' (';
    const string RANGE_SEP = ' – ';
    const string RANGE_END = ')';
    // joins the phrase id and a counter to the id of a further column of the same phrase in
    // another unit, so that the id can never clash with a phrase id
    const string UNIT_COLUMN_SEP = '|';

    /*
     * set and get
     */

    /**
     * set the vars of a value object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new value());
    }


    /*
     * load
     */

    /**
     * add the values of any of the given phrases to this list
     *
     * @param phrase_list $phr_lst the phrases whose values should be loaded
     * @param user_message $msg to report a problem of the api message to the user
     * @return bool true if at least one value has been loaded
     */
    function load_by_phr_lst(phrase_list $phr_lst, user_message $msg): bool
    {
        $result = false;
        $rest = new rest_call();

        $data = array();
        // comma separated like every other id list of the api e.g. url_var::ID_LST
        $data[api::JSON_LIST_PHRASE_IDS] = implode(',', $phr_lst->ids());
        $json_body = $rest->api_get(self::class, $data);
        $msg->merge($this->api_mapper($json_body));
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /**
     * the phrases that the values of this list carry
     *
     * @return phrase_list every phrase of every value group, each phrase only once
     */
    function phrase_list(): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $val) {
            foreach ($val->grp->phr_lst()->lst() as $phr) {
                if (!$result->has_id($phr->id())) {
                    $result->add_phrase($phr);
                }
            }
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * get the first value of the list that is related to all given phrase names
     * TODO use a memory db
     * @param array $names list of phrase names
     * @return value|null this first matching value or null if no value is found
     */
    function get_by_names(array $names): ?value
    {
        $result = null;
        foreach ($this->lst() as $val) {
            if ($result == null) {
                if ($val->match_all($names)) {
                    $result = $val;
                }
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a value to the list
     * @param value|IdObject|TextIdObject|CombineObject|null $to_add the value that should be added
     * @param user_message $msg to report which entry is double
     * @returns bool true if the value has been added
     */
    function add(value|IdObject|TextIdObject|CombineObject|null $to_add, user_message $msg): bool
    {
        $result = false;
        if (!in_array($to_add->id(), $this->id_lst())) {
            $this->add_direct($to_add);
            $this->set_hash_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * get a list with the values related directly to the given word, triple or source
     *
     * @param word|triple|source|formula|db_object|type_object|null $dbo to filter the values
     * @return value_list with only the direct linked values
     */
    function filter(
        user_message $msg,
        word|triple|source|formula|db_object|type_object|null $dbo = null,
        ?phrase_list $ctx_lst = null
    ): value_list
    {
        $val_lst = new value_list();
        if ($dbo::class == word::class or $dbo::class == triple::class) {
            // a value of a child phrase belongs to the page phrase too, e.g. a "global warming"
            // value is shown on the "global problem" page, because the context list contains
            // the triple "global warming (global problem)"
            $child_names = $ctx_lst?->child_names($dbo->phrase()) ?? [];
            foreach ($this->lst() as $val) {
                if ($val->has_phrase($dbo->phrase(), $msg)) {
                    $val_lst->add($val, $msg);
                } elseif (array_intersect($child_names, $val->grp->phr_lst()->names()) != []) {
                    $val_lst->add($val, $msg);
                }
            }
        }
        if ($dbo::class == source::class) {
            foreach ($this->lst() as $val) {
                // a value without a source belongs to no source, so the null source id must not
                // match the id 0 of a source that is not yet written to the database
                if ($val->source_id() != null and $val->source_id() == $dbo->id()) {
                    $val_lst->add($val, $msg);
                }
            }
        }
        return $val_lst;
    }

    /**
     * sort this value list in place so that the value with the highest impact is first
     * the impact of a value is the highest impact of the phrases it is assigned to
     * @return void
     */
    function sort_by_impact(): void
    {
        $lst = $this->lst();
        // impact first, then number, then the group name so that values with the same impact and
        // number keep a deterministic order that does not depend on the value (group) id: a value's
        // id is its phrase group key, packed from the word/triple db ids, which the seed assigns
        // serially and shift between test database rebuilds, so an id tiebreak reorders the list per
        // rebuild; the group name is built from the (stable) phrase names (see docs/llm/frontend.md)
        usort($lst, fn(value $a, value $b) => $b->impact() <=> $a->impact()
            ?: $b->number() <=> $a->number()
                ?: strcmp($a->name() ?? '', $b->name() ?? ''));
        $this->set_lst($lst);
    }


    /*
     * display
     */

    /**
     * create the html code to show a list of values
     * TODO use a more general parent function
     *
     * @param phrase_list $context_phr_lst list of phrases that should be excluded from the value name because humans would assume these phrases
     * @param string $back list of the last view to suggest the best follow-up view
     * @param string $style to define e.g. the width of the list
     * @param int|null $limit the max number of entries to show
     * @param int|null $page the offset if there are more entries that could be shown at once
     * @return string the html code to display the values to the user
     */
    function list(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        string       $back = '',
        string       $style = '',
        ?int         $limit = null,
        ?int         $page = null
    ): string
    {
        $html = new html_base();

        $result = '';

        if (!$this->is_empty()) {
            // sort so the highest impact value is shown first and the order is always deterministic
            $this->sort_by_impact();
            if ($limit == null) {
                $limit = $this->configured_limit($msg);
            }

            $i = 0;
            foreach ($this->lst() as $val) {
                if ($i <= $limit) {
                    if ($i < $limit) {
                        $result .= $this->value_line($val, $msg, $context_phr_lst, $back);
                    } else {
                        $diff = $this->count() - $i;
                        if ($diff > 0) {
                            $result .= ' ' . $this->more_tail($diff, $context_phr_lst);
                        }
                    }
                    $i++;
                }
            }
        }
        return $result;
    }

    /**
     * render one value as a line for a value list: the phrase link(s) on the left and the numeric
     * value (as an edit link) on the right; the shared line renderer of list() and list_most_relevant()
     *
     * @param value $val the value to render
     * @param phrase_list $context_phr_lst the phrases assumed by the reader and therefore left out of the line
     * @param string $back the last view to suggest the best follow-up view
     * @return string the html code of one value line
     */
    private function value_line(value $val, user_message $msg, phrase_list $context_phr_lst, string $back): string
    {
        $html = new html_base();
        // keep the phrases and the value of one value on a single row (text-nowrap) so they are never
        // wrapped and the html snapshot keeps them on one line as well (see library::format_html)
        $line = $val->grp->name_link_list($context_phr_lst) . ' ' . $val->value_edit($msg, $back);
        $row = $html->span($line, styles::TEXT_NOWRAP) . $html->lf();
        return $row;
    }

    /**
     * create the html code to show the most relevant values grouped for a quick overview, ordered
     * - first the time groups (newest period first) where a time word (e.g. "2022") is shared by more
     *   than one value, each line showing the remaining phrase on the left and the number on the right
     * - then a group per phrase that is used by more than the configured minimum of values, the groups
     *   ordered by the aggregated impact of their values
     * - last the remaining values sorted by impact descending (with the usual limit and "... more" tail)
     * see docs/llm/pending_next_launch.md for the feature description
     *
     * @param phrase_list $context_phr_lst phrases assumed by the reader and left out of each value line
     * @param string $back the last view to suggest the best follow-up view
     * @param string $style to define e.g. the width of the list
     * @return string the html code to display the grouped values to the user
     */
    function list_most_relevant(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        string       $back = '',
        string       $style = '',
        ?int         $limit = null
    ): string
    {
        $result = '';
        if (!$this->is_empty()) {
            $html = new html_base();
            // the limit is a page total: however the values are grouped, the page never shows
            // more values than the configured number, so that the user messages below the view
            // stay visible (see docs/llm/frontend.md); the remaining budget is passed from
            // section to section and the values that do not fit are counted in one more tail
            if ($limit == null) {
                $limit = $this->configured_limit($msg);
            }
            // the values still to be placed; each section consumes the values it groups
            $pool = $this->lst();
            [$time_html, $pool, $limit] = $this->time_groups($pool, $context_phr_lst, $msg, $back, $limit);
            [$phrase_html, $pool, $limit] = $this->relevant_phrase_groups($pool, $msg, $context_phr_lst, $back, $limit);
            $rest_html = $this->impact_group($pool, $msg, $context_phr_lst, $back, $limit);
            // the whole grouped list is one 'value-list' container div; the caller style (e.g. the
            // list width) is added as a second css class of that container
            $cls = $style != '' ? styles::VALUE_LIST . ' ' . $style : styles::VALUE_LIST;
            $result = $html->div($time_html . $phrase_html . $rest_html, $cls);
        }
        return $result;
    }

    /**
     * section one of list_most_relevant: group the values by their time phrase and render each time
     * word that is shared by more than one value as a group, newest period first
     *
     * @param array $pool the values still to be placed
     * @param phrase_list $context_phr_lst phrases left out of each value line
     * @param string $back the last view to suggest the best follow-up view
     * @param int $budget the number of values that may still be shown on the page
     * @return array [string the html of the time groups,
     *                array the values not put into a rendered time group,
     *                int the remaining budget]
     */
    private function time_groups(
        array        $pool,
        phrase_list  $context_phr_lst,
        user_message $msg,
        string       $back,
        int          $budget
    ): array
    {
        // bucket the values by the id of their time phrase, keeping the time phrase per bucket
        $buckets = [];
        $time_phr = [];
        foreach ($pool as $val) {
            $tphr = $val->time_phrase($msg);
            if ($tphr != null) {
                $buckets[$tphr->id()][] = $val;
                $time_phr[$tphr->id()] = $tphr;
            }
        }
        // keep only the time words shared by more than one value, newest (name descending) first
        $ids = [];
        foreach ($buckets as $id => $vals) {
            if (count($vals) > 1) {
                $ids[] = $id;
            }
        }
        usort($ids, fn($a, $b) => strcmp($time_phr[$b]->name(), $time_phr[$a]->name()));

        $result = '';
        $grouped = [];
        $per_group = $this->configured_limit($msg);
        foreach ($ids as $id) {
            // render a group only while the page budget allows more values; a group is rendered
            // whole (up to the per group limit), so the last rendered group may overshoot the
            // budget slightly; the values of a skipped group stay in the pool, so the final
            // more tail of impact_group counts them
            if ($budget > 0) {
                $result .= $this->group_block($time_phr[$id], $buckets[$id], $context_phr_lst, $msg, $back);
                $budget = $budget - min(count($buckets[$id]), $per_group);
                foreach ($buckets[$id] as $val) {
                    $grouped[$val->id()] = true;
                }
            }
        }
        $rest = array_values(array_filter($pool, fn(value $val) => !isset($grouped[$val->id()])));
        return [$result, $rest, $budget];
    }

    /**
     * section two of list_most_relevant: group the remaining values by a phrase that is used by more
     * than the configured minimum of values, the groups ordered by the aggregated impact of the values
     *
     * @param array $pool the values still to be placed
     * @param phrase_list $context_phr_lst phrases left out of each value line
     * @param string $back the last view to suggest the best follow-up view
     * @param int $budget the number of values that may still be shown on the page
     * @return array [string the html of the phrase groups,
     *                array the values not put into a rendered phrase group,
     *                int the remaining budget]
     */
    private function relevant_phrase_groups(
        array        $pool,
        user_message $msg,
        phrase_list  $context_phr_lst,
        string       $back,
        int          $budget
    ): array
    {
        $min = config::MIN_PHRASE_GROUP;
        [$phr_by_id, $val_phr_ids] = $this->phrase_ranking($pool, $msg, $context_phr_lst, $min);

        // greedily assign each still-remaining value to the highest-impact phrase group it belongs to
        $result = '';
        $remaining = $pool;
        $per_group = $this->configured_limit($msg);
        foreach ($phr_by_id as $id => $phr) {
            // render a group only while the page budget allows more values (like time_groups);
            // the values of a skipped group stay in the pool for the final more tail
            if ($budget > 0) {
                [$members, $rest] = $this->split_by_phrase($remaining, $id, $val_phr_ids);
                if (count($members) > $min) {
                    $result .= $this->group_block($phr, $members, $context_phr_lst, $msg, $back);
                    $budget = $budget - min(count($members), $per_group);
                    $remaining = $rest;
                }
            }
        }
        return [$result, $remaining, $budget];
    }

    /**
     * show the values of this list in up to position_types::MAX_SIDE_COLUMNS columns that are shown
     * side by side on the widest screens and wrap onto fewer columns (down to one) as the screen gets
     * narrower, using the same wrapping row as the 'side or below' components of a view;
     * each column is headed by one of the phrases used most often within the values
     * (e.g. inhabitants for the city of Zurich) and lists the values that use this phrase;
     * if a column is still free, the values that share no column phrase fill it ordered by impact
     *
     * @param phrase_list $context_phr_lst the phrases assumed by the reader e.g. the phrase of the page
     * @param string $back the last view to suggest the best follow-up view
     * @return string the html code of the value columns or '' if this list is empty
     */
    function columns_by_phrase(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        string       $back = ''
    ): string
    {
        $result = '';
        if (!$this->is_empty()) {
            $html = new html_base();
            // a column phrase needs to be used by at least two values, else the column shows one line
            [$phr_by_id, $val_phr_ids] = $this->phrase_ranking(
                $this->lst(), $msg, $context_phr_lst, config::MIN_PHRASE_GROUP - 1);
            $col_lst = [];
            $remaining = $this->lst();
            foreach ($phr_by_id as $id => $phr) {
                // no break in the loop, so the free column count is checked per phrase
                if (count($col_lst) < position_types::MAX_SIDE_COLUMNS) {
                    [$members, $rest] = $this->split_by_phrase($remaining, $id, $val_phr_ids);
                    if ($members != []) {
                        $col_lst[] = $this->group_block($phr, $members, $context_phr_lst, $msg, $back);
                        $remaining = $rest;
                    }
                }
            }
            // the values that share no column phrase fill the last free column,
            // limited like every column to the configured number of values
            if ($remaining != [] and count($col_lst) < position_types::MAX_SIDE_COLUMNS) {
                $col_lst[] = $this->impact_group(
                    $remaining, $msg, $context_phr_lst, $back, $this->configured_limit($msg));
            }
            $result = $html->div_row_wrapping_cols($col_lst, $msg);
        }
        return $result;
    }

    /**
     * show the values of this list as a table with one column per phrase used most often within the
     * values (e.g. inhabitants and area for the city of Zurich) and one row per remaining phrase
     * combination (e.g. per year), so that the values of one row can be compared column by column;
     * the column phrases are ranked like the value columns (columns_by_phrase), but here the values
     * are lined up by their remaining phrases instead of listed below each other per column;
     * the values that share no column phrase are shown in a last column headed by "Values"
     *
     * @param phrase_list $context_phr_lst the phrases assumed by the reader e.g. the phrase of the page
     * @param string $back the last view to suggest the best follow-up view
     * @param array $col_order the defined column phrase names, the most important column first
     * @param bool $with_header true to name the selected phrase centred above the table, false
     *                          where the page already says which phrase the table is about; a
     *                          table of several rows shows more than one item of the phrase, so
     *                          its header is the plural in a headline, while a table of one row
     *                          keeps the singular in the smaller label size
     * @param bool $with_border true for the bordered standard table, false for a table without
     *                          the lines between the cells e.g. within a page that groups tables
     * @param phrase_list|null $rel_lst the phrases related to the page phrase, which define the
     *                                  columns and say which phrase belongs into a phrase column
     * @param int|null $limit the max number of rows to show, null for the size named by the url
     *                        or else the configured limit, and self::LIMIT_ALL for every row
     * @param array $url_array the url parameters of the page that shows the table, so that the
     *                         "... more" tail can call the same page with the next list size and
     *                         the url can name the list size and the list page; an empty array
     *                         if the page is not known, e.g. for a table taken out of its page
     * @return string the html code of the value table or '' if this list is empty
     */
    function table_by_related_columns(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        string       $back = '',
        array        $col_order = [],
        bool         $with_header = false,
        bool         $with_border = true,
        ?phrase_list $rel_lst = null,
        ?int         $limit = null,
        array        $url_array = []
    ): string
    {
        $result = '';
        if (!$this->is_empty()) {
            $html = new html_base();
            // the row order follows the impact, so it never depends on the api/db row order
            $this->sort_by_impact();
            // a phrase that every value carries describes the whole table, so the header names
            // it once and no row repeats it
            $tbl_phr = $this->phrases_of_every_value(
                $this->column_names_with_parts($col_order, $rel_lst));
            // the unit and the phrases of the whole table say nothing about a single row, so
            // both are assumed like the phrase of the page
            $grp_ctx = $this->context_with_units($context_phr_lst, $msg, $tbl_phr);
            // a column phrase needs to be used by at least two values, else the column has one entry
            [$phr_by_id, $val_phr_ids] = $this->phrase_ranking(
                $this->lst(), $msg, $grp_ctx, config::MIN_PHRASE_GROUP - 1);
            // a phrase that the system column tiers define as a column wins over the impact
            // ranking and is used even if only one value carries it
            $all_by_id = $phr_by_id;
            if ($col_order != []) {
                [$all_by_id, $val_phr_ids] = $this->phrase_ranking(
                    $this->lst(), $msg, $grp_ctx, 0);
                $phr_by_id = $this->columns_by_definition(
                    $all_by_id, $phr_by_id, $col_order, $rel_lst);
            }

            // the column phrases, per column the phrases a value must carry to belong to it and
            // per value the column it belongs to
            $col_phr = [];
            $col_parts = [];
            $val_col = [];
            $remaining = $this->lst();
            foreach ($phr_by_id as $id => $phr) {
                // a defined column is shown on the screens its tier says, so only the columns that
                // the data suggests are limited to the number that fit on the widest screen; no
                // break in the loop, so the free column count is checked per phrase
                $defined = in_array($phr->name(), $col_order);
                if ($defined or count($col_phr) < position_types::MAX_SIDE_COLUMNS) {
                    $parts = $this->column_parts($phr, $all_by_id);
                    [$members, $rest] = $this->split_by_parts($remaining, $parts, $val_phr_ids);
                    // a column shows one measure, so a phrase with values in several units gets
                    // one column per unit, the unit of the most relevant value first
                    foreach ($this->split_by_unit($members, $msg) as $unit_members) {
                        $col_id = $this->unit_column_id($id, $col_phr);
                        $col_phr[$col_id] = $phr;
                        $col_parts[$col_id] = $parts;
                        foreach ($unit_members as $val) {
                            $val_col[$val->id()] = $col_id;
                        }
                    }
                    if ($members != []) {
                        $remaining = $rest;
                    }
                }
            }
            // the values that share no column phrase get a last column of their own, so that no
            // value of a shown row is silently dropped; whether that column is needed is decided
            // once the rows are cut, because a value behind the limit is not shown either

            // a defined column that no value carries names a phrase of the row instead of a
            // number, e.g. the "solution" column shows the solution of the problem row
            $phr_col = $this->phrase_columns($col_order, $col_phr, $rel_lst, $msg);
            // the names that belong into each phrase column, read once for the whole table
            $phr_col_names = [];
            foreach ($phr_col as $phr_col_id => $phr) {
                $phr_col_names[$phr_col_id] = $rel_lst->child_names($phr);
            }

            // per row the label and per row and column the value html
            $row_label = [];
            $cells = [];
            $phr_cells = [];
            foreach ($this->lst() as $val) {
                $col_id = $val_col[$val->id()] ?? '';
                $ctx = clone $grp_ctx;
                if ($col_id !== '') {
                    // the phrases that put the value into its column do not name the row, which
                    // for a column of two phrases are both of them e.g. "potential" and "loss"
                    foreach ($col_parts[$col_id] as $part_id) {
                        $ctx->add_phrase($all_by_id[$part_id]);
                    }
                }
                // a phrase shown in a column of its own does not name the row any more, so it is
                // added to the context before the row key is built
                $phr_cell = [];
                foreach (array_keys($phr_col) as $phr_col_id) {
                    $child = $this->phrase_of_column($val, $phr_col_names[$phr_col_id]);
                    if ($child != null) {
                        $ctx->add_phrase($child);
                        $phr_cell[$phr_col_id] = $child->name_link();
                    }
                }
                // the row is named by the phrases that are left after the context and the column
                // phrase, e.g. the year if the columns are inhabitants and area
                $row_key = $val->grp->name($ctx);
                if (!key_exists($row_key, $row_label)) {
                    $row_label[$row_key] = $val->grp->name_link_list($ctx);
                    $cells[$row_key] = [];
                }
                // the phrase of a row is the same for every value of that row, so it is set
                // instead of added, e.g. the solution is named once although the row has the
                // potential loss and the potential gain of the problem
                foreach ($phr_cell as $phr_col_id => $link) {
                    $phr_cells[$row_key][$phr_col_id] = $link;
                }
                // two values with the same row and column are shown in the same cell instead of
                // the second one replacing the first; the cell keeps the values, because a range
                // bound is shown behind its centre value and not as a value of its own
                $cells[$row_key][$col_id][] = $val;
            }

            // a page must not fill the screen, because the user messages are shown below the
            // view and would else be hidden below the fold, so the rows are cut to the number
            // named by the url or else the configured number before the header is built; the
            // url names the list page as well, so the cut starts at the first row of that page
            $row_limit = $limit ?? $this->row_limit($url_array, $msg);
            $shown_keys = array_keys($row_label);
            $first_row = 0;
            if ($row_limit != self::LIMIT_ALL) {
                $first_row = $this->first_row($url_array, $row_limit);
                $shown_keys = array_slice($shown_keys, $first_row, $row_limit);
            }
            // a defined column is shown even if it is empty, because the reader has asked for
            // it, but the rest column only exists because of the data, so it is shown only if a
            // row that the table shows really has a value without a column
            $rest_col = false;
            foreach ($shown_keys as $row_key) {
                if (($cells[$row_key][''] ?? []) != []) {
                    $rest_col = true;
                }
            }

            // a phrase column is defined like a value column, so both kinds are shown in one
            // order, e.g. the "solution" column between the "loss" and the "gain" column
            $col_ids = $this->column_id_order($col_order, $col_phr, $phr_col);

            // the row column is headed by the phrase that the page phrase is built from, e.g.
            // "problem" for the page phrase "global problem", and stays empty if that phrase is
            // no defined column, because the row phrases differ per row
            $row_col = $this->row_column($context_phr_lst, $col_order);
            $header = $html->th($row_col?->name_link() ?? '');
            // the unit describes the number of a whole column, so its own header names it
            $col_unit = $this->column_units($col_phr, $val_col, $msg);
            // the tier of a defined column says on which screens it is shown
            $col_style = [];
            foreach ($col_ids as $col_id) {
                $phr = $col_phr[$col_id] ?? $phr_col[$col_id];
                // a phrase column names a phrase of the row, so it has no unit
                $unit_lst = $col_unit[$col_id] ?? new phrase_list();
                $col_style[$col_id] = $this->column_style($phr->name(), $rel_lst);
                $header .= $html->th($this->column_header($phr, $unit_lst), '', $col_style[$col_id]);
            }
            if ($rest_col) {
                $header .= $html->th(msg_id::FORM_SUB_TITLE_VALUES->text());
            }
            $rows = $html->tr($header);
            foreach ($shown_keys as $row_key) {
                $row = $html->td($row_label[$row_key]);
                foreach ($col_ids as $col_id) {
                    // a phrase column names a phrase of the row, a value column its values
                    if (array_key_exists($col_id, $col_phr)) {
                        $row .= $this->cell(
                            $cells[$row_key][$col_id] ?? [], $msg, $back, $col_style[$col_id]);
                    } else {
                        $row .= $html->td($phr_cells[$row_key][$col_id] ?? '', $col_style[$col_id]);
                    }
                }
                if ($rest_col) {
                    $row .= $this->cell($cells[$row_key][''] ?? [], $msg, $back, '');
                }
                $rows .= $html->tr($row);
            }
            // the rows behind the shown ones, so a later page counts its own rest only
            $diff = count($row_label) - $first_row - count($shown_keys);
            if ($diff > 0) {
                // the empty cells of the more row hide with their column like every other cell
                $pad_styles = array_values($col_style);
                if ($rest_col) {
                    $pad_styles[] = '';
                }
                $more_url = $this->more_url($url_array, $row_limit, $msg);
                $rows .= $this->tr_more($diff, $context_phr_lst, $pad_styles, $more_url);
            }
            $result = $html->tbl($rows, $with_border ? html_base::SIZE_FULL : styles::TABLE_PUR);
            // the header names the phrase that the reader has selected centred above the table,
            // so that a table taken out of its page still says what it is about; more than one
            // row means more than one item of the phrase, so the header names it in the plural
            if ($with_header) {
                // a phrase of every value stays singular, because it describes the values of the
                // table and not its items, e.g. "global problems, potential"
                $tbl_name = $tbl_phr->name_link_list();
                if ($tbl_name != '') {
                    $tbl_name = ', ' . $tbl_name;
                }
                if (count($row_label) > 1) {
                    // a table of several items is a list of its own, so its header is a headline
                    $header_html = $html->text_h2($context_phr_lst->plural() . $tbl_name);
                } else {
                    // a table of one item only labels that item, so the header stays small
                    $header_html = $html->text_h3($context_phr_lst->name_link_list() . $tbl_name);
                }
                $result = $html->div_center($header_html) . $result;
            }
        }
        return $result;
    }

    /**
     * true if the phrase describes the number instead of the row or the column
     *
     * the scaling, the measure and the percent format all belong to the value, e.g. "35.2 billion
     * htp" or "10 percent", so such a phrase is shown once in the column header behind the phrase
     * that names the column and never heads a column of its own
     *
     * @param phrase $phr the phrase to check
     * @return bool true if the phrase is a unit of the number
     */
    private function is_unit(phrase $phr, user_message $msg): bool
    {
        return ($phr->is_scaling($msg) or $phr->is_measure($msg) or $phr->is_percent($msg));
    }

    /**
     * the phrases that a table neither names a row nor a column by
     *
     * a unit describes the number and is shown with it (e.g. "35.2 billion htp"), so like the
     * phrase of the page it says nothing about the row; the target layout in the view-validation
     * of solution_prio.json names the unit in the column header instead
     *
     * a marker like "low", "high" or "assumed" says how a number is stated and not what it is
     * about, so it names no row either; a range bound is shown behind its centre value (see
     * cell) and the estimate qualifier as the tooltip of the cell
     *
     * a phrase that every value carries describes the whole table, so the header names it once
     * and it says as little about a single row as the phrase of the page
     *
     * @param phrase_list $context_phr_lst the phrases assumed by the reader e.g. the phrase of the page
     * @param phrase_list $tbl_phr the phrases that every value of this list carries
     * @return phrase_list the assumed phrases plus every unit, marker and all-value phrase
     */
    private function context_with_units(
        phrase_list  $context_phr_lst,
        user_message $msg,
        phrase_list  $tbl_phr
    ): phrase_list
    {
        $result = clone $context_phr_lst;
        foreach ($this->lst() as $val) {
            foreach ($val->grp->phr_lst()->lst() as $phr) {
                // the same unit is used by many values, so a repeat is expected and no double
                if (!$result->has_id($phr->id())) {
                    if ($this->is_unit($phr, $msg) or $this->is_marker($phr)) {
                        $result->add_phrase($phr);
                    }
                }
            }
        }
        foreach ($tbl_phr->lst() as $phr) {
            if (!$result->has_id($phr->id())) {
                $result->add_phrase($phr);
            }
        }
        return $result;
    }

    /**
     * the phrases that every value of this list carries, e.g. "potential" in a table of the
     * potential loss and the potential gain of each problem
     *
     * such a phrase cannot tell one row from another, so the table header names it once instead
     * of every row repeating it; a single value shares all its phrases with itself, which would
     * leave its row without a name, so a list of one value has no shared phrase
     *
     * @param array $col_order the defined column phrase names, the leftmost column first
     * @return phrase_list the phrases of the first value that every other value carries too
     */
    private function phrases_of_every_value(array $col_order): phrase_list
    {
        $result = new phrase_list();
        if (count($this->lst()) > 1) {
            foreach ($this->phrases_of_all($this->lst())->lst() as $phr) {
                // a defined column shows its phrase in the column header already, so the reader
                // sees it there instead of in the table header
                if (!in_array($phr->name(), $col_order)) {
                    $result->add_phrase($phr);
                }
            }
        }
        return $result;
    }

    /**
     * the phrases that every one of the given values carries
     *
     * @param array $val_lst the values to compare e.g. the values of one column
     * @return phrase_list the phrases of the first value that every other value carries too
     */
    private function phrases_of_all(array $val_lst): phrase_list
    {
        $result = new phrase_list();
        $first = array_shift($val_lst);
        if ($first != null) {
            foreach ($first->grp->phr_lst()->lst() as $phr) {
                $shared = true;
                foreach ($val_lst as $val) {
                    if (!$val->grp->phr_lst()->has_id($phr->id())) {
                        $shared = false;
                    }
                }
                if ($shared) {
                    $result->add_phrase($phr);
                }
            }
        }
        return $result;
    }

    /**
     * per value column the scaling and measure phrases that every value of that column carries
     *
     * the unit describes the number and is the same for the whole column, so the column header
     * names it once instead of every row repeating it, e.g. "loss (trillion EUR)"
     *
     * @param array $col_phr the columns that hold a value, keyed by phrase id
     * @param array $val_col per value id the id of the column the value belongs to
     * @return array per column id the unit phrases of that column, the scaling first
     */
    private function column_units(array $col_phr, array $val_col, user_message $msg): array
    {
        $result = [];
        foreach (array_keys($col_phr) as $col_id) {
            $col_val_lst = [];
            foreach ($this->lst() as $val) {
                // a confidence value follows the value it qualifies into the column, but it
                // states a share and not the number of the column, so its unit is left out
                if (($val_col[$val->id()] ?? '') == $col_id and !$this->is_confidence($val)) {
                    $col_val_lst[] = $val;
                }
            }
            $shared = $this->phrases_of_all($col_val_lst);
            $unit_lst = new phrase_list();
            // the scaling comes first, so the header reads like the number e.g. "trillion EUR"
            foreach ($shared->lst() as $phr) {
                if ($phr->is_scaling($msg)) {
                    $unit_lst->add_phrase($phr);
                }
            }
            // every other unit follows the scaling, e.g. the measure "EUR" of "trillion EUR"
            foreach ($shared->lst() as $phr) {
                if ($this->is_unit($phr, $msg) and !$phr->is_scaling($msg)) {
                    $unit_lst->add_phrase($phr);
                }
            }
            $result[$col_id] = $unit_lst;
        }
        return $result;
    }

    /**
     * the content of one column header cell
     *
     * the unit is separated from the phrases that name the column by a translatable word, so
     * that the header reads like a sentence e.g. "cost in trillion EUR"
     *
     * @param phrase $phr the phrase that heads the column
     * @param phrase_list $unit_lst the unit phrases of that column, empty if it has no common unit
     * @return string the html of the header cell e.g. 'cost in trillion EUR'
     */
    private function column_header(phrase $phr, phrase_list $unit_lst): string
    {
        $result = $phr->name_link();
        $unit_html = '';
        foreach ($unit_lst->lst() as $unit_phr) {
            // the parts of a unit are read as one term, so a space and not a comma joins them
            if ($unit_html != '') {
                $unit_html .= ' ';
            }
            $unit_html .= $unit_phr->name_link();
        }
        if ($unit_html != '') {
            $result .= ' ' . msg_id::VALUE_TBL_UNIT->text() . ' ' . $unit_html;
        }
        return $result;
    }

    /**
     * true if the phrase says how a number is stated instead of what it is about
     *
     * @param phrase $phr the phrase to check
     * @return bool true for a range bound tag or the estimate qualifier
     */
    private function is_marker(phrase $phr): bool
    {
        return in_array($phr->name(), words::VALUE_MARKERS);
    }

    /**
     * one table cell with the values of one row and column
     *
     * a value tagged with a range word is a bound of the probability range of the value with the
     * same phrases, so it is shown behind that centre value as "centre (low – high)" instead of
     * as a value of its own (see the view-validation of solution_prio.json); a bound without a
     * centre is shown like a value, so that nothing is lost; the estimate qualifier of a value
     * is the tooltip of the cell, because the table is about the numbers and not about how they
     * have been stated
     *
     * a value tagged "confidence" says how sure the value it qualifies is, so it is the tooltip
     * of that value and no value of its own; one that qualifies no value of the cell is shown
     * like a value, so that nothing is lost
     *
     * @param array $val_lst the values of the cell
     * @param user_message $msg to report a problem of the value display
     * @param string $back the last view to suggest the best follow-up view
     * @param string $style the css class of the column, which hides it on the screens its tier excludes
     * @return string the html of the cell, the centre values separated by a comma
     */
    private function cell(array $val_lst, user_message $msg, string $back, string $style): string
    {
        $html = new html_base();
        [$centre_lst, $bound, $conf_lst, $title_lst] = $this->sort_cell_values($val_lst, $msg);
        $txt_lst = [];
        $conf_used = [];
        foreach ($centre_lst as $val) {
            $txt = $val->value_edit($msg, $back);
            $key = $this->range_key($val);
            if (array_key_exists($key, $bound)) {
                // a range normally has both bounds, but a missing one leaves its place empty
                $low = $bound[$key][words::LOW] ?? null;
                $high = $bound[$key][words::HIGH] ?? null;
                $low_txt = $low?->value_edit($msg, $back) ?? '';
                $high_txt = $high?->value_edit($msg, $back) ?? '';
                $txt .= self::RANGE_START . $low_txt . self::RANGE_SEP . $high_txt . self::RANGE_END;
                unset($bound[$key]);
            }
            foreach ($conf_lst as $conf_key => $conf_val) {
                if ($this->qualifies($conf_val, $val, $msg)) {
                    $title_lst[] = words::CONFIDENCE . ' ' . $conf_val->value($msg);
                    $conf_used[] = $conf_key;
                }
            }
            $txt_lst[] = $txt;
        }
        // a bound without a centre is shown like a value, so that nothing is lost
        foreach ($bound as $bound_by_word) {
            foreach ($bound_by_word as $val) {
                $txt_lst[] = $val->value_edit($msg, $back);
            }
        }
        // the same for a confidence value that qualifies no value of this cell
        foreach ($conf_lst as $conf_key => $conf_val) {
            if (!in_array($conf_key, $conf_used)) {
                $txt_lst[] = $conf_val->value_edit($msg, $back);
            }
        }
        $title = implode(', ', array_unique($title_lst));
        return $html->td(implode(', ', $txt_lst), $style, 0, $title);
    }

    /**
     * sort the values of a cell into the centre values, the range bounds, the confidence values
     * and the qualifiers shown as the tooltip of the cell
     *
     * @param array $val_lst the values of the cell
     * @param user_message $msg to report a problem of reading a phrase type
     * @return array [the centre values, the bounds keyed by range key and range word,
     *                the confidence values, the qualifier names]
     */
    private function sort_cell_values(array $val_lst, user_message $msg): array
    {
        $centre_lst = [];
        $bound = [];
        $conf_lst = [];
        $qualifier_lst = [];
        foreach ($val_lst as $val) {
            $range_word = $this->range_word($val);
            if ($this->is_confidence($val)) {
                $conf_lst[] = $val;
            } elseif ($range_word == '') {
                $centre_lst[] = $val;
            } else {
                $bound[$this->range_key($val)][$range_word] = $val;
            }
            foreach ($val->grp->phr_lst()->lst() as $phr) {
                $is_new = !in_array($phr->name(), $qualifier_lst);
                if (in_array($phr->name(), words::QUALIFIERS) and $is_new) {
                    $qualifier_lst[] = $phr->name();
                }
            }
        }
        return [$centre_lst, $bound, $conf_lst, $qualifier_lst];
    }

    /**
     * @param value $val the value to check
     * @param string $name the phrase name to look for
     * @return bool true if a phrase of the value has the given name
     */
    private function has_phrase_name(value $val, string $name): bool
    {
        return in_array($name, $val->grp->phr_lst()->names());
    }

    /**
     * true if the value says how sure another value is instead of stating a number of its own
     *
     * such a value is shown as the tooltip of the value with the same subject (see cell), so it
     * is neither a value of a cell nor does its unit, which is always a share, name the column
     *
     * @param value $val the value to check
     * @return bool true if the value states a confidence
     */
    private function is_confidence(value $val): bool
    {
        return $this->has_phrase_name($val, words::CONFIDENCE);
    }

    /**
     * @param value $val the value to describe
     * @param user_message $msg to report a problem of reading a phrase type
     * @return array the phrase names of the value without the markers and units, which is what a
     *               value shares with its confidence value
     */
    private function subject_names(value $val, user_message $msg): array
    {
        $names = [];
        foreach ($val->grp->phr_lst()->lst() as $phr) {
            if (!$this->is_marker($phr) and !$this->is_unit($phr, $msg)) {
                $names[] = $phr->name();
            }
        }
        return $names;
    }

    /**
     * true if the given confidence value says how sure the given value is
     *
     * the qualified value names all subject phrases of the confidence value and often more, e.g.
     * the confidence of the "initial effort" of a problem qualifies the effort of the solution of
     * that problem, which names the solution too (see solution_prio.json), so a confidence value
     * that names less than the value it qualifies is still matched
     *
     * @param value $conf_val the confidence value
     * @param value $val the value that the confidence value may qualify
     * @param user_message $msg to report a problem of reading a phrase type
     * @return bool true if the value carries all subject phrases of the confidence value
     */
    private function qualifies(value $conf_val, value $val, user_message $msg): bool
    {
        return array_diff(
                $this->subject_names($conf_val, $msg),
                $this->subject_names($val, $msg)) == [];
    }

    /**
     * @param value $val the value to key
     * @return string the names of the unit phrases of the value, encoded like the range key, so
     *                that the values of one measure share the key e.g. "trillion EUR"
     */
    private function unit_key(value $val, user_message $msg): string
    {
        $names = [];
        foreach ($val->grp->phr_lst()->lst() as $phr) {
            if ($this->is_unit($phr, $msg)) {
                $names[] = $phr->name();
            }
        }
        sort($names);
        return json_encode($names);
    }

    /**
     * split the values of one column phrase into the values of each unit, e.g. the potential
     * loss in trillion EUR and the potential loss in percent htp, because a column shows one
     * measure; the values are ordered by impact, so the unit of the most relevant value leads
     *
     * a confidence value is a share whatever the unit of the value it qualifies, so it follows
     * the value with the same subject instead of its own unit (see cell)
     *
     * @param array $members the values of the column phrase in the order of the impact
     * @param user_message $msg to report a problem of reading a phrase type
     * @return array per unit the values of that unit, the leading unit first
     */
    private function split_by_unit(array $members, user_message $msg): array
    {
        $result = [];
        $conf_lst = [];
        foreach ($members as $val) {
            if ($this->is_confidence($val)) {
                $conf_lst[] = $val;
            } else {
                $result[$this->unit_key($val, $msg)][] = $val;
            }
        }
        foreach ($conf_lst as $conf_val) {
            $found = $this->unit_of_qualified($result, $conf_val, $msg);
            // a confidence value that qualifies no value of the column keeps its own unit, where
            // it is shown like a value instead of a tooltip (see cell), so that it is not lost
            if ($found == '') {
                $found = $this->unit_key($conf_val, $msg);
            }
            $result[$found][] = $conf_val;
        }
        return $result;
    }

    /**
     * the unit of the values that the given confidence value qualifies
     *
     * @param array $unit_lst per unit the values of that unit collected so far
     * @param value $conf_val the confidence value to place
     * @param user_message $msg to report a problem of reading a phrase type
     * @return string the unit key of the qualified values or an empty string if none is qualified
     */
    private function unit_of_qualified(array $unit_lst, value $conf_val, user_message $msg): string
    {
        $result = '';
        // the first unit with a qualified value wins, so the confidence follows the leading unit
        foreach ($unit_lst as $unit_key => $unit_members) {
            foreach ($unit_members as $val) {
                if ($result == '' and $this->qualifies($conf_val, $val, $msg)) {
                    $result = $unit_key;
                }
            }
        }
        return $result;
    }

    /**
     * the id of the next column of the given phrase: the phrase id for the first column and the
     * phrase id joined with a counter for a further column of the same phrase in another unit
     *
     * @param int|string $id the phrase id of the column
     * @param array $col_phr the columns opened so far, keyed by column id
     * @return int|string the id of the column to open
     */
    private function unit_column_id(int|string $id, array $col_phr): int|string
    {
        $result = $id;
        $i = 1;
        while (array_key_exists($result, $col_phr)) {
            $result = $id . self::UNIT_COLUMN_SEP . $i;
            $i++;
        }
        return $result;
    }

    /**
     * @param value $val the value to check
     * @return string the range word of the value e.g. "low", or an empty string for a centre value
     */
    private function range_word(value $val): string
    {
        $result = '';
        foreach ($val->grp->phr_lst()->lst() as $phr) {
            if (in_array($phr->name(), words::RANGE_WORDS)) {
                $result = $phr->name();
            }
        }
        return $result;
    }

    /**
     * @param value $val the value to key
     * @return string the phrase names of the value without the range word, which a centre value
     *                shares with its bounds; encoded, so that a comma in a name cannot join two
     *                different phrase sets to the same key
     */
    private function range_key(value $val): string
    {
        $names = array_diff($val->grp->phr_lst()->names(), words::RANGE_WORDS);
        sort($names);
        return json_encode(array_values($names));
    }

    /**
     * the phrase that heads the column with the row names
     *
     * the rows of a table are the children of the page phrase, so the page phrase says what they
     * are: the rows of "global problem" are problems, and because the triple "global problem" is
     * built from "problem", that word heads the row column as soon as it is a defined column
     *
     * @param phrase_list $context_phr_lst the phrases assumed by the reader e.g. the phrase of the page
     * @param array $col_order the defined column phrase names, the most important column first
     * @return phrase|null the phrase to head the row column or null if the page phrase names none
     */
    private function row_column(phrase_list $context_phr_lst, array $col_order): ?phrase
    {
        $result = null;
        foreach ($context_phr_lst->lst() as $phr) {
            if ($result == null and $phr->is_triple()) {
                $from = $phr->obj()->get_from();
                if ($from != null and in_array($from->name(), $col_order)) {
                    $result = $from;
                }
            }
        }
        return $result;
    }

    /**
     * the defined columns that name a phrase of the row instead of a value
     *
     * e.g. the "solution" column of solution_prio.json: no value carries the phrase "solution",
     * but the solutions are linked to it, so the column can show the solution of the problem row
     *
     * @param array $col_order the defined column phrase names, the most important column first
     * @param array $col_phr the columns that hold a value, keyed by phrase id
     * @param phrase_list|null $rel_lst the phrases related to the page phrase
     * @param user_message $msg to report a problem of reading the phrase type
     * @return array the phrase columns keyed by phrase id in the order of the definition
     */
    private function phrase_columns(
        array        $col_order,
        array        $col_phr,
        ?phrase_list $rel_lst,
        user_message $msg
    ): array
    {
        $result = [];
        foreach ($col_order as $name) {
            $phr = $rel_lst?->column_phrase($name);
            // a column that already holds the values of this phrase cannot name a phrase too,
            // and a unit describes the number, so it heads no column of its own either
            if ($phr != null and !array_key_exists($phr->id(), $col_phr)
                and !$this->is_unit($phr, $msg)) {
                if ($rel_lst->child_names($phr) != []) {
                    $result[$phr->id()] = $phr;
                }
            }
        }
        return $result;
    }

    /**
     * merge the value columns and the phrase columns into one left to right column order
     *
     * both kinds are defined by the same column tiers, so the definition decides where a phrase
     * column stands between the value columns; a column that no definition names is shown behind
     * them, because only the data suggested it and its order is the impact ranking
     *
     * @param array $col_order the defined column phrase names, the leftmost column first
     * @param array $col_phr the columns that hold a value, keyed by phrase id
     * @param array $phr_col the columns that name a phrase of the row, keyed by phrase id
     * @return array the ids of all columns of the table, the leftmost column first
     */
    private function column_id_order(array $col_order, array $col_phr, array $phr_col): array
    {
        // a phrase column is never a value column too, so the two lists share no id
        $all = $col_phr + $phr_col;
        $result = [];
        foreach ($col_order as $name) {
            foreach ($all as $id => $phr) {
                if ($phr->name() == $name and !in_array($id, $result)) {
                    $result[] = $id;
                }
            }
        }
        foreach (array_keys($all) as $id) {
            if (!in_array($id, $result)) {
                $result[] = $id;
            }
        }
        return $result;
    }

    /**
     * the phrase of the given value that belongs into a phrase column
     *
     * @param value $val the value whose phrases are searched
     * @param array $child_names the names of the phrases linked to the column phrase
     * @return phrase|null the first phrase of the value that the column covers or null if none
     */
    private function phrase_of_column(value $val, array $child_names): ?phrase
    {
        $result = null;
        foreach ($val->grp->phr_lst()->lst() as $phr) {
            if ($result == null and in_array($phr->name(), $child_names)) {
                $result = $phr;
            }
        }
        return $result;
    }

    /**
     * put the phrases the system column tiers define first, in the order of the definition, and
     * append the impact ranked phrases that have no definition; so a table shows the columns a
     * user has decided on before the ones the data suggests
     *
     * @param array $all every groupable phrase keyed by phrase id, whatever its usage count
     * @param array $ranked the impact ranked phrases keyed by phrase id
     * @param array $col_order the defined column phrase names, the most important column first
     * @return array the column phrases keyed by phrase id in the order they should be shown
     */
    private function columns_by_definition(
        array        $all,
        array        $ranked,
        array        $col_order,
        ?phrase_list $rel_lst
    ): array
    {
        $by_parts = [];
        $by_phrase = [];
        // the defined columns first, in the order of the definition
        foreach ($col_order as $name) {
            $found = false;
            foreach ($all as $id => $phr) {
                if ($phr->name() == $name and !array_key_exists($id, $by_phrase)) {
                    $by_phrase[$id] = $phr;
                    $found = true;
                }
            }
            // a defined triple that no value carries can still be a column if the values carry
            // its two parts (see column_parts); it takes its values before the column of one of
            // the parts, because it names more of the phrases of a value, so it comes first
            if (!$found) {
                $col_phr = $rel_lst?->column_phrase($name);
                if ($col_phr != null and count($this->column_parts($col_phr, $all)) > 1) {
                    $by_parts[$col_phr->id()] = $col_phr;
                }
            }
        }
        $result = $by_parts + $by_phrase;
        // then the phrases the data suggests, still ordered by the aggregated impact
        foreach ($ranked as $id => $phr) {
            if (!array_key_exists($id, $result)) {
                $result[$id] = $phr;
            }
        }
        return $result;
    }

    /**
     * the group phrase ids that a value must carry to belong to the column of the given phrase
     *
     * a column of a triple that no value carries stands for the values that carry both parts of
     * the triple, e.g. the "potential loss" column for the values with "potential" and "loss",
     * because the values name the measure with the two words (see solution_prio.json)
     *
     * @param phrase $phr the phrase that heads the column
     * @param array $all every groupable phrase keyed by phrase id
     * @return array the ids of the phrases that a value must all carry to be in the column
     */
    private function column_parts(phrase $phr, array $all): array
    {
        $result = [$phr->id()];
        if (!array_key_exists($phr->id(), $all) and $phr->is_triple()) {
            $from_id = $phr->obj()->get_from()?->id() ?? 0;
            $to_id = $phr->obj()->get_to()?->id() ?? 0;
            if (array_key_exists($from_id, $all) and array_key_exists($to_id, $all)) {
                $result = [$from_id, $to_id];
            }
        }
        return $result;
    }

    /**
     * the phrase names that a column definition names, directly or as a part of a defined column
     *
     * a column of a triple stands for the values that carry both parts of the triple (see
     * column_parts), so a part must stay groupable even if every value carries it; else the
     * "potential" of the "potential loss" column would describe the whole table and the column
     * of the two phrases could not be told apart from the column of one of them
     *
     * @param array $col_order the defined column phrase names, the leftmost column first
     * @param phrase_list|null $rel_lst the phrases related to the page phrase with the definitions
     * @return array the defined column names plus the parts of the defined triple columns
     */
    private function column_names_with_parts(array $col_order, ?phrase_list $rel_lst): array
    {
        $result = $col_order;
        foreach ($col_order as $name) {
            $phr = $rel_lst?->column_phrase($name);
            if ($phr != null and $phr->is_triple()) {
                foreach ([$phr->obj()->get_from(), $phr->obj()->get_to()] as $part) {
                    if ($part != null and !in_array($part->name(), $result)) {
                        $result[] = $part->name();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * rank the phrases that can group the given values: per phrase the aggregated impact of the values
     * using it, highest first; the shared base of the grouped value list and of the value columns
     *
     * @param array $pool the values to group
     * @param phrase_list $context_phr_lst the phrases assumed by the reader and never used as a group
     * @param int $min the number of values a phrase must group to become a group phrase
     * @return array [array the group phrases keyed by phrase id ordered by the aggregated impact,
     *                array per value id the ids of its group phrases]
     */
    private function phrase_ranking(
        array        $pool,
        user_message $msg,
        phrase_list  $context_phr_lst,
        int          $min
    ): array
    {
        $ctx_ids = $this->phrase_id_set($context_phr_lst);

        // per value the ids of its groupable phrases, plus the count, phrase and aggregated impact per id
        $val_phr_ids = [];
        $count = [];
        $phr_by_id = [];
        $impact = [];
        foreach ($pool as $val) {
            $ids_of_val = [];
            foreach ($this->group_phrases($val, $msg, $ctx_ids) as $phr) {
                $id = $phr->id();
                $ids_of_val[$id] = true;
                $count[$id] = ($count[$id] ?? 0) + 1;
                $phr_by_id[$id] = $phr;
                $impact[$id] = ($impact[$id] ?? 0) + $val->impact();
            }
            $val_phr_ids[$val->id()] = $ids_of_val;
        }
        // the phrases used often enough, ordered by the aggregated impact of their values
        $ids = [];
        foreach ($count as $id => $cnt) {
            if ($cnt > $min) {
                $ids[] = $id;
            }
        }
        usort($ids, fn($a, $b) => $impact[$b] <=> $impact[$a]
            ?: strcmp($phr_by_id[$a]->name(), $phr_by_id[$b]->name()));
        $ranked = [];
        foreach ($ids as $id) {
            $ranked[$id] = $phr_by_id[$id];
        }
        return [$ranked, $val_phr_ids];
    }

    /**
     * split the given values into the values that use the given group phrase and the remaining values
     *
     * @param array $remaining the values still to be placed
     * @param int|string $phr_id the id of the group phrase
     * @param array $val_phr_ids per value id the ids of its group phrases
     * @return array [array the values using the group phrase, array the values still to be placed]
     */
    private function split_by_phrase(array $remaining, int|string $phr_id, array $val_phr_ids): array
    {
        return $this->split_by_parts($remaining, [$phr_id], $val_phr_ids);
    }

    /**
     * split the given values into the values that use all the given group phrases and the rest
     *
     * @param array $remaining the values still to be placed
     * @param array $part_ids the ids of the group phrases that a value must all carry
     * @param array $val_phr_ids per value id the ids of its group phrases
     * @return array [array the values using all the phrases, array the values still to be placed]
     */
    private function split_by_parts(array $remaining, array $part_ids, array $val_phr_ids): array
    {
        $members = [];
        $rest = [];
        foreach ($remaining as $val) {
            $carries_all = true;
            foreach ($part_ids as $part_id) {
                if (!isset($val_phr_ids[$val->id()][$part_id])) {
                    $carries_all = false;
                }
            }
            if ($carries_all) {
                $members[] = $val;
            } else {
                $rest[] = $val;
            }
        }
        return [$members, $rest];
    }

    /**
     * section three of list_most_relevant: the remaining values sorted by impact descending, rendered
     * as a titleless value item list (the values share no group phrase) limited to the remaining
     * page budget; its "... more" tail is the single closing tail of the page and covers the
     * ungrouped values as well as the values of the groups skipped by the budget
     *
     * @param array $pool the remaining values
     * @param phrase_list $context_phr_lst phrases left out of each value name
     * @param string $back the last view to suggest the best follow-up view
     * @param int $budget the number of values that may still be shown on the page
     * @return string the html of the remaining values as a 'value-items' list, or '' if none remain
     */
    private function impact_group(array $pool, user_message $msg, phrase_list $context_phr_lst, string $back, int $budget): string
    {
        $html = new html_base();
        $result = '';
        if (count($pool) > 0) {
            $val_lst = new value_list();
            $val_lst->set_lst($pool);
            $val_lst->sort_by_impact();
            // the remaining page budget after the grouped sections; the pool holds the ungrouped
            // values and the values of the skipped groups, so the more tail of this last section
            // covers everything the page does not show
            $limit = max(0, $budget);
            $items = '';
            $i = 0;
            foreach ($val_lst->lst() as $val) {
                if ($i < $limit) {
                    $items .= $this->value_item($val, $msg, $context_phr_lst, $back);
                }
                $i++;
            }
            $diff = count($pool) - $limit;
            if ($diff > 0) {
                $items .= $html->list_item($this->more_tail($diff, $context_phr_lst));
            }
            $result = $html->list_unsorted($items, styles::VALUE_ITEMS);
        }
        return $result;
    }

    /**
     * render one group of the most relevant value list as a 'value-group' div: the group phrase (time
     * word or shared phrase) as the title followed by one value item per member; the header phrase is
     * added to the context so it is not repeated on each member name
     *
     * @param phrase $header the time word or shared phrase shown as the group title
     * @param array $members the values of the group
     * @param phrase_list $context_phr_lst phrases already assumed by the reader
     * @param string $back the last view to suggest the best follow-up view
     * @return string the html code of the value group (title and item list)
     */
    private function group_block(
        phrase       $header,
        array        $members,
        phrase_list  $context_phr_lst,
        user_message $msg,
        string       $back
    ): string
    {
        $html = new html_base();
        $title = $html->div($header->name_link(), styles::VALUE_GROUP_TITLE);
        $ctx = clone $context_phr_lst;
        $ctx->add_phrase($header);
        // sort the group members by the same deterministic key as the rest of the list so the item
        // order never depends on the api/db row order (see docs/llm/frontend.md); the members are
        // bucketed in pool order by time_groups / relevant_phrase_groups, which is not stable
        $val_lst = new value_list();
        $val_lst->set_lst($members);
        $val_lst->sort_by_impact();
        // show at most the configured number of values per group and offer the rest behind the
        // "... and n more" tail, so that a phrase with many values cannot fill the whole screen
        // and the messages below the view stay visible (see docs/llm/frontend.md)
        $limit = $this->configured_limit($msg);
        $items = '';
        $i = 0;
        foreach ($val_lst->lst() as $val) {
            if ($i < $limit) {
                $items .= $this->value_item($val, $msg, $ctx, $back);
            }
            $i++;
        }
        $diff = count($members) - $limit;
        if ($diff > 0) {
            $items .= $html->list_item($this->more_tail($diff, $ctx));
        }
        $result = $html->div($title . $html->list_unsorted($items, styles::VALUE_ITEMS), styles::VALUE_GROUP);
        return $result;
    }

    /**
     * render one value of the grouped value list as a list item: the phrase name(s) on the left and
     * the number on the right; the shared item renderer of group_block and impact_group
     *
     * @param value $val the value to render
     * @param phrase_list $context_phr_lst the phrases assumed by the reader and left out of the name
     * @param string $back the last view to suggest the best follow-up view
     * @return string the html code of one value list item
     */
    private function value_item(value $val, user_message $msg, phrase_list $context_phr_lst, string $back): string
    {
        $html = new html_base();
        $name = $html->span($val->grp->name_link_list($context_phr_lst), styles::VALUE_NAME);
        $num = $html->span($val->value_edit($msg, $back), styles::VALUE_NUM);
        $result = $html->list_item($name . $num);
        return $result;
    }

    /**
     * the "... and n more" row of a table that shows only the configured number of rows
     *
     * the tail is in the row name column, because that is the column the reader follows down;
     * the value columns of that row stay empty like in change_log_list::tr_page_nav
     *
     * @param int $diff the number of rows that this table does not show
     * @param phrase_list $context_phr_lst the phrases assumed by the reader; the first is the page phrase
     * @param array $col_styles per column behind the row name column its css class
     * @param string $more_url the url of the same page with the next list size or '' if not known
     * @return string the html code of the "... and n more" table row
     */
    private function tr_more(
        int          $diff,
        phrase_list  $context_phr_lst,
        array        $col_styles,
        string       $more_url = ''
    ): string
    {
        $html = new html_base();
        $cells = $html->td($this->more_tail($diff, $context_phr_lst, $more_url));
        foreach ($col_styles as $style) {
            $cells .= $html->td('', $style);
        }
        $result = $html->tr($cells);
        return $result;
    }

    /**
     * the css class of a table column, which follows the tier of its definition
     *
     * @param string $name the name of the column phrase e.g. "loss"
     * @param phrase_list|null $rel_lst the phrases related to the page phrase with the definitions
     * @return string the css class that hides the column on the screens its tier excludes, or ''
     */
    private function column_style(string $name, ?phrase_list $rel_lst): string
    {
        $result = '';
        $tier = $rel_lst?->column_tier($name) ?? '';
        if ($tier == triples::SYSTEM_COLUMN_MAIN) {
            $result = styles::COL_MAIN;
        } elseif ($tier == triples::SYSTEM_COLUMN_MINOR) {
            $result = styles::COL_MINOR;
        }
        return $result;
    }

    /**
     * the configured number of rows shown in a value table
     * (config.yaml "select > initial > entries", falling back to config::LIMIT_SHORT_LIST if the
     * config is not loaded), which is the short version of a list; the more and the all version
     * are not wired yet (see docs/llm/frontend.md "Short, more and all")
     *
     * @param user_message $msg to report a problem of reading the config
     * @return int the maximum number of table rows to show
     */
    private function configured_row_limit(user_message $msg): int
    {
        global $ui_sys;
        $result = config::LIMIT_SHORT_LIST;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by(
                [words::ENTRIES, words::INITIAL, words::SELECT], $msg, config::LIMIT_SHORT_LIST);
        }
        return $result;
    }

    /**
     * the "... and n more" tail of a truncated value list as a link that shows more (see
     * docs/llm/frontend.md: a "more" is always a link that shows more): the same page with
     * the next list size if the page is known, else all values of the page phrase via the
     * phrase values view; only if neither is known the tail stays a plain text
     *
     * @param int $diff the number of values that are not shown
     * @param phrase_list $context_phr_lst the phrases assumed by the reader; the first is the page phrase
     * @param string $more_url the url of the same page with the next list size or '' if not known
     * @return string the html code of the more tail
     */
    private function more_tail(int $diff, phrase_list $context_phr_lst, string $more_url = ''): string
    {
        $html = new html_base();
        $txt = msg_id::THREE_POINTS->text() . ' ' . msg_id::AND_MORE_BEFORE->text() . ' '
            . $diff . ' ' . msg_id::MORE->text();
        $phr = $context_phr_lst->lst()[0] ?? null;
        if ($more_url != '') {
            $result = $html->ref($more_url, $txt);
        } elseif ($phr != null) {
            $result = $html->ref($html->url_back(views::PHRASE_VALUES_ID, $phr->id()), $txt);
        } else {
            $result = $txt;
        }
        return $result;
    }

    /**
     * the number of table rows to show: the list size named by the url, which a "... more"
     * click has raised, or else the configured number of the short list
     *
     * @param array $url_array the url parameters of the page that shows the table
     * @param user_message $msg to report a problem of reading the config
     * @return int the maximum number of table rows to show, self::LIMIT_ALL for every row
     */
    private function row_limit(array $url_array, user_message $msg): int
    {
        $result = $this->configured_row_limit($msg);
        if (array_key_exists(url_var::DISPLAY_LIST_SIZE, $url_array)) {
            $result = (int)$url_array[url_var::DISPLAY_LIST_SIZE];
        }
        return $result;
    }

    /**
     * @param array $url_array the url parameters of the page that shows the table
     * @param int $row_limit the number of rows of one page
     * @return int the position of the first row of the list page named by the url, 0 for the first
     */
    private function first_row(array $url_array, int $row_limit): int
    {
        return (int)($url_array[url_var::DISPLAY_LIST_PAGE] ?? 0) * $row_limit;
    }

    /**
     * the url of the same page with the next list size, so that a "... more" click shows the
     * next version of the list (docs/llm/frontend.md "Short, more and all"); the list page is
     * dropped, because the larger list starts again with its first row
     *
     * @param array $url_array the url parameters of the page that shows the table
     * @param int $row_limit the number of rows shown now
     * @param user_message $msg to report a problem of reading the config
     * @return string the url of the same page with the next list size or '' if the page is not known
     */
    private function more_url(array $url_array, int $row_limit, user_message $msg): string
    {
        $result = '';
        if ($url_array != []) {
            $url_pars = $url_array;
            unset($url_pars[url_var::DISPLAY_LIST_PAGE]);
            $url_pars[url_var::DISPLAY_LIST_SIZE] = $this->next_row_limit($row_limit, $msg);
            $result = api::MAIN_SCRIPT . url_var::PAR . http_build_query($url_pars);
        }
        return $result;
    }

    /**
     * the list size of the next version of a list: the short list grows to the more list and
     * the more list to all rows (docs/llm/frontend.md "Short, more and all")
     *
     * @param int $row_limit the number of rows shown now
     * @param user_message $msg to report a problem of reading the config
     * @return int the number of rows of the next version, self::LIMIT_ALL for every row
     */
    private function next_row_limit(int $row_limit, user_message $msg): int
    {
        $result = self::LIMIT_ALL;
        $more_limit = $this->configured_more_limit($msg);
        if ($row_limit < $more_limit) {
            $result = $more_limit;
        }
        return $result;
    }

    /**
     * the configured number of rows of the more version of a list
     * (config.yaml "select > more > entries", falling back to config::LIMIT_MORE_LIST if the
     * config is not loaded)
     *
     * @param user_message $msg to report a problem of reading the config
     * @return int the number of rows of the more list
     */
    private function configured_more_limit(user_message $msg): int
    {
        global $ui_sys;
        $result = config::LIMIT_MORE_LIST;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by(
                [words::ENTRIES, words::MORE, words::SELECT], $msg, config::LIMIT_MORE_LIST);
        }
        return $result;
    }

    /**
     * the configured maximum number of values shown in a value list
     * (config.yaml "user > frontend > lists > limit > value list", falling back to
     * config::LIMIT_VALUE_LIST if the config is not loaded);
     * shared by list(), list_unit() and impact_group so that every value list uses the same limit
     * @return int the maximum number of value rows to show
     */
    private function configured_limit(user_message $msg): int
    {
        global $ui_sys;
        $result = config::LIMIT_VALUE_LIST;
        if ($ui_sys?->cfg !== null) {
            $result = $ui_sys->cfg->get_by(
                [triples::VALUE_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_VALUE_LIST);
        }
        return $result;
    }

    /**
     * the phrases of a value that can form a phrase group: the group phrases without the context phrases
     * and without the time phrases (a time phrase groups in the time section, not here)
     *
     * @param value $val the value whose groupable phrases are returned
     * @param array $ctx_ids the ids of the context phrases keyed by id
     * @return array the groupable phrase objects of the value
     */
    private function group_phrases(value $val, user_message $msg, array $ctx_ids): array
    {
        $result = [];
        foreach ($val->grp->phr_lst()->lst() as $phr) {
            if (!isset($ctx_ids[$phr->id()]) and !$phr->is_time($msg)) {
                $result[] = $phr;
            }
        }
        return $result;
    }

    /**
     * @param phrase_list $phr_lst the phrase list whose ids are collected
     * @return array the phrase ids of the given list keyed by id for a fast "contains" lookup
     */
    private function phrase_id_set(phrase_list $phr_lst): array
    {
        $result = [];
        foreach ($phr_lst->lst() as $phr) {
            $result[$phr->id()] = true;
        }
        return $result;
    }

    /**
     * create the html code to show a list of values where the unit is behind the value
     * TODO use a more general parent function
     *
     * @param int|null $limit the max number of entries to show
     * @param int|null $page the offset if there are more entries that could be shown at once
     * @return string the html code to display the values to the user
     */
    function list_unit(
        user_message $msg,
        ?int $limit = null,
        ?int $page = null
    ): string
    {
        $html = new html_base();

        $result = '';

        if (!$this->is_empty()) {
            // sort so the highest impact value is shown first and the order (and the limited subset)
            // never depends on the api/db row order (see docs/llm/frontend.md), like list() and table()
            $this->sort_by_impact();
            if ($limit == null) {
                $limit = $this->configured_limit($msg);
            }

            $i = 0;
            foreach ($this->lst() as $val) {
                if ($i <= $limit) {
                    if ($i < $limit) {
                        $row = $val->with_unit_and_info($msg);
                        $row .= $html->lf();
                        $result .= $row;
                    } else {
                        $diff = $this->count() - $i;
                        if ($diff > 0) {
                            // the unit list does not know the page phrase, so the tail has no link target
                            $result .= ' ' . $this->more_tail($diff, new phrase_list());
                        }
                    }
                    $i++;
                }
            }
        }
        return $result;
    }

    /**
     * @param user_message $msg to collect the error messages
     * @param phrase_list|null $context_phr_lst list of phrases that are already known to the user by the context of this table and that does not need to be shown to the user again
     * @param string $back
     * @return string the html code to show the values as a table to the user
     */
    function table(user_message $msg, ?phrase_list $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // sort so the highest impact value is shown first and the order is always deterministic
        $this->sort_by_impact();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;

        // get the common phrases of the value list e.g. inhabitants, 2019
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        // TODO make the replacement language and user-specific
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->name_link();
        }

        // TODO add a button to add a new value using
        //$btn_new = $common_phrases->btn_add_value();
        $btn_new = '';

        // display the single values
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $val) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($val->grp->name_link_list($common_phrases));
            $row .= $html->td($val->value_edit($msg, $back));
            $rows .= $html->tr($row);
            // TODO add button to delete a value or add a similar value
            //$btn_del = $val->btn_del();
            //$btn_add = $val->btn_add();
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF) . $btn_new;
    }


    /*
     * info
     */

    /**
     * @return phrase_list a list of phrases used for each value
     * similar to the model function with the same name
     */
    function common_phrases(): phrase_list
    {
        $lib = new library();
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * return a list of phrase groups for all values of this list
     */
    function phrase_groups(): group_list
    {
        log_debug();
        $lib = new library();
        $grp_lst = new group_list();
        foreach ($this->lst() as $val) {
            $grp = $val->grp;
            if ($grp != null) {
                $grp_lst->lst[] = $grp;
            } else {
                log_err("The phrase group for value " . $val->id() . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug($lib->dsp_count($grp_lst->lst));
        return $grp_lst;
    }

    /*
     * to review
     */

    // creates a table of all values related to a word and a related word and all the sub words of the related word
    // e.g. for "ABB" ($this->phr) list all values for the cash flow statement ($phr_row)
    /*
    function dsp_table($phr_row, $back, user_message $msg): string
    {
        $usr = $ui_sys->usr;

        $result = '';
        $html = new html_base();
        $lib = new library();

        // check the parameters
        if (!isset($this->phr)) {
            $result = log_warning('The main phrase is not set.', "value_list_dsp->dsp_table");
        }
        if ($phr_row->id() == 0) {
            $result = log_warning('The main phrase is not selected.', "value_list_dsp->dsp_table");
        }
        if (!isset($phr_row)) {
            $result = log_warning('The row type is not set.', "value_list_dsp->dsp_table");
        }
        if (get_class($phr_row) <> word::class) {
            $result = log_err('The row is of type ' . get_class($phr_row) . ' but should be a phrase.', "value_list_dsp->dsp_table");
        }
        // if (get_class($phr_row) <> phrase::class) { $result = zu_err('The row is of type '.get_class($phr_row).' but should be a phrase.', "value_list_dsp->dsp_table"); }
        if ($phr_row->id() == 0) {
            $result = log_warning('The row type is not selected.', "value_list_dsp->dsp_table");
        }

        // if parameters are fine display the table
        if ($result == '') {
            log_debug('"' . $phr_row->name . '" for "' . $this->common_phrases()->name() . '" and user "' . $usr->name . '"');

            // init the display vars
            $val_main = null; // the "main" value used as a sample for a new value
            $modal_nbr = 1;   // to create a unique id for each modal form; the total number of modal boxes will not get too high, because the user will only see a limited number of values at once

            // create the table headline e.g. cash flow statement
            log_debug('all pre head: ' . $phr_row->name);
            $result .= $phr_row->dsp_tbl_row();
            log_debug('all head: ' . $phr_row->name);
            $result .= '<br>';

            // get all values related to the selecting word, because this is probably the strongest selection and to save time reduce the number of records asap
            //$val_lst = $this->common_phrases()->val_lst();
            log_debug('all values: ' . $lib->dsp_count($val_lst->lst));

            //$val_lst->load_phrases();
            foreach ($val_lst->lst AS $val) {
              zu_debug('value_list_dsp->dsp_table value: '.$val->number().' (group '.$val->grp_id.' and time '.$val->time_id.')');
            }

            // get all words related to the value list to be able to define the column and the row names
            $phr_lst_all = $val_lst->phr_lst();
            log_debug('all words: ' . $phr_lst_all->dsp_name());

            // get the time words for the column heads
            $all_time_lst = $val_lst->time_lst();
            log_debug('times ' . $all_time_lst->dsp_name());

            // adjust the time words to display
            $time_phr = $all_time_lst->time_useful();
            $time_lst = null;
            if ($time_phr != null) {
                $time_lst = new phrase_list($time_phr->user());
                $time_lst->add($time_phr, $msg);
                log_debug('times sorted ' . $time_lst->name());
            }

            // filter the value list by the time words used
            $used_value_lst = $val_lst->filter_by_time($time_lst);
            log_debug('values in the time period: ' . $lib->dsp_count($used_value_lst->lst));

            // get the word tree for the left side of the table
            $row_wrd_lst = $phr_row->are_and_contains();
            log_debug('row words: ' . $row_wrd_lst->name());

            // add potential differentiators to the word tree
            $word_incl_differentiator_lst = $row_wrd_lst->differentiators_filtered($phr_lst_all);
            log_debug('differentiator words: ' . $word_incl_differentiator_lst->name());
            log_debug('row words after differentiators added: ' . $row_wrd_lst->name());

            // filter the value list by the row words used
            $row_phr_lst_incl = clone $row_wrd_lst;
            log_debug('row phrase list: ' . $row_phr_lst_incl->name());
            $used_value_lst = $used_value_lst->filter_by_phrase_lst($row_phr_lst_incl);
            log_debug('used values for all rows: ' . $lib->dsp_count($used_value_lst->lst));

            // get the common words
            $common_lst = $used_value_lst->common_phrases();
            log_debug('common: ' . $common_lst->dsp_name());

            // get all words not yet part of the table rows, columns or common words
            $extra_phrases = clone $phr_lst_all;
            $extra_phrases->not_in($word_incl_differentiator_lst);
            $extra_phrases->not_in($common_lst);
            if ($time_lst != null) {
                $extra_phrases->not_in($time_lst);
            }
            log_debug('extra phrase, that might need to be added to each table cell: ' . $extra_phrases->dsp_name());

            // display the common words
            // TODO sort the words and use the short form e.g. in mio. CHF instead of in CHF millions
            if (count($common_lst->lst) > 0) {
                $common_text = '(in ';
                foreach ($common_lst->lst as $common_word) {
                    if ($common_word->id() <> $common_word->phrase()->id()) {
                        $common_text .= $common_word->dsp_tbl_row();
                    }
                }
                $common_text .= ')';
                $result .= $html->dsp_line_small($common_text);
            }
            $result .= '<br>';

            // display the table
            $result .= $html->dsp_tbl_start();
            $result .= '   <colgroup>' . "\n";
            //$result .= '<col span="'.sizeof($time_lst)+1.'">';
            $result .= '    <col span="7">' . "\n";
            $result .= '  </colgroup>' . "\n";
            $result .= '  <tbody>' . "\n";

            // display the column heads
            $result .= '  <tr>' . "\n";
            $result .= '    <th></th>' . "\n";
            foreach ($time_lst->lst() as $time_word) {
                $result .= $time_word->dsp_obj()->dsp_th($back, styles::STYLE_RIGHT);
            }
            $result .= '  </tr>' . "\n";

            // temp: display the word tree
            $last_words = '';
            $id = 0; // TODO review and rename
            foreach ($row_wrd_lst->lst as $sub_wrd) {
                $wrd_ids = array();
                $wrd_ids[] = $this->phr->id();
                $wrd_ids[] = $sub_wrd->id();
                foreach ($common_lst->id_lst() as $extra_id) {
                    if (!in_array($extra_id, $wrd_ids)) {
                        $wrd_ids[] = $extra_id;
                    }
                }

                // check if row is empty
                $row_has_value = false;
                $grp = new group($usr);
                $grp->load_by_ids(new phr_ids($wrd_ids));
                foreach ($time_lst->lst() as $time_wrd) {
                    $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                    if ($tbl_value->number() <> "") {
                        $row_has_value = true;
                        $val_main = $tbl_value;
                    }
                }

                if (!$row_has_value) {
                    log_debug('no value found for ' . $grp->name() . ' skip row');
                } else {
                    $result .= '  <tr>' . "\n";
                    $result .= $sub_wrd->dsp_tbl(0);

                    foreach ($time_lst->lst() as $time_wrd) {
                        $val_wrd_ids = $wrd_ids;
                        if (!in_array($time_wrd->id, $val_wrd_ids)) {
                            $val_wrd_ids[] = $time_wrd->id();
                        }

                        // get the phrase group for the value row
                        // to be done for the list at once
                        $grp = new group($usr);
                        $grp->load_by_ids(new phr_ids($val_wrd_ids));
                        $lib = new library();
                        log_debug("val ids " . $lib->dsp_array($val_wrd_ids) . " = " . $grp->id() . ".");

                        $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                        if ($tbl_value->number() == "") {
                            $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                            // to review
                            $add_phr_lst = clone $common_lst;
                            $add_phr_ids = $common_lst->id_lst();
                            $type_ids = array();
                            foreach ($add_phr_lst->id_lst() as $pos) {
                                $type_ids[] = 0;
                            }

                            if ($sub_wrd->id() > 0) {
                                $add_phr_lst->add($sub_wrd->phrase(), $msg);
                                $add_phr_ids[] = $sub_wrd->id();
                                $type_ids[] = $sub_wrd->id(); // TODO check if it should not be $type_word_id
                            }
                            // if values for just one column are added, the column head word id is already in the common id list and due to that does not need to be added
                            if (!in_array($time_wrd->id(), $add_phr_ids) and $time_wrd->id() > 0) {
                                $add_phr_lst->add($time_wrd->phrase(), $msg);
                                $add_phr_ids[] = $time_wrd->id();
                                $type_ids[] = 0;
                            }

                            //$result .= '      '.btn_add_value_fast ($modal_nbr, $add_phr_lst, $common_lst, $back);
                            $result .= '      ' . \Zukunft\ZukunftCom\main\php\web\html\btn_add_value_fast($modal_nbr, $add_phr_lst, $this->phr, $common_lst, $back);
                            $modal_nbr++;
                            //$result .= '      '.btn_add_value ($add_phr_lst, $type_ids, $back);
                            $result .= '      </td>' . "\n";
                        } else {
                            $result .= $tbl_value->dsp_tbl($back);
                            // maybe display the extra words of this value
                        }
                    }
                    $result .= '  </tr>' . "\n";
                }

                // display the row differentiators
                $sub_wrd->usr = $usr; // to be fixed in the lines before
                log_debug("... get differentiator for " . $sub_wrd->id() . " and user " . $sub_wrd->usr->name . ".");
                // get all potential differentiator words
                $sub_wrd_lst = $sub_wrd->lst();
                $differentiator_words = $sub_wrd_lst->differentiators_filtered($phr_lst_all);
                $sub_phr_lst = $sub_wrd_lst->phrase_lst();
                $differentiator_phrases = $differentiator_words->phrase_lst();
                log_debug("... show differentiator of " . $differentiator_phrases->name() . ".");
                // select only the differentiator words that have a value for the main word
                //$differentiator_phrases = zu_lst_in($differentiator_phrases, $extra_phrases);
                $differentiator_phrases = $differentiator_phrases->filter($extra_phrases);

                // find direct differentiator words
                //$differentiator_type = cl(SQL_LINK_TYPE_DIFFERENTIATOR);
                log_debug("... get differentiator type " . $differentiator_phrases->name() . ".");
                $type_phrases = $sub_phr_lst->differentiators();

                // if there is more than one type of differentiator group the differentiators by type
                // and add on each one an "other" line, if the sum is not 100%

                //foreach ($type_word_ids as $type_word_id) {
                foreach ($type_phrases->lst as $type_phr) {
                    if ($type_phr->id <> 1) {
                        $result .= '  <tr>' . "\n";
                        //$result .= '      <td>&nbsp;</td>';
                        $result .= $type_phr->dsp_tbl(0);
                        $result .= '  </tr>' . "\n";
                    }
                    // display the differentiator rows that are matching to the word type (e.g. the country)
                    //foreach (array_keys($differentiator_phrases) as $diff_word_id) {
                    $time_wrd = null;
                    $diff_phrase = null;
                    foreach ($differentiator_phrases->lst as $diff_phrase) {
                        if ($diff_phrase->is_a($type_phr)) {
                            $result .= '  <tr>' . "\n";
                            //$result .= '      <td>&nbsp;</td>';
                            $result .= $sub_wrd->dsp_tbl(0);
                            $wrd_ids = array();
                            $wrd_ids[] = $this->phr->id();
                            if (!in_array($sub_wrd->id, $wrd_ids)) {
                                $wrd_ids[] = $sub_wrd->id();
                            }
                            if (!in_array($diff_phrase->id, $wrd_ids)) {
                                $wrd_ids[] = $diff_phrase->id();
                            }
                            foreach ($common_lst->id_lst() as $extra_id) {
                                if (!in_array($extra_id, $wrd_ids)) {
                                    $wrd_ids[] = $extra_id;
                                }
                            }

                            foreach ($time_lst->lst() as $time_wrd) {
                                $val_wrd_ids = $wrd_ids;
                                if (!in_array($time_wrd->id, $val_wrd_ids)) {
                                    $val_wrd_ids[] = $time_wrd->id();
                                }

                                // get the phrase group for the value row
                                // to be done for the list at once
                                $grp = new group($usr);
                                $grp->load_by_ids(new phr_ids($val_wrd_ids));
                                $lib = new library();
                                log_debug("val ids " . $lib->dsp_array($val_wrd_ids) . " = " . $grp->id() . ".");

                                $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                                if ($tbl_value->number() == "") {
                                    $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                                    // to review
                                    $add_phr_lst = $common_lst;
                                    $add_phr_ids = $common_lst->id_lst();
                                    $type_ids = array();
                                    foreach ($add_phr_lst->id_lst() as $pos) {
                                        $type_ids[] = 0;
                                    }

                                    if ($sub_wrd->id() > 0) {
                                        $add_phr_lst->add($sub_wrd->phrase(), $msg);
                                        $add_phr_ids[] = $sub_wrd->id();
                                        $type_ids[] = $type_phr->id();
                                    }
                                    if ($diff_phrase->id() <> 0) {
                                        $add_phr_lst->add($diff_phrase, $msg);
                                        $add_phr_ids[] = $diff_phrase->id();
                                        $type_ids[] = 0;
                                    }
                                    // if values for just one column are added, the column head word id is already in the common id list and due to that does not need to be added
                                    if (!in_array($time_wrd->id, $add_phr_ids) and $time_wrd->id() > 0) {
                                        $add_phr_lst->add($time_wrd->phrase(), $msg);
                                        $add_phr_ids[] = $time_wrd->id();
                                        $type_ids[] = 0;
                                    }

                                    $result .= '      ' . \Zukunft\ZukunftCom\main\php\web\html\btn_add_value($add_phr_lst, $type_ids, $back);
                                    $result .= '      </td>' . "\n";
                                } else {
                                    $result .= $tbl_value->dsp_tbl($back);
                                    // maybe display the extra words of this value
                                }
                            }
                            $result .= '  </tr>' . "\n";
                        }
                    }
                    // add a new part value for the sub_word
                    if (!empty($differentiator_phrases)) {
                        $result .= '  <tr>' . "\n";
                        $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                        // to review
                        $add_phr_ids = $common_lst->id_lst();
                        $type_ids = array();
                        foreach ($add_phr_ids as $pos) {
                            $type_ids[] = 0;
                        }

                        $add_phr_ids[] = $sub_wrd->id();
                        if ($time_wrd != null) {
                            $add_phr_ids[] = $time_wrd->id();
                        }
                        if ($diff_phrase != null) {
                            $add_phr_ids[] = $diff_phrase->id();
                        }
                        $type_ids[] = $type_phr->id();
                        $type_ids[] = $type_phr->id();
                        $type_ids[] = $type_phr->id();

                        $result .= '      &nbsp;&nbsp;' . \Zukunft\ZukunftCom\main\php\web\html\btn_add_value($add_phr_ids, $type_ids, $back);
                        $result .= '      </td>' . "\n";
                        $result .= '  </tr>' . "\n";
                    }
                }

            }

            // allow the user to add a completely new value
            if ($last_words == '') {
                $last_words = $id;
            }

            // add an extra row to add new rows
            $result .= '  <tr>' . "\n";
            $result .= '      <td>' . "\n";

            // offer the user to add a new row related word
            $result .= $phr_row->btn_add($back);
            $result .= '&nbsp;&nbsp;';

            // offer the user to add a new value e.g. to add a value for a new year
            // this extra adds value button is needed for the case that all values are filled and due to that there is no other plus sign on the table
            if (isset($val_main)) {
                foreach ($time_lst->lst() as $time_wrd) {
                    $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";
                    $result .= $val_main->btn_add($back);
                    $result .= '      </td>' . "\n";
                }
            }

            $result .= '      </td>' . "\n";
            $result .= '  </tr>' . "\n";

            $result .= '    </tbody>' . "\n";
            $result .= $html->dsp_tbl_end();

            $result .= '<br><br>';

        }
        log_debug("... done");

        return $result;
    }
    */

    /**
     * return the html code to display all values related to a given word
     * $phr->id is the related word that should not be included in the display
     * $this->user()->id() is a parameter, because the viewer must not be the owner of the value
     * TODO add back
     */
    function html(user_message $msg, $back): string
    {
        $lib = new library();
        $html = new html_base();
        log_debug($lib->dsp_count($this->lst()));
        $result = '';

        $html = new html_base();

        // get common words
        $common_phr_ids = array();
        foreach ($this->lst() as $val) {
            if ($val->check() > 0) {
                log_warning('The group id for value ' . $val->id . ' has not been updated, but should now be correct.', "value_list->html");
            }
            $val->load_phrases();
            log_debug('value_list->html loaded');
            $val_phr_lst = $val->phr_lst;
            if ($val_phr_lst->count() > 0) {
                log_debug('get words ' . $val->phr_lst->dsp_id() . ' for "' . $val->number() . '" (' . $val->id . ')');
                if (empty($common_phr_ids)) {
                    $common_phr_ids = $val_phr_lst->id_lst();
                } else {
                    $common_phr_ids = array_intersect($common_phr_ids, $val_phr_lst->id_lst());
                }
            }
        }

        log_debug('common ');
        $common_phr_ids = array_diff($common_phr_ids, array($this->ids()));  // exclude the list word
        $common_phr_ids = array_values($common_phr_ids);            // cleanup the array

        // display the common words
        log_debug('common dsp');
        if (!empty($common_phr_ids)) {
            $common_phr_lst = new word_list();
            $common_phr_lst->load_by_ids($common_phr_ids, $msg);
            $common_phr_lst_dsp = $common_phr_lst->dsp_obj();
            $result .= ' in (' . implode(",", $common_phr_lst_dsp->names_linked()) . ')<br>';
        }

        // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
        log_debug('tbl_start');
        $result .= $html->dsp_tbl_start();

        // to avoid repeating the same words in each line and to offer a useful "add new value"
        $last_phr_lst = array();

        log_debug('add new button');
        foreach ($this->lst() as $val) {
            //$this->user()->id()  = $val->user()->id();

            // get the words
            $val->load_phrases();
            if (isset($val->phr_lst)) {
                $val_phr_lst = $val->phr_lst;

                // remove the main word from the list, because it should not be shown on each line
                log_debug('remove main ' . $val->id);
                $dsp_phr_lst = $val_phr_lst->dsp_obj();
                log_debug('cloned ' . $val->id);
                if (isset($this->phr)) {
                    if ($this->phr->id() != null) {
                        $dsp_phr_lst->diff_by_ids(array($this->phr->id()));
                    }
                }
                log_debug('removed ' . $this->phr->id());
                $dsp_phr_lst->diff_by_ids($common_phr_ids);
                // remove the words of the previous row, because it should not be shown on each line
                if (isset($last_phr_lst->ids)) {
                    $dsp_phr_lst->diff_by_ids($last_phr_lst->ids);
                }

                //if (isset($val->time_phr)) {
                log_debug('add time ' . $val->id);
                if ($val->time_phr != null) {
                    if ($val->time_phr->id() > 0) {
                        $time_phr = new phrase();
                        $time_phr->load_by_id($val->time_phr->id(), $msg);
                        $val->time_phr = $time_phr;
                        $dsp_phr_lst->add($time_phr, $msg);
                        log_debug('add time word ' . $val->time_phr->name());
                    }
                }

                $result .= '  <tr>';
                $result .= '    <td>';
                log_debug('linked words ' . $val->id);
                $ref_edit = $val->dsp_obj()->ref_edit();
                $result .= '      ' . $dsp_phr_lst->name_linked() . $ref_edit;
                log_debug('linked words ' . $val->id . ' done');
                // to review
                // list the related results
                $res_lst = new result_list();
                $res_lst->load_by_val($val, $msg);
                $result .= $res_lst->frm_links_html();
                $result .= '    </td>';
                log_debug('formula results ' . $val->id . ' loaded');

                // the reused button object

                if ($last_phr_lst != $val_phr_lst) {
                    $last_phr_lst = $val_phr_lst;
                    $result .= '    <td>';
                    $url = $html->url_back(views::VALUE_ADD_ID, $val->id(), '', $back);
                    $btn = new button($url, $back);
                    $result .= \Zukunft\ZukunftCom\main\php\web\html\btn_add_value($val_phr_lst, Null, $this->common_phrases()->ids());

                    $result .= '    </td>';
                }
                $result .= '    <td>';
                $url = $html->url_back(views::VALUE_EDIT_ID, $val->id(), '', $back);
                $btn = new button($url, $back);
                $result .= '      ' . $btn->edit_value($val_phr_lst, $val->id, $this->common_phrases()->ids());
                $result .= '    </td>';
                $result .= '    <td>';
                $url = $html->url_back(views::VALUE_DEL_ID, $val->id(), '', $back);
                $btn = new button($url, $back);
                $result .= '      ' . $btn->del_value($val_phr_lst, $val->id, $this->common_phrases()->ids());
                $result .= '    </td>';
                $result .= '  </tr>';
            }
        }
        log_debug('add new button done');

        $result .= $html->dsp_tbl_end();

        // allow the user to add a completely new value
        log_debug('new');
        if (empty($common_phr_ids)) {
            $common_phr_lst_new = new word_list();
            $common_phr_ids[] = $this->phr->id();
            $common_phr_lst_new->load_by_ids($common_phr_ids, $msg);
        }

        $common_phr_lst = $common_phr_lst->phrase_list($msg);

        // TODO review probably wrong call from /var/www/default/src/main/php/model/view/view.php(267): component_dsp->all(Object(word), 291, 17
        /*
        if (get_class($this->phr) == word::class or get_class($this->phr) == word::class) {
            $this->phr = $this->phr->phrase();
        }
        */
        if ($common_phr_lst->is_valid()) {
            if (!empty($common_phr_lst->lst())) {
                $common_phr_lst->add($this->phr, $msg);
                $phr_lst_ui = new phrase_list($common_phr_lst->api_json([], $msg));
                $result .= $phr_lst_ui->btn_add_value($back);
            }
        }

        log_debug("value_list->html ... done");

        return $result;
    }


}
