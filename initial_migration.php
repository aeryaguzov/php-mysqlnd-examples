<?php
/**
 * Initial db migration
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

$result = $link->query('DROP TABLE IF EXISTS `mysqlnd_test`;');
if ($result) {
    $result = $link->query('
        CREATE TABLE `mysqlnd_test` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `val` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ');
}

if (!$result) {
    echo 'Failed create table. Exit.' . PHP_EOL; die();
}

$bulk_count = $cnf['initial_migration']['bulk_count'];
$rows_count = $cnf['initial_migration']['rows_count'];

$count = 0;
$query = '';
$insert = 'INSERT INTO `mysqlnd_test` (`id`, `val`) VALUES ';

for ($i = 1; $i <= $rows_count; $i++) {
    if ($count == $bulk_count) {
        // run bulk insert
        $query = substr($query, 0, strlen($query) - 2);
        $query .= ';';
        if ($link->query($query)) {
            // reset counter
            $count = 0;
            $query = '';
            echo 'Successfully inserted ' . $bulk_count . ' rows.' . PHP_EOL;
        } else {
            echo 'Failed insert ' . $bulk_count . ' rows. Exit.' . PHP_EOL; die();
        }

    }

    if($count == 0) {
        $query .= $insert;
    }

    if ($count < $bulk_count) {
        $query .= '( "", "test' . $i . '" ), ';
        $count++;
    }
}