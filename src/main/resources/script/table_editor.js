"use strict";

/* the table editor (system_page::table_editor): the view renders the empty host, the phraseData answer of
   the table phrase as the seed, the translated texts and the settings, and this script builds the whole
   editor into the host: the top line of the calculator editor, the parameter rail, the live table, the
   definition as json and the table as csv; the save sends the changed parameters as triples and values
   to the updatePut api */
(function () {
    /* the html ids of the host and of the json elements that the view renders (system_page::TABLE_EDITOR_*) */
    const ELEMENT = {
        host: "tableEditor",
        seed: "seed",
        seedName: "seedName",
        texts: "tableEditorTexts",
        settings: "tableEditorSettings"
    };

    /* the variables of the messages of the pod e.g. z$VarValueCount$z */
    const VAR_START = "z$";
    const VAR_END = "$z";
    const VAR = {
        name: "VarObjName",
        phrase: "VarPhraseName",
        count: "VarValueCount",
        value: "VarValue",
        time: "VarTime"
    };
    /* the message ids of every text of the editor; the top line uses the texts of the calculator editor */
    const TXT = {
        title: "system_title_table_editor",
        for: "calc_editor_for",
        settings: "calc_editor_settings",
        phrasePlaceholder: "calc_editor_phrase_short_placeholder",
        load: "calc_editor_load",
        noChanges: "calc_editor_no_changes",
        change: "calc_editor_change",
        changes: "calc_editor_changes",
        undo: "calc_editor_undo",
        undoTip: "calc_editor_undo_tip",
        redo: "calc_editor_redo",
        redoTip: "calc_editor_redo_tip",
        discardChanges: "calc_editor_discard_changes",
        discardTip: "calc_editor_discard_tip",
        discarded: "calc_editor_discarded",
        export: "calc_editor_export",
        exportTip: "table_editor_export_tip",
        save: "calculator_save",
        saveTip: "calc_editor_save_tip",
        saving: "calc_editor_saving",
        showTable: "table_editor_show_table",
        noJs: "table_editor_no_js",
        enterName: "calc_editor_enter_name",
        offlineOnly: "calc_editor_offline_only",
        unsavedLost: "calc_editor_unsaved_lost",
        loadAnyway: "calc_editor_load_anyway",
        refused: "table_editor_refused",
        unreachable: "table_editor_unreachable",
        notConfirmed: "calc_editor_not_confirmed",
        saveNotConfirmed: "calc_editor_save_not_confirmed",
        numberInvalid: "table_editor_number_invalid",
        measured: "table_editor_measured",
        openGroups: "table_editor_open_groups",
        closeGroups: "table_editor_close_groups",
        railNote: "table_editor_rail_note",
        tokenPlaceholder: "table_editor_token_placeholder",
        tokenFind: "table_editor_token_find",
        tokenHint: "table_editor_token_hint",
        tokenEmpty: "table_editor_token_empty",
        tokenRemove: "table_editor_token_remove",
        noMatch: "table_editor_no_match",
        tabTable: "table_editor_tab_table",
        tabJson: "table_editor_tab_json",
        tabCsv: "table_editor_tab_csv",
        jsonNote: "table_editor_json_note",
        applyJson: "table_editor_apply_json",
        copy: "table_editor_copy",
        applied: "table_editor_applied",
        jsonInvalid: "table_editor_json_invalid",
        copied: "table_editor_copied",
        copyManual: "table_editor_copy_manual",
        csvNote: "table_editor_csv_note",
        csvAllRows: "table_editor_csv_all_rows",
        csvSources: "table_editor_csv_sources",
        noRows: "table_editor_no_rows",
        sumTip: "table_editor_sum_tip",
        level: "table_editor_level",
        subtotal: "table_editor_subtotal",
        total: "table_editor_total",
        count: "table_editor_count",
        row: "table_editor_row",
        selectRow: "table_editor_select_row",
        toggleTip: "table_editor_toggle_tip",
        rowsShown: "table_editor_rows_shown",
        metaLevel: "table_editor_meta_level",
        asOf: "table_editor_as_of",
        selected: "table_editor_selected",
        columnLevel: "table_editor_column_level",
        searchRows: "table_editor_search_rows",
        expandAll: "table_editor_expand_all",
        collapseAll: "table_editor_collapse_all",
        previous: "table_editor_previous",
        next: "table_editor_next",
        page: "table_editor_page",
        rowsHidden: "table_editor_rows_hidden"
    };
    /* the prefix of the label, the hint and the group title of a parameter field */
    const TXT_PREFIX = "table_editor_";
    const TXT_GROUP = "group_";
    const TXT_HINT = "_hint";
    /* the message type of the updatePut api and the header of a change with the session token of the page,
       like in the calculator editor (api::CSRF_TOKEN_HEADER, url_var::SESSION_TOKEN) */
    const UPDATE_PUT_TYPE = "updatePut";
    const CSRF_HEADER = "X-CSRF-Token";
    const SESSION_TOKEN_NAME = "token";
    /* the answer of the updatePut api that confirms a removal (update_put::confirmed) */
    const ACTION_REMOVED = "removed";
    const ACTION_UNCHANGED = "unchanged";

    /* ---------- the seed: the phraseData answer of the phrase of the table, as the pod sends it ---------- */
    /* an id beyond the safe integer of javascript is kept as text like in the calculator editor, so that a save sends it unchanged */
    function seedDoc() {
        const seedText = document.getElementById(ELEMENT.seed).textContent.replace(/<\\\//g, "</");
        const safeText = seedText.replace(/("id":\s*)(-?\d{16,})(?=\s*[,}\]])/g, '$1"$2"');
        const doc = JSON.parse(safeText);
        const result = {doc: doc, name: seedName(doc)};
        return result;
    }

    /* the name of the seed file in a sample; the pod sends none, so it is the phrase of the table */
    function seedName(doc) {
        const el = document.getElementById(ELEMENT.seedName);
        const result = el ? JSON.parse(el.textContent) : doc.selection[0].name;
        return result;
    }

    function jsonOf(elementId) {
        const el = document.getElementById(elementId);
        const result = el ? JSON.parse(el.textContent) : {};
        return result;
    }

    const SEED = seedDoc();
    const TRIPLES = SEED.doc.triples || [];
    const VALUES = SEED.doc.values || [];
    const WORDS = SEED.doc.words || [];

    /* the translated texts of the pod by their message id */
    const TEXTS = jsonOf(ELEMENT.texts);

    /* the names of the verbs, the own columns keyword, the words of a value, the keyword of each parameter
       and the urls of the save, the table and the editor without javascript, which the pod sends from its consts */
    const SETTINGS = jsonOf(ELEMENT.settings);
    const VERB = SETTINGS.verbs;
    const COLUMN_KEYWORD = SETTINGS.column;
    const WORD = SETTINGS.words;
    const KEYWORD = SETTINGS.keywords;
    const URLS = SETTINGS.urls;

    /* the translated text of the message id with its variables e.g. txt(TXT.subtotal, {[VAR.name]: name}),
       or the message id itself, so that a missing text is seen */
    function txt(txtId, vars) {
        let result = TEXTS[txtId] || txtId;
        Object.keys(vars || {}).forEach(function (key) {
            result = result.split(VAR_START + key + VAR_END).join(String(vars[key]));
        });
        return result;
    }

    function txtHtml(txtId, vars) {
        const result = escapeHtml(txt(txtId, vars));
        return result;
    }

    /* the translated text of a number e.g. "1 change" or "2 changes" */
    function txtCount(oneId, manyId, count) {
        const result = txt(count === 1 ? oneId : manyId, {[VAR.count]: count});
        return result;
    }

    /* ---------- parameter specification: what the rail shows ---------- */
    /* each parameter is a triple from the phrase of the table to its keyword (settings.keywords by the key):
       a text in the description of the triple, a number as a value with the keyword, a flag as the triple
       itself and a choice as the triples from the parameter triple to the selected phrases; an own column is
       an item of the column keyword ordered by "is follower of" with its tier as a choice; the save keeps the
       form in which a parameter is saved, so e.g. a select saved as a description stays a description;
       the label of a field is the text table_editor_<key> and its hint table_editor_<key>_hint */
    const SPEC = [
        {
            group: "columns", fields: [
                {key: "column_level", type: "select", options: ["mayor", "main", "minor", "all"]},
                {key: "column_mayor", tier: "mayor", type: "tokens", wide: true},
                {key: "column_main", tier: "main", type: "tokens", wide: true},
                {key: "column_minor", tier: "minor", type: "tokens", wide: true},
                {key: "column_marginal", tier: "marginal", type: "tokens", wide: true},
                {key: "column_order_rule", type: "select", options: ["listed", "alphabetical", "by value count"]},
                {key: "column_frozen", type: "number", min: 0, max: 3},
                {key: "header_label", type: "select", options: ["full", "short"]},
                {key: "header_show_unit", type: "check"},
                {key: "header_rotate", type: "check"},
                {key: "column_pivot_phrase", type: "text"}
            ]
        },
        {
            group: "rows", fields: [
                {key: "row_phrase", type: "text"},
                {key: "row_verb", type: "select", options: ["is a", "is part of", "has"]},
                {key: "row_direction", type: "select", options: ["subject", "object"]},
                {key: "row_filter", type: "number"},
                {key: "row_order", type: "text"},
                {key: "row_limit", type: "number", min: 1},
                {key: "row_show_all", type: "check"},
                {key: "row_page_size", type: "number", min: 1},
                {key: "row_empty", type: "select", options: ["show", "hide", "grey"]},
                {key: "row_label_mode", type: "select", options: ["name", "abbreviation", "id", "name + description"]},
                {key: "row_pinned", type: "text"}
            ]
        },
        {
            group: "hierarchy", fields: [
                {key: "hierarchy_verb", type: "select", options: ["is part of", "is a"]},
                {key: "hierarchy_expand", type: "select", options: ["collapsed", "expanded", "level 1"]},
                {key: "hierarchy_max_depth", type: "number", min: 0, max: 3},
                {key: "hierarchy_indent", type: "select", options: ["indent", "tree lines", "level column"]},
                {key: "hierarchy_parent_value", type: "select", options: ["own", "sum of children", "both", "blank"]},
                {key: "hierarchy_orphans", type: "select", options: ["top", "bottom", "hidden"]},
                {key: "hierarchy_sort_scope", type: "select", options: ["siblings", "flat"]}
            ]
        },
        {
            group: "values", fields: [
                {key: "value_format", type: "text"},
                {key: "value_scale", type: "select", options: [["1", "table_editor_value_scale_one"], ["0.001", "table_editor_value_scale_billion"], ["1000", "table_editor_value_scale_thousand"]]},
                {key: "value_unit", type: "text", join: " "},
                {key: "value_missing", type: "select", options: ["dash", "blank", "n/a", "zero"]},
                {key: "value_delta", type: "select", options: ["none", "absolute", "percent"]},
                {key: "value_show_source", type: "select", options: ["none", "tooltip", "column"]},
                {key: "value_show_confidence", type: "check"},
                {key: "value_conditional_style", type: "number"},
                {key: "value_align", type: "select", options: ["auto", "left", "right", "center"]},
                {key: "value_time", type: "select", options: ["latest", "specific year", "average"]},
                {key: "value_source_mode", type: "select", options: ["preferred", "latest", "average", "own"]}
            ]
        },
        {
            group: "aggregation", fields: [
                {key: "totals_row", type: "select", options: ["none", "sum", "average", "count"]},
                {key: "totals_column", type: "check"},
                {key: "subtotals", type: "check"},
                {key: "aggregation_scope", type: "select", options: ["all", "visible page"]}
            ]
        },
        {
            group: "layout", fields: [
                {key: "title", type: "text", wide: true},
                {key: "caption", type: "textarea", wide: true},
                {key: "density", type: "select", options: ["compact", "normal", "comfortable"]},
                {key: "striped", type: "check"},
                {key: "borders", type: "select", options: ["none", "horizontal", "grid"]},
                {key: "max_width", type: "text"},
                {key: "transpose", type: "check"},
                {key: "responsive_mode", type: "select", options: ["scroll", "stack", "drop minor columns"]}
            ]
        },
        {
            group: "interaction", fields: [
                {key: "sortable", type: "check"},
                {key: "searchable", type: "check"},
                {key: "selectable", type: "check"},
                {key: "drilldown", type: "select", options: ["phrase", "value history", "sub-table", "nothing"]},
                {key: "refresh_mode", type: "select", options: ["static", "on demand", "live"]}
            ]
        },
        {
            group: "context", fields: [
                {key: "language", type: "select", options: [["en", "English"], ["de", "Deutsch (de-CH)"], ["fr", "Français"]]},
                {key: "time_reference", type: "text"},
                {key: "profile", type: "text"},
                {key: "view_owner", type: "text"},
                {key: "view_shared", type: "check"}
            ]
        },
        {
            group: "export", fields: [
                {key: "export_formats", type: "text", join: ", "},
                {key: "export_scope", type: "select", options: ["visible", "all rows"]},
                {key: "export_include_sources", type: "check"}
            ]
        },
        {
            group: "state", fields: [
                {key: "persist_user_changes", type: "check"},
                {key: "view_version", type: "number", min: 1}
            ]
        }
    ];

    function groupTitle(g) {
        const result = txt(TXT_PREFIX + TXT_GROUP + g.group);
        return result;
    }

    function fieldLabel(f) {
        const result = txt(TXT_PREFIX + f.key);
        return result;
    }

    function fieldHint(f) {
        const result = TEXTS[TXT_PREFIX + f.key + TXT_HINT] || "";
        return result;
    }

    /* the name of the keyword triple of a parameter e.g. frozen table column (system) */
    function keywordOf(f) {
        const result = KEYWORD[f.key];
        return result;
    }

    function optionValue(o) {
        const result = Array.isArray(o) ? o[0] : o;
        return result;
    }

    function optionLabel(o) {
        const result = Array.isArray(o) ? txt(o[1]) : o;
        return result;
    }

    /* ---------- the view definition and the rows from the seed ---------- */
    function tripleOf(from, verb, to) {
        const result = TRIPLES.find(function (t) {
            return t.from === from && t.verb === verb && t.to === to;
        }) || null;
        return result;
    }

    function tripleByName(name) {
        const result = TRIPLES.find(function (t) {
            return t.name === name;
        }) || null;
        return result;
    }

    /* the triples from the phrase with the verb, in the order of their is follower of triples
       e.g. the triples to csv and json of sector of revenue export formats */
    function orderedTriples(from, verb) {
        const names = TRIPLES.filter(function (t) {
            return t.from === from && t.verb === verb;
        }).map(function (t) {
            return t.name;
        });
        const next = {};
        const hasPrior = new Set();
        TRIPLES.forEach(function (t) {
            if (t.verb === VERB.follow && names.indexOf(t.from) >= 0 && names.indexOf(t.to) >= 0) {
                next[t.to] = t.from;
                hasPrior.add(t.from);
            }
        });
        const chain = [];
        names.filter(function (n) {
            return !hasPrior.has(n);
        }).forEach(function (n) {
            let item = n;
            while (item && chain.indexOf(item) < 0) {
                chain.push(item);
                item = next[item];
            }
        });
        /* a loop in the follower triples has no first item, so its items are added in the order of the seed */
        names.forEach(function (n) {
            if (chain.indexOf(n) < 0) {
                chain.push(n);
            }
        });
        const result = chain.map(tripleByName);
        return result;
    }

    /* the to sides of the ordered triples e.g. csv and json */
    function orderedTargets(from, verb) {
        const result = orderedTriples(from, verb).map(function (t) {
            return t.to;
        });
        return result;
    }

    function valuePhrases(v) {
        const result = (v.words || []).concat(v.triples || []);
        return result;
    }

    /* the value with exactly these phrases e.g. sector of revenue and frozen table column */
    function seedValue(phrases) {
        const result = VALUES.find(function (v) {
            const names = valuePhrases(v);
            return names.length === phrases.length && phrases.every(function (p) {
                return names.indexOf(p) >= 0;
            });
        }) || null;
        return result;
    }

    function seedNumber(phrases) {
        const val = seedValue(phrases);
        const result = val ? Number(val.number) : null;
        return result;
    }

    /* the own columns of the tier in their order e.g. 2024 and 2025 for mayor */
    function seedColumns(tier) {
        const colTrp = tripleOf(phraseName, VERB.has, COLUMN_KEYWORD);
        let result = [];
        if (colTrp) {
            result = orderedTargets(colTrp.name, VERB.has).filter(function (col) {
                const itm = tripleOf(colTrp.name, VERB.has, col);
                return tripleOf(itm.name, VERB.has, tier) !== null;
            });
        }
        return result;
    }

    /* the option of a select field whose value or label is the chosen phrase e.g. en for English */
    function selectValue(f, name) {
        const opt = f.options.find(function (o) {
            return optionValue(o) === name || optionLabel(o) === name;
        });
        const result = opt === undefined ? name : optionValue(opt);
        return result;
    }

    function emptyParam(f) {
        const result = f.type === "check" ? false : "";
        return result;
    }

    /* the triple from the phrase of the table to the keyword of the parameter, null if not saved */
    function paramTriple(f) {
        const result = tripleOf(phraseName, VERB.has, keywordOf(f));
        return result;
    }

    function seedParam(f) {
        let result = emptyParam(f);
        if (f.tier) {
            result = seedColumns(f.tier);
        } else {
            const trp = paramTriple(f);
            const num = seedNumber([phraseName, keywordOf(f)]);
            const choices = trp ? orderedTargets(trp.name, VERB.has) : [];
            if (num !== null) {
                result = f.type === "select" ? String(num) : num;
            } else if (trp && trp.description) {
                result = trp.description;
            } else if (choices.length > 0 && f.type === "select") {
                result = selectValue(f, choices[0]);
            } else if (choices.length > 0) {
                result = choices.join(f.join || " ");
            } else if (trp) {
                result = true;
            }
        }
        return result;
    }

    function seedView() {
        const result = {};
        SPEC.forEach(function (g) {
            result[g.group] = {};
            g.fields.forEach(function (f) {
                result[g.group][f.key] = seedParam(f);
            });
        });
        return result;
    }

    function wordDescription(name) {
        const wrd = WORDS.find(function (w) {
            return w.name === name;
        });
        const result = wrd && wrd.description ? wrd.description : "";
        return result;
    }

    /* a row of a triple of the owner e.g. Example AG energy is shown by its to side energy */
    function seedRow(name, def) {
        const trp = tripleByName(name);
        const acr = TRIPLES.find(function (t) {
            return t.verb === VERB.acronym && t.to === name;
        });
        const up = TRIPLES.find(function (t) {
            return t.from === name && t.verb === def.hierarchy.hierarchy_verb;
        });
        const vals = VALUES.filter(function (v) {
            return valuePhrases(v).indexOf(name) >= 0;
        });
        const nums = vals.filter(function (v) {
            return valuePhrases(v).indexOf(WORD.confidence) < 0;
        });
        const conf = vals.find(function (v) {
            return valuePhrases(v).indexOf(WORD.confidence) >= 0;
        });
        const values = {};
        nums.forEach(function (v) {
            const year = valuePhrases(v).find(isYear);
            if (year) {
                values[year] = Number(v.number);
            }
        });
        const result = {
            id: name,
            name: trp && trp.verb === VERB.has ? trp.to : name,
            abbr: acr ? acr.from : "",
            parent: up ? up.to : null,
            values: values,
            source: nums.length > 0 && nums[0].source ? nums[0].source : "",
            assumed: nums.some(function (v) {
                return valuePhrases(v).indexOf(WORD.assumed) >= 0;
            }),
            conf: conf ? Number(conf.number) : 1,
            description: trp && trp.description ? trp.description : wordDescription(name)
        };
        return result;
    }

    /* the rows are the phrases linked to the row phrase by the row verb, the subject or the object side;
       a top row without sub-rows has no place in the hierarchy, so it is an orphan e.g. other */
    function seedData(def) {
        const rows = def.rows;
        const isSubject = rows.row_direction === "subject";
        const names = TRIPLES.filter(function (t) {
            return t.verb === rows.row_verb && (isSubject ? t.to === rows.row_phrase : t.from === rows.row_phrase);
        }).map(function (t) {
            return isSubject ? t.from : t.to;
        });
        const phrases = names.map(function (n) {
            return seedRow(n, def);
        });
        phrases.forEach(function (p) {
            if (names.indexOf(p.parent) < 0) {
                p.parent = null;
            }
        });
        phrases.forEach(function (p) {
            const hasKids = phrases.some(function (c) {
                return c.parent === p.id;
            });
            p.orphan = p.parent === null && !hasKids;
        });
        const years = [];
        phrases.forEach(function (p) {
            Object.keys(p.values).forEach(function (y) {
                if (years.indexOf(y) < 0) {
                    years.push(y);
                }
            });
        });
        const result = {phrases: phrases, years: years.sort()};
        return result;
    }

    /* the phrases that can be a column: the own columns of the table with their description */
    function seedColumnPhrases() {
        const colTrp = tripleOf(phraseName, VERB.has, COLUMN_KEYWORD);
        const names = colTrp ? orderedTargets(colTrp.name, VERB.has) : [];
        const result = names.map(function (n) {
            return {name: n, description: wordDescription(n)};
        });
        return result;
    }

    /* ---------- runtime state ---------- */
    /* the phrase of the table and its definition as saved in the database */
    const phraseName = SEED.doc.selection[0].name;
    const savedView = seedView();
    const DATA = seedData(savedView);
    const COLUMN_PHRASES = seedColumnPhrases();
    let view = deepClone(savedView);
    /* the undo and redo snapshots of the view definition, a name apart from window.history */
    const steps = {undo: [], redo: [], lastKey: "", lastTime: 0};
    /* like the calculator editor: typing into the same field within 1.5 seconds is one step, at most 200 steps */
    const STEP_MERGE_MS = 1500;
    const STEP_MAX = 200;
    let saving = false;
    let expanded = new Set();
    let expandRule = "";
    let selected = new Set();
    let searchText = "";
    let page = 1;
    let lastMatrix = null;

    function deepClone(obj) {
        const result = JSON.parse(JSON.stringify(obj));
        return result;
    }

    function groupOf(key) {
        const grp = SPEC.find(function (g) {
            return g.fields.some(function (f) {
                return f.key === key;
            });
        });
        const result = grp ? grp.group : null;
        return result;
    }

    function get(key) {
        const result = view[groupOf(key)][key];
        return result;
    }

    function set(key, value) {
        pushHistory(key);
        view[groupOf(key)][key] = value;
    }

    /* ---------- the editor markup in the host ---------- */
    function buttonHtml(id, txtId, tipId, cls) {
        const tip = tipId ? ' title="' + txtHtml(tipId) + '"' : "";
        const result = '<button class="btn' + (cls ? " " + cls : "") + '" id="' + id + '" type="button"' + tip + ">" + txtHtml(txtId) + "</button>";
        return result;
    }

    /* a link of the top line e.g. to the table, only if the pod has sent its url */
    function linkHtml(url, txtId) {
        const result = url ? '<a class="btn" href="' + escapeHtml(url) + '">' + txtHtml(txtId) + "</a>" : "";
        return result;
    }

    function tabHtml(tab, txtId, on) {
        const result = '<button role="tab" aria-selected="' + on + '" data-tab="' + tab + '" type="button">' + txtHtml(txtId) + "</button>";
        return result;
    }

    function barHtml() {
        const result = '<div class="bar">'
            + '<div class="brand"><b>' + txtHtml(TXT.title) + "</b></div>"
            + '<form class="phrasebar" id="phraseForm" autocomplete="off">'
            + '<label for="phraseIn">' + txtHtml(TXT.for) + "</label>"
            + '<input class="inp" id="phraseIn" placeholder="' + txtHtml(TXT.phrasePlaceholder) + '" spellcheck="false">'
            + '<button class="btn primary" type="submit" id="btnLoad">' + txtHtml(TXT.load) + "</button>"
            + "</form>"
            + '<div class="tools">'
            + '<span class="chip count" id="changeCount"></span>'
            + buttonHtml("btnUndo", TXT.undo, TXT.undoTip)
            + buttonHtml("btnRedo", TXT.redo, TXT.redoTip)
            + buttonHtml("btnReset", TXT.discardChanges, TXT.discardTip)
            + buttonHtml("btnExport", TXT.export, TXT.exportTip)
            + linkHtml(URLS.table, TXT.showTable)
            + linkHtml(URLS.no_js, TXT.noJs)
            + buttonHtml("btnSave", TXT.save, TXT.saveTip, "primary")
            + "</div></div>";
        return result;
    }

    function panelsHtml() {
        const result = '<div class="tabs" role="tablist">'
            + tabHtml("preview", TXT.tabTable, true) + tabHtml("json", TXT.tabJson, false) + tabHtml("csv", TXT.tabCsv, false)
            + "</div>"
            + '<section class="panel" id="panel-preview">'
            + '<div class="view-head"><h2 id="viewTitle"></h2><span class="meta" id="viewMeta"></span></div>'
            + '<div class="controls" id="controls"></div>'
            + '<div class="table-box" id="tableBox"></div>'
            + '<div class="pager" id="pager"></div>'
            + '<p class="caption" id="viewCaption"></p>'
            + "</section>"
            + '<section class="panel json-box" id="panel-json" hidden>'
            + '<p class="note">' + txtHtml(TXT.jsonNote) + "</p>"
            + '<div class="controls">' + buttonHtml("btnApply", TXT.applyJson, "", "primary") + buttonHtml("btnCopyJson", TXT.copy, "")
            + '<span class="msg" id="jsonMsg"></span></div>'
            + '<textarea id="jsonText" spellcheck="false"></textarea>'
            + "</section>"
            + '<section class="panel json-box" id="panel-csv" hidden>'
            + '<p class="note">' + txtHtml(TXT.csvNote) + "</p>"
            + '<div class="controls">' + buttonHtml("btnCopyCsv", TXT.copy, "") + '<span class="msg" id="csvMsg"></span></div>'
            + '<textarea id="csvText" spellcheck="false" readonly></textarea>'
            + "</section>";
        return result;
    }

    function buildEditor() {
        document.getElementById(ELEMENT.host).innerHTML = barHtml()
            + '<div id="noticeHost"></div>'
            + '<div class="wrap"><aside class="col"><div class="col-head"><h2>' + txtHtml(TXT.settings) + "</h2></div>"
            + '<div id="rail"></div></aside><main class="col">' + panelsHtml() + "</main></div>";
    }

    /* ---------- undo, redo and the changes since the last save, like the calculator editor ---------- */
    function pushHistory(key) {
        const now = Date.now();
        const coalesce = key && key === steps.lastKey && now - steps.lastTime < STEP_MERGE_MS;
        if (!coalesce) {
            steps.undo.push(JSON.stringify(view));
            if (steps.undo.length > STEP_MAX) {
                steps.undo.shift();
            }
            steps.redo = [];
        }
        steps.lastKey = key || "";
        steps.lastTime = now;
    }

    function restore(snap) {
        view = JSON.parse(snap);
        steps.lastKey = "";
        expandRule = "";
        // e.g. the notice of a discard is no longer true after the undo
        notice("", "");
        syncForm();
        render();
    }

    function undo() {
        if (steps.undo.length > 0) {
            steps.redo.push(JSON.stringify(view));
            restore(steps.undo.pop());
        }
    }

    function redo() {
        if (steps.redo.length > 0) {
            steps.undo.push(JSON.stringify(view));
            restore(steps.redo.pop());
        }
    }

    function isChanged(group, key) {
        const result = JSON.stringify(view[group][key]) !== JSON.stringify(savedView[group][key]);
        return result;
    }

    /* the number of parameters that differ from the saved definition */
    function changeCount() {
        let result = 0;
        SPEC.forEach(function (g) {
            g.fields.forEach(function (f) {
                if (isChanged(g.group, f.key)) {
                    result += 1;
                }
            });
        });
        return result;
    }

    /* the save needs the put url of the pod, so it stays disabled without e.g. in the sample */
    function paintBar() {
        const changed = changeCount();
        const save = document.getElementById("btnSave");
        document.getElementById("changeCount").textContent = changed === 0 ? txt(TXT.noChanges) : txtCount(TXT.change, TXT.changes, changed);
        document.getElementById("btnUndo").disabled = steps.undo.length === 0;
        document.getElementById("btnRedo").disabled = steps.redo.length === 0;
        save.disabled = !URLS.put || changed === 0 || saving;
        save.textContent = saving ? txt(TXT.saving) : txt(TXT.save);
        const phraseIn = document.getElementById("phraseIn");
        if (document.activeElement !== phraseIn) {
            phraseIn.value = phraseName;
        }
    }

    function notice(html, kind) {
        let result = "";
        if (html) {
            result = '<div class="notice' + (kind === "err" ? " err" : "") + '">' + html + "</div>";
        }
        document.getElementById("noticeHost").innerHTML = result;
    }

    function discard() {
        if (changeCount() > 0) {
            pushHistory("");
            view = deepClone(savedView);
            expandRule = "";
            syncForm();
            render();
            notice(txtHtml(TXT.discarded), "");
        }
    }

    /* the seed holds the table of one phrase, so Load reads the saved table of this phrase again */
    function loadPhrase(force) {
        const name = document.getElementById("phraseIn").value.trim();
        const changed = changeCount();
        if (!name) {
            notice(txtHtml(TXT.enterName), "err");
        } else if (name !== phraseName) {
            notice(txtHtml(TXT.offlineOnly, {[VAR.phrase]: phraseName}), "err");
        } else if (changed > 0 && !force) {
            notice(txtHtml(TXT.unsavedLost, {[VAR.count]: changed, [VAR.phrase]: phraseName}) + " "
                + '<button class="btn small" id="btnLoadAnyway" type="button">' + txtHtml(TXT.loadAnyway, {[VAR.phrase]: name}) + "</button>", "err");
            document.getElementById("btnLoadAnyway").addEventListener("click", function () {
                loadPhrase(true);
            });
        } else {
            view = deepClone(savedView);
            steps.undo = [];
            steps.redo = [];
            expandRule = "";
            page = 1;
            notice("", "");
            syncForm();
            render();
        }
    }

    function exportCsv() {
        const blob = new Blob([document.getElementById("csvText").value], {type: "text/csv"});
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = phraseName + ".csv";
        link.click();
        URL.revokeObjectURL(link.href);
    }

    function onKey(ev) {
        const mod = ev.ctrlKey || ev.metaKey;
        const inField = ev.target.matches && ev.target.matches("input, textarea, select");
        if (mod && !inField && ev.key.toLowerCase() === "z") {
            ev.preventDefault();
            if (ev.shiftKey) {
                redo();
            } else {
                undo();
            }
        }
    }

    function mergeView(saved) {
        const result = deepClone(savedView);
        Object.keys(result).forEach(function (g) {
            if (saved && saved[g]) {
                Object.keys(result[g]).forEach(function (k) {
                    if (k in saved[g]) {
                        result[g][k] = saved[g][k];
                    }
                });
            }
        });
        return result;
    }

    /* ---------- the save: the changed parameters as the triples and values of the comment above SPEC ---------- */
    /* the forms in which a parameter is saved */
    const FORM = {flag: "flag", number: "number", text: "text", choice: "choice"};

    /* the name of a new parameter triple e.g. sector of revenue column level */
    function paramName(f) {
        const result = phraseName + " " + f.key.split("_").join(" ");
        return result;
    }

    /* the triples from the parameter triple to its choices or from an own column to its tier */
    function itemTriples(fromName) {
        const result = TRIPLES.filter(function (t) {
            return t.from === fromName && t.verb === VERB.has;
        });
        return result;
    }

    function followerOf(itemName) {
        const result = TRIPLES.find(function (t) {
            return t.from === itemName && t.verb === VERB.follow;
        }) || null;
        return result;
    }

    /* a select with only numbers as options e.g. the scale is a value like a number field */
    function hasNumberOptions(f) {
        const result = f.type === "select" && f.options.every(function (o) {
            return optionValue(o) !== "" && isFinite(Number(optionValue(o)));
        });
        return result;
    }

    /* the form of the saved parameter or, if not saved yet, the form of its type */
    function formOf(f) {
        const trp = paramTriple(f);
        let result = FORM.text;
        if (f.type === "check") {
            result = FORM.flag;
        } else if (f.type === "number" || hasNumberOptions(f)) {
            result = FORM.number;
        } else if (trp && trp.description) {
            result = FORM.text;
        } else if (trp && itemTriples(trp.name).length > 0) {
            result = FORM.choice;
        } else if (f.type === "select" || f.join) {
            result = FORM.choice;
        }
        return result;
    }

    function knownPhrase(name) {
        const result = WORDS.some(function (w) {
            return w.name === name;
        }) || TRIPLES.some(function (t) {
            return t.name === name;
        });
        return result;
    }

    /* a phrase that the seed does not have is added as a word, which the pod merges by its name */
    function usePhrase(out, name) {
        const added = out.body.words.some(function (w) {
            return w.name === name;
        });
        if (!knownPhrase(name) && !added) {
            out.body.words.push({name: name});
        }
    }

    /* a triple of the update put message by the names like in the calculator editor, with the id if saved */
    function tripleJson(id, name, description, from, verb, to) {
        const json = {};
        if (id) {
            json.id = id;
        }
        if (name) {
            json.name = name;
        }
        if (description) {
            json.description = description;
        }
        json.from = from;
        json.verb = verb;
        json.to = to;
        return json;
    }

    function addTriple(out, name, from, verb, to) {
        out.body.triples.push(tripleJson(0, name, "", from, verb, to));
    }

    function pushDelete(out, cls, id) {
        const key = cls + " " + id;
        if (!out.deleted.has(key)) {
            out.deleted.add(key);
            out.body.delete.push({class: cls, id: id});
        }
    }

    /* the removal of a triple with every triple built from it, the children first so that the pod can delete each with the logged del() */
    function removeTree(out, trp) {
        if (!out.visited.has(trp.name)) {
            out.visited.add(trp.name);
            TRIPLES.filter(function (t) {
                return t.from === trp.name;
            }).forEach(function (t) {
                removeTree(out, t);
            });
            pushDelete(out, "triple", trp.id);
        }
    }

    /* the ordered choices of a parameter: the group triple if new, the new items, the follower triples that
       differ and the removal of the items that are no longer wanted; returns the item name of each choice */
    function writeList(out, kwd, newName, wanted) {
        const grp = tripleOf(phraseName, VERB.has, kwd);
        const grpName = grp ? grp.name : newName;
        const saved = grp ? itemTriples(grp.name) : [];
        const names = {};
        if (!grp && wanted.length > 0) {
            addTriple(out, grpName, phraseName, VERB.has, kwd);
        }
        wanted.forEach(function (to) {
            const itm = saved.find(function (t) {
                return t.to === to;
            });
            names[to] = itm ? itm.name : grpName + " " + to;
            if (!itm) {
                usePhrase(out, to);
                addTriple(out, names[to], grpName, VERB.has, to);
            }
        });
        wanted.forEach(function (to, pos) {
            const follower = followerOf(names[to]);
            const prev = pos === 0 ? null : names[wanted[pos - 1]];
            if (follower && follower.to !== prev) {
                pushDelete(out, "triple", follower.id);
            }
            if (prev !== null && (!follower || follower.to !== prev)) {
                addTriple(out, "", names[to], VERB.follow, prev);
            }
        });
        saved.filter(function (t) {
            return wanted.indexOf(t.to) < 0;
        }).forEach(function (t) {
            removeTree(out, t);
        });
        if (grp && wanted.length === 0) {
            removeTree(out, grp);
        }
        return names;
    }

    function tierFields() {
        const result = [];
        SPEC.forEach(function (g) {
            g.fields.forEach(function (f) {
                if (f.tier) {
                    result.push(f);
                }
            });
        });
        return result;
    }

    /* the tier of an own column as the triple from the column item to the tier e.g. mayor */
    function writeTier(out, itemName, tier, tiers) {
        const savedTier = TRIPLES.find(function (t) {
            return t.from === itemName && t.verb === VERB.has && tiers.some(function (f) {
                return f.tier === t.to;
            });
        });
        if (!savedTier || savedTier.to !== tier) {
            if (savedTier) {
                pushDelete(out, "triple", savedTier.id);
            }
            addTriple(out, itemName + " " + tier, itemName, VERB.has, tier);
        }
    }

    /* the own columns of all tiers as one ordered list of the column keyword, a column in the first tier that lists it */
    function writeColumns(out) {
        const tiers = tierFields();
        const wanted = [];
        const tierOf = {};
        tiers.forEach(function (f) {
            parseList(get(f.key)).forEach(function (col) {
                if (!(col in tierOf)) {
                    wanted.push(col);
                    tierOf[col] = f.tier;
                }
            });
        });
        const names = writeList(out, COLUMN_KEYWORD, phraseName + " " + COLUMN_KEYWORD, wanted);
        wanted.forEach(function (col) {
            writeTier(out, names[col], tierOf[col], tiers);
        });
    }

    function writeFlag(out, f, on) {
        const trp = paramTriple(f);
        if (on && !trp) {
            addTriple(out, paramName(f), phraseName, VERB.has, keywordOf(f));
        } else if (!on && trp) {
            removeTree(out, trp);
        }
    }

    function writeNumber(out, f, raw) {
        const kwd = keywordOf(f);
        const val = seedValue([phraseName, kwd]);
        const num = Number(raw);
        if (raw === "" || raw === null || raw === undefined) {
            if (val) {
                pushDelete(out, "value", val.id);
            }
        } else if (!isFinite(num)) {
            out.problems.push(txt(TXT.numberInvalid, {[VAR.name]: fieldLabel(f), [VAR.value]: raw}));
        } else if (val) {
            out.body.values.push({id: val.id, number: num});
        } else {
            out.body.values.push({words: [phraseName, kwd], number: num});
        }
    }

    function writeText(out, f, text) {
        const trp = paramTriple(f);
        if (text === "") {
            if (trp) {
                removeTree(out, trp);
            }
        } else {
            const name = trp ? trp.name : paramName(f);
            out.body.triples.push(tripleJson(trp ? trp.id : 0, name, text, phraseName, VERB.has, keywordOf(f)));
        }
    }

    /* the phrase of a select option e.g. English for en */
    function choicePhrase(f, val) {
        const opt = f.options.find(function (o) {
            return optionValue(o) === val;
        });
        let result = String(val);
        if (Array.isArray(opt)) {
            result = opt[1];
        }
        return result;
    }

    /* the phrases of a choice, without a double, because each is one triple from the parameter triple */
    function choicePhrases(f, val) {
        let result = [];
        if (f.type === "select") {
            result = [choicePhrase(f, val)];
        } else if (f.join && f.join.trim() !== "") {
            result = String(val).split(f.join.trim());
        } else if (f.join) {
            result = String(val).split(/\s+/);
        } else {
            result = [String(val)];
        }
        result = result.map(function (s) {
            return s.trim();
        }).filter(function (s, i, all) {
            return s !== "" && all.indexOf(s) === i;
        });
        return result;
    }

    function writeParam(out, f, val) {
        const form = formOf(f);
        if (form === FORM.flag) {
            writeFlag(out, f, Boolean(val));
        } else if (form === FORM.number) {
            writeNumber(out, f, val);
        } else if (form === FORM.choice) {
            writeList(out, keywordOf(f), paramName(f), choicePhrases(f, val));
        } else {
            writeText(out, f, String(val === null || val === undefined ? "" : val));
        }
    }

    /* the changes in the import format of the updatePut api; the pod saves the words, the triples, the values
       and then the removals, so a triple can use a new word and the new follower triple is added before the old one goes */
    function putBody() {
        const out = {
            body: {words: [], triples: [], formulas: [], values: [], delete: []},
            problems: [],
            deleted: new Set(),
            visited: new Set()
        };
        let columnsChanged = false;
        SPEC.forEach(function (g) {
            g.fields.forEach(function (f) {
                if (isChanged(g.group, f.key) && f.tier) {
                    columnsChanged = true;
                } else if (isChanged(g.group, f.key)) {
                    writeParam(out, f, view[g.group][f.key]);
                }
            });
        });
        if (columnsChanged) {
            writeColumns(out);
        }
        return out;
    }

    function putMessage(body) {
        const doc = SEED.doc;
        const result = {
            pod: doc.pod,
            type: UPDATE_PUT_TYPE,
            user_id: doc.user_id,
            user: doc.user,
            version: doc.version,
            timestamp: new Date().toISOString(),
            body: body
        };
        return result;
    }

    /* the body of an api answer; a message may come with or without the "body" header */
    function unwrap(json) {
        let result = json;
        if (json && !Array.isArray(json) && json.body !== undefined) {
            result = json.body;
        }
        return result;
    }

    /* every row that the pod has not confirmed (update_put::confirmed) and every warning of the pod; a row without id
       e.g. a column word is sent only to make sure that it exists, so an unchanged one is confirmed */
    function unconfirmed(body) {
        const lines = [];
        ["words", "triples", "values"].forEach(function (section) {
            (body[section] || []).forEach(function (o) {
                if (!o.action || (o.action === ACTION_UNCHANGED && o.id !== undefined)) {
                    lines.push((o.name || section + " " + o.id) + ": " + (o.action || txt(TXT.notConfirmed)));
                }
            });
        });
        (body.delete || []).forEach(function (d) {
            if (d.action !== ACTION_REMOVED) {
                lines.push(d.class + " " + d.id + ": " + (d.action || txt(TXT.notConfirmed)));
            }
        });
        if (body.warnings) {
            lines.push(String(body.warnings));
        }
        const confirmed = ["words", "triples", "values", "delete"].some(function (section) {
            return (body[section] || []).length > 0;
        });
        if (!confirmed && !body.warnings) {
            lines.push(txt(TXT.saveNotConfirmed));
        }
        return lines;
    }

    /* the reason why the pod has refused the answer, the json of the pod or the status of the answer */
    function refusal(resp, json) {
        let result = "";
        if (json && !Array.isArray(json) && json.message) {
            result = String(json.message);
        } else if (!resp.ok) {
            result = resp.status + " " + resp.statusText;
        } else {
            result = unconfirmed(unwrap(json) || {}).join("; ");
        }
        return result;
    }

    function parseAnswer(text) {
        let result = null;
        try {
            result = text ? JSON.parse(text) : null;
        } catch (e) {
            result = null;
        }
        return result;
    }

    /* one call that saves all changes with the session token of the page; never throws, the text of a problem or empty */
    async function sendPut(message) {
        const token = document.querySelector('input[name="' + SESSION_TOKEN_NAME + '"]');
        const headers = {"Content-Type": "application/json", Accept: "application/json"};
        if (token) {
            headers[CSRF_HEADER] = token.value;
        }
        let result = "";
        try {
            const resp = await fetch(URLS.put, {method: "POST", credentials: "same-origin", headers: headers, body: JSON.stringify(message)});
            const reason = refusal(resp, parseAnswer(await resp.text()));
            result = reason === "" ? "" : txt(TXT.refused) + " " + reason;
        } catch (e) {
            result = txt(TXT.unreachable) + " (" + e.message + ")";
        }
        return result;
    }

    /* the pod stops at the first problem, so after a refusal the edits stay for a fix; after a save the page
       reads the saved triples with their ids again */
    async function save() {
        const out = putBody();
        if (out.problems.length > 0) {
            notice(escapeHtml(out.problems.join(" ")), "err");
        } else if (URLS.put && changeCount() > 0 && !saving) {
            saving = true;
            paintBar();
            const problem = await sendPut(putMessage(out.body));
            saving = false;
            if (problem === "") {
                location.reload();
            } else {
                paintBar();
                notice(escapeHtml(problem), "err");
            }
        }
    }

    /* ---------- rail (parameter form) ---------- */
    function buildRail() {
        const rail = document.getElementById("rail");
        const parts = [];
        parts.push('<div class="toolbar">' + buttonHtml("btnExpandAll", TXT.openGroups, "", "small")
            + '<span class="note">' + txtHtml(TXT.railNote) + "</span></div>");
        SPEC.forEach(function (g, idx) {
            const open = idx < 3 ? " open" : "";
            parts.push('<details class="group" data-group="' + g.group + '"' + open + "><summary>" + escapeHtml(groupTitle(g))
                + '<span class="count">' + g.fields.length + '</span></summary><div class="fields">');
            g.fields.forEach(function (f) {
                parts.push(fieldHtml(f));
            });
            parts.push("</div></details>");
        });
        rail.innerHTML = parts.join("");
        rail.addEventListener("input", onFieldChange);
        rail.addEventListener("change", onFieldChange);
    }

    function tokensHtml(f, id) {
        const result = '<div class="tokens" id="' + id + '" data-tokens="' + f.key + '">'
            + '<div class="chips" role="list" data-empty="' + txtHtml(TXT.tokenEmpty) + '"></div>'
            + '<div class="picker"><input type="text" placeholder="' + txtHtml(TXT.tokenPlaceholder) + '" aria-label="'
            + txtHtml(TXT.tokenFind, {[VAR.name]: fieldLabel(f)}) + '" autocomplete="off"><div class="suggest" hidden></div></div>'
            + '<span class="hint">' + txtHtml(TXT.tokenHint) + "</span>"
            + "</div>";
        return result;
    }

    function fieldHtml(f) {
        const id = "p_" + f.key;
        const hint = fieldHint(f) ? " · " + fieldHint(f) : "";
        const data = ' id="' + id + '" data-key="' + f.key + '"';
        let control = "";
        if (f.type === "select") {
            const opts = f.options.map(function (o) {
                return '<option value="' + escapeHtml(optionValue(o)) + '">' + escapeHtml(optionLabel(o)) + "</option>";
            }).join("");
            control = "<select" + data + ">" + opts + "</select>";
        } else if (f.type === "check") {
            control = '<input type="checkbox"' + data + ">";
        } else if (f.type === "number") {
            const min = f.min !== undefined ? ' min="' + f.min + '"' : "";
            const max = f.max !== undefined ? ' max="' + f.max + '"' : "";
            control = '<input type="number"' + data + min + max + ">";
        } else if (f.type === "textarea") {
            control = "<textarea" + data + "></textarea>";
        } else if (f.type === "tokens") {
            control = tokensHtml(f, id);
        } else {
            control = '<input type="text"' + data + ">";
        }
        const cls = f.wide || f.type === "textarea" ? "field wide" : "field";
        const result = '<div class="' + cls + '"><label for="' + id + '">' + escapeHtml(fieldLabel(f))
            + "<code>" + escapeHtml(f.key + hint) + "</code></label>" + control + "</div>";
        return result;
    }

    function syncForm() {
        SPEC.forEach(function (g) {
            g.fields.forEach(function (f) {
                const el = document.getElementById("p_" + f.key);
                const val = view[g.group][f.key];
                if (f.type === "check") {
                    el.checked = !!val;
                } else if (f.type === "tokens") {
                    paintChips(el, parseList(val));
                } else {
                    el.value = val === null || val === undefined ? "" : String(val);
                }
            });
        });
    }

    /* ---------- token fields ---------- */
    function paintChips(box, list) {
        const chips = box.querySelector(".chips");
        const parts = [];
        list.forEach(function (name, i) {
            parts.push('<span class="chip-tok" role="listitem" tabindex="0" draggable="true" data-idx="' + i + '" data-name="' + escapeHtml(name) + '">'
                + '<span class="grip" aria-hidden="true">::</span>'
                + escapeHtml(name)
                + '<button type="button" class="rm" data-rm="' + i + '" aria-label="' + txtHtml(TXT.tokenRemove, {[VAR.name]: name}) + '">×</button>'
                + "</span>");
        });
        chips.innerHTML = parts.join("");
    }

    function tokenList(key) {
        const result = parseList(get(key));
        return result;
    }

    /* the columns of every tier, because a column is one item of the own columns with one tier */
    function usedColumns() {
        let result = [];
        tierFields().forEach(function (f) {
            result = result.concat(tokenList(f.key));
        });
        return result;
    }

    function setTokens(key, list) {
        set(key, list);
        const box = document.getElementById("p_" + key);
        paintChips(box, list);
        page = 1;
        render();
    }

    function moveToken(key, from, to) {
        const list = tokenList(key);
        if (from !== to && from >= 0 && from < list.length && to >= 0 && to <= list.length) {
            const item = list.splice(from, 1)[0];
            const target = to > from ? to - 1 : to;
            list.splice(target, 0, item);
            setTokens(key, list);
        }
    }

    function phraseMatches(pattern, exclude) {
        const p = pattern.trim().toLowerCase();
        const result = COLUMN_PHRASES.filter(function (ph) {
            const notUsed = exclude.indexOf(ph.name) < 0;
            const hit = p === "" || ph.name.toLowerCase().indexOf(p) >= 0 || ph.description.toLowerCase().indexOf(p) >= 0;
            return notUsed && hit;
        }).slice(0, 8);
        return result;
    }

    function highlight(text, pattern) {
        let result = escapeHtml(text);
        const p = pattern.trim();
        if (p) {
            const i = text.toLowerCase().indexOf(p.toLowerCase());
            if (i >= 0) {
                result = escapeHtml(text.slice(0, i)) + "<mark>" + escapeHtml(text.slice(i, i + p.length)) + "</mark>" + escapeHtml(text.slice(i + p.length));
            }
        }
        return result;
    }

    function paintSuggest(box) {
        const input = box.querySelector(".picker input");
        const list = box.querySelector(".suggest");
        const matches = phraseMatches(input.value, usedColumns());
        const parts = [];
        if (matches.length === 0) {
            parts.push('<div class="opt none">' + txtHtml(TXT.noMatch, {[VAR.name]: input.value}) + "</div>");
        } else {
            matches.forEach(function (ph, i) {
                parts.push('<div class="opt' + (i === 0 ? " active" : "") + '" data-add="' + escapeHtml(ph.name) + '">'
                    + "<span>" + highlight(ph.name, input.value) + '</span><span class="desc">' + highlight(ph.description, input.value) + "</span></div>");
            });
        }
        list.innerHTML = parts.join("");
        list.hidden = false;
    }

    function addToken(key, name) {
        const list = tokenList(key);
        if (name && usedColumns().indexOf(name) < 0) {
            list.push(name);
            setTokens(key, list);
        }
    }

    function onTokenClick(ev) {
        const box = ev.target.closest(".tokens");
        if (box) {
            const key = box.dataset.tokens;
            const rm = ev.target.closest("[data-rm]");
            const add = ev.target.closest("[data-add]");
            if (rm) {
                const list = tokenList(key);
                list.splice(Number(rm.dataset.rm), 1);
                setTokens(key, list);
            } else if (add) {
                addToken(key, add.dataset.add);
                const input = box.querySelector(".picker input");
                input.value = "";
                box.querySelector(".suggest").hidden = true;
                input.focus();
            }
        }
    }

    function onPickerKey(ev, box, key) {
        const list = box.querySelector(".suggest");
        const opts = Array.prototype.slice.call(list.querySelectorAll(".opt[data-add]"));
        let active = opts.findIndex(function (o) {
            return o.classList.contains("active");
        });
        if (ev.key === "ArrowDown" || ev.key === "ArrowUp") {
            ev.preventDefault();
            if (opts.length > 0) {
                opts.forEach(function (o) {
                    o.classList.remove("active");
                });
                active = ev.key === "ArrowDown" ? (active + 1) % opts.length : (active - 1 + opts.length) % opts.length;
                opts[active].classList.add("active");
                opts[active].scrollIntoView({block: "nearest"});
            }
        } else if (ev.key === "Enter") {
            ev.preventDefault();
            if (active >= 0 && opts[active]) {
                addToken(key, opts[active].dataset.add);
                ev.target.value = "";
                paintSuggest(box);
            }
        } else if (ev.key === "Escape") {
            list.hidden = true;
        } else if (ev.key === "Backspace" && ev.target.value === "") {
            const current = tokenList(key);
            if (current.length > 0) {
                current.pop();
                setTokens(key, current);
                paintSuggest(box);
            }
        }
    }

    function onChipKey(ev, box, key) {
        const idx = Number(ev.target.dataset.idx);
        const list = tokenList(key);
        if (ev.key === "ArrowLeft" && idx > 0) {
            ev.preventDefault();
            moveToken(key, idx, idx - 1);
            focusChip(box, idx - 1);
        } else if (ev.key === "ArrowRight" && idx < list.length - 1) {
            ev.preventDefault();
            moveToken(key, idx, idx + 2);
            focusChip(box, idx + 1);
        } else if (ev.key === "Delete" || ev.key === "Backspace") {
            ev.preventDefault();
            list.splice(idx, 1);
            setTokens(key, list);
            focusChip(box, Math.min(idx, list.length - 1));
        }
    }

    function onTokenKey(ev) {
        const box = ev.target.closest(".tokens");
        if (box && ev.target.matches(".picker input")) {
            onPickerKey(ev, box, box.dataset.tokens);
        } else if (box && ev.target.matches(".chip-tok")) {
            onChipKey(ev, box, box.dataset.tokens);
        }
    }

    /* the position before or after the chip under the mouse */
    function dropBefore(chip, ev) {
        const rect = chip.getBoundingClientRect();
        const result = ev.clientX < rect.left + rect.width / 2;
        return result;
    }

    function wireTokenFields() {
        const rail = document.getElementById("rail");
        const drag = {key: null, from: -1};

        rail.addEventListener("click", onTokenClick);
        rail.addEventListener("focusin", function (ev) {
            const box = ev.target.closest(".tokens");
            if (box && ev.target.matches(".picker input")) {
                paintSuggest(box);
            }
        });
        rail.addEventListener("focusout", function (ev) {
            const box = ev.target.closest(".tokens");
            if (box && ev.target.matches(".picker input")) {
                setTimeout(function () {
                    if (!box.contains(document.activeElement)) {
                        box.querySelector(".suggest").hidden = true;
                    }
                }, 120);
            }
        });
        rail.addEventListener("input", function (ev) {
            const box = ev.target.closest(".tokens");
            if (box && ev.target.matches(".picker input")) {
                paintSuggest(box);
            }
        });
        rail.addEventListener("keydown", onTokenKey);

        rail.addEventListener("dragstart", function (ev) {
            const chip = ev.target.closest(".chip-tok");
            if (chip) {
                drag.key = chip.closest(".tokens").dataset.tokens;
                drag.from = Number(chip.dataset.idx);
                chip.classList.add("dragging");
                ev.dataTransfer.effectAllowed = "move";
                ev.dataTransfer.setData("text/plain", chip.dataset.name);
            }
        });
        rail.addEventListener("dragover", function (ev) {
            const chip = ev.target.closest(".chip-tok");
            const box = ev.target.closest(".tokens");
            if (box && box.dataset.tokens === drag.key) {
                ev.preventDefault();
                ev.dataTransfer.dropEffect = "move";
                box.querySelectorAll(".chip-tok").forEach(function (c) {
                    c.classList.remove("drop-before", "drop-after");
                });
                if (chip) {
                    chip.classList.add(dropBefore(chip, ev) ? "drop-before" : "drop-after");
                }
            }
        });
        rail.addEventListener("drop", function (ev) {
            const chip = ev.target.closest(".chip-tok");
            const box = ev.target.closest(".tokens");
            if (box && box.dataset.tokens === drag.key) {
                ev.preventDefault();
                let to = tokenList(drag.key).length;
                if (chip) {
                    to = Number(chip.dataset.idx) + (dropBefore(chip, ev) ? 0 : 1);
                }
                moveToken(drag.key, drag.from, to);
            }
            drag.key = null;
            drag.from = -1;
        });
        rail.addEventListener("dragend", function () {
            rail.querySelectorAll(".chip-tok").forEach(function (c) {
                c.classList.remove("dragging", "drop-before", "drop-after");
            });
            drag.key = null;
            drag.from = -1;
        });
    }

    function focusChip(box, idx) {
        const chip = box.querySelector('.chip-tok[data-idx="' + idx + '"]');
        if (chip) {
            chip.focus();
        }
    }

    function onFieldChange(ev) {
        const el = ev.target;
        const key = el.dataset.key;
        if (key) {
            let val = el.value;
            if (el.type === "checkbox") {
                val = el.checked;
            } else if (el.type === "number") {
                val = el.value === "" ? "" : Number(el.value);
            }
            set(key, val);
            if (key === "hierarchy_expand" || key === "hierarchy_max_depth") {
                expandRule = "";
            }
            if (key !== "row_page_size" && key !== "row_show_all") {
                page = 1;
            }
            render();
        }
    }

    /* ---------- helpers ---------- */
    function parseList(text) {
        let result = [];
        if (Array.isArray(text)) {
            result = text.map(function (s) {
                return String(s).trim();
            }).filter(function (s) {
                return s.length > 0;
            });
        } else {
            result = String(text || "").split(",").map(function (s) {
                return s.trim();
            }).filter(function (s) {
                return s.length > 0;
            });
        }
        return result;
    }

    function localeFor(lang) {
        let result = "en-US";
        if (lang === "de") {
            result = "de-CH";
        } else if (lang === "fr") {
            result = "fr-CH";
        }
        return result;
    }

    function isYear(col) {
        const result = /^\d{4}$/.test(col);
        return result;
    }

    function isNumeric(col) {
        const result = isYear(col) || col === "growth" || col === "share" || col === "total";
        return result;
    }

    function scaled(n) {
        const factor = Number(view.values.value_scale) || 1;
        const result = n * factor;
        return result;
    }

    /* the digits after the point and the thousands separator of the number format e.g. #,##0.0 */
    function numberFormat() {
        const pattern = String(view.values.value_format || "0");
        const point = pattern.indexOf(".");
        const decimals = point < 0 ? 0 : pattern.slice(point + 1).replace(/[^0#]/g, "").length;
        const result = {decimals: decimals, grouping: pattern.indexOf(",") >= 0};
        return result;
    }

    /* the column and the direction of the sort order e.g. growth desc */
    function rowOrder() {
        const text = String(view.rows.row_order || "").trim();
        const parts = text.match(/^(.*?)\s+(asc|desc)$/);
        const result = parts ? {col: parts[1], dir: parts[2]} : {col: text, dir: "asc"};
        return result;
    }

    function fmtNumber(n, decimalsOverride) {
        const format = numberFormat();
        const dec = decimalsOverride !== undefined ? decimalsOverride : format.decimals;
        const opts = {minimumFractionDigits: dec, maximumFractionDigits: dec, useGrouping: format.grouping};
        const result = new Intl.NumberFormat(localeFor(view.context.language), opts).format(n);
        return result;
    }

    function fmtPercent(n) {
        const result = fmtNumber(n * 100, 1) + " %";
        return result;
    }

    function missingText() {
        const mode = view.values.value_missing;
        let result = "–";
        if (mode === "blank") {
            result = "";
        } else if (mode === "n/a") {
            result = "n/a";
        } else if (mode === "zero") {
            result = fmtNumber(0);
        }
        return result;
    }

    function escapeHtml(s) {
        const result = String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        return result;
    }

    /* an assumed value is named by its word, a measured one has no word, so its label is a text */
    function confidenceLabel(p) {
        const result = p.assumed ? WORD.assumed : txt(TXT.measured);
        return result;
    }

    /* ---------- column selection ---------- */
    function activeColumns() {
        const lv = view.columns.column_level;
        let cols = parseList(view.columns.column_mayor);
        if (lv === "main" || lv === "minor" || lv === "all") {
            cols = cols.concat(parseList(view.columns.column_main));
        }
        if (lv === "minor" || lv === "all") {
            cols = cols.concat(parseList(view.columns.column_minor));
        }
        if (lv === "all") {
            cols = cols.concat(parseList(view.columns.column_marginal));
        }
        if (view.values.value_show_source === "column" && cols.indexOf("source") < 0) {
            cols.push("source");
        }
        if (view.values.value_show_confidence && cols.indexOf(WORD.confidence) < 0) {
            cols.push(WORD.confidence);
        }
        cols = cols.filter(function (c, i) {
            return cols.indexOf(c) === i;
        });
        const rule = view.columns.column_order_rule;
        if (rule === "alphabetical") {
            cols.sort();
        } else if (rule === "by value count") {
            cols.sort(function (a, b) {
                return valueCount(b) - valueCount(a);
            });
        }
        if (view.aggregation.totals_column) {
            cols.push("total");
        }
        return cols;
    }

    function valueCount(col) {
        let result = 0;
        DATA.phrases.forEach(function (p) {
            if (cellValue(p, col) !== null) {
                result += 1;
            }
        });
        return result;
    }

    /* ---------- row model ---------- */
    function childrenOf(id) {
        const result = DATA.phrases.filter(function (p) {
            return p.parent === id;
        });
        return result;
    }

    function sumChildren(p, year) {
        let result = null;
        childrenOf(p.id).forEach(function (c) {
            const v = cellRaw(c, year);
            if (v !== null) {
                result = (result || 0) + v;
            }
        });
        return result;
    }

    /* raw stored value for a year, before the parent rule */
    function cellRaw(p, year) {
        const v = p.values[year];
        const result = v === null || v === undefined ? null : v;
        return result;
    }

    /* the value shown in a year cell, after the parent rule */
    function yearValue(p, year) {
        const mode = view.hierarchy.hierarchy_parent_value;
        const hasKids = childrenOf(p.id).length > 0;
        let result = cellRaw(p, year);
        if (hasKids && mode === "sum of children") {
            result = sumChildren(p, year);
        } else if (hasKids && mode === "blank") {
            result = null;
        }
        return result;
    }

    function latestYear() {
        const years = DATA.years;
        const result = years[years.length - 1];
        return result;
    }

    function cellValue(p, col) {
        let result = null;
        if (isYear(col)) {
            result = yearValue(p, col);
        } else if (col === "growth") {
            const y = latestYear();
            const a = yearValue(p, String(Number(y) - 1));
            const b = yearValue(p, y);
            if (a !== null && b !== null && a !== 0) {
                result = (b - a) / a;
            }
        } else if (col === "share") {
            const y = latestYear();
            const total = topLevelTotal(y);
            const v = yearValue(p, y);
            if (v !== null && total) {
                result = v / total;
            }
        } else if (col === "source") {
            result = p.source;
        } else if (col === WORD.confidence) {
            result = confidenceLabel(p) + " (" + p.conf.toFixed(1) + ")";
        } else if (col === "total") {
            result = rowTotal(p);
        }
        return result;
    }

    function rowTotal(p) {
        let result = null;
        activeColumns().forEach(function (c) {
            if (isYear(c)) {
                const v = yearValue(p, c);
                if (v !== null) {
                    result = (result || 0) + v;
                }
            }
        });
        return result;
    }

    function topLevelTotal(year) {
        let result = 0;
        DATA.phrases.filter(function (p) {
            return p.parent === null;
        }).forEach(function (p) {
            const v = cellRaw(p, year);
            if (v !== null) {
                result += v;
            }
        });
        return result;
    }

    function rowIsEmpty(p, cols) {
        const result = !cols.some(function (c) {
            return isYear(c) && yearValue(p, c) !== null;
        });
        return result;
    }

    function applyExpandRule() {
        const rule = view.hierarchy.hierarchy_expand + "|" + view.hierarchy.hierarchy_max_depth;
        if (rule !== expandRule) {
            expanded = new Set();
            const mode = view.hierarchy.hierarchy_expand;
            if (mode === "expanded") {
                DATA.phrases.forEach(function (p) {
                    expanded.add(p.id);
                });
            } else if (mode === "level 1") {
                DATA.phrases.filter(function (p) {
                    return p.parent === null;
                }).forEach(function (p) {
                    expanded.add(p.id);
                });
            }
            expandRule = rule;
        }
    }

    function compareCells(va, vb, dir) {
        let result = 0;
        if (va === null && vb !== null) {
            result = 1;
        } else if (vb === null && va !== null) {
            result = -1;
        } else if (typeof va === "number" && typeof vb === "number") {
            result = (va - vb) * dir;
        } else {
            result = String(va).localeCompare(String(vb)) * dir;
        }
        return result;
    }

    /* the pinned rows first in their listed order, the others after them */
    function pinRank(pinned, id) {
        const pos = pinned.indexOf(id);
        const result = pos < 0 ? pinned.length : pos;
        return result;
    }

    function sortRows(list, cols) {
        const order = rowOrder();
        const dir = order.dir === "desc" ? -1 : 1;
        const pinned = parseList(view.rows.row_pinned);
        const result = list.slice();
        if (order.col && cols.indexOf(order.col) >= 0) {
            result.sort(function (a, b) {
                return compareCells(cellValue(a, order.col), cellValue(b, order.col), dir);
            });
        }
        if (pinned.length > 0) {
            result.sort(function (a, b) {
                return pinRank(pinned, a.id) - pinRank(pinned, b.id);
            });
        }
        return result;
    }

    /* flatten the tree according to hierarchy and filter settings */
    function visibleRows(cols) {
        applyExpandRule();
        const maxDepth = Number(view.hierarchy.hierarchy_max_depth);
        const orphanMode = view.hierarchy.hierarchy_orphans;
        const minVal = view.rows.row_filter;
        const firstValueCol = cols.filter(isYear)[0] || cols.filter(isNumeric)[0];
        const search = searchText.trim().toLowerCase();
        let result = [];

        function passes(p) {
            let ok = true;
            if (minVal !== "" && minVal !== null && firstValueCol) {
                const v = cellValue(p, firstValueCol);
                if (v === null || v < Number(minVal)) {
                    ok = false;
                }
            }
            if (search && (p.name + " " + p.description).toLowerCase().indexOf(search) < 0) {
                ok = false;
            }
            if (view.rows.row_empty === "hide" && rowIsEmpty(p, cols)) {
                ok = false;
            }
            return ok;
        }

        function walk(parentId, depth) {
            const kids = sortRows(childrenOf(parentId).filter(function (p) {
                return !p.orphan;
            }), cols);
            kids.forEach(function (p) {
                if (passes(p)) {
                    const hasKids = childrenOf(p.id).length > 0 && depth < maxDepth;
                    result.push({p: p, depth: depth, hasKids: hasKids, open: hasKids && expanded.has(p.id)});
                    if (hasKids && expanded.has(p.id)) {
                        walk(p.id, depth + 1);
                    }
                }
            });
        }

        if (view.hierarchy.hierarchy_sort_scope === "flat") {
            sortRows(DATA.phrases.filter(function (p) {
                return !p.orphan;
            }), cols).forEach(function (p) {
                if (passes(p)) {
                    result.push({p: p, depth: 0, hasKids: false, open: false, flat: true});
                }
            });
        } else {
            walk(null, 0);
        }

        const orphans = DATA.phrases.filter(function (p) {
            return p.orphan && passes(p);
        }).map(function (p) {
            return {p: p, depth: 0, hasKids: false, open: false, orphan: true};
        });
        if (orphanMode === "top") {
            result = orphans.concat(result);
        } else if (orphanMode === "bottom") {
            result = result.concat(orphans);
        }
        return result;
    }

    /* ---------- cell rendering ---------- */
    function rowLabel(p) {
        const mode = view.rows.row_label_mode;
        let result = escapeHtml(p.name);
        if (mode === "abbreviation") {
            result = escapeHtml(p.abbr);
        } else if (mode === "id") {
            result = escapeHtml(p.id);
        } else if (mode === "name + description") {
            result = escapeHtml(p.name) + ' <span class="chip">' + escapeHtml(p.description) + "</span>";
        }
        return result;
    }

    function alignClass(col) {
        const a = view.values.value_align;
        let result = "";
        if (a === "auto") {
            result = isNumeric(col) ? "num" : "";
        } else if (a === "right") {
            result = "num";
        } else if (a === "center") {
            result = "center";
        }
        return result;
    }

    function deltaHtml(p, year) {
        const mode = view.values.value_delta;
        let result = "";
        if (mode !== "none") {
            const a = yearValue(p, String(Number(year) - 1));
            const b = yearValue(p, year);
            if (a !== null && b !== null) {
                const diff = b - a;
                const cls = diff > 0 ? "up" : (diff < 0 ? "down" : "");
                const sign = diff > 0 ? "+" : "";
                let text = sign + fmtNumber(scaled(diff));
                if (mode === "percent" && a !== 0) {
                    text = sign + fmtPercent(diff / a);
                }
                result = '<span class="delta ' + cls + '">' + text + "</span>";
            }
        }
        return result;
    }

    function isPercentColumn(col) {
        const result = col === "growth" || col === "share";
        return result;
    }

    /* the text of a cell for the csv */
    function cellText(v, col) {
        let result = String(v);
        if (v === null) {
            result = missingText();
        } else if (typeof v === "number" && isPercentColumn(col)) {
            result = fmtPercent(v);
        } else if (typeof v === "number") {
            result = fmtNumber(scaled(v));
        }
        return result;
    }

    /* the number of a cell with its delta and the sum of the sub-rows */
    function numberHtml(p, col, v) {
        let result = fmtNumber(scaled(v));
        if (isYear(col)) {
            result += deltaHtml(p, col);
            if (view.hierarchy.hierarchy_parent_value === "both" && childrenOf(p.id).length > 0) {
                const s = sumChildren(p, col);
                if (s !== null) {
                    result += '<span class="delta" title="' + txtHtml(TXT.sumTip) + '">Σ ' + fmtNumber(scaled(s)) + "</span>";
                }
            }
        }
        return result;
    }

    function cellHtml(p, col) {
        const v = cellValue(p, col);
        const hi = view.values.value_conditional_style;
        let cls = alignClass(col);
        let title = "";
        let html = "";
        if (v === null) {
            html = escapeHtml(missingText());
            cls += " missing";
        } else if (isPercentColumn(col)) {
            html = fmtPercent(v);
            if (col === "growth" && v < 0) {
                cls += " neg";
            }
        } else if (col === WORD.confidence) {
            html = '<span class="chip ' + (p.assumed ? "assumed" : "measured") + '">' + escapeHtml(v) + "</span>";
        } else if (col === "source") {
            html = escapeHtml(v);
        } else {
            html = numberHtml(p, col, v);
            if (hi !== "" && hi !== null && v >= Number(hi)) {
                cls += " hi";
            }
        }
        if (view.values.value_show_source === "tooltip" && (isYear(col) || col === "total")) {
            title = p.source + " · " + confidenceLabel(p) + " " + p.conf.toFixed(1);
        }
        const result = {html: html, cls: cls.trim(), title: title, text: cellText(v, col)};
        return result;
    }

    function headerText(col) {
        const short = view.columns.header_label === "short";
        let result = col;
        if (short && isYear(col)) {
            result = "'" + col.slice(2);
        } else if (short) {
            result = col.slice(0, 4);
        }
        if (col === "growth") {
            result = (short ? "Δ" : "growth") + " " + latestYear();
        } else if (col === "share") {
            result = (short ? "%" : "share") + " " + latestYear();
        } else if (col === "total") {
            result = txt(TXT.total);
        }
        return result;
    }

    function aggregate(rows, col) {
        const mode = view.aggregation.totals_row;
        const vals = [];
        rows.forEach(function (r) {
            const v = cellValue(r.p, col);
            if (typeof v === "number") {
                vals.push(v);
            }
        });
        let result = null;
        if (vals.length > 0) {
            const sum = vals.reduce(function (a, b) {
                return a + b;
            }, 0);
            if (mode === "sum") {
                result = sum;
            } else if (mode === "average") {
                result = sum / vals.length;
            } else if (mode === "count") {
                result = vals.length;
            }
        }
        return result;
    }

    function aggregateCell(rows, col) {
        const v = aggregate(rows, col);
        let html = "";
        if (v !== null && view.aggregation.totals_row === "count") {
            html = String(v);
        } else if (v !== null && isPercentColumn(col)) {
            html = fmtPercent(v);
        } else if (v !== null) {
            html = fmtNumber(scaled(v));
        }
        const result = {html: html, cls: alignClass(col), title: "", text: html};
        return result;
    }

    function emptyCell() {
        const result = {html: "", text: "", cls: ""};
        return result;
    }

    /* ---------- build matrix and render ---------- */
    /* the rows of the current page or up to the row limit, and the number of pages */
    function pageRows(allRows) {
        const limit = Number(view.rows.row_limit) || 50;
        const pageSize = Number(view.rows.row_page_size) || 10;
        let result = {rows: allRows.slice(0, limit), pageCount: 1};
        if (view.rows.row_show_all) {
            const pageCount = Math.max(1, Math.ceil(allRows.length / pageSize));
            page = Math.min(page, pageCount);
            result = {rows: allRows.slice((page - 1) * pageSize, page * pageSize), pageCount: pageCount};
        }
        return result;
    }

    function showLevelColumn() {
        const result = view.hierarchy.hierarchy_indent === "level column" && view.hierarchy.hierarchy_sort_scope !== "flat";
        return result;
    }

    function headRow(cols) {
        const labelCol = view.rows.row_phrase || txt(TXT.row);
        const head = [];
        if (view.interaction.selectable) {
            head.push({html: "", text: "", cls: "center", col: "__sel"});
        }
        head.push({html: escapeHtml(labelCol), text: labelCol, cls: "", col: "__label", sortable: true});
        if (showLevelColumn()) {
            head.push({html: txtHtml(TXT.level), text: txt(TXT.level), cls: "num", col: "__level"});
        }
        cols.forEach(function (c) {
            let h = escapeHtml(headerText(c));
            if (view.columns.header_show_unit && (isYear(c) || c === "total")) {
                h += '<span class="unit">' + escapeHtml(unitLabel()) + "</span>";
            }
            head.push({html: h, text: headerText(c), cls: alignClass(c), col: c, sortable: isNumeric(c) || c === "source"});
        });
        return head;
    }

    function bodyRow(r, cols) {
        const cells = [];
        const rowCls = [];
        if (r.hasKids) {
            rowCls.push("parent");
        }
        if (selected.has(r.p.id)) {
            rowCls.push("selected");
        }
        if (view.rows.row_empty === "grey" && rowIsEmpty(r.p, cols)) {
            rowCls.push("subtotal");
        }
        if (view.interaction.selectable) {
            const chk = selected.has(r.p.id) ? " checked" : "";
            cells.push({
                html: '<input type="checkbox" data-sel="' + escapeHtml(r.p.id) + '"' + chk + ' aria-label="' + txtHtml(TXT.selectRow, {[VAR.name]: r.p.name}) + '">',
                text: selected.has(r.p.id) ? "x" : "",
                cls: "center"
            });
        }
        cells.push({html: labelHtml(r), text: indentText(r) + r.p.name, cls: "label"});
        if (showLevelColumn()) {
            cells.push({html: String(r.depth), text: String(r.depth), cls: "num"});
        }
        cols.forEach(function (c) {
            cells.push(cellHtml(r.p, c));
        });
        const result = {cells: cells, cls: rowCls.join(" "), id: r.p.id};
        return result;
    }

    /* the subtotal row of the group of sub-rows that the row closes, null if it closes none */
    function subtotalRow(rows, i, cols) {
        const r = rows[i];
        const nextRow = rows[i + 1];
        const closesGroup = r.depth > 0 && (!nextRow || nextRow.depth < r.depth);
        const parentRow = closesGroup ? findParentRow(rows, i) : null;
        let result = null;
        if (view.aggregation.subtotals && !view.rows.row_show_all && parentRow) {
            const kids = rows.filter(function (x) {
                return x.p.parent === parentRow.p.id;
            });
            const cells = [];
            if (view.interaction.selectable) {
                cells.push(emptyCell());
            }
            cells.push({html: txtHtml(TXT.subtotal, {[VAR.name]: parentRow.p.name}), text: txt(TXT.subtotal, {[VAR.name]: parentRow.p.name}), cls: "label"});
            if (showLevelColumn()) {
                cells.push(emptyCell());
            }
            cols.forEach(function (c) {
                cells.push(aggregateCell(kids, c));
            });
            result = {cells: cells, cls: "subtotal", id: ""};
        }
        return result;
    }

    function footRow(rows, allRows, cols) {
        let result = null;
        if (view.aggregation.totals_row !== "none") {
            const scope = view.aggregation.aggregation_scope === "visible page" ? rows : allRows;
            const tops = scope.filter(function (r) {
                return r.depth === 0 || r.flat;
            });
            const label = view.aggregation.totals_row === "count" ? txt(TXT.count) : txt(TXT.total);
            result = [];
            if (view.interaction.selectable) {
                result.push(emptyCell());
            }
            result.push({html: escapeHtml(label), text: label, cls: "label"});
            if (showLevelColumn()) {
                result.push(emptyCell());
            }
            cols.forEach(function (c) {
                result.push(aggregateCell(tops, c));
            });
        }
        return result;
    }

    function render() {
        const cols = activeColumns();
        const allRows = visibleRows(cols);
        const paged = pageRows(allRows);
        const rows = paged.rows;
        const body = [];
        rows.forEach(function (r, i) {
            body.push(bodyRow(r, cols));
            const sub = subtotalRow(rows, i, cols);
            if (sub) {
                body.push(sub);
            }
        });
        let matrix = {head: headRow(cols), body: body, foot: footRow(rows, allRows, cols), transposed: false};
        if (view.layout.transpose) {
            matrix = transposeMatrix(matrix);
        }
        lastMatrix = matrix;
        paintTable(matrix);
        paintChrome(allRows.length, rows.length, paged.pageCount);
        paintJson();
        paintCsv();
        paintBar();
    }

    function unitLabel() {
        const scale = view.values.value_scale;
        let result = view.values.value_unit;
        if (scale === "0.001") {
            result = result.replace("million", "billion");
        } else if (scale === "1000") {
            result = result.replace("million", "thousand");
        }
        return result;
    }

    /* the nearest row above with a smaller depth */
    function findParentRow(rows, i) {
        let k = i;
        while (k >= 0 && rows[k].depth >= rows[i].depth) {
            k -= 1;
        }
        const result = k >= 0 ? rows[k] : null;
        return result;
    }

    function indentText(r) {
        const result = r.depth > 0 ? "  ".repeat(r.depth) : "";
        return result;
    }

    function labelHtml(r) {
        const style = view.hierarchy.hierarchy_indent;
        let prefix = "";
        if (style === "indent" && r.depth > 0) {
            prefix = '<span class="indent" style="width:' + (r.depth * 18) + 'px"></span>';
        } else if (style === "tree lines" && r.depth > 0) {
            prefix = '<span class="tree">' + "│ ".repeat(r.depth - 1) + "├─ </span>";
        }
        let toggle = "";
        if (!r.flat && !r.orphan) {
            const sign = r.hasKids ? (r.open ? "−" : "+") : "";
            const leaf = r.hasKids ? "" : " leaf";
            toggle = '<span class="toggle' + leaf + '" data-toggle="' + escapeHtml(r.p.id) + '" title="' + txtHtml(TXT.toggleTip) + '">' + sign + "</span>";
        }
        const result = prefix + toggle + rowLabel(r.p);
        return result;
    }

    function transposeMatrix(m) {
        const n = m.head.length;
        const newHead = [{html: "", text: "", cls: "", col: "__label"}];
        m.body.forEach(function (row) {
            newHead.push({html: row.cells[0].html, text: row.cells[0].text, cls: "", col: row.id});
        });
        if (m.foot) {
            newHead.push({html: m.foot[0].html, text: m.foot[0].text, cls: "", col: "__total"});
        }
        const newBody = [];
        for (let c = 1; c < n; c += 1) {
            const cells = [{html: m.head[c].html, text: m.head[c].text, cls: "label"}];
            m.body.forEach(function (row) {
                cells.push(row.cells[c]);
            });
            if (m.foot) {
                cells.push(m.foot[c]);
            }
            newBody.push({cells: cells, cls: "", id: ""});
        }
        const result = {head: newHead, body: newBody, foot: null, transposed: true};
        return result;
    }

    function headCellHtml(h, i, m, frozen) {
        const rotate = view.columns.header_rotate && !m.transposed;
        const cls = [h.cls];
        let arrow = "";
        let attrs = "";
        if (i < frozen) {
            cls.push("frozen");
        }
        if (view.interaction.sortable && h.sortable && !m.transposed) {
            cls.push("sortable");
            attrs = ' data-sort="' + escapeHtml(h.col) + '"';
            if (rowOrder().col === h.col) {
                arrow = '<span class="arrow">' + (rowOrder().dir === "desc" ? "▼" : "▲") + "</span>";
            }
        }
        let inner = h.html + arrow;
        if (rotate && i >= 1) {
            cls.push("rotate");
            inner = "<div>" + inner + "</div>";
        }
        const result = '<th class="' + cls.join(" ") + '"' + attrs + ">" + inner + "</th>";
        return result;
    }

    function cellsHtml(cells, frozen) {
        const result = cells.map(function (c, i) {
            const cls = [c.cls || ""];
            if (i < frozen) {
                cls.push("frozen");
            }
            const title = c.title ? ' title="' + escapeHtml(c.title) + '"' : "";
            return '<td class="' + cls.join(" ").trim() + '"' + title + ">" + c.html + "</td>";
        }).join("");
        return result;
    }

    function paintTable(m) {
        const box = document.getElementById("tableBox");
        const frozen = Number(view.columns.column_frozen) || 0;
        const classes = ["density-" + view.layout.density, "borders-" + view.layout.borders];
        box.style.maxWidth = view.layout.max_width || "100%";
        if (m.body.length === 0) {
            box.innerHTML = '<div class="empty">' + txtHtml(TXT.noRows) + "</div>";
        } else {
            const parts = ['<table class="' + classes.join(" ") + '"><thead><tr>'];
            m.head.forEach(function (h, i) {
                parts.push(headCellHtml(h, i, m, frozen));
            });
            parts.push('</tr></thead><tbody class="' + (view.layout.striped ? "striped" : "") + '">');
            m.body.forEach(function (row) {
                parts.push('<tr class="' + row.cls + '" data-row="' + escapeHtml(row.id) + '">' + cellsHtml(row.cells, frozen) + "</tr>");
            });
            if (m.foot) {
                parts.push('<tr class="total">' + cellsHtml(m.foot, frozen) + "</tr>");
            }
            parts.push("</tbody></table>");
            box.innerHTML = parts.join("");
        }
    }

    function metaText(total, shown) {
        const bits = [txt(TXT.rowsShown, {[VAR.count]: shown, [VAR.value]: total})];
        bits.push(txt(TXT.metaLevel, {[VAR.value]: view.columns.column_level}));
        if (view.context.time_reference) {
            bits.push(txt(TXT.asOf, {[VAR.time]: view.context.time_reference}));
        }
        if (selected.size > 0) {
            bits.push(txt(TXT.selected, {[VAR.count]: selected.size}));
        }
        const result = bits.join(" · ");
        return result;
    }

    function controlsHtml() {
        const ctl = ['<div class="levels" role="group" aria-label="' + txtHtml(TXT.columnLevel) + '">'];
        SPEC[0].fields[0].options.forEach(function (lv) {
            ctl.push('<button type="button" data-level="' + escapeHtml(lv) + '" aria-pressed="' + (view.columns.column_level === lv) + '">' + escapeHtml(lv) + "</button>");
        });
        ctl.push("</div>");
        if (view.interaction.searchable) {
            ctl.push('<input type="search" id="searchBox" placeholder="' + txtHtml(TXT.searchRows) + '" value="' + escapeHtml(searchText) + '" aria-label="' + txtHtml(TXT.searchRows) + '">');
        }
        if (view.hierarchy.hierarchy_sort_scope !== "flat") {
            ctl.push('<button class="btn small" type="button" data-expand="all">' + txtHtml(TXT.expandAll) + "</button>");
            ctl.push('<button class="btn small" type="button" data-expand="none">' + txtHtml(TXT.collapseAll) + "</button>");
        }
        const result = ctl.join("");
        return result;
    }

    function pagerHtml(total, shown, pageCount) {
        let result = "";
        if (view.rows.row_show_all && pageCount > 1) {
            result = '<button class="btn small" type="button" data-page="prev"' + (page <= 1 ? " disabled" : "") + ">" + txtHtml(TXT.previous) + "</button>"
                + "<span>" + txtHtml(TXT.page, {[VAR.count]: page, [VAR.value]: pageCount}) + "</span>"
                + '<button class="btn small" type="button" data-page="next"' + (page >= pageCount ? " disabled" : "") + ">" + txtHtml(TXT.next) + "</button>";
        } else if (!view.rows.row_show_all && total > shown) {
            result = "<span>" + txtHtml(TXT.rowsHidden, {[VAR.count]: total - shown}) + "</span>";
        }
        return result;
    }

    function paintChrome(total, shown, pageCount) {
        document.getElementById("viewTitle").textContent = view.layout.title || view.rows.row_phrase;
        document.getElementById("viewCaption").textContent = view.layout.caption || "";
        document.getElementById("viewMeta").textContent = metaText(total, shown);
        document.getElementById("controls").innerHTML = controlsHtml();
        document.getElementById("pager").innerHTML = pagerHtml(total, shown, pageCount);
    }

    function paintJson() {
        const ta = document.getElementById("jsonText");
        if (document.activeElement !== ta) {
            ta.value = JSON.stringify(view, null, 2);
        }
    }

    function csvField(s) {
        const t = String(s === undefined || s === null ? "" : s);
        const result = /[",\n]/.test(t) ? '"' + t.replace(/"/g, '""') + '"' : t;
        return result;
    }

    function csvLine(cells) {
        const result = cells.map(function (c) {
            return csvField(c.text);
        }).join(",");
        return result;
    }

    /* a comment line of the csv */
    const CSV_COMMENT = "# ";

    function paintCsv() {
        const m = lastMatrix;
        const lines = [csvLine(m.head)];
        m.body.forEach(function (row) {
            lines.push(csvLine(row.cells));
        });
        if (m.foot) {
            lines.push(csvLine(m.foot));
        }
        if (view.export.export_scope === "all rows" && view.rows.row_show_all) {
            lines.push("");
            lines.push(CSV_COMMENT + txt(TXT.csvAllRows));
        }
        if (view.export.export_include_sources) {
            const seen = new Set();
            // only the sources of the exported rows, which a transposed table has as its columns
            const rowIds = new Set(m.transposed ? m.head.map(function (h) {
                return h.col;
            }) : m.body.map(function (row) {
                return row.id;
            }));
            lines.push("");
            lines.push(CSV_COMMENT + txt(TXT.csvSources));
            DATA.phrases.filter(function (p) {
                return rowIds.has(p.id);
            }).forEach(function (p) {
                if (!seen.has(p.source)) {
                    seen.add(p.source);
                    lines.push(CSV_COMMENT + p.source);
                }
            });
        }
        document.getElementById("csvText").value = lines.join("\n");
    }

    /* ---------- events on the table and chrome ---------- */
    function onTableClick(ev) {
        const t = ev.target.closest("[data-toggle], [data-sort], [data-sel]");
        if (t && t.dataset.toggle !== undefined) {
            const id = t.dataset.toggle;
            if (expanded.has(id)) {
                expanded.delete(id);
            } else {
                expanded.add(id);
            }
            render();
        } else if (t && t.dataset.sort !== undefined) {
            const col = t.dataset.sort === "__label" ? "" : t.dataset.sort;
            const order = rowOrder();
            const dir = order.col === col && order.dir === "asc" ? "desc" : "asc";
            pushHistory("");
            view.rows.row_order = col === "" ? "" : col + " " + dir;
            syncForm();
            render();
        } else if (t && t.dataset.sel !== undefined) {
            if (t.checked) {
                selected.add(t.dataset.sel);
            } else {
                selected.delete(t.dataset.sel);
            }
            render();
        }
    }

    function onControlsClick(ev) {
        const b = ev.target.closest("button");
        if (b && b.dataset.level) {
            pushHistory("");
            view.columns.column_level = b.dataset.level;
            syncForm();
            render();
        } else if (b && b.dataset.expand) {
            expanded = new Set();
            if (b.dataset.expand === "all") {
                DATA.phrases.forEach(function (p) {
                    expanded.add(p.id);
                });
            }
            render();
        }
    }

    function onSearch(ev) {
        if (ev.target.id === "searchBox") {
            const pos = ev.target.selectionStart;
            searchText = ev.target.value;
            page = 1;
            render();
            const box = document.getElementById("searchBox");
            if (box) {
                box.focus();
                box.setSelectionRange(pos, pos);
            }
        }
    }

    function onPagerClick(ev) {
        const b = ev.target.closest("button");
        if (b && b.dataset.page === "prev") {
            page -= 1;
            render();
        } else if (b && b.dataset.page === "next") {
            page += 1;
            render();
        }
    }

    function onTab(ev) {
        const tab = ev.currentTarget;
        document.querySelectorAll("#" + ELEMENT.host + " .tabs [role=tab]").forEach(function (t) {
            t.setAttribute("aria-selected", "false");
        });
        tab.setAttribute("aria-selected", "true");
        document.querySelectorAll("#" + ELEMENT.host + " .panel").forEach(function (p) {
            p.hidden = true;
        });
        document.getElementById("panel-" + tab.dataset.tab).hidden = false;
    }

    function applyJson() {
        const msg = document.getElementById("jsonMsg");
        try {
            const parsed = JSON.parse(document.getElementById("jsonText").value);
            pushHistory("");
            view = mergeView(parsed);
            expandRule = "";
            page = 1;
            msg.textContent = txt(TXT.applied);
            msg.className = "msg";
            syncForm();
            render();
        } catch (e) {
            msg.textContent = txt(TXT.jsonInvalid, {[VAR.value]: e.message});
            msg.className = "msg err";
        }
    }

    function toggleGroups(ev) {
        const groups = document.querySelectorAll("#rail details.group");
        const anyClosed = Array.prototype.some.call(groups, function (d) {
            return !d.open;
        });
        groups.forEach(function (d) {
            d.open = anyClosed;
        });
        ev.currentTarget.textContent = anyClosed ? txt(TXT.closeGroups) : txt(TXT.openGroups);
    }

    function wireEvents() {
        document.getElementById("tableBox").addEventListener("click", onTableClick);
        document.getElementById("controls").addEventListener("click", onControlsClick);
        document.getElementById("controls").addEventListener("input", onSearch);
        document.getElementById("pager").addEventListener("click", onPagerClick);
        document.querySelectorAll("#" + ELEMENT.host + " .tabs [role=tab]").forEach(function (tab) {
            tab.addEventListener("click", onTab);
        });
        document.getElementById("btnApply").addEventListener("click", applyJson);
        document.getElementById("btnCopyJson").addEventListener("click", function () {
            copyFrom("jsonText", "jsonMsg");
        });
        document.getElementById("btnCopyCsv").addEventListener("click", function () {
            copyFrom("csvText", "csvMsg");
        });
        document.getElementById("btnUndo").addEventListener("click", undo);
        document.getElementById("btnRedo").addEventListener("click", redo);
        document.getElementById("btnReset").addEventListener("click", discard);
        document.getElementById("btnExport").addEventListener("click", exportCsv);
        document.getElementById("btnSave").addEventListener("click", save);
        document.getElementById("phraseForm").addEventListener("submit", function (ev) {
            ev.preventDefault();
            loadPhrase(false);
        });
        document.addEventListener("keydown", onKey);
        document.getElementById("btnExpandAll").addEventListener("click", toggleGroups);
    }

    function copyFrom(taId, msgId) {
        const ta = document.getElementById(taId);
        const msg = document.getElementById(msgId);
        const done = function () {
            msg.textContent = txt(TXT.copied);
            msg.className = "msg";
        };
        const fallback = function () {
            ta.focus();
            ta.select();
            msg.textContent = txt(TXT.copyManual);
            msg.className = "msg";
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(ta.value).then(done).catch(fallback);
        } else {
            fallback();
        }
    }

    /* ---------- start ---------- */
    function start() {
        buildEditor();
        buildRail();
        wireEvents();
        wireTokenFields();
        syncForm();
        render();
    }

    start();
})();
