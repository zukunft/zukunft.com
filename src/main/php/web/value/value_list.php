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
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\library;

class value_list extends ListBase
{

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

    function load_by_phr_lst(phrase_list $phr_lst, user_message $msg): bool
    {
        $result = false;
        $rest = new rest_call();

        $data = array();
        $data[api::JSON_LIST_PHRASE_IDS] = $phr_lst->ids();
        $json_body = $rest->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
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
     * @return string the html code of the value table or '' if this list is empty
     */
    function table_by_related_columns(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        string       $back = '',
        array        $col_order = [],
        bool         $with_header = false,
        bool         $with_border = true
    ): string
    {
        $result = '';
        if (!$this->is_empty()) {
            $html = new html_base();
            // the row order follows the impact, so it never depends on the api/db row order
            $this->sort_by_impact();
            // a column phrase needs to be used by at least two values, else the column has one entry
            [$phr_by_id, $val_phr_ids] = $this->phrase_ranking(
                $this->lst(), $msg, $context_phr_lst, config::MIN_PHRASE_GROUP - 1);
            // a phrase that the system column tiers define as a column wins over the impact
            // ranking and is used even if only one value carries it
            if ($col_order != []) {
                [$all_by_id, $val_phr_ids] = $this->phrase_ranking(
                    $this->lst(), $msg, $context_phr_lst, 0);
                $phr_by_id = $this->columns_by_definition($all_by_id, $phr_by_id, $col_order);
            }

            // the column phrases and per value the column it belongs to
            $col_phr = [];
            $val_col = [];
            $remaining = $this->lst();
            foreach ($phr_by_id as $id => $phr) {
                // no break in the loop, so the free column count is checked per phrase
                if (count($col_phr) < position_types::MAX_SIDE_COLUMNS) {
                    [$members, $rest] = $this->split_by_phrase($remaining, $id, $val_phr_ids);
                    if ($members != []) {
                        $col_phr[$id] = $phr;
                        foreach ($members as $val) {
                            $val_col[$val->id()] = $id;
                        }
                        $remaining = $rest;
                    }
                }
            }
            // the values that share no column phrase get a last column of their own, so that
            // no value of the list is silently dropped from the table
            $rest_col = $remaining != [];

            // per row the label and per row and column the value html
            $row_label = [];
            $cells = [];
            foreach ($this->lst() as $val) {
                $col_id = $val_col[$val->id()] ?? '';
                $ctx = clone $context_phr_lst;
                if ($col_id !== '') {
                    $ctx->add_phrase($col_phr[$col_id]);
                }
                // the row is named by the phrases that are left after the context and the column
                // phrase, e.g. the year if the columns are inhabitants and area
                $row_key = $val->grp->name($ctx);
                if (!key_exists($row_key, $row_label)) {
                    $row_label[$row_key] = $val->grp->name_link_list($ctx);
                    $cells[$row_key] = [];
                }
                // two values with the same row and column are shown in the same cell instead of
                // the second one replacing the first
                $cells[$row_key][$col_id][] = $val->value_edit($msg, $back);
            }

            // the column row keeps the top left cell empty, because the row phrases differ per row
            $header = $html->th('');
            foreach ($col_phr as $phr) {
                $header .= $html->th($phr->name_link());
            }
            if ($rest_col) {
                $header .= $html->th(msg_id::FORM_SUB_TITLE_VALUES->text());
            }
            $rows = $html->tr($header);
            foreach ($row_label as $row_key => $label) {
                $row = $html->td($label);
                foreach (array_keys($col_phr) as $col_id) {
                    $row .= $html->td(implode(', ', $cells[$row_key][$col_id] ?? []));
                }
                if ($rest_col) {
                    $row .= $html->td(implode(', ', $cells[$row_key][''] ?? []));
                }
                $rows .= $html->tr($row);
            }
            $result = $html->tbl($rows, $with_border ? html_base::SIZE_FULL : styles::TABLE_PUR);
            // the header names the phrase that the reader has selected centred above the table,
            // so that a table taken out of its page still says what it is about; more than one
            // row means more than one item of the phrase, so the header names it in the plural
            if ($with_header) {
                if (count($row_label) > 1) {
                    // a table of several items is a list of its own, so its header is a headline
                    $header_html = $html->text_h2($context_phr_lst->plural());
                } else {
                    // a table of one item only labels that item, so the header stays small
                    $header_html = $html->text_h3($context_phr_lst->name_link_list());
                }
                $result = $html->div_center($header_html) . $result;
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
    private function columns_by_definition(array $all, array $ranked, array $col_order): array
    {
        $result = [];
        // the defined columns first, in the order of the definition
        foreach ($col_order as $name) {
            foreach ($all as $id => $phr) {
                if ($phr->name() == $name and !array_key_exists($id, $result)) {
                    $result[$id] = $phr;
                }
            }
        }
        // then the phrases the data suggests, still ordered by the aggregated impact
        foreach ($ranked as $id => $phr) {
            if (!array_key_exists($id, $result)) {
                $result[$id] = $phr;
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
        $members = [];
        $rest = [];
        foreach ($remaining as $val) {
            if (isset($val_phr_ids[$val->id()][$phr_id])) {
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
     * the "... and n more" tail of a truncated value list as a link that shows all values
     * of the page phrase via the phrase values view (docs/llm/frontend.md: a "more" is
     * always a link that shows more); only if no phrase is known that could select the
     * full list the tail stays a plain text
     *
     * @param int $diff the number of values that are not shown
     * @param phrase_list $context_phr_lst the phrases assumed by the reader; the first is the page phrase
     * @return string the html code of the more tail
     */
    private function more_tail(int $diff, phrase_list $context_phr_lst): string
    {
        $html = new html_base();
        $txt = msg_id::THREE_POINTS->text() . ' ' . msg_id::AND_MORE_BEFORE->text() . ' '
            . $diff . ' ' . msg_id::MORE->text();
        $phr = $context_phr_lst->lst()[0] ?? null;
        if ($phr != null) {
            $result = $html->ref($html->url_back(views::PHRASE_VALUES_ID, $phr->id()), $txt);
        } else {
            $result = $txt;
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
