# post_1: тесты массивов PHP

В этой папке собраны сценарии для сравнения скорости и потребления памяти при операциях с массивами в разных версиях PHP.

Все скрипты используют общий формат вывода:

- `PHP <version>`
- `Elapsed: <ms>`
- `Peak RAM: <MB>`

## Скрипты и назначение

- `php_array_func_arg.php`  
  Проверяет стоимость передачи большого массива в функцию, где массив принимается по ссылке и внутри модифицируется (`$arr[] = ...`).

- `php_arrays_hash_table_convert.php`  
  Показывает цену перехода последовательного массива в hash-table:  
  1) вставка элемента с большим числовым ключом,  
  2) вставка элемента со строковым ключом.

- `php_arrays_operations_array_merge_seq_arrays.php`  
  `array_merge` двух последовательных массивов (`list + list`).

- `php_arrays_operations_array_merge_hash_table_arrays.php`  
  `array_merge` двух ассоциативных массивов (`hash-table + hash-table`).

- `php_arrays_operations_array_merge_diff_seq_first_arrays.php`  
  `array_merge`, где первый аргумент — последовательный массив, второй — ассоциативный (`list -> hash-table`).

- `php_arrays_operations_array_merge_diff_hash_table_first_arrays.php`  
  `array_merge`, где первый аргумент — ассоциативный массив, второй — последовательный (`hash-table -> list`).

- `php_arrays_operations_array_plus_diff_seq_first_arrays.php`  
  Оператор объединения массивов `$arr1 + $arr2`, где первым идёт последовательный массив.

- `php_arrays_operations_array_plus_diff_hash_table_first_arrays.php`  
  Оператор объединения массивов `$arr1 + $arr2`, где первым идёт ассоциативный массив.

## Как запускать

Из корня репозитория:

```bash
./run.sh post_1/<script_name>.php all
```

Пример:

```bash
./run.sh post_1/php_arrays_operations_array_merge_seq_arrays.php 8.3
```

Для профилирования Xdebug:

```bash
./run_xdebug.sh 8.3 post_1/php_arrays_operations_array_merge_seq_arrays.php
```
