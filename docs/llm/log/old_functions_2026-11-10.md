# old functions (2026-11-10)

Steps 4 "remove sql_creator::where_old", 5 "remove the unused legacy functions" and 6 "the frontend *_old
functions" of the prompt "deprecate the *_old functions" of `docs/llm/pending.md`. The tables were written before
the first code edit; the line numbers are those of the start state (commit `b2cc7ca0d`); the three steps change
different files, so the line numbers of each step hold after the others. The code of step 4 is done, the tests of
none of the steps have run yet.

## Request

### step 4: remove sql_creator::where_old

`sql_creator::where` (`sql_creator.php:3778`) calls `where_new` (`:3793`) if the parameter list has an entry and
`where_old` (`:4071` to `4257`) if not. The plan says: log the builders that still reach `where_old` during a full
test run, switch them to `add_where_par`, then remove `where_old` and the fallback. The code shows that this log
would stay empty:
- the body of `where_old` runs only if `par_lst->count() > 0` (`:4078`), which is the case that `where` sends to
  `where_new`, so every call of `where_old` returns an empty string
- `where_new` returns the same empty string for an empty parameter list, because it loops over the where list
  and a where entry is only added together with its parameter: `add_where` (`:1125`), `add_where_in_sub`
  (`:1262`) and the direct `add_where_no_par` callers (`value_list.php:732`, `1138`,
  `sandbox_value_list.php:435`, `449`, `459`, `496`), which point to parameters added before them

So no builder has to switch and no fixture can change; the full test run with the log is replaced by this check,
confirmed by the developer before the first code edit.

### step 5: remove the unused legacy functions

Remove the legacy functions of the plan that nothing calls. Each name was searched again in `src`, `test` and
`docs` (php, md, js and json, so also as a string or a dynamic call); outside the generated docs no caller is left:
- `result_list::load_by_obj` (`cfg/result/result_list.php:778`) and `load_sql_by_obj_old` (`:675`) only call each
  other; the hit in `test_base.php:4838` is `change_log_link_list::load_by_obj`
- `change::load_sql_old` (`cfg/log/change.php:326`), a raw sql text; the hits of `load_sql_old` in
  `user_tests.php`, `user_log_write_tests.php` and `user_log.php` are `user_log::load_sql_old`, which stays
  (the `_old` rule of `docs/llm/coding.md`)
- `phrase_list::time_lst_old` (`cfg/phrase/phrase_list.php:2038`), the private
  `expression::element_part_list_old` (`cfg/formula/expression.php:874`), `word::calc_usage_old`
  (`cfg/word/word.php:1800`, whose body is commented out) and `library::str_diff_old` (`shared/library.php:4180`,
  next to the used `str_diff`)

### step 6: the frontend *_old functions

`html_base::url_old` (`html_base.php:1407`) builds the urls of the old layout with one script per page
(`/http/<class>_edit.php?id=`); of these scripts only `view.php` and `about.php` are left in `http/`, so most of
its links lead nowhere. Its live callers and the other `*_old` functions of the plan move to their current
replacements or, if nothing calls them, are removed, decided by the developer:
- the only `url_old` link in the html snapshots is the footer link `/http/about.php` (966 files), which keeps its
  exact url, so no snapshot is expected to change; the other links of `url_old` are in no snapshot
