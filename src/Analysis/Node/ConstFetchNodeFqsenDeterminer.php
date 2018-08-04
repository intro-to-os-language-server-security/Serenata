<?php

namespace Serenata\Analysis\Node;

use PhpParser\Node;

use Serenata\Analysis\Visiting\UseStatementKind;

use Serenata\Common\Position;
use Serenata\Common\FilePosition;

use Serenata\Indexing\Structures;

use Serenata\NameQualificationUtilities\StructureAwareNameResolverFactoryInterface;

use Serenata\Utility\NodeHelpers;

/**
 * Determines the FQSEN of a constant fetch node.
 */
final class ConstFetchNodeFqsenDeterminer
{
    /**
     * @var StructureAwareNameResolverFactoryInterface
     */
    private $structureAwareNameResolverFactory;

    /**
     * @param StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory
     */
    public function __construct(StructureAwareNameResolverFactoryInterface $structureAwareNameResolverFactory)
    {
        $this->structureAwareNameResolverFactory = $structureAwareNameResolverFactory;
    }

    /**
     * @param Node\Expr\ConstFetch $node
     * @param Structures\File      $file
     * @param Position             $position
     *
     * @return string
     */
    public function determine(Node\Expr\ConstFetch $node, Structures\File $file, Position $position): string
    {
        $filePosition = new FilePosition($file->getPath(), $position);

        $fileTypeResolver = $this->structureAwareNameResolverFactory->create($filePosition);

        return $fileTypeResolver->resolve(
            NodeHelpers::fetchClassName($node->name),
            $filePosition,
            UseStatementKind::TYPE_CONSTANT
        );
    }
}
