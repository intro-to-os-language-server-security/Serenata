<?php

namespace PhpIntegrator\UserInterface;

use Exception;
use ArrayAccess;
use RuntimeException;

use Doctrine\Common\Cache\FilesystemCache;

use PhpIntegrator\Analysis\VariableScanner;
use PhpIntegrator\Analysis\DocblockAnalyzer;
use PhpIntegrator\Analysis\ClasslikeInfoBuilder;
use PhpIntegrator\Analysis\ClasslikeExistanceChecker;
use PhpIntegrator\Analysis\GlobalConstantExistanceChecker;
use PhpIntegrator\Analysis\GlobalFunctionExistanceChecker;

use PhpIntegrator\Analysis\Conversion\MethodConverter;
use PhpIntegrator\Analysis\Conversion\ConstantConverter;
use PhpIntegrator\Analysis\Conversion\PropertyConverter;
use PhpIntegrator\Analysis\Conversion\FunctionConverter;
use PhpIntegrator\Analysis\Conversion\ClasslikeConverter;
use PhpIntegrator\Analysis\Conversion\ClasslikeConstantConverter;

use PhpIntegrator\Analysis\Relations\TraitUsageResolver;
use PhpIntegrator\Analysis\Relations\InheritanceResolver;
use PhpIntegrator\Analysis\Relations\InterfaceImplementationResolver;

use PhpIntegrator\Analysis\Typing\TypeDeducer;
use PhpIntegrator\Analysis\Typing\TypeResolver;
use PhpIntegrator\Analysis\Typing\TypeAnalyzer;
use PhpIntegrator\Analysis\Typing\TypeLocalizer;
use PhpIntegrator\Analysis\Typing\FileTypeResolverFactory;
use PhpIntegrator\Analysis\Typing\FileTypeLocalizerFactory;
use PhpIntegrator\Analysis\Typing\ProjectTypeResolverFactory;
use PhpIntegrator\Analysis\Typing\ProjectTypeResolverFactoryFacade;

use PhpIntegrator\Indexing\FileIndexer;
use PhpIntegrator\Indexing\IndexDatabase;
use PhpIntegrator\Indexing\ProjectIndexer;
use PhpIntegrator\Indexing\BuiltinIndexer;
use PhpIntegrator\Indexing\CallbackStorageProxy;

use PhpIntegrator\Parsing\PartialParser;
use PhpIntegrator\Parsing\DocblockParser;
use PhpIntegrator\Parsing\CachingParserProxy;

use PhpIntegrator\Utility\SourceCodeStreamReader;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Main application class.
 */
class Application
{
    /**
     * The version of the database we're currently at. When there are large changes to the layout of the database, this
     * number is bumped and all databases with older versions will be dumped and replaced with a new index database.
     *
     * @var int
     */
    const DATABASE_VERSION = 28;

    /**
     * @var string
     */
    protected $projectName;

    /**
     * @var string
     */
    protected $databaseFile;

    /**
     * @var resource|null
     */
    protected $stdinStream;

