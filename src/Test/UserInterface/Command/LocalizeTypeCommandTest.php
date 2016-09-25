<?php

namespace PhpIntegrator\Test\UserInterface\Command;

use PhpIntegrator\UserInterface\Command\LocalizeTypeCommand;

use PhpIntegrator\Test\IndexedTest;

use PhpIntegrator\Indexing\IndexDatabase;

class LocalizeTypeCommandTest extends IndexedTest
{
    public function testCorrectlyLocalizesVariousTypes()
    {
        $path = __DIR__ . '/LocalizeTypeCommandTest/' . 'LocalizeType.php.test';

        $container = $this->createTestContainer();

        $this->indexTestFile($container, $path);

        $command = new LocalizeTypeCommand(
            $container->get('indexDatabase'),
            $container->get('fileTypeLocalizerFactory')
        );

        $this->assertEquals('\C', $command->localizeType('C', $path, 1));
        $this->assertEquals('\C', $command->localizeType('\C', $path, 5));
        $this->assertEquals('C', $command->localizeType('A\C', $path, 5));
        $this->assertEquals('C', $command->localizeType('B\C', $path, 10));
        $this->assertEquals('DateTime', $command->localizeType('B\DateTime', $path, 10));
        $this->assertEquals('DateTime', $command->localizeType('DateTime', $path, 11));
        $this->assertEquals('DateTime', $command->localizeType('DateTime', $path, 12));
        $this->assertEquals('DateTime', $command->localizeType('\DateTime', $path, 12));
        $this->assertEquals('D\Test', $command->localizeType('C\D\Test', $path, 13));
        $this->assertEquals('E', $command->localizeType('C\D\E', $path, 14));
        $this->assertEquals('H', $command->localizeType('F\G\H', $path, 16));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowsExceptionOnUnknownFile()
    {
        $container = $this->createTestContainer();

        $command = new LocalizeTypeCommand(
            $container->get('indexDatabase'),
            $container->get('fileTypeLocalizerFactory')
        );

        $command->localizeType('C', 'MissingFile.php', 1);
    }
}
