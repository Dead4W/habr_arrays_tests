# habr_arrays_tests

Небольшой набор PHP-скриптов для сравнения поведения и производительности операций с массивами между версиями PHP (`5.6`, `7.2`, `8.1`, `8.2`, `8.3`, `8.4`, `8.5-rc`).

Проект запускается в Docker, каждый тест выполняется в отдельном контейнере с нужной версией PHP и Xdebug.

## Структура

- `post_1/` — тестовые скрипты для первой статьи.
- `docker-compose.yml` — сервисы с разными версиями PHP.
- `docker/php-with-xdebug.Dockerfile` — образ PHP CLI + Xdebug.
- `run.sh` — запуск скрипта на одной или сразу на всех версиях PHP.
- `run_xdebug.sh` — запуск скрипта с профилированием/трассировкой Xdebug.
- `xdebug/` — выходные файлы профиля и trace (игнорируются в git).

## Как это работает

1. В `docker-compose.yml` описаны сервисы `php56`, `php72`, `php81`, `php82`, `php83`, `php84`, `php85`.
2. Все сервисы монтируют текущий проект в `/work` внутри контейнера.
3. `run.sh` выполняет `php /work/<script>` в нужном сервисе.
4. `run_xdebug.sh` добавляет runtime-настройки Xdebug и складывает артефакты в `./xdebug`.

## Быстрый старт

### 1) Собрать образы

```bash
docker compose build
```

### 2) Запустить скрипт

Одна версия PHP:

```bash
./run.sh post_1/php_arrays_operations_array_plus_diff_hash_table_first_arrays.php 8.3
```

Все версии подряд:

```bash
./run.sh post_1/php_arrays_operations_array_plus_diff_hash_table_first_arrays.php all
```

### 3) Запуск с Xdebug

С аргументами:

```bash
./run_xdebug.sh 8.3 post_1/php_arrays_operations_array_plus_diff_hash_table_first_arrays.php
```

Интерактивно (без аргументов):

```bash
./run_xdebug.sh
```

После выполнения скрипт выведет список созданных файлов `.cachegrind.*` и `.trace.*`.

## Примечания

- Скрипты в `post_1` печатают время выполнения (`Elapsed`) и пиковую память (`Peak RAM`).
- В коде тестов задан высокий `memory_limit`, так как массивы большие.
- Детальное описание всех скриптов первой статьи см. в `post_1/README.md`.
