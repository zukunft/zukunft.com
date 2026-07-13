#!/bin/bash

# TODO make sure that before any update in release or prod
# TODO the test.php script has run without errors on the local server
# TODO using a temp test database e.g "zukunft_test"

# stop on the first error, so that a failed update is never reported as done
set -euo pipefail

# this script lives in <repo>/script/ ; ROOT is the repo (and web) root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

die() { echo "ERROR: $*" >&2; exit 1; }

# Defaults
ENV=""
BRANCH=""
# the git repository and branch the program updates are pulled from; both overridable via .env
SOURCE_REPO_URL="https://github.com/zukunft/zukunft.com"
SOURCE_BRANCH="master"

# get the value of a key from the .env file (empty if the file or the key is missing)
env_value() {
    local key="$1" val=""
    [[ -f "$ROOT/.env" ]] || return 0
    # keep the '|| true' - grep exits 1 if the key is missing, which pipefail would turn into an abort
    val="$(grep -E "^[[:space:]]*$key[[:space:]]*=" "$ROOT/.env" | tail -n1 | cut -d= -f2- || true)"
    val="${val%%#*}"                             # drop any inline comment
    echo "$val" | tr -d '"'\''[:space:]'         # strip quotes and whitespace
}

# Parse arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --env=*) ENV="${1#*=}" ;;
        --env) ENV="$2"; shift ;;
        --branch=*) BRANCH="${1#*=}" ;;
        --branch) BRANCH="$2"; shift ;;
        *) echo "Updating with default environment";;
    esac
    shift
done

# git refuses to touch a repo owned by another user, so fail early with the fix
# instead of letting every git call below print the same "dubious ownership"
if ! git -C "$ROOT" rev-parse --is-inside-work-tree &>/dev/null; then
    echo "ERROR: git cannot use the repository at $ROOT" >&2
    echo "if git reports 'dubious ownership' the repo is owned by another user than the one" >&2
    echo "running this script (the server admin page runs it as root); allow it once with:" >&2
    echo "    sudo git config --system --add safe.directory $ROOT" >&2
    exit 1
fi

# read the source repository url and the branch of this pod from .env
# e.g. BRANCH=master for prod, BRANCH=release for test and BRANCH=develop for dev
ENV_REPO="$(env_value SOURCE_REPO_URL)"
if [[ -n "$ENV_REPO" ]]; then
    SOURCE_REPO_URL="$ENV_REPO"
fi
ENV_BRANCH="$(env_value BRANCH)"
if [[ -n "$ENV_BRANCH" ]]; then
    SOURCE_BRANCH="$ENV_BRANCH"
fi

# Check for upstream remote and make sure it points at the configured repository
if ! git -C "$ROOT" remote get-url upstream &>/dev/null; then
    echo "No 'upstream' remote found."
    echo "Adding upstream $SOURCE_REPO_URL ..."
    git -C "$ROOT" remote add upstream "$SOURCE_REPO_URL"
elif [[ "$(git -C "$ROOT" remote get-url upstream)" != "$SOURCE_REPO_URL" ]]; then
    echo "Updating upstream remote to $SOURCE_REPO_URL ..."
    git -C "$ROOT" remote set-url upstream "$SOURCE_REPO_URL"
fi

# Fetch all from upstream
echo "Fetching from upstream"
git -C "$ROOT" fetch upstream || die "cannot fetch from $SOURCE_REPO_URL"

# the --branch parameter wins over BRANCH from .env, which wins over the master default
TARGET_BRANCH="${BRANCH:-$SOURCE_BRANCH}"
echo "Updating from $SOURCE_REPO_URL branch $TARGET_BRANCH"

# server_admin.sh keeps a maintenance page live by copying it over index.html, but
# index.html is under git control, so the changed file would block the checkout;
# take the page down for the local git steps and put it back right after the merge
# (the slow fetch above and the composer install afterwards still show the page)
MAINTENANCE_PAGE="${MAINTENANCE_PAGE:-}"
if [[ -n "$MAINTENANCE_PAGE" ]]; then
    [[ -f "$ROOT/$MAINTENANCE_PAGE" ]] || die "maintenance page not found: $MAINTENANCE_PAGE"
    git -C "$ROOT" checkout -- index.html || die "cannot take the maintenance page down"
fi
show_maintenance_page() {
    [[ -n "$MAINTENANCE_PAGE" ]] || return 0
    local page="$MAINTENANCE_PAGE"
    # put the page up only once, so that the trap below is a no-op after a clean run
    MAINTENANCE_PAGE=""
    if [[ -f "$ROOT/$page" ]]; then
        cp -f "$ROOT/$page" "$ROOT/index.html"
    else
        # the target branch may not have the page; that is no reason to fail the update
        echo "WARNING: $page does not exist in $TARGET_BRANCH - showing the normal index.html" >&2
    fi
}

# a server can carry local adjustments which would abort the checkout too, so move
# them out of the way and put them back once the new branch is in place
STASHED=""
restore_local_changes() {
    [[ -n "$STASHED" ]] || return 0
    STASHED=""
    if git -C "$ROOT" stash pop --quiet; then
        echo "local changes restored"
    else
        # never leave the web root with conflict markers: reset it to the updated
        # branch and keep the local changes in the stash to be merged by hand
        git -C "$ROOT" reset --hard --quiet HEAD
        echo "WARNING: the local changes conflict with the update and are kept in the stash" >&2
        echo "         show them with: git -C $ROOT stash show -p" >&2
    fi
}

# whatever happens below, the web root must not stay without the maintenance page
trap 'restore_local_changes; show_maintenance_page' EXIT

if ! git -C "$ROOT" diff --quiet HEAD --; then
    git -C "$ROOT" stash push --quiet --message "update.sh: local changes before update" \
        || die "cannot stash the local changes of the web root"
    STASHED="yes"
    echo "local changes stashed"
fi

echo "Syncing with upstream/$TARGET_BRANCH"
if git -C "$ROOT" rev-parse --verify "$TARGET_BRANCH" &>/dev/null; then
    git -C "$ROOT" checkout "$TARGET_BRANCH" || die "cannot switch to branch $TARGET_BRANCH"
else
    git -C "$ROOT" checkout --track "upstream/$TARGET_BRANCH" || die "branch $TARGET_BRANCH not found on upstream"
fi
# an unexpected conflict stops the merge, so report it instead of ending half updated
git -C "$ROOT" merge "upstream/$TARGET_BRANCH" || die "cannot merge upstream/$TARGET_BRANCH - the web root has conflicts"

restore_local_changes
show_maintenance_page

echo "Now on branch: $(git -C "$ROOT" branch --show-current)"

if [[ "$ENV" == "docker" ]]; then
    echo "Updating docker env ..."

    # Stop and remove containers, volumes, and networks
    docker-compose down -v --remove-orphans

    # delete not tagged images, appeared after rebuild
    docker image prune -f

    # docker image prune
    docker image prune --filter "label=com.docker.compose.project=$(basename "$PWD")" -f

    # check deleting
    docker images --filter "label=com.docker.compose.project=$(basename "$PWD")"

    # build
    docker-compose build

    # run containers
    docker-compose up -d
fi
