
# pending - list of planned llm prompts with prio 1

### view link

add the missing db field to the view link add and edit views: the description field (a user changeable term_view db field); additionally the forms show fields without a term_view db column: a view style select in both forms and a priority field in the add form resp. the component link order number field in the edit form, so either add the style and priority columns to the term_view table (see the priority TODO in term_view.php and form_field_view_link_priority) or remove these form fields, and use the same priority component in the add and the edit form

### formula link

the formula link add and edit views show a description field, but the formula_link table has no description column; either add the description column to the formula_link table or remove the description field from both forms

the term_view (view link) has no priority or order column, so form_field_view_link_priority submits url_var::VIEW_TERM_LINK_PRIO with the fallback text 'prio missing' and no mapper reads it; this is the open decision of the 'view link' section above (add the column or remove the form field)

## workflows

add the missing workflows for the main objects e.g. source, ref, view, component. Compared to the word workflows the workflows only need one back test.

## admin

add to the admin menu a page that shows the system errors

## main pages

in the logout page add an OK button that calls the back page from the url without token and make the "you have been logged out" bigger

## cleanup

check why diff for docs/code_functions_all.md still takes very long

write a php script that checks that a default page for all main classes exists and that the default pages show all fields that are not explicitly defined as not_show


create a script that checks that all fields of the main classes are shown on the related default view, add and edit view except the fields that are explicitly excluded

create a script loops over the resources that lists all queries '*.sql' that does not have a limit and that does not have a unique db id in the where condition.


add the check of the open_api specification to /test/test.php

if the 'views tab' add after the view name a link to edit the view and make the view name a link to the view default page

fix the view selector link in the word_default page

### verb default view

a cut list shows that it continues, but not by how much: the verb page ends the triples with '...' without a number, because the loaded list is itself cut by the read limit, so the number of the remaining triples is not known (unlike value_list::more_tail, which knows the count within the loaded list). the exact number needs its own count query, the same open point as the per type count of the user page

triple_fields::FLD_NAME_GIVEN and FLD_NAME_AUTO are null for all 971 triples of the test database, although the import sets a name for each of them: the generated name lands in triple_name only, so a query that needs to know whether the user has given an own name cannot tell. check if the import should fill name_generated and if the two fields are needed at all beside triple_name

### value page tabs

the value default view now has the 'value tab box' component, so the value page shows the views that can show a value (all views of the value view type, loaded by the new view_list::load_by_type), the change log and the user overwrites

the 'my' tab of the value page has an undo icon for the numeric value, the source and the sandbox fields (see value::db_fld_to_url), but not for a text, time or geo value, because url_var has only NUMERIC_VALUE; the same applies to the apply icon of the 'others' tab

the shared changes and overwrites api arrays moved from cfg/sandbox/sandbox.php to the new cfg/sandbox/sandbox_related.php, because sandbox (one db id per row) and sandbox_multi (a group id per row) have no common parent and the value needs the same code; a third hierarchy would use the same helper

adding the 'value tab box' to base_views.json shifts the database id of every component imported after it: the two components of company.json and companies.json move from 347/348 to 348/349 in src/test/resources/unit/component/list.csv, and the component links of every view after value_default shift by one too

## user page

a group of the 'all_user_overwrites' column shows only the changes of the shown page and not how many overwrites of that type the user has: change_log_actions::GROUP_BY_TYPE splits the rows into one table per object type, but the list is cut to the configured row limit before the split, so a group holds the newest changes of its type and not all of them. a count per type needs its own query that counts the user sandbox rows per table; user_list::load_sql_count_all_rows already builds that union per user and would only have to keep the per-table counts instead of summing them

## component page tabs

the component default view now has the 'component tab box' component with the changes and the my tab; it has no views tab, because the views that use the component are listed by the separate 'component views' component of the same page

api/component/index.php did not read the url flags at all, so it always sent api_json([]) and the owner of a component was never sent either although component::api_json_array emits it under incl_related; the endpoint now uses api_type_list::from_url_array like the triple and formula endpoint

web/component/component.php has a load_by_id_with_related and a db_fld_to_url, both only on the component: view_base and source extend the same web sandbox_code_id, so adding either to the parent would also change the view and the source page, which are separate pending items

change_log_list::table_field_to_query_name returns '_of_cmp' for a component changes tab (leading underscore) and logs 'field name not expected' although an empty field name is the normal case of load_obj_last; the same wart exists for the formula ('_of_frm'), view, source, verb, group and value branches, whereas the ref and the type branch handle the empty field name properly - fixing all of them renames the prepared statements and churns the committed fixtures, so it needs its own change

