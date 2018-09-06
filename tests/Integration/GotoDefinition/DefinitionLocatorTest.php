<?php

namespace Serenata\Tests\Unit\SignatureHelp;

use UnexpectedValueException;

use Serenata\Common\Range;
use Serenata\Common\Position;

use Serenata\GotoDefinition\GotoDefinitionResponse;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\Utility\Location;
use Serenata\Utility\PositionEncoding;
use Serenata\Utility\TextDocumentItem;

class DefinitionLocatorTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testFunctionCall(): void
    {
        $fileName = 'FunctionCall.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            43,
            48,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(7, 1))
            ))
        );
    }

    /**
     * @return void
     */
    public function testMethodCall(): void
    {
        $fileName = 'MethodCall.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            76,
            79,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(6, 4), new Position(9, 5))
            ))
        );
    }

    /**
     * @return void
     */
    public function testConstant(): void
    {
        $fileName = 'Constant.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            45,
            47,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 6), new Position(4, 13))
            ))
        );
    }

    /**
     * @return void
     */
    public function testConstantInClassConstant(): void
    {
        $fileName = 'ConstantInClassConstant.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            71,
            73,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(6, 17), new Position(6, 24))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInClassConstant(): void
    {
        $fileName = 'ClassInClassConstant.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            68,
            68,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(7, 1))
            ))
        );
    }

    /**
     * @return void
     */
    public function testStaticMethodCallMethod(): void
    {
        $fileName = 'StaticMethodCall.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            79,
            82,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(6, 4), new Position(9, 5))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInStaticMethodCall(): void
    {
        $fileName = 'ClassInStaticMethodCall.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            96,
            96,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(10, 1))
            ))
        );
    }

    /**
     * @return void
     */
    public function testPropertyFetch(): void
    {
        $fileName = 'Property.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            105,
            107,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(6, 14), new Position(6, 22))
            ))
        );
    }

    /**
     * @return void
     */
    public function testPropertyInStaticPropertyFetch(): void
    {
        $fileName = 'PropertyInStaticPropertyFetch.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            108,
            111,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(6, 21), new Position(6, 29))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInStaticPropertyFetch(): void
    {
        $fileName = 'testClassInStaticPropertyFetch.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            60,
            60,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(7, 1))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInUseStatement(): void
    {
        $fileName = 'ClassInUseStatement.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            77,
            79,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 4), new Position(7, 5))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInGroupedUseStatement(): void
    {
        $fileName = 'ClassInGroupedUseStatement.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            93,
            93,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 4), new Position(7, 5))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInImplements(): void
    {
        $fileName = 'ClassInImplements.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            56,
            56,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(4, 14))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInExtends(): void
    {
        $fileName = 'ClassInExtends.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            49,
            49,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(4, 10))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInTraitUse(): void
    {
        $fileName = 'ClassInTraitUse.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            51,
            51,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(4, 0), new Position(4, 10))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInTraitPrecedence(): void
    {
        $fileName = 'ClassInTraitPrecedence.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            131,
            131,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(2, 0), new Position(5, 1))
            ))
        );
    }

    /**
     * @return void
     */
    public function testClassInTraitAlias(): void
    {
        $fileName = 'ClassInTraitAlias.phpt';

        static::assertGotoDefinitionResponseEquals(
            $fileName,
            84,
            84,
            new GotoDefinitionResponse(new Location(
                $this->getPathFor($fileName),
                new Range(new Position(2, 0), new Position(5, 1))
            ))
        );
    }

    /**
     * @param string $file
     * @param int    $position
     *
     * @return GotoDefinitionResponse|null
     */
    private function locateDefinition(string $file, int $position): ?GotoDefinitionResponse
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $code = $this->container->get('sourceCodeStreamReader')->getSourceCodeFromFile($path);

        return $this->container->get('definitionLocator')->locate(
            new TextDocumentItem($path, $code),
            Position::createFromByteOffset($position, $code, PositionEncoding::VALUE)
        );
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getPathFor(string $file): string
    {
        return __DIR__ . '/DefinitionLocatorTest/' . $file;
    }

    /**
     * @param string                 $fileName
     * @param int                    $start
     * @param int                    $end
     * @param GotoDefinitionResponse $gotoDefinitionResponse
     */
    private function assertGotoDefinitionResponseEquals(
        string $fileName,
        int $start,
        int $end,
        GotoDefinitionResponse $gotoDefinitionResponse
    ): void {
        $i = $start;

        while ($i <= $end) {
            static::assertEquals(
                $gotoDefinitionResponse,
                $this->locateDefinition($fileName, $i),
                'Failed locating definition at offset ' . $i
            );

            ++$i;
        }

        // Assert that the range doesn't extend longer than it should.
        $gotException = false;

        try {
            $resultBeforeRange = $this->locateDefinition($fileName, $start - 1);
        } catch (UnexpectedValueException $e) {
            $gotException = true;
        }

        static::assertTrue(
            $gotException === true ||
            $resultBeforeRange->getResult() === null ||
            ($gotException === false && (
                $resultBeforeRange->getResult()[0]->getUri() !== $gotoDefinitionResponse->getUri() ||
                $resultBeforeRange->getResult()[0]->getRange()->getStart()->getLine() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getStart()->getLine() ||
                $resultBeforeRange->getResult()[0]->getRange()->getStart()->getCharacter() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getStart()->getCharacter() ||
                $resultBeforeRange->getResult()[0]->getRange()->getEnd()->getLine() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getEnd()->getLine() ||
                $resultBeforeRange->getResult()[0]->getRange()->getEnd()->getCharacter() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getEnd()->getCharacter()
            )),
            "Range does not start exactly at position {$start}, but seems to continue before it"
        );

        $gotException = false;

        try {
            $resultAfterRange = $this->locateDefinition($fileName, $end + 1);
        } catch (UnexpectedValueException $e) {
            $gotException = true;
        }

        static::assertTrue(
            $gotException === true ||
            $resultAfterRange->getResult() === null ||
            ($gotException === false && (
                $resultAfterRange->getResult()[0]->getUri() !== $gotoDefinitionResponse->getUri() ||
                $resultAfterRange->getResult()[0]->getRange()->getStart()->getLine() !==
                    $gotoDefinitionResponse->getLine() ||
                $resultAfterRange->getResult()[0]->getRange()->getStart()->getLine() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getStart()->getLine() ||
                $resultAfterRange->getResult()[0]->getRange()->getStart()->getCharacter() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getStart()->getCharacter() ||
                $resultAfterRange->getResult()[0]->getRange()->getEnd()->getLine() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getEnd()->getLine() ||
                $resultAfterRange->getResult()[0]->getRange()->getEnd()->getCharacter() !==
                    $gotoDefinitionResponse->getResult()[0]->getRange()->getEnd()->getCharacter()
            )),
            "Range does not end exactly at position {$end}, but seems to continue after it"
        );
    }
}
