# Offline fallback pages

Source-only pages that are **never served from this folder**. The root
`.htaccess` returns a 404 for any request to `/optional/`.

When a fallback needs to go live, the `server_admin` script copies the relevant
page into the docroot (e.g. project root or `http/`), typically as the
stand-in for `index.html` / the entry point. When the condition clears, the
script restores the normal page and removes the fallback copy.

## Pages

| File                    | Shown when                                                    |
|-------------------------|---------------------------------------------------------------|
| `user_reject.html`      | user whitelist is active and the logged-in user is rejected    |
| `ip_reject.html`        | IP whitelist is active and the client IP is rejected           |
| `program_upgrade.html`  | the program is being updated (auto-reloads every 30s)          |
| `database_upgrade.html` | the database is being upgraded (auto-reloads every 30s)        |

## Notes

- The pages reuse the static assets under `/src/main/resources/` (stylesheet and
  logo). Those are served by the web server directly, so the fallbacks still
  render while PHP / the database is unavailable during an upgrade.
- The upgrade pages carry `<meta http-equiv="refresh" content="30">` so visitors
  are returned to the live site automatically once it is back.
- Keep the markup in sync with `index.html` when its header/footer changes.