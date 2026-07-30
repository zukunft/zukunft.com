# pending.md

## list of planned llm prompts

### high prio



#### review formula

add non latex formula to formula view

add the latex field to the formula add and edit views: latex is the only user-editable formula db
field (formula_db::FLD_LST_USER_CAN_CHANGE) without a form field - the url var (url_var::LATEX
'fx' / 'latex'), the url mapper read (web/formula/formula.php url_mapper) and to_url_array already
transport it, but no 'system form field latex' component exists; create the component (unique
ui_msg_code_id, see docs/llm/json_views.md), add it to the formula_add and formula_edit views in
src/main/resources/messages/system_views.json after the formula text field, and extend the
formula workflow tests (fill_url_array already carries every editable url field) plus the
object_pages snapshots

complete web/formula/formula.php db_fld_to_url: it maps only formula_name, formula_text and
description, so the confirm change preview cannot label a pending latex, formula type or
all_values_needed change with the changed db field; add formula_fields::FLD_LATEX,
formula_fields::FLD_TYPE and formula_fields::FLD_ALL_NEEDED with their url vars and add the
matching positive and negative unit tests (docs/llm/testing.md)

note for the review: the other formula db fields are absent from the edit view by design -
formula_text (internal {f1} expression) is derived from the posted user expression on save,
last_update is set by the calculation, usage is shown read-only as the usage number sub title,
impact and usage overwrites are the separate admin GUI pending entry, and excluded is handled by
the delete workflow

#### change log

add change log to value default view

add my changes to word and triple view

in the change log flip the 'to' and 'from'

design and apply the "write only the changed fields" save flow in one deliberate change (the display side of the phantom 'added view id ""' rows is already fixed by resolving the view name from the cache in change_log_named::value_to_show): a description-only word edit still writes a view assignment because (1) word::view_selector preselects the default view (d=90) for a word without a stored view and fabricates the '8'-prefixed baseline from the same value, (2) ui_preview::popup_changes re-posts every field but drops the '8'-prefixed baselines, so action_crud cannot tell "unchanged" from "chosen" and the backend compares the full posted object against the db row; the fix needs an agreed null convention first: in a save request null must mean "field not carried, keep the stored value" while the user must still be able to set a value back to null with the normal save (e.g. an explicit empty string as the clear request that the write converts to null), and before changing anything the import null handling must be audited (import_mapper maps missing fields to null; the no_update re-import round-trips and sandbox::save fill/get_similar rely on the current compare semantics), then carry the '8' baselines through the confirm submit, drop the unchanged fields before the write, add the matching null guards to db_fields_changed (description, usage, plural, impact, view) plus positive and negative unit tests per field, and check the same pattern for triple, source, view and component edit forms


find all '&back=' url parameters and list here the prompts to fix these issues by using instead the url_var::BACK prefix

fix the user type and status export/import round trip: the export writes the type display name under json_fields::TYPE ('type_id', see user::export_json using type_name()) but import_mapper reads json_fields::TYPE_NAME ('type'), so an exported type is silently ignored on import and the guest default fills it (the unit fixture user_import.json only passes because its value "Guest" equals the default); additionally set_type expects the code_id ('guest') while the export writes the name ("Guest"), so even with matching keys the value would not resolve (user_type_list has usr_can_add = false); decide whether the export switches to the code id under the 'type' key (json format change -> minor version raise and db_check upgrade script per docs/llm/versions.md) or the import accepts both; the status has the same name-vs-code-id issue (status_name() exported, usr_sta->id() on import)

fix the last violation of the default-value rule (docs/llm/constants.md "Default values are resolved at the point of use, never fabricated in a mapper"): user::api_mapper still fabricates user_profiles::NORMAL_ID for a missing PROFILE_ID (cfg/user/user.php ~515) and the profile branch in db_fields_changed (~3653) has no null guard, so a json-born user without the profile field saved by an admin requester silently demotes the stored profile to normal (enforce_profile_privilege only blocks unprivileged requesters, an admin passes can_set_profile(normal); the frontend api_array omits PROFILE_ID unless is_profile_valid()). Apply the same treatment as for the type and status: map a missing profile to null in api_mapper, add the !== null guard in db_fields_changed, let enforce_profile_privilege treat null as "keep stored" (its int $req_profile_id parameter needs ?int or an early guard), and add the matching negative and positive tests to the "preserved on save" blocks in src/test/php/unit/user_tests.php; also backfill profile_id in the user exception block of horizontal_ui_tests.php like type_id and status_id, because the add url does not transport the profile and the round trip only passes today because the fabricated normal profile matches the filled test user

