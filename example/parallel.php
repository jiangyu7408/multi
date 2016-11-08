<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:49.
 */
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../bin/multi.php';

$choices = ['jobs:'];
$options = getopt('v', $choices);
if (count($options) === 0) {
    print_r($choices);
    die;
}

$feederFile = $options['jobs'];
$commands = require $feederFile;
if (!is_array($commands)) {
    die('bad job format');
}

$workDir = '/tmp';
$errLogPath = '/tmp/task.error';
$jobLog = '/tmp/multi.log';
multi($commands, $workDir, $errLogPath, $jobLog);
