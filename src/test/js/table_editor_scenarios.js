"use strict";

/* the user actions of the table editor test (unit_ui/use_case_ui_tests.php): the test adds this script to a
   copy of the table editor snapshot, which a headless browser opens with the name of the scenario as the
   hash e.g. #move_column; the scenario clicks and types like a user and ends with the text that the test
   compares: the update put message of the save, which a fetch writes into the page instead of calling the
   pod, the exported csv or the problem that the editor shows to the user */

/* the element with the result of the scenario, empty if nothing is saved; it gets its id only after the
   scenario has ended, so a failed scenario leaves no element and is never read as nothing saved */
const PUT_MESSAGE_ID = "putMessage";
const messageElement = document.createElement("pre");
/* the parameters of the rail that the scenarios change */
const COLUMN_MAYOR = "column_mayor";
const COLUMN_MARGINAL = "column_marginal";
const COLUMN_ORDER_RULE = "column_order_rule";
const HEADER_ROTATE = "header_rotate";
const ROW_LIMIT = "row_limit";
const HIERARCHY_EXPAND = "hierarchy_expand";
const TITLE = "title";
/* the own columns of the seed: 2024 is the first mayor column, 2025 the second, confidence the last marginal one */
const FIRST_MAYOR_COLUMN = "2024";
const SECOND_MAYOR_COLUMN = "2025";
const MAIN_COLUMN = "growth";
const MARGINAL_COLUMN = "confidence";
/* the changed parameters, which match the messages of the write test (files::UPDATE_PUT_TABLE_*),
   the saved row limit and the saved hierarchy expand */
const COLUMN_ORDER_RULE_CHANGED = "alphabetical";
const ROW_LIMIT_SAVED = "50";
const ROW_LIMIT_CHANGED = "20";
const HIERARCHY_EXPAND_SAVED = "expanded";
const TITLE_CHANGED = "Revenue by business area";
/* the sort column of the header and the label column, which is the sort order of the seed */
const SORT_LABEL = "__label";
/* a search text that no row matches */
const SEARCH_NO_MATCH = "no sector has this text";
/* the answer of the pod that refuses the save (use_case_ui_tests::TABLE_REFUSED_MESSAGE) */
const REFUSED_STATUS = 403;
const REFUSED_MESSAGE = "the user is not allowed to change this table";
/* the result if the save has not used the put url, the post method or the session token of the page */
const SAVE_WITHOUT_TOKEN = "the save has not been sent with the session token of the page";
/* the poll interval while waiting for the answer of the save */
const WAIT_MS = 20;

function click(selector) {
    document.querySelector(selector).click();
}

/* the token field of the own columns of the tier e.g. column_mayor */
function tokenBox(key) {
    return document.getElementById("p_" + key);
}

function chip(key, column) {
    return tokenBox(key).querySelector('.chip-tok[data-name="' + column + '"]');
}

/* type the column into the picker of the tier and click it in the suggestions */
function addColumn(key, column) {
    const input = tokenBox(key).querySelector(".picker input");
    input.value = column;
    input.dispatchEvent(new Event("input", {bubbles: true}));
    tokenBox(key).querySelector('.suggest [data-add="' + column + '"]').click();
}

/* type the column into the picker of the tier and press Enter, which adds the active suggestion if there is one */
function typeColumn(key, column) {
    const input = tokenBox(key).querySelector(".picker input");
    input.value = column;
    input.dispatchEvent(new Event("input", {bubbles: true}));
    input.dispatchEvent(new KeyboardEvent("keydown", {key: "Enter", bubbles: true}));
}

function removeColumn(key, column) {
    chip(key, column).querySelector(".rm").click();
}

/* move the column of the tier one place to the left with the arrow key */
function moveColumnLeft(key, column) {
    chip(key, column).dispatchEvent(new KeyboardEvent("keydown", {key: "ArrowLeft", bubbles: true}));
}

/* select the option or type the text into the field of the rail and leave the field */
function setField(key, value) {
    const el = document.getElementById("p_" + key);
    el.value = value;
    el.dispatchEvent(new Event("input", {bubbles: true}));
    el.dispatchEvent(new Event("change", {bubbles: true}));
}

function check(key) {
    document.getElementById("p_" + key).click();
}

function sortBy(column) {
    click('#tableBox th[data-sort="' + column + '"]');
}

function search(text) {
    const box = document.getElementById("searchBox");
    box.value = text;
    box.dispatchEvent(new Event("input", {bubbles: true}));
}

/* true if the save posts to the put url of the page with its session token */
function isSentWithToken(url, opts) {
    const settings = JSON.parse(document.getElementById("tableEditorSettings").textContent);
    const token = document.querySelector('input[name="token"]').value;
    return url === settings.urls.put && opts.method === "POST" && opts.headers["X-CSRF-Token"] === token;
}

/* press Save: the fetch of the save writes its message into the page and never answers */
function pressSave() {
    window.fetch = function (url, opts) {
        messageElement.textContent = isSentWithToken(url, opts) ? opts.body : SAVE_WITHOUT_TOKEN;
        return new Promise(function () {
        });
    };
    click("#btnSave");
}

/* the text of the notice of the editor, once the editor shows one */
function shownNotice() {
    return new Promise(function (resolve) {
        const wait = function () {
            const notice = document.querySelector("#noticeHost .notice");
            if (notice) {
                resolve(notice.textContent);
            } else {
                setTimeout(wait, WAIT_MS);
            }
        };
        wait();
    });
}