- `result_list::display_old` (`web/result/result_list.php:443`) and `formula_list::display_old`
  (`web/formula/formula_list.php:251`) have no caller, `db_object::phrase_selector_old`
  (`web/sandbox/db_object.php:1257`) only a commented-out one (`web/formula/formula.php:916`, which stays like the
  commented-out callers of step 3), and `element_group::dsp_values_old` (`web/element/element_group.php:378`) has
  one live caller (`web/result/result.php:529`) but no newer version

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/db/sql_creator.php` (step 4) | `where` 3773-3785, `where_new` 3787-3846, `where_old` 4066-4257; `sql` 1307; the fields `par_use_link`, `par_named`, `par_name` 289-291 and their reset 420-422 | `where`; the rest removed | `where` gets the body of `where_new`, so the fallback and the second name go; `where_new` does not read its `$par_offset`, so `where` has no parameter and `sql` calls it without the offset (the sub query offset is still passed to `from`); `where_old` is never reached with a parameter and goes; the three arrays are only read by `where_old` and only written by the reset |
| `cfg/result/result_list.php`, `cfg/log/change.php`, `cfg/phrase/phrase_list.php`, `cfg/formula/expression.php`, `cfg/word/word.php`, `shared/library.php` (step 5) | `load_sql_by_obj_old` 659-723, `load_by_obj` 763-784; `load_sql_old` 326-397; `time_lst_old` 2033-2053; `element_part_list_old` 869-891 with its section header; `calc_usage_old` 1797-1814; `str_diff_old` 4174-4214 | removed | no caller; the dedicated loaders `load_by_formula`, `load_by_phrase` and `load_by_grp` with their builders replace the two of `result_list`, the change lists read with named prepared statements; the section "extract helper" of `expression` has no other function |
| `web/html/html_base.php`, `web/view/view.php`, `web/view/view_exe.php`, `web/word/word.php`, `web/system/job.php`, `web/result/result_list.php`, `web/formula/formula_list.php`, `web/sandbox/db_object.php`, `web/element/element_group.php`, `web/result/result.php`, `test/php/unit_ui/base_ui_tests.php` (step 6) | `site_footer` 1111, `url_old` 1407-1427, `edit_url` 2677-2680, `list_sort` 2688-2724; `view.php` 255, 264, 634; `view_exe.php` 650; `word.php` 940; `job.php` 443; `display_old` 443-480, `display_old` 251-296, `phrase_selector_old` 1257-1268, `dsp_values_old` 378-404; `result.php` 529; `base_ui_tests.php` 1515-1518 | same functions; removed; `dsp_values`; same block | the footer builds `/http/about.php` from the `rest_ctrl` consts like before; the two unused url assignments of the view header (`view.php:255`, `264`) go, because the buttons build their own urls; the component add link of a view (`view.php:634`, `view_exe.php:650`) uses `url_back` with `views::COMPONENT_LINK_ADD_ID`; the link of the word that a word is part of (`word.php:940`) and of a job (`job.php:443`) use `obj_url` with `views::WORD_ID` and `views::JOB_DETAIL_ID`; `html_base::list_sort` and its `edit_url` have no caller (the used one is the class `web/html/list_sort.php`) and go with `url_old`; the three frontend functions without a live caller are removed; `dsp_values_old` is the only version and becomes `dsp_values`, so its html does not change; the two tests of `url_old` become a test that the footer has the about link `/http/about.php` and its negative, the footer of the about page has no about link (`footer(true)`, `html_base.php:954`) |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `test/resources/db/` | — | — | no change: every fixture is created through `where`; a changed fixture means that the check of step 4 is wrong, and the step stops there |
| `cfg/db/sql_creator.php` | `par_value` 5630-5647 | removed | only `where_old` called it; the `par_value` of `sql_db` is another function and stays; `where_start`, `key_match` and `like_keyword` are still called by `where` and `where_expression` |
| `cfg/db/sql_creator.php` | `select_by_id_not_owner` 5136-5159 | — | no change: it adds its parameters with `add_par` and without a where entry, so its statement has no where, before and after step 4; it is reached through `sandbox_multi::load_sql_median_user` by `group` (values and results override it in `sandbox_value`); noted in `docs/llm/pending_prio_2.md` |
| `cfg/db/sql_db.php` | `par_use_link`, `par_named` 665-666; `select_by_field_list` | — | no change: the same names in `sql_db` belong to its own legacy sql text builder; `select_by_field_list` is still used by `result_list.php:390` and `web/view/view.php:941` |
| `cfg/result/result_list.php` | 180, 195, 367; include and use block | same docblocks | the docblocks of `load_by_formula`, `load_by_phrase` and `load_sql_by_formula` call them the replacement of the removed functions; that line goes, because the docs state the target only; the includes and uses are expected to stay, because `formula_fields`, `group_fields`, `result_fields`, `result_db`, `sql_par_type` and `group` are used by the other builders; checked after the removal |
| `cfg/log/change.php` | 51, 63, 69, 71, 72, 73; 80, 91, 97, 98, 99, 101 | removed | the (commented-out) includes and the uses of `component`, `formula`, `value`, `view`, `word` and `change_tables` are only needed by `load_sql_old` |
| `web/view/view.php`, `web/view/view_exe.php` | 253-255, 634, 650 | same lines | the old link carried `word=<id>&add_entry=1` for the script `component_link.php`; check which url vars the view `component_link_add` reads for the view and the word, and use the `url_var` consts of these, because the old raw names are no url vars; `$class` is only used for the removed url of the view edit; if it has no other use in the function, it goes too |
| `web/word/word.php` | 940 | same line | the part-of link uses `phrase_page_url()` of the phrase instead of `obj_url` with `views::WORD_ID`: `$is_part_of` is a phrase and can be a triple, which the word view would not show; it is the url that `name_link` of a phrase uses |
| `web/view/view.php`, `web/system/job.php`, `web/word/word.php`, `web/result/result_list.php` | include and use blocks; `dsp_navbar_bs` 230 | — | `rest_ctrl` is no longer used in `view.php`, `job.php` and `word.php` and `library` not in the web `result_list.php`, so their include and use go; `$lib` of `dsp_navbar_bs` was only used for `$class` and goes with it; `job.php` gets the include and use of `views`; the include of `rest_ctrl` in `view_exe.php` was unused before and stays |
| `web/element/element_group.php` | `dsp_values_old` 371-404 | `dsp_values` in the section "display" | the only version of a used function does not belong to the section "deprecate", which has no other function, so it moves to the display section and gets a full docblock |
| `docs/code_user_message_exceptions.md` | — | — | generated; the two explained local messages of the removed frontend `display_old` functions go |
| `test/php/unit_write/a_selected_test.php` | include and use blocks | — | the switched-on ui tests are in the namespace `unit_ui`, so each gets its `include_once test_paths::UNIT_UI` and its `use` (lesson 1 of `docs/llm/log/get_old_2026-11-10.md`) |
| html snapshots under `test/resources/web/html/` | — | — | no change expected: the about link keeps its url and no other link of `url_old` is rendered in a snapshot; a changed snapshot means that a caller is rendered by a test after all |
| `web/verb/verb_list.php` | 144 | — | no change: it builds the dead links `/http/link_type_edit.php?id=` of `verb_list.html` itself without `url_old`, so it is not part of step 6; noted in `docs/llm/pending_prio_2.md` |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | — | generated; the removed `where_new`, `where_old` and `par_value`, the removed legacy functions and the removed and the renamed frontend functions |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | the unit tests (`test/test_unit.php`) compare every fixture of both dialects, which covers steps 4 and 5, because no test calls a removed function; for step 6 switch on base_ui_tests and the ui tests of the changed renderers (view_ui_tests, word_ui_tests, result_ui_tests, job_ui_tests) and the coding rule check of the html snapshots (`run_html_snapshots`) |
| `docs/llm/pending.md` | steps 4, 5 and 6 | same section | removed once done |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `http_old/value_del.php` | 49, 104 | 104 | found by the review after the run: the search for the callers of `url_old` covered only `src` and `test`, so this retired script (deleted by step 9 of "deprecate http_old") was missed and would call the removed function; it builds the delete url with `url_back(views::VALUE_DEL_ID, $val_id)`, and its now unused use of `rest_ctrl` goes; the back part is left out, because its `$back` is a string and the old call with it failed already on the array parameter; this row was first added to the table above after the run had started and is moved here |
| `docs/llm/coding.md`, `docs/llm/pending.md` | "not part of this plan" of the prompt | the `_old` rule in "Architecture & naming" | asked by the developer after the run: the list of the functions whose "old" means the value before a change or an age moves to a coding rule, which explains how to tell them from a legacy function; `get_old_id` is added to the list, because it is the id version of `get_old`; the done prompt is then removed from `pending.md` as planned |
| `cfg/sandbox/sandbox.php` | `sql_key_fields_id_old` 4762 | removed line | the `// TODO Prio 2 deprecate` above the base `sql_key_fields_id_old` contradicted the new `_old` rule, because the function fills the `old_*` fields of a link change row; removed on request of the developer |
| `docs/code_functions_all.md` | `frm_links_html` of the web `result_list` | same entry | generated; it showed the docblock of the removed `display_old` above it and shows its own now, the same generator effect as lesson 3 of `docs/llm/log/get_old_2026-11-10.md`, this time as a fix |

