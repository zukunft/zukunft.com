# server_admin

Data and CLI helpers for the server admin page (`/http/server_admin.php`).

**This folder is never served over the web** — the root `.htaccess` returns a
404 for any request to `/server_admin/`. It holds admin state, whitelist lists,
the maintenance backup and the password-hash helper.

## Files

| File                    | Committed | Purpose                                                     |
|-------------------------|-----------|-------------------------------------------------------------|
| `hashpw.php`            | yes       | CLI: turn a password into the bcrypt hash for `.env`        |
| `db_upgrade.php`        | yes       | CLI run by `script/server_admin.sh upgrade-database` (mirrors setup.php) |
| `state.json`            | no        | runtime toggle state, written by the admin page             |
| `user_whitelist.txt`    | no        | one allowed user (name or id) per line                      |
| `ip_whitelist.txt`      | no        | one allowed IP / CIDR per line                              |
| `index.html.bak`        | no        | backup of the live `index.html` while a maintenance page is up |
| `*.example`             | yes       | templates for the runtime files above                       |

The runtime files are listed in `.gitignore` — they are installation specific
and must not be committed.

## Authentication

The admin page authenticates with a fixed **username + password** per admin
(plus the `SERVER_ADMIN_IP` allowlist that gates the whole page). Passwords are
stored in `.env` as bcrypt hashes; generate one with:

```bash
php server_admin/hashpw.php      # prompts for the password, prints the hash
```

## Admins and privileges

| .env keys                                     | Factors             | Privileges                                  |
|-----------------------------------------------|---------------------|---------------------------------------------|
| `SERVER_ADMIN_USER` + `SERVER_ADMIN_PW`       | username + password | full access (may switch the IP whitelist off) |
| `SERVER_ADMIN_2_USER` + `SERVER_ADMIN_2_PW`   | username + password | restricted: may **not** switch the IP whitelist off |
| `SERVER_ADMIN_3_USER` + `SERVER_ADMIN_3_PW`   | username + password | restricted: may **not** switch the IP whitelist off |

Each admin may optionally be restricted to a client IP range via
`SERVER_ADMIN_x_IP_FROM` / `SERVER_ADMIN_x_IP_TO` (inclusive; leave empty to
skip). A restricted admin can activate the IP whitelist (raise security) but
cannot deactivate it (which would fall back to the database blacklist) — enforced
server side, not just hidden in the UI.

## Whitelist semantics

- User whitelist **active**  -> only users in `user_whitelist.txt` may use the pod.
- User whitelist **inactive** -> the database based blacklist applies instead.
- IP whitelist works the same way with `ip_whitelist.txt`.

Rejected users see `optional/user_reject.html`, rejected IPs see
`optional/ip_reject.html` (served by PHP `readfile`, not from `/optional/`).