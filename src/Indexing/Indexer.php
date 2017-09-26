<?php

namespace PhpIntegrator\Indexing;

use UnexpectedValueException;

use Ds\Queue;

use PhpIntegrator\Sockets\JsonRpcRequest;
use PhpIntegrator\Sockets\JsonRpcResponse;
use PhpIntegrator\Sockets\JsonRpcQueueItem;
use PhpIntegrator\Sockets\JsonRpcResponseSenderInterface;

use PhpIntegrator\Utility\SourceCodeStreamReader;

use Evenement\EventEmitterTrait;
use Evenement\EventEmitterInterface;

/**
 * Indexes directories and files.
 */
class Indexer implements EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var FileIndexer
     */
    private $fileIndexer;

    /**
     * @var DirectoryIndexRequestDemuxer
     */
    private $directoryIndexRequestDemuxer;

    /**
     * @var IndexFilePruner
     */
    private $indexFilePruner;

    /**
     * @var PathNormalizer
     */
    private $pathNormalizer;

    /**
     * @var SourceCodeStreamReader
     */
    private $sourceCodeStreamReader;

    /**
     * @var DirectoryIndexableFileIteratorFactory
     */
    private $directoryIndexableFileIteratorFactory;

    /**
     * @param Queue                                 $queue
     * @param FileIndexer                           $fileIndexer
     * @param DirectoryIndexRequestDemuxer          $directoryIndexRequestDemuxer
     * @param IndexFilePruner                       $indexFilePruner
     * @param PathNormalizer                        $pathNormalizer
     * @param SourceCodeStreamReader                $sourceCodeStreamReader
     * @param DirectoryIndexableFileIteratorFactory $directoryIndexableFileIteratorFactory
     */
    public function __construct(
        Queue $queue,
        FileIndexer $fileIndexer,
        DirectoryIndexRequestDemuxer $directoryIndexRequestDemuxer,
        IndexFilePruner $indexFilePruner,
        PathNormalizer $pathNormalizer,
        SourceCodeStreamReader $sourceCodeStreamReader,
        DirectoryIndexableFileIteratorFactory $directoryIndexableFileIteratorFactory
    ) {
        $this->queue = $queue;
        $this->fileIndexer = $fileIndexer;
        $this->directoryIndexRequestDemuxer = $directoryIndexRequestDemuxer;
        $this->indexFilePruner = $indexFilePruner;
        $this->pathNormalizer = $pathNormalizer;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
        $this->directoryIndexableFileIteratorFactory = $directoryIndexableFileIteratorFactory;
    }

    /**
     * @param string[]                       $paths
     * @param string[]                       $extensionsToIndex
     * @param string[]                       $globsToExclude
     * @param bool                           $useStdin
     * @param JsonRpcResponseSenderInterface $jsonRpcResponseSender
     * @param int|null                       $originatingRequestId
     *
     * @return bool
     */
    public function index(
        array $paths,
        array $extensionsToIndex,
        array $globsToExclude,
        bool $useStdin,
        JsonRpcResponseSenderInterface $jsonRpcResponseSender,
        ?int $originatingRequestId = null
    ): bool {
        $paths = array_map(function (string $path) {
            return $this->pathNormalizer->normalize($path);
        }, $paths);

        $directories = array_filter($paths, function (string $path) {
            return is_dir($path);
        });

        $files = array_filter($paths, function (string $path) {
            return !is_dir($path);
        });

        $this->indexDirectories(
            $directories,
            $extensionsToIndex,
            $globsToExclude,
            $jsonRpcResponseSender,
            $originatingRequestId
        );

        foreach ($files as $path) {
            $this->indexFile($path, $extensionsToIndex, $globsToExclude, $useStdin);
        }

        if ($originatingRequestId === null) {
            return true;
        }

        if (!empty($directories)) {
            $this->indexFilePruner->prune();
        }

        // As a directory index request is demuxed into multiple file index requests, the response for the original
        // request may not be sent until all individual file index requests have been handled. This command will
        // send that "finish" response when executed.
        //
        // This request will not be queued for file reindex requests that are the result of the demuxing as those
        // don't have an originating request ID.
        $delayedIndexFinishRequest = new JsonRpcRequest(null, 'echoResponse', [
            'response' => new JsonRpcResponse($originatingRequestId, true)
        ]);

        $this->queue->push(new JsonRpcQueueItem($delayedIndexFinishRequest, $jsonRpcResponseSender));

        return true;
    }

    /**
     * @param string[]                       $paths
     * @param string[]                       $extensionsToIndex
     * @param string[]                       $globsToExclude
     * @param JsonRpcResponseSenderInterface $jsonRpcResponseSender
     * @param int|null                       $requestId
     */
    protected function indexDirectories(
        array $paths,
        array $extensionsToIndex,
        array $globsToExclude,
        JsonRpcResponseSenderInterface $jsonRpcResponseSender,
        ?int $requestId
    ): void {
        if (empty($paths)) {
            return; // Optimization that skips expensive operations during demuxing, which stack.
        }

        $this->directoryIndexRequestDemuxer->index(
            $paths,
            $extensionsToIndex,
            $globsToExclude,
            $jsonRpcResponseSender,
            $requestId
        );
    }

    /**
     * @param string   $path
     * @param string[] $extensionsToIndex
     * @param string[] $globsToExclude
     * @param bool     $useStdin
     *
     * @return bool
     */
    protected function indexFile(string $path, array $extensionsToIndex, array $globsToExclude, bool $useStdin): bool
    {
        if (!$this->isFileAllowed($path, $extensionsToIndex, $globsToExclude)) {
            return false;
        } else if ($useStdin) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            try {
                $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($path);
            } catch (UnexpectedValueException $e) {
                return false; // Skip files that we can't read.
            }
        }

        if ($code === null) {
            return false;
        }

        try {
            $this->fileIndexer->index($path, $code);
        } catch (IndexingFailedException $e) {
            return false;
        }

        $this->emit(IndexingEventName::INDEXING_SUCCEEDED_EVENT);

        return true;
    }

    /**
     * @param string   $path
     * @param string[] $extensionsToIndex
     * @param string[] $globsToExclude
     *
     * @return bool
     */
    protected function isFileAllowed(string $path, array $extensionsToIndex, array $globsToExclude): bool
    {
        $iterator = new IndexableFileIterator([$path], $extensionsToIndex, $globsToExclude);

        return !empty(iterator_to_array($iterator));
    }
}
