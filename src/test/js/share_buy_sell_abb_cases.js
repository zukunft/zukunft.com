"use strict";

/* the input cases of the ABB share decision test for use_case_cases.js, typed into the hand made sample
   share_buy_sell_abb_sample.html and into the generated page share_buy_sell_abb_js.html */

/* per case the number per field label: standard has the numbers of the sample, high and low change every input and
   assumption that both pages show, within the limits of the fields of the sample; the five weights add up to 100 %
   and the required return stays above both growth rates, so that each method has a target price */
const CASES = {
    /* no input, to read the sample after its script has filled the sensitivity table */
    unchanged: {},
    standard: {
        "Share price": "72.88",
        "Earnings per share 2027 (estimate)": "3.75",
        "Fair price-earnings ratio": "22",
        "Free cash flow 2026": "5.0",
        "Cash flow growth over the next ten years": "8",
        "Required return (discount rate)": "8",
        "Fair EV/EBITA multiple": "18",
        "Analyst consensus target": "77",
        "Margin of safety (decision threshold)": "10",
        "Earnings per share 2026 (estimate)": "3.35",
        "Earnings per share, last twelve months": "2.76",
        "Dividend per share": "0.94",
        "Dividend growth, perpetual": "5",
        "Years of explicit cash flow growth": "10",
        "Terminal growth after that": "2.5",
        "Operational EBITA 2026": "8.3",
        "Net debt": "1.33",
        "Shares outstanding": "1.808",
        "US dollar in Swiss francs": "0.83",
        "Holding period": "3",
        "Weight: earnings multiple": "30",
        "Weight: discounted cash flow": "30",
        "Weight: enterprise value multiple": "20",
        "Weight: dividend discount": "5",
        "Weight: analyst consensus": "15"
    },
    high: {
        "Share price": "90",
        "Earnings per share 2027 (estimate)": "4.2",
        "Fair price-earnings ratio": "26",
        "Free cash flow 2026": "6.0",
        "Cash flow growth over the next ten years": "11",
        "Required return (discount rate)": "10",
        "Fair EV/EBITA multiple": "21",
        "Analyst consensus target": "88",
        "Margin of safety (decision threshold)": "15",
        "Earnings per share 2026 (estimate)": "3.6",
        "Earnings per share, last twelve months": "3.0",
        "Dividend per share": "1.1",
        "Dividend growth, perpetual": "6",
        "Years of explicit cash flow growth": "12",
        "Terminal growth after that": "3",
        "Operational EBITA 2026": "9.5",
        "Net debt": "3.0",
        "Shares outstanding": "1.85",
        "US dollar in Swiss francs": "0.9",
        "Holding period": "5",
        "Weight: earnings multiple": "40",
        "Weight: discounted cash flow": "30",
        "Weight: enterprise value multiple": "10",
        "Weight: dividend discount": "10",
        "Weight: analyst consensus": "10"
    },
    low: {
        "Share price": "55",
        "Earnings per share 2027 (estimate)": "3.4",
        "Fair price-earnings ratio": "17",
        "Free cash flow 2026": "4.0",
        "Cash flow growth over the next ten years": "4",
        "Required return (discount rate)": "6",
        "Fair EV/EBITA multiple": "15",
        "Analyst consensus target": "57",
        "Margin of safety (decision threshold)": "5",
        "Earnings per share 2026 (estimate)": "3.0",
        "Earnings per share, last twelve months": "2.4",
        "Dividend per share": "0.8",
        "Dividend growth, perpetual": "3",
        "Years of explicit cash flow growth": "5",
        "Terminal growth after that": "1.5",
        "Operational EBITA 2026": "7.0",
        "Net debt": "-1.0",
        "Shares outstanding": "1.7",
        "US dollar in Swiss francs": "0.75",
        "Holding period": "2",
        "Weight: earnings multiple": "20",
        "Weight: discounted cash flow": "40",
        "Weight: enterprise value multiple": "20",
        "Weight: dividend discount": "10",
        "Weight: analyst consensus": "10"
    }
};
