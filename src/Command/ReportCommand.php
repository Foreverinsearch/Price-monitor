namespace PriceMonitor\Command;

// ... аналогичные импорты ...

class ReportCommand extends Command
{
    protected static $defaultName = 'report';

    protected function configure()
    {
        $this
            ->setDescription('Генерирует отчёт')
            ->addArgument(
                'period',
                InputArgument::OPTIONAL,
                'Период (day/week/month)',
                'week'
            )
            ->addOption(
                'export',
                'e',
                InputOption::VALUE_REQUIRED,
                'Формат экспорта (csv/json/html)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $period = $input->getArgument('period');
        
        $io->section("📊 Генерация отчёта за {$period}");

        $data = $this->generateReportData($period);

        if ($format = $input->getOption('export')) {
            $this->exportReport($data, $format);
            $io->success("Экспорт в {$format} завершен");
        } else {
            $io->table(['Товар', 'Изменение'], $data);
        }

        return Command::SUCCESS;
    }
}
