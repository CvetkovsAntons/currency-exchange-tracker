<?php

namespace App\Command;

use App\Enum\Argument;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use App\Trait\CommandCurrencyUtilsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:exchange-rate:stop-tracking',
    description: 'Stop track of exchange rate for two currencies',
)]
class ExchangeRateStopTrackingCommand extends AbstractCommand
{
    use CommandCurrencyUtilsTrait;

    public function __construct(
        private readonly CurrencyService        $currencyService,
        private readonly CurrencyPairService    $pairService,
        private readonly ExchangeRateService    $rateService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger
    )
    {
        parent::__construct($this->logger);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: Argument::FROM->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. PHP)',
            )
            ->addArgument(
                name: Argument::TO->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. PHP)',
            );
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Stop track of currencies exchange rate');

        $this->em->beginTransaction();

        try {
            $from = $this->getCurrency(Argument::FROM, $input, $io, false);
            $to = $this->getCurrency(Argument::TO, $input, $io, false);

            $pair = $this->pairService->get($from, $to);

            if (is_null($pair)) {
                throw new CurrencyPairNotFoundException($from->getCode(), $to->getCode());
            }

            $exchangeRate = $this->rateService->get($pair);

            if (!is_null($exchangeRate)) {
                $this->rateService->delete($exchangeRate);
            }

            $this->em->commit();

            $io->success(sprintf(
                "Tracking of %s-%s exchange rate has been stoped successfully!",
                $from->getCode(),
                $to->getCode()
            ));
        } catch (Throwable $e) {
            $this->em->rollback();
            $this->em->close();
            throw $e;
        }
    }

    protected function currencyService(): CurrencyService
    {
        return $this->currencyService;
    }

}
