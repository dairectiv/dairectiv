<?php

declare(strict_types=1);

use Dairectiv\SharedKernel\Infrastructure\Symfony\Kernel;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = dirname(__DIR__, 2);

require $rootDir.'/vendor/autoload.php';

new Dotenv()->bootEnv($rootDir.'/.env');

/** @var string $env */
$env = $_SERVER['APP_ENV'];

/** @var int $debug */
$debug = $_SERVER['APP_DEBUG'];

$kernel = new Kernel($env, (bool) $debug);

$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
