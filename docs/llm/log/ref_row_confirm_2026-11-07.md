# ref row confirm (2026-11-07)

Commit `fbf6fa381` (2026-11-08), part of #247. This log was written after the commit: the request and the
expected changes are reconstructed from the commit message, the diff and the changes of
`docs/llm/pending_prio_2.md` (item 6 of the wikipedia refresh section), because no table of expected changes
was written before the change started.

## Request

The wikipedia refresh offers one change per step (`phrase::confirm_url_of_refresh`): a phrase without
description gets the description and the wikidata id with one confirm, a phrase with an own description first
gets the missing wikidata reference by the filled reference add page, then the wikipedia description. Offer all
of it in one step: one confirm page that shows the wikipedia description and the added or changed wikidata
reference, and write both with the same confirm button. The references travel as numbered rows of the confirm
url, so that the same mechanism can later carry the add, the change and the delete of any reference of a phrase.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `shared/url_var.php` | 90-95, 417-435 | 90-98, 420-455 | new prefix `REF_ROW = '6'`, `REF_ROW_VARS`, `REF_ROW_ACTIONS`, `ROW_DIGITS`; `EXTERNAL_KEY` leaves `LINK_VARS` |
| `shared/url_var.php` | after `split_prefix` (1083) | `split_ref_row` 1127 … `ref_row_fields` 1203-1215 | split, read, build and remove the numbered reference rows of a url |
| `shared/url_var.php` | 496, 786 | 523, 814 | human url twin `REF_ROW_HUMAN = 'ref_change'` and its `HUMAN_TO_STD` row |
| `web/phrase/phrase.php` | `confirm_url_of_refresh` 1010 … `ref_update_url_of_refresh` 1077 | `url_of_page` 1005 … `wikidata_row_url` 1076 | one confirm url with the description and the wikidata row instead of three separate steps |
| `web/phrase/phrase.php` | `ref_of_refresh` 1114 … `save_ref_of_refresh` 1157 | `save_ref_rows` 1118 … `stored_ref_of_row` 1222 | write every row (add, change, delete) and return the url with the rows that failed |
| `web/component/execute/ui_preview.php` | `link_section` 341-355 | 348-424 | `ref_rows_section` and `ref_row_changes`: a table with the field changes per reference row |
| `web/component/execute/ui_preview.php` | `popup_changes` 269-290, `changed_fields` 426, `popup_impact` 826 | 274-296, 499, 897-899 | skip the '6' keys in the hidden fields and in the changed fields; a confirm with rows only is a change |
| `web/component/execute/system_form.php` | `form_back` 566-574 | 566-577 | carry the rows as hidden fields to the confirm |
| `web/frontend.php` | `url_object_values` 2808-2818, `action_crud` 2880-2896 | 2809-2820, 2882-2904 | the rows are no object values; save the rows after the phrase and show the failed rows again |
| `web/helper/url_mapper.php` | `human_url_to_json` 171, `map_standard_to` 221-230 | 171-182, 229-238 | the human url keeps the row number |
| `web/ref/ref.php`, `web/ref/ref_list.php` | `link_preview` 610-619, `get_by_type_code_id` 94-104 | removed, `get_by_type_id` 104-115 | the old one-line preview is replaced by the row table; a lookup by type id for the create row check |
| `shared/enum/messages.php` + `en.yaml` / `de.yaml` | 243 | 244-248 | `WIKIPEDIA_NO_WIKIDATA`, `INFO_CONFIRM_REFS`, `REF_ROW_OF_OTHER_PHRASE`, `REF_ROW_TYPE_USED` |
| `shared/json_fields.php` | 61 | 62-63 | `URL_PART_REF_ROW` for the json of the human url |
| `unit_workflow/ref_url_tests.php`, `unit_write_workflow/ref_write_url_tests.php`, `const/workflows.php` | after `cleanup_wikipedia_refresh` 460 | 516-976 | the workflows `ref_rows` (wf43), `ref_row_confirm` (wf44) and `wikidata_refresh` (wf45) |
| `unit_ui/reference_ui_tests.php` | 256-319 | 258-369 | unit tests of the refresh confirm and of the reference rows section |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `resources/web/html/workflow/ref_row_confirm_wf44/` and `workflow_write/ref_row_confirm_wf44/` | — | 24 + 24 new files | one snapshot and one `_url.txt` per case (add, change, delete, each with and without description) and step |
| `resources/web/html/workflow_write/wikidata_refresh_wf45/` | — | 2 new files | the snapshot of the new workflow |
| `resources/web/html/views_by_object/triple/triple_default_triple_142.html` | — | new file | the page of the triple that the row workflows change |
| `resources/import/wikipedia/refresh_*.json` | — | 2 new files | the wikipedia answers of the pages that the workflows compare |
| `test/php/create/test_refs.php` | after `ref_list_math_without_wikidata_ui` 315 | 318-333 | factory `ref_list_geneva_ui` for the unit test |
| `shared/const/refs.php` | 68-100 | 68-113 | ids and keys of the seed references the workflows use, `SYSTEM_TEST_ROW_ADD` as reserved key |
| `shared/types/ref_types.php` | 43 | 44-45 | `CONCEPTNET` for a create row of a type the phrase does not have yet |
| `test/php/utils/test_base.php` | `cleanup_leftover_refs` 4856-4885 | 4856-4925 | `cleanup_ref_rows_of` and `cleanup_test_ref` to remove the references the workflows add |
| `docs/code_*.md` | — | — | generated overviews follow the new and removed functions |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `cfg/ref/ref.php` | `api_mapper` 302 | 302-304 | `set_obj_from_id` instead of `set_id`: `phrase::set_id` keeps only the absolute id, so the reference of a triple was linked to the word with the same id |
| `cfg/word/triple.php` | `phrase_from_api_json` 1015 | 1015-1016 | the same sign bug for the from and the to of a triple |
| `cfg/formula/formula_link.php` | `set_phrase_from_id` 635-636 | 635-637 | the same sign bug for the phrase of a formula link |
| `cfg/ref/ref.php` | `save` 1473 | 1484-1488 | a reference built from the api json has no owner, so `can_change` let any user change the standard row; the owner of the standard row is taken over |
| `cfg/ref/ref.php`, `cfg/sandbox/sandbox.php` | `to_field` 1102, `sql_insert_switch` 4115-4117, `sql_insert_with_log` 4270-4274 | `link_key_fields` 1105-1113 and 1078-1087, 4125-4127, 4280-4287 | the external key is the to of a reference, but a user may overwrite it, so the user overlay row must keep it; the hard coded triple exception became an overridable `link_key_fields` |
| `cfg/ref/ref_link_wikipedia.php` | `description` 121, `summary` 164-170 | 121-123, 166-173 | the extract of the page Health starts with a line break; `summary` public for the write test that compares the live page |
| `cfg/import/import_wikidata.php` | 69, `request_context` 132-136, `request_json` 150 | 69-73, 136-140, 154 | an own 60 s timeout for the wikidata entity json of an import, which no user waits for; the 10 s of the page refresh apply only to wikipedia now |
| `web/phrase/phrase.php` | `wikipedia_confirm_url` 988-993 | 989-996 | a failed refresh now stays on the page of the phrase with the reason (new `WIKIPEDIA_NO_WIKIDATA` warning) instead of the edit view |
| `test/php/unit_workflow/url_test_base.php` | 222 | 223-233 | `add_phrases_to_cache` for the phrases of the row workflows |
| `docs/llm/pending_prio_2.md` | 2476-2510 | 2476-2529 | items 9 to 12: same type create row, owner of api objects, overlay key without log, damaged rows of the dev database |
| `docs/llm/pending.md` | 1 | 3-11 | the edit view part of the request moved to a new prompt "confirm the reference changes together with the phrase" |

