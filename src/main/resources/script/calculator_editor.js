"use strict";

/* the calculator editor (system_page::calculator_editor): the view renders the empty host, the
   phraseData answer of the page phrase as the seed and this script, which builds the editor */

/* ------------------------------------------------------------------ */
/* the toolbar, the three columns and the toast of the editor           */
/* ------------------------------------------------------------------ */
const EDITOR_HOST_ID = "calcEditor";
document.getElementById(EDITOR_HOST_ID).innerHTML = [
    '<div class="bar">',
    '<div class="brand"><b>Calculator editor</b></div>',
    '<form class="phrasebar" id="phraseForm" autocomplete="off">',
    '<label for="phraseIn">Phrase</label>',
    '<input class="inp" id="phraseIn" list="phraseList" placeholder="word or triple, e.g. example PV installation" spellcheck="false">',
    '<button class="btn primary" type="submit" id="btnLoad">Load</button>',
    '</form>',
    '<div class="tools">',
    '<span class="chip" id="srcChip">loading…</span>',
    '<span class="chip count" id="changeCount">no changes</span>',
    '<span class="chip warn" id="warnCount" hidden></span>',
    '<button class="btn" id="btnUndo" title="Undo (Ctrl+Z)" disabled>Undo</button>',
    '<button class="btn" id="btnRedo" title="Redo (Ctrl+Shift+Z)" disabled>Redo</button>',
    '<button class="btn" id="btnReset" title="Throw away all edits since the last save">Discard changes</button>',
    '<a class="btn" id="btnView" target="_blank" rel="noopener" hidden>View page</a>',
    '<button class="btn primary" id="btnSave" disabled>Save</button>',
    '</div>',
    '</div>',
    '<div id="noticeHost"></div>',
    '<div class="work">',
    '<div class="col tree-col">',
    '<div class="col-head"><h2>Page structure</h2><span class="chip" id="itemCount"></span></div>',
    '<div id="tree"></div>',
    '</div>',
    '<div class="col insp-col">',
    '<div class="col-head"><h2>Settings</h2></div>',
    '<div id="insp" class="insp"></div>',
    '</div>',
    '<div class="col preview-col">',
    '<div class="tabs" role="tablist">',
    '<button class="tab" role="tab" id="tab-page" aria-selected="true" data-tab="page">Page preview</button>',
    '<button class="tab" role="tab" id="tab-texts" aria-selected="false" data-tab="texts">All texts</button>',
    '<button class="tab" role="tab" id="tab-changes" aria-selected="false" data-tab="changes">Changes</button>',
    '</div>',
    '<div class="pane" id="pane"></div>',
    '</div>',
    '</div>',
    '<div class="toast" id="toast"></div>',
    '<datalist id="phraseList"></datalist>'
].join("");

/* ------------------------------------------------------------------ */
/* keywords of the calculator view and of the process catch fields      */
/* (calculator_keywords.json, process_catch_fields.json)                */
/* ------------------------------------------------------------------ */
const KW = {
    label: "calculator label",
    hint: "calculator hint",
    input: "calculator input",
    assumption: "calculator assumption",
    column: "calculator result column",
    rowgroup: "calculator row group",
    detail: "calculator detail table",
    highlight: "calculator highlight",
    signed: "calculator signed"
};
const KW_SET = new Set(Object.values(KW));
const FIELD = {
    number: "number with limits",
    selector: "phrase selector",
    valueBased: "field phrase selector value based",
    valuePhrases: "field value phrases",
    min: "field limit down",
    max: "field limit up",
    step: "field step"
};
const FIELD_VALUE_KW = new Set([FIELD.min, FIELD.max, FIELD.step]);
/* the phrase type names of the api that make a triple a unit of a number */
const UNIT_TYPES = new Set(["measure unit", "format percent"]);
const FOLLOW = "is follower of";
const HAS = "has a";
const WITH = "with";

/* the five lists of a calculator page, in the order of the page */
const LISTS = [
    {key: "input", kw: KW.input, title: "Inputs", suffix: "inputs", fallback: "Inputs"},
    {key: "assumption", kw: KW.assumption, title: "Assumptions", suffix: "assumptions", fallback: "Assumptions"},
    {key: "column", kw: KW.column, title: "Result columns", suffix: "result columns", fallback: ""},
    {key: "rowgroup", kw: KW.rowgroup, title: "Row groups and rows", suffix: "row groups", fallback: ""},
    {key: "detail", kw: KW.detail, title: "Detail tables", suffix: "detail tables", fallback: ""}
];

/* what each kind of item is, which verb links it to its parent and which sub-items it can have */
const KIND = {
    input: {name: "Input field", verb: HAS, kids: [], prefix: "input", field: true, hint: true},
    assumption: {name: "Assumption", verb: HAS, kids: [], prefix: "assumption", field: true, hint: true},
    column: {name: "Result column", verb: HAS, kids: [], prefix: "column"},
    rowgroup: {name: "Row group", verb: HAS, kids: ["row"], prefix: "rows", signed: true},
    row: {name: "Result row", verb: HAS, kids: ["filter"], prefix: "row", hint: true, highlight: true, side: true},
    filter: {name: "Row filter", verb: WITH, kids: [], prefix: null, highlight: false, side: true},
    detail: {name: "Detail table", verb: HAS, kids: ["dcol", "dextra"], prefix: "detail"},
    dcol: {name: "Detail column", verb: HAS, kids: ["dfilter"], prefix: "detail column"},
    dextra: {name: "Extra row", verb: WITH, kids: [], prefix: null, highlight: true},
    dfilter: {name: "Column filter", verb: WITH, kids: [], prefix: null},
    sub: {name: "Sub-item", verb: HAS, kids: [], prefix: null}
};
const ADD_TEXT = {
    input: "+ Add input",
    assumption: "+ Add assumption",
    column: "+ Add column",
    rowgroup: "+ Add row group",
    row: "+ row",
    filter: "+ filter",
    detail: "+ Add detail table",
    dcol: "+ column",
    dextra: "+ extra row",
    dfilter: "+ filter"
};

/* ------------------------------------------------------------------ */
/* small helpers                                                        */
/* ------------------------------------------------------------------ */
let uidCounter = 0;

function uid() {
    uidCounter = uidCounter + 1;
    const result = "n" + uidCounter;
    return result;
}

function esc(s) {
    const text = String(s === undefined || s === null ? "" : s);
    const result = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    return result;
}

function cap(s) {
    const text = String(s || "");
    const result = text.charAt(0).toUpperCase() + text.slice(1);
    return result;
}

function clone(o) {
    const result = JSON.parse(JSON.stringify(o));
    return result;
}

function newProp(src, name, text) {
    const result = {src: src, name: name, text: text || ""};
    return result;
}

function newFlag(src, name, on) {
    const result = {src: src, name: name, on: Boolean(on)};
    return result;
}

/* a dependent triple keeps its name only if it differs from the mechanical name */
function customName(actual, expected) {
    let result = null;
    if (actual && actual !== expected) {
        result = actual;
    }
    return result;
}

function stableId(obj) {
    const text = JSON.stringify(obj);
    let h = 0;
    for (let i = 0; i < text.length; i = i + 1) {
        h = (h * 31 + text.charCodeAt(i)) | 0;
    }
    const result = (h >>> 0).toString(36);
    return result;
}

function bindAttr(obj) {
    const result = esc(JSON.stringify(obj));
    return result;
}

/* ------------------------------------------------------------------ */
/* the loaded file                                                      */
/* ------------------------------------------------------------------ */
let DOC = null;          /* the triples and values as loaded, never changed */
let DOC_NAME = "";
/* where the data comes from: the api of a zukunft.com pod or the embedded demo file */
const SOURCE = {
    mode: "demo",
    online: false,
    base: "",
    root: "",
    pageId: 0,
    seedUsed: false,
    phraseIds: new Map(),
    verbIds: new Map()
};
/* the walk of the phraseData api that sends the triples on the from side of a phrase (url_var::PHRASE_DATA_WALK_FROM) */
const PHRASE_DATA_WALK_FROM = "pwf";
/* the api that saves the changes of a message (cfg/import/update_put.php) */
const UPDATE_PUT_ROUTE = "updatePut";
/* the header of a change with the session token of the page (api::CSRF_TOKEN_HEADER, url_var::SESSION_TOKEN) */
const CSRF_HEADER = "X-CSRF-Token";
const SESSION_TOKEN_NAME = "token";
let saving = false;
let IDX = null;          /* lookup indexes of the loaded file */
let MANAGED = null;      /* the triple and value indexes that the editor owns */
let state = null;        /* the editable model */
let selected = "page";
let activeTab = "page";
/* the lists of the page structure that the user has opened; on startup only the first list, the inputs, is open */
const openLists = new Set([LISTS[0].key]);
/* the collapsed blocks of the page preview that the user has opened e.g. the assumptions; closed on startup */
const openPreview = new Set();
const history = {undo: [], redo: [], lastKey: "", lastTime: 0};

function buildIndex(doc) {
    const T = doc.triples || [];
    const byFrom = new Map();
    const byName = new Map();
    const known = new Set();
    const symbolFor = new Map();
    const unitNames = typeUnits();
    T.forEach(function (t, i) {
        if (!byFrom.has(t.from)) {
            byFrom.set(t.from, []);
        }
        byFrom.get(t.from).push(i);
        if (t.name) {
            byName.set(t.name, i);
            known.add(t.name);
        }
        known.add(t.from);
        known.add(t.to);
        if (t.verb === "is symbol for") {
            symbolFor.set(t.to, t.from);
        }
        if (UNIT_TYPES.has(t.type)) {
            unitNames.add(t.name || (t.from + " " + t.verb + " " + t.to));
        }
    });
    (doc.words || []).forEach(function (w) {
        known.add(w.name);
    });
    (doc.values || []).forEach(function (v) {
        (v.words || []).forEach(function (w) {
            known.add(w);
        });
    });
    KW_SET.forEach(function (k) {
        known.delete(k);
    });
    const pool = [];
    (doc.values || []).forEach(function (v, i) {
        pool.push({words: v.words || [], number: v.number, source: v.source, vidx: i});
    });
    (doc.results || []).forEach(function (c) {
        pool.push({words: c.words || [], number: c.number, calc: true, formula: c.formula});
    });
    const result = {
        T: T,
        byFrom: byFrom,
        byName: byName,
        known: known,
        symbolFor: symbolFor,
        unitNames: unitNames,
        pool: pool
    };
    return result;
}

/* the name of the page phrase, which the api sends as the first phrase of the selection */
function selectionName(doc) {
    const sel = (doc.selection || [])[0] || {};
    const result = sel.name || "";
    return result;
}

/* the phrase id of the page phrase: the selection names a triple by its triple id, so the triples of the data say if it is one */
function selectionId(doc) {
    const sel = (doc.selection || [])[0] || {};
    const isTriple = (doc.triples || []).some(function (t) {
        return t.name === sel.name;
    });
    const result = isTriple ? -Math.abs(Number(sel.id)) : Number(sel.id);
    return result;
}

/* the field keyword of a limit value e.g. "field step" of "PV capacity, field step", in any order of the words */
function fieldKeyword(w) {
    let result = "";
    if (w.length === 2) {
        result = w.find(function (x) {
            return FIELD_VALUE_KW.has(x);
        }) || "";
    }
    return result;
}

function fromList(name) {
    const result = IDX.byFrom.get(name) || [];
    return result;
}

/* the phrase settings that are true wherever the phrase is shown (rule 6 and 9) */
function emptyPhrase() {
    const result = {
        label: newProp(null, null, ""),
        hint: newProp(null, null, ""),
        highlight: newFlag(null, null, false),
        side: [],
        field: {
            type: "", typeSrc: null, typeName: null,
            valueBased: false, vbSrc: null, vbName: null,
            limits: {min: {vsrc: null, num: ""}, max: {vsrc: null, num: ""}, step: {vsrc: null, num: ""}},
            valuePhrases: [],
            options: [],
            isa: [],
            uses: {src: null, name: null, to: ""}
        }
    };
    return result;
}

function phrase(name) {
    if (!state.phrases[name]) {
        state.phrases[name] = emptyPhrase();
    }
    const result = state.phrases[name];
    return result;
}

/* ------------------------------------------------------------------ */
/* parse: from the triples of the use case file to the editable model    */
/* ------------------------------------------------------------------ */
function sortChain(nodes, prevOf, nameOf) {
    const byName = new Map();
    nodes.forEach(function (n) {
        byName.set(nameOf(n), n);
    });
    const next = new Map();
    const heads = [];
    nodes.forEach(function (n) {
        const prev = prevOf(n);
        if (prev && byName.has(prev) && prev !== nameOf(n)) {
            if (!next.has(prev)) {
                next.set(prev, []);
            }
            next.get(prev).push(n);
        } else {
            heads.push(n);
        }
    });
    const out = [];
    const seen = new Set();

    function visit(n) {
        if (!seen.has(n)) {
            seen.add(n);
            out.push(n);
            (next.get(nameOf(n)) || []).forEach(visit);
        }
    }

    heads.forEach(visit);
    nodes.forEach(visit);
    return out;
}

function childKind(parentKind, verb) {
    const map = {
        rowgroup: {"has a": "row"},
        row: {"with": "filter"},
        detail: {"has a": "dcol", "with": "dextra"},
        dcol: {"with": "dfilter"}
    };
    let result = "sub";
    if (map[parentKind] && map[parentKind][verb]) {
        result = map[parentKind][verb];
    }
    return result;
}

function parseNode(i, kind, used) {
    const T = IDX.T;
    const t = T[i];
    used.add(i);
    const node = {
        uid: uid(), kind: kind, src: i, name: t.name || "", nameAuto: false, verb: t.verb, to: t.to,
        label: newProp(null, null, ""), hint: newProp(null, null, ""),
        highlight: newFlag(null, null, false), signed: newFlag(null, null, false),
        orderSrc: null, orderName: null, orderPrev: null, children: []
    };
    const kidIdx = [];
    fromList(t.name).forEach(function (j) {
        const c = T[j];
        if (c.verb === FOLLOW) {
            node.orderSrc = j;
            node.orderName = customName(c.name, t.name + " order");
            node.orderPrev = c.to;
            used.add(j);
        } else if (c.verb === HAS && c.to === KW.label) {
            node.label = newProp(j, customName(c.name, t.name + " label"), c.description || "");
            used.add(j);
        } else if (c.verb === HAS && c.to === KW.hint) {
            node.hint = newProp(j, customName(c.name, t.name + " hint"), c.description || "");
            used.add(j);
        } else if (c.verb === HAS && c.to === KW.highlight) {
            node.highlight = newFlag(j, customName(c.name, t.name + " highlight"), true);
            used.add(j);
        } else if (c.verb === HAS && c.to === KW.signed) {
            node.signed = newFlag(j, customName(c.name, t.name + " signed"), true);
            used.add(j);
        } else if ((c.verb === HAS || c.verb === WITH) && !KW_SET.has(c.to) && c.name) {
            kidIdx.push(j);
        }
    });
    const kids = kidIdx.map(function (j) {
        return parseNode(j, childKind(kind, T[j].verb), used);
    });
    node.children = sortChain(kids, function (n) {
        return n.orderPrev;
    }, function (n) {
        return n.name;
    });
    return node;
}

