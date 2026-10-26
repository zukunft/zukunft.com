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
            editorHost.innerHTML = '<section class="col editor" aria-label="Formula" id="editor"></section>';
            if (compareHost !== null) {
                compareHost.innerHTML = '<section class="col compare" aria-label="Results"><div id="compare"></div></section>';
            }
        } else {
            buildHosts();
        }

        /* the toolbar, the formula list, the editor, the tabs of the compare and the toast of the formula editor view */
        function buildHosts() {
            if (selectorHost !== null) {
                selectorHost.innerHTML = [
                    '<section class="col formulas" aria-label="Formulas">',
                    '<div class="col-head"><h2>Formulas of the page</h2><span class="note" id="formulaCount"></span></div>',
                    '<form class="tools" id="pageForm">',
                    '<label for="pageIn" class="note">Phrase</label>',
                    '<input class="inp" id="pageIn" type="text" placeholder="another page phrase">',
                    '<button class="btn" type="submit">Load</button>',
                    '</form>',
                    '<input class="inp" id="formulaFilter" type="search" placeholder="filter by name or phrase">',
                    '<ul class="flist" id="formulaList"></ul>',
                    '</section>'
                ].join('');
            }
            editorHost.innerHTML = [
                '<header class="bar">',
                '<div class="brand"><b>Formula editor</b><span id="pageName">page</span></div>',
                '<div class="tools right">',
                '<span class="chip" id="changeCount">no changes</span>',
                '<button class="btn" id="btnResetAll" type="button" disabled>Discard all</button>',
                '<button class="btn primary" id="btnSave" type="button" disabled>Save</button>',
                '</div>',
                '</header>',
                '<section class="col editor" aria-label="Formula" id="editor"></section>',
                '<div class="toast" id="toast" hidden></div>'
            ].join('');
            if (compareHost !== null) {
                compareHost.innerHTML = [
                    '<section class="col compare" aria-label="Results">',
                    '<div class="tabs" role="tablist">',
                    '<button class="tab on" type="button" data-tab="compare" role="tab">Results until now vs. after save</button>',
                    '<button class="tab" type="button" data-tab="changes" role="tab">Changes</button>',
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
        /* the decimals of a stored result, the same as formula_calc::DECIMALS */
        const DECIMALS = 2;
        /* the kinds of the tokens of an expression */
        const T_PHR = 'phr';
        const T_NUM = 'num';
        const T_OP = 'op';
        const T_BR = 'br';
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

        /* the tokens of one side of an expression; a phrase joined to the next by the separator is one operand with several names */
        function tokenize(text) {
            const result = {ok: true, error: '', tokens: []};
            const re = /\s*(?:"([^"]*)"|(\d+(?:\.\d+)?)|([-+*\/])|([()])|(,)|(\S))/y;
            let joinNext = false;
            let m = re.exec(text);
            while (result.ok && m !== null) {
                const last = result.tokens[result.tokens.length - 1];
                if (m[1] !== undefined) {
                    const name = m[1].trim();
                    if (name === '') {
                        result.ok = false;
                        result.error = 'A phrase name between the quotes is missing.';
                    } else if (joinNext && last !== undefined && last.t === T_PHR) {
                        last.names.push(name);
                    } else {
                        result.tokens.push({t: T_PHR, names: [name]});
                    }
                    joinNext = false;
                } else if (joinNext) {
                    result.ok = false;
                    result.error = 'A comma joins two phrases of one operand, e.g. "delivered electricity","lifetime".';
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
                        result.error = 'A comma is only allowed between two phrases of one operand.';
                    }
                } else {
                    result.ok = false;
                    result.error = 'Unexpected character "' + m[6] + '": only phrases in quotes, numbers, + - * / and brackets are allowed.';
                }
                if (m[0] === '') {
                    m = null;
                } else {
                    m = re.exec(text);
                }
            }
            if (result.ok && joinNext) {
                result.ok = false;
                result.error = 'A phrase is expected after the comma.';
            }
            return result;
        }

        /* the parsed expression: the result phrases left of the = and the calculation right of it */
        function parseExpression(text) {
            const result = {ok: true, error: '', left: null, right: []};
            const pos = String(text).indexOf(CHAR_CALC);
            if (pos < 0) {
                result.ok = false;
                result.error = 'The expression needs a "=": the phrase left of it gets the result.';
            } else {
                const left = tokenize(text.slice(0, pos));
                const right = tokenize(text.slice(pos + 1));
                if (!left.ok) {
                    result.ok = false;
                    result.error = left.error;
                } else if (left.tokens.length !== 1 || left.tokens[0].t !== T_PHR) {
                    result.ok = false;
                    result.error = 'Left of the "=" exactly one phrase (or phrases joined by a comma) is expected.';
                } else if (!right.ok) {
                    result.ok = false;
                    result.error = right.error;
                } else if (right.tokens.length === 0) {
                    result.ok = false;
                    result.error = 'Right of the "=" the calculation is missing.';
                } else {
                    result.left = left.tokens[0];
                    result.right = right.tokens;
                    const check = evaluate(right.tokens, function () {
                        return 1;
                    });
                    if (check === null) {
                        result.ok = false;
                        result.error = 'The arithmetic is not valid: check the operators and the brackets.';
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
         * @returns the number or null if the tokens are no valid calculation, a number is missing or a division is by zero
         */
        function evaluate(tokens, numberOf) {
            let pos = 0;
            let valid = true;

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
                let result = signed();
                while (valid && isChar(['*', '/'])) {
                    const op = tokens[pos].c;
                    pos = pos + 1;
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

            function atom() {
                let result = 0;
                if (isChar(['('])) {
                    pos = pos + 1;
                    result = sum();
                    if (isChar([')'])) {
                        pos = pos + 1;
                    } else {
                        valid = false;
                    }
                } else if (pos < tokens.length && tokens[pos].t === T_NUM) {
                    result = tokens[pos].v;
                    pos = pos + 1;
                } else if (pos < tokens.length && tokens[pos].t === T_PHR) {
                    const num = numberOf(tokens[pos]);
                    pos = pos + 1;
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

            let result = sum();
            if (!valid || pos !== tokens.length) {
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
            document.getElementById('formulaCount').textContent = shown.length + ' of ' + model.formulas.length;
            host.innerHTML = shown.map(function (f) {
                const sel = f.key === selectedKey ? ' sel' : '';
                const n = model.results.filter(function (r) {
                    return r.formula === f.name;
                }).length;
                let chip = '<span class="note">' + n + (n === 1 ? ' result' : ' results') + '</span>';
                if (isChanged(f)) {
                    chip = '<span class="chip changed">changed</span>';
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
                const x = tok.names.length > 1 ? '<button type="button" class="x" data-act="dropName" data-i="' + i + '" title="remove this phrase from the operand">×</button>' : '';
                const result = '<span class="name' + sel + '" data-act="pickName" data-i="' + i + '">' + esc(name) + x + '</span>';
                return result;
            }).join('');
            const addSel = pickerMode === 'add' ? ' primary' : '';
            let title = 'Replace "' + esc(tok.names[selName] || tok.names[0]) + '" by';
            if (pickerMode === 'add') {
                title = 'Add a phrase to the operand';
            }
            const list = pickerPhrases(f);
            const shown = list.slice(0, pickerFilter.trim() === '' ? 80 : 200);
            const items = shown.map(function (x) {
                const cur = tok.names.includes(x.p.name) ? ' cur' : '';
                let count = '';
                if (x.p.values > 0 || x.p.results > 0) {
                    count = '<span class="note n">' + x.p.values + ' val · ' + x.p.results + ' res</span>';
                }
                let ctx = '';
                if (x.ctx) {
                    ctx = ' <span class="chip ok">in context</span>';
                }
                const result = '<li data-act="pick" data-name="' + esc(x.p.name) + '" title="' + esc(x.p.description) + '">' +
                    '<span>' + esc(x.p.name) + ctx + '</span>' +
                    '<span class="pdesc">' + esc(x.p.description) + '</span>' + count + '</li>';
                return result;
            }).join('');
            let more = '';
            if (list.length > shown.length) {
                more = '<div class="note">' + (list.length - shown.length) + ' more phrases: narrow the filter.</div>';
            }
            const result = '<div class="box"><div class="box-head"><h4>Phrases of the operand</h4>' +
                '<button type="button" class="btn small' + addSel + '" data-act="addMode">+ phrase</button></div>' +
                '<div class="names">' + names + '</div>' +
                '<div class="note">' + title + ' (the result is calculated for every value that names all phrases of the operand):</div>' +
                '<input class="inp" id="pickerIn" type="search" placeholder="search phrase" value="' + esc(pickerFilter) + '" autocomplete="off">' +
                '<ul class="plist" id="pickerList">' + items + '</ul>' + more + '</div>';
            return result;
        }

        function toolsHtml(parsed, tok) {
            let specific = '';
            if (tok !== null && tok.t === T_NUM) {
                specific = '<label class="row">Number <input class="inp" id="numIn" type="number" step="any" value="' + esc(tok.v) + '" style="width: 140px;"></label>';
            } else if (tok !== null && tok.t === T_OP) {
                specific = '<div class="row"><span class="note">Operator</span>' + ['+', '-', '*', '/'].map(function (c) {
                    const on = tok.c === c ? ' primary' : '';
                    const result = '<button type="button" class="btn small' + on + '" data-act="setOp" data-c="' + c + '">' + c + '</button>';
                    return result;
                }).join('') + '</div>';
            }
            const onRight = selTok !== null && selTok.side === 'right';
            const dis = onRight ? '' : ' disabled';
            const result = '<div class="box"><div class="box-head"><h4>Edit the expression</h4><span class="note">click a token to select it</span></div>' +
                specific +
                '<div class="row"><span class="note">Insert after the selection</span>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="phr">phrase</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="num">number</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="+">+</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="-">−</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="*">×</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="op" data-c="/">÷</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="br" data-c="(">(</button>' +
                '<button type="button" class="btn small" data-act="ins" data-kind="br" data-c=")">)</button></div>' +
                '<div class="row"><span class="note">Selection</span>' +
                '<button type="button" class="btn small" data-act="move" data-d="-1"' + dis + '>◀ move</button>' +
                '<button type="button" class="btn small" data-act="move" data-d="1"' + dis + '>move ▶</button>' +
                '<button type="button" class="btn small danger" data-act="del"' + dis + '>remove</button></div></div>';
            return result;
        }

        function renderEditor() {
            const host = document.getElementById('editor');
            const f = formulaByKey(selectedKey);
            if (f === null) {
                host.innerHTML = '<h2>Formula</h2><div class="note">Select a formula on the left.</div>';
            } else {
                const parsed = currentParsed(f);
                const draft = drafts[f.key];
                const text = currentText(f);
                let chip = '';
                if (isChanged(f)) {
                    chip = '<span class="chip changed">changed</span> <button type="button" class="btn small" data-act="resetOne">discard</button>';
                }
                let err = '';
                if (draft !== undefined && draft.error) {
                    err = '<div class="err">' + esc(draft.error) + ' The compare list shows the last valid expression.</div>';
                }
                const tokens = [tokenHtml(parsed.left, 'left', 0), '<span class="tok eq">=</span>']
                    .concat(parsed.right.map(function (tok, i) {
                        return tokenHtml(tok, 'right', i);
                    }));
                tokens.push('<button type="button" class="tok add" data-act="ins" data-kind="phr" data-end="1" title="add an operand at the end">+ phrase</button>');
                const tok = selectedToken(parsed);
                let picker = '';
                if (tok !== null && tok.t === T_PHR) {
                    picker = pickerHtml(f, tok);
                }
                host.innerHTML = '<div class="col-head"><h2>Formula</h2><span>' + chip + '</span></div>' +
                    '<h3>' + esc(f.name) + '</h3>' +
                    (f.description ? '<div class="note">' + esc(f.description) + '</div>' : '') +
                    '<div class="expr" id="exprRow" role="listbox" aria-label="expression">' + tokens.join('') + '</div>' +
                    toolsHtml(parsed, tok) +
                    picker +
                    '<div class="box"><div class="box-head"><h4>Expression as text</h4><span class="note">the phrase left of the = gets the result; each operand is a phrase in quotes</span></div>' +
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
                let entry = '<span class="chip missing">no value names ' + esc(u.names.join(', ')) + ' in the context</span>';
                if (u.hit !== null) {
                    const e = u.hit.entry;
                    let kind = e.kind === 'value' ? 'value' : 'result of ' + esc(e.formula);
                    if (!u.hit.strict) {
                        kind = kind + ' <span class="chip changed" title="no value names the phrases within the context of the result; the nearest value of the page is used">outside the context</span>';
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
            let result = '<div class="note">The expression could not be calculated.</div>';
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
            let chip = '<span class="chip same">same</span>';
            if (status === 'changed') {
                chip = '<span class="chip changed">changes</span>';
            } else if (status === 'missing') {
                chip = '<span class="chip missing">cannot be calculated</span>';
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
                host.innerHTML = '<div class="note">Select a formula to compare its results.</div>';
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
                let summary = '<span class="chip">' + own.length + ' ' + (own.length === 1 ? 'result' : 'results') + ' of this formula</span>';
                if (ownChanged > 0) {
                    summary = summary + '<span class="chip changed">' + ownChanged + ' change' + (ownChanged === 1 ? 's' : '') + '</span>';
                }
                if (ownMissing > 0) {
                    summary = summary + '<span class="chip missing">' + ownMissing + ' cannot be calculated</span>';
                }
                if (otherCount > 0) {
                    summary = summary + '<span class="chip changed">' + otherCount + ' result' + (otherCount === 1 ? '' : 's') + ' of ' + others.size + ' other formula' + (others.size === 1 ? '' : 's') + ' change' + (otherCount === 1 ? 's' : '') + ' too</span>';
                }
                if (ownChanged === 0 && ownMissing === 0 && otherCount === 0) {
                    summary = summary + '<span class="chip ok">no result changes</span>';
                }
                let rows = '<tr class="grp"><td colspan="4">' + esc(f.name) + ' <span class="note mono">' + esc(currentText(f)) + '</span></td></tr>';
                if (own.length === 0) {
                    rows = rows + '<tr><td colspan="4" class="note">The page has no stored result of this formula.</td></tr>';
                }
                rows = rows + own.map(function (i) {
                    return rowHtml(i, f);
                }).join('');
                others.forEach(function (list, name) {
                    const of = formulaByName(name);
                    rows = rows + '<tr class="grp"><td colspan="4">' + esc(name) + ' <span class="note">changes because it uses a changed result</span> <span class="note mono">' + esc(of !== null ? currentText(of) : '') + '</span></td></tr>';
                    rows = rows + list.map(function (i) {
                        return rowHtml(i, of);
                    }).join('');
                });
                host.innerHTML = '<div class="summary">' + summary + '</div>' +
                    '<div class="note">Until now: the number stored by the pod. After save: the number calculated with the changed expression, using the same values; click a row to see the values used.</div>' +
                    '<div class="twrap"><table class="cmp"><thead><tr><th>result</th><th class="n">until now</th><th class="n">after save</th><th></th></tr></thead><tbody>' + rows + '</tbody></table></div>';
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
                host.innerHTML = '<div class="note">No formula is changed.</div>';
            } else {
                const diffs = list.map(function (f) {
                    const result = '<div class="box"><div class="box-head"><h4>' + esc(f.name) + '</h4><button type="button" class="btn small" data-act="resetKey" data-key="' + esc(f.key) + '">discard</button></div>' +
                        '<div class="diff"><span class="k">until now</span><span class="mono old">' + esc(f.expression) + '</span>' +
                        '<span class="k">after save</span><span class="mono">' + esc(currentText(f)) + '</span></div></div>';
                    return result;
                }).join('');
                let errNote = '';
                if (hasErrors()) {
                    errNote = '<div class="err">A changed expression is not valid; fix it before the save.</div>';
                }
                host.innerHTML = '<div class="summary"><span class="chip changed">' + list.length + ' formula' + (list.length === 1 ? '' : 's') + '</span>' +
                    '<span class="chip">' + changedResults + ' result' + (changedResults === 1 ? '' : 's') + ' of the page change</span></div>' + errNote +
                    diffs +
                    '<div class="box"><div class="box-head"><h4>Update put message</h4><div class="row">' +
                    '<button type="button" class="btn small" data-act="copyJson">copy</button>' +
                    '<button type="button" class="btn small" data-act="applyLocal" title="take the changed expressions as the stored ones without a pod, e.g. to try the editor">apply without pod</button></div></div>' +
                    '<div class="note">Save posts this message to ' + esc(API_UPDATE_PUT) + '; the pod stores the expression as your own version of the formula and calculates the results again.</div>' +
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
            btn.textContent = 'Saving…';
            fetch(API_UPDATE_PUT, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken()},
                body: JSON.stringify(message)
            }).then(function (response) {
                let result = null;
                if (response.ok) {
                    result = response.json();
                } else {
                    throw new Error('the pod answered ' + response.status);
                }
                return result;
            }).then(function () {
                applySaved();
                toast('Saved: the changed formulas are stored as your version.');
            }).catch(function (e) {
                tab = 'changes';
                renderAll();
                toast('Not saved: ' + e.message + '. The message is shown in the Changes tab.');
            });
        }

        function updateToolbar() {
            /* the calculator editor counts and saves the changes in its own toolbar */
            if (embedded) {
                return;
            }
            const n = changedFormulas().length;
            const chip = document.getElementById('changeCount');
            chip.textContent = n === 0 ? 'no changes' : n + ' formula' + (n === 1 ? '' : 's') + ' changed';
            chip.className = n === 0 ? 'chip' : 'chip changed';
            document.getElementById('btnResetAll').disabled = n === 0;
            const btn = document.getElementById('btnSave');
            btn.disabled = n === 0 || hasErrors();
            btn.textContent = n === 0 ? 'Save' : 'Save (' + n + ')';
            btn.title = hasErrors() ? 'a changed expression is not valid' : '';
            document.getElementById('pageName').textContent = model.page || 'page';
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

        function insertToken(kind, c, atEnd) {
            editTokens(function (parsed) {
                let tok = {t: T_PHR, names: ['phrase']};
                if (kind === T_NUM) {
                    tok = {t: T_NUM, v: 1};
                } else if (kind === T_OP) {
                    tok = {t: T_OP, c: c};
                } else if (kind === T_BR) {
                    tok = {t: T_BR, c: c};
                }
                let pos = parsed.right.length;
                if (!atEnd && selTok !== null && selTok.side === 'right') {
                    pos = selTok.i + 1;
                }
                parsed.right.splice(pos, 0, tok);
                selTok = {side: 'right', i: pos};
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
                toast('Message copied.');
            }).catch(function () {
                const range = document.createRange();
                range.selectNodeContents(document.getElementById('jsonOut'));
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                toast('Copy is blocked here: the message is selected, copy it with the keyboard.');
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
                    throw new Error('the pod answered ' + response.status);
                }
                return result;
            }).then(function (doc) {
                boot(doc);
                toast('Loaded ' + (model.page || name) + '.');
            }).catch(function (e) {
                toast('Cannot load "' + name + '": ' + e.message + '.');
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
                    toast('Applied without a pod: the changed expressions are now the stored ones.');
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
