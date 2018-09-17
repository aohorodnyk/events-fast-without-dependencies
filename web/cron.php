<?php

if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    header('Forbidden', 403);
    exit;
}

require_once __DIR__ . '/../src/autoloader.php';

$app = new App();
try {
    $app->runJob();
} catch (\Exception $e) {
    header('Internal Error', true, 500);
    echo 'Internal Error';
}