cosmetic alignment with the default-value rule (no data-loss risk, round trip is safe): web/user/user.php api_mapper defaults profile_id, type_id and status_id to 0 instead of null (api_array drops them via the >0 and is_profile_valid() checks) and web/component/component.php api_mapper sets pos_type_id to position_types::DEFAULT_ID (the property is declared non-nullable with that default and api_array suppresses the default), so the frontend mirrors cannot express "not specified"; align them to nullable properties mapping to null when the field is missing

fix as many TODO Prio 0 as possible

create the user_message $msg at the entry point and add it as a parameter to all function that might create a message that needs to be shown to the user

create an admin view with the system errors 

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible.

Add to admin yaml a list of term that should be added on start to the document route. Add a "en" folder with static pages that store each request to a file that is read, executed and cleaned by the sceduled job runner. For non en languages use e.g de subfolder

### complete the frontend backend split — implementation steps

the goal (docs/llm/frontend.md): frontend (web/ and http/) and backend (cfg/ and api/) are two
independent apps talking only over the api; code needed by both lives in shared/. the temporary
bridges are the backend paths include at the top of web/const/paths.php and the html_paths::MODEL_*
/ DB / API_OBJECT copies in the same file — they fall step by step with the prompts below. done so
far: env.php moved from cfg/const to shared/const (read by both apps); api/word/index.php reads the
request body via the backend controller::request_json instead of the frontend rest_call class. run
the steps one at a time, each as its own commit with tests written first:

1. move the pure text logging classes cfg/log_text/text_log.php, text_log_format.php and
   text_log_level.php to shared/log_text (check first that they have no db or model dependency);
   web/log_text/text_log.php then extends the shared class; the model-dependent
   text_log_functions.php (log_err writes to sys_log in the db) stays in the backend for now

2. give the frontend its own error reporting: the web/ code calls log_err / log_warning / log_debug
   from cfg/log_text/text_log_functions.php, which is backend code (db write to sys_log); create
   frontend log functions that report problems via the existing sys_log api endpoint (rest_call)
   and echo to standard io, then remove the text_log_functions include from web/init_ui.php

3. replace the backend $sys usage in web/ with a frontend-owned equivalent on $ui_sys: the timing
   (web/helper/config.php load($sys) and the api call timing TODO in web/html/rest_call.php) and
   the preloaded type list access (web/system/sys_log.php $sys->typ_lst->sys_log_sta) move to
   $ui_sys; then web/init_ui.php no longer creates the cfg system_object and $sys becomes a
   backend-only global in docs/code_object_name_exceptions.md

4. after steps 1-3 remove the remaining cfg includes from web/init_ui.php, remove the backend paths
   include from the top of web/const/paths.php and drop every html_paths::MODEL_* copy that only
   web/init_ui.php used; add the coded check to unit/coding_rule_tests.php that web/init_ui.php and
   web/const/paths.php include no cfg file

5. replace the deprecated direct-db bootstrap of web/frontend.php (start / open_db / end /
   load_cache) with api calls per the TODO in coding_rule_tests::php_web_only_allowed_globals_tests;
   then remove the 'frontend.php' exception from that rule, remove the db and model includes from
   frontend.php and drop the html_paths::DB and now unused html_paths::MODEL_* copies

