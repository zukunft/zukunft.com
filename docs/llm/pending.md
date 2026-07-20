# pending.md

## list of planned llm prompts

## high prio

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible. 

### security before go live

findings of the re-run security check on 2026-07-20, ordered by exploitability. six read-only review
surfaces: an adversarial re-audit of the recent fixes (parts 16-21), auth/session/crypto/csrf,
injection/files/deserialization/ssrf, xss across the whole web/ render layer, access control/idor
across all endpoints, and config/cache/dos/business-logic. each finding below was verified against the
code (file:line evidence in the text). the previously fixed items (parts 1-21) and the already-listed
"security with low prio" items were excluded. two of these are gaps in the share-read fix itself.

ALL FIXED 2026-07-20. summary of the fixes (each finding paragraph below is kept for the audit trail):
- sys_log display xss: web/system/sys_log.php now esc()s text/description/trace/user_name/owner_name
  in display(), page_view(), display_admin() and get_html() before td()/body concat.
- word/triple embedded value idor: word.php and triple.php now call values_related->filter_readable_by($usr)
  before api_json_array under INCL_RELATED.
- admin->system escalation: user::can_set_profile() now blocks assigning TEST and LOG as well as SYSTEM
  (all three make user::is_system() true).
- named-object idor read: added sandbox::is_readable_by (seq-id branch), phrase::is_readable_by and
  term::is_readable_by (delegating), and sandbox_list_named::filter_readable_by; gated the object
  endpoints (word/triple/phrase/formula/source/reference/component/view/group) and filtered the list
  endpoints (wordList/formulaList/viewList/componentList/sourceList/termList/phraseList) with a neutral
  'missing id' message; group was already covered via sandbox_multi. read-access unit tests added to
  word_tests.php (owner/other/admin/public).
- file-cache path traversal / poisoning: config_numbers::cache_file() now keys by the integer user id,
  not the raw name; plus a signup deny-list (frontend.php action_signup) rejecting a user name with a
  slash, backslash or control character (new msg_id SIGNUP_ERR_NAME_INVALID + en/de translations).
- is_admin_local() TypeError: single return at the end (user.php).
- error.log / test/error.log: git rm --cached + added to .gitignore.
- name_tip() overrides: source.php, ref.php, language.php now htmlspecialchars the name/external_key.
- dev debug scripts: src/test/php/dev/test_js.php and test_jquery.php now ENV_DEV-gate the url debug level.
- zip-slip: import_convert_xbrl.php validates every entry path before extractTo (defense-in-depth).
the informational/by-design items (api csrf fail-open, reset key in url, broken api/json export param)
were left as documented.

stored xss in the system error-log display page (high, admin compromise): web/system/sys_log.php lines
350-352 (get_html), 245 (display), 262-268 (page_view) and 293-294 (display_admin) pass the raw sys_log
`text`, `description` and `trace` into html_base::td() / the html body, and td() (html_base.php) emits
its cell body unescaped. these fields routinely embed user-supplied strings - e.g. html_base::ref()
calls log_warning('link to "' . $url . '" is blocked ...') with the raw user source/ref url
(html_base.php ~513), and many log_err/log_warning messages embed object names - so a user who sets a
source/ref url (or a name) to `"><img src=x onerror=...>` stores a sys_log row whose text carries the
payload; when an admin opens the error-log page the payload executes in the admin session. this is a
different code path from the already-fixed backend log_err/critical_error_html echo (that one escapes;
the frontend sys_log renderer does not). fix: esc() each field before td()/body concat, as
change_log_named.php and change_log_link.php already do.

