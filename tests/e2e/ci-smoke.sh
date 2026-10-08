#!/usr/bin/env bash
#
# Smoke tests of the checked-out code against live web_content.
#
#   tests/e2e/ci-smoke.sh
#
# Renders the current working tree with the content of $CONTENT_BRANCH (or the
# exact commit in $CONTENT_REF) and runs the Playwright smoke tests. Pages that
# editors removed from the content are skipped (MMH_SKIP_MISSING=1).
#
# Needs php, composer, node/npm (with `npm ci` done) and Playwright's chromium.
# See ci-lib.sh for the environment.

set -euo pipefail

SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
# shellcheck source=tests/e2e/ci-lib.sh
. "$SCRIPT_DIR/ci-lib.sh"

require_ci
cd "$(git rev-parse --show-toplevel)"
trap stop_server EXIT

resolve_content_ref
prepare_content
init_plugins
composer install --no-interaction --prefer-dist --no-progress --quiet
write_config
clean_media

start_server
export PLAYWRIGHT_BASE_URL="http://localhost:${PORT}" MMH_SKIP_MISSING=1
npx playwright test smoke
