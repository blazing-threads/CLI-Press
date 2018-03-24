<?php
use BlazingThreads\CliPress\Application;

/**
 * @return Application
 */
function app()
{
    return Application::getInstance();
}

/**
 * @param $string
 * @return mixed|string
 */
function camelCase($string)
{
    static $cache = [];

    if (isset($cache[$string])) {
        return $cache[$string];
    }

    $result = ucwords(str_replace(['-', '_'], ' ', @(string) $string));
    return $cache[$string] = lcfirst(str_replace(' ', '', $result));
}

/**
 * @param $json
 * @return array|mixed
 */
function jsonOrEmptyArray($json)
{
    $json = json_decode($json, true);
    return json_last_error() === JSON_ERROR_NONE ? $json : [];
}

/**
 * @param $string
 * @return mixed|string
 */
function kebabCase($string)
{
    static $cache = [];

    if (isset($cache[$string])) {
        return $cache[$string];
    }

    return $cache[$string] = strtolower(preg_replace('/((?<=[^$])[A-Z]|[0-9]+)/', '-$1', $string));
}

