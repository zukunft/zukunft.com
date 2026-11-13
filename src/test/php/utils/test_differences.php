<?php

/*

    test/php/utils/test_differences.php - compare the tests of test.php with the selected tests of test_part.php
    -----------------------------------

    creates the markdown report docs/test_differences.md with the tests that test/test_part.php
    (a_selected_test::run) calls in another way than test/test.php (all_tests::run_all_tests):
    test_part.php should call the same tests in the same order and switch off the tests that are
    not needed as a comment, so that a test can be switched on at its place

    a test is the call of a run* function of a *_test or *_tests class, of a global run_*_test
    function or of the test data loader test_db_load; an all_* class only calls other tests, so its
    call is replaced by the tests it calls, and a test that calls a run* function of its own object
    is listed followed by the tests it calls; a test call in a comment is listed as switched off

    each comment line is read as php code, so a quote in the text of a comment line before a
    test call hides the call; test_part.php has a quote only in the prose of its comments


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

use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_db_load;

class test_differences
{

    // the keys of the differences
    const string MISSING = 'missing';
    const string EXTRA = 'extra';
    const string REPEATED = 'repeated';
    const string MOVED = 'moved';
    const string SWITCHED_ON = 'switched on';

    // the class name prefix of the test classes that only call other tests
    const string ORCHESTRATOR_PREFIX = 'all_';
    // the name of a test class e.g. word_tests or a_selected_test
    const string TEST_CLASS_PATTERN = '/_tests?$/';
    // the name prefix of a test function e.g. run or run_list
    const string RUN_PREFIX = 'run';
    // the name of a global test function e.g. run_system_test
    const string GLOBAL_TEST_PATTERN = '/^run_\w*test$/';
    // the name of a function in its declaration
    const string FUNCTION_NAME_PATTERN = '/^\w+$/';
    // the file name used for the code of code_steps
    const string SNIPPET = 'code snippet';
    const string PHP_OPEN_TAG = '<?php ';
    const string PHP_EXTENSION = 'php';
    const string THIS = '$this';
    const string ON = 'on';
    const string OFF = 'off';

    // the tokens that start a class like body, whose functions are methods and not global functions
    const array CLASS_TOKENS = [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM];
    // the tokens of a class or function name
    const array NAME_TOKENS = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];
    // the tokens that open a code block like the "{" char
    const array BLOCK_OPEN_TOKENS = ['{', T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES];
    // the tokens without code
    const array NO_CODE_TOKENS = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
    // the tokens before a name that make the name to something else than a global function call
    const array NO_FUNCTION_CALL_TOKENS = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW];
    const array OBJECT_OPERATOR_TOKENS = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR];
    // the tokens after use that import a function or a const and not a class
    const array NO_CLASS_USE_TOKENS = [T_FUNCTION, T_CONST];
    // the tokens before a function keyword
    const array MODIFIER_TOKENS = [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_ABSTRACT, T_FINAL];
    const array NOT_PUBLIC_TOKENS = [T_PRIVATE, T_PROTECTED];

    // the class name with namespace => the parent class and the function name => the body position
    private array $cls_lst = [];
    // the class name without namespace => the class names with namespace
    private array $short_lst = [];
    // the global function name => the body position
    private array $fnc_lst = [];
    // the file name => the php tokens of the file
    private array $tkn_lst = [];
    // the file name => the namespace and the use alias list of the file
    private array $ctx_lst = [];

    /**
     * build the markdown report of the tests that test_part.php calls in another way than test.php
     *
     * @return string the markdown content for docs/test_differences.md
     */
    function md(): string
    {
        $this->index_path(TEST_PHP_PATH);
        $part_lst = $this->script_steps(test_files::TEST_PART_SCRIPT, []);
        // test.php runs the selected tests of test_part.php only in its quick mode, so they are no part of the full run
        $full_lst = $this->script_steps(test_files::TEST_ALL_SCRIPT, $this->entry_keys(test_files::TEST_PART_SCRIPT));
        $dif_lst = $this->differences($full_lst, $part_lst);
        return $this->report_md($full_lst, $part_lst, $dif_lst);
    }

    /**
     * list the test calls of a php code without the tests that they call
     *
     * @param string $php the php code incl. the open tag e.g. "<?php new word_tests()->run($t);"
     * @return array the key of each test call e.g. "word_tests::run" and true if it is not switched off by a comment
     */
    function code_steps(string $php): array
    {
        $this->index_tokens(self::SNIPPET, token_get_all($php));
        $var_lst = [];
        $tkn_lst = $this->tkn_lst[self::SNIPPET];
        $stp_lst = $this->body_steps($tkn_lst, 0, count($tkn_lst), self::SNIPPET, '', true, $var_lst);
        $result = [];
        foreach ($stp_lst as [$cls, $fnc, $on]) {
            $result[] = [$this->key($cls, $fnc), $on];
        }
        return $result;
    }

    /**
     * compare the tests of test.php with the tests of test_part.php
     *
     * @param array $full_lst the key and the switched on flag of each test of test.php in the order of the calls
     * @param array $part_lst the key and the switched on flag of each test of test_part.php in the order of the calls
     * @return array the rows of MISSING, EXTRA, REPEATED, MOVED and SWITCHED_ON with the positions counted from one
     */
    function differences(array $full_lst, array $part_lst): array
    {
        $full_keys = array_column($full_lst, 0);
        $part_keys = array_column($part_lst, 0);
        $pairs = $this->common_pairs($full_keys, $part_keys);
        $moved = $this->moved_pairs($full_keys, $part_keys, $pairs);
        $all_pairs = array_merge($pairs, $moved);
        $result = [];
        $result[self::MISSING] = $this->missing_rows($full_lst, $all_pairs);
        $result = array_merge($result, $this->extra_rows($full_lst, $part_lst, $all_pairs));
        $result[self::MOVED] = [];
        foreach ($moved as [$i, $j]) {
            $result[self::MOVED][] = [$i + 1, $j + 1, $full_keys[$i]];
        }
        $result[self::SWITCHED_ON] = [];
        // a test switched off in test.php is e.g. waiting for a fix, so test_part.php should not switch it on
        foreach ($this->sorted_pairs($all_pairs) as [$i, $j]) {
            if ($part_lst[$j][1] and !$full_lst[$i][1]) {
                $result[self::SWITCHED_ON][] = [$i + 1, $j + 1, $full_keys[$i]];
            }
        }
        return $result;
    }


    /*
     * index
     */

    /**
     * read the classes and functions of all php files of a folder and its sub folders
     * @param string $path the folder e.g. src/test/php
     */
    private function index_path(string $path): void
    {
        $file_lst = [];
        $dir_itr = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($dir_itr as $file) {
            if ($file->getExtension() == self::PHP_EXTENSION) {
                $file_lst[] = $file->getPathname();
            }
        }
        // sorted, so that the report does not depend on the file order of the file system
        sort($file_lst);
        foreach ($file_lst as $file_name) {
            $this->index_tokens($file_name, token_get_all(file_get_contents($file_name)));
        }
    }

    /**
     * remember the tokens, the namespace, the classes and the functions of one php file
     * @param string $file the file name that the positions of the function bodies refer to
     * @param array $tkn_lst the php tokens of the file
     */
    private function index_tokens(string $file, array $tkn_lst): void
    {
        $this->tkn_lst[$file] = $tkn_lst;
        $this->ctx_lst[$file] = $this->name_context($tkn_lst);
        $cls = '';
        $cls_end = -1;
        $i = 0;
        while ($i < count($tkn_lst)) {
            if ($i > $cls_end) {
                $cls = '';
            }
            $id = $this->id($tkn_lst[$i]);
            if (in_array($id, self::CLASS_TOKENS, true) and $this->is_class_declaration($tkn_lst, $i)) {
                [$cls, $cls_end] = $this->add_class($tkn_lst, $i, $file);
            } elseif ($id === T_FUNCTION) {
                $i = $this->add_function($tkn_lst, $i, $file, $cls);
            }
            $i++;
        }
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of a class, interface, trait or enum keyword
     * @return bool true if the keyword starts a declaration and is not e.g. the name of word::class
     */
    private function is_class_declaration(array $tkn_lst, int $i): bool
    {
        $prev = $this->prev_code($tkn_lst, $i);
        $prev_id = $prev < 0 ? null : $this->id($tkn_lst[$prev]);
        $next = $this->next_code($tkn_lst, $i, count($tkn_lst));
        $is_named = $this->is_at($tkn_lst, $next, count($tkn_lst), T_STRING);
        return ($is_named and !in_array($prev_id, [T_DOUBLE_COLON, T_NEW], true));
    }

    /**
     * add the class that is declared at the given position to the class list
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the class keyword
     * @param string $file the file name to resolve the parent class name
     * @return array the class name with namespace and the position of the end of the class body
     */
    private function add_class(array $tkn_lst, int $i, string $file): array
    {
        $cnt = count($tkn_lst);
        $name = $tkn_lst[$this->next_code($tkn_lst, $i, $cnt)][1];
        $cls = $this->full_name($name, $file);
        $parent = '';
        $k = $i;
        while ($k < $cnt and $tkn_lst[$k] !== '{') {
            if ($this->id($tkn_lst[$k]) === T_EXTENDS) {
                $parent = $this->full_name($tkn_lst[$this->next_code($tkn_lst, $k, $cnt)][1], $file);
            }
            $k++;
        }
        $this->cls_lst[$cls] = ['parent' => $parent, 'fnc' => []];
        $this->short_lst[$name][] = $cls;
        return [$cls, $this->block_end($tkn_lst, $k)];
    }

    /**
     * add the named function that is declared at the given position to the function list of its class
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the function keyword
     * @param string $file the file name that the position of the body refers to
     * @param string $cls the class name with namespace or an empty string for a global function
     * @return int the position of the end of the function, so that the body is not read as a declaration
     */
    private function add_function(array $tkn_lst, int $i, string $file, string $cls): int
    {
        $cnt = count($tkn_lst);
        $k = $this->next_code($tkn_lst, $i, $cnt);
        if ($this->is_at($tkn_lst, $k, $cnt, '&')) {
            $k = $this->next_code($tkn_lst, $k, $cnt);
        }
        $result = $i;
        // a closure has no name and is part of the body of its function
        if ($k < $cnt and is_array($tkn_lst[$k]) and preg_match(self::FUNCTION_NAME_PATTERN, $tkn_lst[$k][1])) {
            $name = $tkn_lst[$k][1];
            $b = $this->paren_end($tkn_lst, $this->next_code($tkn_lst, $k, $cnt));
            while ($b < $cnt and $tkn_lst[$b] !== '{' and $tkn_lst[$b] !== ';') {
                $b++;
            }
            $result = $b;
            if ($this->is_at($tkn_lst, $b, $cnt, '{')) {
                $result = $this->block_end($tkn_lst, $b);
                $pos = [$file, $b + 1, $result, $this->is_public($tkn_lst, $i)];
                if ($cls == '') {
                    $this->fnc_lst[$name] = $pos;
                } else {
                    $this->cls_lst[$cls]['fnc'][$name] = $pos;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the function keyword
     * @return bool false if the function is private or protected, so that test_part.php cannot call it
     */
    private function is_public(array $tkn_lst, int $i): bool
    {
        $k = $this->prev_code($tkn_lst, $i);
        $result = true;
        while ($k >= 0 and in_array($this->id($tkn_lst[$k]), self::MODIFIER_TOKENS, true)) {
            if (in_array($this->id($tkn_lst[$k]), self::NOT_PUBLIC_TOKENS, true)) {
                $result = false;
            }
            $k = $this->prev_code($tkn_lst, $k);
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @return array the namespace and the class name with namespace of each use alias of the file
     */
    private function name_context(array $tkn_lst): array
    {
        $ns = '';
        $use_lst = [];
        $depth = 0;
        foreach ($tkn_lst as $i => $tkn) {
            $id = $this->id($tkn);
            if (in_array($id, self::BLOCK_OPEN_TOKENS, true)) {
                $depth++;
            } elseif ($id === '}') {
                $depth--;
            } elseif ($id === T_NAMESPACE and $depth == 0) {
                $ns = $tkn_lst[$this->next_code($tkn_lst, $i, count($tkn_lst))][1];
            } elseif ($id === T_USE and $depth == 0) {
                $this->add_use($tkn_lst, $i, $use_lst);
            }
        }
        return ['ns' => $ns, 'use' => $use_lst];
    }

    /**
     * add the classes of a use statement e.g. "use a\b as c;" to the alias list
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the use keyword
     * @param array $use_lst the alias => the class name with namespace
     */
    private function add_use(array $tkn_lst, int $i, array &$use_lst): void
    {
        $cnt = count($tkn_lst);
        $k = $this->next_code($tkn_lst, $i, $cnt);
        // the use of a closure at the top of a script is followed by the vars in brackets
        $done = ($k >= $cnt or $tkn_lst[$k] === '(' or in_array($this->id($tkn_lst[$k]), self::NO_CLASS_USE_TOKENS, true));
        $name = '';
        $alias = '';
        $is_alias = false;
        while (!$done and $k < $cnt) {
            $id = $this->id($tkn_lst[$k]);
            if ($id === T_AS) {
                $is_alias = true;
            } elseif (in_array($id, self::NAME_TOKENS, true) and $is_alias) {
                $alias = $tkn_lst[$k][1];
            } elseif (in_array($id, self::NAME_TOKENS, true)) {
                $name = ltrim($tkn_lst[$k][1], '\\');
            } elseif ($id === ',' or $id === ';') {
                $use_lst[$alias == '' ? $this->short($name) : $alias] = $name;
                $alias = '';
                $is_alias = false;
                $done = ($id === ';');
            }
            $k++;
        }
    }


    /*
     * steps
     */

    /**
     * list the tests of a test runner script with the tests that they call
     * @param string $file the script e.g. test/test.php
     * @param array $skip the keys of the tests that are not listed and not expanded
     * @return array the key and the switched on flag of each test
     */
    private function script_steps(string $file, array $skip): array
    {
        $this->index_tokens($file, token_get_all(file_get_contents($file)));
        $result = [];
        $var_lst = [];
        $tkn_lst = $this->tkn_lst[$file];
        // the script calls the main test function, which is not a test itself
        foreach ($this->body_steps($tkn_lst, 0, count($tkn_lst), $file, '', true, $var_lst) as [$cls, $fnc, $on]) {
            $key = $this->key($cls, $fnc);
            if (!in_array($key, $skip)) {
                $result = array_merge($result, $this->children($cls, $fnc, $on, [$key], $skip, true));
            }
        }
        return $result;
    }

    /**
     * @param string $file the test runner script e.g. test/test_part.php
     * @return array the keys of the test functions that the script calls itself e.g. a_selected_test::run
     */
    private function entry_keys(string $file): array
    {
        $var_lst = [];
        $tkn_lst = $this->tkn_lst[$file];
        $result = [];
        foreach ($this->body_steps($tkn_lst, 0, count($tkn_lst), $file, '', true, $var_lst) as [$cls, $fnc]) {
            $result[] = $this->key($cls, $fnc);
        }
        return $result;
    }

    /**
     * list a test and the tests that it calls
     * @param string $cls the class name with namespace or an empty string for a global function
     * @param string $fnc the name of the test function
     * @param bool $on false if the test or a test that calls it is switched off by a comment
     * @param array $stack the keys of the tests that call this test to stop a recursion
     * @param array $skip the keys of the tests that are not listed and not expanded
     * @return array the key and the switched on flag of the test and the tests it calls
     */
    private function expanded(string $cls, string $fnc, bool $on, array $stack, array $skip): array
    {
        $key = $this->key($cls, $fnc);
        $is_runner = $this->is_orchestrator($cls);
        $result = [];
        if (!in_array($key, $stack) and !in_array($key, $skip)) {
            // a private test function is part of the public test that calls it, because test_part.php cannot call it
            if (!$is_runner and $this->is_callable($cls, $fnc)) {
                $result[] = [$key, $on];
            }
            // the test data loader calls its own helpers, which are no tests
            if ($cls != test_db_load::class) {
                $result = array_merge($result, $this->children($cls, $fnc, $on, [...$stack, $key], $skip, $is_runner));
            }
        }
        return $result;
    }

    /**
     * list the tests that a test function calls with the tests that they call
     * @param string $cls the class name with namespace of the object or an empty string for a global function
     * @param string $fnc the name of the test function
     * @param bool $on false if the test or a test that calls it is switched off by a comment
     * @param array $stack the keys of the calling tests incl. this test to stop a recursion
     * @param array $skip the keys of the tests that are not listed and not expanded
     * @param bool $is_runner true if the function only calls other tests, so that its test data loads are steps of the run
     * @return array the key and the switched on flag of each called test
     */
    private function children(string $cls, string $fnc, bool $on, array $stack, array $skip, bool $is_runner): array
    {
        $result = [];
        $pos = $this->definition($cls, $fnc);
        if ($pos != null) {
            [$file, $start, $end] = $pos;
            $var_lst = [];
            $stp_lst = $this->body_steps($this->tkn_lst[$file], $start, $end, $file, $cls, true, $var_lst);
            foreach ($stp_lst as [$sub_cls, $sub_fnc, $sub_on]) {
                // the test data that a test loads for itself is part of the test
                if ($is_runner or $sub_cls != test_db_load::class) {
                    $sub_lst = $this->expanded($sub_cls, $sub_fnc, ($on and $sub_on), $stack, $skip);
                    $result = array_merge($result, $sub_lst);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $cls the class name with namespace of the object or an empty string for a global function
     * @param string $fnc the name of the test function
     * @return bool true if test_part.php can call the function, which is also true for a function of an unknown class
     */
    private function is_callable(string $cls, string $fnc): bool
    {
        $pos = $this->definition($cls, $fnc);
        return ($pos == null or $pos[3]);
    }

    /**
     * list the test calls of a part of the php tokens incl. the test calls in the comments
     * @param array $tkn_lst the php tokens of a file
     * @param int $start the position of the first token to read
     * @param int $end the position after the last token to read
     * @param string $file the file name to resolve the class names
     * @param string $cls the class name with namespace of the object that $this is
     * @param bool $on false if the tokens are the code of a comment
     * @param array $var_lst the var name => the class name with namespace of the object that the var holds
     * @return array the class name, the function name and the switched on flag of each test call
     */
    private function body_steps(array $tkn_lst, int $start, int $end, string $file, string $cls, bool $on, array &$var_lst): array
    {
        $result = [];
        // the assignments of the statement, which change the var only after the value e.g. "$x = $x->run()" is read
        $asg_lst = [];
        for ($i = $start; $i < $end; $i++) {
            if ($this->id($tkn_lst[$i]) === T_COMMENT) {
                $cmt_lst = $this->comment_steps($tkn_lst[$i][1], $file, $cls, $var_lst);
                $result = array_merge($result, $cmt_lst);
            } else {
                $asg = $this->assignment_at($tkn_lst, $i, $end, $file);
                if ($asg != null) {
                    $asg_lst[] = $asg;
                }
                if ($tkn_lst[$i] === ';') {
                    $this->assign($asg_lst, $var_lst);
                    $asg_lst = [];
                }
                $stp = $this->call_at($tkn_lst, $i, $end, $file, $cls, $var_lst);
                if ($stp != null and $this->is_step($stp[0], $stp[1])) {
                    $result[] = [$stp[0], $stp[1], $on];
                }
            }
        }
        // e.g. a commented assignment without the closing semicolon
        $this->assign($asg_lst, $var_lst);
        return $result;
    }

    /**
     * list the test calls of the code in a comment as switched off
     * @param string $txt the comment incl. the comment chars e.g. "//new word_tests()->run($t);"
     * @param string $file the file name to resolve the class names
     * @param string $cls the class name with namespace of the object that $this is
     * @param array $var_lst the var name => the class name with namespace of the object that the var holds
     * @return array the class name, the function name and false of each test call
     */
    private function comment_steps(string $txt, string $file, string $cls, array &$var_lst): array
    {
        $result = [];
        foreach ($this->comment_lines($txt) as $line) {
            // each line on its own, so that a quote in the text of one line does not hide the code of the next lines
            $tkn_lst = token_get_all(self::PHP_OPEN_TAG . $line);
            $stp_lst = $this->body_steps($tkn_lst, 0, count($tkn_lst), $file, $cls, false, $var_lst);
            $result = array_merge($result, $stp_lst);
        }
        return $result;
    }

    /**
     * @param string $txt the comment incl. the comment chars
     * @return array the lines of the comment without the comment chars
     */
    private function comment_lines(string $txt): array
    {
        if (str_starts_with($txt, '//')) {
            $result = [substr($txt, 2)];
        } elseif (str_starts_with($txt, '#')) {
            $result = [substr($txt, 1)];
        } else {
            $result = [];
            foreach (explode("\n", substr($txt, 2, -2)) as $line) {
                $result[] = ltrim(ltrim($line), '*');
            }
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the token to check
     * @param int $end the position after the last token to read
     * @param string $file the file name to resolve the class names
     * @return array|null the var and the class name with namespace of the new object e.g. of
     *                    "$t_db = new test_db_load($this);", an empty class name if the var gets another value
     *                    or null if no var gets a value at the position
     */
    private function assignment_at(array $tkn_lst, int $i, int $end, string $file): ?array
    {
        $k = $this->next_code($tkn_lst, $i, $end);
        $result = null;
        if ($this->id($tkn_lst[$i]) === T_VARIABLE and $this->is_at($tkn_lst, $k, $end, '=')) {
            $n = $this->next_code($tkn_lst, $k, $end);
            $c = $this->next_code($tkn_lst, $n, $end);
            $result = [$tkn_lst[$i][1], ''];
            if ($this->is_at($tkn_lst, $n, $end, T_NEW) and $this->is_name_at($tkn_lst, $c, $end)) {
                $result = [$tkn_lst[$i][1], $this->resolve($tkn_lst[$c][1], $file)];
            }
        }
        return $result;
    }

    /**
     * remember the class of each var that gets a new object and forget it for a var that gets another value
     * @param array $asg_lst the var and the class name of each assignment of a statement
     * @param array $var_lst the var name => the class name with namespace of the object that the var holds
     */
    private function assign(array $asg_lst, array &$var_lst): void
    {
        foreach ($asg_lst as [$var, $cls]) {
            if ($cls == '') {
                unset($var_lst[$var]);
            } else {
                $var_lst[$var] = $cls;
            }
        }
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the token to check
     * @param int $end the position after the last token to read
     * @param string $file the file name to resolve the class names
     * @param string $cls the class name with namespace of the object that $this is
     * @param array $var_lst the var name => the class name with namespace of the object that the var holds
     * @return array|null the class name and the function name of the call that starts at the position or null
     */
    private function call_at(array $tkn_lst, int $i, int $end, string $file, string $cls, array $var_lst): ?array
    {
        $id = $this->id($tkn_lst[$i]);
        $result = null;
        if ($id === T_NEW) {
            $result = $this->new_call($tkn_lst, $i, $end, $file);
        } elseif ($id === T_VARIABLE) {
            $var = $tkn_lst[$i][1];
            $var_cls = $var == self::THIS ? $cls : ($var_lst[$var] ?? '');
            $fnc = $this->method_at($tkn_lst, $i, $end);
            if ($var_cls != '' and $fnc != '') {
                $result = [$var_cls, $fnc];
            }
        } elseif ($id === T_STRING and $this->is_global_call($tkn_lst, $i, $end)) {
            $result = ['', $tkn_lst[$i][1]];
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of a new keyword e.g. of "new word_tests()->run($t)" or "(new word_tests)->run($t)"
     * @param int $end the position after the last token to read
     * @param string $file the file name to resolve the class name
     * @return array|null the class name and the function name of the call or null if the new object is not called
     */
    private function new_call(array $tkn_lst, int $i, int $end, string $file): ?array
    {
        $result = null;
        $c = $this->next_code($tkn_lst, $i, $end);
        if ($this->is_name_at($tkn_lst, $c, $end)) {
            $k = $this->next_code($tkn_lst, $c, $end);
            if ($this->is_at($tkn_lst, $k, $end, '(')) {
                $k = $this->next_code($tkn_lst, $this->paren_end($tkn_lst, $k), $end);
            }
            if ($this->is_at($tkn_lst, $k, $end, ')')) {
                $k = $this->next_code($tkn_lst, $k, $end);
            }
            $fnc = $this->method_at($tkn_lst, $this->prev_code($tkn_lst, $k), $end);
            if ($fnc != '') {
                $result = [$this->resolve($tkn_lst[$c][1], $file), $fnc];
            }
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of the token before the object operator e.g. of $t_db in "$t_db->load($t)"
     * @param int $end the position after the last token to read
     * @return string the name of the called function or an empty string if no function is called
     */
    private function method_at(array $tkn_lst, int $i, int $end): string
    {
        $o = $this->next_code($tkn_lst, $i, $end);
        $f = $this->next_code($tkn_lst, $o, $end);
        $p = $this->next_code($tkn_lst, $f, $end);
        $is_call = ($o < $end and in_array($this->id($tkn_lst[$o]), self::OBJECT_OPERATOR_TOKENS, true));
        $result = '';
        if ($is_call and $this->is_at($tkn_lst, $f, $end, T_STRING) and $this->is_at($tkn_lst, $p, $end, '(')) {
            $result = $tkn_lst[$f][1];
        }
        return $result;
    }

    /**
     * @param array $tkn_lst the php tokens of a file
     * @param int $i the position of a name
     * @param int $end the position after the last token to read
     * @return bool true if the name is the call of a global test function e.g. "run_system_test($t)"
     */
    private function is_global_call(array $tkn_lst, int $i, int $end): bool
    {
        $prev = $this->prev_code($tkn_lst, $i);
        $prev_id = $prev < 0 ? null : $this->id($tkn_lst[$prev]);
        $is_call = $this->is_at($tkn_lst, $this->next_code($tkn_lst, $i, $end), $end, '(');
        $is_test = preg_match(self::GLOBAL_TEST_PATTERN, $tkn_lst[$i][1]) == 1;
        return ($is_call and $is_test and !in_array($prev_id, self::NO_FUNCTION_CALL_TOKENS, true));
    }

    /**
     * @param string $cls the class name with namespace or an empty string for a global function
     * @param string $fnc the name of the called function
     * @return bool true if the call is a test, a test runner or a test data load
     */
    private function is_step(string $cls, string $fnc): bool
    {
        $name = $this->short($cls);
        $is_test_cls = (str_starts_with($name, self::ORCHESTRATOR_PREFIX) or preg_match(self::TEST_CLASS_PATTERN, $name) == 1);
        if ($cls == '') {
            $result = true;
        } elseif ($cls == test_db_load::class) {
            $result = true;
        } else {
            $result = ($is_test_cls and str_starts_with($fnc, self::RUN_PREFIX));
        }
        return $result;
    }

    /**
     * @param string $cls the class name with namespace
     * @return bool true if the class only calls other tests e.g. all_unit_tests
     */
    private function is_orchestrator(string $cls): bool
    {
        return str_starts_with($this->short($cls), self::ORCHESTRATOR_PREFIX);
    }

    /**
     * @param string $cls the class name with namespace of the object or an empty string for a global function
     * @param string $fnc the function name
     * @return array|null the file, the start and the end position of the function body incl. an inherited one
     */
    private function definition(string $cls, string $fnc): ?array
    {
        $result = null;
        if ($cls == '') {
            $result = $this->fnc_lst[$fnc] ?? null;
        }
        $cls = $this->known_class($cls);
        while ($result == null and array_key_exists($cls, $this->cls_lst)) {
            $result = $this->cls_lst[$cls]['fnc'][$fnc] ?? null;
            $cls = $this->known_class($this->cls_lst[$cls]['parent']);
        }
        return $result;
    }


    /*
     * names
     */

    /**
     * @param string $name the class name as used in the file e.g. "word_tests" or "paths"
     * @param string $file the file name of the namespace and the use aliases
     * @return string the class name with namespace or a class of another namespace with the same name
     */
    private function resolve(string $name, string $file): string
    {
        return $this->known_class($this->full_name($name, $file));
    }

    /**
     * @param string $name the class name as used in the file
     * @param string $file the file name of the namespace and the use aliases
     * @return string the class name with namespace as php resolves it
     */
    private function full_name(string $name, string $file): string
    {
        $ctx = $this->ctx_lst[$file];
        $part_lst = explode('\\', $name, 2);
        $rest = count($part_lst) > 1 ? '\\' . $part_lst[1] : '';
        if (str_starts_with($name, '\\')) {
            $result = substr($name, 1);
        } elseif (array_key_exists($part_lst[0], $ctx['use'])) {
            $result = $ctx['use'][$part_lst[0]] . $rest;
        } elseif ($ctx['ns'] != '') {
            $result = $ctx['ns'] . '\\' . $name;
        } else {
            $result = $name;
        }
        return $result;
    }

    /**
     * a commented test call is never compiled, so it can name a class without its use statement
     * @param string $cls the class name with namespace
     * @return string the class name if it is known, else the only known class with the same name
     */
    private function known_class(string $cls): string
    {
        $same_lst = $this->short_lst[$this->short($cls)] ?? [];
        $result = $cls;
        if (!array_key_exists($cls, $this->cls_lst) and count($same_lst) == 1) {
            $result = $same_lst[0];
        }
        return $result;
    }

    /**
     * @param string $cls the class name with namespace or an empty string for a global function
     * @param string $fnc the function name
     * @return string the unique name of the test e.g. "...\word_tests::run" or "run_system_test()"
     */
    private function key(string $cls, string $fnc): string
    {
        return $cls == '' ? $fnc . '()' : $cls . '::' . $fnc;
    }

    /**
     * @param string $name a class name or a test key with namespace
     * @return string the name without the namespace
     */
    private function short(string $name): string
    {
        $pos = strrpos($name, '\\');
        return $pos === false ? $name : substr($name, $pos + 1);
    }


    /*
     * tokens
     */

    /**
     * @param array|string $tkn a php token
     * @return int|string the token id or the char of a single char token
     */
    private function id(array|string $tkn): int|string
    {
        return is_array($tkn) ? $tkn[0] : $tkn;
    }

    /**
     * @return int the position of the next token with code or the end position
     */
    private function next_code(array $tkn_lst, int $i, int $end): int
    {
        $k = $i + 1;
        while ($k < $end and in_array($this->id($tkn_lst[$k]), self::NO_CODE_TOKENS, true)) {
            $k++;
        }
        return $k;
    }

    /**
     * @return int the position of the previous token with code or -1
     */
    private function prev_code(array $tkn_lst, int $i): int
    {
        $k = $i - 1;
        while ($k >= 0 and in_array($this->id($tkn_lst[$k]), self::NO_CODE_TOKENS, true)) {
            $k--;
        }
        return $k;
    }

    /**
     * @return bool true if the token at the position has the given id or char
     */
    private function is_at(array $tkn_lst, int $k, int $end, int|string $id): bool
    {
        return ($k < $end and $this->id($tkn_lst[$k]) === $id);
    }

    /**
     * @return bool true if the token at the position is a class name
     */
    private function is_name_at(array $tkn_lst, int $k, int $end): bool
    {
        return ($k < $end and in_array($this->id($tkn_lst[$k]), self::NAME_TOKENS, true));
    }

    /**
     * @return int the position of the ")" that closes the "(" at the given position
     */
    private function paren_end(array $tkn_lst, int $open): int
    {
        $depth = 0;
        $k = $open;
        $done = false;
        while (!$done and $k < count($tkn_lst)) {
            if ($tkn_lst[$k] === '(') {
                $depth++;
            } elseif ($tkn_lst[$k] === ')') {
                $depth--;
            }
            $done = ($depth == 0);
            $k++;
        }
        return $k - 1;
    }

    /**
     * @return int the position of the "}" that closes the "{" at the given position
     */
    private function block_end(array $tkn_lst, int $open): int
    {
        $depth = 0;
        $k = $open;
        $done = false;
        while (!$done and $k < count($tkn_lst)) {
            if (in_array($this->id($tkn_lst[$k]), self::BLOCK_OPEN_TOKENS, true)) {
                $depth++;
            } elseif ($tkn_lst[$k] === '}') {
                $depth--;
            }
            $done = ($depth == 0);
            $k++;
        }
        return $k - 1;
    }


    /*
     * compare
     */

    /**
     * @param array $full_keys the test keys of test.php
     * @param array $part_keys the test keys of test_part.php
     * @return array the position pairs of the longest list of tests that both call in the same order
     */
    private function common_pairs(array $full_keys, array $part_keys): array
    {
        $n = count($full_keys);
        $m = count($part_keys);
        $len = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                if ($full_keys[$i] == $part_keys[$j]) {
                    $len[$i][$j] = $len[$i + 1][$j + 1] + 1;
                } else {
                    $len[$i][$j] = max($len[$i + 1][$j], $len[$i][$j + 1]);
                }
            }
        }
        $result = [];
        $i = 0;
        $j = 0;
        while ($i < $n and $j < $m) {
            if ($full_keys[$i] == $part_keys[$j]) {
                $result[] = [$i, $j];
                $i++;
                $j++;
            } elseif ($len[$i + 1][$j] >= $len[$i][$j + 1]) {
                $i++;
            } else {
                $j++;
            }
        }
        return $result;
    }

    /**
     * @param array $full_keys the test keys of test.php
     * @param array $part_keys the test keys of test_part.php
     * @param array $pairs the position pairs of the tests in the same order
     * @return array the position pairs of the other tests that both call, but in another order
     */
    private function moved_pairs(array $full_keys, array $part_keys, array $pairs): array
    {
        $full_open = array_diff_key($full_keys, array_flip(array_column($pairs, 0)));
        $part_open = array_diff_key($part_keys, array_flip(array_column($pairs, 1)));
        $result = [];
        foreach ($full_open as $i => $key) {
            $j = array_search($key, $part_open, true);
            if ($j !== false) {
                $result[] = [$i, $j];
                unset($part_open[$j]);
            }
        }
        return $result;
    }

    /**
     * @param array $full_lst the key and the switched on flag of each test of test.php
     * @param array $pairs the position pairs of all tests that both call
     * @return array the position, the key, the switched on flag in test.php and the position in
     *               test_part.php after which the test should be added (0 for the start) of each missing test
     */
    private function missing_rows(array $full_lst, array $pairs): array
    {
        $part_pos = array_column($pairs, 1, 0);
        $after = 0;
        $result = [];
        foreach ($full_lst as $i => [$key, $on]) {
            if (array_key_exists($i, $part_pos)) {
                $after = $part_pos[$i] + 1;
            } else {
                $result[] = [$i + 1, $key, $on, $after];
            }
        }
        return $result;
    }

    /**
     * @param array $full_lst the key and the switched on flag of each test of test.php
     * @param array $part_lst the key and the switched on flag of each test of test_part.php
     * @param array $pairs the position pairs of all tests that both call
     * @return array the rows of EXTRA with the position, the key and the switched on flag of each test that
     *               test.php does not call and the rows of REPEATED with also the position of the first call in test.php
     */
    private function extra_rows(array $full_lst, array $part_lst, array $pairs): array
    {
        $found = array_flip(array_column($pairs, 1));
        $full_keys = array_column($full_lst, 0);
        $result = [self::EXTRA => [], self::REPEATED => []];
        foreach ($part_lst as $j => [$key, $on]) {
            $full_pos = array_search($key, $full_keys, true);
            if (!array_key_exists($j, $found) and $full_pos === false) {
                $result[self::EXTRA][] = [$j + 1, $key, $on];
            } elseif (!array_key_exists($j, $found)) {
                $result[self::REPEATED][] = [$j + 1, $key, $on, $full_pos + 1];
            }
        }
        return $result;
    }

    /**
     * @param array $pairs position pairs
     * @return array the pairs sorted by the position in test.php
     */
    private function sorted_pairs(array $pairs): array
    {
        usort($pairs, fn(array $a, array $b) => $a[0] <=> $b[0]);
        return $pairs;
    }


    /*
     * report
     */

    /**
     * @param array $full_lst the key and the switched on flag of each test of test.php
     * @param array $part_lst the key and the switched on flag of each test of test_part.php
     * @param array $dif_lst the result of differences()
     * @return string the markdown report
     */
    private function report_md(array $full_lst, array $part_lst, array $dif_lst): string
    {
        $full_on = count(array_filter(array_column($full_lst, 1)));
        $part_on = count(array_filter(array_column($part_lst, 1)));
        $md_txt = '# Test differences' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'generated by bin/test_differences.php - do not edit manually' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'test/test_part.php should call the tests of test/test.php in the same order' . "\n";
        $md_txt .= 'and switch off the tests that are not needed as a comment' . "\n";
        $md_txt .= "\n";
        $md_txt .= '- ' . count($full_lst) . ' tests in test.php, ' . $full_on . ' switched on' . "\n";
        $md_txt .= '- ' . count($part_lst) . ' tests in test_part.php, ' . $part_on . ' switched on' . "\n";
        $md_txt .= '- missing in test_part.php: ' . count($dif_lst[self::MISSING]) . "\n";
        $md_txt .= '- not in test.php: ' . count($dif_lst[self::EXTRA]) . "\n";
        $md_txt .= '- called more often in test_part.php: ' . count($dif_lst[self::REPEATED]) . "\n";
        $md_txt .= '- in another order: ' . count($dif_lst[self::MOVED]) . "\n";
        $md_txt .= '- switched on in test_part.php, but off in test.php: ' . count($dif_lst[self::SWITCHED_ON]) . "\n";
        return $md_txt . $this->sections_md($part_lst, $dif_lst);
    }

    /**
     * @param array $part_lst the key and the switched on flag of each test of test_part.php
     * @param array $dif_lst the result of differences()
     * @return string the markdown sections with a table for each kind of difference
     */
    private function sections_md(array $part_lst, array $dif_lst): string
    {
        $rows = [];
        foreach ($dif_lst[self::MISSING] as [$i, $key, $on, $after]) {
            $after_txt = $after == 0 ? 'at the start' : $after . ' ' . $this->short($part_lst[$after - 1][0]);
            $rows[] = [$i, $this->short($key), $this->state($on), $after_txt];
        }
        $md_txt = $this->table_md('Missing in test_part.php', ['test.php', 'test', 'state', 'add after test_part.php'], $rows);
        $rows = [];
        foreach ($dif_lst[self::EXTRA] as [$j, $key, $on]) {
            $rows[] = [$j, $this->short($key), $this->state($on)];
        }
        $md_txt .= $this->table_md('Not in test.php', ['test_part.php', 'test', 'state'], $rows);
        $rows = [];
        foreach ($dif_lst[self::REPEATED] as [$j, $key, $on, $i]) {
            $rows[] = [$j, $this->short($key), $this->state($on), $i];
        }
        $md_txt .= $this->table_md('Called more often in test_part.php', ['test_part.php', 'test', 'state', 'first call in test.php'], $rows);
        $rows = [];
        foreach ($dif_lst[self::MOVED] as [$i, $j, $key]) {
            $rows[] = [$i, $j, $this->short($key)];
        }
        $md_txt .= $this->table_md('In another order', ['test.php', 'test_part.php', 'test'], $rows);
        $rows = [];
        foreach ($dif_lst[self::SWITCHED_ON] as [$i, $j, $key]) {
            $rows[] = [$i, $j, $this->short($key)];
        }
        $md_txt .= $this->table_md('Switched on in test_part.php, but off in test.php', ['test.php', 'test_part.php', 'test'], $rows);
        return $md_txt;
    }

    /**
     * @param string $title the section title
     * @param array $header the column names
     * @param array $rows the cells of each row
     * @return string the markdown section with the table or "none"
     */
    private function table_md(string $title, array $header, array $rows): string
    {
        $md_txt = "\n" . '## ' . $title . "\n";
        $md_txt .= "\n";
        if (count($rows) == 0) {
            $md_txt .= 'none' . "\n";
        } else {
            $md_txt .= '| ' . implode(' | ', $header) . ' |' . "\n";
            $md_txt .= '|' . str_repeat(' --- |', count($header)) . "\n";
            foreach ($rows as $row) {
                $md_txt .= '| ' . implode(' | ', $row) . ' |' . "\n";
            }
        }
        return $md_txt;
    }

    /**
     * @param bool $on true if the test is not switched off by a comment
     * @return string the state of the test for the report
     */
    private function state(bool $on): string
    {
        return $on ? self::ON : self::OFF;
    }

}
