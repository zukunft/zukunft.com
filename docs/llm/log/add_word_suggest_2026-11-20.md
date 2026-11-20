# add word suggestions (2026-11-20)

Planned on branch `feature/247-frontend-workflow` for the two entries "add word" and "conceptnet.io link" of the
section "bug fixing" in `docs/llm/pending.md`. This log is started before the first edit, so the tables below are the
plan; "Unexpected changes" stays empty until the developer has run the tests.

## Request

From `docs/llm/pending.md`, "bug fixing":

- **add word:** when adding a new word, conceptnet should be used to suggest triples, and wikipedia should be used to
  suggest a description and a wikidata link.
- **conceptnet.io link:** get the phrases of 'is a type of' from conceptnet.io and use them to create a triple; use
  the add of Hamburg as a sample.

The same idea is in `pending.md` "improve the new word / triple process": check if the new word exists in wikipedia,
wikidata or conceptnet.io and, if yes, suggest to include the references, the description and an 'is a' triple that
defines the group or parent of the new word.

### What exists already

- `cfg/ref/ref_link_wikipedia.php`: `summary` reads the summary json of a wikipedia page, `description` takes the
  first paragraph without the pronunciation and `wikidata_id` the item e.g. `Q1055` for Hamburg. `refresh_from_url`
  needs a stored wikipedia reference, so it cannot be used for a new word, which has none yet.
- `web/phrase/phrase.php::confirm_url_of_refresh` builds one confirm page with the description and the wikidata
  reference as a reference row (`url_var::REF_ROW`, see `docs/llm/log/ref_row_confirm_2026-11-07.md`), and
  `web/frontend.php::url_with_wikipedia_refresh` starts it from the refresh icon of an existing phrase.
- `shared/types/ref_types.php::CONCEPTNET` is a reference type, but nothing reads conceptnet.io yet.
- `shared/types/verbs.php::IS_NAME` is the verb "is a" of the suggested triples.
- The saved wikipedia answers of the tests are in `test/resources/import/wikipedia/cache/`
  (`files::IMPORT_WIKIPEDIA_SUMMARY_*`, `files::IMPORT_WIKIPEDIA_LIVE_*`).

- `web/component/execute/system_page.php::add_word_button` is the add word button of a search without a result,
  e.g. `http://localhost/http/view.php?m=107&u=11&pattern=Hamburg`: it opens the add word view (`word_add`) with the
  search pattern as the name.

### Decisions of the developer (2026-11-20)

1. **When the suggestions are read:** by a button: the add word button of the search page reads the 'is a'
   concepts from conceptnet and the description from wikipedia by the existing wikipedia link, and the add word view
   is shown with the fields already filled, so that the user can adjust them and confirm; no live request without
   the click.
2. **Which 'is a' targets are suggested:** also new words, e.g. a target that is no word of the pod yet is added
   together with the triple.
3. **How a suggested triple is confirmed:** as a triple row that is confirmed in the confirm view of the word add,
   like the reference rows.
4. **Language:** english only (`/c/en/` of conceptnet, the english wikipedia of `ref_link_wikipedia::SUMMARY_URL`).
5. **conceptnet without answer:** the conceptnet api answered with a http 502 on 2026-11-20 (only the html website
   worked); decided: implement the documented api anyway, so that the user gets an info while it does not answer and
   the word can still be added with the wikipedia suggestions.
