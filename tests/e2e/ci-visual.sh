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
# Environment:
#   CONTENT_REPO  content repository (default: the public web_content repo)
#   CONTENT_REF   commit of the content to render with; pinned on purpose so
#                 that content edits do not show up as visual changes
#   PORT          port of the PHP built-in server (default 8001)

set -euo pipefail

# The script switches the working tree between the refs with
# `git checkout --force`, which throws away uncommitted work. It is meant for
# a throw-away CI checkout, so it refuses to run anywhere else by default.
if [ "${CI:-}" != "true" ] && [ "${CI_VISUAL_FORCE:-}" != "1" ]; then
  echo "ci-visual.sh discards local changes (git checkout --force)." >&2
  echo "Run it in CI or in a throw-away clone with CI_VISUAL_FORCE=1." >&2
  exit 1
fi

BASE_REF=${1:?usage: ci-visual.sh <base-ref> [head-ref]}
HEAD_REF=${2:-HEAD}
CONTENT_REPO=${CONTENT_REPO:-https://github.com/MachMitGoslar/web_content}
CONTENT_REF=${CONTENT_REF:-72e9eae26021b3f3685421da473983484fbf194d}
PORT=${PORT:-8001}

ROOT=$(git rev-parse --show-toplevel)
cd "$ROOT"

BASE_SHA=$(git rev-parse "$BASE_REF^{commit}")
HEAD_SHA=$(git rev-parse "$HEAD_REF^{commit}")
WORK=$(mktemp -d)
SERVER_PID=""

cleanup() {
  [ -n "$SERVER_PID" ] && kill "$SERVER_PID" 2>/dev/null || true
  rm -rf "$WORK"
}
trap cleanup EXIT

# The tests of the head ref are used for both runs, so the base needs no
# Playwright setup of its own (and older bases can be compared).
git checkout --force --detach "$HEAD_SHA" >/dev/null 2>&1
cp -r tests/e2e "$WORK/e2e"
cp playwright.config.js "$WORK/playwright.config.js"
rm -rf "$WORK/e2e/__screenshots__" "$WORK/e2e/.results"

prepare_content() {
  if [ -d content/.git ] || [ -f content/.git ]; then
    if [ "$(git -C content rev-parse HEAD 2>/dev/null)" = "$CONTENT_REF" ]; then
      return
    fi
  fi
  echo "==> content @ ${CONTENT_REF:0:7}"
  rm -rf content
  mkdir content
  git -C content init -q
  git -C content remote add origin "$CONTENT_REPO"
  git -C content fetch -q --depth 1 origin "$CONTENT_REF"
  git -C content checkout -q FETCH_HEAD
}

write_config() {
  # Not a debug site: the debug lock (and its banner) would hide the pages.
  cat > site/config/config.localhost.php <<PHP
<?php

return [
    'debug' => false,
    'url' => 'http://localhost:${PORT}',
    'panel' => ['install' => false],
    'cache' => ['pages' => ['active' => false]],
];
PHP
}

start_server() {
  php -S "localhost:${PORT}" -t public kirby/router.php >"$WORK/server.log" 2>&1 &
  SERVER_PID=$!

  for _ in $(seq 1 60); do
    if curl -fsS -o /dev/null "http://localhost:${PORT}/"; then
      return
    fi
    sleep 0.5
  done

  echo "PHP server did not come up:" >&2
  cat "$WORK/server.log" >&2
  exit 1
}

stop_server() {
  kill "$SERVER_PID" 2>/dev/null || true
  wait "$SERVER_PID" 2>/dev/null || true
  SERVER_PID=""
}

render() {
  local sha=$1 mode=$2

  echo "==> ${mode} @ ${sha:0:7}"
  git checkout --force --detach "$sha" >/dev/null 2>&1
  git submodule update --init --force -- \
    site/plugins/gs-mmh-web-plugin site/plugins/gs-mmh-signage-plugin >/dev/null

  composer install --no-interaction --prefer-dist --no-progress --quiet
  write_config

  rm -rf tests/e2e
  cp -r "$WORK/e2e" tests/e2e
  cp "$WORK/playwright.config.js" playwright.config.js
  [ -d "$WORK/shots" ] && cp -r "$WORK/shots" tests/e2e/__screenshots__

  find public/media -mindepth 1 ! -name index.html -delete 2>/dev/null || true
  start_server
  export PLAYWRIGHT_BASE_URL="http://localhost:${PORT}"

  if [ "$mode" = baseline ]; then
    npx playwright test visual --update-snapshots
    rm -rf "$WORK/shots"
    cp -r tests/e2e/__screenshots__ "$WORK/shots"
  else
    npx playwright test visual
  fi

  stop_server
}

prepare_content
render "$BASE_SHA" baseline
render "$HEAD_SHA" compare
echo "==> no visual differences between ${BASE_SHA:0:7} and ${HEAD_SHA:0:7}"
