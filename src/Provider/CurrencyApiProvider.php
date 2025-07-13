<?php

namespace App\Provider;

use App\Client\CurrencyApiClient;
use App\Dto\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Exception\ExternalApi\ExternalApiRequestException;
use App\Service\Cache\CurrencyCacheService;
use Psr\Cache\InvalidArgumentException;
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
        private CurrencyApiClient     $client,
        private DenormalizerInterface $denormalizer,
        private CurrencyCacheService  $cache,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ExternalApiRequestException
     */
    public function isAlive(): bool
    {
        $response = $this->client->status();

        return $response->getStatusCode() === 200;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ExternalApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getCurrencies(string ...$code): array
    {
        $mapToDto = fn (array $currencies) => array_map(
            callback: fn ($v) => $this->denormalizer->denormalize($v, Currency::class),
            array: $currencies,
        );

        $currencies = $this->cache->getCurrencies($code);
        $emptyCurrencies = array_keys(array_filter($currencies, fn ($v) => empty($v)));
        $currencies = array_filter($currencies);

        if (!empty($currencies) && empty($emptyCurrencies)) {
            return $mapToDto($currencies);
        }

        if (!$this->isAlive()) {
            throw new CurrencyApiUnavailableException();
        }

        $response = $this->client->currencies(...$emptyCurrencies);

        $emptyCurrencies = $mapToDto($response->toArray()['data'] ?? []);

        if (!empty($emptyCurrencies)) {
            $this->cache->setCurrencies($emptyCurrencies);
        }

        return array_merge($currencies, $emptyCurrencies);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ExternalApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws DecodingExceptionInterface
     * @throws ExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
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
     * @throws ExternalApiRequestException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getLatestExchangeRate(CurrencyPair $currencyPair): ?string
    {
        $fromCurrencyCode = $currencyPair->getFromCurrency()->getCode();
        $toCurrencyCode = $currencyPair->getToCurrency()->getCode();

        $response = $this->client
            ->latestExchangeRate($fromCurrencyCode, $toCurrencyCode)
            ->toArray();

        return $response['data'][$toCurrencyCode] ?? null;
    }

}
