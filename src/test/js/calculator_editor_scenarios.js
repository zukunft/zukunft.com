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
/* the arguments scenarios run in the calculator editor of the Swiss NPP vote: the claim of the first pro argument,
   the fields of an argument, the verb of the contra side and a new text */
const KIND_ARGUMENT = "argument";
const SUPPLY_GAP_CLAIM = "supply gap supports lifting the NPP ban";
const FIELD_ARGUMENT_REASON = "Reason phrase";
const FIELD_ARGUMENT_SIDE = "Side";
const FIELD_ARGUMENT_TEXT = "Text on the page";
const SIDE_CONTRA = "limits";
const ARGUMENT_TEXT = "the winter gap is closed by imports {source:BAK economiesuisse study 2026}";
/* the field of the page settings with the lead text that the page shows collapsed below the title, and its keyword */
const FIELD_MORE_TEXT = "Hideable lead text";
const KW_MORE = "calculator more";
/* the state of the verdict that the default numbers select, its fields and the fields of the verdict box */
const KIND_VERDICT = "verdict";
const VERDICT_UNDECIDED = "undecided";
const FIELD_LABEL = "Label on this page";
const FIELD_VERDICT_SCALE = "Scale below the meter";
const FIELD_VERDICT_NOTE = "Note";
const VERDICT_LABEL = "Undecided - decide on the other arguments";
const VERDICT_SCALE = "cheaper | 0 | dearer";
/* the field of the result that the verdict compares, the loaded phrase and another result of the page */
const FIELD_VERDICT_RESULT = "Compared result";
const VERDICT_RESULT = "average cost";
const VERDICT_RESULT_OTHER = "average cost change";
/* the fields of the ends of the meter, the loaded left end and wider ends */
const FIELD_VERDICT_SCALE_MIN = "Left end of the meter";
const FIELD_VERDICT_SCALE_MAX = "Right end of the meter";
const VERDICT_SCALE_MIN = "-1";
const VERDICT_SCALE_MIN_WIDE = "-2";
const VERDICT_SCALE_MAX_WIDE = "2";
/* the axes of the sensitivity table, a value of the row axis, a new one with its label and number and the fields */
const KIND_SAXIS = "sensitivity";
const KIND_SLEVEL = "slevel";
const SENSITIVITY_ROWS = "overnight construction cost";
const SENSITIVITY_LEVEL = "construction cost 5000";
const SENSITIVITY_LEVEL_NUMBER = "6000";
const SENSITIVITY_NEW_LEVEL = "construction cost 20000";
const SENSITIVITY_NEW_LEVEL_LABEL = "20'000 CHF/kW";
const SENSITIVITY_NEW_LEVEL_NUMBER = "20000";
const SENSITIVITY_CHANGE = "household bill change";
const SENSITIVITY_HINT = "each cell: the levelised cost and the change of the household bill";
const FIELD_SENSITIVITY_NUMBER = "Number of this value";
const FIELD_SENSITIVITY_CHANGE = "Number in brackets";
const FIELD_SENSITIVITY_HINT = "Hint below the table";
/* the presets: the one that the scenarios change, the one that they remove, the fields of the numbers that a preset
   sets by the label of the number field and the new texts and numbers */
const KIND_PRESET = "preset";
const PRESET_ACADEMIES = "Academies pessimistic scenario";
const PRESET_ETH_LOW = "ETH low scenario";
const PRESET_LABEL = "Swiss Academies";
const FIELD_CONSTRUCTION_COST = "Construction cost (overnight)";
const FIELD_CONSTRUCTION_TIME = "Construction time";
const FIELD_CAPACITY_FACTOR = "Capacity factor";
const PRESET_CONSTRUCTION_COST = "20000";
const PRESET_CONSTRUCTION_TIME = "15";
const PRESET_CAPACITY_FACTOR = "0.8";
const PRESET_ACADEMIES_ITEM = "new NPP preset Academies";
/* the detail table of the PV lifetime balance and one of its columns, which a click in the preview selects */
const DETAIL_TABLE = "PV installation detail monthly balance";
const DETAIL_COLUMN = "PV installation detail column direct use";
/* a source of the PV lifetime balance and of the Swiss NPP vote, which a click in the preview selects, the NPP source
   with a changed description, and a source that the texts of the NPP vote name by {source:…} with a new name */
