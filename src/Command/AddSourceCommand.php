namespace PriceMonitor\Command;

// ... импорты ...

class AddSourceCommand extends Command
{
    protected static $defaultName = 'source:add';

    protected function configure()
    {
        $this->setDescription('Добавляет новый источник для мониторинга');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->section("➕ Добавление источника");
        
        $name = $io->ask('Название источника (ozon/wildberries)', null, function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Название обязательно');
            }
            return $value;
        });

        $url = $io->ask('URL страницы', null, function ($value) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('Некорректный URL');
            }
            return $value;
        });

        $this->saveNewSource($name, $url);

        $io->success("Источник {$name} добавлен!");
        return Command::SUCCESS;
    }
}
