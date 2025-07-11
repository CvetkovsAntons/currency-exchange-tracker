<?php

namespace App\Trait;

use App\Entity\Currency;
use App\Enum\Argument;
use App\Exception\Currency\CurrencyNotFoundException;
use App\Exception\Currency\DuplicateCurrencyCodeException;
use App\Exception\Currency\InvalidCurrencyCodeException;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Exception\CurrencyApi\CurrencyDataNotFoundException;
use App\Exception\ExternalApi\ExternalApiRequestException;
use App\Service\Domain\CurrencyService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

trait CommandCurrencyUtilsTrait
{
    abstract protected function currencyService(): CurrencyService;

    /**
     * @throws ClientExceptionInterface
     * @throws ExternalApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws CurrencyDataNotFoundException
     * @throws CurrencyNotFoundException
     * @throws DecodingExceptionInterface
     * @throws DuplicateCurrencyCodeException
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getCurrency(
        Argument       $argument,
        InputInterface $input,
        SymfonyStyle   $io,
        bool           $createIfNotExists = true,
    ): ?Currency
    {
        $currencyService = $this->currencyService();

        $currencyCode = $input->getArgument($argument->value);
        if (is_null($currencyCode)) {
            $validator = function (string $input) use ($currencyService) {
                $input = strtoupper($input);

                if (!preg_match("/^[A-Z]{3}$/i", $input) || !$currencyService->isValidCode($input)) {
                    throw new InvalidCurrencyCodeException($input);
                }

                return $input;
            };

            $question = new Question('Input a currency code (e.g. PHP)')
                ->setValidator($validator);

            $currencyCode = $io->askQuestion($question);
        }

        $currencyCode = strtoupper($currencyCode);

        $currency = $currencyService->get($currencyCode);
        if (is_null($currency)) {
            if (!$createIfNotExists) {
                throw new CurrencyNotFoundException($currencyCode);
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