function parseDoc(doc) {
    IDX = buildIndex(doc);
    const T = IDX.T;
    const used = new Set();
    const usedValues = new Set();
    const page = selectionName(doc);
    let base = "";
    T.forEach(function (t) {
        if (!base && t.verb === HAS && LISTS.some(function (L) {
            return L.kw === t.to;
        })) {
            base = t.from;
        }
    });
    if (!base) {
        const pageTriple = IDX.byName.has(page) ? T[IDX.byName.get(page)] : null;
        if (pageTriple) {
            base = pageTriple.from;
        } else {
            base = page;
        }
    }
    const model = {base: base, page: page, lists: {}, phrases: {}, valueEdits: {}, formulaEdits: {}};
    state = model;
    const itemNames = new Set();
    LISTS.forEach(function (L) {
        const group = {src: null, name: null, label: newProp(null, null, ""), items: []};
        fromList(base).forEach(function (i) {
            const t = T[i];
            if (group.src === null && t.verb === HAS && t.to === L.kw) {
                group.src = i;
                group.name = customName(t.name, base + " " + L.suffix);
                used.add(i);
            }
        });
        if (group.src !== null) {
            const gname = T[group.src].name;
            itemNames.add(gname);
            const nodes = [];
            fromList(gname).forEach(function (j) {
                const c = T[j];
                if (c.verb === HAS && c.to === KW.label) {
                    group.label = newProp(j, customName(c.name, gname + " label"), c.description || "");
                    used.add(j);
                } else if (c.verb === HAS && !KW_SET.has(c.to) && c.name) {
                    nodes.push(parseNode(j, L.key, used));
                }
            });
            group.items = sortChain(nodes, function (n) {
                return n.orderPrev;
            }, function (n) {
                return n.name;
            });
        }
        model.lists[L.key] = group;
    });
    walkAll(function (n) {
        itemNames.add(n.name);
    });

    /* field type and value based selector */
    T.forEach(function (t, i) {
        if (!used.has(i) && t.verb === "can be" && (t.to === FIELD.number || t.to === FIELD.selector)) {
            const f = phrase(t.from).field;
            f.type = t.to;
            f.typeSrc = i;
            f.typeName = customName(t.name, "field " + t.from);
            used.add(i);
        }
        if (!used.has(i) && t.verb === "can be" && t.to === FIELD.valueBased) {
            const f = phrase(t.from).field;
            f.valueBased = true;
            f.vbSrc = i;
            f.vbName = customName(t.name, t.from + " options");
            used.add(i);
        }
    });
    /* field value phrases: X has a P (named), and that triple can be field value phrases */
    T.forEach(function (t, i) {
        if (!used.has(i) && t.verb === "can be" && t.to === FIELD.valuePhrases && IDX.byName.has(t.from)) {
            const j = IDX.byName.get(t.from);
            const p = T[j];
            phrase(p.from).field.valuePhrases.push({
                uid: uid(),
                src: j,
                name: p.name,
                to: p.to,
                fvpSrc: i,
                fvpName: t.name || null
            });
            used.add(i);
            used.add(j);
        }
    });
    /* selector options: X has a O, named "X option ..." */
    T.forEach(function (t, i) {
        if (!used.has(i) && t.verb === HAS && t.name && t.name.indexOf(t.from + " option ") === 0) {
            const opt = {
                uid: uid(),
                src: i,
                name: t.name,
                nameAuto: false,
                to: t.to,
                label: newProp(null, null, ""),
                orderSrc: null,
                orderName: null,
                orderPrev: null
            };
            used.add(i);
            fromList(t.name).forEach(function (j) {
                const c = T[j];
                if (c.verb === HAS && c.to === KW.label) {
                    opt.label = newProp(j, customName(c.name, t.name + " label"), c.description || "");
                    used.add(j);
                } else if (c.verb === FOLLOW) {
                    opt.orderSrc = j;
                    opt.orderName = customName(c.name, t.name + " order");
                    opt.orderPrev = c.to;
                    used.add(j);
                }
            });
            phrase(t.from).field.options.push(opt);
        }
    });
    Object.keys(model.phrases).forEach(function (x) {
        const f = model.phrases[x].field;
        f.options = sortChain(f.options, function (o) {
            return o.orderPrev;
        }, function (o) {
            return o.name;
        });
    });
    /* "is a" options of a phrase selector, e.g. residual mix is a replacement mix */
    Object.keys(model.phrases).forEach(function (x) {
        const f = model.phrases[x].field;
        if (f.type === FIELD.selector) {
            T.forEach(function (t, i) {
                if (!used.has(i) && t.verb === "is a" && t.to === x) {
                    f.isa.push({
                        uid: uid(),
                        src: i,
                        name: t.name || null,
                        from: t.from,
                        orderSrc: null,
                        orderName: null,
                        orderPrev: null
                    });
                    used.add(i);
                }
            });
            const fromSet = new Set(f.isa.map(function (o) {
                return o.from;
            }));
            f.isa.forEach(function (o) {
                fromList(o.from).forEach(function (j) {
                    const c = T[j];
                    if (!used.has(j) && c.verb === FOLLOW && fromSet.has(c.to)) {
                        o.orderSrc = j;
                        o.orderName = customName(c.name, o.from + " order");
                        o.orderPrev = c.to;
                        used.add(j);
                    }
                });
            });
            f.isa = sortChain(f.isa, function (o) {
                return o.orderPrev;
            }, function (o) {
                return o.from;
            });
        }
    });
    /* the option that the page phrase uses */
    fromList(page).forEach(function (i) {
        const t = T[i];
        if (t.verb === "uses") {
            Object.keys(model.phrases).forEach(function (x) {
                const f = model.phrases[x].field;
                const hit = f.options.some(function (o) {
                    return o.to === t.to;
                }) || f.isa.some(function (o) {
                    return o.from === t.to;
                });
                if (hit && f.uses.src === null) {
                    f.uses = {src: i, name: customName(t.name, x + " of the " + page), to: t.to};
                    used.add(i);
                }
            });
        }
    });
    /* side results, labels, hints and highlights of phrases */
    T.forEach(function (t, i) {
        if (!used.has(i) && t.verb === HAS && t.name && t.name.indexOf(t.from + " side result") === 0 && !itemNames.has(t.from)) {
            phrase(t.from).side.push({
                uid: uid(),
                src: i,
                name: customName(t.name, t.from + " side result"),
                to: t.to
            });
            used.add(i);
        }
    });
    T.forEach(function (t, i) {
        if (!used.has(i) && t.verb === HAS && !itemNames.has(t.from)) {
            if (t.to === KW.label) {
                phrase(t.from).label = newProp(i, customName(t.name, t.from + " label"), t.description || "");
                used.add(i);
            } else if (t.to === KW.hint) {
                phrase(t.from).hint = newProp(i, customName(t.name, t.from + " hint"), t.description || "");
                used.add(i);
            } else if (t.to === KW.highlight) {
                phrase(t.from).highlight = newFlag(i, customName(t.name, t.from + " highlight"), true);
                used.add(i);
            }
        }
    });
    /* limits and step of the fields */
    (doc.values || []).forEach(function (v, i) {
        const w = v.words || [];
        const kw = fieldKeyword(w);
        if (kw) {
            const f = phrase(w.find(function (x) {
                return x !== kw;
            })).field;
            const key = kw === FIELD.min ? "min" : (kw === FIELD.max ? "max" : "step");
            f.limits[key] = {vsrc: i, num: String(v.number)};
            usedValues.add(i);
        }
    });
    MANAGED = {triples: used, values: usedValues};
    return model;
}

/* ------------------------------------------------------------------ */
/* tree helpers                                                         */
/* ------------------------------------------------------------------ */
function walkAll(fn) {
    function walk(nodes, parent, listKey) {
        nodes.forEach(function (n, i) {
            fn(n, parent, listKey, nodes, i);
            walk(n.children, n, listKey);
        });
    }

    LISTS.forEach(function (L) {
        walk(state.lists[L.key].items, null, L.key);
    });
}

function findNode(id) {
    let result = null;
    walkAll(function (n, parent, listKey, arr, i) {
        if (n.uid === id) {
            result = {node: n, parent: parent, listKey: listKey, arr: arr, index: i};
        }
    });
    return result;
}

function groupName(listKey) {
    const L = LISTS.find(function (x) {
        return x.key === listKey;
    });
    const g = state.lists[listKey];
    let result = state.base + " " + L.suffix;
    if (g.src !== null) {
        result = IDX.T[g.src].name;
        if (g.name) {
            result = g.name;
        }
    }
    return result;
}

function allNames() {
    const names = new Set(IDX.known);
    walkAll(function (n) {
        names.add(n.name);
    });
    return names;
}

function conventionalName(node, parentName) {
    const prefix = KIND[node.kind].prefix;
    const to = node.to || "…";
    let result = "";
    if (prefix) {
        result = state.base + " " + prefix + " " + to;
    } else if (node.kind === "dextra") {
        result = parentName + " " + to;
    } else {
        result = parentName + " filter";
    }
    return result;
}

function uniqueName(wanted, self) {
    const names = allNames();
    if (self) {
        names.delete(self.name);
    }
    let result = wanted;
    let n = 2;
    while (names.has(result)) {
        result = wanted + " " + n;
        n = n + 1;
    }
    return result;
}

function parentNameOf(hit) {
    let result = groupName(hit.listKey);
    if (hit.parent) {
        result = hit.parent.name;
    }
    return result;
}

function refreshAutoName(hit) {
    if (hit.node.nameAuto) {
        const oldName = hit.node.name;
        hit.node.name = uniqueName(conventionalName(hit.node, parentNameOf(hit)), hit.node);
        renameRefs(oldName, hit.node.name);
    }
}

/* the order triples point to the item before by name, so a rename moves those pointers too */
function renameRefs(oldName, newName) {
    if (oldName !== newName) {
        walkAll(function (n) {
            if (n.orderPrev === oldName) {
                n.orderPrev = newName;
            }
        });
    }
}

/* ------------------------------------------------------------------ */
/* inherited texts and flags (rule 6 and 8)                             */
/* ------------------------------------------------------------------ */
function phraseText(name, key) {
    let result = "";
    const p = state.phrases[name];
    if (p && p[key] && p[key].text) {
        result = p[key].text;
    }
    return result;
}

function phraseFlag(name) {
    let result = false;
    const p = state.phrases[name];
    if (p && p.highlight.on) {
        result = true;
    }
    return result;
}

function labelInfo(node, parent) {
    let result = {text: cap(node.to), from: "the phrase name"};
    const ph = phraseText(node.to, "label");
    const own = node.label.text;
    let other = "";
    if (parent && parent.kind === "rowgroup" && IDX.byName.has(node.to)) {
        const tr = IDX.T[IDX.byName.get(node.to)];
        if (tr.from === parent.to) {
            other = tr.to;
        }
    }
    if (own) {
        result = {text: own, from: "item"};
    } else if (ph) {
        result = {text: ph, from: "the label of the phrase “" + node.to + "”"};
    } else if (other) {
        const otherLabel = phraseText(other, "label");
        if (otherLabel) {
            result = {
                text: otherLabel,
                from: "the label of “" + other + "” (the row phrase is built from the group phrase)"
            };
        } else {
            result = {
                text: cap(other),
                from: "the name of “" + other + "” (the row phrase is built from the group phrase)"
            };
        }
    }
    if (!node.to && !own) {
        result = {text: "(no phrase yet)", from: "nothing"};
    }
    return result;
}

function hintInfo(node) {
    let result = {text: "", from: ""};
    const ph = phraseText(node.to, "hint");
    if (node.hint.text) {
        result = {text: node.hint.text, from: "item"};
    } else if (ph) {
        result = {text: ph, from: "the hint of the phrase “" + node.to + "”"};
    }
    return result;
}

function filtersOf(node) {
    const result = node.children.filter(function (c) {
        return c.verb === WITH;
    }).map(function (c) {
        return c.to;
    }).filter(Boolean);
    return result;
}

function highlightInfo(node) {
    let result = {on: false, from: ""};
    const filterHit = filtersOf(node).find(function (f) {
        return phraseFlag(f);
    });
    if (node.highlight.on) {
        result = {on: true, from: "item"};
    } else if (phraseFlag(node.to)) {
        result = {on: true, from: "the phrase “" + node.to + "”"};
    } else if (node.kind === "row" && filterHit) {
        result = {on: true, from: "the filter phrase “" + filterHit + "”"};
    }
    return result;
}

function groupLabel(listKey) {
    const L = LISTS.find(function (x) {
        return x.key === listKey;
    });
    let result = L.fallback;
    if (state.lists[listKey].label.text) {
        result = state.lists[listKey].label.text;
    }
    return result;
}

/* ------------------------------------------------------------------ */
/* numbers for the preview: the values and the results                   */
/* ------------------------------------------------------------------ */
function poolNumber(entry) {
    let result = entry.number;
    if (entry.vidx !== undefined && state.valueEdits[entry.vidx] !== undefined) {
        result = state.valueEdits[entry.vidx];
    }
    return result;
}

function findEntry(required, opts) {
    const o = opts || {};
    const req = required.filter(Boolean);
    let best = null;
    let bestScore = Infinity;
    if (req.length > 0) {
        IDX.pool.forEach(function (e) {
            const w = e.words;
            const okWords = req.every(function (r) {
                return w.indexOf(r) >= 0;
            });
            const noConf = w.indexOf("confidence") < 0;
            const noField = !w.some(function (x) {
                return FIELD_VALUE_KW.has(x);
            });
            const okPage = !o.needPage || w.indexOf(state.page) >= 0;
            const okKind = !o.valuesOnly || e.vidx !== undefined;
            if (okWords && noConf && noField && okPage && okKind) {
                const pagePenalty = w.indexOf(state.page) >= 0 ? 0 : 50;
                const score = pagePenalty + (w.length - req.length);
                if (score < bestScore) {
                    best = e;
                    bestScore = score;
                }
            }
        });
    }
    return best;
}

function unitOf(words) {
    let result = "";
    (words || []).forEach(function (w) {
        if (!result && IDX.symbolFor.has(w)) {
            result = IDX.symbolFor.get(w);
        } else if (!result && IDX.unitNames.has(w)) {
            result = w;
        }
    });
    return result;
}

function fmtNumber(raw, unit) {
    const num = Number(raw);
    let result = String(raw);
    if (raw !== undefined && raw !== null && raw !== "" && isFinite(num)) {
        let shown = num;
        let suffix = unit ? " " + unit : "";
        // the unit of a share is percent or its symbol e.g. "%"
        const isPercent = unit === "percent" || unit === IDX.symbolFor.get("percent");
        if (isPercent && Math.abs(num) <= 1.5) {
            shown = num * 100;
            suffix = " %";
        }
        const abs = Math.abs(shown);
        let decimals = 2;
        if (abs >= 100) {
            decimals = 0;
        } else if (abs >= 10) {
            decimals = 1;
        }
        const fixed = shown.toFixed(decimals);
        const parts = fixed.split(".");
        const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, "'");
        const decPart = parts.length > 1 ? "." + parts[1] : "";
        result = intPart + decPart + suffix;
    }
    return result;
}

