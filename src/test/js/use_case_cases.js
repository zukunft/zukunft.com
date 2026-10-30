"use strict";

/* the input cases of a use case page (unit_read/system_views_read_tests.php): the test adds a script with the
   CASES of the use case and this script to a copy of the hand made sample and to one of the generated page, which
   a headless browser opens with the name of the case as the hash e.g. #high; the case types the same numbers into
   the fields of both pages like a user, so that both scripts calculate their results from the same inputs, and the
   test compares what the two pages show */

/* the element with the result of the case as json: the labels of the fields or options that the page does not
   have and the html of the page after the case; it gets its id only after the case has ended, so a failed case
   leaves no element */
const CASE_RESULT_ID = "caseResult";
const CASE_MISSING = "missing";
const CASE_PAGE = "page";

/* the field of the page whose label starts with the text, because the tag of an assumption follows the text */
function fieldOf(label) {
    const lab = Array.from(document.querySelectorAll("main label")).find(function (l) {
        return l.firstChild !== null && l.firstChild.textContent.trim() === label;
    });
    return lab === undefined ? null : document.getElementById(lab.htmlFor);
}

/* select the option with the value, also by its attribute, which the test reads from the page */
function selectOption(field, value) {
    const opt = Array.from(field.options).find(function (o) {
        return o.value === value;
    });
    if (opt !== undefined) {
        Array.from(field.options).forEach(function (o) {
            o.removeAttribute("selected");
        });
        opt.setAttribute("selected", "");
        field.value = value;
    }
    return opt !== undefined;
}

/* type the number into the field like a user, also into its attribute, which the test reads from the page */
function typeNumber(field, value) {
    let result = true;
    if (field.tagName === "SELECT") {
        result = selectOption(field, value);
    } else {
        field.value = value;
        field.setAttribute("value", value);
    }
    if (result) {
        field.dispatchEvent(new Event("input", {bubbles: true}));
        field.dispatchEvent(new Event("change", {bubbles: true}));
    }
    return result;
}

window.addEventListener("load", function () {
    const values = CASES[location.hash.slice(1)];
    const missing = Object.keys(values).filter(function (label) {
        const field = fieldOf(label);
        return field === null || !typeNumber(field, values[label]);
    });
    const caseResult = {};
    caseResult[CASE_MISSING] = missing;
    caseResult[CASE_PAGE] = document.documentElement.outerHTML;
    const caseElement = document.createElement("pre");
    caseElement.textContent = JSON.stringify(caseResult);
    document.body.appendChild(caseElement);
    caseElement.id = CASE_RESULT_ID;
});
