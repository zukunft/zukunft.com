# pending.md

## list of planned llm prompts

## high prio

for testing use always the users of the test environment e.g. $t->usr1, ... and never any global user like global $usr

the http entry point like /http/view.php should set the user_message $msg with the requesting user and this should be used in all functions as a parameter

add to the test set used for the borderless change log table here ( src/test/resources/web/html/object_pages/sys_log.html ) a fey more rows including a phrase type change, a description change and a protection type change.

if in the borderless change log table another field than the prime key e.g. the name is changed show the translated name of the field before the changed value e.g. 'added description "ISO 4217 alphabetic code for the ...' . and if the char limit is used, indicate with '...' that there is more. Show the full change text as mouseover popup.

if in the borderless change log table a type field is shown, display the type name instead of the type number

in the borderless change log table use for the username the linked version so that a click on the username shows the user default page

if a session token is not valid any more and there is an indication that a non ip user has been logged in, show the login page with the url as back page. if there is no hind that the user has been logged in or the user has been an ip user, just create a new token and show the page for the url again if permitted  

if a user logs in make sure that always the last used ip address is saved in the user table

check that the login page does not $_POST the unhashed password

find all '&back=' url parameters and list here the prompts to fix these issues by using instead the url_var::BACK prefix

roll out the own-pod data user trust to the remaining read api endpoints: api/word/index.php now passes server_guard::from_own_pod() to user::data_user so the html frontend's server-to-server read call can load the object with the browsing user's sandbox overlay (this fixed the word description changed by a user not being shown in the word and edit views); apply the same one-line change (and the is_readable_by check against $load_usr instead of the session user) to api/value, api/triple, api/formula, api/view, api/component, api/source, api/reference and api/group so user overlays and private objects render correctly for all object types

fix the user type and status export/import round trip: the export writes the type display name under json_fields::TYPE ('type_id', see user::export_json using type_name()) but import_mapper reads json_fields::TYPE_NAME ('type'), so an exported type is silently ignored on import and the guest default fills it (the unit fixture user_import.json only passes because its value "Guest" equals the default); additionally set_type expects the code_id ('guest') while the export writes the name ("Guest"), so even with matching keys the value would not resolve (user_type_list has usr_can_add = false); decide whether the export switches to the code id under the 'type' key (json format change -> minor version raise and db_check upgrade script per docs/llm/versions.md) or the import accepts both; the status has the same name-vs-code-id issue (status_name() exported, usr_sta->id() on import)

fix the last violation of the default-value rule (docs/llm/constants.md "Default values are resolved at the point of use, never fabricated in a mapper"): user::api_mapper still fabricates user_profiles::NORMAL_ID for a missing PROFILE_ID (cfg/user/user.php ~515) and the profile branch in db_fields_changed (~3653) has no null guard, so a json-born user without the profile field saved by an admin requester silently demotes the stored profile to normal (enforce_profile_privilege only blocks unprivileged requesters, an admin passes can_set_profile(normal); the frontend api_array omits PROFILE_ID unless is_profile_valid()). Apply the same treatment as for the type and status: map a missing profile to null in api_mapper, add the !== null guard in db_fields_changed, let enforce_profile_privilege treat null as "keep stored" (its int $req_profile_id parameter needs ?int or an early guard), and add the matching negative and positive tests to the "preserved on save" blocks in src/test/php/unit/user_tests.php; also backfill profile_id in the user exception block of horizontal_ui_tests.php like type_id and status_id, because the add url does not transport the profile and the round trip only passes today because the fabricated normal profile matches the filled test user

cosmetic alignment with the default-value rule (no data-loss risk, round trip is safe): web/user/user.php api_mapper defaults profile_id, type_id and status_id to 0 instead of null (api_array drops them via the >0 and is_profile_valid() checks) and web/component/component.php api_mapper sets pos_type_id to position_types::DEFAULT_ID (the property is declared non-nullable with that default and api_array suppresses the default), so the frontend mirrors cannot express "not specified"; align them to nullable properties mapping to null when the field is missing

fix as many TODO Prio 0 as possible

create the user_message $msg at the entry point and add it as a parameter to all function that might create a message that needs to be shown to the user

create an admin view with the system errors 

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible. 


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
