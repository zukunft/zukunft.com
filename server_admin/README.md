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
| `SERVER_ADMIN_USER` + `SERVER_ADMIN_PW`       | username + password | the primary admin (no login IP range)       |
| `SERVER_ADMIN_2_USER` + `SERVER_ADMIN_2_PW`   | username + password | same rights; may be limited to a login IP range |
| `SERVER_ADMIN_3_USER` + `SERVER_ADMIN_3_PW`   | username + password | same rights; may be limited to a login IP range |

Each admin may optionally be limited to a client IP range via
`SERVER_ADMIN_x_IP_FROM` / `SERVER_ADMIN_x_IP_TO` (inclusive; leave empty to
skip) — this limits *where they may log in from*, not what they can do. All
authenticated admins have the same rights, including toggling **and** editing the
IP whitelist (it is only a DDoS pre-filter, see below).

## Whitelist semantics

- User whitelist **active**  -> only users in `user_whitelist.txt` may use the pod.
- User whitelist **inactive** -> the database based blacklist applies instead.
- IP whitelist works the same way with `ip_whitelist.txt`.

Rejected users see `optional/user_reject.html`, rejected IPs see
`optional/ip_reject.html` (served by PHP `readfile`, not from `/optional/`).

### editing the IP whitelist from the admin page

The IP whitelist can be edited directly on the admin page — a text area, one IP or
CIDR per line, `#` for comments — and saved to `ip_whitelist.txt`. Any
authenticated admin may edit and save it. Because the page runs as the
web user, `script/install.sh` makes `state.json` **and** `ip_whitelist.txt` owned
and writable by `WEB_USER` (mode 0644), while the `server_admin/` directory itself
stays owned by the deploy user — so the web process can overwrite those two data
files but cannot create a new `.php` next to the admin scripts, and both files stay
denied web access by the docroot `.htaccess`.

**Why letting the web page write the IP whitelist is not a security downgrade.**
The IP whitelist is only a **pre-filter for DDoS protection**, not a hard security
boundary. Even if the web process were compromised and rewrote the list, the worst
case is that the pre-filter is bypassed and the pod again just faces too many
requests — exactly the situation you are in with no whitelist at all. The recovery
is unchanged: take the web server down and change the whitelist (or the guard) from
the command line. So trading a little file isolation for the convenience of editing
the list from the browser during an attack is worth it.

The **user whitelist** (`user_whitelist.txt`) is a stronger control — it decides
which *accounts* may use the pod — so it is deliberately **not** made web-writable
and is edited over SSH only.

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