function fieldValue(x) {
    const vp = state.phrases[x] ? state.phrases[x].field.valuePhrases.map(function (v) {
        return v.to;
    }) : [];
    const req = [x].concat(vp);
    const result = findEntry(req, {valuesOnly: true});
    return result;
}

function confidenceOf(x, entry) {
    let result = "";
    if (entry && entry.words.indexOf("assumed") >= 0) {
        IDX.pool.forEach(function (e) {
            if (!result && e.vidx !== undefined && e.words.indexOf(x) >= 0 && e.words.indexOf("confidence") >= 0) {
                result = String(e.number);
            }
        });
    }
    return result;
}

function sourceNumber(name) {
    let result = 0;
    (DOC.sources || []).forEach(function (s, i) {
        if (!result && s.name === name) {
            result = i + 1;
        }
    });
    return result;
}

/* ------------------------------------------------------------------ */
/* export: from the model back to the triples of the use case file       */
/* ------------------------------------------------------------------ */
function emitAll() {
    const out = [];
    const base = state.base;

    function triple(src, name, from, verb, to, description, textManaged, order) {
        const obj = {};
        const keys = order || ["name", "from", "verb", "to", "description"];
        keys.forEach(function (k) {
            if (k === "name" && name) {
                obj.name = name;
            }
            if (k === "from") {
                obj.from = from;
            }
            if (k === "verb") {
                obj.verb = verb;
            }
            if (k === "to") {
                obj.to = to;
            }
            if (k === "description" && textManaged) {
                obj.description = description;
            }
        });
        out.push({src: src, obj: obj, textManaged: Boolean(textManaged)});
    }

    const TXT_ORDER = ["name", "description", "from", "verb", "to"];

    function emitProp(prop, owner, kw, suffix) {
        if (prop.text) {
            triple(prop.src, prop.name || (owner + " " + suffix), owner, HAS, kw, prop.text, true, TXT_ORDER);
        }
    }

    function emitFlag(flag, owner, kw, suffix) {
        if (flag.on) {
            triple(flag.src, flag.name || (owner + " " + suffix), owner, HAS, kw, "", false);
        }
    }

    function emitNodes(nodes, parentName) {
        const prevByVerb = {};
        nodes.forEach(function (n) {
            const prev = prevByVerb[n.verb] || null;
            if (n.to && n.name) {
                triple(n.src, n.name, parentName, n.verb, n.to, "", false);
                emitProp(n.label, n.name, KW.label, "label");
                emitProp(n.hint, n.name, KW.hint, "hint");
                emitFlag(n.highlight, n.name, KW.highlight, "highlight");
                emitFlag(n.signed, n.name, KW.signed, "signed");
                if (prev) {
                    triple(n.orderSrc, n.orderName || (n.name + " order"), n.name, FOLLOW, prev.name, "", false);
                }
                emitNodes(n.children, n.name);
                prevByVerb[n.verb] = n;
            }
        });
    }

    LISTS.forEach(function (L) {
        const g = state.lists[L.key];
        const hasContent = g.items.some(function (n) {
            return n.to;
        }) || g.label.text;
        if (g.src !== null || hasContent) {
            const gname = groupName(L.key);
            triple(g.src, gname, base, HAS, L.kw, "", false);
            emitProp(g.label, gname, KW.label, "label");
            emitNodes(g.items, gname);
        }
    });
    Object.keys(state.phrases).forEach(function (x) {
        const p = state.phrases[x];
        const f = p.field;
        emitProp(p.label, x, KW.label, "label");
        emitProp(p.hint, x, KW.hint, "hint");
        emitFlag(p.highlight, x, KW.highlight, "highlight");
        p.side.forEach(function (s, i) {
            if (s.to) {
                const nm = s.name || (x + " side result" + (i > 0 ? " " + (i + 1) : ""));
                triple(s.src, nm, x, HAS, s.to, "", false);
            }
        });
        if (f.type) {
            triple(f.typeSrc, f.typeName || ("field " + x), x, "can be", f.type, "", false);
        }
        if (f.valueBased) {
            triple(f.vbSrc, f.vbName || (x + " options"), x, "can be", FIELD.valueBased, "", false);
        }
        f.valuePhrases.forEach(function (v) {
            if (v.to) {
                const n1 = v.name || (x + " input " + v.to);
                triple(v.src, n1, x, HAS, v.to, "", false);
                triple(v.fvpSrc, v.fvpName || (x + " field value phrase " + v.to), n1, "can be", FIELD.valuePhrases, "", false);
            }
        });
        let prevOpt = null;
        f.options.forEach(function (o) {
            if (o.to) {
                triple(o.src, o.name, x, HAS, o.to, "", false);
                emitProp(o.label, o.name, KW.label, "label");
                if (prevOpt) {
                    triple(o.orderSrc, o.orderName || (o.name + " order"), o.name, FOLLOW, prevOpt.name, "", false);
                }
                prevOpt = o;
            }
        });
        let prevIsa = null;
        f.isa.forEach(function (o) {
            if (o.from) {
                triple(o.src, o.name, o.from, "is a", x, "", false);
                if (prevIsa) {
                    triple(o.orderSrc, o.orderName || (o.from + " order"), o.from, FOLLOW, prevIsa.from, "", false);
                }
                prevIsa = o;
            }
        });
        if (f.uses.to) {
            triple(f.uses.src, f.uses.name || (x + " of the " + state.page), state.page, "uses", f.uses.to, "", false);
        }
    });
    return out;
}

function emitValues() {
    const out = [];
    Object.keys(state.phrases).forEach(function (x) {
        const lim = state.phrases[x].field.limits;
        [["min", FIELD.min], ["max", FIELD.max], ["step", FIELD.step]].forEach(function (pair) {
            const l = lim[pair[0]];
            if (l.num !== "" && l.num !== null) {
                out.push({src: l.vsrc, words: [x, pair[1]], number: String(l.num)});
            }
        });
    });
    return out;
}

function mergeObj(orig, gen, textManaged) {
    const result = {};
    Object.keys(orig).forEach(function (k) {
        const managed = k === "name" || k === "from" || k === "verb" || k === "to" || (k === "description" && textManaged);
        if (!managed) {
            result[k] = orig[k];
        } else if (gen[k] !== undefined) {
            result[k] = gen[k];
        }
    });
    Object.keys(gen).forEach(function (k) {
        if (result[k] === undefined) {
            result[k] = gen[k];
        }
    });
    return result;
}

/* what has changed against the loaded data: the triples, values and words to add, change or remove */
function buildOutput() {
    const T = IDX.T;
    const emitted = emitAll();
    const bySrc = new Map();
    const fresh = [];
    const changes = {add: [], del: [], mod: [], words: [], values: [], formulas: []};
    emitted.forEach(function (e) {
        if (e.src !== null && e.src !== undefined && !bySrc.has(e.src)) {
            bySrc.set(e.src, e);
        } else {
            fresh.push(e);
        }
    });
    const finalTriples = [];
    T.forEach(function (t, i) {
        if (!MANAGED.triples.has(i)) {
            finalTriples.push(t);
        } else if (bySrc.has(i)) {
            const e = bySrc.get(i);
            const merged = mergeObj(t, e.obj, e.textManaged);
            if (JSON.stringify(merged) !== JSON.stringify(t)) {
                changes.mod.push({src: i, before: t, after: merged});
            }
            finalTriples.push(merged);
        } else {
            changes.del.push({src: i, t: t});
        }
    });
    fresh.forEach(function (e) {
        finalTriples.push(e.obj);
        changes.add.push(e.obj);
    });

    /* values: the limits of the fields and the edited shown numbers */
    const V = DOC.values || [];
    const genValues = emitValues();
    const vBySrc = new Map();
    const vFresh = [];
    genValues.forEach(function (g) {
        if (g.src !== null && g.src !== undefined) {
            vBySrc.set(g.src, g);
        } else {
            vFresh.push(g);
        }
    });
    V.forEach(function (v, i) {
        let number = v.number;
        let keep = true;
        if (MANAGED.values.has(i)) {
            keep = vBySrc.has(i);
            if (keep) {
                number = vBySrc.get(i).number;
            }
        }
        if (state.valueEdits[i] !== undefined) {
            number = String(state.valueEdits[i]);
        }
        if (!keep) {
            changes.values.push({
                kind: "del",
                src: i,
                words: v.words,
                number: v.number,
                text: v.words.join(", ") + " = " + v.number
            });
        } else if (String(number) !== String(v.number)) {
            changes.values.push({
                kind: "mod",
                src: i,
                words: v.words,
                number: String(number),
                text: v.words.join(", ") + ": " + v.number + " → " + number
            });
        }
    });
    vFresh.forEach(function (g) {
        changes.values.push({
            kind: "add",
            src: null,
            words: g.words,
            number: g.number,
            text: g.words.join(", ") + " = " + g.number
        });
    });

    /* formulas: the changed expressions, which are saved as the own version of the user */
    (DOC.formulas || []).forEach(function (f) {
        const after = formulaExpression(f);
        if (after !== (f.expression || "")) {
            changes.formulas.push({id: f.id, name: f.name, before: f.expression || "", after: after});
        }
    });

    /* words: every phrase that is not in the database yet is added as a word */
    const knownNow = new Set(IDX.known);
    finalTriples.forEach(function (t) {
        if (t.name) {
            knownNow.add(t.name);
        }
    });
    finalTriples.forEach(function (t) {
        [t.from, t.to].forEach(function (p) {
            if (p && !knownNow.has(p) && !KW_SET.has(p) && changes.words.indexOf(p) < 0) {
                changes.words.push(p);
            }
        });
    });

    const changed = changes.add.length + changes.del.length + changes.mod.length + changes.values.length + changes.words.length + changes.formulas.length;
    const warnings = validate(finalTriples);
    const result = {changes: changes, changed: changed, warnings: warnings};
    return result;
}

function validate(finalTriples) {
    const warns = [];
    const keySeen = new Map();
    const nameSeen = new Map();
    finalTriples.forEach(function (t) {
        const key = t.from + " | " + t.verb + " | " + t.to;
        keySeen.set(key, (keySeen.get(key) || 0) + 1);
        if (t.name) {
            nameSeen.set(t.name, (nameSeen.get(t.name) || 0) + 1);
        }
    });
    keySeen.forEach(function (n, k) {
        if (n > 1) {
            warns.push("The triple “" + k + "” exists " + n + " times. Two items with the same phrase in one list need a filter or another phrase.");
        }
    });
    nameSeen.forEach(function (n, k) {
        if (n > 1) {
            warns.push("The name “" + k + "” is used by " + n + " triples. Give one of the items another name.");
        }
    });
    walkAll(function (n) {
        if (!n.to) {
            warns.push("A " + KIND[n.kind].name.toLowerCase() + " has no phrase yet and is left out of the file.");
        }
    });
    const result = warns;
    return result;
}

/* ------------------------------------------------------------------ */
/* history and changes                                                  */
/* ------------------------------------------------------------------ */
function snapshot() {
    const result = JSON.stringify({state: state, selected: selected});
    return result;
}

function restore(snap) {
    const s = JSON.parse(snap);
    state = s.state;
    selected = s.selected;
}

function pushHistory(key) {
    const now = Date.now();
    const coalesce = key && key === history.lastKey && now - history.lastTime < 1500;
    if (!coalesce) {
        history.undo.push(snapshot());
        if (history.undo.length > 200) {
            history.undo.shift();
        }
        history.redo = [];
    }
    history.lastKey = key || "";
    history.lastTime = now;
}

function mutate(fn, opts) {
    const o = opts || {};
    pushHistory(o.key || "");
    fn();
    afterChange(o);
}

function afterChange(o) {
    const opts = o || {};
    renderTree();
    if (!opts.keepInspector) {
        renderInspector();
    }
    renderPane();
    refreshStatus();
    autosave();
}

function undo() {
    if (history.undo.length > 0) {
        history.redo.push(snapshot());
        restore(history.undo.pop());
        history.lastKey = "";
        afterChange();
    }
}

function redo() {
    if (history.redo.length > 0) {
        history.undo.push(snapshot());
        restore(history.redo.pop());
        history.lastKey = "";
        afterChange();
    }
}

let lastOutput = null;

function refreshStatus() {
    lastOutput = buildOutput();
    const cc = document.getElementById("changeCount");
    const wc = document.getElementById("warnCount");
    cc.textContent = lastOutput.changed === 0 ? "no changes" : lastOutput.changed + (lastOutput.changed === 1 ? " change" : " changes");
    wc.hidden = lastOutput.warnings.length === 0;
    wc.textContent = lastOutput.warnings.length + (lastOutput.warnings.length === 1 ? " warning" : " warnings");
    const save = document.getElementById("btnSave");
    save.disabled = SOURCE.mode !== "api" || lastOutput.changed === 0 || saving;
    save.textContent = saving ? "Saving…" : "Save";
    save.title = "Write the changes to the database";
    document.getElementById("btnUndo").disabled = history.undo.length === 0;
    document.getElementById("btnRedo").disabled = history.redo.length === 0;
    let count = 0;
    walkAll(function () {
        count = count + 1;
    });
    document.getElementById("itemCount").textContent = count + " items";
}

/* ------------------------------------------------------------------ */
/* draft in the browser, so a reload keeps the work                     */
/* ------------------------------------------------------------------ */
const DRAFT_KEY = "zukunft-calculator-editor-draft";
let autosaveTimer = null;

function autosave() {
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(function () {
        try {
            if (lastOutput && lastOutput.changed > 0) {
                localStorage.setItem(DRAFT_KEY, JSON.stringify({
                    at: Date.now(),
                    name: DOC_NAME,
                    mode: SOURCE.mode,
                    pageId: SOURCE.pageId,
                    sig: signature(DOC),
                    state: state
                }));
            } else {
                localStorage.removeItem(DRAFT_KEY);
            }
        } catch (e) {
            /* storage is a convenience only */
        }
    }, 400);
}

function readDraft() {
    let result = null;
    try {
        const raw = localStorage.getItem(DRAFT_KEY);
        if (raw) {
            result = JSON.parse(raw);
        }
    } catch (e) {
        result = null;
    }
    return result;
}

function clearDraft() {
    try {
        localStorage.removeItem(DRAFT_KEY);
    } catch (e) {
        /* nothing to clear */
    }
}

