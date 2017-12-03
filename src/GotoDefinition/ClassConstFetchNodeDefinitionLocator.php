<?php

namespace PhpIntegrator\GotoDefinition;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\Deduction\NodeTypeDeducerInterface;

use PhpIntegrator\Indexing\Structures;

use PhpParser\Node;

/**
 * Locates the definition of the class constant called in {@see Node\Expr\ClassConstFetch} nodes.
 */
class ClassConstFetchNodeDefinitionLocator
{
    /**
     * @var NodeTypeDeducerInterface
     */
    private $nodeTypeDeducer;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param NodeTypeDeducerInterface $nodeTypeDeducer
     * @param ClasslikeInfoBuilder     $classlikeInfoBuilder
     */
    public function __construct(NodeTypeDeducerInterface $nodeTypeDeducer, ClasslikeInfoBuilder $classlikeInfoBuilder)
    {
        $this->nodeTypeDeducer = $nodeTypeDeducer;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     *
     * @throws UnexpectedValueException when the constant name is not a string (i.e. an error node).
     * @throws UnexpectedValueException when the type of the class could not be determined.
     * @throws UnexpectedValueException when no tooltips could be determined.
     *
     * @return GotoDefinitionResult
     */
    public function locate(Node\Expr\ClassConstFetch $node, Structures\File $file, string $code): GotoDefinitionResult
    {
        if (!$node->name instanceof Node\Identifier) {
            throw new UnexpectedValueException("Can't deduce the type of a non-string node");
        }

        $classTypes = $this->getClassTypes($node, $file, $code);

        $definitions = [];

        foreach ($classTypes as $classType) {
            $constantInfo = $this->fetchClassConstantInfo($classType, $node->name);

            if ($constantInfo === null) {
                continue;
            }

            $definitions[] = new GotoDefinitionResult($constantInfo['filename'], $constantInfo['startLine']);
        }

        if (empty($definitions)) {
            throw new UnexpectedValueException('Could not determine any tooltips for the class constant');
        }

        // Fetch the first tooltip. In theory, multiple tooltips are possible, but we don't support these at the moment.
        return $definitions[0];
    }

    /**
     * @param Node\Expr\ClassConstFetch $node
     * @param Structures\File           $file
     * @param string                    $code
     *
     * @throws UnexpectedValueException
     *
     * @return array
     */
    private function getClassTypes(Node\Expr\ClassConstFetch $node, Structures\File $file, string $code): array
    {
        $classTypes = [];

        try {
            $classTypes = $this->nodeTypeDeducer->deduce($node->class, $file, $code, $node->getAttribute('startFilePos'));
        } catch (UnexpectedValueException $e) {
            throw new UnexpectedValueException('Could not deduce the type of class', 0, $e);
        }

        if (empty($classTypes)) {
            throw new UnexpectedValueException('No types returned for class');
        }

        return $classTypes;
    }

    /**
     * @param string $classType
     * @param string $name
     *
     * @return array|null
     */
    private function fetchClassConstantInfo(string $classType, string $name): ?array
    {
        $classInfo = null;

        try {
            $classInfo = $this->classlikeInfoBuilder->getClasslikeInfo($classType);
        } catch (UnexpectedValueException $e) {
            return null;
        }

        if (!isset($classInfo['constants'][$name])) {
            return null;
        }

        return $classInfo['constants'][$name];
    }
}
