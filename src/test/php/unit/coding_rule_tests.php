<?php

/*

    test/unit/system.php - unit testing of the system functions
    -------------------

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::UTILS . 'code_test_coverage.php';
include_once test_paths::UTILS . 'code_user_message_exceptions.php';
include_once test_paths::UTILS . 'json_validation.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\utils\code_test_coverage;
use Zukunft\ZukunftCom\test\php\utils\code_user_message_exceptions;
use Zukunft\ZukunftCom\test\php\utils\json_validation;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use ReflectionClass;

class coding_rule_tests
{

    // the app areas that can use a global var (shown in docs/code_object_name_exceptions.md)
    private const string AREA_BACKEND = 'backend';
    private const string AREA_FRONTEND = 'frontend';
    private const string AREA_BOTH = 'both';

    // the max number of chars of a line of docs/code_functions_all.md; a longer line is wrapped,
    // never cut, because the file is the complete list of the function order errors and a cut line
    // would hide the function that has to be moved (see md_wrap)
    const int MD_MAX_LINE_LEN = 120;

    // the continuation lines of a wrapped md line start with this marker plus the indent of the
    // line that is continued, so that a wrapped line is not read as an own entry of the tree
    const string MD_WRAP_MARKER = '    ';

    // use path that does not need to be included
    const array PATH_NO_INCLUDE = [
        'PgSql\Connection',
        'Random\RandomException',
        'Zukunft\ZukunftCom\main\php\cfg\const\paths',
        'Zukunft\ZukunftCom\main\php\web\const\paths',
        'Zukunft\ZukunftCom\test\php\const\paths'
    ];

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'code_rule->';
        $t->resource_path = 'db/system/';
        $t->usr_system = $t->user_system();

        // start the test section (ts)
        $ts = 'unit code rules ';
        $t->header($ts);


        /*
         * system consistency SQL creation tests
         */

        $t->subheader($ts . 'class tree');

        // building the class / function tree over the whole source takes clearly longer than a
        // normal unit function, so a generous timeout is used to avoid a false timeout
        $test_name = 'check that the docs with all objects is updated';
        $md_txt = $this->php_class_tree();
        $obj_upd = $t->assert_file($test_name, $md_txt, test_files::DOCS_OBJECTS, '', '', $t::TIMEOUT_LIMIT_LONG);

        $test_name = 'check that the docs with all function is updated';
        $md_txt = $this->php_function_tree();
        $fnc_upd = $t->assert_file($test_name, $md_txt, test_files::DOCS_FUNCTIONS, '', '', $t::TIMEOUT_LIMIT_LONG);

        $test_name = 'check that the docs with the unit test coverage is updated';
        $md_txt = new code_test_coverage()->md();
        $t->assert_file($test_name, $md_txt, test_files::DOCS_TEST_COVERAGE, '', '', $t::TIMEOUT_LIMIT_LONG);

        $this->php_class_name_check($t);

        $this->php_include_tests($t, paths::MODEL);
        // TODO Prio 1 activate but take into account the const
        //$this->php_include_tests($t, paths::API);
        $this->php_include_tests($t, paths::WEB);
        $this->php_include_tests($t, test_paths::CREATE);

        $this->php_cfg_no_web_tests($t);

        $t->subheader($ts . 'frontend globals');
        $this->php_web_only_allowed_globals_tests($t);

        $t->subheader($ts . 'frontend config cache');
        $this->php_web_config_from_cache_tests($t);

        $t->subheader($ts . 'requesting user on the message');
        $this->php_user_message_param_shadow_tests($t);
        $this->php_user_message_user_write_tests($t);
        $this->php_user_message_creation_tests($t);

        $t->subheader($ts . 'backend globals');
        $this->php_cfg_only_allowed_globals_tests($t);

        $t->subheader($ts . 'config.yaml consistency');
        $this->config_yaml_word_triple_tests($t);

        $t->subheader($ts . 'import json consistency');
        // TODO Prio 3 maybe switch it on as a warning
        //$this->json_no_measured_value_tests($t);
        $this->json_view_component_defined_tests($t);
        $this->json_section_covered_tests($t);

        $t->subheader($ts . 'verb consistency');
        $this->verb_group_tests($t);
        $this->json_verb_defined_tests($t);

        $t->subheader($ts . 'path consts');
        $this->php_path_const_tests($t);

    }

    /**
     * verify that every word / triple key used in src/main/resources/config.yaml has a matching
     * string constant in either src/main/php/shared/const/words.php or
     * src/main/php/shared/const/triples.php; structural meta keys that are read directly by
     * the config loader (tooltip-comment, sys-conf-value, source-name, source-description,
     * pod-user-config) are skipped because they are not domain words
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function config_yaml_word_triple_tests(test_cleanup $t): void
    {
        // meta keys consumed directly by the config loader; they are structural, not domain words
        $meta_keys = [
            words::TOOLTIP_COMMENT,
            words::SYS_CONF_VALUE,
            words::SYS_CONF_SOURCE,
            words::SYS_CONF_SOURCE_COM,
            words::SYS_CONF_USER,
        ];

        // collect every yaml key referenced in config.yaml (deduplicated, sorted for stable output)
        $yaml_tree = yaml_parse_file(files::CONFIG_YAML);
        $yaml_keys = [];
        if (is_array($yaml_tree)) {
            $this->collect_yaml_keys($yaml_tree, $yaml_keys, $meta_keys);
        }

        // collect every string constant value defined by words and triples for an O(1) lookup
        $word_vals = array_values(array_filter(
            new ReflectionClass(words::class)->getConstants(), 'is_string'));
        $triple_vals = array_values(array_filter(
            new ReflectionClass(triples::class)->getConstants(), 'is_string'));
        $known_names = array_flip(array_merge($word_vals, $triple_vals));

        $missing = [];
        foreach (array_keys($yaml_keys) as $key) {
            if (!array_key_exists($key, $known_names)) {
                $missing[] = $key;
            }
        }
        sort($missing);

        $test_name = 'every config.yaml key has a const in words.php or triples.php';
        $t->assert($test_name, implode(', ', $missing), '');
    }

    /**
     * verify that no import json of src/main/resources/messages adds a "measured value" qualifier:
     * every value is assumed to be measured, so the qualifier only repeats the default while it
     * lengthens the phrase group and needs a word or triple in every file that borrows it;
     * only the deviation, the word "assumed", is worth recording (see docs/llm/json_structure.md)
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function json_no_measured_value_tests(test_cleanup $t): void
    {
        // the hit detection is shared with test/json_validation.php, which lists the findings
        // of the test imports too, while this rule check judges only the import data
        $chk = new json_validation();
        $names = [];
        foreach ($chk->json_file_list(files::MESSAGE_PATH) as $path) {
            $json_array = json_decode(file_get_contents($path), true);
            if (is_array($json_array)) {
                // only the keys, because the assertion names the file and not the sample entry
                foreach (array_keys($chk->measured_value_hits($json_array)) as $hit) {
                    $names[] = basename($path) . ' (' . $hit . ')';
                }
            }
        }
        sort($names);

        $test_name = 'no import json adds a "' . json_validation::MEASURED_VALUE . '" qualifier';
        $t->assert($test_name, implode(', ', $names), '');
    }

    /**
     * verify that every verb a group array of verbs.php names is defined in verbs.json: the groups
     * (CATEGORY_VERBS, PROPERTY_VERBS, SYNONYM_VERBS, ARGUMENT_VERBS) are the coded functionality of
     * a verb, so a group entry without a verb row is a predicate that silently never matches
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function verb_group_tests(test_cleanup $t): void
    {
        $known = [];
        foreach ($this->verbs_json()[json_fields::VERBS] ?? [] as $vrb) {
            $known[$vrb[json_fields::CODE_ID]] = true;
        }
        $groups = [
            'CATEGORY_VERBS' => verbs::CATEGORY_VERBS,
            'PROPERTY_VERBS' => verbs::PROPERTY_VERBS,
            'SYNONYM_VERBS' => verbs::SYNONYM_VERBS,
            'ARGUMENT_VERBS' => verbs::ARGUMENT_VERBS,
        ];
        $missing = [];
        foreach ($groups as $name => $group) {
            foreach ($group as [$code_id, $direction]) {
                if (!array_key_exists($code_id, $known)) {
                    $missing[] = $name . ': ' . $code_id;
                }
            }
        }
        sort($missing);

        $test_name = 'every verb of a verbs.php group is defined in verbs.json';
        $t->assert($test_name, implode(', ', $missing), '');
    }

    /**
     * verify that every verb used by an import json is defined in verbs.json or proposed by the file
     * itself: the import resolves a verb by an exact name match and creates the verb when the name is
     * unknown (see triple::import_mapper), so a typo silently grows the shared verb vocabulary
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function json_verb_defined_tests(test_cleanup $t): void
    {
        // the hit detection is shared with test/json_validation.php, see the measured value check
        $chk = new json_validation();
        $names = [];
        foreach ($chk->json_file_list(files::MESSAGE_PATH) as $path) {
            $json_array = json_decode(file_get_contents($path), true);
            if (is_array($json_array)) {
                // only the keys, see the measured value check
                foreach (array_keys($chk->verb_undefined_hits($json_array)) as $hit) {
                    $names[] = basename($path) . ': ' . $hit;
                }
            }
        }
        sort($names);

        $test_name = 'every verb used by an import json is defined in verbs.json';
        $t->assert($test_name, implode(', ', $names), '');
    }

    /**
     * verify that the field check of json_validation covers every top level section of the import:
     * the check reads the allowed fields out of the php source of the mapper of the section, so it
     * needs to know the mapper class of each section, and a section that the import has added
     * without an entry here would be checked against no field at all
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function json_section_covered_tests(test_cleanup $t): void
    {
        // the difference detection is shared with test/json_validation.php, which lists it as a
        // finding, while this rule check lets the test run fail as soon as the two drift apart
        $chk = new json_validation();
        $names = $chk->section_check_list();
        sort($names);

        $test_name = 'every import section has a mapper class in ' . json_validation::class;
        $t->assert($test_name, implode(', ', $names), '');
    }

    /**
     * verify that every component used by a view of a view import json is defined in the components
     * block of the same file, because the import resolves a component of a view by its name within
     * the file: a view that uses a component defined only in another file stops the import of the
     * complete file with 'component with name ... missing', so e.g. base_views.json repeats the
     * definition of the system components that its views use (see docs/llm/json_views.md)
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function json_view_component_defined_tests(test_cleanup $t): void
    {
        $names = [];
        foreach ([files::SYSTEM_VIEWS, files::BASE_VIEWS] as $path) {
            $json_array = json_decode(file_get_contents($path), true);
            if (is_array($json_array)) {
                $defined = [];
                foreach ($json_array[json_fields::COMPONENTS] ?? [] as $cmp) {
                    $defined[] = $cmp[json_fields::NAME] ?? '';
                }
                foreach ($json_array[json_fields::VIEWS] ?? [] as $msk) {
                    foreach ($msk[json_fields::COMPONENTS] ?? [] as $cmp) {
                        $name = $cmp[json_fields::NAME] ?? '';
                        if ($name != '' and !in_array($name, $defined, true)) {
                            $names[] = basename($path) . ': ' . $name
                                . ' used by ' . ($msk[json_fields::CODE_ID] ?? '');
                        }
                    }
                }
            }
        }
        sort($names);

        $test_name = 'every component used by a view is defined in the same import json';
        $t->assert($test_name, implode(', ', $names), '');
    }

    /**
     * @return array the decoded src/main/resources/verbs.json
     */
    private function verbs_json(): array
    {
        return json_decode(file_get_contents(files::VERBS), true) ?? [];
    }

    /**
     * recursively walk a parsed yaml subtree and collect every string key into $keys (keyed by
     * the key value so the caller gets unique keys for free); meta keys are skipped
     *
     * @param array $data the parsed yaml subtree
     * @param array $keys (in/out) accumulator keyed by yaml key for uniqueness
     * @param array $meta_keys keys to skip because they are structural, not domain words
     * @return void
     */
    private function collect_yaml_keys(array $data, array &$keys, array $meta_keys): void
    {
        foreach ($data as $key => $value) {
            if (is_string($key) and !in_array($key, $meta_keys, true)) {
                $keys[$key] = true;
            }
            if (is_array($value)) {
                $this->collect_yaml_keys($value, $keys, $meta_keys);
            }
        }
    }

    function php_class_tree(): string
    {
        $test_name = 'c';
        $class_lst = [];
        $class_lst = array_merge($class_lst, $this->php_classes(paths::MODEL, paths::MODEL_SECTION));
        $class_lst = array_merge($class_lst, $this->php_classes(paths::SHARED, paths::SHARED_SECTION));
        $class_lst = array_merge($class_lst, $this->php_classes(paths::WEB, paths::WEB_SECTION));
        $class_tree = $this->classTree($class_lst);
        $class_parents = $this->classTreeParents($class_lst);
        return $this->php_class_list_to_md($class_tree);
    }

    /**
     * check that objects are created with the suggested var name of their class
     * and keep docs/code_object_name_exceptions.md up to date with the deviations
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_class_name_check(test_cleanup $t): void
    {
        // scanning the whole source for the name exceptions takes clearly longer than a normal
        // unit function, so a generous timeout is used to avoid a false timeout
        $test_name = 'check that the object name exceptions doc is updated';
        $md_txt = $this->php_class_name_exceptions();
        $t->assert_file($test_name, $md_txt, test_files::DOCS_NAME_EXCEPTIONS, '', '', $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * build the markdown report of object creations that do not use the suggested var name
     * the first section lists classes that have a suggested var name but are created with
     * another name (e.g. 'word: $wrd_ge, $wrd_zh'); the second section lists the classes
     * without a suggested var name and the names used to create them; both sorted by class
     * and var name
     *
     * @return string the markdown content for docs/code_object_name_exceptions.md
     */
    private function php_class_name_exceptions(): string
    {
        $glb_lst = [];
        $glb_area_lst = [];
        $glb_add_lst = [];
        $this->php_global_vars(paths::PHP_LIB, $glb_lst, $glb_area_lst, $glb_add_lst);
        $this->php_global_vars(TEST_PHP_PATH, $glb_lst, $glb_area_lst, $glb_add_lst);
        $this->php_global_vars(paths::API, $glb_lst, $glb_area_lst, $glb_add_lst);
        $this->php_global_vars(html_paths::HTTP, $glb_lst, $glb_area_lst, $glb_add_lst);
        $this->php_global_vars(html_paths::HTTP_OLD, $glb_lst, $glb_area_lst, $glb_add_lst);
        ksort($glb_lst, SORT_STRING);

        $suggested = $this->php_suggested_var_names(paths::PHP_LIB);
        $usage = [];
        $this->php_collect_new_usage(paths::PHP_LIB, $usage);
        $this->php_collect_new_usage(TEST_PHP_PATH, $usage);
        ksort($usage, SORT_STRING);

        $exceptions = [];
        $no_suggestion = [];
        foreach ($usage as $class => $vars) {
            sort($vars, SORT_STRING);
            if (array_key_exists($class, $suggested)) {
                $deviations = [];
                foreach ($vars as $var) {
                    if ($var != $suggested[$class]) {
                        $deviations[] = '$' . $var;
                    }
                }
                if ($deviations != []) {
                    $exceptions[] = $class . ': ' . implode(', ', $deviations);
                }
            } else {
                $names = [];
                foreach ($vars as $var) {
                    $names[] = '$' . $var;
                }
                $no_suggestion[] = $class . ': ' . implode(', ', $names);
            }
        }

        $lines = [];
        $lines[] = '# Object name exceptions';
        $lines[] = '';
        $lines[] = 'generated by coding_rule_tests::php_class_name_check - do not edit manually';
        $lines[] = '';
        $lines[] = '## global vars';
        foreach ([self::AREA_BACKEND, self::AREA_FRONTEND, self::AREA_BOTH] as $area) {
            $area_lines = [];
            foreach ($glb_lst as $var => $des_lst) {
                if ($this->php_global_var_area_group($glb_area_lst[$var]) == $area) {
                    $des_txt = $des_lst == [] ? 'description missing' : implode(' or ', $des_lst);
                    // for a var that is not clearly frontend or backend show the classes
                    // resp. scripts of the shared code that also declare it
                    $add_lst = $glb_add_lst[$var] ?? [];
                    if ($area == self::AREA_BOTH and $add_lst != []) {
                        sort($add_lst, SORT_STRING);
                        $des_txt .= ' (additional in ' . implode(', ', $add_lst) . ')';
                    }
                    $area_lines[] = '$' . $var . ': ' . $des_txt;
                }
            }
            if ($area_lines != []) {
                $lines[] = '';
                $lines[] = '### ' . $area;
                $lines[] = '';
                $lines = array_merge($lines, $area_lines);
            }
        }
        $lines[] = '';
        $lines[] = '';
        $lines[] = '## Classes with a suggested var name created with a different name';
        $lines[] = '';
        $lines = array_merge($lines, $exceptions);
        $lines[] = '';
        $lines[] = '## Classes without a suggested var name';
        $lines[] = '';
        $lines = array_merge($lines, $no_suggestion);
        return implode("\n", $lines) . "\n";
    }

    /**
     * collect all global vars declared in the code with the description from the comment behind
     * the declaration e.g. 'global $sys; // the preloaded types'; a var declared with different
     * descriptions collects all distinct descriptions; additionally the app areas that declare
     * the var are collected to show if a var is only used by the backend or the frontend
     *
     * @param string $root the directory to scan recursively for php files
     * @param array $glb_lst (in/out) map of the var name to the list of distinct descriptions
     * @param array $glb_area_lst (in/out) map of the var name to the list of app areas using it
     * @param array $glb_add_lst (in/out) map of the var name to the declaring classes resp.
     *                           scripts that are not clearly frontend or backend
     * @return void
     */
    private function php_global_vars(
        string $root,
        array  &$glb_lst,
        array  &$glb_area_lst,
        array  &$glb_add_lst
    ): void
    {
        $lib = new library();
        foreach ($this->php_file_list($root) as $file) {
            $area = $this->php_global_var_area(str_replace('//', '/', $file));
            $file_lines = explode("\n", file_get_contents($file));
            // the class name resp. the script name of a shared code file that declares the var
            $src = '';
            if ($area == self::AREA_BOTH) {
                $src = $lib->php_class_from_code($file_lines);
                if ($src == '') {
                    $src = basename($file);
                }
            }
            foreach ($file_lines as $line) {
                $found = [];
                if (preg_match('/^\s*global\s+([^;\/]+);\s*(?:\/\/\s*(.*))?$/', rtrim($line), $found)) {
                    $des = trim($found[2] ?? '');
                    $vars = array_map('trim', explode(',', $found[1]));
                    // a description behind a line that declares several vars would be ambiguous,
                    // so it is only collected if the line declares a single var
                    if (count($vars) > 1) {
                        $des = '';
                    }
                    foreach ($vars as $var_txt) {
                        if (str_starts_with($var_txt, '$')) {
                            $var = substr($var_txt, 1);
                            if (!array_key_exists($var, $glb_lst)) {
                                $glb_lst[$var] = [];
                                $glb_area_lst[$var] = [];
                            }
                            if ($des != '' and !in_array($des, $glb_lst[$var])) {
                                $glb_lst[$var][] = $des;
                            }
                            if ($area != '' and !in_array($area, $glb_area_lst[$var])) {
                                $glb_area_lst[$var][] = $area;
                            }
                            if ($src != '' and !in_array($src, $glb_add_lst[$var] ?? [])) {
                                $glb_add_lst[$var][] = $src;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $area_lst the list of app areas that declare a global var
     * @return string backend or frontend if only that area declares the var, else both
     */
    private function php_global_var_area_group(array $area_lst): string
    {
        $result = self::AREA_BOTH;
        if ($area_lst == [self::AREA_BACKEND]) {
            $result = self::AREA_BACKEND;
        } elseif ($area_lst == [self::AREA_FRONTEND]) {
            $result = self::AREA_FRONTEND;
        }
        return $result;
    }

    /**
     * @param string $path the path of the php file with the global var declaration
     * @return string the app area of the file: backend (model and api), frontend (web and http)
     *                or both for the code shared by the areas e.g. init.php; empty for the test
     *                code, because the tests simulate both areas and would turn every var to both
     */
    private function php_global_var_area(string $path): string
    {
        $result = self::AREA_BOTH;
        if (str_starts_with($path, paths::MODEL)
            or str_starts_with($path, paths::API)
            or str_starts_with($path, paths::API_OBJECT)) {
            $result = self::AREA_BACKEND;
        } elseif (str_starts_with($path, html_paths::WEB)
            or str_starts_with($path, html_paths::HTTP)
            or str_starts_with($path, html_paths::HTTP_OLD)) {
            $result = self::AREA_FRONTEND;
        } elseif (str_starts_with($path, TEST_PHP_PATH)) {
            $result = '';
        }
        return $result;
    }

    /**
     * collect the suggested var name of every class that declares one in its file docblock
     * the suggestion line has the fixed format '$abbr is the suggested var name'
     *
     * @param string $root the directory to scan recursively for php class files
     * @return array map of class short name to its suggested var name (without the leading '$')
     */
    private function php_suggested_var_names(string $root): array
    {
        $suggested = [];
        foreach ($this->php_file_list($root) as $file) {
            $lines = explode("\n", file_get_contents($file));
            $class = '';
            $var = '';
            foreach ($lines as $line) {
                $line = trim($line);
                if ($class == '' and str_starts_with($line, 'class ')) {
                    $class = preg_split('/\s+/', $line)[1];
                }
                $found = [];
                if ($var == '' and preg_match('/^\$([A-Za-z_]\w*)\s+is the suggested var name/', $line, $found)) {
                    $var = $found[1];
                }
            }
            if ($class != '' and $var != '') {
                $suggested[$class] = $var;
            }
        }
        return $suggested;
    }

    /**
     * collect every '$var = new <class>' object creation under the given directory
     * a class imported with an alias (use ... as x) is resolved back to its real class name
     *
     * @param string $root the directory to scan recursively for php files
     * @param array $usage (in/out) map of class short name to the list of var names used
     * @return void
     */
    private function php_collect_new_usage(string $root, array &$usage): void
    {
        foreach ($this->php_file_list($root) as $file) {
            $code = file_get_contents($file);
            // per-file alias map e.g. 'use Zukunft\...\word as word_ui;' -> word_ui => word
            $aliases = [];
            $alias = [];
            foreach (explode("\n", $code) as $line) {
                if (preg_match('/^use\s+(.+?)\s+as\s+(\w+)\s*;/', trim($line), $alias)) {
                    $full = $alias[1];
                    $aliases[$alias[2]] = str_contains($full, '\\')
                        ? substr($full, strrpos($full, '\\') + 1)
                        : $full;
                }
            }
            // collect every '$var = new <class>(' creation
            $hits = [];
            preg_match_all('/\$(\w+)\s*=\s*new\s+(\w+)\s*\(/', $code, $hits, PREG_SET_ORDER);
            foreach ($hits as $hit) {
                $var = $hit[1];
                $token = $hit[2];
                if (in_array($token, ['self', 'static', 'parent'])) {
                    continue;
                }
                $class = array_key_exists($token, $aliases) ? $aliases[$token] : $token;
                if (!array_key_exists($class, $usage)) {
                    $usage[$class] = [];
                }
                if (!in_array($var, $usage[$class])) {
                    $usage[$class][] = $var;
                }
            }
        }
    }

    /**
     * list all php files found recursively under a directory
     * @param string $root the directory to scan
     * @return array flat list of php file paths
     */
    private function php_file_list(string $root): array
    {
        $lib = new library();
        $files = $lib->array_to_path($lib->dir_to_array($root), $root);
        $result = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $result[] = $file;
            }
        }
        return $result;
    }

    function php_function_tree(): string
    {
        $fnc_lst = [];
        // TODO Prio 0 the target class sections are
        $main_classes = array_merge(def::MAIN_CLASSES, def::MAIN_SUB_CLASSES);
        $fnc_lst = array_merge($fnc_lst, $this->php_functions(paths::MODEL, 'main backend', $main_classes));
        $fnc_lst = array_merge($fnc_lst, $this->php_functions(paths::MODEL, 'other backend', [], $main_classes));
        $fnc_lst = array_merge($fnc_lst, $this->php_functions(html_paths::WEB, 'frontend'));
        $fnc_tree = $this->functionTree($fnc_lst);
        return $this->php_function_list_to_md($fnc_tree);
    }

    private function php_class_list_to_md(array $class_tree): string
    {
        $md_txt = '# Objects' . "\n";
        $md_txt .= "\n";
        $md_txt .= '## Object structure' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'the object structure is:' . "\n";
        $md_txt .= "\n";
        $md_txt .= '```' . "\n";
        $md_txt .= $this->php_class_list_to_md_row($class_tree);
        $md_txt .= '```' . "\n";
        return $md_txt;
    }

    private function php_function_list_to_md(array $fnc_lst): string
    {
        $md_txt = '# Object functions' . "\n";
        $md_txt .= "\n";
        $md_txt .= '## Functions sections' . "\n";
        $md_txt .= "\n";
        $md_txt .= $this->php_function_list_to_md_row_start($fnc_lst);
        $md_txt .= "\n";
        return $md_txt;
    }

     private function php_class_list_to_md_row(array $class_tree, string $prefix = ''): string
    {
        $md_txt = '';
        $count = count($class_tree);
        $pos = 0;
        foreach ($class_tree as $child => $info_lst) {
            $is_last = ($pos == $count - 1);
            // box-drawing connector: last child gets the corner, others a tee
            $connector = $is_last ? '└── ' : '├── ';
            if (is_string($info_lst)) {
                $md_txt .= $prefix . $connector . $child . ' - ' . $info_lst . "\n";
            } else {
                $md_txt .= $prefix . $connector . $child . "\n";
                // children of a last node align with spaces, others keep the vertical bar
                $child_prefix = $prefix . ($is_last ? '    ' : '│   ');
                $md_txt .= $this->php_class_list_to_md_row($info_lst, $child_prefix);
            }
            $pos++;
        }
        return $md_txt;
    }

    function php_function_list_to_md_row_start(array $fnc_lst): string
    {
        $md_txt = $this->php_function_list_to_md_row($fnc_lst);
        if ($md_txt != '') {
            $md_txt .= '```' . "\n";
        }
        return $md_txt;
    }

    /**
     * split one line of docs/code_functions_all.md into lines of at most MD_MAX_LINE_LEN chars,
     * because a function order error names every function of the section, which for a big class
     * is a line of thousands of chars that no editor and no diff shows in a readable way
     *
     * the line is split after a comma of the function lists whenever one is within the limit and
     * only otherwise at the limit itself, so that a function name is never torn apart; the
     * continuation lines keep the indent of the first line plus MD_WRAP_MARKER
     *
     * @param string $line the complete line including the tree indent and the trailing line break
     * @param string $intent the tree indent of the line, repeated on each continuation line
     * @return string the line or the wrapped lines, each closed with a line break
     */
    private function md_wrap(string $line, string $intent): string
    {
        $result = '';
        $rest = $line;
        // the continuation lines repeat the indent as spaces, so they have less room for the text
        $next_indent = str_repeat(' ', strlen($intent)) . self::MD_WRAP_MARKER;
        while (strlen($rest) > self::MD_MAX_LINE_LEN) {
            $cut = strrpos(substr($rest, 0, self::MD_MAX_LINE_LEN), ',');
            if ($cut === false or $cut < strlen($next_indent)) {
                // no comma within the limit, so split at the limit
                $cut = self::MD_MAX_LINE_LEN;
            } else {
                // keep the comma at the end of the line, so that the list stays readable
                $cut = $cut + 1;
            }
            $result .= substr($rest, 0, $cut) . "\n";
            $rest = $next_indent . substr($rest, $cut);
        }
        return $result . $rest . "\n";
    }

    function php_function_list_to_md_row(array $fnc_lst, string $intent = '### ', string $code_maker = ''): string
    {
        $md_txt = '';
        foreach ($fnc_lst as $child => $info_lst) {
            if (is_string($info_lst)) {
                $md_txt .= $this->md_wrap($intent . $child . ' - ' . $info_lst, $intent);
            } else {
                $before = '';
                $after = '';
                if ($intent == '### ') {
                    // close the code section
                    if ($md_txt != '') {
                        $before = '```' . "\n" . "\n";
                    }
                    $this_intent = $intent;
                    $next_intent = '+-- ';
                    // extra line after the headline
                    $after = "\n";
                } elseif ($intent == '+-- ') {
                    // open the code section
                    if ($code_maker == '') {
                        $code_maker = '```';
                        $before = $code_maker . "\n";
                    }
                    $this_intent = '\-- ';
                    $next_intent = '    ' . $this_intent;
                } else {
                    $this_intent = $intent;
                    $next_intent = '    ' . $intent;
                }
                $md_txt .= $before . $this_intent . $child . "\n" . $after;
                $md_txt .= $this->php_function_list_to_md_row($info_lst, $next_intent, $code_maker);
            }
        }
        return $md_txt;
    }

    private function php_classes(string $path, string $section): array
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($path);
        $code_files = $lib->array_to_path($file_array);
        $class_lst = [];
        // create parent child class list upfront for a complete check
        foreach ($code_files as $code_file) {
            $file_path = str_replace('//', '/', $path . $code_file);
            $ctrl_code = file($path . $code_file);
            $class_info = $lib->php_code_parent($ctrl_code, $section, $file_path);
            if ($class_info != []) {
                $class_lst = array_merge($class_lst, $class_info);
            }
        }
        return $class_lst;
    }

    /**
     * check that no class in cfg uses a class from web
     * because the backend model layer must not depend on the frontend web layer
     *
     * @param test_cleanup $t
     * @return void
     */
    function php_cfg_no_web_tests(test_cleanup $t): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array(paths::MODEL);
        $code_files = $lib->array_to_path($file_array);
        $pos = 1;
        foreach ($code_files as $code_file) {
            $ctrl_code = file(paths::MODEL . $code_file);
            $use_classes = $lib->php_code_use($ctrl_code);
            foreach ($use_classes as $use) {
                $class = $use[0];
                $path = $use[1];
                if (str_contains($path, '\main\php\web\\')) {
                    $sub_path = $lib->str_right_of(paths::MODEL, '../');
                    $test_name = 'cfg must not use web class ' . $path . '\\' . $class
                        . ' in ' . $sub_path . $code_file
                        . ' (' . $pos . ' of ' . count($code_files) . ')';
                    // TODO Prio 2 remove exception
                    if ($code_file != '/log_text/text_log_functions.php'
                        and $code_file != '/helper/db_cache_page.php') {
                        $t->assert($test_name, '', $class);
                    }

                }
            }
            $pos++;
        }
    }

    /**
     * check that files in src/main/php/web/** declare no PHP global other than
     * $ui_sys and $mtr — the only frontend-scoped globals allowed by
     * docs/llm/state-and-messages.md
     *
     * each violation produces one failing assertion identifying the file, line
     * and offending name; a clean tree produces the summary assertion only, which
     * checks that at least one file has been scanned
     *
     * positive (test fires when it should): a line like "global $sys;" inside
     *     web/ flags the rule violation
     * negative (test tolerates good code): "global $ui_sys;" and "global $mtr;"
     *     in web/ pass without an assertion
     *
     * web/frontend.php is excluded for now because its deprecated direct-db
     * bootstrap (start/open_db/end/load_cache) still needs the backend globals
     * $sys/$cac/$cfg; see the TODO below
     *
     * web/init_ui.php is excluded because it is the frontend bootstrap that
     * creates the shared globals ($debug, $sys and $log_txt) for the ui scripts,
     * like init.php does for the backend scripts
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_web_only_allowed_globals_tests(test_cleanup $t): void
    {
        // TODO Prio 1 use the api instead of the direct database connection in
        //      web/frontend.php so the deprecated start/open_db/end/load_cache
        //      bootstrap no longer needs the backend globals ($sys/$cac/$cfg) and
        //      the 'frontend.php' exception below can be removed
        $this->php_only_allowed_globals_tests(
            $t,
            paths::WEB,
            ['ui_sys', 'mtr'],
            'web/ must declare only $ui_sys and $mtr as globals',
            ['frontend.php', 'init_ui.php']
        );
    }

    /**
     * check that no file in src/main/php/web/** creates its own config object:
     * frontend config values always come from the request cache $ui_sys->cfg,
     * which http/view.php fills once at request start (and test_lib::ui_test_cache
     * for unit tests); a freshly created config is empty, so get_by() would
     * silently return the fallback instead of the user setting
     *
     * each violation produces one failing assertion identifying the file and line;
     * a clean tree produces the summary assertion only, which checks that at least
     * one file has been scanned
     *
     * positive (test fires when it should): a line like "$cfg = new config();"
     *     inside web/ flags the rule violation
     * negative (test tolerates good code): "$cfg = $ui_sys->cfg;" in web/ passes
     *     without an assertion
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_web_config_from_cache_tests(test_cleanup $t): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array(paths::WEB);
        $code_files = $lib->array_to_path($file_array);
        $files_checked = 0;
        foreach ($code_files as $code_file) {
            $files_checked++;
            $ctrl_code = file(paths::WEB . $code_file);
            foreach ($ctrl_code as $line_idx => $line) {
                if (str_contains($line, 'new config(')) {
                    $test_name = 'web/ must read the user config from $ui_sys->cfg'
                        . ' but found new config() in ' . $code_file . ':' . ($line_idx + 1);
                    // the offending line is the actual result and no hit is the target
                    $t->assert($test_name, trim($line));
                }
            }
        }
        // one summary assertion so that a clean tree also produces a visible pass (see
        // php_only_allowed_globals_tests for why a silent pass would hide a scanner that reads no file)
        $test_name = 'web/ config from cache checked in ' . $files_checked . ' files';
        $t->assert_greater($test_name, 0, $files_checked);
    }

    /**
     * check that no function overwrites its own user_message parameter with a fresh new user_message():
     * the message is append-only, so resetting a $msg parameter silently drops every error collected so
     * far and the requesting user that lives on it (docs/llm/state-and-messages.md); 11 such shadows
     * (word/triple/source del_links, figure/group api_mapper, db_object url_mapper, sandbox save_id
     * stubs, formula_map unlink_phrase, sandbox_multi import_obj, sql_db delete) each dropped errors
     * until they were fixed
     *
     * a token parser (not a line grep) is required because a grep cannot tell a parameter shadow
     * ($msg is a user_message parameter) from a legitimate local buffer ($msg is a fresh local);
     * the guarded null-init of a *nullable* parameter is still tolerated, but no code relies on it
     * any more - a message parameter is required now (docs/llm/state-and-messages.md, "$msg is
     * never null"), and the last such init, import_convert_xbrl::build_data, became dead code when
     * its parameter stopped being nullable, so the tolerance can go with the last nullable parameter
     * listed in docs/code_user_message_exceptions.md
     *
     * each violation produces one failing assertion identifying the file and line;
     * a clean tree produces the summary assertion only
     *
     * positive (test fires when it should): "$msg = new user_message();" in a function with a
     *     "user_message $msg" parameter flags the rule violation
     * negative (test tolerates good code): a local buffer "$msg = new user_message();" (not a
     *     parameter), a default value "user_message $msg = new user_message()" in the signature, and
     *     the guarded null-init of a nullable "?user_message $msg = null" parameter all pass;
     *     a nullable parameter that needs a fallback uses a local ("$map_msg = $msg ?? new
     *     user_message();") instead of reassigning the parameter, as the three web list
     *     constructors do
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_user_message_param_shadow_tests(test_cleanup $t): void
    {
        // the library code below the entry points: backend model, api objects, frontend and shared
        foreach ([paths::MODEL, paths::API_OBJECT, html_paths::WEB, paths::SHARED] as $base_path) {
            $this->php_msg_shadow_scan($t, $base_path);
        }
    }

    /**
     * scan every php file under $base_path and assert one failure per user_message parameter shadow
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @param string $base_path the source dir to scan e.g. paths::MODEL
     * @return void
     */
    private function php_msg_shadow_scan(test_cleanup $t, string $base_path): void
    {
        $lib = new library();
        $code_files = $lib->array_to_path($lib->dir_to_array($base_path));
        $files_checked = 0;
        foreach ($code_files as $code_file) {
            $files_checked++;
            $lines = file($base_path . $code_file);
            $shadows = $this->msg_param_shadows(implode('', $lines), $code_file);
            foreach ($shadows as $shadow) {
                $test_name = 'a function must not overwrite its user_message parameter'
                    . ' with a fresh new user_message() (append-only), but $' . $shadow['var']
                    . ' is reset in ' . $shadow['code_file'] . ':' . $shadow['line'];
                // the offending line is the actual result and no hit is the target
                $t->assert($test_name, trim($lines[$shadow['line'] - 1]));
            }
        }
        // one summary assertion per scanned tree so that a clean tree also produces a visible pass;
        // scanning and tokenising the whole source tree takes clearly longer than a normal unit
        // function, so a generous timeout is used to avoid a false timeout as the codebase grows
        $test_name = 'user_message param shadows checked in ' . $files_checked . ' files of ' . $base_path;
        $t->assert_greater($test_name, 0, $files_checked, $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * find every function in the php source that overwrites its own user_message parameter with a
     * fresh new user_message(); every top-level and nested function is walked, a nested closure body
     * is attributed to its own scope so its parameters do not leak into the enclosing function
     *
     * @param string $src the full php source of the file
     * @param string $code_file the file path used in the violation message
     * @return array<int,array{code_file:string,line:int,var:string}> one entry per shadow found
     */
    private function msg_param_shadows(string $src, string $code_file): array
    {
        $tokens = token_get_all($src);
        $n = count($tokens);
        $out = [];
        $i = 0;
        while ($i < $n) {
            if (is_array($tokens[$i]) and $tokens[$i][0] == T_FUNCTION) {
                $open = $i + 1;
                while ($open < $n and $tokens[$open] !== '(') {
                    $open++;
                }
                $close = $open;
                $params = $this->msg_params_of($tokens, $open, $close);
                $body = $close + 1;
                while ($body < $n and $tokens[$body] !== '{' and $tokens[$body] !== ';') {
                    $body++;
                }
                if ($body < $n and $tokens[$body] === '{') {
                    $body_end = $this->brace_end($tokens, $body);
                    $this->scan_msg_shadow($tokens, $body + 1, $body_end - 1, $params, $code_file, $out);
                }
            }
            $i++;
        }
        return $out;
    }

    /**
     * parse the parameter list of a function from the '(' at $open to its matching ')'
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $open the index of the opening '(' of the parameter list
     * @param int $close set by reference to the index of the matching ')'
     * @return array<string,array{is_msg:bool,nullable:bool}> the params keyed by variable name (no $),
     *         each flagged whether its type is user_message and whether it is nullable
     */
    private function msg_params_of(array $tokens, int $open, int &$close): array
    {
        $n = count($tokens);
        $name_tokens = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];
        $params = [];
        $depth = 0;
        $type = '';        // accumulated type text before the variable of the current segment
        $var = '';         // the parameter variable name of the current segment
        $nullable = false; // a '?' prefix or a '= null' default makes the parameter nullable
        $done = false;
        $i = $open;
        while ($i < $n and !$done) {
            $tok = $tokens[$i];
            if ($tok === '(') {
                $depth++;
            } elseif ($tok === ')') {
                $depth--;
                if ($depth == 0) {
                    $close = $i;
                    $done = true;
                }
            } elseif ($depth == 1 and $tok === ',') {
                $this->add_msg_param($params, $type, $var, $nullable);
                $type = '';
                $var = '';
                $nullable = false;
            } elseif ($depth >= 1 and is_array($tok) and $tok[0] == T_VARIABLE and $var === '') {
                $var = ltrim($tok[1], '$');
            } elseif ($depth >= 1 and is_array($tok) and in_array($tok[0], $name_tokens)) {
                if ($var === '') {
                    $type .= $tok[1];
                } elseif (strtolower($tok[1]) == 'null') {
                    $nullable = true;
                }
            } elseif ($depth >= 1 and $tok === '?' and $var === '') {
                $nullable = true;
            }
            $i++;
        }
        // add the last segment, which has no trailing comma to close it
        $this->add_msg_param($params, $type, $var, $nullable);
        return $params;
    }

    /**
     * append one parsed parameter to the param map, skipping an empty segment (e.g. a trailing comma)
     *
     * @param array $params the param map being built, keyed by variable name
     * @param string $type the accumulated type text of the segment
     * @param string $var the variable name of the segment (empty for no parameter)
     * @param bool $nullable true if the parameter is nullable
     * @return void
     */
    private function add_msg_param(array &$params, string $type, string $var, bool $nullable): void
    {
        if ($var !== '') {
            $params[$var] = [
                'is_msg' => str_contains($type, 'user_message'),
                'nullable' => $nullable
            ];
        }
    }

    /**
     * return the index of the '}' that matches the '{' at $open
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $open the index of the opening '{'
     * @return int the index of the matching '}' (or the last token if unbalanced)
     */
    private function brace_end(array $tokens, int $open): int
    {
        $n = count($tokens);
        $depth = 0;
        $end = $n - 1;
        $found = false;
        $i = $open;
        while ($i < $n and !$found) {
            if ($tokens[$i] === '{') {
                $depth++;
            } elseif ($tokens[$i] === '}') {
                $depth--;
                if ($depth == 0) {
                    $end = $i;
                    $found = true;
                }
            }
            $i++;
        }
        return $end;
    }

    /**
     * return the index of the next code token at or after $i, skipping whitespace and comments
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $i the start index
     * @return int the index of the next code token
     */
    private function next_code_idx(array $tokens, int $i): int
    {
        $n = count($tokens);
        $skip = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
        while ($i < $n and is_array($tokens[$i]) and in_array($tokens[$i][0], $skip)) {
            $i++;
        }
        return $i;
    }

    /**
     * scan a function body (tokens $start..$end) for a "$p = new user_message(...)" assignment to one
     * of the function's user_message parameters; a nested closure body is skipped so its assignments
     * are attributed to its own scope by the outer walk in msg_param_shadows
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $start the first body token index (after the opening '{')
     * @param int $end the last body token index (before the closing '}')
     * @param array $params the parameter map of the enclosing function
     * @param string $code_file the file path used in the violation entry
     * @param array $out the violation list being collected
     * @return void
     */
    private function scan_msg_shadow(
        array  $tokens,
        int    $start,
        int    $end,
        array  $params,
        string $code_file,
        array  &$out
    ): void
    {
        $i = $start;
        while ($i <= $end) {
            $tok = $tokens[$i];
            if (is_array($tok) and $tok[0] == T_FUNCTION) {
                // skip the nested closure; the outer walk visits it later with its own parameters
                $i = $this->skip_function($tokens, $i, $end);
            } else {
                if ($this->is_msg_shadow($tokens, $i, $end, $params)) {
                    $var = ltrim($tok[1], '$');
                    // the guarded null-init of a nullable parameter is the one sanctioned reset
                    $guarded = ($params[$var]['nullable']
                        && $this->has_null_guard($tokens, $start, $end, $var));
                    if (!$guarded) {
                        $out[] = ['code_file' => $code_file, 'line' => $tok[2], 'var' => $var];
                    }
                }
                $i++;
            }
        }
    }

    /**
     * return the token index just past a nested function/closure: its body if it has one, else the
     * token after its ';' (an abstract or interface method stub)
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $i the index of the nested T_FUNCTION token
     * @param int $end the last token index that may be inspected
     * @return int the index just past the nested function
     */
    private function skip_function(array $tokens, int $i, int $end): int
    {
        $open = $i + 1;
        while ($open <= $end and $tokens[$open] !== '(') {
            $open++;
        }
        $close = $open;
        $this->msg_params_of($tokens, $open, $close);
        $body = $close + 1;
        while ($body <= $end and $tokens[$body] !== '{' and $tokens[$body] !== ';') {
            $body++;
        }
        $next = $body + 1;
        if ($body <= $end and $tokens[$body] === '{') {
            $next = $this->brace_end($tokens, $body) + 1;
        }
        return $next;
    }

    /**
     * true if the token at $i is a user_message parameter variable immediately assigned a
     * new user_message(...) — the shadow pattern "$p = new user_message"
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $i the candidate T_VARIABLE index
     * @param int $end the last body token index
     * @param array $params the parameter map of the enclosing function
     * @return bool true if the assignment shadows a user_message parameter
     */
    private function is_msg_shadow(array $tokens, int $i, int $end, array $params): bool
    {
        $hit = false;
        $tok = $tokens[$i];
        if (is_array($tok) and $tok[0] == T_VARIABLE) {
            $var = ltrim($tok[1], '$');
            if (isset($params[$var]) and $params[$var]['is_msg']) {
                $eq = $this->next_code_idx($tokens, $i + 1);
                if ($eq <= $end and $tokens[$eq] === '=') {
                    $new = $this->next_code_idx($tokens, $eq + 1);
                    if ($new <= $end and is_array($tokens[$new]) and $tokens[$new][0] == T_NEW) {
                        $cls = $this->next_code_idx($tokens, $new + 1);
                        $name = is_array($tokens[$cls] ?? '') ? $tokens[$cls][1] : '';
                        // strip any namespace qualifier and compare the short class name
                        $short = strtolower(substr(strrchr('\\' . $name, '\\'), 1));
                        $hit = ($short == 'user_message');
                    }
                }
            }
        }
        return $hit;
    }

    /**
     * true if the function body between $start and $end guards $var against null before use
     * (a "$var == null" / "$var === null" comparison or a "$var ??" coalesce), which marks the
     * sanctioned null-init of a nullable parameter (import_convert_xbrl::build_data)
     *
     * @param array $tokens the token_get_all output of the file
     * @param int $start the first body token index
     * @param int $end the last body token index
     * @param string $var the parameter variable name to check (no $)
     * @return bool true if a null guard on $var is present
     */
    private function has_null_guard(array $tokens, int $start, int $end, string $var): bool
    {
        $guarded = false;
        $i = $start;
        while ($i <= $end) {
            $tok = $tokens[$i];
            if (is_array($tok) and $tok[0] == T_VARIABLE and ltrim($tok[1], '$') == $var) {
                $op = $this->next_code_idx($tokens, $i + 1);
                $nxt = $tokens[$op] ?? '';
                if (is_array($nxt) and in_array($nxt[0], [T_COALESCE, T_COALESCE_EQUAL])) {
                    $guarded = true;
                } elseif (is_array($nxt) and in_array($nxt[0], [T_IS_EQUAL, T_IS_IDENTICAL])) {
                    $val = $this->next_code_idx($tokens, $op + 1);
                    $null_tok = $tokens[$val] ?? '';
                    if (is_array($null_tok) and strtolower($null_tok[1]) == 'null') {
                        $guarded = true;
                    }
                }
            }
            $i++;
        }
        return $guarded;
    }

    /**
     * check that no php file below the entry points writes the requesting user onto a user_message
     * with a post-hoc $msg->usr assignment: the requesting user is set once by the entry point
     * (the http scripts and the api index.php scripts) and every function below reads it from
     * $msg->usr, never re-sets it
     * (docs/llm/state-and-messages.md, the "requesting user lives on $msg" migration); the only
     * sanctioned writers are the user_message classes themselves (skipped as a whole) and the
     * exact file and line pairs listed in MSG_USR_WRITE_SANCTIONED (the frontend login user
     * switch and the signup fallback of user::db_insert) — those exact lines are tolerated
     * while the rest of the listed files stays under the rule
     *
     * user_message variables follow the $..msg.. / $..message.. naming convention, so a
     * "$<msg>->usr =" write is the machine-detectable violation; a comment line is skipped
     *
     * each violation produces one failing assertion identifying the file and line;
     * a clean tree produces the summary assertion only
     *
     * positive (test fires when it should): "$msg->usr = $sys_usr;" below the entry points flags the
     *     rule violation
     * negative (test tolerates good code): setting the user through the constructor
     *     "$msg = new user_message($sys_usr);" passes, and a comparison "$msg->usr == ..." is not a write
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_user_message_user_write_tests(test_cleanup $t): void
    {
        // the library code below the entry points: backend model, api objects, frontend and shared
        foreach ([paths::MODEL, paths::API_OBJECT, html_paths::WEB, paths::SHARED] as $base_path) {
            $this->php_msg_user_write_scan($t, $base_path);
        }
    }

    /**
     * scan every php file under $base_path and assert one failure per post-hoc user_message->usr write;
     * the user_message class files are skipped and the exact sanctioned lines of
     * MSG_USR_WRITE_SANCTIONED are tolerated
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @param string $base_path the source dir to scan e.g. paths::MODEL
     * @return void
     */
    // the sanctioned post-hoc writers of the requesting user, as exact file to line pairs:
    // - the frontend login user switch (frontend::url_to_action) is the one allowed change of
    //   the requesting user after the entry point assignment
    // - the signup fallback (user::db_insert) sets the signup system user as the requester
    //   when a guest user is created based on the ip address and no requesting user is given
    // exactly these lines are tolerated while the rest of the files stays under the rule
    private const array MSG_USR_WRITE_SANCTIONED = [
        DIRECTORY_SEPARATOR . 'frontend.php' => '$msg_ui->usr = $usr_ui;',
        DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'user.php' => '$msg->usr = $usr_req;',
    ];

    private function php_msg_user_write_scan(test_cleanup $t, string $base_path): void
    {
        $lib = new library();
        $code_files = $lib->array_to_path($lib->dir_to_array($base_path));
        // a "$<var>->usr =" write where the var name carries the message convention (msg / message);
        // the negative lookahead excludes the == / === comparisons and the => arrow
        $pattern = '#\$[a-z0-9_]*(msg|message)[a-z0-9_]*->usr\s*=(?![=>])#i';
        $files_checked = 0;
        foreach ($code_files as $code_file) {
            $full = str_replace('\\', '/', $base_path . $code_file);
            // the user_message classes are the sanctioned home of the ->usr assignment
            if (str_ends_with($full, '/user_message.php') or str_ends_with($full, '/sql_message.php')) {
                continue;
            }
            $files_checked++;
            $ctrl_code = file($base_path . $code_file);
            foreach ($ctrl_code as $line_idx => $line) {
                // skip comment lines so a docblock that cites the anti-pattern is not flagged
                $head = ltrim($line);
                if ($head === '' or $head[0] === '*'
                    or str_starts_with($head, '//') or str_starts_with($head, '/*')) {
                    continue;
                }
                // tolerate only the exact sanctioned requesting user switches, nothing else
                if (array_key_exists($code_file, self::MSG_USR_WRITE_SANCTIONED)
                    and trim($line) == self::MSG_USR_WRITE_SANCTIONED[$code_file]) {
                    continue;
                }
                if (preg_match($pattern, $line)) {
                    $test_name = 'the requesting user lives on $msg from the entry point;'
                        . ' a function below must not write $msg->usr, but found one in '
                        . $code_file . ':' . ($line_idx + 1);
                    // the offending line is the actual result and no hit is the target
                    $t->assert($test_name, trim($line));
                }
            }
        }
        // one summary assertion per scanned tree so that a clean tree also produces a visible pass;
        // scanning the whole source tree takes clearly longer than a normal unit function, so a
        // generous timeout is used to avoid a false timeout as the codebase grows
        $test_name = 'user_message->usr writes checked in ' . $files_checked . ' files of ' . $base_path;
        $t->assert_greater($test_name, 0, $files_checked, $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * check that the user_message of a request is created only by the http resp. api entry point:
     * every 'new user_message(' below the entry points is an exception that needs a comment
     * explaining why a local message is needed (docs/llm/state-and-messages.md), and the still
     * unexplained ones are listed in docs/code_user_message_exceptions.md as the remaining rule
     * breaks, so a new one changes the generated doc and fails this test
     *
     * and check that a created message never gets lost: what it collects must reach the caller
     * (merged, returned, read or kept in an object field), so a message that is only filled and
     * then goes out of scope - an inline 'new user_message()' handed to a called function above all
     * - is listed as well, unless the comment behind it says that the drop is on purpose
     *
     * a list instead of one assertion per hit, because the tree still has ~180 open creations:
     * a per-hit assertion would drown the test output, while the doc keeps the work list reviewable
     * and shrinks with every threading pass (same pattern as docs/code_object_name_exceptions.md)
     *
     * positive (test fires when it should): a new unexplained '$msg = new user_message()' in cfg/
     *     adds a line to the report, so the generated markdown no longer matches the committed doc
     * negative (test tolerates good code): a creation with a comment above it counts as explained,
     *     and the entry points (http/, api/) are outside the scanned trees
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_user_message_creation_tests(test_cleanup $t): void
    {
        // scanning the whole source tree takes clearly longer than a normal unit function,
        // so a generous timeout is used to avoid a false timeout as the codebase grows
        $test_name = 'check that the docs with the user_message creations is updated';
        $md_txt = new code_user_message_exceptions()->md();
        $t->assert_file($test_name, $md_txt, test_files::DOCS_MSG_EXCEPTIONS, '', '', $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * check that no php file extends a path const with an inline directory string literal:
     * every directory is a const in one of the three paths.php files (cfg / web / test) and a longer
     * path is composed from those consts (docs/llm/constants.md); only a leaf file name may stay
     * inline, so a string literal ending in '/' next to a *paths:: const is the flagged violation
     *
     * each violation produces one failing assertion identifying the file and line;
     * a clean tree produces the summary assertion only, which checks that at least
     * one file has been scanned
     *
     * positive (test fires when it should): "test_paths::HTML . 'workflow/'" flags the rule violation
     * negative (test tolerates good code): "paths::DB . 'sql_db.php'" (a leaf file name) passes, and
     *     the three const/paths.php files are skipped because they are the home of the path consts
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_path_const_tests(test_cleanup $t): void
    {
        // a *paths:: const concatenated with a directory literal (a quoted string ending in '/'),
        // in either order; the quote may be a single or double quote
        $const = '[a-zA-Z_]*paths::[A-Z_]+';
        $dir = '[\'"][^\'"]*/[\'"]';
        $pattern = '#' . $const . '\s*\.\s*' . $dir . '|' . $dir . '\s*\.\s*' . $const . '#';
        foreach ([paths::PHP_LIB, TEST_PHP_PATH] as $base_path) {
            $this->php_path_const_scan($t, $base_path, $pattern);
        }
    }

    /**
     * scan every php file under $base_path and assert one failure per inline directory path violation;
     * the three const/paths.php files (the allowed home of the path consts) are skipped
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @param string $base_path the source dir to scan e.g. paths::PHP_LIB
     * @param string $pattern the regex matching a *paths:: const next to an inline directory literal
     * @return void
     */
    private function php_path_const_scan(test_cleanup $t, string $base_path, string $pattern): void
    {
        $lib = new library();
        $code_files = $lib->array_to_path($lib->dir_to_array($base_path));
        $files_checked = 0;
        foreach ($code_files as $code_file) {
            if (str_ends_with(str_replace('\\', '/', $code_file), 'const/paths.php')) {
                continue;
            }
            $files_checked++;
            $ctrl_code = file($base_path . $code_file);
            foreach ($ctrl_code as $line_idx => $line) {
                // skip comment lines so a docblock that cites the anti-pattern is not flagged
                $head = ltrim($line);
                if ($head === '' or $head[0] === '*' or $head[0] === '#'
                    or str_starts_with($head, '//') or str_starts_with($head, '/*')) {
                    continue;
                }
                if (preg_match($pattern, $line)) {
                    $test_name = 'a directory must be a paths.php const, not an inline string,'
                        . ' but found one in ' . $code_file . ':' . ($line_idx + 1);
                    // the offending line is the actual result and no hit is the target
                    $t->assert($test_name, trim($line));
                }
            }
        }
        // one summary assertion per scanned tree so that a clean tree also produces a visible pass
        // (see php_only_allowed_globals_tests for why a silent pass would hide an empty scan);
        // the base path keeps the test name unique across the two calls
        // scanning the whole source tree takes clearly longer than a normal unit function,
        // so a generous timeout is used to avoid a false timeout as the codebase grows
        $test_name = 'path consts checked in ' . $files_checked . ' files of ' . $base_path;
        $t->assert_greater($test_name, 0, $files_checked, $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * check that files in src/main/php/cfg/** declare no PHP global other than
     * $sys, $db_con, $cfg, $cac, $mtr and $debug — the only backend-scoped globals
     * allowed by docs/llm/state-and-messages.md
     *
     * each violation produces one failing assertion identifying the file, line
     * and offending name; a clean tree produces the summary assertion only, which
     * checks that at least one file has been scanned
     *
     * positive (test fires when it should): a line like "global $usr;" inside
     *     cfg/ flags the rule violation
     * negative (test tolerates good code): "global $sys;", "global $db_con;",
     *     "global $cfg;", "global $cac;", "global $mtr;" and "global $debug;" in
     *     cfg/ pass without an assertion
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @return void
     */
    function php_cfg_only_allowed_globals_tests(test_cleanup $t): void
    {
        $this->php_only_allowed_globals_tests(
            $t,
            paths::MODEL,
            ['sys', 'db_con', 'cfg', 'cac', 'mtr', 'debug'],
            'cfg/ must declare only $sys, $db_con, $cfg, $cac, $mtr and $debug as globals'
        );
    }

    /**
     * shared scanner for the global-declaration coding rules: assert that no file
     * under $base_path declares a PHP global whose name is not in $allowed
     *
     * each violation produces one failing assertion identifying the file, line and
     * offending name; a clean tree produces the summary assertion only, which checks
     * that at least one file has been scanned, because otherwise a scanner that reads
     * no file would be indistinguishable from a tree without any violation
     *
     * @param test_cleanup $t the test harness used for the assertion
     * @param string $base_path the source dir to scan e.g. paths::WEB or paths::MODEL
     * @param array $allowed the permitted global names without the leading $ e.g. ['ui_sys', 'mtr']
     * @param string $rule_msg the rule description shown before the offending name e.g.
     *     'web/ must declare only $ui_sys and $mtr as globals'
     * @param array $exclude relative file paths (within $base_path) to skip e.g. ['frontend.php']
     * @return void
     */
    private function php_only_allowed_globals_tests(
        test_cleanup $t,
        string       $base_path,
        array        $allowed,
        string       $rule_msg,
        array        $exclude = []
    ): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($base_path);
        $code_files = $lib->array_to_path($file_array);
        $files_checked = 0;
        foreach ($code_files as $code_file) {
            if (in_array(ltrim($code_file, '/\\'), $exclude, true)) {
                continue;
            }
            $files_checked++;
            $ctrl_code = file($base_path . $code_file);
            foreach ($ctrl_code as $line_idx => $line) {
                if (preg_match_all('/global\s+\$([a-zA-Z_][a-zA-Z0-9_]*)/', $line, $matches)) {
                    foreach ($matches[1] as $name) {
                        if (!in_array($name, $allowed, true)) {
                            $test_name = $rule_msg
                                . ' but found $' . $name
                                . ' in ' . $code_file . ':' . ($line_idx + 1);
                            // the offending name is the actual result and no global is the target,
                            // so the failure reports 'actual: sys, expected: ' and not the other way round
                            $t->assert($test_name, $name);
                        }
                    }
                }
            }
        }
        // one summary assertion so that a clean tree also produces a visible pass: without it a
        // scanner that reaches no file at all (a wrong base path, an empty dir_to_array) would look
        // exactly like a successful run, because a violation is the only thing that asserts above
        // scanning the whole source tree takes clearly longer than a normal unit function,
        // so a generous timeout is used to avoid a false timeout as the codebase grows
        $test_name = $rule_msg . ' checked in ' . $files_checked . ' files';
        $t->assert_greater($test_name, 0, $files_checked, $t::TIMEOUT_LIMIT_LONG);
    }

    /**
     * check if all used classes are also included once within the same file
     * TODO add a child parent list and make sure that a parent never includes a child object
     *      but the child always includes the parent
     *      and make sure that all not needed deactivated includes are removed
     *
     * @param test_cleanup $t
     * @param string $base_path path name of the folder with the php scripts that should be checked
     * @return void
     */
    function php_include_tests(test_cleanup $t, string $base_path): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($base_path);
        $code_files = $lib->array_to_path($file_array);
        $pos = 1;
        foreach ($code_files as $code_file) {
            log_debug($code_file);
            $ctrl_code = file($base_path . $code_file);
            $use_classes = $lib->php_code_use($ctrl_code);
            // the use code lines sorted by name for copy and paste to code
            $use_sorted = implode("\n", $lib->php_code_use_sorted($ctrl_code));
            // the include code lines sorted by name for copy and paste to code
            $use_converted = implode("\n", $lib->php_code_use_converted($ctrl_code));
            $include_classes = $lib->php_code_include($ctrl_code);
            foreach ($use_classes as $use) {
                $class = $use[0];
                $path = $use[1];
                if ($path != '') {
                    $found = false;
                    foreach ($include_classes as $include) {
                        $class_incl = $include[0];
                        $path_incl = $include[1];
                        if ($class == $class_incl) {
                            $path_conv = $lib->php_path_convert($path);
                            // a frontend file may include a backend class via the html_paths
                            // copy of the backend path const, which has the same const name
                            // (e.g. html_paths::MODEL_HELPER for paths::MODEL_HELPER)
                            $path_alias = str_starts_with($path_conv, 'paths::')
                                ? 'html_' . $path_conv
                                : '';
                            if ($path_conv == $path_incl
                                or $path_alias == $path_incl
                                or $path_conv == '') {
                                $found = true;
                            }
                        }
                    }
                    if (!$found) {
                        if (!in_array($path . '\\' . $class, self::PATH_NO_INCLUDE)) {
                            $sub_path = $lib->str_right_of($base_path, '../');
                            $test_name = 'includes missing in ' . $path . '\\' . $class
                                . ' in ' . $sub_path . $code_file
                                . ' (' . $pos . ' of ' . count($code_files) . ')';
                            $t->assert($test_name, '', $class);
                        }
                    }
                } else {
                    log_debug($class . ' is expected to be a PHP default library');
                }
            }
            $pos++;
        }
    }

    private function classTree(array $map): array
    {
        $root = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            if ($parent == '') {
                $root[$child] = $info_lst;
            }
        }
        $tree = [];
        foreach ($root as $parent => $info_lst) {
            $description = $info_lst[2];
            $children = $this->classTreeChildren($map, $parent);
            if (count($children) == 0) {
                $tree[$parent] = $description;
            } else {
                $tree[$parent] = $children;
            }
        }
        return $tree;
    }

    private function classTreeChildren(
        array  $map,
        string $opa
    ): array|string
    {
        $children = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            $description = $info_lst[2];
            if ($opa == $parent) {
                $grants = $this->classTreeChildren($map, $child);
                if (count($grants) == 0) {
                    $children[$child] = $description;
                } else {
                    $children[$child] = $grants;
                }
            }
        }
        return $children;
    }

    private function classTreeParents(array $map): array
    {
        $lst = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            if ($parent == '') {
                $lst[$child] = $parent;
            }
        }
        $tree = [];
        foreach ($lst as $class => $info_lst) {
            if (is_array($info_lst)) {
                $parent = $info_lst[0];
            } else {
                $parent = $info_lst;
            }
            $tree = array_merge($tree, $this->classTreeGrants($map, $class, $parent, []));
        }
        return $tree;
    }

    private function classTreeGrants(
        array  $map,
        string $class,
        string $parent,
        array  $tree
    ): array|string
    {
        if ($parent == '') {
            // if it does not have a parent just add it to the list if not yet done
            if (!in_array($class, $tree)) {
                $tree[$class] = '';
            }
        } else {
            // if it has an opa add the family tree
            if (array_key_exists($parent, $map)) {
                $opa = $map[$parent];
                $tree[$class] = $this->classTreeGrants($map, $parent, $opa, $tree);
            } else {
                if (!in_array($class, $tree)) {
                    $tree[$class] = $parent;
                }
            }
        }
        return $tree;
    }

    private function functionTree(array $map): array
    {
        /*
        $root = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            if ($parent == '') {
                $root[$child] = $info_lst;
            }
        }
        $tree = [];
        foreach ($root as $parent => $info_lst) {
            $description = $info_lst[2];
            $children = $this->classTreeChildren($map, $parent);
            if (count($children) == 0) {
                $tree[$parent] = $description;
            } else {
                $tree[$parent] = $children;
            }
        }
        */
        return $map;
    }

    /**
     * check if the functions in the classes are grouped by sections
     * if the sections of all classes are in the same order
     * and if the sections are described in the class header
     * TODO check that all sections have a description in the header
     * TODO check that the sections match the order in the header
     * TODO check that the header section list match the general order
     * TODO check that no function is in an unexpected section
     *
     * @param string $base_path path name of the folder with the php scripts that should be checked
     * @param string $obj_grp_txt nae of the object group e.g. "main backend" or "html frontend"
     * @return array with the messages where the section is missing or unexpected
     */
    function php_functions(
        string $base_path,
        string $obj_grp_txt,
        array  $only_classes = [],
        array  $except_classes = []
    ): array
    {
        $lib = new library();
        $result = [];
        $all_fnc_lst = [];
        $msg_lst = [];
        $file_array = $lib->dir_to_array($base_path);
        $code_files = $lib->array_to_path($file_array);
        // loop over the code files
        foreach ($code_files as $code_file) {
            $file_msg_lst = [];
            log_debug($code_file);
            // get the function names and the sec in the code
            $ctrl_code = file($base_path . $code_file);
            $fnc_lst = $lib->php_code_function($ctrl_code);
            $namespace = $lib->php_namespace_from_code($ctrl_code);
            $class = $lib->php_class_from_code($ctrl_code);
            $class_with = $namespace . '\\' . $class;
            $use_class = true;
            if ($only_classes != []) {
                if (!in_array($class_with, $only_classes)) {
                    $use_class = false;
                }
            }
            if ($except_classes != []) {
                if (in_array($class_with, $except_classes)) {
                    $use_class = false;
                }
            }
            if ($use_class) {
                // check the mandatory function are in the correct sec
                foreach ($fnc_lst as $fnc_row) {
                    $fnc = $fnc_row['name'];
                    $sec = $fnc_row['section'];
                    $section_expected = $lib->php_expected_function_section($fnc);
                    // if a class has more than 100 lines the functions should be grouped in sections
                    if (count($ctrl_code) > 100) {
                        if ($sec == '' and $fnc != '') {
                            $file_msg_lst[$fnc] = 'section for function ' . $fnc . ' missing in ' . $code_file;
                        }
                        // check if the function is in the expected sec
                        if ($sec != $section_expected) {
                            if ($section_expected == '') {
                                if ($sec != '') {
                                    $file_msg_lst[$fnc] = 'section for function ' . $fnc
                                        . ' not yet defined that it should be ' . $sec
                                        . ' in ' . $code_file;
                                } else {
                                    $file_msg_lst[$fnc] = 'section for function ' . $fnc
                                        . ' not yet defined' . ' in ' . $code_file;
                                }
                            } else {
                                $file_msg_lst[$fnc] = 'section for function ' . $fnc
                                    . ' is expected to be ' . $section_expected . ' in ' . $code_file;
                            }
                        }
                    }
                }
                $class_result = $this->php_check_function_order_and_merge($fnc_lst, $all_fnc_lst, $class);
                if (is_string($class_result)) {
                    $file_msg_lst['order error'] = $class_result;
                } else {
                    $all_fnc_lst = $class_result;
                }
                if ($file_msg_lst != []) {
                    $msg_lst[$class] = $file_msg_lst;
                }
            }

        }

        if ($msg_lst != []) {
            $result[$obj_grp_txt . ' errors'] = $msg_lst;
        }
        $result[$obj_grp_txt] = $all_fnc_lst;

        return $result;
    }

    private function php_check_function_order_and_merge(array $fnc_lst, array $all_fnc_lst, string $class): array|string
    {
        $lib = new library();
        $msg_lst = [];

        // generate the $all_fnc_lst format
        $sec_lst = [];
        foreach ($fnc_lst as $fnc_row) {
            $fnc = $fnc_row['name'];
            $sec = $fnc_row['section'];
            $des = $fnc_row['description'];
            $class_row = [];
            $class_row[$class] = $des;
            $sec_fnc_lst = [];
            if (array_key_exists($sec, $sec_lst)) {
                $sec_fnc_lst = $sec_lst[$sec];
                if (array_key_exists($fnc, $sec_fnc_lst)) {
                    $fnc_class_lst = $sec_fnc_lst[$fnc];
                    $fnc_class_lst[] = $class_row;
                    $sec_fnc_lst[$fnc] = $fnc_class_lst;
                } else {
                    $sec_fnc_lst[$fnc] = $class_row;
                }
            } else {
                $sec_fnc_lst[$fnc] = $class_row;
            }
            $sec_lst[$sec] = $sec_fnc_lst;
        }

        // if the target list is empty just use this list
        if ($all_fnc_lst == []) {
            $all_fnc_lst = $sec_lst;
        } else {
            // ... if not add the missing functions or report an error if tne order differs
            $prev = '';
            foreach ($fnc_lst as $fnc_row) {
                $fnc = $fnc_row['name'];
                $sec = $fnc_row['section'];
                $des = $fnc_row['description'];
                $class_row = [];
                $class_row[$class] = $des;
                if (array_key_exists($sec, $all_fnc_lst)) {
                    $sec_all_fnc_lst = $all_fnc_lst[$sec];
                    $sec_all_fnc_lst_keys = array_keys($all_fnc_lst[$sec]);
                    $sec_fnc_lst = $sec_lst[$sec];
                    $sec_fnc_lst_keys = array_keys($sec_fnc_lst);
                    if ($lib->arrayCompareOrder($sec_all_fnc_lst_keys, $sec_fnc_lst_keys)) {
                        if (in_array($fnc, $sec_all_fnc_lst_keys)) {
                            // add description in other class
                            $fnc_class_lst = $sec_all_fnc_lst[$fnc];
                            $fnc_class_lst[$class] = $des;
                            $sec_all_fnc_lst[$fnc] = $fnc_class_lst;
                        } else {
                            if (in_array($prev, $sec_all_fnc_lst_keys)) {
                                $sec_all_fnc_lst = $lib->arrayAddAfter($sec_all_fnc_lst, $class_row, $prev, $fnc);
                            } else {
                                // get first match
                                $start = array_search($fnc, $sec_fnc_lst_keys);
                                $pos = $start;
                                $found = '';
                                $len = count($sec_fnc_lst_keys);
                                while ($pos < $len and $found == '') {
                                    $s_fnc = $sec_fnc_lst_keys[$pos];
                                    if (in_array($s_fnc, $sec_all_fnc_lst_keys)) {
                                        $s_pos = array_search($s_fnc, $sec_all_fnc_lst_keys);
                                        $found = $sec_all_fnc_lst_keys[$s_pos];
                                    } else {
                                        $pos++;
                                    }
                                }
                                // if match add all function before the match
                                if ($found != '') {
                                    $to_add = [];
                                    for ($p = $start; $p < $pos; $p++) {
                                        if (array_key_exists($p, $sec_fnc_lst_keys)) {
                                            $fnc_pos = $sec_fnc_lst_keys[$p];
                                            if (array_key_exists($fnc_pos, $sec_fnc_lst)) {
                                                $to_add[$pos] = $sec_fnc_lst[$fnc_pos];
                                            } else {
                                                $msg = $fnc_pos . ' is missing in ' . implode(",", $sec_fnc_lst) . ' while adding ' . implode(",", array_keys($to_add));
                                                log_err($msg);
                                            }
                                        } else {
                                            $msg = $p . ' is missing in ' . implode(",", $sec_fnc_lst_keys) . ' while adding ' . implode(",", array_keys($to_add));
                                            log_err($msg);
                                        }
                                    }
                                    $sec_all_fnc_lst = $lib->arrayAddArrayBefore($sec_all_fnc_lst, $to_add, $found);
                                } else {
                                    $sec_all_fnc_lst = $lib->arrayAddAfter($sec_all_fnc_lst, $class_row, $prev, $fnc);
                                }
                            }
                        }
                        $all_fnc_lst[$sec] = $sec_all_fnc_lst;
                    } else {
                        $diff_txt = $lib->arrayOrderDiff($sec_fnc_lst_keys, $sec_all_fnc_lst_keys);
                        $msg_lst[] = 'order of section ' . $sec . ' has difference at ' . $diff_txt . ' of ' . implode(",", $sec_fnc_lst_keys)
                            . ' does not match ' . implode(",", $sec_all_fnc_lst_keys);
                    }
                } else {
                    $class_row = [];
                    $class_row[$class] = $des;
                    $fnc_row = [];
                    $fnc_row[$fnc] = $class_row;
                    $all_fnc_lst[$sec] = $fnc_row;
                }
                $prev = $fnc;
            }
        }

        if ($msg_lst == []) {
            return $all_fnc_lst;
        } else {
            return implode(",", $msg_lst);
        }
    }

}