<?php

/*

    test/php/utils/test_object_overview.php - the matrix of the test object factories per object and factory type
    ---------------------------------------

    creates the markdown report docs/test_object_overview.md: the rows are the factory types
    e.g. *_filled or *_filled_add, the columns are the main objects e.g. word, and a cell says
    OK if the factory with the plain name e.g. word_filled exists, names the factories with an
    extension e.g. word_filled_zh if only these exist, or says missing if the object has no
    factory of the type; reads only the create classes and needs no database

    a factory type is a function name part after the object name e.g. "filled_add" that at least
    MIN_OBJECTS objects use with the plain name, so that a special function or a sample data name
    that only a few objects share is no type


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

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class test_object_overview
{

    // the cell texts of the matrix
    const string OK = 'OK';
    const string MISSING = 'missing';
    // a name part after the object name that at least this number of objects use with the plain name is a
    // factory type; a sample data name that two objects share e.g. word_ch and value_ch is not a type
    const int MIN_OBJECTS = 3;
    // the separator of the object name and the type in a factory name e.g. word_filled
    const string SEP = '_';
    // a function declaration in a create class e.g. "    function word_filled(): word"
    const string FUNCTION_PATTERN = '/^\s+(?:static\s+)?function\s+([a-z][a-z0-9_]*)\s*\(/m';
    const string PHP_EXTENSION = 'php';

    /**
     * build the markdown report of the factory matrix of the create classes
     *
     * @return string the markdown content for docs/test_object_overview.md
     */
    function md(): string
    {
        $obj_lst = [];
        foreach (def::MAIN_CLASSES as $class) {
            $obj_lst[] = substr(strrchr($class, '\\'), 1);
        }
        return $this->report_md($obj_lst, $this->table($obj_lst, $this->factory_names(test_paths::CREATE)));
    }

    /**
     * the names of all factory functions of the create classes
     *
     * @param string $path the folder with the create classes
     * @return array the function names sorted by name
     */
    function factory_names(string $path): array
    {
        $fnc_lst = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isFile() and $file->getExtension() == self::PHP_EXTENSION) {
                preg_match_all(self::FUNCTION_PATTERN, file_get_contents($file->getPathname()), $matches);
                $fnc_lst = array_merge($fnc_lst, $matches[1]);
            }
        }
        $fnc_lst = array_unique($fnc_lst);
        // sorted, so that the report does not depend on the file order of the file system
        sort($fnc_lst);
        return $fnc_lst;
    }

    /**
     * the factory matrix: each function belongs to the object with the longest matching name prefix,
     * the rest of the name is the factory type and a type is a row if MIN_OBJECTS use its plain name
     *
     * @param array $obj_lst the object names e.g. word in the order of the columns
     * @param array $fnc_lst the factory function names e.g. word_filled
     * @return array the type e.g. filled => the object => OK, missing or the names of the factories with an extension
     */
    function table(array $obj_lst, array $fnc_lst): array
    {
        // the object => the name rest of each of its factories
        $rest_lst = array_fill_keys($obj_lst, []);
        foreach ($fnc_lst as $fnc) {
            $obj = $this->object_of($fnc, $obj_lst);
            if ($obj != '') {
                $rest_lst[$obj][] = substr($fnc, strlen($obj . self::SEP));
            }
        }

        // the types with the number of objects that use the plain name
        $typ_cnt = [];
        foreach ($rest_lst as $rests) {
            foreach ($rests as $rest) {
                $typ_cnt[$rest] = ($typ_cnt[$rest] ?? 0) + 1;
            }
        }
        $typ_lst = array_keys(array_filter($typ_cnt, fn($cnt) => $cnt >= self::MIN_OBJECTS));
        // the most used types first, so that the common pattern is on top
        usort($typ_lst, fn($a, $b) => [$typ_cnt[$b], $a] <=> [$typ_cnt[$a], $b]);

        $result = [];
        foreach ($typ_lst as $typ) {
            foreach ($obj_lst as $obj) {
                $result[$typ][$obj] = $this->cell($obj, $typ, $rest_lst[$obj]);
            }
        }
        return $result;
    }

    /**
     * @param string $fnc a factory function name e.g. view_relation_filled
     * @param array $obj_lst the object names
     * @return string the object with the longest matching name prefix e.g. view_relation or '' if none matches
     */
    private function object_of(string $fnc, array $obj_lst): string
    {
        $result = '';
        foreach ($obj_lst as $obj) {
            if (str_starts_with($fnc, $obj . self::SEP) and strlen($obj) > strlen($result)) {
                $result = $obj;
            }
        }
        return $result;
    }

    /**
     * @param string $obj the object name e.g. word
     * @param string $typ the factory type e.g. filled
     * @param array $rests the name rests of the factories of the object e.g. filled_zh
     * @return string OK, missing or the names of the factories with an extension of the type
     */
    private function cell(string $obj, string $typ, array $rests): string
    {
        if (in_array($typ, $rests)) {
            return self::OK;
        }
        $names = [];
        foreach ($rests as $rest) {
            if (str_starts_with($rest, $typ . self::SEP)
                or str_ends_with($rest, self::SEP . $typ)
                or str_contains($rest, self::SEP . $typ . self::SEP)) {
                $names[] = '`' . $obj . self::SEP . $rest . '`';
            }
        }
        if ($names == []) {
            return self::MISSING;
        }
        return implode(', ', $names);
    }

    /**
     * @param array $obj_lst the object names in the order of the columns
     * @param array $tbl the result of table()
     * @return string the markdown report
     */
    private function report_md(array $obj_lst, array $tbl): string
    {
        $md_txt = '# Test object overview' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'generated by bin/test_object_overview.php - do not edit manually' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'the factory functions of src/test/php/create/ per object and factory type:' . "\n";
        $md_txt .= '- ' . self::OK . ': the factory with the plain name exists e.g. word_filled' . "\n";
        $md_txt .= '- a name: only factories with an extension exist e.g. word_filled_zh' . "\n";
        $md_txt .= '- ' . self::MISSING . ': the object has no factory of the type' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'a name part after the object name is a type if at least ' . self::MIN_OBJECTS
            . ' objects use it with the plain name' . "\n";
        $md_txt .= "\n";
        $md_txt .= '| type | ' . implode(' | ', $obj_lst) . ' |' . "\n";
        $md_txt .= '|---|' . str_repeat('---|', count($obj_lst)) . "\n";
        foreach ($tbl as $typ => $cells) {
            $md_txt .= '| *_' . $typ . ' | ' . implode(' | ', $cells) . ' |' . "\n";
        }
        return $md_txt;
    }

}
