# Shared by ci-visual.sh and ci-smoke.sh (sourced, not executed).
#
# Environment:
#   CONTENT_REPO  content repository (default: the public web_content repo)
#   CONTENT_REF   commit of the content to render with. Resolved ONCE per run
#                 (see resolve_content_ref) so that every render of a run,
#                 base and head alike, sees exactly the same content.
#   ENV_BRANCH    the environment to render: `production` (PRs into main) or
#                 `staging`. Content and plugins both follow it (default: staging)
#   CONTENT_BRANCH  content branch when CONTENT_REF is not set (default: ENV_BRANCH)
#   WEB_PLUGIN_REF, SIGNAGE_PLUGIN_REF
#                 exact plugin commits, to reproduce a run
#
# The plugins (gs-mmh-web-plugin, gs-mmh-signage-plugin) are NOT taken from
# the commit the repository pins, but from the head of the plugin branch of
# the same environment, like the content. A plugin without that branch falls
# back to `main`. The refs are resolved once per run.
#   PORT          port of the PHP built-in server (default 8001)

CONTENT_REPO=${CONTENT_REPO:-https://github.com/MachMitGoslar/web_content}
ENV_BRANCH=${ENV_BRANCH:-staging}
CONTENT_BRANCH=${CONTENT_BRANCH:-$ENV_BRANCH}
PLUGIN_ORG=${PLUGIN_ORG:-https://github.com/MachMitGoslar}
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

# ls-remote with a few retries: a single network hiccup must not fail a run.
remote_sha() {
  local url=$1 branch=$2 sha="" attempt
  for attempt in 1 2 3; do
    if sha=$(git ls-remote "$url" "refs/heads/$branch" 2>/dev/null | cut -f1); then
      echo "$sha"
      return
    fi
    sleep 2
  done
  echo ""
}

# Prints "<sha> <branch>" for the environment branch of a plugin repo,
# or for `main` if the plugin has no such branch.
resolve_plugin() {
  local repo=$1 override=${2:-} sha
  if [ -n "$override" ]; then
    echo "$override override"
    return
  fi
  sha=$(remote_sha "$PLUGIN_ORG/$repo" "$ENV_BRANCH")
  if [ -n "$sha" ]; then
    echo "$sha $ENV_BRANCH"
    return
  fi
  sha=$(remote_sha "$PLUGIN_ORG/$repo" main)
  if [ -z "$sha" ]; then
    echo "Could not resolve $repo ($ENV_BRANCH or main)" >&2
    exit 1
  fi
  echo "$sha main"
}

resolve_plugin_refs() {
  local line
  line=$(resolve_plugin GS_MMH_WEB_PLUGIN "${WEB_PLUGIN_REF:-}") || exit 1
  WEB_PLUGIN_REF=${line%% *}
  WEB_PLUGIN_FROM=${line##* }
  line=$(resolve_plugin GS_MMH_SIGNAGE_PLUGIN "${SIGNAGE_PLUGIN_REF:-}") || exit 1
  SIGNAGE_PLUGIN_REF=${line%% *}
  SIGNAGE_PLUGIN_FROM=${line##* }
  export WEB_PLUGIN_REF SIGNAGE_PLUGIN_REF

  echo "==> web plugin    ${WEB_PLUGIN_FROM} @ ${WEB_PLUGIN_REF:0:7}"
  echo "==> signage plugin ${SIGNAGE_PLUGIN_FROM} @ ${SIGNAGE_PLUGIN_REF:0:7}"

  if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
    {
      echo "Web plugin: \`${WEB_PLUGIN_FROM}\` @ ${WEB_PLUGIN_REF:0:7}"
      echo ""
      echo "Signage plugin: \`${SIGNAGE_PLUGIN_FROM}\` @ ${SIGNAGE_PLUGIN_REF:0:7}"
    } >> "$GITHUB_STEP_SUMMARY"
  fi
}

# Replaces the submodule checkouts by the resolved plugin commits. Called
# after every `git checkout` of the working tree, because the checked-out ref
# may pin something else.
clone_plugin() {
  local dir=$1 repo=$2 ref=$3
  if [ -e "$dir/.git" ] && [ "$(git -C "$dir" rev-parse HEAD 2>/dev/null)" = "$ref" ]; then
    return
  fi
  rm -rf "$dir"
  mkdir -p "$dir"
  git -C "$dir" init -q
  git -C "$dir" remote add origin "$PLUGIN_ORG/$repo"
  git -C "$dir" fetch -q --depth 1 origin "$ref"
  git -C "$dir" checkout -q FETCH_HEAD
}

prepare_plugins() {
  clone_plugin site/plugins/gs-mmh-web-plugin GS_MMH_WEB_PLUGIN "$WEB_PLUGIN_REF"
  clone_plugin site/plugins/gs-mmh-signage-plugin GS_MMH_SIGNAGE_PLUGIN "$SIGNAGE_PLUGIN_REF"
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

clean_media() {
  find public/media -mindepth 1 ! -name index.html -delete 2>/dev/null || true
}