const SOURCE_PV = "Swissolar: Fiche d'information Photovoltaïque, Feb 2024 (European residual mix)";
const SOURCE_NPP = "Fraunhofer ISE LCOE 2024 via Faktenfackel";
const FIELD_SOURCE_NAME = "Name";
const FIELD_SOURCE_DESCRIPTION = "Description";
const FIELD_SOURCE_URL = "Link";
const SOURCE_DESCRIPTION = "Fraunhofer ISE (2024): levelised cost of electricity of renewable energy technologies";
const SOURCE_RENAMED = "BAK economiesuisse study 2026";
const SOURCE_NEW_NAME = "BAK economiesuisse balance study 2026";
/* a note of the notes list of the Swiss NPP vote by its phrase and its triple, and its changed text */
const KIND_NOTE = "note";
const NOTE_MODEL_LIMITS = "model limits";
const NOTE_MODEL_LIMITS_ITEM = "new NPP note model limits";
const FIELD_NOTE_TEXT = "Text on the page";
const NOTE_TEXT = "annual balance in prices of 2024, no hourly dispatch";
/* the parts of the page preview that a click selects: an argument, the hideable lead text and a result row without a part */
const SUPPLY_GAP_ARGUMENT = "new NPP argument supply gap";

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

/* the row of a source in the list of the sources of the page structure */
function sourceOf(name) {
    const result = Array.from(document.querySelectorAll("#tree .node.source")).find(function (n) {
        return n.querySelector(".lbl").textContent === name;
    });
    return result;
}

/* the link of a source in the list of the sources of the page preview */
function sourceLink(name) {
    return document.getElementById("pane").querySelector(".src li[" + PART_ATTR + '="' + name + '"] a');
}

/* move the item of the page structure one place up or down with its arrow button */
function moveItem(phrase, kind, direction) {
    itemOf(phrase, kind).querySelector('[data-act="' + direction + '"]').click();
}

/* type the text into the field of the settings with the label, which the editor stores while typing,
   and leave the field; a selector gets the option with the text as its value */
function typeField(label, text) {
    const group = Array.from(document.querySelectorAll("#insp .fg")).find(function (g) {
        const lab = g.querySelector("label");
        return lab !== null && lab.textContent.indexOf(label) === 0;
    });
    const el = group.querySelector("input, textarea, select");
    el.value = text;
    el.dispatchEvent(new Event("input", {bubbles: true}));
    el.dispatchEvent(new Event("change", {bubbles: true}));
}

function click(selector) {
    document.querySelector(selector).click();
}

