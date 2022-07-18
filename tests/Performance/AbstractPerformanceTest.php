<?php

namespace Serenata\Tests\Performance;

use Closure;
use RuntimeException;

use Serenata\Sockets\JsonRpcRequest;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\Utility\InitializeParams;

use Serenata\Workspace\ActiveWorkspaceManager;

/**
 * @group Performance
 */
abstract class AbstractPerformanceTest extends AbstractIntegrationTest
{
    private const EXECUTION_TIMES_OUTPUT_FILE = __DIR__ . '/Output/execution-times.txt';

    protected function getNormalizedUri(string $path): string
    {
        return 'file://' . $this->normalizePath($path);
    }

    protected function time(Closure $closure): float
    {
        $time = microtime(true);

        $closure();

        return (microtime(true) - $time) * 1000;
    }

    protected function finish(float $time, string $caller): void
    {
        $message = "Took {$time} milliseconds (" . ($time / 1000) . " seconds)";

        // Output the results to a file in the case of running with Paratest
        // See: https://github.com/paratestphp/paratest/issues/683
        file_put_contents(
            self::EXECUTION_TIMES_OUTPUT_FILE,
            "{$caller}:" . PHP_EOL . $message . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        self::markTestSkipped($message);
    }

    protected function getActiveWorkspaceManager(): ActiveWorkspaceManager
    {
        $manager = $this->container->get(ActiveWorkspaceManager::class);

        assert($manager instanceof ActiveWorkspaceManager);

        return $manager;
    }

    /**
     * Initializes a project as dummy, with dummy values.
     */
    protected function initializeDummyProject(string $uriToIndex, float $phpVersion = 8.0): void
    {
        $tmpDatabaseFile = tmpfile() ?:
            throw new RuntimeException('Temporary database file cannot be created');
        $dummyDatabaseUri = $this->getNormalizedUri(stream_get_meta_data($tmpDatabaseFile)['uri']);

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
