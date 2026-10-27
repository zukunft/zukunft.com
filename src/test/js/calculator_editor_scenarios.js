"use strict";

/* the user actions of the calculator editor test (unit_ui/use_case_ui_tests.php): the test adds this
   script to a copy of the calculator editor snapshot, which a headless browser opens with the name of
   the scenario as the hash e.g. #autarky_formula; the scenario clicks and types like a user and presses
   Save, which sends the update put message to a fetch that writes the message into the page instead of
   calling the pod, so the test reads the message from the dumped page */

/* the element with the message of the save, empty if the scenario has not saved; it gets its id only after
   the scenario has ended, so a failed scenario leaves no element and is never read as nothing saved */
const PUT_MESSAGE_ID = "putMessage";
const messageElement = document.createElement("pre");
const AUTARKY_FORMULA = "autarky formula";
/* the autarky without the battery discharge */
const AUTARKY_EXPRESSION = '"autarky" = "direct use" / "household consumption"';
/* the input field of the page that the scenarios change and the one that they remove */
const PV_CAPACITY = "PV capacity";
const BATTERY_CAPACITY = "battery capacity";
const PV_CAPACITY_LABEL = "Installation size";
const PV_CAPACITY_HINT = "the peak power of all modules";
const PV_CAPACITY_NAME = "PV installation input installation size";
const PV_CAPACITY_NUMBER = "12.5";
const PV_CAPACITY_MAXIMUM = "100";
/* the last row of its row group and a row group in the middle, which the order scenarios move */
const AUTARKY = "autarky";
const EMBODIED_EMISSION = "embodied emission";
/* the kinds of the items in the page structure, because e.g. "lifetime" is an assumption and a result column */
const KIND_INPUT = "input";
const KIND_ROW = "row";
const KIND_ROW_GROUP = "rowgroup";
const MOVE_UP = "up";
const MOVE_DOWN = "down";
/* a phrase that is not in the database yet, so the save adds it as a word */
const NEW_WORD = "System Test Word";
/* the field labels of the inspector */
const FIELD_PHRASE = "Phrase";
const FIELD_GENERAL_LABEL = "General label";
const FIELD_GENERAL_HINT = "General hint";
const FIELD_TRIPLE_NAME = "Triple name";
const FIELD_SHOWN_NUMBER = "Shown number";
const FIELD_MINIMUM = "Minimum";
const FIELD_MAXIMUM = "Maximum";

/* select the formula by its name in the page structure, which shows it in the formula editor */
function selectFormula(name) {
    const node = Array.from(document.querySelectorAll(".node.formula")).find(function (n) {
        return n.querySelector(".lbl").textContent === name;
    });
    node.click();
}

/* type the expression into the text area of the formula editor */
function typeExpression(text) {
    const area = document.getElementById("expText");
    area.value = text;
    area.dispatchEvent(new Event("input", {bubbles: true}));
}

/* select the input field of the page structure that shows the phrase */
function selectItem(phrase) {
    itemOf(phrase, KIND_INPUT).click();
}

function itemOf(phrase, kind) {
    const result = Array.from(document.querySelectorAll('#tree .node[data-kind="' + kind + '"]')).find(function (n) {
        return n.querySelector(".ph").textContent === phrase;
    });
    return result;
}

/* move the item of the page structure one place up or down with its arrow button */
function moveItem(phrase, kind, direction) {
    itemOf(phrase, kind).querySelector('[data-act="' + direction + '"]').click();
}

/* type the text into the field of the settings with the label, which the editor stores while typing,
   and leave the field */
function typeField(label, text) {
    const group = Array.from(document.querySelectorAll("#insp .fg")).find(function (g) {
        const lab = g.querySelector("label");
        return lab !== null && lab.textContent.indexOf(label) === 0;
    });
    const el = group.querySelector("input, textarea");
    el.value = text;
    el.dispatchEvent(new Event("input", {bubbles: true}));
    el.dispatchEvent(new Event("change", {bubbles: true}));
}

function click(selector) {
    document.querySelector(selector).click();
}

/* press Save as on a pod: the fetch of the save writes its message into the page and never answers */
function pressSave() {
    SOURCE.mode = "api";
    window.fetch = function (url, opts) {
        messageElement.textContent = opts.body;
        return new Promise(function () {
        });
    };
    refreshStatus();
    click("#btnSave");
}

