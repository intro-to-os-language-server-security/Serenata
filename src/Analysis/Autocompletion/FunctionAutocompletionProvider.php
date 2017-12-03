<?php

namespace PhpIntegrator\Analysis\Autocompletion;

use PhpIntegrator\Analysis\FunctionListProviderInterface;

/**
 * Provides function autocompletion suggestions at a specific location in a file.
 */
final class FunctionAutocompletionProvider implements AutocompletionProviderInterface
{
    /**
     * @var FunctionListProviderInterface
     */
    private $functionListProvider;

    /**
     * @param FunctionListProviderInterface $functionListProvider
     */
    public function __construct(FunctionListProviderInterface $functionListProvider)
    {
        $this->functionListProvider = $functionListProvider;
    }

    /**
     * @inheritDoc
     */
    public function provide(string $code, int $offset): iterable
    {
        $shouldIncludeParanthesesInInsertText = $this->shouldIncludeParanthesesInInsertText($code, $offset);

        foreach ($this->functionListProvider->getAll() as $function) {
            yield $this->createSuggestion($function, $shouldIncludeParanthesesInInsertText);
        }
    }

    /**
     * @param string $code
     * @param int    $offset
     *
     * @return bool
     */
    private function shouldIncludeParanthesesInInsertText(string $code, int $offset): bool
    {
        $length = mb_strlen($code);

        for ($i = $offset; $i < $length; ++$i) {
            if ($code[$i] === '(') {
                return false;
            } elseif ($this->isWhitespace($code[$i])) {
                continue;
            }

            return true;
        }

        return true;
    }

    /**
     * @param string $character
     *
     * @return bool
     */
    private function isWhitespace(string $character): bool
    {
        return ($character === ' ' || $character === "\r" || $character === "\n" || $character === "\t");
    }

    /**
     * @param array $function
     * @param bool  $shouldIncludeParanthesesInInsertText
     *
     * @return AutocompletionSuggestion
     */
    private function createSuggestion(
        array $function,
        bool $shouldIncludeParanthesesInInsertText
    ): AutocompletionSuggestion {
        $insertText = $function['name'];
        $placeCursorBetweenParentheses = !empty($function['parameters']);

        if ($shouldIncludeParanthesesInInsertText) {
            $insertText .= '()';
        }

        return new AutocompletionSuggestion(
            $function['name'],
            SuggestionKind::FUNCTION,
            $insertText,
            $this->createLabel($function),
            $function['shortDescription'],
            [
                'isDeprecated'                  => $function['isDeprecated'],
                'protectionLevel'               => null,
                'declaringStructure'            => null,
                'url'                           => null,
                'returnTypes'                   => $this->createReturnTypes($function),
                'placeCursorBetweenParentheses' => $placeCursorBetweenParentheses
            ]
        );
    }

    /**
     * @param array $function
     *
     * @return string
     */
    private function createLabel(array $function): string
    {
        $body = '(';

        $isInOptionalList = false;

        foreach ($function['parameters'] as $index => $param) {
            $description = '';

            if ($param['isOptional'] && !$isInOptionalList) {
                $description .= '[';
            }

            if ($index > 0) {
                $description .= ', ';
            }

            if ($param['isVariadic']) {
                $description .= '...';
            }

            if ($param['isReference']) {
                $description .= '&';
            }

            $description .= '$' . $param['name'];

            if ($param['defaultValue']) {
                $description .= ' = ' . $param['defaultValue'];
            }

            if ($param['isOptional'] && $index === (count($function['parameters']) - 1)) {
                $description .= ']';
            }

            $isInOptionalList = $param['isOptional'];

            $body .= $description;
        }

        $body .= ')';

        return $function['name'] . $body;
    }

    /**
     * @param array $function
     *
     * @return string|null
     */
    private function getFunctionProtectionLevel(array $function): ?string
    {
        if ($function['isPublic']) {
            return 'public';
        } elseif ($function['isProtected']) {
            return 'private';
        } elseif ($function['isPrivate']) {
            return 'private';
        }

        return null;
    }

    /**
     * @param array $function
     *
     * @return string
     */
    private function createReturnTypes(array $function): string
    {
        $typeNames = $this->getShortReturnTypes($function);

        return implode('|', $typeNames);
    }

    /**
     * @param array $function
     *
     * @return string[]
     */
    private function getShortReturnTypes(array $function): array
    {
        $shortTypes = [];

        foreach ($function['returnTypes'] as $type) {
            $shortTypes[] = $this->getClassShortNameFromFqcn($type['fqcn']);
        }

        return $shortTypes;
    }

    /**
     * @param string $fqcn
     *
     * @return string
     */
    private function getClassShortNameFromFqcn(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return array_pop($parts);
    }
}