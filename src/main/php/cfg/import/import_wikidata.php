<?php

/*

    cfg/import/import_wikidata.php - retrieve and capture wikidata entity data
    -----------------------------

    get_entity_json() retrieves the entity data json from wikidata for a given
    wikidata id e.g. "Q167" for Pi and returns the raw json text
    store_json() is a test helper that stores the received json as a fixture in
    the folder of the caller so that the import can be tested offline; the
    converter of an offline test reads the units and criteria only from its
    fixture folder, so the backend never reads a test folder of its own


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
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;

class import_wikidata
{

    // cache of the already read labels of units and criteria by wikidata id so that each is read only once per convert
    private array $item_labels = [];
    // the folder of the wikidata answers of an offline test, empty to read wikidata by http
    private string $fixture_path;

    /**
     * @param string $fixture_path empty for the pod, which reads wikidata by http, or the folder of the wikidata
     *                             answers of an offline test, which never sends a request and reports a missing file
     */
    function __construct(string $fixture_path = '')
    {
        $this->fixture_path = $fixture_path;
    }

    // the base url of the wikidata entity data json api; the wikidata id and the json extension are added
    const string ENTITY_DATA_URL = 'https://www.wikidata.org/wiki/Special:EntityData/';
    const string IMPORT_PREFIX = 'import_';
    const string JSON_EXTENSION = '.json';
    // wikidata answers with a http 403 if no descriptive user agent is sent, so identify zukunft.com
    const string USER_AGENT = 'zukunft.com (https://github.com/zukunft/zukunft.com)';
    // the seconds to wait for wikipedia, so that a slow answer does not block the page of the user
    const int READ_TIMEOUT = 10;
    // the seconds to wait for the entity json of wikidata, which an import reads without a user waiting
    // for the page, so a slow answer of wikidata is waited for like with the php default socket timeout
    const int IMPORT_TIMEOUT = 60;
    // the fields of the php stream context of the http request
    const string HTTP_CONTEXT = 'http';
    const string HTTP_HEADER = 'header';
    const string HTTP_TIMEOUT = 'timeout';
    const string HTTP_USER_AGENT = 'User-Agent: ';

    // the field names of the wikidata json structure used by the converter
    const string WD_ENTITIES = 'entities';
    const string WD_LABELS = 'labels';
    const string WD_LANG_EN = 'en';
    // the wikidata code for the default label that is the same in most languages
    const string WD_LANG_MUL = 'mul';
    const string WD_VALUE = 'value';
    const string WD_ID = 'id';
    const string WD_CLAIMS = 'claims';
    const string WD_MAINSNAK = 'mainsnak';
    const string WD_DATAVALUE = 'datavalue';
    const string WD_AMOUNT = 'amount';
    const string WD_UNIT = 'unit';
    const string WD_QUALIFIERS = 'qualifiers';
    const string WD_TIME = 'time';
    // the wikidata property id of the "point in time" qualifier that gives the date of a value
    const string WD_POINT_IN_TIME = 'P585';
    // the wikidata property id of the "criterion used" qualifier that separates values of the same date
    // e.g. the "resident population" and the "population present" of Zurich
    const string WD_CRITERION_USED = 'P1013';
    // the wikidata property id of the "determination method" qualifier, which says how the number has been found
    // e.g. by a census, but not what it means, so a value with it is imported like one without it
    const string WD_DETERMINATION_METHOD = 'P459';
    // the qualifiers that never change what the number of a claim means; any other e.g. P518 "applies to
    // part" does, so the claim is not imported
    const array WD_KNOWN_QUALIFIERS = [
        self::WD_POINT_IN_TIME,
        self::WD_CRITERION_USED,
        self::WD_DETERMINATION_METHOD,
    ];
    // a claim with a number has the snak type "value", whereas "somevalue" and "novalue" have no number
    const string WD_SNAKTYPE = 'snaktype';
    const string WD_SNAKTYPE_VALUE = 'value';
    // a claim that wikidata marks as wrong or outdated has the rank "deprecated"
    const string WD_RANK = 'rank';
    const string WD_RANK_DEPRECATED = 'deprecated';
    // the precision of a point in time: 9 is a year, 10 a month and 11 a day, a lower one e.g. 8 a decade
    const string WD_PRECISION = 'precision';
    const int WD_PRECISION_YEAR = 9;
    // the sign and all digits of the year of a wikidata time e.g. "+2021-12-31T00:00:00Z" or "-0500-00-00T00:00:00Z"
    const string WD_TIME_YEAR_PATTERN = '/^([+-])(\d+)-\d{2}-\d{2}T/';
    const string WD_TIME_SIGN_NEGATIVE = '-';
    // the keys of the words, triples and value that one wikidata claim adds to the import json
    const string PART_WORD_TYPES = 'word_types';
    const string PART_WORD_REFS = 'word_refs';
    const string PART_VALUE = 'value';
    // the message vars that name the claim, so that a claim with the same words as another one is reported
    const string PART_VARS = 'vars';
    // the phrases of the pod that the import json re-declares by their name (see convert)
    const string PART_DECLARATIONS = 'declarations';
    // the keys of a phrase that a wikidata id gives: its name, the declaration of the phrase of the pod with this
    // wikidata reference (empty for a new word) and the wikidata id that a new word gets as its reference
    const string PHR_NAME = 'name';
    const string PHR_DECLARATION = 'declaration';
    const string PHR_REF = 'ref';
    // true for the currency pair of a price whose number is turned to its inverse, because only the reverse
    // pair is listed e.g. the euros of one US dollar as the US dollars of one euro of the pair "USD per EUR"
    const string PHR_INVERTED = 'inverted';
    // the id of a wikidata property starts with a "P" e.g. "P1082", the one of an item with a "Q" e.g. "Q72"
    const string WD_PROPERTY_PREFIX = 'P';
    // the separator of the from, verb and to of a triple without name e.g. "EUR per USD"
    const string TRIPLE_NAME_SEPARATOR = ' ';
    // the decimal point and the sign of the amount of wikidata e.g. "+0.92" or "-500"
    const string AMOUNT_DECIMAL_POINT = '.';
    const string AMOUNT_SIGNS = '+-';

    /**
     * retrieve the entity data json from wikidata for the given wikidata id e.g. "Q167" for Pi
     * @param string $wikidata_id the wikidata entity id e.g. "Q167" that selects the entity to read
     * @return string the raw json text returned by wikidata or an empty string if the request failed
     */
    function get_entity_json(string $wikidata_id): string
    {
        return $this->request_json($wikidata_id);
    }

    /**
     * retrieve the property data json from wikidata for the given property id e.g. "P2284" for price
     * properties are served by the same EntityData endpoint as the entities
     * @param string $property_id the wikidata property id e.g. "P2284" that selects the property to read
     * @return string the raw json text returned by wikidata or an empty string if the request failed
     */
    function get_property_json(string $property_id): string
    {
        return $this->request_json($property_id);
    }

    /**
     * the context of a http request to wikidata or wikipedia: a descriptive user agent, because
     * else they answer with a http 403, and a timeout, because else a slow answer blocks the page
     *
     * @param int $timeout the seconds to wait for the answer e.g. self::IMPORT_TIMEOUT for an import
     * @return resource the php stream context for file_get_contents
     */
    static function request_context(int $timeout = self::READ_TIMEOUT)
    {
        $http = [
            self::HTTP_HEADER => self::HTTP_USER_AGENT . self::USER_AGENT,
            self::HTTP_TIMEOUT => $timeout,
        ];
        return stream_context_create([self::HTTP_CONTEXT => $http]);
    }

    /**
     * retrieve the json from the wikidata EntityData endpoint for the given entity or property id
     * @param string $id the wikidata entity or property id e.g. "Q167" or "P2284"
     * @return string the raw json text returned by wikidata or an empty string if the request failed
     */
    private function request_json(string $id): string
    {
        $result = '';
        $url = self::ENTITY_DATA_URL . $id . self::JSON_EXTENSION;
        $context = self::request_context(self::IMPORT_TIMEOUT);
        // the @ suppresses the php warning of a failed http request (e.g. a http 403 or no network)
        // so that the failure is reported as a log warning and the program continues
        $json = @file_get_contents($url, false, $context);
        if ($json === false) {
            log_warning('the wikidata request for ' . $id . ' via ' . $url . ' failed', 'import_wikidata->request_json');
        } else {
            $result = $json;
        }
        return $result;
    }

    /**
     * retrieve and store the json received from wikidata for the given entity or property id as a test resource file
     * used to capture a real wikidata response as a fixture for the offline import tests
     * @param string $wikidata_id the wikidata entity or property id e.g. "Q167" or "P2284"
     * @param string $path the target directory e.g. the wikidata cache folder of the tests
     * @return user_message ok or a warning with the reason if the request or the file write failed
     */
    function store_json(string $wikidata_id, string $path): user_message
    {
        return $this->store_text($wikidata_id, $this->request_json($wikidata_id), $path);
    }

    /**
     * store the given json for the given wikidata id as a formatted test resource file
     * @param string $wikidata_id the wikidata entity or property id e.g. "Q167" or "P2284"
     * @param string $json the json text to store e.g. as received from get_entity_json or get_property_json
     * @param string $path the target directory e.g. the wikidata cache folder of the tests
     * @return user_message ok or a warning with the reason if the json is empty or the file write failed
     */
    function store_text(string $wikidata_id, string $json, string $path): user_message
    {
        $msg = new user_message(); // the message IS the return value, so the caller merges it
        if ($json == '') {
            $msg->add(msg_id::IMPORT_FAILED, [msg_id::VAR_SUMMARY => 'no data received from wikidata for ' . $wikidata_id]);
        } else {
            $filename = $path . $wikidata_id . self::JSON_EXTENSION;
            if (file_put_contents($filename, $this->pretty_json($json)) === false) {
                $msg->add(msg_id::FILE_WRITE_FAILED, [msg_id::VAR_FILE_NAME => $filename]);
            }
        }
        return $msg;
    }

    /**
     * pretty print the given json so that the stored fixture is human readable
     * @param string $json the raw json text e.g. as received from wikidata
     * @return string the formatted json or the unchanged text if it cannot be decoded
     */
    private function pretty_json(string $json): string
    {
        $result = $json;
        $decoded = json_decode($json, true);
        if ($decoded !== null) {
            $result = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return $result;
    }


    /*
     * convert
     */

    /**
     * create a zukunft.com import json array from a wikidata entity json and a wikidata property json
     * for each value of the property in the entity (e.g. each population of Zurich) a value is created
     * that is assigned to the property word (e.g. 'population'), the entity, the unit and the year of the value;
     * the unit name (e.g. 'square kilometre') is read from wikidata via the unit id given in the value; a value
     * without a unit is assigned to the entity word itself and the criteria of a value (e.g. 'resident
     * population') are added as words (see claim_import); the price of a currency in another currency is a value
     * of the listed currency pair instead e.g. the euros of one US dollar of the pair 'EUR per USD'
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q4917.json
     * @param string $property_json the wikidata json of the property e.g. the content of P2284.json
     * @param array $pod_phrases the phrases of the pod with a wikidata reference by its wikidata id, each as the list of
     *                           the phrases with this reference, each as its import declaration: the phrase name
     *                           (json_fields::NAME) and the words and triples that the import json needs to declare it
     *                           (json_fields::WORDS, json_fields::TRIPLES) e.g. the triple "Zurich (city)" with its
     *                           from, verb and to and the words "Zurich" and "city"; an id without phrase becomes a new word
     * @param array $currency_pairs the currencies of the pod by their wikidata id, each with the import declaration of
     *                              its listed currency pairs by the wikidata id of the other currency (see pod_phrases)
     *                              e.g. ['Q4917' => ['Q4916' => the triple "EUR per USD"], 'Q4916' => []] for the
     *                              euros of one US dollar; a currency without listed pair has an empty list, so that its
     *                              price in another currency is reported instead of imported with the unit as a phrase
     * @param user_message $msg to report each value that is skipped, e.g. because the name of its unit cannot be read
     * @return array the zukunft.com import json as an array ready to be json encoded or an empty array if the entity or the property has no name
     */
    function convert(
        string       $entity_json,
        string       $property_json,
        array        $pod_phrases,
        array        $currency_pairs,
        user_message $msg
    ): array
    {
        $result = [];
        $entity = json_decode($entity_json, true);
        $property = json_decode($property_json, true);
        if (is_array($entity) and is_array($property)) {
            $ent = $this->first_entity($entity);
            $entity_label = $this->label($ent);
            $entity_id = $ent[self::WD_ID] ?? '';
            $prp = $this->first_entity($property);
            $property_id = $prp[self::WD_ID] ?? '';
            $ent_phr = $this->phrase_of($entity_id, $entity_label, $pod_phrases, $entity_label, $msg);
            $prp_phr = $this->phrase_of($property_id, $this->label($prp), $pod_phrases, $entity_label, $msg);

            // without a name the entity or the property cannot be named as a phrase, so nothing is converted
            if ($this->has_name($ent_phr) and $this->has_name($prp_phr)) {
                $entity_name = $ent_phr[self::PHR_NAME];
                $property_name = $prp_phr[self::PHR_NAME];
                // collect the new words with their phrase type and wikidata id, the phrases of the pod and the values
                $phrases = $this->phrase_parts([$prp_phr, $ent_phr]);
                $entity_pairs = $this->entity_pairs($entity_id, $currency_pairs);
                $values = [];
                $parts = [];
                foreach ($ent[self::WD_CLAIMS][$property_id] ?? [] as $claim) {
                    $part = $this->claim_import($claim, $entity_name, $property_name, $pod_phrases, $entity_pairs, $msg);
                    if ($part != []) {
                        $parts[] = $part;
                    }
                }
                // after all claims, because a claim can only be a double of a later one too
                foreach ($this->parts_without_doubles($parts, $msg) as $part) {
                    // the union keeps the numeric keys of the years, which array_merge would renumber
                    $phrases[self::PART_WORD_TYPES] += $part[self::PART_WORD_TYPES];
                    $phrases[self::PART_WORD_REFS] += $part[self::PART_WORD_REFS];
                    $phrases[self::PART_DECLARATIONS] += $part[self::PART_DECLARATIONS];
                    $values[] = $part[self::PART_VALUE];
                }
                $result = $this->import_json($phrases, $values);
            }
        }
        return $result;
    }

    /**
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q72.json
     * @return string the wikidata id of the entity e.g. "Q72" or an empty string if the json has no entity
     */
    function entity_id(string $entity_json): string
    {
        $entity = json_decode($entity_json, true);
        $result = '';
        if (is_array($entity)) {
            $result = $this->first_entity($entity)[self::WD_ID] ?? '';
        }
        return $result;
    }

    /**
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q72.json
     * @return array the wikidata ids of the properties of the claims of the entity e.g. ["P1082", "P31", ...],
     *               so that the caller selects those that the pod knows
     */
    function property_ids(string $entity_json): array
    {
        $entity = json_decode($entity_json, true);
        $result = [];
        if (is_array($entity)) {
            // the keys of the claims are texts like "P1082", so they never become numeric keys
            $result = array_keys($this->first_entity($entity)[self::WD_CLAIMS] ?? []);
        }
        return $result;
    }

    /**
     * the wikidata ids that the convert of the given properties names, so that the caller loads the phrases of
     * the pod with these wikidata references (the pod phrases of convert) before the convert
     *
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q72.json
     * @param array $property_ids the wikidata ids of the properties to convert e.g. ["P1082"]
     * @return array the wikidata ids of the entity, the units and the criteria of the claims of these properties
     *               e.g. ["Q72", "Q56229488", "Q56229478"], each only once
     */
    function item_ids(string $entity_json, array $property_ids): array
    {
        $entity = json_decode($entity_json, true);
        $result = [];
        if (is_array($entity)) {
            $ent = $this->first_entity($entity);
            $result[] = $ent[self::WD_ID] ?? '';
            foreach ($property_ids as $property_id) {
                foreach ($ent[self::WD_CLAIMS][$property_id] ?? [] as $claim) {
                    $value = $claim[self::WD_MAINSNAK][self::WD_DATAVALUE][self::WD_VALUE] ?? [];
                    $unit = is_array($value) ? ($value[self::WD_UNIT] ?? '') : '';
                    $result[] = $this->id_from_url($unit);
                    foreach ($claim[self::WD_QUALIFIERS][self::WD_CRITERION_USED] ?? [] as $qualifier) {
                        $result[] = $qualifier[self::WD_DATAVALUE][self::WD_VALUE][self::WD_ID] ?? '';
                    }
                }
            }
        }
        // the unit "1" of a number without unit and a criterion without id give no wikidata id
        return array_values(array_unique(array_filter($result, fn($id) => $id != '')));
    }

    /**
     * the property json that convert needs for a property that the pod knows, so that its label is not read from
     * wikidata: the converter names the property by the phrase of the pod with this wikidata reference
     *
     * @param string $property_id the wikidata id of a property of the pod e.g. "P1082"
     * @return string the wikidata json of the property with only its id and without labels
     */
    static function property_json(string $property_id): string
    {
        return json_encode([self::WD_ENTITIES => [$property_id => [self::WD_ID => $property_id]]]);
    }

    /**
     * the phrase that the import uses for a wikidata id: the phrase of the pod with this wikidata reference, so that
     * e.g. the values of Q72 are added to the triple "Zurich (city)", else a new word named by the wikidata label
     *
     * @param string $id the wikidata id of the entity, the property, a unit or a criterion e.g. "Q72"
     * @param string|null $label the wikidata label of the id, or null to read it only if the pod has no phrase for it
     * @param array $pod_phrases the declarations of the phrases of the pod by their wikidata id (see convert)
     * @param string $entity_name the name of the entity whose values are converted, to name it in the message
     * @param user_message $msg to report an id that is the reference of more than one phrase of the pod
     * @return array the name, the declaration and the reference of the phrase (see the PHR_* keys) with an empty name
     *               if wikidata has no label, or an empty array if the id is the reference of more than one phrase
     */
    private function phrase_of(string $id, ?string $label, array $pod_phrases, string $entity_name, user_message $msg): array
    {
        $declarations = $pod_phrases[$id] ?? [];
        $result = [];
        if (count($declarations) > 1) {
            // never one phrase guessed, because the values would then be added to the wrong one
            $msg->add(msg_id::WIKIDATA_ID_TWICE, [
                msg_id::VAR_NAME => $entity_name,
                msg_id::VAR_ID => $id,
                msg_id::VAR_NAME_LIST => implode(', ', array_column($declarations, json_fields::NAME)),
            ]);
        } elseif (count($declarations) == 1) {
            $declaration = $declarations[0];
            $result = [
                self::PHR_NAME => $declaration[json_fields::NAME],
                self::PHR_DECLARATION => $declaration,
                self::PHR_REF => '',
            ];
        } else {
            if ($label === null) {
                $label = $this->item_label($id, $msg);
            }
            $result = [self::PHR_NAME => $label, self::PHR_DECLARATION => [], self::PHR_REF => $id];
        }
        return $result;
    }

    /**
     * @param array $phr a phrase of a wikidata id as created by phrase_of
     * @return bool true if the phrase can be used, false if it has no name or its id names more than one phrase
     */
    private function has_name(array $phr): bool
    {
        return ($phr[self::PHR_NAME] ?? '') != '';
    }

    /**
     * @param array $phrases the named phrases of wikidata ids as created by phrase_of
     * @param array $parts the new words and the phrases of the pod collected so far e.g. for the unit before the criteria
     * @return array the new words with their type and wikidata id and the phrases of the pod by name (see PART_* keys)
     */
    private function phrase_parts(array $phrases, array $parts = []): array
    {
        $result = $parts + [self::PART_WORD_TYPES => [], self::PART_WORD_REFS => [], self::PART_DECLARATIONS => []];
        foreach ($phrases as $phr) {
            $name = $phr[self::PHR_NAME];
            if ($phr[self::PHR_DECLARATION] == []) {
                $result[self::PART_WORD_TYPES][$name] = '';
                $result[self::PART_WORD_REFS][$name] = $phr[self::PHR_REF];
            } else {
                $result[self::PART_DECLARATIONS][$name] = $phr[self::PHR_DECLARATION];
            }
        }
        return $result;
    }

    /**
     * the currency pair that the price of the entity gets for each other currency of the pod, so that each claim
     * only looks up its unit
     *
     * @param string $entity_id the wikidata id of the entity e.g. "Q4917" for the US dollar
     * @param array $currency_pairs the currencies of the pod with their listed pairs (see convert)
     * @return array the pair phrase (see pair_phrase) by the wikidata id of each other currency, an empty array for a
     *               currency without listed pair in either direction, or no entry at all if the entity is no currency
     */
    private function entity_pairs(string $entity_id, array $currency_pairs): array
    {
        $result = [];
        if (array_key_exists($entity_id, $currency_pairs)) {
            foreach (array_keys($currency_pairs) as $unit_id) {
                if ($unit_id != $entity_id) {
                    $result[$unit_id] = $this->currency_pair($entity_id, $unit_id, $currency_pairs);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $entity_id the wikidata id of the currency whose price is given e.g. "Q4917" for the US dollar
     * @param string $unit_id the wikidata id of the currency of the price e.g. "Q4916" for the euro
     * @param array $currency_pairs the currencies of the pod with their listed pairs (see convert)
     * @return array the listed pair "unit per entity" e.g. "EUR per USD", else the listed reverse pair "entity per
     *               unit" whose number is the inverse, or an empty array if neither is listed
     */
    private function currency_pair(string $entity_id, string $unit_id, array $currency_pairs): array
    {
        $pair = $currency_pairs[$entity_id][$unit_id] ?? [];
        $reverse = $currency_pairs[$unit_id][$entity_id] ?? [];
        $result = [];
        if ($pair != []) {
            $result = $this->pair_phrase($pair, false);
        } elseif ($reverse != []) {
            $result = $this->pair_phrase($reverse, true);
        }
        return $result;
    }

    /**
     * @param array $declaration the import declaration of a listed currency pair (see convert)
     * @param bool $inverted true if the number of wikidata is turned to its inverse for this pair
     * @return array the pair as a named phrase of the pod (see phrase_of) with the PHR_INVERTED flag
     */
    private function pair_phrase(array $declaration, bool $inverted): array
    {
        return [
            self::PHR_NAME => $declaration[json_fields::NAME],
            self::PHR_DECLARATION => $declaration,
            self::PHR_REF => '',
            self::PHR_INVERTED => $inverted,
        ];
    }

    /**
     * convert the given wikidata entity and property json to a zukunft.com import json and write it to the cache folder
     * the written file is meant to be imported by import_file and can optionally be removed again with cleanup_file
     * @param string $entity_id the wikidata id of the entity used for the cache file name e.g. "Q4917"
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q4917.json
     * @param string $property_json the wikidata json of the property e.g. the content of P2284.json
     * @param array $pod_phrases the declarations of the phrases of the pod by their wikidata id (see convert)
     * @param array $currency_pairs the currencies of the pod with their listed pairs (see convert)
     * @param user_message $msg to report each value that is skipped, because the name of its unit or of a criterion cannot be read
     * @param string $path the target directory e.g. the wikidata to import folder of the tests
     * @return string the path of the written import file or an empty string if nothing has been written
     */
    function convert_to_file(
        string       $entity_id,
        string       $entity_json,
        string       $property_json,
        array        $pod_phrases,
        array        $currency_pairs,
        user_message $msg,
        string       $path
    ): string
    {
        $result = '';
        $import = $this->convert($entity_json, $property_json, $pod_phrases, $currency_pairs, $msg);
        if ($import != []) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $filename = $path . self::IMPORT_PREFIX . $entity_id . self::JSON_EXTENSION;
            if (file_put_contents($filename, $this->pretty_json(json_encode($import))) !== false) {
                $result = $filename;
            }
        }
        return $result;
    }

    /**
     * remove a cache file created by convert_to_file, e.g. as an optional cleanup after the import
     * @param string $file the path of the cache file to remove e.g. as returned by convert_to_file
     * @return void
     */
    function cleanup_file(string $file): void
    {
        if ($file != '' and file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * build the zukunft.com import json array from the collected words, triples and values
     * the import never creates a triple of its own, so the only triples are those of the pod e.g. a currency pair
     * @param array $phrases the new words with their type and wikidata id and the phrases of the pod (see phrase_parts)
     * @param array $values the already created value entries
     * @return array the zukunft.com import json as an array
     */
    private function import_json(array $phrases, array $values): array
    {
        // the phrases of the pod first, because the new words and the values use them
        [$words, $triples] = $this->declared_phrases($phrases[self::PART_DECLARATIONS]);
        foreach ($phrases[self::PART_WORD_TYPES] as $name => $type) {
            // php turns a numeric key such as the year "2016" into an int, but a word name is a text
            $word = [json_fields::NAME => (string)$name];
            if ($type != '') {
                $word[json_fields::TYPE_NAME] = $type;
            }
            // add the wikidata reference to the word so that the import can link it to the source
            $ref_id = $phrases[self::PART_WORD_REFS][$name] ?? '';
            if ($ref_id != '') {
                $word[json_fields::REFS] = [[json_fields::NAME => $ref_id, json_fields::TYPE_NAME => $this->ref_type($ref_id)]];
            }
            $words[] = $word;
        }
        return [
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::DATA_VERSION => def::DATA_VERSION_INIT,
            json_fields::WORDS => $words,
            json_fields::TRIPLES => $triples,
            json_fields::VALUES => $values
        ];
    }

    /**
     * @param array $declarations the import declarations of the phrases of the pod by the phrase name (see convert)
     * @return array the words and the triples that the import json needs to declare these phrases, each only once
     */
    private function declared_phrases(array $declarations): array
    {
        $words = [];
        $triples = [];
        // by name, because e.g. the triples "Zurich (city)" and "Bern (city)" both declare the word "city"
        foreach ($declarations as $declaration) {
            foreach ($declaration[json_fields::WORDS] ?? [] as $word) {
                $words[$word[json_fields::NAME]] = $word;
            }
            foreach ($declaration[json_fields::TRIPLES] ?? [] as $triple) {
                $triples[$this->triple_key($triple)] = $triple;
            }
        }
        return [array_values($words), array_values($triples)];
    }

    /**
     * @param array $triple the import declaration of a triple of the pod
     * @return string the name of the triple, or for a triple without name, whose name the import derives,
     *                its from, verb and to e.g. "EUR per USD"
     */
    private function triple_key(array $triple): string
    {
        return $triple[json_fields::NAME] ?? implode(self::TRIPLE_NAME_SEPARATOR, [
            $triple[json_fields::EX_FROM],
            $triple[json_fields::EX_VERB],
            $triple[json_fields::EX_TO],
        ]);
    }

    /**
     * @param string $wikidata_id the wikidata id of a new word e.g. "Q72" or "P1082"
     * @return string the code id of the reference type, because wikidata links a property on another page than an item
     */
    private function ref_type(string $wikidata_id): string
    {
        $result = refs::WIKIDATA_TYPE;
        if (str_starts_with($wikidata_id, self::WD_PROPERTY_PREFIX)) {
            $result = ref_types::WIKIDATA_PROPERTY;
        }
        return $result;
    }

    /**
     * @param array $wikidata_json a decoded wikidata json with the "entities" field
     * @return array the first entity of the wikidata json or an empty array if none is found
     */
    private function first_entity(array $wikidata_json): array
    {
        $result = [];
        $entities = $wikidata_json[self::WD_ENTITIES] ?? [];
        if (is_array($entities) and $entities != []) {
            $result = reset($entities);
        }
        return $result;
    }

    /**
     * @param array $entity a decoded wikidata entity or property
     * @return string the english label of the entity e.g. "United States dollar" or an empty string
     */
    private function label(array $entity): string
    {
        // wikidata stores a label that is the same in most languages only once with the
        // language code "mul" e.g. "euro" for Q4916 has no extra english label
        return $entity[self::WD_LABELS][self::WD_LANG_EN][self::WD_VALUE]
            ?? $entity[self::WD_LABELS][self::WD_LANG_MUL][self::WD_VALUE]
            ?? '';
    }

    /**
     * @param string $url a wikidata entity url e.g. "http://www.wikidata.org/entity/Q4916"
     * @return string the wikidata id e.g. "Q4916" or an empty string if no id is found
     */
    private function id_from_url(string $url): string
    {
        $result = '';
        if (preg_match('/[QP]\d+/', $url, $matches) === 1) {
            $result = $matches[0];
        }
        return $result;
    }

    /**
     * create the words and the value that one wikidata claim adds to the import json
     * a value is assigned to the property word, the entity word and its unit (or only the currency pair for the
     * price of a currency in another currency), its year and the words of its criteria, so that e.g. two
     * populations of the same year stay apart
     * @param array $claim a wikidata statement of the property e.g. one price of the US dollar
     * @param string $entity_name the name of the entity e.g. "United States dollar"
     * @param string $property_name the name of the property e.g. "price"
     * @param array $pod_phrases the declarations of the phrases of the pod by their wikidata id (see convert)
     * @param array $entity_pairs the currency pair of the entity by the wikidata id of each other currency of the pod,
     *                            empty if the entity is no currency (see entity_pairs)
     * @param user_message $msg to report each claim that is not imported, e.g. because it has no number or the name of its unit cannot be read
     * @return array the word types, word refs, phrases of the pod, the value and the message vars of the
     *               claim (see the PART_* keys) or an empty array if the claim is not imported, which is reported
     */
    private function claim_import(
        array        $claim,
        string       $entity_name,
        string       $property_name,
        array        $pod_phrases,
        array        $entity_pairs,
        user_message $msg
    ): array
    {
        $result = [];
        $vars = $this->claim_vars($claim, $entity_name, $property_name);
        if ($this->is_importable($claim, $vars, $msg)) {
            $value = $claim[self::WD_MAINSNAK][self::WD_DATAVALUE][self::WD_VALUE];
            $unit_id = $this->id_from_url($value[self::WD_UNIT] ?? '');
            $amount = ltrim($value[self::WD_AMOUNT], '+');
            $is_price = $this->is_currency_price($unit_id, $entity_pairs);
            if ($is_price) {
                // the pair names both currencies e.g. "EUR per USD", so the entity is no word of its own
                $pair = $entity_pairs[$unit_id];
                $units = $this->pair_units($unit_id, $pair);
                $val_words = [$property_name];
            } else {
                $pair = [];
                $units = $this->claim_units($unit_id, $entity_name, $pod_phrases, $msg);
                $val_words = [$property_name, $entity_name];
            }
            $criteria = $this->claim_criteria($claim, $entity_name, $pod_phrases, $msg);
            $number = '';
            // the number of a price only after the names, so that an inverse is reported only if it is imported
            if ($this->has_named_phrases($units, $criteria, $entity_name, $msg)) {
                $number = $amount;
                if ($is_price) {
                    $number = $this->pair_number($amount, $pair, $unit_id, $vars, $msg);
                }
            }
            if ($number !== '') {
                $val_words = array_merge($val_words, array_column($units, self::PHR_NAME));
                // the unit before the year and the year before the criteria, so that the words keep their order
                $phrases = $this->phrase_parts($units);
                $year = $this->claim_year($claim);
                // the year "0" is a year of its own, so only an empty text means no point in time
                if ($year !== '') {
                    $phrases[self::PART_WORD_TYPES][$year] = phrase_types::TIME;
                    $val_words[] = $year;
                }
                $phrases = $this->phrase_parts($criteria, $phrases);
                $val_words = array_merge($val_words, array_column($criteria, self::PHR_NAME));
                $result = $phrases + [
                    self::PART_VALUE => [json_fields::WORDS => $val_words, json_fields::NUMBER => $number],
                    self::PART_VARS => $vars,
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $unit_id the wikidata id of the unit of a value e.g. "Q4916" or an empty string for a value without unit
     * @param array $entity_pairs the currency pair of the entity by the wikidata id of each other currency (see entity_pairs)
     * @return bool true if the value is the price of the currency entity in another currency of the pod,
     *              also if no pair of the two is listed, which pair_number reports
     */
    private function is_currency_price(string $unit_id, array $entity_pairs): bool
    {
        return array_key_exists($unit_id, $entity_pairs);
    }

    /**
     * @param string $unit_id the wikidata id of the currency of a price e.g. "Q4916"
     * @param array $pair the currency pair of the price (see currency_pair), empty if no pair is listed
     * @return array the pair as the unit phrase of the value by the wikidata id of the currency, empty without
     *               listed pair, which pair_number reports, so that no label message is added for it
     */
    private function pair_units(string $unit_id, array $pair): array
    {
        $result = [];
        if ($pair != []) {
            $result[$unit_id] = $pair;
        }
        return $result;
    }

    /**
     * the number that a price of a currency gets for its pair: the amount of wikidata for the listed pair "unit per
     * entity", or its inverse for the listed reverse pair, which is rounded to the significant digits of wikidata
     *
     * @param string $amount the amount of wikidata without the plus sign e.g. "0.92"
     * @param array $pair the currency pair of the price (see currency_pair), empty if no pair of the two currencies is listed
     * @param string $unit_id the wikidata id of the currency of the price e.g. "Q4916", to name it in the message
     * @param array $vars the message vars that name the claim (see claim_vars)
     * @param user_message $msg to report a price without listed pair and as info a price that is turned to its inverse
     * @return string the number of the value e.g. "0.92" or "1.1" for the inverse, or an empty string if the price is not imported
     */
    private function pair_number(string $amount, array $pair, string $unit_id, array $vars, user_message $msg): string
    {
        $result = '';
        if ($pair == []) {
            // never the unit as a phrase of its own, because it does not say which currency is the base
            $msg->add(msg_id::WIKIDATA_CURRENCY_PAIR_MISSING, $vars + [msg_id::VAR_NAME_TO => $unit_id]);
        } elseif (!$pair[self::PHR_INVERTED]) {
            $result = $amount;
        } elseif ((float)$amount == 0.0) {
            $msg->add(msg_id::WIKIDATA_CURRENCY_PRICE_ZERO, $vars + [msg_id::VAR_TRIPLE_NAME => $pair[self::PHR_NAME]]);
        } else {
            $result = $this->inverse($amount);
            $msg->add_info_with_vars(msg_id::WIKIDATA_CURRENCY_PAIR_INVERTED, $vars + [
                    msg_id::VAR_TRIPLE_NAME => $pair[self::PHR_NAME],
                    msg_id::VAR_VALUE_CHK => $result,
                ]);
        }
        return $result;
    }

    /**
     * @param string $amount a number of wikidata without the plus sign that is not zero e.g. "0.92"
     * @return string the inverse of the number with as many significant digits as wikidata gives e.g. "1.1",
     *                so that the inverse never claims to be more precise than the number of wikidata
     */
    private function inverse(string $amount): string
    {
        $inverse = 1 / (float)$amount;
        $decimals = $this->significant_digits($amount) - 1 - (int)floor(log10(abs($inverse)));
        return number_format(round($inverse, $decimals), max(0, $decimals), self::AMOUNT_DECIMAL_POINT, '');
    }

    /**
     * @param string $amount a number of wikidata without exponent e.g. "0.92", "-500" or "1.0813"
     * @return int the number of its significant digits e.g. 2 for "0.92" and 5 for "1.0813", at least one
     */
    private function significant_digits(string $amount): int
    {
        $digits = str_replace(self::AMOUNT_DECIMAL_POINT, '', ltrim($amount, self::AMOUNT_SIGNS));
        return max(1, strlen(ltrim($digits, '0')));
    }

    /**
     * @param string $unit_id the wikidata id of the unit of a value e.g. "Q4916" or an empty string for a value without unit
     * @param string $entity_name the name of the entity whose values are converted, to name it in the message
     * @param array $pod_phrases the declarations of the phrases of the pod by their wikidata id (see convert)
     * @param user_message $msg to report a unit that is the reference of more than one phrase of the pod
     * @return array the phrase of the unit by its wikidata id (see phrase_of), empty for a value without unit
     */
    private function claim_units(string $unit_id, string $entity_name, array $pod_phrases, user_message $msg): array
    {
        $result = [];
        if ($unit_id != '') {
            $result[$unit_id] = $this->phrase_of($unit_id, null, $pod_phrases, $entity_name, $msg);
        }
        return $result;
    }

    /**
     * report a unit or a criterion of a claim that has no name, so that its value is not imported
     *
     * @param array $units the phrase of the unit of the claim by its wikidata id (see claim_units)
     * @param array $criteria the phrases of the criteria of the claim by their wikidata id (see claim_criteria)
     * @param string $entity_name the name of the entity to name it in the message
     * @param user_message $msg to report a unit or a criterion whose name cannot be read
     * @return bool true if the unit and each criterion has a name; false also for an id of more than one phrase of the pod, which phrase_of has reported
     */
    private function has_named_phrases(array $units, array $criteria, string $entity_name, user_message $msg): bool
    {
        $result = true;
        foreach ($units as $unit_id => $unit) {
            if (!$this->has_name($unit)) {
                $result = false;
                // a unit of more than one phrase has its own message
                if ($unit != []) {
                    $msg->add(msg_id::WIKIDATA_UNIT_LABEL_MISSING, [msg_id::VAR_NAME => $entity_name, msg_id::VAR_ID => $unit_id]);
                }
            }
        }
        foreach ($criteria as $criterion_id => $criterion) {
            if (!$this->has_name($criterion)) {
                $result = false;
                if ($criterion != []) {
                    $msg->add(msg_id::WIKIDATA_CRITERION_LABEL_MISSING, [msg_id::VAR_NAME => $entity_name, msg_id::VAR_ID => $criterion_id]);
                }
            }
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement of the property e.g. one population of Zurich
     * @param string $entity_name the name of the entity e.g. "Zurich"
     * @param string $property_name the name of the property e.g. "population"
     * @return array the message vars that name the claim: the entity, the property, the claim id and the amount
     */
    private function claim_vars(array $claim, string $entity_name, string $property_name): array
    {
        return [
            msg_id::VAR_NAME => $entity_name,
            msg_id::VAR_PHRASE_NAME => $property_name,
            msg_id::VAR_ID => $claim[self::WD_ID] ?? '',
            msg_id::VAR_VALUE => $claim[self::WD_MAINSNAK][self::WD_DATAVALUE][self::WD_VALUE][self::WD_AMOUNT] ?? '',
        ];
    }

    /**
     * report a claim that cannot be imported, because the number would mean something else in zukunft.com
     *
     * @param array $claim a wikidata statement of the property e.g. one population of Zurich
     * @param array $vars the message vars that name the claim (see claim_vars)
     * @param user_message $msg to report why the claim is not imported
     * @return bool true if the claim has a number, is not deprecated and has only qualifiers with a known meaning
     */
    private function is_importable(array $claim, array $vars, user_message $msg): bool
    {
        $unknown_qualifiers = $this->unknown_qualifiers($claim);
        $time_msg_id = $this->time_problem($claim);
        $result = false;
        if ($this->is_deprecated($claim)) {
            $msg->add(msg_id::WIKIDATA_DEPRECATED, $vars);
        } elseif (!$this->has_value($claim)) {
            $msg->add(msg_id::WIKIDATA_NO_VALUE, $vars);
        } elseif ($unknown_qualifiers != []) {
            $vars[msg_id::VAR_FIELD_NAME] = implode(', ', $unknown_qualifiers);
            $msg->add(msg_id::WIKIDATA_QUALIFIER_UNKNOWN, $vars);
        } elseif ($time_msg_id != null) {
            $vars[msg_id::VAR_TIME] = $this->claim_time($claim);
            $msg->add($time_msg_id, $vars);
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement
     * @return bool true if wikidata marks the claim as wrong or outdated
     */
    private function is_deprecated(array $claim): bool
    {
        return ($claim[self::WD_RANK] ?? '') == self::WD_RANK_DEPRECATED;
    }

    /**
     * @param array $claim a wikidata statement
     * @return bool true if the claim has a number, false e.g. for the snak type "somevalue" of an unknown value
     */
    private function has_value(array $claim): bool
    {
        $snaktype = $claim[self::WD_MAINSNAK][self::WD_SNAKTYPE] ?? '';
        $amount = $claim[self::WD_MAINSNAK][self::WD_DATAVALUE][self::WD_VALUE][self::WD_AMOUNT] ?? '';
        return ($snaktype == self::WD_SNAKTYPE_VALUE and is_string($amount) and $amount != '');
    }

    /**
     * @param array $claim a wikidata statement
     * @return array the ids of the qualifiers that may change what the number means e.g. ["P518"]
     */
    private function unknown_qualifiers(array $claim): array
    {
        $qualifier_ids = array_keys($claim[self::WD_QUALIFIERS] ?? []);
        return array_values(array_diff($qualifier_ids, self::WD_KNOWN_QUALIFIERS));
    }

    /**
     * @param array $claim a wikidata statement that may have a "point in time" (P585) qualifier
     * @return msg_id|null the message why the point in time cannot be used, null if it can be used or the claim has none
     */
    private function time_problem(array $claim): ?msg_id
    {
        $times = $claim[self::WD_QUALIFIERS][self::WD_POINT_IN_TIME] ?? [];
        $result = null;
        if (count($times) > 1) {
            $result = msg_id::WIKIDATA_TIME_TWICE;
        } elseif ($times != []) {
            if (preg_match(self::WD_TIME_YEAR_PATTERN, $this->claim_time($claim)) !== 1) {
                $result = msg_id::WIKIDATA_TIME_UNKNOWN;
            } elseif ($this->claim_precision($claim) < self::WD_PRECISION_YEAR) {
                $result = msg_id::WIKIDATA_TIME_PRECISION;
            }
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement that may have a "point in time" (P585) qualifier
     * @return string the complete time text of wikidata e.g. "+2021-12-31T00:00:00Z", empty if the claim has none
     */
    private function claim_time(array $claim): string
    {
        $result = '';
        $time = $claim[self::WD_QUALIFIERS][self::WD_POINT_IN_TIME][0][self::WD_DATAVALUE][self::WD_VALUE][self::WD_TIME] ?? '';
        if (is_string($time)) {
            $result = $time;
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement that may have a "point in time" (P585) qualifier
     * @return int the precision of the point in time e.g. 9 for a year, 0 if the claim has none
     */
    private function claim_precision(array $claim): int
    {
        return (int)($claim[self::WD_QUALIFIERS][self::WD_POINT_IN_TIME][0][self::WD_DATAVALUE][self::WD_VALUE][self::WD_PRECISION] ?? 0);
    }

    /**
     * report every claim whose value has the same words as the value of another claim and import none of them,
     * because the import cannot know which number is meant e.g. two populations of the same year
     *
     * @param array $parts the parts of the claims as created by claim_import
     * @param user_message $msg to report each claim with the same words as another one
     * @return array the parts whose words no other part has
     */
    private function parts_without_doubles(array $parts, user_message $msg): array
    {
        $count = [];
        foreach ($parts as $part) {
            $key = $this->words_key($part);
            $count[$key] = ($count[$key] ?? 0) + 1;
        }
        $result = [];
        foreach ($parts as $part) {
            if ($count[$this->words_key($part)] > 1) {
                $vars = $part[self::PART_VARS];
                $vars[msg_id::VAR_NAME_LIST] = implode(', ', $part[self::PART_VALUE][json_fields::WORDS]);
                $msg->add(msg_id::WIKIDATA_SAME_WORDS, $vars);
            } else {
                $result[] = $part;
            }
        }
        return $result;
    }

    /**
     * @param array $part the part of one claim as created by claim_import
     * @return string the complete words of the value in a fixed order, so that the same words give the same key
     */
    private function words_key(array $part): string
    {
        $words = $part[self::PART_VALUE][json_fields::WORDS];
        sort($words, SORT_STRING);
        return json_encode($words);
    }

    /**
     * read the english label of the given wikidata item e.g. "euro" for the unit "Q4916"
     * @param string $item_id the wikidata id of a unit or a criterion e.g. "Q4916"
     * @param user_message $msg to report a missing answer file of an offline test
     * @return string the english label of the item or an empty string if it cannot be read
     */
    private function item_label(string $item_id, user_message $msg): string
    {
        $result = '';
        if ($item_id != '') {
            if (array_key_exists($item_id, $this->item_labels)) {
                $result = $this->item_labels[$item_id];
            } else {
                $json = $this->read_json($item_id, $msg);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $result = $this->label($this->first_entity($decoded));
                }
                $this->item_labels[$item_id] = $result;
            }
        }
        return $result;
    }

    /**
     * @param string $id the wikidata entity or property id e.g. "Q4916"
     * @param user_message $msg to report a missing answer file of an offline test
     * @return string the json of the item read from wikidata, or from the fixture folder of an offline test
     */
    private function read_json(string $id, user_message $msg): string
    {
        if ($this->fixture_path == '') {
            $result = $this->request_json($id);
        } else {
            $result = $this->fixture_json($id, $msg);
        }
        return $result;
    }

    /**
     * @param string $id the wikidata entity or property id e.g. "Q4916"
     * @param user_message $msg to report that the test resources have no answer for this id
     * @return string the json of the answer file in the fixture folder or an empty string if it is missing
     */
    private function fixture_json(string $id, user_message $msg): string
    {
        $result = '';
        $file = $this->fixture_path . $id . self::JSON_EXTENSION;
        if (file_exists($file)) {
            $result = file_get_contents($file);
        } else {
            $msg->add(msg_id::WIKIDATA_FIXTURE_MISSING, [msg_id::VAR_ID => $id, msg_id::VAR_FILE_NAME => $file]);
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement that may have "criterion used" (P1013) qualifiers
     * @param string $entity_name the name of the entity whose values are converted, to name it in the message
     * @param array $pod_phrases the declarations of the phrases of the pod by their wikidata id (see convert)
     * @param user_message $msg to report a missing answer file of an offline test or a criterion of more than one phrase
     * @return array the phrase of each criterion by its wikidata id (see phrase_of) e.g. the new word "resident population"
     *               for "Q56229488", with an empty name if its label cannot be read
     */
    private function claim_criteria(array $claim, string $entity_name, array $pod_phrases, user_message $msg): array
    {
        $result = [];
        foreach ($claim[self::WD_QUALIFIERS][self::WD_CRITERION_USED] ?? [] as $qualifier) {
            $criterion_id = $qualifier[self::WD_DATAVALUE][self::WD_VALUE][self::WD_ID] ?? '';
            if ($criterion_id != '') {
                $result[$criterion_id] = $this->phrase_of($criterion_id, null, $pod_phrases, $entity_name, $msg);
            }
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement whose point in time can be used (see time_problem)
     * @return string the year of the value with its sign and all its digits e.g. "2016", "10000" or "-500",
     *                or an empty string if no point in time is set
     */
    private function claim_year(array $claim): string
    {
        $result = '';
        if (preg_match(self::WD_TIME_YEAR_PATTERN, $this->claim_time($claim), $matches) === 1) {
            // without the leading zeros of wikidata e.g. "-0500", but the year 0 stays "0"
            $result = ltrim($matches[2], '0');
            if ($result === '') {
                $result = '0';
            }
            if ($matches[1] == self::WD_TIME_SIGN_NEGATIVE) {
                $result = self::WD_TIME_SIGN_NEGATIVE . $result;
            }
        }
        return $result;
    }

}