    /**
     * @param Command\AbstractCommand $command
     * @param ArrayAccess             $arguments
     *
     * @return mixed
     */
    public function handle(Command\AbstractCommand $command, ArrayAccess $arguments)
    {
        $result = null;
        $success = false;

        try {
            $result = $command->execute($arguments);
            $success = true;
        } catch (Command\InvalidArgumentsException $e) {
            $result = $e->getMessage();
        } catch (Exception $e) {
            $result = $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage();
        } catch (\Throwable $e) {
            // On PHP < 7, throwable simply won't exist and this clause is never triggered.
            $result = $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage();
        }

        return $this->outputJson($success, $result);
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder();

        $container
            ->register('application', Application::class)
            ->setSynthetic(true);

        $container->set('application', $this);

        $container
            ->register('lexer', Lexer::class)
            ->addArgument([
                'usedAttributes' => [
                    'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'
                ]
            ]);

        $container
            ->register('parser.phpParserFactory', ParserFactory::class);

        $container
            ->register('parser.phpParser', Parser::class)
            ->setFactory([new Reference('parser.phpParserFactory'), 'create'])
            ->setArguments([ParserFactory::PREFER_PHP7, new Reference('lexer'), [
                'throwOnError' => false
            ]]);

        $container
            ->register('parser.cachingParserProxy', CachingParserProxy::class)
            ->addArgument(new Reference('parser.phpParser'));

        $container->setAlias('parser', 'parser.cachingParserProxy');

        $container
            ->register('cache', FilesystemCache::class)
            ->setArguments([new Expression("service('application').getCacheDirectory()")]);

        $container
            ->register('variableScanner', VariableScanner::class);

        $container
            ->register('typeAnalyzer', TypeAnalyzer::class);

        $container
            ->register('typeResolver', TypeResolver::class)
            ->setArguments([new Reference('typeAnalyzer')]);

        $container
            ->register('typeLocalizer', TypeLocalizer::class)
            ->setArguments([new Reference('typeAnalyzer')]);

        $container
            ->register('partialParser', PartialParser::class);

        $container
            ->register('sourceCodeStreamReader', SourceCodeStreamReader::class)
            ->setArguments([$this->stdinStream]);

        $container
            ->register('docblockParser', DocblockParser::class);

        $container
            ->register('docblockAnalyzer', DocblockAnalyzer::class);

        $container
            ->register('constantConverter', ConstantConverter::class);

        $container
            ->register('classlikeConstantConverter', ClasslikeConstantConverter::class);

        $container
            ->register('propertyConverter', PropertyConverter::class);

        $container
            ->register('classlikeConverter', ClasslikeConverter::class);

        $container
            ->register('functionConverter', FunctionConverter::class);

        $container
            ->register('methodConverter', MethodConverter::class);

        $container
            ->register('fileTypeResolverFactory', FileTypeResolverFactory::class)
            ->setArguments([new Reference('typeResolver'), new Reference('indexDatabase')]);

        $container
            ->register('projectTypeResolverFactory', ProjectTypeResolverFactory::class)
            ->setArguments([
                new Reference('globalConstantExistanceChecker'),
                new Reference('globalFunctionExistanceChecker'),
                new Reference('indexDatabase')
            ]);

        $container
            ->register('projectTypeResolverFactoryFacade', ProjectTypeResolverFactoryFacade::class)
            ->setArguments([
                new Reference('projectTypeResolverFactory'),
                new Reference('fileTypeResolverFactory')
            ]);

        $container
            ->register('fileTypeLocalizerFactory', FileTypeLocalizerFactory::class)
            ->setArguments([new Reference('typeLocalizer'), new Reference('indexDatabase')]);

        $container
            ->register('inheritanceResolver', InheritanceResolver::class)
            ->setArguments([new Reference('docblockAnalyzer'), new Reference('typeAnalyzer')]);

        $container
            ->register('interfaceImplementationResolver', InterfaceImplementationResolver::class)
            ->setArguments([new Reference('docblockAnalyzer'), new Reference('typeAnalyzer')]);

        $container
            ->register('traitUsageResolver', TraitUsageResolver::class)
            ->setArguments([new Reference('docblockAnalyzer'), new Reference('typeAnalyzer')]);

        $container
            ->register('indexDatabase', IndexDatabase::class)
            ->setArguments([new Expression("service('application').getDatabaseFile()"), self::DATABASE_VERSION]);

        $container
            ->register('classlikeInfoBuilderProviderCachingProxy', ClasslikeInfoBuilderProviderCachingProxy::class)
            ->setArguments([new Reference('indexDatabase'), new Reference('cache')]);

        $container
            ->setAlias('classlikeInfoBuilderProvider', 'classlikeInfoBuilderProviderCachingProxy');

        $container
            ->register('classlikeExistanceChecker', ClasslikeExistanceChecker::class)
            ->setArguments([new Reference('indexDatabase')]);

        $container
            ->register('globalFunctionExistanceChecker', GlobalFunctionExistanceChecker::class)
            ->setArguments([new Reference('indexDatabase')]);

        $container
            ->register('globalConstantExistanceChecker', GlobalConstantExistanceChecker::class)
            ->setArguments([new Reference('indexDatabase')]);

        $container
            ->register('storageForIndexers', CallbackStorageProxy::class)
            ->setArguments([new Reference('indexDatabase'), function ($fqcn) use ($container) {
                $provider = $container->get('classlikeInfoBuilderProvider');

                if ($provider instanceof ClasslikeInfoBuilderProviderCachingProxy) {
                    $provider->clearCacheFor($fqcn);
                }
            }]);

        $container
            ->register('classlikeInfoBuilder', ClasslikeInfoBuilder::class)
            ->setArguments([
                new Reference('constantConverter'),
                new Reference('classlikeConstantConverter'),
                new Reference('propertyConverter'),
                new Reference('functionConverter'),
                new Reference('methodConverter'),
                new Reference('classlikeConverter'),
                new Reference('inheritanceResolver'),
                new Reference('interfaceImplementationResolver'),
                new Reference('traitUsageResolver'),
                new Reference('classlikeInfoBuilderProvider'),
                new Reference('typeAnalyzer')
            ]);

        $container
            ->register('typeDeducer', TypeDeducer::class)
            ->setArguments([
                new Reference('parser'),
                new Reference('classListCommand'),
                new Reference('docblockParser'),
                new Reference('partialParser'),
                new Reference('typeAnalyzer'),
                new Reference('typeResolver'),
                new Reference('fileTypeResolverFactory'),
                new Reference('indexDatabase'),
                new Reference('classlikeInfoBuilder'),
                new Reference('functionConverter'),
                new Reference('constantConverter')
            ]);

        $container
            ->register('builtinIndexer', BuiltinIndexer::class)
            ->setArguments([
                new Reference('indexDatabase'),
                new Reference('typeAnalyzer'),
                new Reference('typeDeducer')
            ]);

        $container
            ->register('fileIndexer', FileIndexer::class)
            ->setArguments([
                new Reference('storageForIndexers'),
                new Reference('typeAnalyzer'),
                new Reference('typeResolver'),
                new Reference('docblockParser'),
                new Reference('typeDeducer'),
                new Reference('parser')
            ]);

        $container
            ->register('projectIndexer', ProjectIndexer::class)
            ->setArguments([
                new Reference('storageForIndexers'),
                new Reference('builtinIndexer'),
                new Reference('fileIndexer'),
                new Reference('sourceCodeStreamReader'),
                new Expression("service('indexDatabase').getFileModifiedMap()")
            ]);

        // Commands.
        $container
            ->register('initializeCommand', Command\InitializeCommand::class)
            ->setArguments([new Reference('projectIndexer')]);

        $container
            ->register('reindexCommand', Command\ReindexCommand::class)
            ->setArguments([
                new Reference('indexDatabase'),
                new Reference('projectIndexer'),
                new Reference('sourceCodeStreamReader')
            ]);

        $container
            ->register('vacuumCommand', Command\VacuumCommand::class)
            ->setArguments([new Reference('projectIndexer')]);

        $container
            ->register('truncateCommand', Command\TruncateCommand::class)
            ->setArguments([new Expression("service('application').getDatabaseFile()"), new Reference('cache')]);

        $container
            ->register('classListCommand', Command\ClassListCommand::class)
            ->setArguments([
                new Reference('constantConverter'),
                new Reference('classlikeConstantConverter'),
                new Reference('propertyConverter'),
                new Reference('functionConverter'),
                new Reference('methodConverter'),
                new Reference('classlikeConverter'),
                new Reference('inheritanceResolver'),
                new Reference('interfaceImplementationResolver'),
                new Reference('traitUsageResolver'),
                new Reference('classlikeInfoBuilderProvider'),
                new Reference('typeAnalyzer'),
                new Reference('indexDatabase')
            ]);

        $container
            ->register('classInfoCommand', Command\ClassInfoCommand::class)
            ->setArguments([new Reference('typeAnalyzer'), new Reference('classlikeInfoBuilder')]);

        $container
            ->register('globalFunctionsCommand', Command\GlobalFunctionsCommand::class)
            ->setArguments([new Reference('functionConverter'), new Reference('indexDatabase')]);

        $container
            ->register('globalConstantsCommand', Command\GlobalConstantsCommand::class)
            ->setArguments([new Reference('constantConverter'), new Reference('indexDatabase')]);

        $container
            ->register('resolveTypeCommand', Command\ResolveTypeCommand::class)
            ->setArguments([new Reference('indexDatabase'), new Reference('ProjectTypeResolverFactoryFacade')]);

        $container
            ->register('localizeTypeCommand', Command\LocalizeTypeCommand::class)
            ->setArguments([new Reference('indexDatabase'), new Reference('fileTypeLocalizerFactory')]);

        $container
            ->register('semanticLintCommand', Command\SemanticLintCommand::class)
            ->setArguments([
                new Reference('sourceCodeStreamReader'),
                new Reference('parser'),
                new Reference('fileTypeResolverFactory'),
                new Reference('typeDeducer'),
                new Reference('classlikeInfoBuilder'),
                new Reference('docblockParser'),
                new Reference('typeAnalyzer'),
                new Reference('docblockAnalyzer'),
                new Reference('classlikeExistanceChecker'),
                new Reference('globalConstantExistanceChecker'),
                new Reference('globalFunctionExistanceChecker')
            ]);

        $container
            ->register('availableVariablesCommand', Command\AvailableVariablesCommand::class)
            ->setArguments([
                new Reference('variableScanner'),
                new Reference('parser'),
                new Reference('sourceCodeStreamReader')
            ]);

        $container
            ->register('deduceTypesCommand', Command\DeduceTypesCommand::class)
            ->setArguments([
                    new Reference('typeDeducer'),
                    new Reference('partialParser'),
                    new Reference('sourceCodeStreamReader')
                ]);

        $container
            ->register('invocationInfoCommand', Command\InvocationInfoCommand::class)
            ->setArguments([new Reference('partialParser'), new Reference('sourceCodeStreamReader')]);

        $container
            ->register('namespaceListCommand', Command\NamespaceListCommand::class)
            ->setArguments([new Reference('indexDatabase')]);

        return $container;
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        $cachePath = sys_get_temp_dir() .
            '/php-integrator-base/' .
            get_current_user() . '/' .
            $this->projectName . '/' .
            self::DATABASE_VERSION .
            '/';

        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        return $cachePath;
    }

    /**
     * @return string
     */
    public function getDatabaseFile()
    {
        return $this->databaseFile;
    }

    /**
     * Outputs JSON.
     *
     * @param bool  $success
     * @param mixed $data
     *
     * @throws RuntimeException When the encoding fails, which should never happen.
     *
     * @return string
     */
    protected function outputJson($success, $data)
    {
        $output = json_encode([
            'success' => $success,
            'result'  => $data
        ]);

        if (!$output) {
            $errorMessage = json_last_error_msg() ?: 'Unknown';

            throw new RuntimeException(
                'The encoded JSON output was empty, something must have gone wrong! The error message was: ' .
                '"' .
                $errorMessage .
                '"'
            );
        }

        return $output;
    }
}
