"use strict";

/* the input cases of the Swiss NPP vote for use_case_cases.js with the sensitivity table opened, for the page that the
   browser builds and for the page preview of the calculator editor: both fill the cells only while the table is open,
   so the table is opened on the load before use_case_cases.js reads the page; the toggle event is sent at once,
   because the browser sends its own only after the page has been read */
const CASES = {
    /* no input, to read both pages with the cells of the sensitivity table */
    unchanged: {}
};

window.addEventListener("load", function () {
    const table = document.querySelector("details.sensitivity");
    if (table !== null) {
        table.open = true;
        table.dispatchEvent(new Event("toggle"));
    }
});
