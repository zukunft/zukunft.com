"use strict";

/* the input cases of the PV calculator test for use_case_cases.js, typed into the hand made sample
   pv_lifetime_balance_sample.html and into the generated page pv_lifetime_balance_js.html */

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
