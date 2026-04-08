<?php

namespace App\Logging;

use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        return new Logger('database', [
            new DatabaseHandler($config['level'] ?? 'debug'),
        ]);
    }
}
