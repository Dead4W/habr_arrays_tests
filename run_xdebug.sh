#!/usr/bin/env bash

set -euo pipefail

declare -A SERVICE_BY_VERSION=(
  ["5.6"]="php56"
  ["7.2"]="php72"
  ["8.1"]="php81"
  ["8.2"]="php82"
  ["8.3"]="php83"
  ["8.4"]="php84"
  ["8.5"]="php85"
)

SUPPORTED_VERSIONS=(5.6 7.2 8.1 8.2 8.3 8.4 8.5)

VERSION_ARG="${1:-}"
SCRIPT_ARG="${2:-}"

choose_version() {
  local selected

  echo "Select PHP version:"
  PS3="Enter number: "
  select selected in "${SUPPORTED_VERSIONS[@]}"; do
    if [[ -n "${selected:-}" ]]; then
      echo "${selected}"
      return 0
    fi
    echo "Invalid selection, try again."
  done
}

resolve_relative_script_path() {
  local input_path="$1"
  local absolute_path

  if [[ "${input_path}" = /* ]]; then
    absolute_path="${input_path}"
  else
    absolute_path="${PWD}/${input_path}"
  fi

  if [[ ! -f "${absolute_path}" ]]; then
    echo "Error: script not found: ${input_path}" >&2
    return 1
  fi

  if [[ "${absolute_path}" != "${PWD}/"* ]]; then
    echo "Error: script must be inside current project directory" >&2
    return 1
  fi

  echo "${absolute_path#${PWD}/}"
}

if [[ -n "${VERSION_ARG}" ]]; then
  PHP_VERSION="${VERSION_ARG}"
else
  PHP_VERSION="$(choose_version)"
fi

if [[ -z "${SERVICE_BY_VERSION[$PHP_VERSION]:-}" ]]; then
  echo "Unsupported PHP version: ${PHP_VERSION}"
  echo "Supported versions: ${SUPPORTED_VERSIONS[*]}"
  exit 1
fi

if [[ -n "${SCRIPT_ARG}" ]]; then
  SCRIPT_INPUT="${SCRIPT_ARG}"
else
  read -r -e -p "Enter path to PHP script: " SCRIPT_INPUT
fi

if [[ -z "${SCRIPT_INPUT}" ]]; then
  echo "Error: script path is required"
  exit 1
fi

REL_SCRIPT_PATH="$(resolve_relative_script_path "${SCRIPT_INPUT}")"
SERVICE="${SERVICE_BY_VERSION[$PHP_VERSION]}"
TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"

DEBUG_DIR="${PWD}/xdebug"
mkdir -p "${DEBUG_DIR}"

SAFE_SCRIPT_NAME="$(echo "${REL_SCRIPT_PATH}" | tr '/[:space:]' '__')"
XDEBUG_RUN_TAG="${TIMESTAMP}_php${PHP_VERSION}_${SAFE_SCRIPT_NAME}"

echo "Running /work/${REL_SCRIPT_PATH} on PHP ${PHP_VERSION} (${SERVICE})"
echo "Xdebug output directory: ${DEBUG_DIR}"
echo "Run tag: ${XDEBUG_RUN_TAG}"

docker compose --ansi never run --rm \
  -e SCRIPT_TO_RUN="/work/${REL_SCRIPT_PATH}" \
  -e XDEBUG_OUTPUT_DIR="/work/xdebug" \
  -e XDEBUG_RUN_TAG="${XDEBUG_RUN_TAG}" \
  "${SERVICE}" sh -lc '
    set -e

    if ! php -m | grep -iq "^xdebug$"; then
      echo "[error] Xdebug extension is not detected in this image."
      echo "[hint] Rebuild images: docker compose build"
      exit 2
    fi

    xdebug_version="$(php -r "echo phpversion(\"xdebug\") ?: \"\";")"
    echo "[info] Xdebug extension detected: ${xdebug_version}"

    if [ "${xdebug_version%%.*}" = "2" ]; then
      php -d display_errors=1 \
        -d error_reporting=E_ALL \
        -d xdebug.default_enable=1 \
        -d xdebug.profiler_enable=1 \
        -d xdebug.profiler_output_dir="$XDEBUG_OUTPUT_DIR" \
        -d xdebug.profiler_output_name="${XDEBUG_RUN_TAG}.cachegrind.%p" \
        -d xdebug.auto_trace=1 \
        -d xdebug.trace_output_dir="$XDEBUG_OUTPUT_DIR" \
        -d xdebug.trace_output_name="${XDEBUG_RUN_TAG}.trace.%p" \
        -d xdebug.collect_params=4 \
        -d xdebug.collect_return=1 \
        -d xdebug.show_mem_delta=1 \
        "$SCRIPT_TO_RUN"
    else
      php -d display_errors=1 \
        -d error_reporting=E_ALL \
        -d xdebug.mode=profile,trace \
        -d xdebug.start_with_request=yes \
        -d xdebug.output_dir="$XDEBUG_OUTPUT_DIR" \
        -d xdebug.profiler_output_name="${XDEBUG_RUN_TAG}.cachegrind.%p" \
        -d xdebug.trace_output_name="${XDEBUG_RUN_TAG}.trace.%p" \
        -d xdebug.trace_format=1 \
        "$SCRIPT_TO_RUN"
    fi
  ' 2>&1 \
  | awk '{
      line = $0
      gsub(/\r/, "", line)
      gsub(/\033\[[0-9;]*[[:alpha:]]/, "", line)
      if (line !~ /^[[:space:]]*Container[[:space:]].*[[:space:]](Creating|Created)[[:space:]]*$/) {
        print line
      }
    }'

echo "Done. Xdebug artifacts:"
rg --files "${DEBUG_DIR}" | rg "${XDEBUG_RUN_TAG}" || true
