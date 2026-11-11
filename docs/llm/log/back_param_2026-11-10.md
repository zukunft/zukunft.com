# back param (2026-11-10)

The prompt "the old `$back` url parameter" of `docs/llm/pending.md`. The tables were written before the first code
edit; the line numbers are those of the start state (commit `8b60ac85d` with the uncommitted switch off of the
committed tests in `a_selected_test.php`), several of them moved since the prompt was written on the same day.

## Request

The back target of a page is the `'9'`-prefixed part of its url (`url_var::BACK`), built from the url array of
the calling page; `web/` is free of `$back`, which `coding_rule_tests::php_web_no_back_param_tests` keeps that way.
The prompt lists what is left of the old parameter, and the target is that nothing of it is left:
- the backend code that names it but is not reachable: `cfg/phrase/phrase.php::dsp_time_selector` (`:2001-2006`)
  passes a `$back` to a `word::dsp_time_selector` that exists in no layer, and its only callers, the two calls
  `$time_phr->dsp_time_selector(...)` of the frontend `value::dsp_edit` (`web/value/value.php:1319`, `:1334`),
  call a frontend function that does not exist either, so the legacy pages `http_old/value_edit.php` and
  `value_add.php`, which call `dsp_edit`, end in an uncaught fatal as soon as they reach the time selector
- two comments that name the old parameter: the TODO `cfg/word/word_list.php:10` and the docblock
  `web/html/html_base.php:1396-1406` ("TODO Prio 1 deprecate and replace if the the url_arr based back var"),
  which has no function any more and stands directly above the docblock of `base_url_clean`
- the tests: the switched off targets of `element::link` with `&back=0`
  (`unit_write/element_write_tests.php:117`, `:121`, `:123`), the switched off target of `button::back` with the
  old page `?words=1` (`unit_ui/base_ui_tests.php:1400`), the local `$back` of `login_url_tests.php:128`, which
  holds the new `'9'`-prefixed part under the old name, and the commented out tests of the retired
  `http/*_add.php`, `*_edit.php` and `*_del.php` pages (`unit_write/formula_write_tests.php:375-395`,
  `test_word_display.php:174`, `view_write_tests.php:112`, `value_write_tests.php:283`, `:292`,
  `element_group_write_tests.php:168`, `unit_ui/formula_ui_tests.php:673-685`)
- the issue descriptions that are done with this prompt: `docs/todo.md:97`, the last item of
  `docs/llm/pending_prio_2.md:609`, the remark on `$back` of the `dsp_errors` item (`pending_prio_2.md:1050`),
  and in the "more" section of `docs/llm/pending.md` the done note on `web/value/value_list.php` under "review
  buttons", the item "review $back" and the `$back` of the agent buttons of the triage prompt

The sample text `&back=1` of the `str_diff` test (`unit/lib_tests.php:1518-1522`) is no use of the parameter and
stays.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/phrase/phrase.php`, `web/value/value.php`, `cfg/word/word_list.php`, `web/html/html_base.php` | `dsp_time_selector` 1995-2006; `dsp_edit` 1308-1340; 10; 1396-1406 | without these lines | `phrase::dsp_time_selector` has no caller in the backend and calls a function that does not exist; the two time selector blocks of `value::dsp_edit` call a function that exists in no layer; `dsp_edit` still calls `dsp_share` and `dsp_protection`, which exist nowhere either, so the legacy pages keep their fatal until the item "`value::dsp_edit` calls `dsp_share` and `dsp_protection`" of `pending_prio_2.md` is decided; the time selector of the value edit, with the default selection of the removed comment (e.g. the quarters of the year for Q1), is a missing part of the value edit view (`pending_prio_2.md:1534`), so it is noted there; the TODO and the docblock without function name a parameter that no longer exists |
| `test/php/unit_write/element_write_tests.php`, `test/php/unit_ui/base_ui_tests.php`, `test/php/unit_workflow/login_url_tests.php`, the commented out tests of the retired pages | 117-123; 1399-1402; 128-129; see the request | the targets of today; the `button::back` assert switched on; `$back_part`; without the commented out tests | the targets of `element::link` and of `button::back` are the links that the functions write today (the `element::link` assert stays off with its `TODO Prio 0 activate`, because it needs the formula elements of the write tests); the `button::back` assert is switched on, because a back button without url array links the start page (`button.php:204`); the local var gets a name of the new back part; the commented out tests of functions that exist in no layer any more (`formula::dsp_result`, the `dsp_hist` and `dsp_hist_links` of a formula, `word::dsp_val_list`, the backend `view::display`) and of the retired formula pages are removed with their dead setup lines (`$page`, `$size`, `$call`, the `$wrd` of the view display test) and the imports that only they used (`rest_ctrl` in `formula_write_tests`, `word` and `words` in `view_write_tests`); the commented out `btn_edit` test of a formula keeps its reactivation TODO, because `db_object::btn_edit` exists, and loses only its old target with `&back=`; the three commented out old targets in `value_write_tests` and `element_group_write_tests` stand above their active successor, so only the line goes |
| `docs/todo.md`, `docs/llm/pending_prio_2.md`, `docs/llm/pending.md` | 97; 609 last item, 1050-1055, 1534; "more" (2263, 2292-2302), the prompt | without the done parts; + the time selector in the value edit view | the issue descriptions state only what is open; the prompt is removed once done |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `coding_rule_tests::php_web_no_back_param_tests` | — | — | no change: `web/` stays free of `$back` and of `&back=` |
| `test/php/unit/lib_tests.php` | 1518-1522 | — | no change: `&back=1` is only sample text of the `str_diff` test |
| html snapshots | — | — | no change: no snapshot renders `value::dsp_edit`, which only the legacy pages call; checked by the html snapshot run |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | — | generated; without `phrase::dsp_time_selector` |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switched on: `base_ui_tests` (the `button::back` assert), `login_url_tests` (the renamed var, with its include and use), `coding_rule_tests` with `run_html_snapshots` (the back url rule and the generated docs), and the write tests whose active lines changed: `element_write_tests` (the target), `formula_write_tests` and `view_write_tests` (the removed setup lines) |
| `docs/llm/pending_prio_2.md` | 609-621, the items of `value::dsp_edit` and of the `$back` left outside `web/` | same section | the section intro says that no `$back` is left, the `dsp_edit` item names only `dsp_share` and `dsp_protection`, the item of the `$back` left outside `web/` goes |
| `test/php/unit_workflow/word_url_tests.php` | 167 | — | no change: the comment quotes the failing url of an old bug with `&back=259` as an example |

A `test/test_part.php` run is enough, because no import and no id changes; the full `test/test.php` run follows
before the commit.

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|

Written after the unexpected changes are known.

## Lessons learned by llm

Written after the unexpected changes are known.

## Lessons learned by human

nice and smooth because code has been prepared? Or because no functionality has been added?
