# the look of the portfolio split page: the diagram scale and the button icons (2026-11-26)

Part of #247, the phase 1 task "the look" of `docs/llm/pending.md` (portfolio split page compared with its sample).

## 1. the font of the diagram scale and the icons of the buttons

### Request

"do the next open task in pending.md and remove the task done already": csv change 2 waits for the run of the
follow-up `csv name`, and part 1 of step 9 needs a run of the developer, so the next open task is "the look" with three
points: step 5 (the colours of the walk cells and the hint), the font of the diagram scale (decision 3 of
2026-11-25) and the icons of step 7.

Checked before the first edit:
- step 5 is done already: since 0719c97c4 (2026-11-01) the walk colours its child columns `col-1`, `col-2` and its
  parent column `col-first` with the colours of the sample (`#385eb7`, `#6b86c2`, `#274b8f`), signs the deviation
  with `pos` and `neg`, and shows the hint below the holdings as `p.hint` in the grey of the sample; so the point is
  removed from `docs/llm/pending.md` without a change
- the sample draws the three numbers of the scale in `IBM Plex Mono, monospace` and the first and the last position
  below the diagram in the normal font; both diagram renderers (`html_base::line_diagram` and `lineDiagram` of
  `calculator_json.js`) write the three scale texts as the first three `text` elements of the svg
- the sample has an icon in front of five button texts: `fas fa-plus` for "Add child", `fas fa-file-import` for
  "Import" and "Import FX rates", `fas fa-download` for "Download" and `fas fa-file-pdf` for "Report"; the icon
  belongs to the kind of the button (the keywords `calculator repeat`, `calculator file import`, `calculator
  download` and `calculator printable report`), so every calculator page gets it, and the copy buttons stay without

Planned:
- the scale: one css rule `.use-case .chart text:nth-of-type(-n+3)` with a monospace font, like the existing rule
  `.use-case .chart line:nth-of-type(2)` of the zero line, so no renderer changes
- the icons: four consts in `web/const/icons.php`; in `ui_list.php` a helper for a button with an icon in front of
  its text, used by `calculator_buttons`, `calculator_imports` and `calculator_repeat`, and the report link with its
  icon by a new parameter of `html_base::ref_to_frame`, which escapes the text; the same in `calculator_json.js`
  (`buttons`, `importSection`, `repeatFields`) with the icon consts "the same as icons"
- not in this change: the tooltips of the sample on "Import", "Download" and "Report", because they are texts of the
  use case and not of the look

#### Follow-up request of 2026-11-26 (before the first run)

"check if there is anything more that can be fixed" of the portfolio split page before `test/reset_db.php` and the
re-pin. All case reports are at 0 differences, except `_remove_child.md`, whose fix waits for the run. Open
otherwise: part 1 of step 9 and csv change 2 need a run first, change 6 of step 3, the holdings fields and the
button functions are tasks of their own. What the page diff does not compare: the sample has three tooltips, which
the generated page lacks:
- "Import the portfolio of the Spot rates" on "Import"
- "Export the portfolio splitting as a spreadsheet" on "Download"
- "Show the Portfolio Split as a printable PDF" on "Report"

Decision of the developer (2026-11-26): add them now, so that their json change goes into the same reset.

Planned:
- in `portfolio_split_calculator.json`, three triples from the button triples to `calculator hint` with the texts
  of the sample, appended at the end of the triples, like the tooltip of a preset button
  (`title="' + esc(hint(item))` in `calculator_json.js`)
- `calculator::button_hint` next to `button_label`
- an optional title on `html_base::button` and a title on `ref_to_frame`, and in `ui_list` a helper for the button
  of a keyword with its label, its icon and its hint
- the same in `calculator_json.js`
- the button fixture gets a hint for the download button (new id `CALC_BUTTON_HINT_ID` 1975, between the ranges of
  the case and the check hint fixtures, which are free)

Findings 3, 5 and 6 of the review of 2026-11-26, still before the first run:
- the negative tooltip test checked `calculator_imports`, which never reads a hint, so it becomes a direct test of
  `calculator::button_hint` with its hint and without
- `html_base::title_attr` is a private helper: it moves to the private helpers behind the public functions, and
  `details` and `definition` use it too instead of their own copies of the same attribute code
