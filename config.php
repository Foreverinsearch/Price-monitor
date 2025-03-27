<?php
declare(strict_types=1);

/**
 * Конфигурация системы мониторинга цен PriceMonitor
 * 
 * ВНИМАНИЕ:
 * 1. Для защиты данных НЕ коммитьте этот файл в git (добавьте в .gitignore)
 * 2. Реальные токены и пароли храните в .env файле
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Настройки парсеров
    |--------------------------------------------------------------------------
    */
    'parsers' => [
        // Общие настройки парсинга
        'default' => [
            'timeout' => 30,      // Таймаут запросов в секундах
            'retry_attempts' => 3 // Количество попыток повтора при ошибках
        ],
        
        // Настройки для конкретных магазинов
        'ozon' => [
            'enabled' => true,
            'proxy_list' => [     // Прокси для обхода блокировок
                'proxy1.example.com:3128',
                'proxy2.example.com:3128'
            ],
            'selectors' => [      // CSS-селекторы для парсинга
                'product' => '[data-widget="searchResultsV2"] article',
                'name' => '[data-widget="webProductHeading"]',
                'price' => '[data-widget="webPrice"]'
            ]
        ],
        
        'wildberries' => [
            'enabled' => true,
            'api_key' => env('WB_API_KEY', '') // Ключ API (если доступен)
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Уведомления
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'telegram' => [
            'enabled' => true,
            'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
            'main_chat_id' => env('TELEGRAM_MAIN_CHAT', ''),
            'alert_chat_id' => env('TELEGRAM_ALERT_CHAT', ''),
            'parse_mode' => 'HTML' // или 'Markdown'
        ],
        
        'email' => [
            'enabled' => false,
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'username' => 'noreply@example.com',
            'password' => env('EMAIL_PASSWORD', ''),
            'from_address' => 'monitoring@example.com',
            'to_address' => 'admin@example.com'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Хранение данных
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default' => 'json', // Варианты: json, sqlite, mysql
        
        'json' => [
            'path' => __DIR__ . '/data/prices.json',
            'auto_backup' => true
        ],
        
        'sqlite' => [
            'path' => __DIR__ . '/data/prices.db',
            'journal_mode' => 'WAL'
        ],
        
        'mysql' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => 'price_monitor',
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', ''),
            'charset' => 'utf8mb4'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Планировщик задач
    |--------------------------------------------------------------------------
    */
    'scheduler' => [
        'check_interval' => 3600, // Интервал проверки в секундах (1 час)
        'working_hours' => [      // Часы работы (чтобы не ддосить сайты ночью)
            'start' => 8,         // 8:00
            'end' => 22           // 22:00
        ],
        'timezone' => 'Europe/Moscow'
    ],

    /*
    |--------------------------------------------------------------------------
    | Настройки логирования
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'path' => __DIR__ . '/logs',
        'level' => 'debug', // Уровни: debug, info, warning, error
        'max_files' => 7     // Хранение логов за 7 дней
    ]
];

/**
 * Вспомогательная функция для чтения .env
 */
function env(string $key, mixed $default = null): mixed
{
    static $env = null;
    
    if ($env === null) {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
        } else {
            $env = [];
        }
    }
    
    return $env[$key] ?? $default;
}
