<?php

namespace App\Provider;

use App\Dto\Currency;
use App\Entity\CurrencyPair;
use App\Exception\CurrencyApiException;
use App\Service\Api\CurrencyApiService;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CurrencyApiProvider
{
    public function __construct(
        private CurrencyApiService  $apiService,
        private SerializerInterface $serializer,
    ) {}

    /**
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function isAlive(): bool
    {
        $response = $this->apiService->status();

        return $response->getStatusCode() === 200;
    }

    /**
     * @param string ...$code
     * @return CUrrency[]
     * @throws CurrencyApiException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrencies(string ...$code): array
    {
        if (!$this->isAlive()) {
            throw new CurrencyApiException("Couldn't connect to API");
        }

        $response = $this->apiService->currencies(...$code);

        return array_map(
            callback: fn($v) => $this->serializer->denormalize($v, Currency::class),
            array: $response->toArray()['data'] ?? [],
        );
    }

    /**
     * @param string $code
     * @return Currency|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
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
     * @param CurrencyPair $currencyPair
     * @return string|null
     * @throws ClientExceptionInterface
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
