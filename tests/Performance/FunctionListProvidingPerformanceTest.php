<?php

namespace Serenata\Tests\Performance;

/**
 * @group Performance
 */
final class FunctionListProvidingPerformanceTest extends AbstractPerformanceTest
{
    /**
     * @return void
     */
    public function testFetchAllColdFromStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = 'file://' . $this->normalizePath(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $this->indexPath($this->container, $uriToIndex);

        $time = $this->time(function (): void {
            $this->container->get('functionListProvider')->getAll();
        });

        $this->finish($time);
    }

    /**
     * @return void
     */
    public function testFetchAllHotFromStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = 'file://' . $this->normalizePath(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $this->indexPath($this->container, $uriToIndex);
        $this->container->get('functionListProvider')->getAll();

        $time = $this->time(function (): void {
            $this->container->get('functionListProvider')->getAll();
        });

        $this->finish($time);
    }
}
