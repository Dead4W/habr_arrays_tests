#!/usr/bin/env bash

set -euo pipefail

SCRIPT_PATH="${1:-}"
PHP_VERSION="${2:-all}"

if [[ -z "${SCRIPT_PATH}" ]]; then
  echo "Usage: ./run.sh <path/to/script.php> [5.6|7.2|8.1|8.2|8.3|8.4|8.5|all]"
  exit 1
fi

if [[ ! -f "${SCRIPT_PATH}" ]]; then
  echo "Error: script not found: ${SCRIPT_PATH}"
  exit 1
fi

if [[ "${SCRIPT_PATH}" = /* ]]; then
  REL_SCRIPT_PATH="${SCRIPT_PATH#$PWD/}"
else
  REL_SCRIPT_PATH="${SCRIPT_PATH}"
fi

if [[ "${REL_SCRIPT_PATH}" = /* ]]; then
  echo "Error: script must be inside current project directory"
  exit 1
fi

declare -A SERVICE_BY_VERSION=(
  ["5.6"]="php56"
  ["7.2"]="php72"
  ["8.1"]="php81"
  ["8.2"]="php82"
  ["8.3"]="php83"
  ["8.4"]="php84"
  ["8.5"]="php85"
)

run_for_version() {
  local version="$1"
  local service="${SERVICE_BY_VERSION[$version]:-}"

  if [[ -z "${service}" ]]; then
    echo "Unsupported PHP version: ${version}"
    exit 1
  fi

  docker compose --ansi never run --rm "${service}" php "/work/${REL_SCRIPT_PATH}" 2>&1 \
    | awk '{
        line = $0
        gsub(/\r/, "", line)
        gsub(/\033\[[0-9;]*[[:alpha:]]/, "", line)
        if (line !~ /^[[:space:]]*Container[[:space:]].*[[:space:]](Creating|Created)[[:space:]]*$/) {
          print line
        }
      }'
}

if [[ "${PHP_VERSION}" == "all" ]]; then
  for version in 5.6 7.2 8.1 8.2 8.3 8.4 8.5; do
    run_for_version "${version}"
  done
else
  run_for_version "${PHP_VERSION}"
fi