6. **Fallback of the 'is a' triples:** decided on 2026-11-20: if conceptnet suggests no group, the "instance of" (P31)
   items of the wikidata item that the wikipedia page names are suggested, e.g. "city-state", "port city" and "big
   city" for Hamburg (Q1055); not the categories of the wikipedia page, because they are plural sets with qualifiers
   e.g. "Port cities and towns in Germany", which no text rule turns into a correct singular for every category.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/ref/ref_link_conceptnet.php` (new), `cfg/import/import_wikidata.php` | — | `is_a_names`, `concept_key`, `names_of_answer`; `WD_INSTANCE_OF`, `instance_of_names`, `instance_of_ids` | conceptnet.io link: read the 'is a' edges of a name from the conceptnet api (`/query?start=/c/en/<name>&rel=/r/IsA`) through `import::without_note_timer` like `ref_link_wikipedia::summary`, with a generic user agent, and return the names of the english targets e.g. "city" and "port" for Hamburg; a failed request is a message, never a php warning; without a group of conceptnet the labels of the "instance of" items of the wikidata item of the wikipedia page (decision 6), read with the existing wikidata requests (one for the item and one per group, each label read once) |
| `web/component/execute/system_page.php`, `web/frontend.php`, `web/word/word.php`, `web/html/rest_call.php`, `api/word/index.php`, `cfg/ref/ref_link_wikipedia.php`, `cfg/word/word.php` | `add_word_button`, `url_with_wikipedia_refresh`, `api_call_name`, the load by name of the word api, `api_json_array` | the suggest request `url_var::REFRESH_SUGGEST`, `url_with_word_suggestions`, `suggestion_url`, `url_with_suggestions`, `suggest_from_url`, `suggestions_array` | add word: the add word button asks for the suggestions; the backend word api fills a word that does not exist with the wikipedia description and the wikipedia, wikidata and conceptnet references (`take_over` like the refresh) and the 'is a' names (`json_fields::SUGGESTED_PARENTS`); the frontend opens the add view with the name and the description and with the references as reference rows (`url_var::REF_ROW`), and tells the user which source has suggested nothing; decided on 2026-11-20: each step is shown to the user, e.g. "get data from wikipedia 'https://en.wikipedia.org/wiki/Hamburg'", "found description '...'", "found wikidata entry 'https://www.wikidata.org/wiki/Q1055'", the conceptnet and the wikidata request with the groups found or the reason why none; the backend sends the translated steps as `json_fields::SUGGEST_STEPS` (`word::suggest_steps`, `ref_link_wikipedia::add_step`), because an ok api answer sends no info, and the frontend shows them as one info with a line per step (`word::add_suggest_steps`), because the page shows one notification; the steps are shown on the filled add view, not live during the requests, because the frontend sends no javascript |
| `shared/url_var.php`, `web/component/execute/ui_preview.php`, `web/component/execute/system_form.php`, `web/frontend.php`, `web/phrase/phrase.php` | `split_ref_row`, `ref_row_url_array`, `without_ref_row`, `ref_row_fields`, `link_section`, `form_back`, `action_crud`, `save_ref_rows` | `TRIPLE_ROW = '5'`, `TRIPLE_ROW_VARS`, `triple_rows`, `triple_rows_section`, `save_triple_rows` | add word: each 'is a' concept is a numbered triple row of the add word url, e.g. "Hamburg" "is a" "city", which the add view carries as hidden fields, the confirm view lists and the save writes after the word together with the reference rows; a target that is no phrase yet is added as a new word; a retry of open rows of a new word goes to its edit view, so that the word is never added twice |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `shared/enum/messages.php`, `resources/translations/en.yaml`, `de.yaml` | — | `CONCEPTNET_READ_FAILED`, `SUGGEST_WIKIPEDIA_MISSING`, `SUGGEST_PARENTS_MISSING`, `INFO_CONFIRM_TRIPLES`, `SUGGEST_READ_WIKIPEDIA`, `SUGGEST_READ_CONCEPTNET`, `SUGGEST_READ_WIKIDATA`, `SUGGEST_FOUND_DESCRIPTION`, `SUGGEST_FOUND_WIKIDATA`, `SUGGEST_FOUND_PARENTS`, `SUGGEST_NO_PARENTS` | the failed conceptnet request, the infos that wikipedia has suggested nothing or neither conceptnet nor wikidata a group, the title of the triple rows on the confirm page and the steps of the suggestions; also `ref_link_wikipedia::PAGE_URL`, `import_wikidata::ITEM_URL` and `ref_link_conceptnet::is_a_url` for the urls of the steps |
| `test/php/unit/wikidata_tests.php` | — | the "instance of" test after the property ids | the ids of the "instance of" claims without the deprecated one, and negative: an entity without such a claim names no group |
| `shared/url_var.php`, `web/helper/url_mapper.php`, `shared/json_fields.php` | `HUMAN_TO_STD`, `HUMAN_TO_STD_REFRESH_VAL`, `human_url_to_json` | `TRIPLE_ROW_HUMAN`, `REFRESH_SUGGEST_HUMAN`, `URL_PART_TRIPLE_ROW` | every new url var has a human format, and the human url keeps the row number like for the reference rows |
| `web/phrase/phrase.php`, `web/frontend.php` | `save_ref_rows` only for an update | also for the create of a word | the reference rows have been written only for a phrase with an id so far; for an add they are written after the word has its id, which only the new workflow covers |
| the html snapshots of a search without a result | the add word button | changed once | the button url carries the suggest request `fr=frs`; in a test render (no backend call) the button still opens the confirm page with only the name |
| `test/php/unit_ui/word_ui_tests.php`, `test/php/unit/ref_tests.php`, `test/php/const/word_names.php` | — | the sections "add word suggestions" and "conceptnet is a", `word_names::HAMBURG` | unit tests of the triple rows, of the filled add view url with and without suggestions, of the shown steps (a given step is shown, and negative: without suggestions the two lines of what has not been found) and of the conceptnet answer, which is given in the test, because the api answered with a http 502 on 2026-11-20 |
| `test/php/unit_write_workflow/word_write_url_tests.php` | — | `add_word_with_suggestions`, `is_phrase_name`, `cleanup_hamburg` | the workflow from the add word button of the search of Hamburg to the filled add view with the shown steps (starting with the wikipedia page of Hamburg) and the confirm that writes the word, its references and triples, checked by the result and not by snapshots, because the suggestions are read live; while conceptnet does not answer, the triples come from the "instance of" items of wikidata, and only if neither names a group, it checks the info instead of the triples |
| `docs/code_*.md` | — | — | generated; the new functions and tests |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `web/word/word.php` | the includes | `include_once html_paths::HTML . 'rest_call.php'` | found by the coding rule test "includes missing in rest_call" of the run of 2026-11-20: the `use` of `rest_call` for `suggestion_url` had no include |
| `cfg/ref/ref_link_wikipedia.php`, `web/phrase/phrase.php` | `$wiki_msg`, `$cpn_msg`, `$wd_msg` with the reason above the line; `phrase_of_row` with an unnamed message | the reason on the same line; `$wd_msg` removed; `$find_msg` | found by the check of `docs/code_user_message_exceptions.md` of the same run: a local message below the entry points needs its reason on the same line; the wikidata message is not needed, because the live read adds a message only in an offline test; the generated file is written again by the next run |
| `web/word/word.php`, `shared/enum/messages.php`, `resources/translations/en.yaml`, `de.yaml`, `test/php/unit_ui/word_ui_tests.php` | `add_suggest_steps`: the infos that wikipedia and the groups are missing for every answer | `SUGGEST_NO_ANSWER` for an answer without steps | found by the Hamburg workflow of the same run ("... and shows the user the steps, starting with the wikipedia page" and the two following checks): the add view opened, but the backend answer had no steps, no description and no references, so the backend has not read any source; the cause is still open: either the web server serves older backend code than the checkout or the suggest step stopped with a fatal error, which the raw api answer (`curl "http://localhost/api/word/?k=Hamburg&fr=frs"`) or the web server error log can tell; until then the user was told that no wikipedia page can be read, although wikipedia has never been asked, so an answer without steps now says that the backend has sent no suggestions; the unit test of the case without suggestions expects this text, and a new negative test checks the two lines of what has not been found after a step |
| `shared/url_var.php`, `shared/types/ref_types.php`, `cfg/ref/ref_link_wikipedia.php`, `cfg/ref/ref_link_conceptnet.php`, `cfg/import/import_wikidata.php`, `web/word/word.php`, `web/html/rest_call.php`, `test/php/unit_ui/word_ui_tests.php`, `test/php/unit_write_workflow/word_write_url_tests.php` | one backend request `REFRESH_SUGGEST` for all sources with the read steps; the steps on the add view (`add_suggest_steps`); `ref_link_wikipedia::PAGE_URL`, `import_wikidata::ITEM_URL`; `send_loading_step` | `REFRESH_SUGGEST_WIKIPEDIA`, `REFRESH_SUGGEST_CONCEPTNET`, `REFRESH_SUGGEST_WIKIDATA`, `REFRESH_SUGGEST_SOURCES`; `suggest_from_wikidata`; `suggestion_of`, `step_text`, `add_suggested_refs`, `suggested_wikidata_id`, `add_missing_suggestions`; `ref_types::WIKIPEDIA_PAGE_URL`, `ref_types::WIKIDATA_ITEM_URL`; `rest_call::send_loading_text` | requested by the developer on 2026-11-20: the steps of the data collection are shown below the loading text "Collecting data … since x sec" while the data is collected, and not on the add view with the save button; one backend request could show its steps only after its end, so the frontend now asks each source in its own request and shows "get data from …" before and the result steps of the answer after each request (`rest_call::send_loading_text`, which `send_loading_step` uses too); wikidata is asked only if conceptnet knows no group, with the wikidata id of the wikipedia answer; the page urls of wikipedia and wikidata moved to the shared `ref_types`, because the frontend names them in its steps; the add view keeps only what has not been found, because that explains an empty field and the loading text is not shown on a fast page; `ref_link_conceptnet::is_a_url` is private now; the unit tests expect no info with all suggestions and only the lines of what has not been found without them, and the workflow checks, negative, that the add view shows no step of the collection |
| — (no file changed) | — | — | the cause of the answer without steps of the row `SUGGEST_NO_ANSWER` above, found with the run of 2026-11-20 that showed the new text "The backend has sent no suggestions for Hamburg": the test frontend runs from the checkout, but its api calls go via http to the web server, which serves the rsync copy of the repo in `/var/www/html` (`docs/deployment.md`), so the new backend code of the suggestions (`api/word/index.php`, `ref_link_wikipedia`, `ref_link_conceptnet`, the new url vars and json fields) is not served until the developer has updated the copy with `rsync -av --delete <checkout>/ /var/www/html`; no code change, because deploying is no LLM task; the earlier backend changes of this branch were not affected, because their tests call the backend in the same process; the same run reported a timeout of 6 seconds for the page after the save of Hamburg, almost all of it in `calc_sheet`, which is checked again after the update of the served copy |
| `test/php/unit_write_workflow/word_write_url_tests.php`, `test/php/utils/test_base.php` | `add_word_with_suggestions` with `$this->usr` and `$this->msg`; `cleanup_hamburg` loads Hamburg and its triples as usr1 and deletes each triple as usr1; the owner delete inside `write_named_cleanup_force` | a usr1 `user_ui` for the suggestion message and the confirm request; `$t->cleanup_last_used()` in the cleanup of the word workflow; `cleanup_hamburg` loads as the system user and deletes each triple with the new public `test_base::del_by_owner`, which `write_named_cleanup_force` uses too; `is_test_user_word` adds a parent word of a test user to the removed parent words | found by two errors of the runs of 2026-11-20: "... but not of another user" of the triple workflow (actual 2 = `verbs::IS_ID`) and "the write tests have not left the words as they were" (Hamburg, "federated state of Germany", "big city" and "Hanseatic city" owned by user 4, the test partner); `word_login_workflow` starts with `wf_start` for usr2, which sets `$this->usr` and `$this->msg->usr` for all following tests, so the Hamburg add has written the word and its "is a" triples for usr2 and with that set "is a" as the last used verb of usr2; the delete of a usr2 triple by usr1 only writes an exclusion for usr1 (the `added "1"` log rows), so the triples kept Hamburg and its parent words in use and every delete of the words was refused; a parent word owned by a test user is a leftover of an earlier run, because the parent words of the base data are owned by the system user, so the cleanup at the start of the next run removes the four words left now |
| — (no file changed) | — | — | found by "the write tests have not left the triples as they were" of the same run of 2026-11-20: the six "is a" triples from Hamburg to its parent words e.g. "Hamburg (big city)", owned by user 4, the test partner, and deleted by the system user, which has only written an exclusion (`added "1"`); the same cause as the row above, so the triples are removed by the owner delete of `cleanup_hamburg` (`test_base::del_by_owner`) at the start of the next run, before the words that they keep in use |
| `test/php/unit_read/phrase_list_read_tests.php` | `uses_test_word` skips the triples of `word_names::TEST_WORDS` | also the triples from or to `word_names::HAMBURG` | found by "... and are the saved fixed list" (`list_is_a.csv`) of the run of 2026-11-20: a Hamburg triple of an earlier write run, "Hamburg (urban …" with the id 8647, was read as seed data, because Hamburg is written only by the add word workflow and is no test word; Hamburg is not added to `TEST_WORDS`, because these are also the reserved test names, which would block the add of Hamburg by a real user; the id 8647 is newer than the six left triples 8638 to 8643, so if the run that wrote it has already used the owner delete of `cleanup_hamburg`, another leak is still open |

## Lessons learned by llm

- A frontend change that reads new backend data via `rest_call` needs the updated served copy (`/var/www/html`,
  `docs/deployment.md`) before the test run, unlike a backend change that a test calls in the same process; name this
  in the final report of such a change, so that an answer without the new data is not taken for a code error.

## Lessons learned by human
