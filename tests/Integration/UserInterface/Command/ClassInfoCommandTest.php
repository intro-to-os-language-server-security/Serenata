<?php

namespace PhpIntegrator\Tests\Integration\UserInterface\Command;

use PhpIntegrator\Tests\Integration\AbstractIntegrationTest;

class ClassInfoCommandTest extends AbstractIntegrationTest
{
    /**
     * @return void
     */
    public function testLeadingSlashIsResolvedCorrectly(): void
    {
        $fileName = 'SimpleClass.phpt';

        static::assertSame(
            $this->getClassInfo($fileName, 'A\SimpleClass'),
            $this->getClassInfo($fileName, '\A\SimpleClass')
        );
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForASimpleClass(): void
    {
        $fileName = 'SimpleClass.phpt';

        $output = $this->getClassInfo($fileName, 'A\SimpleClass');

        static::assertSame([
            'name'               => 'SimpleClass',
            'fqcn'               => '\A\SimpleClass',
            'startLine'          => 10,
            'endLine'            => 13,
            'filename'           => $this->getPathFor($fileName),
            'type'               => 'class',
            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,
            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'isAnonymous'        => false,
            'isAbstract'         => false,
            'isFinal'            => false,
            'isAnnotation'       => false,
            'parents'            => [],
            'interfaces'         => [],
            'traits'             => [],
            'directParents'      => [],
            'directInterfaces'   => [],
            'directTraits'       => [],
            'directChildren'     => [],
            'directImplementors' => [],
            'directTraitUsers'   => [],
            'constants'          => [
                'class' => [
                    'name'               => 'class',
                    'startLine'          => 10,
                    'endLine'            => 10,
                    'defaultValue'       => '\'A\SimpleClass\'',
                    'filename'           => $this->getPathFor($fileName),
                    'isStatic'           => true,
                    'isDeprecated'       => false,
                    'hasDocblock'        => false,
                    'hasDocumentation'   => false,
                    'shortDescription'   => 'PHP built-in class constant that evaluates to the FQCN.',
                    'longDescription'    => null,
                    'typeDescription'    => null,

                    'types'             => [
                        [
                            'type'         => 'string',
                            'fqcn'         => 'string',
                            'resolvedType' => 'string'
                        ]
                    ],

                    'isPublic'           => true,
                    'isProtected'        => false,
                    'isPrivate'          => false,

                    'declaringClass'     => [
                        'fqcn'      => '\A\SimpleClass',
                        'filename'  => $this->getPathFor($fileName),
                        'startLine' => 10,
                        'endLine'   => 13,
                        'type'      => 'class'
                    ],

                    'declaringStructure' => [
                        'fqcn'            => '\A\SimpleClass',
                        'filename'        => $this->getPathFor($fileName),
                        'startLine'       => 10,
                        'endLine'         => 13,
                        'type'            => 'class',
                        'startLineMember' => 10,
                        'endLineMember'   => 10
                    ]
                ]
            ],
            'properties'         => [],
            'methods'            => []
        ], $output);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassProperties(): void
    {
        $fileName = 'ClassProperty.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame([
            'name'               => 'testProperty',
            'startLine'          => 14,
            'endLine'            => 14,
            'filename'           => $this->getPathFor($fileName),
            'defaultValue'       => "'test'",
            'isMagic'            => false,
            'isPublic'           => false,
            'isProtected'        => true,
            'isPrivate'          => false,
            'isStatic'           => false,
            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,
            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'typeDescription'    => null,

            'types'             => [
                [
                    'type'         => 'MyType',
                    'fqcn'         => '\A\MyType',
                    'resolvedType' => '\A\MyType'
                ],

                [
                    'type'         => 'string',
                    'fqcn'         => 'string',
                    'resolvedType' => 'string'
                ]
            ],

            'override'           => null,

            'declaringClass' => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 15,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 15,
                'type'            => 'class',
                'startLineMember' => 14,
                'endLineMember'   => 14
            ]
        ], $output['properties']['testProperty']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassMethods(): void
    {
        $fileName = 'ClassMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame([
            'name'               => 'testMethod',
            'startLine'          => 19,
            'endLine'            => 22,
            'filename'           => $this->getPathFor($fileName),

            'parameters'         => [
                [
                    'name'         => 'firstParameter',
                    'typeHint'     => '\DateTimeInterface',
                    'types'        => [
                        [
                            'type'         => '\DateTimeInterface',
                            'fqcn'         => '\DateTimeInterface',
                            'resolvedType' => '\DateTimeInterface'
                        ],

                        [
                            'type'         => '\DateTime',
                            'fqcn'         => '\DateTime',
                            'resolvedType' => '\DateTime'
                        ]
                    ],

                    'description'  => 'First parameter description.',
                    'defaultValue' => 'null',
                    'isReference'  => false,
                    'isVariadic'   => false,
                    'isOptional'   => true
                ],

                [
                    'name'         => 'secondParameter',
                    'typeHint'     => null,
                    'types'        => [
                        [
                            'type'         => 'bool',
                            'fqcn'         => 'bool',
                            'resolvedType' => 'bool'
                        ]
                    ],

                    'description'  => null,
                    'defaultValue' => 'true',
                    'isReference'  => true,
                    'isVariadic'   => false,
                    'isOptional'   => true
                ],

                [
                    'name'         => 'thirdParameter',
                    'typeHint'     => null,
                    'types'        => [],
                    'description'  => null,
                    'defaultValue' => null,
                    'isReference'  => false,
                    'isVariadic'   => true,
                    'isOptional'   => false
                ]
            ],

            'throws'             => [
                [
                    'type'        => '\UnexpectedValueException',
                    'description' => 'when something goes wrong.'
                ],

                [
                    'type'        => '\LogicException',
                    'description' => 'when something is wrong.'
                ]
            ],

            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,

            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'returnDescription'  => null,
            'returnTypeHint'     => null,

            'returnTypes' => [
                [
                    'type'         => 'mixed',
                    'fqcn'         => 'mixed',
                    'resolvedType' => 'mixed'
                ],

                [
                    'type'         => 'bool',
                    'fqcn'         => 'bool',
                    'resolvedType' => 'bool'
                ]
            ],

            'isMagic'            => false,
            'isPublic'           => true,
            'isProtected'        => false,
            'isPrivate'          => false,
            'isStatic'           => false,
            'isAbstract'         => false,
            'isFinal'            => false,
            'override'           => null,
            'implementations'    => [],

            'declaringClass'     => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 23,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 23,
                'type'            => 'class',
                'startLineMember' => 19,
                'endLineMember'   => 22
            ]
        ], $output['methods']['testMethod']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassConstants(): void
    {
        $fileName = 'ClassConstant.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame([
            'name'               => 'TEST_CONSTANT',
            'startLine'          => 14,
            'endLine'            => 14,
            'defaultValue'       => '5',
            'filename'           => $this->getPathFor($fileName),
            'isStatic'           => true,
            'isDeprecated'       => false,
            'hasDocblock'        => true,
            'hasDocumentation'   => true,
            'shortDescription'   => 'This is the summary.',
            'longDescription'    => 'This is a long description.',
            'typeDescription'    => null,

            'types'             => [
                [
                    'type'         => 'MyType',
                    'fqcn'         => '\A\MyType',
                    'resolvedType' => '\A\MyType'
                ],

                [
                    'type'         => 'string',
                    'fqcn'         => 'string',
                    'resolvedType' => 'string'
                ]
            ],

            'isPublic'           => true,
            'isProtected'        => false,
            'isPrivate'          => false,

            'declaringClass'     => [
                'fqcn'      => '\A\TestClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 5,
                'endLine'   => 15,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 5,
                'endLine'         => 15,
                'type'            => 'class',
                'startLineMember' => 14,
                'endLineMember'   => 14
            ]
        ], $output['constants']['TEST_CONSTANT']);
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForClasses(): void
    {
        $fileName = 'ClassDocblockInheritance.phpt';

        $childClassOutput = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');
        $anotherChildClassOutput = $this->getClassInfo($fileName, 'A\AnotherChildClass');

        static::assertSame('This is the summary.', $childClassOutput['shortDescription']);
        static::assertSame('This is a long description.', $childClassOutput['longDescription']);

        static::assertSame(
            'Pre. ' . $parentClassOutput['longDescription'] . ' Post.',
            $anotherChildClassOutput['longDescription']
        );
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForMethods(): void
    {
        $fileName = 'MethodDocblockInheritance.phpt';

        $traitOutput       = $this->getClassInfo($fileName, 'A\TestTrait');
        $interfaceOutput   = $this->getClassInfo($fileName, 'A\TestInterface');
        $childClassOutput  = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');

        $keysToTestForEquality = [
            'hasDocumentation',
            'isDeprecated',
            'longDescription',
            'shortDescription',
            'returnTypes',
            'parameters',
            'throws'
        ];

        foreach ($keysToTestForEquality as $key) {
            static::assertSame(
                $childClassOutput['methods']['basicDocblockInheritanceTraitTest'][$key],
                $traitOutput['methods']['basicDocblockInheritanceTraitTest'][$key]
            );

            static::assertSame(
                $childClassOutput['methods']['basicDocblockInheritanceInterfaceTest'][$key],
                $interfaceOutput['methods']['basicDocblockInheritanceInterfaceTest'][$key]
            );

            static::assertSame(
                $childClassOutput['methods']['basicDocblockInheritanceBaseClassTest'][$key],
                $parentClassOutput['methods']['basicDocblockInheritanceBaseClassTest'][$key]
            );
        }

        static::assertSame(
            'Pre. ' . $parentClassOutput['methods']['inheritDocBaseClassTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocBaseClassTest']['longDescription']
        );

        static::assertSame(
            'Pre. ' . $interfaceOutput['methods']['inheritDocInterfaceTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocInterfaceTest']['longDescription']
        );

        static::assertSame(
            'Pre. ' . $traitOutput['methods']['inheritDocTraitTest']['longDescription'] . ' Post.',
            $childClassOutput['methods']['inheritDocTraitTest']['longDescription']
        );
    }

    /**
     * @return void
     */
    public function testDocblockInheritanceWorksProperlyForProperties(): void
    {
        $fileName = 'PropertyDocblockInheritance.phpt';

        $traitOutput       = $this->getClassInfo($fileName, 'A\TestTrait');
        $childClassOutput  = $this->getClassInfo($fileName, 'A\ChildClass');
        $parentClassOutput = $this->getClassInfo($fileName, 'A\ParentClass');

        $keysToTestForEquality = [
            'hasDocumentation',
            'isDeprecated',
            'shortDescription',
            'longDescription',
            'typeDescription',
            'types'
        ];

        foreach ($keysToTestForEquality as $key) {
            static::assertSame(
                $childClassOutput['properties']['basicDocblockInheritanceTraitTest'][$key],
                $traitOutput['properties']['basicDocblockInheritanceTraitTest'][$key]
            );

            static::assertSame(
                $childClassOutput['properties']['basicDocblockInheritanceBaseClassTest'][$key],
                $parentClassOutput['properties']['basicDocblockInheritanceBaseClassTest'][$key]
            );
        }

        static::assertSame(
            $childClassOutput['properties']['inheritDocBaseClassTest']['longDescription'],
            'Pre. ' . $parentClassOutput['properties']['inheritDocBaseClassTest']['longDescription'] . ' Post.'
        );

        static::assertSame(
            $childClassOutput['properties']['inheritDocTraitTest']['longDescription'],
            'Pre. ' . $traitOutput['properties']['inheritDocTraitTest']['longDescription'] . ' Post.'
        );
    }

    /**
     * @return void
     */
    public function testMethodOverridingIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverride.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ]
                ],

                'description'  => null,
                'defaultValue' => null,
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => false
            ]
        ], $output['methods']['__construct']['parameters']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 25,
                'endLineMember'   => 28
            ],

            'startLine'   => 25,
            'endLine'     => 28,
            'wasAbstract' => false
        ], $output['methods']['__construct']['override']);

        static::assertSame(55, $output['methods']['__construct']['startLine']);
        static::assertSame(58, $output['methods']['__construct']['endLine']);

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ],

                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true
            ]
        ], $output['methods']['parentTraitMethod']['parameters']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 13,
                'endLine'         => 19,
                'type'            => 'trait',
                'startLineMember' => 15,
                'endLineMember'   => 18
            ],

            'startLine'   => 15,
            'endLine'     => 18,
            'wasAbstract' => false
        ], $output['methods']['parentTraitMethod']['override']);

        static::assertSame(65, $output['methods']['parentTraitMethod']['startLine']);
        static::assertSame(68, $output['methods']['parentTraitMethod']['endLine']);

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ],

                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true
            ]
        ], $output['methods']['parentMethod']['parameters']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 30,
                'endLineMember'   => 33
            ],

            'startLine'   => 30,
            'endLine'     => 33,
            'wasAbstract' => false
        ], $output['methods']['parentMethod']['override']);

        static::assertSame(70, $output['methods']['parentMethod']['startLine']);
        static::assertSame(73, $output['methods']['parentMethod']['endLine']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 21,
                'endLine'   => 39,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 21,
                'endLine'         => 39,
                'type'            => 'class',
                'startLineMember' => 35,
                'endLineMember'   => 38
            ],

            'startLine'   => 35,
            'endLine'     => 38,
            'wasAbstract' => false
        ], $output['methods']['ancestorMethod']['override']);

        static::assertSame(60, $output['methods']['ancestorMethod']['startLine']);
        static::assertSame(63, $output['methods']['ancestorMethod']['endLine']);

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ],

                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true
            ]
        ], $output['methods']['traitMethod']['parameters']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\TestTrait',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 41,
                'endLine'   => 49,
                'type'      => 'trait'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\TestTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 41,
                'endLine'         => 49,
                'type'            => 'trait',
                'startLineMember' => 43,
                'endLineMember'   => 46
            ],

            'startLine'   => 43,
            'endLine'     => 46,
            'wasAbstract' => false
        ], $output['methods']['traitMethod']['override']);

        static::assertSame(75, $output['methods']['traitMethod']['startLine']);
        static::assertSame(78, $output['methods']['traitMethod']['endLine']);

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',

                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ],

                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true
            ]
        ], $output['methods']['abstractMethod']['parameters']);

        static::assertSame($output['methods']['abstractMethod']['override']['wasAbstract'], true);
    }

    /**
     * @return void
     */
    public function testMethodOverridingOfParentImplementationIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverrideOfParentImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 10,
                'endLine'   => 16,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 16,
                'type'            => 'class',
                'startLineMember' => 12,
                'endLineMember'   => 15
            ],

            'startLine'   => 12,
            'endLine'     => 15,
            'wasAbstract' => false,
        ], $output['methods']['interfaceMethod']['override']);

        static::assertEmpty($output['methods']['interfaceMethod']['implementations']);

        static::assertSame(20, $output['methods']['interfaceMethod']['startLine']);
        static::assertSame(23, $output['methods']['interfaceMethod']['endLine']);
    }

    /**
     * @return void
     */
    public function testMethodOverridingAndImplementationSimultaneouslyIsAnalyzedCorrectly(): void
    {
        $fileName = 'MethodOverrideAndImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface',
                    'filename'  =>  $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ]
        ], $output['methods']['interfaceMethod']['implementations']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  =>  $this->getPathFor($fileName),
                'startLine' => 10,
                'endLine'   => 16,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 16,
                'type'            => 'class',
                'startLineMember' => 12,
                'endLineMember'   => 15
            ],

            'startLine'   => 12,
            'endLine'     => 15,
            'wasAbstract' => false
        ], $output['methods']['interfaceMethod']['override']);

        static::assertSame(20, $output['methods']['interfaceMethod']['startLine']);
        static::assertSame(23, $output['methods']['interfaceMethod']['endLine']);
    }

    /**
     * @return void
     */
    public function testPropertyOverridingIsAnalyzedCorrectly(): void
    {
        $fileName = 'PropertyOverride.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentTrait',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 10,
                'endLine'         => 13,
                'type'            => 'trait',
                'startLineMember' => 12,
                'endLineMember'   => 12
            ],

            'startLine' => 12,
            'endLine'   => 12
        ], $output['properties']['parentTraitProperty']['override']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 15,
                'endLine'         => 21,
                'type'            => 'class',
                'startLineMember' => 19,
                'endLineMember'   => 19
            ],

            'startLine' => 19,
            'endLine'   => 19
        ], $output['properties']['parentProperty']['override']);

        static::assertSame([
            'declaringClass' => [
                'fqcn'      => '\A\ParentClass',
                'filename'  => $this->getPathFor($fileName),
                'startLine' => 15,
                'endLine'   => 21,
                'type'      => 'class'
            ],

            'declaringStructure' => [
                'fqcn'            => '\A\ParentClass',
                'filename'        => $this->getPathFor($fileName),
                'startLine'       => 15,
                'endLine'         => 21,
                'type'            => 'class',
                'startLineMember' => 20,
                'endLineMember'   => 20
            ],

            'startLine' => 20,
            'endLine'   => 20
        ], $output['properties']['ancestorProperty']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceReferencedByParentClass(): void
    {
        $fileName = 'MethodImplementationFromParentClassInterface.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            [
                'name'         => 'foo',
                'typeHint'     => '\A\Foo',
                'types' => [
                    [
                        'type'         => 'Foo',
                        'fqcn'         => '\A\Foo',
                        'resolvedType' => '\A\Foo'
                    ],

                    [
                        'type'         => 'null',
                        'fqcn'         => 'null',
                        'resolvedType' => 'null'
                    ]
                ],

                'description'  => null,
                'defaultValue' => 'null',
                'isReference'  => false,
                'isVariadic'   => false,
                'isOptional'   => true
            ]
        ], $output['methods']['parentInterfaceMethod']['parameters']);

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\ParentClass',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'class'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\ParentInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ]
        ], $output['methods']['parentInterfaceMethod']['implementations']);

        static::assertSame('\A\ChildClass', $output['methods']['parentInterfaceMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\ChildClass', $output['methods']['parentInterfaceMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceParent(): void
    {
        $fileName = 'MethodImplementationFromInterfaceParent.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\ParentInterface',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\ParentInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ]
        ], $output['methods']['interfaceParentMethod']['implementations']);

        static::assertNull($output['methods']['interfaceParentMethod']['override']);

        static::assertSame('\A\ChildClass', $output['methods']['interfaceParentMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\ChildClass', $output['methods']['interfaceParentMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationIsAnalyzedCorrectlyWhenImplementingMethodFromInterfaceDirectlyReferenced(): void
    {
        $fileName = 'MethodImplementationFromDirectInterface.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ]
        ], $output['methods']['interfaceMethod']['implementations']);

        static::assertSame('\A\ChildClass', $output['methods']['interfaceMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\ChildClass', $output['methods']['interfaceMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodParameterTypeIsCorrectlyDeducedIfParameterIsVariadic(): void
    {
        $fileName = 'MethodVariadicParameter.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');
        $parameters = $output['methods']['testMethod']['parameters'];

        static::assertSame('\stdClass[]', $parameters[0]['types'][0]['type']);
    }

    /**
     * @return void
     */
    public function testDataIsCorrectForClassInheritance(): void
    {
        $fileName = 'ClassInheritance.phpt';

        $output = $this->getClassInfo($fileName, 'A\ChildClass');

        static::assertSame($output['parents'], ['\A\BaseClass', '\A\AncestorClass']);
        static::assertSame($output['directParents'], ['\A\BaseClass']);

        static::assertThat($output['constants'], $this->arrayHasKey('INHERITED_CONSTANT'));
        static::assertThat($output['constants'], $this->arrayHasKey('CHILD_CONSTANT'));

        static::assertThat($output['properties'], $this->arrayHasKey('inheritedProperty'));
        static::assertThat($output['properties'], $this->arrayHasKey('childProperty'));

        static::assertThat($output['methods'], $this->arrayHasKey('inheritedMethod'));
        static::assertThat($output['methods'], $this->arrayHasKey('childMethod'));

        // Do a couple of sanity checks.
        static::assertSame('\A\BaseClass', $output['constants']['INHERITED_CONSTANT']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseClass', $output['properties']['inheritedProperty']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseClass', $output['methods']['inheritedMethod']['declaringClass']['fqcn']);

        static::assertSame('\A\BaseClass', $output['constants']['INHERITED_CONSTANT']['declaringStructure']['fqcn']);
        static::assertSame('\A\BaseClass', $output['properties']['inheritedProperty']['declaringStructure']['fqcn']);
        static::assertSame('\A\BaseClass', $output['methods']['inheritedMethod']['declaringStructure']['fqcn']);

        $output = $this->getClassInfo($fileName, 'A\BaseClass');

        static::assertSame($output['directChildren'], ['\A\ChildClass']);
        static::assertSame($output['parents'], ['\A\AncestorClass']);
    }

    /**
     * @return void
     */
    public function testInterfaceImplementationIsCorrectlyProcessed(): void
    {
        $fileName = 'InterfaceImplementation.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame(['\A\BaseInterface', '\A\FirstInterface', '\A\SecondInterface'], $output['interfaces']);
        static::assertSame(['\A\FirstInterface', '\A\SecondInterface'], $output['directInterfaces']);

        static::assertThat($output['constants'], $this->arrayHasKey('FIRST_INTERFACE_CONSTANT'));
        static::assertThat($output['constants'], $this->arrayHasKey('SECOND_INTERFACE_CONSTANT'));

        static::assertThat($output['methods'], $this->arrayHasKey('methodFromFirstInterface'));
        static::assertThat($output['methods'], $this->arrayHasKey('methodFromSecondInterface'));

        // Do a couple of sanity checks.
        static::assertSame('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringStructure']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['methodFromFirstInterface']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstInterface', $output['methods']['methodFromFirstInterface']['declaringStructure']['fqcn']);

        static::assertSame('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstInterface', $output['constants']['FIRST_INTERFACE_CONSTANT']['declaringStructure']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['methodFromFirstInterface']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstInterface', $output['methods']['methodFromFirstInterface']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testClassTraitUsageIsCorrectlyProcessed(): void
    {
        $fileName = 'ClassTraitUsage.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame(['\A\FirstTrait', '\A\SecondTrait', '\A\BaseTrait'], $output['traits']);
        static::assertSame(['\A\FirstTrait', '\A\SecondTrait'], $output['directTraits']);

        static::assertThat($output['properties'], $this->arrayHasKey('baseTraitProperty'));
        static::assertThat($output['properties'], $this->arrayHasKey('firstTraitProperty'));
        static::assertThat($output['properties'], $this->arrayHasKey('secondTraitProperty'));

        static::assertThat($output['methods'], $this->arrayHasKey('testAmbiguous'));
        static::assertThat($output['methods'], $this->arrayHasKey('testAmbiguousAsWell'));
        static::assertThat($output['methods'], $this->arrayHasKey('baseTraitMethod'));

        // Do a couple of sanity checks.
        static::assertSame('\A\BaseClass', $output['properties']['baseTraitProperty']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseTrait', $output['properties']['baseTraitProperty']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestClass', $output['properties']['firstTraitProperty']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['properties']['firstTraitProperty']['declaringStructure']['fqcn']);

        static::assertSame('\A\BaseClass', $output['methods']['baseTraitMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseTrait', $output['methods']['baseTraitMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestClass', $output['methods']['test1']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['methods']['test1']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestClass', $output['methods']['overriddenInBaseAndChild']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['overriddenInBaseAndChild']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestClass', $output['methods']['overriddenInChild']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['overriddenInChild']['declaringStructure']['fqcn']);

        // Test the 'as' keyword for renaming trait method.
        static::assertThat($output['methods'], $this->arrayHasKey('test1'));
        static::assertThat($output['methods'], $this->logicalNot($this->arrayHasKey('test')));

        static::assertTrue($output['methods']['test1']['isPrivate']);

        static::assertSame('\A\TestClass', $output['methods']['testAmbiguous']['declaringClass']['fqcn']);
        static::assertSame('\A\SecondTrait', $output['methods']['testAmbiguous']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestClass', $output['methods']['testAmbiguousAsWell']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['methods']['testAmbiguousAsWell']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithoutAccessModifier(): void
    {
        $fileName = 'ClassTraitAliasWithoutAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertFalse($output['methods']['test1']['isPublic']);
        static::assertTrue($output['methods']['test1']['isProtected']);
        static::assertFalse($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testClassTraitAliasWithAccessModifier(): void
    {
        $fileName = 'ClassTraitAliasWithAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertFalse($output['methods']['test1']['isPublic']);
        static::assertFalse($output['methods']['test1']['isProtected']);
        static::assertTrue($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenClassHasMethodThatIsAlsoDefinedByOneOfItsOwnTraits(): void
    {
        $fileName = 'ClassOverridesOwnTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        static::assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenClassHasMethodThatIsAlsoDefinedByOneOfItsOwnTraitsAndByTheParent(): void
    {
        $fileName = 'ClassOverridesTraitAndParentMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\BaseClass', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseClass', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        static::assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testTraitTraitUsageIsCorrectlyProcessed(): void
    {
        $fileName = 'TraitTraitUsage.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        static::assertSame(['\A\FirstTrait', '\A\SecondTrait'], $output['traits']);
        static::assertSame(['\A\FirstTrait', '\A\SecondTrait'], $output['directTraits']);

        static::assertThat($output['properties'], $this->arrayHasKey('firstTraitProperty'));
        static::assertThat($output['properties'], $this->arrayHasKey('secondTraitProperty'));

        static::assertThat($output['methods'], $this->arrayHasKey('testAmbiguous'));
        static::assertThat($output['methods'], $this->arrayHasKey('testAmbiguousAsWell'));

        // Do a couple of sanity checks.
        static::assertSame('\A\TestTrait', $output['properties']['firstTraitProperty']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['properties']['firstTraitProperty']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestTrait', $output['methods']['test1']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['methods']['test1']['declaringStructure']['fqcn']);

        // Test the 'as' keyword for renaming trait method.
        static::assertThat($output['methods'], $this->arrayHasKey('test1'));
        static::assertThat($output['methods'], $this->logicalNot($this->arrayHasKey('test')));

        static::assertTrue($output['methods']['test1']['isPrivate']);

        static::assertSame('\A\TestTrait', $output['methods']['testAmbiguous']['declaringClass']['fqcn']);
        static::assertSame('\A\SecondTrait', $output['methods']['testAmbiguous']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestTrait', $output['methods']['testAmbiguousAsWell']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['methods']['testAmbiguousAsWell']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testTraitTraitAliasWithoutAccessModifier(): void
    {
        $fileName = 'TraitTraitAliasWithoutAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        static::assertFalse($output['methods']['test1']['isPublic']);
        static::assertTrue($output['methods']['test1']['isProtected']);
        static::assertFalse($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testTraitTraitAliasWithAccessModifier(): void
    {
        $fileName = 'TraitTraitAliasWithAccessModifier.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        static::assertFalse($output['methods']['test1']['isPublic']);
        static::assertFalse($output['methods']['test1']['isProtected']);
        static::assertTrue($output['methods']['test1']['isPrivate']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenTraitHasMethodThatIsAlsoDefinedByOneOfItsOwnTraits(): void
    {
        $fileName = 'TraitOverridesOwnTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestTrait');

        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\FirstTrait', $output['methods']['someMethod']['override']['declaringClass']['fqcn']);
        static::assertSame('\A\FirstTrait', $output['methods']['someMethod']['override']['declaringStructure']['fqcn']);

        static::assertEmpty($output['methods']['someMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodOverrideDataIsCorrectWhenInterfaceOverridesParentInterfaceMethod(): void
    {
        $fileName = 'InterfaceOverridesParentInterfaceMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestInterface');

        static::assertSame('\A\TestInterface', $output['methods']['interfaceMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestInterface', $output['methods']['interfaceMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\BaseInterface', $output['methods']['interfaceMethod']['override']['declaringClass']['fqcn']);
        static::assertSame('\A\BaseInterface', $output['methods']['interfaceMethod']['override']['declaringStructure']['fqcn']);

        static::assertEmpty($output['methods']['interfaceMethod']['implementations']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenTraitMethodIndirectlyImplementsInterfaceMethod(): void
    {
        $fileName = 'TraitImplementsInterfaceMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame('\A\TestInterface', $output['methods']['someMethod']['implementations'][0]['declaringClass']['fqcn']);
        static::assertSame('\A\TestInterface', $output['methods']['someMethod']['implementations'][0]['declaringStructure']['fqcn']);

        static::assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassReceivesSameInterfaceMethodFromTwoInterfacesAndDoesNotImplementMethod(): void
    {
        $fileName = 'ClassWithTwoInterfacesWithSameMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestInterface1', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertEmpty($output['methods']['someMethod']['implementations']);

        static::assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodDeclaringStructureIsCorrectWhenMethodDirectlyOriginatesFromTrait(): void
    {
        $fileName = 'ClassUsingTraitMethod.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassMethodImplementsMultipleInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassMethodImplementsMultipleInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        static::assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassTraitMethodImplementsMultipleInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassTraitMethodImplementsMultipleInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestTrait', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        static::assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testMethodImplementationDataIsCorrectWhenClassMethodImplementsMultipleDirectAndIndirectInterfaceMethodsSimultaneously(): void
    {
        $fileName = 'ClassMethodImplementsMultipleDirectAndIndirectInterfaceMethods.phpt';

        $output = $this->getClassInfo($fileName, 'A\TestClass');

        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringClass']['fqcn']);
        static::assertSame('\A\TestClass', $output['methods']['someMethod']['declaringStructure']['fqcn']);

        static::assertSame([
            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface1',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 5,
                    'endLine'   => 8,
                    'type'      => 'interface'
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface1',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 5,
                    'endLine'         => 8,
                    'type'            => 'interface',
                    'startLineMember' => 7,
                    'endLineMember'   => 7
                ],

                'startLine' => 7,
                'endLine'   => 7
            ],

            [
                'declaringClass' => [
                    'fqcn'      => '\A\TestInterface2',
                    'filename'  => $this->getPathFor($fileName),
                    'startLine' => 10,
                    'endLine'   => 13,
                    'type'      => 'interface',
                ],

                'declaringStructure' => [
                    'fqcn'            => '\A\TestInterface2',
                    'filename'        => $this->getPathFor($fileName),
                    'startLine'       => 10,
                    'endLine'         => 13,
                    'type'            => 'interface',
                    'startLineMember' => 12,
                    'endLineMember'   => 12
                ],

                'startLine' => 12,
                'endLine'   => 12
            ]
        ], $output['methods']['someMethod']['implementations']);

        static::assertNull($output['methods']['someMethod']['override']);
    }

    /**
     * @return void
     */
    public function testSpecialTypesAreCorrectlyResolved(): void
    {
        $fileName = 'ResolveSpecialTypes.phpt';

        $output = $this->getClassInfo($fileName, 'A\childClass');

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropSelf']['types']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['basePropStatic']['types']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['basePropThis']['types']);

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propSelf']['types']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propStatic']['types']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['properties']['propThis']['types']);

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodSelf']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodStatic']['returnTypes']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodThis']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodSelf']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodStatic']['returnTypes']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodThis']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'childClass',
                'fqcn'         => '\A\childClass',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['methodOwnClassName']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][0]['types']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][1]['types']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\childClass'
            ]
        ], $output['methods']['baseMethodWithParameters']['parameters'][2]['types']);

        $output = $this->getClassInfo($fileName, 'A\ParentClass');

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropSelf']['types']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropStatic']['types']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['properties']['basePropThis']['types']);

        static::assertSame([
            [
                'type'         => 'self',
                'fqcn'         => 'self',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodSelf']['returnTypes']);

        static::assertSame([
            [
                'type'         => 'static',
                'fqcn'         => 'static',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodStatic']['returnTypes']);

        static::assertSame([
            [
                'type'         => '$this',
                'fqcn'         => '$this',
                'resolvedType' => '\A\ParentClass'
            ]
        ], $output['methods']['baseMethodThis']['returnTypes']);
    }

    /**
     * @return void
     */
    public function testSkipsInterfaceImplementedTwice(): void
    {
        $fileName = 'InterfaceImplementedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        static::assertSame(['\A\I'], $output['interfaces']);
    }

    /**
     * @return void
     */
    public function testSkipsTraitUsedTwice(): void
    {
        $fileName = 'TraitUsedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        static::assertSame(['\A\T', '\A\T2'], $output['traits']);
    }

    /**
     * @return void
     */
    public function testSkipsInterfaceExtendedTwice(): void
    {
        $fileName = 'InterfaceExtendedTwice.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestInterface');

        static::assertSame(['\A\I'], $output['parents']);
    }

    /**
     * @return void
     */
    public function testUnresolvedReturnType(): void
    {
        $fileName = 'UnresolvedReturnType.phpt';

        $output = $this->getClassInfo($fileName, '\A\TestClass');

        static::assertSame([
            [
                'type'         => 'DateTime',
                'fqcn'         => '\DateTime',
                'resolvedType' => '\DateTime'
            ]
        ], $output['methods']['foo']['returnTypes']);
    }

    /**
     * @expectedException \UnexpectedValueException
     *
     * @return void
     */
    public function testFailsOnUnknownClass(): void
    {
        $output = $this->getClassInfo('SimpleClass.phpt', 'DoesNotExist');
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassExtendingItselfDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyExtends.phpt';

        static::assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassImplementingItselfDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyImplements.phpt';

        static::assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @return void
     */
    public function testCircularDependencyWithClassUsingItselfAsTraitDoesNotLoop(): void
    {
        $fileName = 'CircularDependencyUses.phpt';

        static::assertNotNull($this->getClassInfo($fileName, 'A\C'));
    }

    /**
     * @return void
     */
    public function testInterfaceIncorrectlyUsingTraitDoesNotCrash(): void
    {
        $fileName = 'InterfaceIncorrectlyUsesTrait.phpt';

        static::assertNotNull($this->getClassInfo($fileName, 'A\I'));
    }

    /**
     * @param string $file
     * @param string $fqcn
     *
     * @return array
     */
    private function getClassInfo(string $file, string $fqcn): array
    {
        $path = $this->getPathFor($file);

        $this->indexTestFile($this->container, $path);

        $command = $this->container->get('classInfoCommand');

        return $command->getClassInfo($fqcn);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getPathFor(string $file): string
    {
        return __DIR__ . '/ClassInfoCommandTest/' . $file;
    }
}
