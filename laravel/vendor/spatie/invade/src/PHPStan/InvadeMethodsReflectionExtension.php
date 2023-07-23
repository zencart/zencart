<?php

namespace Spatie\Invade\PHPStan;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\ShouldNotHappenException;
use Spatie\Invade\Invader;

class InvadeMethodsReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->is(Invader::class)) {
            return false;
        }

        $invaded = $classReflection->getActiveTemplateTypeMap()->getType('T')
            ?? throw new ShouldNotHappenException();

        return $invaded->hasMethod($methodName)->yes();
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $invaded = $classReflection->getActiveTemplateTypeMap()->getType('T')
            ?? throw new ShouldNotHappenException();

        return new InvadedMethodReflection(
            $invaded->getMethod($methodName, new OutOfClassScope()),
        );
    }
}
