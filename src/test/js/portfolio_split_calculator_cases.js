"use strict";

/* the input cases of the portfolio split test for use_case_cases.js, typed into the hand made sample
   portfolio_split_calculator_sample.html and into the generated page portfolio_split_calculator_js.html */
const CASES = {
    /* no input, to read the sample after its script has filled the tables */
    unchanged: {},
    standard: {
        "Target weight first child": "35",
        "Target weight second child": "35",
        "Walk order": "Unit value × round lot, descending"
    },
    low: {
        "Target weight first child": "20",
        "Target weight second child": "20",
        "Walk order": "Unit value × round lot, descending"
    },
    high: {
        "Target weight first child": "45",
        "Target weight second child": "45",
        "Walk order": "Bank report order"
    },
    /* the weights of the children exceed 100 %, which shows the warning of the excess */
    over: {
        "Target weight first child": "60",
        "Target weight second child": "60"
    },
    /* different weights while mirroring is asked, which shows the warning of the difference */
    differ: {
        "Target weight first child": "30",
        "Target weight second child": "40"
    }
};
