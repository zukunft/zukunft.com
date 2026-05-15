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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class coding_rule_tests
{

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

        $test_name = 'check that the docs with all objects is updated';
        $md_txt = $this->php_class_tree();
        $doc_txt = file_get_contents(test_files::DOCS_OBJECTS);
        $t->assert($test_name, $md_txt, $doc_txt);

        $test_name = 'check that the docs with all function is updated';
        $md_txt = $this->php_function_tree();
        $doc_txt = file_get_contents(test_files::DOCS_FUNCTIONS);
        $fnc_upd = $t->assert($test_name, $md_txt, $doc_txt);
        if (!$fnc_upd and test_files::AUTO_UPDATE_HTML) {
            $t->update_path_file(test_files::DOCS_FUNCTIONS, $md_txt);
        }

        $this->php_include_tests($t, paths::MODEL);
        // TODO Prio 1 activate but take into account the const
        //$this->php_include_tests($t, paths::API);
        $this->php_include_tests($t, paths::WEB);
        $this->php_include_tests($t, test_paths::CREATE);

        $this->php_cfg_no_web_tests($t);

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

    private function php_class_list_to_md_row(array $class_tree, string $intent = '+-- '): string
    {
        $md_txt = '';
        foreach ($class_tree as $child => $info_lst) {
            if (is_string($info_lst)) {
                $md_txt .= $intent . $child . ' - ' . $info_lst . "\n";
            } else {
                if ($intent == '+-- ') {
                    $this_intent = '\-- ';
                    $next_intent = '    ' . $this_intent;
                } else {
                    $this_intent = $intent;
                    $next_intent = '    ' . $intent;
                }
                $md_txt .= $this_intent . $child . "\n";
                $md_txt .= $this->php_class_list_to_md_row($info_lst, $next_intent);
            }
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

    function php_function_list_to_md_row(array $fnc_lst, string $intent = '### ', string $code_maker = ''): string
    {
        $md_txt = '';
        foreach ($fnc_lst as $child => $info_lst) {
            if (is_string($info_lst)) {
                $md_txt .= $intent . $child . ' - ' . $info_lst . "\n";
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
                    if ($code_file != '/log_text/text_log_functions.php') {
                        $t->assert($test_name, '', $class);
                    }

                }
            }
            $pos++;
        }
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
                            if ($path_conv == $path_incl or $path_conv == '') {
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