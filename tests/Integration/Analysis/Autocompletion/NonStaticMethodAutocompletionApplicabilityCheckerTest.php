<?php

namespace PhpIntegrator\Tests\Integration\Analysis\Autocompletion;

class NonStaticMethodAutocompletionApplicabilityCheckerTest extends AbstractAutocompletionProviderTest
{
    /**
     * @dataProvider getFileNamesWhereShouldApply
     *
     * @param string $fileName
     *
     * @return void
     */
    public function testAppliesAtExpectedLocations(string $fileName): void
    {
        static::assertNotEmpty($this->provide($fileName, 'MethodList.phpt'));
    }

    /**
     * @dataProvider getFileNamesWhereShouldNotApply
     *
     * @param string $fileName
     *
     * @return void
     */
    public function testDoesNotApplyAtExpectedLocations(string $fileName): void
    {
        static::assertEmpty($this->provide($fileName, 'MethodList.phpt'));
    }

    /**
     * @return string[]
     */
    public function getFileNamesWhereShouldApply(): array
    {
        return [
            ['PropertyFetch.phpt'],
            ['MethodCall.phpt'],
            ['StaticMethodCallParent.phpt']
        ];
    }

    /**
     * @return string[]
     */
    public function getFileNamesWhereShouldNotApply(): array
    {
        return [
            ['VariableName.phpt'],
            ['StaticMethodCall.phpt'],
            ['ClassConstFetch.phpt'],
            ['UseStatement.phpt'],
            // ['Docblock.phpt'],
            // ['Comment.phpt'],
            ['FunctionSignature.phpt'],
            ['MethodSignature.phpt'],
            ['StaticPropertyFetch.phpt'],
            ['ClassBody.phpt'],
            ['String.phpt'],
            ['TopLevelNamespace.phpt'],
            ['FunctionLike.phpt']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getFolderName(): string
    {
        return 'ApplicabilityCheckingAutocompletionProviderTest';
    }

    /**
     * @inheritDoc
     */
    protected function getProviderName(): string
    {
        return 'applicabilityCheckingNonStaticMethodAutocompletionProvider';
    }
}