## Lessons learned

1. **A new write path finds the bugs of the old read path.** The sign of a phrase id (negative = triple) was
   lost in three api mappers by `phrase::set_id`. Nothing showed it before, because no write sent a triple
   phrase id through the api json. It showed only after the write workflows had moved the wikidata reference
   277 of Zurich (canton) to the word with id 142 in the development database. For the expected table: when a
   new write sends a phrase id through the api, list every mapper that reads a phrase id as "to be checked for
   the sign" before the first write test runs on the database. The trap itself, `phrase::set_id` with `abs()`,
   is still there; a rename or a fix of it would prevent the next case.
2. **A request object from the api is not a loaded object.** It has no owner, so the generic `can_change`
   allowed any user to change the standard row. Fixed for the reference only; the same gap for every class is in
   `pending_prio_2.md` item 10. Rule for the expected table: every new frontend write via `MapObject` lists
   the owner and the user overlay as "to be checked".
3. **A generic assumption of the sandbox does not hold for every link class.** "The to of a link is identity"
   is true for a triple, but the external key of a reference can be overwritten per user. The class
   specific exception was turned into a method (`link_key_fields`) instead of a second hard coded class check,
   which removed the triple `if` too.
4. **The request was bigger than one change.** The infrastructure for any reference row was built, but the edit
   view form that lets the user add, change or delete references needs a new component in `base_views.json`,
   which shifts the component ids. That part moved to `pending.md` as its own prompt. A request that says "the
   same mechanism for all references" should be split upfront into the infrastructure and the first user of it.
5. **The snapshots are most of the diff.** 17'239 added lines, 15'753 of them html snapshots of two
   tiers (`workflow` and `workflow_write`) with six cases (add, change, delete, each with and without
   description) times two steps (save, confirmed). The expected table should list the
   snapshot folders with the number of files, so that a reviewer can check them as a block.
6. **External data adds unexpected fixes.** The leading line break of a wikipedia extract and the wikidata
   timeout came only from the live answers that the write workflows read. A write test against a live service
   should save its answers (`resources/import/wikipedia/`) so that the next difference is visible in a diff.
