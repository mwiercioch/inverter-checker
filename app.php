#!/usr/bin/env php
<?php
// app.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\CheckInverterCommand;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$application = new Application();

$application->add(new CheckInverterCommand());

$application->run();