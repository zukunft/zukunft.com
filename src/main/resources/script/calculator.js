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

    const element = document.getElementById(DATA_ID);
    if (element === null) {
        return;
    }
    const data = JSON.parse(element.textContent);

    /**
     * evaluate the tokens of one calculation with the grammar of shared/calc/arithmetic.php:
     * a sum of products of signed numbers or brackets
     *
     * @param tokens the tokens of the calculation
     * @param numberOf the function that returns the number of a referenced html id or null
     * @returns the number or null if the tokens are no valid calculation, a number is missing or a division is by zero
     */
    function evaluate(tokens, numberOf) {
        let pos = 0;
        let valid = true;

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
            let result = signed();
            while (valid && isOp(['*', '/'])) {
                const op = tokens[pos++][TOKEN_OP];
                const right = signed();
                if (op === '*') {
                    result = result * right;
                } else if (right === 0) {
                    valid = false;
                } else {
                    result = result / right;
                }
            }
            return result;
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

        function atom() {
            let result = 0;
            if (isOp(['('])) {
                pos++;
                result = sum();
                if (isOp([')'])) {
                    pos++;
                } else {
                    valid = false;
                }
            } else if (pos < tokens.length && TOKEN_NUM in tokens[pos]) {
                result = tokens[pos++][TOKEN_NUM];
            } else if (pos < tokens.length && TOKEN_REF in tokens[pos]) {
                const num = numberOf(tokens[pos++][TOKEN_REF]);
                if (num === null) {
                    valid = false;
                } else {
                    result = num;
                }
            } else {
                valid = false;
            }
            return result;
        }

        const result = sum();
        return valid && pos === tokens.length ? result : null;
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
                result += ' ' + cell.percent;
            }
        } else {
            result = fixed(num * cell.factor, cell.decimals);
        }
        if (cell.unit !== '') {
            result += ' ' + cell.unit;
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
