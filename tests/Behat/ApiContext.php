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
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Webmozart\Assert\Assert;

final class ApiContext implements Context
{
    private MinkContext $minkContext;
    private array $headers = [];

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
    }

    /**
     * @Given I add :name header equal to :value
     */
    public function iAddHeaderEqualTo(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * @When I send a :method request to :url
     */
    public function iSendARequestTo(string $method, string $url): void
    {
        $client = $this->getClient();
        $client->followRedirects(false);
        $this->getClient()->request($method, $url, server: $this->prepareHeader());
        $this->headers = [];
    }

    /**
     * @Then the response header :name should be :value
     */
    public function theResponseHeaderShouldBe(string $name, string $value): void
    {
        $response = $this->getClient()->getResponse();
        Assert::eq($response->headers->get($name), $value);
    }

    /**
     * @Then the response should contain :nums :rangeName
     */
    public function theResponseShouldContainBooks(int $nums, string $rangeName): void
    {
        $response = $this->getClient()->getResponse();
        $contentRange = $response->headers->get('Content-range');
        Assert::notNull($contentRange);

        preg_match("/$rangeName (\d+)-(\d+)\/(\d*|\*)/", $contentRange, $contentRangeParts);

        $total = (int) $contentRangeParts[2] - (int) $contentRangeParts[1];
        Assert::eq($nums, $total);

        $collection = json_decode($response->getContent(), true);
        Assert::count($collection, $nums);
    }

    private function getClient(): AbstractBrowser
    {
        return $this->minkContext->getMink()->getSession()->getDriver()->getClient();
    }

    private function prepareHeader(): array
    {
        $headers = [];

        foreach ($this->headers as $name => $value) {
            $headers['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $headers;
    }
}
