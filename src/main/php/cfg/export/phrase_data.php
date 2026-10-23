<?php

/*

    model/export/phrase_data.php - all data of one phrase in the import format with the database ids
    ----------------------------

    $phd is the suggested var name

    the answer of api/phraseData; every object carries its database id, because the answer is for
    a client of this pod e.g. the calculator editor and not for another pod
    (src/test/resources/api/phrase_data/phrase_data_pv_install.json is a sample)

    the walk (phrase_data_walks) decides where the data starts
    - VALUE (the default): the phrase alone, so the data are the values that have the phrase
    - FROM: the walked phrases, e.g. the whole calculator tree of a calculator page

    the data of a phrase are
    - for the from walk the walked phrases: the phrase, the phrases it is built from and every triple whose from
      side is a walked phrase; the to side of a triple is walked too if it is an item ("has a")
      e.g. "PV capacity" of "PV installation input PV capacity", because the to side of another
      triple is a category e.g. "climate solution", which links to the data of other phrases
    - the keywords of the calculator and of the input fields (triples::CALCULATOR_KEYWORDS) are
      never walked, because every page phrase links to them; a phrase selector field follows its
      calculator rule instead and adds its options, the phrases that "is a" the field phrase
    - the values of the phrase resp. of the walked triples with all their phrases, the formulas of the phrases with
      the phrases they use, the labels, hints and flags (triples::CALCULATOR_PROPERTIES) and the
      symbols, acronyms and aliases (verbs::NAME_VERBS) of the phrases, the phrases that every
      triple is built from and the sources of the values
    - on request the results of the phrase resp. of the walked triples

    The main sections of this object are
    - export:            the json array of the phrase data
    - walk:              the phrases that are followed and the keywords that are not
    - add:               the phrases that the walked phrases need
    - internal:          the header and the objects with their id


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

namespace Zukunft\ZukunftCom\main\php\cfg\export;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_FORMULA . 'expression.php';
include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'phrase_data_walks.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\shared\const\def as shared_def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;

class phrase_data
{

    // the time format of the header e.g. 2026-10-16 01:40:00
    const string TIME_FORMAT = 'Y-m-d H:i:s';


    /*
     * export
     */

    /**
     * all data of one phrase in the import format with the database id of each object
     *
     * @param int $phr_id the id of the requested phrase e.g. -2780 for the triple example PV installation
     * @param phrase_data_walks $walk VALUE for the values of the phrase, FROM for the triples on its from side
     * @param bool $incl_res true to add the results of the phrase resp. of the walked triples
     * @param user_message $msg with the requesting user, to report e.g. a phrase that does not exist
     * @return array the json array of the phrase data or an empty array if the phrase does not exist
     */
    function export_json(int $phr_id, phrase_data_walks $walk, bool $incl_res, user_message $msg): array
    {
        $result = [];
        $phr = new phrase($msg->usr);
        if ($phr->load_by_id($phr_id, $msg) == 0) {
            $lib = new library();
            $msg->add(msg_id::OBJECT_NOT_FOUND, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name(phrase::class),
                msg_id::VAR_ID => (string)$phr_id
            ]);
        } else {
            $result = $this->phrase_vars($phr, $walk, $incl_res, $msg);
        }
        return $result;
    }

    /**
     * @param phrase $phr the loaded requested phrase
     * @param phrase_data_walks $walk VALUE for the values of the phrase, FROM for the triples on its from side
     * @param bool $incl_res true to add the results of the phrase resp. of the walked triples
     * @param user_message $msg with the requesting user
     * @return array the header and the objects of the phrase data in the order of an import file
     */
    private function phrase_vars(phrase $phr, phrase_data_walks $walk, bool $incl_res, user_message $msg): array
    {
        $usr = $msg->usr;
        $this->debug_step('start ' . $walk->name . ' walk of ' . $phr->dsp_id(), 1);
        $kwd_ids = $this->keyword_ids($msg);
        $this->debug_step('keywords loaded', count($kwd_ids));
        $phr_lst = $this->start_phrases($phr, $walk, $kwd_ids, $msg);
        $this->debug_step('start phrases', $phr_lst->count());
        $val_phr_lst = $this->value_phrases($phr, $phr_lst, $walk, $msg);
        $val_lst = new value_list($usr);
        $val_lst->load_by_phr_lst($val_phr_lst, $msg, true);
        $val_lst->filter_readable_by($usr);
        $this->add_field_values($val_lst, $phr_lst, $kwd_ids, $msg);
        $this->debug_step('values loaded for ' . $val_phr_lst->count() . ' phrases', $val_lst->count());
        // the data is collected by the phrase ids and loaded once per step
        $ids = array_merge($phr_lst->id_lst(), $this->value_phrase_ids($val_lst, $msg));
        $phr_lst = $this->complete_phrases($ids, $msg);
        $frm_lst = $phr_lst->frm_lst($msg);
        $frm_lst->filter_readable_by($usr);
        $this->debug_step('formulas loaded', $frm_lst->count());
        $frm_ids = array_merge($phr_lst->id_lst(), $this->formula_phrase_ids($frm_lst));
        $phr_lst = $this->complete_phrases($frm_ids, $msg);
        // the naming triples after the sides, because a side e.g. PV has its own label and acronym
        $nam_ids = array_merge($phr_lst->id_lst(), $this->naming_triple_ids($phr_lst, $kwd_ids, $msg));
        $phr_lst = $this->complete_phrases($nam_ids, $msg);
        $this->report_missing_value_phrases($val_lst, $phr_lst, $msg);
        $trp_lst = $this->full_triples($phr_lst, $msg);
        $trp_lst->filter_readable_by($usr);
        $this->debug_step('phrases completed, full triples', $trp_lst->count());

        $vars = $this->header($phr, $msg);
        $vars[json_fields::WORDS] = $this->with_ids($phr_lst->words()->lst(), $msg);
        $this->debug_step('words exported', count($vars[json_fields::WORDS]));
        $vars[json_fields::TRIPLES] = $this->with_ids($trp_lst->lst(), $msg);
        $this->debug_step('triples exported', count($vars[json_fields::TRIPLES]));
        $vars[json_fields::FORMULAS] = $this->with_ids($frm_lst->lst(), $msg);
        $this->debug_step('formulas exported', count($vars[json_fields::FORMULAS]));
        $vars[json_fields::SOURCES] = $this->with_ids($val_lst->source_lst($msg), $msg);
        $this->debug_step('sources exported', count($vars[json_fields::SOURCES]));
        $vars[json_fields::VALUES] = $this->with_ids($val_lst->lst(), $msg);
        $this->debug_step('values exported', count($vars[json_fields::VALUES]));
        if ($incl_res) {
            $res_lst = new result_list($usr);
            $res_lst->load_by_phrase_list($val_phr_lst, $msg, true);
            $res_lst->filter_readable_by($usr);
            $vars[json_fields::RESULTS] = $this->with_ids($res_lst->lst(), $msg);
            $this->debug_step('results exported', count($vars[json_fields::RESULTS]));
        }
        return $vars;
    }


    /*
     * walk
     */

    /**
     * @param phrase $phr the requested phrase
     * @param phrase_data_walks $walk VALUE for the values of the phrase, FROM for the triples on its from side
     * @param array $kwd_ids the phrase ids of the keywords by their code id
     * @param user_message $msg with the requesting user
     * @return phrase_list the phrase alone for the value walk or the walked phrases for the from walk
     */
    private function start_phrases(phrase $phr, phrase_data_walks $walk, array $kwd_ids, user_message $msg): phrase_list
    {
        $result = new phrase_list($msg->usr);
        if ($walk == phrase_data_walks::FROM) {
            $result = $this->walked_phrases($phr, $kwd_ids, $msg);
        } else {
            $result->add($phr, false, $msg);
        }
        return $result;
    }

    /**
     * @param phrase $phr the requested phrase
     * @param phrase_list $phr_lst the start phrases of the walk
     * @param phrase_data_walks $walk VALUE for the values of the phrase, FROM for the triples on its from side
     * @param user_message $msg with the requesting user
     * @return phrase_list the phrases whose values and results are part of the data: the phrase for the
     *                     value walk, the walked triples and the phrase for the from walk, because a walked
     *                     word e.g. kWh has the values of every use case
     */
    private function value_phrases(phrase $phr, phrase_list $phr_lst, phrase_data_walks $walk, user_message $msg): phrase_list
    {
        $result = new phrase_list($msg->usr);
        if ($walk == phrase_data_walks::FROM) {
            $result = $this->triple_phrases($phr_lst);
        }
        // a word as the requested phrase has its own values too
        if (!in_array($phr->id(), $result->id_lst())) {
            $result->add($phr, false, $msg);
        }
        return $result;
    }

    /**
     * the keywords are loaded once, because a triple of a list load names its sides by id and name
     * only, so the rules compare the phrase ids; they are selected by their name like the frontend
     * calculator does, because the save of a triple does not yet write its code id
     * (docs/llm/pending_prio_2.md "code id of a triple"), and mapped to their code id
     *
     * @param user_message $msg with the requesting user
     * @return array the phrase ids of the calculator and the field keywords by their code id e.g.
     *               system_calculator_label => -2727; these phrases are never walked
     */
    private function keyword_ids(user_message $msg): array
    {
        $trp_lst = new triple_list($msg->usr);
        $trp_lst->load_by_names(array_keys(triples::CALCULATOR_KEYWORDS), $msg);
        $result = [];
        foreach ($trp_lst->lst() as $kwd) {
            $code_id = triples::CALCULATOR_KEYWORDS[$kwd->name()] ?? '';
            if ($code_id != '') {
                $result[$code_id] = $kwd->phrase()->id();
            }
        }
        return $result;
    }

    /**
     * walk from the phrase and the phrases it is built from over the calculator triples on the
     * from side, one database read per step, until no new phrase is found; the phrase of a list
     * item is walked for its general triples (rule 6), but what it has a e.g. the option east of
     * yield factor is a leaf that is added without its triples, so the walk stays within the
     * calculator and never reaches e.g. coal electricity from electricity
     *
     * @param phrase $phr the requested phrase
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param user_message $msg with the requesting user
     * @return phrase_list the walked phrases and the leaves
     */
    private function walked_phrases(phrase $phr, array $kwd_ids, user_message $msg): phrase_list
    {
        $found_lst = new phrase_list($msg->usr);
        $itm_lst = new phrase_list($msg->usr);
        $leaf_lst = new phrase_list($msg->usr);
        $next = $this->built_from($phr, $msg);
        $loops = 0;
        while (!$next->is_empty() and $loops < def::MAX_LOOP) {
            $found_lst->merge($next);
            $next = $this->next_phrases($next, $found_lst, $itm_lst, $leaf_lst, $kwd_ids, $msg);
            $loops++;
            $this->debug_step('walk step ' . $loops . ' of ' . $found_lst->count() . ' walked, next', $next->count());
        }
        $this->report_loop_limit(!$next->is_empty(), 'walk', $msg);
        $found_lst->merge($leaf_lst);
        return $found_lst;
    }

    /**
     * @param phrase $phr the requested phrase e.g. example PV installation
     * @param user_message $msg with the requesting user
     * @return phrase_list the phrase and the phrases it is built from e.g. PV installation and installation
     */
    private function built_from(phrase $phr, user_message $msg): phrase_list
    {
        $result = new phrase_list($msg->usr);
        $result->add($phr, false, $msg);
        $from_id = $this->from_id($phr, $msg);
        $loops = 0;
        while ($this->is_new_from($from_id, $result) and $loops < def::MAX_LOOP) {
            $from = new phrase($msg->usr);
            $from->load_by_id($from_id, $msg);
            $result->add($from, false, $msg);
            $from_id = $this->from_id($from, $msg);
            $loops++;
        }
        $this->report_loop_limit($this->is_new_from($from_id, $result), 'built from chain', $msg);
        return $result;
    }

    /**
     * @param int $from_id the phrase id of the from side of the last phrase of the chain or zero for a word
     * @param phrase_list $chain the phrases of the chain so far
     * @return bool true if the from side is a phrase that the chain does not yet contain
     */
    private function is_new_from(int $from_id, phrase_list $chain): bool
    {
        return ($from_id != 0 and !$chain->has_id($from_id));
    }

    /**
     * the triple is read again, because a phrase load maps a triple without its from and to
     * (see phrase::row_mapper_sandbox)
     *
     * @param phrase $phr a word or a triple
     * @param user_message $msg with the requesting user
     * @return int the phrase id of the from side of a triple or zero for a word
     */
    private function from_id(phrase $phr, user_message $msg): int
    {
        $result = 0;
        if ($phr->is_triple()) {
            $trp = new triple($msg->usr);
            $trp->load_by_id($phr->obj_id(), $msg);
            $result = $trp->from_id();
        }
        return $result;
    }

    /**
     * @param phrase_list $step the phrases walked in the last step
     * @param phrase_list $walked all phrases walked so far
     * @param phrase_list $itm_lst the phrases of the list items so far, extended by this step
     * @param phrase_list $leaf_lst the leaves so far, extended by this step
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param user_message $msg with the requesting user
     * @return phrase_list the phrases to walk in the next step
     */
    private function next_phrases(
        phrase_list  $step,
        phrase_list  $walked,
        phrase_list  $itm_lst,
        phrase_list  $leaf_lst,
        array        $kwd_ids,
        user_message $msg
    ): phrase_list
    {
        $next = new phrase_list($msg->usr);
        // the field phrases of the phrase selectors, whose options are added
        $src_phr_lst = new phrase_list($msg->usr);
        $trp_lst = new triple_list($msg->usr);
        $trp_lst->load_by_phr_lst($step, $msg, null, foaf_direction::UP);
        foreach ($trp_lst->lst() as $trp) {
            if ($this->is_calculator_link($trp)) {
                $this->add_new($next, $walked, $kwd_ids, $trp->phrase(), $msg);
            }
            if ($this->is_item_link($trp, $kwd_ids)) {
                $this->add_item($next, $walked, $itm_lst, $leaf_lst, $kwd_ids, $trp, $msg);
            }
            if ($this->is_selector_field($trp, $kwd_ids)) {
                $this->add_new($src_phr_lst, new phrase_list($msg->usr), [], $trp->get_from(), $msg);
            }
        }
        $this->add_selector_options($next, $walked, $kwd_ids, $src_phr_lst, $msg);
        return $next;
    }

    /**
     * @param triple $trp a triple whose from side has been walked
     * @return bool true if the triple is part of a calculator e.g. a list item, its order or its field type,
     *              false for a general triple e.g. coal electricity = electricity of coal
     */
    private function is_calculator_link(triple $trp): bool
    {
        return in_array($trp->get_verb_code_id(), verbs::CALCULATOR_VERBS);
    }

    /**
     * add the to side of a list item triple: to the next step and the item phrases if the triple is
     * an item of the calculator e.g. PV capacity of the inputs list, as a leaf if it is what the
     * phrase of an item has a e.g. the option east of yield factor or the input year 2018
     *
     * @param phrase_list $next the phrases to walk in the next step
     * @param phrase_list $walked all phrases walked so far
     * @param phrase_list $itm_lst the phrases of the list items, whose to sides are leaves
     * @param phrase_list $leaf_lst the phrases that are part of the data but not walked
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param triple $trp the list item triple e.g. yield factor option east
     * @param user_message $msg with the requesting user
     * @return void
     */
    private function add_item(
        phrase_list  $next,
        phrase_list  $walked,
        phrase_list  $itm_lst,
        phrase_list  $leaf_lst,
        array        $kwd_ids,
        triple       $trp,
        user_message $msg
    ): void
    {
        if ($itm_lst->has_id($trp->from_id())) {
            $this->add_new($leaf_lst, $walked, $kwd_ids, $trp->get_to(), $msg);
        } else {
            $this->add_new($next, $walked, $kwd_ids, $trp->get_to(), $msg);
            $this->add_new($itm_lst, $walked, $kwd_ids, $trp->get_to(), $msg);
        }
    }

    /**
     * @param triple $trp a triple whose from side has been walked
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @return bool true if the to side is an item of a list e.g. PV capacity of the inputs list
     */
    private function is_item_link(triple $trp, array $kwd_ids): bool
    {
        return ($trp->get_verb_code_id() == verbs::HAS and !in_array($trp->to_id(), $kwd_ids));
    }

    /**
     * @param triple $trp a triple whose from side has been walked
     * @param array $kwd_ids the phrase ids of the keywords by their code id
     * @return bool true if the from side is a phrase selector field e.g. replacement mix can be phrase selector
     */
    private function is_selector_field(triple $trp, array $kwd_ids): bool
    {
        $selector_id = $kwd_ids[triples::FIELD_PHRASE_SELECTOR_TYPE_CODE_ID] ?? 0;
        return ($selector_id != 0 and $trp->to_id() == $selector_id);
    }

    /**
     * add the options of the phrase selector fields, which are the phrases that "is a" field phrase
     * e.g. residual mix and production mix for the replacement mix
     *
     * @param phrase_list $next the phrases to walk in the next step
     * @param phrase_list $walked all phrases walked so far
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param phrase_list $selector_lst the field phrases of the phrase selectors
     * @param user_message $msg with the requesting user
     * @return void
     */
    private function add_selector_options(
        phrase_list  $next,
        phrase_list  $walked,
        array        $kwd_ids,
        phrase_list  $selector_lst,
        user_message $msg
    ): void
    {
        global $sys;
        if (!$selector_lst->is_empty()) {
            $rel_trp_lst = new triple_list($msg->usr);
            $rel_trp_lst->load_by_phr_lst($selector_lst, $msg, $sys->verb(verbs::IS), foaf_direction::DOWN);
            foreach ($rel_trp_lst->lst() as $opt) {
                $this->add_new($next, $walked, $kwd_ids, $opt->phrase(), $msg);
                $this->add_new($next, $walked, $kwd_ids, $opt->get_from(), $msg);
            }
        }
    }

    /**
     * @param phrase_list $next the phrases to walk in the next step
     * @param phrase_list $walked all phrases walked so far
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param phrase|sandbox_named|combine_named|null $phr the phrase that has been found e.g. the to side of a triple
     * @param user_message $msg to report a problem of the list
     * @return void
     */
    private function add_new(
        phrase_list                             $next,
        phrase_list                             $walked,
        array                                   $kwd_ids,
        phrase|sandbox_named|combine_named|null $phr,
        user_message                            $msg
    ): void
    {
        if ($phr instanceof phrase) {
            if ($this->is_new($phr, $kwd_ids, $walked) and !$next->has_id($phr->id())) {
                $next->add($phr, false, $msg);
            }
        } elseif ($phr != null) {
            // a side of a loaded triple is a phrase, because only a phrase has the signed phrase id
            log_err_msg('the side ' . $phr->dsp_id() . ' of a triple is not a phrase', $msg);
        }
    }

    /**
     * @param phrase $phr the phrase that has been found e.g. the to side of a triple
     * @param array $kwd_ids the phrase ids of the keywords that are never walked
     * @param phrase_list $walked all phrases walked so far
     * @return bool true if the phrase has an id, is no keyword and has not been walked yet
     */
    private function is_new(phrase $phr, array $kwd_ids, phrase_list $walked): bool
    {
        $id = $phr->id();
        return ($id != 0 and !in_array($id, $kwd_ids) and !$walked->has_id($id));
    }


    /*
     * add
     */

    /**
     * @param phrase_list $phr_lst the walked phrases
     * @return phrase_list the walked triples, whose values and results are part of the data
     */
    private function triple_phrases(phrase_list $phr_lst): phrase_list
    {
        $result = new phrase_list($phr_lst->get_user());
        foreach ($phr_lst->lst() as $phr) {
            if ($phr->is_triple()) {
                $result->add($phr);
            }
        }
        return $result;
    }

    /**
     * add the limits and steps of the fields of the phrases e.g. (lifetime, field step) = 1, because the
     * values are loaded only for the walked triples, and a walked word like lifetime has the values of
     * every use case; a field value is added only if all its phrases are part of the data
     *
     * @param value_list $val_lst the values of the data, extended by the field values
     * @param phrase_list $phr_lst the start phrases of the walk e.g. with the input and assumption phrases
     * @param array $kwd_ids the phrase ids of the keywords by their code id
     * @param user_message $msg with the requesting user
     * @return void
     */
    private function add_field_values(value_list $val_lst, phrase_list $phr_lst, array $kwd_ids, user_message $msg): void
    {
        $fld_ids = array_values(array_intersect_key($kwd_ids, array_flip(triples::FIELD_VALUE_PROPERTIES)));
        $fld_val_lst = new value_list($msg->usr);
        // without the field keywords e.g. in an empty database there is no field value
        if ($fld_ids != []) {
            $fld_val_lst->load_by_phr_lst($this->phrases_by_ids($fld_ids, $msg), $msg, true);
        }
        $fld_val_lst->filter_readable_by($msg->usr);
        $ids = array_merge($phr_lst->id_lst(), $fld_ids);
        foreach ($fld_val_lst->lst() as $val) {
            $val->load_phrases($msg);
            if ($this->is_within($val->phrase_list()->id_lst(), $ids)) {
                $val_lst->add($val);
            }
        }
        $this->debug_step('field values checked', $fld_val_lst->count());
    }

    /**
     * @param array $val_ids the phrase ids of a value
     * @param array $ids the phrase ids of the data
     * @return bool true if every phrase of the value is part of the data
     */
    private function is_within(array $val_ids, array $ids): bool
    {
        return array_diff($val_ids, $ids) == [];
    }

    /**
     * @param value_list $val_lst the values of the data
     * @param user_message $msg with the requesting user
     * @return array the phrase ids of all values e.g. the months of the monthly values
     */
    private function value_phrase_ids(value_list $val_lst, user_message $msg): array
    {
        $result = [];
        foreach ($val_lst->lst() as $val) {
            // a value of a list load knows its group id, the phrases come with the group
            $val->load_phrases($msg);
            $result = array_merge($result, $val->phrase_list()->id_lst());
        }
        return $result;
    }

    /**
     * @param formula_list $frm_lst the formulas of the data
     * @return array the phrase ids that the formulas use e.g. of PV capacity and PV yield
     */
    private function formula_phrase_ids(formula_list $frm_lst): array
    {
        $result = [];
        foreach ($frm_lst->lst() as $frm) {
            // the ids are read from the reference text e.g. {w12}={t5}*{w13} without a database read
            $exp = new expression($frm);
            $result = array_merge($result, $exp->phr_id_lst($frm->ref_text ?? '')->lst);
        }
        return $result;
    }

    /**
     * @param phrase_list $phr_lst the phrases of the data
     * @param array $kwd_ids the phrase ids of the keywords by their code id
     * @param user_message $msg with the requesting user
     * @return array the phrase ids of the label, hint and flag triples of the phrases e.g. PV label and
     *               of their symbols, acronyms and aliases e.g. kWh/a is symbol for kilowatthours per year
     */
    private function naming_triple_ids(phrase_list $phr_lst, array $kwd_ids, user_message $msg): array
    {
        global $sys;
        $result = [];
        $prop_ids = array_values(array_intersect_key($kwd_ids, array_flip(triples::CALCULATOR_PROPERTIES)));
        $trp_lst = new triple_list($msg->usr);
        $trp_lst->load_by_phr_lst($phr_lst, $msg, null, foaf_direction::UP);
        $this->debug_step('from side triples checked for labels', $trp_lst->count());
        foreach ($trp_lst->lst() as $trp) {
            if (in_array($trp->to_id(), $prop_ids)) {
                $result[] = $trp->phrase()->id();
            }
        }
        foreach (verbs::NAME_VERBS as $vrb_code_id) {
            $lnk_lst = new triple_list($msg->usr);
            $lnk_lst->load_by_phr_lst($phr_lst, $msg, $sys->verb($vrb_code_id), foaf_direction::BOTH);
            foreach ($lnk_lst->lst() as $trp) {
                $result[] = $trp->phrase()->id();
            }
        }
        return $result;
    }

    /**
     * reload the phrases fully and add the phrases that a triple is built from, until a step adds
     * no phrase, so that every triple of the data names only phrases that are part of the data;
     * each step checks only the triples of the phrases added by the step before, so a phrase that
     * cannot be loaded e.g. because it is excluded is asked for once and reported
     *
     * @param array $ids the phrase ids of the data, which may contain an id more than once
     * @param user_message $msg with the requesting user
     * @return phrase_list the readable phrases with all their fields and the sides of their triples
     */
    private function complete_phrases(array $ids, user_message $msg): phrase_list
    {
        $all_ids = array_values(array_unique($ids));
        $load_lst = $this->phrases_by_ids($all_ids, $msg);
        $not_loaded = array_diff($all_ids, $load_lst->id_lst());
        $this->debug_step('phrases reloaded', $load_lst->count());
        $loops = 0;
        while (!$load_lst->is_empty() and $loops < def::MAX_LOOP) {
            $missing = $this->missing_sides($load_lst, $all_ids, $msg);
            $load_lst = $this->phrases_by_ids($missing, $msg);
            $not_loaded = array_merge($not_loaded, array_diff($missing, $load_lst->id_lst()));
            $all_ids = array_merge($all_ids, $load_lst->id_lst());
            $loops++;
            $this->debug_step('side step ' . $loops . ' of ' . count($all_ids) . ' phrases, added', $load_lst->count());
        }
        $this->report_loop_limit(!$load_lst->is_empty(), 'side completion', $msg);
        $this->report_not_loaded($not_loaded, $msg);
        $result = $this->phrases_by_ids($all_ids, $msg);
        $result->filter_readable_by($msg->usr);
        return $result;
    }

    /**
     * @param array $ids the phrase ids that should be loaded
     * @param user_message $msg with the requesting user
     * @return phrase_list the phrases of the ids that can be loaded, an empty list for no id
     */
    private function phrases_by_ids(array $ids, user_message $msg): phrase_list
    {
        $result = new phrase_list($msg->usr);
        if (count($ids) > 0) {
            $result->load_by_ids(new phr_ids(array_values($ids)), $msg);
        }
        return $result;
    }

    /**
     * @param value_list $val_lst the values of the data
     * @param phrase_list $phr_lst the phrases of the data
     * @return array the names of the value phrases that are not part of the data by their phrase id
     */
    private function missing_value_phrases(value_list $val_lst, phrase_list $phr_lst): array
    {
        $ids = $phr_lst->id_lst();
        $result = [];
        foreach ($val_lst->lst() as $val) {
            foreach ($val->phrase_list()->lst() as $phr) {
                if (!in_array($phr->id(), $ids)) {
                    $result[$phr->id()] = $phr->name();
                }
            }
        }
        return $result;
    }

    /**
     * every phrase of a value of the data is part of the data, so that a client can show the value;
     * a missing phrase is an internal inconsistency, which is reported to the admin
     *
     * @param value_list $val_lst the values of the data
     * @param phrase_list $phr_lst the completed phrases of the data
     * @param user_message $msg to add the internal warning with the link to the log entry
     * @return void
     */
    private function report_missing_value_phrases(value_list $val_lst, phrase_list $phr_lst, user_message $msg): void
    {
        $missing = $this->missing_value_phrases($val_lst, $phr_lst);
        $this->debug_step('value phrases not in the data ' . implode(',', array_keys($missing))
            . ' (' . implode(',', $missing) . ')', count($missing));
        if (count($missing) > 0) {
            log_warning_msg('the phrase data misses the value phrases ' . implode(',', array_keys($missing))
                . ' (' . implode(',', $missing) . ')', $msg);
        }
    }

    /**
     * the data names a phrase that the phrase view does not return e.g. an excluded word that a
     * triple of the data is still built from; the answer is complete without it, so it is only
     * reported to the admin with the ids, which the user cannot fix
     *
     * @param array $ids the phrase ids that have been requested but not loaded
     * @param user_message $msg to add the internal warning with the link to the log entry
     * @return void
     */
    private function report_not_loaded(array $ids, user_message $msg): void
    {
        $this->debug_step('phrases not loaded ' . implode(',', $ids), count($ids));
        if (count($ids) > 0) {
            log_warning_msg('the phrase data names the phrases ' . implode(',', $ids)
                . ', which cannot be loaded e.g. because they are excluded', $msg);
        }
    }

    /**
     * a loop that still finds phrases after def::MAX_LOOP steps is stopped, so the data is
     * incomplete; the user cannot fix a phrase tree that deep, so it is reported to the admin
     *
     * @param bool $incomplete true if the loop has been stopped by the limit and not by its end
     * @param string $loop the name of the loop for the admin e.g. walk
     * @param user_message $msg to add the internal warning with the link to the log entry
     * @return void
     */
    private function report_loop_limit(bool $incomplete, string $loop, user_message $msg): void
    {
        if ($incomplete) {
            log_warning_msg('the phrase data ' . $loop . ' has been stopped after '
                . def::MAX_LOOP . ' steps, so the data is incomplete', $msg);
        }
    }

    /**
     * the triples are read by the triple list, because a phrase load maps a triple without its
     * from and to (see phrase::row_mapper_sandbox)
     *
     * @param phrase_list $phr_lst the phrases of the data
     * @param user_message $msg with the requesting user
     * @return triple_list the triples of the phrases with their from, verb and to
     */
    private function full_triples(phrase_list $phr_lst, user_message $msg): triple_list
    {
        $result = new triple_list($msg->usr);
        $trp_ids = $phr_lst->trp_ids();
        if (count($trp_ids) > 0) {
            $result->load_by_ids(array_values($trp_ids), $msg);
        }
        return $result;
    }

    /**
     * @param phrase_list $check_lst the phrases whose triples are checked e.g. the phrases added by the last step
     * @param array $ids the phrase ids of all phrases of the data
     * @param user_message $msg with the requesting user
     * @return array the ids of the phrases that a triple of the check list is built from but that are not in the data
     */
    private function missing_sides(phrase_list $check_lst, array $ids, user_message $msg): array
    {
        $result = [];
        foreach ($this->full_triples($check_lst, $msg)->lst() as $trp) {
            foreach ([$trp->from_id(), $trp->to_id()] as $side_id) {
                if ($this->is_missing_side($side_id, $ids, $result)) {
                    $result[] = $side_id;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $side_id the phrase id of the from or to side of a triple or zero if the side is not set
     * @param array $ids the phrase ids of all phrases of the data
     * @param array $found the missing side ids found so far
     * @return bool true if the side is a phrase that is neither in the data nor found yet
     */
    private function is_missing_side(int $side_id, array $ids, array $found): bool
    {
        return ($side_id != 0 and !in_array($side_id, $ids) and !in_array($side_id, $found));
    }


    /*
     * internal
     */

    /**
     * @param phrase $phr the requested phrase
     * @param user_message $msg with the requesting user
     * @return array the header of the phrase data with the requested phrase as the selection
     */
    private function header(phrase $phr, user_message $msg): array
    {
        global $db_con;
        $lib = new library();
        $api_msg = new api_message();
        $time = new DateTime();
        return [
            json_fields::POD => $api_msg->api_site_name($db_con),
            json_fields::TYPE_NAME => $lib->class_to_api_route(self::class),
            json_fields::USER_ID => $msg->usr->id(),
            json_fields::USER => $msg->usr->name(),
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::TIME => $time->format(self::TIME_FORMAT),
            json_fields::SELECTION => $this->with_ids([$phr->obj()], $msg),
        ];
    }

    /**
     * show a step of the phrase data if shared_def::DEBUG_PHRASE_DATA is switched on; log_debug adds the
     * seconds since the last message, so the slow step is the line with the most seconds
     *
     * @param string $step what has been done e.g. "values loaded"
     * @param int $count the number of objects of the step
     * @return void
     */
    private function debug_step(string $step, int $count): void
    {
        if (shared_def::DEBUG_PHRASE_DATA) {
            log_debug('phrase data ' . $step . ': ' . $count, url_var::DEBUG_LEVEL_SWITCHED);
        }
    }

    /**
     * sorted by the id, because the database returns the rows in no fixed order, so that the same
     * data gives the same answer e.g. the same numbering of the sources in the calculator editor
     *
     * @param array $lst the words, triples, formulas, sources, values or results of the data
     * @param user_message $msg to collect the export problems
     * @return array the export json of each object with its database id as the first field
     */
    private function with_ids(array $lst, user_message $msg): array
    {
        usort($lst, fn($a, $b) => $this->compare_ids($a->id(), $b->id()));
        $result = [];
        foreach ($lst as $obj) {
            $result[] = [json_fields::ID => $obj->id()] + $obj->export_json($msg);
        }
        return $result;
    }

    /**
     * a total order of the ids: the integer ids first by their number, then the text keys of the
     * values and results e.g. "fQ-ex-/u+2V+" by their text, because a mixed comparison of a number
     * and a text is not transitive
     *
     * @param int|string $id_a the id of the first object
     * @param int|string $id_b the id of the second object
     * @return int below zero if the first id comes first, zero if both are the same, else above zero
     */
    private function compare_ids(int|string $id_a, int|string $id_b): int
    {
        $result = is_string($id_a) <=> is_string($id_b);
        if ($result == 0 and is_string($id_a)) {
            $result = strcmp($id_a, $id_b);
        } elseif ($result == 0) {
            $result = $id_a <=> $id_b;
        }
        return $result;
    }

}