The run left the fixtures and the html snapshots under `src/test/resources/` unchanged, which confirms the check of
step 4 (the same sql from `where`) and the unchanged html of the replaced `url_old` links of step 6; the generated
docs show the removed functions and 252 instead of 254 local messages; `AUTO_UPDATE_TEST_FILES` is `false` again.
The run output itself was not seen by the llm, only the files it changed.

## Lessons learned by llm

1. A caller search for a function that is removed covers every tracked php folder of the repository (`git grep`),
   not only `src` and `test`: a retired folder like `http_old` stays loadable until its deletion step is done.
2. A check of the code can replace a planned measuring step if it proves the outcome: the log run of step 4 was not
   needed, because `where_old` could only return an empty string; the unchanged fixtures confirmed the reasoning.
3. A replacement of the plan is checked against the type of the object: `obj_url(views::WORD_ID)` of the plan
   would have been wrong for a phrase that is a triple, so the phrase builds its own page url.
4. Every side effect found while coding goes into the tables before the developer starts the run; one found after
   the start goes into "Unexpected changes", even if the tests have not reached it yet.
5. A name is no proof of legacy code: `_old` also means the value before a change, so the docblock and the callers
   decide, and a TODO that asks to deprecate such a function is removed with the rule.

## Lessons learned by human
