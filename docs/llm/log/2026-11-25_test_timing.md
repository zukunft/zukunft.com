# test timing (2026-11-25)

This log is started before the first edit, so the tables below are the plan; "Unexpected changes" stays empty until
the developer has run the tests.

## Request

"is it possible to reduce the test time to do tha workflow tests in parallel?" and then "yes, write the timing
script first": a full `test/test.php` takes almost an hour, and before a part is run in parallel the time per test
section has to be known. In the log of a run the workflow tests start after about 24 of the 60 minutes, so another
section may be the better target.

The log of a run in the text format has a runtime timestamp in front of every line (`text_log::time_stamp`, e.g.
`1463.5311 workflow start `), and a section header (`test_base::header` and `subheader`) is a line with only the
header text. The script reads a saved log (`php test/test.php > test_run.log`), takes the time from one header to
the next as the time of the section and writes a markdown report with the total, the time per first word and per
first two words of the header (e.g. `db` and `db write`) and the slowest sections. A line is no header if it is in
an error block (from `ERROR ...` to `, took ...`), starts with an info prefix (`ok `, `updated `, `timeout: `,
`PHP `, `#`), contains a `:` or a `/` or does not start with a letter. It reads only the log file, needs no
database and is not a `/test/*` script, so the developer starts it after a run; the report goes to the standard
output and is not committed, because it changes with every run.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/test/php/utils/test_timing.php` (new), `bin/test_timing.php` (new), `src/test/php/const/files.php` (no change needed if the bin takes the log path as argument) | no time per test section | `test_timing::line_parts`, `is_header`, `sections`, `sums` and `md`; the bin reads the log file of its first argument and echoes the report | find the test sections that take the most time before one is run in parallel |
| `src/test/php/unit/coding_rule_tests.php` | — | the tests of `test_timing`: a header and the next header give the time of the section, an error block and an info line are no header, the sums per first word and per first two words, the report with the slowest section first; negative: a line without a timestamp and a line inside an error block are skipped | the tests first, like the other reports of `bin/` |
| `docs/llm/testing.md` | — | the use of `bin/test_timing.php` after a run in *Each change is tested upfront with `test/test_part.php`* | the developer finds the script |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `docs/code_test_coverage.md`, `docs/functions.md`, `docs/objects.md` and the other generated docs of the code rules | — | — | a new class with five functions |
| `docs/test_differences.md` | — | — | no change: `a_selected_test` and `all_tests` are not changed |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/unit/coding_rule_tests.php` | `$t->assert($test_name, $tim->line_parts('workflow start'), null)` | `$t->assert_null($test_name, $tim->line_parts('workflow start'))` | the run reported "... but a line without a timestamp is no log line (result of test ... has been null)": `assert` reports a null result as an error even if null is expected |

## Lessons learned by llm (2026-11-25)

- A test that expects null uses `assert_null`, never `assert` with null as the expected value, because `assert`
  treats a null result as a failed call.
