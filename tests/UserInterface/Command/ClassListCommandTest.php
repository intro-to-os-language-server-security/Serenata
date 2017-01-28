<?php

namespace PhpIntegrator\Tests\UserInterface\Command;

use PhpIntegrator\UserInterface\Command\ClassListCommand;

use PhpIntegrator\Tests\IndexedTest;

class ClassListCommandTest extends IndexedTest
{
    /**
     * @return void
     */
    public function testClassList(): void
    {
        $path = __DIR__ . '/ClassListCommandTest/' . 'ClassList.phpt';

        $container = $this->createTestContainer();

        $this->indexTestFile($container, $path);

        $command = new ClassListCommand(
            $container->get('constantConverter'),
            $container->get('classlikeConstantConverter'),
            $container->get('propertyConverter'),
            $container->get('functionConverter'),
            $container->get('methodConverter'),
            $container->get('classlikeConverter'),
            $container->get('inheritanceResolver'),
            $container->get('interfaceImplementationResolver'),
            $container->get('traitUsageResolver'),
            $container->get('classlikeInfoBuilderProvider'),
            $container->get('typeAnalyzer'),
            $container->get('indexDatabase')
        );

        $output = $command->getClassList($path);

        $this->assertThat($output, $this->arrayHasKey('\A\FirstClass'));
        $this->assertThat($output, $this->arrayHasKey('\A\SecondClass'));
    }
}
