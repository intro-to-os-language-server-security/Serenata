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
            $dummyDatabaseUri = $this->getOutputDirectory() . '/test-stubs.sqlite'
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        unlink($dummyDatabaseUri);

        $this->finish($time);
    }

    /**
     * @return void
     */
    public function testIndexLargeFile(): void
    {
        $this->initializeDummyProject(
            $uriToIndex = 'file://' . $this->normalizePath(__DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/UnitOfWork.php'),
            $dummyDatabaseUri = $this->getOutputDirectory() . '/test-large-file.sqlite'
        );

        $time = $this->time(function () use ($uriToIndex): void {
            $this->indexPath($this->container, $uriToIndex);
        });

        unlink($dummyDatabaseUri);

        $this->finish($time);
    }
}
