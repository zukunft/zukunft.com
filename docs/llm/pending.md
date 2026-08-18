# pending - list of planned llm prompts with prio 1

## user default view

add the trible overwrites to the 'all_user_overwrites' component

similar to the my tab in the word default view add a my tab to the formula default view

add the formula overwrites to the 'all_user_overwrites' component used in the user_default view

fill the formula_link_default view with the missing fields including the my tab for the user_changes

add the formula_link overwrites to the 'all_user_overwrites' component used in the user_default view

write a php script that checks that a default page for all main classes exists and that the default pages show all fields that are not explicitly defined as not_show 

add a 'my' tab to the value_default view that shows tha user overwrites similar to the 'my' tab in the word default page

add the value overwrites to the 'all_user_overwrites' component used in the user_default view

add a 'my' tab to the component_default view that shows tha user overwrites similar to the 'my' tab in the word default page

add the component overwrites to the 'all_user_overwrites' component used in the user_default view

add a 'my' tab to the view_default view that shows tha user overwrites similar to the 'my' tab in the word default page

add the view overwrites to the 'all_user_overwrites' component used in the user_default view

fill the component_link_default view with the missing fields including the my tab for the user_changes

add the component_link overwrites to the 'all_user_overwrites' component used in the user_default view

add a 'my' tab to the source_default view that shows tha user overwrites similar to the 'my' tab in the word default page

add the source overwrites to the 'all_user_overwrites' component used in the user_default view

check if any functionality or information from src/main/php/web/user/user_display_old.php has not yet been part of the default and user views and report missing parts in /docs/llm/pending.md

repeat the check of the fields in the default page, the my tab and the fill of 'all_user_overwrites' for refs, term_views and any missing main or link class

## temp

fix the view selector link in the word_default page

## cleanup

review buttons

web/value/value_list.php:1352 and :1360 were not migrated — $phr_row->btn_add($back) / $val_main->btn_add($back) still pass the old string $back into the new array $url_arr parameter. dsp_table($phr_row, $back, ...) is untyped so nothing catches it statically; at runtime this is a TypeError.

deprecate dsp_edit

decprecate http_old

review $back

review $msg

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


## main pages

in the logout page add an OK button that calls the back page from the url without token and make the "you have been logged out" bigger

### triple

show the missing db fields in the triple default view: the weight, the condition formula

### verb

extend the verb default view, which today only shows the verb name and the related triples, with the missing db fields: the description, the plural (name_plural), the reverse (name_reverse), the plural reverse (name_plural_reverse), the name used in formulas (formula_name) and the usage

### values

add change log to value default view

show the missing db fields in the value default view: the source of the value, the timestamp of the last update and the share and protection status

### source

add the missing db field to the source add and edit views: the code_id field, shown only for users whose profile passes can_set_code_id (for sources the code_id is a user changeable field)

extend the source default view, which today only shows the source name and the related values, with the missing db fields: the source type, the url as a link, the description and the usage

### ref

show the non-changeable ref db fields last_update (the timestamp of the last successful update of the reference) and impact as display-only info in the ref edit view

extend the ref default view, which today only shows the reference name and the related values, with the missing db fields: the linked phrase, the reference type, the external key, the url as a link to the external page, the source, the description and the last_update timestamp

### formula

show the missing db fields in the formula default view: the latex format (rendered, next to the existing expression components), the formula type, the 'all values needed' flag and the timestamp of the last update

### result

extend the result default view, which today only shows the related results, with the missing db fields of the requested result itself: the result value with its phrase group, a link to the formula that calculated it and the timestamp of the last calculation (last_update)

### view

add the missing db fields to the view add and edit views: a language selection (language_id, preselected with the user language) and, for users whose profile passes can_set_code_id, the code_id field; show the usage as display-only info in the edit view, because unlike the other edit views the view edit view has no usage section yet

create the missing view default view (view_default): a view that shows a view itself with its name, description, type, style and the list of linked components, plus the usage e.g. the terms that use it as their default view

### component

add the missing db fields to the component add and edit views: a formula selection (formula_id, used for the calculated component types), a linked component selection (linked_component_id) together with its link types (component_link_type_id and link_type_id), and, for users whose profile passes can_set_code_id, the code_id and ui_msg_code_id fields (with ui_msg_code_id_vars, ui_msg_code_id_exception and ui_msg_value_exception); show the usage as display-only info in the edit view

create the missing component default view (component_default): a view that shows a component itself with its name, description, type, style, the row and column phrases and the views that use it

### view link

add the missing db field to the view link add and edit views: the description field (a user changeable term_view db field); additionally the forms show fields without a term_view db column: a view style select in both forms and a priority field in the add form resp. the component link order number field in the edit form, so either add the style and priority columns to the term_view table (see the priority TODO in term_view.php and form_field_view_link_priority) or remove these form fields, and use the same priority component in the add and the edit form

### formula link

the formula link add and edit views show a description field, but the formula_link table has no description column; either add the description column to the formula_link table or remove the description field from both forms

## workflows

add the missing workflows for the main objects e.g. source, ref, view, component. Compared to the word workflows the workflows only need one back test.

## admin

add to the admin menu a page that shows the system errors

# move to Prio 2

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

