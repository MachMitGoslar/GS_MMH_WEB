#!/usr/bin/env bash
#
# Visual regression between two git refs, rendered with the same content.
#
#   tests/e2e/ci-visual.sh <base-ref> [head-ref]
#
# 1. renders every visual page at <base-ref> and stores the screenshots
# 2. renders them again at [head-ref] (default HEAD) and compares
#
# No baselines live in the repository: the base ref IS the baseline. That
# keeps the check independent of the operating system and of the repository
# size, and it is exactly the question of a refactoring: "does anything look
# different from before?".
#
# Needs php, composer, node/npm (with `npm ci` done) and Playwright's chromium.
# The working tree is switched between the refs: run it in a clean clone.
#
# Environment: see ci-lib.sh. The content is resolved once, so both renders
# use the same commit even if editors push while the job runs. Pages that were
# removed from the content are skipped (MMH_SKIP_MISSING=1).

set -euo pipefail

SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
# shellcheck source=tests/e2e/ci-lib.sh
. "$SCRIPT_DIR/ci-lib.sh"

require_ci

BASE_REF=${1:?usage: ci-visual.sh <base-ref> [head-ref]}
HEAD_REF=${2:-HEAD}

ROOT=$(git rev-parse --show-toplevel)
cd "$ROOT"

BASE_SHA=$(git rev-parse "$BASE_REF^{commit}")
HEAD_SHA=$(git rev-parse "$HEAD_REF^{commit}")
WORK=$(mktemp -d)

cleanup() {
  stop_server
  rm -rf "$WORK"
}
trap cleanup EXIT

# The tests of the head ref are used for both runs, so the base needs no
# Playwright setup of its own (and older bases can be compared).
git checkout --force --detach "$HEAD_SHA" >/dev/null 2>&1
cp -r tests/e2e "$WORK/e2e"
cp playwright.config.js "$WORK/playwright.config.js"
rm -rf "$WORK/e2e/__screenshots__" "$WORK/e2e/.results"

render() {
  local sha=$1 mode=$2

  echo "==> ${mode} @ ${sha:0:7}"
  git checkout --force --detach "$sha" >/dev/null 2>&1
  init_plugins

  composer install --no-interaction --prefer-dist --no-progress --quiet
  write_config

  # Older refs (the base of a PR) have no tests/ directory at all.
  mkdir -p tests
  rm -rf tests/e2e
  cp -r "$WORK/e2e" tests/e2e
  cp "$WORK/playwright.config.js" playwright.config.js
  [ -d "$WORK/shots" ] && cp -r "$WORK/shots" tests/e2e/__screenshots__

  clean_media
  start_server "$WORK/server.log"
  export PLAYWRIGHT_BASE_URL="http://localhost:${PORT}" MMH_SKIP_MISSING=1

  if [ "$mode" = baseline ]; then
    npx playwright test visual --update-snapshots
    rm -rf "$WORK/shots"
    cp -r tests/e2e/__screenshots__ "$WORK/shots"
  else
    npx playwright test visual
  fi

  stop_server
}

resolve_content_ref
prepare_content
render "$BASE_SHA" baseline
render "$HEAD_SHA" compare
echo "==> no visual differences between ${BASE_SHA:0:7} and ${HEAD_SHA:0:7}"
