<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PriceMonitor\Parser;
use PriceMonitor\Notifier;
use PriceMonitor\DataStorage;

// 1. Конфигурация
const CONFIG = [
    'sources' => [
        'ozon' => 'https://www.ozon.ru/category/smartfony-15502/',
        'wildberries' => 'https://www.wildberries.ru/catalog/elektronika/telefony',
    ],
    'telegram' => [
        'bot_token' => 'YOUR_BOT_TOKEN',
        'chat_id' => 'YOUR_CHAT_ID'
    ],
    'storage' => [
        'type' => 'json', // или 'database'
        'path' => __DIR__ . '/data/prices.json'
    ]
];

// 2. Инициализация компонентов
$parser = new Parser();
$notifier = new Notifier(CONFIG['telegram']);
$storage = new DataStorage(CONFIG['storage']);

// 3. Основной рабочий поток
try {
    echo "🔄 Запуск мониторинга цен...\n";
    
    $newPrices = [];
    foreach (CONFIG['sources'] as $source => $url) {
        $newPrices[$source] = $parser->parse($source, $url);
        echo "✅ $source: получено " . count($newPrices[$source]) . " товаров\n";
    }

    $changes = $storage->compareAndSave($newPrices);
    
    if (!empty($changes)) {
        $notifier->sendChanges($changes);
        echo "🔔 Отправлены уведомления о " . count($changes) . " изменениях\n";
    }

    echo "✔️ Готово! Данные сохранены\n";

} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    $notifier->sendAlert($e->getMessage());
}
