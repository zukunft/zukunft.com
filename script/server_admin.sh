#!/bin/bash

# server_admin.sh - maintenance / update helper for the server admin page
# --------------------------------------------------------------------------
#
# Puts an offline fallback page from /optional live (by copying it over
# index.html, backing up the real one), runs the requested operation, and then
# restores the normal index.html - even if the operation fails.
#
# Subcommands:
#   maintenance-on  <page.html>   copy optional/<page.html> over index.html (backup first)
#   maintenance-off               restore index.html from the backup
#   update-program  [git args]    show program_upgrade page, git update, restore
#   upgrade-database              show database_upgrade page, run db upgrade, restore
#   status                        print whether a maintenance page is currently live
#
# The server admin page (http/server_admin.php) calls this via sudo, e.g.
#   sudo -n /var/www/html/script/server_admin.sh update-program
# so the web user needs a matching sudoers entry running as the deploy user.
#
# This file is part of zukunft.com - calc with words, GNU AGPL v3.

set -euo pipefail

# this script lives in <repo>/script/ ; ROOT is the repo (and web) root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
OPTIONAL_DIR="$ROOT/optional"
ADMIN_DIR="$ROOT/server_admin"
INDEX="$ROOT/index.html"
BACKUP="$ADMIN_DIR/index.html.bak"

log() { echo "[server_admin] $*"; }
die() { echo "[server_admin] ERROR: $*" >&2; exit 1; }

maintenance_on() {
    local page="$1"
    local src="$OPTIONAL_DIR/$page"
    [[ -f "$src" ]] || die "fallback page not found: $src"
    # only back up the real index.html once, so repeated calls stay safe
    if [[ ! -f "$BACKUP" && -f "$INDEX" ]]; then
        cp -p "$INDEX" "$BACKUP"
        log "backed up index.html -> $BACKUP"
    fi
    cp -f "$src" "$INDEX"
    log "maintenance page live: optional/$page"
}

maintenance_off() {
    if [[ -f "$BACKUP" ]]; then
        cp -f "$BACKUP" "$INDEX"
        rm -f "$BACKUP"
        log "restored index.html"
    else
        log "no backup found - nothing to restore"
    fi
}

status() {
    if [[ -f "$BACKUP" ]]; then
        echo "maintenance: ON (a fallback page is live, backup at $BACKUP)"
    else
        echo "maintenance: OFF"
    fi
}

update_program() {
    maintenance_on "program_upgrade.html"
    # always restore the site, whatever happens during the update
    trap 'maintenance_off' EXIT
    # git / docker in update.sh expect the repo root as working directory
    cd "$ROOT"
    log "updating program ..."
    if [[ -x "$SCRIPT_DIR/update.sh" ]]; then
        "$SCRIPT_DIR/update.sh" "$@"
    else
        die "update.sh not found or not executable"
    fi
    if command -v composer >/dev/null 2>&1; then
        log "installing composer dependencies ..."
        composer install --no-dev --no-interaction --prefer-dist
    fi
    log "program update done"
}

upgrade_database() {
    maintenance_on "database_upgrade.html"
    trap 'maintenance_off' EXIT
    cd "$ROOT"
    log "upgrading database ..."
    php "$ADMIN_DIR/db_upgrade.php"
    log "database upgrade done"
}

cmd="${1:-}"
shift || true
case "$cmd" in
    maintenance-on)   [[ $# -ge 1 ]] || die "usage: server_admin.sh maintenance-on <page.html>"; maintenance_on "$1" ;;
    maintenance-off)  maintenance_off ;;
    update-program)   update_program "$@" ;;
    upgrade-database) upgrade_database ;;
    status)           status ;;
    *)
        echo "usage: server_admin.sh {maintenance-on <page.html>|maintenance-off|update-program|upgrade-database|status}" >&2
        exit 1
        ;;
esac