/* ------------------------------------------------------------------ */
/* rendering: the tree                                                  */
/* ------------------------------------------------------------------ */
function nodeRow(n, parent, listKey, arr, i) {
    const info = labelInfo(n, parent);
    const hl = highlightInfo(n);
    const chips = [];
    if (hl.on && (n.kind === "row" || n.kind === "dextra")) {
        chips.push('<span class="chip key" title="key row, from ' + esc(hl.from) + '">key</span>');
    }
    if (n.signed.on) {
        chips.push('<span class="chip" title="the cells show the sign">±</span>');
    }
    const f = state.phrases[n.to] ? state.phrases[n.to].field : null;
    if (f && f.type === FIELD.selector && (n.kind === "input" || n.kind === "assumption")) {
        chips.push('<span class="chip" title="phrase selector">select</span>');
    }
    if (n.src === null) {
        chips.push('<span class="chip new">new</span>');
    }
    if (!n.to) {
        chips.push('<span class="chip warn">no phrase</span>');
    }
    const verbNote = n.verb === WITH ? "with " : "";
    const sel = selected === n.uid ? " sel" : "";
    const kidKinds = KIND[n.kind].kids;
    /* a result row lists the formulas that calculate its number, which may be more than one */
    const formulas = n.kind === "row" && n.to ? formulasUsed(n.to).map(formulaRow).join("") : "";
    let kids = "";
    if (n.children.length > 0 || kidKinds.length > 0 || formulas) {
        const inner = n.children.map(function (c, j) {
            return nodeRow(c, n, listKey, n.children, j);
        }).join("");
        const adds = kidKinds.map(function (k) {
            return '<button class="addlink" data-act="addChild" data-uid="' + n.uid + '" data-kind="' + k + '">' + esc(ADD_TEXT[k]) + "</button>";
        }).join("");
        kids = '<div class="kids">' + inner + formulas + (adds ? '<div class="addrow">' + adds + "</div>" : "") + "</div>";
    }
    const result =
        '<div class="node' + sel + '" data-uid="' + n.uid + '" data-kind="' + n.kind + '" draggable="true" tabindex="0" role="treeitem" aria-selected="' + (sel ? "true" : "false") + '">' +
        '<span class="grip" aria-hidden="true">⋮⋮</span>' +
        '<span class="txt"><span class="lbl' + (info.from === "item" ? "" : " inh") + '">' + esc(info.text) + "</span>" +
        '<span class="ph">' + esc(verbNote + (n.to || "—")) + "</span></span>" +
        '<span class="side">' + chips.join("") +
        '<span class="acts">' +
        '<button class="ico" data-act="up" data-uid="' + n.uid + '" title="Move up" aria-label="Move up"' + (i === 0 ? " disabled" : "") + ">↑</button>" +
        '<button class="ico" data-act="down" data-uid="' + n.uid + '" title="Move down" aria-label="Move down"' + (i === arr.length - 1 ? " disabled" : "") + ">↓</button>" +
        '<button class="ico rm" data-act="remove" data-uid="' + n.uid + '" title="Remove" aria-label="Remove">✕</button>' +
        "</span></span>" +
        "</div>" + kids;
    return result;
}

function renderTree() {
    const host = document.getElementById("tree");
    const pageSel = selected === "page" ? " sel" : "";
    let html = '<div class="sect"><div class="node page-node' + pageSel + '" data-uid="page" tabindex="0" role="treeitem">' +
        '<span class="txt"><span class="lbl">' + esc(phraseText(state.base, "label") || cap(state.base)) + "</span>" +
        '<span class="ph">page title, lead text and headings</span></span><span></span></div></div>';
    LISTS.forEach(function (L) {
        const g = state.lists[L.key];
        const items = g.items.map(function (n, i) {
            return nodeRow(n, null, L.key, g.items, i);
        }).join("");
        /* like the assumptions of the calculator page, each list opens and closes by its heading */
        const open = openLists.has(L.key) ? " open" : "";
        html += '<details class="sect" data-list="' + L.key + '"' + open + '>' +
            '<summary class="sect-head"><h3>' + esc(L.title) + '</h3><span class="chip">' + g.items.length + '</span><span class="kw mono">' + esc(L.kw) + "</span></summary>" +
            '<div class="list" data-list="' + L.key + '">' + (items || '<div class="empty">Nothing in this list yet.</div>') + "</div>" +
            '<div class="addrow" style="padding-left:6px"><button class="addlink" data-act="addTop" data-list="' + L.key + '">' + esc(ADD_TEXT[L.key]) + "</button></div>" +
            "</details>";
    });
    host.innerHTML = html;
}

/* ------------------------------------------------------------------ */
/* rendering: the inspector                                             */
/* ------------------------------------------------------------------ */
function inputHtml(bind, value, opts) {
    const o = opts || {};
    const id = "f_" + (o.id || stableId(bind));
    const cls = "inp" + (o.mono ? " mono" : "");
    const list = o.phrases ? ' list="phraseList"' : "";
    const ph = o.placeholder ? ' placeholder="' + esc(o.placeholder) + '"' : "";
    const type = o.type || "text";
    let control = "";
    if (o.area) {
        control = '<textarea id="' + id + '" class="' + cls + '" data-bind="' + bindAttr(bind) + '"' + ph + ' rows="2">' + esc(value) + "</textarea>";
    } else {
        control = '<input id="' + id + '" type="' + type + '" class="' + cls + '" data-bind="' + bindAttr(bind) + '" value="' + esc(value) + '"' + ph + list + (type === "number" ? ' step="any"' : "") + ">";
    }
    const label = o.label ? '<label for="' + id + '">' + o.label + "</label>" : "";
    const note = o.note ? '<div class="note">' + o.note + "</div>" : "";
    const result = '<div class="fg">' + label + control + note + "</div>";
    return result;
}

function checkHtml(bind, on, text, small) {
    const id = "c_" + stableId(bind);
    const result = '<label class="check" for="' + id + '"><input type="checkbox" id="' + id + '" data-bind="' + bindAttr(bind) + '"' + (on ? " checked" : "") + ">" +
        "<span>" + text + (small ? "<small>" + small + "</small>" : "") + "</span></label>";
    return result;
}

function renderInspector() {
    const host = document.getElementById("insp");
    let html = "";
    if (selected === "page") {
        html = pageInspector();
    } else if (findFormula(selected)) {
        html = formulaInspector(findFormula(selected));
    } else {
        const hit = findNode(selected);
        if (hit) {
            html = nodeInspector(hit);
        } else {
            selected = "page";
            html = pageInspector();
        }
    }
    host.innerHTML = html;
}

function pageInspector() {
    const base = state.base;
    let html = '<div class="insp-title"><span class="kind">Page</span><span class="big">' + esc(phraseText(base, "label") || cap(base)) + "</span></div>";
    html += '<div class="triple">page phrase <b>' + esc(state.page || "—") + "</b><br>lists and texts read from <b>" + esc(base) + "</b></div>";
    html += inputHtml({t: "ph", p: base, f: "label"}, phraseText(base, "label"), {
        label: "Page title",
        placeholder: cap(base),
        note: "<span class=\"mono\">" + esc(base) + " label</span>"
    });
    html += inputHtml({t: "ph", p: base, f: "hint"}, phraseText(base, "hint"), {
        label: "Lead text",
        area: true,
        note: "<span class=\"mono\">" + esc(base) + " hint</span>"
    });
    html += '<div class="box"><div class="box-head"><h4>Headings of the lists</h4></div>';
    LISTS.forEach(function (L) {
        const g = state.lists[L.key];
        const fb = L.fallback ? L.fallback : "no heading";
        html += inputHtml({t: "grp", l: L.key}, g.label.text, {
            label: esc(L.title),
            placeholder: fb,
            note: "label of <span class=\"mono\">" + esc(groupName(L.key)) + "</span>"
        });
    });
    html += "</div>";
    html += '<div class="note" style="font-size:12px;color:var(--muted)">Select an item in the structure or click a part of the preview to change it. Drag items by the grip to reorder them.</div>';
    return html;
}

function nodeInspector(hit) {
    const n = hit.node;
    const K = KIND[n.kind];
    const info = labelInfo(n, hit.parent);
    const hint = hintInfo(n);
    const hl = highlightInfo(n);
    const parentName = parentNameOf(hit);
    let html = '<div class="insp-title"><span class="kind">' + esc(K.name) + '</span><span class="big">' + esc(info.text) + "</span></div>";
    html += inputHtml({t: "node", uid: n.uid, f: "to"}, n.to, {
        label: "Phrase", phrases: true, mono: true, id: "phrase_" + n.uid,
        placeholder: "word or triple",
        note: n.to && !IDX.known.has(n.to) ? "Not in the file yet: it is added to <span class=\"mono\">words</span> on save." : ""
    });
    const labelNote = n.label.text ? "Shown on this page only. Clear it to use " + esc(labelInfo(Object.assign({}, n, {label: newProp(null, null, "")}), hit.parent).from) + "." : "Empty: shows " + esc(info.from) + ".";
    html += inputHtml({t: "node", uid: n.uid, f: "label"}, n.label.text, {
        label: "Label on this page",
        placeholder: n.label.text ? "" : info.text,
        note: labelNote
    });
    if (K.hint || n.hint.text) {
        const hintNote = hint.from && hint.from !== "item" ? "Empty: shows " + esc(hint.from) + "." : "";
        html += inputHtml({t: "node", uid: n.uid, f: "hint"}, n.hint.text, {
            label: "Hint on this page",
            area: true,
            placeholder: n.hint.text ? "" : hint.text,
            note: hintNote
        });
    }
    if (K.highlight || n.highlight.on) {
        const small = !n.highlight.on && hl.on ? "Already a key row by " + esc(hl.from) + "." : "Shows the row in bold (calculator highlight).";
        html += checkHtml({t: "node", uid: n.uid, f: "highlight"}, n.highlight.on, "Key row on this page", small);
    }
    if (K.signed || n.signed.on) {
        html += checkHtml({
            t: "node",
            uid: n.uid,
            f: "signed"
        }, n.signed.on, "Show the sign of the numbers", "Positive and negative cells get their own colour (calculator signed).");
    }
    html += '<div class="triple"><b>' + esc(n.name || "—") + "</b> = " + esc(parentName) + " <i>" + esc(n.verb) + "</i> " + esc(n.to || "…") + "</div>";
    html += inputHtml({t: "node", uid: n.uid, f: "name"}, n.name, {
        label: "Triple name" + (n.nameAuto ? ' <span class="chip">follows the phrase</span>' : ""), mono: true,
        note: "The label, hint, order and sub-item triples are named after it."
    });
    if (n.kind === "input" || n.kind === "assumption") {
        html += exampleValueHtml(n);
    }
    if (n.to) {
        html += phraseBox(n);
    }
    html += '<div class="danger-zone"><button class="btn danger" data-act="remove" data-uid="' + n.uid + '">Remove ' + esc(K.name.toLowerCase()) + "</button></div>";
    return html;
}

function exampleValueHtml(n) {
    let html = "";
    const f = state.phrases[n.to] ? state.phrases[n.to].field : null;
    const isSelector = f && f.type === FIELD.selector;
    const entry = n.to && !isSelector ? fieldValue(n.to) : null;
    if (entry) {
        html = inputHtml({t: "val", v: entry.vidx}, poolNumber(entry), {
            label: "Shown number", type: "number",
            note: "The value (" + esc(entry.words.join(", ")) + "). A change here changes the stored value."
        });
    }
    return html;
}

function phraseBox(n) {
    const x = n.to;
    const p = phrase(x);
    const K = KIND[n.kind];
    let html = '<div class="box shared"><div class="box-head"><h4>Phrase “' + esc(x) + '”</h4><span class="chip shared">shared</span>' +
        "<p>These settings belong to the phrase. They change every calculator that shows “" + esc(x) + "”.</p></div>";
    html += inputHtml({t: "ph", p: x, f: "label"}, p.label.text, {label: "General label", placeholder: cap(x)});
    html += inputHtml({t: "ph", p: x, f: "hint"}, p.hint.text, {label: "General hint", area: true});
    if (n.kind === "row" || n.kind === "filter") {
        html += checkHtml({t: "ph", p: x, f: "highlight"}, p.highlight.on, "Key figure on every page");
        html += sideHtml(x, p);
    }
    if (K.field) {
        html += fieldHtml(x, p);
    }
    html += "</div>";
    return html;
}

function sideHtml(x, p) {
    let html = '<div class="fg"><span class="lab">Side results <span class="note">shown in the sub text of the row</span></span><div class="mini">';
    p.side.forEach(function (s) {
        html += '<div class="mini-row two">' +
            '<input class="inp mono" list="phraseList" data-bind="' + bindAttr({
                t: "side",
                p: x,
                uid: s.uid
            }) + '" value="' + esc(s.to) + '" aria-label="side result phrase">' +
            '<button class="ico rm" data-act="rmSide" data-p="' + esc(x) + '" data-uid="' + s.uid + '" aria-label="Remove side result">✕</button></div>';
    });
    html += '</div><div><button class="addlink" data-act="addSide" data-p="' + esc(x) + '">+ side result</button></div></div>';
    return html;
}

function fieldHtml(x, p) {
    const f = p.field;
    let html = '<div class="fg"><label for="ftype_' + esc(x) + '">Field type</label>' +
        '<select class="inp" id="ftype_' + esc(x) + '" data-bind="' + bindAttr({t: "ph", p: x, f: "type"}) + '">' +
        '<option value=""' + (f.type === "" ? " selected" : "") + ">Number (default)</option>" +
        '<option value="' + FIELD.number + '"' + (f.type === FIELD.number ? " selected" : "") + ">Number with limits</option>" +
        '<option value="' + FIELD.selector + '"' + (f.type === FIELD.selector ? " selected" : "") + ">Phrase selector</option>" +
        "</select></div>";
    if (f.type !== FIELD.selector) {
        html += '<div class="row2">' +
            inputHtml({t: "lim", p: x, k: "min"}, f.limits.min.num, {label: "Minimum", type: "number"}) +
            inputHtml({t: "lim", p: x, k: "max"}, f.limits.max.num, {label: "Maximum", type: "number"}) +
            inputHtml({t: "lim", p: x, k: "step"}, f.limits.step.num, {label: "Step", type: "number"}) +
            "</div>";
    }
    html += '<div class="fg"><span class="lab">Value phrases <span class="note">the stored value must have these phrases too</span></span><div class="mini">';
    f.valuePhrases.forEach(function (v) {
        html += '<div class="mini-row two"><input class="inp mono" list="phraseList" data-bind="' + bindAttr({
                t: "vp",
                p: x,
                uid: v.uid
            }) + '" value="' + esc(v.to) + '" aria-label="value phrase">' +
            '<button class="ico rm" data-act="rmVp" data-p="' + esc(x) + '" data-uid="' + v.uid + '" aria-label="Remove value phrase">✕</button></div>';
    });
    html += '</div><div><button class="addlink" data-act="addVp" data-p="' + esc(x) + '">+ value phrase</button></div></div>';
    if (f.type === FIELD.selector) {
        html += optionsHtml(x, f);
    }
    return html;
}

function optionChoices(x, f) {
    const result = [];
    f.options.forEach(function (o) {
        if (o.to) {
            result.push({phrase: o.to, label: o.label.text || cap(o.to)});
        }
    });
    f.isa.forEach(function (o) {
        if (o.from) {
            result.push({phrase: o.from, label: phraseText(o.from, "label") || cap(o.from)});
        }
    });
    if (result.length === 0 && f.valueBased) {
        valueBasedOptions(x).forEach(function (op) {
            result.push(op);
        });
    }
    return result;
}

function valueBasedOptions(x) {
    const seen = new Set();
    const result = [];
    IDX.pool.forEach(function (e) {
        const ph = optionPhrase(e, x);
        if (ph && !seen.has(ph)) {
            seen.add(ph);
            result.push({phrase: ph, label: cap(ph)});
        }
    });
    result.sort(function (a, b) {
        return a.phrase.localeCompare(b.phrase);
    });
    return result;
}

