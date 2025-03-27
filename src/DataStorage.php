<?php
declare(strict_types=1);

namespace PriceMonitor;

use League\Csv\Reader;
use League\Csv\Writer;
use PDO;

class DataStorage
{
    private string $storageType;
    private string $storagePath;
    private ?PDO $pdo = null;

    public function __construct(array $config)
    {
        $this->storageType = $config['type'];
        $this->storagePath = $config['path'];

        if ($this->storageType === 'database') {
            $this->initDatabase();
        } elseif ($this->storageType === 'json') {
            $this->ensureDirExists(dirname($this->storagePath));
        }
    }

    /**
     * Сравнивает новые данные с сохранёнными и возвращает изменения
     */
    public function compareAndSave(array $newData): array
    {
        $changes = [];

        foreach ($newData as $source => $products) {
            $savedData = $this->loadData($source);
            $changes[$source] = $this->detectChanges($savedData, $products);
            $this->saveData($source, $products);
        }

        return array_filter($changes);
    }

    private function detectChanges(array $old, array $new): array
    {
        $changes = [];

        foreach ($new as $newProduct) {
            $found = false;
            
            foreach ($old as $oldProduct) {
                if ($this->isSameProduct($oldProduct, $newProduct)) {
                    $found = true;
                    
                    if ($oldProduct['price'] != $newProduct['price']) {
                        $changes[] = [
                            'name' => $newProduct['name'],
                            'old_price' => $oldProduct['price'],
                            'new_price' => $newProduct['price'],
                            'change_percent' => $this->calculateChangePercent(
                                $oldProduct['price'],
                                $newProduct['price']
                            )
                        ];
                    }
                    break;
                }
            }

            if (!$found) {
                $changes[] = [
                    'name' => $newProduct['name'],
                    'old_price' => null,
                    'new_price' => $newProduct['price'],
                    'change_percent' => 100
                ];
            }
        }

        return $changes;
    }

    private function isSameProduct(array $a, array $b): bool
    {
        // Улучшенное сравнение с учётом возможных изменений в названии
        $nameSimilarity = similar_text(
            mb_strtolower($a['name']),
            mb_strtolower($b['name']),
            $percent
        );

        return $percent > 70;
    }

    private function calculateChangePercent(float $old, float $new): float
    {
        return round((($new - $old) / $old) * 100, 2);
    }

    private function loadData(string $source): array
    {
        return match ($this->storageType) {
            'json' => $this->loadJson($source),
            'database' => $this->loadFromDb($source),
            default => []
        };
    }

    private function saveData(string $source, array $data): void
    {
        match ($this->storageType) {
            'json' => $this->saveJson($source, $data),
            'database' => $this->saveToDb($source, $data)
        };
    }

    // ===== JSON Реализация =====
    private function loadJson(string $source): array
    {
        if (!file_exists($this->storagePath)) {
            return [];
        }

        $data = json_decode(file_get_contents($this->storagePath), true);
        return $data[$source] ?? [];
    }

    private function saveJson(string $source, array $data): void
    {
        $allData = [];

        if (file_exists($this->storagePath)) {
            $allData = json_decode(file_get_contents($this->storagePath), true) ?: [];
        }

        $allData[$source] = $data;
        file_put_contents($this->storagePath, json_encode($allData, JSON_PRETTY_PRINT));
    }

    // ===== Database Реализация =====
    private function initDatabase(): void
    {
        $isNewDb = !file_exists($this->storagePath);

        $this->pdo = new PDO("sqlite:" . $this->storagePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($isNewDb) {
            $this->createTables();
        }
    }

    private function createTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                source TEXT NOT NULL,
                name TEXT NOT NULL,
                price INTEGER NOT NULL,
                timestamp INTEGER NOT NULL,
                UNIQUE(source, name) ON CONFLICT REPLACE
            )
        ");
    }

    private function loadFromDb(string $source): array
    {
        $stmt = $this->pdo->prepare("
            SELECT name, price, timestamp 
            FROM products 
            WHERE source = :source
            ORDER BY timestamp DESC
        ");
        $stmt->execute([':source' => $source]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveToDb(string $source, array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (source, name, price, timestamp)
            VALUES (:source, :name, :price, :timestamp)
        ");

        foreach ($data as $item) {
            $stmt->execute([
                ':source' => $source,
                ':name' => $item['name'],
                ':price' => $item['price'],
                ':timestamp' => time()
            ]);
        }
    }

    private function ensureDirExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
