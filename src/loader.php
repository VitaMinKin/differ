<?php

namespace Differ\loader;

function read($path)
{
    $absPath = realpath($path);
    if ($absPath !== false) {
        return file_get_contents($absPath);
    } else {
        throw new \Exception("Invalid config path passed: {$path}");
    }
}

function getFormat(string $path)
{
    return pathinfo($path, PATHINFO_EXTENSION);
}
