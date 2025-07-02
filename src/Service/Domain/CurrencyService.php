<?php

namespace App\Service\Domain;

use App\Entity\Currency;
use App\Exception\Currency\DuplicateCurrencyCodeException;
use App\Exception\CurrencyApi\CurrencyDataNotFoundException;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyApiProvider;
use App\Repository\CurrencyRepository;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

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
     * @throws DuplicateCurrencyCodeException
     * @throws CurrencyDataNotFoundException
     */
    public function create(string $currencyCode): Currency
    {
        if ($this->exists($currencyCode)) {
            throw new DuplicateCurrencyCodeException($currencyCode);
        }

        $currency = $this->provider->getCurrency($currencyCode);

        if (empty($currency)) {
            throw new CurrencyDataNotFoundException($currencyCode);
        }

        $currency = $this->factory->create($currency);

        $this->repository->save($currency);

        return $currency;
    }

    public function exists(string $currencyCode): bool
    {
        return !is_null($this->repository->findOneBy(['code' => $currencyCode]));
    }

    /**
     * @return Currency[]
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function get(string $currencyCode): ?Currency
    {
        return $this->repository->findOneBy(['code' => $currencyCode]);
    }

    public function isValidCode(string $currencyCode): bool
    {
        try {
            return !is_null($this->provider->getCurrency($currencyCode));
        } catch (Throwable) {
            return false;
        }
    }

}
