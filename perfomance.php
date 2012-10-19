<?php
/**
 * This example shows mysqlnd perfomance for common MySQL drivers
 *
 * @author Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @license MIT License (distributed with source code)
 * @copyright (c) 2012 Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @link https://github.com/aeryaguzov/php-mysqlnd-examples for the canonical source repository
 */
$cnf = include 'config.php';
$query = 'SELECT id, val FROM mysqlnd_test LIMIT 10;';

$link = new mysqli(
    $cnf['db']['host'],
    $cnf['db']['username'],
    $cnf['db']['password'],
    $cnf['db']['database'],
    $cnf['db']['port']
);

$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $result = $link->query($query);
}
$stop = microtime(true);
echo 'MYSQLI time: ' . ($stop - $start) . PHP_EOL;

$pdo = new PDO(
    'mysql:host=' . $cnf['db']['host'] . ';dbname=' . $cnf['db']['database'],
    $cnf['db']['username'],
    $cnf['db']['password']
);

$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    $result = $pdo->query($query);
}
$stop = microtime(true);
echo 'PDO time: ' . ($stop - $start) . PHP_EOL;