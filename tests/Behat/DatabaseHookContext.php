<?php

/*
 * This file is part of the API Platform range header pagination Bundle.
 *
 * (c) Campings.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Behat;

use Behat\Behat\Context\Context;
use Symfony\Component\Process\Process;

final class DatabaseHookContext implements Context
{
    private const BIN_DIR = __DIR__.'/../Application/';
    private const BIN_ENV = ['APP_ENV' => 'test'];

    /**
     * @BeforeSuite
     */
    public static function createDatabaseAndSchema(): void
    {
        self::runCommand('bin/console doctrine:database:drop --if-exists --force -n');
        self::runCommand('bin/console doctrine:database:create -n');
        self::runCommand('bin/console doctrine:schema:create -n ');
    }

    /**
     * @BeforeScenario @loadFixtures
     */
    public function loadFixtures(): void
    {
        self::runCommand('bin/console doctrine:fixtures:load -n');
    }

    private static function runCommand(string $str): void
    {
        $process = Process::fromShellCommandline($str, self::BIN_DIR, self::BIN_ENV);
        $process->run();
    }
}
