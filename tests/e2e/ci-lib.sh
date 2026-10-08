# Shared by ci-visual.sh and ci-smoke.sh (sourced, not executed).
#
# Environment:
#   CONTENT_REPO  content repository (default: the public web_content repo)
#   CONTENT_REF   commit of the content to render with. Resolved ONCE per run
#                 (see resolve_content_ref) so that every render of a run,
#                 base and head alike, sees exactly the same content.
#   CONTENT_BRANCH  branch to resolve when CONTENT_REF is not set
#                 (default: staging; CI passes production for PRs into main)
#   PORT          port of the PHP built-in server (default 8001)

CONTENT_REPO=${CONTENT_REPO:-https://github.com/MachMitGoslar/web_content}
CONTENT_BRANCH=${CONTENT_BRANCH:-staging}
PORT=${PORT:-8001}
SERVER_PID=""

# The scripts switch or rewrite the working tree, which throws away
# uncommitted work. They are meant for a throw-away CI checkout.
require_ci() {
  if [ "${CI:-}" != "true" ] && [ "${CI_VISUAL_FORCE:-}" != "1" ]; then
    echo "$(basename "$0") rewrites the working tree (config, content, git checkout)." >&2
    echo "Run it in CI or in a throw-away clone with CI_VISUAL_FORCE=1." >&2
    exit 1
  fi
}

resolve_content_ref() {
  local source="$CONTENT_BRANCH"

  if [ -n "${CONTENT_REF:-}" ]; then
    source="override"
  else
    CONTENT_REF=$(git ls-remote "$CONTENT_REPO" "refs/heads/$CONTENT_BRANCH" | cut -f1)
  fi

  if [ -z "$CONTENT_REF" ]; then
    echo "Could not resolve content branch '$CONTENT_BRANCH' of $CONTENT_REPO" >&2
    exit 1
  fi

  export CONTENT_REF
  echo "==> content ${source} @ ${CONTENT_REF:0:7}"
}

prepare_content() {
  if [ -e content/.git ] && [ "$(git -C content rev-parse HEAD 2>/dev/null)" = "$CONTENT_REF" ]; then
    return
  fi

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
  local log=${1:-/tmp/mmh-php-server.log}
  php -S "localhost:${PORT}" -t public kirby/router.php >"$log" 2>&1 &
  SERVER_PID=$!

  for _ in $(seq 1 60); do
    if curl -fsS -o /dev/null "http://localhost:${PORT}/"; then
      return
    fi
    sleep 0.5
  done

  echo "PHP server did not come up:" >&2
  cat "$log" >&2
  exit 1
}

stop_server() {
  [ -n "$SERVER_PID" ] && kill "$SERVER_PID" 2>/dev/null || true
  [ -n "$SERVER_PID" ] && wait "$SERVER_PID" 2>/dev/null || true
  SERVER_PID=""
}

init_plugins() {
  git submodule update --init --force -- \
    site/plugins/gs-mmh-web-plugin site/plugins/gs-mmh-signage-plugin >/dev/null
}

clean_media() {
  find public/media -mindepth 1 ! -name index.html -delete 2>/dev/null || true
}
