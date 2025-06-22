<?php

namespace App\Service\Console;

use App\Enum\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

readonly class CommandInvokerService
{
    public function __construct(
        private Application $application,
    ) {}

    public function runCreateCurrencyPair(
        string $fromCurrencyCode,
        string $toCurrencyCode,
        ?OutputInterface $output = null
    ): void
    {
        $currencyPairCreate = new ArrayInput([
            'command' => 'app:create:currency-pair',
            Argument::CURRENCY_FROM->value => $fromCurrencyCode,
            Argument::CURRENCY_TO->value=> $toCurrencyCode,
        ]);

        $currencyPairCreate->setInteractive(false);

        $output ??= new NullOutput();

        $this->application->doRun($currencyPairCreate, $output);
    }

}
