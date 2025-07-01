<?php

namespace App\Tests\Controller;

use App\Enum\HttpMethod;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use App\Tests\Internal\Factory\ExchangeRateHistoryTestFactory;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class HealthCheckControllerTest extends WebTestCase
{
    use PurgeDatabaseTrait;

    private EntityManagerInterface $em;
    private KernelBrowser $client;

    protected function container(): Container
    {
        return static::getContainer();
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->container()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        $this->purgeDatabase();
        parent::tearDown();
    }

    public function testGetStatusSuccess(): void
    {
        $this->client->request(HttpMethod::GET->value, '/health-check');

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
    }

}
