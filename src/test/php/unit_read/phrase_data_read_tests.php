<?php

/*

    test/php/unit_read/phrase_data_read_tests.php - database unit testing of the phrase data of api/phraseData
    ---------------------------------------------

    the phrase data of the example PV installation contains at least the objects of the hand made
    sample api/phrase_data/phrase_data_pv_install.json; the use case is user data, so the objects are compared by
    their names and never by their database ids (docs/llm/json_structure.md "Use case files")


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::EXPORT . 'phrase_data.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'phrase_data_walks.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'triple_names.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_data_read_tests
{

    // the separator of the parts of a triple or value key e.g. "PV|is an acronym for|photovoltaic"
    const string KEY_SEP = '|';
    // the json fields of the objects that the phrase data contains without the results
    const array OBJECT_FIELDS = [
        json_fields::WORDS,
        json_fields::TRIPLES,
        json_fields::FORMULAS,
        json_fields::SOURCES,
        json_fields::VALUES
    ];

    function run(test_cleanup $t): void
    {
        // init
        $lib = new library();
        $phd = new phrase_data();
        $msg = new user_message($t->usr1);
        $t->name = 'phrase data read db->';

        // start the test section (ts)
        $ts = 'db read phrase data ';
        $t->header($ts);

        // the use case objects are selected by name, because their ids follow the import order
        $test_name = 'the example PV installation of the sample is loaded';
        $sample = json_decode(file_get_contents(test_files::PHRASE_DATA_PV_INSTALL), true);
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        // the sample is the expected minimum of the from walk: the answer may contain more objects
        // than the sample e.g. the other values of a walked triple, but never less, and the overhead
        // is limited by the test tolerance of the configuration
        $t->subheader($ts . 'from walk of the example PV installation');
        $test_name = 'the phrase data contains every word of the sample';
        $vars = $phd->export_json($phr->id(), phrase_data_walks::FROM, false, $msg);
        $t->write_api_actual_file(test_files::PHRASE_DATA_PV_INSTALL, $vars);
        $names = $this->names($vars[json_fields::WORDS] ?? []);
        $t->assert_contains($test_name, $names, $this->names($sample[json_fields::WORDS]), $t::TIMEOUT_LIMIT_IMPORT);
        $t->assert_msg($test_name, $msg);
        $msg->reset();
        $test_name = '... with the description of each word';
        $descriptions = $this->descriptions($vars[json_fields::WORDS] ?? []);
        $t->assert_contains($test_name, $descriptions, $this->descriptions($sample[json_fields::WORDS]));
        $test_name = '... every triple';
        $trp_keys = $this->triple_keys($vars[json_fields::TRIPLES] ?? []);
        $t->assert_contains($test_name, $trp_keys, $this->triple_keys($sample[json_fields::TRIPLES]));
        $test_name = '... every formula';
        $frm_names = $this->names($vars[json_fields::FORMULAS] ?? []);
        $t->assert_contains($test_name, $frm_names, $this->names($sample[json_fields::FORMULAS]));
        $test_name = '... every source';
        $src_names = $this->names($vars[json_fields::SOURCES] ?? []);
        $t->assert_contains($test_name, $src_names, $this->names($sample[json_fields::SOURCES]));
        $test_name = '... every value';
        $val_keys = $this->value_keys($vars[json_fields::VALUES] ?? []);
        $t->assert_contains($test_name, $val_keys, $this->value_keys($sample[json_fields::VALUES]));
        $test_name = '... with the description of each value';
        $val_descriptions = $this->value_descriptions($vars[json_fields::VALUES] ?? []);
        $t->assert_contains($test_name, $val_descriptions, $this->value_descriptions($sample[json_fields::VALUES]));
        $test_name = '... and the database id of each object';
        $objects = $this->objects($vars);
        $without_id = $this->names_without_id($objects);
        $t->assert($test_name, $without_id, []);
        $test_name = '... and not much more objects than the sample';
        $max_count = $this->max_object_count($sample);
        $obj_count = count($objects);
        $t->assert_max($test_name, $max_count, $obj_count);
        $test_name = 'the header names the type of the data';
        $type = $vars[json_fields::TYPE_NAME] ?? '';
        $t->assert($test_name, $type, $lib->class_to_api_route(phrase_data::class));
        $test_name = '... and the requested phrase as the selection';
        $selection = $this->names($vars[json_fields::SELECTION] ?? []);
        $t->assert($test_name, $selection, [triple_names::EXAMPLE_PV_INSTALLATION]);
        // negative: the results are only added on request, because they are calculated data
        $test_name = '... without the results if they are not requested';
        $t->assert_key_not_exists($test_name, json_fields::RESULTS, $vars);

        $t->subheader($ts . 'value walk of the example PV installation');
        $test_name = 'the value walk contains every value of the sample that names the phrase';
        $val_vars = $phd->export_json($phr->id(), phrase_data_walks::VALUE, false, $msg);
        $val_keys = $this->value_keys($val_vars[json_fields::VALUES] ?? []);
        $phr_vals = $this->values_by_phrase($sample[json_fields::VALUES], triple_names::EXAMPLE_PV_INSTALLATION, true);
        $t->assert_contains($test_name, $val_keys, $this->value_keys($phr_vals), $t::TIMEOUT_LIMIT_IMPORT);
        $t->assert_msg($test_name, $msg);
        $msg->reset();
        $test_name = '... and only values that name the phrase';
        $walk_vals = $val_vars[json_fields::VALUES] ?? [];
        $other_vals = $this->values_by_phrase($walk_vals, triple_names::EXAMPLE_PV_INSTALLATION, false);
        $other_keys = $this->value_keys($other_vals);
        $t->assert($test_name, $other_keys, []);
        // negative: a value of the sample without the phrase e.g. a yield factor is not part of the value walk
        $test_name = '... but not the values of the sample without the phrase';
        $sample_other = $this->values_by_phrase($sample[json_fields::VALUES], triple_names::EXAMPLE_PV_INSTALLATION, false);
        $t->assert_contains_not($test_name, $val_keys, $this->value_keys($sample_other));

        $test_name = 'the phrase data contains the results if they are requested';
        $res_vars = $phd->export_json($phr->id(), phrase_data_walks::VALUE, true, $msg);
        $t->assert_msg($test_name, $msg, $t::TIMEOUT_LIMIT_IMPORT);
        $t->assert_key_exists($test_name, json_fields::RESULTS, $res_vars);
        $msg->reset();
        // a result is reloaded for the export by its own id, because its group id selects all results of the group
        $test_name = '... and each exported result names its phrases';
        $res_objs = $res_vars[json_fields::RESULTS] ?? [];
        $without_words = $this->count_without_field($res_objs, json_fields::WORDS);
        $t->assert($test_name, $without_words, 0);

        $test_name = 'the received api json is written next to the expected file';
        $actual_name = basename($t->api_actual_path(test_files::PHRASE_DATA_PV_INSTALL));
        $t->assert($test_name, $actual_name,
            test_files::PHRASE_DATA_PV_INSTALL_NAME . test_files::API_ACTUAL_SUFFIX . test_files::JSON);
        // negative: a file without extension gets only the suffix and no extension
        $test_name = '... and a file without extension gets no extension';
        $no_ext_path = test_paths::API_PHRASE_DATA_RES . test_files::PHRASE_DATA_PV_INSTALL_NAME;
        $no_ext_name = basename($t->api_actual_path($no_ext_path));
        $t->assert($test_name, $no_ext_name, test_files::PHRASE_DATA_PV_INSTALL_NAME . test_files::API_ACTUAL_SUFFIX);

        // negative: a phrase that does not exist has no data and the user is told why
        $test_name = 'the phrase data of a missing phrase is empty';
        $missing_vars = $phd->export_json(triple_names::MISSING_PHRASE_ID, phrase_data_walks::DEFAULT, false, $msg);
        $t->assert($test_name, $missing_vars, []);
        $test_name = '... and names the missing phrase id';
        $t->assert_msg_false($test_name, $msg, (string)triple_names::MISSING_PHRASE_ID);
        $msg->reset();
    }

    /**
     * @param array $sample the json of the expected minimal phrase data
     * @return int the max number of objects that the answer may have, the objects of the sample plus the test tolerance
     */
    private function max_object_count(array $sample): int
    {
        global $cfg;
        $tolerance = $cfg->get_by([words::TEST, words::TOLERANCE, words::PERCENT], def::FALLBACK_TEST_TOLERANCE);
        $count = count($this->objects($sample));
        return (int)floor($count * (1 + $tolerance));
    }

    /**
     * @param array $vars the json of the phrase data
     * @return array the words, triples, formulas, sources and values of the data in one list
     */
    private function objects(array $vars): array
    {
        $result = [];
        foreach (self::OBJECT_FIELDS as $fld) {
            $result = array_merge($result, $vars[$fld] ?? []);
        }
        return $result;
    }

    /**
     * @param array $objects the json of values
     * @return array the sorted phrase names and the description of each value with a description
     */
    private function value_descriptions(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            if (array_key_exists(json_fields::DESCRIPTION, $obj)) {
                $phr_names = $obj[json_fields::WORDS] ?? [];
                sort($phr_names);
                $result[] = implode(self::KEY_SEP, $phr_names) . self::KEY_SEP . $obj[json_fields::DESCRIPTION];
            }
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $values the json of values
     * @param string $phr_name the name of a phrase e.g. example PV installation
     * @param bool $with_phrase true for the values that name the phrase, false for the other values
     * @return array the values that name resp. do not name the phrase
     */
    private function values_by_phrase(array $values, string $phr_name, bool $with_phrase): array
    {
        $result = [];
        foreach ($values as $val) {
            if (in_array($phr_name, $val[json_fields::WORDS] ?? []) == $with_phrase) {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * @param array $objects the json of words, formulas, sources or of the selection
     * @return array the sorted names of the objects
     */
    private function names(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            $result[] = $obj[json_fields::NAME] ?? '';
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $objects the json of words
     * @return array the sorted name and description of each word with a description
     */
    private function descriptions(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            if (array_key_exists(json_fields::DESCRIPTION, $obj)) {
                $result[] = $obj[json_fields::NAME] . self::KEY_SEP . $obj[json_fields::DESCRIPTION];
            }
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $objects the json of triples
     * @return array the sorted from, verb and to of each triple, which is unique within the data
     */
    private function triple_keys(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            $result[] = implode(self::KEY_SEP, [
                $obj[json_fields::EX_FROM] ?? '',
                $obj[json_fields::EX_VERB] ?? '',
                $obj[json_fields::EX_TO] ?? ''
            ]);
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $objects the json of values
     * @return array the sorted phrase names of each value, which select the value
     */
    private function value_keys(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            $phr_names = $obj[json_fields::WORDS] ?? [];
            sort($phr_names);
            $result[] = implode(self::KEY_SEP, $phr_names);
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $objects the json of the objects of the data
     * @return array the name or the phrases of each object without a database id, to show which ones miss it
     */
    private function names_without_id(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            if (!$this->has_db_id($obj)) {
                $phr_names = $obj[json_fields::WORDS] ?? [];
                $result[] = $obj[json_fields::NAME] ?? implode(self::KEY_SEP, $phr_names);
            }
        }
        return $result;
    }

    /**
     * @param array $objects the json of the objects of the data e.g. the results
     * @param string $fld the json field that each object should have e.g. the words
     * @return int the number of objects without the field
     */
    private function count_without_field(array $objects, string $fld): int
    {
        $result = 0;
        foreach ($objects as $obj) {
            if (!array_key_exists($fld, $obj)) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @param array $obj the json of one object of the data
     * @return bool true if the object has a database id that is neither zero nor empty
     */
    private function has_db_id(array $obj): bool
    {
        // a value id can be a text key, so it is never compared with a number
        $id = $obj[json_fields::ID] ?? null;
        return ($id !== null and $id !== 0 and $id !== '');
    }

}
