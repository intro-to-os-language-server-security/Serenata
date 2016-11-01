<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ClearableCache;

use GetOptionKit\OptionCollection;

/**
 * Command that truncates the database.
 */
class TruncateCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $databaseFile;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param string $databaseFile
     * @param Cache  $cache
     */
    public function __construct($databaseFile, Cache $cache)
    {
        $this->databaseFile = $databaseFile;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function attachOptions(OptionCollection $optionCollection)
    {

    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        $success = $this->truncate();

        return $this->outputJson($success, []);
    }

    /**
     * @return bool
     */
    public function truncate()
    {
        @unlink($this->databaseFile);

        if ($this->cache instanceof ClearableCache) {
            $this->cache->deleteAll();
        }

        return true;
    }
}
