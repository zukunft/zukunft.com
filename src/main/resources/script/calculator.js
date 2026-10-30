/*

    src/main/resources/script/calculator.js - repeat the calculation of a calculator page in the browser
    ---------------------------------------

    the only script of the html frontend (docs/llm/frontend.md "Javascript only for the
    calculator"): when an input of a calculator page changes, the results of the page are
    calculated again with the entered numbers and the cells show the new numbers

    the page gives everything as data (ui_list::calculator_script): the numbers by their html
    id, the calculation of each result as tokens, the number that each input sets and the number
    and the format of each cell; the script never runs a formula text as code, it only evaluates
    numbers, + - * / and brackets like shared/calc/arithmetic.php


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

'use strict';

(function () {

    // the html id and the keys of the data, the same as the CALC_* consts of ui_list
    const DATA_ID = 'calc-data';
    // the kinds of the tokens, the same as the TOKEN_* consts of formula_calc
    const TOKEN_NUM = 'n';
    const TOKEN_OP = 'o';
    const TOKEN_REF = 'r';
    // the math functions, the separator of their arguments and the power, the same as the chars::FUNC_* and chars::POW
    const FUNC_MIN = 'min';
    const FUNC_MAX = 'max';
    const FUNC_IF = 'if';
    const FUNC_ARG_SEPARATOR = ';';
    const POWER = '^';
    // the functions of a list like arithmetic::list_number; formula_calc::tokens has written all values of a list as the arguments
    const LIST_NUMBER = {sum: (nums) => nums.reduce((a, b) => a + b, 0), count: (nums) => nums.length, mean: (nums) => nums.reduce((a, b) => a + b, 0) / nums.length};
    // the functions of one number like arithmetic::one_number, so also round away from zero and an angle in radian
    const ONE_NUMBER = {abs: Math.abs, cos: Math.cos, exp: Math.exp, ln: Math.log, log: Math.log10, round: (x) => Math.sign(x) * Math.round(Math.abs(x)), sec: (x) => 1 / Math.cos(x), sin: Math.sin, sqrt: Math.sqrt};

    const element = document.getElementById(DATA_ID);
    if (element === null) {
        return;
    }
    const data = JSON.parse(element.textContent);

    /**
     * evaluate the tokens of one calculation with the grammar of shared/calc/arithmetic.php:
     * a sum of products of powers of signed numbers, brackets or a math function
     *
     * @param tokens the tokens of the calculation
     * @param numberOf the function that returns the number of a referenced html id or null
     * @returns the number or null if the tokens are no valid calculation, a number is missing or a division is by zero
     */
    function evaluate(tokens, numberOf) {
        let pos = 0;
        let valid = true;
        // a number that cannot be calculated e.g. a division by zero, which an if function may leave out
        let noNumber = false;

        function isOp(chars) {
            return pos < tokens.length && TOKEN_OP in tokens[pos] && chars.includes(tokens[pos][TOKEN_OP]);
        }

        function sum() {
            let result = product();
            while (valid && isOp(['+', '-'])) {
                const op = tokens[pos++][TOKEN_OP];
                const right = product();
                result = op === '+' ? result + right : result - right;
            }
            return result;
        }

        function product() {
            let result = power();
            while (valid && isOp(['*', '/'])) {
                const op = tokens[pos++][TOKEN_OP];
                const right = power();
                if (op === '*') {
                    result = result * right;
                } else if (right === 0) {
                    noNumber = true;
                } else {
                    result = result / right;
                }
            }
            return result;
        }

        // from right to left and a number keeps its sign, the same as arithmetic::power
        function power() {
            let result = signed();
            if (valid && isOp([POWER])) {
                pos++;
                result = finite(Math.pow(result, power()));
            }
            return result;
        }

        function finite(num) {
            noNumber = noNumber || !Number.isFinite(num);
            return num;
        }

        function signed() {
            let result;
            if (isOp(['-'])) {
                pos++;
                result = -signed();
            } else {
                if (isOp(['+'])) {
                    pos++;
                }
                result = atom();
            }
            return result;
        }

        function bracketClose() {
            if (isOp([')'])) {
                pos++;
            } else {
                valid = false;
            }
        }

        /* the number that pick keeps of the sums separated by the argument separator (chars::FUNC_ARG_SEPARATOR) */
        function pickArgument(pick) {
            let result = sum();
            while (valid && isOp([FUNC_ARG_SEPARATOR])) {
                pos++;
                result = pick(result, sum());
            }
            bracketClose();
            return result;
        }

        function listArguments() {
            const result = [sum()];
            while (valid && isOp([FUNC_ARG_SEPARATOR])) {
                pos++;
                result.push(sum());
            }
            bracketClose();
            return result;
        }

        function expect(chars) {
            valid = valid && isOp(chars);
            pos++;
        }

        // two sums compared by = or a sum that is true if it is not zero
        function compare() {
            const left = sum();
            let result = left !== 0;
            if (valid && isOp(['='])) {
                pos++;
                result = left === sum();
            }
            return result;
        }

        function andCondition() {
            let result = compare();
            while (valid && isOp(['&'])) {
                pos++;
                const right = compare();
                result = result && right;
            }
            return result;
        }

        function orCondition() {
            let result = andCondition();
            while (valid && isOp(['|'])) {
                pos++;
                const right = andCondition();
                result = result || right;
            }
            return result;
        }

        // the then or the else part, only a number of the chosen part counts like in arithmetic::if_function
        function ifFunction() {
            const isTrue = orCondition();
            expect([FUNC_ARG_SEPARATOR]);
            const noNumberBefore = noNumber;
            const thenNum = sum();
            const noThen = noNumber;
            noNumber = noNumberBefore;
            expect([FUNC_ARG_SEPARATOR]);
            const elseNum = sum();
            noNumber = isTrue ? noThen : noNumber;
            bracketClose();
            return isTrue ? thenNum : elseNum;
        }

        function atom() {
            let result = 0;
            if (isOp(['('])) {
                pos++;
                result = sum();
                bracketClose();
            } else if (isOp([FUNC_MIN, FUNC_MAX])) {
                const pick = isOp([FUNC_MIN]) ? Math.min : Math.max;
                pos++;
                expect(['(']);
                result = pickArgument(pick);
            } else if (isOp([FUNC_IF])) {
                pos++;
                expect(['(']);
                result = ifFunction();
            } else if (isOp(Object.keys(ONE_NUMBER))) {
                const calc = ONE_NUMBER[tokens[pos++][TOKEN_OP]];
                expect(['(']);
                const arg = sum();
                bracketClose();
                result = finite(calc(arg));
            } else if (isOp(Object.keys(LIST_NUMBER))) {
                const calc = LIST_NUMBER[tokens[pos++][TOKEN_OP]];
                expect(['(']);
                result = finite(calc(listArguments()));
            } else if (pos < tokens.length && TOKEN_NUM in tokens[pos]) {
                result = tokens[pos++][TOKEN_NUM];
            } else if (pos < tokens.length && TOKEN_REF in tokens[pos]) {
                const num = numberOf(tokens[pos++][TOKEN_REF]);
                if (num === null) {
                    noNumber = true;
                } else {
                    result = num;
                }
            } else {
                valid = false;
            }
            return result;
        }

        const result = sum();
        return valid && !noNumber && pos === tokens.length ? result : null;
    }

    /**
     * @param numbers the numbers of the page with the entered numbers
     * @returns the numbers with each result calculated again, a result that cannot be calculated keeps its number
     */
    function calculate(numbers) {
        const done = {};
        const busy = {};

        function numberOf(key) {
            let result = key in numbers ? numbers[key] : null;
            if (key in data.results && !(key in done) && !(key in busy)) {
                // a result that uses itself keeps its number instead of an endless calculation
                busy[key] = true;
                const num = evaluate(data.results[key], numberOf);
                delete busy[key];
                if (num !== null) {
                    numbers[key] = num;
                    result = num;
                }
                done[key] = true;
            }
            return result;
        }

        Object.keys(data.results).forEach(numberOf);
        return numbers;
    }

    /**
     * @param num a number
     * @param decimals the number of decimals
     * @returns the number rounded half away from zero like the php round
     */
    function round(num, decimals) {
        const factor = Math.pow(10, decimals);
        return Math.sign(num) * Math.round(Math.abs(num) * factor) / factor;
    }

    /**
     * @param num a number
     * @param decimals the number of decimals
     * @returns the number with the decimals, the decimal point and the thousand separator of the user like the php number_format
     */
    function fixed(num, decimals) {
        const rounded = round(num, decimals);
        const parts = Math.abs(rounded).toFixed(decimals).split('.');
        const digits = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, data.format.thousand_sep);
        const sign = rounded < 0 ? '-' : '';
        return sign + digits + (decimals > 0 ? data.format.dec_point + parts[1] : '');
    }

    /**
     * @param num the number of a cell
     * @param cell the format of the cell: the factor and the decimals of the unit that the cell shows (see web\helper\number_format)
     * @returns the text of the cell like ui_list::calculator_cell_text
     */
    function text(num, cell) {
        let result;
        if (cell.percent !== null) {
            result = String(round(num * 100, data.format.percent_decimals));
            if (cell.percent !== '') {
                result += data.format.unit_sep + cell.percent;
            }
        } else {
            result = fixed(num * cell.factor, cell.decimals);
        }
        if (cell.unit !== '') {
            result += data.format.unit_sep + cell.unit;
        }
        return result;
    }

    /**
     * write the numbers into the cells of the page, a signed cell in the positive or negative style
     *
     * @param numbers the numbers of the page by their html id
     */
    function show(numbers) {
        Object.entries(data.cells).forEach(([id, cell]) => {
            const td = document.getElementById(id);
            if (td !== null && cell.number in numbers) {
                const num = numbers[cell.number];
                const txt = text(num, cell);
                if (cell.signed) {
                    const span = document.createElement('span');
                    span.className = num < 0 ? data.format.negative : data.format.positive;
                    span.textContent = txt;
                    td.replaceChildren(span);
                } else {
                    td.textContent = txt;
                }
            }
        });
    }

    /**
     * calculate the page again with the numbers of all inputs and show the results
     */
    function update() {
        const numbers = Object.assign({}, data.numbers);
        Object.entries(data.inputs).forEach(([name, key]) => {
            const input = document.getElementsByName(name)[0];
            const num = input === undefined ? NaN : parseFloat(input.value);
            if (!Number.isNaN(num)) {
                numbers[key] = num;
            }
        });
        show(calculate(numbers));
    }

    Object.keys(data.inputs).forEach((name) => {
        const input = document.getElementsByName(name)[0];
        if (input !== undefined) {
            input.addEventListener('input', update);
            input.addEventListener('change', update);
        }
    });

})();
