<?php

namespace App\Command;

use App\Factory\CurrencyFactory;
use App\Repository\CurrencyRepository;
use App\Service\CurrencyApiService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:currency:create',
    description: 'Creates a new currency',
)]
class CurrencyCreateCommand extends Command
{
    private const string ARG_CURRENCY_CODE = 'currency-code';

    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly CurrencyFactory    $factory,
        private readonly CurrencyApiService $apiService,
        private readonly LoggerInterface    $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            self::ARG_CURRENCY_CODE,
            InputArgument::REQUIRED,
            'Unique currency code. Example: EUR'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $currencyCode = $input->getArgument(self::ARG_CURRENCY_CODE);
        $currencyCode = strtoupper($currencyCode);

        try {
            if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
                throw new Exception('Incorrect currency code format as per ISO 4217 standard. Example: EUR');
            }

            if (!$this->apiService->isAlive()) {
                throw new Exception("Couldn't connect to API");
            }

            $currencyData = $this->apiService->getCurrencyData($currencyCode);
            $io->note(print_r($currencyData, true));

            if (empty($currencyData)) {
                throw new Exception("Data for $currencyCode not found");
            }

            $currency = $this->factory->create();

            $io->success(" ($currencyCode) currency has been created!");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error("Couldn't create currency: " . $e->getMessage());
            $this->logger->error($e);
            return Command::FAILURE;
        }
    }

}
