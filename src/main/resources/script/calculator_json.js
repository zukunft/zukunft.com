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
    // the field of the name of a case, the api that creates a case and the url var of the page phrase, the same as url_var ID
    const CASE_FIELD_ID = 'caseName';
    const CASE_ROUTE = '/api/updatePut/';
    const ID_VAR = 'id';
    // the url var of a number field and of its save vars, the same as url_var NUMERIC_VALUE, GROUP, PRE, NAME and PHRASE
    const NUMBER_VAR = 'n';
    // in front of the phrase id of a selector option without a number, the same as ui_list CALC_OPTION_PHRASE
    const OPTION_PHRASE = 'phrase';
    // the header of the row number of a detail table, the same as ui_list CALC_ROW_NUMBER_HEAD
    const ROW_NUMBER_HEAD = '#';
    // the icon of the button to remove an instance and the instances that a field keeps, the same as ui_list CALC_REMOVE_ICON and CALC_MIN_INSTANCES
    const REMOVE_ICON = 'fas fa-times';
    const MIN_INSTANCES = 1;
    // the operand name that reads the number of the previous row, the same as phrase_types PRIOR_NAME
    const PRIOR_NAME = 'prior';
    // the word of a value of the check table, the same as words EXPECTED
    const EXPECTED_WORD = 'expected';
    // the size of a line diagram, the same as html_base DIAGRAM_*
    const DIAGRAM = {width: 760, height: 200, left: 70, right: 12, top: 12, bottom: 24, point: '2.4'};
    // the mark of a line in the legend, the same as ui_list CALC_DIAGRAM_MARK
    const DIAGRAM_MARK = '●';
    // the unit of a number field that shows a stored ratio in percent, the same as sandbox_value UNIT_PERCENT
    const UNIT_PERCENT = '%';
    const GROUP_VAR = 'g';
    const PRE_VAR = '8';
    const NAME_VAR = 'k';
    const UNIT_VAR = 'p';
    // the styles of the calculator page of the server (web/html/styles.php, view_styles::USE_CASE)
    const STYLE = {
        grid: 'grid', panel: 'panel', inputs: 'inputs', field: 'field',
        row: 'row', unit: 'unit', hint: 'hint', tag: 'tag', tagMeasured: 'tag m', tagAssumed: 'tag a',
        scroll: 'scroll', section: 'sec', key: 'key', sub: 'sub', number: 'n', more: 'more',
        notes: 'notes', sources: 'src', select: 'form-control', check: 'check', readOnly: 'ro',
        addButton: 'btn btn-outline-primary btn-sm', removeButton: 'btn btn-outline-secondary btn-sm', imports: 'imports',
        warnings: 'warnings', warning: 'warn',
        button: 'btn btn-outline-primary', chart: 'chart', legend: 'legend', statusOk: 'pill ok', statusFail: 'pill fail',
        // the colours of the flagged columns, the first for a single column, the others cycling for the instances (styles::CALC_COLUMN_COLOURS)
        colours: ['col-first', 'col-1', 'col-2', 'col-3', 'col-4']
    };
    // a warning ignores only the float noise of a calculation, the same as ui_list CALC_WARNING_DECIMALS
    const WARNING_DECIMALS = 12;
    // a check row ignores only the float noise of a calculation, the same as ui_list CALC_CHECK_TOLERANCE
    const CHECK_TOLERANCE = 1e-9;
    // a shown number is first rounded to these decimals, the same as number_format NOISE_DECIMALS
    const NOISE_DECIMALS = 8;
    // the suffix of the html id of a selector, the same as html_selector builds it from its name
    const SELECT_ID_SUFFIX = '_form_select';
    // between the label of a selector option and its number (ui_list::CALC_OPTION_SEP)
    const OPTION_SEP = ' — ';
    // in a hint the place of the source number and a phrase shown as its number (ui_list::CALC_HINT_SOURCE and CALC_HINT_NUMBER_PATTERN)
    const HINT_SOURCE = '[n]';
    const HINT_NUMBER_PATTERN = /\{([^{}|]+)(\|number)?\}/g;
    // the translated texts by their message id (system_page::CALC_PAGE_TEXT_IDS)
    const TXT = {
        inputs: 'calculator_inputs', assumptions: 'calculator_assumptions', result: 'calculator_result',
        details: 'calculator_details', sources: 'calculator_sources',
        copy_json: 'calculator_copy_json', copy_csv: 'calculator_copy_csv', remove: 'calculator_remove',
        diagram_final: 'calculator_diagram_final',
        case_name: 'calculator_case_name', case_failed: 'calculator_case_failed',
        check_value: 'calculator_check_value', check_expected: 'calculator_check_expected',
        check_calculated: 'calculator_check_calculated', check_status: 'calculator_check_status',
        check_match: 'calculator_check_match', check_differs: 'calculator_check_differs',
        measured: 'field_tag_measured', assumed: 'field_tag_assumed'
    };

    const host = document.getElementById(HOST_ID);
    const seed = readJson(SEED_ID);
    const settings = readJson(SETTINGS_ID);
    if (host === null || seed === null || settings === null) {
        return;
    }
    const KW = settings.keywords || {};
    // the phrases of a value that make it a property of a field and not the number of the field (ui_list::CALC_FIELD_PROPERTIES)
    const FIELD_PROPERTIES = [KW.limit_down, KW.limit_up, KW.step, KW.confidence, KW.decimals, KW.result_width];
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
    /* the nearest phrase of the list before the phrase in its follower chain, also over phrases of other lists, null if there is none (calculator::previous_in_list) */
    function previousInList(name, keys) {
        const seen = [name];
        let cur = name;
        let result = null;
        while (cur !== null && result === null) {
            const trp = triplesFrom(cur).filter(function (t) {
                return t.verb === VERB.follow && seen.indexOf(t.to) < 0;
            }).pop();
            cur = trp === undefined ? null : trp.to;
            if (cur !== null && keys.indexOf(cur) >= 0) {
                result = cur;
            }
            seen.push(cur);
        }
        return result;
    }

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
            // a row can follow a row of another kind e.g. a stock after a bond, which keeps the order of the stocks
            if (!asItems && Array.from(next.values()).indexOf(keyOf(x)) < 0) {
                const prev = previousInList(keyOf(x), keys);
                if (prev !== null) {
                    next.set(prev, keyOf(x));
                }
            }
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
        const props = FIELD_PROPERTIES;
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

    /* the number as a number field shows it: a share in percent e.g. 35 for the stored ratio 0.35 (ui_list::field_shown) */
    function fieldShown(num, entry) {
        const result = showsPercent(entry) ? String(parseFloat((Number(num) * 100).toFixed(12))) : String(num);
        return result;
    }

    /* true if a number field shows the number in percent, but not for a ratio unit e.g. share (ratio) (ui_list::shows_percent) */
    function showsPercent(entry) {
        return isPercentEntry(entry) && !entry.words.some(isRatioUnit);
    }

    /* the unit behind a number field (ui_list::field_unit) */
    function fieldUnit(entry) {
        return showsPercent(entry) ? UNIT_PERCENT : unitOf(entry);
    }

    /* the number of the source of a value in the source list of the page e.g. [2] */
    function sourceRef(entry) {
        const pos = entry === null ? -1 : pageSources.indexOf(entry.source);
        const result = pos < 0 ? '' : '[' + (pos + 1) + ']';
        return result;
    }

    /* the numbers of the source of a field value, else of the values it is based on e.g. [5][6] (ui_list::field_source_refs) */
    function fieldSourceRefs(name, entry) {
        const refs = [sourceRef(entry)].concat(basisEntries(name, entry).map(sourceRef));
        const result = refs.filter(function (r, i) {
            return refs.indexOf(r) === i;
        }).join('');
        return result;
    }

    /* the names of the unit, scaling and percent phrases of a number in alphabetic order (sandbox_value::unit_key) */
    function unitKey(entry) {
        const result = entry.words.filter(function (w) {
            return isMeasure(w) || isPercent(w) || hasType(w, PT.scaling) || hasType(w, PT.scaling_hidden);
        }).sort().join(', ');
        return result;
    }

    /* the measured values that an assumed field value is based on: the values of the field phrase with the same unit and a
       source, but no property of the field, ordered by their phrase names (ui_list::calculator_basis_values) */
    function basisEntries(name, entry) {
        let result = [];
        if (entry !== null && !entry.result && !entry.source) {
            const unit = unitKey(entry);
            result = pool.filter(function (e) {
                const noProperty = !FIELD_PROPERTIES.some(function (p) {
                    return e.words.indexOf(p) >= 0;
                });
                return !e.result && e.source && noProperty && e.words.indexOf(name) >= 0 && unitKey(e) === unit;
            }).sort(function (a, b) {
                const keyA = a.words.slice().sort().join(', ');
                const keyB = b.words.slice().sort().join(', ');
                return keyA < keyB ? -1 : (keyA > keyB ? 1 : 0);
            });
        }
        return result;
    }

    /* the sources of the page in the order of their first use: the values of the number fields of
       the inputs and of the listed assumptions, then the operands of the results (ui_list::calculator_source_ids) */
    function sourceNames() {
        let fieldValues = [];
        items(KW.input).concat(items(KW.assumption)).forEach(function (item) {
            if (fieldType(item.to) === KW.selector) {
                fieldValues = fieldValues.concat(options(item.to).map(function (o) {
                    return optionEntry(item.to, o[0]);
                }).filter(hasUnit));
            } else {
                const entry = fieldEntry(item.to);
                fieldValues = fieldValues.concat([entry], basisEntries(item.to, entry));
            }
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
    // one number, one char of the arithmetic or of a condition or a math function between the operands (formula_calc::TOKEN_PATTERN)
    const TOKEN_PATTERN = /\s*(\d+(?:\.\d+)?|[-+*\/()^=&|;]|min|max|if|abs|cos|exp|ln|log|round|sec|sin|sqrt|sum|count|mean)/y;
    // the math functions, the separator of their arguments and the power (the chars::FUNC_* and chars::POW)
    const FUNC_MIN = 'min';
    const FUNC_MAX = 'max';
    const FUNC_IF = 'if';
    const FUNC_ARG_SEPARATOR = ';';
    const POWER = '^';
    // the logarithm like arithmetic::log_number, to the base 10 if no base is given
    const LOG_NUMBER = (nums) => nums.length === 1 ? Math.log10(nums[0]) : (nums.length === 2 && nums[1] > 0 && nums[1] !== 1 ? Math.log(nums[0]) / Math.log(nums[1]) : NaN);
    // the functions of a list like arithmetic::list_number, a plain operand stands for all its values, except a second number of the same fact (words::LIST_FUNCTION_EXCLUDED)
    const LIST_NUMBER = {sum: (nums) => nums.reduce((a, b) => a + b, 0), count: (nums) => nums.length, mean: (nums) => nums.reduce((a, b) => a + b, 0) / nums.length};
    const LIST_ARGUMENT_START = /\b(?:sum|count|mean)\s*\([^()]*$/;
    const LIST_ARGUMENTS_PLAIN = /\b(?:sum|count|mean)\s*\(\s*(?:x|-?\d+(?:\.\d+)?)\s*(?:;\s*(?:x|-?\d+(?:\.\d+)?)\s*)*\)/g;
    const LIST_FUNCTION = /\b(?:sum|count|mean)\s*\(/;
    const LIST_EXCLUDED = ['variance low (system)', 'variance high (system)', 'confidence'];
    // the functions of one number like arithmetic::one_number, so also round away from zero and an angle in radian
    const ONE_NUMBER = {abs: Math.abs, cos: Math.cos, exp: Math.exp, ln: Math.log, log: Math.log10, round: (x) => Math.sign(x) * Math.round(Math.abs(x)), sec: (x) => 1 / Math.cos(x), sin: Math.sin, sqrt: Math.sqrt};
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
        const selected = priorSelect(names, res.context);
        const result = pool.find(function (e) {
            return e !== res && hasAll(e, selected.names) && e.words.every(function (w) {
                return selected.context.indexOf(w) >= 0;
            });
        }) || null;
        return result;
    }

    /* the one phrase of the context in a follower chain for an operand with the name prior, else null (prior_operand::chain_phrase) */
    function priorChainPhrase(names, context) {
        let result = null;
        if (names.length > 1 && names.indexOf(PRIOR_NAME) >= 0) {
            const links = followerLinks();
            const found = context.filter(function (w) {
                return links.some(function (p) {
                    return p.from === w || p.to === w;
                });
            });
            const before = found.length !== 1 ? [] : links.filter(function (p) {
                return p.from === found[0];
            }).map(function (p) {
                return p.to;
            });
            // two different previous phrases make the previous row not clear (prior_operand::UNCLEAR)
            result = found.length === 1 && new Set(before).size < 2 ? found[0] : null;
        }
        return result;
    }

    /* the phrase before a phrase in its follower chain, null for the first phrase */
    function priorOf(name) {
        const trp = followerLinks().find(function (p) {
            return p.from === name;
        });
        return trp === undefined ? null : trp.to;
    }

    /* the triples of the follower chains */
    function followerLinks() {
        return Array.from(phrases.values()).filter(function (p) {
            return p.verb === VERB.follow;
        });
    }

    /* the names and the context that select the number of an operand (prior_operand::select) */
    function priorSelect(names, context) {
        const phr = priorChainPhrase(names, context);
        const before = phr === null ? null : priorOf(phr);
        let result = {names: names, context: context};
        if (before !== null) {
            result = {
                names: names.filter(function (n) {
                    return n !== PRIOR_NAME;
                }).concat([before]),
                context: context.filter(function (w) {
                    return w !== phr;
                }).concat([before])
            };
        }
        return result;
    }

    /* true if the operand reads the previous row of the first row, which is the number 0 (prior_operand::is_first) */
    function priorIsFirst(names, context) {
        const phr = priorChainPhrase(names, context);
        return phr !== null && priorOf(phr) === null;
    }

    /* the stored values with all names of the operand and the context of the result (formula_calc::list_values) */
    function listEntries(names, res) {
        const result = pool.filter(function (e) {
            const isExcluded = e.words.some(function (w) {
                return LIST_EXCLUDED.indexOf(w) >= 0;
            });
            return !e.result && !isExcluded && hasAll(e, [...names, ...res.context]);
        });
        return result;
    }

    /* each list function has only plain operands or numbers as arguments (arithmetic::list_args_are_plain) */
    function listsArePlain(exp) {
        const rest = exp.replace(OPERAND_PATTERN, 'x').replace(LIST_ARGUMENTS_PLAIN, '');
        return !LIST_FUNCTION.test(rest);
    }

    /* the values and results that the results of the page use, each once (formula_calc::operands) */
    function operandValues() {
        const result = [];
        pageResults.forEach(function (res) {
            const exp = calculation(res);
            let found = OPERAND_PATTERN.exec(exp);
            while (found !== null) {
                const names = operandNames(found[0]);
                const entry = operandEntry(names, res);
                const entries = LIST_ARGUMENT_START.test(exp.slice(0, found.index)) ? listEntries(names, res) : (entry === null ? [] : [entry]);
                entries.filter(function (e) {
                    return result.indexOf(e) < 0;
                }).forEach(function (e) {
                    result.push(e);
                });
                found = OPERAND_PATTERN.exec(exp);
            }
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
            const text = calculation(res).replace(OPERAND_PATTERN, function (term, start, all) {
                if (LIST_ARGUMENT_START.test(all.slice(0, start))) {
                    const nums = listEntries(operandNames(term), res).map(function (e) {
                        return Number(e.number).toFixed(12);
                    });
                    missing = missing || nums.length === 0;
                    return nums.length === 0 ? '0' : nums.join(FUNC_ARG_SEPARATOR);
                }
                const names = operandNames(term);
                const entry = operandEntry(names, res);
                // the first row has no previous row and the number 0
                const first = entry === null && priorIsFirst(names, res.context);
                const num = first ? 0 : (entry === null ? null : operandNumber(entry, done, open));
                missing = missing || num === null;
                return num === null ? '0' : '(' + num.toFixed(12) + ')';
            });
            missing = missing || !listsArePlain(calculation(res));
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

    /* the arithmetic of shared/calc/arithmetic.php: a sum of products of powers of signed numbers, brackets or a math function;
       a number that cannot be calculated is NaN, which an if function may leave out and which else makes the result no number */
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
        let result = powerOf(tokens, state);
        while (tokens[state.pos] === '*' || tokens[state.pos] === '/') {
            const op = tokens[state.pos];
            state.pos++;
            const next = powerOf(tokens, state);
            result = op === '*' ? result * next : finiteNumber(result / next);
        }
        return result;
    }

    function listArguments(tokens, state) {
        const result = [sum(tokens, state)];
        while (state.ok && tokens[state.pos] === FUNC_ARG_SEPARATOR) {
            state.pos++;
            result.push(sum(tokens, state));
        }
        expectToken(tokens, state, ')');
        return result;
    }

    /* from right to left and a number keeps its sign, the same as arithmetic::power */
    function powerOf(tokens, state) {
        let result = signed(tokens, state);
        if (tokens[state.pos] === POWER) {
            state.pos++;
            result = finiteNumber(Math.pow(result, powerOf(tokens, state)));
        }
        return result;
    }

    /* NaN instead of an infinite number, because a later division would else make it a wrong zero */
    function finiteNumber(num) {
        return Number.isFinite(num) ? num : NaN;
    }

    /* two sums compared by = or a sum that is true if it is not zero; null for a part without a number */
    function compareCondition(tokens, state) {
        const left = sum(tokens, state);
        let result = left !== 0;
        let right = 0;
        if (tokens[state.pos] === '=') {
            state.pos++;
            right = sum(tokens, state);
            result = left === right;
        }
        return Number.isNaN(left) || Number.isNaN(right) ? null : result;
    }

    function andCondition(tokens, state) {
        let result = compareCondition(tokens, state);
        while (tokens[state.pos] === '&') {
            state.pos++;
            const right = compareCondition(tokens, state);
            result = result === null || right === null ? null : result && right;
        }
        return result;
    }

    function orCondition(tokens, state) {
        let result = andCondition(tokens, state);
        while (tokens[state.pos] === '|') {
            state.pos++;
            const right = andCondition(tokens, state);
            result = result === null || right === null ? null : result || right;
        }
        return result;
    }

    function expectToken(tokens, state, token) {
        state.ok = state.ok && tokens[state.pos] === token;
        state.pos++;
    }

    /* the then or the else part, only the chosen part counts like in arithmetic::if_function */
    function ifFunction(tokens, state) {
        const isTrue = orCondition(tokens, state);
        expectToken(tokens, state, FUNC_ARG_SEPARATOR);
        const thenNum = sum(tokens, state);
        expectToken(tokens, state, FUNC_ARG_SEPARATOR);
        const elseNum = sum(tokens, state);
        expectToken(tokens, state, ')');
        return isTrue === null ? NaN : (isTrue ? thenNum : elseNum);
    }

    /* the number that pick keeps of the sums separated by the argument separator up to the closing bracket */
    function pickArgument(tokens, state, pick) {
        let result = sum(tokens, state);
        while (tokens[state.pos] === FUNC_ARG_SEPARATOR) {
            state.pos++;
            result = pick(result, sum(tokens, state));
        }
        state.ok = state.ok && tokens[state.pos] === ')';
        state.pos++;
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
        } else if (token === FUNC_MIN || token === FUNC_MAX) {
            state.ok = state.ok && tokens[state.pos + 1] === '(';
            state.pos += 2;
            result = pickArgument(tokens, state, token === FUNC_MIN ? Math.min : Math.max);
        } else if (token === FUNC_IF) {
            expectToken(tokens, state, FUNC_IF);
            expectToken(tokens, state, '(');
            result = ifFunction(tokens, state);
        } else if (token === 'log') {
            state.pos++;
            expectToken(tokens, state, '(');
            result = finiteNumber(LOG_NUMBER(listArguments(tokens, state)));
        } else if (token in ONE_NUMBER) {
            state.pos++;
            expectToken(tokens, state, '(');
            const arg = sum(tokens, state);
            expectToken(tokens, state, ')');
            result = finiteNumber(ONE_NUMBER[token](arg));
        } else if (token in LIST_NUMBER) {
            state.pos++;
            expectToken(tokens, state, '(');
            result = finiteNumber(LIST_NUMBER[token](listArguments(tokens, state)));
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
    function nextUnit(num, unit, digits) {
        const tooBig = magnitude(num) > digits;
        const tooSmall = num < 1;
        let result = null;
        CONVERSIONS.forEach(function (cnv) {
            const isUnit = cnv[0] === unit || cnv[0] === owners.get(unit);
            const shown = num * cnv[3];
            const toLarger = tooBig && cnv[3] < 1 && shown >= 1;
            const toSmaller = tooSmall && cnv[3] > 1 && magnitude(shown) <= digits;
            const nearer = result === null || Math.abs(Math.log(cnv[3])) < Math.abs(Math.log(result[3]));
            if (isUnit && (toLarger || toSmaller) && nearer) {
                result = cnv;
            }
        });
        return result;
    }

    /* the unit, its text, the factor and the decimals of a group of numbers of one unit with the digits of a precision tier (number_format::group) */
    function groupFormat(numbers, unit, digits) {
        const max = numbers.reduce(function (m, n) {
            return Math.max(m, Math.abs(Number(n)));
        }, 0);
        const result = {unit: unit, text: unit === null ? null : (symbols.get(unit) || unit), factor: 1, decimals: 0};
        if (unit !== null && max > 0) {
            let cnv = nextUnit(max, unit, digits);
            while (cnv !== null) {
                result.unit = cnv[1];
                result.text = cnv[2];
                result.factor = result.factor * cnv[3];
                cnv = nextUnit(max * result.factor, result.unit, digits);
            }
        }
        if (max > 0) {
            result.decimals = Math.max(0, digits - magnitude(max * result.factor));
        }
        return result;
    }

    /* the number rounded half away from zero without the float noise of a calculation (number_format::text) */
    function roundedNumber(num, decimals) {
        const factor = Math.pow(10, decimals);
        const clean = Number(num.toFixed(NOISE_DECIMALS));
        return Math.sign(clean) * Math.round(Math.abs(clean) * factor) / factor;
    }

    function formatNumber(num, decimals) {
        const fixed = roundedNumber(Math.abs(Number(num)), decimals).toFixed(decimals).split('.');
        const int = thousands(fixed[0]);
        // a number that rounds to zero has no sign
        const sign = Number(num) < 0 && Number(fixed.join('.')) !== 0 ? '-' : '';
        const result = sign + int + (fixed.length > 1 ? (FMT.dec_point || '.') + fixed[1] : '');
        return result;
    }

    function thousands(int) {
        return int.replace(/\B(?=(\d{3})+(?!\d))/g, FMT.thousand_sep || "'");
    }

    /* per unit the format of the numbers of one column, so that the numbers of a unit can be compared at a glance (ui_list::calculator_formats) */
    function columnFormats(entries, withUnit, digits) {
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
            result.set(unit, groupFormat(nums, withUnit ? measureOf(group[0]) : null, digits));
        });
        return result;
    }

    /* a share times 100 with the percent decimals and its symbol e.g. "49 %" (sandbox_value::val_formatted) */
    function percentText(entry, decimals) {
        const word = entry.words.find(isPercent);
        const percent = Number(entry.number) * 100;
        // a recalculated share has all its digits, and from 10 % on the whole percent is what a reader compares
        const scale = Math.abs(percent) >= 10 ? 1 : Math.pow(10, FMT.percent_decimals);
        const num = decimals === null ? String(Math.round(percent * scale) / scale) : formatNumber(percent, decimals);
        const sym = isRatioUnit(word) ? '' : (symbols.get(word) || '');
        const result = num + (sym === '' ? '' : FMT.unit_sep + sym);
        return result;
    }

    /* the number in the format of its group with the unit text, which a converted group takes from the conversion */
    /* decimals: what the row sets, null or undefined for the decimals of the column (ui_list::calculator_cell_text) */
    function numberText(entry, formats, withUnit, decimals) {
        const rowDecimals = decimals === undefined ? null : decimals;
        let result = '';
        if (isPercentEntry(entry)) {
            result = percentText(entry, rowDecimals);
        } else {
            const fmt = formats.get(unitOf(entry));
            const unit = withUnit ? (fmt.text === null ? unitOf(entry) : fmt.text) : '';
            result = formatNumber(Number(entry.number) * fmt.factor, rowDecimals === null ? fmt.decimals : rowDecimals) + (unit === '' ? '' : FMT.unit_sep + unit);
        }
        return result;
    }

    /* plus: true to show the sign with the plus sign, the style of the sign only for a number that does not round to zero; sub: the sub text behind the number; colour: the colour style of the column of a detail table (ui_list::calculator_cell) */
    function cellHtml(entry, formats, signed, withUnit, decimals, plus, sub, colour) {
        let text = '';
        if (entry !== null) {
            text = esc(numberText(entry, formats, withUnit, decimals));
            if (signed) {
                text = block('span', text, Number(entry.number) < 0 ? FMT.negative : FMT.positive);
            } else if (plus) {
                const fmt = formats.get(unitOf(entry));
                const shown = roundedNumber(Number(entry.number) * fmt.factor, decimals === null || decimals === undefined ? fmt.decimals : decimals);
                text = block('span', (shown > 0 ? '+' : '') + text, shown > 0 ? FMT.positive : (shown < 0 ? FMT.negative : ''));
            }
            if (sub) {
                text += block('span', esc(sub), STYLE.sub);
            }
        }
        return block('td', text, (STYLE.number + ' ' + (colour || '')).trim());
    }

    /* the decimals of the value with exactly these names and the keyword calculator decimals, null if there is none (ui_list::decimals_of) */
    function decimalsOf(names) {
        const wanted = names.concat([KW.decimals]);
        const entry = pool.find(function (e) {
            return !e.result && e.words.length === wanted.length && hasAll(e, wanted);
        });
        return entry === undefined ? null : Number(entry.number);
    }

    /* the decimals that the column phrase or its filter set for a kind of the row, else for all rows (ui_list::cell_decimals) */
    function cellDecimals(row, col) {
        const colNames = [col.to, filterOf(col)].filter(function (n) {
            return n;
        });
        let result = null;
        triplesFrom(row).filter(function (t) {
            return t.verb === VERB.is;
        }).forEach(function (t) {
            colNames.forEach(function (n) {
                result = result === null ? decimalsOf([n, t.to]) : result;
            });
        });
        colNames.forEach(function (n) {
            result = result === null ? decimalsOf([n]) : result;
        });
        return result;
    }

    /* the unit for the sub text of a column with the flag: the name of the measure and not its symbol, else the one phrase besides the row and the column (ui_list::calculator_column_unit) */
    function columnUnit(entry, row, col) {
        let result = '';
        if (col.hasUnit && entry !== null) {
            result = entry.words.find(isMeasure) || '';
            const others = entry.words.filter(function (w) {
                return w !== row && w !== col.to && w !== filterOf(col);
            });
            if (result === '' && others.length === 1) {
                result = others[0];
            }
        }
        return result;
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

    /* the number of the source at the marker of the hint, else behind it (ui_list::calculator_hint) */
    function hintHtml(text, ref) {
        const refTxt = ref === '' ? '' : ' ' + ref;
        const mark = ' ' + HINT_SOURCE;
        const shown = esc(text);
        const txt = (shown.includes(mark) ? shown.replace(mark, refTxt) : shown + refTxt).trim();
        const result = txt === '' ? '' : block('span', txt, STYLE.hint);
        return result;
    }

    /* a number of a label or a sub text with the digits of the rough tier and at most the given decimals if it is whole (ui_list::calculator_rough_text) */
    function roughText(entry, wholeDecimals) {
        const formats = columnFormats([entry], true, FMT.rough_digits);
        const fmt = formats.get(unitOf(entry));
        if (Number.isInteger(Number(entry.number) * fmt.factor)) {
            fmt.decimals = Math.min(fmt.decimals, wholeDecimals);
        }
        return numberText(entry, formats, true);
    }

    /* the hint of a row with the number of each phrase in curly brackets or an empty text if a number is missing (ui_list::calculator_hint_numbers) */
    function hintNumbers(text) {
        let missing = false;
        const result = text.replace(HINT_NUMBER_PATTERN, function (found, name, plain) {
            const entry = fieldEntry(name) || cellEntry([page.name, name]);
            missing = missing || entry === null;
            let num = '';
            if (entry !== null) {
                num = plain ? fieldNumber(entry).replace(/^\d+/, thousands) : roughText(entry, 1);
            }
            return num;
        });
        return missing ? '' : result;
    }

    /* measured for a value with a source, else assumed with its confidence e.g. "assumed 0.6" (ui_list::assumption_tag) */
    function tagText(entry) {
        let result = entry.source ? TEXTS[TXT.measured] : TEXTS[TXT.assumed];
        // without the unit of the value, because a confidence has its own unit e.g. percent (ui_list::names_without_unit)
        const names = entry.words.filter(function (w) {
            return !isMeasure(w) && !isRatioUnit(w) && !isPercent(w);
        });
        const conf = entry.source ? null : cellEntry(names.concat([KW.confidence]));
        if (conf !== null) {
            result += ' ' + conf.number;
        }
        return result;
    }

    /* the hidden vars that the save needs for a number field, with the stored number as the shown one (ui_list::calculator_save_vars) */
    function saveVars(key, entry, lbl, unit) {
        let result = hidden(GROUP_VAR + key, entry.id) + hidden(PRE_VAR + NUMBER_VAR + key, unit === UNIT_PERCENT ? fieldShown(entry.number, entry) : entry.number) + hidden(NAME_VAR + key, lbl);
        if (unit !== '') {
            result += hidden(UNIT_VAR + key, unit);
        }
        return result;
    }

    /* instance and its entry only for a repeated field, which gets its own number and name behind the label */
    function numberField(item, name, assumption, instance, instanceEntry) {
        const entry = instance === undefined ? fieldEntry(name) : instanceEntry;
        const key = idOf(instance === undefined ? name : instance);
        const field = NUMBER_VAR + key;
        const lbl = instance === undefined ? label(item) : label(item) + ' ' + phraseLabel(instance);
        let text = esc(lbl);
        let hintText = hint(item);
        if (assumption && entry !== null) {
            text += ' ' + block('span', esc(tagText(entry)), entry.source ? STYLE.tagMeasured : STYLE.tagAssumed);
            hintText = hintText || entry.description;
        }
        const limits = [['min', KW.limit_down], ['max', KW.limit_up], ['step', KW.step]].map(function (l) {
            const num = fieldProperty(name, l[1]);
            const limit = num === null || entry === null ? num : fieldShown(num, entry);
            return num === null ? '' : ' ' + l[0] + '="' + esc(limit) + '"';
        }).join('');
        const shown = entry === null ? '' : fieldShown(entry.number, entry);
        let input = '<input type="number" name="' + field + '" id="' + field + '" value="' + esc(shown) + '"' + limits + ' form="' + esc(FORM) + '">';
        const unit = entry === null ? '' : fieldUnit(entry);
        if (entry !== null) {
            input += saveVars(key, entry, lbl, unit);
            inputEntries.set(field, entry);
        }
        if (unit !== '') {
            input += block('span', esc(unit), STYLE.unit);
        }
        input += removeButton(name, instance);
        const result = block('div', '<label for="' + field + '">' + text + '</label>' + block('div', input, STYLE.row)
            + hintHtml(hintText, fieldSourceRefs(name, entry)), STYLE.field);
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
        const unit = fieldUnit(entry);
        const text = esc(lbl) + ' ' + block('span', esc(tagText(entry)), STYLE.tag);
        let input = '<input type="number" name="' + field + '" id="' + field + '" value="' + esc(fieldShown(entry.number, entry)) + '" form="' + esc(FORM) + '">'
            + saveVars(entry.id, entry, lbl, unit);
        if (unit !== '') {
            input += block('span', esc(unit), STYLE.unit);
        }
        input += hintHtml(entry.source || '', '');
        inputEntries.set(field, entry);
        return block('div', '<label for="' + field + '">' + text + '</label>' + input, STYLE.field);
    }

    function optionEntry(name, option) {
        return cellEntry([name, option]) || fieldEntry(option);
    }

    /* true for a number with a unit, which a selector option names with its source (ui_list::calculator_option_text) */
    function hasUnit(entry) {
        return entry !== null && entry.words.some(isMeasure);
    }

    /* the label; for a number with a unit also the number and its source e.g. European residual mix — 523 g/kWh [3] */
    function optionText(text, entry) {
        let result = text;
        if (hasUnit(entry)) {
            const num = numberText(entry, columnFormats([entry], true, FMT.rough_digits), true);
            result = (text + OPTION_SEP + num + ' ' + sourceRef(entry)).trim();
        }
        return result;
    }

    function selectorField(item, name) {
        const field = NUMBER_VAR + idOf(name);
        const domId = field + SELECT_ID_SUFFIX;
        const opts = options(name);
        const usedOpt = used(opts.map(function (o) {
            return o[0];
        }));
        const html = opts.map(function (o) {
            const entry = optionEntry(name, o[0]);
            const sel = o[0] === usedOpt ? ' selected' : '';
            // the selector sets the number of the option that the page uses, which the results read
            if (entry !== null && o[0] === usedOpt) {
                inputEntries.set(field, entry);
            }
            // an option without a number selects only the phrase e.g. the walk order bank statement (ui_list::calculator_selector)
            return entry === null
                ? '<option value="' + OPTION_PHRASE + esc(idOf(o[0])) + '"' + sel + '>' + esc(o[1]) + '</option>'
                : '<option value="' + esc(fieldNumber(entry)) + '"' + sel + '>' + esc(optionText(o[1], entry)) + '</option>';
        }).join('');
        // a used option without a number: the first option with a number takes the number of the selected option (ui_list::calculator_field_value)
        const first = opts.map(function (o) {
            return optionEntry(name, o[0]);
        }).find(function (e) {
            return e !== null;
        });
        if (usedOpt && !inputEntries.has(field) && first !== undefined) {
            inputEntries.set(field, first);
        }
        const select = '<select class="' + STYLE.select + '" name="' + field + '" form="' + esc(FORM) + '" id="' + domId + '">' + html + '</select>';
        const result = block('div', '<label for="' + domId + '">' + esc(label(item)) + '</label>' + select
            + hintHtml(hint(item), ''), STYLE.field);
        return result;
    }

    /* a number that a user cannot change e.g. the target weight of the parent (ui_list::calculator_read_only) */
    function readOnlyField(item, name) {
        const entry = fieldEntry(name);
        const number = entry === null ? '' : roughText(entry, 1);
        const result = block('div', '<label>' + esc(label(item)) + '</label>' + block('div', esc(number), STYLE.readOnly)
            + hintHtml(hint(item), fieldSourceRefs(name, entry)), STYLE.field);
        return result;
    }

    /* checked is the number 1 and unchecked 0, which the hidden field sends, because a browser sends no unchecked checkbox (ui_list::calculator_checkbox) */
    function checkboxField(item, name) {
        const entry = fieldEntry(name);
        const key = idOf(name);
        const field = NUMBER_VAR + key;
        const lbl = label(item);
        const checked = entry !== null && Number(entry.number) === 1 ? ' checked' : '';
        const input = hidden(field, 0) + '<input type="checkbox" name="' + field + '" id="' + field + '" value="1" form="' + esc(FORM) + '"' + checked + '>';
        const vars = entry === null ? '' : saveVars(key, entry, lbl, '');
        if (entry !== null) {
            inputEntries.set(field, entry);
        }
        const result = '<label class="' + STYLE.check + '">' + input + '<span>' + esc(lbl) + hintHtml(hint(item), '') + '</span></label>' + vars;
        return result;
    }

    /* the button to remove an instance, without a function so far (ui_list::calculator_remove_button) */
    function removeButton(name, instance) {
        let result = '';
        if (instance !== undefined && kinds(name).length > MIN_INSTANCES) {
            const title = esc(TEXTS[TXT.remove] + ' ' + phraseLabel(instance));
            result = '<button type="button" class="' + STYLE.removeButton + '" title="' + title + '" aria-label="' + title + '"><i class="' + REMOVE_ICON + '"></i></button>';
        }
        return result;
    }

    /* a field per instance of a repeated input and the button to add one, which has no function yet, because a new instance needs its own phrase (ui_list::calculator_repeat) */
    function repeatFields(item, name) {
        const has = subItems(name, VERB.has)[0];
        const valuePhrase = has === undefined ? name : has.to;
        const add = ownText(item.name, KW.repeat);
        const result = kinds(name).map(function (instance) {
            return numberField(item, name, false, instance, optionEntry(valuePhrase, instance));
        }).join('');
        return result + (add ? '<button type="button" class="' + STYLE.addButton + '">' + esc(add) + '</button>' : '');
    }

    function fields(list, assumption) {
        const result = list.map(function (item) {
            let html = '';
            if (hasFlag(item, KW.read_only)) {
                html = readOnlyField(item, item.to);
            } else if (hasFlag(item, KW.repeat)) {
                html = repeatFields(item, item.to);
            } else if (triplesFrom(item.to).some(function (t) {
                return t.to === KW.checkbox;
            })) {
                html = checkboxField(item, item.to);
            } else if (fieldType(item.to) === KW.selector) {
                html = selectorField(item, item.to);
            } else {
                html = numberField(item, item.to, assumption);
            }
            return html;
        }).join('');
        return result;
    }

    /* the warnings whose result of the page phrase is above zero, sorted by their text (ui_list::calculator_warnings) */
    function warningTexts() {
        const texts = [];
        phrases.forEach(function (p) {
            if (p.to === KW.warning && p.from !== undefined) {
                const entry = cellEntry([page.name, p.from]);
                if (entry !== null && Number(Number(entry.number).toFixed(WARNING_DECIMALS)) > 0) {
                    texts.push(p.description);
                }
            }
        });
        const result = Array.from(new Set(texts)).sort().map(function (text) {
            return block('div', esc(text), STYLE.warning);
        }).join('');
        return result;
    }

    function inputPanel() {
        const inputs = items(KW.input);
        const assumptions = items(KW.assumption);
        let result = '';
        if (inputs.length > 0 || assumptions.length > 0) {
            const heading = ownText((group(KW.input) || {}).name, KW.label) || TEXTS[TXT.inputs];
            result = block('div', block('h2', esc(heading)) + fields(inputs, false)
                + block('div', warningTexts(), STYLE.warnings) + caseField() + (settings.save || '') + buttons(group(KW.input)), STYLE.inputs);
            const listed = assumptions.map(function (item) {
                return item.to;
            });
            const assumed = fields(assumptions, true) + operandFields(listed) + buttons(group(KW.assumption));
            if (assumed !== '') {
                const summary = ownText((group(KW.assumption) || {}).name, KW.label) || TEXTS[TXT.assumptions];
                result += block('details', block('summary', esc(summary)) + assumed);
            }
        }
        return block('aside', result, STYLE.panel);
    }

    /* the name of the case that the page shows, empty for the default case e.g. Miller family of the triple Miller family portfolio split */
    function caseName() {
        return page.verb === VERB.of && page.from === base ? page.to : '';
    }

    /* the field for the name of a case, only for a page that is built from another phrase, of which a case is another kind */
    function caseField() {
        const input = '<input type="text" id="' + CASE_FIELD_ID + '" value="' + esc(caseName()) + '">';
        return base === null ? '' : block('div', '<label for="' + CASE_FIELD_ID + '">' + esc(TEXTS[TXT.case_name]) + '</label>' + input, STYLE.field);
    }

    /* the updatePut message of a new case without a database id: the word of the name, the triple of the case, the triples and the values of the page with the triple of the case instead of the page phrase and with the entered numbers; the results are calculated by the backend */
    function caseMessage(name) {
        const caseTriple = name + ' ' + base;
        const pageTriples = (seed.triples || []).filter(function (t) {
            return t.from === page.name;
        }).map(function (t) {
            return {name: t.name.replace(page.name, caseTriple), from: caseTriple, verb: t.verb, to: t.to};
        });
        const values = pool.filter(function (e) {
            return !e.result && e.words.indexOf(page.name) >= 0;
        }).map(function (e) {
            const json = {
                words: e.words.map(function (w) {
                    return w === page.name ? caseTriple : w;
                }),
                number: Number(e.number)
            };
            // a source exists already, and only its id makes the new value keep the source
            if (e.source && sources.has(e.source)) {
                json.source_id = sources.get(e.source).id;
            }
            return json;
        });
        return {
            pod: seed.pod,
            type: 'updatePut',
            user_id: seed.user_id,
            user: seed.user,
            version: seed.version,
            timestamp: new Date().toISOString(),
            body: {
                words: [{name: name}],
                triples: [{name: caseTriple, from: base, verb: VERB.of, to: name}].concat(pageTriples),
                values: values
            }
        };
    }

    /* show why a case has not been saved below the warnings of the inputs */
    function caseProblem(text) {
        host.querySelector('div.' + STYLE.warnings).insertAdjacentHTML('beforeend', block('div', esc(text), STYLE.warning));
    }

    /* a new case name first creates the case by the updatePut api and then opens its page, which loads the new database ids, so that the next save updates the values by the normal form */
    function saveCase(event) {
        const name = document.getElementById(CASE_FIELD_ID)?.value.trim() || '';
        if (event.target.id === FORM && name !== '' && name !== caseName()) {
            event.preventDefault();
            const token = event.target.querySelector('input[name="token"]').value;
            fetch(CASE_ROUTE, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': token},
                body: JSON.stringify(caseMessage(name))
            }).then(function (response) {
                return response.json();
            }).then(function (json) {
                const id = json.body?.triples?.[0]?.id;
                if (id) {
                    const url = new URL(location.href);
                    // the phrase id of a triple is negative
                    url.searchParams.set(ID_VAR, String(-id));
                    location.assign(url.toString());
                } else {
                    caseProblem(json.message || TEXTS[TXT.case_failed]);
                }
            }).catch(function (error) {
                caseProblem(error.message);
            });
        }
    }

    /* the own hint of a table item below the table (ui_list::calculator_table_hint) */
    function tableHint(name) {
        const hint = ownText(name, KW.hint);
        return hint ? block('p', esc(hint), STYLE.hint) : '';
    }

    function table(head, rows) {
        const result = block('div', block('table', block('thead', block('tr', head)) + block('tbody', rows)), STYLE.scroll);
        return result;
    }

    /* the sub text of a row: its hint, else its side results with their number */
    function rowSub(row) {
        let sub = hintNumbers(hint(row));
        if (sub === '') {
            sub = sideResults(row).map(function (side) {
                // without the filter of the row, because a side result is a number of its own (ui_list::calculation_side_context)
                const entry = cellEntry([page.name, chosen(row.to), side]);
                return entry === null ? '' : phraseLabel(side) + ' ' + numberText(entry, columnFormats([entry], true, FMT.rough_digits), true);
            }).filter(function (s) {
                return s !== '';
            }).join(', ');
        }
        const result = sub === '' ? '' : block('span', esc(sub), STYLE.sub);
        return result;
    }

    /* the result of the page whose formula has the phrase alone left of the = (formula_calc::calculates_phrase);
       a result that names the filter of the row is the number of the cells e.g. the battery effect, and with several
       results e.g. one per month a label shows no number, because it never picks one (ui_list::calculator_phrase_result) */
    function phraseResult(name, filter) {
        const found = pool.filter(function (e) {
            const frm = formulas.get(e.formula);
            const left = frm === undefined ? '' : String(frm.expression || '').split(CHAR_CALC)[0];
            const isCell = e.words.indexOf(filter) >= 0;
            return !isCell && e.words.indexOf(page.name) >= 0 && left.replaceAll('"', '').trim() === name;
        });
        return found.length === 1 ? found[0] : null;
    }

    /* the number of the row phrase after the label of a row with a filter e.g. " (128 g/kWh)" with the digits of the
       rough tier, because the number in a label is only an indication (ui_list::calculator_row_number) */
    function rowNumber(row) {
        const phr = chosen(row.to);
        const flt = filterOf(row);
        const entry = flt === null ? null : fieldEntry(phr) || phraseResult(phr, flt);
        const note = ownText(row.name, KW.number_note);
        const txt = entry === null ? '' : roughText(entry, 0);
        const result = entry === null ? '' : ' (' + txt + (note === null ? '' : ' ' + note) + ')';
        return result;
    }

    /* the columns of a table with their text, where the column of a repeated input is shown once per instance (calculator::columns) */
    function columns(list) {
        const result = [];
        list.forEach(function (c) {
            const repeated = isRepeated(c.to);
            const hasColour = hasFlag(c, KW.colour);
            const isPhrase = hasFlag(c, KW.phrase_column);
            (repeated ? kinds(c.to) : [c.to]).forEach(function (to, pos) {
                // a column can name an instance shorter e.g. First for the first child
                const short = repeated ? subItems(c.name, VERB.has).find(function (sub) {
                    return sub.to === to;
                }) : undefined;
                const text = repeated ? (short === undefined ? capital(phraseLabel(to)) : label(short)) : label(c);
                result.push({name: c.name, to: to, text: text, colour: hasColour ? columnColour(repeated, pos) : '', repeated: repeated, isPhrase: isPhrase, plus: hasFlag(c, KW.signed), hasUnit: hasFlag(c, KW.column_unit)});
            });
        });
        return result;
    }

    /* the colour style of a column: the first colour once, then the others cycling (calculator::column_colour) */
    function isRepeated(name) {
        return items(KW.input).some(function (i) {
            return i.to === name && hasFlag(i, KW.repeat);
        });
    }

    function columnColour(repeated, pos) {
        const cycle = STYLE.colours.length - 1;
        return STYLE.colours[repeated ? 1 + pos % cycle : 0];
    }

    /* the unit if all numbers except the shares have one unit, else an empty string (ui_list::calculation_table_unit) */
    function tableUnit(rows) {
        const units = new Set();
        rows.forEach(function (r) {
            r.cells.filter(function (e) {
                return e !== null && !isPercentEntry(e);
            }).forEach(function (e) {
                units.add(unitOf(e));
            });
        });
        return units.size === 1 ? Array.from(units)[0] : '';
    }

    function resultTable() {
        const cols = columns(items(KW.column));
        const grps = items(KW.rowgroup);
        let result = '';
        if (cols.length > 0 || grps.length > 0) {
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
            // one unit of all numbers is named by the first header instead of each cell
            const unit = tableUnit(rows);
            const withUnit = unit === '';
            const head = block('th', esc(withUnit ? TEXTS[TXT.result] : unit)) + cols.map(function (c) {
                return block('th', esc(c.text), (STYLE.number + ' ' + c.colour).trim());
            }).join('');
            const formats = cols.map(function (c, i) {
                return columnFormats(rows.map(function (r) {
                    return r.cells[i];
                }), withUnit, FMT.digits);
            });
            let body = '';
            grps.forEach(function (g) {
                body += block('tr', '<td colspan="' + (cols.length + 1) + '">' + esc(label(g)) + '</td>', STYLE.section);
                const groupSigned = hasFlag(g, KW.signed);
                rows.filter(function (r) {
                    return r.grp === g;
                }).forEach(function (r) {
                    // a flagged row shows the plus sign like a flagged column, a flagged group only the style of the sign
                    const plus = hasFlag(r.row, KW.signed);
                    const signed = groupSigned && !plus;
                    const decimals = decimalsOf([r.row.to]);
                    const cells = r.cells.map(function (e, i) {
                        return cellHtml(e, formats[i], signed, withUnit, decimals, plus);
                    }).join('');
                    const first = esc(label(r.row, g) + rowNumber(r.row)) + rowSub(r.row);
                    body += block('tr', block('td', first) + cells, hasFlag(r.row, KW.highlight) ? STYLE.key : '');
                });
            });
            result = table(head, body);
        }
        return result;
    }

    /* the one stored value with these names that does not name the page e.g. the Swiss consumption mix of a month,
       never picked from several values (ui_list::calculator_general_value) */
    function generalEntry(names, skip, withResults) {
        const wanted = names.filter(function (n) {
            return n;
        });
        const all = pool.filter(function (e) {
            return hasAll(e, wanted) && e.words.indexOf(EXPECTED_WORD) < 0 && !(skip || []).some(function (s) {
                return e.words.indexOf(s) >= 0;
            });
        });
        const found = all.filter(function (e) {
            return !e.result;
        });
        const results = all.filter(function (e) {
            return e.result;
        });
        let result = null;
        if (found.length === 1) {
            result = found[0];
        } else if (withResults === true && results.length === 1) {
            result = results[0];
        }
        return result;
    }

    /* the phrases at the end of the is a and is part of levels below the phrase e.g. LISP is a stock is a holding (calculator::row_phrases) */
    function rowPhrases(name, seen) {
        const below = [];
        phrases.forEach(function (p) {
            if ((p.verb === VERB.is || p.verb === VERB.part) && p.to === name && seen.indexOf(p.from) < 0) {
                below.push(p.from);
            }
        });
        let result = [];
        sorted(below, false).forEach(function (kid) {
            const leaves = rowPhrases(kid, seen.concat([name]));
            result = result.concat(leaves.length === 0 ? [kid] : leaves);
        });
        return result;
    }

    /* the row phrases by the biggest number of the phrase that the table is ranked by, then by name (ui_list::ranked_rows) */
    function rankedRows(tbl) {
        const result = rowPhrases(tbl.to, []);
        const rank = subItems(tbl.name, VERB.rank)[0];
        if (rank !== undefined) {
            // the one stored value of the row, else its one result; a row without one number is the last row (ui_list::rank_number)
            const numberOf = function (row) {
                const results = pool.filter(function (e) {
                    return e.result && hasAll(e, [row, rank.to]);
                });
                const entry = generalEntry([row, rank.to]) || (results.length === 1 ? results[0] : null);
                return entry === null ? -Infinity : Number(entry.number);
            };
            // sort keeps the order of a tie, which is the order of the follower chain
            result.sort(function (a, b) {
                return numberOf(b) - numberOf(a) || 0;
            });
        }
        return result;
    }

    /* the phrase of a stored number with a source of the row that is a column phrase e.g. EUR (ui_list::calculator_phrase_cells) */
    function phraseCell(row, col) {
        const found = [];
        const names = kinds(col.to);
        pool.filter(function (e) {
            return !e.result && e.source && e.words.indexOf(row) >= 0;
        }).forEach(function (e) {
            e.words.filter(function (w) {
                return names.indexOf(w) >= 0;
            }).forEach(function (w) {
                found.push(w);
            });
        });
        found.sort();
        return found.length === 0 ? '' : found[0];
    }

    /* the number of a detail cell: also of the kinds of the column phrase, and without the numbers of the other single columns (ui_list::calculator_detail_values) */
    function detailCell(rowNames, col, cols, withResults) {
        const skip = cols.filter(function (c) {
            return !c.repeated && c.to !== col.to;
        }).map(function (c) {
            return c.to;
        });
        let result = null;
        (col.isPhrase ? [] : [col.to].concat(col.repeated ? [] : kinds(col.to))).forEach(function (phr) {
            const cellNames = [phr, filterOf(col)];
            result = result || cellEntry(rowNames.concat(cellNames)) || generalEntry(rowNames.slice(1).concat(cellNames), skip, withResults);
        });
        return result;
    }

    /* the buttons of a list or a detail table at their place: the report is a link to the pdf and the others have no function so far (ui_list::calculator_buttons) */
    function buttons(item) {
        let labels = [];
        let report = null;
        let copy = [];
        if (item !== null && item !== undefined) {
            labels = [KW.file_import, KW.download].map(function (keyword) {
                return buttonLabel(item, keyword);
            });
            report = buttonLabel(item, KW.report);
            if (hasFlag(item, KW.copy)) {
                copy = [TEXTS[TXT.copy_json], TEXTS[TXT.copy_csv]];
            }
        }
        const link = report ? '<a href="' + esc(settings.report) + '" class="' + STYLE.button + '">' + esc(report) + '</a>' : '';
        return plainButtons(labels) + link + plainButtons(copy);
    }

    /* the label of the triple from the item to the button keyword, null if the item has no such button (calculator::button_label) */
    function buttonLabel(item, keyword) {
        const prop = property(item.name, keyword);
        return prop === null ? null : ownText(prop.name, KW.label);
    }

    function plainButtons(labels) {
        return labels.filter(function (text) {
            return text;
        }).map(function (text) {
            return '<button type="button" class="' + STYLE.button + '">' + esc(text) + '</button>';
        }).join('');
    }

    /* true if the numbers differ only by the float noise of a calculation (ui_list::calculator_check_same) */
    function checkSame(got, expected) {
        return Math.abs(got - expected) <= CHECK_TOLERANCE * Math.max(1, Math.abs(got), Math.abs(expected));
    }

    /* the section with a row per value with the word expected, a missing number differs (ui_list::calculator_check) */
    function checkTable() {
        const grp = group(KW.check);
        const rows = grp === null ? [] : pool.filter(function (e) {
            return !e.result && e.words.indexOf(EXPECTED_WORD) >= 0;
        }).map(function (e) {
            return {label: e.description || e.words.join(', '), exp: e};
        });
        rows.sort(function (a, b) {
            return a.label < b.label ? -1 : (a.label > b.label ? 1 : 0);
        });
        const body = rows.map(function (r) {
            const names = r.exp.words.filter(function (w) {
                return w !== EXPECTED_WORD;
            });
            const got = generalEntry(names, [], true);
            // a number that is missing differs and has no text, also an expected value without a number
            const hasExp = r.exp.number !== null;
            const hasGot = got !== null && got.number !== null;
            const same = hasGot && hasExp && checkSame(Number(got.number), Number(r.exp.number));
            const status = block('span', esc(TEXTS[same ? TXT.check_match : TXT.check_differs]), same ? STYLE.statusOk : STYLE.statusFail);
            const first = esc(r.label) + (r.exp.source ? block('span', esc(r.exp.source), STYLE.sub) : '');
            return block('tr', block('td', first)
                + block('td', hasExp ? esc(formatNumber(r.exp.number, 0)) : '', STYLE.number)
                + block('td', hasGot ? esc(formatNumber(got.number, 0)) : '', STYLE.number)
                + block('td', status, STYLE.number));
        }).join('');
        const head = block('th', esc(TEXTS[TXT.check_value])) + [TXT.check_expected, TXT.check_calculated, TXT.check_status].map(function (id) {
            return block('th', esc(TEXTS[id]), STYLE.number);
        }).join('');
        return rows.length === 0 ? '' : block('details', block('summary', esc(ownText(grp.name, KW.label) || '')) + table(head, body) + tableHint(grp.name));
    }

    /* the svg of the lines along the numbers with a line at zero and at the biggest number (html_base::line_diagram) */
    function lineDiagram(series, first, last, maxText) {
        const all = series.reduce(function (list, s) {
            return list.concat(s.nums);
        }, []);
        const max = Math.max.apply(null, [1].concat(all.map(Math.abs)));
        const half = (DIAGRAM.height - DIAGRAM.top - DIAGRAM.bottom) / 2;
        const yOf = function (num) {
            return DIAGRAM.top + half * (1 - num / max);
        };
        const xEnd = DIAGRAM.width - DIAGRAM.right;
        const lines = [max, 0, -max].map(function (num) {
            const y = yOf(num).toFixed(1);
            return '<line x1="' + DIAGRAM.left + '" x2="' + xEnd + '" y1="' + y + '" y2="' + y + '"/>';
        }).join('');
        const scale = ['+' + maxText, '0', '-' + maxText].map(function (text, k) {
            return '<text x="' + (DIAGRAM.left - 6) + '" y="' + (yOf([max, 0, -max][k]) + 4).toFixed(1) + '" text-anchor="end">' + esc(text) + '</text>';
        }).join('');
        const groups = series.map(function (s) {
            const xy = s.nums.map(function (num, k) {
                const x = DIAGRAM.left + k * (xEnd - DIAGRAM.left) / Math.max(1, s.nums.length - 1);
                return [x.toFixed(1), yOf(num).toFixed(1)];
            });
            const circles = xy.map(function (p, k) {
                return '<circle cx="' + p[0] + '" cy="' + p[1] + '" r="' + DIAGRAM.point + '"><title>' + esc(s.titles[k]) + '</title></circle>';
            }).join('');
            const points = xy.map(function (p) {
                return p.join(',');
            }).join(' ');
            return block('g', '<polyline points="' + points + '"/>' + circles, s.colour);
        }).join('');
        return '<svg viewBox="0 0 ' + DIAGRAM.width + ' ' + DIAGRAM.height + '" role="img">' + lines + scale + groups
            + '<text x="' + DIAGRAM.left + '" y="' + (DIAGRAM.height - 6) + '">' + esc(first) + '</text>'
            + '<text x="' + xEnd + '" y="' + (DIAGRAM.height - 6) + '" text-anchor="end">' + esc(last) + '</text></svg>';
    }

    /* a section with a diagram per column with the flag diagram along the rows of its table (ui_list::calculator_diagrams) */
    function diagrams() {
        let result = '';
        items(KW.detail).forEach(function (tbl) {
            subItems(tbl.name, VERB.has).forEach(function (col) {
                const title = ownText(col.name, KW.diagram);
                const lines = title ? diagramLines(col) : [];
                const rows = title ? rankedRows(tbl).filter(function (row) {
                    return lines.every(function (line) {
                        return generalEntry([row, col.to, line.filter], [], true) !== null;
                    });
                }) : [];
                if (rows.length > 1) {
                    const series = lines.map(function (line) {
                        const entries = rows.map(function (row) {
                            return generalEntry([row, col.to, line.filter], [], true);
                        });
                        const nums = entries.map(function (e) {
                            return Number(e.number);
                        });
                        const unit = unitOf(entries[entries.length - 1]);
                        const unitText = unit === '' ? '' : ' ' + unit;
                        const titles = nums.map(function (num, k) {
                            return line.text + ', ' + rows[k] + ': ' + formatNumber(num, 2) + unitText;
                        });
                        return {nums: nums, colour: line.colour, titles: titles, unitText: unitText, text: line.text};
                    });
                    const all = series.reduce(function (list, s) {
                        return list.concat(s.nums);
                    }, []);
                    const maxText = formatNumber(Math.max.apply(null, [1].concat(all.map(Math.abs))), 0);
                    const svg = lineDiagram(series, rows[0], rows[rows.length - 1], maxText);
                    const legend = series.map(function (s) {
                        const final = s.text + ' ' + TEXTS[TXT.diagram_final] + ' ' + formatNumber(s.nums[s.nums.length - 1], 2) + s.unitText;
                        return block('span', block('span', DIAGRAM_MARK, s.colour) + ' ' + esc(final));
                    }).join('');
                    result += block('details', block('summary', esc(title)) + block('div', svg, STYLE.chart) + block('div', legend, STYLE.legend));
                }
            });
        });
        return result;
    }

    /* per line of a diagram its filter, colour and label: one line per instance if the filter of the column is an instance of a repeated input (calculator::diagram_lines) */
    function diagramLines(col) {
        const flt = filterOf(col);
        let result = [{filter: flt, colour: '', text: label(col)}];
        phrases.forEach(function (p) {
            if (flt !== null && p.verb === VERB.is && p.from === flt && isRepeated(p.to)) {
                result = kinds(p.to).map(function (instance, pos) {
                    return {filter: instance, colour: columnColour(true, pos), text: capital(phraseLabel(instance))};
                });
            }
        });
        return result;
    }

    /* the section with per item the label in bold in front of the description (ui_list::calculator_notes) */
    function pageNotes() {
        const html = items(KW.note).map(function (note) {
            return block('p', block('b', esc(label(note))) + ' ' + esc(note.description));
        }).join('');
        return html === '' ? '' : block('details', block('summary', esc(ownText(group(KW.note).name, KW.label) || '')) + block('div', html, STYLE.notes));
    }

    /* the section with the import buttons of the page, without a function so far (ui_list::calculator_imports) */
    function importSection() {
        const html = items(KW.file_import).map(function (item) {
            return '<button type="button" class="' + STYLE.button + '">' + esc(label(item)) + '</button>';
        }).join('');
        return html === '' ? '' : block('details', block('summary', esc(ownText(group(KW.file_import).name, KW.label) || '')) + block('div', html, STYLE.imports));
    }

    /* sections: true for the tables with a collapsed section of their own, false for the other tables */
    function detailTables(sections) {
        let result = '';
        items(KW.detail).filter(function (tbl) {
            return hasFlag(tbl, KW.section) === sections;
        }).forEach(function (tbl) {
            const cols = columns(subItems(tbl.name, VERB.has));
            const numbered = hasFlag(tbl, KW.row_number);
            const head = (numbered ? block('th', ROW_NUMBER_HEAD, STYLE.number) : '') + block('th', esc(phraseLabel(tbl.to))) + cols.map(function (c) {
                return block('th', esc(c.text), (STYLE.number + ' ' + c.colour).trim());
            }).join('');
            const rows = rankedRows(tbl).map(function (k) {
                return [phraseLabel(k), [page.name, k], false];
            }).concat(subItems(tbl.name, VERB.with).map(function (x) {
                return [label(x), [page.name, x.to], hasFlag(x, KW.highlight)];
            }));
            const cells = rows.map(function (r) {
                return cols.map(function (c) {
                    return detailCell(r[1], c, cols, hasFlag(tbl, KW.page_independent));
                });
            });
            // the header names the unit, so a column of a detail table shares only the decimals, and a detail shows rough numbers
            const formats = cols.map(function (c, i) {
                return columnFormats(cells.map(function (row) {
                    return row[i];
                }), false, FMT.rough_digits);
            });
            const body = rows.map(function (r, j) {
                const tds = cells[j].map(function (e, i) {
                    return cols[i].isPhrase
                        ? block('td', esc(phraseCell(r[1][1], cols[i])))
                        : cellHtml(e, formats[i], false, false, cellDecimals(r[1][1], cols[i]), cols[i].plus, columnUnit(e, r[1][1], cols[i]), cols[i].colour);
                }).join('');
                const number = numbered ? block('td', String(j + 1), STYLE.number) : '';
                // a numbered table shows the hint of the row phrase e.g. the full name of a ticker (calculator::phrase_hint)
                const sub = numbered ? ownText(r[1][1], KW.hint) : '';
                return block('tr', number + block('td', esc(r[0]) + (sub ? block('span', esc(sub), STYLE.sub) : '')) + tds, r[2] ? STYLE.key : '');
            }).join('');
            const tableHtml = table(head, body) + tableHint(tbl.name) + buttons(tbl);
            result += sections
                ? block('details', block('summary', esc(label(tbl))) + tableHtml)
                : block('h2', esc(label(tbl))) + tableHtml;
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
        result += checkTable() + diagrams() + detailTables(true) + pageNotes() + importSection();
        // the details after the main table, so that their notes and sources include the numbers of both
        const more = detailTables(false);
        const extra = more + notes() + sourceList();
        if (extra !== '') {
            const summary = ownText((group(KW.detail) || {}).name, KW.label) || TEXTS[TXT.details];
            result += block('details', block('summary', esc(summary)) + block('div', extra, STYLE.more));
        }
        return block('section', result, STYLE.panel);
    }

    /* the number of an input for the calculation: the value of a checkbox stays 1 also if it is unchecked, and a number field of a share shows percent */
    function enteredNumber(input, entry) {
        let result = Number(input.value);
        if (input.type === 'checkbox') {
            result = Number(input.checked);
        } else if (input.type === 'number' && entry !== undefined && showsPercent(entry)) {
            result = result / 100;
        }
        return result;
    }

    /* the result panel again with the numbers of the inputs, the open blocks staying open */
    function refreshResults(event) {
        const entry = inputEntries.get(event.target.name);
        const panel = host.querySelector('section.' + STYLE.panel);
        const value = enteredNumber(event.target, entry);
        if (entry !== undefined && panel !== null && event.target.value !== '' && isFinite(value)) {
            entry.number = value;
            recalculate();
            const open = Array.from(panel.querySelectorAll('details')).map(function (d) {
                return d.open;
            });
            panel.outerHTML = resultPanel();
            host.querySelector('div.' + STYLE.warnings).innerHTML = warningTexts();
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
    host.addEventListener('submit', saveCase);

})();