/* the option phrase of a value of a value based selector e.g. east-west of "yield factor, east-west, percent"; */
/* the api sends the words in any order, so the option is the one word that is neither x nor a unit */
function optionPhrase(e, x) {
    let result = "";
    if (e.vidx !== undefined && e.words.length === 3 && e.words.indexOf(x) >= 0) {
        const rest = e.words.filter(function (w) {
            return w !== x && !isUnit(w);
        });
        if (rest.length === 1) {
            result = rest[0];
        }
    }
    return result;
}

function isUnit(w) {
    const result = IDX.unitNames.has(w) || IDX.symbolFor.has(w);
    return result;
}

function optionsHtml(x, f) {
    let html = '<div class="fg"><span class="lab">Options <span class="note">“' + esc(x) + '” has a …, in this order</span></span><div class="mini">';
    f.options.forEach(function (o, i) {
        html += '<div class="mini-row">' +
            '<input class="inp mono" list="phraseList" data-bind="' + bindAttr({
                t: "opt",
                p: x,
                uid: o.uid,
                f: "to"
            }) + '" value="' + esc(o.to) + '" aria-label="option phrase">' +
            '<input class="inp" data-bind="' + bindAttr({
                t: "opt",
                p: x,
                uid: o.uid,
                f: "label"
            }) + '" value="' + esc(o.label.text) + '" placeholder="' + esc(cap(o.to)) + '" aria-label="option label">' +
            '<span style="display:flex">' +
            '<button class="ico" data-act="optUp" data-p="' + esc(x) + '" data-uid="' + o.uid + '" aria-label="Move option up"' + (i === 0 ? " disabled" : "") + ">↑</button>" +
            '<button class="ico rm" data-act="rmOpt" data-p="' + esc(x) + '" data-uid="' + o.uid + '" aria-label="Remove option">✕</button></span></div>';
    });
    html += '</div><div><button class="addlink" data-act="addOpt" data-p="' + esc(x) + '">+ option</button></div></div>';
    html += '<div class="fg"><span class="lab">Options by “is a” <span class="note">… is a “' + esc(x) + '”</span></span><div class="mini">';
    f.isa.forEach(function (o, i) {
        html += '<div class="mini-row two">' +
            '<input class="inp mono" list="phraseList" data-bind="' + bindAttr({
                t: "isa",
                p: x,
                uid: o.uid
            }) + '" value="' + esc(o.from) + '" aria-label="is a option phrase">' +
            '<span style="display:flex">' +
            '<button class="ico" data-act="isaUp" data-p="' + esc(x) + '" data-uid="' + o.uid + '" aria-label="Move option up"' + (i === 0 ? " disabled" : "") + ">↑</button>" +
            '<button class="ico rm" data-act="rmIsa" data-p="' + esc(x) + '" data-uid="' + o.uid + '" aria-label="Remove option">✕</button></span></div>';
    });
    html += '</div><div><button class="addlink" data-act="addIsa" data-p="' + esc(x) + '">+ “is a” option</button></div></div>';
    html += checkHtml({
        t: "ph",
        p: x,
        f: "valueBased"
    }, f.valueBased, "Take the options from the values", "Used when no option is listed: every phrase with a value of “" + esc(x) + "”.");
    const choices = optionChoices(x, f);
    html += '<div class="fg"><label for="uses_' + esc(x) + '">Selected on “' + esc(state.page) + '”</label>' +
        '<select class="inp" id="uses_' + esc(x) + '" data-bind="' + bindAttr({t: "ph", p: x, f: "uses"}) + '">' +
        '<option value="">— first option —</option>' +
        choices.map(function (c) {
            return '<option value="' + esc(c.phrase) + '"' + (c.phrase === f.uses.to ? " selected" : "") + ">" + esc(c.label) + "</option>";
        }).join("") +
        '</select><div class="note">The page phrase <i>uses</i> this option.</div></div>';
    return html;
}

/* ------------------------------------------------------------------ */
/* rendering: the preview of the calculator page                        */
/* ------------------------------------------------------------------ */
function selAttr(id) {
    const result = ' data-sel="' + id + '"';
    return result;
}

function previewField(n, inInputs) {
    const info = labelInfo(n, null);
    const hint = hintInfo(n);
    const p = state.phrases[n.to];
    const f = p ? p.field : null;
    let body = "";
    let tag = "";
    let hintText = hint.text;
    if (f && f.type === FIELD.selector) {
        const choices = optionChoices(n.to, f);
        const opts = choices.map(function (c) {
            const val = findEntry([n.to, c.phrase], {valuesOnly: true}) || findEntry([c.phrase], {valuesOnly: true});
            const v = val ? poolNumber(val) : "";
            return "<option" + (c.phrase === f.uses.to ? " selected" : "") + ' value="' + esc(v) + '">' + esc(c.label) + "</option>";
        }).join("");
        body = "<select tabindex=\"-1\">" + (opts || "<option>no options</option>") + "</select>";
    } else {
        const entry = fieldValue(n.to);
        const num = entry ? poolNumber(entry) : "";
        const unit = entry ? unitOf(entry.words) : "";
        const lim = f ? f.limits : null;
        const attrs = lim ? (lim.min.num !== "" ? ' min="' + esc(lim.min.num) + '"' : "") + (lim.max.num !== "" ? ' max="' + esc(lim.max.num) + '"' : "") + (lim.step.num !== "" ? ' step="' + esc(lim.step.num) + '"' : "") : "";
        body = '<div class="row"><input type="number" tabindex="-1" value="' + esc(num) + '"' + attrs + ">" + (unit ? '<span class="unit">' + esc(unit) + "</span>" : "") + "</div>";
        if (!inInputs && entry) {
            const conf = confidenceOf(n.to, entry);
            if (entry.source) {
                tag = '<span class="tag m">measured</span>';
                const sn = sourceNumber(entry.source);
                if (sn && hintText) {
                    hintText = hintText + " [" + sn + "]";
                }
            } else if (entry.words.indexOf("assumed") >= 0) {
                tag = '<span class="tag a">assumed' + (conf ? " " + esc(conf) : "") + "</span>";
            }
        }
        if (!entry) {
            body += '<span class="hint">no value with “' + esc(n.to) + '” in the file</span>';
        }
    }
    const result = '<div class="field"' + selAttr(n.uid) + "><label>" + esc(info.text) + tag + "</label>" + body + (hintText ? '<span class="hint">' + esc(hintText) + "</span>" : "") + "</div>";
    return result;
}

function rowSub(n) {
    const hint = hintInfo(n).text;
    const parts = [];
    if (hint) {
        parts.push(esc(hint));
    }
    const sideFrom = [n.to].concat(filtersOf(n));
    sideFrom.forEach(function (ph) {
        const p = state.phrases[ph];
        if (p) {
            p.side.forEach(function (s) {
                if (s.to) {
                    const e = findEntry([s.to, n.to].concat(filtersOf(n).filter(function (f) {
                        return f !== ph;
                    })), {needPage: true});
                    const label = phraseText(s.to, "label") || s.to;
                    if (e) {
                        parts.push(esc(label) + " " + esc(fmtNumber(poolNumber(e), unitOf(e.words))));
                    }
                }
            });
        }
    });
    const result = parts.length > 0 ? '<span class="sub">' + parts.join(" · ") + "</span>" : "";
    return result;
}

function cellHtml(required, signed) {
    const e = findEntry(required, {needPage: true});
    let result = '<td class="n"></td>';
    if (e) {
        const num = poolNumber(e);
        const text = esc(fmtNumber(num, unitOf(e.words)));
        if (signed) {
            result = '<td class="n"><span class="' + (Number(num) < 0 ? "neg" : "pos") + '">' + text + "</span></td>";
        } else {
            result = '<td class="n">' + text + "</td>";
        }
    }
    return result;
}

/* the start of a collapsed block of the preview, open only if the user has opened it, because the preview is drawn again after each change */
function previewDetailsStart(key) {
    const open = openPreview.has(key) ? " open" : "";
    const result = '<details data-preview="' + key + '"' + open + ">";
    return result;
}

function previewPage() {
    const base = state.base;
    const cols = state.lists.column.items.filter(function (c) {
        return c.to;
    });
    let html = '<div class="legend"><span><i></i>click a part to edit it</span><span>numbers come from the values and the results of the file</span></div>';
    html += '<div class="use-case">';
    html += '<header data-sel="page"><h4>' + esc(phraseText(base, "label") || cap(base)) + "</h4>" +
        (phraseText(base, "hint") ? "<p>" + esc(phraseText(base, "hint")) + "</p>" : "") + "</header>";
    html += '<div class="grid"><aside class="panel"><div class="inputs"><h2>' + esc(groupLabel("input")) + "</h2>";
    const inputs = state.lists.input.items.filter(function (n) {
        return n.to;
    });
    html += inputs.map(function (n) {
        return previewField(n, true);
    }).join("") || '<div class="empty-list">No inputs.</div>';
    html += '<button type="button" class="btn-save" tabindex="-1">Save</button></div>';
    html += previewDetailsStart("assumption") + "<summary>" + esc(groupLabel("assumption")) + "</summary>";
    const assumptions = state.lists.assumption.items.filter(function (n) {
        return n.to;
    });
    html += assumptions.map(function (n) {
        return previewField(n, false);
    }).join("") || '<div class="empty-list">No assumptions listed.</div>';
    html += '<p class="derived">+ the other operands of the results, derived from the formulas</p></details></aside>';
    html += '<section class="panel">';
    if (groupLabel("column")) {
        html += "<h2>" + esc(groupLabel("column")) + "</h2>";
    }
    html += '<div class="scroll"><table><thead><tr><th>Result</th>' +
        cols.map(function (c) {
            return '<th class="n"' + selAttr(c.uid) + ">" + esc(labelInfo(c, null).text) + "</th>";
        }).join("") + "</tr></thead><tbody>";
    const span = cols.length + 1;
    state.lists.rowgroup.items.forEach(function (g) {
        if (g.to) {
            html += '<tr class="sec" data-sel="' + g.uid + '"><td colspan="' + span + '">' + esc(labelInfo(g, null).text) + "</td></tr>";
            g.children.forEach(function (r) {
                if (r.to && r.kind === "row") {
                    const key = highlightInfo(r).on;
                    const filters = filtersOf(r);
                    const cls = [];
                    if (key) {
                        cls.push("key");
                    }
                    if (selected === r.uid) {
                        cls.push("is-sel");
                    }
                    html += '<tr data-sel="' + r.uid + '"' + (cls.length ? ' class="' + cls.join(" ") + '"' : "") + "><td>" + esc(labelInfo(r, g).text) + rowSub(r) + "</td>" +
                        cols.map(function (c) {
                            return cellHtml([r.to].concat(filters).concat([c.to]), g.signed.on);
                        }).join("") + "</tr>";
                }
            });
        }
    });
    if (state.lists.rowgroup.items.length === 0) {
        html += '<tr><td colspan="' + span + '" class="miss">No row groups.</td></tr>';
    }
    html += "</tbody></table></div>";
    const details = state.lists.detail.items.filter(function (d) {
        return d.to;
    });
    html += previewDetailsStart("detail") + "<summary>" + esc(groupLabel("detail") || "Details") + '</summary><div class="more">';
    details.forEach(function (d) {
        html += detailTable(d);
    });
    html += notesHtml() + sourcesHtml() + "</div></details>";
    html += "</section></div></div>";
    return html;
}

function detailTable(d) {
    const colsD = d.children.filter(function (c) {
        return c.kind === "dcol" && c.to;
    });
    const extra = d.children.filter(function (c) {
        return c.kind === "dextra" && c.to;
    });
    const rowPhrases = IDX.T.filter(function (t) {
        return t.verb === "is a" && t.to === d.to;
    }).map(function (t) {
        return t.from;
    });
    let html = "<h2" + selAttr(d.uid) + ">" + esc(labelInfo(d, null).text) + "</h2>";
    html += '<div class="scroll"><table><thead><tr><th>' + esc(cap(d.to)) + "</th>" +
        colsD.map(function (c) {
            return '<th class="n"' + selAttr(c.uid) + ">" + esc(labelInfo(c, null).text) + "</th>";
        }).join("") + "</tr></thead><tbody>";
    rowPhrases.forEach(function (rp) {
        html += "<tr><td>" + esc(phraseText(rp, "label") || cap(rp)) + "</td>" + colsD.map(function (c) {
            return cellHtml([c.to, rp].concat(filtersOf(c)), false);
        }).join("") + "</tr>";
    });
    extra.forEach(function (x) {
        const key = highlightInfo(x).on;
        const cls = [];
        if (key) {
            cls.push("key");
        }
        if (selected === x.uid) {
            cls.push("is-sel");
        }
        html += '<tr data-sel="' + x.uid + '"' + (cls.length ? ' class="' + cls.join(" ") + '"' : "") + "><td>" + esc(labelInfo(x, null).text) + "</td>" +
            colsD.map(function (c) {
                return cellHtml([c.to, x.to].concat(filtersOf(c)), false);
            }).join("") + "</tr>";
    });
    if (rowPhrases.length === 0 && extra.length === 0) {
        html += '<tr><td colspan="' + (colsD.length + 1) + '" class="miss">No rows: no phrase <i>is a</i> “' + esc(d.to) + "”.</td></tr>";
    } else if (rowPhrases.length === 0) {
        html += '<tr><td colspan="' + (colsD.length + 1) + '" class="miss"><small>The rows of the table are the phrases that <i>is a</i> “' + esc(d.to) + "”; the file has none yet.</small></td></tr>";
    }
    html += "</tbody></table></div>";
    return html;
}

/* ------------------------------------------------------------------ */
/* formulas                                                             */
/* ------------------------------------------------------------------ */
/* the selection key of a formula in the structure, so that it never matches the uid of an item */
const FORMULA_SEL = "formula:";
const FORMULA_PHRASE = /"([^"]+)"/g;

/* the expression of a formula with the change of this session e.g. "autarky" = ( "direct use" + ... ) / ... */
function formulaExpression(f) {
    const edits = state.formulaEdits || {};
    const result = edits[f.id] !== undefined ? edits[f.id] : (f.expression || "");
    return result;
}

/* the phrase names of an expression: the phrases left of the "=" get the result, the others are the operands */
function expressionPhrases(expression) {
    const text = String(expression);
    const pos = text.indexOf("=");
    const names = function (part) {
        return Array.from(part.matchAll(FORMULA_PHRASE), function (m) {
            return m[1];
        });
    };
    const result = {
        result: pos < 0 ? [] : names(text.slice(0, pos)),
        operands: names(pos < 0 ? text : text.slice(pos + 1))
    };
    return result;
}

/* the formulas that a number of the phrase depends on: every formula whose result is the phrase,
   because e.g. a result can be calculated by one formula per mix, and then the formulas of their
   operands, each formula once and in the order of the calculation chain */
function formulasUsed(name) {
    const result = [];
    const seen = new Set();

    function visit(phr) {
        (DOC.formulas || []).forEach(function (f) {
            if (!seen.has(f.id) && expressionPhrases(formulaExpression(f)).result[0] === phr) {
                seen.add(f.id);
                result.push(f);
                expressionPhrases(formulaExpression(f)).operands.forEach(visit);
            }
        });
    }

    visit(name);
    return result;
}

