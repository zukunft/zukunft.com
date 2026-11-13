<?php

/*

    cfg/import/import_wikidata_preview.php - what the wikidata import of a phrase would add to this pod
    --------------------------------------

    the refresh icon of the wikidata reference of a phrase asks the backend for the values of the phrase
    in wikidata: this class reads the entity json, builds the two maps of the converter from the database
    (the phrases of the pod with a wikidata reference and the listed currency pairs), converts the claims
    of the properties that the pod knows and compares each value with the database, so that the user sees
    the new words, triples and values, the values that are already in line and the conflicts before the
    confirm writes the import json as the user with the source wikidata

    a conflict is a value of the pod with another number: if the number is not yet a value of the user,
    the import writes the number of wikidata as the user value; if the user has already an own value,
    the import adds the number of wikidata as a value with the additional word "wikidata", so that the
    value of the user is never overwritten without the user

    TODO Prio 2 if the label reads of the units and criteria make the page wait longer than
         import_wikidata::READ_TIMEOUT, the read becomes a job (see docs/llm/pending_prio_2.md)


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

namespace Zukunft\ZukunftCom\main\php\cfg\import;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class import_wikidata_preview
{

    // the seed triple of the currency codes e.g. "USD is a ISO 4217 code", so that a phrase whose symbol is such
    // a code is a currency, whose price in another currency is a value of a listed currency pair
    const string ISO_4217_CODE = 'ISO 4217 code';
    // the word that keeps the number of wikidata apart from the own value of the user with another number,
    // named like the source, because it says where the number comes from
    const string CONFLICT_WORD = sources::WIKIDATA;
    // a wikidata item id e.g. "Q72", the only key that is read from wikidata
    const string WIKIDATA_ITEM_PATTERN = '/^Q\d+$/';

    // the user who sees the preview and who would write the import
    private user $usr;
    // the converter with the reader of wikidata, which an offline test replaces by its fixture folder
    private import_wikidata $imp;

    /**
     * @param user $usr the user who sees the preview and who would write the import
     * @param import_wikidata $imp the converter that reads wikidata, or for a test the one with the fixture folder
     */
    function __construct(user $usr, import_wikidata $imp = new import_wikidata())
    {
        $this->usr = $usr;
        $this->imp = $imp;
    }


    /*
     * preview
     */

    /**
     * the preview of the wikidata import of a phrase by its wikidata reference e.g. the values of Q72 for the
     * triple "Zurich (city)"
     *
     * @param int $phr_id the id of the phrase e.g. of "Zurich (city)" (negative for a triple)
     * @param user_message $msg to report why nothing can be imported e.g. because the phrase has no wikidata reference
     * @return array the preview (see compared) or an empty array if nothing can be imported, which is reported
     */
    function preview_of_phrase(int $phr_id, user_message $msg): array
    {
        $result = [];
        $phr = new phrase($this->usr);
        $phr->load_by_id($phr_id, $msg);
        $ref_lst = new ref_list($this->usr);
        $ref_lst->load_by_phr_id($phr_id, $msg);
        $key = $ref_lst->get_by_type_code_id(ref_types::WIKIDATA)?->get_external_key() ?? '';
        if ($key == '') {
            $msg->add(msg_id::WIKIDATA_REF_MISSING, [msg_id::VAR_NAME => $phr->name()]);
        } else {
            $result = $this->preview_of_key($key, $phr->name() ?? $key, $msg);
        }
        return $result;
    }

    /**
     * the preview of the wikidata import of an entity e.g. the values of Q72, which is also the base of the admin
     * view that imports by a wikidata id
     *
     * @param string $wikidata_id the wikidata id of the entity e.g. "Q72"
     * @param string $name the name of the entity for the messages e.g. "Zurich (city)"
     * @param user_message $msg to report why nothing can be imported e.g. because wikidata cannot be read
     * @return array the preview (see compared) or an empty array if nothing can be imported, which is reported
     */
    function preview_of_key(string $wikidata_id, string $name, user_message $msg): array
    {
        $result = [];
        $vars = [msg_id::VAR_NAME => $name, msg_id::VAR_ID => $wikidata_id];
        // the id is part of the url that is read, so only an item id of wikidata is read
        $entity_json = '';
        if (preg_match(self::WIKIDATA_ITEM_PATTERN, $wikidata_id) === 1) {
            $entity_json = $this->imp->get_entity_json($wikidata_id);
        }
        if ($entity_json == '') {
            $msg->add(msg_id::WIKIDATA_READ_FAILED, $vars);
        } else {
            // the problems of single claims are part of the preview, because the other values can still be imported
            $claim_msg = new user_message($this->usr);
            $import = $this->import_of_entity($entity_json, $name, $msg, $claim_msg);
            if ($import != []) {
                $result = $this->compared($import, $name, $msg, $claim_msg);
                $result[json_fields::EXTERNAL_KEY] = $wikidata_id;
            }
        }
        return $result;
    }

    /**
     * the import json of the claims of all properties of the entity that the pod knows, so that not the hundreds of
     * statements of e.g. Q72 are offered and no property label has to be read
     *
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q72.json
     * @param string $name the name of the entity for the messages e.g. "Zurich (city)"
     * @param user_message $msg to report that the pod knows none of the properties of the entity
     * @param user_message $claim_msg to collect the claims that are not imported e.g. because they have no unit name
     * @return array the zukunft.com import json as an array or an empty array if no property is known, which is reported
     */
    function import_of_entity(string $entity_json, string $name, user_message $msg, user_message $claim_msg): array
    {
        $result = [];
        $prp_phrases = $this->pod_phrases(ref_types::WIKIDATA_PROPERTY, $this->imp->property_ids($entity_json), $msg);
        if ($prp_phrases == []) {
            $vars = [msg_id::VAR_NAME => $name, msg_id::VAR_ID => $this->imp->entity_id($entity_json)];
            $msg->add(msg_id::WIKIDATA_PROPERTY_UNKNOWN, $vars);
        } else {
            $prp_ids = array_keys($prp_phrases);
            $item_ids = $this->imp->item_ids($entity_json, $prp_ids);
            $item_phrases = $this->pod_phrases(ref_types::WIKIDATA, $item_ids, $msg);
            $pod_phrases = $this->declarations($prp_phrases + $item_phrases, $msg);
            $currency_pairs = $this->currency_pairs($item_phrases, $msg);
            $imports = [];
            foreach ($prp_ids as $prp_id) {
                $prp_json = import_wikidata::property_json($prp_id);
                $imports[] = $this->imp->convert($entity_json, $prp_json, $pod_phrases, $currency_pairs, $claim_msg);
            }
            $result = $this->merged($imports);
        }
        return $result;
    }


    /*
     * the maps of the converter
     */

    /**
     * @param string $type_code_id the code id of the reference type e.g. ref_types::WIKIDATA
     * @param array $keys the wikidata ids e.g. ["Q72", "Q4916"]
     * @param user_message $msg to collect the load warnings for the user
     * @return array the phrases of the pod with these references by the wikidata id, each as a list of phrases,
     *               because one wikidata id can be the reference of more than one phrase, which the converter reports
     */
    private function pod_phrases(string $type_code_id, array $keys, user_message $msg): array
    {
        global $sys;
        $result = [];
        $ref_lst = new ref_list($this->usr);
        $ref_lst->load_by_type_and_keys($sys->typ_lst->ref_typ->id($type_code_id), $keys, $msg);
        foreach ($ref_lst->lst() as $ref) {
            $phr = new phrase($this->usr);
            if ($phr->load_by_id($ref->phrase_id(), $msg) != 0) {
                $result[$ref->get_external_key()][] = $phr;
            }
        }
        return $result;
    }

    /**
     * @param array $phrases the phrases of the pod by their wikidata id (see pod_phrases)
     * @param user_message $msg to collect the load warnings for the user
     * @return array the import declaration of each phrase by the wikidata id, the pod phrases of the converter
     */
    private function declarations(array $phrases, user_message $msg): array
    {
        $result = [];
        foreach ($phrases as $key => $phr_lst) {
            foreach ($phr_lst as $phr) {
                $result[$key][] = $this->declaration($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * the import declaration of a phrase of the pod: its name and the words and triples that the import json needs
     * to declare it, e.g. for the triple "Zurich (city)" the words "Zurich" and "city" and the triple itself
     *
     * @param phrase $phr a phrase of the pod e.g. the triple "Zurich (city)"
     * @param user_message $msg to collect the load warnings of the parts of a triple
     * @return array the name (json_fields::NAME) and the words and triples (json_fields::WORDS, json_fields::TRIPLES)
     */
    private function declaration(phrase $phr, user_message $msg): array
    {
        // a negative phrase id is a triple, which is loaded with its from, verb and to, because the phrase
        // row of the database view has only the name and the type of the triple
        if ($phr->id() < 0) {
            $trp = new triple($this->usr);
            $trp->load_by_id($phr->id() * -1, $msg);
            $from = $this->loaded_phrase($trp->get_from()?->id() ?? 0, $msg);
            $to = $this->loaded_phrase($trp->get_to()?->id() ?? 0, $msg);
            $from_declaration = $this->declaration($from, $msg);
            $to_declaration = $this->declaration($to, $msg);
            // the parts first, because the import creates the from and the to before the triple
            $result = [
                json_fields::NAME => $phr->name(),
                json_fields::WORDS => array_merge(
                    $from_declaration[json_fields::WORDS],
                    $to_declaration[json_fields::WORDS]),
                json_fields::TRIPLES => array_merge(
                    $from_declaration[json_fields::TRIPLES],
                    $to_declaration[json_fields::TRIPLES],
                    [$this->triple_declaration($trp, $from, $to, $msg)]),
            ];
        } else {
            $result = [
                json_fields::NAME => $phr->name(),
                json_fields::WORDS => [[json_fields::NAME => $phr->name()]],
                json_fields::TRIPLES => [],
            ];
        }
        return $result;
    }

    /**
     * @param int $phr_id the id of the from or the to of a triple (negative for a triple)
     * @param user_message $msg to collect the load warnings for the user
     * @return phrase the phrase with its name and, for a triple, its from, verb and to
     */
    private function loaded_phrase(int $phr_id, user_message $msg): phrase
    {
        $phr = new phrase($this->usr);
        $phr->load_by_id($phr_id, $msg);
        return $phr;
    }

    /**
     * @param triple $trp a triple of the pod e.g. "Zurich (city)" or the currency pair "EUR per USD"
     * @param phrase $from the loaded from of the triple e.g. "Zurich"
     * @param phrase $to the loaded to of the triple e.g. "city"
     * @param user_message $msg to collect the load warning of the phrase type
     * @return array the triple as the import json declares it: from, verb and to, its phrase type if it has one and
     *               only a name that the import does not derive from them, so the derived name is never repeated
     */
    private function triple_declaration(triple $trp, phrase $from, phrase $to, user_message $msg): array
    {
        $result = [];
        // with the loaded from and to, because without their names the generated name would be the given name
        $trp->set_from($from);
        $trp->set_to($to);
        // the name of the database row, because a loaded triple keeps it in triple_name and not as given name
        $name = $trp->name() ?? '';
        if ($name != '' and $name != $trp->generate_name()) {
            $result[json_fields::NAME] = $name;
        }
        $result[json_fields::EX_FROM] = $from->name();
        $result[json_fields::EX_VERB] = $trp->get_verb()?->name() ?? '';
        $result[json_fields::EX_TO] = $to->name();
        $type = $trp->type_code_id($msg) ?? '';
        if ($type != '') {
            $result[json_fields::TYPE_NAME] = $type;
        }
        return $result;
    }

    /**
     * the listed currency pairs of the currencies of the claims (the currencies map of the converter): a phrase is a
     * currency if its symbol is a currency code e.g. "USD is symbol for US dollar" and "USD is a ISO 4217 code", and
     * a pair is the measure unit triple "unit per entity" of the seed between two codes e.g. "EUR per USD"
     *
     * @param array $item_phrases the phrases of the pod by the wikidata id of the entity, the units and the criteria
     * @param user_message $msg to collect the load warnings for the user
     * @return array the currencies by their wikidata id, each with the declaration of its listed pairs by the
     *               wikidata id of the other currency, an empty list for a currency without listed pair (see convert)
     */
    private function currency_pairs(array $item_phrases, user_message $msg): array
    {
        $codes = [];
        foreach ($item_phrases as $key => $phr_lst) {
            // an id of more than one phrase is reported by the converter, so it is no currency of a pair
            if (count($phr_lst) == 1) {
                $code = $this->currency_code($phr_lst[0], $msg);
                if ($code != null) {
                    $codes[$key] = $code;
                }
            }
        }
        $result = [];
        foreach ($codes as $entity_key => $entity_code) {
            $result[$entity_key] = [];
            foreach ($codes as $unit_key => $unit_code) {
                $pair = $this->currency_pair($unit_code, $entity_code, $msg);
                if ($pair != null) {
                    $result[$entity_key][$unit_key] = $this->declaration($pair, $msg);
                }
            }
        }
        return $result;
    }

    /**
     * @param phrase $phr a phrase of the pod e.g. the triple "US dollar"
     * @param user_message $msg to collect the load warnings for the user
     * @return phrase|null the currency code of the phrase e.g. "USD" or null if the phrase is no currency
     */
    private function currency_code(phrase $phr, user_message $msg): ?phrase
    {
        global $sys;
        $result = null;
        $phr_lst = new phrase_list($this->usr);
        $phr_lst->add($phr);
        $symbols = new triple_list($this->usr);
        // down, because the symbol is the from of e.g. "USD is symbol for US dollar"
        $symbols->load_by_phr_lst($phr_lst, $msg, $sys->typ_lst->vrb->get_verb(verbs::SYMBOL), foaf_direction::DOWN);
        foreach ($symbols->lst() as $symbol) {
            $code = $this->loaded_phrase($symbol->get_from()?->id() ?? 0, $msg);
            if ($this->is_currency_code($code, $msg)) {
                $result = $code;
            }
        }
        return $result;
    }

    /**
     * @param phrase $code a symbol of a phrase e.g. the word "USD"
     * @param user_message $msg to collect the load warnings for the user
     * @return bool true if the seed links the symbol with "is a" to the ISO 4217 code e.g. "USD is a ISO 4217 code"
     */
    private function is_currency_code(phrase $code, user_message $msg): bool
    {
        global $sys;
        $iso_code = new phrase($this->usr);
        $iso_code->load_by_name(self::ISO_4217_CODE, $msg);
        $lnk = new triple($this->usr);
        $is_id = $sys->typ_lst->vrb->id(verbs::IS);
        return $code->id() != 0
            and $lnk->load_by_link_id($code->id(), $msg, $is_id, $iso_code->id()) > 0;
    }

    /**
     * @param phrase $unit_code the code of the currency of the price e.g. "EUR"
     * @param phrase $entity_code the code of the currency whose price is given e.g. "USD"
     * @param user_message $msg to collect the load warnings for the user
     * @return phrase|null the listed measure unit triple "unit per entity" e.g. "EUR per USD" or null if it is not listed
     */
    private function currency_pair(phrase $unit_code, phrase $entity_code, user_message $msg): ?phrase
    {
        global $sys;
        $result = null;
        $pair = new triple($this->usr);
        $per_id = $sys->typ_lst->vrb->id(verbs::PER);
        if ($unit_code->id() != $entity_code->id()
            and $pair->load_by_link_id($unit_code->id(), $msg, $per_id, $entity_code->id()) > 0
            and $pair->is_measure()) {
            $result = $this->loaded_phrase($pair->phrase()->id(), $msg);
        }
        return $result;
    }


    /*
     * compare with the pod
     */

    /**
     * @param array $imports the import json of each property of the entity (see import_wikidata::convert)
     * @return array one import json with each word and triple only once and the values of all properties
     */
    private function merged(array $imports): array
    {
        $words = [];
        $triples = [];
        $values = [];
        foreach ($imports as $import) {
            foreach ($import[json_fields::WORDS] ?? [] as $word) {
                $words[$word[json_fields::NAME]] = $word;
            }
            foreach ($import[json_fields::TRIPLES] ?? [] as $triple) {
                $triples[$this->triple_name($triple)] = $triple;
            }
            $values = array_merge($values, $import[json_fields::VALUES] ?? []);
        }
        return [
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::DATA_VERSION => def::DATA_VERSION_INIT,
            json_fields::WORDS => array_values($words),
            json_fields::TRIPLES => array_values($triples),
            json_fields::VALUES => $values,
        ];
    }

    /**
     * @param array $triple the import declaration of a triple
     * @return string the name of the triple, which for a triple without name is the name that the import derives
     *                e.g. "Zurich (city)" for the verb "is a" or "EUR per USD"
     */
    private function triple_name(array $triple): string
    {
        return $triple[json_fields::NAME] ?? $this->generated_name($triple);
    }

    /**
     * @param array $triple the import declaration of a triple without name
     * @return string the name that the import gives the triple, by the rule of triple::generate_name
     */
    private function generated_name(array $triple): string
    {
        global $sys;
        $trp = new triple($this->usr);
        $trp->set_from($this->named_phrase((string)$triple[json_fields::EX_FROM]));
        $trp->set_to($this->named_phrase((string)$triple[json_fields::EX_TO]));
        $vrb_name = (string)$triple[json_fields::EX_VERB];
        $vrb = $sys->typ_lst->vrb->get_by_name($vrb_name);
        if ($vrb instanceof verb) {
            $trp->set_verb($vrb);
        } else {
            // the declarations are made from the triples of the pod, so their verb is always known
            log_err('the verb "' . $vrb_name . '" of a triple of the wikidata import preview is unknown');
        }
        return $trp->generate_name();
    }

    /**
     * @param string $name the name of the from or the to of a triple e.g. "Zurich"
     * @return phrase the phrase with only this name, which is all that the generated name of a triple needs
     */
    private function named_phrase(string $name): phrase
    {
        $phr = new phrase($this->usr);
        $phr->set_name($name, word::class);
        return $phr;
    }

    /**
     * the preview of the import json: the new words, triples and values, the values that are already in line and
     * the conflicts, and the import json that the confirm writes, which has only the new values and the conflicts,
     * each with the source wikidata
     *
     * @param array $import the zukunft.com import json of the entity (see import_of_entity)
     * @param string $name the name of the entity e.g. "Zurich (city)"
     * @param user_message $msg to collect the load warnings, never the claims, because a load maps nothing on an earlier error
     * @param user_message $claim_msg the claims that are not imported, which are shown on the preview
     * @return array the preview by the json_fields::PREVIEW_* keys
     */
    private function compared(array $import, string $name, user_message $msg, user_message $claim_msg): array
    {
        $new_values = [];
        $same_values = [];
        $conflicts = [];
        $to_import = [];
        foreach ($import[json_fields::VALUES] ?? [] as $val_json) {
            $old = $this->pod_value($val_json[json_fields::WORDS], $msg);
            if ($old == null) {
                $new_values[] = $val_json;
                $to_import[] = $val_json;
            } elseif ($this->is_same_number($val_json, $old)) {
                $same_values[] = $val_json;
            } else {
                $conflict = $this->conflict($val_json, $old, $msg);
                if ($conflict == []) {
                    $same_values[] = $val_json;
                } else {
                    $conflicts[] = $conflict;
                    $to_import[] = $conflict[json_fields::PREVIEW_IMPORT_VALUE];
                }
            }
        }
        $words = $this->pod_words_by_name($import[json_fields::WORDS] ?? [], $msg, $claim_msg);
        if ($this->uses_conflict_word($to_import)) {
            $words[] = [json_fields::NAME => self::CONFLICT_WORD];
        }
        $import[json_fields::WORDS] = $words;
        $import[json_fields::VALUES] = array_map(fn($val) => $this->with_source($val), $to_import);
        // the import json declares the source of its values like every phrase it uses, by name, because the seed has it
        if ($to_import != []) {
            $import[json_fields::SOURCES] = [[json_fields::NAME => sources::WIKIDATA]];
        }
        return [
            json_fields::NAME => $name,
            json_fields::PREVIEW_WORDS_NEW => $this->new_names(array_column($words, json_fields::NAME), $msg),
            json_fields::PREVIEW_TRIPLES_NEW => $this->new_names(
                array_map(fn($trp) => $this->triple_name($trp), $import[json_fields::TRIPLES] ?? []), $msg),
            json_fields::PREVIEW_VALUES_NEW => $new_values,
            json_fields::PREVIEW_VALUES_SAME => $same_values,
            json_fields::PREVIEW_VALUES_CONFLICT => $conflicts,
            json_fields::PREVIEW_PROBLEMS => $claim_msg->all_message_text(),
            json_fields::PREVIEW_IMPORT => $import,
        ];
    }

    /**
     * a conflict is a value of the pod with another number than wikidata: if the number is not yet a value of the
     * user, the number of wikidata becomes the user value; if the user has an own value, the number of wikidata is
     * added with the word "wikidata", so that the value of the user is never overwritten without the user
     *
     * @param array $val_json the value of the import json e.g. the population of Zurich in 2021
     * @param value $old the value of the pod with the same words and another number
     * @param user_message $msg to collect the load warnings for the user
     * @return array the value with the old number and the value to import (see the json_fields::PREVIEW_* keys),
     *               or an empty array if the number of wikidata is already the value with the word "wikidata"
     */
    private function conflict(array $val_json, value $old, user_message $msg): array
    {
        $result = [];
        $import_value = $val_json;
        $is_own = ($old->owner_id() == $this->usr->id() or $old->has_usr_cfg());
        if ($is_own) {
            $import_value[json_fields::WORDS][] = self::CONFLICT_WORD;
        }
        $old_wikidata = $is_own ? $this->pod_value($import_value[json_fields::WORDS], $msg) : null;
        if ($old_wikidata == null or !$this->is_same_number($val_json, $old_wikidata)) {
            $result = $val_json + [
                    json_fields::PREVIEW_NUMBER_OLD => $old->number(),
                    json_fields::PREVIEW_CONFLICT_WORD => $is_own ? self::CONFLICT_WORD : '',
                    json_fields::PREVIEW_IMPORT_VALUE => $import_value,
                ];
        }
        return $result;
    }

    /**
     * a word that is already a phrase of the pod is declared by its name only, because the import of values never
     * changes a phrase that the preview does not show: e.g. the year word "1950" of the converter has the type time,
     * but the word "1950" of the pod is also a postal code and keeps its type; a new word of the converter is named by
     * its wikidata label, which can be the name of a phrase of the pod with another or no wikidata reference (e.g. a
     * criterion labelled "population"), so the reference that the import would add is reported on the preview
     *
     * @param array $words the words of the import json, the new ones with their wikidata reference
     * @param user_message $msg to collect the load warnings
     * @param user_message $claim_msg to report each label that is already the name of a phrase of the pod on the preview
     * @return array the words, each that the pod has already only with its name
     */
    private function pod_words_by_name(array $words, user_message $msg, user_message $claim_msg): array
    {
        $result = [];
        foreach ($words as $word) {
            $name = (string)$word[json_fields::NAME];
            $key = $word[json_fields::REFS][0][json_fields::NAME] ?? '';
            $phr = new phrase($this->usr);
            if ($phr->load_by_name($name, $msg) != 0) {
                if ($key != '') {
                    $claim_msg->add(msg_id::WIKIDATA_NAME_USED, [msg_id::VAR_NAME => $name, msg_id::VAR_ID => $key]);
                }
                $word = [json_fields::NAME => $name];
            }
            $result[] = $word;
        }
        return $result;
    }

    /**
     * @param array $values the values of the import json that the confirm writes
     * @return bool true if a value keeps the number of wikidata apart from an own value of the user by the word
     *              "wikidata", which the import json must then declare
     */
    private function uses_conflict_word(array $values): bool
    {
        $result = false;
        foreach ($values as $val_json) {
            if (in_array(self::CONFLICT_WORD, $val_json[json_fields::WORDS], true)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param array $names the names of the words of a value of the import json e.g. ["population", "Zurich (city)", "2021"]
     * @param user_message $msg to collect the load warnings for the user
     * @return value|null the value of the pod with exactly these phrases or null if a phrase or the value is new
     */
    private function pod_value(array $names, user_message $msg): ?value
    {
        $result = null;
        $phr_lst = new phrase_list($this->usr);
        $all_found = true;
        foreach ($names as $name) {
            $phr = new phrase($this->usr);
            if ($phr->load_by_name((string)$name, $msg) == 0) {
                $all_found = false;
            } else {
                $phr_lst->add($phr);
            }
        }
        if ($all_found and !$phr_lst->is_empty()) {
            $val = new value($this->usr);
            if ($val->load_by_grp($phr_lst->get_grp_id(), $msg)) {
                $result = $val;
            }
        }
        return $result;
    }

    /**
     * @param array $val_json the value of the import json with the number of wikidata as text e.g. "421878"
     * @param value $val the value of the pod
     * @return bool true if the number of the pod is the number of wikidata
     */
    private function is_same_number(array $val_json, value $val): bool
    {
        return (float)$val_json[json_fields::NUMBER] == (float)$val->number();
    }

    /**
     * @param array $names the names of the words or triples of the import json
     * @param user_message $msg to collect the load warnings for the user
     * @return array the names that are no phrase of the pod yet, so that the import adds them
     */
    private function new_names(array $names, user_message $msg): array
    {
        $result = [];
        foreach ($names as $name) {
            $phr = new phrase($this->usr);
            if ($phr->load_by_name((string)$name, $msg) == 0) {
                $result[] = (string)$name;
            }
        }
        return $result;
    }

    /**
     * @param array $val_json a value of the import json
     * @return array the value with the source wikidata, so that the import writes where the number comes from
     */
    private function with_source(array $val_json): array
    {
        return $val_json + [json_fields::SOURCE_NAME => sources::WIKIDATA];
    }
}
