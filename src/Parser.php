<?php
declare(strict_types=1);

namespace PriceMonitor;

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Parser
{
    private Client $httpClient;
    private array $userAgents;
    private ?string $proxy;

    public function __construct(?string $proxy = null)
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false // Для локального тестирования
        ]);

        $this->userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'
        ];

        $this->proxy = $proxy;
    }

    /**
     * Парсит цены с указанного источника
     * 
     * @throws \RuntimeException
     */
    public function parse(string $source, string $url): array
    {
        return match ($source) {
            'ozon' => $this->parseOzon($url),
            'wildberries' => $this->parseWildberries($url),
            default => throw new \RuntimeException("Unknown source: $source")
        };
    }

    private function parseOzon(string $url): array
    {
        $html = $this->fetchHtml($url);
        $crawler = new Crawler($html);

        $products = [];
        $crawler->filter('[data-widget="searchResultsV2"] > div > div > div > div > article')->each(
            function (Crawler $node) use (&$products) {
                try {
                    $name = $node->filter('[data-widget="webProductHeading"]')->text();
                    $price = $node->filter('[data-widget="webPrice"]')->text();
                    
                    $products[] = [
                        'name' => trim($name),
                        'price' => (int)preg_replace('/[^0-9]/', '', $price),
                        'timestamp' => time()
                    ];
                } catch (\Exception $e) {
                    // Пропускаем битые карточки
                }
            }
        );

        return $products;
    }

    private function parseWildberries(string $url): array
    {
        $html = $this->fetchHtml($url);
        $crawler = new Crawler($html);

        $products = [];
        $crawler->filter('.product-card__wrapper')->each(
            function (Crawler $node) use (&$products) {
                try {
                    $name = $node->filter('.product-card__name')->text();
                    $price = $node->filter('.price__lower-price')->text();
                    
                    $products[] = [
                        'name' => trim($name),
                        'price' => (int)preg_replace('/[^0-9]/', '', $price),
                        'timestamp' => time()
                    ];
                } catch (\Exception $e) {
                    // Пропускаем битые карточки
                }
            }
        );

        return $products;
    }

    /**
     * Загружает HTML-страницу с обработкой ошибок
     */
    private function fetchHtml(string $url): string
    {
        $options = [
            'headers' => [
                'User-Agent' => $this->userAgents[array_rand($this->userAgents)],
                'Accept' => 'text/html,application/xhtml+xml',
            ]
        ];

        if ($this->proxy) {
            $options['proxy'] = $this->proxy;
        }

        try {
            $response = $this->httpClient->get($url, $options);
            return (string)$response->getBody();
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to fetch $url: " . $e->getMessage());
        }
    }
}
