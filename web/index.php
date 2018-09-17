<?php

require_once __DIR__ . '/../src/autoloader.php';

$app = new App();
try {
    $app->run();
} catch (\Exceptions\BadRequestMethodException $e) {
    header('Method Not Allowed', true, 405);
    echo $e->getMessage();
} catch (\Exceptions\ValidationException $e) {
    header('Bad Request', true, 400);
    echo $e->getMessage();
} catch (\Exception $e) {
    header('Internal Error', true, 500);
    echo 'Internal Error';
}