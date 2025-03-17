<?php

use Orchestra\Testbench\TestCase;

pest()->extend(Tests\TestCase::class)
    ->in('Feature', 'Unit');

