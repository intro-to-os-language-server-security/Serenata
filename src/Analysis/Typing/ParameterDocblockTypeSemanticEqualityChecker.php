<?php

namespace PhpIntegrator\Analysis\Typing;

use UnexpectedValueException;

use PhpIntegrator\Analysis\ClasslikeInfoBuilder;

use PhpIntegrator\Analysis\Typing\Resolving\FileTypeResolverInterface;
use PhpIntegrator\Analysis\Typing\Resolving\FileTypeResolverFactoryInterface;

use PhpIntegrator\Parsing\DocblockTypes;

use PhpIntegrator\Utility\DocblockTyping\DocblockType;
use PhpIntegrator\Utility\DocblockTyping\ClassDocblockType;
use PhpIntegrator\Utility\DocblockTyping\ArrayDocblockType;
use PhpIntegrator\Utility\DocblockTyping\SpecialDocblockTypeString;

use PhpIntegrator\Utility\Typing\Type;
use PhpIntegrator\Utility\Typing\TypeList;
use PhpIntegrator\Utility\Typing\ClassType;
use PhpIntegrator\Utility\Typing\SpecialTypeString;

/**
 * Checks if a specified (normal parameter) type is semantically equal to a docblock type specification.
 */
class ParameterDocblockTypeSemanticEqualityChecker
{
    /**
     * @var FileTypeResolverFactoryInterface
     */
    private $fileTypeResolverFactory;

    /**
     * @var ClasslikeInfoBuilder
     */
    private $classlikeInfoBuilder;

    /**
     * @param FileTypeResolverFactoryInterface $fileTypeResolverFactory
     * @param ClasslikeInfoBuilder             $classlikeInfoBuilder
     */
    public function __construct(
        FileTypeResolverFactoryInterface $fileTypeResolverFactory,
        ClasslikeInfoBuilder $classlikeInfoBuilder
    ) {
        $this->fileTypeResolverFactory = $fileTypeResolverFactory;
        $this->classlikeInfoBuilder = $classlikeInfoBuilder;
    }

    /**
     * @param array  $parameter
     * @param array  $docblockParameter
     * @param string $filePath
     * @param int    $line
     *
     * @return bool
     */
    public function isEqual(array $parameter, array $docblockParameter, string $filePath, int $line): bool
    {
        $fileTypeResolver = $this->fileTypeResolverFactory->create($filePath);

        $parameterTypeList = $this->calculateParameterTypeList($parameter, $line, $fileTypeResolver);
        $docblockType = $this->getResolvedDocblockParameterType($docblockParameter['type'], $line, $fileTypeResolver);

        if (!$this->doesParameterTypeListMatchDocblockTypeList($parameterTypeList, $docblockType)) {
            return false;
        } elseif ($parameter['isReference'] !== $docblockParameter['isReference']) {
            return false;
        }

        return true;
    }

    /**
     * @param array                     $parameter
     * @param int                       $line
     * @param FileTypeResolverInterface $fileTypeResolver
     *
     * @return array
     */
    protected function calculateParameterTypeList(
        array $parameter,
        int $line,
        FileTypeResolverInterface $fileTypeResolver
    ): TypeList {
        $baseType = $fileTypeResolver->resolve($parameter['type'], $line);

        if ($parameter['isVariadic']) {
            $baseType .= '[]';
        }

        $typeList = [$baseType];

        if ($parameter['isNullable']) {
            $typeList[] = SpecialTypeString::NULL_;
        }

        return TypeList::createFromStringTypeList(...$typeList);
    }

    /**
     * @param DocblockTypes\DocblockType $docblockType
     * @param int                        $line
     * @param FileTypeResolverInterface  $fileTypeResolver
     *
     * @return DocblockTypes\DocblockType
     */
    protected function getResolvedDocblockParameterType(
        DocblockTypes\DocblockType $docblockType,
        int $line,
        FileTypeResolverInterface $fileTypeResolver
    ): DocblockTypes\DocblockType {
        if ($docblockType instanceof DocblockTypes\CompoundDocblockType) {
            return new DocblockTypes\CompoundDocblockType(...array_map(function (DocblockTypes\DocblockType $type) use ($line, $fileTypeResolver) {
                return $this->getResolvedDocblockParameterType($type, $line, $fileTypeResolver);
            }, $docblockType->getParts()));
        } elseif ($docblockType instanceof DocblockTypes\SpecializedArrayDocblockType) {
            $resolvedType = $this->getResolvedDocblockParameterType($docblockType->getType(), $line, $fileTypeResolver);

            return new DocblockTypes\SpecializedArrayDocblockType($resolvedType);
        } elseif ($docblockType instanceof DocblockTypes\ClassDocblockType) {
            $resolvedType = $fileTypeResolver->resolve($docblockType->getName(), $line);

            return new DocblockTypes\ClassDocblockType($resolvedType);
        }

        return $docblockType;
    }

    /**
     * @param TypeList                   $parameterTypeList
     * @param DocblockTypes\DocblockType $docblockType
     *
     * @return bool
     */
    protected function doesParameterTypeListMatchDocblockTypeList(
        TypeList $parameterTypeList,
        DocblockTypes\DocblockType $docblockType
    ): bool {
        if ($this->doesParameterTypeListStrictlyMatchDocblockTypeList($parameterTypeList, $docblockType)) {
            return true;
        } elseif ($parameterTypeList->hasStringType(SpecialTypeString::ARRAY_)) {
            return $this->doesParameterArrayTypeListMatchDocblockTypeList($parameterTypeList, $docblockType);
        } elseif ($this->doesParameterTypeListContainClassType($parameterTypeList)) {
            return $this->doesParameterClassTypeListMatchDocblockTypeList($parameterTypeList, $docblockType);
        }

        return false;
    }