adding the 'component tab box' to base_views.json shifts the database id of every component imported after it, like the value tab box did before

### more

find and fix the silent not-ok on the fresh-database reset path: during reset_db_forced the request message reached the config check of db_check with status NOK but without any error text (only the DONE and 'finished successful' infos), so a sub step of the fresh-db startup (user creation, type fill or base import) returns a failed message without recording the reason - a 'never fail silently' violation; the broken-sql side effect is fixed (db_object_seq_id::sql_write now uses a build-scoped message), so the next forced reset should surface which step it is

review Message::add_err: it passes ok=true to add(), so an error added via add_err never sets the message to not ok (is_ok() stays true), which inverts the intent of the plain add() that flips to not ok even for infos; decide the intended contract and align add, add_err, add_id and add_info_id

decide how a no update import should treat the object type: sandbox_typed::diff_msg skips the type when $ex_def is set, so a re-declaration with another type is never reported and the later import file silently wins; company.json declares the components "Cash Flow Statement" and "company with ratios" as calc_sheet and companies.json declares the same names as values_related resp. word_value_list, so today companies.json overwrites the type of company.json without a message; either report the type like the other fields or give the two files their own component names resp. one code_id to merge (see docs/llm/json_views.md)

thread the two fill() results of the no update import in sandbox_list_named::update: both $sbc->fill($dbo, ...) and $dbc->fill($sbx, ...) drop the returned user_message, so a permission problem of a fill setter (e.g. component::set_ui_msg_code_id for a user without can_set_ui_msg_id) is lost instead of being reported

review buttons

web/value/value_list.php:1352 and :1360 were not migrated — $phr_row->btn_add($back) / $val_main->btn_add($back) still pass the old string $back into the new array $url_arr parameter. dsp_table($phr_row, $back, ...) is untyped so nothing catches it statically; at runtime this is a TypeError.

deprecate dsp_edit

deprecate http_old

review $back

review $msg

repeat the check of the fields in the default page, the my tab and the fill of 'all_user_overwrites' for refs, term_views and any missing main or link class

