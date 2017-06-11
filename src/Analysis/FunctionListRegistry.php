<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

use Doctrine\DBAL\Exception\DriverException;

use PhpIntegrator\Indexing\Structures;
use PhpIntegrator\Indexing\ManagerRegistry;

/**
 * Registry that maintains a list of (global) functions.
 */
final class FunctionListRegistry implements FunctionListProviderInterface
{
    /**
     * @var FunctionListProviderInterface
     */
    private $delegate;

    /**
     * @var array
     */
    private $registry;

    /**
     * @param FunctionListProviderInterface $delegate
     */
    public function __construct(FunctionListProviderInterface $delegate)
    {
        $this->delegate = $delegate;
    }

     /// @inherited
     public function getAll(): array
     {
         return $this->getRegistry();
     }

     /**
      * @param array $function
      */
     public function add(array $function): void
     {
         $this->initializeRegistryIfNecessary();

         $this->registry[$function['fqcn']] = $function;
     }

     /**
      * @param array $function
      */
     public function remove(array $function): void
     {
         $this->initializeRegistryIfNecessary();

         if (isset($this->registry[$function['fqcn']])) {
             unset($this->registry[$function['fqcn']]);
         }
     }

     /**
      * @return void
      */
     public function reset(): void
     {
         $this->registry = null;
     }

     /**
      * @return array
      */
     protected function getRegistry(): array
     {
         $this->initializeRegistryIfNecessary();

         return $this->registry;
     }

     /**
      * @return void
      */
     protected function initializeRegistryIfNecessary(): void
     {
         if ($this->registry === null) {
             $this->initializeRegistry();
         }
     }

     /**
      * @return void
      */
     protected function initializeRegistry(): void
     {
         $this->registry = $this->delegate->getAll();
     }
}