ARG BASE_IMAGE
FROM ${BASE_IMAGE}

ARG XDEBUG_PECL_PACKAGE=xdebug

RUN set -eux; \
    using_archive_repos=0; \
    if ! apt-get update; then \
      echo "Debian mirrors look EOL, switching to archive.debian.org"; \
      printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99archive-no-valid-until; \
      printf 'Acquire::AllowInsecureRepositories "true";\nAcquire::AllowDowngradeToInsecureRepositories "true";\n' > /etc/apt/apt.conf.d/99archive-allow-insecure; \
      for sources_file in /etc/apt/sources.list /etc/apt/sources.list.d/*.list; do \
        [ -f "${sources_file}" ] || continue; \
        sed -i \
          -e 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' \
          -e 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' \
          -e '/[[:space:]]buster-updates[[:space:]]/d' \
          -e '/[[:space:]]stretch-updates[[:space:]]/d' \
          -e '/[[:space:]]jessie-updates[[:space:]]/d' \
          "${sources_file}"; \
      done; \
      apt-get update -o Acquire::Check-Valid-Until=false; \
      using_archive_repos=1; \
    fi; \
    if [ "${using_archive_repos}" = "1" ]; then \
      apt-get install -y --allow-unauthenticated --no-install-recommends ${PHPIZE_DEPS}; \
    else \
      apt-get install -y --no-install-recommends ${PHPIZE_DEPS}; \
    fi; \
    printf "\n" | pecl install -f "${XDEBUG_PECL_PACKAGE}"; \
    docker-php-ext-enable xdebug; \
    apt-get purge -y --auto-remove; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    xdebug_version="$(php -r 'echo phpversion("xdebug") ?: "";')"; \
    xdebug_major="${xdebug_version%%.*}"; \
    if [ "$xdebug_major" = "2" ]; then \
      { \
        echo "xdebug.default_enable=0"; \
        echo "xdebug.profiler_enable=0"; \
        echo "xdebug.auto_trace=0"; \
        echo "xdebug.remote_enable=0"; \
      } > /usr/local/etc/php/conf.d/zz-xdebug-defaults.ini; \
    else \
      echo "xdebug.mode=off" > /usr/local/etc/php/conf.d/zz-xdebug-defaults.ini; \
    fi
