<?php
/**
 * Prepare data for migration while query is being executed
 *
 * @author Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @license MIT License (distributed with source code)
 * @copyright (c) 2012 Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @link https://github.com/aeryaguzov/php-mysqlnd-examples for the canonical source repository
 */
$cnf = include 'config.php';

$link = new mysqli(
    $cnf['db']['host'],
    $cnf['db']['username'],
    $cnf['db']['password'],
    $cnf['db']['database'],
    $cnf['db']['port']
);

$query = 'SELECT MAX(id) FROM `mysqlnd_test`;';
$result = $link->query($query);
$max = $result->fetch_all();
$max = $max[0][0];

echo 'Max id: ' . $max . PHP_EOL;

$query = 'ALTER TABLE `mysqlnd_test` ADD COLUMN `is_mersenne` BIT NOT NULL DEFAULT 0  AFTER `val` ;';

$mersenne = array();
$processed = false;
$link->query($query, MYSQLI_ASYNC);

do {
    $read = $errors = $reject = array();
    $read[] = $errors[] = $reject[] = $link;

    if (!mysqli_poll($read, $errors, $reject, 1)) {
        echo 'Waiting for query execution...' . PHP_EOL;
        if (empty($mersenne)) {
            $mersenne = findMersenne($max);
            echo 'While query been executed, we calculated mersenne numbers: ' . implode(',', $mersenne) . PHP_EOL;
        }
        continue;
    } else {
        $link->reap_async_query();
        $processed = true;
        echo 'Query successfully executed' . PHP_EOL;
    }
} while($processed == false);

echo 'Performing update...';
$query = 'UPDATE `mysqlnd_test` SET `is_mersenne` = 1 WHERE id IN(' . implode(',', $mersenne). ');';
$result = $link->query($query);

if ($result) {
    echo 'OK!';
} else {
    echo 'FAIL!';
}

/**
 * Find mersenne numbers smaller than $max
 *
 * @param $max
 * @return array
 */
function findMersenne($max) {
    $mersenne = array();

    $current = $n = 0;
    while ($current < $max) {
        $n++;
        $current = pow(2, $n) - 1;
        $mersenne[] = $current;
    }

    return $mersenne;
}