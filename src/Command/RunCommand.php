namespace PriceMonitor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunCommand extends Command
{
    protected static $defaultName = 'run';

    protected function configure()
    {
        $this
            ->setDescription('Запускает мониторинг цен')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Принудительный запуск, даже если не время'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        if ($this->isAlreadyRunning()) {
            $io->error('Мониторинг уже запущен!');
            return Command::FAILURE;
        }

        $io->title('🔄 Запуск мониторинга цен');
        
        // Основная логика
        $result = $this->runMonitoring($input->getOption('force'));

        if ($result) {
            $io->success('Мониторинг завершен');
            return Command::SUCCESS;
        }

        $io->error('Ошибка мониторинга');
        return Command::FAILURE;
    }
}
