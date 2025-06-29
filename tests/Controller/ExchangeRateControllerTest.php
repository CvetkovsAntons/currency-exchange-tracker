<?php

namespace App\Tests\Controller;

use App\Enum\HttpMethod;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use App\Tests\Utils\Factory\ExchangeRateHistoryTestFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExchangeRateControllerTest extends WebTestCase
{
    public function testGetRateSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $from = CurrencyTestFactory::create();
        $to = CurrencyTestFactory::create('EUR');
        $pair = CurrencyPairTestFactory::create($from, $to);

        $now = new DateTimeImmutable('2025-06-29 16:52:32');
        $rate = ExchangeRateHistoryTestFactory::create($pair, '1.23', $now);

        $em->persist($from);
        $em->persist($to);
        $em->persist($pair);
        $em->persist($rate);
        $em->flush();
        $em->clear();

        $client->request(
            method: 'GET',
            uri: '/exchange-rate/',
            parameters: [
                'from' => 'USD',
                'to' => 'EUR',
                'datetime' => $now->format('Y-m-d H:i:s'),
            ]
        );

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('rate', $data);
        $this->assertArrayHasKey('datetime', $data);
    }

    public function testMissingParameters(): void
    {
        $client = static::createClient();

        $client->request(
            method: HttpMethod::GET->value,
            uri: '/exchange-rate/',
        );

        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

}
