<?php

namespace App\Service\Domain;

use App\Entity\Currency;
use App\Exception\CurrencyApiException;
use App\Exception\CurrencyCodeException;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\CurrencyRepository;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CurrencyService
{
    public function __construct(
        private CurrencyApiProvider $provider,
        private CurrencyFactory     $factory,
        private CurrencyRepository  $repository,
    ) {}

    /**
     * @param string $currencyCode
     * @return Currency
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws CurrencyApiException
     * @throws CurrencyCodeException
     */
    public function create(string $currencyCode): Currency
    {
        if ($this->exists($currencyCode)) {
            throw new CurrencyCodeException($currencyCode);
        }

        $currency = $this->provider->getCurrency($currencyCode);

        if (empty($currency)) {
            throw new CurrencyApiException(sprintf("Data for %s not found", $currencyCode));
        }

        $currency = $this->factory->create($currency);

        $this->repository->save($currency);

        return $currency;
    }

    public function exists(string $currencyCode): bool
    {
        return $this->repository->exists($currencyCode);
    }

    public function getByCode(string $currencyCode): ?Currency
    {
        return $this->repository->getByCode($currencyCode);
    }

    public function getAllCodes(): array
    {
        return $this->repository->getAllCodes();
    }

}