/* the formula of a selection key of the structure or null if the key selects no formula */
function findFormula(sel) {
    const key = String(sel);
    const id = key.indexOf(FORMULA_SEL) === 0 ? key.slice(FORMULA_SEL.length) : null;
    const result = id === null ? null : (DOC.formulas || []).find(function (f) {
        return String(f.id) === id;
    }) || null;
    return result;
}

function formulaRow(f) {
    const key = FORMULA_SEL + f.id;
    const sel = selected === key ? " sel" : "";
    const changed = formulaExpression(f) !== (f.expression || "") ? '<span class="chip new">changed</span>' : "";
    const result = '<div class="node formula' + sel + '" data-uid="' + esc(key) + '" tabindex="0" role="treeitem" aria-selected="' + (sel ? "true" : "false") + '">' +
        '<span class="txt"><span class="lbl">' + esc(f.name) + "</span>" +
        '<span class="ph mono">' + esc(formulaExpression(f)) + "</span></span>" +
        '<span class="side">' + changed + "</span></div>";
    return result;
}

/* the number of the phrases that the page shows, with its unit, as a sample input or result of a formula */
function sampleNumber(names) {
    const entry = findEntry(names);
    const result = entry ? fmtNumber(poolNumber(entry), unitOf(entry.words)) : "—";
    return result;
}

function formulaInspector(f) {
    const exp = formulaExpression(f);
    const phr = expressionPhrases(exp);
    const changed = exp !== (f.expression || "");
    let html = '<div class="insp-title"><span class="kind">Formula</span><span class="big">' + esc(f.name) + "</span></div>";
    html += inputHtml({t: "frm", id: f.id}, exp, {
        label: "Formula", area: true, mono: true, id: "formula_" + f.id,
        note: changed ? "Changed: Save stores it as your own version of the formula." :
            "The phrase left of the = gets the result; each operand is a phrase in quotes."
    });
    if (f.description) {
        html += '<div class="note">' + esc(f.description) + "</div>";
    }
    const rows = phr.operands.filter(function (name, i, all) {
        return all.indexOf(name) === i;
    }).map(function (name) {
        return '<tr><td>' + esc(name) + '</td><td class="n">' + esc(sampleNumber([name])) + "</td></tr>";
    }).join("");
    html += '<div class="box"><div class="box-head"><h4>Sample input values</h4></div>';
    html += rows ? '<table class="samples"><tbody>' + rows + "</tbody></table>" : '<div class="note">The formula has no operand.</div>';
    if (phr.result.length > 0) {
        html += '<div class="note">The page shows ' + esc(phr.result.join(", ")) + " = " + esc(sampleNumber(phr.result)) +
            (changed ? ", calculated with the stored formula" : "") + ".</div>";
    }
    html += "</div>";
    return html;
}

function notesHtml() {
    const notes = (DOC.formulas || []).map(function (f) {
        return "<p><b>" + esc(f.name) + "</b> " + esc(f.description || "") + "</p>";
    }).join("");
    const result = notes ? '<div class="notes">' + notes + '<p class="derived">derived from the formulas; a formula is changed in the structure below the result row it calculates</p></div>' : "";
    return result;
}

function sourcesHtml() {
    const items = (DOC.sources || []).map(function (s) {
        return "<li>" + (s.url ? '<a href="' + esc(s.url) + '" target="_blank" rel="noopener">' + esc(s.name) + "</a>" : esc(s.name)) + "</li>";
    }).join("");
    const result = items ? '<div class="src"><h2>Sources</h2><ol>' + items + "</ol></div>" : "";
    return result;
}

/* ------------------------------------------------------------------ */
/* rendering: texts, changes and json                                   */
/* ------------------------------------------------------------------ */
function textsPane() {
    const rows = [];
    rows.push({
        own: state.base + " label",
        where: "page title",
        bind: {t: "ph", p: state.base, f: "label"},
        text: phraseText(state.base, "label"),
        shared: false
    });
    rows.push({
        own: state.base + " hint",
        where: "lead text",
        bind: {t: "ph", p: state.base, f: "hint"},
        text: phraseText(state.base, "hint"),
        shared: false
    });
    LISTS.forEach(function (L) {
        rows.push({
            own: groupName(L.key) + " label",
            where: L.title + " heading",
            bind: {t: "grp", l: L.key},
            text: state.lists[L.key].label.text,
            shared: false,
            ph: L.fallback
        });
    });
    walkAll(function (n, parent) {
        const info = labelInfo(n, parent);
        rows.push({
            own: n.name + " label",
            where: KIND[n.kind].name,
            bind: {t: "node", uid: n.uid, f: "label"},
            text: n.label.text,
            shared: false,
            ph: info.from === "item" ? "" : info.text
        });
        if (KIND[n.kind].hint || n.hint.text) {
            rows.push({
                own: n.name + " hint",
                where: KIND[n.kind].name + " hint",
                bind: {t: "node", uid: n.uid, f: "hint"},
                text: n.hint.text,
                shared: false,
                ph: hintInfo(n).from === "item" ? "" : hintInfo(n).text
            });
        }
    });
    Object.keys(state.phrases).sort().forEach(function (x) {
        const p = state.phrases[x];
        if (x !== state.base && p.label.text) {
            rows.push({
                own: x + " label",
                where: "phrase label",
                bind: {t: "ph", p: x, f: "label"},
                text: p.label.text,
                shared: true
            });
        }
        if (x !== state.base && p.hint.text) {
            rows.push({
                own: x + " hint",
                where: "phrase hint",
                bind: {t: "ph", p: x, f: "hint"},
                text: p.hint.text,
                shared: true
            });
        }
        p.field.options.forEach(function (o) {
            if (o.label.text) {
                rows.push({
                    own: o.name + " label",
                    where: "option of " + x,
                    bind: {t: "opt", p: x, uid: o.uid, f: "label"},
                    text: o.label.text,
                    shared: true
                });
            }
        });
    });
    let html = '<p style="margin:0 0 12px;color:var(--muted);font-size:12.5px">Every label and hint of the page in one list, for proofreading. <span class="chip shared">shared</span> texts belong to a phrase and change every calculator that shows it.</p>';
    html += '<div class="scrollx"><table class="texts"><thead><tr><th>Triple</th><th>Text</th></tr></thead><tbody>';
    rows.forEach(function (r) {
        html += '<tr><td><div class="own">' + esc(r.own) + '</div><div style="font-size:11.5px;color:var(--muted)">' + esc(r.where) + (r.shared ? ' <span class="chip shared">shared</span>' : "") + "</div></td>" +
            '<td><input class="inp" data-bind="' + bindAttr(r.bind) + '" value="' + esc(r.text) + '" placeholder="' + esc(r.ph || "") + '" aria-label="' + esc(r.own) + '"></td></tr>';
    });
    html += "</tbody></table></div>";
    return html;
}

function tripleLine(t) {
    const result = (t.name ? t.name + " = " : "") + t.from + " · " + t.verb + " · " + t.to + (t.description && (t.to === KW.label || t.to === KW.hint) ? " → “" + t.description + "”" : "");
    return result;
}