const SCENARIOS = {
    /* the user changes the expression of the autarky formula and saves it */
    autarky_formula: function () {
        selectFormula(AUTARKY_FORMULA);
        typeExpression(AUTARKY_EXPRESSION);
        pressSave();
    },
    /* the user types the loaded expression again, so there is nothing to save */
    autarky_formula_unchanged: function () {
        selectFormula(AUTARKY_FORMULA);
        const f = DOC.formulas.find(function (frm) {
            return frm.name === AUTARKY_FORMULA;
        });
        typeExpression(f.expression);
        pressSave();
    },
    /* the user changes the general label of a phrase, which is the description of its label triple */
    pv_capacity_label: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_GENERAL_LABEL, PV_CAPACITY_LABEL);
        pressSave();
    },
    /* the user changes the general label and the general hint and undoes the hint */
    pv_capacity_label_undo: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_GENERAL_LABEL, PV_CAPACITY_LABEL);
        typeField(FIELD_GENERAL_HINT, PV_CAPACITY_HINT);
        click("#btnUndo");
        pressSave();
    },
    /* the user changes the general label and undoes it, so there is nothing to save */
    pv_capacity_label_undo_all: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_GENERAL_LABEL, PV_CAPACITY_LABEL);
        click("#btnUndo");
        pressSave();
    },
    /* the user renames the triple of an input field, which renames the triples named after it */
    pv_capacity_rename: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_TRIPLE_NAME, PV_CAPACITY_NAME);
        pressSave();
    },
    /* the user changes the shown number of an input field, which is the stored value */
    pv_capacity_number: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_SHOWN_NUMBER, PV_CAPACITY_NUMBER);
        pressSave();
    },
    /* the user sets the missing maximum of an input field, which adds the limit value */
    pv_capacity_maximum: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_MAXIMUM, PV_CAPACITY_MAXIMUM);
        pressSave();
    },
    /* the user clears the minimum of an input field, which removes the limit value */
    pv_capacity_minimum_cleared: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_MINIMUM, "");
        pressSave();
    },
    /* the user adds an input field with a phrase that is not in the database yet */
    add_input: function () {
        click('[data-act="addTop"][data-list="input"]');
        typeField(FIELD_PHRASE, NEW_WORD);
        pressSave();
    },
    /* the user adds an input field but types no phrase, so there is nothing to save */
    add_input_without_phrase: function () {
        click('[data-act="addTop"][data-list="input"]');
        pressSave();
    },
    /* the user removes an input field with its triples and values */
    remove_input: function () {
        itemOf(BATTERY_CAPACITY, KIND_INPUT).querySelector('[data-act="remove"]').click();
        pressSave();
    },
    /* the user moves the first input field down, which adds the order triples of the new order and
       removes the ones of the old order */
    pv_capacity_down: function () {
        moveItem(PV_CAPACITY, KIND_INPUT, MOVE_DOWN);
        pressSave();
    },
    /* the first input field can not move up, so there is nothing to save */
    pv_capacity_up: function () {
        moveItem(PV_CAPACITY, KIND_INPUT, MOVE_UP);
        pressSave();
    },
    /* the user moves an input field down and up again, so there is nothing to save */
    pv_capacity_down_up: function () {
        moveItem(PV_CAPACITY, KIND_INPUT, MOVE_DOWN);
        moveItem(PV_CAPACITY, KIND_INPUT, MOVE_UP);
        pressSave();
    },
    /* the user moves the last row of a row group up, which replaces the two order triples of the two rows */
    autarky_up: function () {
        moveItem(AUTARKY, KIND_ROW, MOVE_UP);
        pressSave();
    },
    /* the user moves a row group down, which replaces the order triples of the two row groups */
    embodied_emission_down: function () {
        moveItem(EMBODIED_EMISSION, KIND_ROW_GROUP, MOVE_DOWN);
        pressSave();
    }
};

window.addEventListener("load", function () {
    document.body.appendChild(messageElement);
    SCENARIOS[location.hash.slice(1)]();
    messageElement.id = PUT_MESSAGE_ID;
});
