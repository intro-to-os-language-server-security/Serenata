<?php

namespace PhpIntegrator\Analysis\Typing\Deduction;

use UnexpectedValueException;

use PhpIntegrator\Indexing\IndexDatabase;

use PhpParser\Node;

/**
 * Type deducer that can deduce the type of a {@see Node\Expr\MethodCall} or a {@see Node\Expr\StaticCall} node based on
 * data supplied by meta files and delegates to another deducer if no such data is present.
 */
class MethodCallNodeMetaTypeDeducer extends AbstractNodeTypeDeducer
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $delegate;

    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var IndexDatabase
     */
    private $indexDatabase;

    /**
     * @param NodeTypeDeducerInterface $delegate
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param IndexDatabase            $indexDatabase
     */
    public function __construct(
        NodeTypeDeducerInterface $delegate,
        NodeTypeDeducerInterface $nodeTypeDeducer,
        IndexDatabase $indexDatabase
    ) {
        $this->delegate = $delegate;
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->indexDatabase = $indexDatabase;
    }

    /**
     * @inheritDoc
     */
    public function deduce(Node $node, string $file, string $code, int $offset): array
    {
        if (!$node instanceof Node\Expr\MethodCall && !$node instanceof Node\Expr\StaticCall) {
            throw new UnexpectedValueException("Can't handle node of type " . get_class($node));
        }

        return $this->deduceTypesFromMethodCallNode($node, $file, $code, $offset);
    }

    /**
     * @param Node\Expr\MethodCall|Node\Expr\StaticCall $node
     * @param string                                    $file
     * @param string                                    $code
     * @param int                                       $offset
     *
     * @return string[]
     */
    protected function deduceTypesFromMethodCallNode(Node\Expr $node, string $file, string $code, int $offset): array
    {
        $objectNode = ($node instanceof Node\Expr\MethodCall) ? $node->var : $node->class;
        $methodName = ($node instanceof Node\Expr\New_) ? '__construct' : $node->name;

        $typesOfVar = $this->nodeTypeDeducer->deduce($objectNode, $file, $code, $offset);

        $staticTypes = [];

        foreach ($typesOfVar as $type) {
            $staticTypes = array_merge(
                $staticTypes,
                $this->indexDatabase->getMetaStaticMethodTypesFor($type, $methodName)
            );
        }

        if (empty($staticTypes)) {
            return $this->delegate->deduce($node, $file, $code, $offset);
        }

        $types = [];

        foreach ($staticTypes as $staticType) {
            if (count($node->args) <= $staticType['argument_index']) {
                continue;
            }

            $relevantArgumentNode = $node->args[$staticType['argument_index']];

            if (get_class($relevantArgumentNode->value) !== $staticType['value_node_type']) {
                continue;
            }

            if (
                $relevantArgumentNode->value instanceof Node\Scalar\String_ &&
                $relevantArgumentNode->value->value === $staticType['value']
            ) {
                $types[] = $staticType['return_type'];
            }
        }

        return $types;
    }
}