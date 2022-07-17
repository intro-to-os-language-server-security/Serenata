<?php

namespace Serenata\Tests\Performance;

/**
 * @group Performance
 */
final class FunctionListProvidingPerformanceTest extends AbstractPerformanceTest
{
    public function testFetchAllColdFromStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = $this->getNormalizedUri(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $this->indexPath($this->container, $uriToIndex);

        $time = $this->time(function (): void {
            $this->container->get('functionListProvider')->getAll();
        });

        $this->finish($time, __METHOD__);
    }

    public function testFetchAllHotFromStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = $this->getNormalizedUri(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $this->indexPath($this->container, $uriToIndex);
        $this->container->get('functionListProvider')->getAll();

        $time = $this->time(function (): void {
            $this->container->get('functionListProvider')->getAll();
        });

        $this->finish($time, __METHOD__);
    }
}
