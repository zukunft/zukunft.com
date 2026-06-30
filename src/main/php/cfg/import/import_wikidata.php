<?php

/*

    cfg/import/import_wikidata.php - retrieve and capture wikidata entity data
    -----------------------------

    get_entity_json() retrieves the entity data json from wikidata for a given
    wikidata id e.g. "Q167" for Pi and returns the raw json text
    store_json() is a test helper that stores the received json as a fixture in
    the wikidata test resource folder so that the import can be tested offline


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
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class import_wikidata
{

    // cache of the already read unit labels by wikidata id so that each unit is read only once per convert
    private array $unit_labels = [];

    // the base url of the wikidata entity data json api; the wikidata id and the json extension are added
    const string ENTITY_DATA_URL = 'https://www.wikidata.org/wiki/Special:EntityData/';
    const string IMPORT_PREFIX = 'import_';
    const string JSON_EXTENSION = '.json';
    // wikidata answers with a http 403 if no descriptive user agent is sent, so identify zukunft.com
    const string USER_AGENT = 'zukunft.com (https://github.com/zukunft/zukunft.com)';

    // the field names of the wikidata json structure used by the converter
    const string WD_ENTITIES = 'entities';
    const string WD_LABELS = 'labels';
    const string WD_LANG_EN = 'en';
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
    // the verb that links the entity and the unit in the created triple e.g. "United States dollar" in "euro"
    const string IN_VERB = 'in';

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
     * retrieve the json from the wikidata EntityData endpoint for the given entity or property id
     * @param string $id the wikidata entity or property id e.g. "Q167" or "P2284"
     * @return string the raw json text returned by wikidata or an empty string if the request failed
     */
    private function request_json(string $id): string
    {
        $result = '';
        $url = self::ENTITY_DATA_URL . $id . self::JSON_EXTENSION;
        // send a descriptive user agent so that wikidata does not answer with a http 403
        $context = stream_context_create(['http' => ['header' => 'User-Agent: ' . self::USER_AGENT]]);
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
     * @param string|null $path the target directory; null uses the wikidata test resource folder
     * @return user_message ok or a warning with the reason if the request or the file write failed
     */
    function store_json(string $wikidata_id, ?string $path = null): user_message
    {
        return $this->store_text($wikidata_id, $this->request_json($wikidata_id), $path);
    }

    /**
     * store the given json for the given wikidata id as a formatted test resource file
     * @param string $wikidata_id the wikidata entity or property id e.g. "Q167" or "P2284"
     * @param string $json the json text to store e.g. as received from get_entity_json or get_property_json
     * @param string|null $path the target directory; null uses the wikidata test resource folder
     * @return user_message ok or a warning with the reason if the json is empty or the file write failed
     */
    function store_text(string $wikidata_id, string $json, ?string $path = null): user_message
    {
        $usr_msg = new user_message();
        if ($path === null) {
            $path = test_paths::IMPORT_WIKIDATA;
        }
        if ($json == '') {
            $usr_msg->add(msg_id::IMPORT_FAILED, [msg_id::VAR_SUMMARY => 'no data received from wikidata for ' . $wikidata_id]);
        } else {
            $filename = $path . $wikidata_id . self::JSON_EXTENSION;
            if (file_put_contents($filename, $this->pretty_json($json)) === false) {
                $usr_msg->add(msg_id::FILE_WRITE_FAILED, [msg_id::VAR_FILE_NAME => $filename]);
            }
        }
        return $usr_msg;
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
     * for each value of the property in the entity (e.g. each price of the US dollar) a value is created
     * that is assigned to the property word (e.g. 'price'), the entity-in-unit triple (e.g. 'United States
     * dollar in euro') and the year of the value; the unit name (e.g. 'euro') is read from wikidata via the
     * unit id given in the value (e.g. 'Q4916')
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q4917.json
     * @param string $property_json the wikidata json of the property e.g. the content of P2284.json
     * @return array the zukunft.com import json as an array ready to be json encoded
     */
    function convert(string $entity_json, string $property_json): array
    {
        $result = [];
        $entity = json_decode($entity_json, true);
        $property = json_decode($property_json, true);
        if (is_array($entity) and is_array($property)) {
            $ent = $this->first_entity($entity);
            $entity_name = $this->label($ent);
            $entity_id = $ent[self::WD_ID] ?? '';
            $prp = $this->first_entity($property);
            $property_name = $this->label($prp);
            $property_id = $prp[self::WD_ID] ?? '';

            // collect the word names with their phrase type, the wikidata reference id, the triple units and the values
            $word_types = [$property_name => '', $entity_name => ''];
            $word_refs = [$property_name => $property_id, $entity_name => $entity_id];
            $triple_units = [];
            $values = [];
            foreach ($ent[self::WD_CLAIMS][$property_id] ?? [] as $claim) {
                $value = $claim[self::WD_MAINSNAK][self::WD_DATAVALUE][self::WD_VALUE] ?? null;
                if ($value != null) {
                    $unit_id = $this->id_from_url($value[self::WD_UNIT] ?? '');
                    $unit_name = $this->unit_label($unit_id);
                    $year = $this->claim_year($claim);
                    $triple_name = $entity_name . ' ' . self::IN_VERB . ' ' . $unit_name;
                    $word_types[$unit_name] = '';
                    $word_refs[$unit_name] = $unit_id;
                    $triple_units[$triple_name] = $unit_name;
                    $val_words = [$property_name, $triple_name];
                    if ($year != '') {
                        $word_types[$year] = phrase_types::TIME;
                        $val_words[] = $year;
                    }
                    $values[] = [json_fields::WORDS => $val_words, json_fields::NUMBER => ltrim($value[self::WD_AMOUNT] ?? '', '+')];
                }
            }
            $result = $this->import_json($entity_name, $word_types, $word_refs, $triple_units, $values);
        }
        return $result;
    }

    /**
     * convert the given wikidata entity and property json to a zukunft.com import json and write it to the cache folder
     * the written file is meant to be imported by import_file and can optionally be removed again with cleanup_file
     * @param string $entity_id the wikidata id of the entity used for the cache file name e.g. "Q4917"
     * @param string $entity_json the wikidata json of the entity e.g. the content of Q4917.json
     * @param string $property_json the wikidata json of the property e.g. the content of P2284.json
     * @param string|null $path the target directory; null uses the wikidata cache test resource folder
     * @return string the path of the written cache file or an empty string if nothing has been written
     */
    function convert_to_file(string $entity_id, string $entity_json, string $property_json, ?string $path = null): string
    {
        $result = '';
        if ($path === null) {
            $path = test_paths::IMPORT_WIKIDATA_CACHE;
        }
        $import = $this->convert($entity_json, $property_json);
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
     * @param string $entity_name the name of the entity used as the "from" phrase of each triple
     * @param array $word_types the word name as key and the phrase type code id (or '') as value
     * @param array $word_refs the word name as key and the wikidata reference id (or '') as value
     * @param array $triple_units the triple name as key and the unit name (the "to" phrase) as value
     * @param array $values the already created value entries
     * @return array the zukunft.com import json as an array
     */
    private function import_json(string $entity_name, array $word_types, array $word_refs, array $triple_units, array $values): array
    {
        $words = [];
        foreach ($word_types as $name => $type) {
            $word = [json_fields::NAME => $name];
            if ($type != '') {
                $word[json_fields::TYPE_NAME] = $type;
            }
            // add the wikidata reference to the word so that the import can link it to the source
            $ref_id = $word_refs[$name] ?? '';
            if ($ref_id != '') {
                $word[json_fields::REFS] = [[json_fields::NAME => $ref_id, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]];
            }
            $words[] = $word;
        }
        $triples = [];
        foreach ($triple_units as $unit_name) {
            $triples[] = [
                json_fields::NAME => '',
                json_fields::EX_FROM => $entity_name,
                json_fields::EX_VERB => self::IN_VERB,
                json_fields::EX_TO => $unit_name
            ];
        }
        return [
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::WORDS => $words,
            json_fields::TRIPLES => $triples,
            json_fields::VALUES => $values
        ];
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
        return $entity[self::WD_LABELS][self::WD_LANG_EN][self::WD_VALUE] ?? '';
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
     * read the english label of the given wikidata unit e.g. "euro" for "Q4916"
     * the unit json is read from the captured fixture if present, otherwise it is requested from wikidata
     * @param string $unit_id the wikidata id of the unit e.g. "Q4916"
     * @return string the english label of the unit or an empty string if it cannot be read
     */
    private function unit_label(string $unit_id): string
    {
        $result = '';
        if ($unit_id != '') {
            if (array_key_exists($unit_id, $this->unit_labels)) {
                $result = $this->unit_labels[$unit_id];
            } else {
                $file = test_paths::IMPORT_WIKIDATA . $unit_id . self::JSON_EXTENSION;
                $json = file_exists($file) ? file_get_contents($file) : $this->get_entity_json($unit_id);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $result = $this->label($this->first_entity($decoded));
                }
                $this->unit_labels[$unit_id] = $result;
            }
        }
        return $result;
    }

    /**
     * @param array $claim a wikidata statement that may have a "point in time" (P585) qualifier
     * @return string the year of the value e.g. "2016" or an empty string if no date is set
     */
    private function claim_year(array $claim): string
    {
        $result = '';
        $time = $claim[self::WD_QUALIFIERS][self::WD_POINT_IN_TIME][0][self::WD_DATAVALUE][self::WD_VALUE][self::WD_TIME] ?? '';
        if (preg_match('/(\d{4})-/', $time, $matches) === 1) {
            $result = $matches[1];
        }
        return $result;
    }

}