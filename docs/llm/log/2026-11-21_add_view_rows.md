# references and triples on the add word view (2026-11-21)

Planned on branch `feature/247-frontend-workflow` as a follow-up of `docs/llm/log/2026-11-20_add_word_suggest.md`.
This log is started before the first edit, so the tables below are the plan; "Unexpected changes" stays empty until
the developer has run the tests.

## Request

From the developer on 2026-11-21: if after pressing the add word button of a search e.g. the page
`http://localhost/http/view.php?m=47&z=1&k=Aarau&fr=frs&u=11` is shown and save is pressed, the collected triples e.g.
"Aarau is a municipality of Switzerland" and the references e.g. "wikidata added: External key Q14274" are shown on
the confirm page. Show the related triples and references also on the add word view and on the confirm page.

### What exists already

- The add word view (`word_add`, `views::WORD_ADD_DETAIL_ID` 47, `messages/system_views.json`) carries the suggested
  reference rows (`url_var::REF_ROW`) and triple rows (`url_var::TRIPLE_ROW`) only as hidden fields of
  `system_form::form_back`, so the user does not see them before the save.
- The confirm page lists both read only (`ui_preview::ref_rows_section` and `triple_rows_section`).
- The word edit view has the component "system form ref rows" (`component_types::FORM_REF_ROWS`,
  `system_form::form_ref_rows`), which shows the stored references as editable rows and one empty add row; a posted
  add row is shown again only at the row number after the stored references.

### Decision of the developer (2026-11-21)

The add word view shows the suggestions as **editable rows**, so that the user can change or remove a suggestion
before the save: the references with the existing component "system form ref rows", the triples with a new
component "system form triple rows"; the confirm page keeps its read only lists.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `messages/system_views.json`, `web/component/execute/system_form.php`, `web/component/execute/ui_list.php` | `word_add` components 1-15; `form_ref_rows` with one add row after the stored references; `refs_of_phrase` reads the page cache for a phrase without references | `word_add` with "system form ref rows" and "system form triple rows" before the button row; `form_ref_rows` shows each posted add row of the url e.g. the suggested wikipedia and wikidata reference as an editable add row and then one empty add row; `refs_of_phrase` returns an empty list for a new phrase | the references: a suggested reference is changed in its fields or removed by clearing its external key (an add row without external key is no change, see `url_var::ref_rows`); the visible rows come after the hidden rows of `form_back`, so their posted value wins; a new word has no stored references, so the page cache is not asked for the phrase without id |
| `db_code_links/component_types.csv`, `shared/types/component_types.php`, `web/component/component_exe.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, `messages/system_views.json`, `web/component/execute/system_form.php` | — | the row `298, 'form triple rows', …, 'system_form_triple_rows'`; `FORM_TRIPLE_ROWS` and `FORM_TRIPLE_ROWS_ID = 298` in the three places; the arm `FORM_TRIPLE_ROWS => $form->form_triple_rows(…)`; the message `FORM_TRIPLE_ROWS` with en and de texts; the component "system form triple rows"; `system_form::form_triple_rows` with the helper `triple_row` | the triples: each suggested triple row of the url e.g. "is a" "city-state" as an editable row with the hidden add action `5a<n>`, the verb selector `5b<n>` and the to phrase as a text field `5pt<n>`, then one empty add row with the verb "is a", so that the user can also add an own triple; a triple is removed by clearing its to phrase (a triple row without to phrase is no change, see `url_var::triple_rows`); only for a word, because only the add of a word writes the triple rows (`frontend::action_crud`) |
| `test/php/unit_ui/word_ui_tests.php`, `test/php/create/test_mappers.php` | section "add word suggestions" | the tests of the rows of the add view, each with its negative; `FORM_TRIPLE_ROWS` with the filled word | the add view shows the suggested reference and triple of the url as editable rows and an empty add row each, the posted form rows of the suggestions are the same rows again, a cleared external key and a cleared to phrase are no change; negative: an object that is no word gets no triple rows |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the html snapshots of the add word view e.g. `views_by_id/word/47_*`, the add word workflows `add_word_wf*` and their write versions | the add view without rows | the label and the empty add row of the references and of the triples | every add word view shows the two components, also without suggestions |
| the html snapshots of the word and triple edit view that are shown again with a posted add row e.g. after the back of a confirm | one add row with the posted values | the posted add row and one more empty add row | `form_ref_rows` keeps the posted add row and offers an empty one after it |
| `test/resources/web/html/object_pages/all_component_types.html`, `types.html`, the component list fixtures `unit/component/list*.csv`, `db/component/component_names*.sql`, `api/type_lists/type_lists.json`, `api/ui_config/ui_config.json` | — | the new component type and component | generated by the test run; never edited by the LLM |
| `docs/code_*.md` | — | — | generated; the new functions and tests |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|

## Lessons learned by llm

## Lessons learned by human