private value idor still open on the word/triple pages (high; a gap in the part-20 share-read fix):
the filter_readable_by() gate was applied to the five direct value/result/figure endpoints but not to
the same values embedded in a word/triple api response under api_types::INCL_RELATED. word.php ~543 and
triple.php emit values_related->api_json_array(...) with no filter (verified: filter_readable_by is
called only from the five api controllers, and load_by_phr / load_sql_by_phr_lst_multi add no
share/owner where). so user A opening a public word that user B attached a private/personal value to
receives B's value (number, group phrases, share flag) in the `values` array - the same idor closed on
the direct endpoints, still open on the primary viewing path, no id guessing needed. fix: apply
filter_readable_by(session_user) to values_related (and the triple's) before api_json_array; check
against the session user, not the object's get_user().

admin can escalate to full system privileges via the TEST/LOG profile (medium-high; admin-tier
requester, self-escalation): user::is_system() (user.php ~2563-2566) is true for the TEST, LOG *and*
SYSTEM profiles, but can_set_profile() (user.php ~1902-1906) only forbids an admin from assigning the
SYSTEM profile - the broader check `// if (!$profile->is_system())` is commented out and replaced by
the narrower `!$profile->is_type(SYSTEM)`. so an admin may assign TEST or LOG (to themselves via
can_change, which permits an admin to change their own params); that account then satisfies is_system()
everywhere - can_change any user incl. real system users, can_set_profile all profiles incl. SYSTEM,
admin_mask_denied, reserved-name usage - defeating the intended admin↛system boundary. fix: block TEST
and LOG as well as SYSTEM in can_set_profile (i.e. exclude every profile for which is_system() is true),
or implement the intended numeric right_level comparison the enforce_profile_privilege TODO describes.

idor read of private named sandbox objects by id (medium; same class as the value fix, seq-id branch
not covered): is_readable_by() exists only on sandbox_multi (value/result) and figure, not on the
seq-id sandbox branch, so api/word, api/triple, api/phrase, api/formula, api/source, api/reference,
api/component, api/view, api/group (and the *List endpoints via load_by_ids) do load_by_id ->
api_json with only the `id > 0` gate that every anonymous ip-user passes. these tables all carry a
standard-row share_type_id "to restrict the access" but no load path filters on it, so an object a
user set private/personal is returned in full (name, description, expression, url) to anyone iterating
ids. severity medium because these graph objects are usually deliberately public; the confidential
case leaks. fix: add is_readable_by to sandbox/sandbox_named (share public OR owner OR admin/system)
and gate the object endpoints + filter the list load_by_ids, mirroring the value fix.

path traversal / cache poisoning via an unvalidated username in the file config cache (medium,
config-gated): config_numbers.php::cache_file() (~605) builds a filesystem path by concatenating
$usr->name() (paths::CACHE . CACHE_CONFIG . SEP . name . .json) with no sanitisation, and there is NO
username charset validation anywhere - verified: no preg_match/basename/ctype in user.php and
frontend.php ~1428 assigns the raw signup username directly. with CACHE=file a user who registers a
name like `x/../../../var/www/html/http/pwn` makes write_file_cache() write attacker-influenced json
outside cache/, and a name containing `/` poisons another user's config cache. precondition CACHE=file
(default and .env.example use CACHE=database, which keys by integer type_id+user_id via bound sql and
is safe), hence medium. root cause - the missing username charset allow-list at signup - is worth
fixing regardless of cache mode. fix: derive the cache filename from the integer user_id (as the db
cache does) or hash()/basename() the name, and add a username charset allow-list at signup.

lower severity: is_admin_local() (user.php ~2535-2544) puts its `return $result;` inside the
is_admin() branch, so a non-admin falls off the end and the `: bool` return type throws an uncaught
TypeError (a php fatal on a permission path, e.g. type_object.php ~1087 when a normal user deletes a
used type) - fails closed by crashing but violates the no-fatal rule; fix: single return at the end or
`return $this->is_admin() && $this->ip_addr == 'localhost';`. error.log and test/error.log are
git-tracked runtime log files (currently 0 bytes) that the app appends to (the sys_log throttle note,
fatal errors) - a future commit could capture logged internal paths / request strings; fix: git rm
--cached and add to .gitignore like the server_admin state files. three name_tip() overrides return
the raw name instead of the esc()'d base contract - web/ref/source.php ~207, web/ref/ref.php ~428
(external_key is user input), web/system/language.php ~125 - latent xss: the primary render arms go
through ui_base::dbo_name() which escapes, but a generic name_tip caller in a list would render raw;
fix: escape in the overrides or route through the base. two dev-only harnesses set $debug from the
request ungated - src/test/php/dev/test_js.php ~34 and test_jquery.php ~35 - not deployed (the three
production bootstraps are correctly ENV_DEV-gated), low; add the same gate for consistency. zip-slip in
import_convert_xbrl.php ~207 (ZipArchive::extractTo of `..` entry names) is not attacker-reachable
today (test/cli-only tool, no web upload / $_FILES path exists); validate entry paths if a web import
is ever added.

informational / by-design (not counted as findings): the api write path's same-origin csrf check
fails open when both Origin and Referer are absent, but a browser cannot be coerced into omitting both
on a cross-origin state-changing request (browsers always send Origin on cross-site post; `Origin:
null` is correctly rejected), so it is not browser-exploitable - it only allows genuine
server-to-server calls, the documented intent; add a token/Sec-Fetch check if cookie auth is ever added
to the api. the reset/activation key travels as a url query parameter (frontend.php ~1673) so it can
land in access logs / history, mitigated by sha256-at-rest, one-time use, 1h expiry, 80-bit entropy and
the Referrer-Policy header - low, a post-based activation form would avoid it. api/json/index.php ~995
hard-codes $wrd_id = 1 overriding the request param (a broken export feature, ignores attacker input -
not a security issue). the DUMMY_PW_HASH cost coupling and the no-login/reset-throttle items remain as
already listed under "security with low prio".

### security improvements

add TOTP authentification for SERVER_ADMIN2 and 3, so that the first login can be done with the pure user name and password and than a page shows the QR code e.g. for an App like FreeOTP+ to add a second factor

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
headers are in place). the api write path has no anti-csrf check (api/word/index.php /
api/controller.php) but is currently unreachable dead code because the method-detection uses
`in_array(REQUEST_METHOD, $_SERVER)` (should be array_key_exists), so every request falls through to
GET; close the csrf gap in the same change that fixes the method detection and enables writes. the
bcrypt DUMMY_PW_HASH is pinned at cost 12 while real hashes use the runtime default cost - equal on
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

