#!/usr/bin/env php
<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Remcosmits\PhpunitWrapper\PHPUnitWrapperRegisterCommand;
use Remcosmits\PhpunitWrapper\SetupCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$defaultCommand = new PHPUnitWrapperRegisterCommand();

// add commands
$application->add(new SetupCommand());
$application->add($defaultCommand);

// set default command
$application->setDefaultCommand($defaultCommand->getName());

$application->run();
