<?php

namespace App\Provider;

use App\Client\CurrencyApiClient;
use App\Dto\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyApi\CurrencyApiRequestException;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CurrencyApiProvider
{
    public function __construct(
        private CurrencyApiClient     $apiService,
        private DenormalizerInterface $serializer,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws CurrencyApiRequestException
     */
    public function isAlive(): bool
    {
        $response = $this->apiService->status();

        return $response->getStatusCode() === 200;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CurrencyApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrencies(string ...$code): array
    {
        if (!$this->isAlive()) {
            throw new CurrencyApiUnavailableException();
        }

        $response = $this->apiService->currencies(...$code);

        return array_map(
            callback: fn ($v) => $this->serializer->denormalize($v, Currency::class),
            array: $response->toArray()['data'] ?? [],
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CurrencyApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrency(string $code): ?Currency
    {
        $currencies = $this->getCurrencies($code);

        return $currencies[$code] ?? null;
    }

    /**
     * Returns decimal string
     *
     * @throws ClientExceptionInterface
     * @throws CurrencyApiRequestException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getLatestExchangeRate(CurrencyPair $currencyPair): ?string
    {
        $fromCurrencyCode = $currencyPair->getFromCurrency()->getCode();
        $toCurrencyCode = $currencyPair->getToCurrency()->getCode();

        $response = $this->apiService
            ->latestExchangeRate($fromCurrencyCode, $toCurrencyCode)
            ->toArray();

        return $response['data'][$toCurrencyCode] ?? null;
    }

}
