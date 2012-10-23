<?php
/**
 * Polling async queries example
 *
 * @author Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @license MIT License (distributed with source code)
 * @copyright (c) 2012 Andrey Ryaguzov <dev.aeryaguzov@gmail.com>
 * @link https://github.com/aeryaguzov/php-mysqlnd-examples for the canonical source repository
 */
$cnf = include 'config.php';

$link = createLink($cnf);
$result = $link->query('SELECT 1 AS val, SLEEP(2) as sleep;', MYSQLI_ASYNC);

if ($result) {
    echo 'Executing async query...' . PHP_EOL;
} else {
    die('Can\'t execute async query');
}

if (poll($link) == 0) {
    echo 'Poll result = 0. All stream arrays must be empty!' . PHP_EOL;
    echo 'Waiting 1 second for query execution...' . PHP_EOL;
    sleep(1);
    if (poll($link) == 1) {
        echo 'Poll result = 1. Read stream array must be not empty!' . PHP_EOL;
        $result = mysqli_reap_async_query($link);
        echo 'Free query result' . PHP_EOL;
        mysqli_free_result($result);
    }
} else {
    echo 'Unexpected result: Poll result must be 0!' . PHP_EOL;
}

echo 'Now poll connection with no executing query...' . PHP_EOL;

if (poll($link) == 0) {
    echo 'Poll result = 0. Rejected stream array must be not empty!' . PHP_EOL;
} else {
    echo 'Unexpected result: Poll result must be 0!' . PHP_EOL;
}

echo 'TODO: emulate errors!' . PHP_EOL;

/**
 * Create connection
 *
 * @param array $cnf
 * @return mysqli
 */
function createLink(array $cnf)
{
    return new mysqli(
        $cnf['db']['host'],
        $cnf['db']['username'],
        $cnf['db']['password'],
        $cnf['db']['database'],
        $cnf['db']['port']
    );
}

/**
 * Poll connection
 *
 * @param mysqli $link
 * @return int
 */
function poll(mysqli $link)
{
    echo 'Poll connection...' . PHP_EOL;
    $read = $error = $reject = array();
    $read[] = $error[] = $reject[] = $link;

    $result = mysqli_poll($read, $error, $reject, 1);

    echo '[' . PHP_EOL;
    echo '  Read: ' . count($read) . PHP_EOL;
    echo '  Errors: ' . count($error) . PHP_EOL;
    echo '  Rejected: ' . count($reject) . PHP_EOL;
    echo ']' . PHP_EOL;

    return $result;
}