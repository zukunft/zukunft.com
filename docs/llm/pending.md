# pending.md

## list of planned llm prompts

## high prio

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible. 

### security before go live

findings of the security check on 2026-07-17, ordered by exploitability. all items of that
round are fixed (fix #334 parts 1-18: .htaccess allow-list, docker exposure, config cache as
system user, csrf token on action masks, session regeneration, reflected xss escaping,
html_base::ref href escaping, sudoers pinning + setup gate, signup enumeration trade-off,
sys_log write throttle, protection-level raise gating for the seq-id branch).

findings of the re-run security check on 2026-07-19, ordered by exploitability. the six reviewed
surfaces were entry points/routing, auth/session/passwords, injection sinks, frontend xss output,
access control, and config/secrets/deployment. injection is clean (sql parameterised end to end,
the single exec sink pinned, no eval/unserialize/xxe). the items below are the confirmed findings,
each verified against the code (file:line evidence in the description).

[FIXED 2026-07-19] the api debug parameter is not environment-gated (info disclosure, medium):
api/api_const.php line 36 sets `$debug = $_GET['debug'] ?? 0` directly from the request, while the
frontend entry points http/const.php and http/setup.php were hardened in fix #334 to stage the
requested level and honour it only in ENV_DEV. every api endpoint includes api_const.php, so an
anonymous request such as `GET /api/word/index.php?id=1&debug=11` echoes the live sql, table/column
names and the internal call graph into the response in production (log_debug echoes when
`$debug >= min_level`). fixed by mirroring the http/const.php pattern in api_const.php: read
`$debug_requested`, keep `$debug = 0`, then apply it after init.php loads the env only
`if (getenv(ENVIRONMENT) == ENV_DEV)`. (found independently by two reviewers.)

[FIXED 2026-07-19] stored xss in several frontend render paths that emit a user-controlled
name/value without escaping (high). the frontend keeps names raw at the source and escapes per
render site (e.g. word.php h1 uses esc(), ui_base::dbo_name escapes), so the sites that forget the
escape were live holes: (a) the page `<title>` - html_base::title() (html_base.php line 2552) wrapped
the title raw; it is fed the raw object name + view name via view_base::title() and frontend.php line
969, so a word/view renamed to `</title><script>...</script>` executed in the head for any viewer;
(b) the related-views tab - ui_list.php line 551 emitted `$msk->name()` raw into a div; (c) formula
expression and latex - formula.php user_expression() (line 529) escaped only the double quote, and
the latex path (expression_latex / expression_latex_link) emitted the user latex raw into a span, so
`<script>`/`<img onerror>` in the expression or latex fields ran on the formula page; (d) text values
- html_base::span() (line 609) emits its body raw and value.php lines 402/470/485 passed the raw text
value through it. fixes: title() now esc()s the title; ui_list.php escapes the view name; formula
user_expression() uses full htmlspecialchars and both latex methods escape the user latex before the
latex->html transforms (the term-link html stays trusted); web value::value() now escapes its text
and time branches at source so it returns uniformly display-safe html, and its three ref() callers
pass name_is_html=true to avoid double-escaping (which also fixes a latent bug where a non-standard
number span was being escaped by ref). span()/div() were left as generic html containers (they
receive already-composed markup, so escaping their body would double-escape). two more sinks of the
same class were found and fixed while verifying: ui_list::children_of_word (the h2/h4 header built
from the raw verb and phrase name, dsp_text_h2 emits raw) and ui_list::link_list_word (raw
`$dbo->name()` into the component arm output).

[FIXED 2026-07-19] the part-14 protection-level gating was missing on the value/result branch
(privilege escalation / self-lock, medium-high). check_protection_change was called only from the
seq-id branch (sandbox.php lines 2810/2909 and sandbox_list_named.php); the value/result save path -
value_base::save() (value_base.php line 2183) - never called it, yet protection_id is fully
user-settable there (sandbox_multi::api_mapper line 428). a normal user could save a value with
protection set to admin and self-lock a shared value above their own privilege, the exact scenario
the part-14 fix prevents for words/triples/etc. fixed by adding a parallel check_protection_change to
sandbox_multi (the value/result branch shares no parent with sandbox, so the protection api is
duplicated there like protection_type_name() and can_change() already are - the two branches are
intentionally parallel), and calling it in value_base::save on both the new-object and the update
path, mirroring the seq-id wiring (warning-only, the save proceeds with the protection limited to
what the user may set). added a `protection` unit-test block to value_tests.php mirroring the
word_tests protection tests (normal user cannot reduce, cannot raise to no-change, cannot set admin
on a new value; admin can; denials reported, admin changes not).