/* the kind and the label of what the settings show e.g. "Argument: Winter supply gap" */
function selectedTitle() {
    return document.querySelector("#insp .insp-title .kind").textContent + ": " + document.querySelector("#insp .insp-title .big").textContent;
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

/* open the Definition tab and write its json into the page as the message of the scenario */
function showJsonTab() {
    click("#tab-json");
    messageElement.textContent = document.getElementById("jsonText").value;
}

/* open the PDF tab as on a pod and press Create PDF: the fetch of the pdf writes the route, whether the token of the
   page is sent and the posted data into the page as the message of the scenario and never answers */
function pressCreatePdf() {
    SOURCE.mode = "api";
    SOURCE.online = true;
    SOURCE.root = "/";
    window.fetch = function (url, opts) {
        const token = document.querySelector('input[name="' + SESSION_TOKEN_NAME + '"]');
        messageElement.textContent = JSON.stringify({
            route: url,
            token_sent: token !== null && opts.headers[CSRF_HEADER] === token.value,
            body: JSON.parse(opts.body)
        });
        return new Promise(function () {
        });
    };
    click("#tab-pdf");
    click('[data-act="createPdf"]');
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
    /* the user changes the general label and opens the Definition tab, whose json is the message */
    json_tab_label: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_GENERAL_LABEL, PV_CAPACITY_LABEL);
        showJsonTab();
    },
    /* the user opens the Definition tab without a change */
    json_tab: function () {
        showJsonTab();
    },
    /* the user changes the general label and creates the pdf on a pod, which posts the data with the change */
    pdf_tab_label: function () {
        selectItem(PV_CAPACITY);
        typeField(FIELD_GENERAL_LABEL, PV_CAPACITY_LABEL);
        pressCreatePdf();
    },
    /* the user opens the PDF tab of a page that is not on a pod, where only a pod can create the pdf */
    pdf_tab_offline: function () {
        click("#tab-pdf");
        messageElement.textContent = JSON.stringify({disabled: document.querySelector('[data-act="createPdf"]').disabled});
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
    },
    /* the arguments start collapsed above the inputs, which start open */
    arguments_collapsed: function () {
        const lists = Array.from(document.querySelectorAll("#tree details.sect")).map(function (d) {
            return {list: d.dataset.list, open: d.open};
        });
        messageElement.textContent = JSON.stringify(lists.slice(0, 2));
    },
    /* the user changes the text of an argument, which is the description of its claim triple */
    argument_text: function () {
        itemOf(SUPPLY_GAP_CLAIM, KIND_ARGUMENT).click();
        typeField(FIELD_ARGUMENT_TEXT, ARGUMENT_TEXT);
        pressSave();
    },
    /* the user moves an argument to the other side, which changes the verb and so the name of its claim */
    argument_side: function () {
        itemOf(SUPPLY_GAP_CLAIM, KIND_ARGUMENT).click();
        typeField(FIELD_ARGUMENT_SIDE, SIDE_CONTRA);
        pressSave();
    },
    /* the user adds an argument with a reason that is not in the database yet */
    add_argument: function () {
        click('[data-act="addTop"][data-list="argument"]');
        typeField(FIELD_ARGUMENT_REASON, NEW_WORD);
        typeField(FIELD_ARGUMENT_TEXT, ARGUMENT_TEXT);
        pressSave();
    },
    /* the user adds an argument but types no reason, so there is nothing to save */
    add_argument_without_reason: function () {
        click('[data-act="addTop"][data-list="argument"]');
        typeField(FIELD_ARGUMENT_TEXT, ARGUMENT_TEXT);
        pressSave();
    },
    /* the user removes an argument from the page, which keeps its claim */
    remove_argument: function () {
        itemOf(SUPPLY_GAP_CLAIM, KIND_ARGUMENT).querySelector('[data-act="remove"]').click();
        pressSave();
    },
    /* the user changes the lead text that the page shows collapsed below the title */
    more_text: function () {
        typeField(FIELD_MORE_TEXT, ARGUMENT_TEXT);
        pressSave();
    },
    /* the user types the loaded lead text again, so there is nothing to save */
    more_text_unchanged: function () {
        const more = DOC.triples.find(function (t) {
            return t.to === KW_MORE;
        });
        typeField(FIELD_MORE_TEXT, more.description);
        pressSave();
    },
    /* the user changes the headline of a state of the verdict, which is the label of the state */
    verdict_label: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_LABEL, VERDICT_LABEL);
        pressSave();
    },
    /* the user changes the scale and the note of the verdict box, which all states share */
    verdict_scale_note: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_VERDICT_SCALE, VERDICT_SCALE);
        typeField(FIELD_VERDICT_NOTE, ARGUMENT_TEXT);
        pressSave();
    },
    /* the user lets the verdict compare another result, which links the result triple of the verdict to it */
    verdict_result: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_VERDICT_RESULT, VERDICT_RESULT_OTHER);
        pressSave();
    },
    /* the user types the loaded result again, so there is nothing to save */
    verdict_result_unchanged: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_VERDICT_RESULT, VERDICT_RESULT);
        pressSave();
    },
    /* the user widens the meter of the verdict, which changes the two limit values of the scale */
    verdict_scale_limits: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_VERDICT_SCALE_MIN, VERDICT_SCALE_MIN_WIDE);
        typeField(FIELD_VERDICT_SCALE_MAX, VERDICT_SCALE_MAX_WIDE);
        pressSave();
    },
    /* the user types the loaded left end again, so there is nothing to save */
    verdict_scale_limits_unchanged: function () {
        itemOf(VERDICT_UNDECIDED, KIND_VERDICT).click();
        typeField(FIELD_VERDICT_SCALE_MIN, VERDICT_SCALE_MIN);
        pressSave();
    },
    /* the user changes the number of a value of the row axis of the sensitivity table, which is its stored value */
    sensitivity_number: function () {
        itemOf(SENSITIVITY_LEVEL, KIND_SLEVEL).click();
        typeField(FIELD_SENSITIVITY_NUMBER, SENSITIVITY_LEVEL_NUMBER);
        pressSave();
    },
    /* the user adds a value to the row axis with its label and number, which adds the phrase, the triples and the value */
    sensitivity_add_level: function () {
        const axis = itemOf(SENSITIVITY_ROWS, KIND_SAXIS);
        click('[data-act="addChild"][data-uid="' + axis.dataset.uid + '"]');
        typeField(FIELD_PHRASE, SENSITIVITY_NEW_LEVEL);
        typeField(FIELD_LABEL, SENSITIVITY_NEW_LEVEL_LABEL);
        typeField(FIELD_SENSITIVITY_NUMBER, SENSITIVITY_NEW_LEVEL_NUMBER);
        pressSave();
    },
    /* the user adds a value to the row axis with a number, but types no phrase, so there is nothing to save */
    sensitivity_add_level_without_phrase: function () {
        const axis = itemOf(SENSITIVITY_ROWS, KIND_SAXIS);
        click('[data-act="addChild"][data-uid="' + axis.dataset.uid + '"]');
        typeField(FIELD_SENSITIVITY_NUMBER, SENSITIVITY_NEW_LEVEL_NUMBER);
        pressSave();
    },
    /* the user lets the cells show another result in brackets and changes the hint below the table */
    sensitivity_cell_hint: function () {
        itemOf(SENSITIVITY_ROWS, KIND_SAXIS).click();
        typeField(FIELD_SENSITIVITY_CHANGE, SENSITIVITY_CHANGE);
        typeField(FIELD_SENSITIVITY_HINT, SENSITIVITY_HINT);
        pressSave();
    },
    /* the user clicks a column heading and a month row of the detail table in the page preview, which selects the
       column and the table, because a row of the table phrase has no triple of its own, then the heading of the
       sources, which has no part and keeps the selection, and the link of a source, which selects the source and
       stays in the editor */
    preview_detail_parts: function () {
        const pane = document.getElementById("pane");
        const clicks = {};
        pane.querySelector("th[" + PART_ATTR + '="' + DETAIL_COLUMN + '"]').click();
        clicks.column = selectedTitle();
        pane.querySelector("tr[" + PART_ATTR + '="' + DETAIL_TABLE + '"] td').click();
        clicks.row = selectedTitle();
        pane.querySelector(".src h2").click();
        clicks.heading = selectedTitle();
        const href = location.href;
        sourceLink(SOURCE_PV).click();
        clicks.source = selectedTitle();
        clicks.stays = location.href === href;
        messageElement.textContent = JSON.stringify(clicks);
    },
    /* the user changes the text of a preset button, which is the label of the preset */
    preset_label: function () {
        itemOf(PRESET_ACADEMIES, KIND_PRESET).click();
        typeField(FIELD_LABEL, PRESET_LABEL);
        pressSave();
    },
    /* the user changes a number that a preset sets, which is the stored value of the preset and the field */
    preset_value: function () {
        itemOf(PRESET_ACADEMIES, KIND_PRESET).click();
        typeField(FIELD_CONSTRUCTION_COST, PRESET_CONSTRUCTION_COST);
        pressSave();
    },
    /* the user lets a preset set a field that it has left as it is, which adds a value with the unit of the field */
    preset_new_value: function () {
        itemOf(PRESET_ACADEMIES, KIND_PRESET).click();
        typeField(FIELD_CAPACITY_FACTOR, PRESET_CAPACITY_FACTOR);
        pressSave();
    },
    /* the user clears a number of a preset, which deletes its value */
    preset_value_cleared: function () {
        itemOf(PRESET_ACADEMIES, KIND_PRESET).click();
        typeField(FIELD_CONSTRUCTION_TIME, "");
        pressSave();
    },
    /* the user adds a preset with a new scenario, its button text and a number */
    add_preset: function () {
        click('[data-act="addTop"][data-list="preset"]');
        typeField(FIELD_PHRASE, NEW_WORD);
        typeField(FIELD_LABEL, PRESET_LABEL);
        typeField(FIELD_CONSTRUCTION_TIME, PRESET_CONSTRUCTION_TIME);
        pressSave();
    },
    /* the user adds a preset with a button text, but no scenario, so there is nothing to save */
    add_preset_without_phrase: function () {
        click('[data-act="addTop"][data-list="preset"]');
        typeField(FIELD_LABEL, PRESET_LABEL);
        pressSave();
    },
    /* the user changes the text of a note, which is the description of the triple of the note */
    note_text: function () {
        itemOf(NOTE_MODEL_LIMITS, KIND_NOTE).click();
        typeField(FIELD_NOTE_TEXT, NOTE_TEXT);
        pressSave();
    },
    /* the user types the loaded text of a note again, so there is nothing to save */
    note_text_unchanged: function () {
        itemOf(NOTE_MODEL_LIMITS, KIND_NOTE).click();
        const note = DOC.triples.find(function (t) {
            return t.name === NOTE_MODEL_LIMITS_ITEM;
        });
        typeField(FIELD_NOTE_TEXT, note.description);
        pressSave();
    },
    /* the user changes the description of a source, which is sent as the changed field of the source */
    source_description: function () {
        sourceOf(SOURCE_NPP).click();
        typeField(FIELD_SOURCE_DESCRIPTION, SOURCE_DESCRIPTION);
        pressSave();
    },
    /* the user renames a source, which also changes the texts that name it by {source:…} */
    source_rename: function () {
        sourceOf(SOURCE_RENAMED).click();
        typeField(FIELD_SOURCE_NAME, SOURCE_NEW_NAME);
        pressSave();
    },
    /* the user types the loaded link of a source again, so there is nothing to save */
    source_url_unchanged: function () {
        sourceOf(SOURCE_NPP).click();
        const source = DOC.sources.find(function (s) {
            return s.name === SOURCE_NPP;
        });
        typeField(FIELD_SOURCE_URL, source.url || "");
        pressSave();
    },
    /* the user removes a preset from the page, which keeps the numbers of its scenario */
    remove_preset: function () {
        itemOf(PRESET_ETH_LOW, KIND_PRESET).querySelector('[data-act="remove"]').click();
        pressSave();
    },
    /* the user clicks an argument, the verdict, a field, a preset button, a row group, a result row, a result column,
       a row of the sensitivity table, the hideable lead text, a source, a note of the notes list and a note of a
       formula in the page preview, which selects where each is changed, and the heading of the sources, which has no
       part and keeps the selection */
    preview_parts: function () {
        const pane = document.getElementById("pane");
        const clicks = {};
        // PART_ATTR of calculator_editor.js, which the page of the editor loads before this script
        pane.querySelector("[" + PART_ATTR + '="' + SUPPLY_GAP_ARGUMENT + '"] b').click();
        clicks.argument = selectedTitle();
        pane.querySelector(".verdict .headline").click();
        clicks.verdict = selectedTitle();
        pane.querySelector(".src h2").click();
        clicks.heading = selectedTitle();
        sourceLink(SOURCE_NPP).click();
        clicks.source = selectedTitle();
        pane.querySelector(".field label").click();
        clicks.field = selectedTitle();
        pane.querySelector("[" + PART_ATTR + '="' + PRESET_ACADEMIES_ITEM + '"]').click();
        clicks.preset = selectedTitle();
        pane.querySelector("tbody tr.sec td").click();
        clicks.rowGroup = selectedTitle();
        pane.querySelector("tbody tr:not(.sec) td").click();
        clicks.row = selectedTitle();
        pane.querySelector("thead th[" + PART_ATTR + "]").click();
        clicks.column = selectedTitle();
        pane.querySelector("details.sensitivity tbody td").click();
        clicks.level = selectedTitle();
        const more = pane.querySelector("header details");
        more.querySelector("summary").click();
        clicks.more = selectedTitle();
        clicks.moreOpen = pane.contains(more) && more.open;
        pane.querySelector(".notes p[" + PART_ATTR + '="' + NOTE_MODEL_LIMITS_ITEM + '"]').click();
        clicks.note = selectedTitle();
        // the last click, because the selected formula shows the formula compare instead of the preview
        pane.querySelector(".notes p[" + PART_ATTR + "]").click();
        const formula = findFormula(selected);
        clicks.formula = formula === null ? "" : formula.name;
        clicks.compare = !document.getElementById(FORMULA_COMPARE_HOST_ID).hidden;
        messageElement.textContent = JSON.stringify(clicks);
    }
};

window.addEventListener("load", function () {
    document.body.appendChild(messageElement);
    SCENARIOS[location.hash.slice(1)]();
    messageElement.id = PUT_MESSAGE_ID;
});
