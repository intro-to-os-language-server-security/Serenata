<?php

namespace Serenata\Tests\Performance;

use Closure;
use RuntimeException;

use Serenata\Sockets\JsonRpcRequest;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\UserInterface\JsonRpcQueueItemHandler\InitializeJsonRpcQueueItemHandler;

use Serenata\Utility\InitializeParams;

use Serenata\Workspace\ActiveWorkspaceManager;

/**
 * @group Performance
 */
abstract class AbstractPerformanceTest extends AbstractIntegrationTest
{
    /**
     * @return string
     */
    protected function getOutputDirectory(): string
    {
        return 'file://' . $this->normalizePath(__DIR__ . '/Output');
    }

    /**
     * @param Closure $closure
     *
     * @return float
     */
    protected function time(Closure $closure): float
    {
        $time = microtime(true);

        $closure();

        return (microtime(true) - $time) * 1000;
    }

    /**
     * @param float $time
     *
     * @return void
     */
    protected function finish(float $time): void
    {
        self::markTestSkipped("Took {$time} milliseconds (" . ($time / 1000) . " seconds)");
    }


    /**
     * @return ActiveWorkspaceManager
     */
    protected function getActiveWorkspaceManager(): ActiveWorkspaceManager
    {
        $manager = $this->container->get(ActiveWorkspaceManager::class);

        assert($manager instanceof ActiveWorkspaceManager);

        return $manager;
    }

    /**
     * Initializes a project as dummy, with dummy values.
     */
    protected function initializeDummyProject(
        string $uriToIndex,
        float $phpVersion = 8.0
    ): void {
        $tmpDatabaseFile = tmpfile() ?:
            throw new RuntimeException('Temporary database file cannot be created');
        $dummyDatabaseUri = 'file://' . stream_get_meta_data($tmpDatabaseFile)['uri'];

        $this->getActiveWorkspaceManager()->setActiveWorkspace(null);
        $this->container->get('managerRegistry')->setDatabaseUri($dummyDatabaseUri);

        $this->container->get('initializeJsonRpcQueueItemHandler')->initialize(
            new InitializeParams(
                123,
                null,
                $uriToIndex,
                [
                    'configuration' => [
                        'uris'                    => [$uriToIndex],
                        'indexDatabaseUri'        => $dummyDatabaseUri,
                        'phpVersion'              => $phpVersion,
                        'excludedPathExpressions' => [],
                        'fileExtensions'          => ['php'],
                    ],
                ],
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest(null, 'NO_METHOD'),
            false
        );
    }
}
