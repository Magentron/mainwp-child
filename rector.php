<?php

declare(strict_types=1);

use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/class',
        __DIR__ . '/includes',
        __DIR__ . '/tests',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        DowngradeLevelSetList::DOWN_TO_PHP_73,
    ]);
};
