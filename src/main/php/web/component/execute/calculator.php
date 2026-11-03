<?php

/*

    web/component/execute/calculator.php - read the calculator triples of a page phrase
    ------------------------------------

    the reader of the calculator view (docs/llm/new_view_sample_pv_balance.md): from the page
    phrase, or the phrase it is built from, to the lists, their items and sub-items in the order
    of the follower chain, and the labels, hints, flags and side results of an item by rule 6;
    built from the request cache only, so the renderers and the unit tests use the same reader


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class calculator
{

    // the phrases and triples of the request cache
    // the position in a column of self::columns of the flag that it shows the plus sign
    // TODO Prio 2 review
    const int COLUMN_PLUS = 6;

    private phrase_list $cac;
    // the phrase of the page e.g. example PV installation
    private phrase $phr;
    // the phrase that the page phrase is built from e.g. PV installation or null for a word
    // TODO Prio 1 create a sample for a calculator with a word as base phrase
    private ?phrase $base;
    // per phrase id what has been read once from the request cache, because a page with many rows
    // asks for the same phrases per cell, but the request cache does not change during a page
    private array $cached_by_id = [];
    private array $triples_by_id = [];
    private array $kinds_by_id = [];

    /**
     * @param phrase $phr the phrase of the page e.g. example PV installation
     * @param phrase_list $cac the phrases and triples of the request cache
     */
    function __construct(phrase $phr, phrase_list $cac)
    {
        $this->cac = $cac;
        $this->phr = $cac->cached_phrase($phr);
        $base = $this->phr->is_triple() ? $this->phr->get_from() : null;
        $this->base = $base == null ? null : $this->cached($base);
    }


    /*
     * page
     */

    /**
     * @return bool true if the page phrase or the phrase it is built from has a calculator list
     */
    function is_calculator(): bool
    {
        $result = false;
        foreach (array_keys(triples::CALCULATOR_LISTS) as $list) {
            if ($this->group($list) != null) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return string the title of the page: the label of the page phrase, else of the phrase it
     *                is built from, else the name of the page phrase
     */
    function title(): string
    {
        $label = $this->page_label();
        return $label != '' ? $label : $this->capital($this->phr->name());
    }

    /**
     * @return string the label of the page phrase, else of the phrase it is built from, else an
     *                empty string e.g. so that the page title falls back to the view name
     */
    function page_label(): string
    {
        return $this->own_text($this->phr, triples::CALCULATOR_LABEL)
            ?? $this->own_text($this->base, triples::CALCULATOR_LABEL)
            ?? '';
    }

    /**
     * @return string the lead text of the page: the hint of the page phrase, else of the phrase
     *                it is built from, else an empty string
     */
    function lead(): string
    {
        return $this->own_text($this->phr, triples::CALCULATOR_HINT)
            ?? $this->own_text($this->base, triples::CALCULATOR_HINT)
            ?? '';
    }


    /*
     * lists
     */

    /**
     * @return string the collapsed lead text of the page phrase, else of the phrase it is built from, else an empty string
     */
    function more(): string
    {
        return $this->own_text($this->phr, triples::CALCULATOR_MORE)
            ?? $this->own_text($this->base, triples::CALCULATOR_MORE)
            ?? '';
    }

    /**
     * @param phrase $item an input or assumption of the page
     * @return string the configured tag of the field or an empty string for the generated tag
     */
    function tag(phrase $item): string
    {
        return $this->own_text($item, triples::CALCULATOR_TAG) ?? '';
    }

    /**
     * @param bool $pro true for the items whose triple supports the claim, false for those that limit it
     * @return phrase[] the argument items of one side of the page in their order
     */
    function arguments(bool $pro): array
    {
        $vrb_id = $pro ? verbs::SUPPORTS_ID : verbs::LIMITS_ID;
        return array_values(array_filter($this->items(triples::CALCULATOR_ARGUMENT),
            fn(phrase $item) => $this->item_phrase($item)?->get_verb()?->id() == $vrb_id));
    }

    /**
     * @param phrase $row a row of the result table
     * @param phrase|null $col_phr the phrase of a column
     * @return phrase|null the phrase whose number the cell of the column shows instead of the row phrase
     */
    function cell_phrase(phrase $row, ?phrase $col_phr): ?phrase
    {
        $result = null;
        $grp = $this->property($row, triples::CALCULATOR_CELL);
        if ($grp != null and $col_phr != null) {
            foreach ($this->sub_items($grp, verbs::HAS_ID) as $cell) {
                if ($this->filter($cell)?->name() == $col_phr->name()) {
                    $result = $this->item_phrase($cell);
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase|null $col_phr the phrase of a result column
     * @return bool true if the column shows the first column minus the second column
     */
    function is_difference(?phrase $col_phr): bool
    {
        $result = false;
        foreach ($this->items(triples::CALCULATOR_RESULT_COLUMN) as $col) {
            if ($this->item_phrase($col)?->name() == $col_phr?->name() and $this->has_flag($col, triples::CALCULATOR_DIFFERENCE)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return array the names of the sources that the texts of the page name by a placeholder in the order of the page
     */
    function text_sources(): array
    {
        $texts = [$this->more()];
        foreach ([...$this->arguments(true), ...$this->arguments(false)] as $item) {
            $texts[] = $this->item_phrase($item)?->get_description() ?? '';
        }
        foreach ([...$this->items(triples::CALCULATOR_INPUT), ...$this->items(triples::CALCULATOR_ASSUMPTION)] as $item) {
            $texts[] = $this->hint($item);
        }
        preg_match_all('/\{source:([^{}]+)\}/', implode(' ', $texts), $found);
        return array_values(array_unique($found[1]));
    }

    /**
     * @return array the names of the own columns of the table of the page phrase in their order, empty if the table uses the columns of all tables
     */
    function table_columns(): array
    {
        return array_map(fn(phrase $col) => $col->name(), $this->table_column_phrases());
    }

    /**
     * @return phrase[] the own columns of the table of the page phrase in their order e.g. potential gain in EUR
     */
    function table_column_phrases(): array
    {
        return $this->table_item_phrases(triples::TABLE_COLUMN);
    }

    /**
     * @return phrase[] the order triples of the own sorting of the table of the page phrase in their order e.g. numeric descending of reward ratio
     */
    function table_sorts(): array
    {
        return $this->table_item_phrases(triples::TABLE_SORT);
    }

    /**
     * @param string $keyword the keyword of the table definition e.g. triples::TABLE_COLUMN
     * @return phrase[] the phrases that the items of the definition name, in their order
     */
    private function table_item_phrases(string $keyword): array
    {
        $phrases = array_map(fn(phrase $item) => $this->item_phrase($item), $this->table_items($keyword));
        return array_values(array_filter($phrases));
    }

    /**
     * @param string $keyword the keyword of the table definition e.g. triples::TABLE_COLUMN
     * @return phrase[] the items of the page phrase itself, because a table definition is not taken over from the phrase it is built from
     */
    private function table_items(string $keyword): array
    {
        $grp = $this->property($this->phr, $keyword);
        return $grp == null ? [] : $this->sub_items($grp, verbs::HAS_ID);
    }

    /**
     * the group triple of a list: the triple from the page phrase to the list keyword, else the
     * one of the phrase that the page phrase is built from, so that every installation that is
     * built from PV installation gets the page without own triples
     *
     * @param string $list the name of the list keyword e.g. triples::CALCULATOR_INPUT
     * @return phrase|null the group triple e.g. PV installation inputs or null if there is no such list
     */
    function group(string $list): ?phrase
    {
        return $this->property($this->phr, $list) ?? $this->property($this->base, $list);
    }

    /**
     * @param string $list the name of the list keyword e.g. triples::CALCULATOR_INPUT
     * @return phrase[] the items of the list in the order of the follower chain
     */
    function items(string $list): array
    {
        $grp = $this->group($list);
        return $grp == null ? [] : $this->sub_items($grp, verbs::HAS_ID);
    }

    /**
     * the triples from an item with the given verb, without its properties and its order
     * triple, e.g. the rows of a row group (has a) or the filter of a row (with)
     *
     * @param phrase $item the group or the item e.g. PV installation rows saving
     * @param int $vrb_id the id of the verb e.g. verbs::HAS_ID for the rows or verbs::WITH_ID for a filter
     * @return phrase[] the sub-items in the order of the follower chain
     */
    function sub_items(phrase $item, int $vrb_id): array
    {
        $result = [];
        foreach ($this->triples_from($item) as $trp) {
            if ($trp->get_verb()?->id() == $vrb_id and !$this->is_keyword($trp->get_to())) {
                $result[] = $trp;
            }
        }
        return $this->sorted($result);
    }

    /**
     * @param phrase $item a row e.g. PV installation row saving consumption mix
     * @return phrase|null the filter phrase of the row e.g. net avoided emission or null if the row has none
     */
    function filter(phrase $item): ?phrase
    {
        $flt = $this->sub_items($item, verbs::WITH_ID);
        return $flt == [] ? null : $this->cached($flt[0]->get_to());
    }


    /*
     * texts and flags
     */

    /**
     * the label of an item by rule 6: the label of the item, then of its phrase, then for a row
     * built from the group phrase the label or the name of the other phrase (rule 8), then the
     * name of the phrase with the first letter in capitals; never the label of the filter phrase,
     * because a filter names the group and not the row
     *
     * @param phrase $item the item e.g. PV installation row battery discharge
     * @param phrase|null $grp the row group item of a row e.g. PV installation rows production emissions
     * @return string the text that the page shows for the item
     */
    function label(phrase $item, ?phrase $grp = null): string
    {
        $phr = $this->item_phrase($item);
        $result = $this->own_text($item, triples::CALCULATOR_LABEL)
            ?? $this->own_text($phr, triples::CALCULATOR_LABEL);
        if ($result == null and $grp != null) {
            $other = $this->other_phrase($phr, $this->item_phrase($grp));
            if ($other != null) {
                $result = $this->own_text($other, triples::CALCULATOR_LABEL) ?? $this->capital($other->name());
            }
        }
        return $result ?? $this->capital($phr?->name() ?? '');
    }

    /**
     * @param phrase $item the item e.g. PV installation row battery discharge
     * @return string the hint of the item, else of its phrase, else an empty string
     */
    function hint(phrase $item): string
    {
        return $this->own_text($item, triples::CALCULATOR_HINT)
            ?? $this->own_text($this->item_phrase($item), triples::CALCULATOR_HINT)
            ?? '';
    }

    /**
     * @param phrase $item a row e.g. PV installation row saving PV-weighted mix
     * @return string the note behind the unit of the number in the label of the row e.g. PV-weighted, else an empty string
     */
    function number_note(phrase $item): string
    {
        return $this->own_text($item, triples::CALCULATOR_NUMBER_NOTE) ?? '';
    }

    /**
     * @param phrase|null $item a list or a detail table e.g. portfolio split inputs
     * @param string $keyword the name of the button keyword e.g. triples::CALCULATOR_DOWNLOAD
     * @return string the label of the triple from the item to the keyword e.g. Download, '' if the item has no such button
     */
    function button_label(?phrase $item, string $keyword): string
    {
        return $this->own_label($this->property($item, $keyword));
    }

    /**
     * @return bool true if the page is built from another phrase, of which a case can be another kind
     */
    function has_base(): bool
    {
        return $this->base != null;
    }

    /**
     * @return string the name of the case that the page shows e.g. Miller family of Miller family portfolio split, empty for the default case
     */
    function case_name(): string
    {
        $is_case = ($this->phr->is_triple() and $this->phr->get_verb()?->id() == verbs::OF_ID);
        return $is_case ? ($this->phr->get_to()?->name() ?? '') : '';
    }

    /**
     * @return int the phrase id of the page, which is negative for a triple
     */
    function phrase_id(): int
    {
        return $this->phr->id();
    }

    /**
     * @param phrase $col a column of a detail table e.g. portfolio split walk column cumulative deviation
     * @return string the title of the diagram of the column, '' for a column without a diagram
     */
    function diagram_title(phrase $col): string
    {
        return $this->own_text($col, triples::CALCULATOR_DIAGRAM) ?? '';
    }

    /**
     * @param phrase $col a column of a detail table with a diagram
     * @return array per line of the diagram its filter phrase, its colour style and its label: one line per
     *               instance if the filter of the column is an instance of a repeated input e.g. each child, else the column
     */
    function diagram_lines(phrase $col): array
    {
        $flt = $this->filter($col);
        $result = [[$flt, '', $this->label($col)]];
        foreach ($flt == null ? [] : $this->row_kinds($flt) as $kind) {
            if ($this->is_repeated($kind)) {
                $result = [];
                foreach (array_values($this->kinds($kind)) as $pos => $instance) {
                    $result[] = [$instance, $this->column_colour(true, $pos), mb_ucfirst($this->phrase_label($instance))];
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase $phr a phrase that is no item e.g. a row of a detail table
     * @return string the hint of the phrase e.g. the full name of a ticker, an empty string if it has none
     */
    function phrase_hint(phrase $phr): string
    {
        return $this->own_text($this->cached($phr), triples::CALCULATOR_HINT) ?? '';
    }

    /**
     * @param phrase $row a row of a detail table e.g. CHF cash
     * @return phrase[] the phrases that the row is e.g. cash
     */
    function row_kinds(phrase $row): array
    {
        return array_filter(array_map(fn(phrase $trp) => $this->item_phrase($trp), $this->sub_items($this->cached($row), verbs::IS_ID)));
    }

    /**
     * @param phrase $item a repeated input item e.g. portfolio split input child portfolio
     * @return string the text of the button that adds an instance e.g. Add child
     */
    function repeat_label(phrase $item): string
    {
        return $this->own_text($item, triples::CALCULATOR_REPEAT) ?? '';
    }

    /**
     * @param phrase $item the item e.g. a row or a row group
     * @param string $flag the name of the flag keyword e.g. triples::CALCULATOR_HIGHLIGHT
     * @return bool true if the item, its phrase or the filter phrase of the item has the flag
     */
    function has_flag(phrase $item, string $flag): bool
    {
        $result = false;
        foreach ([$item, $this->item_phrase($item), $this->filter($item)] as $phr) {
            if ($this->property($phr, $flag) != null) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * the candidates of the side results of a row: the phrases that the phrase or the filter
     * phrase of the row has a, e.g. the emission payback of net avoided emission; the renderer
     * shows only those with a result for the page phrase and the row phrases
     *
     * @param phrase $item a row e.g. PV installation row saving consumption mix
     * @return phrase[] the side result phrases without the calculator properties
     */
    function side_results(phrase $item): array
    {
        $result = [];
        foreach ([$this->item_phrase($item), $this->filter($item)] as $phr) {
            if ($phr != null) {
                foreach ($this->triples_from($phr) as $trp) {
                    $to = $trp->get_to();
                    if ($trp->get_verb()?->id() == verbs::HAS_ID and $to != null and !$this->is_keyword($to)) {
                        $result[] = $this->cached($to);
                    }
                }
            }
        }
        return $result;
    }


    /**
     * @param phrase|null $item a list item e.g. PV installation input yield factor
     * @return phrase|null the phrase that the item lists e.g. yield factor
     */
    function item_phrase(?phrase $item): ?phrase
    {
        $to = $item?->get_to();
        return $to == null ? null : $this->cached($to);
    }

    /**
     * @param phrase|null $phr e.g. the group of the inputs
     * @return string the own label of the phrase without a fallback, e.g. so that a group without
     *                a label gets the translated default heading, or an empty string
     */
    function own_label(?phrase $phr): string
    {
        return $this->own_text($phr, triples::CALCULATOR_LABEL) ?? '';
    }

    /**
     * @param phrase|null $phr an item e.g. a detail table
     * @return string the own hint of the item without the hint of its phrase, or an empty string
     */
    function own_hint(?phrase $phr): string
    {
        return $this->own_text($phr, triples::CALCULATOR_HINT) ?? '';
    }

    /**
     * @param phrase $phr a phrase that is no item e.g. an option of a selector or a row of a detail table
     * @return string the label of the phrase, else its acronym e.g. Jan for January, else its name with a capital first letter
     */
    function phrase_label(phrase $phr): string
    {
        return $this->own_text($this->cached($phr), triples::CALCULATOR_LABEL)
            ?? $this->acronym($phr)
            ?? $this->capital($phr->name());
    }

    /**
     * the options of a phrase selector (rule 6): the "has a" triples of the field phrase in their
     * order e.g. yield factor has a south, else the phrases that are a field phrase e.g. residual
     * mix is a replacement mix
     *
     * @param phrase $phr the field phrase e.g. yield factor
     * @return array the option phrase and its label for each option in the order of the selector
     */
    function options(phrase $phr): array
    {
        $result = [];
        foreach ($this->sub_items($phr, verbs::HAS_ID) as $opt) {
            $result[] = [$this->item_phrase($opt), $this->label($opt)];
        }
        if ($result == []) {
            foreach ($this->kinds($phr) as $opt) {
                $result[] = [$opt, $this->phrase_label($opt)];
            }
        }
        return $result;
    }

    /**
     * @return array per warning of the page its phrase e.g. target share excess and its text, sorted by the text
     */
    function warnings(): array
    {
        $result = [];
        foreach ($this->cac->lst() as $trp) {
            $is_warning = ($trp->is_triple() and $trp->get_to()?->name() == triples::CALCULATOR_WARNING);
            if ($is_warning and $trp->get_from() != null) {
                $result[$trp->get_description()] = [$this->cached($trp->get_from()), $trp->get_description()];
            }
        }
        ksort($result);
        return array_values($result);
    }

    /**
     * the columns of a table, where the column of a repeated input is shown once per instance
     * e.g. first child and second child for the child portfolio
     *
     * @param phrase[] $cols the column items of the table
     * @return array per column its label, its phrase, its filter phrase, its colour style ('' without the colour flag),
     *               if it is an instance of a repeated phrase, if it shows a phrase instead of a number,
     *               if it shows the plus sign and if it shows the unit of each number
     */
    function columns(array $cols): array
    {
        $result = [];
        foreach ($cols as $col) {
            $phr = $this->item_phrase($col);
            $flt = $this->filter($col);
            $has_colour = $this->has_flag($col, triples::CALCULATOR_COLOUR);
            $repeated = $this->is_repeated($phr);
            $is_phrase = $this->has_flag($col, triples::CALCULATOR_PHRASE_COLUMN);
            // a column can name an instance shorter e.g. First for the first child
            $short = [];
            foreach ($repeated ? $this->sub_items($col, verbs::HAS_ID) : [] as $sub) {
                $short[$this->item_phrase($sub)?->id()] = $this->label($sub);
            }
            foreach (array_values($repeated ? $this->kinds($phr) : [$phr]) as $pos => $col_phr) {
                $label = $repeated ? ($short[$col_phr->id()] ?? mb_ucfirst($this->phrase_label($col_phr))) : $this->label($col);
                $result[] = [$label, $col_phr, $flt, $has_colour ? $this->column_colour($repeated, $pos) : '', $repeated, $is_phrase,
                    $this->has_flag($col, triples::CALCULATOR_SIGNED), $this->has_flag($col, triples::CALCULATOR_COLUMN_UNIT)];
            }
        }
        return $result;
    }

    /**
     * the rows of a detail table: the phrases at the end of the is a and is part of levels below
     * the table phrase e.g. LISP is a stock is a holding is part of split walk
     *
     * @param phrase $phr the phrase of the table or of a level
     * @param array $seen the ids of the phrases above, so that a loop in the data ends
     * @return phrase[] the phrases without a level below, in the order of their levels
     */
    function row_phrases(phrase $phr, array $seen = []): array
    {
        $below = [];
        foreach ($this->cac->lst() as $trp) {
            $vrb_id = $trp->is_triple() ? $trp->get_verb()?->id() : null;
            $from = $trp->is_triple() ? $trp->get_from() : null;
            $is_level = ($vrb_id == verbs::IS_ID or $vrb_id == verbs::PART_ID);
            if ($is_level and $from != null and $trp->get_to()?->id() == $phr->id() and !in_array($from->id(), $seen)) {
                $below[] = $this->cached($from);
            }
        }
        $result = [];
        foreach ($this->sorted($below, false) as $kid) {
            $leaves = $this->row_phrases($kid, [...$seen, $phr->id()]);
            $result = [...$result, ...($leaves == [] ? [$kid] : $leaves)];
        }
        return $result;
    }

    /**
     * @param bool $repeated true for a column of an instance of a repeated input e.g. the first child
     * @param int $pos the position of the instance
     * @return string the colour style of the column: the first colour for a single column wherever it stands, the others cycling for the instances
     */
    private function column_colour(bool $repeated, int $pos): string
    {
        $cycle = count(styles::CALC_COLUMN_COLOURS) - 1;
        return styles::CALC_COLUMN_COLOURS[$repeated ? 1 + $pos % $cycle : 0];
    }

    /**
     * @param phrase|null $phr the phrase of a column e.g. child portfolio
     * @return bool true if an input item of the page repeats the phrase
     */
    private function is_repeated(?phrase $phr): bool
    {
        $result = false;
        foreach ($this->items(triples::CALCULATOR_INPUT) as $item) {
            $same = ($phr != null and $this->item_phrase($item)?->id() == $phr->id());
            if ($same and $this->has_flag($item, triples::CALCULATOR_REPEAT)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param phrase $phr e.g. month or replacement mix
     * @return phrase[] the phrases that are a phrase e.g. January to December, in the order of
     *                  the follower chain and the others sorted by label
     */
    function kinds(phrase $phr): array
    {
        if (!array_key_exists($phr->id(), $this->kinds_by_id)) {
            $result = [];
            foreach ($this->cac->lst() as $trp) {
                if ($trp->is_triple() and $trp->get_verb()?->id() == verbs::IS_ID
                    and $trp->get_to()?->id() == $phr->id() and $trp->get_from() != null) {
                    $result[] = $this->cached($trp->get_from());
                }
            }
            $this->kinds_by_id[$phr->id()] = $this->sorted($result, false);
        }
        return $this->kinds_by_id[$phr->id()];
    }

    /**
     * @param phrase[] $phr_lst e.g. the option phrases of a selector
     * @return phrase|null the phrase of the list that the page phrase uses e.g. east-west or null if it uses none
     */
    function used(array $phr_lst): ?phrase
    {
        $ids = array_map(fn($opt) => $opt->id(), $phr_lst);
        foreach ($this->triples_from($this->phr) as $trp) {
            if ($trp->get_verb()?->id() == verbs::USES_ID and in_array($trp->get_to()?->id(), $ids)) {
                return $this->cached($trp->get_to());
            }
        }
        return null;
    }

    /**
     * the phrase whose numbers a row shows: the option that the page phrase uses if the phrase
     * is a selector, so that the row follows the selector e.g. residual mix for replacement mix
     *
     * @param phrase|null $phr the phrase of a row e.g. replacement mix
     * @return phrase|null the used option e.g. residual mix, else the phrase itself
     */
    function chosen(?phrase $phr): ?phrase
    {
        return $phr == null ? null : ($this->used(array_column($this->options($phr), 0)) ?? $phr);
    }

    /**
     * the phrases whose general triples the page reads by rule 6 and 8: the phrase of each item,
     * of each row, detail column and extra row, the filter phrase of a row and the other phrase
     * of a row built from the phrase of its group; so that the request cache can be filled with
     * the labels, hints, flags, side results and options of all of them with one load
     *
     * @return phrase[] the phrases in the order of the lists, each once
     */
    function phrases(): array
    {
        $result = [];
        foreach (array_keys(triples::CALCULATOR_LISTS) as $list) {
            foreach ($this->items($list) as $item) {
                $phr = $this->item_phrase($item);
                $result[] = $phr;
                // the options of a selector, because an option may have a label of its own
                if ($phr != null) {
                    array_push($result, ...array_column($this->options($phr), 0));
                }
                foreach ([...$this->sub_items($item, verbs::HAS_ID), ...$this->sub_items($item, verbs::WITH_ID)] as $sub) {
                    $sub_phr = $this->item_phrase($sub);
                    array_push($result, $sub_phr, $this->filter($sub), $this->other_phrase($sub_phr, $phr));
                }
            }
        }
        $by_id = [];
        foreach (array_filter($result) as $phr) {
            $by_id[$phr->id()] ??= $phr;
        }
        return array_values($by_id);
    }


    /*
     * internal
     */

    /**
     * @param phrase|null $phr the phrase of a row e.g. PV embodied emission = embodied emission of PV
     * @param phrase|null $grp_phr the phrase of the row group e.g. embodied emission
     * @return phrase|null the other phrase of the row triple e.g. PV or null if the row is not built from the group phrase
     */
    private function other_phrase(?phrase $phr, ?phrase $grp_phr): ?phrase
    {
        $result = null;
        if ($phr != null and $grp_phr != null and $phr->is_triple()) {
            $from = $phr->get_from();
            $to = $phr->get_to();
            if ($from != null and $to != null) {
                if ($from->id() == $grp_phr->id()) {
                    $result = $this->cached($to);
                } elseif ($to->id() == $grp_phr->id()) {
                    $result = $this->cached($from);
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase|null $phr the phrase or the item e.g. battery discharge
     * @param string $keyword the name of the text keyword e.g. triples::CALCULATOR_LABEL
     * @return string|null the description of the triple from the phrase to the keyword or null if there is none
     */
    function own_text(?phrase $phr, string $keyword): ?string
    {
        $txt = $this->property($phr, $keyword)?->get_description();
        return $txt == '' ? null : $txt;
    }

    /**
     * @param phrase $phr a phrase e.g. January
     * @return string|null the name of the phrase that is an acronym for it e.g. Jan or null if it has none
     */
    private function acronym(phrase $phr): ?string
    {
        $result = null;
        foreach ($this->cac->lst() as $trp) {
            if ($result == null and $trp->is_triple() and $trp->get_verb()?->id() == verbs::ACRONYM_ID
                and $trp->get_to()?->id() == $phr->id() and $trp->get_from() != null) {
                $result = $this->cached($trp->get_from())->name();
            }
        }
        return $result;
    }

    /**
     * @param phrase|null $phr the phrase or the item e.g. PV installation
     * @param string $keyword the name of the keyword e.g. triples::CALCULATOR_INPUT
     * @return phrase|null the triple from the phrase to the keyword e.g. PV installation inputs
     */
    function property(?phrase $phr, string $keyword): ?phrase
    {
        $result = null;
        if ($phr != null) {
            foreach ($this->triples_from($phr) as $trp) {
                $to = $trp->get_to();
                if ($result == null and $to != null and $this->cached($to)->name() == $keyword) {
                    $result = $trp;
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase|null $phr a phrase that a triple points to
     * @return bool true if the phrase is a calculator keyword, so the triple to it is a list or a property
     *              and never an item, also for a proposed keyword that the page does not show yet
     */
    private function is_keyword(?phrase $phr): bool
    {
        $result = false;
        if ($phr != null) {
            $kwd = $this->cached($phr);
            $name = $kwd->name();
            $result = (array_key_exists($name, triples::CALCULATOR_LISTS)
                or array_key_exists($name, triples::CALCULATOR_PROPERTIES)
                or $this->is_from_calculator_keyword($kwd));
        }
        return $result;
    }

    /**
     * @param phrase $phr a phrase of the request cache e.g. calculator file import
     * @return bool true if the phrase is a triple from the calculator keyword (calculator (system))
     */
    private function is_from_calculator_keyword(phrase $phr): bool
    {
        $result = false;
        if ($phr->is_triple() and $phr->get_from() != null) {
            $result = ($this->cached($phr->get_from())->name() == triples::SYSTEM_CALCULATOR);
        }
        return $result;
    }

    /**
     * @param phrase $phr the phrase or the item
     * @return phrase[] the triples of the request cache from the phrase
     */
    private function triples_from(phrase $phr): array
    {
        if (!array_key_exists($phr->id(), $this->triples_by_id)) {
            $this->triples_by_id[$phr->id()] = $this->cac->children($phr, new user_message())->lst(); // not reported: the triples are only read from the request cache
        }
        return $this->triples_by_id[$phr->id()];
    }

    /**
     * sort the items by rule 7: the longest follower chain first, then the items in no chain
     * sorted by label; an item is in the chain if it is the follower of the item before
     *
     * @param phrase[] $items the items of one list
     * @param bool $as_items false for phrases that are no list items e.g. the rows of a detail
     *                       table, which are sorted by their own label
     * @return phrase[] the items in the order of the page
     */
    private function sorted(array $items, bool $as_items = true): array
    {
        $ids = array_map(fn(phrase $item) => $item->id(), $items);
        $by_id = array_combine($ids, $items);
        // the follower of each item that is in the same list
        $next = [];
        foreach ($items as $item) {
            foreach ($this->triples_from($item) as $trp) {
                $prev_id = $trp->get_to()?->id();
                if ($trp->get_verb()?->id() == verbs::FOLLOW_ID and in_array($prev_id, $ids)) {
                    $next[$prev_id] = $item->id();
                }
            }
            // a row can follow a row of another kind e.g. a stock after a bond, which keeps the order of the stocks
            if (!$as_items and !in_array($item->id(), $next)) {
                $prev_id = $this->previous_in_list($item, $ids);
                if ($prev_id !== null) {
                    $next[$prev_id] = $item->id();
                }
            }
        }
        // the longest chain from an item that follows no other item
        $chain = [];
        foreach ($ids as $id) {
            if (!in_array($id, $next)) {
                $ids_of_chain = [$id];
                while (array_key_exists(end($ids_of_chain), $next)
                    and !in_array($next[end($ids_of_chain)], $ids_of_chain)) {
                    $ids_of_chain[] = $next[end($ids_of_chain)];
                }
                if (count($ids_of_chain) > 1 and count($ids_of_chain) > count($chain)) {
                    $chain = $ids_of_chain;
                }
            }
        }
        $rest = [];
        foreach ($items as $item) {
            if (!in_array($item->id(), $chain)) {
                $label = $as_items ? $this->label($item) : $this->phrase_label($item);
                $rest[$label . ' ' . $item->id()] = $item;
            }
        }
        ksort($rest);
        return array_merge(array_map(fn(int $id) => $by_id[$id], $chain), array_values($rest));
    }

    /**
     * @param phrase $item a phrase e.g. GIVN
     * @param array $ids the ids of the phrases of the list
     * @return int|null the id of the nearest phrase of the list before the item in its follower chain, also over phrases of other lists, null if there is none
     */
    private function previous_in_list(phrase $item, array $ids): ?int
    {
        $result = null;
        $seen = [$item->id()];
        $cur = $item;
        while ($cur != null and $result === null) {
            $prev = null;
            foreach ($this->triples_from($cur) as $trp) {
                if ($trp->get_verb()?->id() == verbs::FOLLOW_ID and !in_array($trp->get_to()?->id(), $seen)) {
                    $prev = $trp->get_to();
                }
            }
            if ($prev != null and in_array($prev->id(), $ids)) {
                $result = $prev->id();
            }
            $seen[] = $prev?->id();
            $cur = $prev == null ? null : $this->cached($prev);
        }
        return $result;
    }

    /**
     * @param phrase $phr a phrase that a triple names e.g. only by its id and name
     * @return phrase the phrase of the request cache with its description, else the given phrase
     */
    private function cached(phrase $phr): phrase
    {
        $result = $this->cached_by_id[$phr->id()] ?? $this->cac->cached_phrase($phr);
        // a phrase that the request cache does not have stays itself, so it is not kept for another phrase without an id
        if ($result !== $phr) {
            $this->cached_by_id[$phr->id()] = $result;
        }
        return $result;
    }

    /**
     * @param string $name the name of a phrase e.g. battery
     * @return string the name with the first letter in capitals e.g. Battery (rule 5)
     */
    private function capital(string $name): string
    {
        return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
    }

}
