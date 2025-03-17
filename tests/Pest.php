<?php

use Orchestra\Testbench\TestCase;

uses(TestCase::class)->in("Feature", "Unit");

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'Unit');

