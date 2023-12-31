<?php

namespace Serenata\Analysis\Visiting;

use DomainException;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use Serenata\Common\Range;
use Serenata\Common\Position;

use Serenata\Utility\PositionEncoding;

/**
 * Node visitor that fetches namespaces and their use statements.
 */
final class UseStatementFetchingVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<int,array<string,mixed>>
     */
    private $namespaces = [];

    /**
     * @var int
     */
    private $lastNamespaceIndex = 0;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;

        $this->namespaces[0] = [
            'name'          => null,
            'range'         => new Range(
                new Position(0, 0),
                new Position(mb_substr_count($code, "\n") + 1, 0)
            ),
            'useStatements' => [],
        ];

        $this->lastNamespaceIndex = 0;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            // There is no way to fetch the end of a namespace, so determine it manually (a value of null signifies the
            // end of the file).
            $this->beginNamespace($node);
        } elseif ($node instanceof Node\Stmt\Use_ || $node instanceof Node\Stmt\GroupUse) {
            $this->registerImportNode($node);
        }

        // if (isset($this->namespaces[$this->lastNamespaceIndex])) {
        //     $this->namespaces[$this->lastNamespaceIndex]['endLine'] = max(
        //         $this->namespaces[$this->lastNamespaceIndex]['endLine'],
        //         $node->getAttribute('endLine') + 1
        //     );
        // }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        if (isset($this->namespaces[$this->lastNamespaceIndex])) {
            $this->namespaces[$this->lastNamespaceIndex]['range'] = new Range(
                $this->namespaces[$this->lastNamespaceIndex]['range']->getStart(),
                new Position(mb_substr_count($this->code, "\n") + 1, 0)
            );
        }

        return null;
    }

    /**
     * Retrieves a list of namespaces.
     *
     * @return array<array<string,mixed>>
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param Node\Stmt\Namespace_ $node
     */
    private function beginNamespace(Node\Stmt\Namespace_ $node): void
    {
        $this->namespaces[$this->lastNamespaceIndex]['range'] = new Range(
            $this->namespaces[$this->lastNamespaceIndex]['range']->getStart(),
            Position::createFromByteOffset($node->getAttribute('startFilePos'), $this->code, PositionEncoding::VALUE)
        );

        $this->namespaces[++$this->lastNamespaceIndex] = [
            'name'          => $node->name !== null ? (string) $node->name : null,
            'range'         => new Range(
                Position::createFromByteOffset(
                    $node->getAttribute('startFilePos'),
                    $this->code,
                    PositionEncoding::VALUE
                ),
                Position::createFromByteOffset(
                    $node->getAttribute('endFilePos') + 1,
                    $this->code,
                    PositionEncoding::VALUE
                )
            ),
            'useStatements' => [],
        ];
    }

    /**
     * @param Node\Stmt\Use_|Node\Stmt\GroupUse $node
     *
     * @return void
     */
    private function registerImportNode(Node $node): void
    {
        $prefix = '';

        if ($node instanceof Node\Stmt\GroupUse) {
            $prefix = ((string) $node->prefix) . '\\';
        }

        foreach ($node->uses as $use) {
            $this->registerImport($node, $use, $prefix);
        }
    }

    /**
     * @param Node\Stmt\Use_|Node\Stmt\GroupUse $node
     * @param Node\Stmt\UseUse                  $use
     * @param string                            $prefix
     *
     * @return void
     */
    private function registerImport(Node $node, Node\Stmt\UseUse $use, string $prefix): void
    {
        $type = $node->type === Node\Stmt\Use_::TYPE_UNKNOWN ? $use->type : $node->type;

        if ($type === Node\Stmt\Use_::TYPE_UNKNOWN) {
            throw new DomainException('Unknown use statement type encountered!');
        }

        $kindMap = [
            Node\Stmt\Use_::TYPE_NORMAL   => UseStatementKind::TYPE_CLASSLIKE,
            Node\Stmt\Use_::TYPE_FUNCTION => UseStatementKind::TYPE_FUNCTION,
            Node\Stmt\Use_::TYPE_CONSTANT => UseStatementKind::TYPE_CONSTANT,
        ];

        $this->namespaces[$this->lastNamespaceIndex]['useStatements'][$use->getAlias()->name] = [
            'name'  => $prefix . ((string) $use->name),
            'alias' => $use->getAlias()->name,
            'kind'  => $kindMap[$type],
            'range' => new Range(
                Position::createFromByteOffset(
                    $node->getAttribute('startFilePos'),
                    $this->code,
                    PositionEncoding::VALUE
                ),
                Position::createFromByteOffset(
                    $node->getAttribute('endFilePos') + 1,
                    $this->code,
                    PositionEncoding::VALUE
                )
            ),
            'start' => $use->getAttribute('startFilePos'),
            'end'   => $use->getAttribute('endFilePos') !== null ? $use->getAttribute('endFilePos') + 1 : null,
        ];
    }
}
