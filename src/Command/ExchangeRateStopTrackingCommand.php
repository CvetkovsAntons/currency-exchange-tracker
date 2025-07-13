<?php

namespace App\Command;

use App\Entity\CurrencyPair;
use App\Service\Domain\CurrencyPairService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Throwable;

#[AsCommand(
    name: 'app:exchange-rate:stop-tracking',
    description: 'Stop track of exchange rate for two currencies',
)]
class ExchangeRateStopTrackingCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyPairService    $pairService,
        private readonly LoggerInterface        $logger
    )
    {
        parent::__construct($this->logger);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Throwable
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Stop track of currencies exchange rate');

        $pairs = $this->pairService->getAllTracked();

        if (empty($pairs)) {
            $io->warning('Any tracked currency pair has not been found');
            return;
        }

        $pair = $this->getCurrencyPair($pairs, $io);

        $this->pairService->untrack($pair);

        $io->success(sprintf(
            "Tracking of %s-%s exchange rate has been stoped successfully!",
            $pair->getFromCurrency()->getCode(),
            $pair->getToCurrency()->getCode()
        ));
    }

    /**
     * @param CurrencyPair[] $pairs
     * @param SymfonyStyle $io
     * @return CurrencyPair|null
     */
    private function getCurrencyPair(array $pairs, SymfonyStyle $io): ?CurrencyPair
    {
        $choices = [];

        foreach ($pairs as $pair) {
            $from = $pair->getFromCurrency()->getCode();
            $to = $pair->getToCurrency()->getCode();
            $choices[] = sprintf('%s-%s', $from, $to);
        }

        $question = new ChoiceQuestion('Select a currency code to remove', $choices);
        $selectedPair = array_flip($choices)[$io->askQuestion($question)] ?? null;

        return $pairs[$selectedPair] ?? null;
    }

}
