"use strict";

/* the table editor (system_page::table_editor): the view renders the empty host and the seed with the
   phraseData answer of the page phrase, and this script builds the lists of the own columns and of
   the own sorting of the table; the save sends the changes as triples to the updatePut api */
(function () {
    const HOST_ID = "tableEditor";
    const SEED_ID = "tableEditorSeed";
    const TOKEN_NAME = "token";
    const CSRF_HEADER = "X-CSRF-Token";
    const TXT = {
        columns: "table_editor_columns",
        columnsHint: "table_editor_columns_hint",
        sorting: "table_editor_sorting",
        sortingHint: "table_editor_sorting_hint",
        add: "table_editor_add",
        up: "table_editor_up",
        down: "table_editor_down",
        remove: "table_editor_remove",
        preview: "table_editor_preview",
        showTable: "table_editor_show_table",
        noJs: "table_editor_no_js",
        refused: "table_editor_refused",
        unreachable: "table_editor_unreachable",
        save: "calculator_save"
    };

    const host = document.getElementById(HOST_ID);
    const SEED = JSON.parse(document.getElementById(SEED_ID).textContent);
    const DOC = SEED.doc || {};
    const TRIPLES = DOC.triples || [];
    const NAMES = SEED.names;
    const PAGE = NAMES.phrase;
    let notice = "";
    let saving = false;

    function esc(text) {
        const el = document.createElement("div");
        el.textContent = text;
        return el.innerHTML.replace(/"/g, "&quot;");
    }

    function text(id) {
        return SEED.texts[id] || id;
    }

    /* an unnamed triple is its generated name */
    function nameOf(t) {
        return t.name || t.from + " " + t.verb + " " + t.to;
    }

    /* the name of the group triple from the page phrase to the keyword, null if the table has no such list */
    function groupOf(keyword) {
        const grp = TRIPLES.find(function (t) {
            return t.from === PAGE && t.to === keyword;
        });
        return grp === undefined ? null : nameOf(grp);
    }

    /* the follower triple of an item, undefined for the first item */
    function followerOf(itemName) {
        return TRIPLES.find(function (t) {
            return t.from === itemName && t.verb === NAMES.follow;
        });
    }

    /* the saved items of a list in the order of their follower triples */
    function savedItems(grpName) {
        const items = TRIPLES.filter(function (t) {
            return grpName !== null && t.from === grpName && t.verb === NAMES.has;
        });
        const prevOf = new Map(items.map(function (t) {
            const follower = followerOf(nameOf(t));
            return [nameOf(t), follower === undefined ? null : follower.to];
        }));
        const names = items.map(nameOf);
        const result = [];
        let cur = items.find(function (t) {
            return !names.includes(prevOf.get(nameOf(t)));
        });
        while (cur !== undefined) {
            result.push(cur);
            const curName = nameOf(cur);
            cur = items.find(function (t) {
                return prevOf.get(nameOf(t)) === curName && !result.includes(t);
            });
        }
        /* an item outside of the chain is kept at the end and never lost */
        return result.concat(items.filter(function (t) {
            return !result.includes(t);
        }));
    }

    /* the condition and the column of an order triple, which the phraseData answer has as a phrase of the item */
    function orderParts(orderName) {
        const order = TRIPLES.find(function (t) {
            return nameOf(t) === orderName;
        });
        return order === undefined ? {cond: SEED.conditions[0], column: orderName} : {cond: order.from, column: order.to};
    }

    const COLUMN_GROUP = groupOf(NAMES.column);
    const SORT_GROUP = groupOf(NAMES.sort);
    const SAVED_COLUMNS = savedItems(COLUMN_GROUP);
    const SAVED_SORTS = savedItems(SORT_GROUP);
    const columns = SAVED_COLUMNS.map(function (t) {
        return t.to;
    });
    const sorts = SAVED_SORTS.map(function (t) {
        return orderParts(t.to);
    });

    function orderName(sort) {
        return sort.cond + " " + NAMES.of + " " + sort.column;
    }

    /* the names of the columns that can be added next to those in the list */
    function selectOptions(names, used) {
        return names.filter(function (name) {
            return !used.includes(name);
        }).map(function (name) {
            return '<option value="' + esc(name) + '">' + esc(name) + "</option>";
        }).join("");
    }

    function moveButtons(list, pos, last) {
        return '<button type="button" class="btn btn-outline-secondary btn-sm" data-action="up" data-list="' + list + '" data-pos="' + pos + '" title="' + esc(text(TXT.up)) + '"' + (pos === 0 ? " disabled" : "") + ">&uarr;</button>"
            + '<button type="button" class="btn btn-outline-secondary btn-sm" data-action="down" data-list="' + list + '" data-pos="' + pos + '" title="' + esc(text(TXT.down)) + '"' + (pos === last ? " disabled" : "") + ">&darr;</button>"
            + '<button type="button" class="btn btn-outline-secondary btn-sm" data-action="remove" data-list="' + list + '" data-pos="' + pos + '" title="' + esc(text(TXT.remove)) + '">&times;</button>';
    }

    function columnList() {
        const rows = columns.map(function (name, pos) {
            return '<div class="entry"><span class="name">' + esc(name) + "</span>" + moveButtons("columns", pos, columns.length - 1) + "</div>";
        }).join("");
        return "<section><h5>" + esc(text(TXT.columns)) + "</h5>" + rows
            + '<div class="add"><select class="form-control" id="addColumn">' + selectOptions(SEED.columns, columns) + "</select>"
            + '<button type="button" class="btn btn-outline-primary btn-sm" data-action="addColumn">' + esc(text(TXT.add)) + "</button></div>"
            + '<p class="hint">' + esc(text(TXT.columnsHint)) + "</p></section>";
    }

    function sortList() {
        const used = sorts.map(function (sort) {
            return sort.column;
        });
        const rows = sorts.map(function (sort, pos) {
            return '<div class="entry"><span class="name">' + esc(sort.column) + "</span>"
                + '<select class="form-control" data-pos="' + pos + '">' + SEED.conditions.map(function (cond) {
                    return '<option value="' + esc(cond) + '"' + (cond === sort.cond ? " selected" : "") + ">" + esc(cond) + "</option>";
                }).join("") + "</select>" + moveButtons("sorts", pos, sorts.length - 1) + "</div>";
        }).join("");
        const choices = columns.length > 0 ? columns : SEED.columns;
        return "<section><h5>" + esc(text(TXT.sorting)) + "</h5>" + rows
            + '<div class="add"><select class="form-control" id="addSort">' + selectOptions(choices, used) + "</select>"
            + '<button type="button" class="btn btn-outline-primary btn-sm" data-action="addSort">' + esc(text(TXT.add)) + "</button></div>"
            + '<p class="hint">' + esc(text(TXT.sortingHint)) + "</p></section>";
    }

    /* the triples of one list: the group if needed, the new items and the follower triples that differ, and the ids to delete */
    function listChanges(keyword, grpName, grpLabel, saved, wanted, body) {
        const grp = grpName === null ? PAGE + " " + grpLabel : grpName;
        if (grpName === null && wanted.length > 0) {
            body.triples.push({name: grp, from: PAGE, verb: NAMES.has, to: keyword});
        }
        const savedByTo = new Map(saved.map(function (t) {
            return [t.to, t];
        }));
        const itemName = function (to) {
            return savedByTo.has(to) ? nameOf(savedByTo.get(to)) : grp + " " + to;
        };
        wanted.forEach(function (to, pos) {
            if (!savedByTo.has(to)) {
                body.triples.push({name: itemName(to), from: grp, verb: NAMES.has, to: to});
            }
            const follower = followerOf(itemName(to));
            const prev = pos === 0 ? null : itemName(wanted[pos - 1]);
            if (follower !== undefined && follower.to !== prev) {
                body.delete.push({class: "triple", id: follower.id});
            }
            if (prev !== null && (follower === undefined || follower.to !== prev)) {
                body.triples.push({from: itemName(to), verb: NAMES.follow, to: prev});
            }
        });
        /* the children first, because a follower triple is built from its item */
        saved.filter(function (t) {
            return !wanted.includes(t.to);
        }).forEach(function (t) {
            const follower = followerOf(nameOf(t));
            if (follower !== undefined) {
                body.delete.push({class: "triple", id: follower.id});
            }
            body.delete.push({class: "triple", id: t.id});
        });
    }

    function putBody() {
        const body = {words: [], triples: [], formulas: [], values: [], delete: []};
        /* an unnamed order triple is its generated name, which the items of the sorting use */
        sorts.forEach(function (sort) {
            body.triples.push({from: sort.cond, verb: NAMES.of, to: sort.column});
        });
        listChanges(NAMES.column, COLUMN_GROUP, "columns", SAVED_COLUMNS, columns, body);
        listChanges(NAMES.sort, SORT_GROUP, "sort", SAVED_SORTS, sorts.map(orderName), body);
        return body;
    }

    function isChanged() {
        const savedOrders = SAVED_SORTS.map(function (t) {
            return t.to;
        });
        const savedColumns = SAVED_COLUMNS.map(function (t) {
            return t.to;
        });
        return JSON.stringify(columns) !== JSON.stringify(savedColumns)
            || JSON.stringify(sorts.map(orderName)) !== JSON.stringify(savedOrders);
    }

    function render() {
        host.innerHTML = '<div class="toolbar"><h4>' + esc(SEED.title) + "</h4>"
            + '<a class="btn btn-outline-secondary btn-sm" href="' + esc(SEED.urls.no_js) + '">' + esc(text(TXT.noJs)) + "</a>"
            + '<a class="btn btn-outline-secondary btn-sm" href="' + esc(SEED.urls.table) + '">' + esc(text(TXT.showTable)) + "</a>"
            + '<button type="button" class="btn btn-primary btn-sm" data-action="save"' + (isChanged() && !saving ? "" : " disabled") + ">" + esc(text(TXT.save)) + "</button></div>"
            + (notice === "" ? "" : '<div class="notice">' + esc(notice) + "</div>")
            + '<div class="lists">' + columnList() + sortList() + "</div>"
            + "<h5>" + esc(text(TXT.preview)) + "</h5>"
            + '<iframe src="' + esc(SEED.urls.table) + '" title="' + esc(text(TXT.preview)) + '"></iframe>';
    }

    async function save() {
        saving = true;
        notice = "";
        render();
        const message = {pod: DOC.pod, type: "updatePut", user_id: DOC.user_id, user: DOC.user, version: DOC.version, timestamp: new Date().toISOString(), body: putBody()};
        const token = document.querySelector('input[name="' + TOKEN_NAME + '"]');
        let problem = "";
        try {
            const response = await fetch(SEED.urls.put, {
                method: "POST",
                credentials: "same-origin",
                headers: {"Content-Type": "application/json", Accept: "application/json", [CSRF_HEADER]: token === null ? "" : token.value},
                body: JSON.stringify(message)
            });
            const json = await response.json();
            problem = response.ok && !json.message ? "" : text(TXT.refused) + " " + (json.message || response.status);
        } catch (e) {
            problem = text(TXT.unreachable);
        }
        if (problem === "") {
            /* the page reads the saved triples with their ids again */
            location.reload();
        } else {
            saving = false;
            notice = problem;
            render();
        }
    }

    function move(list, pos, step) {
        const moved = list.splice(pos, 1)[0];
        list.splice(pos + step, 0, moved);
    }

    function onClick(event) {
        const action = event.target.dataset ? event.target.dataset.action : undefined;
        const list = event.target.dataset && event.target.dataset.list === "sorts" ? sorts : columns;
        const pos = Number(event.target.dataset ? event.target.dataset.pos : 0);
        if (action === "up") {
            move(list, pos, -1);
        } else if (action === "down") {
            move(list, pos, 1);
        } else if (action === "remove") {
            list.splice(pos, 1);
        } else if (action === "addColumn") {
            const name = document.getElementById("addColumn").value;
            if (name) {
                columns.push(name);
            }
        } else if (action === "addSort") {
            const name = document.getElementById("addSort").value;
            if (name) {
                sorts.push({cond: SEED.conditions[0], column: name});
            }
        }
        if (action === "save") {
            save();
        } else if (action) {
            render();
        }
    }

    function onChange(event) {
        if (event.target.dataset && event.target.dataset.pos !== undefined && event.target.tagName === "SELECT") {
            sorts[Number(event.target.dataset.pos)].cond = event.target.value;
            render();
        }
    }

    host.addEventListener("click", onClick);
    host.addEventListener("change", onChange);
    render();
})();
