<?php

namespace Serenata\Indexing;

use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\SchemaTool;

use Serenata\Indexing\Structures\AccessModifierNameValue;

/**
 * Initializes the database schema.
 */
final class SchemaInitializer
{
    /**
     * @var int
     */
    public const SCHEMA_VERSION = 30;

    /**
     * @var string
     */
    public const VERSION_SETTING_NAME = 'version';

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $entityManager = $this->managerRegistry->getManager();

        assert($entityManager instanceof EntityManagerInterface);

        $schemaTool = new SchemaTool($entityManager);

        // $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $this->loadFixtures();
    }

    /**
     * @return void
     */
    private function loadFixtures(): void
    {
        $entityManager = $this->managerRegistry->getManager();

        $entityManager->persist(new Structures\AccessModifier(AccessModifierNameValue::PUBLIC_));
        $entityManager->persist(new Structures\AccessModifier(AccessModifierNameValue::PROTECTED_));
        $entityManager->persist(new Structures\AccessModifier(AccessModifierNameValue::PRIVATE_));

        $entityManager->persist(new Structures\Setting(self::VERSION_SETTING_NAME, self::SCHEMA_VERSION));

        $entityManager->flush();
    }
}
