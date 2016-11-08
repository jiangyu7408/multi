<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:42.
 */
namespace Tools\Command;

use Generator;

/**
 * Class JobHandler.
 */
class JobHandler
{
    /** @var Generator */
    protected $gen;

    /**
     * JobHandler constructor.
     *
     * @param Generator $gen
     */
    public function __construct(Generator $gen)
    {
        $gen->rewind();
        $gen->next();
        $this->gen = $gen;
    }

    /**
     * @param string $filePath
     *
     * @return Generator
     */
    public static function makeForwardHandler($filePath)
    {
        yield;
        $handler = fopen($filePath, 'w');
        fseek($handler, 0, SEEK_END);
        list($pipe, $input) = yield;
        while (true) {
            $input = rtrim($input);
            if (stripos($input, 'error') !== false) {
                Debug::red(sprintf('JOB[%s] Error', $pipe), $input);
            } else {
                fwrite($handler, sprintf('%-20s %s'.PHP_EOL, $pipe, $input));
            }
            list($pipe, $input) = yield;
        }
    }

    /**
     * @return Generator
     */
    public static function makeJobHandler()
    {
        yield;
        list($pipe, $input) = yield;
        while (true) {
            $input = rtrim($input);
            if (stripos($input, 'error') !== false) {
                Debug::red(sprintf('JOB[%s] Error', $pipe), $input);
            }
            list($pipe, $input) = yield;
        }
    }

    /**
     * @param string   $cmd
     * @param resource $pipe
     * @param string   $content
     */
    public function handle($cmd, $pipe, $content)
    {
        $this->gen->send([$pipe, sprintf('[%s] => %s', $this->shortenCmd($cmd), $content)]);
    }

    /**
     * @param string $cmd
     *
     * @return string
     */
    private function shortenCmd($cmd)
    {
        list(, $filePath) = explode(' ', $cmd);

        return basename($filePath);
    }
}
