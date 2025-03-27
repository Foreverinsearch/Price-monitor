<?php
declare(strict_types=1);

namespace PriceMonitor;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Notifier
{
    private array $config;
    private Client $httpClient;
    private string $logPath;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = new Client(['timeout' => 10]);
        $this->logPath = __DIR__ . '/../logs/notifications.log';
        $this->ensureLogDirExists();
    }

    /**
     * Отправляет уведомление об изменениях цен
     */
    public function sendChanges(array $changes): void
    {
        foreach ($changes as $source => $sourceChanges) {
            if (empty($sourceChanges)) continue;

            $message = $this->formatChangesMessage($source, $sourceChanges);
            
            // Отправка в Telegram
            if (!empty($this->config['telegram'])) {
                $this->sendTelegram($message);
            }

            // Логирование
            $this->logNotification($message);
        }
    }

    /**
     * Отправляет экстренное уведомление об ошибке
     */
    public function sendAlert(string $error): void
    {
        $message = "🚨 Ошибка в системе мониторинга:\n{$error}";
        
        if (!empty($this->config['telegram']['alert_chat_id'])) {
            $this->sendTelegram($message, true);
        }

        $this->logNotification($message, 'ERROR');
    }

    private function sendTelegram(string $message, bool $isAlert = false): void
    {
        $chatId = $isAlert 
            ? $this->config['telegram']['alert_chat_id']
            : $this->config['telegram']['chat_id'];

        try {
            $response = $this->httpClient->post(
                "https://api.telegram.org/bot{$this->config['telegram']['bot_token']}/sendMessage",
                [
                    'form_params' => [
                        'chat_id' => $chatId,
                        'text' => $message,
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Telegram API error');
            }

        } catch (GuzzleException $e) {
            $this->logNotification(
                "Telegram send failed: " . $e->getMessage(),
                'ERROR'
            );
        }
    }

    private function formatChangesMessage(string $source, array $changes): string
    {
        $message = "<b>📊 Изменения цен на {$source}</b>\n\n";
        $message .= "⏰ " . date('d.m.Y H:i') . "\n\n";

        foreach ($changes as $change) {
            if ($change['old_price'] === null) {
                $message .= "🆕 <b>Новый товар:</b> {$change['name']}\n";
                $message .= "💰 Цена: {$change['new_price']} руб.\n\n";
            } else {
                $trend = $change['new_price'] > $change['old_price'] ? '📈' : '📉';
                $message .= "{$trend} <b>{$change['name']}</b>\n";
                $message .= "Старая цена: {$change['old_price']} руб.\n";
                $message .= "Новая цена: <b>{$change['new_price']}</b> руб. ";
                $message .= "({$change['change_percent']}%)\n\n";
            }
        }

        $message .= "🔗 <i>Подробнее в системе мониторинга</i>";

        return $message;
    }

    private function logNotification(string $message, string $level = 'INFO'): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $level,
            str_replace("\n", " ", strip_tags($message))
        );

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }

    private function ensureLogDirExists(): void
    {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