/* press Save on a pod that refuses the save and return the problem that the editor shows */
function pressSaveRefused() {
    window.fetch = function () {
        return Promise.resolve({
            ok: false,
            status: REFUSED_STATUS,
            statusText: "",
            text: function () {
                return Promise.resolve(JSON.stringify({message: REFUSED_MESSAGE}));
            }
        });
    };
    click("#btnSave");
    return shownNotice().then(function (text) {
        messageElement.textContent = text;
    });
}

/* press Export and return the csv of the download, which the browser never saves */
function pressExport() {
    let exported = Promise.resolve("");
    URL.createObjectURL = function (blob) {
        exported = blob.text();
        return "";
    };
    URL.revokeObjectURL = function () {
    };
    HTMLAnchorElement.prototype.click = function () {
    };
    click("#btnExport");
    return exported.then(function (csv) {
        messageElement.textContent = csv;
    });
}

const SCENARIOS = {
    /* the picker suggests only the own columns, which all have a tier, so the user removes the last marginal
       column and adds it to the mayor columns */
    add_column_to_tier: function () {
        removeColumn(COLUMN_MARGINAL, MARGINAL_COLUMN);
        addColumn(COLUMN_MAYOR, MARGINAL_COLUMN);
        pressSave();
    },
    /* a column of another tier is not suggested, so Enter adds nothing and there is nothing to save */
    add_column_used: function () {
        typeColumn(COLUMN_MAYOR, MAIN_COLUMN);
        pressSave();
    },
    /* the user moves the second mayor column to the left, which replaces the follower triples of the columns */
    move_column: function () {
        moveColumnLeft(COLUMN_MAYOR, SECOND_MAYOR_COLUMN);
        pressSave();
    },
    /* the first column can not move to the left, so there is nothing to save */
    move_column_first: function () {
        moveColumnLeft(COLUMN_MAYOR, FIRST_MAYOR_COLUMN);
        pressSave();
    },
    /* the user removes the last marginal column, which removes its triples */
    remove_column: function () {
        removeColumn(COLUMN_MARGINAL, MARGINAL_COLUMN);
        pressSave();
    },
    /* the user removes the last marginal column and adds it again, so there is nothing to save */
    remove_column_added_again: function () {
        removeColumn(COLUMN_MARGINAL, MARGINAL_COLUMN);
        addColumn(COLUMN_MARGINAL, MARGINAL_COLUMN);
        pressSave();
    },
    /* the user sorts the rows by a column in the header, which saves the sort order */
    sort_column: function () {
        sortBy(FIRST_MAYOR_COLUMN);
        pressSave();
    },
    /* the rows are sorted by their label already, so there is nothing to save */
    sort_label: function () {
        sortBy(SORT_LABEL);
        pressSave();
    },
    /* the user orders the columns alphabetically, which replaces the choice triple of the order rule */
    column_order_rule: function () {
        setField(COLUMN_ORDER_RULE, COLUMN_ORDER_RULE_CHANGED);
        pressSave();
    },
    /* the user turns on the rotated header, which adds its flag triple */
    header_rotate: function () {
        check(HEADER_ROTATE);
        pressSave();
    },
    /* the user changes the row limit, which changes the number of its value */
    row_limit: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        pressSave();
    },
    /* the user changes the row limit and types the saved one again, so there is nothing to save */
    row_limit_back: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        setField(ROW_LIMIT, ROW_LIMIT_SAVED);
        pressSave();
    },
    /* the user changes the row limit and the title and undoes the title */
    undo: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        setField(TITLE, TITLE_CHANGED);
        click("#btnUndo");
        pressSave();
    },
    /* the user changes the row limit and undoes it, so there is nothing to save */
    undo_all: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        click("#btnUndo");
        pressSave();
    },
    /* the user changes the row limit, undoes it and redoes it */
    redo: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        click("#btnUndo");
        click("#btnRedo");
        pressSave();
    },
    /* a new step after the undo ends the redo, so there is nothing to save */
    redo_after_new_step: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        click("#btnUndo");
        setField(HIERARCHY_EXPAND, HIERARCHY_EXPAND_SAVED);
        click("#btnRedo");
        pressSave();
    },
    /* the user discards two changes and undoes the discard, which brings both back */
    discard_undo: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        setField(TITLE, TITLE_CHANGED);
        click("#btnReset");
        click("#btnUndo");
        pressSave();
    },
    /* the user discards two changes, so there is nothing to save */
    discard: function () {
        setField(ROW_LIMIT, ROW_LIMIT_CHANGED);
        setField(TITLE, TITLE_CHANGED);
        click("#btnReset");
        pressSave();
    },
    /* the user exports the table as csv */
    export: function () {
        return pressExport();
    },
    /* the user searches a text that no row has, so the csv has no row of a sector */
    export_no_rows: function () {
        search(SEARCH_NO_MATCH);
        return pressExport();
    },
    /* the user changes the title, which is the description of its parameter triple */
    title: function () {
        setField(TITLE, TITLE_CHANGED);
        pressSave();
    },
    /* without a change Save is disabled, so nothing is sent */
    save_unchanged: function () {
        pressSave();
    },
    /* the pod refuses the save, so the editor shows the reason of the pod */
    save_refused: function () {
        setField(TITLE, TITLE_CHANGED);
        return pressSaveRefused();
    }
};

window.addEventListener("load", function () {
    document.body.appendChild(messageElement);
    Promise.resolve(SCENARIOS[location.hash.slice(1)]()).then(function () {
        messageElement.id = PUT_MESSAGE_ID;
    });
});
