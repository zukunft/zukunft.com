# pending.md

## list of planned llm prompts

## high prio

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible. 

add to the .env (and sample) parameter for the api to allow the cache (or deny) so that e.g. the api for the config just reads the env file checks the user / token and than returns the message from cache one-to-one. Review the debug call so that &debug=9 basically shows only these main steps

### security before go live

findings of the security check on 2026-07-17, ordered by exploitability.

fix the docker deployment exposure: docker-compose.yaml bind-mounts the whole repo into the docroot (so .env with the real db password, .git and the /src tree all live inside the web root) and docker/apache-config.conf sets 'Options Indexes' with 'AllowOverride All', so only the root .htaccess prevents listing and download. also adminer is published on :8081 reachable with the db login whose compose default is zukunft/zukunft. mount .env one level above the docroot (like the debian install), set 'Options -Indexes' in the base vhost, bind adminer to 127.0.0.1 and drop it from any shared/prod compose, and never default the db password

close the allow-by-default file exposure in .htaccess: composer.json / composer.lock, the stale package.xml, and every *.json / *.csv / *.ini under src/main/resources and cache/ are web-fetchable because only .sh/.sql/.yaml/.yml/.md/.log and dotfiles are blocked. todays files hold no secret but the model is fragile - a future secret dropped as .json/.ini would be silently served. switch to an allow-list (serve only the image and style extensions) or move src/main/resources and cache out of the docroot; at minimum deny composer.json/lock and .xml and delete package.xml

gate raising the protection level by privilege: sandbox::check_protection_change (cfg/sandbox/sandbox.php around line 1345) only refuses a protection REDUCTION, so a normal user can set admin / full protection on their own objects via the mapped protection_id (sandbox::api_mapper around line 408), and on a new object (post, no db_obj) there is no check at all - a user can self-lock objects so only an admin can touch them. also gate the target protection level by requester privilege (only admin / system may assign admin / full) for both the increase and the new-object case

escape the href in html_base::ref (web/html/html_base.php around line 494): ref() escapes the title but emits href="' . $url . '"' and the link text raw. internal callers pass safe int-built urls today, but any caller passing a user-supplied source / reference url yields attribute-context injection or a javascript: uri. htmlspecialchars($url, ENT_QUOTES) on the href, whitelist the scheme, and escape the link text unless the caller guarantees it

throttle the sys_log write amplification (dos): text_log_functions.php::log_msg inserts a sys_log row per distinct error and the dedup at around line 415 is per-request only, so a flood of distinct malformed requests grows sys_log unbounded and turns each request into one or two db writes. cap or rate-limit the sys_log inserts per time window (part of the planned rate limiter)

pin the sudoers command and tidy the setup gate (defense in depth): the rule created in script/install.sh (around line 303) is 'www-data ALL=(root) NOPASSWD: $ADMIN_SCRIPT' with no argument restriction, so any code-exec as www-data escalates to root through server_admin.sh; pin the exact allowed sub-commands (update-program, upgrade-database). also http/setup.php is gated only by getenv('ZUKUNFT_ALLOW_SETUP')=='1' which is fail-closed today, but install.sh never blanks it and the code comment wrongly claims it is unset after install - add it to the post-setup blanking and fix the comment

reduce the signup username enumeration: action_signup (web/frontend.php around line 1153) returns a distinct SIGNUP_ERR_NAME_EXISTS when the name is taken, which lets an attacker probe which usernames exist - inconsistent with the neutral reset flow. keep the hint only if the ux needs it, otherwise use a generic message and/or rate limit; at least accept it as a conscious trade-off

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

moved to [pending_next_launch.md](pending_next_launch.md) to keep this file small; see also [pending_fermi_live.md](pending_fermi_live.md)
