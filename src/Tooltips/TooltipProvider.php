<?php

namespace PhpIntegrator\Tooltips;

use UnexpectedValueException;

use PhpIntegrator\Analysis\Visiting\NodeFetchingVisitor;
use PhpIntegrator\Analysis\Visiting\NamespaceAttachingVisitor;
use PhpIntegrator\Analysis\Visiting\ResolvedNameAttachingVisitor;

use PhpIntegrator\Utility\NodeHelpers;

use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ErrorHandler;
use PhpParser\NodeTraverser;

/**
 * Provides tooltips.
 */
class TooltipProvider
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var FuncCallNodeTooltipGenerator
     */
    protected $funcCallNodeTooltipGenerator;

    /**
     * @param Parser                       $parser
     * @param FuncCallNodeTooltipGenerator $funcCallNodeTooltipGenerator
     */
    public function __construct(Parser $parser, FuncCallNodeTooltipGenerator $funcCallNodeTooltipGenerator)
    {
        $this->parser = $parser;
        $this->funcCallNodeTooltipGenerator = $funcCallNodeTooltipGenerator;
    }

    /**
     * @param string $code
     * @param int    $position
     *
     * @return TooltipResult|null
     */
    public function get(string $code, int $position): ?TooltipResult
    {
        $nodes = [];

        try {
            $nodes = $this->getNodesFromCode($code);
            $node = $this->getNodeAt($nodes, $position);

            $contents = $this->getTooltipForNode($node);

            return new TooltipResult($contents);
        } catch (UnexpectedValueException $e) {
            return null;
        }
    }

    /**
     * @param array $nodes
     * @param int   $position
     *
     * @throws UnexpectedValueException
     *
     * @return Node
     */
    protected function getNodeAt(array $nodes, int $position): Node
    {
        $visitor = new NodeFetchingVisitor($position);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ResolvedNameAttachingVisitor());
        $traverser->addVisitor(new NamespaceAttachingVisitor());
        $traverser->addVisitor($visitor);

        $traverser->traverse($nodes);

        $node = $visitor->getNearestInterestingNode();

        if (!$node) {
            throw new UnexpectedValueException('No node found at location ' . $position);
        }

        return $node;
    }

    /**
     * @param Node $node
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    protected function getTooltipForNode(Node $node): string
    {
        if ($node instanceof Node\Expr\FuncCall) {
            return $this->getTooltipForFuncCallNode($node);
        }

        throw new UnexpectedValueException('Don\'t know how to handle node of type ' . get_class($node));
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    protected function getTooltipForFuncCallNode(Node\Expr\FuncCall $node): string
    {
        if (!$node->name instanceof Node\Name) {
            throw new UnexpectedValueException('Determining tooltips for dynamic function calls is not supported');
        }

        $fullyQualifiedName = NodeHelpers::fetchClassName($node->name);

        return $this->funcCallNodeTooltipGenerator->generate($node);
    }

    /**
     * @param string $code
     *
     * @throws UnexpectedValueException
     *
     * @return Node[]
     */
    protected function getNodesFromCode(string $code): array
    {
        $nodes = $this->parser->parse($code, $this->getErrorHandler());

        if ($nodes === null) {
            throw new UnexpectedValueException('No nodes returned after parsing code');
        }

        return $nodes;
    }

    /**
     * @return ErrorHandler\Collecting
     */
    protected function getErrorHandler(): ErrorHandler\Collecting
    {
        return new ErrorHandler\Collecting();
    }
}
