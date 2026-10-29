/*

    src/main/resources/script/calculator_json.js - build a calculator page in the browser from its json data
    --------------------------------------------

    the page (system_page::calculator_json) has only the empty host and the json data: the
    phraseData answer of the page phrase as the seed and the settings of the page with the number
    format, the translated texts, the save form, the names of the calculator keywords, the verbs
    and the phrase types and the unit conversions of the system formulas; every label, hint, list,
    order, unit and number of the page
    comes from the triples and values of the seed by the rules of
    web/component/execute/calculator.php and of the calculator functions of ui_list, so the page
    has the html and the styles of the calculator page of the server (docs/llm/frontend.md
    "Javascript only for the calculator")


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

    // the html ids of the data, the same as the CALC_EDITOR_* and CALC_PAGE_* consts of system_page
    const HOST_ID = 'calcPage';
    const SEED_ID = 'seed';
    const SETTINGS_ID = 'calcPageSettings';
    // the url var of a number field and of its save vars, the same as url_var NUMERIC_VALUE, GROUP, PRE, NAME and PHRASE
    const NUMBER_VAR = 'n';
    const GROUP_VAR = 'g';
    const PRE_VAR = '8';
    const NAME_VAR = 'k';
    const UNIT_VAR = 'p';
    // the styles of the calculator page of the server (web/html/styles.php, view_styles::USE_CASE)
    const STYLE = {
        grid: 'grid', panel: 'panel', inputs: 'inputs', field: 'field',
        row: 'row', unit: 'unit', hint: 'hint', tag: 'tag', tagMeasured: 'tag m', tagAssumed: 'tag a',
        scroll: 'scroll', section: 'sec', key: 'key', sub: 'sub', number: 'n', more: 'more',
        notes: 'notes', sources: 'src', select: 'form-control'
    };
    // the suffix of the html id of a selector, the same as html_selector builds it from its name
    const SELECT_ID_SUFFIX = '_form_select';
    // the translated texts by their message id (system_page::CALC_PAGE_TEXT_IDS)
    const TXT = {
        inputs: 'calculator_inputs', assumptions: 'calculator_assumptions', result: 'calculator_result',
        details: 'calculator_details', sources: 'calculator_sources',
        measured: 'field_tag_measured', assumed: 'field_tag_assumed'
    };

    const host = document.getElementById(HOST_ID);
    const seed = readJson(SEED_ID);
    const settings = readJson(SETTINGS_ID);
    if (host === null || seed === null || settings === null) {
        return;
    }
    const KW = settings.keywords || {};
    const VERB = settings.verbs || {};
    // the names of the phrase types that decide the unit and the format of a number
    const PT = settings.phrase_types || {};
    // per conversion of the system formulas the unit, the other unit, its text and the factor (number_format::unit_conversions)
    const CONVERSIONS = settings.conversions || [];
    const FMT = settings.format || {};
    const TEXTS = settings.texts || {};
    const FORM = settings.form || '';

    /* ------------------------------------------------------------------ */
    /* the data of the seed                                                 */
    /* ------------------------------------------------------------------ */

    // each word and triple of the seed by its name, a triple without its own name by its generated name
    const phrases = new Map();
    // the triples by the name of their from phrase, which hold the properties and the items of a phrase
    const children = new Map();
    // the symbol of a unit e.g. kWh/a of kilowatthours per year, and the phrase that a symbol stands for
    const symbols = new Map();
    const owners = new Map();
    // the acronym of a phrase e.g. Jan of January, the first one like calculator::acronym
    const acronyms = new Map();
    (seed.words || []).forEach(function (w) {
        phrases.set(w.name, {name: w.name, id: String(w.id), type: w.type || '', description: w.description || ''});
    });
    (seed.triples || []).forEach(function (t) {
        const name = t.name || generatedName(t);
        const trp = {name: name, id: '-' + t.id, type: t.type || '', from: t.from, verb: t.verb, to: t.to, description: t.description || ''};
        phrases.set(name, trp);
        if (!children.has(t.from)) {
            children.set(t.from, []);
        }
        children.get(t.from).push(trp);
        if (t.verb === VERB.symbol) {
            symbols.set(t.to, t.from);
            owners.set(t.from, t.to);
        }
        if (t.verb === VERB.acronym && !acronyms.has(t.to)) {
            acronyms.set(t.to, t.from);
        }
    });
    // the numbers of the page: the values first, then the results, as the server resolves an operand
    const pool = (seed.values || []).map(function (v) {
        return {id: String(v.id), words: v.words || [], number: v.number, source: v.source || '', description: v.description || ''};
    }).concat((seed.results || []).map(function (r) {
        // the stored number is kept, so that a calculation that differs from it can be reported
        return {id: String(r.id), words: r.words || [], number: r.number, stored: r.number, formula: r.formula || '', context: r.context || [], result: true};
    }));
    const sources = new Map((seed.sources || []).map(function (s) {
        return [s.name, s];
    }));
    const formulas = new Map((seed.formulas || []).map(function (f) {
        return [f.name, f];
    }));
    const page = phrases.get(((seed.selection || [])[0] || {}).name || '');
    if (page === undefined) {
        return;
    }
    const base = page.from === undefined ? null : page.from;
    // the number that each input field sets by the name of the field, which an input changes
    const inputEntries = new Map();
    // the names of the sources of the page in the order of their number (see sourceNames)
    let pageSources = [];
    // the values and results that the results of the page use (see operandValues)
    let pageOperands = new Set();
    // the results of the page phrase, whose operands are the assumptions and whose formulas are the notes of the page
    const pageResults = pool.filter(function (e) {
        return e.result && e.words.indexOf(page.name) >= 0;
    });

    /* ------------------------------------------------------------------ */
    /* helpers                                                              */
    /* ------------------------------------------------------------------ */

    /* the json of a data element, where an id beyond the safe integer of javascript e.g. the group id of a value is read as text */
    function readJson(id) {
        const element = document.getElementById(id);
        let result = null;
        if (element !== null) {
            result = JSON.parse(element.textContent.replace(/("id":\s*)(-?\d{16,})(?=\s*[,}\]])/g, '$1"$2"'));
        }
        return result;
    }

    /* like the backend triple::generate_name, because the other objects name a triple without its own name so */
    function generatedName(t) {
        const result = t.verb === VERB.is ? t.from + ' (' + t.to + ')' : t.from + ' ' + t.verb + ' ' + t.to;
        return result;
    }

    function esc(text) {
        const result = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        return result;
    }

    function capital(name) {
        const result = name === '' ? '' : name.charAt(0).toUpperCase() + name.slice(1);
        return result;
    }

    function block(tag, html, style) {
        const cls = style ? ' class="' + style + '"' : '';
        const result = '<' + tag + cls + '>' + html + '</' + tag + '>';
        return result;
    }

    function idOf(name) {
        const phr = phrases.get(name);
        const result = phr === undefined ? '' : phr.id;
        return result;
    }

    /* ------------------------------------------------------------------ */
    /* the phrase types of the seed, which say how a number is stated       */
    /* ------------------------------------------------------------------ */

    /* true if the phrase has the type, never for a type that the type cache does not know */
    function hasType(name, typeName) {
        const phr = phrases.get(name);
        const result = Boolean(typeName) && phr !== undefined && phr.type === typeName;
        return result;
    }

    function isMeasure(name) {
        return hasType(name, PT.measure);
    }

    function isPercent(name) {
        return hasType(name, PT.percent);
    }

    /* a ratio unit e.g. "share (ratio)" is a percent triple, whose number is never shown times 100 */
    function isRatioUnit(name) {
        const result = isPercent(name) && (phrases.get(name) || {}).from !== undefined;
        return result;
    }

    /* a unit, a scaling or a time word says how a number is stated, not what it measures (ui_list::is_marker) */
    function isMarker(name) {
        const result = [PT.measure, PT.percent, PT.scaling, PT.scaling_hidden, PT.time].some(function (typeName) {
            return hasType(name, typeName);
        });
        return result;
    }

    /* ------------------------------------------------------------------ */
    /* the reader of the calculator triples (calculator.php)                */
    /* ------------------------------------------------------------------ */

    function triplesFrom(name) {
        const result = name === null || name === undefined ? [] : (children.get(name) || []);
        return result;
    }

    /* a triple from the calculator keyword is a keyword too, also a proposed one that the page does not show yet */
    function isKeyword(name) {
        const lists = [KW.input, KW.assumption, KW.column, KW.rowgroup, KW.detail];
        const props = [KW.label, KW.hint, KW.highlight, KW.signed];
        const phr = phrases.get(name);
        // a word has no from phrase, so without the root name every word would count as a keyword
        const fromRoot = KW.root !== undefined && phr !== undefined && phr.from === KW.root;
        const result = lists.indexOf(name) >= 0 || props.indexOf(name) >= 0 || fromRoot;
        return result;
    }

    /* the triple from the phrase to the keyword e.g. "PV installation label" */
    function property(name, keyword) {
        const result = triplesFrom(name).find(function (t) {
            return t.to === keyword;
        }) || null;
        return result;
    }

    function ownText(name, keyword) {
        const prop = property(name, keyword);
        const result = prop === null || prop.description === '' ? null : prop.description;
        return result;
    }

    function group(list) {
        const result = property(page.name, list) || property(base, list);
        return result;
    }

    function items(list) {
        const grp = group(list);
        const result = grp === null ? [] : subItems(grp.name, VERB.has);
        return result;
    }

    function subItems(name, verb) {
        const result = sorted(triplesFrom(name).filter(function (t) {
            return t.verb === verb && !isKeyword(t.to);
        }), true);
        return result;
    }

    function filterOf(item) {
        const flt = subItems(item.name, VERB.with);
        const result = flt.length === 0 ? null : flt[0].to;
        return result;
    }

    function label(item, grp) {
        let result = ownText(item.name, KW.label) || ownText(item.to, KW.label);
        if (result === null && grp) {
            const other = otherPhrase(item.to, grp.to);
            if (other !== null) {
                result = ownText(other, KW.label) || capital(other);
            }
        }
        return result || capital(item.to || '');
    }

    function hint(item) {
        const result = ownText(item.name, KW.hint) || ownText(item.to, KW.hint) || '';
        return result;
    }

    function hasFlag(item, flag) {
        const result = [item.name, item.to, filterOf(item)].some(function (name) {
            return property(name, flag) !== null;
        });
        return result;
    }

    function sideResults(item) {
        const result = [];
        [item.to, filterOf(item)].forEach(function (name) {
            triplesFrom(name).forEach(function (t) {
                if (t.verb === VERB.has && !isKeyword(t.to)) {
                    result.push(t.to);
                }
            });
        });
        return result;
    }

    function phraseLabel(name) {
        const result = ownText(name, KW.label) || acronyms.get(name) || capital(name);
        return result;
    }

    /* the options of a selector phrase as [phrase name, label] */
    function options(name) {
        let result = subItems(name, VERB.has).map(function (opt) {
            return [opt.to, label(opt)];
        });
        if (result.length === 0) {
            result = kinds(name).map(function (k) {
                return [k, phraseLabel(k)];
            });
        }
        return result;
    }

    /* the phrases that are a kind of the phrase e.g. the months of a detail table */
    function kinds(name) {
        const result = [];
        phrases.forEach(function (p) {
            if (p.verb === VERB.is && p.to === name) {
                result.push(p.from);
            }
        });
        return sorted(result, false);
    }

    /* the option that the page phrase uses */
    function used(names) {
        const trp = triplesFrom(page.name).find(function (t) {
            return t.verb === VERB.uses && names.indexOf(t.to) >= 0;
        });
        const result = trp === undefined ? null : trp.to;
        return result;
    }

    function chosen(name) {
        const opts = options(name).map(function (o) {
            return o[0];
        });
        const result = used(opts) || name;
        return result;
    }

    function otherPhrase(name, grpName) {
        const phr = phrases.get(name);
        let result = null;
        if (phr !== undefined && phr.from !== undefined) {
            if (phr.from === grpName) {
                result = phr.to;
            } else if (phr.to === grpName) {
                result = phr.from;
            }
        }
        return result;
    }

    /* the longest follower chain first, then the other items by their label (calculator::sorted) */
    function sorted(list, asItems) {
        const keyOf = function (x) {
            return asItems ? x.name : x;
        };
        const keys = list.map(keyOf);
        const next = new Map();
        list.forEach(function (x) {
            triplesFrom(keyOf(x)).forEach(function (t) {
                if (t.verb === VERB.follow && keys.indexOf(t.to) >= 0) {
                    next.set(t.to, keyOf(x));
                }
            });
        });
        const followers = Array.from(next.values());
        let chain = [];
        keys.forEach(function (k) {
            if (followers.indexOf(k) < 0) {
                const ids = [k];
                while (next.has(ids[ids.length - 1]) && ids.indexOf(next.get(ids[ids.length - 1])) < 0) {
                    ids.push(next.get(ids[ids.length - 1]));
                }
                if (ids.length > 1 && ids.length > chain.length) {
                    chain = ids;
                }
            }
        });
        const rest = list.filter(function (x) {
            return chain.indexOf(keyOf(x)) < 0;
        }).map(function (x) {
            const lbl = asItems ? label(x) : phraseLabel(x);
            return [lbl + ' ' + idOf(keyOf(x)), x];
        }).sort(function (a, b) {
            return a[0] < b[0] ? -1 : (a[0] > b[0] ? 1 : 0);
        }).map(function (r) {
            return r[1];
        });
        const inChain = chain.map(function (k) {
            return list[keys.indexOf(k)];
        });
        return inChain.concat(rest);
    }

    /* ------------------------------------------------------------------ */
    /* the numbers of the page by a plain match of their phrases            */
    /* ------------------------------------------------------------------ */

    function hasAll(entry, names) {
        const result = names.every(function (n) {
            return entry.words.indexOf(n) >= 0;
        });
        return result;
    }

    /* the entries of the fewest words, or null with a warning if they are not one number (ui_list::calculator_result) */
    function onlyOne(best, what) {
        const numbers = new Set(best.map(function (e) {
            return String(e.number);
        }));
        let result = best.length === 0 ? null : best[0];
        if (numbers.size > 1) {
            console.warn('the data selects several numbers for ' + what + ': ' + Array.from(numbers).join(', '));
            result = null;
        }
        return result;
    }

    /* the number of a cell: the value or result with all the phrases of the cell and the fewest other phrases */
    function cellEntry(names) {
        const wanted = names.filter(function (n) {
            return n;
        });
        let best = [];
        pool.forEach(function (e) {
            if (hasAll(e, wanted)) {
                if (best.length === 0 || e.words.length < best[0].words.length) {
                    best = [e];
                } else if (e.words.length === best[0].words.length) {
                    best.push(e);
                }
            }
        });
        return onlyOne(best, wanted.join(', '));
    }

    /* the value of a field: the operand of a result of the page first, so that a changed field changes the results, then with the page phrase
       before a part of the page triple, then with the fewest other phrases (ui_list::calculator_value) */
    function fieldEntry(name) {
        const props = [KW.limit_down, KW.limit_up, KW.step, KW.confidence];
        const parts = [page.from, page.to].filter(function (p) {
            return p;
        });
        let best = [];
        let bestRank = null;
        pool.forEach(function (e) {
            const isField = !e.result && e.words.indexOf(name) >= 0 && hasFieldValuePhrases(name, e)
                && !props.some(function (p) {
                    return e.words.indexOf(p) >= 0;
                });
            if (isField) {
                const pageRank = e.words.indexOf(page.name) >= 0 ? 0 : (parts.some(function (p) {
                    return e.words.indexOf(p) >= 0;
                }) ? 1 : 2);
                const context = e.words.filter(function (w) {
                    return w !== name && !isMarker(w);
                }).length;
                const operandRank = pageOperands.has(e) ? 0 : 1;
                const rank = [operandRank, pageRank, context, e.words.length];
                const cmp = bestRank === null ? -1 : compareRank(rank, bestRank);
                if (cmp < 0) {
                    best = [e];
                    bestRank = rank;
                } else if (cmp === 0) {
                    best.push(e);
                }
            }
        });
        return onlyOne(best, name);
    }

    function compareRank(a, b) {
        let result = 0;
        a.forEach(function (x, i) {
            if (result === 0 && x !== b[i]) {
                result = x < b[i] ? -1 : 1;
            }
        });
        return result;
    }

    /* false if a triple of the field names a phrase that the value of the field must have, which the value lacks */
    function hasFieldValuePhrases(name, entry) {
        const result = triplesFrom(name).every(function (trp) {
            const isValuePhrase = property(trp.name, KW.value_phrases) !== null;
            return !isValuePhrase || entry.words.indexOf(trp.to) >= 0;
        });
        return result;
    }

    function fieldType(name) {
        const prop = triplesFrom(name).find(function (t) {
            return t.to === KW.selector;
        });
        const result = prop === undefined ? '' : KW.selector;
        return result;
    }

    /* a limit or the step of a field e.g. the value of PV capacity and field step */
    function fieldProperty(name, prop) {
        const entry = cellEntry([name, prop]);
        const result = entry === null ? null : entry.number;
        return result;
    }

    /* the unit text of a number: the symbol of its measure or ratio unit, else the name of its percent word (ui_list::unit_name) */
    function unitOf(entry) {
        let result = '';
        entry.words.forEach(function (w) {
            if (result === '' && (isMeasure(w) || isRatioUnit(w))) {
                result = symbols.get(w) || w;
            } else if (result === '' && isPercent(w)) {
                result = w;
            }
        });
        return result;
    }

    /* the measure unit of a number, which a column may convert e.g. kg to tonne */
    function measureOf(entry) {
        const result = entry.words.find(isMeasure) || null;
        return result;
    }

    function isPercentEntry(entry) {
        return entry.words.some(isPercent);
    }

    /* the number of a field or an option: a share with the percent decimals e.g. 0.90 (ui_list::field_decimals) */
    function fieldNumber(entry) {
        const result = isPercentEntry(entry) ? Number(entry.number).toFixed(FMT.percent_decimals) : String(entry.number);
        return result;
    }

    /* the number of the source of a value in the source list of the page e.g. [2] */
    function sourceRef(entry) {
        const pos = entry === null ? -1 : pageSources.indexOf(entry.source);
        const result = pos < 0 ? '' : '[' + (pos + 1) + ']';
        return result;
    }

    /* the sources of the page in the order of their first use: the values of the number fields of
       the inputs and of the listed assumptions, then the operands of the results (ui_list::calculator_source_ids) */
    function sourceNames() {
        const fieldValues = items(KW.input).concat(items(KW.assumption)).filter(function (item) {
            return fieldType(item.to) !== KW.selector;
        }).map(function (item) {
            return fieldEntry(item.to);
        });
        const result = [];
        fieldValues.concat(operandValues()).forEach(function (e) {
            if (e !== null && sources.has(e.source) && result.indexOf(e.source) < 0) {
                result.push(e.source);
            }
        });
        return result;
    }

    /* ------------------------------------------------------------------ */
    /* the calculation of the results from their formulas (formula_calc)    */
    /* ------------------------------------------------------------------ */

    // the terms of an expression e.g. "PV capacity" or "battery charge", "January" (formula_calc::operand_pattern)
    const OPERAND_PATTERN = /"[^"]+"(?:\s*,\s*"[^"]+")*/g;
    // one number or one char of the arithmetic between the operands (formula_calc::TOKEN_PATTERN)
    const TOKEN_PATTERN = /\s*(\d+(?:\.\d+)?|[-+*\/()])/y;
    // the char between the result and the calculation of a formula (chars::CHAR_CALC)
    const CHAR_CALC = '=';
    // the decimals of a compare, the same as formula_calc::DECIMALS
    const CALC_DECIMALS = 2;

    function operandNames(term) {
        const result = term.replace(/"/g, '').split(',').map(function (n) {
            return n.trim();
        });
        return result;
    }

    function calculation(res) {
        const frm = formulas.get(res.formula);
        const text = frm === undefined ? '' : String(frm.expression || '');
        const pos = text.indexOf(CHAR_CALC);
        const result = pos < 0 || res.context.length === 0 ? '' : text.slice(pos + 1);
        return result;
    }

    /* the value or result with all names of the operand, all of whose phrases are in the context of the result (formula_calc::value_of) */
    function operandEntry(names, res) {
        const result = pool.find(function (e) {
            return e !== res && hasAll(e, names) && e.words.every(function (w) {
                return res.context.indexOf(w) >= 0;
            });
        }) || null;
        return result;
    }

    /* the values and results that the results of the page use, each once (formula_calc::operands) */
    function operandValues() {
        const result = [];
        pageResults.forEach(function (res) {
            (calculation(res).match(OPERAND_PATTERN) || []).forEach(function (term) {
                const entry = operandEntry(operandNames(term), res);
                if (entry !== null && result.indexOf(entry) < 0) {
                    result.push(entry);
                }
            });
        });
        return result;
    }

    /* the number of a result calculated with the numbers of the page, its operand results calculated first, or null if an operand is missing;
       done is null for the check of the stored results, which uses the stored numbers of the operand results like ui_list::check_results */
    function calculated(res, done, open) {
        let result = null;
        if (done !== null && done.has(res)) {
            result = done.get(res);
        } else if (!open.has(res) && calculation(res) !== '') {
            open.add(res);
            let missing = false;
            const text = calculation(res).replace(OPERAND_PATTERN, function (term) {
                const entry = operandEntry(operandNames(term), res);
                const num = entry === null ? null : operandNumber(entry, done, open);
                missing = missing || num === null;
                return num === null ? '0' : '(' + num.toFixed(12) + ')';
            });
            result = missing ? null : evaluate(text);
            open.delete(res);
            if (done !== null) {
                done.set(res, result);
            }
        }
        return result;
    }

    /* the number of an operand: a value as entered, a result as stored for the check or else
       calculated, and without a calculation e.g. a modelled result as stored */
    function operandNumber(entry, done, open) {
        let result = Number(entry.number);
        if (entry.result) {
            const num = done === null ? null : calculated(entry, done, open);
            result = num === null ? Number(entry.stored) : num;
        }
        return result;
    }

    /* the check of the page start: each result with the stored numbers of the results it uses, so
       that only a stored number that differs is replaced and reported (ui_list::check_results) */
    function checkResults() {
        const scale = Math.pow(10, CALC_DECIMALS);
        pool.filter(function (e) {
            return e.result;
        }).forEach(function (res) {
            const num = calculated(res, null, new Set());
            if (num !== null && Math.round(num * scale) !== Math.round(Number(res.stored) * scale)) {
                console.warn('the stored result ' + res.words.join(', ') + ' is ' + res.stored + ', but the page calculates ' + num);
                res.number = num;
            }
        });
    }

    /* after a changed input every result again, the results that a result uses first */
    function recalculate() {
        const done = new Map();
        pool.filter(function (e) {
            return e.result;
        }).forEach(function (res) {
            const num = calculated(res, done, new Set());
            res.number = num === null ? res.stored : num;
        });
    }

    /* the arithmetic of shared/calc/arithmetic.php: a sum of products of signed numbers or brackets */
    function evaluate(text) {
        const tokens = [];
        TOKEN_PATTERN.lastIndex = 0;
        let found = TOKEN_PATTERN.exec(text);
        let end = 0;
        while (found !== null) {
            tokens.push(found[1]);
            end = TOKEN_PATTERN.lastIndex;
            found = TOKEN_PATTERN.exec(text);
        }
        const state = {pos: 0, ok: text.slice(end).trim() === ''};
        const value = sum(tokens, state);
        const result = state.ok && state.pos === tokens.length && isFinite(value) ? value : null;
        return result;
    }

    function sum(tokens, state) {
        let result = product(tokens, state);
        while (tokens[state.pos] === '+' || tokens[state.pos] === '-') {
            const op = tokens[state.pos];
            state.pos++;
            const next = product(tokens, state);
            result = op === '+' ? result + next : result - next;
        }
        return result;
    }

    function product(tokens, state) {
        let result = signed(tokens, state);
        while (tokens[state.pos] === '*' || tokens[state.pos] === '/') {
            const op = tokens[state.pos];
            state.pos++;
            const next = signed(tokens, state);
            result = op === '*' ? result * next : result / next;
        }
        return result;
    }

    function signed(tokens, state) {
        const token = tokens[state.pos];
        let result = NaN;
        if (token === '-' || token === '+') {
            state.pos++;
            result = token === '-' ? -signed(tokens, state) : signed(tokens, state);
        } else if (token === '(') {
            state.pos++;
            result = sum(tokens, state);
            state.ok = state.ok && tokens[state.pos] === ')';
            state.pos++;
        } else if (token !== undefined && !isNaN(Number(token))) {
            state.pos++;
            result = Number(token);
        } else {
            state.ok = false;
        }
        return result;
    }

    /* ------------------------------------------------------------------ */
    /* the number format of the user                                        */
    /* ------------------------------------------------------------------ */

    function magnitude(num) {
        return Math.floor(Math.log10(num)) + 1;
    }

    /* the nearest conversion of the unit if the largest number of a group has too many or too few digits (number_format::next_unit) */
    function nextUnit(num, unit) {
        const tooBig = magnitude(num) > FMT.digits;
        const tooSmall = num < 1;
        let result = null;
        CONVERSIONS.forEach(function (cnv) {
            const isUnit = cnv[0] === unit || cnv[0] === owners.get(unit);
            const shown = num * cnv[3];
            const toLarger = tooBig && cnv[3] < 1 && shown >= 1;
            const toSmaller = tooSmall && cnv[3] > 1 && magnitude(shown) <= FMT.digits;
            const nearer = result === null || Math.abs(Math.log(cnv[3])) < Math.abs(Math.log(result[3]));
            if (isUnit && (toLarger || toSmaller) && nearer) {
                result = cnv;
            }
        });
        return result;
    }

    /* the unit, its text, the factor and the decimals of a group of numbers of one unit (number_format::group) */
    function groupFormat(numbers, unit) {
        const max = numbers.reduce(function (m, n) {
            return Math.max(m, Math.abs(Number(n)));
        }, 0);
        const result = {unit: unit, text: unit === null ? null : (symbols.get(unit) || unit), factor: 1, decimals: 0};
        if (unit !== null && max > 0) {
            let cnv = nextUnit(max, unit);
            while (cnv !== null) {
                result.unit = cnv[1];
                result.text = cnv[2];
                result.factor = result.factor * cnv[3];
                cnv = nextUnit(max * result.factor, result.unit);
            }
        }
        if (max > 0) {
            result.decimals = Math.max(0, FMT.digits - magnitude(max * result.factor));
        }
        return result;
    }

    function formatNumber(num, decimals) {
        const fixed = Math.abs(Number(num)).toFixed(decimals).split('.');
        const int = fixed[0].replace(/\B(?=(\d{3})+(?!\d))/g, FMT.thousand_sep || "'");
        const sign = Number(num) < 0 ? '-' : '';
        const result = sign + int + (fixed.length > 1 ? (FMT.dec_point || '.') + fixed[1] : '');
        return result;
    }

    /* per unit the format of the numbers of one column, so that the numbers of a unit can be compared at a glance (ui_list::calculator_formats) */
    function columnFormats(entries, withUnit) {
        const byUnit = new Map();
        entries.filter(function (e) {
            return e !== null;
        }).forEach(function (e) {
            const unit = unitOf(e);
            if (!byUnit.has(unit)) {
                byUnit.set(unit, []);
            }
            byUnit.get(unit).push(e);
        });
        const result = new Map();
        byUnit.forEach(function (group, unit) {
            const nums = group.map(function (e) {
                return e.number;
            });
            result.set(unit, groupFormat(nums, withUnit ? measureOf(group[0]) : null));
        });
        return result;
    }

    /* a share times 100 with the percent decimals and its symbol e.g. "49 %" (sandbox_value::val_formatted) */
    function percentText(entry) {
        const word = entry.words.find(isPercent);
        const scale = Math.pow(10, FMT.percent_decimals);
        const num = Math.round(Number(entry.number) * 100 * scale) / scale;
        const sym = isRatioUnit(word) ? '' : (symbols.get(word) || '');
        const result = String(num) + (sym === '' ? '' : FMT.unit_sep + sym);
        return result;
    }

    /* the number in the format of its group with the unit text, which a converted group takes from the conversion */
    function numberText(entry, formats, withUnit) {
        let result = '';
        if (isPercentEntry(entry)) {
            result = percentText(entry);
        } else {
            const fmt = formats.get(unitOf(entry));
            const unit = withUnit ? (fmt.text === null ? unitOf(entry) : fmt.text) : '';
            result = formatNumber(Number(entry.number) * fmt.factor, fmt.decimals) + (unit === '' ? '' : FMT.unit_sep + unit);
        }
        return result;
    }

    function cellHtml(entry, formats, signed, withUnit) {
        let text = '';
        if (entry !== null) {
            text = esc(numberText(entry, formats, withUnit));
            if (signed) {
                text = block('span', text, Number(entry.number) < 0 ? FMT.negative : FMT.positive);
            }
        }
        return block('td', text, STYLE.number);
    }

    /* ------------------------------------------------------------------ */
    /* the page                                                             */
    /* ------------------------------------------------------------------ */

    function title() {
        const pageLabel = ownText(page.name, KW.label) || ownText(base, KW.label) || capital(page.name);
        const lead = ownText(page.name, KW.hint) || ownText(base, KW.hint) || '';
        const result = block('header', block('h4', esc(pageLabel)) + (lead === '' ? '' : block('p', esc(lead))));
        return result;
    }

    function hidden(name, value) {
        const result = '<input type="hidden" name="' + esc(name) + '" value="' + esc(value) + '" form="' + esc(FORM) + '">';
        return result;
    }

    function hintHtml(text, ref) {
        const txt = (esc(text) + ' ' + ref).trim();
        const result = txt === '' ? '' : block('span', txt, STYLE.hint);
        return result;
    }

    /* measured for a value with a source, else assumed with its confidence e.g. "assumed 0.6" (ui_list::assumption_tag) */
    function tagText(entry) {
        let result = entry.source ? TEXTS[TXT.measured] : TEXTS[TXT.assumed];
        const conf = entry.source ? null : cellEntry(entry.words.concat([KW.confidence]));
        if (conf !== null) {
            result += ' ' + conf.number;
        }
        return result;
    }

    /* the hidden vars that the save needs for a number field, with the stored number as the shown one (ui_list::calculator_save_vars) */
    function saveVars(key, entry, lbl, unit) {
        let result = hidden(GROUP_VAR + key, entry.id) + hidden(PRE_VAR + NUMBER_VAR + key, entry.number) + hidden(NAME_VAR + key, lbl);
        if (unit !== '') {
            result += hidden(UNIT_VAR + key, unit);
        }
        return result;
    }

    function numberField(item, name, assumption) {
        const entry = fieldEntry(name);
        const key = idOf(name);
        const field = NUMBER_VAR + key;
        const lbl = label(item);
        let text = esc(lbl);
        let hintText = hint(item);
        if (assumption && entry !== null) {
            text += ' ' + block('span', esc(tagText(entry)), entry.source ? STYLE.tagMeasured : STYLE.tagAssumed);
            hintText = hintText || entry.description;
        }
        const limits = [['min', KW.limit_down], ['max', KW.limit_up], ['step', KW.step]].map(function (l) {
            const num = fieldProperty(name, l[1]);
            return num === null ? '' : ' ' + l[0] + '="' + esc(num) + '"';
        }).join('');
        const shown = entry === null ? '' : fieldNumber(entry);
        let input = '<input type="number" name="' + field + '" id="' + field + '" value="' + esc(shown) + '"' + limits + ' form="' + esc(FORM) + '">';
        const unit = entry === null ? '' : unitOf(entry);
        if (entry !== null) {
            input += saveVars(key, entry, lbl, unit);
            inputEntries.set(field, entry);
        }
        if (unit !== '') {
            input += block('span', esc(unit), STYLE.unit);
        }
        const result = block('div', '<label for="' + field + '">' + text + '</label>' + block('div', input, STYLE.row)
            + hintHtml(hintText, sourceRef(entry)), STYLE.field);
        return result;
    }

    /* the operand values of the results that no listed assumption shows, sorted by their label (ui_list::assumption_fields) */
    function operandFields(listed) {
        const fieldsByKey = [];
        operandValues().forEach(function (e) {
            const isOther = !e.result && e.words.indexOf(page.name) < 0 && !e.words.some(function (w) {
                return listed.indexOf(w) >= 0;
            });
            if (isOther) {
                const lbl = e.words.filter(function (w) {
                    return !isMarker(w);
                }).join(' ');
                fieldsByKey.push([lbl + e.id, operandField(e, lbl)]);
            }
        });
        const result = fieldsByKey.sort(function (a, b) {
            return a[0] < b[0] ? -1 : (a[0] > b[0] ? 1 : 0);
        }).map(function (f) {
            return f[1];
        }).join('');
        return result;
    }

    /* the field of an operand value with its source name as the hint (ui_list::assumption_field) */
    function operandField(entry, lbl) {
        const field = NUMBER_VAR + entry.id;
        const unit = unitOf(entry);
        const text = esc(lbl) + ' ' + block('span', esc(tagText(entry)), STYLE.tag);
        let input = '<input type="number" name="' + field + '" id="' + field + '" value="' + esc(entry.number) + '" form="' + esc(FORM) + '">'
            + saveVars(entry.id, entry, lbl, unit);
        if (unit !== '') {
            input += block('span', esc(unit), STYLE.unit);
        }
        input += hintHtml(entry.source || '', '');
        inputEntries.set(field, entry);
        return block('div', '<label for="' + field + '">' + text + '</label>' + input, STYLE.field);
    }

    function selectorField(item, name) {
        const field = NUMBER_VAR + idOf(name);
        const domId = field + SELECT_ID_SUFFIX;
        const opts = options(name);
        const usedOpt = used(opts.map(function (o) {
            return o[0];
        }));
        const html = opts.map(function (o) {
            const entry = cellEntry([name, o[0]]) || fieldEntry(o[0]);
            const sel = o[0] === usedOpt ? ' selected' : '';
            // the selector sets the number of the option that the page uses, which the results read
            if (entry !== null && o[0] === usedOpt) {
                inputEntries.set(field, entry);
            }
            return entry === null ? '' : '<option value="' + esc(fieldNumber(entry)) + '"' + sel + '>' + esc(o[1]) + '</option>';
        }).join('');
        const select = '<select class="' + STYLE.select + '" name="' + field + '" form="' + esc(FORM) + '" id="' + domId + '">' + html + '</select>';
        const result = block('div', '<label for="' + domId + '">' + esc(label(item)) + '</label>' + select
            + hintHtml(hint(item), ''), STYLE.field);
        return result;
    }

    function fields(list, assumption) {
        const result = list.map(function (item) {
            return fieldType(item.to) === KW.selector ? selectorField(item, item.to) : numberField(item, item.to, assumption);
        }).join('');
        return result;
    }

    function inputPanel() {
        const inputs = items(KW.input);
        const assumptions = items(KW.assumption);
        let result = '';
        if (inputs.length > 0 || assumptions.length > 0) {
            const heading = ownText((group(KW.input) || {}).name, KW.label) || TEXTS[TXT.inputs];
            result = block('div', block('h2', esc(heading)) + fields(inputs, false) + (settings.save || ''), STYLE.inputs);
            const listed = assumptions.map(function (item) {
                return item.to;
            });
            const assumed = fields(assumptions, true) + operandFields(listed);
            if (assumed !== '') {
                const summary = ownText((group(KW.assumption) || {}).name, KW.label) || TEXTS[TXT.assumptions];
                result += block('details', block('summary', esc(summary)) + assumed);
            }
        }
        return block('aside', result, STYLE.panel);
    }

    function table(head, rows) {
        const result = block('div', block('table', block('thead', block('tr', head)) + block('tbody', rows)), STYLE.scroll);
        return result;
    }

    /* the sub text of a row: its hint, else its side results with their number */
    function rowSub(row, ctx) {
        let sub = hint(row);
        if (sub === '') {
            sub = sideResults(row).map(function (side) {
                const entry = cellEntry(ctx.concat([side]));
                return entry === null ? '' : phraseLabel(side) + ' ' + numberText(entry, columnFormats([entry], true), true);
            }).filter(function (s) {
                return s !== '';
            }).join(', ');
        }
        const result = sub === '' ? '' : block('span', esc(sub), STYLE.sub);
        return result;
    }

    /* the number of the row phrase after the label of a row with a filter e.g. " (128 g/kWh)" (ui_list::calculator_row_number) */
    function rowNumber(row) {
        const entry = filterOf(row) === null ? null : fieldEntry(chosen(row.to));
        const result = entry === null ? '' : ' (' + numberText(entry, columnFormats([entry], true), true) + ')';
        return result;
    }

    function resultTable() {
        const cols = items(KW.column);
        const grps = items(KW.rowgroup);
        let result = '';
        if (cols.length > 0 || grps.length > 0) {
            const head = block('th', esc(TEXTS[TXT.result])) + cols.map(function (c) {
                return block('th', esc(label(c)), STYLE.number);
            }).join('');
            // the numbers first, so that the numbers of one column and unit share their decimals
            const rows = [];
            grps.forEach(function (g) {
                subItems(g.name, VERB.has).forEach(function (r) {
                    const ctx = [page.name, chosen(r.to), filterOf(r)];
                    rows.push({grp: g, row: r, ctx: ctx, cells: cols.map(function (c) {
                        return cellEntry(ctx.concat([c.to]));
                    })});
                });
            });
            const formats = cols.map(function (c, i) {
                return columnFormats(rows.map(function (r) {
                    return r.cells[i];
                }), true);
            });
            let body = '';
            grps.forEach(function (g) {
                body += block('tr', '<td colspan="' + (cols.length + 1) + '">' + esc(label(g)) + '</td>', STYLE.section);
                const signed = hasFlag(g, KW.signed);
                rows.filter(function (r) {
                    return r.grp === g;
                }).forEach(function (r) {
                    const cells = r.cells.map(function (e, i) {
                        return cellHtml(e, formats[i], signed, true);
                    }).join('');
                    const first = esc(label(r.row, g) + rowNumber(r.row)) + rowSub(r.row, r.ctx);
                    body += block('tr', block('td', first) + cells, hasFlag(r.row, KW.highlight) ? STYLE.key : '');
                });
            });
            result = table(head, body);
        }
        return result;
    }

    function detailTables() {
        let result = '';
        items(KW.detail).forEach(function (tbl) {
            const cols = subItems(tbl.name, VERB.has);
            const head = block('th', esc(phraseLabel(tbl.to))) + cols.map(function (c) {
                return block('th', esc(label(c)), STYLE.number);
            }).join('');
            const rows = kinds(tbl.to).map(function (k) {
                return [phraseLabel(k), [page.name, k], false];
            }).concat(subItems(tbl.name, VERB.with).map(function (x) {
                return [label(x), [page.name, x.to], hasFlag(x, KW.highlight)];
            }));
            const cells = rows.map(function (r) {
                return cols.map(function (c) {
                    return cellEntry(r[1].concat([c.to, filterOf(c)]));
                });
            });
            // the header names the unit, so a column of a detail table shares only the decimals
            const formats = cols.map(function (c, i) {
                return columnFormats(cells.map(function (row) {
                    return row[i];
                }), false);
            });
            const body = rows.map(function (r, j) {
                const tds = cells[j].map(function (e, i) {
                    return cellHtml(e, formats[i], false, false);
                }).join('');
                return block('tr', block('td', esc(r[0])) + tds, r[2] ? STYLE.key : '');
            }).join('');
            result += block('h2', esc(label(tbl))) + table(head, body);
        });
        return result;
    }

    /* the descriptions of the formulas of the results of the page, sorted by the formula name (ui_list::formula_notes) */
    function notes() {
        const names = new Set(pageResults.map(function (res) {
            return res.formula;
        }));
        const html = Array.from(names).sort().map(function (name) {
            const frm = formulas.get(name);
            return frm === undefined || !frm.description ? '' : block('p', block('b', esc(name)) + ' ' + esc(frm.description));
        }).join('');
        const result = html === '' ? '' : block('div', html, STYLE.notes);
        return result;
    }

    function sourceList() {
        const html = pageSources.map(function (name) {
            const src = sources.get(name);
            const text = src.url ? '<a href="' + esc(src.url) + '">' + esc(name) + '</a>' : esc(name);
            return block('li', text);
        }).join('');
        const result = html === '' ? '' : block('div', block('h2', esc(TEXTS[TXT.sources])) + block('ol', html), STYLE.sources);
        return result;
    }

    function resultPanel() {
        const heading = ownText((group(KW.column) || {}).name, KW.label) || '';
        let result = (heading === '' ? '' : block('h2', esc(heading))) + resultTable();
        // the details after the main table, so that their notes and sources include the numbers of both
        const more = detailTables();
        const extra = more + notes() + sourceList();
        if (extra !== '') {
            const summary = ownText((group(KW.detail) || {}).name, KW.label) || TEXTS[TXT.details];
            result += block('details', block('summary', esc(summary)) + block('div', extra, STYLE.more));
        }
        return block('section', result, STYLE.panel);
    }

    /* the result panel again with the numbers of the inputs, the open blocks staying open */
    function refreshResults(event) {
        const entry = inputEntries.get(event.target.name);
        const panel = host.querySelector('section.' + STYLE.panel);
        const value = Number(event.target.value);
        if (entry !== undefined && panel !== null && event.target.value !== '' && isFinite(value)) {
            entry.number = value;
            recalculate();
            const open = Array.from(panel.querySelectorAll('details')).map(function (d) {
                return d.open;
            });
            panel.outerHTML = resultPanel();
            host.querySelectorAll('section.' + STYLE.panel + ' details').forEach(function (d, i) {
                d.open = open[i] === true;
            });
        }
    }

    // the stored results are checked once, after an input change the page shows the numbers that it calculates
    checkResults();
    pageOperands = new Set(operandValues());
    pageSources = sourceNames();
    // the host has the style of the use case page (system_page::calculator_json), so it gets only its content
    host.innerHTML = title() + block('div', inputPanel() + resultPanel(), STYLE.grid);
    host.addEventListener('input', refreshResults);
    host.addEventListener('change', refreshResults);

})();
