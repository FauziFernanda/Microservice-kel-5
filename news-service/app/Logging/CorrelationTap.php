<?php

namespace App\Logging;

use Monolog\Logger;

class CorrelationTap
{
    public function __invoke($logger)
    {
        $logger->pushProcessor(function ($record) {
            $corr = app()->bound('correlation_id') ? app('correlation_id') : null;
            $record['extra']['correlation_id'] = $corr;
            $record['extra']['service'] = config('app.name', 'news-service');
            return $record;
        });
    }
}
