<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;
use UnexpectedValueException;

use GetOptionKit\OptionCollection;

use PhpIntegrator\Analysis\VariableScanner;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

use PhpParser\Parser;

/**
 * Command that shows information about the scopes at a specific position in a file.
 */
class AvailableVariablesCommand extends AbstractCommand
{
    use ParserAwareTrait;

    /**
     * @var VariableScanner
     */
    protected $variableScanner;

    /**
     * @var SourceCodeStreamReader
     */
    protected $sourceCodeStreamReader;

    /**
     * @param VariableScanner        $variableScanner
     * @param Parser                 $parser
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(
        VariableScanner $variableScanner,
        Parser $parser,
        SourceCodeStreamReader $sourceCodeStreamReader
    ) {
        $this->variableScanner = $variableScanner;
        $this->parser = $parser;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @inheritDoc
     */
    public function attachOptions(OptionCollection $optionCollection)
    {
        $optionCollection->add('file?', 'The file to examine.')->isa('string');
        $optionCollection->add('stdin?', 'If set, file contents will not be read from disk but the contents from STDIN will be used instead.');
        $optionCollection->add('charoffset?', 'If set, the input offset will be treated as a character offset instead of a byte offset.');
        $optionCollection->add('offset:', 'The character byte offset into the code to use for the determination.')->isa('number');
    }

    /**
     * @inheritDoc
     */
    protected function process(ArrayAccess $arguments)
    {
        if (!isset($arguments['offset'])) {
            throw new UnexpectedValueException('An --offset must be supplied into the source code!');
        }

        $code = null;

        if (isset($arguments['stdin']) && $arguments['stdin']->value) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } elseif (isset($arguments['file']) && $arguments['file']->value) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']->value);
        } else {
            throw new UnexpectedValueException('Either a --file file must be supplied or --stdin must be passed!');
        }

        $offset = $arguments['offset']->value;

        if (isset($arguments['charoffset']) && $arguments['charoffset']->value == true) {
            $offset = SourceCodeHelpers::getByteOffsetFromCharacterOffset($offset, $code);
        }

        $result = $this->getAvailableVariables($code, $offset);

        return $this->outputJson(true, $result);
     }

    /**
     * @param string $code
     * @param int    $offset
     *
     * @return array
     */
     public function getAvailableVariables($code, $offset)
     {
         $nodes = $this->parse($code);

         return $this->variableScanner->getAvailableVariables($nodes, $offset);
     }
}
