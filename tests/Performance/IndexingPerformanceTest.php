<?php

namespace Serenata\Tests\Performance;

/**
 * @group Performance
 */
final class IndexingPerformanceTest extends AbstractPerformanceTest
{
    /**
     * @return void
     */
    public function testIndexStubs(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = 'file://' . $this->normalizePath(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs'),
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        $this->finish($time);
    }

    /**
     * @return void
     */
    public function testIndexLargeFile(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = 'file://' . $this->normalizePath(__DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/UnitOfWork.php'),
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        $this->finish($time);
    }
}
