<?php

spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    $filename = __DIR__ . "/{$file}";
    if (file_exists($filename)) {
        require_once $filename;
        return true;
    }
    return false;
});
