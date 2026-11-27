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
    },
    /* a third child, which gets the weight of the first child if the parent can still give it, so 30 % */
    add_child: {
        "press": ["Add child"]
    },
    /* a third child and then the second one removed, so the first and the former third child remain */
    remove_child: {
        "press": ["Add child", "Remove the second child"]
    },
    /* the second child of the database removed, whose is a triple the save deletes; only for the save vars of the
       generated page (system_views_read_tests::assert_calculator_save_vars), so without a case report */
    remove_saved_child: {
        "press": ["Remove the second child"]
    }
};
