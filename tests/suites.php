<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

/**
 * Test suites for Lifecycle component.
 */
return [
    new SuiteConfig(
        name: 'Lifecycle/Self',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
    ),
    new SuiteConfig(
        name: 'Lifecycle/Unit',
        location: new FinderConfig(
            include: [__DIR__ . '/Unit'],
            exclude: [__DIR__ . '/Unit/Fixture'],
        ),
    ),
    new SuiteConfig(
        name: 'Lifecycle/Feature',
        location: new FinderConfig(
            include: [__DIR__ . '/Feature'],
        ),
    ),
];
