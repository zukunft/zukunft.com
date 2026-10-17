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

include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class calculator
{

    // the phrases and triples of the request cache
    private phrase_list $cac;
    // the phrase of the page e.g. example PV installation
    private phrase $phr;
    // the phrase that the page phrase is built from e.g. PV installation or null for a word
    private ?phrase $base;

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
        return $this->own_text($this->phr, triples::CALCULATOR_LABEL)
            ?? $this->own_text($this->base, triples::CALCULATOR_LABEL)
            ?? $this->capital($this->phr->name());
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
     * @param phrase $phr a phrase that is no item e.g. an option of a selector that is a kind of the field phrase
     * @return string the label of the phrase, else its name with a capital first letter
     */
    function phrase_label(phrase $phr): string
    {
        return $this->own_text($this->cached($phr), triples::CALCULATOR_LABEL) ?? $this->capital($phr->name());
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
            foreach ($this->cac->lst() as $trp) {
                if ($trp->is_triple() and $trp->get_verb()?->id() == verbs::IS_ID
                    and $trp->get_to()?->id() == $phr->id()) {
                    $opt = $this->cached($trp->get_from());
                    $result[] = [$opt, $this->phrase_label($opt)];
                }
            }
            usort($result, fn($a, $b) => strcmp($a[1], $b[1]));
        }
        return $result;
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
    private function own_text(?phrase $phr, string $keyword): ?string
    {
        $txt = $this->property($phr, $keyword)?->get_description();
        return $txt == '' ? null : $txt;
    }

    /**
     * @param phrase|null $phr the phrase or the item e.g. PV installation
     * @param string $keyword the name of the keyword e.g. triples::CALCULATOR_INPUT
     * @return phrase|null the triple from the phrase to the keyword e.g. PV installation inputs
     */
    private function property(?phrase $phr, string $keyword): ?phrase
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
     */
    private function is_keyword(?phrase $phr): bool
    {
        $name = $phr == null ? '' : $this->cached($phr)->name();
        return array_key_exists($name, triples::CALCULATOR_LISTS)
            or array_key_exists($name, triples::CALCULATOR_PROPERTIES);
    }

    /**
     * @param phrase $phr the phrase or the item
     * @return phrase[] the triples of the request cache from the phrase
     */
    private function triples_from(phrase $phr): array
    {
        return $this->cac->children($phr, new user_message())->lst(); // not reported: the triples are only read from the request cache
    }

    /**
     * sort the items by rule 7: the longest follower chain first, then the items in no chain
     * sorted by label; an item is in the chain if it is the follower of the item before
     *
     * @param phrase[] $items the items of one list
     * @return phrase[] the items in the order of the page
     */
    private function sorted(array $items): array
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
                $rest[$this->label($item) . ' ' . $item->id()] = $item;
            }
        }
        ksort($rest);
        return array_merge(array_map(fn(int $id) => $by_id[$id], $chain), array_values($rest));
    }

    /**
     * @param phrase $phr a phrase that a triple names e.g. only by its id and name
     * @return phrase the phrase of the request cache with its description, else the given phrase
     */
    private function cached(phrase $phr): phrase
    {
        return $this->cac->cached_phrase($phr);
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
