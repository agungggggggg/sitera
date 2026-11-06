<?php

return [
    'seed_enabled' => (bool) env('SITERA_DEMO_SEED_ENABLED', false),
    'approved_target' => (bool) env('SITERA_DEMO_SEED_ENABLED', false)
        && (string) env('APP_ENV', 'production') === 'local'
        && app()->basePath() === 'C:\\Users\\agung\\Documents\\sistem-laporan-irigasi-laravel'
        && (string) config('database.default') === 'mysql'
        && (string) config('database.connections.mysql.database') === 'sistem_laporan_irigasi'
        && in_array((string) config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true),
];
