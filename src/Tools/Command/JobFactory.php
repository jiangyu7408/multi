<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:41.
 */
namespace Tools\Command;

/**
 * Class JobFactory.
 */
class JobFactory
{
    /** @var int */
    protected $status;
    /** @var Job[] */
    protected $jobs;

    /**
     * Task constructor.
     *
     * @param string $errLogPath
     */
    public function __construct($errLogPath)
    {
        $this->errLogPath = $errLogPath;
    }

    /**
     * @param string $identity
     * @param string $cmd
     * @param string $workDir
     *
     * @return Job
     */
    public function makeJob($identity, $cmd, $workDir)
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
            2 => ['file', $this->errLogPath, 'a'], // stderr is a file to write to
        ];
        $pipes = [];
        $wrappedCmd = $cmd;
        $process = proc_open($wrappedCmd, $descriptorSpec, $pipes, $workDir, []);
        if (!is_resource($process)) {
            throw new \RuntimeException('not process');
        }

        $job = new Job($identity, $cmd, $process, $pipes[1]);
        $this->jobs[$cmd] = $job;

        return $job;
    }
}
