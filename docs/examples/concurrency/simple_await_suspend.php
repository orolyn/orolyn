<?php

namespace Demo;

use function Orolyn\Lang\Await;
use function Orolyn\Lang\Suspend;

require '../../../vendor/autoload.php';

$time = microtime(true);

function get_time()
{
    global $time;

    return floor((microtime(true) - $time));
}

function task1()
{
    Suspend(1000); // Release at second 1
    var_dump('function task1() A - ' . get_time());
    Suspend(2000); // Release at second 3
    var_dump('function task1() B - ' . get_time());
}

function task2()
{
    Suspend(2000); // Release at second 2
    var_dump('function task2() A - ' . get_time());
    Suspend(2000); // Release at second 4
    var_dump('function task2() B - ' . get_time());
}

Await(
    task1(...),
    task2(...)
);

/*
string(22) "function task1() A - 1"
string(22) "function task2() A - 2"
string(22) "function task1() B - 3"
string(22) "function task2() B - 4"
*/
