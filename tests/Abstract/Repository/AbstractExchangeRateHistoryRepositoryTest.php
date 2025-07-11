<?php

declare(strict_types=1);

namespace App\Tests\Abstract\Repository;

use App\Entity\ExchangeRateHistory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractExchangeRateHistoryRepositoryTest extends KernelTestCase
{
    use PurgeDatabaseTrait;

    protected EntityManagerInterface $em;
    protected ExchangeRateHistoryRepository $repository;

    protected function container(): Container
    {
        return static::getContainer();
    }

    protected function setUp(): void
    {
        static::bootKernel();

        $this->em = $this->container()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(ExchangeRateHistory::class);
    }

    protected function tearDown(): void
    {
        $this->purgeDatabase();
        parent::tearDown();
    }

}
