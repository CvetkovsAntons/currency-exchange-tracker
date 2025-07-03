<?php

namespace App\Tests\Command;

use App\Command\ExchangeRateStopTrackingCommand;
use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Enum\Argument;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ExchangeRateStopTrackingCommandTest extends TestCase
{
    private CurrencyService&MockObject $currencyService;
    private CurrencyPairService&MockObject $pairService;
    private ExchangeRateService&MockObject $rateService;
    private EntityManagerInterface&MockObject $em;
    private ExchangeRateStopTrackingCommand $command;

    protected function setUp(): void
    {
        $this->currencyService = $this->createMock(CurrencyService::class);
        $this->pairService = $this->createMock(CurrencyPairService::class);
        $this->rateService = $this->createMock(ExchangeRateService::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->command = new ExchangeRateStopTrackingCommand(
            $this->currencyService,
            $this->pairService,
            $this->rateService,
            $this->em,
            $logger,
        );
    }

    public function testExchangeRateStopTrackingWithArguments(): void
    {
        $this->prepareCommand();

        $tester = new CommandTester($this->command);

        $tester->execute([
            Argument::FROM->value => 'USD',
            Argument::TO->value => 'EUR',
        ]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString(
            needle: 'Tracking of USD-EUR exchange rate has been stoped successfully!',
            haystack: $output
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExchangeRateStopTrackingWithoutArguments(): void
    {
        $this->prepareCommand();

        $tester = new CommandTester($this->command);
        $tester->setInputs(['USD', 'EUR']);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString(
            needle: 'Tracking of USD-EUR exchange rate has been stoped successfully!',
            haystack: $output
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExchangeRateStopTrackingWithInvalidCurrency(): void
    {
        $this->prepareCommand(fn ($v) => in_array($v, ['USD', 'EUR']));

        $tester = new CommandTester($this->command);

        $tester->setInputs(['XXX', 'USD', 'EUR']);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Invalid currency code', $output);
        $this->assertStringContainsString(
            needle: 'Tracking of USD-EUR exchange rate has been stoped successfully!',
            haystack: $output
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    private function prepareCommand(?callable $isValidCodeReturn = null): void
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);
        $pair = $this->createMock(CurrencyPair::class);
        $rate = $this->createMock(ExchangeRate::class);

        $from
            ->method('getCode')
            ->willReturn('USD');

        $to
            ->method('getCode')
            ->willReturn('EUR');

        if (is_null($isValidCodeReturn)) {
            $this->currencyService->method('isValidCode')->willReturn(true);
        } else {
            $this->currencyService->method('isValidCode')->willReturnCallback($isValidCodeReturn);
        }

        $this->currencyService
            ->method('get')
            ->willReturnMap([['USD', $from], ['EUR', $to]]);

        $this->pairService
            ->method('get')
            ->with($from, $to)
            ->willReturn($pair);

        $this->rateService
            ->method('get')
            ->with($pair)
            ->willReturn($rate);

        $this->em
            ->expects($this->once())
            ->method('beginTransaction');

        $this->em
            ->expects($this->once())
            ->method('commit');

        $this->rateService
            ->expects($this->once())
            ->method('delete')
            ->with($rate);
    }

}