[FIXED 2026-07-19] the sysLogList api returns the full system error log to anyone, including
anonymous (info disclosure, medium): api/sysLogList/index.php line 67 gated only on `$usr->id > 0`,
which every anonymous ip-user satisfies, then load_all() forces DSP_ALL and returns every row
including sys_log_trace (internal code paths), text and description - data the frontend only ever
exposes to admins. fixed by requiring `$usr->is_admin() or $usr->is_system()` before loading (else a
'not permitted' message), matching the api/user pattern.

[FIXED 2026-07-19 for values, results and figures; scoped] no share-based read enforcement - idor
read of another user's private object by id (medium, an unimplemented feature rather than a
regression): share_id (public/personal/group/private) is stored and round-tripped but no read path
filtered on it. the object creator owns the standard row, so a value created as private is the
standard row; another user with no overlay got it verbatim on `load_by_id`, e.g. `GET
/api/value/?id=N` iterating ids. fixed for the confidential data - values, results and figures (the
sandbox_multi branch; share's own doc is "value can be seen and used by everyone", and the app is a
public word/triple graph so words/triples are shared vocabulary, not private): added
is_readable_by(user) on sandbox_multi (public/default or owner or admin/system -> readable;
private/personal/group owned by another normal user -> not readable), inherited by value and result;
filter_readable_by(user) on sandbox_value_list (shared by value_list and result_list); and
figure::is_readable_by (delegates to the underlying value/result) + figure_list::filter_readable_by.
enforced at the untrusted api read boundary: api/value and api/figure (single, neutral 'missing id'
so existence is not confirmed), api/valueList, api/resultList and api/figureList (list filtered).
internal flows (save, calculation) are deliberately not gated so cross-user aggregation of public
data keeps working. unit tests added to value_tests.php (owner/other/admin read, public read, list
filter) and figure_tests.php (figure delegation). remaining as follow-up: (a) a real user-group
membership model - the group share is conservatively treated like personal (owner only) until then;
(b) if words/triples ever need private sharing, mirror is_readable_by on the seq-id sandbox branch.
related lower-severity siblings gated only by `id > 0`: api/job (api/job/index.php line 64, load_by_id
with no owner filter - reads another user's job metadata) and api/changeLogList
(api/changeLogList/index.php line 71, returns any object's change history; intended for the public
graph but combines with this finding to expose private-object history).

[FIXED 2026-07-19] the top-level test/ tree was blocked only by its own nested test/.htaccess, with
no central backstop (medium, deployment): every other sensitive dir is also blocked in the root
.htaccess as defense-in-depth (^/vendor/, ^/server_admin/, ^/script/, ^/cache/, ^/http_old/, ^/.git),
but test/ had no root-level RedirectMatch and most scripts (test.php, test_unit.php, test_workflow.php,
sync_sql_sequences.php, ...) lacked the `PHP_SAPI !== 'cli'` guard that reset_db.php has. if the
nested .htaccess were ever not deployed or .htaccess stopped being honoured, `GET /test/test.php`
would run the full suite and reset the database against a live pod. fixes: added `RedirectMatch 404
^/test/` to the root .htaccess; added the `PHP_SAPI !== 'cli'` guard (403 + exit) to the remaining
ten test/*.php entry scripts, including test_const.php which every runner includes as its first step;
and added a vhost-level `<Directory /var/www/html/test>` deny in docker/apache-config.conf so the
folder is blocked even if .htaccess is not honoured, mirroring the existing .git backstop.

lower-severity / hardening items: api/auth.php is a broken stub - it reads Basic Auth credentials
into locals and never uses them, and calls send_auth_request() (line 60) which is defined nowhere, so
an unauthenticated request without an Authorization header fatals; implement it or remove it. the api
write path has no anti-csrf check (api/word/index.php / api/controller.php) but is currently
unreachable dead code because the method-detection uses `in_array(REQUEST_METHOD, $_SERVER)` (should
be array_key_exists), so every request falls through to GET; close the csrf gap in the same change
that fixes the method detection and enables writes. .env.example line 53 commits a real admin source
ip (94.130.31.152) rather than a placeholder - replace with a documentation ip. docker/Dockerfile
pins php 8.2 while CLAUDE.md and install.sh require 8.4 - align the base image. the main app sets no
Content-Security-Policy / X-Frame-Options / X-Content-Type-Options headers (small surface since web/
emits no javascript, but nosniff + frame-ancestors are cheap wins). the bcrypt DUMMY_PW_HASH is
pinned at cost 12 while real hashes use the runtime default cost - equal on php 8.4 (default 12) but
diverges on any php where the default is 10, re-opening the timing oracle the dummy prevents; derive
the dummy from the same cost. save_user() has no general can_change() gate (safe today because no
write caller passes an attacker-influenced target, latent defense-in-depth). login and the
password-reset email have no throttle - covered by the planned rate limiter, ensure it also bounds
the reset endpoint. informational (injection review): sql_par_field_list::par_sql() (line 785) builds
inline unescaped sql but only into `$qp->call`, the documented never-executed sample string - add a
guard/comment so it is never routed into exe(); finish deprecating sql_db::sf() in favour of bound
parameters.

### security improvements

add TOTP authentification for SERVER_ADMIN2 and 3, so that the first login can be done with the pure user name and password and than a page shows the QR code e.g. for an App like FreeOTP+ to add a second factor

### reduce response time

Add non-interactive backend job execution to zukunft.com using systemd.

Context: read CLAUDE.md and docs/llm/ first. The goal is that periodic
backend tasks (cache refresh sweeps, database cleanup) run without user
interaction on a Debian-based server, installed automatically by
install.sh via systemd service and timer units. Reactive cache updates
triggered by a user request are out of scope for the scheduler and must
not be moved into it.

Tasks:
1. Create a single CLI dispatcher entry point (e.g. bin/job_runner.php)
   that:
    - refuses to run via a web request (CLI check),
    - reads pending jobs from the existing job/batch_job structure (extend
      the table if needed with: job type, status, priority, scheduled_at,
      started_at, finished_at, last error message),
    - executes due jobs in priority order with a per-run time budget,
    - logs start, end, and errors of each job through the existing logging
      mechanism, writing to stdout/stderr as well so journald captures it.
2. Implement two initial job types: proactive cache refresh sweep and
   database cleanup. Keep each job type in its own class implementing a
   common job interface.
3. Add two systemd unit files to the repo (e.g. under deploy/systemd/):
    - zukunft-jobs.service: Type=oneshot, ExecStart runs the dispatcher
      with the PHP CLI binary, User= set to the web/app user, a sensible
      WorkingDirectory, and basic hardening (ProtectSystem=full,
      PrivateTmp=true, NoNewPrivileges=true);
    - zukunft-jobs.timer: OnCalendar=minutely, RandomizedDelaySec=10,
      Persistent=true, Unit=zukunft-jobs.service.
      Because the service is Type=oneshot, systemd itself prevents
      overlapping runs; no flock is needed.
4. Extend install.sh:
    - copy both unit files to /etc/systemd/system/,
    - run systemctl daemon-reload,
    - enable and start zukunft-jobs.timer,
    - make these steps idempotent so re-running install.sh is safe,
    - fail with a clear message if systemd is not present (e.g. inside a
      container) instead of silently skipping,
    - add the matching disable/remove steps to the uninstall path if one
      exists.
5. Document in the README or install docs how to check job status
   (systemctl status zukunft-jobs.timer, journalctl -u zukunft-jobs).
6. Add PHPUnit tests for the dispatcher's job selection logic (due vs.
   not due, priority order, error handling) using the existing test
   structure.

Coding rules: single-exit functions using a $result variable, one logic
step per line, follow the existing naming and documentation conventions
in the codebase. Do not reformat unrelated code.

Before writing code, present a short plan listing the files you will
create or change, and wait for confirmation.


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