    /**
     * @param TypeList                   $parameterTypeList
     * @param DocblockTypes\DocblockType $docblockType
     *
     * @return bool
     */
    protected function doesParameterTypeListStrictlyMatchDocblockTypeList(
        TypeList $parameterTypeList,
        DocblockTypes\DocblockType $docblockType
    ): bool {
        $parameterTypeListAsCompoundTypeString = implode('|', array_map(function (Type $type) {
            return $type->toString();
        }, $parameterTypeList->toArray()));



        // if ($docblockType instanceof DocblockTypes\CompoundDocblockType) {
        //     // return $docblockType-
        // }

        if ($docblockType->toString() === $parameterTypeListAsCompoundTypeString) {
            return true;
        }

        //



        // if ($docblockTypeList->equals(DocblockTypeList::createFromTypeList($parameterTypeList))) {
        //     return true;
        // }

        return false;
    }

    /**
     * @param TypeList $typeList
     *
     * @return bool
     */
    protected function doesParameterTypeListContainClassType(TypeList $typeList): bool
    {
        return !$typeList->filter(function (Type $type) {
            return $type instanceof ClassType;
        })->isEmpty();
    }

    /**
     * @param TypeList                   $parameterTypeList
     * @param DocblockTypes\DocblockType $docblockType
     *
     * @return bool
     */
    protected function doesParameterArrayTypeListMatchDocblockTypeList(
        TypeList $parameterTypeList,
        DocblockTypes\DocblockType $docblockType
    ): bool {
        $isDocblockTypeNullable =
            $docblockType instanceof DocblockTypes\CompoundDocblockType &&
            $docblockType->has(DocblockTypes\NullDocblockType::class);

        if ($parameterTypeList->hasStringType(SpecialTypeString::NULL_) !== $isDocblockTypeNullable) {
            return false;
        }

        if ($docblockType instanceof DocblockTypes\CompoundDocblockType) {
            $docblockTypesThatAreNotArrayTypes = $docblockType->filter(function (DocblockTypes\DocblockType $docblockType) {
                return
                    !$docblockType instanceof DocblockTypes\ArrayDocblockType &&
                    !$docblockType instanceof DocblockTypes\NullDocblockType;
            });

            return empty($docblockTypesThatAreNotArrayTypes);
        }

        return
            $docblockType instanceof DocblockTypes\ArrayDocblockType ||
            $docblockType instanceof DocblockTypes\NullDocblockType;
    }

    /**
     * @param TypeList                   $parameterTypeList
     * @param DocblockTypes\DocblockType $docblockType
     *
     * @return bool
     */
    protected function doesParameterClassTypeListMatchDocblockTypeList(
        TypeList $parameterTypeList,
        DocblockTypes\DocblockType $docblockType
    ): bool {
        $isDocblockTypeNullable =
            $docblockType instanceof DocblockTypes\CompoundDocblockType &&
            $docblockType->has(DocblockTypes\NullDocblockType::class);

        if ($parameterTypeList->hasStringType(SpecialTypeString::NULL_) !== $isDocblockTypeNullable) {
            return false;
        }

        $docblockTypesThatAreClassTypes = null;

        if ($docblockType instanceof DocblockTypes\CompoundDocblockType) {
            $docblockTypesThatAreNotClassTypes = $docblockType->filter(function (DocblockTypes\DocblockType $docblockType) {
                return
                    !$docblockType instanceof DocblockTypes\ClassDocblockType &&
                    !$docblockType instanceof DocblockTypes\NullDocblockType;
            });

            if (!empty($docblockTypesThatAreNotClassTypes)) {
                return false;
            }

            $docblockTypesThatAreClassTypes = $docblockType->filter(function (DocblockTypes\DocblockType $docblockType) {
                return $docblockType instanceof DocblockTypes\ClassDocblockType;
            });
        } else {
            $docblockTypesThatAreClassTypes = [$docblockType];
        }

        $parameterClassTypes = $parameterTypeList->filter(function (Type $type) {
            return $type instanceof ClassType;
        });

        $parameterClassType = $parameterClassTypes->toArray()[0];

        foreach ($docblockTypesThatAreClassTypes as $docblockTypeThatIsClassType) {
            if (!$this->doesDocblockClassSatisfyTypeParameterClassType($docblockTypeThatIsClassType, $parameterClassType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Indicates if the docblock satisfies the parameter type (hint).
     *
     * Satisfaction is achieved if either the parameter type matches the docblock type or if the docblock type
     * specializes the parameter type (i.e. it is a subclass of it or implements it as interface).
     *
     * @param DocblockTypes\ClassDocblockType $docblockType
     * @param ClassType                       $type
     *
     * @return bool
     */
    protected function doesDocblockClassSatisfyTypeParameterClassType(
        DocblockTypes\ClassDocblockType $docblockType,
        ClassType $type
    ): bool {
        if ($docblockType->getName() === $type->toString()) {
            return true;
        }

        try {
            $typeClassInfo = $this->classlikeInfoBuilder->getClasslikeInfo($type->toString());
            $docblockTypeClassInfo = $this->classlikeInfoBuilder->getClasslikeInfo($docblockType->getName());
        } catch (UnexpectedValueException $e) {
            return false;
        }

        if (in_array($typeClassInfo['name'], $docblockTypeClassInfo['parents'], true)) {
            return true;
        } elseif (in_array($typeClassInfo['name'], $docblockTypeClassInfo['interfaces'], true)) {
            return true;
        }

        return false;
    }
}
