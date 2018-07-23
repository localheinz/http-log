<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */
use Localheinz\Http\Log;
use Symfony\Component\Console;

require_once __DIR__ . '/../vendor/autoload.php';

$command = new Log\Console\Command\DashboardCommand();

$application = new Console\Application();

$application->add($command);
$application->setDefaultCommand(
    $command->getName(),
    true
);

$application->run();
