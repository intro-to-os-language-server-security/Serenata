<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use GetOptionKit\OptionCollection;

use PhpIntegrator\Analysis\Typing\TypeDeducer;

use PhpIntegrator\Parsing\PartialParser;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Allows deducing the types of an expression (e.g. a call chain, a simple string, ...).
 */
class DeduceTypesCommand extends AbstractCommand
{
    /**
     * @var TypeDeducer
     */
    protected $typeDeducer;

    /**
     * @var PartialParser
     */
    protected $partialParser;

    /**
     * @var SourceCodeStreamReader
     */
    protected $sourceCodeStreamReader;

    /**
     * @param TypeDeducer            $typeDeducer
     * @param PartialParser          $partialParser
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        TypeDeducer $typeDeducer,
        PartialParser $partialParser,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->typeDeducer = $typeDeducer;
        $this->partialParser = $partialParser;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @inheritDoc
     */
    public function attachOptions(OptionCollection $optionCollection)
    {
        $optionCollection->add('file:', 'The file to examine.')->isa('string');
        $optionCollection->add('stdin?', 'If set, file contents will not be read from disk but the contents from STDIN will be used instead.');
        $optionCollection->add('charoffset?', 'If set, the input offset will be treated as a character offset instead of a byte offset.');
        $optionCollection->add('part+', 'A part of the expression as string. Specify this as many times as you have parts.')->isa('string');
        $optionCollection->add('offset:', 'The character byte offset into the code to use for the determination.')->isa('number');
        $optionCollection->add('ignore-last-element?', 'If set, when determining the parts automatically, the last part of the expression will be ignored (i.e. because it may not be complete).');
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['file'])) {
            throw new InvalidArgumentsException('A --file must be supplied!');
        } elseif (!isset($arguments['offset'])) {
            throw new InvalidArgumentsException('An --offset must be supplied into the source code!');
        }

        if (isset($arguments['stdin']) && $arguments['stdin']->value) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } else {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']->value);
        }

        $offset = $arguments['offset']->value;

        if (isset($arguments['charoffset']) && $arguments['charoffset']->value == true) {
            $offset = SourceCodeHelpers::getByteOffsetFromCharacterOffset($offset, $code);
        }

        $parts = [];

        if (isset($arguments['part'])) {
            $parts = $arguments['part']->value;
        } else {
            $parts = $this->partialParser->retrieveSanitizedCallStackAt(substr($code, 0, $offset));

            if (!empty($parts) && isset($arguments['ignore-last-element']) && $arguments['ignore-last-element']) {
                array_pop($parts);
            }
        }

        $result = $this->deduceTypes(
           isset($arguments['file']) ? $arguments['file']->value : null,
           $code,
           $parts,
           $offset
        );

        return $this->outputJson(true, $result);
    }

    /**
     * @param string   $file
     * @param string   $code
     * @param string[] $parts
     * @param int      $offset
     *
     * @return string[]
     */
    protected function deduceTypes($file, $code, array $parts, $offset)
    {
        return $this->typeDeducer->deduceTypes($file, $code, $parts, $offset);
    }
}
