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

## Maintenance (program update / database upgrade)

The **Update program** and **Upgrade database** buttons run
`script/server_admin.sh` as root via `sudo -n` (non-interactive). Three things
must be in place for that:

1. a **passwordless sudo** rule for the web server user (`www-data` on Debian),
   scoped to exactly that one script,
2. the **execute bit** on `script/server_admin.sh` and `script/update.sh`,
3. a git **`safe.directory`** exception for the web root — the update runs as
   root while the web root belongs to another user, and git rejects a repo owned
   by a foreign user (CVE-2022-24765).

On a debian install `script/install.sh` sets all three up automatically
(`configureServerAdmin`), based on `WWW_ROOT` and `WEB_USER` in `.env`: it
writes the sudo rule to `/etc/sudoers.d/zukunft-server-admin` (mode 0440,
checked with `visudo -cf` and removed again if invalid), makes both scripts
executable and adds the `safe.directory` exception. Nothing else is needed after
a fresh install.

To set it up by hand — on an existing install, on a non-debian host or after
changing `WEB_USER` — use a dedicated drop-in file for the sudo rule so it stays
easy to review and remove:

```bash
# /etc/sudoers.d/zukunft-server-admin  (chmod 0440, edit via visudo)
# e.g. sudo visudo -f /etc/sudoers.d/zukunft-server-admin 
#      and add this line:
www-data ALL=(root) NOPASSWD: /var/www/html/script/server_admin.sh
# ... and 
sudo chmod +x /var/www/html/script/update.sh 
sudo chmod +x /var/www/html/script/server_admin.sh 
# ... and let root use the git repo in the web root
sudo git config --system --add safe.directory /var/www/html
```

Adjust the user and the absolute path to your install (`WEB_USER` and
`WWW_ROOT` in `.env`). Use `--system` (writes `/etc/gitconfig`), **not**
`--global`: sudo may keep the caller's `HOME`, so root's global config is not
reliably `/root/.gitconfig`. Keep the sudo rule pinned to the single script —
never grant the web user broader sudo. `update-program` pulls from the
repository configured as `SOURCE_REPO_URL` in `.env`, so an update can only ever
come from that source.

If the rule is missing (or a script is not executable) the admin page shows
*"… could not be started (exit code 1); the web server user is likely not
permitted to run \"sudo -n server_admin.sh\" without a password."*

If the `safe.directory` exception is missing, the **Output** box shows
*"fatal: detected dubious ownership in repository at '/var/www/html'"* — run the
`git config --system` line above. Once sudo is allowed, any remaining failure
(git, composer, php) is reported with the script's last output line and the full
log in the **Output** box.