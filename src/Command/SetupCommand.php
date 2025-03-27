namespace PriceMonitor\Command;

// ... импорты ...

class SetupCommand extends Command
{
    protected static $defaultName = 'setup';

    protected function configure()
    {
        $this->setDescription('Первоначальная настройка системы');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('⚙️ Настройка PriceMonitor');

        // Интерактивная настройка
        $config = [
            'telegram' => [
                'bot_token' => $io->ask('Telegram Bot Token'),
                'chat_id' => $io->ask('Chat ID для уведомлений'),
            ],
            'storage' => $io->choice(
                'Тип хранилища',
                ['json', 'database'],
                'json'
            )
        ];

        $this->saveConfig($config);

        $io->success('Настройка завершена!');
        return Command::SUCCESS;
    }
}
