<?php
/**
 * Difference between sync and async queries execution
 *
 * @author Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @license MIT License (distributed with source code)
 * @copyright (c) 2012 Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @link https://github.com/aeryaguzov/php-mysqlnd-examples for the canonical source repository
 */

$cnf = include 'config.php';

$links = array();
$queries = array();
for ($i = 1; $i <= 3; $i++) {
    $links[$i] = new mysqli(
        $cnf['db']['host'],
        $cnf['db']['username'],
        $cnf['db']['password'],
        $cnf['db']['database'],
        $cnf['db']['port']
    );

    $queries[$i] = 'SELECT ' . $i . ' AS val, SLEEP(' . $i . ') as sleep';
}
$results = array();
echo 'Start executing queries synchronously...' . PHP_EOL;

$start = microtime(true);
for ($i = 1; $i <= 3; $i++) {
    $link = $links[$i];
    $result = $link->query($queries[$i]);
    while($row = mysqli_fetch_assoc($result)) {
        $results[] = $row['val'];
    }
}
$stop = microtime(true);

echo 'Results: ' . PHP_EOL;
print_r($results);
echo 'Sync execution time (seconds): ' . ($stop - $start) . PHP_EOL;

echo 'Clear results...' . PHP_EOL;
$results = array();
echo 'Start executing queries asynchronously...' . PHP_EOL;

$start = microtime(true);
for ($i = 1; $i <= 3; $i++) {
    $link = $links[$i];
    $link->query($queries[$i], MYSQLI_ASYNC);
}

$all_links = $links;
do {
    $read = $errors = $reject = array();
    foreach($all_links as $link) {
        $read[] = $link;
        $errors[] = $link;
        $reject[] = $link;
    }

    if (!mysqli_poll($read, $errors, $reject, 1)) {
        continue;
    }

    foreach($all_links as $id => $link) {
        if ($result = mysqli_reap_async_query($link)) {
            while($row = mysqli_fetch_assoc($result)) {
                $results[] = $row['val'];
            }
            mysqli_free_result($result);
            unset($all_links[$id]);
        }
    }
} while(count($all_links) > 0);
$stop = microtime(true);
echo 'Results: ' . PHP_EOL;
print_r($results);
echo 'Async execution time (seconds): ' . ($stop - $start) . PHP_EOL;