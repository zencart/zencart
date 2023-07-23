<?php

namespace Spatie\Invade\PHPStan;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\ShouldNotHappenException;
use Spatie\Invade\Invader;

class InvadePropertiesReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (! $classReflection->is(Invader::class)) {
            return false;
        }

        $invaded = $classReflection->getActiveTemplateTypeMap()->getType('T')
            ?? throw new ShouldNotHappenException();

        return $invaded->hasProperty($propertyName)->yes();
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $invaded = $classReflection->getActiveTemplateTypeMap()->getType('T')
            ?? throw new ShouldNotHappenException();

        return new InvadedPropertyReflection(
            $invaded->getProperty($propertyName, new OutOfClassScope()),
        );
    }
}
