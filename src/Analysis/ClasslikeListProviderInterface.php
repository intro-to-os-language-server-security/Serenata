<?php

namespace Serenata\Analysis;

use RuntimeException;

/**
 * Retrieves a list of classlikes.
 */
interface ClasslikeListProviderInterface
{
    /**
     * @throws RuntimeException
     *
     * @return array<string, array> mapping FQCN's to classlikes.
     */
    public function getAll(): array;
}
