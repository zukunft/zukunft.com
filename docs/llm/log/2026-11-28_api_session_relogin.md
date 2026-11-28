# api session check and relogin hint (2026-11-28)

The tasks "api security" and "relogin" of `docs/llm/pending.md` (the agent security of the prompt "calculation editor
messages, code rules and api security").

## 1. the test of the session check and the login link of the calculator editor

### Request

"do the "api security" and "relogin" (session check test and the relogin hint)":
- "api security": "if it does not yet exist create a test that checks if an api request is rejected if it is not part
  of a previous created session"
- "relogin": "if the calculation editor gets a message from the backend that might indicate that e.g. the session id
  is missing or invalide suggest to the user to login again with a link in the message shown to the user"

Checked before the first edit:
- every api request starts a php session (`application::start_api`), so a request without the cookie of a session
  gets a new session with a new token; a change (post, put, delete) is refused by `controller::change_permitted` if
  the X-CSRF-Token header does not match the token of the session (`server_guard::csrf_token_valid`), with the message
  `CHANGE_BLOCKED_SESSION_TOKEN` and the http status 401 (`controller::not_permitted`)
- a read (get) is allowed without a login, because the data of a pod is public
- tested so far: the token check of the calculator pdf script over http (`api_tests::run_calculator_pdf`) and the user
  check of a simulated api write (`test_api`), but no change of the api over http without the session or its token
- the calculator editor sends its changes to `api/updatePut` and its import to the import api with the token of the
  page (`apiCall`), and shows a refusal with `calc_editor_save_refused` and `calc_editor_import_failed`; its unsaved
  changes are kept as a draft in the browser (`autosave`), so a login in between loses nothing
- the login url names the pinned id of the login view (`api::LOGIN_SCRIPT`), so the script gets it from the page like
  its texts; the navbar adds the page as the back target of the login (`html_base::url_with_back`)

Planned:
- not in this change: the predefined api session login with a key pair of the second paragraph of "api security",
  which is a design of its own
- `test_base`: the new `delete_with_session` sends a delete with or without the session cookie of `api_login`
- `api_tests` (new `run_session_check`): the delete of `api/word/0` is refused without a session, with the session
  but without its token and with the token of another session (negative); with the session and its token the request
  passes the session check and ends with the missing id, so no data is changed (positive)
- `system_page::calculator_editor` embeds the login url with the editor page as its back target (new
  `CALC_EDITOR_LINKS_ID`), which `component_exe` gets the url of the page for
- `script/calculator_editor.js`: a refusal with the status 401 of the save and of the import shows below the reason
  the new text `calc_editor_relogin` with a link `calc_editor_relogin_link` to the login
- `use_case_ui_tests`: the editor embeds the login url with the page as its back target (positive) and without the
  url of the page the plain login url (negative); in the headless browser a save refused with the status 401 shows
  the login link (positive) and a save refused with 400 none (negative); the scenario adds the links of the page if
  the snapshot is older than this change

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/test/php/utils/test_base.php` (new `delete_with_session`), `src/test/php/unit_api/api_tests.php` (new `run_session_check`, `HTTP_UNAUTHORIZED`) | — | a change of the api without a session or its token is refused | "api security" |
| `src/main/php/web/component/execute/system_page.php` (`calculator_editor`, new `CALC_EDITOR_LINKS_ID` and `CALC_EDITOR_LOGIN`), `src/main/php/web/component/component_exe.php`, `src/main/resources/script/calculator_editor.js` (`save`, the import, new `reloginHtml`), `src/main/php/shared/enum/messages.php`, `en.yaml`, `de.yaml` | a refusal shows its reason | and for the status 401 a link to the login with the editor as the way back | "relogin" |
| `src/test/php/unit_ui/use_case_ui_tests.php`, `src/test/js/calculator_editor_scenarios.js` (new `saveAnswered`, the scenarios `save_session_refused` and `save_refused`, the wait for a scenario that returns a promise), `src/test/php/const/files.php`, `docs/llm/pending.md` | — | the test of the login url and of the link of a refused save in the headless browser; the two tasks removed, the key pair login kept as open | the tests and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/calculator_editor.html` | — | the script data of the login url | the editor embeds it; written by the auto update |
| the ids | — | unchanged | no import file changes, so no re-pin |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | the new functions | generated reports |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `docs/code_user_message_exceptions.md` | `system_page.php:2262` (the `$msg` parameter default of the component render) | `system_page.php:2271` | only the line shift of the nine lines that `calculator_editor` gained above it; the report names the line of each parameter default, so it is regenerated, not a new exception |

### Lessons learned by llm (2026-11-28)

- a generated report that names the line of each hit (`code_user_message_exceptions.md`) changes with every line added
  above a listed hit, so the expected changes of an edit in a file with a listed hit include that report
