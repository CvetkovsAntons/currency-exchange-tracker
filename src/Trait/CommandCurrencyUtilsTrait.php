<?php

namespace App\Trait;

use App\Entity\Currency;
use App\Enum\Argument;
use App\Service\Domain\CurrencyService;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

trait CommandCurrencyUtilsTrait
{
    abstract protected function getCurrencyService(): CurrencyService;

    private function getCurrency(
        Argument       $argument,
        InputInterface $input,
        SymfonyStyle   $io,
        bool           $createIfNotExists = true,
        ?string        $question = null,
    ): ?Currency
    {
        $currencyService = $this->getCurrencyService();

        $currencyCode = $input->getArgument($argument->value);
        if (is_null($currencyCode)) {
            $question ??= 'Select a currency';

            $currencyCodes = $currencyService->getAllCodes();
            $question = new ChoiceQuestion($question, $currencyCodes)
                ->setNormalizer(fn($v) => is_numeric($v) ? $currencyCodes[$v] : $v);

            $currencyCode = $io->askQuestion($question);
        }

        $currency = $currencyService->get($currencyCode);
        if (is_null($currency)) {
            if (!$createIfNotExists) {
                throw new Exception(sprintf('Currency %s does not exist', $currencyCode));
            }

            $io->warning(sprintf(
                "Currency %s doesn't exist. Will be created",
                $currencyCode
            ));

            $currency = $currencyService->create($currencyCode);

            $io->success(sprintf('Currency %s has been created', $currencyCode));
        }

        return $currency;
    }

}
