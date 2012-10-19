<?php
/**
 * This example shows the difference in memory management
 *
 * @author Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @license MIT License (distributed with source code)
 * @copyright (c) 2012 Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @link https://github.com/aeryaguzov/php-mysqlnd-examples for the canonical source repository
 */
ini_set('memory_limit', '1M');

$cnf = include 'config.php';

if (!empty($cnf['initial_migration']['rows_count']) && $cnf['initial_migration']['rows_count'] < 100000) {
    echo 'You can\'t use this example because you have not enough rows in your table' . PHP_EOL; die();
}

$link = new mysqli(
    $cnf['db']['host'],
    $cnf['db']['username'],
    $cnf['db']['password'],
    $cnf['db']['database'],
    $cnf['db']['port']
);

echo 'Start memory usage: ' . memory_get_usage() . PHP_EOL;
$result = $link->query('SELECT * FROM mysqlnd_test LIMIT 10000;');
echo 'Stop memory usage: ' . memory_get_usage() . PHP_EOL;
echo 'If you are using mysqlnd, you can see big difference between start and stop memory usage values.' . PHP_EOL;
echo 'This is because mysqlnd was build as PHP extension and all work with db data is under your control' . PHP_EOL;
echo PHP_EOL;
echo 'So if you are using mysqlnd, this script must exceed memory limit(1M) and fail right now...' . PHP_EOL;
$result = $link->query('SELECT * FROM mysqlnd_test LIMIT 100000;');
echo PHP_EOL;
echo 'Your script works because you are not using mysqnd and it must fail when you assign db data to variable...' . PHP_EOL;
$data = array();
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}