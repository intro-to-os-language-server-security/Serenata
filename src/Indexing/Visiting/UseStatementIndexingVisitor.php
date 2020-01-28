<?php

namespace Serenata\Indexing\Visiting;

use Serenata\Analysis\Visiting\UseStatementFetchingVisitor;

use Serenata\Indexing\Structures;
use Serenata\Indexing\StorageInterface;

use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * Visitor that traverses a set of nodes and indexes use statements and namespaces in the process.
 */
final class UseStatementIndexingVisitor implements NodeVisitor
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var Structures\File
     */
    private $file;

    /**
     * @var UseStatementFetchingVisitor
     */
    private $useStatementFetchingVisitor;

    /**
     * @param StorageInterface $storage
     * @param Structures\File  $file
     * @param string           $code
     */
    public function __construct(StorageInterface $storage, Structures\File $file, string $code)
    {
        $this->storage = $storage;
        $this->file = $file;

        $this->useStatementFetchingVisitor = new UseStatementFetchingVisitor($code);
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        foreach ($this->file->getNamespaces() as $namespace) {
            $this->file->removeNamespace($namespace);

            $this->storage->delete($namespace);
        }

        $this->useStatementFetchingVisitor->beforeTraverse($nodes);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $this->useStatementFetchingVisitor->enterNode($node);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        $this->useStatementFetchingVisitor->leaveNode($node);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        $this->useStatementFetchingVisitor->afterTraverse($nodes);

        foreach ($this->useStatementFetchingVisitor->getNamespaces() as $namespace) {
            $this->indexNamespace($namespace);
        }

        return null;
    }

    /**
     * @param array $namespace
     *
     * @return void
     */
    private function indexNamespace(array $namespace): void
    {
        $namespaceEntity = new Structures\FileNamespace(
            $namespace['range'],
            $namespace['name'],
            $this->file,
            []
        );

        $this->storage->persist($namespaceEntity);

        foreach ($namespace['useStatements'] as $useStatement) {
            $this->indexUseStatement($useStatement, $namespaceEntity);
        }
    }

    /**
     * @param array                    $useStatement
     * @param Structures\FileNamespace $namespace
     *
     * @return void
     */
    private function indexUseStatement(array $useStatement, Structures\FileNamespace $namespace): void
    {
        $import = new Structures\FileNamespaceImport(
            $useStatement['range'],
            $useStatement['alias'] !== '' ? $useStatement['alias'] : null,
            $useStatement['name'],
            $useStatement['kind'],
            $namespace
        );

        $this->storage->persist($import);
    }
}
