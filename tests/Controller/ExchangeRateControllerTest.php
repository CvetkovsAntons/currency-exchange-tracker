<?php

namespace App\Tests\Controller;

use App\Enum\HttpMethod;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use App\Tests\Utils\Factory\ExchangeRateHistoryTestFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class ExchangeRateControllerTest extends WebTestCase
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

    public function testGetRateSuccess(): void
    {
        $from = CurrencyTestFactory::create();

        $to = CurrencyTestFactory::create(
            code: 'EUR',
            name: 'Euro',
            namePlural: 'Euros',
            symbol: '€',
            symbolNative: '€',
        );

        $pair = CurrencyPairTestFactory::create($from, $to);

        $now = new DateTimeImmutable('2025-06-29 16:52:32');
        $rate = ExchangeRateHistoryTestFactory::create($pair, '1.23', $now);

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($rate);
        $this->em->flush();
        $this->em->clear();

        $this->client->request(
            method: 'GET',
            uri: '/exchange-rate/',
            parameters: [
                'from' => 'USD',
                'to' => 'EUR',
                'datetime' => $now->format('Y-m-d H:i:s'),
            ]
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('rate', $data);
        $this->assertArrayHasKey('datetime', $data);
    }

    public function testMissingParameters(): void
    {
        $this->client->request(
            method: HttpMethod::GET->value,
            uri: '/exchange-rate/',
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

}
