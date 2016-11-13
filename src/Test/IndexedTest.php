<?php

namespace PhpIntegrator\Test;

use ReflectionClass;

use PhpIntegrator\UserInterface\CliApplication;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Abstract base class for tests that need to test functionality that requires an indexing database to be set up with
 * the contents of a file or folder already indexed.
 */
abstract class IndexedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    static $testContainerBuiltinStructuralElements;

    /**
     * @return ContainerBuilder
     */
    protected function createTestContainer()
    {
        $app = new CliApplication();

        $refClass = new ReflectionClass(CliApplication::class);

        $refMethod = $refClass->getMethod('createContainer');
        $refMethod->setAccessible(true);

        $container = $refMethod->invoke($app);

        // Replace some container items for testing purposes.
        $container->setAlias('parser', 'parser.phpParser');
        $container->set('cache', new \Doctrine\Common\Cache\VoidCache());
        $container->get('indexDatabase')->setDatabasePath(':memory:');

        $success = $container->get('initializeCommand')->initialize(false);

        $this->assertTrue($success);

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $testPath
     * @param bool             $mayFail
     */
    protected function indexTestFile(ContainerBuilder $container, $testPath, $mayFail = false)
    {
        $success = $container->get('indexer')->reindex(
            [$testPath],
            false,
            false,
            false,
            [],
            ['phpt']
        );

        if (!$mayFail) {
            $this->assertTrue($success);
        }
    }

    /**
     *
     */
    protected function createTestContainerForBuiltinStructuralElements()
    {
        // Indexing builtin items is a fairy large performance hit to run every test, so keep the property static.
        if (!self::$testContainerBuiltinStructuralElements) {
            self::$testContainerBuiltinStructuralElements = $this->createTestContainer();
            self::$testContainerBuiltinStructuralElements->get('builtinIndexer')->index();
        }

        return self::$testContainerBuiltinStructuralElements;
    }
}
