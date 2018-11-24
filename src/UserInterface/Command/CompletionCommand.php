<?php

namespace Serenata\UserInterface\Command;

use Serenata\Autocompletion\AutocompletionPrefixDeterminerInterface;

use Serenata\Autocompletion\Providers\AutocompletionProviderContext;
use Serenata\Autocompletion\Providers\AutocompletionProviderInterface;

use Serenata\Common\Position;

use Serenata\Indexing\TextDocumentContentRegistry;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Utility\TextDocumentItem;

/**
 * Command that shows autocompletion suggestions at a specific location.
 */
final class CompletionCommand extends AbstractCommand
{
    /**
     * @var AutocompletionProviderInterface
     */
    private $autocompletionProvider;

    /**
     * @var TextDocumentContentRegistry
     */
    private $textDocumentContentRegistry;

    /**
     * @var AutocompletionPrefixDeterminerInterface
     */
    private $autocompletionPrefixDeterminer;

    /**
     * @param AutocompletionProviderInterface         $autocompletionProvider
     * @param TextDocumentContentRegistry             $textDocumentContentRegistry
     * @param AutocompletionPrefixDeterminerInterface $autocompletionPrefixDeterminer
     */
    public function __construct(
        AutocompletionProviderInterface $autocompletionProvider,
        TextDocumentContentRegistry $textDocumentContentRegistry,
        AutocompletionPrefixDeterminerInterface $autocompletionPrefixDeterminer
    ) {
        $this->autocompletionProvider = $autocompletionProvider;
        $this->textDocumentContentRegistry = $textDocumentContentRegistry;
        $this->autocompletionPrefixDeterminer = $autocompletionPrefixDeterminer;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $parameters = $queueItem->getRequest()->getParams() ?: [];

        return new JsonRpcResponse($queueItem->getRequest()->getId(), $this->getSuggestions(
            $parameters['textDocument']['uri'],
            $this->textDocumentContentRegistry->get($parameters['textDocument']['uri']),
            new Position($parameters['position']['line'], $parameters['position']['character'])
        ));
    }

    /**
     * @param string   $uri
     * @param string   $code
     * @param Position $position
     *
     * @return array
     */
    public function getSuggestions(string $uri, string $code, Position $position): array
    {
        return $this->autocompletionProvider->provide(new AutocompletionProviderContext(
            new TextDocumentItem($uri, $code),
            $position,
            $this->autocompletionPrefixDeterminer->determine($code, $position)
        ));
    }
}