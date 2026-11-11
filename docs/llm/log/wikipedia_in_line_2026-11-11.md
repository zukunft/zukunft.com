# wikipedia in line (2026-11-11)

The prompt "wikipedia link" of `docs/llm/pending.md`: if the sync with wikipedia does not suggest any update, the
user gets a message like "description and wikidata link are in line with wikipedia page from <time>. No update
needed". The tables were written before the first code edit; the line numbers are those of the start state
(commit `52eb329b1`); the files of the plan are unchanged since commit `8b60ac85d`, which the prompt names.

## Request

The refresh icon of a wikipedia reference calls `frontend::url_with_wikipedia_refresh` (`web/frontend.php:3262`),
which loads the stored phrase and the backend answer with the description and the references taken from wikipedia
(`phrase::wikipedia_confirm_url`, `web/phrase/phrase.php:982`) and builds the confirm page in
`phrase::confirm_url_of_refresh` (`:1028`). It stays on the page of the phrase only if wikipedia names no wikidata
item, else it always returns the confirm page, so a refresh without any difference shows a confirm page without a
change. The time of the page is the `timestamp` of the summary json (the time of the last revision of the page,
e.g. `2026-11-07T14:48:15Z` in `import/wikipedia/cache/summary_Geneva.json`), which the backend reads in
`ref_link_wikipedia::take_over` (`cfg/ref/ref_link_wikipedia.php:200-209`) but does not pass on.

The target: the backend passes the time of the page with the refresh answer, and a refresh whose description and
wikidata id are both equal to the stored ones stays on the page of the phrase with the info message and the time.
Open questions for the developer before the code of the message: the format of the time (the frontend config
gives `d-m-Y H:i`, the prompt names year-mm-dd hh-mm) and whether the UTC time of wikipedia is shown as UTC or in
the time zone of the pod.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/ref/ref_link_wikipedia.php`, `cfg/ref/ref.php`, `web/ref/ref.php`, `shared/json_fields.php`, `test/php/unit/wikipedia_tests.php`, the ref unit tests | the summary fields 70-75, `take_over` 200-209; `api_json_array` 490-495; 138, 301 | the const `WP_TIMESTAMP`, a public `page_time(array $summary): ?DateTime` set by `take_over` on the wikipedia reference; a not stored reference field e.g. `page_time` with `json_fields::PAGE_TIME`, written only if set and read by the frontend | the time of the wikipedia page reaches the frontend with the refresh answer; `ref::last_update` (`cfg/ref/ref.php:203`) stays the time of the last refresh by zukunft.com, which triggers the next refresh job; the tests: `page_time` of the frozen Geneva summary is `2026-11-07 14:48:15` UTC and a summary without `timestamp` gives null; the api json round trip of a reference with and without the page time |
| `web/phrase/phrase.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, `web/user/user_message.php`, `test/php/unit_ui/reference_ui_tests.php`, `test/php/unit_workflow/ref_url_tests.php` | `confirm_url_of_refresh` 1028; 240-244; 455-459; `add_warning_with_vars` 223; 279-282; `wikipedia_refresh_workflow` 399 | a named check e.g. `is_in_line_with_wikipedia($old, $new)` and the page of the phrase with the info message; `msg_id::WIKIPEDIA_IN_LINE` with `msg_id::VAR_TIME` and the en and de texts; `add_info_with_vars`; the tests | a refresh without any difference shows the page of the phrase with the message instead of an empty confirm page; the time in the format of the frontend config (`$ui_sys->cfg->date_time_format()`, `web/helper/config.php:155`) unless the developer decides otherwise, without a page time the message names no time; the tests: the case "an unchanged wikidata id is not confirmed again" (the stored phrase as both sides) gives the page of the phrase with the message, its negatives: only the description differs gives the confirm page with the description, only the wikidata id differs the confirm page with the reference row; the write workflow `wikipedia_refresh` (wf42) gets a second refresh after the confirmed step, which shows the message |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `test/resources/web/html/workflow_write/wikipedia_refresh_wf42/` | — | regenerated, new | the second refresh adds a snapshot with the message; reviewed by the developer |
| `test/resources/api/` messages with a reference | — | — | no change: the page time is written to the api json only if it is set, which only the refresh does |
| `web/frontend.php` | `url_with_wikipedia_refresh` 3262 | — | no change: it shows whatever url `wikipedia_confirm_url` returns, the page of the phrase with the message included |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new functions and the new message |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switched on: `wikipedia_tests`, `ref_tests`, `reference_ui_tests`, `ref_write_url_tests` (the live wikipedia page) and `coding_rule_tests` (the new message and its translations) |
| `docs/llm/pending.md` | the prompt "wikipedia link" | — | removed once done |

No import and no id changes, so a `test/test_part.php` run is enough, then the full `test/test.php` before the
commit.

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|

Written after the unexpected changes are known.

## Lessons learned by llm

Written after the unexpected changes are known.

## Lessons learned by human
