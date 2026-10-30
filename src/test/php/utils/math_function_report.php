<?php

/*

    test/php/utils/math_function_report.php - write the math function report to docs/math.md
    ---------------------------------------

    lists the math functions with a tested example, the functions that the formulas of the data
    use but no calculation has, and calculates every example and edge case with each evaluator,
    so that a function missing in one evaluator or a different result e.g. between the backend
    and the browser is reported; the javascript evaluators run in node with their real code
    (math_function_report.js), so without node they are reported as not run


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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SERVICE_MATH . 'calc_internal.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CALC . 'arithmetic.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'files.php';
include_once html_paths::FORMULA . 'formula_calc.php';

use Throwable;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\service\math\calc_internal;
use Zukunft\ZukunftCom\main\php\shared\calc\arithmetic;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\formula\formula_calc;

class math_function_report
{

    // the evaluators of a math expression
    const string BACKEND = 'backend';
    const string ARITHMETIC = 'arithmetic';
    const string CALCULATOR = 'calculator';
    const string CALCULATOR_JSON = 'calculator_json';
    const string FORMULA_EDITOR = 'formula_editor';
    // per evaluator the column title and where it is used
    const array EVALUATORS = [
        self::BACKEND => 'backend (`calc_internal.php`, the result that is saved)',
        self::ARITHMETIC => 'page check (`arithmetic.php`, compares a stored result on the server page)',
        self::CALCULATOR => 'calculator page (`calculator.js`)',
        self::CALCULATOR_JSON => 'json calculator page (`calculator_json.js`)',
        self::FORMULA_EDITOR => 'formula editor (`formula_editor.js`)',
    ];

    // the text of a result without a number
    const string NO_NUMBER = 'no number';
    // the start of the text of a result that crashed instead of giving a result
    const string FATAL = 'fatal: ';
    // the text of a result of an evaluator that could not be started e.g. because node is missing
    const string NOT_RUN = 'not run';
    // the part of a formula that is shown as an example, the complete formula is in the data
    const int EXAMPLE_CHARS = 90;

    // per math function or operator its description, a tested example and the result of the example
    const array FUNCTIONS = [
        '+' => ['addition', '2 + 3', '5'],
        '-' => ['subtraction, also the sign of a number', '10 - 3 - 2', '5'],
        '*' => ['multiplication, before an addition', '1 + 2 * 3', '7'],
        '/' => ['division, a division by zero gives no number', '100 / 5 / 2', '10'],
        '( )' => ['brackets are calculated first', '( 1 + 2 ) * 3', '9'],
        'min' => ['the smallest of its arguments, separated by `;`', 'min( 3 ; 1 + 1 ; 5 )', '2'],
        'max' => ['the largest of its arguments, separated by `;`', 'max( 3 ; 1 + 1 ; 5 )', '5'],
        'if' => ['the then or the else part, only the chosen part is calculated', 'if( 1 = 2 ; 3 ; 4 )', '4'],
        '=' => ['a condition of `if`: true if both sides are the same', 'if( 2 = 2 ; 3 ; 4 )', '3'],
        '&' => ['a condition of `if`: true if both conditions are true', 'if( 1 & 0 ; 3 ; 4 )', '4'],
        '|' => ['a condition of `if`: true if one condition is true', 'if( 1 | 0 ; 3 ; 4 )', '3'],
        '^' => ['power, before a multiplication and from right to left', '2 * 3 ^ 2', '18'],
        'abs' => ['the number without its sign', 'abs( -2.5 )', '2.5'],
        'cos' => ['cosine of an angle in radian', 'cos( 0 )', '1'],
        'exp' => ['e to the power of the number', 'exp( 0 )', '1'],
        'ln' => ['natural logarithm', 'ln( 1 )', '0'],
        'log' => ['logarithm to the base 10', 'log( 100 )', '2'],
        'round' => ['the nearest whole number', 'round( 2.6 )', '3'],
        'sec' => ['secant of an angle in radian: one divided by the cosine', 'sec( 0 )', '1'],
        'sin' => ['sine of an angle in radian', 'sin( 0 )', '0'],
        'sqrt' => ['square root', 'sqrt( 9 )', '3'],
        'sum' => ['the sum of a list: a phrase stands for all its stored values', 'sum( 1 ; 2 ; 3 )', '6'],
        'count' => ['the number of values of a list', 'count( 1 ; 2 ; 3 )', '3'],
        'mean' => ['the average of a list', 'mean( 1 ; 2 ; 3 )', '2'],
    ];

    // the texts on which the evaluators could differ: each gives one number or no number in all of them
    const array EDGE_CASES = [
        // a division by zero, also if the zero is calculated or the number above it is zero
        '1 / 0', '0 / 0', '5 / ( 2 - 2 )', '0 / 5',
        // signs
        '-2 * 3', '2 - -3', '2 + -3', '2 * -3', '6 / -2', '--3', '+3', '-( 1 + 2 )',
        // the order of the calculation
        '2 * 3 + 4', '10 - 2 * 3', '2 / 4 * 2',
        // the notation of a number
        '5E3', '1.5E-5 * 2', '1e3', '.5', '2.', '1000000000000000000000 * 10',
        // the rounding of a float and of an intermediate result
        '0.1 + 0.2', '( 1 / 3 ) * 3', '( 0.1 + 0.2 ) * 10',
        // an incomplete or invalid text
        '', ' ', '2 +', '* 2', '2 3', '2 x 3', '( 2 + 3', '2 + 3 )', '( )', '1 ; 2', 'abc + 1',
        // the functions with too few, wrong or nested arguments
        'min( 7 )', 'min( )', 'min( 1 ; 2', 'min 1', 'min( ; 1 )', 'min( 1 ; )', 'MIN( 1 ; 2 )', 'min( 1 ; abc )',
        'max( 7 )', 'max( )', 'max( 1 ; 2', 'max 1', 'max( ; 1 )', 'max( 1 ; )', 'max( -5 ; -2 )',
        'min( max( 1 ; 2 ) ; 3 ) * 2', '( min( 1 ; 2 ) ) * 3', 'min ( 1 ; 2 )', 'min( 1 ; 2 ) min( 3 ; 4 )',
        // the if function in its two forms
        'if( 1 ) 5', 'if( 0 ) 5', 'if( 1 ; 2 )', 'if( 1 ; 2 ; 3 ; 4 )', 'if( 1 ; 2 ; 5 / 0 )', 'if( abc ; 1 ; 2 )',
        // a function or an operator that is missing everywhere
        'tan( 1 )', '10 - tan( 1 )', 'median( 2.6 ) * 3', 'sum( ) * 3', 'sum( 1 ; abc )', '5 %',
        // the functions of one number and the power with a number that they cannot give
        'sqrt( -1 )', 'log( 0 )', 'ln( -1 )', 'exp( 1000 )', 'sqrt( 4 ; 9 )', 'sqrt( abc )',
        '0 ^ -1', '( -8 ) ^ 0.5', '2 ^ 3 ^ 2', '-2 ^ 2', '( -3 ) ^ 2', '2 ^ -1', 'round( -2.5 )', 'round( 2.5 )',
        'if( 0 ; 5 / 0 + 1 ; 2 )', 'if( 1 ; 2 ; 5 / 0 + 1 )', 'if( 1 ; 5 / 0 ; 2 )', 'if( 1 = 1 & 2 = 3 ; 5 ; 6 )', 'if( 1 = 1 | 2 = 3 ; 5 ; 6 )',
    ];

    // the formula symbols that are no function name but a math operation that the data may use
    const array SYMBOLS = ['^', '×', '÷', '−', '√', '%', '∑', '∫'];
    // the second spellings of a math operation with the one symbol that a formula has to use, because each operation has one symbol
    const array SYMBOLS_TO_REPLACE = ['×' => '*', '÷' => '/', '−' => '-', 'Σ' => 'sum', '∑' => 'sum'];
    // the folders with the formulas of the data
    const array DATA_PATHS = [files::MESSAGE_PATH, test_paths::IMPORT];

    /**
     * build the markdown report
     *
     * @return string the text of docs/math.md
     */
    function md(): string
    {
        $functions = self::FUNCTIONS;
        $txts = array_merge(array_column($functions, 1), self::EDGE_CASES);
        $results = $this->results($txts);
        $used = $this->used_functions($this->data_expressions());
        $gaps = $this->function_gaps($results);
        $diffs = $this->edge_diffs($results);
        $missing = array_diff_key($used, $functions);

        $result = "# Math functions\n\n";
        $result .= "do not edit manually: this file is written by `php bin/math_functions.php` and checked by the test run\n\n";
        $result .= "A formula calculates in up to five places. The backend calculates the result that is saved, the\n";
        $result .= "others repeat the calculation on the page, so a difference between them shows a user a number\n";
        $result .= "that the saved result does not confirm.\n\n";
        $result .= "- functions missing in one of the calculations: " . count($gaps) . "\n";
        $result .= "- edge cases with a different result: " . count($diffs) . "\n";
        $result .= "- functions used in the data that no calculation has: " . count($missing) . "\n\n";
        $result .= $this->functions_md($results);
        $result .= $this->gaps_md($gaps);
        $result .= $this->used_md($missing);
        $result .= $this->edge_md($results, $diffs);
        return $result;
    }

    /**
     * @param array $txts the math expressions to calculate
     * @return array per expression and evaluator the number as text, NO_NUMBER, a FATAL text or NOT_RUN
     */
    function results(array $txts): array
    {
        $txts = array_values(array_unique($txts));
        $js_lst = $this->js_results($txts);
        $result = [];
        foreach ($txts as $pos => $txt) {
            $result[$txt] = [
                self::BACKEND => $this->backend_result($txt),
                self::ARITHMETIC => $this->arithmetic_result($txt),
                self::CALCULATOR => $js_lst[$pos][self::CALCULATOR] ?? self::NOT_RUN,
                self::CALCULATOR_JSON => $js_lst[$pos][self::CALCULATOR_JSON] ?? self::NOT_RUN,
                self::FORMULA_EDITOR => $js_lst[$pos][self::FORMULA_EDITOR] ?? self::NOT_RUN,
            ];
        }
        return $result;
    }

    /**
     * @param array $results the results per expression and evaluator
     * @param array $functions the math functions with their example and its expected result
     * @return array per function the evaluators that do not give the expected result of its example
     */
    function function_gaps(array $results, array $functions = self::FUNCTIONS): array
    {
        $result = [];
        foreach ($functions as $name => [, $example, $expected]) {
            $missing = array_keys(array_filter($results[$example] ?? [], fn($num) => $num !== $expected));
            if ($missing != []) {
                $result[$name] = $missing;
            }
        }
        return $result;
    }

    /**
     * @param array $results the results per expression and evaluator
     * @return array the expressions with a different result in at least one evaluator
     */
    function edge_diffs(array $results): array
    {
        $result = [];
        foreach ($results as $txt => $nums) {
            // compared as numbers, because php and javascript write a small or big number differently
            $values = array_map(fn($num) => is_numeric($num) ? (float)$num : $num, $nums);
            if (count(array_unique($values, SORT_REGULAR)) > 1) {
                $result[] = $txt;
            }
        }
        return $result;
    }

    /**
     * @param array $expressions the formula expressions of the data
     * @return array per function name or symbol the number of formulas that use it and the first as an example, sorted by name
     */
    function used_functions(array $expressions): array
    {
        $calc = new calc_internal();
        $result = [];
        foreach ($expressions as $exp) {
            $without_text = $calc->without_text($exp);
            $symbols = array_filter(self::SYMBOLS, fn($sym) => str_contains($without_text, $sym));
            foreach (array_unique(array_merge($calc->function_names($exp), $symbols)) as $name) {
                $count = $result[$name][0] ?? 0;
                $result[$name] = [$count + 1, $result[$name][1] ?? $exp];
            }
        }
        ksort($result);
        return $result;
    }

    /**
     * @param array $expressions the formula expressions to check
     * @return array per expression with a second spelling of a math operation the symbol and the symbol to use
     *               e.g. "× instead of *: years = 2 × 3"; a phrase name in quotes may contain any char
     */
    function symbols_to_replace(array $expressions): array
    {
        $calc = new calc_internal();
        $result = [];
        foreach ($expressions as $exp) {
            $without_text = $calc->without_text($exp);
            foreach (self::SYMBOLS_TO_REPLACE as $symbol => $replacement) {
                if (str_contains($without_text, $symbol)) {
                    $result[] = $symbol . ' instead of ' . $replacement . ': ' . $exp;
                }
            }
        }
        return $result;
    }

    /**
     * @return array the expressions of all formulas of the data in the order of the sorted files
     */
    function data_expressions(): array
    {
        $result = [];
        foreach ($this->data_files() as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (is_array($json)) {
                $result = array_merge($result, $this->json_expressions($json));
            }
        }
        return $result;
    }

    /**
     * @return array the sorted paths of all json files of the data
     */
    private function data_files(): array
    {
        $result = [];
        foreach (self::DATA_PATHS as $path) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->getExtension() == 'json') {
                    $result[] = $file->getPathname();
                }
            }
        }
        sort($result);
        return $result;
    }

    /**
     * @param array $json a part of an import file
     * @return array the formula expressions in this part
     */
    private function json_expressions(array $json): array
    {
        $result = [];
        foreach ($json as $key => $val) {
            if (is_array($val)) {
                $result = array_merge($result, $this->json_expressions($val));
            } elseif ($key === json_fields::EXPRESSION and is_string($val)) {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * log messages are switched off, because an invalid text is the purpose of an edge case and no error of the system
     * @param string $txt the math expression
     * @return string the number of the backend calculation as text, NO_NUMBER or a FATAL text
     */
    private function backend_result(string $txt): string
    {
        global $sys;
        $errors = $sys?->errors;
        $writing = $sys?->log_writing;
        if ($sys !== null) {
            $sys->log_writing = true;
        }
        $result = $this->without_output(fn() => new calc_internal()->parse($txt, new user_message())); // not reported, the report shows the result
        if ($sys !== null) {
            $sys->log_writing = $writing;
            $sys->errors = $errors;
        }
        return $result;
    }

    /**
     * @param string $txt the math expression
     * @return string the number of the page check as text, NO_NUMBER or a FATAL text
     */
    private function arithmetic_result(string $txt): string
    {
        return $this->without_output(fn() => new arithmetic()->calc($txt));
    }

    /**
     * @param callable $calc returns the number or null
     * @return string the number as text, NO_NUMBER or a FATAL text if the calculation crashed
     */
    private function without_output(callable $calc): string
    {
        ob_start();
        try {
            $result = $this->number_text($calc());
        } catch (Throwable $e) {
            $result = self::FATAL . $e::class;
        }
        ob_end_clean();
        return $result;
    }

    /**
     * @param float|int|null $num the result of a calculation
     * @return string the complete number without a .0 before the end or the exponent or NO_NUMBER
     */
    function number_text(float|int|null $num): string
    {
        $result = self::NO_NUMBER;
        if ($num !== null) {
            $result = (string)json_encode((float)$num, JSON_PRESERVE_ZERO_FRACTION);
            $result = preg_replace('/\.0(?=e|$)/', '', $result);
        }
        return $result;
    }

    /**
     * @param array $txts the math expressions to calculate
     * @return array per expression the results of the javascript evaluators or an empty array if node cannot be started
     */
    private function js_results(array $txts): array
    {
        $formula_calc = new formula_calc();
        $cases = [];
        foreach ($txts as $txt) {
            $cases[] = ['txt' => $txt, 'tokens' => $formula_calc->arithmetic_tokens($txt)];
        }
        $input = json_encode(['dir' => paths::SCRIPT_RES, 'cases' => $cases]);
        $pipes = [];
        $spec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $proc = @proc_open(['node', test_files::MATH_FUNCTION_REPORT_JS], $spec, $pipes);
        $result = [];
        if (is_resource($proc)) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
            $result = json_decode($output, true) ?? [];
        }
        return $result;
    }

    /**
     * @param array $results the results per expression and evaluator
     * @return string the table of the math functions with the example and its result
     */
    private function functions_md(array $results): string
    {
        $result = "## Functions\n\n";
        $result .= "| function | what it does | example | result | missing in |\n| --- | --- | --- | ---: | --- |\n";
        foreach (self::FUNCTIONS as $name => [$description, $example, $expected]) {
            $gaps = $this->function_gaps($results, [$name => self::FUNCTIONS[$name]]);
            $missing = implode(', ', $gaps[$name] ?? []);
            $result .= '| ' . $this->code($name) . ' | ' . $description . ' | ' . $this->code($example)
                . ' | ' . $expected . ' | ' . $missing . " |\n";
        }
        return $result . "\n";
    }

    /**
     * @param array $gaps per function the evaluators without it
     * @return string the list of the evaluators that miss a function
     */
    private function gaps_md(array $gaps): string
    {
        $result = "## Missing in a calculation\n\n";
        $result .= "A function that one calculation does not have gives no number there, so a page keeps the stored result.\n\n";
        foreach ($gaps as $name => $evaluators) {
            $result .= '- ' . $this->code($name) . ': ' . implode(', ', $evaluators) . "\n";
        }
        if ($gaps == []) {
            $result .= "Every function is in every calculation.\n";
        }
        return $result . "\n";
    }

    /**
     * @param array $missing per function that no calculation has the number of formulas and an example
     * @return string the table of the functions that the data uses but no calculation has
     */
    private function used_md(array $missing): string
    {
        $result = "## Used in the data but not calculated\n\n";
        $result .= "A formula may name such a function e.g. to document a law; calculating it shows the user an error.\n\n";
        $result .= "| function | formulas | example |\n| --- | ---: | --- |\n";
        foreach ($missing as $name => [$count, $example]) {
            $result .= '| ' . $this->code((string)$name) . ' | ' . $count . ' | ' . $this->code($this->short($example)) . " |\n";
        }
        return $result . "\n";
    }

    /**
     * @param array $results the results per expression and evaluator
     * @param array $diffs the expressions with a different result
     * @return string the table of the edge cases with the result per evaluator
     */
    private function edge_md(array $results, array $diffs): string
    {
        $result = "## Edge cases\n\nEach text is calculated by each calculation; a difference is marked with **diff**.\n\n";
        foreach (self::EVALUATORS as $key => $title) {
            $result .= '- ' . $key . ': ' . $title . "\n";
        }
        $result .= "\n| text | " . implode(' | ', array_keys(self::EVALUATORS)) . " | |\n";
        $result .= '| --- |' . str_repeat(' ---: |', count(self::EVALUATORS)) . " --- |\n";
        foreach (self::EDGE_CASES as $txt) {
            $mark = in_array($txt, $diffs, true) ? '**diff**' : '';
            $result .= '| ' . $this->code($txt) . ' | ' . implode(' | ', $results[$txt]) . ' | ' . $mark . " |\n";
        }
        return $result;
    }

    /**
     * @param string $txt a text of a table cell
     * @return string the text as code that does not break the table
     */
    private function code(string $txt): string
    {
        $result = '(empty)';
        if ($txt != '') {
            $result = '(blank)';
        }
        if (trim($txt) != '') {
            $result = '`' . str_replace('|', '\|', $txt) . '`';
        }
        return $result;
    }

    /**
     * @param string $txt a formula
     * @return string the start of the formula for the display in a table
     */
    private function short(string $txt): string
    {
        $result = $txt;
        if (mb_strlen($txt) > self::EXAMPLE_CHARS) {
            $result = mb_substr($txt, 0, self::EXAMPLE_CHARS) . ' …';
        }
        return $result;
    }

}
