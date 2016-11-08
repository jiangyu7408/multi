<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:39.
 */
namespace Tools\Command;

use PHP_Timer;

/**
 * Class Job.
 *
 * @property string   identity
 * @property string   cmd
 * @property resource process
 * @property resource output
 * @property int      status
 * @property string   summary
 */
class Job
{
    /** @var float */
    protected $startTs;
    /** @var float */
    protected $stopTs;

    /**
     * Job constructor.
     *
     * @param string   $identity
     * @param string   $cmd
     * @param resource $process
     * @param resource $output
     */
    public function __construct($identity, $cmd, $process, $output)
    {
        $this->startTs = microtime(true);
        $this->identity = $identity;
        $this->cmd = $cmd;
        $this->process = $process;
        $this->output = $output;
        stream_set_blocking($this->output, 0);
    }

    /**
     * @param string   $identity
     * @param string   $cmd
     * @param resource $pipe
     * @param float    $delta
     *
     * @return string
     */
    public static function format($identity, $cmd, $pipe, $delta)
    {
        return sprintf(
            '%-20s %-35s %-20s %s',
            $pipe,
            $identity,
            self::shortenCmd($cmd),
            PHP_Timer::secondsToTimeString($delta)
        );
    }

    public function finish()
    {
        fclose($this->output);
        $this->status = proc_close($this->process);
        $this->stopTs = microtime(true);
        $this->summary = self::format($this->identity, $this->cmd, $this->output, ($this->stopTs - $this->startTs));
        echo $this->summary.PHP_EOL;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status !== -1;
    }

    /**
     * @return float
     */
    public function getProfile()
    {
        return ($this->stopTs - $this->startTs) * 1000;
    }

    /**
     * @param string $cmd
     *
     * @return string
     */
    private static function shortenCmd($cmd)
    {
        if (strpos($cmd, ' ') === false) {
            return $cmd;
        }
        list ($command, $filePath) = explode(' ', $cmd);
        if (strpos($command, 'php') !== false) {
            return sprintf('%-50s', basename($filePath));
        }

        return $cmd;
    }
}
