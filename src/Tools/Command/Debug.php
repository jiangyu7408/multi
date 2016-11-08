<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/11/08
 * Time: 15:43.
 */
namespace Tools\Command;

/**
 * Class Debug.
 */
class Debug
{
    /**
     * @param string $key
     * @param string $value
     */
    public static function red($key, $value)
    {
        printf("\x1b[33m\x1b[1m%s\x1b[0m: \x1b[31m\x1b[1m%s\x1b[0m\n", $key, $value);
    }
}
