#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PriceMonitor\Command\{
    RunCommand,
    AddSourceCommand,
    ReportCommand,
    SetupCommand
};
use Symfony\Component\Console\Application;

$app = new Application('PriceMonitor CLI', '1.0.0');

// Регистрируем команды
$app->add(new RunCommand());
$app->add(new AddSourceCommand());
$app->add(new ReportCommand());
$app->add(new SetupCommand());

$app->run();
