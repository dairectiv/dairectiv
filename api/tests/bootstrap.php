<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__).'/.env');

$appDebug = (bool) $_SERVER['APP_DEBUG'];

if ($appDebug) {
    umask(0000);
}