- `object_pages/buttons.html` gets a button and a report link with the icon and the tooltip, so that the new html of
  `button`, `ref_to_frame` and `icon_and_text` is in an object page snapshot

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/style/style_html.css` (`.use-case .chart text`) | the scale in the font of the page | the three scale texts in a monospace font | decision 3 of 2026-11-25 |
| `src/main/php/web/const/icons.php`, `src/main/php/web/component/execute/ui_list.php` (`calculator_buttons`, `calculator_imports`, `calculator_repeat`), `src/main/php/web/html/html_base.php` (`ref_to_frame`, new `icon_and_text`), `src/main/resources/script/calculator_json.js` (`buttons`, `importSection`, `repeatFields`, new `iconButton` and `iconText`), `src/test/php/unit_ui/base_ui_tests.php`, `src/test/php/unit_ui/use_case_ui_tests.php` | the button texts without an icon | the icon of the sample in front of the text of the add, import, download and report buttons, on the page without and with javascript; the tests of the download, the import and the add button expect the icon, and a new test of `ref_to_frame` with its icon and its escaped text; follow-up: the tooltips by `calculator hint` (`portfolio_split_calculator.json`, `calculator::button_hint`, the title of `html_base::button` and `ref_to_frame`, `ui_list::calculator_keyword_button`, `buttonHint` of `calculator_json.js`, the hint of the button fixture with `triple_names::CALC_BUTTON_HINT_ID` and its tests; review: the direct test of `button_hint`, `details` and `definition` with `title_attr`, the samples on `object_pages/buttons.html` in `base_ui_tests::buttons_page`) | step 7, the icons and the tooltips of the sample |
| `docs/llm/pending.md` | the look with three points | step 5 removed as done, the look done and waiting for the run | the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_no_js.html` (and by the follow-up its seed with the three hint triples and the `title` of "Import", "Download" and "Report", also in `src/test/resources/api/phrase_data/` if a run writes it) and the no-js snapshots of the other calculator pages with such a button (`pv_lifetime_balance_no_js.html`, `npp_ch_vote.html`), `calculator_editor.html` and the other editor snapshots that render a calculator page | — | — | the `<i>` of the icon in front of the button text; the js snapshots change only by the script, which they link |
| `src/test/resources/web/html/object_pages/buttons.html` | — | the calculator button and the report link with icon and tooltip | the samples of the review finding 6 |
| the snapshots with a `details` or a `definition` that has a title | — | unchanged | both write the same attribute by `title_attr` |
| the case reports `portfolio_split_calculator_diff*.md` and the other `_diff*.md` | 0 differences | 0 differences | `html_page_diff` compares the texts and an icon has none |
| the ids of `portfolio_split_calculator.json` and of the later files | — | — | the three hint triples of the follow-up are appended at the end, but they link a triple, so the importer may write them between the triples of the file (lesson of section 6 of `docs/llm/log/2026-11-25_csv_child_shares.md`), and every triple of a later file shifts by three; `test/test_re_pin.php` re-pins them |
| the ids of the other files | — | — | none by this change, because no other import file changes; but the next run also has the uncommitted follow-up `csv name` of `docs/llm/log/2026-11-26_csv_format_phrases.md`, which shifts the triple ids after the system files by one, so `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php` |

### Unexpected changes

The run of 2026-11-26 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`) covered this change
together with the follow-ups of section 7 of `docs/llm/log/2026-11-25_csv_child_shares.md` and of
`docs/llm/log/2026-11-26_csv_format_phrases.md`, and with `files::AUTO_UPDATE_TEST_FILES` set to `true`. So the
snapshots were overwritten instead of failing, and this table compares the regenerated files with the plan; whether
the new tests passed is in the output of the run, not in a file. The font of the scale is in the css only, which no
snapshot holds, so it shows only in a browser.

As planned:
- `portfolio_split_calculator_no_js.html` has the icon in front of "Add child", "Import", "Download", "Report" and
  "Import FX rates", and the three tooltips on "Import", "Download" and "Report"; "Import FX rates" has none.
- The seed of `portfolio_split_calculator_js.html` has the three hint triples; the importer wrote them as 4811 to
  4813 between `portfolio split assumptions import label` and `portfolio split row target share label`, so the
  later triples of the file and of the later files shift by three, plus one by `csv name`.
- `object_pages/buttons.html` has the calculator button and the report link with icon and tooltip.
- The snapshots with a `details` or a `definition` changed by the ids only.
- The case reports have no new difference; `_remove_child.md` is at 0 by section 7 of the csv child shares log.
- Nothing was written to `src/test/resources/api/phrase_data/`.

| file | old | new | reason |
|---|---|---|---|
| `pv_lifetime_balance_no_js.html`, `npp_ch_vote.html`, `calculator_editor.html` and the other editor snapshots | the `<i>` of the icon in front of the button text | the ids only; the editors also the new text of section 7 of the csv child shares log | these pages have no add, import, download or report button that the server draws: the two calculator pages have none, and the editors draw the calculator page by the script |
| `docs/code_test_coverage.md` | — | `button` and `ref_to_frame` leave the list of the functions without a test; `icon_and_text` is new in it with 0 unit test calls | the report counts only the calls of a test block from `$test_name = ...` to its last assert; `button_hint` and the two others have such tests, but `icon_and_text` is called directly only in `base_ui_tests::buttons_page`, which builds a snapshot page without an assert |
| `docs/code_functions_all.md` | — | `button_hint` and `icon_and_text` with "section ... not yet defined" (texts and flags, header & footer) and their `@param` lines | the generated report lists every new public function with its section check |

### Lessons learned by llm (2026-11-26)

- Before listing a snapshot that gets the html of a changed element, check that the page has the element: two of
  the three pages of the plan have no such button.
- A function used only to build a snapshot page counts as untested in `docs/code_test_coverage.md`, because only an
  asserted test block counts; a new public function needs a test with an assert, and it is a side effect in the
  generated reports.
