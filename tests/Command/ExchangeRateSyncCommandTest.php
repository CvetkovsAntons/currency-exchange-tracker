<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ExchangeRateSyncCommand;
use App\Enum\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExchangeRateSyncCommandTest extends WebTestCase
{
    public function testExecuteWithValidCurrencies(): void
    {
        self::bootKernel();

        $application = new Application();
        $command = self::getContainer()->get(ExchangeRateSyncCommand::class);

        $application->add($command);

        $tester = new CommandTester($command);

        $tester->execute([
            Argument::FROM->value => 'USD',
            Argument::TO->value => 'EUR',
        ]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('exchange rate has been synced successfully', $output);
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteWithValidCurrenciesWithoutArguments(): void
    {
        self::bootKernel();

        $application = new Application();
        $command = self::getContainer()->get(ExchangeRateSyncCommand::class);

        $application->add($command);

        $tester = new CommandTester($command);
        $tester->setInputs(['USD', 'EUR']);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('exchange rate has been synced successfully', $output);
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteWithInvalidCurrency(): void
    {
        self::bootKernel();

        $command = self::getContainer()->get(ExchangeRateSyncCommand::class);
        $tester = new CommandTester($command);

        $tester->setInputs(['XXX', 'USD', 'EUR']);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Invalid currency code', $output);
        $this->assertStringContainsString('exchange rate has been synced successfully', $output);
        $this->assertSame(0, $tester->getStatusCode());
    }

}
