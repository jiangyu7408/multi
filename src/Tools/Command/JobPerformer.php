<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:42.
 */
namespace Tools\Command;

/**
 * Class JobPerformer.
 */
class JobPerformer
{
    /** @var Job[] */
    protected $pendingJobs = [];
    /** @var JobHandler[] */
    protected $jobHandlers;
    /** @var Job[] */
    protected $finishedJobs = [];
    /** @var resource[] */
    protected $pipes = [];
    /** @var string[] */
    protected $pipeMapping = [];

    /**
     * @param Job        $job
     * @param JobHandler $handler
     */
    public function addJob(Job $job, JobHandler $handler)
    {
        if (isset($this->pendingJobs[$job->cmd])) {
            throw new \InvalidArgumentException(sprintf('job[%s] is running', $job->cmd));
        }
        $this->pendingJobs[$job->cmd] = $job;
        $this->jobHandlers[$job->cmd] = $handler;

        $this->pipes[$job->cmd] = $job->output;
        $this->pipeMapping[(string) $job->output] = $job->cmd;
    }

    public function run()
    {
        while (true) {
            if (count($this->pipes) === 0) {
                break; // no pending jobs
            }
            $read = $this->pipes;
            $write = $except = [];
            $ret = stream_select($read, $write, $except, 1, 500);
            if ($ret === false) {
                break;
            }
            if ($ret > 0) {
                foreach ($read as $readyPipe) {
                    $content = fgets($readyPipe);
                    $cmd = $this->mapToCmd($readyPipe);
                    if ($content === false) {
                        $this->onJobFinish($cmd);
                        continue;
                    }
                    $this->jobHandlers[$cmd]->handle($cmd, $readyPipe, $content);
                }
            }
        }
    }

    /**
     * @return Job[]
     */
    public function getFinishedJobs()
    {
        return $this->finishedJobs;
    }

    /**
     * @param resource $pipe
     *
     * @return string
     */
    private function mapToCmd($pipe)
    {
        return $this->pipeMapping[(string) $pipe];
    }

    /**
     * @param string $cmd
     */
    private function onJobFinish($cmd)
    {
        $job = $this->pendingJobs[$cmd];
        $job->finish();
        unset($this->pipes[$cmd]);
        unset($this->pendingJobs[$cmd]);
        $this->finishedJobs[$cmd] = $job;
    }
}
