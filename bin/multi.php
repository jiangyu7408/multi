<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:45.
 */

use Tools\Command\Debug;
use Tools\Command\JobFactory;
use Tools\Command\JobHandler;
use Tools\Command\JobPerformer;

set_error_handler(function ($errno, $errstr) {
    if ($errno === E_WARNING && $errstr === 'stream_socket_accept(): accept failed: Operation timed out') {
        return;
    }
    if ($errno === E_NOTICE) {
        return;
    }
    echo $errno.' => ';
    print_r($errstr);

    $args = func_get_args();
    $args = array_splice($args, 2);
    print_r($args);
    die;
});

if (!function_exists('multi')) {
    /**
     * @param array  $commands
     * @param string $workDir
     * @param string $errorLog
     * @param string $jobLog
     */
    function multi(array $commands, $workDir, $errorLog, $jobLog)
    {
        $jobFactory = new JobFactory($errorLog);

        $jobPerformer = new JobPerformer();
        $jobHandler = new JobHandler(JobHandler::makeForwardHandler($jobLog));
        foreach ($commands as $identity => $cmd) {
            $job = $jobFactory->makeJob($identity, $cmd, $workDir);
            $jobPerformer->addJob($job, $jobHandler);
        }

        $jobPerformer->run();

        echo str_repeat('-', 40).PHP_EOL;
        $finishedJobs = $jobPerformer->getFinishedJobs();
        foreach ($finishedJobs as $cmd => $finishedJob) {
            if (!$finishedJob->isSuccess()) {
                Debug::red('Error', sprintf('[%s] invoke failed, check log', $cmd));
            }
        }
    }
}
