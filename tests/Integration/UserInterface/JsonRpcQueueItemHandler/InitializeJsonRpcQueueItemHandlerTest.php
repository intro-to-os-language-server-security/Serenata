<?php

namespace Serenata\Tests\Integration\UserInterface\JsonRpcQueueItemHandler;

use Serenata\Sockets\JsonRpcRequest;
use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\UserInterface\JsonRpcQueueItemHandler\InvalidArgumentsException;
use Serenata\UserInterface\JsonRpcQueueItemHandler\InitializeJsonRpcQueueItemHandler;

use Serenata\Utility\SaveOptions;
use Serenata\Utility\InitializeParams;
use Serenata\Utility\InitializeResult;
use Serenata\Utility\CompletionOptions;
use Serenata\Utility\ServerCapabilities;
use Serenata\Utility\SignatureHelpOptions;
use Serenata\Utility\TextDocumentSyncOptions;

use Serenata\Workspace\Workspace;
use Serenata\Workspace\ActiveWorkspaceManager;

use Serenata\Workspace\Configuration\WorkspaceConfiguration;

/**
 * @group Integration
 */
final class InitializeJsonRpcQueueItemHandlerTest extends AbstractIntegrationTest
{
    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->processOpenQueueItems();
        $this->getActiveWorkspaceManager()->setActiveWorkspace(null);
    }

    /**
     * @return void
     */
    public function testFailsWhenNoRootUriIsPresent(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest/';

        $handler = $this->getHandler();

        $this->expectException(InvalidArgumentsException::class);

        $response = $handler->initialize(
            new InitializeParams(
                123,
                $path,
                null,
                null,
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            false
        );
    }

    /**
     * @return void
     */
    public function testFailsWhenNoRootPathIsPresent(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest/';
        $uri = 'file://' . $path;

        $handler = $this->getHandler();

        $this->expectException(InvalidArgumentsException::class);

        $response = $handler->initialize(
            new InitializeParams(
                123,
                null,
                $uri,
                null,
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            false
        );
    }

    /**
     * @return void
     */
    public function testRespondsWithServerCapabilities(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest/';
        $uri = 'file://' . $path;

        $handler = $this->getHandler();

        $response = $handler->initialize(
            new InitializeParams(
                123,
                $path,
                $uri,
                null,
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            false
        );

        static::assertEquals(new JsonRpcResponse('TESTID', new InitializeResult(new ServerCapabilities(
            new TextDocumentSyncOptions(
                false,
                1,
                false,
                false,
                new SaveOptions(true)
            ),
            true,
            new CompletionOptions(false, null),
            new SignatureHelpOptions(['(', ',']),
            true,
            false,
            false,
            false,
            true,
            true,
            false,
            false,
            true,
            false,
            false,
            null,
            false,
            null,
            false,
            false,
            null,
            null,
            null
        ))), $response);
    }

    /**
     * @return void
     */
    public function testFallsBackToTemporaryConfigurationIfNoneIsPassed(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest/';
        $uri = 'file://' . $path;

        $handler = $this->getHandler();

        $handler->initialize(
            new InitializeParams(
                123,
                $path,
                $uri,
                null,
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            false
        );

        $workspace = $this->getActiveWorkspaceManager()->getActiveWorkspace();

        static::assertNotNull($workspace);

        static::assertEquals(new Workspace(
            new WorkspaceConfiguration(
                [$uri],
                'file://' . sys_get_temp_dir() . '/' . md5($uri),
                7.3,
                [],
                ['php']
            )
        ), $workspace);
    }

    /**
     * @return void
     */
    public function testUsesPassedConfigurationFromIntializationParams(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest/Folder1';
        $uri = 'file://' . $path;

        $handler = $this->getHandler();

        $handler->initialize(
            new InitializeParams(
                123,
                $path,
                $uri,
                [
                    'configuration' => [
                        'uris'                    => [$uri],
                        'indexDatabaseUri'        => ':memory:',
                        'phpVersion'              => 7.0,
                        'excludedPathExpressions' => ['/path/1'],
                        'fileExtensions'          => ['php2'],
                    ],
                ],
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            false
        );

        $workspace = $this->getActiveWorkspaceManager()->getActiveWorkspace();

        static::assertNotNull($workspace);

        static::assertEquals(new Workspace(
            new WorkspaceConfiguration(
                [$uri],
                ':memory:',
                7.0,
                ['/path/1'],
                ['php2']
            )
        ), $workspace);
    }

    /**
     * @return void
     */
    public function testIndexesUrisInConfigurationIfRequested(): void
    {
        $path = __DIR__ . '/InitializeJsonRpcQueueItemHandlerTest';
        $uri = 'file://' . $path;

        $handler = $this->getHandler();

        $handler->initialize(
            new InitializeParams(
                123,
                $path,
                $uri,
                [
                    'configuration' => [
                        'uris'                    => [$uri . '/Folder1', $uri . '/Folder2'],
                        'indexDatabaseUri'        => ':memory:',
                        'phpVersion'              => 7.3,
                        'excludedPathExpressions' => [],
                        'fileExtensions'          => ['phpt'],
                    ],
                ],
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest('TESTID', 'Not used'),
            true
        );

        static::assertEquals(new JsonRpcRequest(null, 'serenata/internal/index', [
            'textDocument' => [
                'uri' => $uri . '/Folder1/File1.phpt',
            ],
        ]), $this->getRequestQueue()->pop()->getRequest());

        $this->getRequestQueue()->pop(); // echoMessage, ignore.

        static::assertEquals(new JsonRpcRequest(null, 'serenata/internal/index', [
            'textDocument' => [
                'uri' => $uri . '/Folder2/File2.phpt',
            ],
        ]), $this->getRequestQueue()->pop()->getRequest());
    }

    /**
     * @return ActiveWorkspaceManager
     */
    private function getActiveWorkspaceManager(): ActiveWorkspaceManager
    {
        $manager = $this->container->get('activeWorkspaceManager');

        assert($manager instanceof ActiveWorkspaceManager);

        return $manager;
    }

    /**
     * @return InitializeJsonRpcQueueItemHandler
     */
    private function getHandler(): InitializeJsonRpcQueueItemHandler
    {
        $handler = $this->container->get('initializeJsonRpcQueueItemHandler');

        assert($handler instanceof InitializeJsonRpcQueueItemHandler);

        return $handler;
    }
}