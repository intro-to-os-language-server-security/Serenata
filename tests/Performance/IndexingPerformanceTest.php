<?php

namespace Serenata\Tests\Performance;

/**
 * @group Performance
 */
final class IndexingPerformanceTest extends AbstractPerformanceTest
{
    public function testIndexStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = $this->getNormalizedUri(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        $this->finish($time, __METHOD__);
    }

    public function testIndexLargeFile(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = $this->getNormalizedUri(__DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/UnitOfWork.php'),
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        $this->finish($time, __METHOD__);
    }
}
