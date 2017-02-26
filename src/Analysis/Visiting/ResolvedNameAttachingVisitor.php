<?php

namespace PhpIntegrator\Analysis\Visiting;

use PhpParser\Node;
use PhpParser\Node\Name;

use PhpParser\NodeVisitor\NameResolver;

/**
 * Non-destructive extension of php-parser's {@see NameResolver}.
 *
 * This variant will not actually replace any of the existing data in the nodes, but will only attach new data to them.
 * This way, combining this visitor with other visitors will not break them if they depend on the original, unaltered
 * data.
 *
 * The resolved name is available as a 'resolvedName' attribute of the name subnode.
 */
class ResolvedNameAttachingVisitor extends NameResolver
{
    /**
     * @inheritDoc
     */
    protected function resolveClassName(Name $name)
    {
        $resolvedName = parent::resolveClassName($name);

        $name->setAttribute('resolvedName', $resolvedName);

        return $name;
    }

    /**
     * @inheritDoc
     */
    protected function resolveOtherName(Name $name, $type)
    {
        $resolvedName = parent::resolveOtherName($name, $type);

        $name->setAttribute('resolvedName', $resolvedName);

        return $name;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $value = parent::enterNode($node);

        // NOTE: Workaround for nullable types not being resolved. See also
        // https://github.com/nikic/PHP-Parser/issues/360
        if ($node instanceof Node\FunctionLike) {
            $this->resolveSignatureNullableTypes($node);
        }

        return $value;
    }

    /**
     * @param Node\FunctionLike $node
     */
    protected function resolveSignatureNullableTypes(Node\FunctionLike $node)
    {
        foreach ($node->getParams() as $param) {
            if ($param->type instanceof Node\NullableType && $param->type->type instanceof Name) {
                $this->resolveClassName($param->type->type);
            }
        }

        if ($node->getReturnType() instanceof Node\NullableType && $node->getReturnType()->type instanceof Name) {
            $this->resolveClassName($node->getReturnType()->type);
        }
    }
}