Worth fixing
formula button tooltips lost their object name
button is now btn_edit() with no $url_arr at view.php:171 and word.php:751.
Production web/ code now includes and reads test constants. web/view/view.php:67 and web/word/triple_list.php:51 add a real include_once test_paths::CONST . 'word_names.php' and hardcode word_names::ZH_ID as the back target. list_sort.php extends the same pattern to triple_names::GLOBAL_PROBLEM_ID — and there the include_once is commented out while the use is active, so it only works via the autoloader. triple_list.php:147 has a TODO Prio 0 admitting the $url_arr is a placeholder; view.php:145 has none. Either way "Zurich" as the back link on a generic view navbar is a placeholder, not behaviour.
Dead code left behind in view.php:617,619 — $call_edit / $call_del are still built via url_back(...) but no longer used, since btn_edit()/btn_del() build their own url. The buttons also lost the word= and back context those urls carried.
Unused additions in tests: graph_tests.php:60-62 adds $base_url, $lan, $url_arr — none used in the file; languages is imported unused in both graph_tests.php and horizontal_ui_tests.php.
Tooltip regression, visible in base_ui_tests.php:512: the delete title went from delete this formula of scale minute to sec to delete this formula — the object name dropped out of the button title. Intentional?
Nit: shared/const/words.php:335 inserts SOLUTION after STATEMENT, breaking the alphabetical order of that list.
docs/llm/pending.md drops the self:: vs $this:: item (correctly — db_object::btn_add uses $this::VIEW_ADD_ID) but keeps the AUTO_UPDATE_TEST_FILES item, which is still open per point 1.              
horizontal_ui_tests:106-111 calls these for many classes but only asserts the icon is present, not the url — so nothing catches it.
dsp_list() drops the add button on an empty list. $vrb is assigned inside the foreach, so it stays null when there are no verbs and the if ($vrb != null) block is skipped — a user looking at an empty verb list has no way to add the first one. The old free btn_add() was unconditional. The button also now belongs to whichever verb happened to be last in the list, which reads oddly for an "add new verb" action.
$add_script in dsp_list() is now dead (verb_list.php:131) — nothing consumes it since btn_add() builds its own url.
Two url styles on one page, visible in the new snapshot: list() emits m=verb_edit / m=verb_add (code ids, via html_base::list()'s url_back) while the migrated dsp_list() button emits m=6 (view id). Same page, same action, two conventions — the html_base::list() side has not been through the #247 migration yet.
My architecture.md section pins the wrong parameter order. It writes add_obj($obj, $allow_duplicates, $msg), which matches ListOfIdObjects and sandbox_list_named but not the new type_list::add_obj($obj, $msg, $allow_duplicates). The new order is the one the rules actually require (a user_message parameter must be required, so it cannot follow an optional one) — the two older siblings still have Message $msg = new Message(). The doc should either not name an order or say the new one is the target.
type_list::get() now has a false alarm. It logs log_err('probably ... are duplicate code_id') whenever count($this->hash) != count($this->lst) — which is exactly the state a legitimately duplicated list is in, since hash is keyed by code_id. It only fires if someone calls get() on such a list, but the check contradicts the feature just added.
url_back() lost its docblock (html_base.php:730-760). base_url_clean() was inserted between the existing /** … @return string the created url */ block and function url_back(, so that block now documents the private helper (which has its own docblock right below it) and url_back() is undocumented. Moving base_url_clean() above the docblock fixes it.
sort_by_name()'s docblock is now false (type_list.php:403). It justifies returning a copy with "because get() maps the position in the hash to the position in the list" — but get() no longer does that, it was changed in this same diff. Returning a copy is still right (a preloaded type list is shared for the whole request and must not be reordered under other holders), but the stated reason has to be the new one.
text_h4() breaks the pattern of its siblings. text_h1/2/3 pass the matching level as the non-bootstrap tag (H2,H1 / H4,H2 / H5,H3); text_h4 passes self::H6, self::H3, so with UI_USE_BOOTSTRAP = false an h4 renders as <h3>. Invisible today because bootstrap is on — the snapshot correctly shows <h6>.
The snapshot does not actually exercise sort_by_impact(). Every verb from load_dummy() has impact 0.0, so the short and more versions fall through to the name tie-break and render alphabetically — identical to what sort_by_name() would produce. The new sort is unproven by the committed test; a fixture with distinct impacts (or an assert on sort_by_impact() directly) would close that.
$url_arr is unused in both verb_list_ui_tests.php:64 and word_list_ui_tests.php:57. Nothing on either page takes one.
word_list_ui_tests.php / word_list.html are unrelated to this change. The only effect is that the three stylesheet hrefs became absolute (http://localhost/…) because $base_url, $lan were added to its html_page_test call. Fine in itself, but it is a separate concern riding along — worth a glance that you want it in this commit.
src/test/resources/import/carbon_leakage_effect.json is staged and unrelated — 244 new lines, referenced by no test and no PHP file, and nothing in this change set produces it. It looks like it wandered in from other work. Either it belongs to a different commit or its consumer is still missing.
The component insertion point causes ~1,600 lines of avoidable churn. I placed system title user directly after system title user settings, which lands it at component id 98 and shifts every later component id by one: unit/component/list.csv 450 changed lines, unit/component_link/list.csv 1,866. Appending both new components at the end of their components blocks would have given the same result with a two-line fixture diff. I checked the pinned consts in shared/const/components.php — the highest is FORM_PLURAL_ID = 92, below the insertion point, so nothing is silently mis-pinned. But if you would rather not carry that renumbering, moving the two definitions to the end of their blocks and re-running is cheap now and awkward later.
Seven user snapshots were renamed, not edited — 74_user.html → 74_user_9.html, and likewise 49, 50, 62, 85, 87, 89. The filename carries the dbo id, which is now 9 instead of 0. Correct consequence of user_filled_loaded, but it makes the diff read as delete+add; worth confirming the deletions are the paired old files and that the runner's "remove test files not used any more" pass did not drop anything else.
component_types.csv gains a seeded row, so the database needs the new type. I read that as data rather than structure and left version.txt alone — your call if it should carry the minor bump plus a db_check step.
The new component is not wrapped in the row col-md-12 column div — visible in 74_user_9.html: the first four components each sit in <div class="row col-md-12">, the fifth does not. Since the request was specifically "in a fixed column", this defeats the point of the placement.
The cause is pre-existing, not introduced here: view_exe::show() wraps a row only when it reaches the next component ($result .= $html->div_row($row, $row_style) in the BELOW branch), and the final flush at line 271-273 is a bare $result .= $row; with no div_row(). So the last component of any view has always been unwrapped — in the committed snapshot it was user system errors that lacked the div, and now it is the new one. Fixing the flush to use div_row()  with the same style resolution would wrap both, and would change the tail of many view snapshots.
The two change-log api fixtures now carry a per-run change_time. change_log_list_word_1_word_name.json gained "change_time": "2026-08-18T09:10:19+00:00", which the committed baseline did not have. assert_api_compare() strips it before comparing (json_remove_volatile → change_log::FLD_TIME), so tests still pass — but the stored file now holds a timestamp that differs on every reset, so these two files will churn on each run.
Cause: the update_files_with_not_yet_fixed_db_id() helpers I added last commit write the raw api response ($lib->json_for_dev($created)) without passing it through json_remove_volatile() first. The type_list_check() pattern I copied has the same property (its header timestamp also moves each run), so this is consistent with the existing code — but for the change log it bakes a volatile value per row where there was none. Stripping volatile fields before writing would make these fixtures stable.
The files also switched from 2-space to 4-space indentation, confirming a different writer than whatever produced the committed versions.
rest_call::api_call() gained $extra_headers for a test-only need. The parameter exists solely so assert_api_get_not_permitted() can send X-Forwarded-For and reach the rejection arm — no production caller passes it. Defensible (the local test runner is an own-pod call, so there is no other way to exercise that branch), but it is production API surface added for a test.
The two skip-guards in assert_api_get / assert_api_get_by_text got looser. They now also skip when the response lacks an email field. That correctly absorbs the new core-json response when the admin login fails — but it also means a genuine regression that drops the email from the admin response would be silently skipped rather than failing. Narrower would be to check for the msg key or an explicit core-shape match rather than "email missing".
load_by_id_with_related() ignores the load_by_user result. The $msg is threaded and merged, so problems do reach the user; but a failed change-log call leaves chg_log as an empty list, which the renderer cannot distinguish from "this user has no changes" — the page then says "This user has not changed anything." even when the load failed.
The changeLogList branch has no permission check, justified in the comment as "the change log is public like on the object pages". That is consistent with the existing by-object branch, which is equally unguarded — worth being deliberate about, since this is the first endpoint keyed on a user id rather than an object id.
The budget can overshoot by design. A group renders whole once started, so the last group may push the total past the limit — with your 20 and a 15-member group starting at budget 1, the page shows 21. The comment says so, and it follows from your "cap each group / keep groups whole" choice, but the limit is a target rather than a hard ceiling. If you want a hard cap, the last group would have to be truncated mid-way.
impact_group no longer defaults to the configured limit. Its $budget is now required and it uses max(0, $budget). Every caller passes a real value today, but a future caller that passes 0 gets a section with no rows and a tail counting everything — silently correct-looking, easy to get wrong. A ?int $budget = null defaulting to configured_limit() would be safer.
The per-group cap re-reads the config per section. time_groups() and relevant_phrase_groups() each call configured_limit() for $per_group, and group_block() calls it again per group. Correct, just repeated work on a hot path; passing it down with the budget would do.
The word pages moved too — word.html, word_api.html, word_default_word_1/197, word_del, word_edit, value_list.html all changed. Expected (the same renderer), but they are the pages to eyeball for over-truncation, since the fixtures there are small and now render against a limit of 6 in tests.
The owner is still not rendered — the task is incomplete. No snapshot anywhere contains ow=. url_mapper::url_par() drops null values, so $obj->get_user()->name() is null when the test url is built, and the system show field owner component renders nothing on both 116_formula_link_1.html and 115_component_1.html. The transport chain exists end to end; the test fixture just never supplies the name.
view_relation start position is now an empty field. Removing the 'position missing missing' placeholder was right, but test_mappers::view_relation_url doesn't carry url_var::POSITION (and view_relation has no to_url_array override), so 43_view_relation_update_1.html renders the field with no value. Component link works (value="1"), formula link is fully wired. That is exactly the "unexplained asymmetry" the fix-the-pattern rule warns about — one of the three is wired, one reads from an existing url var, one can't.
Empty "Changes" tab on the formula link page. change_log_table_pure returns a table with when / who / what headers even with zero rows, so tab_box doesn't drop it. 116_formula_link_1.html shows a Changes tab containing only headers.
form_field_formula_link_prio has no German translation. I changed the en text to "Priority"; de.yaml has no entry for it at all (nor for form_field_view_term_link_prio / form_field_component_link). Pre-existing, but the rule is en and de.
Seed component ids shifted. The three new components in base_views.json pushed Cash Flow Statement 344→347 and company with ratios 345→348. list.csv is regenerated and no *_ID const pins either, so nothing broke — reporting it rather than absorbing it silently, as the json rules require.
Two include_once lines commented out (yours): sandbox_named.php no longer includes its own parent sandbox.php, and type_list.php drops change_table_field.php. Same shape in web/sandbox/sandbox.php, where change_log_list.php is commented out while api_mapper does new change_log_list(). All load today, but each now depends on some other file loading the class first — the same class of fragility as the sandbox_link fatal.
Minor: $url_arr is declared but unused in source_ui_tests.php and user_ui_tests.php (used 4× in component_ui_tests.php). And code_object_name_exceptions.md grew for formula_link — my test vars $lnk_fld_url, $lnk_plain, $lnk_tab — against the rule that the list stays short.
Not a defect: the coverage doc lists show_link_type, show_order_nbr and link_type as "0 unit test calls". They are tested, but in unit_ui/, which that generator doesn't count.     
api/ui_config/ui_config.json — timestamp-only diff. Only the header timestamp changed. Consider reverting; it's pure churn.
load_sql_by_user_value() is public but has no external caller. load_by_user() is the only user. Its sibling load_sql_by_user() is public too, and the test calls it directly, so this matches the local convention — just noting it.
sort_by_time_and_cut() duplicates the web twin. The frontend web/log/change_log_list already has sort_by_time_and_what() + head(). The two apps are deliberately independent (docs/llm/frontend.md), so this is the accepted pattern rather than a DRY violation — but the backend now sorts and the frontend re-sorts the same list.
The changes table comment is the load contract. load_by_user() reading 13 tables rests entirely on that comment ("all tables except value and link changes"). If a value change ever lands in changes, it would be listed twice. Not currently possible, but nothing asserts it.
version.txt correctly untouched — no json format or db structure change.
Generated docs: code_user_message_exceptions.md only shifts a line number, doesn't grow. code_test_coverage.md adds the three new functions.
The fold is duplicated verbatim in the backend and the frontend std_table* function. That mirrors the existing prefix-strip duplication rather than adding a new pattern, so it is consistent, but both copies now have to move together.
Verification: start page and user page render without a fatal; a script replaying both std_table* functions over every row of change_tables.csv shows all fifteen user_values* folding to values while user_value_links, value_ts_data, the change_values_* log tables and the *_standard* tables stay untouched; and a text group id round-trips through the api mapper into …?m=19&id=PmS%2BXk9wQzR0dGVzdA&n=&8n=123456&z=1. No sql fixtures need regenerating — the l2.table_id  filter travels as a bound parameter (= ANY ($2) / IN (?)), so the longer list does not change the prepared-statement text. No secrets in the diff.
A caller I missed, already fixed by you: test_words::zh_full_ui() calls the web word's set_type, so it needed user_message_ui not user_message. My caller scan mis-classified it because zh_ui() returns a word_ui. I re-checked — that was the only one; every other set_type call is on a backend word, a component, element or sql_db.
The fixtures are now coherent: component 352 = the new field, link 1013 = verb view position 7, sub-title 8, triples 9 — exactly as computed; the api type-list fixtures carry only the new system_show_field_name_in_formulas, none of the old colliding name. The verb snapshot's triples are in ascending id order, confirming the tie-break landed.
change_log_list_word_1.json shifted by +3 (ids 6885→6888 …) — the re-import wrote three more change rows. That fixture pins absolute change-log ids, so it will drift on every re-import; not caused by this change, but worth knowing.
docs/code_user_message_exceptions.md now reports db_object_seq_id.php:350 - user_message $msg = new user_message(), instead of the full signature — the generator matches a single line and your reformat of api_json split the signature across lines. Cosmetic, but the entry is now less readable than its siblings.
Checked and clean: no &&/|| in added code, no secrets, includes and use blocks alphabetically placed, log_err only on genuine programming-error paths.

Worth deciding
Button tooltips lost their object detail. formula::btn_edit()/btn_del() were renamed to *_back, so the formula page now uses the generic db_object::btn_edit(), which passes no $explain. Visible in formula.html: title="change formula for scale minute to sec" → "change formula", and "delete this formula of scale minute to sec" → "delete this formula". If that's intended, fine; if not, the new generic variants need an $explain path too.
api/.htaccess — <FilesMatch "^[A-Za-z0-9_]+$"> re-allows every extensionless name under /api, which is what the routes need. Worth noting it also re-allows any other dot-free file that lands there; a tighter alternative is listing the route names, at the cost of maintenance. Your call — the current form is the one I proposed.
src/test/resources/unit/user/list.csv and api/ui_config/ui_config.json changed too — presumably from a reset_db run; worth a glance that they belong in this commit.
code_object_name_exceptions.md grows by two: $lst_dbl and $lst_empty are added to the verb_list line. Three instances in one scope is a legitimate deviation, but the file is supposed to shrink — $vrb_lst, $vrb_lst_dbl, $vrb_lst_empty in the test would keep it flat.
test_verbs::list_short() mixes $this->verb_is() with self::verb_part() (the factory declares that one static) — harmless, just uneven.
In the base type_list::set_from_json_array(), the verb arm calls $vrb->api_mapper($value, $msg) and ignores the returned bool, while verb_list's override guards on it. Pre-existing, but the two now sit side by side.
ames_one_line() / the sort closures type the entries as verb|type_object while get() uses verb|ref_type|type_object. ref_type extends type_object, so it works, but the two unions should read the same.
name_tip() entries without a description render as a bare <span>name</span> — a wrapper with neither class nor title. Only "is a" carries a tooltip in the snapshot, since load_dummy() sets no descriptions.
Still open from earlier: get() opens with count($this->hash) != count($this->lst) and logs probably … are duplicate code_id, which is a false alarm on a duplicates-allowed list and fires on every call. It is the last consumer of the parallel-arrays assumption the rest of this diff removed.

Fixtures are half-regenerated — rerun before committing. Two regeneration runs are mixed in the tree (28-08 22:25 and 29-08 10:05):
- api/type_lists/type_lists.json is from the older run: its 13 system sub title usage number entries lack ui_msg_value_exception: 0, while ui_config.json has all 13.
- views_by_id/component/34_component_update_1.html (older run) still renders Used 0 times, while its sibling views_by_object/component/component_edit_component_1.html renders Not used.
- views_by_object/formula/formula_default_formula_21.html / _26.html still carry the live 29-08-2026 10:31; they pick up the fixed test time only on the next run.
- Two snapshots from July still show Used 0 times and were never regenerated: workflow/change_triple_by_name_wf8/wf8_show_edit.html and workflow_write/change_word_wf2/wf2_show_edit_back_edit_save_cancel_edit_save_confirmed_edit.html (the second is one of the orphaned wf snapshots already listed in pending_prio_2.md).

api/result/ is untracked — needs git add, and the served copy in /var/www/html needs the new endpoint plus the backend changes before the http api tests run.

api/formula/formula_body.json lost "usage": 7 and "ref_text" while gaining need_all_val. Nothing in this batch removes either; both are db-state dependent (usage is written by a batch job, ref_text on save), so this may flip back on the next regeneration — worth confirming it is intended.

## prepare

create in triple list a 'validate_alias_direction' function that checks if the alias verb always leads to the same main phrase

based on the /test/create/ class function and the solution_prio.json create a set of test data (a filled $dto object where the database id is taken from the /test/create/ class functions and the rest e.g. the val)

## start page

the basic steps to show the start page are

- table with 'global issues'
- sort global issues by impact which is htp
- get mayor, main and minor columns linked to 'global issues' via triple
- the order of the column may differ and is relative e.g. 'per cent is after number'
- the number of rows to show is taken from the config but can be overwritten


# move to Prio 2

## triple add and edit view

reduce the size of the triple description field to 8/12 and add after the description a formula selector for the conditional formula 

## word db table

similar to the triple table field 'triple_condition_id' add a field 'condition_formula_id' to the word table

## word add and edit view

reduce the size of the word description field to 8/12 and add after the description a formula selector for the conditional formula

## triple db table

rename the triples table field 'triple_condition_id' to 'condition_formula_id'

## log in 

if no picture of the user is uploaded create a random picture and show it in the top write corner if the user is logged in

## language 

### word

add the missing db fields to the word add and edit views: a language selection (language_id, preselected with the user language) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable fields impact and inactive as display-only info in the edit view next to the existing usage component

show the missing db fields in the word default view the language e.g. as a small info line next to the description

### triple

add the missing db fields to the triple add and edit views: a language selection (language_id, preselected with the user language), the given name (name_given) as its own field beside the generated name (name_auto, display-only) so the user sees the difference and can empty the given name to fall back to the generated one, the condition formula selection (triple_condition_id) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable fields impact and inactive as display-only info in the edit view next to the existing usage component

## later

add the word splitter to convert_wikipedia_table.php