function changesPane() {
    const o = lastOutput || buildOutput();
    const c = o.changes;
    let html = '<div class="chg">';
    if (o.warnings.length > 0) {
        html += "<div><h3>Check before saving</h3><ul>" + o.warnings.map(function (w) {
            return '<li class="wrn">' + esc(w) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (o.changed === 0) {
        html += '<p style="color:var(--muted);margin:0">No changes since the last save.</p>';
    } else if (SOURCE.mode === "api") {
        html += '<p style="color:var(--muted);margin:0">Save sends these changes to the database, one api call each.</p>';
    }
    if (c.add.length) {
        html += "<div><h3>Added triples (" + c.add.length + ")</h3><ul>" + c.add.map(function (t) {
            return '<li class="add">+ ' + esc(tripleLine(t)) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (c.mod.length) {
        html += "<div><h3>Changed triples (" + c.mod.length + ")</h3><ul>" + c.mod.map(function (m) {
            return '<li class="mod">' + esc(tripleLine(m.before)) + "<br>→ " + esc(tripleLine(m.after)) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (c.del.length) {
        html += "<div><h3>Removed triples (" + c.del.length + ")</h3><ul>" + c.del.map(function (d) {
            return '<li class="del">− ' + esc(tripleLine(d.t)) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (c.values.length) {
        html += "<div><h3>Values (" + c.values.length + ")</h3><ul>" + c.values.map(function (v) {
            return '<li class="' + v.kind + '">' + esc(v.text) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (c.words.length) {
        html += "<div><h3>New words (" + c.words.length + ")</h3><ul>" + c.words.map(function (w) {
            return '<li class="add">+ ' + esc(w) + "</li>";
        }).join("") + "</ul></div>";
    }
    if (c.formulas.length) {
        html += "<div><h3>Changed formulas (" + c.formulas.length + ")</h3><ul>" + c.formulas.map(function (f) {
            return '<li class="mod">' + esc(f.name) + ": " + esc(f.before) + "<br>→ " + esc(f.after) + "</li>";
        }).join("") + "</ul></div>";
    }
    html += "</div>";
    return html;
}

function renderPane() {
    const host = document.getElementById("pane");
    let html = "";
    if (activeTab === "page") {
        html = previewPage();
    } else if (activeTab === "texts") {
        html = textsPane();
    } else {
        html = changesPane();
    }
    host.innerHTML = html;
    host.querySelectorAll('[data-sel="' + selected + '"]').forEach(function (el) {
        el.classList.add("is-sel");
    });
    document.querySelectorAll(".tab").forEach(function (t) {
        t.setAttribute("aria-selected", t.dataset.tab === activeTab ? "true" : "false");
    });
}

function renderPhraseList() {
    const dl = document.getElementById("phraseList");
    const names = Array.from(IDX.known).filter(Boolean).sort(function (a, b) {
        return a.localeCompare(b);
    });
    dl.innerHTML = names.map(function (n) {
        return '<option value="' + esc(n) + '"></option>';
    }).join("");
}

function renderAll() {
    renderPhraseList();
    renderTree();
    renderInspector();
    renderPane();
    refreshStatus();
    document.querySelector(".work").hidden = !state.page;
    const phraseIn = document.getElementById("phraseIn");
    if (document.activeElement !== phraseIn) {
        phraseIn.value = state.page;
    }
    const chip = document.getElementById("srcChip");
    chip.hidden = SOURCE.mode !== "api";
    chip.className = "chip live";
    chip.textContent = "database";
    chip.title = DOC_NAME;
    const view = document.getElementById("btnView");
    view.hidden = SOURCE.mode !== "api" || !SOURCE.pageId;
    view.href = SOURCE.root + "http/view.php?m=calculator&id=" + SOURCE.pageId;
}

/* ------------------------------------------------------------------ */
/* actions                                                              */
/* ------------------------------------------------------------------ */
function newNode(kind) {
    const result = {
        uid: uid(), kind: kind, src: null, name: "", nameAuto: true, verb: KIND[kind].verb, to: "",
        label: newProp(null, null, ""), hint: newProp(null, null, ""),
        highlight: newFlag(null, null, false), signed: newFlag(null, null, false),
        orderSrc: null, orderName: null, orderPrev: null, children: []
    };
    return result;
}

function focusPhrase(id) {
    setTimeout(function () {
        const el = document.getElementById("f_phrase_" + id);
        if (el) {
            el.focus();
        }
    }, 30);
}

function addTop(listKey) {
    const n = newNode(listKey);
    openLists.add(listKey);
    mutate(function () {
        state.lists[listKey].items.push(n);
        n.name = uniqueName(conventionalName(n, groupName(listKey)), n);
        selected = n.uid;
    });
    focusPhrase(n.uid);
}

function addChild(parentId, kind) {
    const hit = findNode(parentId);
    const n = newNode(kind);
    if (hit) {
        mutate(function () {
            hit.node.children.push(n);
            n.name = uniqueName(conventionalName(n, hit.node.name), n);
            selected = n.uid;
        });
        focusPhrase(n.uid);
    }
}

function removeNode(id) {
    const hit = findNode(id);
    if (hit) {
        mutate(function () {
            hit.arr.splice(hit.index, 1);
            if (selected === id) {
                selected = hit.parent ? hit.parent.uid : "page";
            }
        });
        toast("Removed. Undo brings it back.");
    }
}

function moveNode(id, delta) {
    const hit = findNode(id);
    if (hit) {
        const j = hit.index + delta;
        if (j >= 0 && j < hit.arr.length) {
            mutate(function () {
                const item = hit.arr.splice(hit.index, 1)[0];
                hit.arr.splice(j, 0, item);
            });
        }
    }
}

/* drag and drop: an item can move within its list or to another list of the same kind */
function dropNode(dragId, targetId, after) {
    const src = findNode(dragId);
    const dst = findNode(targetId);
    const allowed = src && dst && src.node.kind === dst.node.kind && dragId !== targetId && !isInside(dst.node, src.node);
    if (allowed) {
        mutate(function () {
            src.arr.splice(src.index, 1);
            const again = findNode(targetId);
            const pos = again.index + (after ? 1 : 0);
            again.arr.splice(pos, 0, src.node);
        });
    }
}

function isInside(node, maybeAncestor) {
    let result = false;

    function walk(n) {
        n.children.forEach(function (c) {
            if (c === node) {
                result = true;
            }
            walk(c);
        });
    }

    walk(maybeAncestor);
    return result;
}

function phraseListAction(act, x, id) {
    const p = phrase(x);
    const f = p.field;
    mutate(function () {
        if (act === "addSide") {
            p.side.push({uid: uid(), src: null, name: null, to: ""});
        } else if (act === "rmSide") {
            p.side = p.side.filter(function (s) {
                return s.uid !== id;
            });
        } else if (act === "addVp") {
            f.valuePhrases.push({uid: uid(), src: null, name: null, to: "", fvpSrc: null, fvpName: null});
        } else if (act === "rmVp") {
            f.valuePhrases = f.valuePhrases.filter(function (v) {
                return v.uid !== id;
            });
        } else if (act === "addOpt") {
            f.options.push({
                uid: uid(),
                src: null,
                name: "",
                nameAuto: true,
                to: "",
                label: newProp(null, null, ""),
                orderSrc: null,
                orderName: null,
                orderPrev: null
            });
        } else if (act === "rmOpt") {
            f.options = f.options.filter(function (o) {
                return o.uid !== id;
            });
        } else if (act === "optUp") {
            moveInArray(f.options, id, -1);
        } else if (act === "addIsa") {
            f.isa.push({
                uid: uid(),
                src: null,
                name: null,
                from: "",
                orderSrc: null,
                orderName: null,
                orderPrev: null
            });
        } else if (act === "rmIsa") {
            f.isa = f.isa.filter(function (o) {
                return o.uid !== id;
            });
        } else if (act === "isaUp") {
            moveInArray(f.isa, id, -1);
        }
    });
}

function moveInArray(arr, id, delta) {
    const i = arr.findIndex(function (o) {
        return o.uid === id;
    });
    const j = i + delta;
    if (i >= 0 && j >= 0 && j < arr.length) {
        const item = arr.splice(i, 1)[0];
        arr.splice(j, 0, item);
    }
}

/* one setter for every bound input */
function readEl(el) {
    let result = el.value;
    if (el.type === "checkbox") {
        result = el.checked;
    }
    return result;
}

function setBound(b, value) {
    if (b.t === "node") {
        const hit = findNode(b.uid);
        if (hit) {
            setNodeField(hit, b.f, value);
        }
    } else if (b.t === "grp") {
        state.lists[b.l].label.text = value;
    } else if (b.t === "ph") {
        setPhraseField(b.p, b.f, value);
    } else if (b.t === "lim") {
        phrase(b.p).field.limits[b.k].num = String(value).trim();
    } else if (b.t === "val") {
        const orig = (DOC.values || [])[b.v];
        if (orig && String(orig.number) === String(value)) {
            delete state.valueEdits[b.v];
        } else {
            state.valueEdits[b.v] = String(value).trim();
        }
    } else if (b.t === "frm") {
        /* a draft of an older editor has no formula edits yet */
        state.formulaEdits = state.formulaEdits || {};
        const f = findFormula(FORMULA_SEL + b.id);
        if (f && (f.expression || "") === value) {
            delete state.formulaEdits[b.id];
        } else {
            state.formulaEdits[b.id] = value;
        }
    } else if (b.t === "side") {
        const s = phrase(b.p).side.find(function (x) {
            return x.uid === b.uid;
        });
        if (s) {
            s.to = value.trim();
        }
    } else if (b.t === "vp") {
        const v = phrase(b.p).field.valuePhrases.find(function (x) {
            return x.uid === b.uid;
        });
        if (v) {
            v.to = value.trim();
        }
    } else if (b.t === "opt") {
        const o = phrase(b.p).field.options.find(function (x) {
            return x.uid === b.uid;
        });
        if (o && b.f === "to") {
            o.to = value.trim();
            if (o.nameAuto) {
                o.name = b.p + " option " + o.to;
            }
        } else if (o) {
            o.label.text = value;
        }
    } else if (b.t === "isa") {
        const o = phrase(b.p).field.isa.find(function (x) {
            return x.uid === b.uid;
        });
        if (o) {
            o.from = value.trim();
        }
    }
}

function setNodeField(hit, f, value) {
    const n = hit.node;
    if (f === "to") {
        n.to = value.trim();
        refreshAutoName(hit);
    } else if (f === "name") {
        const oldName = n.name;
        n.name = value.trim();
        n.nameAuto = false;
        renameRefs(oldName, n.name);
    } else if (f === "label") {
        n.label.text = value;
    } else if (f === "hint") {
        n.hint.text = value;
    } else if (f === "highlight") {
        n.highlight.on = Boolean(value);
    } else if (f === "signed") {
        n.signed.on = Boolean(value);
    }
}

function setPhraseField(x, f, value) {
    const p = phrase(x);
    if (f === "label") {
        p.label.text = value;
    } else if (f === "hint") {
        p.hint.text = value;
    } else if (f === "highlight") {
        p.highlight.on = Boolean(value);
    } else if (f === "type") {
        p.field.type = value;
    } else if (f === "valueBased") {
        p.field.valueBased = Boolean(value);
    } else if (f === "uses") {
        p.field.uses.to = value;
    }
}

/* ------------------------------------------------------------------ */
/* saving                                                               */
/* ------------------------------------------------------------------ */
function toast(msg) {
    const t = document.getElementById("toast");
    t.textContent = msg;
    t.classList.add("on");
    clearTimeout(toast.timer);
    toast.timer = setTimeout(function () {
        t.classList.remove("on");
    }, 2200);
}

/* ------------------------------------------------------------------ */
/* the api of the zukunft.com pod                                       */
/* ------------------------------------------------------------------ */
function apiUrl(route, params) {
    const p = params || {};
    const qs = Object.keys(p).filter(function (k) {
        return p[k] !== undefined && p[k] !== null && p[k] !== "";
    }).map(function (k) {
        return encodeURIComponent(k) + "=" + encodeURIComponent(p[k]);
    }).join("&");
    /* with the slash of the api directory, because the redirect of the server would turn a post into a get without the body */
    const result = SOURCE.base + route + "/" + (qs ? "?" + qs : "");
    return result;
}

/* the json of the pod, where an id beyond the safe integer of javascript e.g. the group id of a value
   is kept as text, so that a save sends the id unchanged; a shorter id stays a number */
function parseJson(text) {
    const result = JSON.parse(text.replace(/("id":\s*)(-?\d{16,})(?=\s*[,}\]])/g, '$1"$2"'));
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

function asList(json, key) {
    const body = unwrap(json);
    let result = [];
    if (Array.isArray(body)) {
        result = body;
    } else if (body && Array.isArray(body[key])) {
        result = body[key];
    }
    return result;
}

/* one api call; never throws, the answer says if it worked */
async function apiCall(method, route, params, body) {
    const opts = {method: method, credentials: "same-origin", headers: {Accept: "application/json"}};
    if (body !== undefined) {
        opts.headers["Content-Type"] = "application/json";
        opts.body = JSON.stringify(body);
    }
    const token = document.querySelector('input[name="' + SESSION_TOKEN_NAME + '"]');
    if (method !== "GET" && token) {
        opts.headers[CSRF_HEADER] = token.value;
    }
    let result = {ok: false, status: 0, json: null, message: ""};
    try {
        const resp = await fetch(apiUrl(route, params), opts);
        const text = await resp.text();
        let json = null;
        try {
            json = text ? parseJson(text) : null;
        } catch (e) {
            json = null;
        }
        let message = "";
        /* the reason of a refusal, which the pod sends as json_fields::MSG */
        if (json && !Array.isArray(json) && json.message) {
            message = String(json.message);
        } else if (!resp.ok) {
            message = "the pod answered " + resp.status + " " + resp.statusText + " for " + method + " " + route;
        }
        result = {ok: resp.ok && !(json && json.message), status: resp.status, json: json, message: message};
    } catch (e) {
        result = {ok: false, status: 0, json: null, message: "the pod could not be reached (" + e.message + ")"};
    }
    return result;
}

/* a reference of the update put message by its database id, or by its name only if it has no id yet
   e.g. a word that the same message adds */
function addRef(json, idKey, nameKey, id, name) {
    if (id !== undefined) {
        json[idKey] = id;
    } else {
        json[nameKey] = name;
    }
}

/* a triple of the update put message with the phrases and the verb by their database id if known */
function tripleJson(t, id) {
    const json = {};
    if (id) {
        json.id = id;
    }
    if (t.name) {
        json.name = t.name;
    }
    if (t.description) {
        json.description = t.description;
    }
    addRef(json, "from_id", "from", SOURCE.phraseIds.get(t.from), t.from);
    addRef(json, "verb_id", "verb", SOURCE.verbIds.get(t.verb), t.verb);
    addRef(json, "to_id", "to", SOURCE.phraseIds.get(t.to), t.to);
    return json;
}

/* a new value of the update put message with its phrases by their database id if known, else by their name */
function valueJson(v) {
    const json = {};
    v.words.forEach(function (w) {
        const id = SOURCE.phraseIds.get(w);
        if (id !== undefined) {
            json.phrases = (json.phrases || []).concat([{id: id}]);
        } else {
            json.words = (json.words || []).concat([w]);
        }
    });
    json.number = Number(v.number);
    return json;
}

/* the message of a save for the updatePut api: the api header and the changes in the import format
   with the database ids; the sections are saved in the order new words, triples, values and deletes,
   so a triple can use a new word, and the removed triples are sorted children first */
function putMessage(changes) {
    const T = IDX.T;
    const V = DOC.values || [];
    const body = {words: [], triples: [], formulas: [], values: [], delete: []};
    changes.words.forEach(function (w) {
        body.words.push({name: w});
    });
    changes.add.forEach(function (t) {
        body.triples.push(tripleJson(t, 0));
    });
    changes.mod.forEach(function (m) {
        body.triples.push(tripleJson(m.after, T[m.src].id));
    });
    /* the expression in the user format, which the formula api names user_text */
    changes.formulas.forEach(function (f) {
        body.formulas.push({id: f.id, user_text: f.after});
    });
    changes.values.forEach(function (v) {
        if (v.kind === "add") {
            body.values.push(valueJson(v));
        } else if (v.kind === "mod") {
            body.values.push({id: V[v.src].id, number: Number(v.number)});
        }
    });
    const delNames = new Set(changes.del.map(function (d) {
        return d.t.name;
    }).filter(Boolean));
    const byName = new Map(changes.del.map(function (d) {
        return [d.t.name, d];
    }));

    function depth(d) {
        let n = 0;
        let cur = d;
        while (cur && delNames.has(cur.t.from) && n < 20) {
            n = n + 1;
            cur = byName.get(cur.t.from);
        }
        return n;
    }

    changes.del.slice().sort(function (a, b) {
        return depth(b) - depth(a);
    }).forEach(function (d) {
        body.delete.push({class: "triple", id: T[d.src].id});
    });
    changes.values.forEach(function (v) {
        if (v.kind === "del") {
            body.delete.push({class: "value", id: V[v.src].id});
        }
    });
    const result = {
        pod: DOC.pod,
        type: UPDATE_PUT_ROUTE,
        user_id: DOC.user_id,
        user: DOC.user,
        version: DOC.version,
        timestamp: new Date().toISOString(),
        body: body
    };
    return result;
}

/* one call that saves all changes; the pod stops at the first problem, so after a refusal the edits
   stay for a fix and the notice tells that the changes before the problem may be saved */
async function save() {
    const out = buildOutput();
    if (SOURCE.mode === "api" && out.changed > 0 && !saving) {
        saving = true;
        refreshStatus();
        const r = await apiCall("POST", UPDATE_PUT_ROUTE, {}, putMessage(out.changes));
        saving = false;
        if (r.ok) {
            clearDraft();
            const loaded = await reload();
            /* after the reload, which clears the notice, and with the reason of a failed reload */
            const report = savedReport(unwrap(r.json) || {});
            const failed = loaded ? "" : "<br>" + esc(document.getElementById("noticeHost").textContent);
            notice(report.html + failed, report.warn || !loaded ? "err" : "");
            if (!report.warn && loaded) {
                toast("Saved " + out.changed + (out.changed === 1 ? " change." : " changes."));
            }
        } else {
            refreshStatus();
            notice("Not all changes are saved. The pod refused the save: " + esc(r.message || "no answer") +
                ". The changes before the problem may be saved; Load shows the database.", "err");
        }
    }
}

/* what the pod has saved, confirmed by the reload of each saved row (update_put::confirmed): the
   action, the changed fields with the old and the new value and if the user has an own version of
   the object after the save; a missing confirmation, an unchanged object or a warning of the pod is a warning */
function savedReport(body) {
    const lines = [];
    let warn = Boolean(body.warnings);
    ["words", "triples", "formulas", "values"].forEach(function (section) {
        (body[section] || []).forEach(function (o) {
            const name = o.name || (section.slice(0, -1) + " " + o.id);
            const own = o.user_overwrite ? " (your own version)" : " (the shared version)";
            const fields = (o.changes || []).map(function (c) {
                const old = c.old_value === null ? "" : JSON.stringify(c.old_value) + " → ";
                return c.field + " " + old + JSON.stringify(c.new_value);
            });
            warn = warn || !o.action || o.action === "unchanged";
            lines.push(esc(name) + ": " + esc(o.action || "not confirmed") + own + (fields.length ? ": " + esc(fields.join(", ")) : ""));
        });
    });
    (body.delete || []).forEach(function (d) {
        warn = warn || d.action !== "removed";
        lines.push(esc(d.class + " " + d.id) + ": " + esc(d.action || "not confirmed"));
    });
    if (body.warnings) {
        lines.push(esc(body.warnings));
    }
    /* a save is only called with changes, so an answer without any confirmation is never a success */
    if (lines.length === 0) {
        warn = true;
        lines.push("The pod has not confirmed the save, so the changes may not be saved. Load shows the database.");
    }
    const result = {html: lines.join("<br>"), warn: warn};
    return result;
}

/* phrases that match the typed text, so a new item can use any phrase of the pod */
let searchTimer = null;

function searchPhrases(text) {
    clearTimeout(searchTimer);
    if (SOURCE.online && text && text.length >= 2) {
        searchTimer = setTimeout(async function () {
            const r = await apiCall("GET", "phraseList", {kp: text});
            if (r.ok) {
                let added = false;
                asList(r.json, "phrases").forEach(function (e) {
                    if (e && e.name && !IDX.known.has(e.name)) {
                        IDX.known.add(e.name);
                        SOURCE.phraseIds.set(e.name, phraseIdOf(e));
                        added = true;
                    }
                });
                if (added) {
                    renderPhraseList();
                }
            }
        }, 250);
    }
}

/* ------------------------------------------------------------------ */
/* loading                                                              */
/* ------------------------------------------------------------------ */
function notice(html, kind) {
    document.getElementById("noticeHost").innerHTML = html ? '<div class="notice' + (kind === "err" ? " err" : "") + '">' + html + "</div>" : "";
}

/* a fingerprint of the loaded triples, so a draft is only restored on the same data */
function signature(doc) {
    const rows = (doc.triples || []).map(function (t) {
        return [t.id || "", t.name || "", t.from, t.verb, t.to];
    });
    const result = stableId(rows) + "-" + rows.length;
    return result;
}

/* the phrase id of an api entry: a word keeps its id, a triple is negative */
function phraseIdOf(e) {
    let result = Number(e.id);
    if (e.class === "triple" || e.from_id !== undefined) {
        result = -Math.abs(Number(e.id));
    }
    return result;
}

/* show the given data in the editor */
function loadDoc(doc, name, savedState) {
    DOC = doc;
    DOC_NAME = name;
    uidCounter = 0;
    parseDoc(doc);
    if (savedState) {
        state = savedState;
        walkAll(function (n) {
            const k = Number(String(n.uid).slice(1));
            if (k > uidCounter) {
                uidCounter = k;
            }
        });
        uidCounter = uidCounter + 1000;
    }
    if (!findNode(selected)) {
        selected = "page";
    }
    history.undo = [];
    history.redo = [];
    const lists = LISTS.filter(function (L) {
        return state.lists[L.key].src !== null;
    }).length;
    if (lists === 0 && state.page) {
        notice("“" + esc(state.page) + "” has no calculator lists yet. The lists you add here are added to “" + esc(state.base) + "”.", "");
    }
    renderAll();
}

/* the data of the page phrase as the phraseData api sends it (cfg/export/phrase_data.php): the
   triples on the from side of the phrase with their values and results in one call; the view
   embeds the answer of its phrase as the seed, so that the start needs no call, but a reload
   e.g. after a save reads the database */
async function loadFromApi() {
    const seed = seedDoc().doc;
    let doc = seed;
    let message = "";
    if (SOURCE.seedUsed || selectionId(seed) !== SOURCE.pageId) {
        /* the user of the seed, because DOC is not yet loaded on startup; apiUrl skips a missing id */
        const r = await apiCall("GET", "phraseData", {id: SOURCE.pageId, pw: PHRASE_DATA_WALK_FROM, is: 1, u: seed.user_id});
        doc = unwrap(r.json) || {};
        message = r.ok ? "" : r.message;
    }
    SOURCE.seedUsed = true;
    if (!message && !selectionName(doc)) {
        message = "the phrase " + SOURCE.pageId + " was not found";
    }
    if (!message) {
        message = readIds(doc);
    }
    const result = {ok: message === "", doc: doc, message: message};
    return result;
}

/* the database ids that a save needs: the phrases by their name, and the verbs, which the data names only */
function readIds(doc) {
    SOURCE.phraseIds = new Map();
    (doc.words || []).forEach(function (w) {
        SOURCE.phraseIds.set(w.name, Number(w.id));
    });
    (doc.triples || []).forEach(function (t) {
        if (t.name) {
            SOURCE.phraseIds.set(t.name, -Math.abs(Number(t.id)));
        }
    });
    SOURCE.verbIds = typeVerbs();
    let message = "";
    (doc.triples || []).forEach(function (t) {
        if (!message && t.verb && !SOURCE.verbIds.has(t.verb)) {
            message = "the verb \"" + t.verb + "\" is not in the type lists of the page";
        }
    });
    return message;
}

/* the type lists that the view embeds in the format of the typeLists api
   (system_page::calculator_editor_types): the verbs and the unit and scaling phrases */
function embeddedTypes() {
    const el = document.getElementById("calcEditorTypes");
    const result = el ? parseJson(el.textContent) : {};
    return result;
}

/* the verb ids by the verb name, so that no verb is read from the api */
function typeVerbs() {
    const result = new Map();
    (embeddedTypes().verbs || []).forEach(function (v) {
        result.set(v.name, Number(v.id));
    });
    return result;
}

/* the names of the unit and scaling phrases of the pod e.g. kWh or million, so that a number is shown with its unit */
function typeUnits() {
    const result = new Set();
    (embeddedTypes().system_phrases || []).forEach(function (p) {
        result.add(p.name);
    });
    return result;
}

async function reload() {
    const r = await loadFromApi();
    if (r.ok) {
        notice("", "");
        loadDoc(r.doc, location.host, null);
    } else {
        notice("The calculator could not be read from the database: " + esc(r.message) + ".", "err");
    }
    return r.ok;
}

/* ------------------------------------------------------------------ */
/* events                                                               */
/* ------------------------------------------------------------------ */
function onClick(e) {
    const btn = e.target.closest("[data-act]");
    const nodeEl = e.target.closest(".node");
    const prev = e.target.closest("[data-sel]");
    const tab = e.target.closest(".tab");
    if (btn) {
        const act = btn.dataset.act;
        e.stopPropagation();
        if (act === "addTop") {
            addTop(btn.dataset.list);
        } else if (act === "addChild") {
            addChild(btn.dataset.uid, btn.dataset.kind);
        } else if (act === "remove") {
            removeNode(btn.dataset.uid);
        } else if (act === "up") {
            moveNode(btn.dataset.uid, -1);
        } else if (act === "down") {
            moveNode(btn.dataset.uid, 1);
        } else {
            phraseListAction(act, btn.dataset.p, btn.dataset.uid);
        }
    } else if (nodeEl) {
        select(nodeEl.dataset.uid);
    } else if (prev && document.getElementById("pane").contains(prev)) {
        e.preventDefault();
        select(prev.dataset.sel);
    } else if (tab) {
        activeTab = tab.dataset.tab;
        renderPane();
    }
}

function select(id) {
    if (selected !== id) {
        selected = id;
        history.lastKey = "";
        /* an item selected e.g. in the page preview is shown in its list */
        const hit = findNode(id);
        if (hit) {
            openLists.add(hit.listKey);
        }
        renderTree();
        renderInspector();
        if (activeTab === "page") {
            renderPane();
        }
    }
}

function onInput(e) {
    const el = e.target;
    if (el.dataset && el.dataset.bind) {
        const b = JSON.parse(el.dataset.bind);
        const isText = el.tagName === "TEXTAREA" || (el.tagName === "INPUT" && el.type !== "checkbox");
        if (isText) {
            mutate(function () {
                setBound(b, readEl(el));
            }, {key: el.dataset.bind, keepInspector: true});
            if (el.getAttribute("list") === "phraseList") {
                searchPhrases(el.value);
            }
        }
    }
}

function onChange(e) {
    const el = e.target;
    if (el.dataset && el.dataset.bind) {
        const b = JSON.parse(el.dataset.bind);
        const isText = el.tagName === "TEXTAREA" || (el.tagName === "INPUT" && el.type !== "checkbox");
        if (isText) {
            /* the text was stored while typing; refresh the inspector for the new placeholders
               and the sample values of the operands of a changed formula */
            const structural = (b.t === "node" && (b.f === "to" || b.f === "name")) || b.t === "frm";
            if (structural && document.getElementById("insp").contains(el)) {
                setTimeout(function () {
                    const active = document.activeElement;
                    const activeId = active && active.id;
                    renderInspector();
                    if (activeId) {
                        const again = document.getElementById(activeId);
                        if (again) {
                            again.focus();
                        }
                    }
                }, 0);
            }
        } else {
            mutate(function () {
                setBound(b, readEl(el));
            });
        }
    }
}

/* the text tabs re-render on every keystroke only outside the edited field */
function paneKeepsFocus() {
    const result = activeTab === "texts";
    return result;
}

const originalRenderPane = renderPane;
renderPane = function () {
    const active = document.activeElement;
    const inPane = active && document.getElementById("pane").contains(active) && paneKeepsFocus();
    if (!inPane) {
        originalRenderPane();
    }
};

let dragId = null;

function onDragStart(e) {
    const el = e.target.closest(".node[data-kind]");
    if (el) {
        dragId = el.dataset.uid;
        el.classList.add("dragging");
        e.dataTransfer.effectAllowed = "move";
        e.dataTransfer.setData("text/plain", dragId);
    }
}

function clearDropMarks() {
    document.querySelectorAll(".drop-before, .drop-after, .dragging").forEach(function (x) {
        x.classList.remove("drop-before", "drop-after", "dragging");
    });
}

function onDragOver(e) {
    const el = e.target.closest(".node[data-kind]");
    const src = dragId ? findNode(dragId) : null;
    if (el && src && el.dataset.kind === src.node.kind && el.dataset.uid !== dragId) {
        e.preventDefault();
        const r = el.getBoundingClientRect();
        const after = e.clientY > r.top + r.height / 2;
        document.querySelectorAll(".drop-before, .drop-after").forEach(function (x) {
            x.classList.remove("drop-before", "drop-after");
        });
        el.classList.add(after ? "drop-after" : "drop-before");
    }
}

function onDrop(e) {
    const el = e.target.closest(".node[data-kind]");
    if (el && dragId) {
        e.preventDefault();
        const after = el.classList.contains("drop-after");
        const id = dragId;
        dragId = null;
        clearDropMarks();
        dropNode(id, el.dataset.uid, after);
    }
}

function onDragEnd() {
    dragId = null;
    clearDropMarks();
}

function onKey(e) {
    const mod = e.ctrlKey || e.metaKey;
    const inField = e.target.matches && e.target.matches("input, textarea, select");
    if (mod && !inField && e.key.toLowerCase() === "z") {
        e.preventDefault();
        if (e.shiftKey) {
            redo();
        } else {
            undo();
        }
    } else if (mod && !inField && e.key.toLowerCase() === "y") {
        e.preventDefault();
        redo();
    } else if (e.key === "Enter" && e.target.classList && e.target.classList.contains("node")) {
        select(e.target.dataset.uid);
    } else if (e.altKey && (e.key === "ArrowUp" || e.key === "ArrowDown") && selected !== "page" && !inField) {
        e.preventDefault();
        moveNode(selected, e.key === "ArrowUp" ? -1 : 1);
    }
}

document.addEventListener("click", onClick);
document.addEventListener("input", onInput);
document.addEventListener("change", onChange);
document.addEventListener("keydown", onKey);
/* the toggle event does not bubble, so it is caught on the way down to remember the open lists and preview blocks */
document.addEventListener("toggle", function (e) {
    const el = e.target;
    const list = el.matches && el.matches("details.sect") ? el.dataset.list : "";
    const block = el.dataset ? el.dataset.preview : "";
    const set = list ? openLists : openPreview;
    const key = list || block;
    if (key && el.open) {
        set.add(key);
    } else if (key) {
        set.delete(key);
    }
}, true);
const treeEl = document.getElementById("tree");
treeEl.addEventListener("dragstart", onDragStart);
treeEl.addEventListener("dragover", onDragOver);
treeEl.addEventListener("drop", onDrop);
treeEl.addEventListener("dragend", onDragEnd);
document.getElementById("btnUndo").addEventListener("click", undo);
document.getElementById("btnRedo").addEventListener("click", redo);
document.getElementById("btnSave").addEventListener("click", save);
document.getElementById("btnReset").addEventListener("click", function () {
    if (lastOutput && lastOutput.changed > 0) {
        mutate(function () {
            const keep = selected;
            parseDoc(DOC);
            selected = findNode(keep) ? keep : "page";
        });
        toast("Changes discarded. Undo brings them back.");
    }
});

/* start: with ?id=<phrase id> on a pod the calculator of that phrase is read from the database,
   anywhere else the embedded demo file is shown; a draft of unsaved edits is offered if one was left */
function seedDoc() {
    const seedText = document.getElementById("seed").textContent.replace(/<\\\//g, "</");
    const doc = parseJson(seedText);
    /* the seed of the view names no file, so the page phrase names it */
    const nameEl = document.getElementById("seedName");
    const name = nameEl ? JSON.parse(nameEl.textContent) : selectionName(doc);
    const result = {doc: doc, name: name};
    return result;
}

function emptyDoc() {
    const result = {selection: [], triples: [], values: [], results: [], sources: [], formulas: []};
    return result;
}

function setUrlId(id) {
    try {
        const url = new URL(location.href);
        url.searchParams.set("id", String(id));
        window.history.replaceState(null, "", url.toString());
    } catch (e) {
        /* the address only helps a reload */
    }
}

/* the phrase id of a name: known from the loaded data, else by a search of the pod */
async function resolvePhrase(name) {
    let result = SOURCE.phraseIds.has(name) ? SOURCE.phraseIds.get(name) : 0;
    if (!result) {
        const r = await apiCall("GET", "phraseList", {kp: name});
        if (r.ok) {
            asList(r.json, "phrases").forEach(function (e) {
                if (!result && e && e.name === name) {
                    result = phraseIdOf(e);
                }
            });
        }
    }
    return result;
}

/* fill the editor with the calculator of the phrase in the phrase field */
async function loadPhrase(force) {
    const name = document.getElementById("phraseIn").value.trim();
    const unsaved = lastOutput && lastOutput.changed > 0;
    if (!name) {
        notice("Enter the name of a word or triple first.", "err");
    } else if (unsaved && !force) {
        notice("The " + lastOutput.changed + " unsaved changes to “" + esc(state.page) + "” will be lost. " +
            '<button class="btn small" id="btnLoadAnyway">Load “' + esc(name) + '” anyway</button> <button class="btn small ghost" id="btnLoadCancel">Cancel</button>', "err");
        document.getElementById("btnLoadAnyway").addEventListener("click", function () {
            loadPhrase(true);
        });
        document.getElementById("btnLoadCancel").addEventListener("click", function () {
            notice("", "");
            document.getElementById("phraseIn").value = state.page;
        });
    } else if (!SOURCE.online) {
        const seed = seedDoc();
        if (name === selectionName(seed.doc)) {
            notice("", "");
            clearDraft();
            loadDoc(seed.doc, seed.name, null);
            toast("Loaded “" + name + "”.");
        } else {
            notice("This page is not connected to a zukunft.com pod, so only “" + esc(selectionName(seed.doc)) + "” can be loaded.", "err");
        }
    } else {
        const btn = document.getElementById("btnLoad");
        btn.disabled = true;
        notice("Reading the calculator of “" + esc(name) + "” from the database…", "");
        const id = await resolvePhrase(name);
        if (!id) {
            notice("No word or triple “" + esc(name) + "” on this pod. Pick one of the suggestions.", "err");
        } else {
            const before = SOURCE.pageId;
            SOURCE.mode = "api";
            SOURCE.pageId = id;
            const ok = await reload();
            if (ok) {
                clearDraft();
                setUrlId(id);
                toast("Loaded “" + name + "”.");
            } else {
                SOURCE.pageId = before;
            }
        }
        btn.disabled = false;
    }
}

/* start: on a pod the calculator of the phrase in ?id= is read from the database, or the user picks
   a phrase; anywhere else the embedded demo data is shown; unsaved edits are offered if any were left */
async function boot() {
    const params = new URLSearchParams(location.search);
    const id = Number(params.get("id") || 0);
    const path = location.pathname;
    const cut = path.indexOf("/http/");
    /* only a copy served by a pod below /http/ reads the database; a file or the preview of an IDE shows the demo data */
    SOURCE.online = (location.protocol === "http:" || location.protocol === "https:") && cut >= 0;
    SOURCE.root = cut >= 0 ? path.slice(0, cut + 1) : "/";
    SOURCE.base = params.get("api") || (SOURCE.root + "api/");
    let loaded = false;
    if (SOURCE.online && id !== 0) {
        SOURCE.mode = "api";
        SOURCE.pageId = id;
        notice("Reading the calculator of phrase " + esc(String(id)) + " from the database…", "");
        loaded = await reload();
    }
    if (!loaded && SOURCE.online && id === 0) {
        SOURCE.mode = "api";
        SOURCE.pageId = 0;
        loadDoc(emptyDoc(), location.host, null);
        document.getElementById("phraseIn").focus();
    } else if (!loaded) {
        const seed = seedDoc();
        SOURCE.mode = "demo";
        SOURCE.pageId = 0;
        loadDoc(seed.doc, seed.name, null);
    }
    const draft = readDraft();
    const sameData = draft && draft.mode === SOURCE.mode && draft.pageId === SOURCE.pageId && draft.sig === signature(DOC);
    if (sameData) {
        const when = new Date(draft.at).toLocaleString();
        notice("Unsaved edits from " + esc(when) + ". " +
            '<button class="btn small" id="btnRestore">Restore</button> <button class="btn small ghost" id="btnDiscard">Discard</button>', "");
        document.getElementById("btnRestore").addEventListener("click", function () {
            loadDoc(DOC, DOC_NAME, draft.state);
            notice("", "");
            toast("Edits restored.");
        });
        document.getElementById("btnDiscard").addEventListener("click", function () {
            clearDraft();
            notice("", "");
        });
    } else if (draft) {
        clearDraft();
    }
    document.getElementById("phraseForm").addEventListener("submit", function (e) {
        e.preventDefault();
        loadPhrase(false);
    });
    document.getElementById("phraseIn").addEventListener("input", function (e) {
        searchPhrases(e.target.value);
    });
    window.__calcEditor = {
        buildOutput: buildOutput,
        getState: function () {
            return state;
        },
        addTop: addTop,
        removeNode: removeNode,
        moveNode: moveNode,
        findNode: findNode,
        putMessage: putMessage,
        source: SOURCE
    };
}

boot();
