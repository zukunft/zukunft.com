/*

    src/main/resources/script/formula_editor.js - the formula editor of the formulas of a page phrase
    -------------------------------------------

    the formula editor component renders its empty host, the phraseData answer of the page phrase
    with the stored results as the seed and the session token (system_page::formula_editor); this
    script builds the expression of the selected formula as selectable tokens in that host and, if
    the view has these components, the formulas of the page in the host of the formula selector and
    the results until now against the results after the save in the host of the result compare;
    the save sends one update put message (cfg/import/update_put.php); the styles are in
    style/formula_editor.css


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

    /**
     * start the formula editor: the formula editor view starts it without options when the page is
     * read, because the host of the result compare follows the script; the calculator editor starts
     * it with its own hosts and data, then the calculator editor selects the formula, keeps the
     * changes and saves them, so the editor has no toolbar, no selector and no draft of its own
     *
     * @param options {editorHost, compareHost, doc, onChange(id, text), toast(text)} of the calculator
     *                editor or nothing for the formula editor view
     * @return {select(id), setTexts(edits), load(doc)} to select a formula by its id, to set the changed
     *         expressions by the formula id e.g. after an undo of the calculator editor and to show the
     *         data of another phrase, or null if the page has no host of the formula editor
     */
    function startFormulaEditor(options) {

        /* ------------------------------------------------------------------ */
        /* the translated texts, which the view embeds by their message id      */
        /* (system_page::formula_editor_texts), with the variables of the       */
        /* messages of the pod e.g. z$VarPhraseName$z                           */
        /* ------------------------------------------------------------------ */

        const TEXTS_ID = 'formulaEditorTexts';
        const VAR_START = 'z$';
        const VAR_END = '$z';
        const VAR = {
            phrase: 'VarPhraseName',
            formula: 'VarFormulaName',
            count: 'VarValueCount',
            countChk: 'VarValueCountCheck',
            value: 'VarValue',
            list: 'VarValueList'
        };
        const TXT = {
            save: 'calculator_save',
            title: 'formula_editor_title',
            formula: 'formula_editor_formula',
            results: 'formula_editor_results',
            formulas: 'formula_editor_formulas',
            formulasOfPage: 'formula_editor_formulas_of_page',
            phrase: 'formula_editor_phrase',
            otherPagePlaceholder: 'formula_editor_other_page_placeholder',
            load: 'formula_editor_load',
            filterPlaceholder: 'formula_editor_filter_placeholder',
            page: 'formula_editor_page',
            noChanges: 'formula_editor_no_changes',
            discardAll: 'formula_editor_discard_all',
            saveCount: 'formula_editor_save_count',
            tabCompare: 'formula_editor_tab_compare',
            tabChanges: 'formula_editor_tab_changes',
            errQuotesEmpty: 'formula_editor_err_quotes_empty',
            errCommaJoin: 'formula_editor_err_comma_join',
            errCommaPlace: 'formula_editor_err_comma_place',
            errChar: 'formula_editor_err_char',
            errCommaEnd: 'formula_editor_err_comma_end',
            errNoEqual: 'formula_editor_err_no_equal',
            errLeft: 'formula_editor_err_left',
            errRight: 'formula_editor_err_right',
            errArithmetic: 'formula_editor_err_arithmetic',
            errCompareNote: 'formula_editor_err_compare_note',
            formulaCount: 'formula_editor_formula_count',
            resultOne: 'formula_editor_result_one',
            resultMany: 'formula_editor_result_many',
            chipChanged: 'formula_editor_chip_changed',
            removeName: 'formula_editor_remove_name',
            replaceBy: 'formula_editor_replace_by',
            addPhraseTo: 'formula_editor_add_phrase_to',
            phraseCounts: 'formula_editor_phrase_counts',
            inContext: 'formula_editor_in_context',
            morePhrases: 'formula_editor_more_phrases',
            moreSymbols: 'formula_editor_more_symbols',
            phrasesOfOperand: 'formula_editor_phrases_of_operand',
            addPhrase: 'formula_editor_add_phrase',
            pickerNote: 'formula_editor_picker_note',
            searchPhrase: 'formula_editor_search_phrase',
            number: 'formula_editor_number',
            operator: 'formula_editor_operator',
            editExpression: 'formula_editor_edit_expression',
            clickToken: 'formula_editor_click_token',
            insertAfter: 'formula_editor_insert_after',
            insPhrase: 'formula_editor_ins_phrase',
            insNumber: 'formula_editor_ins_number',
            selection: 'formula_editor_selection',
            moveLeft: 'formula_editor_move_left',
            moveRight: 'formula_editor_move_right',
            remove: 'formula_editor_remove',
            selectFormula: 'formula_editor_select_formula',
            discard: 'formula_editor_discard',
            addOperandTip: 'formula_editor_add_operand_tip',
            expression: 'formula_editor_expression',
            expressionText: 'formula_editor_expression_text',
            expressionTextNote: 'formula_editor_expression_text_note',
            noValueNames: 'formula_editor_no_value_names',
            kindValue: 'formula_editor_kind_value',
            kindResult: 'formula_editor_kind_result',
            outsideTip: 'formula_editor_outside_tip',
            outside: 'formula_editor_outside',
            notCalculated: 'formula_editor_not_calculated',
            chipSame: 'formula_editor_chip_same',
            chipChanges: 'formula_editor_chip_changes',
            chipMissing: 'formula_editor_chip_missing',
            selectCompare: 'formula_editor_select_compare',
            ownResultOne: 'formula_editor_own_result_one',
            ownResultMany: 'formula_editor_own_result_many',
            ownChangesOne: 'formula_editor_own_changes_one',
            ownChangesMany: 'formula_editor_own_changes_many',
            ownMissing: 'formula_editor_own_missing',
            otherChanges: 'formula_editor_other_changes',
            noResultChanges: 'formula_editor_no_result_changes',
            noStoredResult: 'formula_editor_no_stored_result',
            usesChanged: 'formula_editor_uses_changed',
            compareNote: 'formula_editor_compare_note',
            colResult: 'formula_editor_col_result',
            untilNow: 'formula_editor_until_now',
            afterSave: 'formula_editor_after_save',
            noFormulaChanged: 'formula_editor_no_formula_changed',
            errFix: 'formula_editor_err_fix',
            formulaOne: 'formula_editor_formula_one',
            formulaMany: 'formula_editor_formula_many',
            pageResultsOne: 'formula_editor_page_results_one',
            pageResultsMany: 'formula_editor_page_results_many',
            updatePut: 'formula_editor_update_put',
            copy: 'formula_editor_copy',
            applyLocalTip: 'formula_editor_apply_local_tip',
            applyLocal: 'formula_editor_apply_local',
            savePosts: 'formula_editor_save_posts',
            saving: 'formula_editor_saving',
            podAnswered: 'formula_editor_pod_answered',
            saved: 'formula_editor_saved',
            notSaved: 'formula_editor_not_saved',
            changedOne: 'formula_editor_changed_one',
            changedMany: 'formula_editor_changed_many',
            invalidTip: 'formula_editor_invalid_tip',
            copied: 'formula_editor_copied',
            copyBlocked: 'formula_editor_copy_blocked',
            loaded: 'formula_editor_loaded',
            loadFailed: 'formula_editor_load_failed',
            applied: 'formula_editor_applied'
        };
        const textsElement = document.getElementById(TEXTS_ID);
        const TEXTS = textsElement !== null ? JSON.parse(textsElement.textContent) : {};

        /* the translated text of the message id with its variables e.g. txt(TXT.loaded, {[VAR.phrase]: name}),
           or the message id itself, so that a missing text is seen */
        function txt(txtId, vars) {
            let result = TEXTS[txtId] || txtId;
            Object.keys(vars || {}).forEach(function (key) {
                result = result.split(VAR_START + key + VAR_END).join(String(vars[key]));
            });
            return result;
        }

        /* the translated text of the message id as html */
        function txtHtml(txtId, vars) {
            const result = esc(txt(txtId, vars));
            return result;
        }

        /* the translated text of a number e.g. "1 result" or "2 results" */
        function txtCount(oneId, manyId, count) {
            const result = txt(count === 1 ? oneId : manyId, {[VAR.count]: count});
            return result;
        }

        /* ------------------------------------------------------------------ */
        /* the hosts of the three components and the toast                      */
        /* ------------------------------------------------------------------ */

        const opts = options || {};
        const embedded = opts.editorHost !== undefined;
        /* the hosts of the components, the same as system_page::FORMULA_*_HOST_ID; the view may */
        /* leave out the selector or the compare */
        const editorHost = embedded ? opts.editorHost : document.getElementById('formulaEditor');
        const selectorHost = embedded ? null : document.getElementById('formulaSelector');
        const compareHost = embedded ? (opts.compareHost || null) : document.getElementById('formulaCompare');
        const hosts = [selectorHost, editorHost, compareHost].filter(function (h) {
            return h !== null;
        });
        if (editorHost === null) {
            return null;
        }
        if (embedded) {
            editorHost.innerHTML = '<section class="col editor" aria-label="' + txtHtml(TXT.formula) + '" id="editor"></section>';
            if (compareHost !== null) {
                compareHost.innerHTML = '<section class="col compare" aria-label="' + txtHtml(TXT.results) + '"><div id="compare"></div></section>';
            }
        } else {
            buildHosts();
        }

        /* the toolbar, the formula list, the editor, the tabs of the compare and the toast of the formula editor view */
        function buildHosts() {
            if (selectorHost !== null) {
                selectorHost.innerHTML = [
                    '<section class="col formulas" aria-label="' + txtHtml(TXT.formulas) + '">',
                    '<div class="col-head"><h2>' + txtHtml(TXT.formulasOfPage) + '</h2><span class="note" id="formulaCount"></span></div>',
                    '<form class="tools" id="pageForm">',
                    '<label for="pageIn" class="note">' + txtHtml(TXT.phrase) + '</label>',
                    '<input class="inp" id="pageIn" type="text" placeholder="' + txtHtml(TXT.otherPagePlaceholder) + '">',
                    '<button class="btn" type="submit">' + txtHtml(TXT.load) + '</button>',
                    '</form>',
                    '<input class="inp" id="formulaFilter" type="search" placeholder="' + txtHtml(TXT.filterPlaceholder) + '">',
                    '<ul class="flist" id="formulaList"></ul>',
                    '</section>'
                ].join('');
            }
            editorHost.innerHTML = [
                '<header class="bar">',
                '<div class="brand"><b>' + txtHtml(TXT.title) + '</b><span id="pageName">' + txtHtml(TXT.page) + '</span></div>',
                '<div class="tools right">',
                '<span class="chip" id="changeCount">' + txtHtml(TXT.noChanges) + '</span>',
                '<button class="btn" id="btnResetAll" type="button" disabled>' + txtHtml(TXT.discardAll) + '</button>',
                '<button class="btn primary" id="btnSave" type="button" disabled>' + txtHtml(TXT.save) + '</button>',
                '</div>',
                '</header>',
                '<section class="col editor" aria-label="' + txtHtml(TXT.formula) + '" id="editor"></section>',
                '<div class="toast" id="toast" hidden></div>'
            ].join('');
            if (compareHost !== null) {
                compareHost.innerHTML = [
                    '<section class="col compare" aria-label="' + txtHtml(TXT.results) + '">',
                    '<div class="tabs" role="tablist">',
                    '<button class="tab on" type="button" data-tab="compare" role="tab">' + txtHtml(TXT.tabCompare) + '</button>',
                    '<button class="tab" type="button" data-tab="changes" role="tab">' + txtHtml(TXT.tabChanges) + '</button>',
                    '</div>',
                    '<div id="compare"></div>',
                    '<div id="changes" hidden></div>',
                    '</section>'
                ].join('');
            }
        }

        /* ------------------------------------------------------------------ */
        /* constants, the same as in shared/const/chars.php and formula_calc    */
        /* ------------------------------------------------------------------ */

        const SEED_ID = 'seed';
        const CHAR_CALC = '=';
        const TERM_DELIMITER = '"';
        const SEPARATOR = ',';
        const FUNC_MIN = 'min';
        const FUNC_MAX = 'max';
        const FUNC_IF = 'if';
        const FUNC_ARG_SEPARATOR = ';';
        /* the functions of one number like chars::FUNC_ONE_NUMBER of the backend, so also round away from zero and an angle in radian */
        const ONE_NUMBER = {abs: Math.abs, cos: Math.cos, exp: Math.exp, ln: Math.log, log: Math.log10, round: (x) => Math.sign(x) * Math.round(Math.abs(x)), sec: (x) => 1 / Math.cos(x), sin: Math.sin, sqrt: Math.sqrt};
        /* the functions and symbols that are offered after a press on more, because the basic ones are enough for most formulas */
        /* the logarithm like arithmetic::log_number, to the base 10 if no base is given */
        const LOG_NUMBER = (nums) => nums.length === 1 ? Math.log10(nums[0]) : (nums.length === 2 && nums[1] > 0 && nums[1] !== 1 ? Math.log(nums[0]) / Math.log(nums[1]) : NaN);
        /* the functions of a list like arithmetic::list_number, a plain operand stands for all its values, except a second number of the same fact (words::LIST_FUNCTION_EXCLUDED) */
        const LIST_NUMBER = {sum: (nums) => nums.reduce((a, b) => a + b, 0), count: (nums) => nums.length, mean: (nums) => nums.reduce((a, b) => a + b, 0) / nums.length};
        const LIST_EXCLUDED = ['variance low (system)', 'variance high (system)', 'confidence'];
        const MORE_FUNCTIONS = [FUNC_MIN, FUNC_MAX, FUNC_IF, ...Object.keys(ONE_NUMBER), ...Object.keys(LIST_NUMBER)];
        const MORE_STRUCTURE = [FUNC_ARG_SEPARATOR, '=', '&', '|'];
        const POWER = '^';
        /* the number of separators that a function gets when it is inserted */
        const FUNC_SEPARATORS = {[FUNC_MIN]: 1, [FUNC_MAX]: 1, [FUNC_IF]: 2};
        /* the decimals of a stored result, the same as formula_calc::DECIMALS */
        const DECIMALS = 2;
        /* the kinds of the tokens of an expression */
        const T_PHR = 'phr';
        const T_NUM = 'num';
        const T_OP = 'op';
        const T_BR = 'br';
        /* a kind of a button, which inserts the function name with its brackets as T_BR tokens */
        const T_FUNC = 'func';
        /* the max passes of the calculation chain, more than the longest chain of the page */
        const MAX_PASSES = 30;
        const API_UPDATE_PUT = '/api/updatePut/';
        const API_PHRASE_DATA = '/api/phraseData/';

        /* ------------------------------------------------------------------ */
        /* helpers                                                              */
        /* ------------------------------------------------------------------ */

        function esc(s) {
            const text = String(s === undefined || s === null ? '' : s);
            const result = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            return result;
        }

        /* round half away from zero like the php round */
        function roundTo(num, decimals) {
            const factor = Math.pow(10, decimals);
            const result = Math.sign(num) * Math.round(Math.abs(num) * factor) / factor;
            return result;
        }

        function fmt(num) {
            let result = '—';
            if (num !== null && num !== undefined && !Number.isNaN(num)) {
                let digits = DECIMALS;
                const abs = Math.abs(num);
                if (abs !== 0 && abs < 0.01) {
                    digits = 4;
                }
                result = num.toLocaleString(undefined, {maximumFractionDigits: digits});
            }
            return result;
        }

        function sameNumber(a, b) {
            let result = false;
            if (a === null && b === null) {
                result = true;
            } else if (a !== null && b !== null) {
                result = Math.abs(roundTo(a, DECIMALS) - roundTo(b, DECIMALS)) < Math.pow(10, -DECIMALS) / 2;
            }
            return result;
        }

        function isSubset(small, big) {
            let result = true;
            small.forEach(function (name) {
                if (!big.has(name)) {
                    result = false;
                }
            });
            return result;
        }

        function clone(o) {
            const result = JSON.parse(JSON.stringify(o));
            return result;
        }

        let toastTimer = null;

        function toast(text) {
            /* the calculator editor shows the message in its own toast */
            if (opts.toast !== undefined) {
                opts.toast(text);
                return;
            }
            const el = document.getElementById('toast');
            el.textContent = text;
            el.hidden = false;
            if (toastTimer !== null) {
                clearTimeout(toastTimer);
            }
            toastTimer = setTimeout(function () {
                el.hidden = true;
            }, 3500);
        }

        /* ------------------------------------------------------------------ */
        /* the model: phrases, values, stored results and formulas of the seed  */
        /* ------------------------------------------------------------------ */

        /* the stored results of the page: the calc-validation of an import file or the results of a phraseData answer */
        function seedResults(doc) {
            let result = doc['calc-validation'] || [];
            if (result.length === 0 && Array.isArray(doc.results)) {
                result = doc.results;
            }
            return result;
        }

        function buildModel(doc) {
            const phrases = new Map();
            const entries = [];
            const results = [];

            function addPhrase(name, kind, description) {
                if (name && !phrases.has(name)) {
                    phrases.set(name, {name: name, kind: kind, description: description || '', values: 0, results: 0});
                }
            }

            (doc.words || []).forEach(function (w) {
                addPhrase(w.name, 'word', w.description);
            });
            (doc.triples || []).forEach(function (t) {
                addPhrase(t.name, 'triple', t.description);
            });
            (doc.values || []).forEach(function (v, i) {
                const num = Number(v.number);
                if (!Number.isNaN(num)) {
                    const entry = {kind: 'value', src: i, words: v.words || [], wset: new Set(v.words || []), number: num, description: v.description || ''};
                    entries.push(entry);
                    entry.words.forEach(function (name) {
                        addPhrase(name, 'word', '');
                        phrases.get(name).values = phrases.get(name).values + 1;
                    });
                }
            });
            seedResults(doc).forEach(function (r, i) {
                const num = Number(r.number);
                const entry = {
                    kind: 'result', src: i, ri: results.length, words: r.words || [], wset: new Set(r.words || []),
                    stored: Number.isNaN(num) ? null : num, formula: r.formula, ctx: new Set(r.context || [])
                };
                entries.push(entry);
                results.push(entry);
                entry.words.forEach(function (name) {
                    addPhrase(name, 'word', '');
                    phrases.get(name).results = phrases.get(name).results + 1;
                });
            });
            const formulas = (doc.formulas || []).map(function (f, i) {
                const result = {
                    id: f.id === undefined || f.id === null ? null : f.id,
                    key: f.id === undefined || f.id === null ? 'n:' + i : 'id:' + f.id,
                    name: f.name,
                    description: f.description || '',
                    expression: f.expression || f.user_text || ''
                };
                return result;
            });
            const sel = (doc.selection || [])[0] || {};
            const result = {
                page: sel.name || '',
                phrases: phrases,
                entries: entries,
                results: results,
                formulas: formulas
            };
            return result;
        }

        /* ------------------------------------------------------------------ */
        /* the expression: text to tokens and back                              */
        /* ------------------------------------------------------------------ */

        /* the tokens of one side of an expression; a phrase joined to the next by the separator is one operand with several names;
           the functions, the separator of their arguments and the chars of a condition are structure tokens like the brackets */
        function tokenize(text) {
            const result = {ok: true, error: '', tokens: []};
            const re = /\s*(?:"([^"]*)"|(\d+(?:\.\d+)?)|([-+*\/^])|([();=&|]|(?:min|max|if|abs|cos|exp|ln|log|round|sec|sin|sqrt|sum|count|mean)(?=\s*\())|(,)|(\S))/y;
            let joinNext = false;
            let m = re.exec(text);
            while (result.ok && m !== null) {
                const last = result.tokens[result.tokens.length - 1];
                if (m[1] !== undefined) {
                    const name = m[1].trim();
                    if (name === '') {
                        result.ok = false;
                        result.error = txt(TXT.errQuotesEmpty);
                    } else if (joinNext && last !== undefined && last.t === T_PHR) {
                        last.names.push(name);
                    } else {
                        result.tokens.push({t: T_PHR, names: [name]});
                    }
                    joinNext = false;
                } else if (joinNext) {
                    result.ok = false;
                    result.error = txt(TXT.errCommaJoin);
                } else if (m[2] !== undefined) {
                    result.tokens.push({t: T_NUM, v: Number(m[2])});
                } else if (m[3] !== undefined) {
                    result.tokens.push({t: T_OP, c: m[3]});
                } else if (m[4] !== undefined) {
                    result.tokens.push({t: T_BR, c: m[4]});
                } else if (m[5] !== undefined) {
                    if (last !== undefined && last.t === T_PHR) {
                        joinNext = true;
                    } else {
                        result.ok = false;
                        result.error = txt(TXT.errCommaPlace);
                    }
                } else {
                    result.ok = false;
                    result.error = txt(TXT.errChar, {[VAR.value]: m[6]});
                }
                if (m[0] === '') {
                    m = null;
                } else {
                    m = re.exec(text);
                }
            }
            if (result.ok && joinNext) {
                result.ok = false;
                result.error = txt(TXT.errCommaEnd);
            }
            return result;
        }

        /* the parsed expression: the result phrases left of the = and the calculation right of it */
        function parseExpression(text) {
            const result = {ok: true, error: '', left: null, right: []};
            const pos = String(text).indexOf(CHAR_CALC);
            if (pos < 0) {
                result.ok = false;
                result.error = txt(TXT.errNoEqual);
            } else {
                const left = tokenize(text.slice(0, pos));
                const right = tokenize(text.slice(pos + 1));
                if (!left.ok) {
                    result.ok = false;
                    result.error = left.error;
                } else if (left.tokens.length !== 1 || left.tokens[0].t !== T_PHR) {
                    result.ok = false;
                    result.error = txt(TXT.errLeft);
                } else if (!right.ok) {
                    result.ok = false;
                    result.error = right.error;
                } else if (right.tokens.length === 0) {
                    result.ok = false;
                    result.error = txt(TXT.errRight);
                } else {
                    result.left = left.tokens[0];
                    result.right = right.tokens;
                    const check = evaluate(right.tokens, function () {
                        return 1;
                    }, function () {
                        return [1];
                    });
                    if (check === null) {
                        result.ok = false;
                        result.error = txt(TXT.errArithmetic);
                    }
                }
            }
            return result;
        }

        function tokenText(tok) {
            let result = '';
            if (tok.t === T_PHR) {
                result = tok.names.map(function (name) {
                    return TERM_DELIMITER + name + TERM_DELIMITER;
                }).join(SEPARATOR);
            } else if (tok.t === T_NUM) {
                result = String(tok.v);
            } else {
                result = tok.c;
            }
            return result;
        }

        function expressionText(parsed) {
            const result = tokenText(parsed.left) + ' ' + CHAR_CALC + ' ' + parsed.right.map(tokenText).join(' ');
            return result;
        }

        /* ------------------------------------------------------------------ */
        /* the calculation, the same grammar as shared/calc/arithmetic.php      */
        /* ------------------------------------------------------------------ */

        /**
         * @param tokens the tokens right of the =
         * @param numberOf the function that returns the number of a phrase token or null
         * @param numbersOf the function that returns all numbers of a phrase token in a list function
         * @returns the number or null if the tokens are no valid calculation, a number is missing or a division is by zero
         */
        function evaluate(tokens, numberOf, numbersOf) {
            let pos = 0;
            let valid = true;
            /* a number that cannot be calculated e.g. a division by zero, which an if function may leave out */
            let noNumber = false;

            function isChar(chars) {
                let result = false;
                if (pos < tokens.length) {
                    const tok = tokens[pos];
                    if (tok.t === T_OP || tok.t === T_BR) {
                        result = chars.includes(tok.c);
                    }
                }
                return result;
            }

            function sum() {
                let result = product();
                while (valid && isChar(['+', '-'])) {
                    const op = tokens[pos].c;
                    pos = pos + 1;
                    const right = product();
                    if (op === '+') {
                        result = result + right;
                    } else {
                        result = result - right;
                    }
                }
                return result;
            }

            function product() {
                let result = power();
                while (valid && isChar(['*', '/'])) {
                    const op = tokens[pos].c;
                    pos = pos + 1;
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

            /* from right to left and a number keeps its sign, the same as calc_internal::math_pow */
            function power() {
                let result = signed();
                if (valid && isChar([POWER])) {
                    pos = pos + 1;
                    result = finite(Math.pow(result, power()));
                }
                return result;
            }

            function finite(num) {
                noNumber = noNumber || !Number.isFinite(num);
                return num;
            }

            function signed() {
                let result = 0;
                if (isChar(['-'])) {
                    pos = pos + 1;
                    result = -signed();
                } else {
                    if (isChar(['+'])) {
                        pos = pos + 1;
                    }
                    result = atom();
                }
                return result;
            }

            function bracketClose() {
                if (isChar([')'])) {
                    pos = pos + 1;
                } else {
                    valid = false;
                }
            }

            /* the number that pick keeps of the sums separated by the argument separator */
            function pickArgument(pick) {
                let result = sum();
                while (valid && isChar([FUNC_ARG_SEPARATOR])) {
                    pos = pos + 1;
                    result = pick(result, sum());
                }
                bracketClose();
                return result;
            }

            function expect(chars) {
                valid = valid && isChar(chars);
                pos = pos + 1;
            }

            /* a phrase or a number with its sign up to the next separator or bracket (arithmetic::list_args_are_plain) */
            function listArgument() {
                const sign = isChar(['-']) ? -1 : 1;
                pos = sign < 0 ? pos + 1 : pos;
                const tok = tokens[pos];
                let result = [];
                if (tok !== undefined && tok.t === T_NUM) {
                    result = [sign * tok.v];
                } else if (tok !== undefined && tok.t === T_PHR && sign > 0) {
                    result = numbersOf(tok);
                } else {
                    valid = false;
                }
                pos = pos + 1;
                return result;
            }

            function listArguments() {
                const result = [...listArgument()];
                while (valid && isChar([FUNC_ARG_SEPARATOR])) {
                    pos = pos + 1;
                    result.push(...listArgument());
                }
                bracketClose();
                return result;
            }

            /* two sums compared by = or a sum that is true if it is not zero */
            function compare() {
                const left = sum();
                let result = left !== 0;
                if (valid && isChar(['='])) {
                    pos = pos + 1;
                    result = left === sum();
                }
                return result;
            }

            function andCondition() {
                let result = compare();
                while (valid && isChar(['&'])) {
                    pos = pos + 1;
                    const right = compare();
                    result = result && right;
                }
                return result;
            }

            function orCondition() {
                let result = andCondition();
                while (valid && isChar(['|'])) {
                    pos = pos + 1;
                    const right = andCondition();
                    result = result || right;
                }
                return result;
            }

            /* the then or the else part, only a number of the chosen part counts like in calc_internal::if_number */
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
                if (isChar(['('])) {
                    pos = pos + 1;
                    result = sum();
                    bracketClose();
                } else if (isChar([FUNC_MIN, FUNC_MAX])) {
                    const pick = isChar([FUNC_MIN]) ? Math.min : Math.max;
                    pos = pos + 1;
                    expect(['(']);
                    result = pickArgument(pick);
                } else if (isChar([FUNC_IF])) {
                    pos = pos + 1;
                    expect(['(']);
                    result = ifFunction();
                } else if (isChar(['log'])) {
                    pos = pos + 1;
                    expect(['(']);
                    const nums = [sum()];
                    while (valid && isChar([FUNC_ARG_SEPARATOR])) {
                        pos = pos + 1;
                        nums.push(sum());
                    }
                    bracketClose();
                    result = finite(LOG_NUMBER(nums));
                } else if (isChar(Object.keys(ONE_NUMBER))) {
                    const calc = ONE_NUMBER[tokens[pos].c];
                    pos = pos + 1;
                    expect(['(']);
                    const arg = sum();
                    bracketClose();
                    result = finite(calc(arg));
                } else if (isChar(Object.keys(LIST_NUMBER))) {
                    const calc = LIST_NUMBER[tokens[pos].c];
                    pos = pos + 1;
                    expect(['(']);
                    const nums = listArguments();
                    noNumber = noNumber || nums.length === 0;
                    result = nums.length === 0 ? 0 : finite(calc(nums));
                } else if (pos < tokens.length && tokens[pos].t === T_NUM) {
                    result = tokens[pos].v;
                    pos = pos + 1;
                } else if (pos < tokens.length && tokens[pos].t === T_PHR) {
                    const num = numberOf(tokens[pos]);
                    pos = pos + 1;
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

            let result = sum();
            if (!valid || noNumber || pos !== tokens.length) {
                result = null;
            }
            return result;
        }

        /**
         * the value or result that an operand uses, found like formula_calc::value_of: the first entry
         * that names all phrases of the operand and only phrases of the context of the result, the
         * values before the results; if none matches (e.g. after a phrase of the formula is replaced)
         * the entry that names the operand phrases and shares most phrases with the result
         */
        function listEntries(names, res) {
            const result = model.entries.filter(function (e) {
                const isExcluded = LIST_EXCLUDED.some(function (name) {
                    return e.wset.has(name);
                });
                return e.kind === 'value' && !isExcluded && isSubset(names, e.wset) && isSubset(Array.from(res.ctx), e.wset);
            });
            return result;
        }

        function findEntry(names, res) {
            let result = null;
            let i = 0;
            while (result === null && i < model.entries.length) {
                const e = model.entries[i];
                if (e !== res && isSubset(names, e.wset) && isSubset(e.wset, res.ctx)) {
                    result = {entry: e, strict: true};
                }
                i = i + 1;
            }
            if (result === null) {
                const near = new Set(res.ctx);
                res.wset.forEach(function (name) {
                    near.add(name);
                });
                let best = null;
                let bestScore = 0;
                model.entries.forEach(function (e) {
                    if (e !== res && isSubset(names, e.wset)) {
                        let score = 0;
                        e.wset.forEach(function (name) {
                            if (near.has(name)) {
                                score = score + 1;
                            } else {
                                score = score - 1;
                            }
                        });
                        if (best === null || score > bestScore) {
                            best = e;
                            bestScore = score;
                        }
                    }
                });
                if (best !== null && bestScore > 0) {
                    result = {entry: best, strict: false};
                }
            }
            return result;
        }

        /**
         * calculate every stored result of the page with the given expressions until no number changes
         *
         * @param parsedOf the function that returns the parsed expression of a formula name or null
         * @returns per result index the number (null if it cannot be calculated) and the operands used
         */
        function calcAll(parsedOf) {
            const numbers = model.results.map(function () {
                return null;
            });
            const operands = model.results.map(function () {
                return [];
            });
            let changed = true;
            let pass = 0;
            while (changed && pass < MAX_PASSES) {
                changed = false;
                pass = pass + 1;
                model.results.forEach(function (res, i) {
                    const parsed = parsedOf(res.formula);
                    const used = [];
                    let num = null;
                    if (parsed !== null && parsed.ok) {
                        num = evaluate(parsed.right, function (tok) {
                            const hit = findEntry(tok.names, res);
                            let result = null;
                            if (hit !== null) {
                                if (hit.entry.kind === 'value') {
                                    result = hit.entry.number;
                                } else {
                                    result = numbers[hit.entry.ri];
                                }
                            }
                            used.push({names: tok.names, hit: hit, number: result});
                            return result;
                        }, function (tok) {
                            const entries = listEntries(tok.names, res);
                            entries.forEach(function (e) {
                                used.push({names: tok.names, hit: {entry: e, strict: true}, number: e.number});
                            });
                            return entries.map(function (e) {
                                return e.number;
                            });
                        });
                    }
                    if (num !== null) {
                        num = roundTo(num, DECIMALS);
                    }
                    operands[i] = used;
                    if (!sameNumber(num, numbers[i])) {
                        numbers[i] = num;
                        changed = true;
                    }
                });
            }
            const result = {numbers: numbers, operands: operands};
            return result;
        }

        /* ------------------------------------------------------------------ */
        /* the state of the editor                                              */
        /* ------------------------------------------------------------------ */

        let DOC = null;
        let model = null;
        const baseParsed = new Map();
        /* the draft of each changed formula by its key: the parsed expression and the text as typed */
        let drafts = {};
        let selectedKey = null;
        /* the selected token: side 'left' or 'right' and the index, and the name within a phrase token */
        let selTok = null;
        let selName = 0;
        let pickerMode = 'replace';
        let moreSymbols = false;
        let pickerFilter = '';
        let formulaFilter = '';
        let tab = 'compare';
        const openRows = new Set();
        let BASE = null;
        let CUR = null;

        function formulaByKey(key) {
            const result = model.formulas.find(function (f) {
                return f.key === key;
            }) || null;
            return result;
        }

        function formulaByName(name) {
            const result = model.formulas.find(function (f) {
                return f.name === name;
            }) || null;
            return result;
        }

        function storedParsed(f) {
            if (!baseParsed.has(f.key)) {
                baseParsed.set(f.key, parseExpression(f.expression));
            }
            const result = baseParsed.get(f.key);
            return result;
        }

        /* the parsed expression of the formula with the change of this session */
        function currentParsed(f) {
            let result = storedParsed(f);
            if (drafts[f.key] !== undefined) {
                result = drafts[f.key].parsed;
            }
            return result;
        }

        function currentText(f) {
            let result = f.expression;
            if (drafts[f.key] !== undefined) {
                result = drafts[f.key].text;
            }
            return result;
        }

        function isChanged(f) {
            const result = drafts[f.key] !== undefined && drafts[f.key].text !== f.expression;
            return result;
        }

        function changedFormulas() {
            const result = model.formulas.filter(isChanged);
            return result;
        }

        function parsedOfName(useDrafts) {
            const result = function (name) {
                const f = formulaByName(name);
                let parsed = null;
                if (f !== null) {
                    if (useDrafts) {
                        parsed = currentParsed(f);
                    } else {
                        parsed = storedParsed(f);
                    }
                }
                return parsed;
            };
            return result;
        }

        function recalc() {
            CUR = calcAll(parsedOfName(true));
        }

        /* set the draft of a formula from its tokens, which are always valid, and calculate the page again */
        function setDraftTokens(f, left, right) {
            const parsed = {ok: true, error: '', left: left, right: right};
            const text = expressionText(parsed);
            if (text === f.expression) {
                delete drafts[f.key];
            } else {
                drafts[f.key] = {parsed: parsed, text: text};
            }
            recalc();
            saveDraft();
            reportChange(f);
        }

        /* set the draft of a formula from the typed text; an invalid text keeps the last valid tokens for the calculation */
        function setDraftText(f, text) {
            const result = draftFromText(f, text);
            recalc();
            saveDraft();
            reportChange(f);
            return result;
        }

        /* the draft of a formula from a text without the calculation, e.g. for all changes of the calculator editor */
        function draftFromText(f, text) {
            const parsed = parseExpression(text);
            if (text === f.expression) {
                delete drafts[f.key];
            } else if (parsed.ok) {
                drafts[f.key] = {parsed: parsed, text: text};
            } else {
                const keep = drafts[f.key] !== undefined ? drafts[f.key].parsed : storedParsed(f);
                drafts[f.key] = {parsed: keep, text: text, error: parsed.error};
            }
            return parsed;
        }

        /* the calculator editor keeps the changed expression in its change list, which it saves */
        function reportChange(f) {
            if (opts.onChange !== undefined && f.id !== null) {
                opts.onChange(f.id, currentText(f));
            }
        }

        /* set the changed expressions of the calculator editor by the formula id, e.g. after its undo */
        function setTexts(edits) {
            let changed = false;
            model.formulas.forEach(function (f) {
                const text = f.id !== null && edits[f.id] !== undefined ? edits[f.id] : f.expression;
                if (text !== currentText(f)) {
                    draftFromText(f, text);
                    changed = true;
                }
            });
            if (changed) {
                recalc();
                renderAll();
            }
        }

        /* the drafts survive a reload of the page in the browser of the user only */
        function draftKey() {
            const result = 'zukunft.formula_editor.' + model.page;
            return result;
        }

        function saveDraft() {
            /* the calculator editor keeps the draft of all its changes */
            if (embedded) {
                return;
            }
            try {
                const texts = {};
                Object.keys(drafts).forEach(function (key) {
                    texts[key] = drafts[key].text;
                });
                localStorage.setItem(draftKey(), JSON.stringify(texts));
            } catch (e) {
                /* a browser without storage keeps the drafts for the session only */
            }
        }

        function loadDraft() {
            try {
                const raw = localStorage.getItem(draftKey());
                if (raw !== null) {
                    const texts = JSON.parse(raw);
                    Object.keys(texts).forEach(function (key) {
                        const f = formulaByKey(key);
                        if (f !== null) {
                            setDraftText(f, texts[key]);
                        }
                    });
                }
            } catch (e) {
                drafts = {};
            }
        }

        /* ------------------------------------------------------------------ */
        /* the formula list                                                     */
        /* ------------------------------------------------------------------ */

        function renderFormulaList() {
            const host = document.getElementById('formulaList');
            if (host === null) {
                return;
            }
            const needle = formulaFilter.trim().toLowerCase();
            const shown = model.formulas.filter(function (f) {
                const hay = (f.name + ' ' + currentText(f)).toLowerCase();
                const result = needle === '' || hay.includes(needle);
                return result;
            });
            document.getElementById('formulaCount').textContent = txt(TXT.formulaCount, {[VAR.count]: shown.length, [VAR.countChk]: model.formulas.length});
            host.innerHTML = shown.map(function (f) {
                const sel = f.key === selectedKey ? ' sel' : '';
                const n = model.results.filter(function (r) {
                    return r.formula === f.name;
                }).length;
                let chip = '<span class="note">' + esc(txtCount(TXT.resultOne, TXT.resultMany, n)) + '</span>';
                if (isChanged(f)) {
                    chip = '<span class="chip changed">' + txtHtml(TXT.chipChanged) + '</span>';
                }
                const result = '<li class="' + sel.trim() + '" data-key="' + esc(f.key) + '" tabindex="0" role="option" aria-selected="' + (sel ? 'true' : 'false') + '">' +
                    '<div class="fname"><span>' + esc(f.name) + '</span>' + chip + '</div>' +
                    '<div class="fexp" title="' + esc(currentText(f)) + '">' + esc(currentText(f)) + '</div></li>';
                return result;
            }).join('');
        }

        /* ------------------------------------------------------------------ */
        /* the editor of the selected formula                                   */
        /* ------------------------------------------------------------------ */

        function tokenHtml(tok, side, i) {
            let result = '';
            const sel = selTok !== null && selTok.side === side && selTok.i === i ? ' sel' : '';
            const attrs = ' data-side="' + side + '" data-i="' + i + '"';
            if (tok.t === T_PHR) {
                const known = tok.names.every(function (name) {
                    return model.phrases.has(name);
                });
                const inner = tok.names.map(esc).join('<span class="sep">,</span>');
                result = '<button type="button" class="tok phr' + sel + (known ? '' : ' unknown') + '"' + attrs + ' title="' + esc(tokenText(tok)) + '">' + inner + '</button>';
            } else if (tok.t === T_NUM) {
                result = '<button type="button" class="tok num' + sel + '"' + attrs + '>' + esc(tok.v) + '</button>';
            } else {
                result = '<button type="button" class="tok ' + tok.t + sel + '"' + attrs + '>' + esc(tok.c) + '</button>';
            }
            return result;
        }

        function selectedToken(parsed) {
            let result = null;
            if (selTok !== null) {
                if (selTok.side === 'left') {
                    result = parsed.left;
                } else if (selTok.i >= 0 && selTok.i < parsed.right.length) {
                    result = parsed.right[selTok.i];
                }
            }
            return result;
        }

        /* the phrases of the picker: the phrases of the contexts of the results of the formula first,
           then the phrases with values or results, each with the number of values that name it */
        function pickerPhrases(f) {
            const inCtx = new Set();
            model.results.forEach(function (r) {
                if (r.formula === f.name) {
                    r.ctx.forEach(function (name) {
                        inCtx.add(name);
                    });
                    r.words.forEach(function (name) {
                        inCtx.add(name);
                    });
                }
            });
            const needle = pickerFilter.trim().toLowerCase();
            const list = [];
            model.phrases.forEach(function (p) {
                if (needle === '' || p.name.toLowerCase().includes(needle) || p.description.toLowerCase().includes(needle)) {
                    list.push({p: p, ctx: inCtx.has(p.name)});
                }
            });
            list.sort(function (a, b) {
                let result = Number(b.ctx) - Number(a.ctx);
                if (result === 0) {
                    result = (b.p.values + b.p.results) - (a.p.values + a.p.results);
                }
                if (result === 0) {
                    result = a.p.name.localeCompare(b.p.name);
                }
                return result;
            });
            const result = list;
            return result;
        }

        function pickerHtml(f, tok) {
            const names = tok.names.map(function (name, i) {
                const sel = pickerMode === 'replace' && i === selName ? ' sel' : '';
                const x = tok.names.length > 1 ? '<button type="button" class="x" data-act="dropName" data-i="' + i + '" title="' + txtHtml(TXT.removeName) + '">×</button>' : '';
                const result = '<span class="name' + sel + '" data-act="pickName" data-i="' + i + '">' + esc(name) + x + '</span>';
                return result;
            }).join('');
            const addSel = pickerMode === 'add' ? ' primary' : '';
            let title = txt(TXT.replaceBy, {[VAR.phrase]: tok.names[selName] || tok.names[0]});
            if (pickerMode === 'add') {
                title = txt(TXT.addPhraseTo);
            }
            const list = pickerPhrases(f);
            const shown = list.slice(0, pickerFilter.trim() === '' ? 80 : 200);
            const items = shown.map(function (x) {
                const cur = tok.names.includes(x.p.name) ? ' cur' : '';
                let count = '';
                if (x.p.values > 0 || x.p.results > 0) {
                    count = '<span class="note n">' + txtHtml(TXT.phraseCounts, {[VAR.count]: x.p.values, [VAR.countChk]: x.p.results}) + '</span>';
                }
                let ctx = '';
                if (x.ctx) {
                    ctx = ' <span class="chip ok">' + txtHtml(TXT.inContext) + '</span>';
                }
                const result = '<li data-act="pick" data-name="' + esc(x.p.name) + '" title="' + esc(x.p.description) + '">' +
                    '<span>' + esc(x.p.name) + ctx + '</span>' +
                    '<span class="pdesc">' + esc(x.p.description) + '</span>' + count + '</li>';
                return result;
            }).join('');
            let more = '';
            if (list.length > shown.length) {
                more = '<div class="note">' + txtHtml(TXT.morePhrases, {[VAR.count]: list.length - shown.length}) + '</div>';
            }
            const result = '<div class="box"><div class="box-head"><h4>' + txtHtml(TXT.phrasesOfOperand) + '</h4>' +
                '<button type="button" class="btn small' + addSel + '" data-act="addMode">' + txtHtml(TXT.addPhrase) + '</button></div>' +
                '<div class="names">' + names + '</div>' +
                '<div class="note">' + txtHtml(TXT.pickerNote, {[VAR.value]: title}) + '</div>' +
                '<input class="inp" id="pickerIn" type="search" placeholder="' + txtHtml(TXT.searchPhrase) + '" value="' + esc(pickerFilter) + '" autocomplete="off">' +
                '<ul class="plist" id="pickerList">' + items + '</ul>' + more + '</div>';
            return result;
        }

        /* the button more and after one press on it the math functions and symbols that are not basic */
        function moreSymbolsHtml() {
            let result = '<button type="button" class="btn small" data-act="moreSymbols">' + txtHtml(TXT.moreSymbols) + '</button>';
            if (moreSymbols) {
                const button = function (kind, c) {
                    return '<button type="button" class="btn small" data-act="ins" data-kind="' + kind + '" data-c="' + esc(c) + '">' + esc(c) + '</button>';
                };
                result = button(T_OP, POWER) + MORE_FUNCTIONS.map(function (c) {
                    return button(T_FUNC, c);
                }).join('') + MORE_STRUCTURE.map(function (c) {
                    return button(T_BR, c);
                }).join('');
            }
            return result;
        }

        function toolsHtml(parsed, tok) {
            let specific = '';
            if (tok !== null && tok.t === T_NUM) {
                specific = '<label class="row">' + txtHtml(TXT.number) + ' <input class="inp" id="numIn" type="number" step="any" value="' + esc(tok.v) + '" style="width: 140px;"></label>';
            } else if (tok !== null && tok.t === T_OP) {
                specific = '<div class="row"><span class="note">' + txtHtml(TXT.operator) + '</span>' + ['+', '-', '*', '/', POWER].map(function (c) {
                    const on = tok.c === c ? ' primary' : '';
                    const result = '<button type="button" class="btn small' + on + '" data-act="setOp" data-c="' + c + '">' + c + '</button>';
                    return result;
                }).join('') + '</div>';
            }
            const onRight = selTok !== null && selTok.side === 'right';
            const dis = onRight ? '' : ' disabled';
            const result = '<div class="box"><div class="box-head"><h4>' + txtHtml(TXT.editExpression) + '</h4><span class="note">' + txtHtml(TXT.clickToken) + '</span></div>' +
                specific +
                '<div class="row"><span class="note">' + txtHtml(TXT.insertAfter) + '</span>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="phr">' + txtHtml(TXT.insPhrase) + '</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="num">' + txtHtml(TXT.insNumber) + '</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="+">+</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="-">−</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="*">×</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="/">÷</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="br" data-c="(">(</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="br" data-c=")">)</button>' +
                moreSymbolsHtml() + '</div>' +
                '<div class="row"><span class="note">' + txtHtml(TXT.selection) + '</span>' +
                '<button type="button" class="btn small" data-act="move" data-d="-1"' + dis + '>' + txtHtml(TXT.moveLeft) + '</button>' +
                '<button type="button" class="btn small" data-act="move" data-d="1"' + dis + '>' + txtHtml(TXT.moveRight) + '</button>' +
                '<button type="button" class="btn small danger" data-act="del"' + dis + '>' + txtHtml(TXT.remove) + '</button></div></div>';
            return result;
        }

        function renderEditor() {
            const host = document.getElementById('editor');
            const f = formulaByKey(selectedKey);
            if (f === null) {
                host.innerHTML = '<h2>' + txtHtml(TXT.formula) + '</h2><div class="note">' + txtHtml(TXT.selectFormula) + '</div>';
            } else {
                const parsed = currentParsed(f);
                const draft = drafts[f.key];
                const text = currentText(f);
                let chip = '';
                if (isChanged(f)) {
                    chip = '<span class="chip changed">' + txtHtml(TXT.chipChanged) + '</span> <button type="button" class="btn small" data-act="resetOne">' + txtHtml(TXT.discard) + '</button>';
                }
                let err = '';
                if (draft !== undefined && draft.error) {
                    err = '<div class="err">' + esc(draft.error) + ' ' + txtHtml(TXT.errCompareNote) + '</div>';
                }
                const tokens = [tokenHtml(parsed.left, 'left', 0), '<span class="tok eq">=</span>']
                    .concat(parsed.right.map(function (tok, i) {
                        return tokenHtml(tok, 'right', i);
                    }));
                tokens.push('<button type="button" class="tok add" data-act="ins" data-kind="phr" data-end="1" title="' + txtHtml(TXT.addOperandTip) + '">' + txtHtml(TXT.addPhrase) + '</button>');
                const tok = selectedToken(parsed);
                let picker = '';
                if (tok !== null && tok.t === T_PHR) {
                    picker = pickerHtml(f, tok);
                }
                host.innerHTML = '<div class="col-head"><h2>' + txtHtml(TXT.formula) + '</h2><span>' + chip + '</span></div>' +
                    '<h3>' + esc(f.name) + '</h3>' +
                    (f.description ? '<div class="note">' + esc(f.description) + '</div>' : '') +
                    '<div class="expr" id="exprRow" role="listbox" aria-label="' + txtHtml(TXT.expression) + '">' + tokens.join('') + '</div>' +
                    toolsHtml(parsed, tok) +
                    picker +
                    '<div class="box"><div class="box-head"><h4>' + txtHtml(TXT.expressionText) + '</h4><span class="note">' + txtHtml(TXT.expressionTextNote) + '</span></div>' +
                    '<textarea class="inp" id="expText" rows="2" spellcheck="false">' + esc(text) + '</textarea>' + err + '</div>';
            }
        }

        /* ------------------------------------------------------------------ */
        /* the compare list                                                     */
        /* ------------------------------------------------------------------ */

        /* the status of a result: compared with the number calculated with the stored expressions,
           so that a rounding difference between the pod and the browser is no change */
        function statusOf(i) {
            const before = BASE.numbers[i];
            const after = CUR.numbers[i];
            let result = 'same';
            if (after === null && before !== null) {
                result = 'missing';
            } else if (!sameNumber(before, after)) {
                result = 'changed';
            }
            return result;
        }

        /* true if a changed formula has a text that cannot be parsed */
        function hasErrors() {
            const result = Object.keys(drafts).some(function (key) {
                return Boolean(drafts[key].error);
            });
            return result;
        }

        function wordsHtml(res, f) {
            const resultNames = f !== null ? currentParsed(f).left.names : [];
            const result = '<span class="wds">' + res.words.map(function (name) {
                let cls = 'wd';
                if (name === model.page) {
                    cls = 'wd page';
                } else if (resultNames.includes(name)) {
                    cls = 'wd res';
                }
                const html = '<span class="' + cls + '">' + esc(name) + '</span>';
                return html;
            }).join('') + '</span>';
            return result;
        }

        function operandsHtml(i) {
            const used = CUR.operands[i];
            const base = BASE.operands[i];
            const rows = used.map(function (u, k) {
                const before = base[k] !== undefined && base[k].hit !== null && base[k].names.join(',') === u.names.join(',') ? base[k].number : null;
                let entry = '<span class="chip missing">' + txtHtml(TXT.noValueNames, {[VAR.list]: u.names.join(', ')}) + '</span>';
                if (u.hit !== null) {
                    const e = u.hit.entry;
                    let kind = e.kind === 'value' ? txtHtml(TXT.kindValue) : txtHtml(TXT.kindResult, {[VAR.formula]: e.formula});
                    if (!u.hit.strict) {
                        kind = kind + ' <span class="chip changed" title="' + txtHtml(TXT.outsideTip) + '">' + txtHtml(TXT.outside) + '</span>';
                    }
                    entry = wordsHtml(e, null) + ' <span class="note">' + kind + '</span>';
                }
                let num = fmt(u.number);
                if (before !== null && !sameNumber(before, u.number)) {
                    num = '<span class="old">' + fmt(before) + '</span> → ' + fmt(u.number);
                }
                const result = '<tr><td class="mono">' + esc(u.names.map(function (n) {
                    return '"' + n + '"';
                }).join(',')) + '</td><td>' + entry + '</td><td class="n">' + num + '</td></tr>';
                return result;
            }).join('');
            let result = '<div class="note">' + txtHtml(TXT.notCalculated) + '</div>';
            if (rows !== '') {
                result = '<table class="ops"><tbody>' + rows + '</tbody></table>';
            }
            return result;
        }

        function rowHtml(i, f) {
            const res = model.results[i];
            const status = statusOf(i);
            const after = CUR.numbers[i];
            const open = openRows.has(i);
            let delta = '';
            if (status === 'changed' && res.stored !== null && res.stored !== 0) {
                const pct = (after - res.stored) / Math.abs(res.stored) * 100;
                delta = '<div class="delta">' + (after - res.stored > 0 ? '+' : '') + fmt(after - res.stored) + ' (' + (pct > 0 ? '+' : '') + fmt(pct) + ' %)</div>';
            }
            let chip = '<span class="chip same">' + txtHtml(TXT.chipSame) + '</span>';
            if (status === 'changed') {
                chip = '<span class="chip changed">' + txtHtml(TXT.chipChanges) + '</span>';
            } else if (status === 'missing') {
                chip = '<span class="chip missing">' + txtHtml(TXT.chipMissing) + '</span>';
            }
            let result = '<tr class="res' + (open ? ' open' : '') + '" data-i="' + i + '" tabindex="0" aria-expanded="' + (open ? 'true' : 'false') + '">' +
                '<td>' + wordsHtml(res, f) + '</td>' +
                '<td class="n">' + fmt(res.stored) + '</td>' +
                '<td class="n new ' + status + '">' + fmt(after) + delta + '</td>' +
                '<td>' + chip + '</td></tr>';
            if (open) {
                result = result + '<tr class="det"><td colspan="4">' + operandsHtml(i) + '</td></tr>';
            }
            return result;
        }

        function renderCompare() {
            const host = document.getElementById('compare');
            const f = formulaByKey(selectedKey);
            if (host === null) {
                /* the view has no result compare */
            } else if (f === null) {
                host.innerHTML = '<div class="note">' + txtHtml(TXT.selectCompare) + '</div>';
            } else {
                const own = [];
                const others = new Map();
                model.results.forEach(function (res, i) {
                    if (res.formula === f.name) {
                        own.push(i);
                    } else if (statusOf(i) !== 'same') {
                        if (!others.has(res.formula)) {
                            others.set(res.formula, []);
                        }
                        others.get(res.formula).push(i);
                    }
                });
                const ownChanged = own.filter(function (i) {
                    return statusOf(i) === 'changed';
                }).length;
                const ownMissing = own.filter(function (i) {
                    return statusOf(i) === 'missing';
                }).length;
                let otherCount = 0;
                others.forEach(function (list) {
                    otherCount = otherCount + list.length;
                });
                let summary = '<span class="chip">' + esc(txtCount(TXT.ownResultOne, TXT.ownResultMany, own.length)) + '</span>';
                if (ownChanged > 0) {
                    summary = summary + '<span class="chip changed">' + esc(txtCount(TXT.ownChangesOne, TXT.ownChangesMany, ownChanged)) + '</span>';
                }
                if (ownMissing > 0) {
                    summary = summary + '<span class="chip missing">' + txtHtml(TXT.ownMissing, {[VAR.count]: ownMissing}) + '</span>';
                }
                if (otherCount > 0) {
                    summary = summary + '<span class="chip changed">' + txtHtml(TXT.otherChanges, {[VAR.count]: otherCount}) + '</span>';
                }
                if (ownChanged === 0 && ownMissing === 0 && otherCount === 0) {
                    summary = summary + '<span class="chip ok">' + txtHtml(TXT.noResultChanges) + '</span>';
                }
                let rows = '<tr class="grp"><td colspan="4">' + esc(f.name) + ' <span class="note mono">' + esc(currentText(f)) + '</span></td></tr>';
                if (own.length === 0) {
                    rows = rows + '<tr><td colspan="4" class="note">' + txtHtml(TXT.noStoredResult) + '</td></tr>';
                }
                rows = rows + own.map(function (i) {
                    return rowHtml(i, f);
                }).join('');
                others.forEach(function (list, name) {
                    const of = formulaByName(name);
                    rows = rows + '<tr class="grp"><td colspan="4">' + esc(name) + ' <span class="note">' + txtHtml(TXT.usesChanged) + '</span> <span class="note mono">' + esc(of !== null ? currentText(of) : '') + '</span></td></tr>';
                    rows = rows + list.map(function (i) {
                        return rowHtml(i, of);
                    }).join('');
                });
                host.innerHTML = '<div class="summary">' + summary + '</div>' +
                    '<div class="note">' + txtHtml(TXT.compareNote) + '</div>' +
                    '<div class="twrap"><table class="cmp"><thead><tr><th>' + txtHtml(TXT.colResult) + '</th><th class="n">' + txtHtml(TXT.untilNow) + '</th>' +
                    '<th class="n">' + txtHtml(TXT.afterSave) + '</th><th></th></tr></thead><tbody>' + rows + '</tbody></table></div>';
            }
        }

        /* ------------------------------------------------------------------ */
        /* the changes and the save                                             */
        /* ------------------------------------------------------------------ */

        /* the message for the updatePut api: each changed formula by its id with the expression in the user format */
        function putMessage() {
            const formulas = changedFormulas().map(function (f) {
                const result = {id: f.id, user_text: currentText(f)};
                if (f.id === null) {
                    delete result.id;
                    result.name = f.name;
                }
                return result;
            });
            const result = {
                pod: DOC.pod || 'zukunft.com',
                type: 'updatePut',
                version: DOC.version || '0.0.3',
                timestamp: new Date().toISOString(),
                body: {formulas: formulas}
            };
            return result;
        }

        function renderChanges() {
            const host = document.getElementById('changes');
            if (host === null) {
                return;
            }
            const list = changedFormulas();
            let changedResults = 0;
            model.results.forEach(function (res, i) {
                if (statusOf(i) !== 'same') {
                    changedResults = changedResults + 1;
                }
            });
            if (list.length === 0) {
                host.innerHTML = '<div class="note">' + txtHtml(TXT.noFormulaChanged) + '</div>';
            } else {
                const diffs = list.map(function (f) {
                    const result = '<div class="box"><div class="box-head"><h4>' + esc(f.name) + '</h4><button type="button" class="btn small" data-act="resetKey" data-key="' + esc(f.key) + '">' + txtHtml(TXT.discard) + '</button></div>' +
                        '<div class="diff"><span class="k">' + txtHtml(TXT.untilNow) + '</span><span class="mono old">' + esc(f.expression) + '</span>' +
                        '<span class="k">' + txtHtml(TXT.afterSave) + '</span><span class="mono">' + esc(currentText(f)) + '</span></div></div>';
                    return result;
                }).join('');
                let errNote = '';
                if (hasErrors()) {
                    errNote = '<div class="err">' + txtHtml(TXT.errFix) + '</div>';
                }
                host.innerHTML = '<div class="summary"><span class="chip changed">' + esc(txtCount(TXT.formulaOne, TXT.formulaMany, list.length)) + '</span>' +
                    '<span class="chip">' + esc(txtCount(TXT.pageResultsOne, TXT.pageResultsMany, changedResults)) + '</span></div>' + errNote +
                    diffs +
                    '<div class="box"><div class="box-head"><h4>' + txtHtml(TXT.updatePut) + '</h4><div class="row">' +
                    '<button type="button" class="btn small" data-act="copyJson">' + txtHtml(TXT.copy) + '</button>' +
                    '<button type="button" class="btn small" data-act="applyLocal" title="' + txtHtml(TXT.applyLocalTip) + '">' + txtHtml(TXT.applyLocal) + '</button></div></div>' +
                    '<div class="note">' + txtHtml(TXT.savePosts, {[VAR.value]: API_UPDATE_PUT}) + '</div>' +
                    '<pre class="json" id="jsonOut">' + esc(JSON.stringify(putMessage(), null, 2)) + '</pre></div>';
            }
        }

        /* after a save the changed expressions are the stored ones and the calculated numbers the stored numbers */
        function applySaved() {
            changedFormulas().forEach(function (f) {
                f.expression = currentText(f);
                baseParsed.delete(f.key);
            });
            model.results.forEach(function (res, i) {
                if (CUR.numbers[i] !== null) {
                    res.stored = CUR.numbers[i];
                }
            });
            drafts = {};
            saveDraft();
            BASE = calcAll(parsedOfName(false));
            recalc();
            renderAll();
        }

        function csrfToken() {
            let result = '';
            const meta = document.querySelector('meta[name="csrf-token"]');
            const input = document.querySelector('input[name="token"]');
            if (meta !== null) {
                result = meta.getAttribute('content') || '';
            } else if (input !== null) {
                result = input.value;
            }
            return result;
        }

        function save() {
            const btn = document.getElementById('btnSave');
            const message = putMessage();
            btn.disabled = true;
            btn.textContent = txt(TXT.saving);
            fetch(API_UPDATE_PUT, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken()},
                body: JSON.stringify(message)
            }).then(function (response) {
                let result = null;
                if (response.ok) {
                    result = response.json();
                } else {
                    throw new Error(txt(TXT.podAnswered, {[VAR.value]: response.status}));
                }
                return result;
            }).then(function () {
                applySaved();
                toast(txt(TXT.saved));
            }).catch(function (e) {
                tab = 'changes';
                renderAll();
                toast(txt(TXT.notSaved, {[VAR.value]: e.message}));
            });
        }

        function updateToolbar() {
            /* the calculator editor counts and saves the changes in its own toolbar */
            if (embedded) {
                return;
            }
            const n = changedFormulas().length;
            const chip = document.getElementById('changeCount');
            chip.textContent = n === 0 ? txt(TXT.noChanges) : txtCount(TXT.changedOne, TXT.changedMany, n);
            chip.className = n === 0 ? 'chip' : 'chip changed';
            document.getElementById('btnResetAll').disabled = n === 0;
            const btn = document.getElementById('btnSave');
            btn.disabled = n === 0 || hasErrors();
            btn.textContent = n === 0 ? txt(TXT.save) : txt(TXT.saveCount, {[VAR.count]: n});
            btn.title = hasErrors() ? txt(TXT.invalidTip) : '';
            document.getElementById('pageName').textContent = model.page || txt(TXT.page);
        }

        function renderAll() {
            renderFormulaList();
            renderEditor();
            renderCompare();
            renderChanges();
            updateToolbar();
            /* the compare in the calculator editor has no tabs, because the calculator editor shows the changes */
            if (compareHost !== null && !embedded) {
                compareHost.querySelectorAll('.tab').forEach(function (b) {
                    b.classList.toggle('on', b.dataset.tab === tab);
                });
                document.getElementById('compare').hidden = tab !== 'compare';
                document.getElementById('changes').hidden = tab !== 'changes';
            }
        }

        /* ------------------------------------------------------------------ */
        /* the actions of the editor                                            */
        /* ------------------------------------------------------------------ */

        function selectFormula(key) {
            selectedKey = key;
            selTok = null;
            selName = 0;
            pickerMode = 'replace';
            pickerFilter = '';
            openRows.clear();
            renderAll();
        }

        function selectToken(side, i) {
            selTok = {side: side, i: i};
            selName = 0;
            pickerMode = 'replace';
            pickerFilter = '';
            renderEditor();
        }

        /* change the tokens of the selected formula with a function and show the new expression and results */
        function editTokens(change) {
            const f = formulaByKey(selectedKey);
            if (f !== null) {
                const parsed = clone(currentParsed(f));
                change(parsed);
                setDraftTokens(f, parsed.left, parsed.right);
                renderAll();
            }
        }

        /* the tokens of a function with its brackets and separators e.g. min ( ; ), so that only its arguments are missing */
        function functionTokens(name) {
            const chars = [name, '('];
            for (let i = 0; i < (FUNC_SEPARATORS[name] || 0); i++) {
                chars.push(FUNC_ARG_SEPARATOR);
            }
            chars.push(')');
            return chars.map(function (c) {
                return {t: T_BR, c: c};
            });
        }

        function insertToken(kind, c, atEnd) {
            editTokens(function (parsed) {
                let toks = [{t: T_PHR, names: ['phrase']}];
                if (kind === T_NUM) {
                    toks = [{t: T_NUM, v: 1}];
                } else if (kind === T_OP) {
                    toks = [{t: T_OP, c: c}];
                } else if (kind === T_BR) {
                    toks = [{t: T_BR, c: c}];
                } else if (kind === T_FUNC) {
                    toks = functionTokens(c);
                }
                let pos = parsed.right.length;
                if (!atEnd && selTok !== null && selTok.side === 'right') {
                    pos = selTok.i + 1;
                }
                parsed.right.splice(pos, 0, ...toks);
                // a function selects its open bracket, so that the next insert is its first argument
                selTok = {side: 'right', i: pos + (kind === T_FUNC ? 1 : 0)};
                selName = 0;
                pickerMode = 'replace';
            });
        }

        function removeToken() {
            editTokens(function (parsed) {
                if (selTok !== null && selTok.side === 'right') {
                    parsed.right.splice(selTok.i, 1);
                    selTok = null;
                }
            });
        }

        function moveToken(d) {
            editTokens(function (parsed) {
                if (selTok !== null && selTok.side === 'right') {
                    const to = selTok.i + d;
                    if (to >= 0 && to < parsed.right.length) {
                        const tok = parsed.right.splice(selTok.i, 1)[0];
                        parsed.right.splice(to, 0, tok);
                        selTok = {side: 'right', i: to};
                    }
                }
            });
        }

        function pickPhrase(name) {
            editTokens(function (parsed) {
                const tok = selectedToken(parsed);
                if (tok !== null && tok.t === T_PHR) {
                    if (pickerMode === 'add') {
                        if (!tok.names.includes(name)) {
                            tok.names.push(name);
                        }
                        selName = tok.names.length - 1;
                        pickerMode = 'replace';
                    } else {
                        if (selName >= tok.names.length) {
                            selName = tok.names.length - 1;
                        }
                        tok.names[selName] = name;
                    }
                }
            });
        }

        function dropName(i) {
            editTokens(function (parsed) {
                const tok = selectedToken(parsed);
                if (tok !== null && tok.t === T_PHR && tok.names.length > 1) {
                    tok.names.splice(i, 1);
                    selName = 0;
                }
            });
        }

        function discard(f) {
            delete drafts[f.key];
            recalc();
            saveDraft();
            reportChange(f);
            renderAll();
        }

        function discardAll() {
            drafts = {};
            recalc();
            saveDraft();
            renderAll();
        }

        function copyJson() {
            const text = document.getElementById('jsonOut').textContent;
            navigator.clipboard.writeText(text).then(function () {
                toast(txt(TXT.copied));
            }).catch(function () {
                const range = document.createRange();
                range.selectNodeContents(document.getElementById('jsonOut'));
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                toast(txt(TXT.copyBlocked));
            });
        }

        /* read another page phrase from the pod with one phraseData call */
        function loadPhrase(name) {
            const url = API_PHRASE_DATA + '?name=' + encodeURIComponent(name) + '&pw=pwf&is=1';
            fetch(url).then(function (response) {
                let result = null;
                if (response.ok) {
                    result = response.json();
                } else {
                    throw new Error(txt(TXT.podAnswered, {[VAR.value]: response.status}));
                }
                return result;
            }).then(function (doc) {
                boot(doc);
                toast(txt(TXT.loaded, {[VAR.phrase]: model.page || name}));
            }).catch(function (e) {
                toast(txt(TXT.loadFailed, {[VAR.phrase]: name, [VAR.value]: e.message}));
            });
        }

        /* ------------------------------------------------------------------ */
        /* the events                                                           */
        /* ------------------------------------------------------------------ */

        function onClick(e) {
            const t = e.target.closest('[data-act], .tok, .flist li, tr.res, .tab');
            if (t !== null) {
                const act = t.dataset.act || '';
                const f = formulaByKey(selectedKey);
                if (t.classList.contains('tab')) {
                    tab = t.dataset.tab;
                    renderAll();
                } else if (t.matches('.flist li')) {
                    selectFormula(t.dataset.key);
                } else if (t.matches('tr.res')) {
                    const i = Number(t.dataset.i);
                    if (openRows.has(i)) {
                        openRows.delete(i);
                    } else {
                        openRows.add(i);
                    }
                    renderCompare();
                } else if (act === 'moreSymbols') {
                    moreSymbols = true;
                    renderEditor();
                } else if (act === 'ins') {
                    insertToken(t.dataset.kind, t.dataset.c || '', t.dataset.end === '1');
                } else if (t.classList.contains('tok') && t.dataset.side !== undefined) {
                    selectToken(t.dataset.side, Number(t.dataset.i));
                } else if (act === 'del') {
                    removeToken();
                } else if (act === 'move') {
                    moveToken(Number(t.dataset.d));
                } else if (act === 'setOp') {
                    editTokens(function (parsed) {
                        const tok = selectedToken(parsed);
                        if (tok !== null && tok.t === T_OP) {
                            tok.c = t.dataset.c;
                        }
                    });
                } else if (act === 'pick') {
                    pickPhrase(t.dataset.name);
                } else if (act === 'pickName') {
                    selName = Number(t.dataset.i);
                    pickerMode = 'replace';
                    renderEditor();
                } else if (act === 'dropName') {
                    e.stopPropagation();
                    dropName(Number(t.dataset.i));
                } else if (act === 'addMode') {
                    pickerMode = pickerMode === 'add' ? 'replace' : 'add';
                    renderEditor();
                } else if (act === 'resetOne' && f !== null) {
                    discard(f);
                } else if (act === 'resetKey') {
                    const g = formulaByKey(t.dataset.key);
                    if (g !== null) {
                        discard(g);
                    }
                } else if (act === 'copyJson') {
                    copyJson();
                } else if (act === 'applyLocal' && !hasErrors()) {
                    applySaved();
                    toast(txt(TXT.applied));
                }
            }
        }

        function onInput(e) {
            const f = formulaByKey(selectedKey);
            if (e.target.id === 'expText' && f !== null) {
                const parsed = setDraftText(f, e.target.value);
                if (parsed.ok) {
                    selTok = null;
                }
                /* keep the text area while typing: refresh everything but the text itself */
                const pos = e.target.selectionStart;
                renderAll();
                const area = document.getElementById('expText');
                area.focus();
                area.setSelectionRange(pos, pos);
            } else if (e.target.id === 'pickerIn') {
                pickerFilter = e.target.value;
                const list = document.getElementById('pickerList');
                const tok = selectedToken(currentParsed(f));
                if (tok !== null) {
                    const html = pickerHtml(f, tok);
                    const tmp = document.createElement('div');
                    tmp.innerHTML = html;
                    list.innerHTML = tmp.querySelector('#pickerList').innerHTML;
                }
            } else if (e.target.id === 'numIn') {
                const num = Number(e.target.value);
                if (!Number.isNaN(num) && e.target.value.trim() !== '') {
                    editTokens(function (parsed) {
                        const tok = selectedToken(parsed);
                        if (tok !== null && tok.t === T_NUM) {
                            tok.v = num;
                        }
                    });
                    const inp = document.getElementById('numIn');
                    if (inp !== null) {
                        inp.focus();
                    }
                }
            } else if (e.target.id === 'formulaFilter') {
                formulaFilter = e.target.value;
                renderFormulaList();
            }
        }

        function onKey(e) {
            const inRow = e.target.closest('#exprRow') !== null;
            if (inRow && selTok !== null && selTok.side === 'right') {
                if (e.key === 'ArrowLeft' && selTok.i > 0) {
                    selectToken('right', selTok.i - 1);
                    e.preventDefault();
                } else if (e.key === 'ArrowRight' && selTok.i + 1 < currentParsed(formulaByKey(selectedKey)).right.length) {
                    selectToken('right', selTok.i + 1);
                    e.preventDefault();
                } else if (e.key === 'Delete' || e.key === 'Backspace') {
                    removeToken();
                    e.preventDefault();
                }
            } else if (e.key === 'Enter' && e.target.matches('.flist li')) {
                selectFormula(e.target.dataset.key);
            } else if (e.key === 'Enter' && e.target.matches('tr.res')) {
                e.target.click();
            }
        }

        function boot(doc) {
            DOC = doc;
            model = buildModel(doc);
            baseParsed.clear();
            drafts = {};
            selectedKey = null;
            selTok = null;
            openRows.clear();
            BASE = calcAll(parsedOfName(false));
            CUR = BASE;
            if (!embedded) {
                loadDraft();
            }
            recalc();
            if (model.formulas.length > 0) {
                selectedKey = model.formulas[0].key;
            }
            renderAll();
        }

        /* only the events of the own hosts, so that the editor can be a part of another page */
        hosts.forEach(function (h) {
            h.addEventListener('click', onClick);
            h.addEventListener('input', onInput);
            h.addEventListener('keydown', onKey);
        });
        if (!embedded) {
            document.getElementById('btnSave').addEventListener('click', save);
            document.getElementById('btnResetAll').addEventListener('click', discardAll);
        }
        if (selectorHost !== null) {
            document.getElementById('pageForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const name = document.getElementById('pageIn').value.trim();
                if (name !== '') {
                    loadPhrase(name);
                }
            });
        }

        const seedElement = document.getElementById(SEED_ID);
        if (embedded) {
            boot(opts.doc);
        } else if (seedElement !== null) {
            boot(JSON.parse(seedElement.textContent));
        }

        const result = {
            /* select a formula by its id instead of the formula selector; the same formula keeps the selected token */
            select: function (id) {
                const key = 'id:' + id;
                if (key !== selectedKey && formulaByKey(key) !== null) {
                    selectFormula(key);
                }
            },
            setTexts: setTexts,
            /* the data of another page phrase e.g. after the calculator editor loaded another phrase */
            load: boot
        };
        return result;
    }

    /* the formula editor view; the calculator editor names its hosts differently and starts the editor itself */
    function startFormulaEditorView() {
        startFormulaEditor();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startFormulaEditorView);
    } else {
        startFormulaEditorView();
    }
