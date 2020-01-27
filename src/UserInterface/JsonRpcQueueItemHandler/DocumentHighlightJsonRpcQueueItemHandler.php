<?php

namespace Serenata\UserInterface\JsonRpcQueueItemHandler;

use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

use Serenata\Common\Position;

use Serenata\Indexing\TextDocumentContentRegistry;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Highlights\DocumentHighlightsRetriever;

use Serenata\Utility\TextDocumentItem;

/**
 * Handler that retrieves a list of known symbols for a document.
 */
final class DocumentHighlightJsonRpcQueueItemHandler extends AbstractJsonRpcQueueItemHandler
{
    /**
     * @var DocumentHighlightsRetriever
     */
    private $documentHighlightsRetriever;

    /**
     * @var TextDocumentContentRegistry
     */
    private $textDocumentContentRegistry;

    /**
     * @param DocumentHighlightsRetriever $documentHighlightsRetriever
     * @param TextDocumentContentRegistry $textDocumentContentRegistry
     */
    public function __construct(
        DocumentHighlightsRetriever $documentHighlightsRetriever,
        TextDocumentContentRegistry $textDocumentContentRegistry
    ) {
        $this->documentHighlightsRetriever = $documentHighlightsRetriever;
        $this->textDocumentContentRegistry = $textDocumentContentRegistry;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ExtendedPromiseInterface
    {
        $parameters = $queueItem->getRequest()->getParams() !== null ?
            $queueItem->getRequest()->getParams() :
            [];

        $response = new JsonRpcResponse(
            $queueItem->getRequest()->getId(),
            $this->getAll(
                $parameters['textDocument']['uri'],
                $this->textDocumentContentRegistry->get($parameters['textDocument']['uri']),
                new Position($parameters['position']['line'], $parameters['position']['character'])
            )
        );

        $deferred = new Deferred();
        $deferred->resolve($response);

        return $deferred->promise();
    }

    /**
     * @param string   $uri
     * @param string   $code
     * @param Position $position
     *
     * @return array|null
     */
    public function getAll(string $uri, string $code, Position $position): ?array
    {
        return $this->documentHighlightsRetriever->retrieve(new TextDocumentItem($uri, $code), $position);
    }
}
