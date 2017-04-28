<?php
use BlazingThreads\CliPress\Application;

/**
 * @return Application
 */
function app()
{
    return Application::getInstance();
}

function camelCase($string)
{
    static $cache = [];

    if (isset($cache[$string])) {
        return $cache[$string];
    }

    $result = ucwords(str_replace(['-', '_'], ' ', @(string) $string));
    return $cache[$string] = lcfirst(str_replace(' ', '', $result));
}

function kebabCase($string)
{
    static $cache = [];

    if (isset($cache[$string])) {
        return $cache[$string];
    }

    return $cache[$string] = strtolower(preg_replace('/((?<=[^$])[A-Z]|[0-9]+)/', '-$1', $string));
}