6. move the reserved test name consts that production web code includes (test_paths::CONST
   formula_names.php / triple_names.php / word_names.php in web/frontend.php,
   web/formula/formula_list.php and web/component/execute/system_form.php - included "to avoid that
   names used for testing are used in production") to shared/const so production code never
   includes test code; then remove the test path block from http/const.php (its TODO Prio 2) and
   the test_paths use from the web files

7. migrate the remaining direct-db web files to api calls or delete the deprecated functions:
   web/log/user_log_display.php and web/user/user_display_old.php create their own sql_db, and
   web/word/word.php still builds sql; afterwards no web file references sql_db and the
   html_paths::DB copy falls if not already removed in step 5

8. extend coding_rule_tests::php_cfg_no_web_tests to also scan paths::API, paths::API_OBJECT and
   the /api scripts, so a backend file using a web class (like the api/word/index.php rest_call
   use fixed above) is caught by the unit tests

### requesting user lives on $msg — implementation steps

the rule (docs/llm/coding.md, docs/llm/state-and-messages.md): every http entry point sets the requesting user on the request's user_message once, as early as possible, and every function below takes $msg as a parameter and reads $msg->usr — never a second requesting-user parameter, never a global, never $_SESSION. http/view.php is done; run the steps below one at a time, each as its own commit with tests written first:

1. done (web/frontend.php reads the requesting user from $msg->usr; the by-reference user parameters of action_login/signup/activate/logout and the backend user of url_to_action stay until the login user switch goes through the api)

2. done (web/sandbox/db_object.php add_via_api / update / del and shared/helper/MapObject.php convertToDb read the requesting user from the message — the backend twin travels inside the message api json via convertMsgToDb — and user_request no longer carries a separate frontend user; a crud call without a message user reports msg_id::USER_MISSING, see the crud guard tests in unit_ui/word_ui_tests.php; no new backend flag was needed because user::api_json_array already emits uses_sandbox)

3. done (all 33 api/*/index.php and http/setup.php create one backend user_message right after $usr->get() and set $msg->usr = $usr; the controller functions get_json / post_json / put_json / delete / not_permitted / change_permitted take the user_message instead of the string msg and the separate user parameter, and the write path saves directly onto the request message; change_permitted refuses a null message user like an ip user — test helper assert_api_write_blocked_without_user is in the still deactivated "TODO Prio 0 activate" block of unit_write/api_write_tests.php; api/config/index.php also lost its $msg reassignments in favour of merge())

4. done (sandbox_multi::change_blocked and sql_db::setup_db read $msg->usr and log_err a missing user instead of overwriting it; the local bootstrap messages — sql_db load_db_code_link_file / add_user_from_env / create_internal_words, text_log_functions sys_log_fnc save, user::db_insert / db_update_user, db_id_object_non_sandbox::del_exe, convert_wikipedia_table and frontend::action_logout — now set their user via the user_message constructor instead of a post-hoc ->usr assignment; the remaining writers below the entry points are frontend::url_to_action:819 (the sanctioned login user switch), user_message::clone_reset (the class itself) and db_object_seq_id_user::set_requesting_user:230 — the last one guards every sandbox save/del with a documented owner fallback for reset() messages, so it is deferred to step 6)

5. done (import.php and user.php read $msg->usr where the message is already threaded; the query user became a connection-level fact instead of a global — sql_db::$usr_req is set once by the entry point / setup_db and read by sql_db::set_class and sql_creator::set_class as the default query user, so both factory- and directly-built sql_creators default to the same user without threading it through the hundreds of set_class call sites; web/frontend.php url_to_html_cached reads $db_con->usr_req until the refresh job goes via the api; system_object::$usr_req is deleted; the sql-generation test setups all_unit_tests.php / group_write_tests / import_write_tests / sandbox_tests set $db_con->usr_req = $t->usr1 so user-scoped query snapshots stay scoped to usr1)

6. done (user_message::reset() now keeps the requesting user by default (keep_usr = true), so the reset loophole is closed; db_object_seq_id_user::set_requesting_user no longer overwrites $msg->usr — it mirrors change_blocked and log_errs a missing requesting user as an internal inconsistency instead of silently patching it with the object owner; the 20 remaining dual user+user_message signatures (down from 32 as earlier passes trimmed them) are all legitimate and split into three groups: subject-user payloads the function operates on (sql_creator::sql_func_log, config_numbers::read_cache / read_db_cache / read_file_cache, sandbox::take_ownership, sandbox_list::load_user_changes / load_sql_user_changes, job_cache_refresh::invalidate_if_outdated, job_db_cleanup::del_old_jobs, user::no_diff / no_non_id_diff / is_same / check_preserved), the requesting user passed explicitly to a pure permission helper that only reads it (sandbox::check_protection_change ×2 — the callers feed the object's own get_user(), user::enforce_profile_privilege, sql_db::add_user_from_env / add_admin_users_from_env), and an optional requesting-user override that defaults to $msg->usr (user::import_obj / save_user); none is a fallback writer, and each keeper's user @param now states which user it is)

7. done (unit/coding_rule_tests.php now has both coded checks, wired under the 'requesting user on the message' subheader and scanning the library below the entry points — MODEL, API_OBJECT, WEB, SHARED: (a) php_user_message_param_shadow_tests uses a token_get_all parser (msg_param_shadows + helpers) to flag any function that overwrites its own user_message parameter with a fresh new user_message() — the append-only shadow — while tolerating a legitimate local buffer of the same class, a default-value initializer in the signature and the guarded null-init of a nullable parameter (import_convert_xbrl::build_data); a line grep could not tell these apart, so the parser walks each function body, attributes nested-closure bodies to their own scope, and matches $param = new user_message including the namespaced form; validated to catch the del_links-style shadow and to leave the current tree clean; (b) php_user_message_user_write_tests is a line scanner following php_web_config_from_cache_tests that flags a post-hoc $<msg>->usr = write on a message-named variable, skipping comment lines and the sanctioned writers (the user_message class files and web/frontend.php's login user switch); the http and api index.php entry points sit outside the scanned trees; while wiring this, one leftover post-hoc write in user::create_system_user was folded into the user_message constructor so the tree is clean)

All messages to the user should be transported via $msg. Check if there are any e.g. log_warning messages that should better be shown to the user. Use the function that does both in one (log and $msg enrichment if possible)

partly done — audited all 765 log_warning/log_err sites and 263 `= new user_message(` buffers (most are correct: internal-only diagnostics stay off $msg per coding.md 56-57, and ~230 buffers are legitimate per-item buffers that are merge()'d or is_ok()-checked). Fixed the clear, safe cases that need no new user-facing text:
  - user::can_be_changed_by (user.php ~1901) now adds msg_id::USER_NO_UPDATE_PRIVILEGES on the permission-denied branch instead of only log_warning (its sibling USER_MISSING branch already enriched $msg)
  - triple::import_obj (triple.php ~928) and phrase_list::import_lst (phrase_list.php ~588) import save-failures now use log_err_msg($txt, $msg) — the first real uses of the combined log+enrich helper (was 0)
  - triple::phrase_from_api_json (triple.php ~853) dropped its fresh shadow buffer and maps the nested from/to phrase into the threaded $msg, so an invalid nested phrase surfaces
  - user::create_system_user (user.php ~1734) now merges the $msg_sys profile buffer so a failed admin-profile grant at setup is not lost
also done (log_warning_msg + new msg_ids):
  - added log_warning_msg / log_warning_msg_ui (text_log_functions.php) — the warning parallel of log_err_msg; adds a generic user notice with a log link at ok=true (non-breaking) plus the technical log; note add_warning_with_vars(msg_id::X, ...) already does log+enrich for a specific warning
  - added msg_ids INTERNAL_WARNING (used by log_warning_msg) and BACKEND_OLDER_THAN_DB
  - wired: db_check.php ~143 backend-older now add_warning_with_vars(BACKEND_OLDER_THAN_DB) (added the msg_id include+use)
  - REVERTED the import.php 544-559 "not yet implemented" surfacing (and removed the IMPORT_PART_NOT_YET_SUPPORTED msg_id): these are standard export metadata sections (pod/time/selection/description/user/users) present in every export, so add_warning_with_vars piled six notices onto $msg on every import — it buried the genuine error in import_tests get_last_message ("Unknown element test") and would spam the user on normal round-trips; a known dev limitation belongs in the admin log (log_warning), not the user message
  - REVERTED the sandbox_named.php ~215 url_mapper surfacing back to log-only: url_mapper only checks url_var::NAME ('k'), but a form can carry the name under url_var::NAME_GIVEN ('kg') or identify an existing object by id, so add(MANDATORY_FIELD_NAME_MISSING) there is a false positive that leaked into the views_by_id snapshot HTML (broke system_view_ui_tests); the user-facing mandatory-name check stays in api_mapper (:143), which reads the canonical json name field
still open (need a decision, not just mechanics):
  - web/types/type_object.php ~211 is the twin of sandbox_named ~215 and has the SAME url_mapper false-positive (name may arrive under NAME_GIVEN or the object is identified by id) — do NOT surface it until the url_mapper name check accounts for url_var::NAME_GIVEN ('kg') and id-based edits; the proper fix is to make that check reliable, then surface a genuine no-name-at-all submit. cfg/group/group.php ~1156 group-description-not-saved (candidate for IMPORT_PART_NOT_YET_SUPPORTED-style notice or add_info)
  - buffers whose fix needs a signature change (thread a user_message param, each marked TODO Prio 1): web/group/group.php ~241 set_phrase_from_json_array (void), cfg/component/component.php ~496 formula_from_api_json plus its 5 siblings (742,775,808,841,873); constructors web/sandbox/db_object.php ~137 and web/sandbox/combine_object.php ~70 (optional ?user_message param)
  - leaf buffers to surface via the return value / is_ok() check: cfg/value/value_base.php ~2108 (job enqueue failure lost), cfg/import/convert_wikipedia_table.php ~196, web/element/element_group.php ~159
  - low priority: 7 sandbox base-class "missing override" stubs discard a throwaway $msg (should log_err/return is_ok()); legacy *_old methods; side lead cfg/word/triple_list.php ~874 calls get_ready() with no arg so IMPORT_TRIPLE_NOT_READY drops (formula_list.php ~891 passes a real buffer)

### change log table deterministic order (carry the change id through the api)

the change log table rows still swap between runs: two changes that fall in the same wall-clock second are ordered only by change_log_list::sort_by_time_and_what, whose tie-break is the what text (alphabetical), not the insertion order. e.g. the initial `added "System Test Word"` and the later `added description "..."` flip depending on whether they land in the same second - alphabetical puts the name-add above the description-add even though the description was written later. the whole-second bucketing added for test mode makes this worse because it deliberately collapses the sub-second difference, so the what-text tie-break decides every same-second group. the root cause is that the change entries carry no unique key: change_log::api_json_array (src/main/php/cfg/log/change_log.php ~683) sends action_id, table_id, field_id, row_id and change_time but not the entry's own change_id (change_log::FLD_ID), so the frontend change_log_named entries all have id 0 (see the "api change entries carry no own id" note in change_log_list::head, which is why add() is called with allow-duplicates).

fix: add the change id (change_log::FLD_ID) to change_log::api_json_array, map it into the frontend change_log / change_log_named via api_mapper (into $this->id), and change change_log_list::sort_by_time_and_what to order by the change id descending as the primary key (the change id is monotonic with the db insert, so it is the true insertion order and never collides), keeping change_time only for the display; the test-mode whole-second bucketing and the what-text tie-break then become unnecessary and can be removed, and change_log_list::head can drop the allow-duplicates flag once the entries have real ids so the id-dedup of add() works normally. write the test first: extend the change_word workflow write snapshot so the `added "System Test Word"` and `added description` rows keep the insertion order regardless of the wall-clock second. note the docs/llm/frontend.md deterministic-order rule and the still-open change_log_list TODO Prio 1 about the write order.

### change log table paging (implement the prepared forward/back buttons)

the change log table pure already renders a forward button (icons::PAGE_FORWARD) when the row limit is reached and has a prepared back button (icons::PAGE_BACK) in change_log_list::tr_page_nav; the buttons are only icons so far and do not navigate yet. implement the actual paging with the prompts below in order.

add a change log page url parameter (a new url_var const, '9'-prefix rules do not apply as this is not a back param) that carries the zero-based page number of the change log table pure; read it in http/view.php resp. the word/triple page controller and pass it down as an explicit parameter (never a superglobal) to ui_log::change_log_table_pure.

thread the page number from ui_log::change_log_table_pure into change_log_list::tbl_when_who_what as a new $page parameter (default 0) next to $max_rows; replace the hard coded $first_page = true with $first_page = ($page == 0) and compute $more_rows from the page window (the list has more changes than $page * $max_rows + $max_rows), so the back button shows from page 1 on and the forward button hides on the last page.

show only the rows of the current page: instead of head($max_rows) use an offset+limit slice of the sorted list (add change_log_list::page(int $page, int $max_rows) returning the $max_rows changes starting at $page * $max_rows, mirroring head()); keep the newest-first sort from ui_log::prepared_change_log. note that prepared_change_log currently head()s the list to the 'word changes' row limit (20) before the pure table is rendered, which would cap paging at those 20 rows - remove that head() for the pure table (or raise it) so the paging can reach all changes of the object.

turn the prepared icons in change_log_list::tr_page_nav into real links: the forward button links to the same page with the change log page url param increased by one, the back button decreased by one, built via html_base::url_new / ref like ref::refresh_job_link; add a msg_id + en/de translation for the 'next page' and 'previous page' tooltip and pass it as the ref title.

add a unit workflow snapshot test for the change log paging under unit_ui: render the change log table pure for page 0 (forward only), page 1 (back and forward) and the last page (back only) from a change log longer than two pages, and assert the correct buttons and the correct row window per page; extend src/test/resources/web/html/object_pages/sys_log.html accordingly.

### reduce response time

if no prepared cached page is found, repeat the previous page with a 'processing' message and 'processing since 1 second', 2, 3 ... up to the timeout limit 

include in the install.sh script the creation of a crontab job 

create a job that checks for some users (the number of users to check should be defined in the config.yaml) if the 'uses_sandbox' is still valid and if not switch off the flag and set the 'last_update' time so that always the least updated users are checked with the next job run

**4. Backend job: sandbox page generation**
> Implement a backend job (queue-based) that regenerates the sandboxed HTML for a given user+view+object. On completion, write the result to the cache store keyed appropriately for that user's sandbox context, and mark it available for the frontend to fetch on next poll. Include a check to avoid enqueuing a duplicate job if one is already in flight for the same key.

**5. Reactive cache invalidation**
> Implement reactive invalidation: when a request finds the cached HTML for a view+object is stale (define staleness check — e.g. compare object's last-modified timestamp to cache timestamp), trigger the backend regeneration job instead of serving straight from cache, following the same stale-serve-with-refresh-flag pattern as sandbox users.

**6. Proactive invalidation via object dependency index**
> Implement a reverse index mapping object_id → list of dependent view cache keys. When an object is updated, look up dependents and enqueue regeneration jobs for each. Add a configurable limit on the number of dependent views invalidated per object update in a single pass, to prevent fan-out overload for widely-referenced objects (e.g. batch/throttle beyond the limit).

**7. Frontend polling with backoff**
> Implement frontend polling for pages served with the "refresh coming" flag: poll for the updated page with increasing interval (define starting interval and growth factor), up to a configured maximum number of attempts/time limit. On limit reached, fall back to [describe your auto-user/IP-whitelist fallback mechanism here — needs your existing spec, since I don't have those details].

**8. Request logging**
> Add a lightweight insert into the request log table for each page request, capturing at minimum: user_id, view_id, object_id, timestamp, and whether served from cache or freshly generated/regenerated.

Fill the gap: Compare with the actual spec for your existing "auto user and IP whitelist fallback" mechanism, so prompt 7's fallback description is a placeholder — you'll want to fill that in from your existing plan before comparing.

add to the config.yaml the size and age limit for the cache tables and use it to clean up the cache if needed


### prepare denial-of-service protection

use the cache for read only pages

limit the number of read requests per time unit by the webbrowser and auto switch to ip whitelist mode

count the number of db write requests by user and ip

add to config.yaml the max age for each db_cache_type 

after each write to db_cache check if there are row older than defined in the setting and delete them using a prepared query

### denial of service test

The goal of this block is an end-to-end test that a flood of change requests first blacklists the abusing user and, if the abuse continues from more than one user, automatically raises the pod to user whitelist mode. The current protection is only the manual, file based whitelist in `src/main/php/web/server_guard.php` toggled from `http/server_admin.php` (state in `server_admin/state.json`); the automatic per-user rate limit, the database blacklist and the auto-switch to whitelist mode still have to be wired up. Build the prompts below in order: the first ones add the enforcement and the make-it-testable config knobs, the later ones are the actual DoS test that lowers those knobs, simulates the flood and asserts the reaction.

wire the existing `max change` config (config.yaml at `zukunft.com > system configuration > user > default > backend > max change > daily > ip user`, currently 1000 and only defined) to real enforcement: count the change requests a user has made in the configured period and reject a change that would exceed the limit. Add a second daily limit `logged in user` next to `ip user` so a registered user has its own threshold, and read both through the normal config accessor (the same path used for other `system configuration` values) instead of a hard coded number. Confirm no superglobals are read inside the enforcement method - the requesting user must be passed in as a parameter.

when a user exceeds its `max change` daily limit add that user to the database blacklist (the fallback that `server_guard.php` and `http/server_admin.php` already refer to as "the database based blacklist") and from then on reject every further change request from that user with a clear user_message that tells the user why the change was refused and how to contact the admin. Keep the blacklist in the database, not in the file based `user_whitelist.txt`, so the manual whitelist and the automatic blacklist stay independent.

add a new system configuration knob `user whitelist auto switch` = the number of distinct blacklisted users within the detection period that automatically activates the user whitelist mode (the same `user_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted users reaches this threshold, set `user_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to whitelist mode.

write the denial of service test itself (a dedicated test, e.g. under `src/test/php/` following the existing test structure, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step the test must lower the two knobs to test values and remember the previous values so it can restore them at the end: set the `max change` daily limit for `ip user` and for `logged in user` to a very low number (e.g. 2 changes per period) and set `user whitelist auto switch` to 2 users. Assert that reading the knobs back returns the lowered values before continuing.

in the same test simulate one user that sends more change requests than the lowered `max change` limit in a short period. Assert that after crossing the limit the user is added to the database blacklist, and assert that the next change request from that already-blacklisted user is rejected (the change is not stored and the returned user_message says the request was refused). The user whitelist mode must still be inactive at this point because only one user has been blacklisted (threshold is 2).

extend the test with a second user that also sends too many change requests in a short period. Assert that once the second distinct user is blacklisted the `user whitelist auto switch` threshold of 2 is reached and `user_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that an ip user (anonymous, not logged in) change request is now rejected by `server_guard.php` with the `optional/user_reject.html` reject page, and assert that the server admin page (`http/server_admin.php`) reports the user whitelist as active - check the rendered state that the page reads via `read_state()` / shows as "User whitelist: active".

as the final step of the test reset everything to the pre-test state: restore the `max change` daily limits and the `user whitelist auto switch` knob to their remembered default values, clear the two test users from the database blacklist, and switch the user whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle user whitelist` POST action in `http/server_admin.php`), leaving `user_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal change request from a fresh user succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

### distributed denial of service test

This block is the IP based sibling of the denial of service test above: instead of one logged in user flooding change requests, many different IP addresses each send too many requests in a short period, and the pod must blacklist each abusing IP and, once more than one IP is abusing, automatically raise the pod to IP whitelist mode. The existing pieces are the IP branch of `src/main/php/web/server_guard.php` (`ip_whitelist_active`, `server_admin/ip_whitelist.txt`, `ip_allowed()` with CIDR matching, `optional/ip_reject.html`), the IP toggle in `http/server_admin.php`, and the initial `ip_blacklist.json` (see `src/main/php/cfg/const/files.php::IP_BLACKLIST_FILE` and the `ip_ranges` test constants). The automatic per-IP request rate limit and the auto-switch to IP whitelist mode still have to be wired up. Build the prompts below in order, same as for the single-user test: enforcement and testable config knobs first, then the actual distributed test.

add a per-IP request rate limit: count the requests coming from one client IP (`$_SERVER['REMOTE_ADDR']`, passed in as a parameter, never read as a superglobal inside the enforcement method) within a configured period and reject requests from an IP that exceeds the limit. Add the knob to config.yaml next to the existing `max change` values, e.g. `zukunft.com > system configuration > user > default > backend > max requests > per minute > ip` for the raw request flood limit (distinct from `max change` which counts stored changes), and read it through the normal config accessor. Note that a DDoS is about request volume, not only stored changes, so this limit must also cover anonymous read/GET requests that never reach the change logic.

when an IP exceeds its request rate limit add that IP (or its /32 resp. /128 range) to the database IP blacklist that `server_guard.php` and `http/server_admin.php` already refer to as the fallback, reusing the same blacklist storage that `ip_blacklist.json` seeds. From then on reject every further request from that IP early in `server_guard.php` with `optional/ip_reject.html`, the same page an active IP whitelist uses for a non-listed IP. Keep the automatic IP blacklist independent of the file based `ip_whitelist.txt` so the manual whitelist and the automatic blacklist do not overwrite each other.

add a system configuration knob `ip whitelist auto switch` = the number of distinct blacklisted IPs within the detection period that automatically activates the IP whitelist mode (the `ip_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted IPs reaches the threshold, set `ip_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to IP whitelist mode. Note that activating an empty IP whitelist locks everyone out (the warning already emitted by `server_guard::warn_if_empty_ip_whitelist`), so on the auto-switch the current admin / server IPs must be seeded into `ip_whitelist.txt` first.

write the distributed denial of service test itself (a dedicated test under `src/test/php/`, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step lower the two knobs to test values and remember the previous values for restore at the end: set the per-IP `max requests` limit to a very low number (e.g. 2 requests per period) and set `ip whitelist auto switch` to 2 IPs. Assert that reading the knobs back returns the lowered values before continuing. Since the CLI has no real remote address, drive the requests through a helper that lets the test set the client IP per request (the same value `server_guard.php` reads from `REMOTE_ADDR`), so the test can simulate distinct source IPs.

in the same test simulate one IP address that sends more requests than the lowered per-IP limit in a short period. Assert that after crossing the limit that IP is added to the database IP blacklist, and assert that the next request from that already-blacklisted IP is rejected by `server_guard.php` with the `optional/ip_reject.html` page (403). The IP whitelist mode must still be inactive at this point because only one IP has been blacklisted (threshold is 2).

extend the test with a second, different IP address that also sends too many requests in a short period. Assert that once the second distinct IP is blacklisted the `ip whitelist auto switch` threshold of 2 is reached and `ip_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that a request from a further, non-whitelisted IP is now rejected with `optional/ip_reject.html`, that a request from an allowed IP in `ip_whitelist.txt` still passes, and that the server admin page (`http/server_admin.php`) reports the IP whitelist as active - check the rendered state it reads via `read_state()` and shows as "IP whitelist: active".

as the final step reset everything to the pre-test state: restore the per-IP `max requests` limit and the `ip whitelist auto switch` knob to their remembered default values, clear the test IPs from the database IP blacklist, and switch the IP whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle ip whitelist` POST action in `http/server_admin.php`; note that only a full-access admin may switch the IP whitelist off, restricted admins may not), leaving `ip_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal request from a fresh IP succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

## fine-tuning for next launch

add to the .env (and sample) parameter for the api to allow the cache (or deny) so that e.g. the api for the config just reads the env file checks the user / token and than returns the message from cache one-to-one. Review the debug call so that &debug=9 basically shows only these main steps

moved to [pending_next_launch.md](pending_next_launch.md) to keep this file small; see also [pending_fermi_live.md](pending_fermi_live.md)

### security with low prio

still open (deliberately skipped as too risky / too large to change safely without a test run):
Content-Security-Policy is not set - needs an audit of the frontend inline styles first (the other
headers are in place). [the api-write anti-csrf gap and the in_array method detection are now FIXED -
see "security before go live" 2026-07-21; the api write path is enabled with origin + X-CSRF-Token +
checked code_id setters, but was previously dead code so it needs an end-to-end write test before
production.] the bcrypt DUMMY_PW_HASH is pinned at cost 12 while real hashes use the runtime default
cost - equal on
php 8.4 (default 12) but diverges on any php where the default is 10, re-opening the timing oracle the
dummy prevents; derive the dummy from the same cost (skipped: touches auth timing, wants a careful
test). save_user() has no general can_change() gate (safe today because no write caller passes an
attacker-influenced target, latent defense-in-depth; skipped: a guard risks breaking a normal user
saving their own profile without a test run). api/job (api/job/index.php) and api/changeLogList are
gated only by `id > 0` and leak another user's job / change-history metadata; api/job wants an owner
filter (skipped: job ownership model unclear), api/changeLogList is largely intended for the public
graph (needs an explicit access decision). login and the password-reset email have no throttle -
covered by the planned rate limiter, ensure it also bounds the reset endpoint. informational
(injection review): sql_par_field_list::par_sql() (line 785) builds inline unescaped sql but only into
`$qp->call`, the documented never-executed sample string - add a guard/comment so it is never routed
into exe(); finish deprecating sql_db::sf() in favour of bound parameters.

### Prio 2

allow at least admin users to overwrite the impact and usage via GUI 

add to /docs/llm/* that instead of "is instance of" a const array should be used for a more specific selections
