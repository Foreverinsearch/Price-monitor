📊 PriceMonitor: Система мониторинга цен конкурентов
GitHub
License
Docker

PriceMonitor — это профессиональное решение для автоматического отслеживания цен на товары в интернет-магазинах (Ozon, Wildberries и др.) с аналитикой изменений и мгновенными уведомлениями.

🌟 Возможности
Мультиплатформенный парсинг (Ozon, Wildberries, AliExpress)

Интеллектуальное сравнение цен с детектированием изменений

Уведомления в Telegram/Email при изменении цен

Гибкое хранение данных (JSON, SQLite, MySQL)

Консольное управление с интерактивным интерфейсом

Docker-поддержка для быстрого развертывания

🚀 Быстрый старт
Требования
PHP 8.2+

Composer

(Опционально) Docker

Установка

bash
Copy
git clone https://github.com/ваш-репозиторий/PriceMonitor.git
cd PriceMonitor
composer install

Настройка
Создайте конфигурационный файл:

bash
Copy
cp config.example.php config.php
Заполните настройки в config.php:


return [
    'telegram' => [
        'bot_token' => 'ВАШ_BOT_TOKEN',
        'chat_id' => 'ВАШ_CHAT_ID'
    ],
    'storage' => [
        'type' => 'json', // или 'database'
        'path' => __DIR__ . '/data/prices.json'
    ]
];

Запуск

bash

./cli.php setup     # Первоначальная настройка
./cli.php run       # Запуск мониторинга

🛠 Команды CLI
Команда	Описание	Пример
run	Запуск мониторинга	./cli.php run -f
report	Генерация отчета	./cli.php report week --export=csv
source:add	Добавить источник	./cli.php source:add
setup	Первоначальная настройка	./cli.php setup
test:parser	Тест парсера	./cli.php test:parser ozon
🐳 Запуск через Docker

docker-compose up -d --build
docker exec -it price-monitor ./cli.php setup

📊 Примеры отчетов
Изменение цен
Copy
📊 Изменения цен на ozon (15.07.2023)

📉 iPhone 15 Pro
Было: 99 990 ₽
Стало: 94 990 ₽ (-5.0%)

🆕 Samsung Galaxy S23
Новая цена: 79 990 ₽
График цен
Пример графика цен

📂 Структура проекта
Copy
PriceMonitor/
├── src/
│   ├── Parser/          # Парсеры магазинов
│   ├── Command/         # CLI-команды
│   ├── Notifier.php     # Система уведомлений
│   └── DataStorage.php  # Хранение данных
├── data/                # База данных/файлы
├── config.php           # Конфигурация
├── cli.php              # Точка входа CLI
├── docker-compose.yml
└── README.md
🔧 Технологический стек
PHP 8.2+ с strict typing

Symfony Console для CLI

GuzzleHTTP для запросов

League CSV для работы с данными

Docker для контейнеризации

📈 Планы развития
Поддержка API Wildberries

Визуальный дашборд

Мобильное приложение

Интеграция с ChatGPT для анализа трендов

Автор: [Роман]
Контакты: rova.leo@yandex.ru
Версия: 1.0.0
