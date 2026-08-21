<?php

/*

    test/php/utils/json_validation.php - check the import json files against the json rules
    ----------------------------------

    the checks of coding_rule_tests that judge an import json are collected here, so that
    they can be used by the coding rule test (which asserts that no file breaks a rule) and
    by test/json_validation.php (which lists every finding per file in docs/json_findings.md)

    the folders are scanned with a directory iterator and never with a list of file consts,
    so a json file added later is checked without changing any code


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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class json_validation
{

    // the folders with the import json files: the data of the pod and the data used for testing
    const array SCAN_PATHS = [
        'main data' => files::MESSAGE_PATH,
        'test data' => test_paths::IMPORT,
    ];

    // the folders whose json files are not zukunft.com import files, so that the import rules
    // do not apply: the files of the inconsistency tests break a rule on purpose to test the
    // import error handling and the wikidata cache holds the json as received from wikidata;
    // the converted json of test_paths::IMPORT_WIKIDATA_TO_IMPORT is checked as any other file
    const array SKIP_PATHS = [
        test_paths::IMPORT_INCONSISTENCY,
        test_paths::IMPORT_WIKIDATA_CACHE,
    ];

    // the classes that name a json file with a const, checked to tell a file that no const names
    // from one that is only missing in an import list
    const array FILE_CONST_CLASSES = [
        'files' => cfg_files::class,
        'test_files' => test_files::class,
    ];

    // the const arrays of the json files that test/test.php and test/test_full_load.php import
    // by the file name only, so that the message path is added by the importer
    const array LOAD_LISTS_MESSAGE = [
        'files::SYSTEM_DATA_FILES' => cfg_files::SYSTEM_DATA_FILES,
        'files::POD_CONFIG_FILES_DIRECT' => cfg_files::POD_CONFIG_FILES_DIRECT,
        'files::BASE_DATA_FILES' => cfg_files::BASE_DATA_FILES,
    ];

    // the const arrays of the json files that the same two scripts import by the complete path
    const array LOAD_LISTS_PATH = [
        'files::SAMPLE_VIEW_DATA_FILES' => cfg_files::SAMPLE_VIEW_DATA_FILES,
        'files::BASE_DATA_PATH_FILES' => cfg_files::BASE_DATA_PATH_FILES,
        'files::FULL_LOAD_FILES' => cfg_files::FULL_LOAD_FILES,
        'test_files::TEST_DATA_FILES' => test_files::TEST_DATA_FILES,
        'test_files::TEST_DATA_FILES_DIRECT' => test_files::TEST_DATA_FILES_DIRECT,
        'test_files::TEST_DATA_FILES_NOT_REVIEWED' => test_files::TEST_DATA_FILES_NOT_REVIEWED,
    ];

    // the qualifier that only repeats the default, see docs/llm/json_structure.md
    const string MEASURED_VALUE = 'measured value';

    // the max length of a lower case word that can be part of a proper noun e.g. "and" or "de"
    const int CONNECTOR_LENGTH = 3;

    // the max length of the sample of a finding, so that one finding stays on one report line
    // this limit is for the report only: a comparison always uses the complete value
    const int SAMPLE_LENGTH = 120;

    // the check names used as the section names of the report
    const string CHK_SYNTAX = 'not a valid json';
    const string CHK_MEASURED = 'measured value qualifier';
    const string CHK_VERB = 'verb not defined';
    const string CHK_FIELD = 'field not read by the import';
    const string CHK_WORD_SPACE = 'word with a space';
    const string CHK_FORWARD = 'triple uses a phrase that is defined later';
    const string CHK_TRIPLE_KEY = 'triple key used twice';
    const string CHK_NAME_DOUBLE = 'phrase name defined twice';
    const string CHK_CROSS_NAME = 'triple name with different keys across the main data';
    const string CHK_CROSS_KEY = 'triple key with different names across the main data';
    const string CHK_CROSS_DESC = 'description differs across the main data';
    const string CHK_NOT_LOADED = 'file not named by an import const array';
    const string CHK_VERSION = 'format version';

    // the top level lists whose entries the import merges by name, mapped to the namespace of
    // that name: a word and a triple are one phrase, while a source, a formula, a view and a
    // component each live in their own namespace, so the same name there is another database row
    const array NAMED_SECTIONS = [
        json_fields::WORDS => 'phrase',
        json_fields::TRIPLES => 'phrase',
        json_fields::SOURCES => 'source',
        json_fields::FORMULAS => 'formula',
        json_fields::VIEWS => 'view',
        json_fields::COMPONENTS => 'component',
    ];


    /*
     * report
     */

    /**
     * build the markdown report of all findings of all import json files
     *
     * @param bool $update true to write the format version and the data version into the files
     *                     that have no other finding, false to only report what would change
     * @return string the markdown content for docs/json_findings.md
     */
    function md(bool $update = false): string
    {
        $find_lst = $this->findings($update);
        $file_cnt = 0;
        foreach (self::SCAN_PATHS as $path) {
            $file_cnt += count($this->json_file_list($path));
        }

        $md_txt = '# Json findings' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'generated by test/json_validation.php - do not edit manually' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'the import json rules are described in docs/llm/json_structure.md;'
            . ' the checks are the same as in coding_rule_tests, but listed per file here' . "\n";
        $md_txt .= "\n";
        $md_txt .= $file_cnt . ' json files checked, '
            . $this->hit_count($find_lst) . ' findings'
            . ' (' . implode(' and ', self::SKIP_PATHS) . ' are not checked, because these files'
            . ' are no zukunft.com import json)' . "\n";

        $md_txt .= $this->section_md(self::CHK_SYNTAX, $find_lst,
            'a file that cannot be decoded is skipped by every other check,'
            . ' so it is imported as an empty message without any warning');
        $md_txt .= $this->section_md(self::CHK_MEASURED, $find_lst,
            'every value is assumed to be measured, so the qualifier only repeats the default'
            . ' while it lengthens the phrase group and needs a word or triple in every file'
            . ' that borrows it; only the deviation, the word "assumed", is worth recording');
        $md_txt .= $this->section_md(self::CHK_VERB, $find_lst,
            'the import resolves a verb by an exact name match and creates the verb when the name'
            . ' is unknown (see triple::import_mapper), so a typo silently grows the shared verb'
            . ' vocabulary; a file may propose its own verb in its "verbs" section');
        $md_txt .= $this->section_md(self::CHK_FIELD, $find_lst,
            'the import mapper reads a field by its json_fields const, so a field name that is'
            . ' not one of them is silently dropped: the data of the field is never imported and'
            . ' nothing is reported, because an unknown key is simply not looked at; checked are'
            . ' the top level keys and the keys of the entries of a top level list');
        $md_txt .= $this->section_md(self::CHK_WORD_SPACE, $find_lst,
            'a word is the smallest reusable unit of meaning, so a name with a space usually'
            . ' hides a composition that belongs in a triple: define the single word atoms and'
            . ' join them e.g. {from: "textbook", verb: "of", to: "economics"}; an external'
            . ' proper noun (Burkina Faso) keeps its space and is not listed here');
        $md_txt .= $this->section_md(self::CHK_FORWARD, $find_lst,
            'the import resolves the from and the to of a triple against the phrases known at'
            . ' that point of the file, so a building block that stands below its user is not'
            . ' found: the triple gets the phrase id 0, is reported as incomplete and the whole'
            . ' file is dropped; move the building block in front of its first user');
        $md_txt .= $this->section_md(self::CHK_TRIPLE_KEY, $find_lst,
            'two triples with the same from, verb and to get the same database key, so the'
            . ' second insert fails with a database duplicate key error that only names the ids;'
            . ' if two concepts share the key, add a building block triple in between'
            . ' (docs/llm/json_structure.md "from/verb/to is unique within an import")');
        $md_txt .= $this->section_md(self::CHK_NAME_DOUBLE, $find_lst,
            'a word and a triple must never share a name and a name must be defined only once,'
            . ' because the import resolves a phrase by its name, so the second definition is'
            . ' either dropped or replaces the first one silently');
        $md_txt .= $this->section_md(self::CHK_CROSS_NAME, $find_lst,
            'the main data files are imported into the same pod, so a triple name that maps to'
            . ' different from/verb/to keys in two files is one database row fought over by two'
            . ' concepts: the import of the later file cannot create its triple, the phrase id'
            . ' stays 0 and the insert fails with a database duplicate key error');
        $md_txt .= $this->section_md(self::CHK_CROSS_KEY, $find_lst,
            'the same from/verb/to key must have the same name in every main data file, because'
            . ' the key is unique in the database, so the second name cannot be created; a'
            . ' triple without a name is not compared, because it takes the existing name');
        $md_txt .= $this->section_md(self::CHK_CROSS_DESC, $find_lst,
            'the import merges an object by its name, so the second file describes the row that'
            . ' the first file has created: a system import runs with no_upd, compares the two'
            . ' descriptions and stops the whole file with "description is ... instead of ...";'
            . ' the description belongs in the home file (the one imported first, see'
            . ' docs/llm/json_structure.md) and every other file repeats the name without it');
        $md_txt .= $this->section_md(self::CHK_NOT_LOADED, $find_lst,
            'the import loads a json file only if a const of cfg/const/files.php or'
            . ' test/php/const/files.php names it and that const is part of one of the arrays'
            . ' used by test/test.php and test/test_full_load.php (' . $this->load_list_names()
            . '), so a file that is in none of them is never imported and never tested:'
            . ' add it to the matching list or delete the file');
        $md_txt .= $this->section_md(self::CHK_VERSION, $find_lst,
            '"version" is the version of the json format and not of the data, so it is matched'
            . ' against def::PRG_VERSION ("' . def::PRG_VERSION . '"); a file that is behind is'
            . ' raised to it, a file that is ahead is only reported, because its data may need a'
            . ' newer program; the data version is the version of the content of the file and is'
            . ' raised by the author with every data change');
        return $md_txt;
    }

    /**
     * check every json file of the scanned folders
     *
     * @param bool $update true to write the version fields of the files without another finding
     * @return array map of the check name and the scanned folder to the sorted findings, so that
     *               the folder is named once in the report and not in front of every file
     */
    function findings(bool $update = false): array
    {
        $result = [];
        foreach (self::SCAN_PATHS as $sec => $path) {
            foreach ($this->json_file_list($path) as $file_path) {
                $this->check_file($sec, $file_path, $result, $update);
            }
        }
        $this->cross_file_hits($result);
        $this->cross_description_hits($result);
        foreach ($result as $chk => $sec_lst) {
            foreach ($sec_lst as $sec => $hits) {
                sort($hits);
                $result[$chk][$sec] = $hits;
            }
        }
        return $result;
    }

    /**
     * check that the main data files agree on the triple names and keys
     *
     * only the main data, because these files are all imported into the same pod, while the
     * test data contains alternative versions of the same scenario on purpose
     *
     * @param array $find_lst (in/out) map of the check name and the folder to the findings
     * @return void
     */
    private function cross_file_hits(array &$find_lst): void
    {
        $sec = array_key_first(self::SCAN_PATHS);
        $name_keys = [];
        $key_names = [];
        foreach ($this->json_file_list(self::SCAN_PATHS[$sec]) as $file_path) {
            $json_array = json_decode(file_get_contents($file_path), true);
            if (is_array($json_array)) {
                foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
                    if (is_array($trp)) {
                        $key = '"' . ($trp[json_fields::EX_FROM] ?? '') . '" "'
                            . ($trp[json_fields::EX_VERB] ?? '') . '" "'
                            . ($trp[json_fields::EX_TO] ?? '') . '"';
                        $name_keys[$this->triple_name($trp)][$key] = basename($file_path);
                        // only an explicit name is a naming claim: a triple without a name
                        // simply takes the name that the key already has on import
                        $name = $trp[json_fields::NAME] ?? '';
                        if ($name != '') {
                            $key_names[$key][$name] = basename($file_path);
                        }
                    }
                }
            }
        }
        foreach ($name_keys as $name => $keys) {
            if (count($keys) > 1) {
                $dsp = [];
                foreach ($keys as $key => $file) {
                    $dsp[] = $key . ' (' . $file . ')';
                }
                $find_lst[self::CHK_CROSS_NAME][$sec][] = '"' . $name . '" - ' . implode(' vs ', $dsp);
            }
        }
        foreach ($key_names as $key => $names) {
            if (count($names) > 1) {
                $dsp = [];
                foreach ($names as $name => $file) {
                    $dsp[] = '"' . $name . '" (' . $file . ')';
                }
                $find_lst[self::CHK_CROSS_KEY][$sec][] = $key . ' - ' . implode(' vs ', $dsp);
            }
        }
    }

    /**
     * check that the main data files agree on the description of a name
     *
     * only the main data, because these files are all imported into the same pod, while the
     * test data contains alternative versions of the same scenario on purpose
     *
     * @param array $find_lst (in/out) map of the check name and the folder to the findings
     * @return void
     */
    private function cross_description_hits(array &$find_lst): void
    {
        $sec = array_key_first(self::SCAN_PATHS);
        $name_desc = [];
        foreach ($this->json_file_list(self::SCAN_PATHS[$sec]) as $file_path) {
            $json_array = json_decode(file_get_contents($file_path), true);
            if (is_array($json_array)) {
                $this->description_by_name($json_array, basename($file_path), $name_desc);
            }
        }
        foreach ($name_desc as $space => $space_lst) {
            foreach ($space_lst as $name => $desc_lst) {
                if (count($desc_lst) > 1) {
                    $dsp = [];
                    foreach ($desc_lst as $desc => $file) {
                        $dsp[] = '"' . $this->cut($desc) . '" (' . $file . ')';
                    }
                    $find_lst[self::CHK_CROSS_DESC][$sec][] = $space . ' "' . $name . '" - '
                        . implode(' vs ', $dsp);
                }
            }
        }
    }

    /**
     * add the descriptions of one file to the given map of the name to its descriptions
     *
     * an entry without a description is the correct re-declaration of a borrowed name, so only
     * a filled description is a claim and only two filled ones can disagree
     *
     * @param array $json_array the decoded json file
     * @param string $file_name the name of the file used to name the source of a finding
     * @param array $name_desc (in/out) map of namespace, name and description to the first file
     * @return void
     */
    private function description_by_name(array $json_array, string $file_name, array &$name_desc): void
    {
        foreach (self::NAMED_SECTIONS as $sec_name => $space) {
            foreach ($json_array[$sec_name] ?? [] as $entry) {
                if (is_array($entry)) {
                    $name = $entry[json_fields::NAME] ?? '';
                    $desc = trim($entry[json_fields::DESCRIPTION] ?? '');
                    if ($name != '' and $desc != '') {
                        $name_desc[$space][$name][$desc] ??= $file_name;
                    }
                }
            }
        }
    }

    /**
     * check one json file and add each finding to the given list
     *
     * @param string $sec the name of the scanned folder e.g. 'test data'
     * @param string $file_path the json file to check
     * @param array $find_lst (in/out) map of the check name and the folder to the findings
     * @param bool $update true to write the version fields if no other check has a finding
     * @return void
     */
    private function check_file(string $sec, string $file_path, array &$find_lst, bool $update): void
    {
        $name = basename($file_path);
        if (!in_array($file_path, $this->loaded_file_list())) {
            $const_name = $this->file_const_name($file_path);
            if ($const_name == '') {
                $find_lst[self::CHK_NOT_LOADED][$sec][] = $name . ' - named by no const';
            } else {
                $find_lst[self::CHK_NOT_LOADED][$sec][] = $name . ' - ' . $const_name
                    . ' is in no import list';
            }
        }
        $json_array = json_decode(file_get_contents($file_path), true);
        if (!is_array($json_array)) {
            $find_lst[self::CHK_SYNTAX][$sec][] = $name . ' - ' . json_last_error_msg();
        } else {
            $clean = true;
            foreach ($this->measured_value_hits($json_array) as $sec_name => $sample) {
                $find_lst[self::CHK_MEASURED][$sec][] = $name . ' (' . $sec_name . ') - ' . $sample;
                $clean = false;
            }
            foreach ($this->verb_undefined_hits($json_array) as $verb_name => $sample) {
                $find_lst[self::CHK_VERB][$sec][] = $name . ' - "' . $verb_name . '" in ' . $sample;
                $clean = false;
            }
            foreach ($this->field_unknown_hits($json_array) as $fld_name => $sample) {
                $find_lst[self::CHK_FIELD][$sec][] = $name . ' - ' . $fld_name . ' - ' . $sample;
                $clean = false;
            }
            foreach ($this->word_space_hits($json_array) as $wrd_name => $sample) {
                $find_lst[self::CHK_WORD_SPACE][$sec][] = $name . ' - ' . $wrd_name . ' - ' . $sample;
                $clean = false;
            }
            foreach ($this->forward_reference_hits($json_array) as $trp_name => $sample) {
                $find_lst[self::CHK_FORWARD][$sec][] = $name . ' - ' . $trp_name . ' - ' . $sample;
                $clean = false;
            }
            foreach ($this->triple_key_hits($json_array) as $key_name => $sample) {
                $find_lst[self::CHK_TRIPLE_KEY][$sec][] = $name . ' - ' . $key_name . ' - ' . $sample;
                $clean = false;
            }
            foreach ($this->name_double_hits($json_array) as $dbl_name => $sample) {
                $find_lst[self::CHK_NAME_DOUBLE][$sec][] = $name . ' - ' . $dbl_name . ' - ' . $sample;
                $clean = false;
            }
            // the version is checked last, because a file with a finding is not yet in the
            // format of this program version, so raising its version would hide the finding
            if ($clean) {
                $hit = $this->version_hit($file_path, $json_array, $update);
                if ($hit != '') {
                    $find_lst[self::CHK_VERSION][$sec][] = $name . ' - ' . $hit;
                }
            }
        }
    }


    /*
     * check
     */

    /**
     * the places where the "measured value" qualifier can hide: as a value or calc-validation
     * qualifier and as the word or triple that such a qualifier needs
     *
     * @param array $json_array the decoded json file
     * @return array map of the section that uses the qualifier to the first entry that uses it,
     *               so that the report can show what to fix and the caller can use the keys only
     */
    function measured_value_hits(array $json_array): array
    {
        $hits = [];
        foreach ($json_array[json_fields::VALUES] ?? [] as $val) {
            if (in_array(self::MEASURED_VALUE, $val[json_fields::WORDS] ?? [], true)) {
                $hits['value'] ??= $this->sample($val);
            }
        }
        foreach ($json_array[json_fields::CALC_VALIDATION] ?? [] as $calc) {
            foreach ([json_fields::CONTEXT, json_fields::WORDS] as $fld) {
                if (in_array(self::MEASURED_VALUE, $calc[$fld] ?? [], true)) {
                    $hits['calc-validation'] ??= $this->sample($calc);
                }
            }
        }
        foreach ($json_array[json_fields::WORDS] ?? [] as $wrd) {
            if (($wrd[json_fields::NAME] ?? '') == self::MEASURED_VALUE) {
                $hits['word'] ??= $this->sample($wrd);
            }
        }
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            foreach ([json_fields::NAME, json_fields::EX_FROM, json_fields::EX_TO] as $fld) {
                if (($trp[$fld] ?? '') == self::MEASURED_VALUE) {
                    $hits['triple'] ??= $this->sample($trp);
                }
            }
        }
        return $hits;
    }

    /**
     * the verbs that the triples of the given file use without a definition
     *
     * @param array $json_array the decoded json file
     * @return array map of the verb name that is neither in verbs.json nor proposed by the file
     *               to the first triple that uses it, see measured_value_hits
     */
    function verb_undefined_hits(array $json_array): array
    {
        $known = $this->verbs_known();
        // a file may propose its own verb, which stays private until an admin confirms it
        foreach ($json_array[json_fields::VERBS] ?? [] as $vrb) {
            $known[$vrb[json_fields::NAME] ?? ''] = true;
        }
        $unknown = [];
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            $name = $trp[json_fields::EX_VERB] ?? '';
            if ($name != '' and !array_key_exists($name, $known)) {
                $unknown[$name] ??= $this->sample($trp);
            }
        }
        return $unknown;
    }

    /**
     * the word names of the given file that contain a space
     *
     * a word is the most atomic text of the graph, so a composition like "economics textbook"
     * belongs in a triple that joins the single word atoms (docs/llm/json_structure.md);
     * the documented exception is not reported: an external proper noun that is written with
     * a space in the real world
     *
     * @param array $json_array the decoded json file
     * @return array map of the word name with a space to the first word entry that uses it
     */
    function word_space_hits(array $json_array): array
    {
        $hits = [];
        foreach ($json_array[json_fields::WORDS] ?? [] as $wrd) {
            if (is_array($wrd)) {
                $name = $wrd[json_fields::NAME] ?? '';
                if (is_string($name) and str_contains(trim($name), ' ')) {
                    if (!$this->is_proper_noun($name)) {
                        $hits['"' . $name . '"'] ??= $this->sample($wrd);
                    }
                }
            }
        }
        return $hits;
    }

    /**
     * the triples of the given file that use a phrase which the file defines only later
     *
     * the import maps the triples in the order of the file and resolves the from and the to
     * against the phrases known until then, so a forward reference gets the phrase id 0 and
     * the import of the complete file fails (docs/llm/json_structure.md)
     *
     * @param array $json_array the decoded json file
     * @return array map of the triple and the phrase used too early to the triple entry
     */
    function forward_reference_hits(array $json_array): array
    {
        // the words are imported before the triples, so all of them are known
        $known = [];
        foreach ($json_array[json_fields::WORDS] ?? [] as $wrd) {
            if (is_array($wrd)) {
                $known[$wrd[json_fields::NAME] ?? ''] = true;
            }
        }
        // every name that the file creates at all, because a phrase that the file never
        // defines is expected to come from a file that has been imported before
        $own = $known;
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            if (is_array($trp)) {
                $own[$this->triple_name($trp)] = true;
            }
        }
        $hits = [];
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            if (is_array($trp)) {
                foreach ([json_fields::EX_FROM, json_fields::EX_TO] as $fld) {
                    $ref = $trp[$fld] ?? '';
                    if ($ref != ''
                        and !array_key_exists($ref, $known)
                        and array_key_exists($ref, $own)) {
                        $hits['"' . $this->triple_name($trp) . '" ' . $fld . ' "' . $ref . '"']
                            ??= $this->sample($trp);
                    }
                }
                // only after the check, because a triple never uses itself
                $known[$this->triple_name($trp)] = true;
            }
        }
        return $hits;
    }

    /**
     * @param array $trp the triple entry of the import json
     * @return string the name of the triple, which is the given name or the generated name
     */
    private function triple_name(array $trp): string
    {
        $result = $trp[json_fields::NAME] ?? '';
        if ($result == '') {
            $result = trim(($trp[json_fields::EX_FROM] ?? '') . ' '
                . ($trp[json_fields::EX_VERB] ?? '') . ' '
                . ($trp[json_fields::EX_TO] ?? ''));
        }
        return $result;
    }

    /**
     * the from, verb and to combinations that the given file uses for more than one triple
     *
     * the combination is the database key of a triple, so a double gets a duplicate key error
     * from the database that only names the ids; reporting it here names the entries instead
     *
     * @param array $json_array the decoded json file
     * @return array map of the double key to the second triple entry that uses it
     */
    function triple_key_hits(array $json_array): array
    {
        $keys = [];
        $hits = [];
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            if (is_array($trp)) {
                $key = '"' . ($trp[json_fields::EX_FROM] ?? '') . '" "'
                    . ($trp[json_fields::EX_VERB] ?? '') . '" "'
                    . ($trp[json_fields::EX_TO] ?? '') . '"';
                if (array_key_exists($key, $keys)) {
                    $hits[$key] ??= $this->sample($trp);
                }
                $keys[$key] = true;
            }
        }
        return $hits;
    }

    /**
     * the phrase names that the given file defines more than once
     *
     * the import resolves a phrase by its name, so a name that is defined as a word and as a
     * triple or by two triples with different keys is silently reduced to one of them
     *
     * @param array $json_array the decoded json file
     * @return array map of the double name to the second entry that defines it
     */
    function name_double_hits(array $json_array): array
    {
        $names = [];
        $hits = [];
        foreach ($json_array[json_fields::WORDS] ?? [] as $wrd) {
            if (is_array($wrd)) {
                $name = $wrd[json_fields::NAME] ?? '';
                if ($name != '' and array_key_exists($name, $names)) {
                    $hits['"' . $name . '"'] ??= $this->sample($wrd);
                }
                $names[$name] = true;
            }
        }
        foreach ($json_array[json_fields::TRIPLES] ?? [] as $trp) {
            if (is_array($trp)) {
                $name = $this->triple_name($trp);
                if ($name != '' and array_key_exists($name, $names)) {
                    $hits['"' . $name . '"'] ??= $this->sample($trp);
                }
                $names[$name] = true;
            }
        }
        return $hits;
    }

    /**
     * a name is treated as an external proper noun if it is written like one: it starts with an
     * upper case letter and every following word is upper case too, a short connector ("and",
     * "de"), a name with an apostrophe ("d'Ivoire") or a parenthetical of an official name
     * ("Cocos (Keeling) Islands"); a lower case start like "second (time)" is never a proper noun
     *
     * @param string $name the word name to judge
     * @return bool true if the space of the name is expected
     */
    private function is_proper_noun(string $name): bool
    {
        $tok_lst = explode(' ', trim($name));
        $result = $this->starts_upper(array_shift($tok_lst));
        foreach ($tok_lst as $tok) {
            if (!$this->starts_upper($tok)
                and strlen($tok) > self::CONNECTOR_LENGTH
                and !str_contains($tok, "'")
                and !str_starts_with($tok, '(')) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * the first byte and not mb_substr, so that the check also runs where mbstring is missing;
     * a name that starts with a multi byte char is treated as upper case, which is the safe
     * default for a proper noun like "Ãlysee"
     *
     * @param string $txt the text to check
     * @return bool true if the text starts with an upper case letter
     */
    private function starts_upper(string $txt): bool
    {
        $chr = substr($txt, 0, 1);
        return $chr != '' and $chr == strtoupper($chr);
    }

    /**
     * check the format version and the data version of a file that has no other finding
     *
     * "version" names the version of the json format, so it is matched against the program
     * version; a file that is behind is raised, because the format has been migrated with the
     * program, and a file that is ahead is only reported, because its data may need a program
     * that is not yet installed (this is what msg_id::IMPORT_VERSION_NEWER warns about)
     *
     * @param string $file_path the json file to check
     * @param array $json_array the decoded json file
     * @param bool $update true to write the missing or outdated versions into the file
     * @return string the finding or an empty string if both versions are as expected
     */
    private function version_hit(string $file_path, array $json_array, bool $update): string
    {
        $lib = new library();
        $result = '';
        $version = $json_array[json_fields::VERSION] ?? '';
        $data_version = $json_array[json_fields::DATA_VERSION] ?? '';
        if ($version == '') {
            // where to add the field cannot be guessed, because the header may be missing at all
            $result = 'no "' . json_fields::VERSION . '" field';
        } elseif ($lib->prg_version_is_newer($version)) {
            $result = 'format version "' . $version . '" is newer than the program version "'
                . def::PRG_VERSION . '", so it is not changed';
        } else {
            if ($version != def::PRG_VERSION) {
                $result = 'format version "' . $version . '" raised to "' . def::PRG_VERSION . '"';
            }
            if ($data_version == '') {
                $add_txt = 'no "' . json_fields::DATA_VERSION . '" field, so "'
                    . def::DATA_VERSION_INIT . '" added';
                $result = $result == '' ? $add_txt : $result . ', ' . $add_txt;
            }
            if ($result != '' and $update) {
                $this->version_update($file_path, $version, $data_version);
            } elseif ($result != '') {
                $result .= ' (dry run)';
            }
        }
        return $result;
    }

    /**
     * write the program version as the format version into the file and add the initial data
     * version if the file has none
     *
     * only the version lines are replaced and never the complete json, because rewriting the
     * file with json_encode would reformat every line and destroy the review of the real change
     *
     * @param string $file_path the json file to update
     * @param string $version the format version that the file has now
     * @param string $data_version the data version that the file has now or an empty string
     * @return void
     */
    private function version_update(string $file_path, string $version, string $data_version): void
    {
        $txt = file_get_contents($file_path);
        $old_line = '"' . json_fields::VERSION . '": "' . $version . '"';
        $new_line = '"' . json_fields::VERSION . '": "' . def::PRG_VERSION . '"';
        if ($data_version == '') {
            $new_line .= ',' . "\n" . $this->line_indent($txt, $old_line)
                . '"' . json_fields::DATA_VERSION . '": "' . def::DATA_VERSION_INIT . '"';
        }
        // only the first hit, because a value or a source of the file may name a version too
        $pos = strpos($txt, $old_line . ',');
        if ($pos !== false) {
            $txt = substr_replace($txt, $new_line . ',', $pos, strlen($old_line) + 1);
            file_put_contents($file_path, $txt);
        }
    }

    /**
     * @param string $txt the complete file text
     * @param string $line the line content to find
     * @return string the whitespace in front of the given line content
     */
    private function line_indent(string $txt, string $line): string
    {
        $lib = new library();
        $before = $lib->str_left_of($txt, $line);
        $last_line = substr($before, strrpos($before, "\n") + 1);
        return str_repeat(' ', strlen($last_line));
    }

    /**
     * the first entry that breaks a rule, short enough for one report line
     *
     * @param array $entry the json entry e.g. the value or the triple that has the finding
     * @return string the entry as a json string, cut after SAMPLE_LENGTH chars
     */
    private function sample(array $entry): string
    {
        return $this->cut(json_encode($entry));
    }

    /**
     * the given text short enough for one report line
     *
     * the cut is used for the display of a finding only, never for a comparison and never as
     * the key of a map, because two texts that differ only behind the cut must still be found
     * (see docs/llm/structure.md "100% correct - never a shortcut")
     *
     * @param string $txt the text to show in the report
     * @return string the text cut after SAMPLE_LENGTH chars
     */
    private function cut(string $txt): string
    {
        $lib = new library();
        $result = $txt;
        if (strlen($result) > self::SAMPLE_LENGTH) {
            $result = $lib->str_left($result, self::SAMPLE_LENGTH) . ' ...';
        }
        return $result;
    }

    /**
     * the fields of the given file that the import mapper does not read
     *
     * every field that an import mapper reads is a const of json_fields, so a key that is not
     * one of them is dropped without any message: array_key_exists simply never matches it
     *
     * checked are the top level keys and the keys of the entries of a top level list, because
     * that is where the mappers read named fields; a deeper structure can use data as keys
     * (e.g. a compact value list), which would create false positives
     *
     * @param array $json_array the decoded json file
     * @return array map of the section and the unknown field to the first entry that uses it
     */
    function field_unknown_hits(array $json_array): array
    {
        $known = $this->fields_known();
        $hits = [];
        foreach ($json_array as $key => $value) {
            if (!array_key_exists($key, $known)) {
                $hits['top level: "' . $key . '"'] ??= $this->sample([$key => $value]);
            }
            if (is_array($value)) {
                $this->entry_field_hits($key, $value, $known, $hits);
            }
        }
        return $hits;
    }

    /**
     * add the unknown fields of the entries of one top level list to the given hit list
     *
     * @param string $sec_name the name of the top level list e.g. 'values'
     * @param array $entry_lst the entries of the top level list
     * @param array $known the field names that an import mapper reads as the keys
     * @param array $hits (in/out) map of the section and the unknown field to the first sample
     * @return void
     */
    private function entry_field_hits(
        string $sec_name,
        array  $entry_lst,
        array  $known,
        array  &$hits
    ): void
    {
        foreach ($entry_lst as $entry) {
            if (is_array($entry)) {
                foreach ($entry as $fld => $val) {
                    if (is_string($fld) and !array_key_exists($fld, $known)) {
                        $hits[$sec_name . ': "' . $fld . '"'] ??= $this->sample($entry);
                    }
                }
            }
        }
    }

    /**
     * @return array the field names that an import mapper can read as the keys
     */
    private function fields_known(): array
    {
        $result = [];
        $fld_lst = new ReflectionClass(json_fields::class)->getConstants();
        foreach ($fld_lst as $fld) {
            if (is_string($fld)) {
                $result[$fld] = true;
            }
        }
        return $result;
    }

    /**
     * @return array the verb names of src/main/resources/verbs.json as the keys
     */
    private function verbs_known(): array
    {
        $result = [];
        $verbs_json = json_decode(file_get_contents(files::VERBS), true) ?? [];
        foreach ($verbs_json[json_fields::VERBS] ?? [] as $vrb) {
            $result[$vrb[json_fields::NAME]] = true;
        }
        return $result;
    }


    /*
     * internal
     */

    /**
     * build the markdown section of one check with one sub section per scanned folder
     *
     * the folder is the sub section headline and not a prefix of every line, so that a file
     * name is not lost in a repeated 'main data: ' and a folder without a finding is silent
     *
     * @param string $chk the check name e.g. 'verb not defined'
     * @param array $find_lst map of the check name and the folder to its findings
     * @param string $explain the one line why the findings of this check matter
     * @return string the markdown section or an empty string if the check has no finding
     */
    private function section_md(string $chk, array $find_lst, string $explain): string
    {
        $sec_txt = '';
        $sec_lst = $find_lst[$chk] ?? [];
        if ($sec_lst != []) {
            $sec_txt = "\n" . '## ' . $chk . "\n" . "\n" . $explain . "\n";
            // the scan order and not the hit order, so that the report is stable
            foreach (array_keys(self::SCAN_PATHS) as $sec) {
                $sec_txt .= $this->folder_md($sec, $sec_lst[$sec] ?? []);
            }
        }
        return $sec_txt;
    }

    /**
     * @param string $sec the name of the scanned folder e.g. 'test data'
     * @param array $hits the findings of this check in this folder
     * @return string the markdown sub section or an empty string if the folder has no finding
     */
    private function folder_md(string $sec, array $hits): string
    {
        $sec_txt = '';
        if ($hits != []) {
            $row_txt = '';
            foreach ($hits as $hit) {
                $row_txt .= $hit . "\n";
            }
            $sec_txt = "\n" . '### ' . $sec . "\n" . "\n"
                . '```' . "\n" . $row_txt . '```' . "\n";
        }
        return $sec_txt;
    }

    /**
     * @param array $find_lst map of the check name to its findings
     * @return int the total number of findings of all checks
     */
    function hit_count(array $find_lst): int
    {
        $result = 0;
        foreach ($find_lst as $sec_lst) {
            foreach ($sec_lst as $hits) {
                $result += count($hits);
            }
        }
        return $result;
    }

    /**
     * the json files that an import const array names, so that a scan of the folders can tell
     * which file no import loads; the message path is added to the lists that name the file only
     *
     * @return array the path of every json file that test/test.php or test/test_full_load.php loads
     */
    function loaded_file_list(): array
    {
        $result = [];
        foreach (self::LOAD_LISTS_MESSAGE as $file_lst) {
            foreach ($file_lst as $file_name) {
                $result[] = files::MESSAGE_PATH . $file_name;
            }
        }
        foreach (self::LOAD_LISTS_PATH as $file_lst) {
            $result = array_merge($result, $file_lst);
        }
        return $result;
    }

    /**
     * the const that names the given json file, so that the report can tell a file that no
     * const names at all from one that only misses the entry in an import list
     *
     * @param string $file_path the path of the json file as the folder scan has returned it
     * @return string the class and the name of the const or an empty string if none names the file
     */
    private function file_const_name(string $file_path): string
    {
        $result = '';
        foreach (self::FILE_CONST_CLASSES as $class_name => $class) {
            $ref = new ReflectionClass($class);
            foreach ($ref->getConstants() as $const_name => $value) {
                if (is_string($value) and $value != '') {
                    if ($this->const_names_file($value, $file_path)) {
                        $result = $class_name . '::' . $const_name;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * true if the given const value names the given json file
     *
     * a const holds the complete path, the path below the message folder or just the file name,
     * with or without the json extension, and the update and the undo file of an import test are
     * composed of the base const and an extension const, so all these forms are matched
     *
     * @param string $value the value of a file name const
     * @param string $file_path the path of the json file as the folder scan has returned it
     * @return bool false if the const value names another file
     */
    private function const_names_file(string $value, string $file_path): bool
    {
        $result = false;
        $base = $value;
        if (str_ends_with($base, files::JSON)) {
            $base = substr($base, 0, -strlen(files::JSON));
        }
        $names = [
            $base . files::JSON,
            $base . test_files::IMPORT_UPDATE_EXT . files::JSON,
            $base . test_files::IMPORT_UNDO_EXT . files::JSON
        ];
        foreach ($names as $name) {
            if ($file_path == $name) {
                $result = true;
            }
            if (str_ends_with($file_path, DIRECTORY_SEPARATOR . $name)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return string the const names of the import lists for the report description
     */
    private function load_list_names(): string
    {
        $names = array_keys(self::LOAD_LISTS_MESSAGE);
        $names = array_merge($names, array_keys(self::LOAD_LISTS_PATH));
        return implode(', ', $names);
    }

    /**
     * @param string $path the folder to scan for json import files
     * @return array the path of every json file below the given folder except the files of
     *               self::SKIP_PATHS, which are no zukunft.com import json
     */
    function json_file_list(string $path): array
    {
        $result = [];
        $dir_iterator = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($dir_iterator) as $file) {
            if (str_ends_with($file->getFilename(), files::JSON)
                && !$this->is_skipped($file->getPathname())) {
                $result[] = $file->getPathname();
            }
        }
        sort($result);
        return $result;
    }

    /**
     * @param string $file_path the path of a json file as the folder scan has returned it
     * @return bool true if the file is in a folder that is not checked
     */
    private function is_skipped(string $file_path): bool
    {
        $result = false;
        foreach (self::SKIP_PATHS as $skip_path) {
            if (str_starts_with($file_path, $skip_path)) {
                $result = true;
            }
        }
        return $result;
    }

}