<?php

namespace PhpIntegrator\Utility;

use RuntimeException;

/**
 * Represents a stream to a temporary file.
 */
final class TmpFileStream extends AbstractResourceStream
{
    /**
     * @inheritDoc
     */
    protected function createHandle()
    {
        return tmpfile();
    }
}