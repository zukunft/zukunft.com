#!/bin/bash

# Defaults
ENV=""
BRANCH=""

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

# Check for upstream remote
if ! git remote get-url upstream &>/dev/null; then
    echo "No 'upstream' remote found."
    echo "Adding upstream ..."
    git remote add upstream https://github.com/zukunft/zukunft.com
fi

# Fetch all from upstream
echo "Fetching from upstream"
git fetch upstream
    
if [[ -n "$BRANCH" ]]; then
    if git rev-parse --verify "$BRANCH" &>/dev/null; then
        echo "Syncing with upstream/$BRANCH..."
        git checkout "$BRANCH" || git checkout -b "$BRANCH"
    else
        git checkout --track upstream/"$BRANCH" || git checkout -b "$BRANCH"
    fi
    git merge "upstream/$BRANCH"
else
    echo "Syncing with upstream/master"
    git checkout master || git checkout -b master
    git merge upstream/master
fi

echo "Now on branch: $(git branch --show-current)"

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
