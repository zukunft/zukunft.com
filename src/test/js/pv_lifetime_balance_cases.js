"use strict";

/* the input cases of the PV calculator test (unit_read/system_views_read_tests.php): the test adds this script
   to a copy of the hand made sample pv_lifetime_balance_sample.html and to one of the generated page
   pv_lifetime_balance_js.html, which a headless browser opens with the name of the case as the hash e.g. #high;
   the case types the same numbers into the fields of both pages like a user, so that both scripts calculate
   their results from the same inputs, and the test compares what the two pages show */

/* the element with the result of the case as json: the labels of the fields or options that the page does not
   have and the html of the page after the case; it gets its id only after the case has ended, so a failed case
   leaves no element */
const CASE_RESULT_ID = "caseResult";
const CASE_MISSING = "missing";
const CASE_PAGE = "page";

/* per case the number or the option value per field label: standard has the numbers of the sample, high and
   low change every input and assumption that both pages show; the replacement mix has only two options, so
   high and low both select the other one */
const CASES = {
    standard: {
        "System size": "10",
        "Orientation": "0.85",
        "Battery capacity (usable)": "10",
        "Local electricity consumption": "4500",
        "Specific yield, south 30°": "976",
        "Lifetime": "30",
        "PV life-cycle emissions": "35.8",
        "Swiss consumer mix, annual": "128",
        "Replacement mix": "523",
        "Load share during PV hours": "0.35",
        "Battery round-trip efficiency": "0.90",
        "Battery production emissions": "100",
        "Battery lifetime": "15"
    },
    high: {
        "System size": "20",
        "Orientation": "1.00",
        "Battery capacity (usable)": "20",
        "Local electricity consumption": "9000",
        "Specific yield, south 30°": "1200",
        "Lifetime": "40",
        "PV life-cycle emissions": "50",
        "Swiss consumer mix, annual": "200",
        "Replacement mix": "395",
        "Load share during PV hours": "0.6",
        "Battery round-trip efficiency": "0.95",
        "Battery production emissions": "150",
        "Battery lifetime": "20"
    },
    low: {
        "System size": "5",
        "Orientation": "0.59",
        "Battery capacity (usable)": "5",
        "Local electricity consumption": "2000",
        "Specific yield, south 30°": "800",
        "Lifetime": "20",
        "PV life-cycle emissions": "20",
        "Swiss consumer mix, annual": "60",
        "Replacement mix": "395",
        "Load share during PV hours": "0.2",
        "Battery round-trip efficiency": "0.80",
        "Battery production emissions": "60",
        "Battery lifetime": "10"
    },
    /* a field that no page has, which the test expects to be reported */
    missing_field: {
        "Wind turbine size": "1"
    }
};

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
