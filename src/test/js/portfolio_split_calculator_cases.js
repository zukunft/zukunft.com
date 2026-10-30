"use strict";

/* the input cases of the portfolio split test for use_case_cases.js, typed into the hand made sample
   portfolio_split_calculator_sample.html and into the generated page portfolio_split_calculator_js.html;
   the two pages name the fields differently so far, so the case has the label of both pages and the
   report of the case names the label that a page does not have */
const CASES = {
    standard: {
        "Target weight first child": "35",
        "Target weight second child": "35",
        "Target weight": "0.35",
        "Walk order": "lot"
    },
    low: {
        "Target weight first child": "20",
        "Target weight second child": "20",
        "Target weight": "0.20",
        "Walk order": "lot"
    },
    high: {
        "Target weight first child": "45",
        "Target weight second child": "45",
        "Target weight": "0.45",
        "Walk order": "bank"
    }
};
