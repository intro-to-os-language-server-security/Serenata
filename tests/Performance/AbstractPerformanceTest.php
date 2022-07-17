<?php

namespace Serenata\Tests\Performance;

use Closure;

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

    protected function initializeDummyProject(
        string $uriToIndex,
        string $dummyDatabaseUri,
        float $phpVersion = 8.0
    ): void {
        @unlink($dummyDatabaseUri);

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
